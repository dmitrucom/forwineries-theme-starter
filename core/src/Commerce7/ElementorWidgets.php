<?php
// GENERATED FILE — do not edit directly. Synced from forwineries-theme-core.
// Edit the source there and re-run tools/sync-core.sh.
namespace ForWineries\Core\Commerce7;

use ForWineries\Core\Config;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Native Elementor widgets for the Commerce7 widgets defined in
 * Blocks::block_definitions() — the same array used by the Gutenberg
 * blocks (Blocks.php), so there's still exactly one place that defines
 * what fields each widget has and what it renders. Only loads if
 * Elementor is actually active; does nothing otherwise.
 */
class ElementorWidgets {

	public static function register( Config $config ): void {
		add_action( 'elementor/elements/categories_registered', function ( $elements_manager ) use ( $config ) {
			$elements_manager->add_category( Blocks::category_slug( $config ), array(
				'title' => sprintf( __( '%s — Widgets', $config->text_domain() ), $config->brand_name() ),
				'icon'  => 'eicon-cart-medium',
			) );
		} );

		add_action( 'elementor/widgets/register', function ( $widgets_manager ) use ( $config ) {
			self::require_widget_class();
			foreach ( array_keys( Blocks::block_definitions( $config ) ) as $def_key ) {
				$widgets_manager->register( new ElementorWidget( array(), array( 'fw_config' => $config, 'fw_def_key' => $def_key ) ) );
			}
		} );

		// Elementor < 3.5 fallback (older register_widget_type() API).
		// Harmless no-op on modern Elementor, which never fires this hook.
		add_action( 'elementor/widgets/widgets_registered', function ( $widgets_manager ) use ( $config ) {
			if ( method_exists( $widgets_manager, 'register' ) ) return; // modern Elementor already handled above
			self::require_widget_class();
			foreach ( array_keys( Blocks::block_definitions( $config ) ) as $def_key ) {
				$widgets_manager->register_widget_type( new ElementorWidget( array(), array( 'fw_config' => $config, 'fw_def_key' => $def_key ) ) );
			}
		} );
	}

	private static function require_widget_class(): void {
		if ( ! class_exists( __NAMESPACE__ . '\\ElementorWidget' ) ) {
			require_once __DIR__ . '/ElementorWidget.php';
		}
	}
}
