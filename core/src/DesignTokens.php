<?php
// GENERATED FILE — do not edit directly. Synced from forwineries-theme-core.
// Edit the source there and re-run tools/sync-core.sh.
namespace ForWineries\Core;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Renders the theme's $config['tokens']/['fonts']/['radius'] into a
 * :root { --fw-*: ...; } block, enqueued as its own tiny inline stylesheet
 * ahead of core/assets/css/components.css (which is written entirely
 * against these unprefixed var(--fw-*) names — see
 * docs/DESIGN-TOKEN-SCHEMA.md for why unprefixed is safe here: only one
 * theme's CSS is ever loaded on a given site, so there's no collision risk
 * in every product theme sharing one token-name vocabulary).
 *
 * MUST enqueue before Commerce7\Integration's CSS-variable bridge, which
 * deliberately prints its own inline <style> block immediately after this
 * one so its --c7-* var(--fw-*) references resolve against real values,
 * not the browser's fallback. Boot::init() enforces that ordering — see
 * the comment there before changing either registration.
 */
class DesignTokens {

	public static function register( Config $config ): void {
		add_action( 'wp_enqueue_scripts', function () use ( $config ) {
			wp_register_style( 'fw-tokens', false, array(), null );
			wp_enqueue_style( 'fw-tokens' );
			wp_add_inline_style( 'fw-tokens', self::css( $config ) );
		}, 5 ); // priority 5: ahead of the theme's own style.css (default 10) and Commerce7's bridge.
	}

	public static function css( Config $config ): string {
		$tokens = (array) $config->get( 'tokens', array() );
		$lines  = array();
		foreach ( $tokens as $name => $value ) {
			$css_name = str_replace( '_', '-', $name );
			$lines[]  = sprintf( '--fw-%s: %s;', $css_name, $value );
			$rgb = self::hex_to_rgb_triplet( (string) $value );
			if ( $rgb !== '' ) {
				// A translucent version of a token (a hover tint, an
				// overlay) can't be built from the hex custom property
				// alone — CSS has no "this color at 40% opacity" operator
				// for a var(). Without this, the only way to write such a
				// rule at all was a hardcoded rgba(R, G, B, alpha) literal
				// duplicating the token's own value by hand — exactly how
				// Fault Line's --fw-clay leaked into 3 sibling themes'
				// hover effects via copy-paste (see LESSONS.md, 2026-09-10):
				// every one of those rules sat right next to correctly-
				// adapted var(--fw-*) references, but the literal itself
				// had no token to grep for or copy correctly. Emitting
				// this tuple for every hex token, always, means shared
				// core CSS can write rgba(var(--fw-clay-rgb), 0.45) once
				// and have it resolve correctly on every theme with zero
				// per-theme literal to ever drift again.
				$lines[] = sprintf( '--fw-%s-rgb: %s;', $css_name, $rgb );
			}
		}
		return ":root {\n\t" . implode( "\n\t", $lines ) . "\n}";
	}

	/**
	 * '#7d2035' -> '125, 32, 53' (the exact format rgba()/rgb() accept
	 * space- or comma-separated inside var()). Silently returns '' for
	 * anything not a plain 6-digit hex string (radius/spacing/font-name
	 * token values, short 3-digit hex, an already-rgb() string) — those
	 * tokens simply get no -rgb companion, which is harmless since
	 * nothing references one that was never emitted.
	 */
	private static function hex_to_rgb_triplet( string $value ): string {
		if ( ! preg_match( '/^#([0-9a-fA-F]{6})$/', trim( $value ), $m ) ) return '';
		$hex = $m[1];
		return sprintf( '%d, %d, %d', hexdec( substr( $hex, 0, 2 ) ), hexdec( substr( $hex, 2, 2 ) ), hexdec( substr( $hex, 4, 2 ) ) );
	}
}
