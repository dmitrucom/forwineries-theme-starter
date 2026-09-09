/**
 * GENERATED FILE — do not edit directly. Synced from forwineries-theme-core.
 * Edit the source there and re-run tools/sync-core.sh.
 *
 * Keeps --fw-bar-h (core/assets/css/demo-bar.css) in sync with the demo
 * bar's REAL height, which varies once its contents wrap to a second
 * line on narrow screens — the CSS-only fallback value is just a
 * reasonable single-line guess. Also handles the "×" session-scoped
 * dismiss button.
 */
( function () {
	"use strict";

	document.addEventListener( "DOMContentLoaded", function () {
		var demoBar = document.querySelector( ".fw-demo-bar" );
		if ( ! demoBar ) return;

		var setDemoBarHeight = function () {
			document.documentElement.style.setProperty( "--fw-bar-h", demoBar.offsetHeight + "px" );
		};

		// Session-scoped dismiss — separate from the permanent "Hide demo
		// bar" admin toggle: closing it holds for the rest of this
		// browsing session across every page, since this is a classic
		// multi-page site, not an SPA, but a fresh tab/visit sees the bar
		// again. try/catch: sessionStorage can throw in a locked-down
		// privacy mode, and this is a cosmetic convenience, not worth
		// breaking the page over.
		var dismissKey = "fwDemoBarDismissed";
		var dismissBar = function () {
			document.documentElement.classList.add( "fw-demo-bar-dismissed" );
			document.documentElement.style.setProperty( "--fw-bar-h", "0px" );
		};

		var wasDismissed = false;
		try {
			wasDismissed = sessionStorage.getItem( dismissKey ) === "1";
		} catch ( e ) {}

		if ( wasDismissed ) {
			dismissBar();
		} else {
			setDemoBarHeight();
			window.addEventListener( "resize", setDemoBarHeight );
		}

		var closeBtn = demoBar.querySelector( ".fw-demo-bar-close" );
		if ( closeBtn ) {
			closeBtn.addEventListener( "click", function () {
				dismissBar();
				try { sessionStorage.setItem( dismissKey, "1" ); } catch ( e ) {}
			} );
		}
	} );
} )();
