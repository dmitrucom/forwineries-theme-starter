/**
 * GENERATED FILE — do not edit directly. Synced from forwineries-theme-core.
 * Edit the source there and re-run tools/sync-core.sh.
 *
 * "More From {Brand}" wine slider (Commerce7\Catalog::render_related_wines())
 * — the track itself is a plain CSS scroll-snap element that already
 * works via native swipe/trackpad scroll with zero JS; this only wires
 * the optional prev/next arrow buttons (scroll by one full visible
 * page, disable at either end).
 */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		document.querySelectorAll( '[data-fw-wine-slider]' ).forEach( function ( slider ) {
			var track = slider.querySelector( '[data-fw-wine-slider-track]' );
			var prev  = slider.querySelector( '[data-fw-wine-slider-prev]' );
			var next  = slider.querySelector( '[data-fw-wine-slider-next]' );
			if ( ! track || ( ! prev && ! next ) ) return;

			var updateArrows = function () {
				var maxScroll = track.scrollWidth - track.clientWidth;
				if ( prev ) prev.disabled = track.scrollLeft <= 1;
				if ( next ) next.disabled = track.scrollLeft >= maxScroll - 1;
			};

			if ( prev ) {
				prev.addEventListener( 'click', function () {
					track.scrollBy( { left: -track.clientWidth, behavior: 'smooth' } );
				} );
			}
			if ( next ) {
				next.addEventListener( 'click', function () {
					track.scrollBy( { left: track.clientWidth, behavior: 'smooth' } );
				} );
			}

			track.addEventListener( 'scroll', updateArrows, { passive: true } );
			window.addEventListener( 'resize', updateArrows );
			updateArrows();
		} );
	} );
} )();
