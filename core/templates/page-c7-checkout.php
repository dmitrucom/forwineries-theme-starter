<?php
// GENERATED FILE — do not edit directly. Synced from forwineries-theme-core.
// Edit the source there and re-run tools/sync-core.sh.
/**
 * Commerce7 — Checkout (blank). A product theme's own page-templates/
 * page-c7-checkout.php stub sets the "Template Name:" header WordPress
 * needs, then requires this file with $fw_config already in scope
 * (e.g. `$fw_config = array('slug' => 'lh', ...); require
 * get_stylesheet_directory() . '/core/templates/page-c7-checkout.php';`
 * — the exact same array Boot::init() was called with, or a fresh
 * Config built from it).
 *
 * Commerce7's own integration guide asks for this page to be "blank and
 * not hold other elements such as a header or footer" — so unlike every
 * other page on the site, this one deliberately skips header.php/
 * footer.php and all nav/topbar/footer markup. Site fonts/colors still
 * load via wp_head() so checkout still feels on-brand, it's just
 * distraction-free.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$fw_config = isset( $fw_config ) ? $fw_config : array();
$fw_theme_color = isset( $fw_config['tokens']['clay'] ) ? $fw_config['tokens']['clay'] : '';
$fw_checkout_td = isset( $fw_config['text_domain'] ) ? $fw_config['text_domain'] : 'default';

/**
 * Overrides the title via the filter WordPress's own title-tag support
 * uses (add_theme_support('title-tag'), declared by every theme built
 * on this core) rather than also hand-printing a literal <title> tag
 * below — wp_head() already prints one automatically once that support
 * is declared, so a second, explicit tag here produced two <title>
 * elements in one <head> (invalid HTML — found via a real page load
 * during Larkhaven's migration, not assumed; the original per-theme
 * checkout templates all had this same latent bug, faithfully NOT
 * carried forward here).
 */
add_filter( 'pre_get_document_title', function () use ( $fw_checkout_td ) {
	/* translators: %s: brand name. */
	return sprintf( __( '%s — Checkout', $fw_checkout_td ), get_bloginfo( 'name' ) );
} );
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<?php if ( $fw_theme_color ) : ?><meta name="theme-color" content="<?php echo esc_attr( $fw_theme_color ); ?>"><?php endif; ?>
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'fw-c7-checkout-body' ); ?>>
<?php wp_body_open(); ?>

<div class="fw-checkout-wrap">
	<div id="c7-content"></div>
</div>

<?php wp_footer(); ?>
</body>
</html>
