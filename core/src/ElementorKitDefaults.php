<?php
// GENERATED FILE — do not edit directly. Synced from forwineries-theme-core.
// Edit the source there and re-run tools/sync-core.sh.
namespace ForWineries\Core;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Pre-populates Elementor's Global Colors and Global Fonts (Site
 * Settings) with the theme's own brand palette/typography, so a client
 * using Elementor's OWN native color/font pickers sees on-brand options
 * everywhere by default, without needing to know any hex codes. Ported
 * from Fault Line's version.
 *
 * Deliberately additive-only: appends to Elementor's "Custom Colors"/
 * "Custom Fonts" lists, never touches the 4 built-in Primary/Secondary/
 * Text/Accent system slots — this theme's own token values aren't a
 * confirmed 1:1 match for what those 4 slots should mean in Elementor's
 * own model, so overwriting them risks a worse guess than adding
 * clearly-labeled options alongside them. Runs once (flag-guarded),
 * hooked to both after_switch_theme (a genuinely fresh install) and
 * admin_init (an already-live site picking this up via a plain git
 * deploy, which never re-fires after_switch_theme) — same pattern as
 * Events::seed(). Both hooks can also fire before Elementor has created
 * its Kit post yet, so this quietly no-ops and retries on the next
 * admin_init until a kit actually exists.
 *
 * Generalized from the original in the same way as DesignSettingsTab:
 * color hex values are read from $config->get('tokens') — the same
 * single source of truth DesignTokens::css() emits as CSS — rather than
 * a second hand-duplicated hex-value list that could drift. Only the
 * brand-voice DISPLAY NAMES ("Burgundy" rather than "Clay") are
 * genuinely per-theme creative content, from
 * $config->get('elementor_kit.color_names'); a token without one falls
 * back to a plain humanized version of its own key.
 *
 * Item ids (e.g. 'lh-clay') use $config->elementor_id() — slug-scoped,
 * unlike core's usual unprefixed CSS vocabulary, because Elementor
 * stores a reference to this exact id string inside any page that picks
 * it (_elementor_data postmeta), making it migration-risk data in the
 * same category as post types and nav menu locations.
 */
class ElementorKitDefaults {

	public static function register( Config $config ): void {
		add_action( 'after_switch_theme', function () use ( $config ) {
			self::seed( $config );
		} );
		add_action( 'admin_init', function () use ( $config ) {
			self::seed( $config );
		} );
	}

	/**
	 * Bare font-family name for Elementor's typography_font_family field
	 * (e.g. "Fraunces") out of a full CSS font-stack value (e.g.
	 * '"Fraunces", Georgia, serif") — every original theme's font tokens
	 * consistently lead with the real family name, quoted or not, so
	 * this is a reliable mechanical extraction, not a guess.
	 */
	private static function bare_font_family( string $css_stack ): string {
		$first = trim( explode( ',', $css_stack )[0] );
		return trim( $first, "\"' " );
	}

	private static function color_label( Config $config, string $token, string $fallback ): string {
		$names = $config->get( 'elementor_kit.color_names', array() );
		return isset( $names[ $token ] ) ? $names[ $token ] : $fallback;
	}

	private static function color_entries( Config $config ): array {
		$entries = array();
		foreach ( DesignSettingsTab::color_tokens( $config ) as $token => $label ) {
			$hex = $config->get( 'tokens.' . $token, '' );
			if ( ! $hex ) continue;
			$fallback  = ucwords( str_replace( '_', ' ', $token ) );
			$entries[] = array(
				'_id'   => $config->elementor_id( str_replace( '_', '-', $token ) ),
				'title' => $config->brand_name() . ' — ' . self::color_label( $config, $token, $fallback ),
				'color' => $hex,
			);
		}
		return $entries;
	}

	/**
	 * Default: heading + body, derived from $config->get('tokens'). A
	 * theme wanting more (e.g. Fault Line's italic accent variant) or
	 * specific font weights overrides via
	 * $config->get('elementor_kit.typography') entirely — each entry:
	 * array( 'id' => ..., 'title' => ..., 'font_family' => ...,
	 * 'font_weight' => ..., 'font_style' => (optional) ).
	 */
	private static function typography_entries( Config $config ): array {
		$default = array();
		$heading = $config->get( 'tokens.font_heading', '' );
		$body    = $config->get( 'tokens.font_body', '' );
		if ( $heading ) {
			$default[] = array(
				'id'          => 'heading-font',
				'title'       => $config->brand_name() . ' — ' . __( 'Headings', $config->text_domain() ),
				'font_family' => self::bare_font_family( $heading ),
				'font_weight' => '600',
			);
		}
		if ( $body ) {
			$default[] = array(
				'id'          => 'body-font',
				'title'       => $config->brand_name() . ' — ' . __( 'Body', $config->text_domain() ),
				'font_family' => self::bare_font_family( $body ),
				'font_weight' => '400',
			);
		}

		$entries = $config->get( 'elementor_kit.typography', $default );

		return array_map( function ( $entry ) use ( $config ) {
			$item = array(
				'_id'                    => $config->elementor_id( $entry['id'] ),
				'title'                  => $entry['title'],
				'typography_typography'  => 'custom',
				'typography_font_family' => $entry['font_family'],
				'typography_font_weight' => $entry['font_weight'],
			);
			if ( ! empty( $entry['font_style'] ) ) {
				$item['typography_font_style'] = $entry['font_style'];
			}
			return $item;
		}, $entries );
	}

	public static function seed( Config $config ): void {
		$flag = $config->option_key( 'elementor_kit_seeded' );
		if ( get_option( $flag ) ) return;
		if ( ! did_action( 'elementor/loaded' ) || ! class_exists( '\Elementor\Plugin' ) ) return;

		$kit_id = get_option( 'elementor_active_kit' );
		if ( ! $kit_id ) return;

		$settings = get_post_meta( $kit_id, '_elementor_page_settings', true );
		if ( ! is_array( $settings ) ) $settings = array();

		$custom_colors = isset( $settings['custom_colors'] ) && is_array( $settings['custom_colors'] ) ? $settings['custom_colors'] : array();
		$settings['custom_colors'] = array_merge( $custom_colors, self::color_entries( $config ) );

		$custom_typography = isset( $settings['custom_typography'] ) && is_array( $settings['custom_typography'] ) ? $settings['custom_typography'] : array();
		$settings['custom_typography'] = array_merge( $custom_typography, self::typography_entries( $config ) );

		update_post_meta( $kit_id, '_elementor_page_settings', $settings );
		update_option( $flag, 1 );
	}
}
