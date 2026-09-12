<?php
// GENERATED FILE — do not edit directly. Synced from forwineries-theme-core.
// Edit the source there and re-run tools/sync-core.sh.
namespace ForWineries\Core;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Ships a default favicon so a fresh install never shows a blank browser
 * tab. WordPress only ever prints favicon <link>/<meta> tags via its own
 * wp_site_icon() (hooked to wp_head/admin_head in core) when
 * has_site_icon() is true — i.e. only once an admin has picked one under
 * Customize → Site Identity → Site Icon. None of the 5 winery themes ship
 * with that option set, and confirmed live: none of the 5 demos had a
 * favicon as a result.
 *
 * Rather than print our own competing <link> tags, this filters
 * get_site_icon_url() to fall back to the active theme's own bundled
 * default whenever the real lookup comes back empty. has_site_icon()
 * is itself defined as `(bool) get_site_icon_url()`, so the fallback
 * makes WordPress's own machinery treat the theme's asset as a genuine
 * site icon and generate every one of its tags for free — the 32x32 and
 * 192x192 <link rel="icon">, the apple-touch-icon, and the
 * msapplication-TileImage meta — all four just reference this one image
 * regardless of the $size WP asks for, since it isn't a real attachment
 * WP can regenerate at each size from. A buyer who later sets their own
 * Site Icon in the Customizer overrides this immediately and
 * permanently: the filter only ever fires when the real value is empty.
 */
class Favicon {

	public static function register( Config $config ): void {
		add_filter( 'get_site_icon_url', function ( $url, $size, $blog_id ) {
			if ( $url ) return $url;
			$default = self::default_url();
			return $default !== '' ? $default : $url;
		}, 10, 3 );
	}

	/**
	 * Convention over configuration, same as every other per-theme asset
	 * path in this codebase: each product theme provides its own
	 * assets/images/favicon.png (this class only supplies the logic, not
	 * the artwork — see docs/ARCHITECTURE.md's PRESENTATION tier). Themes
	 * are not required to have one; a theme that hasn't added its own yet
	 * just leaves has_site_icon() false, same as before this class existed.
	 */
	private static function default_url(): string {
		$path = get_stylesheet_directory() . '/assets/images/favicon.png';
		if ( ! file_exists( $path ) ) return '';
		return get_stylesheet_directory_uri() . '/assets/images/favicon.png';
	}
}
