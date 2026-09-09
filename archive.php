<?php
/**
 * Category, tag, author, and date archives (all fall back to this file
 * since none of category.php/tag.php/author.php/date.php exist). Same
 * .fw-section/.fw-container shell as every other page.
 *
 * NOT used for the main blog index (the "Journal" page) — that's
 * home.php, a genuinely separate template in WP's own hierarchy.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

get_header();
?>

<section class="fw-section">
	<div class="fw-container">
		<div class="fw-section-head">
			<span class="fw-eyebrow"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span>
			<h1><?php echo wp_kses_post( get_the_archive_title() ); ?></h1>
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
