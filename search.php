<?php
/**
 * Search results — covers Pages, Journal posts, and Events (WordPress's
 * own default search scope includes any public post type with
 * exclude_from_search left at default false; the newtheme_event CPT
 * never opts out, so it's included automatically). Wines/products are
 * NOT included: they live in Commerce7, not WordPress — the Wines
 * Catalog's own search (/collection/wines/) is the right tool for that.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

get_header();
?>

<section class="fw-section">
	<div class="fw-container">
		<div class="fw-section-head">
			<span class="fw-eyebrow"><?php esc_html_e( 'Search Results', 'newtheme' ); ?></span>
			<h1>
				<?php
				printf(
					/* translators: %s: search query */
					esc_html__( 'Results for "%s"', 'newtheme' ),
					esc_html( get_search_query() )
				);
				?>
			</h1>
		</div>

		<div class="newtheme-search-form-wrap">
			<?php get_search_form(); ?>
		</div>

		<?php if ( have_posts() ) : ?>
			<div class="newtheme-blog-grid">
				<?php while ( have_posts() ) : the_post(); ?>
					<?php
					$newtheme_result_type_labels = array(
						'post'           => __( 'Journal', 'newtheme' ),
						'page'           => __( 'Page', 'newtheme' ),
						'newtheme_event' => __( 'Event', 'newtheme' ),
					);
					$newtheme_result_type = isset( $newtheme_result_type_labels[ get_post_type() ] ) ? $newtheme_result_type_labels[ get_post_type() ] : ucfirst( get_post_type() );
					?>
					<article <?php post_class( 'newtheme-blog-card' ); ?>>
						<a class="newtheme-blog-card-media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
							<?php if ( has_post_thumbnail() ) : ?>
								<?php the_post_thumbnail( 'newtheme-wide' ); ?>
							<?php else : ?>
								<div class="ph-img ph-vineyard newtheme-blog-card-media-ph"><span class="ph-tag">journal-fallback — 1600x900</span></div>
							<?php endif; ?>
						</a>
						<div class="newtheme-blog-card-body">
							<span class="newtheme-blog-meta"><?php echo esc_html( $newtheme_result_type ); ?></span>
							<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
							<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p>
							<a class="fw-btn" href="<?php the_permalink(); ?>"><?php esc_html_e( 'View', 'newtheme' ); ?></a>
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
			<p><?php esc_html_e( 'Nothing matched that search. Try a different term, or browse our wines instead.', 'newtheme' ); ?></p>
			<a class="fw-btn fw-btn--solid" href="<?php echo esc_url( home_url( '/collection/wines/' ) ); ?>"><?php esc_html_e( 'Browse Our Wines', 'newtheme' ); ?></a>
		<?php endif; ?>
	</div>
</section>

<?php
get_footer();
