<?php
/**
 * Custom header — replaces Hello Elementor's header entirely (this theme
 * has no parent theme at all, so there's nothing to replace, but the
 * comment is kept because every sibling theme's header.php says this and
 * it explains WHY this file exists rather than relying on a builder).
 * Renders on every page/post regardless of Elementor Pro Theme Builder
 * availability.
 *
 * Layout: plain "inline nav" — topbar, logo, primary nav, mobile
 * toggle. No overlay nav, no 3-zone layout, no announcement ribbon —
 * this is the simplest of the layout variants used across the 5 sibling
 * themes (matches Larkhaven's). If your new theme wants Vespera's
 * richer overlay-nav treatment instead, read that theme's header.php
 * directly — this starter deliberately ships the simpler pattern as the
 * generic default.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

use ForWineries\Core\Config;
use ForWineries\Core\DemoBar;
use ForWineries\Core\AgeGate;
use ForWineries\Core\ContactSettings;
use ForWineries\Core\ContentSettings\Framework;

$fw_config = new Config( newtheme_fw_config_array() );
?><!DOCTYPE html>
<html <?php language_attributes(); ?><?php echo DemoBar::is_active( $fw_config ) ? ' class="fw-demo-bar-active"' : ''; ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<meta name="theme-color" content="<?php echo esc_attr( $fw_config->get( 'tokens.clay', '' ) ); ?>">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php DemoBar::render( $fw_config ); ?>

<?php AgeGate::render( $fw_config ); ?>

<a class="newtheme-skip-link" href="#newtheme-main"><?php esc_html_e( 'Skip to content', 'newtheme' ); ?></a>

<?php
/**
 * id="fw-header" is a HARD CONTRACT, not a styling choice — core's
 * DemoBar CSS (core/assets/css/demo-bar.css) depends on this exact id
 * to push the header down when the demo bar is showing. See
 * forwineries-theme-core/docs/ARCHITECTURE.md's "What's genuinely
 * per-theme" section and docs/LESSONS.md. Do not rename this id even
 * though the rest of this file is free to vary theme to theme.
 */
?>
<header id="fw-header">
	<div class="newtheme-topbar">
		<div class="fw-container">
			<span><?php echo esc_html( ContactSettings::get( $fw_config, 'address_line' ) ); ?></span>
			<span>
				<a href="tel:<?php echo esc_attr( ContactSettings::get( $fw_config, 'phone_tel' ) ); ?>"><?php echo esc_html( ContactSettings::get( $fw_config, 'phone_display' ) ); ?></a>
				&nbsp;·&nbsp;
				<a href="<?php echo esc_url( get_option( $fw_config->option_key( 'c7_reservation_url' ), home_url( '/reservation' ) ) ); ?>"><?php esc_html_e( 'Reserve a Tasting', 'newtheme' ); ?></a>
			</span>
		</div>
	</div>

	<div class="fw-container newtheme-header-main">
		<?php $newtheme_logo_image = Framework::field( $fw_config, 'logo_image' ); ?>
		<a class="newtheme-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<?php if ( $newtheme_logo_image !== '' ) : ?>
				<img src="<?php echo esc_url( $newtheme_logo_image ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" class="newtheme-logo-img">
			<?php elseif ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<span>
					<?php echo esc_html( $fw_config->brand_name() ); ?>
					<span class="newtheme-logo-sub"><?php esc_html_e( 'Est. 20XX · Somewhere, CA', 'newtheme' ); ?></span>
				</span>
			<?php endif; ?>
		</a>

		<?php
		// Genuinely theme-specific structural hook (not shared fw-*
		// vocabulary) — this exact id is only ever targeted by THIS
		// theme's own assets/js/theme.js and style.css, never by core.
		?>
		<button class="newtheme-nav-toggle" id="newtheme-nav-toggle" aria-label="<?php esc_attr_e( 'Toggle navigation menu', 'newtheme' ); ?>" aria-expanded="false" aria-controls="newtheme-primary-nav">
			<span></span><span></span><span></span>
		</button>

		<nav id="newtheme-primary-nav" aria-label="<?php esc_attr_e( 'Primary', 'newtheme' ); ?>">
			<?php
			wp_nav_menu( array(
				'theme_location' => $fw_config->nav_location( 'primary' ),
				'container'      => false,
				'fallback_cb'    => 'newtheme_default_primary_menu',
			) );
			?>
		</nav>

		<div class="newtheme-header-actions">
			<?php echo do_shortcode( '[c7_account]' ); ?>
			<?php echo do_shortcode( '[c7_cart_trigger]' ); ?>
		</div>
	</div>
</header>

<main id="newtheme-main">

<?php
/**
 * Last-resort fallback — normally never used. Theme activation creates a
 * real, editable "Primary Menu" (Boot::init()'s PageSetup module) and
 * assigns it here automatically, so Appearance > Menus already has
 * something to edit from day one. This only renders if that assignment
 * is somehow missing.
 */
function newtheme_default_primary_menu() {
	echo '<ul>';
	$items = array(
		__( 'Our Wines', 'newtheme' )   => home_url( '/collection/wines/' ),
		__( 'Our Story', 'newtheme' )   => home_url( '/estate' ),
		__( 'Wine Club', 'newtheme' )   => home_url( '/club' ),
		__( 'Visit', 'newtheme' )       => home_url( '/visit' ),
	);
	foreach ( $items as $label => $url ) {
		printf( '<li><a href="%s">%s</a></li>', esc_url( $url ), esc_html( $label ) );
	}
	echo '</ul>';
}
