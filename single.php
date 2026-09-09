<?php
/**
 * Single blog post. Clean single-column editorial layout — no sidebar,
 * no widget system.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>

	<article <?php post_class(); ?>>
		<?php if ( has_post_thumbnail() ) : ?>
			<div class="newtheme-post-hero">
				<?php the_post_thumbnail( 'newtheme-wide' ); ?>
			</div>
		<?php endif; ?>

		<section class="fw-section">
			<div class="fw-container newtheme-post-container">
				<?php
				$newtheme_journal_breadcrumb_url = get_option( 'page_for_posts' ) ? get_permalink( get_option( 'page_for_posts' ) ) : home_url( '/journal' );
				newtheme_render_breadcrumbs( array( array( __( 'Journal', 'newtheme' ), $newtheme_journal_breadcrumb_url ), array( get_the_title(), '' ) ) );
				?>
				<header class="newtheme-post-header">
					<span class="fw-eyebrow"><?php echo esc_html( get_the_date() ); ?></span>
					<h1><?php the_title(); ?></h1>
					<p>
						<?php
						printf(
							/* translators: %s: author display name */
							esc_html__( 'By %s', 'newtheme' ),
							esc_html( get_the_author() )
						);
						?>
					</p>
				</header>

				<div class="newtheme-post-content">
					<?php the_content(); ?>
				</div>

				<?php
				wp_link_pages( array(
					'before' => '<nav aria-label="' . esc_attr__( 'Post pages', 'newtheme' ) . '"><p>',
					'after'  => '</p></nav>',
				) );
				?>

				<footer>
					<?php
					$newtheme_categories = get_the_category_list( ', ' );
					if ( $newtheme_categories ) :
						?>
						<p><?php esc_html_e( 'Filed under:', 'newtheme' ); ?> <?php echo wp_kses_post( $newtheme_categories ); ?></p>
					<?php endif; ?>

					<nav class="newtheme-post-nav" aria-label="<?php esc_attr_e( 'More posts', 'newtheme' ); ?>">
						<?php
						$newtheme_prev = get_previous_post_link( '%link', '&larr; ' . esc_html__( 'Older Post', 'newtheme' ) );
						$newtheme_next = get_next_post_link( '%link', esc_html__( 'Newer Post', 'newtheme' ) . ' &rarr;' );
						if ( $newtheme_prev ) echo '<div>' . $newtheme_prev . '</div>';
						if ( $newtheme_next ) echo '<div>' . $newtheme_next . '</div>';
						?>
					</nav>

					<?php $newtheme_journal_url = get_option( 'page_for_posts' ) ? get_permalink( get_option( 'page_for_posts' ) ) : home_url( '/journal' ); ?>
					<div class="newtheme-post-back">
						<a class="fw-btn" href="<?php echo esc_url( $newtheme_journal_url ); ?>"><?php esc_html_e( 'Back to the Journal', 'newtheme' ); ?></a>
					</div>
				</footer>

				<?php if ( comments_open() || get_comments_number() ) : ?>
					<div class="newtheme-post-comments">
						<?php comments_template(); ?>
					</div>
				<?php endif; ?>
			</div>
		</section>
	</article>

<?php
endwhile;

get_footer();
