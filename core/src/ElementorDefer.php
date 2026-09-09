<?php
// GENERATED FILE — do not edit directly. Synced from forwineries-theme-core.
// Edit the source there and re-run tools/sync-core.sh.
namespace ForWineries\Core;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * True if a given post has actually been built with Elementor. Every
 * settings-driven template (front-page.php, the marketing page
 * templates under core/templates/) calls this on its own post and
 * steps aside the moment it comes back true — rendering the_content()
 * (the client's Elementor page) instead of the theme's built-in design.
 * That's what makes these pages "editable from settings, but still
 * buildable in Elementor if wanted": nothing to configure either way,
 * it's automatic per page.
 *
 * Pure and stateless — no Config needed — so this is a plain utility
 * rather than a registered module. Promoted from what used to be a
 * theme-prefixed function (lh_is_elementor_built() and 4 siblings)
 * duplicated across all 5 original themes, byte-identical except for
 * the prefix; a shared core template can't call a theme-prefixed
 * function name, so this had to move here to be usable from
 * core/templates/*.php at all.
 */
class ElementorDefer {

	public static function is_built( int $post_id ): bool {
		if ( ! $post_id ) return false;

		if ( did_action( 'elementor/loaded' ) && class_exists( '\Elementor\Plugin' ) ) {
			$document = \Elementor\Plugin::$instance->documents->get( $post_id );
			return $document && $document->is_built_with_elementor();
		}

		return get_post_meta( $post_id, '_elementor_edit_mode', true ) === 'builder';
	}

	/**
	 * Front-page-specific wrapper — front-page.php checks the site's
	 * assigned static front page (Settings > Reading), not "the current
	 * post", since front-page.php doesn't always run in a normal post loop.
	 */
	public static function is_front_page_built(): bool {
		return self::is_built( (int) get_option( 'page_on_front' ) );
	}
}
