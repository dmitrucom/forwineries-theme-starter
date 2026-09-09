/**
 * GENERATED FILE — do not edit directly. Synced from forwineries-theme-core.
 * Edit the source there and re-run tools/sync-core.sh.
 *
 * Filter/sort interactivity for the Wines Catalog widget
 * (Commerce7\Catalog::render_wines_catalog()). Plain JS, no build step
 * — filtering just toggles the native `hidden` attribute on cards
 * already in the page (all products are rendered server-side up
 * front), sorting just re-appends DOM nodes in the new order. Nothing
 * here touches Commerce7's own widgets — each card's .c7-buy-product
 * mount point is left completely alone.
 */
( function () {
	'use strict';

	function applyState( refs, state ) {
		var visibleCount = 0;
		var search       = state.search.trim().toLowerCase();

		refs.items.forEach( function ( item ) {
			var matchesType     = ! state.type || item.getAttribute( 'data-type' ) === state.type;
			var matchesVarietal = ! state.varietal || item.getAttribute( 'data-varietal' ) === state.varietal;
			// Matches on the card's full visible text (title, subtitle,
			// type/varietal/vintage line, description) rather than one
			// specific field — a plain substring search is enough for a
			// boutique catalog and needs no extra data attributes.
			var matchesSearch = ! search || item.textContent.toLowerCase().indexOf( search ) !== -1;
			var visible        = matchesType && matchesVarietal && matchesSearch;
			item.hidden = ! visible;
			if ( visible ) visibleCount++;
		} );

		if ( refs.empty ) refs.empty.hidden = visibleCount !== 0;

		if ( state.sort ) {
			var visibleItems = refs.items.filter( function ( item ) { return ! item.hidden; } );
			visibleItems.sort( function ( a, b ) { return compareItems( a, b, state.sort ); } );
			visibleItems.forEach( function ( item ) { refs.grid.appendChild( item ); } );
		}
	}

	function compareItems( a, b, sort ) {
		var priceA   = parseInt( a.getAttribute( 'data-price' ), 10 ) || 0;
		var priceB   = parseInt( b.getAttribute( 'data-price' ), 10 ) || 0;
		var vintageA = parseInt( a.getAttribute( 'data-vintage' ), 10 ) || 0;
		var vintageB = parseInt( b.getAttribute( 'data-vintage' ), 10 ) || 0;

		switch ( sort ) {
			case 'featured':
				var featuredA = parseInt( a.getAttribute( 'data-featured' ), 10 ) || 0;
				var featuredB = parseInt( b.getAttribute( 'data-featured' ), 10 ) || 0;
				return featuredB - featuredA;
			case 'price-asc':
				return priceA - priceB;
			case 'price-desc':
				return priceB - priceA;
			case 'vintage-desc':
				// Non-Vintage (0) sorts last regardless of direction — it
				// isn't meaningfully "older" than a dated vintage, just
				// undated.
				return ( vintageB || -1 ) - ( vintageA || -1 );
			case 'vintage-asc':
				return ( vintageA || 9999 ) - ( vintageB || 9999 );
			case 'name-asc':
				var nameA = titleOf( a );
				var nameB = titleOf( b );
				return nameA < nameB ? -1 : nameA > nameB ? 1 : 0;
			default:
				return 0;
		}
	}

	function titleOf( item ) {
		var heading = item.querySelector( 'h3' );
		return heading ? heading.textContent.trim().toLowerCase() : '';
	}

	function initCatalog( catalog ) {
		var grid = catalog.querySelector( '[data-fw-catalog-grid]' );
		if ( ! grid ) return;

		var refs = {
			grid:  grid,
			empty: catalog.querySelector( '[data-fw-catalog-empty]' ),
			items: Array.prototype.slice.call( grid.querySelectorAll( '[data-fw-catalog-item]' ) ),
		};

		var state = { type: '', varietal: '', sort: '', search: '' };

		var typeButtons = Array.prototype.slice.call( catalog.querySelectorAll( '[data-filter-type]' ) );
		typeButtons.forEach( function ( button ) {
			button.addEventListener( 'click', function () {
				state.type = button.getAttribute( 'data-filter-type' ) || '';
				typeButtons.forEach( function ( b ) { b.classList.toggle( 'is-active', b === button ); } );
				applyState( refs, state );
			} );
		} );

		var varietalSelect = catalog.querySelector( '[data-filter-varietal]' );
		if ( varietalSelect ) {
			varietalSelect.addEventListener( 'change', function () {
				state.varietal = varietalSelect.value;
				applyState( refs, state );
			} );
		}

		var sortSelect = catalog.querySelector( '[data-sort]' );
		if ( sortSelect ) {
			sortSelect.addEventListener( 'change', function () {
				state.sort = sortSelect.value;
				applyState( refs, state );
			} );
		}

		var searchInput = catalog.querySelector( '[data-search]' );
		if ( searchInput ) {
			searchInput.addEventListener( 'input', function () {
				state.search = searchInput.value;
				applyState( refs, state );
			} );
		}
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		document.querySelectorAll( '[data-fw-catalog]' ).forEach( initCatalog );
	} );
} )();
