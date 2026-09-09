<?php
// GENERATED FILE — do not edit directly. Synced from forwineries-theme-core.
// Edit the source there and re-run tools/sync-core.sh.
namespace ForWineries\Core;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Unified settings page shell — one wp-admin menu item consolidating a
 * theme's settings systems into a single tabbed page instead of several
 * separate wp-admin locations. This file owns ONLY the page shell, menu
 * registration, and tab navigation; each tab's actual field
 * registration/sanitization/data stays with the module that owns it
 * (DemoBar::render_tab_content(), the not-yet-ported ContentSettings\
 * Framework, DesignSettingsTab, and Commerce7\Integration's own setup
 * tab).
 *
 * Ported from the original inc/theme-settings.php, which hardcoded
 * knowledge of all 5 tabs (including a direct call to
 * lh_c7_settings_tab_content()) directly in the page shell — exactly
 * the kind of coupling that made Commerce7\Integration a hard
 * dependency of porting the shell at all. Replaced with an explicit
 * registry (add_tab(), called once per tab from Boot::init(), in the
 * position each module's own doc-comment says it needs — same
 * philosophy as Boot.php itself) so the shell has zero knowledge of
 * which modules exist; it only knows how to render whatever tabs were
 * actually registered in THIS theme's Boot::init() call.
 *
 * Deliberately still one Settings-API group/<form> PER TAB (not merged
 * into a single form) — lower risk than consolidating different
 * sanitize-callback models into one, and it means every tab's "Save
 * Changes" behaves exactly like a normal WordPress settings page always
 * has: POST to options.php, redirect back to the SAME tab. Only
 * SWITCHING between tabs (before saving) is instant/JS — all
 * registered panels render on every page load; core/assets/js/
 * settings-tabs.js just toggles which one is visible.
 *
 * NOT ported: the original file's redirect for old pre-consolidation
 * menu URLs (larkhaven-commerce7 → ?page=larkhaven-settings&tab=setup,
 * etc.) — those URLs were specific to each theme's own settings-page
 * history before this consolidation existed, not a pattern a new theme
 * built on this core ever has to migrate away from.
 */
class SettingsPage {

	/** @var array<string, array{label: string, render: callable, priority: int}> */
	private static array $tabs = array();

	/**
	 * Called once per tab, from Boot::init(), by whichever module owns
	 * that tab's content — e.g.
	 *   SettingsPage::add_tab( 'demo', __( 'Demo Bar', $config->text_domain() ),
	 *       function () use ( $config ) { DemoBar::render_tab_content( $config ); }, 50 );
	 * $priority controls left-to-right tab order (lower first); ties
	 * keep insertion order.
	 */
	public static function add_tab( string $id, string $label, callable $render, int $priority = 10 ): void {
		self::$tabs[ $id ] = array(
			'label'    => $label,
			'render'   => $render,
			'priority' => $priority,
		);
	}

	public static function register( Config $config ): void {
		add_action( 'admin_menu', function () use ( $config ) {
			add_menu_page(
				sprintf( __( '%s Settings', $config->text_domain() ), $config->brand_name() ),
				$config->brand_name(),
				'manage_options',
				$config->slug() . '-settings',
				function () use ( $config ) {
					self::render( $config );
				},
				'dashicons-store',
				58 // Just above the core "Settings" menu — high discoverability for a non-technical client.
			);
		} );

		add_action( 'admin_enqueue_scripts', function ( $hook ) use ( $config ) {
			if ( $hook !== 'toplevel_page_' . $config->slug() . '-settings' ) return;
			wp_enqueue_script(
				'fw-settings-tabs',
				get_stylesheet_directory_uri() . '/core/assets/js/settings-tabs.js',
				array(),
				null,
				true
			);
			wp_enqueue_style(
				'fw-admin-settings',
				get_stylesheet_directory_uri() . '/core/assets/css/admin-settings.css',
				array(),
				null
			);
		} );
	}

	private static function ordered_tabs(): array {
		$tabs = self::$tabs;
		uasort( $tabs, function ( $a, $b ) {
			return $a['priority'] <=> $b['priority'];
		} );
		return $tabs;
	}

	private static function render( Config $config ): void {
		if ( ! current_user_can( 'manage_options' ) ) return;

		$tabs = self::ordered_tabs();
		if ( empty( $tabs ) ) return;

		$tab_ids    = array_keys( $tabs );
		$active_tab = ( isset( $_GET['tab'] ) && isset( $tabs[ $_GET['tab'] ] ) ) ? sanitize_key( $_GET['tab'] ) : $tab_ids[0];
		?>
		<div class="wrap">
			<h1><?php echo esc_html( $config->brand_name() ); ?></h1>

			<h2 class="nav-tab-wrapper" id="<?php echo esc_attr( $config->css( 'settings-tabs' ) ); ?>">
				<?php foreach ( $tabs as $tab_id => $tab ) : ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $config->slug() . '-settings&tab=' . $tab_id ) ); ?>" class="nav-tab<?php echo $tab_id === $active_tab ? ' nav-tab-active' : ''; ?>" data-tab="<?php echo esc_attr( $tab_id ); ?>">
						<?php echo esc_html( $tab['label'] ); ?>
					</a>
				<?php endforeach; ?>
			</h2>

			<?php foreach ( $tabs as $tab_id => $tab ) : ?>
				<div class="<?php echo esc_attr( $config->css( 'settings-tab-panel' ) ); ?>" data-tab="<?php echo esc_attr( $tab_id ); ?>" <?php echo $tab_id !== $active_tab ? 'hidden' : ''; ?>>
					<?php call_user_func( $tab['render'] ); ?>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
	}
}
