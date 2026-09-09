<?php
// GENERATED FILE — do not edit directly. Synced from forwineries-theme-core.
// Edit the source there and re-run tools/sync-core.sh.
namespace ForWineries\Core;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Contact form + Trade & Press form handlers — no form plugin, matching
 * this codebase's existing no-plugin-dependency philosophy (same
 * reasoning as ContentSettings\Framework using core's own Settings API
 * instead of ACF). Ported near-verbatim from the original 5 themes'
 * inc/page-setup.php, which had these byte-identical modulo prefix.
 *
 * Both admin_post_* and the _nopriv_* variant are registered for each
 * form: these are filled in by logged-out visitors, and WordPress only
 * routes admin-post.php requests to the plain (non-nopriv) hook for
 * logged-in users.
 *
 * Field names, nonce actions, and the honeypot field are unprefixed
 * ('fw_contact_*' / 'fw_trade_*') — request-scoped values matched only
 * between a page template's form markup and this handler within the
 * same request, never stored anywhere, so they carry none of the
 * live-site migration risk option_key()/cookie_name()/post_type() exist
 * for for. The page templates that render these forms (still to be
 * ported) must use these same fixed field names.
 *
 * Recipient address reads the same contact_email option Schema.php
 * already reads via $config->option_key('contact_email') — both stay
 * in sync automatically since they share one option key rather than
 * each defining their own.
 */
class ContactForms {

	public static function register( Config $config ): void {
		add_action( 'admin_post_fw_contact_submit', function () use ( $config ) {
			self::handle_contact_submit( $config );
		} );
		add_action( 'admin_post_nopriv_fw_contact_submit', function () use ( $config ) {
			self::handle_contact_submit( $config );
		} );

		add_action( 'admin_post_fw_trade_submit', function () use ( $config ) {
			self::handle_trade_submit( $config );
		} );
		add_action( 'admin_post_nopriv_fw_trade_submit', function () use ( $config ) {
			self::handle_trade_submit( $config );
		} );
	}

	private static function recipient( Config $config ): string {
		$value = get_option( $config->option_key( 'contact_email' ), '' );
		return $value !== '' ? $value : get_option( 'admin_email' );
	}

	private static function handle_contact_submit( Config $config ): void {
		$redirect_base = wp_get_referer() ? wp_get_referer() : home_url( '/contact' );

		if ( ! isset( $_POST['fw_contact_nonce'] ) || ! wp_verify_nonce( $_POST['fw_contact_nonce'], 'fw_contact_submit' ) ) {
			wp_safe_redirect( add_query_arg( 'fw_contact', 'error', $redirect_base ) );
			exit;
		}

		// Honeypot: real visitors never see or fill this field (hidden via
		// CSS, .fw-hp-field). A filled honeypot means it's a bot — pretend
		// success so the bot doesn't retry, without actually sending mail.
		if ( ! empty( $_POST['fw_contact_hp'] ) ) {
			wp_safe_redirect( add_query_arg( 'fw_contact', 'sent', home_url( '/contact' ) ) );
			exit;
		}

		$name    = isset( $_POST['fw_contact_name'] ) ? sanitize_text_field( wp_unslash( $_POST['fw_contact_name'] ) ) : '';
		$email   = isset( $_POST['fw_contact_email'] ) ? sanitize_email( wp_unslash( $_POST['fw_contact_email'] ) ) : '';
		$message = isset( $_POST['fw_contact_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['fw_contact_message'] ) ) : '';

		if ( ! $name || ! $email || ! is_email( $email ) || ! $message ) {
			wp_safe_redirect( add_query_arg( 'fw_contact', 'error', home_url( '/contact' ) ) );
			exit;
		}

		$to      = self::recipient( $config );
		$subject = sprintf( __( 'New contact form message from %s', $config->text_domain() ), $name );
		$body    = "Name: {$name}\nEmail: {$email}\n\nMessage:\n{$message}";
		$headers = array( 'Reply-To: ' . $name . ' <' . $email . '>' );

		$sent = wp_mail( $to, $subject, $body, $headers );

		wp_safe_redirect( add_query_arg( 'fw_contact', $sent ? 'sent' : 'error', home_url( '/contact' ) ) );
		exit;
	}

	/**
	 * Same structure as handle_contact_submit() (nonce, honeypot,
	 * sanitize, wp_mail), just with the two extra fields that form
	 * collects. Deliberately reuses the same recipient() as the consumer
	 * contact form rather than a second settings field — a small client
	 * checks one inbox, not two.
	 */
	private static function handle_trade_submit( Config $config ): void {
		$redirect_base = wp_get_referer() ? wp_get_referer() : home_url( '/trade-press' );

		if ( ! isset( $_POST['fw_trade_nonce'] ) || ! wp_verify_nonce( $_POST['fw_trade_nonce'], 'fw_trade_submit' ) ) {
			wp_safe_redirect( add_query_arg( 'fw_trade', 'error', $redirect_base ) );
			exit;
		}

		if ( ! empty( $_POST['fw_trade_hp'] ) ) {
			wp_safe_redirect( add_query_arg( 'fw_trade', 'sent', home_url( '/trade-press' ) ) );
			exit;
		}

		$types = array(
			'trade' => __( 'Trade / Wholesale', $config->text_domain() ),
			'press' => __( 'Press / Media', $config->text_domain() ),
			'other' => __( 'Other', $config->text_domain() ),
		);
		$type    = isset( $_POST['fw_trade_type'] ) && isset( $types[ $_POST['fw_trade_type'] ] ) ? sanitize_key( $_POST['fw_trade_type'] ) : 'other';
		$company = isset( $_POST['fw_trade_company'] ) ? sanitize_text_field( wp_unslash( $_POST['fw_trade_company'] ) ) : '';
		$name    = isset( $_POST['fw_trade_name'] ) ? sanitize_text_field( wp_unslash( $_POST['fw_trade_name'] ) ) : '';
		$email   = isset( $_POST['fw_trade_email'] ) ? sanitize_email( wp_unslash( $_POST['fw_trade_email'] ) ) : '';
		$message = isset( $_POST['fw_trade_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['fw_trade_message'] ) ) : '';

		if ( ! $company || ! $name || ! $email || ! is_email( $email ) || ! $message ) {
			wp_safe_redirect( add_query_arg( 'fw_trade', 'error', home_url( '/trade-press' ) ) );
			exit;
		}

		$to      = self::recipient( $config );
		$subject = sprintf( __( 'New %1$s inquiry from %2$s (%3$s)', $config->text_domain() ), $types[ $type ], $name, $company );
		$body    = "Type: {$types[$type]}\nCompany/Publication: {$company}\nName: {$name}\nEmail: {$email}\n\nMessage:\n{$message}";
		$headers = array( 'Reply-To: ' . $name . ' <' . $email . '>' );

		$sent = wp_mail( $to, $subject, $body, $headers );

		wp_safe_redirect( add_query_arg( 'fw_trade', $sent ? 'sent' : 'error', home_url( '/trade-press' ) ) );
		exit;
	}
}
