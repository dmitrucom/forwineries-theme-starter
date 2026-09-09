<?php
/**
 * Required fallback template for a standalone theme — WordPress requires
 * every theme to have its own index.php (or a block-theme templates/
 * index.html); a Hello Elementor CHILD theme was exempt because it
 * silently inherited the parent's, but this theme is standalone (see
 * functions.php's file header) so there is no parent to inherit from.
 * Confirmed the hard way during Larkhaven's Phase 1 migration: `wp theme
 * activate` refused with "Template is missing" the moment a theme in
 * this family stopped declaring `Template: hello-elementor` without
 * this file existing — see forwineries-theme-core/docs/LESSONS.md.
 *
 * A theme this template-hierarchy-complete (front-page.php, home.php,
 * archive.php, single.php, search.php, 404.php, plus the event CPT
 * pair) will likely never actually route a real request through this
 * file — WordPress still requires it to exist as the structural
 * fallback.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

get_header();
?>

<section class="fw-section">
	<div class="fw-container">
		<?php if ( have_posts() ) : ?>
			<div class="newtheme-blog-grid">
				<?php while ( have_posts() ) : the_post(); ?>
					<article <?php post_class( 'newtheme-blog-card' ); ?>>
						<a class="newtheme-blog-card-media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
							<?php if ( has_post_thumbnail() ) : ?>
								<?php the_post_thumbnail( 'newtheme-wide' ); ?>
							<?php else : ?>
								<div class="ph-img ph-vineyard newtheme-blog-card-media-ph"><span class="ph-tag">blog-fallback — 1600x900</span></div>
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
		<?php else : ?>
			<p><?php esc_html_e( 'Nothing found.', 'newtheme' ); ?></p>
		<?php endif; ?>
	</div>
</section>

<?php
get_footer();
