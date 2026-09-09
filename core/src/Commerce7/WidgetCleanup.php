<?php
// GENERATED FILE — do not edit directly. Synced from forwineries-theme-core.
// Edit the source there and re-run tools/sync-core.sh.
namespace ForWineries\Core\Commerce7;

use ForWineries\Core\Config;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Just the wp_enqueue_script() call for core/assets/js/c7-widget-cleanup.js
 * — see that file's header for what it actually does. Split out from the
 * rest of the Commerce7 integration (still to be ported — see
 * docs/ARCHITECTURE.md's Phase 0 ordering) because it has zero dependency
 * on it: it only ever touches Commerce7's own client-rendered widget DOM,
 * never our server-side REST/catalog code, so there's no reason to make
 * it wait for the larger, higher-risk Commerce7 port.
 */
class WidgetCleanup {

	public static function register( Config $config ): void {
		add_action( 'wp_enqueue_scripts', function () {
			wp_enqueue_script(
				'fw-c7-widget-cleanup',
				get_stylesheet_directory_uri() . '/core/assets/js/c7-widget-cleanup.js',
				array(),
				Config::asset_version( '/core/assets/js/c7-widget-cleanup.js' ),
				true
			);
		} );
	}
}
