<?php
// GENERATED FILE — do not edit directly. Synced from forwineries-theme-core.
// Edit the source there and re-run tools/sync-core.sh.
/**
 * "The Estate" / "How We Work" page. A product theme's own
 * page-templates/page-estate.php stub sets the "Template Name:" header,
 * then requires this file with $fw_config already in scope. Hero/split
 * images come from $config->get('pages.estate.*') — per-theme asset
 * paths, no sensible core default. Copy comes from ContentSettings\
 * Framework's estate_* fields.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

use ForWineries\Core\Config;
use ForWineries\Core\ElementorDefer;
use ForWineries\Core\ContentSettings\Framework;

$fw_config = new Config( isset( $fw_config ) ? $fw_config : array() );

get_header();

if ( ElementorDefer::is_built( get_the_ID() ) ) :
	while ( have_posts() ) : the_post();
		the_content();
	endwhile;
else :
	$fw_images = $fw_config->get( 'pages.estate', array() );
	?>

	<section class="fw-hero fw-hero--compact ph-img" style="background-image:url('<?php echo esc_url( isset( $fw_images['hero'] ) ? $fw_images['hero'] : '' ); ?>');">
		<div class="fw-hero-content">
			<span class="fw-eyebrow"><?php echo esc_html( Framework::field( $fw_config, 'estate_eyebrow' ) ); ?></span>
			<h1><?php echo esc_html( Framework::field( $fw_config, 'estate_heading' ) ); ?></h1>
			<p><?php echo esc_html( Framework::field( $fw_config, 'estate_intro' ) ); ?></p>
		</div>
	</section>

	<section class="fw-section">
		<div class="fw-container">
			<div class="fw-split">
				<div class="fw-split-media ph-img" style="background-image:url('<?php echo esc_url( isset( $fw_images['history'] ) ? $fw_images['history'] : '' ); ?>');"></div>
				<div class="fw-split-text">
					<span class="fw-eyebrow"><?php esc_html_e( 'History', $fw_config->text_domain() ); ?></span>
					<h2><?php echo esc_html( $fw_config->get( 'pages.estate.history_heading', __( 'Our Story', $fw_config->text_domain() ) ) ); ?></h2>
					<p><?php echo esc_html( Framework::field( $fw_config, 'estate_history' ) ); ?></p>
				</div>
			</div>
		</div>
	</section>

	<section class="fw-section fw-section--cream">
		<div class="fw-container">
			<div class="fw-stats-row">
				<div>
					<span class="fw-stat-num"><?php echo esc_html( Framework::field( $fw_config, 'estate_stat1_number' ) ); ?></span>
					<span class="fw-stat-label"><?php echo esc_html( Framework::field( $fw_config, 'estate_stat1_label' ) ); ?></span>
				</div>
				<div>
					<span class="fw-stat-num"><?php echo esc_html( Framework::field( $fw_config, 'estate_stat2_number' ) ); ?></span>
					<span class="fw-stat-label"><?php echo esc_html( Framework::field( $fw_config, 'estate_stat2_label' ) ); ?></span>
				</div>
				<div>
					<span class="fw-stat-num"><?php echo esc_html( Framework::field( $fw_config, 'estate_stat3_number' ) ); ?></span>
					<span class="fw-stat-label"><?php echo esc_html( Framework::field( $fw_config, 'estate_stat3_label' ) ); ?></span>
				</div>
			</div>
		</div>
	</section>

	<section class="fw-section">
		<div class="fw-container">
			<div class="fw-split fw-split--reverse">
				<div class="fw-split-media ph-img" style="background-image:url('<?php echo esc_url( isset( $fw_images['winemaking'] ) ? $fw_images['winemaking'] : '' ); ?>');"></div>
				<div class="fw-split-text">
					<span class="fw-eyebrow"><?php esc_html_e( 'Winemaking', $fw_config->text_domain() ); ?></span>
					<h2><?php echo esc_html( $fw_config->get( 'pages.estate.winemaking_heading', __( 'A Quiet Hand in the Cellar', $fw_config->text_domain() ) ) ); ?></h2>
					<p><?php echo esc_html( Framework::field( $fw_config, 'estate_philosophy' ) ); ?></p>
					<a class="fw-btn" href="<?php echo esc_url( home_url( '/team' ) ); ?>"><?php esc_html_e( 'Meet the Team', $fw_config->text_domain() ); ?></a>
				</div>
			</div>
		</div>
	</section>

<?php
endif;

get_footer();
