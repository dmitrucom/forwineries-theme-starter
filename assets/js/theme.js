(function () {
	"use strict";

	document.addEventListener( "DOMContentLoaded", function () {
		// Demo bar height-sync + dismiss is handled entirely by core's own
		// assets/js/demo-bar.js (enqueued by ForWineries\Core\DemoBar) — no
		// per-theme JS needed here. Same for the age gate (core's own
		// assets/js/age-gate.js). Do NOT duplicate either here.

		/**
		 * Mobile nav toggle. These 3 ids/classes are the ONLY selectors in
		 * this file that reach into markup this theme's own header.php
		 * renders — matched here directly against the real ids used there
		 * ("newtheme-nav-toggle" / "newtheme-primary-nav"), not against a
		 * name that used to be different. This is deliberate: the single
		 * most severe bug found building the 5 sibling themes (see
		 * forwineries-theme-core/docs/LESSONS.md, "Vespera's theme.js had
		 * 4 stale selectors left over from a rename pass") was a JS file
		 * quietly left querying an OLD class/id name after markup was
		 * renamed elsewhere — silently matching nothing, with no error, so
		 * hero text stayed permanently invisible on 3 live pages through
		 * two rounds of AI verification. A starter theme authored fresh
		 * has no rename pass to get wrong — keep it that way: if you ever
		 * rename an id/class in header.php or front-page.php, grep this
		 * file for the OLD name too, in the same commit, not as a
		 * follow-up.
		 */
		var toggle = document.getElementById( "newtheme-nav-toggle" );
		var nav    = document.getElementById( "newtheme-primary-nav" );

		if ( toggle && nav ) {
			var setNavOpen = function ( isOpen ) {
				nav.classList.toggle( "is-open", isOpen );
				toggle.setAttribute( "aria-expanded", isOpen ? "true" : "false" );
			};

			toggle.addEventListener( "click", function () {
				setNavOpen( ! nav.classList.contains( "is-open" ) );
			} );

			// Close the mobile menu after tapping a link.
			nav.addEventListener( "click", function ( e ) {
				if ( e.target.tagName === "A" && nav.classList.contains( "is-open" ) ) {
					setNavOpen( false );
				}
			} );

			// Collapse the mobile menu if the viewport is resized back to desktop.
			window.addEventListener( "resize", function () {
				if ( window.innerWidth > 900 && nav.classList.contains( "is-open" ) ) {
					setNavOpen( false );
				}
			} );
		}

		/**
		 * Scroll-reveal — opt-in only: stays a no-op (elements plain and
		 * fully visible, per style.css's default) unless BOTH
		 * IntersectionObserver exists AND the visitor hasn't asked for
		 * reduced motion. "newtheme-reveal-init" on <html> is what
		 * actually switches the CSS animations on (style.css, section
		 * 14) — without it, [data-reveal]'s opacity/transform rules never
		 * apply.
		 *
		 * Every selector below (".fw-hero-content", "[data-reveal]",
		 * ".fw-stat-num") is the real, final, unprefixed fw-* name
		 * front-page.php actually renders — written once, correctly, with
		 * no theme-specific alias to keep in sync.
		 */
		var prefersReducedMotion = window.matchMedia && window.matchMedia( "(prefers-reduced-motion: reduce)" ).matches;
		if ( "IntersectionObserver" in window && ! prefersReducedMotion ) {
			document.documentElement.classList.add( "newtheme-reveal-init" );

			var revealTargets = document.querySelectorAll( "[data-reveal]" );
			var revealObserver = new IntersectionObserver( function ( entries, observer ) {
				entries.forEach( function ( entry ) {
					if ( ! entry.isIntersecting ) return;
					entry.target.classList.add( "is-visible" );
					observer.unobserve( entry.target );
				} );
			}, { rootMargin: "0px 0px -8% 0px", threshold: 0.1 } );
			revealTargets.forEach( function ( el ) {
				var delay = el.getAttribute( "data-reveal-delay" );
				if ( delay ) el.style.setProperty( "--newtheme-reveal-delay", delay + "s" );
				revealObserver.observe( el );
			} );

			// Every hero on the page gets its own content stagger the
			// moment IT individually scrolls into view (not just the
			// above-the-fold one) — a separate observer from the generic
			// [data-reveal] one above, since .fw-hero-content isn't always
			// also marked data-reveal (see 404.php, single-newtheme_event.php).
			var heroObserver = new IntersectionObserver( function ( entries, observer ) {
				entries.forEach( function ( entry ) {
					if ( ! entry.isIntersecting ) return;
					entry.target.classList.add( "is-visible" );
					observer.unobserve( entry.target );
				} );
			}, { threshold: 0.15 } );
			document.querySelectorAll( ".fw-hero-content" ).forEach( function ( el ) {
				heroObserver.observe( el );
			} );

			// Stat numbers (homepage + Estate page) count up from 0 once
			// scrolled into view. Only digits/commas in the original text
			// are treated as the count target; anything else (a stray "+"
			// or "$", or this starter's literal "19XX"/"00" placeholder
			// copy) is left alone rather than guessed at.
			var statEls = document.querySelectorAll( ".fw-stat-num" );
			statEls.forEach( function ( el ) {
				var raw = el.textContent.trim();
				if ( ! /^[0-9,]+$/.test( raw ) ) return;
				var target = parseInt( raw.replace( /,/g, "" ), 10 );
				if ( isNaN( target ) ) return;
				var useGrouping = raw.indexOf( "," ) !== -1;

				var statObserver = new IntersectionObserver( function ( entries, observer ) {
					entries.forEach( function ( entry ) {
						if ( ! entry.isIntersecting ) return;
						observer.unobserve( entry.target );
						var start = null;
						var duration = 1200;
						var step = function ( timestamp ) {
							if ( start === null ) start = timestamp;
							var progress = Math.min( ( timestamp - start ) / duration, 1 );
							var eased = 1 - Math.pow( 1 - progress, 3 ); // ease-out cubic
							var value = Math.round( eased * target );
							el.textContent = useGrouping ? value.toLocaleString( "en-US" ) : String( value );
							if ( progress < 1 ) window.requestAnimationFrame( step );
						};
						window.requestAnimationFrame( step );
					} );
				}, { threshold: 0.6 } );
				statObserver.observe( el );
			} );
		}
	} );
})();
