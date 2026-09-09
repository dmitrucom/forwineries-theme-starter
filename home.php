<?php
/**
 * Blog index (the site's designated Posts Page — Settings > Reading,
 * auto-set to the "Journal" page by ForWineries\Core\PageSetup).
 *
 * Genuinely separate from archive.php in WordPress's own template
 * hierarchy: home.php (falling back to index.php if absent) is what
 * renders the Posts Page specifically, NOT archive.php — archive.php
 * only ever fires for category/tag/date/author archives. Without this
 * file, a standalone theme with no parent falls through to a bare,
 * completely unstyled "Archives" list. See docs/LESSONS.md.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

get_header();
?>

<section class="fw-section">
	<div class="fw-container">
		<div class="fw-section-head">
			<span class="fw-eyebrow"><?php esc_html_e( 'From the Journal', 'newtheme' ); ?></span>
			<h1><?php esc_html_e( 'Journal', 'newtheme' ); ?></h1>
			<p><?php esc_html_e( 'Notes from the vineyard, the cellar, and the tasting room.', 'newtheme' ); ?></p>
		</div>

		<?php if ( have_posts() ) : ?>
			<div class="newtheme-blog-grid">
				<?php while ( have_posts() ) : the_post(); ?>
					<article <?php post_class( 'newtheme-blog-card' ); ?>>
						<a class="newtheme-blog-card-media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
							<?php if ( has_post_thumbnail() ) : ?>
								<?php the_post_thumbnail( 'newtheme-wide' ); ?>
							<?php else : ?>
								<div class="ph-img ph-vineyard newtheme-blog-card-media-ph"><span class="ph-tag">journal-fallback — 1600x900</span></div>
							<?php endif; ?>
						</a>
						<div class="newtheme-blog-card-body">
							<span class="newtheme-blog-meta"><?php echo esc_html( get_the_date() ); ?></span>
							<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
							<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p>
							<a class="fw-btn" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Read More', 'newtheme' ); ?></a>
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
			<p><?php esc_html_e( 'No posts yet — check back soon.', 'newtheme' ); ?></p>
		<?php endif; ?>
	</div>
</section>

<?php
get_footer();
