<?php
// GENERATED FILE — do not edit directly. Synced from forwineries-theme-core.
// Edit the source there and re-run tools/sync-core.sh.
/**
 * Trade & Press page. A product theme's own page-templates/
 * page-trade-press.php stub sets the "Template Name:" header, then
 * requires this file with $fw_config already in scope. Modeled directly
 * on page-contact.php (same markup/honeypot/nonce pattern) with two
 * extra fields a restaurant buyer or journalist actually needs. Posts
 * to admin-post.php?action=fw_trade_submit, handled by ContactForms.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

use ForWineries\Core\Config;
use ForWineries\Core\ElementorDefer;
use ForWineries\Core\ContentSettings\Framework;
use ForWineries\Core\ContactSettings;

$fw_config = new Config( isset( $fw_config ) ? $fw_config : array() );

get_header();

if ( ElementorDefer::is_built( get_the_ID() ) ) :
	while ( have_posts() ) : the_post();
		the_content();
	endwhile;
else :
	$fw_status = isset( $_GET['fw_trade'] ) ? sanitize_key( $_GET['fw_trade'] ) : '';
	?>

	<section class="fw-section">
		<div class="fw-container">
			<div class="fw-section-head">
				<span class="fw-eyebrow"><?php echo esc_html( Framework::field( $fw_config, 'trade_eyebrow' ) ); ?></span>
				<h1><?php echo esc_html( Framework::field( $fw_config, 'trade_heading' ) ); ?></h1>
				<p><?php echo esc_html( Framework::field( $fw_config, 'trade_intro' ) ); ?></p>
			</div>

			<div class="fw-split">
				<div class="fw-split-text">
					<?php if ( $fw_status === 'sent' ) : ?>
						<div class="fw-form-notice fw-form-notice--success" role="status"><?php esc_html_e( "Thanks — your message is on its way. We'll be in touch soon.", $fw_config->text_domain() ); ?></div>
					<?php elseif ( $fw_status === 'error' ) : ?>
						<div class="fw-form-notice fw-form-notice--error" role="alert"><?php esc_html_e( 'Something went wrong sending your message — please try again, or email us directly below.', $fw_config->text_domain() ); ?></div>
					<?php endif; ?>

					<form class="fw-contact-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="fw_trade_submit">
						<?php wp_nonce_field( 'fw_trade_submit', 'fw_trade_nonce' ); ?>
						<p class="fw-hp-field" aria-hidden="true">
							<label for="fw_trade_hp"><?php esc_html_e( 'Leave this field empty', $fw_config->text_domain() ); ?></label>
							<input type="text" id="fw_trade_hp" name="fw_trade_hp" tabindex="-1" autocomplete="off">
						</p>
						<p>
							<label for="fw_trade_type"><?php esc_html_e( 'Inquiry Type', $fw_config->text_domain() ); ?></label>
							<select id="fw_trade_type" name="fw_trade_type" required>
								<option value="trade"><?php esc_html_e( 'Trade / Wholesale', $fw_config->text_domain() ); ?></option>
								<option value="press"><?php esc_html_e( 'Press / Media', $fw_config->text_domain() ); ?></option>
								<option value="other"><?php esc_html_e( 'Other', $fw_config->text_domain() ); ?></option>
							</select>
						</p>
						<p>
							<label for="fw_trade_company"><?php esc_html_e( 'Company / Publication', $fw_config->text_domain() ); ?></label>
							<input type="text" id="fw_trade_company" name="fw_trade_company" required>
						</p>
						<p>
							<label for="fw_trade_name"><?php esc_html_e( 'Name', $fw_config->text_domain() ); ?></label>
							<input type="text" id="fw_trade_name" name="fw_trade_name" required>
						</p>
						<p>
							<label for="fw_trade_email"><?php esc_html_e( 'Email', $fw_config->text_domain() ); ?></label>
							<input type="email" id="fw_trade_email" name="fw_trade_email" required>
						</p>
						<p>
							<label for="fw_trade_message"><?php esc_html_e( 'Message', $fw_config->text_domain() ); ?></label>
							<textarea id="fw_trade_message" name="fw_trade_message" rows="5" required></textarea>
						</p>
						<button type="submit" class="fw-btn fw-btn--solid"><?php esc_html_e( 'Send Inquiry', $fw_config->text_domain() ); ?></button>
					</form>
				</div>
				<div class="fw-split-text">
					<h2><?php esc_html_e( 'Or Reach Us Directly', $fw_config->text_domain() ); ?></h2>
					<p>
						<a href="mailto:<?php echo esc_attr( ContactSettings::get( $fw_config, 'email' ) ); ?>"><?php echo esc_html( ContactSettings::get( $fw_config, 'email' ) ); ?></a>
					</p>
					<p><?php esc_html_e( 'Looking for a consumer question instead — an existing order, a reservation, or the wine club? Use our general Contact page.', $fw_config->text_domain() ); ?></p>
					<a class="fw-btn" href="<?php echo esc_url( home_url( '/contact' ) ); ?>"><?php esc_html_e( 'Go to Contact', $fw_config->text_domain() ); ?></a>
				</div>
			</div>
		</div>
	</section>

<?php
endif;

get_footer();
