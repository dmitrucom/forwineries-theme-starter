<?php
// GENERATED FILE — do not edit directly. Synced from forwineries-theme-core.
// Edit the source there and re-run tools/sync-core.sh.
namespace ForWineries\Core;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Thin, typed-ish wrapper over a theme's config array (built in that
 * theme's functions.php and handed to Boot::init()). Nothing here is
 * theme-specific — only the array a given theme passes in is.
 *
 * $slug is where per-theme prefixing still earns its keep on a LIVE
 * site, and ONLY there: wp_options keys (renaming these loses a site's
 * already-saved settings) and cookie names (renaming these makes an
 * already-verified visitor see the age gate again post-migration) both
 * carry real migration risk if changed. CSS class/id names carry no
 * equivalent risk — nothing was ever saved against a class name — so
 * core CSS/JS is written once, against a single unprefixed `fw-*`
 * vocabulary (css() below), and is genuinely byte-identical across every
 * theme. This mirrors the same "only one theme is ever active per WP
 * install, so collision was never a real risk" reasoning documented for
 * the --fw-* design-token custom properties in docs/ARCHITECTURE.md —
 * applied consistently here rather than stopping short at CSS variables.
 */
class Config {

	private array $data;

	public function __construct( array $data ) {
		$this->data = $data;
	}

	/**
	 * Dot-notation getter, e.g. get('tokens.clay') or get('contact.phone').
	 * $default is returned as-is (no type coercion) when the path is missing.
	 */
	public function get( string $path, $default = null ) {
		$segments = explode( '.', $path );
		$value    = $this->data;
		foreach ( $segments as $segment ) {
			if ( ! is_array( $value ) || ! array_key_exists( $segment, $value ) ) {
				return $default;
			}
			$value = $value[ $segment ];
		}
		return $value;
	}

	public function slug(): string {
		return (string) $this->get( 'slug', '' );
	}

	/**
	 * wp_options key, namespaced by slug — e.g. option_key('c7_tenant_id')
	 * => 'lh_c7_tenant_id'. Slug-scoped because renaming an existing
	 * option key on a live site silently reverts that setting to default.
	 */
	public function option_key( string $name ): string {
		return $this->slug() . '_' . $name;
	}

	/**
	 * Cookie name, namespaced by slug — e.g. cookie_name('age_verified')
	 * => 'lh_age_verified'. Slug-scoped for the same reason as
	 * option_key(): a visitor who already verified against the old name
	 * shouldn't see the gate again just because core code moved.
	 */
	public function cookie_name( string $name ): string {
		return $this->slug() . '_' . $name;
	}

	/**
	 * Custom post type slug, namespaced by slug — e.g. post_type('event')
	 * => 'lh_event'. Slug-scoped for the same reason as option_key(): the
	 * post type is stored per-row in wp_posts on a live site, so renaming
	 * it on an already-live install orphans every existing post of that
	 * type from admin listings, archives, and queries. Confirmed each of
	 * the 5 original themes already used its own prefixed CPT slug
	 * (lh_event / flc_event / vsp_event / alb_event / hrv_event) — this
	 * formalizes an existing convention, not a new risk.
	 */
	public function post_type( string $name ): string {
		return $this->slug() . '_' . $name;
	}

	/**
	 * Nav menu location slug, namespaced by slug — e.g.
	 * nav_location('primary') => 'lh-primary'. A fourth slug-scoped
	 * category, same reasoning as post_type(): a site's chosen menu
	 * assignment is stored per-site in the 'nav_menu_locations' theme_mod
	 * keyed by this exact string, so renaming it on an already-migrated
	 * site silently un-assigns whatever menu the site owner picked.
	 */
	public function nav_location( string $name ): string {
		return $this->slug() . '-' . $name;
	}

	/**
	 * Elementor global color/typography item id, namespaced by slug —
	 * e.g. elementor_id('clay') => 'lh-clay'. A fifth slug-scoped
	 * category: when a client picks one of these global colors/fonts on
	 * an element, Elementor stores a reference to this exact id string
	 * in that page's saved Elementor data (_elementor_data postmeta), so
	 * renaming it on an already-migrated site breaks that reference —
	 * the element silently reverts to no color/font selected.
	 */
	public function elementor_id( string $name ): string {
		return $this->slug() . '-' . $name;
	}

	/**
	 * Unprefixed core CSS class/id name — e.g. css('age-gate') always
	 * returns 'fw-age-gate', regardless of theme. Deliberately NOT
	 * slug-scoped: see the class docblock above for why that's safe and
	 * why it's what makes core/assets/css/*.css genuinely shareable.
	 */
	public function css( string $name ): string {
		return 'fw-' . $name;
	}

	/**
	 * Cache-busting version string for a core-owned asset, e.g.
	 * asset_version('/core/assets/css/commerce7-overrides.css'). Every
	 * core asset is enqueued with THIS, never a literal `null` — a static
	 * handle with no version query string has no way to force Cloudflare's
	 * edge cache (or a browser cache) to pick up a change once it's
	 * cached; confirmed live serving a day-old commerce7-overrides.css to
	 * every visitor via `cf-cache-status: HIT`, `max-age=604800`, despite
	 * the origin file already having the fix. filemtime() means this
	 * self-updates on every future edit with no separate version const to
	 * remember to bump — unlike a theme's own {PREFIX}_THEME_VERSION,
	 * which every asset here is deliberately independent of, since core
	 * assets change on their own schedule (a core sync), not a theme's.
	 */
	public static function asset_version( string $relative_path ): string {
		$file = get_stylesheet_directory() . $relative_path;
		return file_exists( $file ) ? (string) filemtime( $file ) : '1';
	}

	public function text_domain(): string {
		return (string) $this->get( 'text_domain', $this->slug() );
	}

	public function brand_name(): string {
		return (string) $this->get( 'brand_name', '' );
	}

	/**
	 * A photo to fall back on for any full-bleed CTA band whose own image
	 * is unset — the shared .fw-hero--band shell paints a dark scrim over
	 * whatever background it has, so a band with no photo doesn't degrade
	 * to "a plain colored band", it degrades to "a photo that failed to
	 * load", which is what the Albariza/Heronrest homepage bands were
	 * reported as (2026-09-10). Rather than let every band render callback
	 * decide that independently (and quietly forget to), they all resolve
	 * through here.
	 *
	 * Walks the per-route hero images every theme already configures, most
	 * specific first, and returns '' only when the theme genuinely ships no
	 * photo at all — at which point the caller must drop the band's ph-img
	 * class too, so it falls back to the .ph-* gradient placeholder rather
	 * than to bare color.
	 */
	public function band_image( string ...$preferred_paths ): string {
		$candidates = array_merge( $preferred_paths, array(
			'pages.c7_content.reservation.image',
			'pages.visit.image',
			'pages.estate.hero',
			'pages.c7_content.club.image',
			'pages.c7_content.wines.image',
			'age_gate.bg_image',
		) );
		foreach ( $candidates as $path ) {
			$value = trim( (string) $this->get( $path, '' ) );
			if ( $value !== '' ) return $value;
		}
		return '';
	}

	public function raw(): array {
		return $this->data;
	}
}
