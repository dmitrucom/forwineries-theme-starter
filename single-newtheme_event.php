<?php
/**
 * Single event page (/events/{slug}). Hero-style header with date/time +
 * RSVP button, then the_content() for the event description.
 *
 * RENAME TOGETHER, ALWAYS — see the note at the top of
 * archive-newtheme_event.php: this filename's "newtheme_event" segment
 * must always match $config->post_type('event').
 */
if ( ! defined( 'ABSPATH' ) ) exit;

use ForWineries\Core\Config;
use ForWineries\Core\Events;
use ForWineries\Core\Schema;

$fw_config = new Config( newtheme_fw_config_array() );

get_header();

while ( have_posts() ) :
	the_post();

	$newtheme_event_date  = Events::date_display( get_the_ID() );
	$newtheme_event_time  = get_post_meta( get_the_ID(), 'event_time', true );
	$newtheme_event_rsvp  = Events::rsvp_url( $fw_config, get_the_ID() );
	$newtheme_event_when  = trim( $newtheme_event_date . ( $newtheme_event_time ? ' · ' . $newtheme_event_time : '' ), ' ·' );
	$newtheme_event_image = has_post_thumbnail() ? get_the_post_thumbnail_url( get_the_ID(), 'full' ) : '';
	echo Schema::event_jsonld( $fw_config, get_the_ID() );
	?>

	<section class="fw-hero fw-hero--compact ph-img ph-club" style="background-image:url('<?php echo esc_url( $newtheme_event_image ); ?>');">
		<?php if ( ! $newtheme_event_image ) : ?><span class="ph-tag">event-photo — 1600x900</span><?php endif; ?>
		<div class="fw-hero-content">
			<?php if ( $newtheme_event_when ) : ?>
				<span class="fw-eyebrow"><?php echo esc_html( $newtheme_event_when ); ?></span>
			<?php endif; ?>
			<h1><?php the_title(); ?></h1>
			<div class="fw-btn-row">
				<a class="fw-btn fw-btn--accent" href="<?php echo esc_url( $newtheme_event_rsvp ); ?>"><?php esc_html_e( 'RSVP', 'newtheme' ); ?></a>
			</div>
		</div>
	</section>

	<section class="fw-section">
		<div class="fw-container newtheme-post-container">
			<?php
			newtheme_render_breadcrumbs( array( array( __( 'Events', 'newtheme' ), home_url( '/events' ) ), array( get_the_title(), '' ) ) );
			?>
			<?php the_content(); ?>
			<p class="fw-back-link">
				<a href="<?php echo esc_url( home_url( '/events' ) ); ?>">&larr; <?php esc_html_e( 'Back to Events', 'newtheme' ); ?></a>
			</p>
		</div>
	</section>

<?php
endwhile;

get_footer();
