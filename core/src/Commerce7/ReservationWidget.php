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
}
