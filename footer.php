<?php
/**
 * Custom footer — pairs with header.php; renders on every page/post with
 * zero Elementor Pro dependency.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

use ForWineries\Core\Config;
use ForWineries\Core\ContactSettings;
use ForWineries\Core\ContentSettings\Framework;

$fw_config = new Config( newtheme_fw_config_array() );
?>

</main>

	<footer id="newtheme-footer">
		<div class="fw-container">
			<div class="newtheme-footer-grid">
				<div>
					<h4><?php echo esc_html( $fw_config->brand_name() ); ?></h4>
					<p><?php echo esc_html( ContactSettings::get( $fw_config, 'address_line' ) ); ?></p>
					<p>
						<a href="tel:<?php echo esc_attr( ContactSettings::get( $fw_config, 'phone_tel' ) ); ?>"><?php echo esc_html( ContactSettings::get( $fw_config, 'phone_display' ) ); ?></a><br>
						<a href="mailto:<?php echo esc_attr( ContactSettings::get( $fw_config, 'email' ) ); ?>"><?php echo esc_html( ContactSettings::get( $fw_config, 'email' ) ); ?></a>
					</p>
					<?php
						// Hidden entirely (not a dead href="#") until a real URL is
						// set — [Your Winery Name] > Contact & Social.
						$newtheme_social_instagram = ContactSettings::get( $fw_config, 'social_instagram' );
						$newtheme_social_facebook  = ContactSettings::get( $fw_config, 'social_facebook' );
					?>
					<?php if ( $newtheme_social_instagram || $newtheme_social_facebook ) : ?>
						<div class="newtheme-social">
							<?php if ( $newtheme_social_instagram ) : ?>
								<a href="<?php echo esc_url( $newtheme_social_instagram ); ?>" aria-label="Instagram" target="_blank" rel="noopener noreferrer">
									<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true" focusable="false"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4.2"/><circle cx="17.2" cy="6.8" r="1.1" fill="currentColor" stroke="none"/></svg>
								</a>
							<?php endif; ?>
							<?php if ( $newtheme_social_facebook ) : ?>
								<a href="<?php echo esc_url( $newtheme_social_facebook ); ?>" aria-label="Facebook" target="_blank" rel="noopener noreferrer">
									<svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true" focusable="false"><path d="M13.5 21v-7h2.6l.5-3h-3.1V9.1c0-.9.3-1.6 1.7-1.6h1.5V4.8c-.3 0-1.2-.1-2.2-.1-2.2 0-3.7 1.3-3.7 3.8V11H8.2v3h2.6v7h2.7z"/></svg>
								</a>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</div>

				<div>
					<h4><?php esc_html_e( 'Explore', 'newtheme' ); ?></h4>
					<?php
					if ( has_nav_menu( $fw_config->nav_location( 'footer' ) ) ) {
						wp_nav_menu( array(
							'theme_location' => $fw_config->nav_location( 'footer' ),
							'container'      => false,
							'menu_class'     => '',
							'items_wrap'     => '<ul>%3$s</ul>',
						) );
					} elseif ( is_active_sidebar( 'newtheme-footer-1' ) ) {
						dynamic_sidebar( 'newtheme-footer-1' );
					} else {
						echo '<ul><li><a href="' . esc_url( home_url( '/collection/wines/' ) ) . '">' . esc_html__( 'Our Wines', 'newtheme' ) . '</a></li><li><a href="' . esc_url( home_url( '/estate' ) ) . '">' . esc_html__( 'Our Story', 'newtheme' ) . '</a></li><li><a href="' . esc_url( home_url( '/team' ) ) . '">' . esc_html__( 'Our Team', 'newtheme' ) . '</a></li><li><a href="' . esc_url( home_url( '/club' ) ) . '">' . esc_html__( 'Wine Club', 'newtheme' ) . '</a></li><li><a href="' . esc_url( home_url( '/gift-cards' ) ) . '">' . esc_html__( 'Gift Cards', 'newtheme' ) . '</a></li><li><a href="' . esc_url( home_url( '/visit' ) ) . '">' . esc_html__( 'Visit', 'newtheme' ) . '</a></li><li><a href="' . esc_url( home_url( '/trade-press' ) ) . '">' . esc_html__( 'Trade & Press', 'newtheme' ) . '</a></li></ul>';
					}
					?>
				</div>

				<div>
					<h4><?php esc_html_e( 'Visit', 'newtheme' ); ?></h4>
					<?php if ( is_active_sidebar( 'newtheme-footer-2' ) ) : ?>
						<?php dynamic_sidebar( 'newtheme-footer-2' ); ?>
					<?php else : ?>
						<ul>
							<?php
							/**
							 * Deliberately NO third argument here — Framework::field()'s
							 * $fallback parameter checks BEFORE the theme's own
							 * registered content_fields default, so passing one here
							 * would silently and permanently override
							 * inc/content-fields.php's real 'visit_hours' default with
							 * this call site's own text instead. Real bug, found for
							 * real during this starter's own smoke test — see
							 * forwineries-theme-core/docs/LESSONS.md's identical
							 * AgeGate finding ("Albariza House" migration) for the full
							 * story of why this parameter is a trap.
							 */
							?>
							<li><?php echo esc_html( Framework::field( $fw_config, 'visit_hours' ) ); ?></li>
							<li><a href="<?php echo esc_url( get_option( $fw_config->option_key( 'c7_reservation_url' ), home_url( '/reservation' ) ) ); ?>"><?php esc_html_e( 'Reserve a Tasting', 'newtheme' ); ?></a></li>
						</ul>
					<?php endif; ?>
				</div>

				<div class="newtheme-footer-newsletter">
					<h4><?php esc_html_e( 'Stay in the Loop', 'newtheme' ); ?></h4>
					<p><?php esc_html_e( 'New releases, harvest updates, and event invitations — replace this placeholder line.', 'newtheme' ); ?></p>
					<?php echo do_shortcode( '[c7_subscribe]' ); ?>
				</div>
			</div>

			<div class="newtheme-bottom-bar">
				<span>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( $fw_config->brand_name() ); ?>. <?php esc_html_e( 'All rights reserved.', 'newtheme' ); ?></span>
				<nav class="newtheme-legal-links" aria-label="<?php esc_attr_e( 'Legal', 'newtheme' ); ?>">
					<a href="<?php echo esc_url( home_url( '/privacy' ) ); ?>"><?php esc_html_e( 'Privacy Policy', 'newtheme' ); ?></a>
					<a href="<?php echo esc_url( home_url( '/terms' ) ); ?>"><?php esc_html_e( 'Terms of Sale', 'newtheme' ); ?></a>
					<a href="<?php echo esc_url( home_url( '/returns' ) ); ?>"><?php esc_html_e( 'Returns', 'newtheme' ); ?></a>
					<a href="<?php echo esc_url( home_url( '/shipping' ) ); ?>"><?php esc_html_e( 'Shipping', 'newtheme' ); ?></a>
					<a href="<?php echo esc_url( home_url( '/faq' ) ); ?>"><?php esc_html_e( 'FAQ', 'newtheme' ); ?></a>
					<a href="<?php echo esc_url( home_url( '/accessibility' ) ); ?>"><?php esc_html_e( 'Accessibility', 'newtheme' ); ?></a>
				</nav>
				<span><?php esc_html_e( 'Please enjoy responsibly.', 'newtheme' ); ?></span>
			</div>
			<div class="fw-credit">
				<a href="https://forwineries.com" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'A ForWineries.com Template', 'newtheme' ); ?></a>
			</div>
		</div>
	</footer>

<?php wp_footer(); ?>
</body>
</html>
