<?php
// GENERATED FILE — do not edit directly. Synced from forwineries-theme-core.
// Edit the source there and re-run tools/sync-core.sh.
namespace ForWineries\Core;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The "Social Feed" flagship widget — a repeater of Instagram-style
 * photo tiles a buyer can drop on any page via Elementor. Independent
 * of the homepage's own hardcoded copy of this section (front-
 * page.php, pulling from ContentSettings\Framework's 'social_feed'
 * field) and each theme's own per-theme {prefix}-social-grid CSS,
 * same reasoning as the other flagship widgets.
 *
 * Deliberately lower priority than the other 5 flagship widgets — a
 * buyer CAN fake this one by hand with an image gallery, it's just
 * tedious, unlike something Elementor genuinely cannot do at all.
 */
class SocialFeed {

	public static function register( Config $config ): void {
		add_action( 'wp_enqueue_scripts', function () use ( $config ) {
			wp_register_style(
				'fw-social-feed',
				get_stylesheet_directory_uri() . '/core/assets/css/social-feed.css',
				array(),
				Config::asset_version( '/core/assets/css/social-feed.css' )
			);
		} );
	}

	/**
	 * A row's 'image' can arrive as a plain URL string (the classic
	 * ContentSettings\Framework admin path) OR as Elementor's MEDIA
	 * control shape, ['url' => ..., 'id' => ...] — same dual-shape
	 * handling as PressLogos::image_url().
	 */
	private static function image_url( $value ): string {
		if ( is_string( $value ) ) return trim( $value );
		if ( is_array( $value ) && ! empty( $value['url'] ) ) return trim( (string) $value['url'] );
		return '';
	}

	/**
	 * Renders nothing at all when every row has no image (never an
	 * empty heading over a blank grid) — same never-show-a-broken-
	 * widget rule as every other widget in this system. A row's own
	 * link falls back to the site's configured Instagram URL (same as
	 * the homepage's original hardcoded version), then to '#' if that
	 * isn't set either — never a dead/missing href.
	 */
	public static function render_widget( Config $config, array $atts = array() ): string {
		$photos = isset( $atts['photos'] ) && is_array( $atts['photos'] ) ? $atts['photos'] : array();

		$tiles_html = '';
		foreach ( $photos as $photo ) {
			$image = self::image_url( isset( $photo['image'] ) ? $photo['image'] : '' );
			if ( ! $image ) continue;
			$link = isset( $photo['link'] ) ? trim( (string) $photo['link'] ) : '';
			if ( ! $link ) $link = ContactSettings::get( $config, 'social_instagram', '#' );
			$tiles_html .= '<a href="' . esc_url( $link ) . '" target="_blank" rel="noopener noreferrer"><img src="' . esc_url( $image ) . '" alt="" loading="lazy"></a>';
		}
		if ( ! $tiles_html ) return '';

		wp_enqueue_style( 'fw-social-feed' );

		$eyebrow = ( isset( $atts['eyebrow'] ) && $atts['eyebrow'] !== '' ) ? $atts['eyebrow'] : __( 'Follow Along', $config->text_domain() );
		$heading = ( isset( $atts['heading'] ) && $atts['heading'] !== '' ) ? $atts['heading'] : __( 'From the Estate', $config->text_domain() );

		ob_start();
		?>
		<section class="fw-section fw-section--cream">
			<div class="fw-container">
				<div class="fw-section-head">
					<span class="fw-eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
					<h2><?php echo esc_html( $heading ); ?></h2>
				</div>
				<div class="fw-social-grid">
					<?php echo $tiles_html; ?>
				</div>
			</div>
		</section>
		<?php
		return ob_get_clean();
	}
}
