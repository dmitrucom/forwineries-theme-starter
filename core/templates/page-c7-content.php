<?php
// GENERATED FILE — do not edit directly. Synced from forwineries-theme-core.
// Edit the source there and re-run tools/sync-core.sh.
/**
 * Commerce7 — Shop Content. A product theme's own page-templates/
 * page-c7-content.php stub sets the "Template Name:" header, then
 * requires this file with $fw_config already in scope — the same
 * config array Boot::init() was called with.
 *
 * Used by the auto-created Wines / Wine / Cart / My Account / Wine
 * Clubs / Reservations pages (Commerce7\Routing::route_pages()).
 * Commerce7's storefront JS reads the current URL and renders the right
 * thing into #c7-content — this template just provides that mount
 * point inside the site's normal header/footer.
 *
 * This template does NOT call the_content() — Commerce7 owns everything
 * inside #c7-content via client-side URL routing, so there's
 * deliberately no Elementor-defer check here.
 *
 * Route-specific hero images (wines/reservation/club/profile) come from
 * $config->get('pages.c7_content.{route}.image') — per-theme asset
 * paths, no sensible core default. Reservation/club/profile hero COPY
 * (eyebrow/heading/intro) comes from ContentSettings\Framework's own
 * per-route fields (reservation_page_eyebrow and siblings), exactly as
 * before. The wines-route hero never went through ContentSettings in
 * the original themes either — kept that way here rather than silently
 * changing which fields control it.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

use ForWineries\Core\Config;
use ForWineries\Core\ContentSettings\Framework;
use ForWineries\Core\Commerce7\Catalog;
use ForWineries\Core\Commerce7\Integration;
use ForWineries\Core\Commerce7\ReservationWidget;

$fw_config = new Config( isset( $fw_config ) ? $fw_config : array() );

global $wp;
$fw_request               = isset( $wp->request ) ? trim( $wp->request, '/' ) : '';
$fw_is_wines_route        = $fw_request === 'collection/wines';
$fw_is_product_route      = strpos( $fw_request, 'product/' ) === 0;
$fw_is_reservation_route  = $fw_request === 'reservation';
$fw_is_club_route         = $fw_request === 'club';
$fw_is_profile_route      = $fw_request === 'profile';
$fw_wines_catalog_html    = '';
$fw_reservation_widget_html = $fw_is_reservation_route ? ReservationWidget::render( $fw_config ) : '';
$fw_club_tiers            = $fw_is_club_route ? Framework::field_repeater( $fw_config, 'club_tiers' ) : array();

// Pre-check for actual product data rather than trusting
// render_wines_catalog() to always succeed — a REST hiccup should fall
// back to the reliable official widget for real visitors, not show a
// blank admin-only notice. fetch_all_wines() is transient-cached, so
// this costs nothing extra beyond the first request of every cache
// window. fetch_all_wines() returns a WP_Error object on API failure,
// NOT an empty array — empty() on an object is always false in PHP
// regardless of contents, so this must check is_array() explicitly.
if ( $fw_is_wines_route && Integration::rest_ready( $fw_config ) ) {
	$fw_wines = Catalog::fetch_all_wines( $fw_config );
	if ( is_array( $fw_wines ) && ! empty( $fw_wines ) ) {
		$fw_wines_catalog_html = Catalog::render_wines_catalog( $fw_config );
	}
}

get_header();
?>

<?php if ( $fw_is_wines_route ) :
	$fw_wines_hero = $fw_config->get( 'pages.c7_content.wines', array() );
	?>
	<section class="fw-hero fw-hero--compact ph-img ph-stone" style="background-image:url('<?php echo esc_url( isset( $fw_wines_hero['image'] ) ? $fw_wines_hero['image'] : '' ); ?>');">
		<div class="fw-hero-content">
			<span class="fw-eyebrow"><?php echo esc_html( isset( $fw_wines_hero['eyebrow'] ) ? $fw_wines_hero['eyebrow'] : __( 'Our Collection', $fw_config->text_domain() ) ); ?></span>
			<h1><?php echo esc_html( isset( $fw_wines_hero['heading'] ) ? $fw_wines_hero['heading'] : __( 'All Wines', $fw_config->text_domain() ) ); ?></h1>
			<p><?php echo esc_html( isset( $fw_wines_hero['intro'] ) ? $fw_wines_hero['intro'] : __( 'Filter by type or varietal, or sort to find exactly what you\'re after.', $fw_config->text_domain() ) ); ?></p>
		</div>
	</section>
<?php endif; ?>

<?php if ( $fw_is_reservation_route ) :
	$fw_hero_image = $fw_config->get( 'pages.c7_content.reservation.image', '' );
	?>
	<section class="fw-hero fw-hero--compact ph-img" style="background-image:url('<?php echo esc_url( $fw_hero_image ); ?>');">
		<div class="fw-hero-content">
			<span class="fw-eyebrow"><?php echo esc_html( Framework::field( $fw_config, 'reservation_page_eyebrow' ) ); ?></span>
			<h1><?php echo esc_html( Framework::field( $fw_config, 'reservation_page_heading' ) ); ?></h1>
			<p><?php echo esc_html( Framework::field( $fw_config, 'reservation_page_intro' ) ); ?></p>
		</div>
	</section>
<?php endif; ?>

<?php if ( $fw_is_club_route ) :
	$fw_hero_image = $fw_config->get( 'pages.c7_content.club.image', '' );
	?>
	<section class="fw-hero fw-hero--compact ph-img" style="background-image:url('<?php echo esc_url( $fw_hero_image ); ?>');">
		<div class="fw-hero-content">
			<span class="fw-eyebrow"><?php echo esc_html( Framework::field( $fw_config, 'club_page_eyebrow' ) ); ?></span>
			<h1><?php echo esc_html( Framework::field( $fw_config, 'club_page_heading' ) ); ?></h1>
			<p><?php echo esc_html( Framework::field( $fw_config, 'club_page_intro' ) ); ?></p>
		</div>
	</section>
<?php endif; ?>

<?php if ( $fw_is_profile_route ) :
	$fw_hero_image = $fw_config->get( 'pages.c7_content.profile.image', '' );
	?>
	<section class="fw-hero fw-hero--compact ph-img" style="background-image:url('<?php echo esc_url( $fw_hero_image ); ?>');">
		<div class="fw-hero-content">
			<span class="fw-eyebrow"><?php echo esc_html( Framework::field( $fw_config, 'profile_page_eyebrow' ) ); ?></span>
			<h1><?php echo esc_html( Framework::field( $fw_config, 'profile_page_heading' ) ); ?></h1>
			<p><?php echo esc_html( Framework::field( $fw_config, 'profile_page_intro' ) ); ?></p>
		</div>
	</section>
<?php endif; ?>

<div class="fw-section fw-c7-page<?php echo $fw_is_product_route ? ' fw-c7-page--product' : ''; ?>">
	<div class="fw-container">
		<?php if ( $fw_is_product_route ) : ?>
			<p class="fw-back-link">
				<a href="<?php echo esc_url( home_url( '/collection/wines/' ) ); ?>">&larr; <?php esc_html_e( 'Back to Wines', $fw_config->text_domain() ); ?></a>
			</p>
			<?php
			// Same manual slug: badge-text map as the Wines Catalog and
			// homepage teaser — doesn't need REST, it's a theme option
			// keyed by slug, so it works here even without Commerce7
			// REST credentials configured. Commerce7 owns everything
			// inside #c7-content below, so this renders outside the
			// mount rather than trying to inject into markup Commerce7's
			// own JS controls.
			$fw_current_product_slug = Catalog::get_product_slug_from_request();
			$fw_product_badges       = Catalog::wine_badges( $fw_config );
			$fw_product_badge        = isset( $fw_product_badges[ $fw_current_product_slug ] ) ? $fw_product_badges[ $fw_current_product_slug ] : '';
			if ( $fw_product_badge ) :
				?>
				<span class="fw-wine-scarcity-badge fw-wine-scarcity-badge--standalone"><?php echo esc_html( $fw_product_badge ); ?></span>
			<?php endif; ?>
		<?php endif; ?>

		<?php if ( $fw_wines_catalog_html ) : ?>
			<?php echo $fw_wines_catalog_html; ?>
		<?php elseif ( $fw_reservation_widget_html ) : ?>
			<div class="fw-reservation-widget-embed fw-reservation-widget-embed--page"><?php echo $fw_reservation_widget_html; ?></div>
		<?php elseif ( $fw_club_tiers ) : ?>
			<?php echo Integration::render_membership_tiers( $fw_config, array( 'tiers' => $fw_club_tiers ) ); ?>
		<?php else : ?>
			<div id="c7-content"></div>
		<?php endif; ?>
	</div>
</div>

<?php if ( $fw_is_product_route ) :
	// Theme-rendered, sitting OUTSIDE #c7-content (Commerce7's own JS
	// only ever renders what's directly under that ID) — needs to
	// exclude the wine currently being viewed, which the Commerce7
	// product template has no way to know. Prints nothing if REST
	// credentials aren't configured or nothing's left to show.
	$fw_related_wines_html = Catalog::render_related_wines( $fw_config, Catalog::get_product_slug_from_request() );
	if ( $fw_related_wines_html ) :
		?>
		<div class="fw-section fw-section--cream fw-related-wines-wrap">
			<div class="fw-container">
				<?php echo $fw_related_wines_html; ?>
			</div>
		</div>
	<?php endif; endif; ?>

<?php
get_footer();
