<?php
// GENERATED FILE — do not edit directly. Synced from forwineries-theme-core.
// Edit the source there and re-run tools/sync-core.sh.
namespace ForWineries\Core;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Single entry point. A product theme's functions.php does:
 *
 *   require get_stylesheet_directory() . '/core/src/Autoload.php';
 *   ForWineries\Core\Boot::init( array( 'slug' => 'lh', 'tokens' => [...], ... ) );
 *
 * register() calls below run in a DELIBERATE, EXPLICIT order — this is
 * not a loop over a class list. The one hard constraint so far: tokens
 * must enqueue before anything that emits CSS referencing var(--fw-*),
 * because Commerce7\Integration's CSS-variable bridge deliberately
 * prints its own inline <style> block immediately after DesignTokens's
 * so its --c7-* custom properties resolve against real values — enforced
 * by DesignTokens using wp_enqueue_scripts priority 5 vs. Integration's
 * default priority 10 on the same hook, not by call order here (both
 * closures are attached either way; priority is what actually decides
 * execution order), but DesignTokens::register() is still called first
 * below for readability.
 *
 * Phase 0 is fully complete: every module, including
 * blocks.php/elementor-widgets.php/seo.php — the 3 files deferred
 * earlier because they called directly into Commerce7's render/lookup
 * functions (see docs/ARCHITECTURE.md) — has landed now that Commerce7
 * itself exists.
 *
 * A module that owns a SettingsPage tab (DemoBar, ContentSettings\
 * Framework) registers that tab itself, inside its own register() —
 * SettingsPage::add_tab() just populates a static array read later at
 * render time, so it doesn't matter whether SettingsPage::register()
 * itself runs before or after those calls, only that both happen
 * somewhere in this method. Tab priority controls left-to-right order;
 * matches the original hardcoded setup/content/contact/design/demo
 * sequence, leaving gaps for modules not yet ported.
 */
class Boot {

	public static function init( array $config_array ): void {
		$config = new Config( $config_array );

		self::dequeue_parent_theme_assets();

		DesignTokens::register( $config );
		AgeGate::register( $config );
		Commerce7\WidgetCleanup::register( $config );
		Commerce7\Integration::register( $config );
		Commerce7\Routing::register( $config );
		Commerce7\Catalog::register( $config );
		Commerce7\ReservationWidget::register( $config );
		Commerce7\Blocks::register( $config );
		Commerce7\ElementorWidgets::register( $config );
		Seo::register( $config );
		DemoBar::register( $config );
		Events::register( $config );
		Schema::register( $config );
		PageSetup::register( $config );
		ContactForms::register( $config );
		ContentSettings\Framework::register( $config );
		ContactSettings::register( $config );
		DesignSettingsTab::register( $config );
		ElementorKitDefaults::register( $config );
		SettingsPage::register( $config );

		// Later modules register here, in order. Still growing during
		// Phase 0 — see docs/ARCHITECTURE.md for the planned order.
	}

	/**
	 * WordPress's `template`/`stylesheet` options are set once, when a
	 * theme is activated, and only change on a real re-activation — a
	 * plain `git push` overwriting style.css to drop `Template:
	 * hello-elementor` does NOT update them. Every product theme here
	 * started life as a Hello Elementor child theme, so on any site
	 * where that re-activation hasn't happened yet, WordPress still
	 * treats Hello Elementor as the parent and auto-loads its
	 * theme.css/reset.css/header-footer.css — whose own body/heading
	 * font-family rules silently win the cascade over this theme's own,
	 * since the new standalone functions.php no longer lists them as
	 * enqueue dependencies. Confirmed live: all 4 migrated sites were
	 * rendering system-font body/headings instead of their real brand
	 * fonts because of exactly this. Hello Elementor's own functions.php
	 * gates each stylesheet behind a filter specifically for this —
	 * using it stops the enqueue at the source, regardless of whether
	 * the DB option ever gets fixed via a real theme re-activation.
	 */
	private static function dequeue_parent_theme_assets(): void {
		add_filter( 'hello_elementor_enqueue_style', '__return_false' );
		add_filter( 'hello_elementor_enqueue_theme_style', '__return_false' );
		add_filter( 'hello_elementor_header_footer', '__return_false' );
	}
}
