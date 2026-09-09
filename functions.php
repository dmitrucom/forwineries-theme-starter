<?php
/**
 * ============================================================
 *  forwineries-theme-starter — fill-in-the-blanks starter theme
 * ============================================================
 *
 * PLACEHOLDER SLUG: this whole theme uses "newtheme" / "NEWTHEME" as a
 * literal, find-and-replaceable placeholder slug — in function names
 * (newtheme_fw_config_array), constants (NEWTHEME_THEME_VERSION), CSS
 * classes that are genuinely per-theme (not the shared fw-* vocabulary,
 * see below), text domain, option keys, etc. Building theme #6 starts
 * with a project-wide find/replace of "newtheme" -> your real short
 * slug (e.g. "abc" for "Alder Bend Cellars") and "NEWTHEME" -> the
 * uppercase form, THEN filling in the values below. See
 * NEW-THEME-CHECKLIST.md for the full step-by-step.
 *
 * Standalone theme, not a Hello Elementor child theme —
 * add_theme_support('elementor') is read by the Elementor PLUGIN, not
 * any parent theme (confirmed in forwineries-theme-core/docs/
 * ARCHITECTURE.md). Header/footer are hard-coded, so every page —
 * including ones built with the free Elementor page builder — gets the
 * same header, footer, and global typography/color system with zero
 * Elementor Pro dependency. Do NOT add a "Template: hello-elementor"
 * line to style.css — see docs/LESSONS.md in forwineries-theme-core for
 * why that silently reintroduces a font-cascade bug.
 *
 * core/ is synced from https://github.com/dmitrucom/forwineries-theme-core
 * by that repo's tools/sync-core.sh — never hand-edit anything under
 * core/, edit the source there and re-sync (see NEW-THEME-CHECKLIST.md).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Bump BOTH this constant and style.css's "Version:" header together on
// EVERY change (not just CSS/asset changes) — it's the cache-busting
// query param on every enqueued asset AND the only reliable deploy-
// verification signal available without server access. See LESSONS.md.
define( 'NEWTHEME_THEME_VERSION', '0.1.0' );
define( 'NEWTHEME_THEME_DIR', get_stylesheet_directory() );
define( 'NEWTHEME_THEME_URI', get_stylesheet_directory_uri() );

/**
 * Full config handed to Boot::init() — also required by every
 * page-template stub (they build their own Config from this same
 * array), so this is the one source of truth. Every key below is
 * either a sensible generic default or an obvious placeholder — see
 * NEW-THEME-CHECKLIST.md for the fill-in order.
 */
function newtheme_fw_config_array() {
	return array(
		// Short, unique, lowercase, no separators — used to namespace
		// wp_options keys, cookie names, the event CPT slug, nav menu
		// locations, and Elementor global-style ids on a LIVE site (see
		// forwineries-theme-core/core/src/Config.php's docblock for why
		// only these 5 things stay slug-scoped). Changing this on an
		// already-live site loses saved settings — pick it once, up front.
		'slug'        => 'newtheme',
		// Shown in page titles, the footer copyright line, the age gate,
		// and anywhere else $config->brand_name() is read.
		'brand_name'  => '[Your Winery Name]',
		// Usually just 'slug' again — used for translation strings.
		'text_domain' => 'newtheme',

		// The canonical 26-token vocabulary (forwineries-theme-core/docs/
		// DESIGN-TOKEN-SCHEMA.md) — ALL 26 keys are required; core's
		// DesignTokens class emits whatever you put here as one :root
		// block, enqueued ahead of style.css. Values below are a neutral,
		// obviously-placeholder palette (greyscale + a plain blue accent)
		// so a freshly cloned site LOOKS like a placeholder, not like a
		// half-finished real theme. Replace every value with real brand
		// colors/fonts — do not just tweak these.
		'tokens' => array(
			'cream'         => '#f5f5f4', // page background
			'cream_raised'  => '#e9e7e4', // alternating/secondary section background
			'charcoal'      => '#232220', // body text / headings
			'charcoal_soft' => '#6b6864', // muted/secondary text
			'clay'          => '#3a5a6b', // primary accent (the brand color) — PLACEHOLDER BLUE, replace
			'clay_dark'     => '#294049', // clay's hover/darken state
			'olive'         => '#6b6864', // secondary accent, used sparingly
			'ink'           => '#1b1a19', // deepest/highest-contrast tone (footers, dark bands)
			'line'          => '#dcd8d4', // one hairline-border/rule color
			'font_heading'  => 'Georgia, "Times New Roman", serif',       // PLACEHOLDER — pick 2 real Google Fonts
			'font_body'     => '"Helvetica Neue", Arial, sans-serif',     // PLACEHOLDER
			'font_accent'   => 'Georgia, "Times New Roman", serif',       // PLACEHOLDER — may equal font_heading
			'radius'        => '4px',   // cards & images
			'radius_sm'     => '2px',   // form fields & badges
			'radius_lg'     => '8px',   // heroes & oversized media
			'container'     => '1240px',
			'space_xs'      => '8px',
			'space_sm'      => '16px',
			'space_md'      => '32px',
			'space_lg'      => 'clamp(44px, 6vw, 72px)',
			'space_xl'      => 'clamp(72px, 10vw, 136px)',
			'ease'          => 'cubic-bezier(0.16, 1, 0.3, 1)',
			'ease_spring'   => 'cubic-bezier(0.34, 1.4, 0.44, 1)',
			'shadow_sm'     => '0 1px 0 var(--fw-line)',
			'shadow_md'     => '0 8px 24px rgba(0, 0, 0, 0.06)',
			'shadow_lg'     => '0 18px 40px rgba(0, 0, 0, 0.08)',
		),
		// PLACEHOLDER — swap for your 2-3 real Google Fonts' CSS2 URL
		// (fonts.google.com > select fonts > "Get embed code" > CSS2).
		'fonts_url'    => 'https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600&family=Public+Sans:wght@400;500;600&display=swap',
		'fonts_handle' => 'newtheme-fonts',

		// PLACEHOLDER. IMPORTANT — read before filling in: 'name' feeds
		// Commerce7\Integration::render_club_join()'s default button text,
		// which is ALWAYS "Join the {name}" — so 'name' must NEVER include
		// a leading "The", or the button reads "Join the The X Society".
		// Put "The" only in 'eyebrow' (which is displayed as-is, not
		// wrapped in "Join the ..."). This bit Fault Line Cellars for real
		// on a live site — see forwineries-theme-core/docs/LESSONS.md
		// ("club's default join-button text has a grammar trap").
		//   WRONG: 'name' => 'The Founders Circle'  -> "Join the The Founders Circle"
		//   RIGHT: 'name' => 'Founders Circle', 'eyebrow' => 'The Founders Circle'
		'club' => array(
			'name'    => 'Wine Club',          // <- must NOT start with "The"
			'eyebrow' => 'Membership',
		),

		// Two placeholder seed events, created once on activation so
		// /events isn't empty on a fresh install (see core/src/Events.php).
		'events' => array(
			'seed' => array(
				array(
					'title'   => __( 'Harvest Celebration', 'newtheme' ),
					'content' => __( 'Join us for our annual harvest celebration — replace this placeholder with your own event copy.', 'newtheme' ),
					'months'  => 2,
					'time'    => '5:00 PM – 9:00 PM',
				),
				array(
					'title'   => __( 'Winter Barrel Tasting', 'newtheme' ),
					'content' => __( 'A look inside the cellar — replace this placeholder with your own event copy.', 'newtheme' ),
					'months'  => 4,
					'time'    => '2:00 PM – 4:30 PM',
				),
			),
		),

		// Shown in the forwineries.com demo bar / browser tab title while
		// this is an unpurchased demo — irrelevant once a client owns the
		// site outright, but harmless to leave filled in.
		'demo' => array(
			'current'   => '[Your Winery Name]',
			'full_name' => '[Your Winery Name]',
		),

		// PLACEHOLDER contact details — shown in the header topbar,
		// footer, Visit/Contact pages, and JSON-LD schema.
		'contact' => array(
			'fields' => array(
				'address_line'     => array( __( 'Address line (shown in header & footer)', 'newtheme' ), '123 Vineyard Lane, Somewhere, CA 00000' ),
				'phone_display'    => array( __( 'Phone (display)', 'newtheme' ), '(555) 555-0100' ),
				'phone_tel'        => array( __( 'Phone (tel: link, digits only, e.g. +15555550100)', 'newtheme' ), '+15555550100' ),
				'email'            => array( __( 'Contact email', 'newtheme' ), 'hello@example.com' ),
				'social_instagram' => array( __( 'Instagram URL (leave blank to hide the icon)', 'newtheme' ), '' ),
				'social_facebook'  => array( __( 'Facebook URL (leave blank to hide the icon)', 'newtheme' ), '' ),
			),
		),

		// TODO — REQUIRED: core's own AgeGate default 'bg_image' points at
		// a file ("/assets/images/age-gate-bg.jpg") that no theme in this
		// family has ever actually shipped — leaving this unset means the
		// age gate silently renders with NO background photo at all. This
		// bit Larkhaven/Fault Line/Albariza for real; see LESSONS.md
		// ("AgeGate.bg_image pointed at a file that never existed").
		// Point this at one of YOUR real, brand-safety-verified photos
		// (see assets/images/README.md) before going live.
		'age_gate' => array(
			'bg_image' => '', // TODO: NEWTHEME_THEME_URI . '/assets/images/your-hero-photo.jpg'
		),

		// Per-route hero/section images — real per-theme asset paths, no
		// sensible generic default. Every path below is a placeholder;
		// see assets/images/README.md before pointing these at real files.
		'pages' => array(
			// Homepage hero / reservation-band photos — read directly by
			// front-page.php (not a core template), so there's no
			// matching key in any core class. TODO before going live.
			'home' => array(
				'hero'        => '', // TODO: NEWTHEME_THEME_URI . '/assets/images/your-hero-photo.jpg'
				'reservation' => '', // TODO
			),
			'c7_content' => array(
				'wines'       => array(
					'image'   => '', // TODO: hero photo for /collection/wines/
					'eyebrow' => __( 'Our Collection', 'newtheme' ),
					'heading' => __( 'All Wines', 'newtheme' ),
					'intro'   => __( 'Replace this placeholder with a short intro to your wine list.', 'newtheme' ),
				),
				'reservation' => array( 'image' => '' ), // TODO
				'club'        => array( 'image' => '' ), // TODO
				'profile'     => array( 'image' => '' ), // TODO
			),
			'visit'  => array( 'image' => '' ), // TODO
			'estate' => array(
				'hero'               => '', // TODO
				'history'            => '', // TODO
				'winemaking'         => '', // TODO
				'history_heading'    => __( 'Our History', 'newtheme' ),
				'winemaking_heading' => __( 'Our Winemaking', 'newtheme' ),
			),
			'gift_cards' => array( 'image' => '' ), // TODO
			// 'marketing' deliberately omitted — PageSetup's converged
			// defaults (estate="How We Work", team="Team", visit="Visit",
			// gift-cards="Gift Cards", contact="Contact",
			// trade-press="Trade & Press") are fine as a starting point.
			// Override only the titles you want to change.
		),

		// A flat, generic 4-item primary nav + a slightly longer footer
		// menu — matches PageSetup's own converged default almost
		// exactly, listed explicitly here (rather than omitted) since
		// 'menus' is an all-or-nothing config key and this makes the
		// starter's actual rendered nav obvious without reading core.
		'menus' => array(
			'newtheme-primary' => array(
				'name'  => __( 'Primary Menu', 'newtheme' ),
				'items' => array(
					array( __( 'Shop', 'newtheme' ), home_url( '/collection/wines/' ) ),
					array( __( 'Our Story', 'newtheme' ), home_url( '/estate' ) ),
					array( __( 'Wine Club', 'newtheme' ), home_url( '/club' ) ),
					array( __( 'Visit', 'newtheme' ), home_url( '/visit' ) ),
				),
			),
			'newtheme-footer' => array(
				'name'  => __( 'Footer Menu', 'newtheme' ),
				'items' => array(
					array( __( 'Our Wines', 'newtheme' ), home_url( '/collection/wines/' ) ),
					array( __( 'Our Story', 'newtheme' ), home_url( '/estate' ) ),
					array( __( 'Our Team', 'newtheme' ), home_url( '/team' ) ),
					array( __( 'Wine Club', 'newtheme' ), home_url( '/club' ) ),
					array( __( 'Gift Cards', 'newtheme' ), home_url( '/gift-cards' ) ),
					array( __( 'Visit', 'newtheme' ), home_url( '/visit' ) ),
					array( __( 'Trade & Press', 'newtheme' ), home_url( '/trade-press' ) ),
				),
			),
		),

		// Elementor Global Colors/Fonts — brand-voice labels only, values
		// come from 'tokens' above.
		'elementor_kit' => array(
			'color_names' => array(
				'cream'         => 'Cream',
				'cream_raised'  => 'Cream Raised',
				'charcoal'      => 'Charcoal',
				'charcoal_soft' => 'Charcoal Soft',
				'clay'          => 'Accent',
				'clay_dark'     => 'Accent Dark',
			),
		),

		// Design tab (wp-admin > NEWTHEME Vineyards > Design): which
		// tokens a client can restyle themselves, plus optional preset
		// palettes/fonts. Left minimal/generic here — add real presets
		// once you have real brand alternates worth offering.
		'design' => array(
			'color_tokens' => array(
				'cream'         => __( 'Page background', 'newtheme' ),
				'cream_raised'  => __( 'Alternating section background', 'newtheme' ),
				'charcoal'      => __( 'Body text / headings', 'newtheme' ),
				'charcoal_soft' => __( 'Muted/secondary text', 'newtheme' ),
				'clay'          => __( 'Primary accent', 'newtheme' ),
				'clay_dark'     => __( 'Primary accent — hover/dark shade', 'newtheme' ),
			),
			'palette_presets' => array(),
			'font_presets'    => array(),
		),

		// 100% per-theme content schema — see inc/content-fields.php.
		'content_fields' => require NEWTHEME_THEME_DIR . '/inc/content-fields.php',
	);
}

function newtheme_theme_setup() {
	load_theme_textdomain( 'newtheme', NEWTHEME_THEME_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'custom-logo', array(
		'height'      => 80,
		'width'       => 240,
		'flex-height' => true,
		'flex-width'  => true,
	) );
	add_theme_support( 'align-wide' );
	add_theme_support( 'elementor' );

	add_image_size( 'newtheme-wine-card', 500, 700, true );
	add_image_size( 'newtheme-wide', 1600, 900, true );
}
add_action( 'after_setup_theme', 'newtheme_theme_setup' );

function newtheme_register_widget_areas() {
	for ( $i = 1; $i <= 3; $i++ ) {
		register_sidebar( array(
			/* translators: %d: footer column number */
			'name'          => sprintf( __( 'Footer Column %d', 'newtheme' ), $i ),
			'id'            => 'newtheme-footer-' . $i,
			'before_widget' => '<div class="newtheme-footer-widget">',
			'after_widget'  => '</div>',
			'before_title'  => '<h4>',
			'after_title'   => '</h4>',
		) );
	}
}
add_action( 'widgets_init', 'newtheme_register_widget_areas' );

/**
 * Styles & scripts. No parent-theme stylesheet to load first — this
 * theme is standalone (see file header above).
 */
function newtheme_enqueue_assets() {
	$config = newtheme_fw_config_array();

	wp_enqueue_style(
		$config['fonts_handle'],
		$config['fonts_url'],
		array(),
		null
	);

	wp_enqueue_style(
		'newtheme-style',
		get_stylesheet_uri(),
		array( $config['fonts_handle'] ),
		NEWTHEME_THEME_VERSION
	);

	wp_enqueue_script(
		'newtheme-theme',
		NEWTHEME_THEME_URI . '/assets/js/theme.js',
		array(),
		NEWTHEME_THEME_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'newtheme_enqueue_assets' );

add_action( 'elementor/editor/after_enqueue_styles', function () {
	$config = newtheme_fw_config_array();
	wp_enqueue_style( $config['fonts_handle'] );
} );

require NEWTHEME_THEME_DIR . '/core/src/Autoload.php';
/**
 * Called directly here (not hooked) so every module's own internal
 * after_setup_theme calls (e.g. PageSetup's register_nav_menus()) get
 * added before that hook fires — hooking Boot::init() itself to
 * after_setup_theme would risk those sub-registrations targeting a
 * priority WordPress has already passed. See any of the 5 sibling
 * product themes' functions.php for the same pattern.
 */
ForWineries\Core\Boot::init( newtheme_fw_config_array() );

function newtheme_is_elementor_built( $post_id ) {
	return ForWineries\Core\ElementorDefer::is_built( (int) $post_id );
}

function newtheme_front_page_is_elementor_built() {
	return ForWineries\Core\ElementorDefer::is_front_page_built();
}

function newtheme_render_breadcrumbs( $items ) {
	$config = new ForWineries\Core\Config( newtheme_fw_config_array() );
	ForWineries\Core\Breadcrumbs::render( $config, $items );
}

require NEWTHEME_THEME_DIR . '/inc/blog-seed-content.php';
