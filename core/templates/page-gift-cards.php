<?php
// GENERATED FILE — do not edit directly. Synced from forwineries-theme-core.
// Edit the source there and re-run tools/sync-core.sh.
/**
 * Gift Cards page. A product theme's own page-templates/
 * page-gift-cards.php stub sets the "Template Name:" header, then
 * requires this file with $fw_config already in scope.
 *
 * Commerce7 has no dedicated gift-card widget — a gift card is just a
 * regular product added to a collection in Commerce7 admin, so this
 * page is a normal [c7_collection] embed pointed at that collection's
 * slug (Setup tab > "Gift card collection slug").
 */
if ( ! defined( 'ABSPATH' ) ) exit;

use ForWineries\Core\Config;
use ForWineries\Core\ElementorDefer;
use ForWineries\Core\ContentSettings\Framework;
use ForWineries\Core\Commerce7\Integration;

$fw_config = new Config( isset( $fw_config ) ? $fw_config : array() );

get_header();

if ( ElementorDefer::is_built( get_the_ID() ) ) :
	while ( have_posts() ) : the_post();
		the_content();
	endwhile;
else :
	$fw_gift_card_slug = get_option( $fw_config->option_key( 'c7_gift_card_collection_slug' ), '' );
	$fw_hero_image      = $fw_config->get( 'pages.gift_cards.image', '' );
	?>

	<section class="fw-hero fw-hero--compact ph-img" style="background-image:url('<?php echo esc_url( $fw_hero_image ); ?>');">
		<div class="fw-hero-content">
			<span class="fw-eyebrow"><?php echo esc_html( Framework::field( $fw_config, 'gift_cards_eyebrow' ) ); ?></span>
			<h1><?php echo esc_html( Framework::field( $fw_config, 'gift_cards_heading' ) ); ?></h1>
			<p><?php echo esc_html( Framework::field( $fw_config, 'gift_cards_intro' ) ); ?></p>
		</div>
	</section>

	<div class="fw-section fw-c7-page">
		<div class="fw-container">
			<?php if ( $fw_gift_card_slug ) : ?>
				<?php echo do_shortcode( '[c7_collection slug="' . esc_attr( $fw_gift_card_slug ) . '"]' ); ?>
			<?php else : ?>
				<?php echo Integration::config_notice( $fw_config, __( 'Set a gift card collection slug under Setup to show gift cards here.', $fw_config->text_domain() ) ); ?>
			<?php endif; ?>
		</div>
	</div>

<?php
endif;

get_footer();
