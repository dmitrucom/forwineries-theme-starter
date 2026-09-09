<?php
// GENERATED FILE — do not edit directly. Synced from forwineries-theme-core.
// Edit the source there and re-run tools/sync-core.sh.
namespace ForWineries\Core\Commerce7;

use ForWineries\Core\Config;
use ForWineries\Core\SettingsPage;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Commerce7 integration — built around Commerce7's own v2 storefront
 * (commerce7.js / commerce7.css), their officially supported way to
 * wire a real cart, checkout, product buy-buttons, collections, and
 * club sign-up into an external site. See:
 *   https://design-docs.commerce7.com/docs/home  (Quick Start / widgets)
 *   https://design-docs.commerce7.com/docs/wordpress-1
 *
 * Only a Tenant ID is required to light up the real shop/cart/checkout
 * — that ID is public (it's just the account slug) and safe to print
 * into the page. An App ID + Secret Key are OPTIONAL and only used for
 * the read-only REST teaser shortcodes (Catalog.php) — never exposed to
 * the browser, per Commerce7's own guidance not to put a Secret Key in
 * JavaScript.
 *
 * Credentials can be set on this class's "Setup" settings tab, or as
 * wp-config.php constants (which always win) for teams that don't want
 * secrets in the DB:
 *
 *   define( 'COMMERCE7_TENANT_ID', 'your-tenant-id' );
 *   define( 'COMMERCE7_APP_ID', 'your-app-id' );          // optional
 *   define( 'COMMERCE7_SECRET_KEY', 'your-secret-key' );  // optional
 *
 * This class owns credentials, the CSS-variable brand bridge, the JS
 * loader, the "Setup" admin tab, and the simple render functions/
 * shortcodes that don't need REST data (collection/buy/club-join/
 * subscribe/account/cart-trigger). Routing.php owns page/rewrite setup;
 * Catalog.php owns everything that reads Commerce7's REST API;
 * ReservationWidget.php owns the live booking-calendar widget.
 */
class Integration {

	public static function credential( Config $config, string $key ): string {
		$map = array(
			'tenant_id'  => 'COMMERCE7_TENANT_ID',
			'app_id'     => 'COMMERCE7_APP_ID',
			'secret_key' => 'COMMERCE7_SECRET_KEY',
		);
		if ( isset( $map[ $key ] ) && defined( $map[ $key ] ) ) {
			return (string) constant( $map[ $key ] );
		}
		return get_option( $config->option_key( 'c7_' . $key ), '' );
	}

	public static function widgets_ready( Config $config ): bool {
		return (bool) self::credential( $config, 'tenant_id' );
	}

	public static function rest_ready( Config $config ): bool {
		return self::widgets_ready( $config ) && self::credential( $config, 'app_id' ) && self::credential( $config, 'secret_key' );
	}

	public static function config_notice( Config $config, string $message ): string {
		if ( ! current_user_can( 'manage_options' ) ) return '';
		return '<div class="' . esc_attr( $config->css( 'c7-notice' ) ) . '">' . esc_html( $message ) . ' ' .
			'<a href="' . esc_url( admin_url( 'admin.php?page=' . $config->slug() . '-settings&tab=setup' ) ) . '">' . esc_html__( 'Configure Commerce7 →', $config->text_domain() ) . '</a></div>';
	}

	public static function register( Config $config ): void {
		self::register_storefront_assets( $config );
		self::register_settings( $config );
		self::register_shortcodes( $config );
		self::register_1px( $config );
	}

	/**
	 * Loads commerce7.css/js on every front-end page (the cart + account
	 * widgets live in the header/footer of every page, not just shop
	 * pages) and bridges the theme's own brand tokens into Commerce7's
	 * --c7-* CSS variables.
	 */
	private static function register_storefront_assets( Config $config ): void {
		add_action( 'wp_enqueue_scripts', function () use ( $config ) {
			if ( ! self::widgets_ready( $config ) ) return;
			// Handle is deliberately named "commerce7" (not e.g.
			// "commerce7-css") — branding/QA plugins check wp_style_is()
			// against a known list of handles (commerce7, c7wp, c7-base,
			// c7css, wp-commerce7, c7dbt-google-fonts) to order their own
			// inline overrides after Commerce7's stylesheet. Renaming
			// this handle would silently break that dependency detection.
			wp_enqueue_style( 'commerce7', 'https://cdn.commerce7.com/v2/commerce7.css', array(), null );

			wp_enqueue_style(
				'fw-commerce7-overrides',
				get_stylesheet_directory_uri() . '/core/assets/css/commerce7-overrides.css',
				array( 'commerce7' ),
				null
			);

			// commerce7.css declares its own :root factory defaults
			// (blue buttons, gray fields), which load AFTER the theme's
			// own tokens and would otherwise win the cascade (CSS custom
			// properties resolve to whichever same-specificity
			// declaration appears LAST). Attaching the brand override as
			// an inline stylesheet on the "commerce7" handle guarantees
			// it prints immediately after commerce7.css's own :root
			// block, so the cascade order is: Commerce7 factory defaults
			// → brand → any branding plugin's own later-priority output.
			$vars = apply_filters( $config->slug() . '_c7_brand_vars', array(
				'--c7-primary-color'             => 'var(--fw-clay)',
				'--c7-primary-color-dark'        => 'var(--fw-clay-dark)',
				'--c7-primary-color-focus'       => 'rgba(92, 26, 36, 0.18)',
				'--c7-primary-button-bg'         => 'var(--fw-clay)',
				'--c7-primary-button-bg-hover'   => 'var(--fw-clay-dark)',
				'--c7-primary-button-text-color' => 'var(--fw-cream)',
				'--c7-primary-button-text-color-hover' => 'var(--fw-cream)',
				'--c7-alt-button-bg'             => 'var(--fw-cream)',
				'--c7-alt-button-bg-hover'       => 'var(--fw-cream-raised)',
				'--c7-alt-button-text-color'     => 'var(--fw-clay-dark)',
				'--c7-alt-button-text-color-hover' => 'var(--fw-clay-dark)',
				'--c7-body-text-color'           => 'var(--fw-charcoal)',
				'--c7-text-color'                => 'var(--fw-charcoal)',
				'--c7-alt-text-color'            => 'var(--fw-charcoal-soft)',
				'--c7-link-color'                => 'var(--fw-clay)',
				'--c7-link-color-hover'          => 'var(--fw-clay-dark)',
				'--c7-header-text-color'         => 'var(--fw-clay-dark)',
				'--c7-heading-text-color'        => 'var(--fw-clay-dark)',
				'--c7-heading-font-family'       => 'var(--fw-font-heading)',
				'--c7-font-family'               => 'var(--fw-font-body)',
				'--c7-font-size'                 => '17px',
				'--c7-button-border-radius'      => 'var(--fw-radius)',
				'--c7-field-border-radius'       => 'var(--fw-radius)',
				'--c7-field-bg'                  => 'var(--fw-cream)',
				'--c7-field-border-color'        => 'rgba(43, 36, 32, 0.2)',
				'--c7-cart-count-bg'             => 'var(--fw-clay)',
				'--c7-cart-count-text-color'     => 'var(--fw-cream)',
				// commerce7.css ships its own 'body { background: var(--c7-bg) }'
				// rule with a #fff factory default, and that stylesheet loads
				// AFTER the theme's own — same specificity, later source order
				// wins — so leaving --c7-bg unmapped silently washes the whole
				// page background to white the moment Commerce7 widgets are
				// configured. Invisible on the 4 light/cream themes (their own
				// --fw-cream is already white-adjacent), but fully exposed on
				// Vespera's dark palette: confirmed live, body rendering solid
				// white instead of --fw-cream (#131016) once a real Tenant ID
				// was configured. Mapped here, not per-theme, since every
				// theme benefits from --c7-bg agreeing with its own page
				// background regardless of how close that already looks.
				'--c7-bg'                        => 'var(--fw-cream)',
				'--c7-bg-alt'                    => 'var(--fw-cream)',
				'--c7-block-bg'                  => 'var(--fw-cream)',
				'--c7-block-border-color'        => 'rgba(43, 36, 32, 0.08)',
			) );

			$css = ":root {\n";
			foreach ( $vars as $prop => $value ) {
				$css .= "\t" . $prop . ': ' . $value . ";\n";
			}
			$css .= "}\n";

			wp_add_inline_style( 'commerce7', $css );
		} );

		add_action( 'wp_footer', function () use ( $config ) {
			if ( ! self::widgets_ready( $config ) ) {
				if ( current_user_can( 'manage_options' ) ) {
					printf(
						"\n<!-- %s: Commerce7 Tenant ID isn't set yet — configure it under %s → Setup to turn on the shop, cart, and club widgets. -->\n",
						esc_html( $config->brand_name() ),
						esc_html( $config->brand_name() )
					);
				}
				return;
			}
			// Printed as a raw tag (not via wp_enqueue_script) so its
			// exact markup — including the id Commerce7 asks integrators
			// to leave uncached — is never altered by a minify/combine
			// plugin. A page-cache or asset-optimization plugin should
			// exclude the script with id="c7-javascript" from
			// caching/minification.
			printf(
				"\n<script type=\"text/javascript\" src=\"https://cdn.commerce7.com/v2/commerce7.js\" id=\"c7-javascript\" data-tenant=\"%s\"></script>\n",
				esc_attr( self::credential( $config, 'tenant_id' ) )
			);
		}, 20 );
	}

	private static function register_settings( Config $config ): void {
		$settings_group = $config->option_key( 'c7_settings' );

		add_action( 'admin_init', function () use ( $config, $settings_group ) {
			register_setting( $settings_group, $config->option_key( 'c7_tenant_id' ), 'sanitize_text_field' );
			register_setting( $settings_group, $config->option_key( 'c7_app_id' ), 'sanitize_text_field' );
			register_setting( $settings_group, $config->option_key( 'c7_secret_key' ), 'sanitize_text_field' );
			register_setting( $settings_group, $config->option_key( 'c7_default_collection_slug' ), 'sanitize_title' );
			register_setting( $settings_group, $config->option_key( 'c7_default_club_slug' ), 'sanitize_title' );
			register_setting( $settings_group, $config->option_key( 'c7_gift_card_collection_slug' ), 'sanitize_title' );
			register_setting( $settings_group, $config->option_key( 'c7_reservation_url' ), 'sanitize_text_field' );
			register_setting( $settings_group, $config->option_key( 'c7_reservation_type_slug' ), 'sanitize_title' );
			register_setting( $settings_group, $config->option_key( 'c7_featured_wine_slugs' ), 'sanitize_text_field' );
			register_setting( $settings_group, $config->option_key( 'c7_wine_badges' ), 'sanitize_textarea_field' );
		} );

		add_action( 'admin_post_fw_c7_flush_rewrites', function () use ( $config ) {
			if ( ! current_user_can( 'manage_options' ) ) wp_die();
			check_admin_referer( 'fw_c7_flush_rewrites' );
			Routing::create_pages( $config );
			Routing::add_rewrite_rules();
			flush_rewrite_rules();
			wp_safe_redirect( admin_url( 'admin.php?page=' . $config->slug() . '-settings&tab=setup&fw_c7_flushed=1' ) );
			exit;
		} );

		add_action( 'admin_post_fw_c7_clear_cache', function () use ( $config ) {
			if ( ! current_user_can( 'manage_options' ) ) wp_die();
			check_admin_referer( 'fw_c7_clear_cache' );
			Catalog::clear_cache( $config );
			wp_safe_redirect( admin_url( 'admin.php?page=' . $config->slug() . '-settings&tab=setup&fw_c7_cache_cleared=1' ) );
			exit;
		} );

		SettingsPage::add_tab( 'setup', __( 'Setup', $config->text_domain() ), function () use ( $config ) {
			self::render_settings_tab( $config );
		}, 10 );
	}

	private static function render_settings_tab( Config $config ): void {
		if ( ! current_user_can( 'manage_options' ) ) return;

		$constants_locked = defined( 'COMMERCE7_TENANT_ID' ) || defined( 'COMMERCE7_APP_ID' ) || defined( 'COMMERCE7_SECRET_KEY' );
		$td               = $config->text_domain();
		?>
			<?php if ( isset( $_GET['fw_c7_flushed'] ) ) : ?>
				<div class="notice notice-success"><p><?php esc_html_e( 'Commerce7 pages and URL rules refreshed.', $td ); ?></p></div>
			<?php endif; ?>
			<?php if ( isset( $_GET['fw_c7_cache_cleared'] ) ) : ?>
				<div class="notice notice-success"><p><?php esc_html_e( 'Commerce7 teaser cache cleared.', $td ); ?></p></div>
			<?php endif; ?>
			<?php if ( $constants_locked ) : ?>
				<div class="notice notice-info"><p><?php esc_html_e( 'One or more Commerce7 credentials are defined in wp-config.php and override the fields below.', $td ); ?></p></div>
			<?php endif; ?>

			<form method="post" action="options.php">
				<?php settings_fields( $config->option_key( 'c7_settings' ) ); ?>
				<input type="hidden" name="_wp_http_referer" value="<?php echo esc_url( admin_url( 'admin.php?page=' . $config->slug() . '-settings&tab=setup' ) ); ?>">
				<h2><?php esc_html_e( 'Storefront (required)', $td ); ?></h2>
				<p class="description"><?php esc_html_e( "This is all that's needed to turn on the real Commerce7 shop, cart, checkout, and club sign-up widgets.", $td ); ?></p>
				<table class="form-table" role="presentation">
					<tr>
						<th><label for="<?php echo esc_attr( $config->option_key( 'c7_tenant_id' ) ); ?>"><?php esc_html_e( 'Tenant ID', $td ); ?></label></th>
						<td>
							<input type="text" id="<?php echo esc_attr( $config->option_key( 'c7_tenant_id' ) ); ?>" name="<?php echo esc_attr( $config->option_key( 'c7_tenant_id' ) ); ?>" class="regular-text" value="<?php echo esc_attr( get_option( $config->option_key( 'c7_tenant_id' ) ) ); ?>" <?php disabled( defined( 'COMMERCE7_TENANT_ID' ) ); ?>>
							<p class="description"><?php esc_html_e( 'The first part of your Commerce7 admin URL, e.g. "example" from example.admin.platform.commerce7.com', $td ); ?></p>
						</td>
					</tr>
					<tr>
						<th><label for="<?php echo esc_attr( $config->option_key( 'c7_default_collection_slug' ) ); ?>"><?php esc_html_e( 'Default collection slug', $td ); ?></label></th>
						<td><input type="text" id="<?php echo esc_attr( $config->option_key( 'c7_default_collection_slug' ) ); ?>" name="<?php echo esc_attr( $config->option_key( 'c7_default_collection_slug' ) ); ?>" class="regular-text" value="<?php echo esc_attr( get_option( $config->option_key( 'c7_default_collection_slug' ) ) ); ?>" placeholder="core-collection"></td>
					</tr>
					<tr>
						<th><label for="<?php echo esc_attr( $config->option_key( 'c7_default_club_slug' ) ); ?>"><?php esc_html_e( 'Default wine club slug', $td ); ?></label></th>
						<td><input type="text" id="<?php echo esc_attr( $config->option_key( 'c7_default_club_slug' ) ); ?>" name="<?php echo esc_attr( $config->option_key( 'c7_default_club_slug' ) ); ?>" class="regular-text" value="<?php echo esc_attr( get_option( $config->option_key( 'c7_default_club_slug' ) ) ); ?>"></td>
					</tr>
					<tr>
						<th><label for="<?php echo esc_attr( $config->option_key( 'c7_gift_card_collection_slug' ) ); ?>"><?php esc_html_e( 'Gift card collection slug', $td ); ?></label></th>
						<td>
							<input type="text" id="<?php echo esc_attr( $config->option_key( 'c7_gift_card_collection_slug' ) ); ?>" name="<?php echo esc_attr( $config->option_key( 'c7_gift_card_collection_slug' ) ); ?>" class="regular-text" value="<?php echo esc_attr( get_option( $config->option_key( 'c7_gift_card_collection_slug' ) ) ); ?>" placeholder="gift-cards">
							<p class="description"><?php esc_html_e( 'Powers the /gift-cards page. Commerce7 has no separate gift-card widget — create a Gift Card product under Store > Gift Cards in Commerce7 admin, add it to a collection, and enter that collection\'s slug here.', $td ); ?></p>
						</td>
					</tr>
					<tr>
						<th><label for="<?php echo esc_attr( $config->option_key( 'c7_reservation_url' ) ); ?>"><?php esc_html_e( 'Reservations link', $td ); ?></label></th>
						<td>
							<input type="text" id="<?php echo esc_attr( $config->option_key( 'c7_reservation_url' ) ); ?>" name="<?php echo esc_attr( $config->option_key( 'c7_reservation_url' ) ); ?>" class="regular-text" value="<?php echo esc_attr( get_option( $config->option_key( 'c7_reservation_url' ), home_url( '/reservation' ) ) ); ?>">
							<p class="description"><?php esc_html_e( "Defaults to the auto-created /reservation page (Commerce7's native reservation widget). Point this at an external URL instead if you book tastings through another platform.", $td ); ?></p>
						</td>
					</tr>
					<tr>
						<th><label for="<?php echo esc_attr( $config->option_key( 'c7_reservation_type_slug' ) ); ?>"><?php esc_html_e( 'Reservation type slug (optional)', $td ); ?></label></th>
						<td>
							<input type="text" id="<?php echo esc_attr( $config->option_key( 'c7_reservation_type_slug' ) ); ?>" name="<?php echo esc_attr( $config->option_key( 'c7_reservation_type_slug' ) ); ?>" class="regular-text" value="<?php echo esc_attr( get_option( $config->option_key( 'c7_reservation_type_slug' ) ) ); ?>">
							<p class="description"><?php esc_html_e( "Leave blank to just link to Commerce7's own /reservation page (the default). Set this to embed a live booking calendar directly on the Reservations page and the homepage instead — find the slug under Commerce7 Admin > Reservations > Reservation Types.", $td ); ?></p>
						</td>
					</tr>
				</table>

				<h2><?php esc_html_e( 'REST teaser shortcodes (optional)', $td ); ?></h2>
				<p class="description"><?php esc_html_e( 'Only needed if you use [c7_wines] / [c7_club] to show a custom-styled "Featured Wines" teaser (e.g. on the homepage) using the theme\'s own card design instead of Commerce7\'s widget styling. Create an App under Commerce7 Admin → Setup → Apps with read access to Products and Clubs.', $td ); ?></p>
				<table class="form-table" role="presentation">
					<tr>
						<th><label for="<?php echo esc_attr( $config->option_key( 'c7_app_id' ) ); ?>"><?php esc_html_e( 'App ID', $td ); ?></label></th>
						<td><input type="text" id="<?php echo esc_attr( $config->option_key( 'c7_app_id' ) ); ?>" name="<?php echo esc_attr( $config->option_key( 'c7_app_id' ) ); ?>" class="regular-text" value="<?php echo esc_attr( get_option( $config->option_key( 'c7_app_id' ) ) ); ?>" <?php disabled( defined( 'COMMERCE7_APP_ID' ) ); ?>></td>
					</tr>
					<tr>
						<th><label for="<?php echo esc_attr( $config->option_key( 'c7_featured_wine_slugs' ) ); ?>"><?php esc_html_e( 'Featured wine slugs', $td ); ?></label></th>
						<td>
							<input type="text" id="<?php echo esc_attr( $config->option_key( 'c7_featured_wine_slugs' ) ); ?>" name="<?php echo esc_attr( $config->option_key( 'c7_featured_wine_slugs' ) ); ?>" class="regular-text" value="<?php echo esc_attr( get_option( $config->option_key( 'c7_featured_wine_slugs' ) ) ); ?>" placeholder="reserve-2021, estate-cabernet-2020">
							<p class="description"><?php esc_html_e( 'Comma-separated product slugs — powers which wines the Featured Wines Teaser ([c7_wines]) shows, and which wines get a "Featured" badge in the Wines Catalog. Commerce7 has no built-in "featured" flag, so this is the theme-side equivalent. Leave blank to show every wine the same way.', $td ); ?></p>
						</td>
					</tr>
					<tr>
						<th><label for="<?php echo esc_attr( $config->option_key( 'c7_wine_badges' ) ); ?>"><?php esc_html_e( 'Wine scarcity badges', $td ); ?></label></th>
						<td>
							<textarea id="<?php echo esc_attr( $config->option_key( 'c7_wine_badges' ) ); ?>" name="<?php echo esc_attr( $config->option_key( 'c7_wine_badges' ) ); ?>" class="large-text code" rows="4" placeholder="reserve-2021: Only 12 cases left&#10;estate-cabernet-2020: Last of the vintage"><?php echo esc_textarea( get_option( $config->option_key( 'c7_wine_badges' ) ) ); ?></textarea>
							<p class="description"><?php esc_html_e( "One per line: product-slug: badge text. Shown as a small badge on that wine's card in the Wines Catalog. Manual on purpose — Commerce7 doesn't expose a live \"cases remaining\" count, so this is edited by you, not pulled automatically.", $td ); ?></p>
						</td>
					</tr>
					<tr>
						<th><label for="<?php echo esc_attr( $config->option_key( 'c7_secret_key' ) ); ?>"><?php esc_html_e( 'Secret Key', $td ); ?></label></th>
						<td>
							<input type="password" id="<?php echo esc_attr( $config->option_key( 'c7_secret_key' ) ); ?>" name="<?php echo esc_attr( $config->option_key( 'c7_secret_key' ) ); ?>" class="regular-text" value="<?php echo esc_attr( get_option( $config->option_key( 'c7_secret_key' ) ) ); ?>" autocomplete="new-password" <?php disabled( defined( 'COMMERCE7_SECRET_KEY' ) ); ?>>
							<p class="description"><?php esc_html_e( 'Server-side only — never sent to the browser. Prefer a wp-config.php constant on production sites.', $td ); ?></p>
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>

			<hr>
			<p>
				<a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=fw_c7_flush_rewrites' ), 'fw_c7_flush_rewrites' ) ); ?>">
					<?php esc_html_e( 'Recreate C7 pages & refresh URL rules', $td ); ?>
				</a>
				<a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=fw_c7_clear_cache' ), 'fw_c7_clear_cache' ) ); ?>">
					<?php esc_html_e( 'Clear teaser cache', $td ); ?>
				</a>
			</p>
			<p class="description"><?php esc_html_e( 'Run "Recreate C7 pages" once after activating the theme, and again any time /collection, /product, /cart, /checkout, /profile, /club or /reservation 404 (e.g. after a permalink change).', $td ); ?></p>

			<hr>
			<h2><?php esc_html_e( 'Shortcodes', $td ); ?></h2>
			<h3><?php esc_html_e( 'Official Commerce7 widgets (recommended — real cart & checkout)', $td ); ?></h3>
			<ul>
				<li><code>[c7_collection slug="core-collection"]</code> — <?php esc_html_e( "Commerce7's own product grid for a collection, with live inventory/pricing and working add-to-cart.", $td ); ?></li>
				<li><code>[c7_buy slug="reserve-2021"]</code> — <?php esc_html_e( 'add-to-cart button for one specific wine.', $td ); ?></li>
				<li><code>[c7_club_join slug="the-society"]</code> — <?php esc_html_e( 'real "Join the Club" / "Manage Membership" button.', $td ); ?></li>
				<li><code>[c7_account]</code> — <?php esc_html_e( 'login / account greeting (used automatically in the header).', $td ); ?></li>
				<li><code>[c7_cart_trigger]</code> — <?php esc_html_e( 'opens the real Commerce7 side cart (used automatically in the header).', $td ); ?></li>
			</ul>
			<h3><?php esc_html_e( 'Custom REST teasers (optional, display-only)', $td ); ?></h3>
			<ul>
				<li><code>[c7_wines limit="4"]</code> — <?php esc_html_e( 'a hand-styled "Featured Wines" grid with a real add-to-cart button on each card.', $td ); ?></li>
				<li><code>[c7_wines_catalog]</code> — <?php esc_html_e( 'the full available wine list with filter-by-type, filter-by-varietal, and sort-by-price/vintage controls.', $td ); ?></li>
				<li><code>[c7_club limit="6"]</code> — <?php esc_html_e( 'hand-styled club teaser cards with a real "Join" button on each.', $td ); ?></li>
			</ul>
			<p class="description"><?php esc_html_e( 'Drop any of these into an Elementor page using the Shortcode widget.', $td ); ?></p>
		<?php
	}

	private static function register_shortcodes( Config $config ): void {
		add_shortcode( 'c7_collection', function ( $atts = array() ) use ( $config ) {
			return self::render_collection( $config, (array) $atts );
		} );
		add_shortcode( 'c7_buy', function ( $atts = array() ) use ( $config ) {
			return self::render_buy( $config, (array) $atts );
		} );
		add_shortcode( 'c7_club_join', function ( $atts = array() ) use ( $config ) {
			return self::render_club_join( $config, (array) $atts );
		} );
		add_shortcode( 'c7_subscribe', function () use ( $config ) {
			return self::render_subscribe( $config );
		} );
		add_shortcode( 'c7_account', function () use ( $config ) {
			return self::render_account( $config );
		} );
		add_shortcode( 'c7_cart_trigger', function () use ( $config ) {
			return self::render_cart_trigger( $config );
		} );
	}

	public static function render_collection( Config $config, array $atts = array() ): string {
		$atts = shortcode_atts( array( 'slug' => get_option( $config->option_key( 'c7_default_collection_slug' ), '' ) ), $atts );
		if ( ! self::widgets_ready( $config ) ) return self::config_notice( $config, __( 'Set your Commerce7 Tenant ID to show this collection.', $config->text_domain() ) );
		if ( ! $atts['slug'] ) return self::config_notice( $config, __( 'Add a collection slug — pass slug="..." or set a default under Setup.', $config->text_domain() ) );
		return '<div class="c7-product-collection" data-collection-slug="' . esc_attr( $atts['slug'] ) . '"></div>';
	}

	public static function render_buy( Config $config, array $atts = array() ): string {
		$atts = shortcode_atts( array( 'slug' => '' ), $atts );
		if ( ! self::widgets_ready( $config ) ) return self::config_notice( $config, __( 'Set your Commerce7 Tenant ID to show this button.', $config->text_domain() ) );
		if ( ! $atts['slug'] ) return self::config_notice( $config, __( 'Add a product slug to show its buy button.', $config->text_domain() ) );
		return '<div class="c7-buy-product" data-product-slug="' . esc_attr( $atts['slug'] ) . '"></div>';
	}

	public static function render_club_join( Config $config, array $atts = array() ): string {
		// club.name defaults to "{Brand} Club" but a theme can override it
		// — a wine club's actual public name (e.g. "The Larkhaven
		// Society") is often not just the brand name with "Club" tacked
		// on, and this same button's default text is real, visible
		// homepage copy, not a cosmetic label.
		$club_name = $config->get( 'club.name', sprintf( __( '%s Club', $config->text_domain() ), $config->brand_name() ) );
		$atts      = shortcode_atts( array(
			'slug'      => get_option( $config->option_key( 'c7_default_club_slug' ), '' ),
			'join_text' => sprintf( __( 'Join the %s', $config->text_domain() ), $club_name ),
			'edit_text' => __( 'Manage Your Membership', $config->text_domain() ),
		), $atts );
		if ( ! self::widgets_ready( $config ) ) return self::config_notice( $config, __( 'Set your Commerce7 Tenant ID to show the club button.', $config->text_domain() ) );
		// "test-club" is a placeholder slug that has shipped by mistake
		// in past builds of this theme family — treated as "not
		// configured" rather than ever rendering a join button that 404s.
		if ( ! $atts['slug'] || $atts['slug'] === 'test-club' ) return self::config_notice( $config, __( 'Set a real wine club slug under Setup (or pass a slug) — never "test-club".', $config->text_domain() ) );
		return sprintf(
			'<div class="c7-club-join-button" data-club-slug="%s" data-join-text="%s" data-edit-text="%s"></div>',
			esc_attr( $atts['slug'] ), esc_attr( $atts['join_text'] ), esc_attr( $atts['edit_text'] )
		);
	}

	/**
	 * The real Commerce7 newsletter/marketing-list opt-in widget —
	 * distinct from the wine club: a low-friction "just email me" list,
	 * no commitment. Email-only (no data-has-name-field) — the minimal
	 * single-field pattern real winery footers use.
	 */
	public static function render_subscribe( Config $config ): string {
		if ( ! self::widgets_ready( $config ) ) return self::config_notice( $config, __( 'Set your Commerce7 Tenant ID to show the newsletter signup.', $config->text_domain() ) );
		return '<div class="c7-subscribe"></div>';
	}

	public static function render_account( Config $config ): string {
		static $already_rendered = false;
		if ( ! self::widgets_ready( $config ) ) return '';
		if ( $already_rendered ) {
			return self::config_notice( $config, __( 'The Account widget can only appear once per page — Commerce7 mounts it by a fixed ID, and this page already has one (usually the header, on every page, automatically).', $config->text_domain() ) );
		}
		$already_rendered = true;
		return '<div id="c7-account"></div>';
	}

	public static function render_cart_trigger( Config $config ): string {
		static $already_rendered = false;
		if ( ! self::widgets_ready( $config ) ) {
			return '<span class="' . esc_attr( $config->css( 'cart-link' ) ) . ' ' . esc_attr( $config->css( 'cart-link--disabled' ) ) . '">' . esc_html__( 'Cart', $config->text_domain() ) . '</span>';
		}
		// This IS the real Commerce7 widget (icon + live item count +
		// built-in click-to-open) — not a custom button. There must be
		// exactly ONE #c7-cart in the whole page (duplicate IDs make
		// getElementById unreliable) — this renders automatically in the
		// header, so only add it elsewhere if removed from the header first.
		if ( $already_rendered ) {
			return self::config_notice( $config, __( 'The Cart icon can only appear once per page — Commerce7 mounts it by a fixed ID, and this page already has one (usually the header, on every page, automatically).', $config->text_domain() ) );
		}
		$already_rendered = true;
		return '<div id="c7-cart"></div>';
	}

	/**
	 * 1PX (1Pixel) — Meta Conversions API for Commerce7, auto-enabled.
	 * A real, installable Commerce7 App: a winery installs it once from
	 * their Commerce7 admin and enters Meta Pixel credentials inside
	 * Commerce7's own app frame — nothing to configure here. Safe to
	 * always include: until a winery installs the app, the hosted loader
	 * returns a harmless no-op. Only enqueued when a Tenant ID is set,
	 * same gate every other Commerce7 widget uses.
	 */
	private static function register_1px( Config $config ): void {
		add_action( 'wp_enqueue_scripts', function () use ( $config ) {
			if ( ! self::widgets_ready( $config ) ) return;
			$url = $config->get( 'c7_1px_url', 'https://1pixel-meta-c7.fly.dev/storefront.js' );
			$handle = 'fw-1px-storefront';
			wp_enqueue_script(
				$handle,
				add_query_arg( 'tenantId', rawurlencode( self::credential( $config, 'tenant_id' ) ), $url ),
				array(),
				null,
				true
			);
			wp_script_add_data( $handle, 'strategy', 'async' );
		} );
	}
}
