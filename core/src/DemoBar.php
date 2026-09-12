<?php
// GENERATED FILE — do not edit directly. Synced from forwineries-theme-core.
// Edit the source there and re-run tools/sync-core.sh.
namespace ForWineries\Core;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Shared "winery template demo" bar — NOT part of any theme's brand.
 * Every skin sold from https://forwineries.com/ ships this same
 * behaviour (only $config->get('demo.current') differs), so a shopper
 * touring multiple demos sees one consistent, deliberately-not-branded
 * strip identifying the site as a template, linking back to the product
 * page, and switching between the other demos. Renders above the header
 * AND above the age gate (core/assets/css/demo-bar.css gives it a
 * higher z-index than .fw-age-gate) so a screenshot taken mid-gate
 * still reads as a template demo, not a real winery.
 *
 * Ships HIDDEN by default everywhere except the known forwineries.com
 * demo hosts (self::sites(), matched against HTTP_HOST) — those show it
 * automatically with zero setup, since that's the one place it needs to
 * be on out of the box. A buyer's own install (or a local/staging copy
 * on any other host, e.g. one of the product's own local dev sites)
 * never surfaces a "request this template" bar pointed at someone
 * else's sale page, and nobody has to remember to flip a setting after
 * purchase. The Demo Bar tab's "Hide demo bar" checkbox always overrides
 * this automatic default, in either direction, once explicitly saved.
 *
 * All markup uses $config->css() — the unprefixed 'fw-*' vocabulary —
 * so core/assets/css/demo-bar.css needs zero per-theme changes.
 */
class DemoBar {

	/**
	 * The live forwineries.com demos — shared verbatim (as data) across
	 * every theme so the switcher always offers the same links regardless
	 * of which one is currently being viewed. Confirmed identical across
	 * all 5 original themes before centralizing here.
	 */
	public static function sites(): array {
		return array(
			'Larkhaven'  => 'https://larkhaven.forwineries.com/',
			'Fault Line' => 'https://faultline.forwineries.com/',
			'Vespera'    => 'https://vespera.forwineries.com/',
			'Albariza'   => 'https://albariza.forwineries.com/',
			'Heronrest'  => 'https://heronrest.forwineries.com/',
		);
	}

	public static function register( Config $config ): void {
		add_action( 'wp_enqueue_scripts', function () {
			wp_enqueue_style(
				'fw-demo-bar',
				get_stylesheet_directory_uri() . '/core/assets/css/demo-bar.css',
				array(),
				Config::asset_version( '/core/assets/css/demo-bar.css' )
			);
			wp_enqueue_script(
				'fw-demo-bar',
				get_stylesheet_directory_uri() . '/core/assets/js/demo-bar.js',
				array(),
				Config::asset_version( '/core/assets/js/demo-bar.js' ),
				true
			);
		} );

		add_action( 'wp_head', function () use ( $config ) {
			self::output_demo_robots_meta( $config );
		}, 1 );

		add_action( 'admin_init', function () use ( $config ) {
			// No 'default' here on purpose — is_hidden() supplies its own
			// host-aware default (see its docblock) and always passes an
			// explicit default to get_option(), which register_setting's
			// own default_option_* filter respects unchanged in that case.
			register_setting( $config->option_key( 'demo_bar_settings' ), $config->option_key( 'demo_bar_hidden' ), array(
				'type'              => 'string',
				'sanitize_callback' => function ( $value ) {
					return $value === '1' ? '1' : '0';
				},
			) );
		} );

		add_filter( 'document_title_parts', function ( $parts ) use ( $config ) {
			return self::filter_document_title( $config, $parts );
		} );

		SettingsPage::add_tab( 'demo', __( 'Demo Bar', $config->text_domain() ), function () use ( $config ) {
			self::render_tab_content( $config );
		}, 50 );
	}

	public static function is_hidden( Config $config ): bool {
		$saved = get_option( $config->option_key( 'demo_bar_hidden' ), '' );
		if ( $saved !== '' ) {
			return $saved === '1';
		}
		// No explicit choice saved yet — fall back to the automatic,
		// host-based default described in the class docblock above.
		return ! self::is_known_demo_host();
	}

	public static function is_active( Config $config ): bool {
		return ! self::is_hidden( $config );
	}

	/**
	 * True only on forwineries.com's own live demo catalog (self::sites()'
	 * hostnames) — the one place the bar should show with no admin having
	 * ever touched the setting. Matches on HTTP_HOST alone (not scheme),
	 * so it still works correctly whether the request came in over http
	 * during local proxying or https in production.
	 */
	private static function is_known_demo_host(): bool {
		$host = isset( $_SERVER['HTTP_HOST'] ) ? strtolower( (string) wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
		if ( $host === '' ) return false;
		foreach ( self::sites() as $url ) {
			if ( strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) ) === $host ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * These demo URLs aren't real wineries — keep them out of search so
	 * they don't compete with forwineries.com itself. Tied to the same
	 * flag as the bar: a buyer who hides it after installing on their
	 * own real site stops being noindexed too.
	 */
	private static function output_demo_robots_meta( Config $config ): void {
		if ( ! self::is_active( $config ) ) return;
		echo '<meta name="robots" content="noindex,nofollow" />' . "\n";
	}

	/**
	 * A demo's <title> was its own hostname, because that's what the WP
	 * site name is set to. The shop invites visitors to open all five
	 * demos at once, each in a new tab — unreadable tabs otherwise. Tied
	 * to the demo-bar flag: a buyer who hides the bar gets their own
	 * site's titles back untouched.
	 *
	 * The tagline is a narrower case: this used to strip it
	 * unconditionally, which made Settings → General → Tagline look
	 * broken/hardcoded to anyone editing it while the demo bar happened
	 * to be showing (including on this theme's own local dev copy, once
	 * the demo bar became toggleable anywhere — see DemoBar's class
	 * docblock). Only the un-set default WordPress ships with every
	 * fresh install ("Just another WordPress site", or its localized
	 * equivalent) gets stripped now; a real tagline an admin actually
	 * typed in survives, same as the rest of a normal WordPress site.
	 */
	private static function filter_document_title( Config $config, $parts ) {
		if ( ! self::is_active( $config ) ) return $parts;
		$name = $config->get( 'demo.full_name', $config->brand_name() );

		if ( isset( $parts['title'] ) && get_bloginfo( 'name' ) === $parts['title'] ) {
			$parts['title'] = $name;
			$parts['site']  = __( 'For Wineries demo', $config->text_domain() );
		} else {
			/* translators: %s: theme name. */
			$parts['site'] = sprintf( __( '%s demo', $config->text_domain() ), $name );
		}

		$tagline = get_bloginfo( 'description' );
		if ( $tagline === '' || $tagline === translate( 'Just another WordPress site' ) ) {
			unset( $parts['tagline'] );
		}

		return $parts;
	}

	/**
	 * Where "Request this look" goes. Sends ?type=&template= so the
	 * shop's contact form pre-selects itself, rather than dropping a
	 * high-intent click on forwineries.com's bare homepage.
	 */
	public static function buy_url( Config $config ): string {
		return add_query_arg(
			array(
				'type'     => rawurlencode( 'Theme purchase question' ),
				'template' => rawurlencode( $config->get( 'demo.full_name', $config->brand_name() ) ),
			),
			'https://forwineries.com/contact/'
		);
	}

	/**
	 * Inner content only — no <div class="wrap">/<h1>; that page chrome
	 * is provided once by the (not yet ported) unified settings page
	 * shell, which calls this inside this tab's panel.
	 */
	public static function render_tab_content( Config $config ): void {
		if ( ! current_user_can( 'manage_options' ) ) return;
		?>
			<p class="description">
				<?php esc_html_e( 'Controls the "Winery template demo" strip shown above the header. It shows automatically, with nothing to set up, only on forwineries.com\'s own live demo sites — everywhere else (your own purchased install, a staging copy, a local dev site) it\'s off by default, and it also sits below the WordPress admin toolbar instead of covering it. Use the checkbox to override the automatic default in either direction, e.g. to show it temporarily while giving someone a tour.', $config->text_domain() ); ?>
			</p>
			<form method="post" action="options.php">
				<?php settings_fields( $config->option_key( 'demo_bar_settings' ) ); ?>
				<input type="hidden" name="_wp_http_referer" value="<?php echo esc_url( admin_url( 'admin.php?page=' . $config->slug() . '-settings&tab=demo' ) ); ?>">
				<table class="form-table" role="presentation">
					<tr>
						<th><?php esc_html_e( 'Demo bar', $config->text_domain() ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( $config->option_key( 'demo_bar_hidden' ) ); ?>" value="1" <?php checked( self::is_hidden( $config ) ); ?>>
								<?php esc_html_e( 'Hide demo bar', $config->text_domain() ); ?>
							</label>
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
			<hr>
			<p class="description">
				<?php esc_html_e( 'Preview bypass: a request with ?fw_preview=1 in the URL, or one arriving with a forwineries.com referrer, skips the age gate automatically — useful for catalog screenshots/embeds on the product page without re-entering a birthdate every time. Normal visitors always see the age gate.', $config->text_domain() ); ?>
			</p>
		<?php
	}

	/**
	 * Call from header.php right after the opening <body> markup, above
	 * the header itself. $config->get('demo.price') defaults to the
	 * price every original theme hardcoded identically ('$349/yr') — see
	 * docs/LESSONS.md: this is exactly the kind of value that silently
	 * drifted out of sync across 5 copy-pasted files before, so check
	 * it's still current pricing before relying on the default here.
	 */
	public static function render( Config $config ): void {
		if ( self::is_hidden( $config ) ) return;
		$sites   = self::sites();
		$current = $config->get( 'demo.current', $config->brand_name() );
		$price   = $config->get( 'demo.price', '$349/yr' );
		?>
		<div class="<?php echo esc_attr( $config->css( 'demo-bar' ) ); ?>" role="note" aria-label="<?php esc_attr_e( 'Winery template demo notice', $config->text_domain() ); ?>">
			<div class="<?php echo esc_attr( $config->css( 'demo-bar-inner' ) ); ?>">
				<span class="<?php echo esc_attr( $config->css( 'demo-bar-label' ) ); ?>">
					<span class="<?php echo esc_attr( $config->css( 'demo-bar-label-full' ) ); ?>"><?php esc_html_e( 'Winery template demo', $config->text_domain() ); ?></span>
					<span class="<?php echo esc_attr( $config->css( 'demo-bar-skin' ) ); ?>"><?php echo esc_html( $current ); ?></span>
				</span>
				<nav class="<?php echo esc_attr( $config->css( 'demo-switcher' ) ); ?>" aria-label="<?php esc_attr_e( 'Switch demo', $config->text_domain() ); ?>">
					<?php foreach ( $sites as $name => $url ) : ?>
						<a href="<?php echo esc_url( $url ); ?>" <?php echo $name === $current ? 'class="is-current" aria-current="page"' : ''; ?>><?php echo esc_html( $name ); ?></a>
					<?php endforeach; ?>
				</nav>
				<a class="<?php echo esc_attr( $config->css( 'demo-buy' ) ); ?>" href="<?php echo esc_url( self::buy_url( $config ) ); ?>"><?php printf( esc_html__( 'Request %1$s · %2$s', $config->text_domain() ), esc_html( $current ), esc_html( $price ) ); ?></a>
				<button type="button" class="<?php echo esc_attr( $config->css( 'demo-bar-close' ) ); ?>" aria-label="<?php esc_attr_e( 'Close demo bar', $config->text_domain() ); ?>">&times;</button>
			</div>
		</div>
		<?php
	}
}
