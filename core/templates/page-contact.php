<?php
// GENERATED FILE — do not edit directly. Synced from forwineries-theme-core.
// Edit the source there and re-run tools/sync-core.sh.
/**
 * Contact page. A product theme's own page-templates/page-contact.php
 * stub sets the "Template Name:" header, then requires this file with
 * $fw_config already in scope. The form posts to
 * admin-post.php?action=fw_contact_submit, handled by ContactForms —
 * field names below (fw_contact_*) must match that class exactly.
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
	$fw_status = isset( $_GET['fw_contact'] ) ? sanitize_key( $_GET['fw_contact'] ) : '';
	?>

	<section class="fw-section">
		<div class="fw-container">
			<div class="fw-section-head">
				<span class="fw-eyebrow"><?php echo esc_html( Framework::field( $fw_config, 'contact_eyebrow' ) ); ?></span>
				<h1><?php echo esc_html( Framework::field( $fw_config, 'contact_heading' ) ); ?></h1>
				<p><?php echo esc_html( Framework::field( $fw_config, 'contact_intro' ) ); ?></p>
			</div>

			<div class="fw-split">
				<div class="fw-split-text">
					<?php if ( $fw_status === 'sent' ) : ?>
						<div class="fw-form-notice fw-form-notice--success" role="status"><?php esc_html_e( "Thanks — your message is on its way. We'll be in touch soon.", $fw_config->text_domain() ); ?></div>
					<?php elseif ( $fw_status === 'error' ) : ?>
						<div class="fw-form-notice fw-form-notice--error" role="alert"><?php esc_html_e( 'Something went wrong sending your message — please try again, or email us directly below.', $fw_config->text_domain() ); ?></div>
					<?php endif; ?>

					<form class="fw-contact-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="fw_contact_submit">
						<?php wp_nonce_field( 'fw_contact_submit', 'fw_contact_nonce' ); ?>
						<p class="fw-hp-field" aria-hidden="true">
							<label for="fw_contact_hp"><?php esc_html_e( 'Leave this field empty', $fw_config->text_domain() ); ?></label>
							<input type="text" id="fw_contact_hp" name="fw_contact_hp" tabindex="-1" autocomplete="off">
						</p>
						<p>
							<label for="fw_contact_name"><?php esc_html_e( 'Name', $fw_config->text_domain() ); ?></label>
							<input type="text" id="fw_contact_name" name="fw_contact_name" required>
						</p>
						<p>
							<label for="fw_contact_email"><?php esc_html_e( 'Email', $fw_config->text_domain() ); ?></label>
							<input type="email" id="fw_contact_email" name="fw_contact_email" required>
						</p>
						<p>
							<label for="fw_contact_message"><?php esc_html_e( 'Message', $fw_config->text_domain() ); ?></label>
							<textarea id="fw_contact_message" name="fw_contact_message" rows="5" required></textarea>
						</p>
						<button type="submit" class="fw-btn fw-btn--solid"><?php esc_html_e( 'Send Message', $fw_config->text_domain() ); ?></button>
					</form>
				</div>
				<div class="fw-split-text">
					<h2><?php esc_html_e( 'Or Reach Us Directly', $fw_config->text_domain() ); ?></h2>
					<p>
						<?php echo esc_html( ContactSettings::get( $fw_config, 'address_line' ) ); ?><br>
						<a href="tel:<?php echo esc_attr( ContactSettings::get( $fw_config, 'phone_tel' ) ); ?>"><?php echo esc_html( ContactSettings::get( $fw_config, 'phone_display' ) ); ?></a><br>
						<a href="mailto:<?php echo esc_attr( ContactSettings::get( $fw_config, 'email' ) ); ?>"><?php echo esc_html( ContactSettings::get( $fw_config, 'email' ) ); ?></a>
					</p>
				</div>
			</div>
		</div>
	</section>

<?php
endif;

get_footer();
