<?php
// GENERATED FILE — do not edit directly. Synced from forwineries-theme-core.
// Edit the source there and re-run tools/sync-core.sh.
namespace ForWineries\Core;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The "Testimonials / What People Are Saying" flagship widget — a
 * repeater of quote+source cards a buyer can drop on any page via
 * Elementor. Independent of the homepage's own hardcoded copy of this
 * section (front-page.php, pulling from ContentSettings\Framework's
 * 'testimonials' field) and each theme's own per-theme
 * {prefix}-testimonial-* CSS, same reasoning as the other flagship
 * widgets: reusing an already-shipped page's per-theme markup/CSS
 * here would mean forking per theme or risking a regression.
 *
 * Deliberately lower priority than the other 5 flagship widgets — a
 * buyer CAN fake this one by hand with stacked text widgets, it's
 * just tedious, unlike something like Flagship Wine's live product
 * data-binding which Elementor genuinely cannot do at all.
 */
class Testimonials {

	public static function register( Config $config ): void {
		add_action( 'wp_enqueue_scripts', function () use ( $config ) {
			wp_register_style(
				'fw-testimonials',
				get_stylesheet_directory_uri() . '/core/assets/css/testimonials.css',
				array(),
				Config::asset_version( '/core/assets/css/testimonials.css' )
			);
		} );
	}

	/**
	 * Renders nothing at all when every row has a blank quote (never an
	 * empty heading over a blank grid) — same never-show-a-broken-widget
	 * rule as every other widget in this system.
	 */
	public static function render_widget( Config $config, array $atts = array() ): string {
		$items = isset( $atts['testimonials'] ) && is_array( $atts['testimonials'] ) ? $atts['testimonials'] : array();

		$cards_html = '';
		foreach ( $items as $item ) {
			$quote = isset( $item['quote'] ) ? trim( (string) $item['quote'] ) : '';
			if ( ! $quote ) continue;
			$source = isset( $item['source'] ) ? trim( (string) $item['source'] ) : '';
			$cards_html .= '<div class="fw-testimonial-card"><p class="fw-testimonial-quote">' . esc_html( $quote ) . '</p>';
			if ( $source ) {
				$cards_html .= '<span class="fw-testimonial-source">' . esc_html( $source ) . '</span>';
			}
			$cards_html .= '</div>';
		}
		if ( ! $cards_html ) return '';

		wp_enqueue_style( 'fw-testimonials' );

		$eyebrow = ( isset( $atts['eyebrow'] ) && $atts['eyebrow'] !== '' ) ? $atts['eyebrow'] : __( 'In Their Words', $config->text_domain() );
		$heading = ( isset( $atts['heading'] ) && $atts['heading'] !== '' ) ? $atts['heading'] : __( 'What People Are Saying', $config->text_domain() );

		ob_start();
		?>
		<section class="fw-section">
			<div class="fw-container">
				<div class="fw-section-head">
					<span class="fw-eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
					<h2><?php echo esc_html( $heading ); ?></h2>
				</div>
				<div class="fw-testimonial-grid">
					<?php echo $cards_html; ?>
				</div>
			</div>
		</section>
		<?php
		return ob_get_clean();
	}
}
