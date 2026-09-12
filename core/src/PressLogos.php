<?php
// GENERATED FILE — do not edit directly. Synced from forwineries-theme-core.
// Edit the source there and re-run tools/sync-core.sh.
namespace ForWineries\Core;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The "As Seen In" flagship widget — a seamless auto-scrolling press-
 * logo marquee (real accessible list + an aria-hidden visual-only
 * duplicate so the CSS loop has a second copy to scroll into) a buyer
 * can drop on any page via Elementor. Independent of the homepage's
 * own hardcoded copy of this section (front-page.php, pulling from
 * ContentSettings\Framework's 'press_logos' field) and its per-theme
 * {prefix}-press-strip CSS — same reasoning as Events::
 * render_upcoming_widget(): reusing an already-shipped page's per-
 * theme markup/CSS here would mean forking per theme or risking a
 * regression on that page. This widget gets its own independent
 * repeater field and its own core-owned CSS (press-logos.css)
 * instead, sharing only the starter default content (the same 6
 * real-but-unbranded publication names every original theme shipped)
 * so a buyer sees familiar copy regardless of which builder they use.
 */
class PressLogos {

	public static function register( Config $config ): void {
		// Registered (not enqueued) here — only actually loaded on a
		// request that places render_widget() somewhere, via
		// wp_enqueue_style() inside that method itself.
		add_action( 'wp_enqueue_scripts', function () use ( $config ) {
			wp_register_style(
				'fw-press-logos',
				get_stylesheet_directory_uri() . '/core/assets/css/press-logos.css',
				array(),
				Config::asset_version( '/core/assets/css/press-logos.css' )
			);
		} );
	}

	/**
	 * Same shape every original theme's default 'press_logos' repeater
	 * used (ContentSettings\Framework) — 6 real publication NAMES with
	 * blank image/link, rendering as honest labeled placeholders rather
	 * than a fabricated logo. Exposed publicly so Blocks::
	 * block_definitions() can use it as the widget field's own default
	 * without duplicating the list a second time.
	 */
	public static function default_logos(): array {
		return array(
			array( 'name' => 'Wine Spectator', 'image' => '', 'link' => '' ),
			array( 'name' => 'Wine Enthusiast', 'image' => '', 'link' => '' ),
			array( 'name' => 'Decanter', 'image' => '', 'link' => '' ),
			array( 'name' => 'Food & Wine', 'image' => '', 'link' => '' ),
			array( 'name' => 'Somm Journal', 'image' => '', 'link' => '' ),
			array( 'name' => 'Napa Valley Register', 'image' => '', 'link' => '' ),
		);
	}

	/**
	 * A repeater row's 'image' can arrive as a plain URL string (the
	 * classic ContentSettings\Framework admin path, and this widget's
	 * own field default above) OR as Elementor's MEDIA control shape,
	 * ['url' => ..., 'id' => ...] (see ElementorWidget::
	 * add_repeater_control()'s own docblock on why the generic
	 * plumbing can't unwrap this automatically per-row). Handling both
	 * here is what lets one render method serve either source.
	 */
	private static function image_url( $value ): string {
		if ( is_string( $value ) ) return trim( $value );
		if ( is_array( $value ) && ! empty( $value['url'] ) ) return trim( (string) $value['url'] );
		return '';
	}

	/**
	 * Renders nothing at all when every row is unnamed (never an empty
	 * "As Seen In" heading over a blank strip) — same never-show-a-
	 * broken-widget rule as every other widget in this system.
	 */
	public static function render_widget( Config $config, array $atts = array() ): string {
		$logos = isset( $atts['logos'] ) && is_array( $atts['logos'] ) ? $atts['logos'] : array();

		$items_html = '';
		foreach ( $logos as $item ) {
			$name = isset( $item['name'] ) ? trim( (string) $item['name'] ) : '';
			if ( ! $name ) continue;
			$image = self::image_url( isset( $item['image'] ) ? $item['image'] : '' );
			$link  = isset( $item['link'] ) ? trim( (string) $item['link'] ) : '';
			$logo_markup = $image
				? '<img src="' . esc_url( $image ) . '" alt="' . esc_attr( $name ) . '" loading="lazy">'
				: '<span class="ph-img fw-press-logo-placeholder"><span class="ph-tag">' . esc_html( $name ) . '</span></span>';
			$items_html .= $link
				? '<a href="' . esc_url( $link ) . '" target="_blank" rel="noopener noreferrer">' . $logo_markup . '</a>'
				: $logo_markup;
		}
		if ( ! $items_html ) return '';

		wp_enqueue_style( 'fw-press-logos' );

		$eyebrow = ( isset( $atts['eyebrow'] ) && $atts['eyebrow'] !== '' ) ? $atts['eyebrow'] : __( 'As Seen In', $config->text_domain() );
		$heading = ( isset( $atts['heading'] ) && $atts['heading'] !== '' ) ? $atts['heading'] : __( 'Recognized by the Press We Respect', $config->text_domain() );

		ob_start();
		?>
		<section class="fw-section fw-press-section">
			<div class="fw-container">
				<span class="fw-eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
				<h2 class="fw-press-heading"><?php echo esc_html( $heading ); ?></h2>
				<div class="fw-press-strip-outer">
					<div class="fw-press-strip">
						<?php echo $items_html; // real, accessible copy ?>
						<?php /* Visual-only duplicate so the CSS loop (press-
						   logos.css) has a seamless second half to scroll
						   into — hidden from assistive tech and tab order
						   so a screen reader/keyboard user never sees the
						   same publications twice. */ ?>
						<div class="fw-press-strip-dup" aria-hidden="true"><?php echo str_replace( '<a href', '<a tabindex="-1" href', $items_html ); ?></div>
					</div>
				</div>
			</div>
		</section>
		<?php
		return ob_get_clean();
	}
}
