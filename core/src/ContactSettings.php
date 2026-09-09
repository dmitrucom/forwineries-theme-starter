<?php
// GENERATED FILE — do not edit directly. Synced from forwineries-theme-core.
// Edit the source there and re-run tools/sync-core.sh.
namespace ForWineries\Core;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * "Contact & Social" settings tab — address/phone/email/social links
 * used in header/footer chrome, the Visit/Contact pages, and (already
 * shipped — see Schema.php's contact() helper) the Winery/Event JSON-LD.
 * Ported from Larkhaven's inc/customizer.php.
 *
 * Schema::register()'s org/event structured data already reads these
 * exact same $config->option_key('contact_*') keys — this class is what
 * actually gives a client a wp-admin UI to set them, closing a real gap:
 * Schema.php shipped in an earlier Phase-0 batch reading option keys
 * nothing yet wrote to.
 *
 * NOT ported: the original's one-time Customizer-theme_mod-to-options
 * migration (this used to be a Customizer panel before Larkhaven's own
 * v1.29.x settings consolidation) — that's a specific historical
 * migration for sites that predate this settings page entirely, not a
 * pattern a new theme built on this core ever needs.
 *
 * The Reservations link is deliberately NOT here, same as the original
 * — it lives on Commerce7\Integration's "Setup" tab, since it defaults
 * to the auto-created /reservation page and may point at Commerce7 or
 * an external booking platform.
 */
class ContactSettings {

	/**
	 * id => [ label, default/placeholder ]. Default is generic; a theme
	 * supplies its own real placeholder copy via $config->get('contact.fields').
	 */
	public static function fields( Config $config ): array {
		return $config->get( 'contact.fields', array(
			'address_line'     => array( __( 'Address line (shown in top bar & footer)', $config->text_domain() ), '' ),
			'phone_display'    => array( __( 'Phone (display)', $config->text_domain() ), '' ),
			'phone_tel'        => array( __( 'Phone (tel: link, digits only, e.g. +17075551874)', $config->text_domain() ), '' ),
			'email'            => array( __( 'Contact email', $config->text_domain() ), '' ),
			// Deliberately blank, not "#" — a real winery has no default
			// social URL to fall back to; header/footer templates hide
			// each icon entirely until a real one is entered here rather
			// than linking nowhere.
			'social_instagram' => array( __( 'Instagram URL (leave blank to hide the icon)', $config->text_domain() ), '' ),
			'social_facebook'  => array( __( 'Facebook URL (leave blank to hide the icon)', $config->text_domain() ), '' ),
		) );
	}

	/**
	 * Read one field's saved value — the same read path Schema::contact()
	 * already uses. Kept here too (not just in Schema.php) since header/
	 * footer templates and Visit/Contact pages read these directly.
	 */
	public static function get( Config $config, string $key, string $default = '' ): string {
		$value = get_option( $config->option_key( 'contact_' . $key ), '' );
		// "#" was this field's old placeholder default (pre-dates the
		// "hide the icon instead of a dead link" fix) — a site that saved
		// this tab before that change may still have a literal "#"
		// stored. Treated exactly like blank everywhere this is read.
		if ( $value === '#' ) $value = '';
		if ( $value !== '' ) return $value;

		if ( $default !== '' ) return $default;

		// No saved value and no explicit fallback given — fall back to
		// this field's own registered placeholder from fields(), same
		// three-tier order ContentSettings\Framework::field() already
		// uses (saved value -> explicit $fallback -> registered
		// default). Without this, a call site that (like most of them)
		// doesn't pass an explicit $default silently rendered blank
		// instead of the theme's own placeholder copy — found live on
		// Larkhaven's header/footer topbar during Phase 1 migration.
		$fields = self::fields( $config );
		return isset( $fields[ $key ][1] ) ? $fields[ $key ][1] : '';
	}

	public static function register( Config $config ): void {
		$settings_group = $config->option_key( 'contact_settings' );

		add_action( 'admin_init', function () use ( $config, $settings_group ) {
			foreach ( self::fields( $config ) as $id => $conf ) {
				register_setting( $settings_group, $config->option_key( 'contact_' . $id ), array(
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
					'default'           => $conf[1],
				) );
			}
		} );

		SettingsPage::add_tab( 'contact', __( 'Contact & Social', $config->text_domain() ), function () use ( $config ) {
			self::render_tab_content( $config );
		}, 30 );
	}

	private static function render_tab_content( Config $config ): void {
		if ( ! current_user_can( 'manage_options' ) ) return;
		?>
			<form method="post" action="options.php">
				<?php settings_fields( $config->option_key( 'contact_settings' ) ); ?>
				<input type="hidden" name="_wp_http_referer" value="<?php echo esc_url( admin_url( 'admin.php?page=' . $config->slug() . '-settings&tab=contact' ) ); ?>">
				<p class="description"><?php esc_html_e( 'Used in the header top bar, footer, and the Visit/Contact pages.', $config->text_domain() ); ?></p>
				<table class="form-table" role="presentation">
					<?php foreach ( self::fields( $config ) as $id => $conf ) :
						list( $label, $default ) = $conf;
						$option_name = $config->option_key( 'contact_' . $id );
						?>
						<tr>
							<th><label for="<?php echo esc_attr( $option_name ); ?>"><?php echo esc_html( $label ); ?></label></th>
							<td>
								<input type="text" id="<?php echo esc_attr( $option_name ); ?>" name="<?php echo esc_attr( $option_name ); ?>" class="regular-text" value="<?php echo esc_attr( self::get( $config, $id, $default ) ); ?>" placeholder="<?php echo esc_attr( $default ); ?>">
							</td>
						</tr>
					<?php endforeach; ?>
				</table>
				<?php submit_button(); ?>
			</form>
		<?php
	}
}
