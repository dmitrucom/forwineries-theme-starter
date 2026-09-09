/**
 * GENERATED FILE — do not edit directly. Synced from forwineries-theme-core.
 * Edit the source there and re-run tools/sync-core.sh.
 *
 * Cleans up two Commerce7-tenant data artifacts that this theme cannot
 * reach server-side, because they're rendered client-side by Commerce7's
 * own widget script (commerce7.js) directly from the C7 API response —
 * not by our PHP:
 *
 *   1. "Sample - " title prefixes — Commerce7 seeds a fresh product
 *      catalog with placeholder titles like "Sample - 2015 Chardonnay".
 *      Our REST-backed teasers (the not-yet-ported Commerce7\Catalog)
 *      already strip this server-side; this covers the same prefix
 *      wherever Commerce7's OWN widgets render a title instead (the
 *      single product page, cart line items, order history) — anywhere
 *      matching the confirmed .c7-product__title / .c7-order-item__title
 *      classes, plus the product-detail page's <h1> inside #c7-content.
 *
 *   2. Lorem-ipsum placeholder body copy left over in a product's
 *      Content field in Commerce7 admin (including the "RLorem ipsum…"
 *      glued-prefix typo), swapped for a short, generic, varietal-aware
 *      tasting note built from the product's own title. Deliberately
 *      generic (no invented awards/scores/vintage-specific claims) since
 *      this runs for any wine in any tenant's catalog; the client-side
 *      swap is a safety net, not a substitute for writing real tasting
 *      notes in Commerce7 admin.
 *
 * Runs on every page (cheap no-op if #c7-content/.c7-product__title etc.
 * never appear) and re-runs on every DOM mutation inside #c7-content,
 * since Commerce7's router re-renders that mount client-side on every
 * shop navigation without a full page reload.
 *
 * Confirmed near-byte-identical across all 5 original themes before
 * porting (only a doc-comment function reference differed per theme).
 */
(function () {
	"use strict";

	var TITLE_PREFIX_RE = /^\s*sample\s*-\s*/i;
	var LOREM_RE = /lorem\s+ipsum/i;

	var VARIETAL_NOTES = [
		[ "cabernet", "This Cabernet Sauvignon shows dark currant and dried herb up front, with a firm, structured tannin backbone built to reward a few years in the cellar." ],
		[ "chardonnay", "This Chardonnay balances orchard fruit and a touch of toasted oak with a bright, mineral-driven finish." ],
		[ "pinot", "This Pinot Noir is light on its feet — red cherry and forest floor, with silky, food-friendly tannins." ],
		[ "merlot", "This Merlot is plush and round, all soft dark plum and cocoa, drinking beautifully on release." ],
		[ "sauvignon blanc", "This Sauvignon Blanc is crisp and citrus-forward, with the bright acidity to match a warm afternoon." ],
		[ "rosé", "This rosé is pale, dry, and refreshing — red berry and stone fruit over a clean, mineral finish." ],
		[ "syrah", "This Syrah leans savory — dark berry, cracked pepper, and a smoky edge from time in oak." ],
		[ "zinfandel", "This Zinfandel is generous and spice-driven, brambly fruit balanced by a warm, peppery finish." ],
		[ "rieseling", "This Riesling is fragrant and lively, with orchard-fruit sweetness kept in check by real acidity." ],
		[ "riesling", "This Riesling is fragrant and lively, with orchard-fruit sweetness kept in check by real acidity." ],
	];

	function fallbackNote( titleText ) {
		var lower = ( titleText || "" ).toLowerCase();
		for ( var i = 0; i < VARIETAL_NOTES.length; i++ ) {
			if ( lower.indexOf( VARIETAL_NOTES[ i ][ 0 ] ) !== -1 ) {
				return VARIETAL_NOTES[ i ][ 1 ] + " Best enjoyed with a meal, or on its own after a long day in the vineyard.";
			}
		}
		return "Estate-grown and bottled in small lots, this wine reflects the site it comes from — ask us about it on your next visit.";
	}

	function cleanTitleEl( el ) {
		if ( ! el || el.childElementCount > 0 ) return;
		var text = el.textContent;
		if ( TITLE_PREFIX_RE.test( text ) ) {
			el.textContent = text.replace( TITLE_PREFIX_RE, "" );
		}
	}

	function cleanLoremEl( el, titleText ) {
		if ( ! el || ! LOREM_RE.test( el.textContent || "" ) ) return;
		el.textContent = fallbackNote( titleText );
	}

	function sweep( root ) {
		if ( ! root || ! root.querySelectorAll ) return;

		var titleSelectors = ".c7-product__title, .c7-order-item__title";
		var titleEls = root.querySelectorAll( titleSelectors );
		for ( var i = 0; i < titleEls.length; i++ ) cleanTitleEl( titleEls[ i ] );

		// Bare product-detail page: Commerce7 renders one <h1> for the
		// product title directly inside #c7-content, with no dedicated
		// class confirmed in this theme family's CSS (unlike the two
		// above).
		var detailTitle = root.matches && root.matches( "#c7-content" ) ? root.querySelector( ":scope > h1, :scope h1" ) : null;
		if ( detailTitle ) cleanTitleEl( detailTitle );

		var titleText = ( titleEls[ 0 ] && titleEls[ 0 ].textContent ) || ( detailTitle && detailTitle.textContent ) || "";

		// No confirmed class for the product description block, so this
		// walks every leaf text-bearing element under the root instead of
		// guessing a selector — cheap, since it only ever does anything on
		// the rare element whose own text matches "lorem ipsum".
		var candidates = root.querySelectorAll( "p, div, span" );
		for ( var j = 0; j < candidates.length; j++ ) {
			var el = candidates[ j ];
			if ( el.childElementCount === 0 ) cleanLoremEl( el, titleText );
		}
	}

	document.addEventListener( "DOMContentLoaded", function () {
		var mount = document.getElementById( "c7-content" ) || document.body;
		sweep( document.body );

		if ( "MutationObserver" in window ) {
			var observer = new MutationObserver( function ( mutations ) {
				for ( var i = 0; i < mutations.length; i++ ) {
					if ( mutations[ i ].addedNodes && mutations[ i ].addedNodes.length ) {
						sweep( mount );
						return;
					}
				}
			} );
			observer.observe( mount, { childList: true, subtree: true } );
		}
	} );
})();
