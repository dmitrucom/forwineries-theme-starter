/**
 * Shared motion engine. Pairs with core/assets/css/motion.css.
 *
 * Additive by design: four of the five themes ship their own scroll-reveal
 * in assets/js/theme.js and keep running untouched. This adds only what
 * none of them had — the html.fw-motion gate, a prefix-agnostic marker
 * class for shared CSS, a reveal system for the one theme without one
 * (Larkhaven, opted in from config), grid stagger for all five, and the
 * hero photo layer.
 *
 * Shared contract: every reveal system pairs data-{prefix}-reveal with
 * --{prefix}-reveal-delay. That pairing is what lets the stagger pass
 * below work without knowing any theme's name.
 *
 * window.fwMotion is printed in <head> by Motion.php from the Motion
 * settings tab: { enabled, autoReveal, off: [effect ids],
 * placementsOff: [placement ids] }.
 */
( function () {
	"use strict";

	// Deferred to DOMContentLoaded because each theme's theme.js wraps its
	// own body the same way. Footer scripts execute at parse time, so
	// running immediately here would read <html> before the theme had
	// stamped its {prefix}-reveal-init class on it. Listeners fire in
	// registration order and theme.js is enqueued first (priority 10 vs
	// this file's 20), so this always lands after it.
	function ready( fn ) {
		if ( document.readyState === "loading" ) {
			document.addEventListener( "DOMContentLoaded", fn );
		} else {
			fn();
		}
	}

	ready( function () {
		var root = document.documentElement;
		var settings = window.fwMotion || {};

		// Gate 1 of 2 (see motion.css). Bail completely rather than
		// partially: the page then stays exactly as the server sent it,
		// fully visible, never half-faded. The site-wide switch from the
		// Motion settings tab bails the same way.
		var allowsMotion = ! ( window.matchMedia && window.matchMedia( "(prefers-reduced-motion: reduce)" ).matches );
		if ( ! allowsMotion || ! ( "IntersectionObserver" in window ) || settings.enabled === false ) return;

		root.classList.add( "fw-motion" );

		// Effects switched off in settings become html.fw-off-{id}, which
		// motion.css and each theme's own motion rules gate on. Kept as
		// classes rather than generated CSS so every rule stays in one
		// stylesheet.
		var off = {};
		( settings.off || [] ).forEach( function ( id ) {
			off[ id ] = true;
			root.classList.add( "fw-off-" + id );
		} );

		// Placements: element groups the client has excluded from scroll
		// reveal. Ids match Motion::placements(); selectors live here so
		// PHP never carries markup knowledge.
		var PLACEMENTS = {
			headings:     '.fw-section-head, [class*="-email-capture"]',
			hero:         ".fw-hero-content",
			photos:       ".fw-split-media, .fw-split-text, div.ph-img",
			stats:        ".fw-stats-row",
			wines:        '.fw-wine-card, [class*="-wine-editorial"] > *',
			club:         ".fw-club-card, .fw-club-tier-card",
			blog:         '[class*="-blog-card"]',
			gallery:      '[class*="-gallery-tile"], [class*="-gallery-mosaic"] > *',
			testimonials: '[class*="-testimonial-card"]',
			team:         ".fw-team-card",
			events:       '[class*="-event-card"]'
		};
		var placementsOff = ( settings.placementsOff || [] ).filter( function ( id ) {
			return id === "commerce" || PLACEMENTS[ id ];
		} );
		var heroOff = placementsOff.indexOf( "hero" ) !== -1;

		var placed = function ( el ) {
			for ( var i = 0; i < placementsOff.length; i++ ) {
				var id = placementsOff[ i ];
				if ( id === "commerce" ) {
					if ( el.closest( "#c7-content" ) ) return false;
				} else if ( el.matches( PLACEMENTS[ id ] ) ) {
					return false;
				}
			}
			return true;
		};

		// Each self-revealing theme stamps <html> with {prefix}-reveal-init
		// and marks elements with data-{prefix}-reveal. Reading the prefix
		// back gives one precise selector instead of hardcoding four theme
		// names in shared code.
		var initMatch = root.className.match( /(?:^|\s)([a-z]+)-reveal-init(?:\s|$)/ );
		var revealAttr = initMatch ? "data-" + initMatch[ 1 ] + "-reveal" : "data-fw-reveal";

		// Text sequence (motion.css rule 7). Inside a text block the eyebrow
		// and heading land first, then every following element in turn; a
		// hero headline additionally builds word by word from masked spans.
		// Each child gets --fw-seq, its step in the sequence, and the block
		// gets .fw-sequenced, which also releases it from the block-level
		// reveal so the copy is not animated twice. textContent only for
		// the split: hero headings are escaped plain text in every template.
		var SEQUENCE_SELECTOR = '.fw-hero-content, .fw-section-head, .fw-split-text, [class*="-email-capture"]';

		var splitWords = function ( heading ) {
			if ( heading.classList.contains( "fw-kinetic" ) ) return;
			var words = heading.textContent.trim().split( /\s+/ );
			if ( words.length < 2 ) return;
			heading.textContent = "";
			words.forEach( function ( word, i ) {
				var mask = document.createElement( "span" );
				mask.className = "fw-word";
				var inner = document.createElement( "span" );
				inner.textContent = word;
				inner.style.setProperty( "--fw-word-delay", ( 0.1 + i * 0.06 ) + "s" );
				mask.appendChild( inner );
				heading.appendChild( mask );
				if ( i < words.length - 1 ) heading.appendChild( document.createTextNode( " " ) );
			} );
			heading.classList.add( "fw-kinetic" );
		};

		var sequence = function ( scope ) {
			if ( off.text ) return;
			scope.querySelectorAll( SEQUENCE_SELECTOR ).forEach( function ( block ) {
				if ( block.classList.contains( "fw-sequenced" ) ) return;
				// Only a block something will mark visible; otherwise its
				// children would stay hidden.
				if ( ! block.classList.contains( "fw-revealable" ) ) return;
				var step = 0;
				var headed = false;
				Array.prototype.forEach.call( block.children, function ( child ) {
					var isHeading = /^H[1-6]$/.test( child.tagName );
					// Eyebrow and heading read as one header, so both take
					// step 0; everything after the heading counts up.
					if ( ! headed && ( isHeading || child.classList.contains( "fw-eyebrow" ) ) ) {
						child.style.setProperty( "--fw-seq", 0 );
						if ( isHeading ) headed = true;
						return;
					}
					child.style.setProperty( "--fw-seq", ++step );
				} );
				if ( block.classList.contains( "fw-hero-content" ) ) {
					var h1 = block.querySelector( ":scope > h1" );
					if ( h1 ) splitWords( h1 );
				}
				block.classList.add( "fw-sequenced" );
			} );
		};

		// Auto-reveal is config-driven, not sniffed. Sniffing would make
		// correctness depend on script order, and a theme whose own JS
		// failed to load would silently get two reveal systems on the same
		// elements. Tagging shared-vocabulary selectors here is also what
		// keeps this a zero-template-edit change.
		if ( settings.autoReveal ) {
			var REVEAL_SELECTOR = [
				".fw-section-head",
				".fw-split-media",
				".fw-split-text",
				".fw-hero-content",
				".fw-stats-row",
				'[class*="-email-capture"]',
				'[class*="-press-strip-outer"]',
				'[class*="-blog-card"]',
				'[class*="-gallery-tile"]',
				'[class*="-testimonial-card"]',
				".fw-wine-card",
				".fw-club-card",
				// Added with the Phase 4 flagship widgets (Membership Tiers,
				// Upcoming Events, Team) — none of these three match an
				// existing wildcard above, so each needs its own entry.
				// [class*="-event-card"] doubles as the hook motion.css's
				// own event-specific slower reveal (rule 6) keys off, so
				// this class name is shared with that file on purpose.
				".fw-team-card",
				".fw-club-tier-card",
				'[class*="-event-card"]',
				// Commerce7 product page. These are injected client-side,
				// long after the first scan — the rescan below is what
				// actually reaches them.
				".pdp-wine-images",
				".pdp-key-features"
			].join( "," );

			// Measured fresh rather than read from --fw-bar-h: that CSS var
			// is written by demo-bar.js, and script order between the two
			// isn't guaranteed (neither declares a dependency on the
			// other), so reading it here could race a page that hasn't run
			// demo-bar.js yet. rootMargin's top is negative by this amount
			// so an element isn't "intersecting" — and doesn't fire its
			// reveal — until it has actually cleared the sticky header (and
			// demo bar, if active), not merely the raw viewport edge. Found
			// 2026-09-11: section heads were revealing (and finishing)
			// while still visually tucked under #fw-header, so by the time
			// they scrolled clear there was nothing left to see happen.
			var stickyChromeHeight = function () {
				var h = 0;
				var header = document.getElementById( "fw-header" );
				if ( header ) h += header.offsetHeight;
				var bar = document.querySelector( ".fw-demo-bar" );
				if ( bar && ! root.classList.contains( "fw-demo-bar-dismissed" ) ) h += bar.offsetHeight;
				return h;
			};

			// Bottom margin was -8% (required an element 8% further into
			// the viewport than its raw edge before revealing) — a hole at
			// the bottom of the screen the visitor scrolled straight past
			// blank. +10% instead fires the reveal a little before the
			// element's true edge crosses into view, so the animation is
			// mid-flight (or done) by the time it's actually visible rather
			// than starting only once it's already on screen.
			var observer = new IntersectionObserver( function ( entries, obs ) {
				entries.forEach( function ( entry ) {
					if ( ! entry.isIntersecting ) return;
					entry.target.classList.add( "is-visible" );
					obs.unobserve( entry.target ); // one-shot; re-firing on scroll-up reads as a glitch
				} );
			}, { rootMargin: "-" + stickyChromeHeight() + "px 0px 10% 0px", threshold: 0.1 } );

			var tag = function ( scope ) {
				scope.querySelectorAll( REVEAL_SELECTOR ).forEach( function ( el ) {
					// The theme's own templates already tag this one; leave
					// it to its own system rather than running two over it.
					if ( initMatch && el.hasAttribute( revealAttr ) ) return;
					if ( el.hasAttribute( "data-fw-reveal" ) ) return; // rescan: already handled
					if ( ! placed( el ) ) return;
					el.setAttribute( "data-fw-reveal", "" );
					el.classList.add( "fw-revealable" );
					// Copy beside a photo lands a beat after it, matching the
					// delay the hand-tagged templates use.
					if ( ! off.stagger && el.matches( ".fw-split-text" ) ) el.style.setProperty( "--fw-reveal-delay", "0.15s" );

					// Anything already on screen is marked visible and never
					// observed — otherwise the top of the page fades in on
					// every load, which reads as a slow site.
					var box = el.getBoundingClientRect();
					if ( box.top < window.innerHeight && box.bottom > 0 ) {
						el.classList.add( "is-visible" );
						return;
					}
					observer.observe( el );
				} );
			};

			tag( document );

			// Commerce7 renders the product page, cart, club and account
			// routes into #c7-content from its own script, after this scan
			// has run — so a one-shot scan reaches none of it and those
			// pages had no motion on any theme. Rescan on mutation, batched
			// into a frame so a burst of inserts costs one pass. tag() skips
			// anything already tagged, so repeats are cheap.
			var c7 = document.getElementById( "c7-content" );
			if ( c7 && "MutationObserver" in window ) {
				var queued = false;
				new MutationObserver( function () {
					if ( queued ) return;
					queued = true;
					window.requestAnimationFrame( function () {
						queued = false;
						tag( c7 );
						sequence( c7 );
					} );
				} ).observe( c7, { childList: true, subtree: true } );
			}
		}

		var delayAttr = revealAttr + "-delay";
		var delayProp = "--" + revealAttr.slice( 5 ) + "-delay";

		// CSS has no "attribute ending in -reveal" selector, so shared
		// rules that need the pre-reveal state key off this marker rather
		// than listing every theme's attribute. The effective delay is
		// mirrored to a shared --fw-reveal-delay for the same reason: it
		// gives core CSS one prefix-agnostic handle on the cascade.
		// Both tagging systems, not just the theme's own: on a theme that
		// also runs the auto pass, its templates cover the homepage and the
		// auto pass covers every page they don't, so an element may carry
		// either attribute. Querying only the theme's own is what left the
		// four self-revealing themes with no motion anywhere except their
		// homepage.
		var markSelector = "[data-fw-reveal]";
		if ( initMatch ) markSelector += ",[" + revealAttr + "]";
		document.querySelectorAll( markSelector ).forEach( function ( el ) {
			if ( ! placed( el ) ) return;
			el.classList.add( "fw-revealable" );
			var authored = el.getAttribute( delayAttr );
			if ( authored && ! off.stagger ) el.style.setProperty( "--fw-reveal-delay", authored + "s" );
		} );

		sequence( document );

		// Hero photo layer. The photo is a background-image on the hero
		// <section> itself, so it cannot be transformed without carrying the
		// headline with it — this moves it onto its own layer underneath the
		// content so motion.css can animate it alone. Injected rather than
		// added to eight page templates across five themes.
		//
		// Two nested elements: the outer clips, the inner scales. .fw-hero--band
		// runs overflow:visible so the Commerce7 calendar can escape the card,
		// so the hero itself cannot be relied on to clip the scaled photo.
		var heroObserver = new IntersectionObserver( function ( entries, obs ) {
			entries.forEach( function ( entry ) {
				if ( ! entry.isIntersecting ) return;
				entry.target.classList.add( "is-visible" );
				obs.unobserve( entry.target );
			} );
		}, { threshold: 0.05 } );

		// Skipped entirely when neither photo effect can run: an inert
		// layer would still cost a composited surface per hero.
		var heroLayer = ! heroOff && ! ( off[ "hero-photo" ] && off[ "hero-lift" ] );
		var heroes = heroLayer ? document.querySelectorAll( ".fw-hero.ph-img, div.ph-img.ph-hero" ) : [];
		heroes.forEach( function ( hero ) {
			var bg = hero.style.backgroundImage;
			// Inline only: a hero whose photo comes from a stylesheet would
			// leave the layer transparent and the section's own background
			// showing through unanimated, which is worse than doing nothing.
			if ( ! bg || bg === "none" ) return;
			if ( hero.querySelector( ".fw-hero-media" ) ) return;

			var media = document.createElement( "div" );
			media.className = "fw-hero-media";
			var inner = document.createElement( "div" );
			inner.className = "fw-hero-media-inner";
			inner.style.backgroundImage = bg;
			media.appendChild( inner );
			hero.insertBefore( media, hero.firstChild );
			hero.classList.add( "fw-has-media" );

			heroObserver.observe( media );
		} );

		// Stagger. Grid items all cross the viewport threshold within a few
		// milliseconds, so without this a row lands as one block.
		//
		// The delay is written to the theme's own --{prefix}-reveal-delay,
		// which each theme declares inside its reveal rule. A shared
		// property applied from a standalone rule would instead land on the
		// element's whole transition list, delaying the hover these cards
		// also carry.
		var STAGGER_SELECTOR = [
			".fw-wine-grid",
			".fw-club-grid",
			".fw-social-grid",
			".fw-stats-row",
			'[class*="-blog-grid"]',
			'[class*="-social-grid"]',
			'[class*="-gallery-mosaic"]',
			'[class*="-wine-editorial"]',
			'[class*="-terroir-grid"]',
			".fw-team-grid",
			".fw-club-tier-grid",
			".fw-testimonial-grid",
			".fw-upcoming-events-grid"
		].join( "," );

		// Capped: past ~6 items a cascade reads as lag, and on a long
		// catalog grid the last card would wait seconds after it is visible.
		var STAGGER_CAP = 6;
		var STAGGER_STEP = 0.09;

		var grids = off.stagger ? [] : document.querySelectorAll( STAGGER_SELECTOR );
		grids.forEach( function ( grid ) {
			// Press strips are infinite linear loops; a per-child entrance
			// delay would fight the loop.
			if ( grid.className.indexOf( "press-strip" ) !== -1 ) return;

			var index = 0;
			Array.prototype.forEach.call( grid.children, function ( child ) {
				if ( ! child.classList.contains( "fw-revealable" ) ) return;
				// A hand-authored delay always wins; several themes set
				// those deliberately and an automatic one would stack.
				if ( child.hasAttribute( delayAttr ) ) return;
				var delay = ( Math.min( index, STAGGER_CAP ) * STAGGER_STEP ) + "s";
				child.style.setProperty( delayProp, delay );
				if ( delayProp !== "--fw-reveal-delay" ) child.style.setProperty( "--fw-reveal-delay", delay );
				index++;
			} );
		} );
	} );
}() );
