<?php
/**
 * 404 (Not Found). Same hero-band treatment as the marketing pages, with
 * the real search form and a couple of the most likely places a lost
 * visitor actually wants: the shop and the homepage.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

get_header();
?>

<section class="fw-hero fw-hero--compact ph-img ph-stone">
	<span class="ph-tag">stone-texture-or-cellar — 1600x900</span>
	<div class="fw-hero-content">
		<span class="fw-eyebrow">404</span>
		<h1><?php esc_html_e( 'Page Not Found', 'newtheme' ); ?></h1>
		<p><?php esc_html_e( 'That page has moved, or never existed — the link may be out of date. Try searching, or head back to familiar ground.', 'newtheme' ); ?></p>
	</div>
</section>

<section class="fw-section">
	<div class="fw-container">
		<div class="newtheme-search-form-wrap">
			<?php get_search_form(); ?>
		</div>

		<div class="fw-btn-row newtheme-404-links">
			<a class="fw-btn fw-btn--solid" href="<?php echo esc_url( home_url( '/collection/wines/' ) ); ?>"><?php esc_html_e( 'Browse Our Wines', 'newtheme' ); ?></a>
			<a class="fw-btn" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to Home', 'newtheme' ); ?></a>
			<a class="fw-btn" href="<?php echo esc_url( home_url( '/contact' ) ); ?>"><?php esc_html_e( 'Contact Us', 'newtheme' ); ?></a>
		</div>
	</div>
</section>

<?php
get_footer();
