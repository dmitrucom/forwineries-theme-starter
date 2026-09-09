<?php
// GENERATED FILE — do not edit directly. Synced from forwineries-theme-core.
// Edit the source there and re-run tools/sync-core.sh.
namespace ForWineries\Core;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Basic schema.org structured data (JSON-LD) — scoped to what's safely
 * derivable without new API calls: a Winery block sitewide, Event data
 * for single event pages (reuses Events::rsvp_url()), and Product data
 * for an already-fetched, already-shaped set of wines.
 *
 * Contact fields are read directly via get_option() against
 * $config->option_key('contact_*') — the exact same wp_options keys the
 * original inc/customizer.php's lh_get_option()/lh_contact_fields()
 * already wrote (lh_contact_address_line, etc.), confirmed before
 * choosing this naming, specifically so a Phase-1-migrated site keeps
 * reading its already-saved contact info instead of resetting to blank.
 * The admin-editable settings UI for these fields (ContentSettings\
 * Framework, still to be ported — see docs/ARCHITECTURE.md) is a
 * separate, additive concern from this read path, same relationship as
 * AgeGate's copy fields to their own not-yet-ported settings UI.
 *
 * products_jsonld() takes a pre-shaped $items array as a parameter — it
 * doesn't call into Commerce7\Integration itself, Commerce7\Integration
 * (still to be ported) will call INTO this once it exists. That's a
 * forward-safe dependency direction, unlike blocks.php/
 * elementor-widgets.php, which call Commerce7 render functions directly
 * and had to be deferred — see docs/LESSONS.md's "Shared-core design
 * boundaries" section for why this file didn't need the same treatment.
 */
class Schema {

	public static function register( Config $config ): void {
		add_action( 'wp_head', function () use ( $config ) {
			self::print_winery_jsonld( $config );
		}, 5 );
	}

	private static function contact( Config $config, string $key, string $default = '' ): string {
		$value = get_option( $config->option_key( 'contact_' . $key ), $default );
		// "#" was this field's old placeholder default (pre-dates the
		// "hide the icon instead of a dead link" fix) — a site that saved
		// this tab before that change may still have a literal "#"
		// stored. Treated exactly like blank everywhere this is read.
		if ( $value === '#' ) $value = '';
		return $value !== '' ? $value : $default;
	}

	private static function print_winery_jsonld( Config $config ): void {
		$data = array(
			'@context' => 'https://schema.org',
			'@type'    => 'Winery',
			'name'     => $config->brand_name(),
			'url'      => home_url( '/' ),
		);

		$address = self::contact( $config, 'address_line' );
		if ( $address ) {
			$data['address'] = array(
				'@type'         => 'PostalAddress',
				'streetAddress' => $address,
			);
		}

		$phone = self::contact( $config, 'phone_tel' );
		if ( $phone ) $data['telephone'] = $phone;

		$email = self::contact( $config, 'email' );
		if ( $email ) $data['email'] = $email;

		$same_as = array_values( array_filter( array(
			self::contact( $config, 'social_instagram' ),
			self::contact( $config, 'social_facebook' ),
		), function ( $url ) { return $url && $url !== '#'; } ) );
		if ( $same_as ) $data['sameAs'] = $same_as;

		if ( has_custom_logo() ) {
			$logo_url = wp_get_attachment_image_url( get_theme_mod( 'custom_logo' ), 'full' );
			if ( $logo_url ) $data['logo'] = $logo_url;
		}

		echo '<script type="application/ld+json">' . wp_json_encode( $data ) . '</script>' . "\n";
	}

	/**
	 * Event JSON-LD for a single event — lets Google show
	 * date/location/RSVP directly in search results. startDate is a
	 * plain date (Events' event_date meta, "Y-m-d") rather than a full
	 * date+time — event_time is a freeform display string (e.g. "6:00 PM
	 * – 9:00 PM", not machine-parseable), and a bare date is still valid
	 * per schema.org rather than guessing at an exact ISO datetime.
	 */
	public static function event_jsonld( Config $config, $post_id ): string {
		$date = get_post_meta( $post_id, 'event_date', true );
		if ( ! $date ) return '';

		$data = array(
			'@context'            => 'https://schema.org',
			'@type'               => 'Event',
			'name'                => get_the_title( $post_id ),
			'startDate'           => $date,
			'eventStatus'         => 'https://schema.org/EventScheduled',
			'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
			'url'                 => get_permalink( $post_id ),
		);

		$data['location'] = array(
			'@type' => 'Place',
			'name'  => $config->brand_name(),
		);
		$address = self::contact( $config, 'address_line' );
		if ( $address ) {
			$data['location']['address'] = array(
				'@type'         => 'PostalAddress',
				'streetAddress' => $address,
			);
		}

		$excerpt = get_the_excerpt( $post_id );
		if ( $excerpt ) $data['description'] = wp_strip_all_tags( $excerpt );

		if ( has_post_thumbnail( $post_id ) ) {
			$data['image'] = get_the_post_thumbnail_url( $post_id, 'full' );
		}

		$data['organizer'] = array(
			'@type' => 'Organization',
			'name'  => $config->brand_name(),
			'url'   => home_url( '/' ),
		);

		$rsvp_url = Events::rsvp_url( $config, $post_id );
		if ( $rsvp_url ) {
			$data['offers'] = array(
				'@type'        => 'Offer',
				'url'          => $rsvp_url,
				'availability' => 'https://schema.org/InStock',
			);
		}

		return '<script type="application/ld+json">' . wp_json_encode( $data ) . '</script>' . "\n";
	}

	/**
	 * Product JSON-LD for an already-fetched, already-shaped set of
	 * wines — called by Commerce7\Integration (once ported) right
	 * alongside the catalog markup it describes, using data that code
	 * already has in hand rather than a fresh REST call. priceCurrency
	 * is hardcoded to USD — every original theme's shipping-compliance
	 * content is US-only; revisit if a non-US winery ever needs this.
	 */
	public static function products_jsonld( array $items ): string {
		if ( empty( $items ) ) return '';

		$html = '';
		foreach ( $items as $item ) {
			if ( empty( $item['title'] ) || empty( $item['slug'] ) ) continue;

			$product = array(
				'@context' => 'https://schema.org',
				'@type'    => 'Product',
				'name'     => $item['title'],
				'url'      => home_url( '/product/' . $item['slug'] ),
			);
			if ( ! empty( $item['image'] ) ) $product['image'] = $item['image'];
			if ( ! empty( $item['teaser'] ) ) $product['description'] = $item['teaser'];
			if ( ! empty( $item['price'] ) && $item['price'] > 0 ) {
				$product['offers'] = array(
					'@type'         => 'Offer',
					'price'         => number_format( $item['price'] / 100, 2, '.', '' ),
					'priceCurrency' => 'USD',
					'availability'  => 'https://schema.org/InStock',
					'url'           => home_url( '/product/' . $item['slug'] ),
				);
			}

			$html .= '<script type="application/ld+json">' . wp_json_encode( $product ) . '</script>' . "\n";
		}

		return $html;
	}
}
