<?php
// GENERATED FILE — do not edit directly. Synced from forwineries-theme-core.
// Edit the source there and re-run tools/sync-core.sh.
namespace ForWineries\Core;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Purges the LiteSpeed page cache once after each deploy. The demo
 * sites deploy by git push, which WordPress never hears about, so the
 * server kept serving pre-deploy HTML (old style.css ?ver, old
 * templates) until the cache expired, on every uncached request the
 * theme version or core commit is compared with the last seen pair
 * and a change purges everything. No-op where LiteSpeed is absent.
 */
class DeployPurge {

	public static function register( Config $config ): void {
		add_action( 'init', function () use ( $config ) {
			$theme    = wp_get_theme();
			$manifest = get_stylesheet_directory() . '/core-manifest.json';
			$core     = '';
			if ( is_readable( $manifest ) ) {
				$data = json_decode( (string) file_get_contents( $manifest ), true );
				$core = isset( $data['core_commit'] ) ? (string) $data['core_commit'] : '';
			}
			$stamp = $theme->get( 'Version' ) . '|' . $core;
			$key   = $config->option_key( 'deployed_stamp' );
			if ( get_option( $key ) === $stamp ) return;
			update_option( $key, $stamp, false );

			if ( ! headers_sent() ) header( 'X-LiteSpeed-Purge: *' );
			do_action( 'litespeed_purge_all' );
			if ( function_exists( 'wp_cache_flush' ) ) wp_cache_flush();
		}, 20 );
	}
}
