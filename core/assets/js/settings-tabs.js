/**
 * GENERATED FILE — do not edit directly. Synced from forwineries-theme-core.
 * Edit the source there and re-run tools/sync-core.sh.
 *
 * Client-side tab switcher for the unified settings page
 * (SettingsPage::render()). All registered tab panels render on every
 * page load — this just toggles which one is visible, so browsing
 * between tabs is instant with no reload. Saving a tab is still a
 * normal WP form POST to options.php (each panel has its own <form>),
 * which is why each tab's own render function overrides its
 * _wp_http_referer — this script only needs to handle browsing, not
 * saving.
 */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var tabLinks = document.querySelectorAll( '#fw-settings-tabs .nav-tab' );
		var panels = document.querySelectorAll( '.fw-settings-tab-panel' );

		if ( ! tabLinks.length || ! panels.length ) return;

		function activate( tabId ) {
			panels.forEach( function ( panel ) {
				panel.hidden = panel.getAttribute( 'data-tab' ) !== tabId;
			} );
			tabLinks.forEach( function ( link ) {
				link.classList.toggle( 'nav-tab-active', link.getAttribute( 'data-tab' ) === tabId );
			} );
		}

		tabLinks.forEach( function ( link ) {
			link.addEventListener( 'click', function ( e ) {
				var tabId = link.getAttribute( 'data-tab' );
				if ( ! tabId ) return;

				e.preventDefault();
				activate( tabId );

				if ( window.history && window.history.pushState ) {
					window.history.pushState( null, '', link.getAttribute( 'href' ) );
				}
			} );
		} );

		window.addEventListener( 'popstate', function () {
			var params = new URLSearchParams( window.location.search );
			var tabId = params.get( 'tab' );
			if ( tabId ) activate( tabId );
		} );

		/**
		 * Collapsible field groups (any tab, not just Pages & Content) —
		 * search filter, expand/collapse all, and sidebar-link-opens-the-
		 * target-<details> for browsers that don't (yet) auto-expand a
		 * <details> on fragment navigation. Every field group is a native
		 * <details>/<summary> — no JS at all is needed for the
		 * collapse/expand mechanic itself, only for these three
		 * conveniences layered on top. Entirely inert (nothing below
		 * matches) on a tab that doesn't use this many groups to need it.
		 */
		var settingsGroups = document.querySelectorAll( '[data-fw-group]' );
		if ( settingsGroups.length ) {
			var filterInput = document.getElementById( 'fw-settings-filter' );
			if ( filterInput ) {
				filterInput.addEventListener( 'input', function () {
					var term = filterInput.value.trim().toLowerCase();
					settingsGroups.forEach( function ( group ) {
						if ( ! term ) {
							group.classList.remove( 'is-search-hidden' );
							return;
						}
						var matches = group.textContent.toLowerCase().indexOf( term ) !== -1;
						group.classList.toggle( 'is-search-hidden', ! matches );
						if ( matches ) group.open = true;
					} );
				} );
			}

			var expandAll = document.querySelector( '[data-fw-expand-all]' );
			if ( expandAll ) {
				expandAll.addEventListener( 'click', function () {
					settingsGroups.forEach( function ( group ) { group.open = true; } );
				} );
			}
			var collapseAll = document.querySelector( '[data-fw-collapse-all]' );
			if ( collapseAll ) {
				collapseAll.addEventListener( 'click', function () {
					settingsGroups.forEach( function ( group ) { group.open = false; } );
				} );
			}

			// Belt-and-suspenders: modern Chrome/Firefox already open a
			// <details> automatically when a fragment link inside it
			// becomes the target, but forcing it explicitly here costs
			// nothing and covers older browsers too.
			document.querySelectorAll( '[data-fw-group-link]' ).forEach( function ( link ) {
				link.addEventListener( 'click', function () {
					var target = document.getElementById( 'fw-group-' + link.getAttribute( 'data-fw-group-link' ) );
					if ( target ) target.open = true;
				} );
			} );
		}
	} );
} )();
