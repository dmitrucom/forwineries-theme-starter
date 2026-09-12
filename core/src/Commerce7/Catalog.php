<?php
// GENERATED FILE — do not edit directly. Synced from forwineries-theme-core.
// Edit the source there and re-run tools/sync-core.sh.
namespace ForWineries\Core\Commerce7;

use ForWineries\Core\Config;
use ForWineries\Core\Schema;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Read-only Commerce7 REST helper + the wine-catalog render functions
 * built on it — used ONLY for the optional teaser shortcodes/blocks/
 * widgets (Featured Wines, Wines Catalog, Related Wines, Club teaser).
 * Never used for cart/checkout/payment — that's the real Commerce7
 * storefront (Integration::register()'s commerce7.js), untouched here.
 *
 * Ported near-verbatim from the original 5 themes' inc/commerce7.php —
 * the REST/catalog logic was confirmed identical in behavior across all
 * five (only brand strings/CSS class prefixes differed).
 *
 * Query PARAMETERS: 'type'/'availability' filters seemed reasonable but
 * Commerce7's API actually rejects them with HTTP 422 (confirmed live)
 * — fetch_all_wines() filters client-side on product.type === 'Wine' /
 * product.webStatus === 'Available' instead, using field names verified
 * against a real Commerce7 example product response, not guessed.
 */
class Catalog {

	public static function register( Config $config ): void {
		add_action( 'wp_enqueue_scripts', function () use ( $config ) {
			if ( ! Integration::rest_ready( $config ) ) return;
			wp_enqueue_script(
				'fw-c7-wines-catalog',
				get_stylesheet_directory_uri() . '/core/assets/js/wines-catalog.js',
				array(),
				Config::asset_version( '/core/assets/js/wines-catalog.js' ),
				true
			);
			// Related-wines rail only ever appears once REST is
			// configured (render_related_wines() returns '' otherwise),
			// so its slider arrows share the same gate as the catalog JS.
			wp_enqueue_script(
				'fw-related-wines-slider',
				get_stylesheet_directory_uri() . '/core/assets/js/related-wines-slider.js',
				array(),
				Config::asset_version( '/core/assets/js/related-wines-slider.js' ),
				true
			);
		} );

		add_shortcode( 'c7_wines', function ( $atts = array() ) use ( $config ) {
			return self::render_wines( $config, (array) $atts );
		} );
		add_shortcode( 'c7_wines_catalog', function () use ( $config ) {
			return self::render_wines_catalog( $config );
		} );
		add_shortcode( 'c7_club', function ( $atts = array() ) use ( $config ) {
			return self::render_club_teaser( $config, (array) $atts );
		} );
	}

	public static function clear_cache( Config $config ): void {
		global $wpdb;
		$prefix = $config->option_key( 'c7' );
		$wpdb->query( $wpdb->prepare(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
			'_transient_' . $prefix . '_%',
			'_transient_timeout_' . $prefix . '_%'
		) );
	}

	private static function request( Config $config, string $endpoint, array $query = array() ) {
		if ( ! Integration::rest_ready( $config ) ) {
			return new \WP_Error( 'fw_c7_not_configured', __( 'Commerce7 REST credentials are not configured.', $config->text_domain() ) );
		}

		$cache_key = $config->option_key( 'c7' ) . '_' . md5( $endpoint . wp_json_encode( $query ) );
		$cached    = get_transient( $cache_key );
		if ( false !== $cached ) {
			return $cached;
		}

		$url = 'https://api.commerce7.com/v1/' . ltrim( $endpoint, '/' );
		if ( ! empty( $query ) ) {
			$url = add_query_arg( $query, $url );
		}

		$response = wp_remote_get( $url, array(
			'timeout' => 12,
			'headers' => array(
				'Authorization' => 'Basic ' . base64_encode( Integration::credential( $config, 'app_id' ) . ':' . Integration::credential( $config, 'secret_key' ) ),
				'tenant'        => Integration::credential( $config, 'tenant_id' ),
				'Accept'        => 'application/json',
			),
		) );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = wp_remote_retrieve_response_code( $response );
		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( $code < 200 || $code >= 300 || ! is_array( $body ) ) {
			return new \WP_Error( 'fw_c7_api_error', sprintf( __( 'Commerce7 API returned HTTP %d.', $config->text_domain() ), $code ) );
		}

		set_transient( $cache_key, $body, HOUR_IN_SECONDS );
		return $body;
	}

	/**
	 * Commerce7 seeds a fresh product catalog with placeholder titles
	 * like "Sample - 2015 Chardonnay" — stripped wherever this theme
	 * renders a product title server-side. Commerce7's OWN client-
	 * rendered widgets are a separate problem this can't reach — see
	 * core/assets/js/c7-widget-cleanup.js for the client-side equivalent.
	 */
	public static function clean_title( string $title ): string {
		return trim( preg_replace( '/^\s*sample\s*-\s*/i', '', $title ) );
	}

	/**
	 * Commerce7 has no "featured" flag on a product — theme-side setting
	 * instead (comma-separated product slugs), so a winery can mark
	 * specific wines to stand out without touching Commerce7 admin.
	 */
	public static function featured_wine_slugs( Config $config ): array {
		$raw = get_option( $config->option_key( 'c7_featured_wine_slugs' ), '' );
		if ( ! $raw ) return array();
		return array_filter( array_map( 'sanitize_title', array_map( 'trim', explode( ',', $raw ) ) ) );
	}

	/**
	 * Per-wine scarcity badges ("Only 12 cases left") — one "slug: badge
	 * text" pair per line, same theme-side-setting reasoning as
	 * featured_wine_slugs(). Deliberately manual, not real-time inventory
	 * from Commerce7's REST API — that would need new REST scope and
	 * risks showing a wrong/stale number with no client-facing framing.
	 */
	public static function wine_badges( Config $config ): array {
		$raw = get_option( $config->option_key( 'c7_wine_badges' ), '' );
		if ( ! $raw ) return array();

		$badges = array();
		foreach ( explode( "\n", $raw ) as $line ) {
			$line = trim( $line );
			if ( ! $line || strpos( $line, ':' ) === false ) continue;
			list( $slug, $text ) = explode( ':', $line, 2 );
			$slug = sanitize_title( trim( $slug ) );
			$text = trim( $text );
			if ( $slug && $text ) $badges[ $slug ] = $text;
		}
		return $badges;
	}

	/**
	 * Returns raw product objects on success (possibly empty, if
	 * Commerce7 genuinely has none matching), or a WP_Error if the API
	 * call itself failed — callers need to tell those two cases apart to
	 * show a useful message, so this deliberately does NOT collapse them
	 * into an empty array.
	 */
	public static function fetch_all_wines( Config $config ) {
		$all         = array();
		$raw_fetched = 0;
		$page        = 1;
		$total       = null;
		$max_pages   = 10; // 10 x 50 = 500 products — generous ceiling for a boutique winery catalog.

		do {
			$data = self::request( $config, 'product', array( 'limit' => 50, 'page' => $page ) );
			if ( is_wp_error( $data ) ) {
				return $page === 1 ? $data : $all; // surface the error only if page 1 got nothing at all
			}
			if ( empty( $data['products'] ) ) break;

			foreach ( $data['products'] as $product ) {
				$raw_fetched++;
				$is_wine      = isset( $product['type'] ) && $product['type'] === 'Wine';
				$is_available = isset( $product['webStatus'] ) && $product['webStatus'] === 'Available';
				if ( $is_wine && $is_available ) {
					$all[] = $product;
				}
			}
			$total = isset( $data['total'] ) ? (int) $data['total'] : $raw_fetched;
			$page++;
		} while ( $raw_fetched < $total && $page <= $max_pages );

		return $all;
	}

	/**
	 * Commerce7's own image CDN serves whichever named size sits in the
	 * URL's path (.../images/{original|large|medium|small}/filename) —
	 * confirmed live: "original" here is the untouched upload (900x3000,
	 * ~1MB PNG), "large" is a real resize (300x1000, ~120KB), not a
	 * CSS-scaled copy of the same bytes. `$product['image']` from the
	 * REST API always comes back as the "original" variant, which is
	 * appropriate for a large, prominent display (e.g. the Flagship Wine
	 * split-photo section, which calls shape_wine() too) but wasteful
	 * for a small thumbnail — confirmed as the direct cause of "Keep
	 * Exploring" loading slowly, one ~1MB bottle photo per related wine.
	 * Deliberately applied only where each thumbnail is actually used,
	 * not inside shape_wine() itself, so a caller displaying the photo
	 * large still gets the original.
	 */
	private static function thumbnail_image( string $url, string $size = 'large' ): string {
		if ( ! $url ) return $url;
		return str_replace( '/images/original/', '/images/' . $size . '/', $url );
	}

	public static function shape_wine( array $product ): array {
		$variant = isset( $product['variants'][0] ) ? $product['variants'][0] : array();
		$wine    = isset( $product['wine'] ) ? $product['wine'] : array();

		return array(
			'title'    => isset( $product['title'] ) ? self::clean_title( $product['title'] ) : '',
			'subtitle' => isset( $product['subTitle'] ) ? (string) $product['subTitle'] : '',
			// 'teaser' is Commerce7's own short listing-page description
			// field (distinct from 'content', the longer product-detail-
			// page copy) — confirmed against Commerce7's product docs.
			'teaser'   => isset( $product['teaser'] ) ? wp_strip_all_tags( $product['teaser'] ) : '',
			'slug'     => isset( $product['slug'] ) ? $product['slug'] : '',
			'image'    => ! empty( $product['image'] ) ? $product['image'] : '',
			'price'    => isset( $variant['price'] ) ? (int) $variant['price'] : 0, // cents
			'type'     => isset( $wine['type'] ) ? (string) $wine['type'] : '',
			'varietal' => isset( $wine['varietal'] ) ? (string) $wine['varietal'] : '',
			'vintage'  => ! empty( $wine['vintage'] ) ? (int) $wine['vintage'] : 0, // 0 = Non-Vintage
		);
	}

	/**
	 * Returns the requested product slug on a Commerce7 /product/{slug}
	 * route, or '' everywhere else. $wp->request is the raw matched
	 * request path — WordPress's rewrite rule for this route doesn't
	 * capture the slug into a query var; Commerce7's JS is meant to read
	 * it client-side. Shared by core/templates/page-c7-content.php
	 * (Related Wines) and the deferred Seo module so both agree on
	 * exactly the same route detection.
	 */
	public static function get_product_slug_from_request(): string {
		global $wp;
		$request = isset( $wp->request ) ? trim( $wp->request, '/' ) : '';
		if ( strpos( $request, 'product/' ) !== 0 ) return '';
		return trim( substr( $request, strlen( 'product/' ) ), '/' );
	}

	/**
	 * Finds one wine by slug from the already-cached full catalog rather
	 * than a fresh REST call filtered by slug — Commerce7's API has
	 * previously rejected filter params that seemed reasonable (see
	 * fetch_all_wines()'s docblock), so this reuses the one confirmed-
	 * working fetch pattern. Returns null if REST isn't configured, the
	 * fetch fails, or no wine matches — callers all treat null as "show/
	 * print nothing", never a placeholder.
	 */
	public static function find_wine_by_slug( Config $config, string $slug ) {
		if ( ! $slug || ! Integration::rest_ready( $config ) ) return null;
		$wines = self::fetch_all_wines( $config );
		if ( is_wp_error( $wines ) ) return null;
		foreach ( $wines as $product ) {
			if ( isset( $product['slug'] ) && $product['slug'] === $slug ) {
				return self::shape_wine( $product );
			}
		}
		return null;
	}

	/**
	 * The flagship-wine spotlight — same fallback-chain logic every
	 * original theme hardcoded on its own homepage (front-page.php's
	 * "FLAGSHIP WINE" section): a Commerce7 product slug drives the
	 * photo/heading/note by default, but each of the three has its own
	 * independent manual override, so an admin can fix just the heading
	 * (say) while the photo and note keep coming from the live product.
	 * Genuinely live product data (needs the optional App ID/Secret Key
	 * under Setup, same as render_wines()/render_club_teaser()) — not
	 * just a themed link, since the real "Add to Cart" button below
	 * needs a real slug to attach to regardless.
	 *
	 * Unlike the homepage's own hardcoded copy of this section (which
	 * can assume an admin has already configured things, since it's
	 * built into the theme), this is a general-purpose Elementor widget
	 * a buyer might drop onto ANY page with nothing configured yet —
	 * never renders an empty card shell with no photo/heading/note at
	 * all; falls back to an admin-only config notice instead, same
	 * pattern as every other Commerce7 widget in this file.
	 */
	public static function render_flagship_wine( Config $config, array $atts = array() ): string {
		$slug = isset( $atts['slug'] ) ? trim( $atts['slug'] ) : '';
		$wine = $slug ? self::find_wine_by_slug( $config, $slug ) : null;

		$image       = isset( $atts['image'] ) ? trim( $atts['image'] ) : '';
		$is_c7_photo = false;
		if ( ! $image && $wine && ! empty( $wine['image'] ) ) {
			$image       = $wine['image'];
			$is_c7_photo = true;
		}

		$heading = isset( $atts['heading'] ) ? trim( $atts['heading'] ) : '';
		if ( ! $heading && $wine && ! empty( $wine['title'] ) ) {
			$heading = $wine['title'];
		}

		$note = isset( $atts['note'] ) ? trim( $atts['note'] ) : '';
		if ( ! $note && $wine ) {
			if ( ! empty( $wine['teaser'] ) ) {
				$note = $wine['teaser'];
			} elseif ( ! empty( $wine['subtitle'] ) ) {
				$note = $wine['subtitle'];
			}
		}

		if ( ! $heading && ! $image && ! $note ) {
			return Integration::config_notice( $config, __( 'Flagship Wine: set a Commerce7 product slug (needs the optional App ID/Secret Key under Setup), or fill in the heading/photo/note overrides by hand, to show this section.', $config->text_domain() ) );
		}

		$eyebrow = isset( $atts['eyebrow'] ) ? trim( $atts['eyebrow'] ) : '';
		// Only clickable with a real slug — a heading/photo from a manual
		// override alone has no product page to link to.
		$url = $slug ? home_url( '/product/' . $slug ) : '';

		ob_start();
		?>
		<section class="fw-section">
			<div class="fw-container">
				<div class="fw-split">
					<?php /* Same cover-vs-contain distinction as the original:
					   Commerce7's own product photo is a bottle cutout on a
					   plain background, not the wide still-life shot this
					   box's default background-size:cover assumes — cover
					   crops a bottle photo into an unrecognizable close-up. */ ?>
					<?php if ( $url ) : ?><a href="<?php echo esc_url( $url ); ?>" tabindex="-1" aria-hidden="true"><?php endif; ?>
					<div class="fw-split-media ph-img ph-wine<?php echo $is_c7_photo ? ' fw-split-media--product-photo' : ''; ?>" <?php if ( $image ) : ?>style="background-image:url('<?php echo esc_url( $image ); ?>');"<?php endif; ?>>
						<?php if ( ! $image ) : ?><span class="ph-tag">wine-bottle-still-life — 500x700</span><?php endif; ?>
					</div>
					<?php if ( $url ) : ?></a><?php endif; ?>
					<div class="fw-split-text">
						<?php if ( $eyebrow ) : ?><span class="fw-eyebrow"><?php echo esc_html( $eyebrow ); ?></span><?php endif; ?>
						<h2><?php if ( $url && $heading ) : ?><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $heading ); ?></a><?php else : ?><?php echo esc_html( $heading ); ?><?php endif; ?></h2>
						<?php if ( $note ) : ?><p><?php echo esc_html( $note ); ?></p><?php endif; ?>
						<?php if ( $slug ) : ?><?php echo do_shortcode( '[c7_buy slug="' . esc_attr( $slug ) . '"]' ); ?><?php endif; ?>
					</div>
				</div>
			</div>
		</section>
		<?php
		return ob_get_clean();
	}

	/**
	 * Optional REST teaser — hand-styled cards using the theme's own
	 * card design instead of Commerce7's widget styling. Each card still
	 * uses a real Commerce7 buy widget, so "Add to Cart" stays fully
	 * functional, not just a link.
	 */
	public static function render_wines( Config $config, array $atts = array() ): string {
		$atts = shortcode_atts( array( 'limit' => 8 ), $atts );

		if ( ! Integration::rest_ready( $config ) ) {
			return Integration::config_notice( $config, __( 'Featured Wines teaser is waiting on Commerce7 App ID/Secret Key (optional — only needed for this widget).', $config->text_domain() ) );
		}

		// Deliberately NO 'availability'/'type' query params — see class
		// docblock. Filtering happens client-side below instead.
		$data = self::request( $config, 'product', array( 'limit' => 50 ) );

		if ( is_wp_error( $data ) ) {
			return Integration::config_notice( $config, sprintf( __( 'Could not load wines from Commerce7: %s', $config->text_domain() ), $data->get_error_message() ) );
		}
		if ( empty( $data['products'] ) ) {
			return Integration::config_notice( $config, __( 'Commerce7 returned zero products.', $config->text_domain() ) );
		}

		$available_wines = array();
		foreach ( $data['products'] as $product ) {
			$is_wine      = isset( $product['type'] ) && $product['type'] === 'Wine';
			$is_available = isset( $product['webStatus'] ) && $product['webStatus'] === 'Available';
			$slug         = isset( $product['slug'] ) ? $product['slug'] : '';
			if ( $is_wine && $is_available && $slug ) {
				$available_wines[ $slug ] = $product;
			}
		}

		// Wines marked "Featured" get bubbled to the front and visually
		// tagged — same "feature-first, not featured-only" logic as the
		// full Wines Catalog, so this teaser shows an actual assortment
		// (not just whatever the API returns first) while still
		// surfacing curated picks.
		$featured_slugs = self::featured_wine_slugs( $config );
		$all_wines      = array_values( $available_wines );
		if ( ! empty( $featured_slugs ) ) {
			usort( $all_wines, function ( $a, $b ) use ( $featured_slugs ) {
				$a_featured = in_array( $a['slug'], $featured_slugs, true );
				$b_featured = in_array( $b['slug'], $featured_slugs, true );
				return (int) $b_featured - (int) $a_featured;
			} );
		}
		$wines = array_slice( $all_wines, 0, (int) $atts['limit'] );

		if ( empty( $wines ) ) {
			return Integration::config_notice( $config, __( 'Commerce7 returned zero available wine products (checked product.type === "Wine" and product.webStatus === "Available").', $config->text_domain() ) );
		}

		$wine_badges = self::wine_badges( $config );

		ob_start();
		echo '<div class="fw-wine-grid">';
		foreach ( $wines as $product ) {
			$title       = isset( $product['title'] ) ? self::clean_title( $product['title'] ) : '';
			$subtitle    = isset( $product['subTitle'] ) ? $product['subTitle'] : '';
			$teaser      = isset( $product['teaser'] ) ? wp_strip_all_tags( $product['teaser'] ) : '';
			$slug        = isset( $product['slug'] ) ? $product['slug'] : '';
			$image       = ! empty( $product['image'] ) ? $product['image'] : '';
			$is_featured = in_array( $slug, $featured_slugs, true );
			$badge       = isset( $wine_badges[ $slug ] ) ? $wine_badges[ $slug ] : '';
			$product_url = $slug ? home_url( '/product/' . $slug ) : '';
			?>
			<div class="fw-wine-card<?php echo $is_featured ? ' fw-wine-card--featured' : ''; ?>">
				<div class="fw-wine-img-wrap">
					<?php if ( $image ) : ?>
						<a href="<?php echo esc_url( $product_url ); ?>" tabindex="-1" aria-hidden="true"><img class="fw-wine-img" src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $title ); ?>" loading="lazy"></a>
					<?php else : ?>
						<div class="ph-img ph-wine fw-wine-img"><span class="ph-tag">wine-bottle-still-life — 500x700</span></div>
					<?php endif; ?>
					<?php if ( $badge ) : ?><span class="fw-wine-scarcity-badge"><?php echo esc_html( $badge ); ?></span><?php endif; ?>
				</div>
				<div class="fw-wine-card-body">
					<?php if ( $is_featured ) : ?><span class="fw-wine-featured-tag"><?php esc_html_e( 'Featured', $config->text_domain() ); ?></span><?php endif; ?>
					<h3><a href="<?php echo esc_url( $product_url ); ?>"><?php echo esc_html( $title ); ?></a></h3>
					<?php if ( $subtitle ) : ?><div class="fw-wine-subtitle"><?php echo esc_html( $subtitle ); ?></div><?php endif; ?>
					<?php if ( $teaser ) : ?><p class="fw-wine-teaser"><?php echo esc_html( wp_trim_words( $teaser, 28 ) ); ?></p><?php endif; ?>
					<?php if ( $slug ) : ?>
						<div class="c7-buy-product" data-product-slug="<?php echo esc_attr( $slug ); ?>"></div>
					<?php endif; ?>
				</div>
			</div>
			<?php
		}
		echo '</div>';
		return ob_get_clean();
	}

	/**
	 * Filterable/sortable Wines Catalog — fetches the FULL available
	 * wine catalog (not just a small teaser) so client-side filter-by-
	 * type/varietal and sort-by-price/vintage can run entirely in the
	 * browser, with no server round-trip per interaction. Real Commerce7
	 * add-to-cart on every card either way — this only changes how
	 * browsing/discovery works, never how checkout works.
	 */
	public static function render_wines_catalog( Config $config ): string {
		if ( ! Integration::rest_ready( $config ) ) {
			return Integration::config_notice( $config, __( 'The filterable Wines Catalog needs Commerce7 App ID/Secret Key — it reads the full product list via the REST API so filtering/sorting can run instantly in the browser. Add-to-cart still uses the real Commerce7 cart either way, same as the other teaser widgets.', $config->text_domain() ) );
		}

		$products = self::fetch_all_wines( $config );
		if ( is_wp_error( $products ) ) {
			return Integration::config_notice( $config, sprintf( __( 'Could not load wines from Commerce7: %s', $config->text_domain() ), $products->get_error_message() ) );
		}
		if ( empty( $products ) ) {
			return Integration::config_notice( $config, __( 'Commerce7 returned zero available products of type "Wine" — check that your products are tagged that way and marked Available in Commerce7 admin.', $config->text_domain() ) );
		}

		$items = array_map( array( __CLASS__, 'shape_wine' ), $products );

		$featured_slugs = self::featured_wine_slugs( $config );
		if ( ! empty( $featured_slugs ) ) {
			foreach ( $items as &$item ) {
				$item['featured'] = in_array( $item['slug'], $featured_slugs, true );
			}
			unset( $item );
			usort( $items, function ( $a, $b ) {
				return (int) $b['featured'] - (int) $a['featured'];
			} );
		} else {
			foreach ( $items as &$item ) {
				$item['featured'] = false;
			}
			unset( $item );
		}

		$wine_badges = self::wine_badges( $config );
		foreach ( $items as &$item ) {
			$item['badge'] = isset( $wine_badges[ $item['slug'] ] ) ? $wine_badges[ $item['slug'] ] : '';
		}
		unset( $item );

		$types     = array();
		$varietals = array();
		foreach ( $items as $item ) {
			if ( $item['type'] && ! in_array( $item['type'], $types, true ) ) {
				$types[] = $item['type'];
			}
			if ( $item['varietal'] && ! in_array( $item['varietal'], $varietals, true ) ) {
				$varietals[] = $item['varietal'];
			}
		}
		sort( $types );
		sort( $varietals );

		ob_start();
		?>
		<div class="fw-catalog" data-fw-catalog>
			<div class="fw-catalog-controls">
				<?php if ( ! empty( $types ) ) : ?>
					<div class="fw-catalog-filter" role="group" aria-label="<?php esc_attr_e( 'Filter by type', $config->text_domain() ); ?>">
						<button type="button" class="fw-catalog-pill is-active" data-filter-type=""><?php esc_html_e( 'All', $config->text_domain() ); ?></button>
						<?php foreach ( $types as $type ) : ?>
							<button type="button" class="fw-catalog-pill" data-filter-type="<?php echo esc_attr( $type ); ?>"><?php echo esc_html( $type ); ?></button>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<div class="fw-catalog-controls-right">
					<div class="fw-catalog-search-wrap">
						<svg class="fw-catalog-search-icon" width="15" height="15" viewBox="0 0 16 16" fill="none" aria-hidden="true">
							<circle cx="7" cy="7" r="5.25" stroke="currentColor" stroke-width="1.5"/>
							<line x1="11.25" y1="11.25" x2="14.5" y2="14.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
						</svg>
						<input type="search" class="fw-catalog-search" data-search aria-label="<?php esc_attr_e( 'Search wines', $config->text_domain() ); ?>" placeholder="<?php esc_attr_e( 'Search wines…', $config->text_domain() ); ?>">
					</div>

					<?php if ( ! empty( $varietals ) ) : ?>
						<select class="fw-catalog-select" data-filter-varietal aria-label="<?php esc_attr_e( 'Filter by varietal', $config->text_domain() ); ?>">
							<option value=""><?php esc_html_e( 'All varietals', $config->text_domain() ); ?></option>
							<?php foreach ( $varietals as $varietal ) : ?>
								<option value="<?php echo esc_attr( $varietal ); ?>"><?php echo esc_html( $varietal ); ?></option>
							<?php endforeach; ?>
						</select>
					<?php endif; ?>

					<select class="fw-catalog-select" data-sort aria-label="<?php esc_attr_e( 'Sort wines', $config->text_domain() ); ?>">
						<option value=""><?php esc_html_e( 'Sort by', $config->text_domain() ); ?></option>
						<option value="featured"><?php esc_html_e( 'Featured First', $config->text_domain() ); ?></option>
						<option value="price-asc"><?php esc_html_e( 'Price: Low to High', $config->text_domain() ); ?></option>
						<option value="price-desc"><?php esc_html_e( 'Price: High to Low', $config->text_domain() ); ?></option>
						<option value="vintage-desc"><?php esc_html_e( 'Vintage: Newest First', $config->text_domain() ); ?></option>
						<option value="vintage-asc"><?php esc_html_e( 'Vintage: Oldest First', $config->text_domain() ); ?></option>
						<option value="name-asc"><?php esc_html_e( 'Name: A to Z', $config->text_domain() ); ?></option>
					</select>
				</div>
			</div>

			<p class="fw-catalog-empty" data-fw-catalog-empty hidden><?php esc_html_e( 'No wines match these filters.', $config->text_domain() ); ?></p>

			<div class="fw-wine-grid" data-fw-catalog-grid>
				<?php foreach ( $items as $item ) :
					$meta_bits   = array_filter( array( $item['type'], $item['varietal'], $item['vintage'] ? (string) $item['vintage'] : '' ) );
					$product_url = $item['slug'] ? home_url( '/product/' . $item['slug'] ) : '';
					?>
					<div class="fw-wine-card<?php echo $item['featured'] ? ' fw-wine-card--featured' : ''; ?>"
						data-fw-catalog-item
						data-type="<?php echo esc_attr( $item['type'] ); ?>"
						data-varietal="<?php echo esc_attr( $item['varietal'] ); ?>"
						data-price="<?php echo esc_attr( $item['price'] ); ?>"
						data-vintage="<?php echo esc_attr( $item['vintage'] ); ?>"
						data-featured="<?php echo $item['featured'] ? '1' : '0'; ?>">
						<div class="fw-wine-img-wrap">
							<?php if ( $item['image'] ) : ?>
								<a href="<?php echo esc_url( $product_url ); ?>" tabindex="-1" aria-hidden="true"><img class="fw-wine-img" src="<?php echo esc_url( $item['image'] ); ?>" alt="<?php echo esc_attr( $item['title'] ); ?>" loading="lazy"></a>
							<?php else : ?>
								<div class="ph-img ph-wine fw-wine-img"><span class="ph-tag">wine-bottle-still-life — 500x700</span></div>
							<?php endif; ?>
							<?php if ( $item['badge'] ) : ?><span class="fw-wine-scarcity-badge"><?php echo esc_html( $item['badge'] ); ?></span><?php endif; ?>
						</div>
						<div class="fw-wine-card-body">
							<?php if ( $item['featured'] ) : ?><span class="fw-wine-featured-tag"><?php esc_html_e( 'Featured', $config->text_domain() ); ?></span><?php endif; ?>
							<h3><a href="<?php echo esc_url( $product_url ); ?>"><?php echo esc_html( $item['title'] ); ?></a></h3>
							<?php if ( $item['subtitle'] ) : ?><div class="fw-wine-subtitle"><?php echo esc_html( $item['subtitle'] ); ?></div><?php endif; ?>
							<?php if ( ! empty( $meta_bits ) ) : ?>
								<div class="fw-wine-meta"><?php echo esc_html( implode( ' · ', $meta_bits ) ); ?></div>
							<?php endif; ?>
							<?php if ( $item['teaser'] ) : ?><p class="fw-wine-teaser"><?php echo esc_html( wp_trim_words( $item['teaser'], 28 ) ); ?></p><?php endif; ?>
							<?php if ( $item['slug'] ) : ?>
								<div class="c7-buy-product" data-product-slug="<?php echo esc_attr( $item['slug'] ); ?>"></div>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
		echo Schema::products_jsonld( $items );
		return ob_get_clean();
	}

	/**
	 * "More From {Brand}" rail — same REST wine data/card markup as
	 * render_wines(), filtered to exclude the wine currently being
	 * viewed, laid out as a scroll-snap slider. Called from
	 * core/templates/page-c7-content.php on the product-detail route,
	 * OUTSIDE the #c7-content mount (Commerce7's own JS only renders
	 * what's directly under that ID). Returns '' if REST isn't
	 * configured or nothing's left to show once the current wine is
	 * excluded — never an empty shell.
	 */
	public static function render_related_wines( Config $config, string $exclude_slug, int $limit = 8 ): string {
		if ( ! Integration::rest_ready( $config ) ) return '';

		$wines = self::fetch_all_wines( $config );
		if ( is_wp_error( $wines ) || empty( $wines ) ) return '';

		$items = array();
		foreach ( $wines as $product ) {
			if ( isset( $product['slug'] ) && $product['slug'] === $exclude_slug ) continue;
			$items[] = self::shape_wine( $product );
			if ( count( $items ) >= $limit ) break;
		}
		if ( empty( $items ) ) return '';

		$has_overflow = count( $items ) > 4;

		ob_start();
		?>
		<div class="fw-related-wines">
			<div class="fw-section-head">
				<span class="fw-eyebrow"><?php esc_html_e( 'Keep Exploring', $config->text_domain() ); ?></span>
				<h2><?php printf( esc_html__( 'More From %s', $config->text_domain() ), esc_html( $config->brand_name() ) ); ?></h2>
			</div>
			<div class="fw-wine-slider" data-fw-wine-slider>
				<?php if ( $has_overflow ) : ?>
					<button type="button" class="fw-wine-slider-arrow fw-wine-slider-arrow--prev" data-fw-wine-slider-prev aria-label="<?php esc_attr_e( 'Previous wines', $config->text_domain() ); ?>" disabled>
						<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
					</button>
				<?php endif; ?>

				<div class="fw-wine-slider-track" data-fw-wine-slider-track>
					<?php foreach ( $items as $item ) :
						$product_url = $item['slug'] ? home_url( '/product/' . $item['slug'] ) : '';
						?>
						<div class="fw-wine-slider-item">
							<div class="fw-wine-card fw-wine-card--slider">
								<?php if ( $item['image'] ) : ?>
									<a href="<?php echo esc_url( $product_url ); ?>" tabindex="-1" aria-hidden="true"><img class="fw-wine-img" src="<?php echo esc_url( self::thumbnail_image( $item['image'] ) ); ?>" alt="<?php echo esc_attr( $item['title'] ); ?> bottle" loading="lazy"></a>
								<?php else : ?>
									<div class="ph-img ph-wine fw-wine-img"><span class="ph-tag">wine-bottle-still-life — 500x700</span></div>
								<?php endif; ?>
								<div class="fw-wine-card-body">
									<h3><a href="<?php echo esc_url( $product_url ); ?>"><?php echo esc_html( $item['title'] ); ?></a></h3>
									<?php if ( $item['subtitle'] ) : ?><div class="fw-wine-subtitle"><?php echo esc_html( $item['subtitle'] ); ?></div><?php endif; ?>
									<?php if ( $item['slug'] ) : ?><div class="c7-buy-product" data-product-slug="<?php echo esc_attr( $item['slug'] ); ?>"></div><?php endif; ?>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>

				<?php if ( $has_overflow ) : ?>
					<button type="button" class="fw-wine-slider-arrow fw-wine-slider-arrow--next" data-fw-wine-slider-next aria-label="<?php esc_attr_e( 'Next wines', $config->text_domain() ); ?>">
						<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
					</button>
				<?php endif; ?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	public static function render_club_teaser( Config $config, array $atts = array() ): string {
		$atts = shortcode_atts( array( 'limit' => 6 ), $atts );

		if ( ! Integration::rest_ready( $config ) ) {
			return Integration::config_notice( $config, __( 'Club teaser is waiting on Commerce7 App ID/Secret Key (optional — only needed for this widget).', $config->text_domain() ) );
		}

		// Endpoint is singular ('club'), confirmed against Commerce7's
		// own API docs and live-tested unauthenticated: /v1/clubs
		// returns 404, /v1/club returns 401 (route exists, needs auth) —
		// same singular-path pattern as the product endpoint. Response
		// body's array key stays plural ('clubs').
		$data = self::request( $config, 'club' );

		if ( is_wp_error( $data ) ) {
			return Integration::config_notice( $config, sprintf( __( 'Could not load club plans from Commerce7: %s', $config->text_domain() ), $data->get_error_message() ) );
		}
		if ( empty( $data['clubs'] ) ) {
			return Integration::config_notice( $config, __( 'Commerce7 returned zero club plans.', $config->text_domain() ) );
		}

		ob_start();
		$clubs = array_slice( $data['clubs'], 0, (int) $atts['limit'] );

		echo '<div class="fw-club-grid">';
		foreach ( $clubs as $club ) {
			$title = isset( $club['title'] ) ? $club['title'] : '';
			$slug  = isset( $club['slug'] ) ? $club['slug'] : '';
			$desc  = isset( $club['webDescription'] ) ? wp_strip_all_tags( $club['webDescription'] ) : '';
			?>
			<div class="fw-club-card">
				<span class="fw-eyebrow"><?php echo esc_html( $config->get( 'club.eyebrow', $config->brand_name() . ' ' . __( 'Club', $config->text_domain() ) ) ); ?></span>
				<h3><?php echo esc_html( $title ); ?></h3>
				<?php if ( $desc ) : ?><p><?php echo esc_html( wp_trim_words( $desc, 28 ) ); ?></p><?php endif; ?>
				<?php if ( $slug ) : ?>
					<div class="c7-club-join-button" data-club-slug="<?php echo esc_attr( $slug ); ?>" data-join-text="<?php esc_attr_e( 'Join the Club', $config->text_domain() ); ?>" data-edit-text="<?php esc_attr_e( 'Manage Membership', $config->text_domain() ); ?>"></div>
				<?php endif; ?>
			</div>
			<?php
		}
		echo '</div>';
		return ob_get_clean();
	}
}
