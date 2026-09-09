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
 * Buyers turn this off after purchase (Demo Bar tab → "Hide demo bar").
 * Ships SHOWN by default — that's what the live forwineries.com demos
 * need; a buyer's own install is expected to flip it once live.
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
				null
			);
			wp_enqueue_script(
				'fw-demo-bar',
				get_stylesheet_directory_uri() . '/core/assets/js/demo-bar.js',
				array(),
				null,
				true
			);
		} );

		add_action( 'wp_head', function () use ( $config ) {
			self::output_demo_robots_meta( $config );
		}, 1 );

		add_action( 'admin_init', function () use ( $config ) {
			register_setting( $config->option_key( 'demo_bar_settings' ), $config->option_key( 'demo_bar_hidden' ), array(
				'type'              => 'string',
				'sanitize_callback' => function ( $value ) {
					return $value === '1' ? '1' : '0';
				},
				'default' => '0',
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
		return get_option( $config->option_key( 'demo_bar_hidden' ), '0' ) === '1';
	}

	public static function is_active( Config $config ): bool {
		return ! self::is_hidden( $config );
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

		unset( $parts['tagline'] );
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
				<?php esc_html_e( 'Controls the "Winery template demo" strip shown above the header on forwineries.com\'s live demo sites. Turn this on once you\'ve purchased and installed this theme on your own site — your visitors never need to see it.', $config->text_domain() ); ?>
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
								<?php esc_html_e( 'Hide demo bar (check this once this theme is installed on a real, purchased site)', $config->text_domain() ); ?>
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
