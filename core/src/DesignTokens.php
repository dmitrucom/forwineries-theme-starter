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
		}
		return ":root {\n\t" . implode( "\n\t", $lines ) . "\n}";
	}
}
