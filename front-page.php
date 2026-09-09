<?php
/**
 * Homepage — starter skin. Real, working narrative: hero, wine grid,
 * story/stats, club CTA, events, reservation CTA, visit CTA. Placeholder
 * copy throughout (inc/content-fields.php) — replace it, don't just
 * restyle around it.
 *
 * Every selector this page's own assets/js/theme.js targets
 * (.fw-hero-content, .fw-stat-num, [data-reveal]) is written here using
 * the correct, final, unprefixed fw-* name from the start — there is no
 * rename pass to get wrong. This is the exact bug class documented in
 * forwineries-theme-core/docs/LESSONS.md (Vespera's theme.js querying
 * 4 stale `.vsp-*` selectors after a rename, leaving hero text invisible
 * on 3 live pages for weeks): a starter theme that's authored fresh
 * against final names can't reproduce it, but only if it stays
 * disciplined about that on every future edit too.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

use ForWineries\Core\Config;
use ForWineries\Core\ContentSettings\Framework;
use ForWineries\Core\Commerce7\Catalog;
use ForWineries\Core\Commerce7\ReservationWidget;
use ForWineries\Core\ContactSettings;
use ForWineries\Core\Events;

$fw_config = new Config( newtheme_fw_config_array() );

get_header();

if ( newtheme_front_page_is_elementor_built() ) :
	while ( have_posts() ) : the_post();
		the_content();
	endwhile;
else :
	?>

	<section id="newtheme-home-top" class="fw-hero fw-hero--full ph-img ph-hero" style="background-image:url('<?php echo esc_url( newtheme_hero_image() ); ?>');">
		<?php if ( ! newtheme_hero_image() ) : ?><span class="ph-tag">vineyard-at-golden-hour — 1920x1200</span><?php endif; ?>
		<div class="fw-hero-content fw-hero-content--centered" data-reveal>
			<h1><?php echo esc_html( Framework::field( $fw_config, 'hero_heading' ) ); ?></h1>
			<p><?php echo esc_html( Framework::field( $fw_config, 'hero_subtext' ) ); ?></p>
			<div class="fw-btn-row">
				<a class="fw-btn fw-btn--solid" href="<?php echo esc_url( home_url( '/collection/wines/' ) ); ?>"><?php echo esc_html( Framework::field( $fw_config, 'hero_btn_primary_label' ) ); ?></a>
				<a class="fw-btn fw-btn--accent" href="<?php echo esc_url( get_option( $fw_config->option_key( 'c7_reservation_url' ), home_url( '/reservation' ) ) ); ?>"><?php echo esc_html( Framework::field( $fw_config, 'hero_btn_secondary_label' ) ); ?></a>
			</div>
		</div>
	</section>

	<section id="newtheme-home-wines" class="fw-section">
		<div class="fw-container">
			<div class="fw-section-head" data-reveal>
				<span class="fw-eyebrow"><?php echo esc_html( Framework::field( $fw_config, 'wines_eyebrow' ) ); ?></span>
				<h2><?php echo esc_html( Framework::field( $fw_config, 'wines_heading' ) ); ?></h2>
				<p><?php echo esc_html( Framework::field( $fw_config, 'wines_description' ) ); ?></p>
			</div>

			<?php echo Catalog::render_wines( $fw_config, array( 'limit' => Framework::field( $fw_config, 'wines_limit', '6' ) ) ); ?>

			<div class="fw-section-cta">
				<a class="fw-btn" href="<?php echo esc_url( home_url( '/collection/wines/' ) ); ?>"><?php echo esc_html( Framework::field( $fw_config, 'wines_btn_label' ) ); ?></a>
			</div>
		</div>
	</section>

	<section id="newtheme-home-story" class="fw-section fw-section--cream">
		<div class="fw-container">
			<div class="fw-split">
				<div class="fw-split-media ph-img ph-vineyard" data-reveal>
					<span class="ph-tag">estate-landscape-wide — 1200x900</span>
				</div>
				<div class="fw-split-text" data-reveal data-reveal-delay="0.1">
					<span class="fw-eyebrow"><?php echo esc_html( Framework::field( $fw_config, 'story_eyebrow' ) ); ?></span>
					<h2><?php echo esc_html( Framework::field( $fw_config, 'story_heading' ) ); ?></h2>
					<p><?php echo esc_html( Framework::field( $fw_config, 'story_paragraph_1' ) ); ?></p>
					<p><?php echo esc_html( Framework::field( $fw_config, 'story_paragraph_2' ) ); ?></p>
					<a class="fw-btn" href="<?php echo esc_url( home_url( '/estate' ) ); ?>"><?php echo esc_html( Framework::field( $fw_config, 'story_btn_label' ) ); ?></a>
				</div>
			</div>

			<div class="fw-stats-row" style="margin-top: var(--fw-space-xl);" data-reveal data-reveal-delay="0.15">
				<div>
					<span class="fw-stat-num"><?php echo esc_html( Framework::field( $fw_config, 'stat1_number' ) ); ?></span>
					<span class="fw-stat-label"><?php echo esc_html( Framework::field( $fw_config, 'stat1_label' ) ); ?></span>
				</div>
				<div>
					<span class="fw-stat-num"><?php echo esc_html( Framework::field( $fw_config, 'stat2_number' ) ); ?></span>
					<span class="fw-stat-label"><?php echo esc_html( Framework::field( $fw_config, 'stat2_label' ) ); ?></span>
				</div>
				<div>
					<span class="fw-stat-num"><?php echo esc_html( Framework::field( $fw_config, 'stat3_number' ) ); ?></span>
					<span class="fw-stat-label"><?php echo esc_html( Framework::field( $fw_config, 'stat3_label' ) ); ?></span>
				</div>
			</div>
		</div>
	</section>

	<section id="newtheme-home-club" class="fw-section">
		<div class="fw-container">
			<div class="fw-section-head" data-reveal>
				<span class="fw-eyebrow"><?php echo esc_html( Framework::field( $fw_config, 'club_eyebrow' ) ); ?></span>
				<h2><?php echo esc_html( Framework::field( $fw_config, 'club_heading' ) ); ?></h2>
				<p><?php echo esc_html( Framework::field( $fw_config, 'club_paragraph' ) ); ?></p>
				<div class="fw-btn-row" style="justify-content:center;">
					<?php echo do_shortcode( '[c7_club_join]' ); ?>
				</div>
			</div>
		</div>
	</section>

	<?php $newtheme_upcoming_events = Events::upcoming( $fw_config, 3 ); ?>
	<?php if ( $newtheme_upcoming_events ) : ?>
		<section id="newtheme-home-events" class="fw-section fw-section--cream">
			<div class="fw-container">
				<div class="fw-section-head" data-reveal>
					<span class="fw-eyebrow"><?php esc_html_e( 'Join Us', 'newtheme' ); ?></span>
					<h2><?php esc_html_e( 'Upcoming Events', 'newtheme' ); ?></h2>
				</div>

				<div class="newtheme-blog-grid">
					<?php foreach ( $newtheme_upcoming_events as $newtheme_event_i => $newtheme_event ) : ?>
						<?php
						$newtheme_event_date = Events::date_display( $newtheme_event->ID );
						$newtheme_event_time = get_post_meta( $newtheme_event->ID, 'event_time', true );
						?>
						<article class="newtheme-blog-card newtheme-event-card" data-reveal data-reveal-delay="<?php echo esc_attr( $newtheme_event_i * 0.1 ); ?>">
							<a class="newtheme-blog-card-media" href="<?php echo esc_url( get_permalink( $newtheme_event ) ); ?>" tabindex="-1" aria-hidden="true">
								<?php if ( has_post_thumbnail( $newtheme_event ) ) : ?>
									<?php echo get_the_post_thumbnail( $newtheme_event, 'newtheme-wide' ); ?>
								<?php else : ?>
									<div class="ph-img ph-vineyard newtheme-blog-card-media-ph"><span class="ph-tag">event-photo — 1600x900</span></div>
								<?php endif; ?>
								<?php if ( $newtheme_event_date ) : ?>
									<span class="newtheme-event-date-badge"><?php echo esc_html( $newtheme_event_date ); ?></span>
								<?php endif; ?>
							</a>
							<div class="newtheme-blog-card-body">
								<?php if ( $newtheme_event_time ) : ?>
									<span class="newtheme-blog-meta"><?php echo esc_html( $newtheme_event_time ); ?></span>
								<?php endif; ?>
								<h2><a href="<?php echo esc_url( get_permalink( $newtheme_event ) ); ?>"><?php echo esc_html( get_the_title( $newtheme_event ) ); ?></a></h2>
								<a class="fw-btn" href="<?php echo esc_url( get_permalink( $newtheme_event ) ); ?>"><?php esc_html_e( 'Details & RSVP', 'newtheme' ); ?></a>
							</div>
						</article>
					<?php endforeach; ?>
				</div>

				<div class="fw-section-cta">
					<a class="fw-btn" href="<?php echo esc_url( home_url( '/events' ) ); ?>"><?php esc_html_e( 'See All Events', 'newtheme' ); ?></a>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php $newtheme_reservation_widget_html = ReservationWidget::render( $fw_config ); ?>
	<section id="newtheme-home-reserve" class="fw-hero fw-hero--band ph-img ph-club" style="background-image:url('<?php echo esc_url( newtheme_reservation_image() ); ?>');">
		<?php if ( ! newtheme_reservation_image() ) : ?><span class="ph-tag">tasting-room-interior — 1600x900</span><?php endif; ?>
		<div class="fw-hero-content" data-reveal>
			<span class="fw-eyebrow"><?php echo esc_html( Framework::field( $fw_config, 'reservation_eyebrow' ) ); ?></span>
			<h2><?php echo esc_html( Framework::field( $fw_config, 'reservation_heading' ) ); ?></h2>
			<p><?php echo esc_html( Framework::field( $fw_config, 'reservation_paragraph' ) ); ?></p>
			<?php if ( $newtheme_reservation_widget_html ) : ?>
				<div class="fw-reservation-widget-embed"><?php echo $newtheme_reservation_widget_html; ?></div>
			<?php else : ?>
				<div class="fw-btn-row">
					<a class="fw-btn fw-btn--solid" href="<?php echo esc_url( get_option( $fw_config->option_key( 'c7_reservation_url' ), home_url( '/reservation' ) ) ); ?>"><?php echo esc_html( Framework::field( $fw_config, 'reservation_btn_label' ) ); ?></a>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<section id="newtheme-home-visit" class="fw-section">
		<div class="fw-container">
			<div class="fw-section-head" data-reveal>
				<span class="fw-eyebrow"><?php echo esc_html( Framework::field( $fw_config, 'visit_eyebrow' ) ); ?></span>
				<h2><?php echo esc_html( Framework::field( $fw_config, 'visit_heading' ) ); ?></h2>
				<p>
					<?php echo esc_html( ContactSettings::get( $fw_config, 'address_line' ) ); ?><br>
					<?php echo esc_html( Framework::field( $fw_config, 'visit_hours' ) ); ?>
				</p>
				<div class="fw-btn-row" style="justify-content:center;">
					<a class="fw-btn fw-btn--solid" href="<?php echo esc_url( get_option( $fw_config->option_key( 'c7_reservation_url' ), home_url( '/reservation' ) ) ); ?>"><?php echo esc_html( Framework::field( $fw_config, 'visit_reserve_btn_label' ) ); ?></a>
					<a class="fw-btn" href="<?php echo esc_url( home_url( '/visit' ) ); ?>"><?php echo esc_html( Framework::field( $fw_config, 'visit_btn_label' ) ); ?></a>
				</div>
			</div>
		</div>
	</section>

<?php
endif;

get_footer();

/**
 * Homepage hero/reservation-band images — per-theme asset paths, no
 * sensible generic default (same reasoning as $config->get('pages.*')).
 * Kept as small helpers here (rather than inline $fw_config->get() calls)
 * only so the "is this blank -> show the ph-tag label" check above stays
 * readable; feel free to inline these once you've set real config values.
 */
function newtheme_hero_image() {
	return ( new Config( newtheme_fw_config_array() ) )->get( 'pages.home.hero', '' );
}
function newtheme_reservation_image() {
	return ( new Config( newtheme_fw_config_array() ) )->get( 'pages.home.reservation', '' );
}
