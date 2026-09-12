<?php
// GENERATED FILE — do not edit directly. Synced from forwineries-theme-core.
// Edit the source there and re-run tools/sync-core.sh.
namespace ForWineries\Core\Commerce7;

use ForWineries\Core\Config;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The real Commerce7 reservation booking widget (confirmed via
 * Commerce7's design docs — <div class="c7-reservation-availability"
 * data-reservation-type-slug="...">) — an actual live availability
 * calendar, not just a link. Optional: callers (core/templates/
 * page-c7-content.php's reservation route, a homepage Reservation CTA)
 * only use this once a reservation type slug is configured; both fall
 * back to a plain "link to /reservation" when it's blank.
 *
 * The calendar-dropdown CSS fix baked into
 * core/assets/css/commerce7-overrides.css (mobile re-anchor + table-
 * layout:fixed + cell-padding trim + button width, plus the hero-band
 * overflow:visible fix) is this widget's — see that file's own comments
 * for the full three-part story. That fix is what this whole shared-
 * core rebuild started from: it had already been applied by hand to 4
 * of the 5 original themes and was still missing from Larkhaven when
 * this session began.
 */
class ReservationWidget {

	public static function register( Config $config ): void {
		add_shortcode( 'c7_reservation_availability', function ( $atts = array() ) use ( $config ) {
			return self::render( $config, (array) $atts );
		} );
	}

	public static function render( Config $config, array $atts = array() ): string {
		$atts = shortcode_atts( array( 'slug' => get_option( $config->option_key( 'c7_reservation_type_slug' ), '' ) ), $atts );
		if ( ! Integration::widgets_ready( $config ) || ! $atts['slug'] ) return '';
		return '<div class="c7-reservation-availability" data-reservation-type-slug="' . esc_attr( $atts['slug'] ) . '"></div>';
	}

	/**
	 * The "Tastings by Appointment" flagship section — a full-bleed
	 * .fw-hero.fw-hero--band with the calendar embedded directly in it,
	 * one Elementor drop instead of a buyer having to separately build a
	 * hero band AND remember it needs the `fw-hero--band` class (the one
	 * that actually carries the `overflow: visible` fix this calendar's
	 * dropdown depends on — see commerce7-overrides.css's "RESERVATION
	 * WIDGET EMBED" section) AND place this shortcode inside it in the
	 * right spot. Previously that exact combination only existed as
	 * hardcoded markup in front-page.php's homepage CTA band and
	 * page-c7-content.php's /reservation route — this is the first way
	 * to get it on any OTHER page too.
	 *
	 * Falls back to a plain button linking to the configured reservation
	 * URL when no reservation-type slug is set, same as every other
	 * caller of render() above — never renders a dead/broken embed.
	 */
	public static function render_band( Config $config, array $atts = array() ): string {
		$eyebrow = ( isset( $atts['eyebrow'] ) && $atts['eyebrow'] !== '' ) ? $atts['eyebrow'] : __( 'Tastings by Appointment', $config->text_domain() );
		$heading = ( isset( $atts['heading'] ) && $atts['heading'] !== '' ) ? $atts['heading'] : __( 'Reserve a Tasting', $config->text_domain() );
		$intro   = ( isset( $atts['intro'] ) && $atts['intro'] !== '' ) ? $atts['intro'] : __( 'Pick a date and time below — tastings are seated, by appointment only, and kept small so every visit feels unhurried.', $config->text_domain() );
		$image   = isset( $atts['image'] ) ? trim( (string) $atts['image'] ) : '';
		$slug    = isset( $atts['slug'] ) ? trim( $atts['slug'] ) : '';

		// "Background photo (optional)" means optional to SET, not optional
		// to HAVE: this band is a full-bleed hero whose scrim assumes a
		// photo underneath, so leaving it blank used to render a flat dark
		// slab that reads as a broken image (the Albariza/Heronrest
		// homepage report, 2026-09-10). Fall back to the theme's own
		// configured reservation/visit hero photo instead.
		if ( $image === '' ) $image = $config->band_image();

		$widget_html = self::render( $config, $slug ? array( 'slug' => $slug ) : array() );

		ob_start();
		?>
		<section class="fw-hero fw-hero--band<?php echo $image ? ' ph-img' : ''; ?>"<?php if ( $image ) : ?> style="background-image:url('<?php echo esc_url( $image ); ?>');"<?php endif; ?>>
			<div class="fw-hero-content">
				<span class="fw-eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
				<h2><?php echo esc_html( $heading ); ?></h2>
				<p><?php echo esc_html( $intro ); ?></p>
				<?php if ( $widget_html ) : ?>
					<div class="fw-reservation-widget-embed"><?php echo $widget_html; ?></div>
				<?php else : ?>
					<div class="fw-btn-row">
						<a class="fw-btn fw-btn--accent" href="<?php echo esc_url( get_option( $config->option_key( 'c7_reservation_url' ), home_url( '/reservation' ) ) ); ?>"><?php esc_html_e( 'Reserve a Tasting', $config->text_domain() ); ?></a>
					</div>
				<?php endif; ?>
			</div>
		</section>
		<?php
		return ob_get_clean();
	}
}
