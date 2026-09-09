<?php
/**
 * Events archive (/events) — upcoming events only, soonest-first (see
 * ForWineries\Core\Events's pre_get_posts filter).
 *
 * RENAME TOGETHER, ALWAYS: this filename's "newtheme_event" segment
 * must exactly match $config->post_type('event') — i.e. "{slug}_event"
 * — which in turn is set by functions.php's 'slug' config key. If you
 * rename the slug in functions.php, rename this file (and
 * single-newtheme_event.php) to match in the same commit, or WordPress
 * silently falls back to archive.php/single.php instead (no fatal — the
 * page still "works", it just loses the event-specific date/RSVP
 * markup, which is a much harder bug to notice than a fatal error).
 */
if ( ! defined( 'ABSPATH' ) ) exit;

use ForWineries\Core\Config;
use ForWineries\Core\Events;

$fw_config = new Config( newtheme_fw_config_array() );

get_header();
?>

<section class="fw-section">
	<div class="fw-container">
		<div class="fw-section-head">
			<span class="fw-eyebrow"><?php esc_html_e( 'Join Us', 'newtheme' ); ?></span>
			<h1><?php esc_html_e( 'Upcoming Events', 'newtheme' ); ?></h1>
			<p><?php esc_html_e( 'Harvest celebrations, barrel tastings, and evenings among the vines.', 'newtheme' ); ?></p>
		</div>

		<?php if ( have_posts() ) : ?>
			<div class="newtheme-blog-grid">
				<?php while ( have_posts() ) : the_post(); ?>
					<?php
					$newtheme_event_date = Events::date_display( get_the_ID() );
					$newtheme_event_time = get_post_meta( get_the_ID(), 'event_time', true );
					?>
					<article <?php post_class( 'newtheme-blog-card newtheme-event-card' ); ?>>
						<a class="newtheme-blog-card-media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
							<?php if ( has_post_thumbnail() ) : ?>
								<?php the_post_thumbnail( 'newtheme-wide' ); ?>
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
							<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
							<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p>
							<a class="fw-btn" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Details & RSVP', 'newtheme' ); ?></a>
						</div>
					</article>
				<?php endwhile; ?>
			</div>

			<div class="newtheme-pagination">
				<?php
				echo paginate_links( array(
					'prev_text' => esc_html__( '&larr; Newer', 'newtheme' ),
					'next_text' => esc_html__( 'Older &rarr;', 'newtheme' ),
				) );
				?>
			</div>
		<?php else : ?>
			<p><?php esc_html_e( 'No upcoming events right now — check back soon.', 'newtheme' ); ?></p>
		<?php endif; ?>
	</div>
</section>

<?php
get_footer();
