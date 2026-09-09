<?php
// GENERATED FILE — do not edit directly. Synced from forwineries-theme-core.
// Edit the source there and re-run tools/sync-core.sh.
/**
 * Maps the ForWineries\Core\* namespace onto core/src/*.php — no Composer,
 * because no build step exists on Hostinger's git-push deploy path (see
 * docs/ARCHITECTURE.md) and none is needed for a namespace this shallow.
 * A product theme's functions.php requires only this one file; everything
 * else loads lazily, on first use, via the autoloader below.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

spl_autoload_register( function ( $class ) {
	$prefix = 'ForWineries\\Core\\';
	if ( strncmp( $prefix, $class, strlen( $prefix ) ) !== 0 ) {
		return;
	}
	$relative = substr( $class, strlen( $prefix ) );
	$path     = __DIR__ . '/' . str_replace( '\\', '/', $relative ) . '.php';
	if ( is_readable( $path ) ) {
		require $path;
	}
} );
