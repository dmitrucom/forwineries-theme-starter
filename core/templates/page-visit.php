<?php
// GENERATED FILE — do not edit directly. Synced from forwineries-theme-core.
// Edit the source there and re-run tools/sync-core.sh.
/**
 * Visit page. A product theme's own page-templates/page-visit.php stub
 * sets the "Template Name:" header, then requires this file with
 * $fw_config already in scope. Hero image is a per-theme asset path via
 * $config->get('pages.visit.image'). Reuses the address from
 * ContactSettings and the reservation link from Commerce7\Integration's
 * option key rather than duplicating either.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

use ForWineries\Core\Config;
use ForWineries\Core\ElementorDefer;
use ForWineries\Core\ContentSettings\Framework;
use ForWineries\Core\ContactSettings;

$fw_config = new Config( isset( $fw_config ) ? $fw_config : array() );

get_header();

if ( ElementorDefer::is_built( get_the_ID() ) ) :
	while ( have_posts() ) : the_post();
		the_content();
	endwhile;
else :
	// A configured "Map embed URL" always wins. Otherwise, fall back to a
	// real Google Maps embed built from the address already on file —
	// the plain output=embed form needs no API key, unlike the "Embed a
	// map" iframe src Google's own UI generates. Never falls back to a
	// placeholder telling the shopper how to configure this.
	$fw_map_embed = Framework::field( $fw_config, 'visit_page_map_embed', '' );
	if ( ! $fw_map_embed ) {
		$fw_map_address = ContactSettings::get( $fw_config, 'address_line' );
		$fw_map_embed   = 'https://www.google.com/maps?q=' . rawurlencode( $fw_map_address ) . '&output=embed';
	}
	$fw_hero_image = $fw_config->get( 'pages.visit.image', '' );
	$fw_reservation_url = get_option( $fw_config->option_key( 'c7_reservation_url' ), home_url( '/reservation' ) );
	?>

	<section class="fw-hero fw-hero--compact ph-img" style="background-image:url('<?php echo esc_url( $fw_hero_image ); ?>');">
		<div class="fw-hero-content">
			<span class="fw-eyebrow"><?php echo esc_html( Framework::field( $fw_config, 'visit_page_eyebrow' ) ); ?></span>
			<h1><?php echo esc_html( Framework::field( $fw_config, 'visit_page_heading' ) ); ?></h1>
			<p><?php echo esc_html( Framework::field( $fw_config, 'visit_page_intro' ) ); ?></p>
			<div class="fw-btn-row">
				<a class="fw-btn fw-btn--solid" href="<?php echo esc_url( $fw_reservation_url ); ?>"><?php esc_html_e( 'Reserve a Tasting', $fw_config->text_domain() ); ?></a>
			</div>
		</div>
	</section>

	<section class="fw-section">
		<div class="fw-container">
			<div class="fw-split">
				<div class="fw-split-text">
					<span class="fw-eyebrow"><?php esc_html_e( 'Hours', $fw_config->text_domain() ); ?></span>
					<h2><?php esc_html_e( 'When to Find Us', $fw_config->text_domain() ); ?></h2>
					<p><?php echo nl2br( esc_html( Framework::field( $fw_config, 'visit_page_hours' ) ) ); ?></p>
					<p>
						<strong><?php esc_html_e( 'Address', $fw_config->text_domain() ); ?></strong><br>
						<?php echo esc_html( ContactSettings::get( $fw_config, 'address_line' ) ); ?><br>
						<a href="tel:<?php echo esc_attr( ContactSettings::get( $fw_config, 'phone_tel' ) ); ?>"><?php echo esc_html( ContactSettings::get( $fw_config, 'phone_display' ) ); ?></a>
					</p>
					<a class="fw-btn" href="<?php echo esc_url( $fw_reservation_url ); ?>"><?php esc_html_e( 'Reserve a Tasting', $fw_config->text_domain() ); ?></a>
				</div>
				<div class="fw-split-media">
					<iframe src="<?php echo esc_url( $fw_map_embed ); ?>" width="100%" height="360" style="border:0; border-radius: var(--fw-radius);" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="<?php printf( esc_attr__( 'Map to %s', $fw_config->text_domain() ), esc_attr( $fw_config->brand_name() ) ); ?>"></iframe>
				</div>
			</div>
		</div>
	</section>

<?php
endif;

get_footer();
