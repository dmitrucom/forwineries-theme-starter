/**
 * GENERATED FILE — do not edit directly. Synced from forwineries-theme-core.
 * Edit the source there and re-run tools/sync-core.sh.
 *
 * Handles the age-gate form (AgeGate::render() in core/src/AgeGate.php
 * renders the markup, a separate inline script in wp_head decides whether
 * the gate is visible at all — see the comment there for why). This
 * script only ever matters once that inline script has already proven JS
 * is available, so there's no code path where the gate shows but the form
 * doesn't work.
 *
 * Element IDs are the fixed "fw-*" names AgeGate::render() always uses —
 * see Config::css()'s docblock for why core markup is unprefixed. Only
 * the "remembered" cookie name is slug-scoped (renaming it on a live
 * site would make an already-verified visitor see the gate again), so
 * that one value alone is threaded in via window.fwAgeGateCookie, set by
 * a small inline script AgeGate::register() prints immediately before
 * this file loads.
 */
( function () {
	"use strict";

	var cookieName = window.fwAgeGateCookie || "fw_age_verified";

	document.addEventListener( "DOMContentLoaded", function () {
		var gate  = document.getElementById( "fw-age-gate" );
		var form  = document.getElementById( "fw-age-gate-form" );
		var error = document.getElementById( "fw-age-gate-error" );

		if ( ! gate || ! form || ! error ) return;

		var minAge = parseInt( gate.getAttribute( "data-min-age" ), 10 );
		if ( ! minAge || minAge < 1 ) minAge = 21;

		function showError( message ) {
			error.textContent = message;
			error.hidden = false;
		}

		form.addEventListener( "submit", function ( e ) {
			e.preventDefault();

			var year  = parseInt( document.getElementById( "fw-age-gate-year" ).value, 10 );
			var month = parseInt( document.getElementById( "fw-age-gate-month" ).value, 10 );
			var day   = parseInt( document.getElementById( "fw-age-gate-day" ).value, 10 );
			var remember = document.getElementById( "fw-age-gate-remember" ).checked;

			if ( ! year || ! month || ! day || month < 1 || month > 12 || day < 1 || day > 31 ) {
				showError( "Please enter a complete date of birth." );
				return;
			}

			var dob = new Date( year, month - 1, day );
			// new Date() silently rolls invalid combos (e.g. Feb 30) into the
			// next valid date instead of failing — checking the parts back
			// against what was entered is what actually catches that case.
			if ( dob.getFullYear() !== year || dob.getMonth() !== month - 1 || dob.getDate() !== day ) {
				showError( "Please enter a valid date of birth." );
				return;
			}
			if ( dob > new Date() ) {
				showError( "That date of birth hasn't happened yet." );
				return;
			}

			var today = new Date();
			var age = today.getFullYear() - dob.getFullYear();
			var hadBirthdayThisYear =
				today.getMonth() > dob.getMonth() ||
				( today.getMonth() === dob.getMonth() && today.getDate() >= dob.getDate() );
			if ( ! hadBirthdayThisYear ) age--;

			if ( age < minAge ) {
				gate.classList.add( "fw-age-gate--refused" );
				return;
			}

			error.hidden = true;

			if ( remember ) {
				// 30 days — the visitor explicitly asked to be remembered.
				document.cookie = cookieName + "=1; path=/; max-age=" + ( 60 * 60 * 24 * 30 ) + "; SameSite=Lax";
			} else {
				// No max-age = session cookie, cleared once the browser fully
				// closes — matches the checkbox's own "shared device" copy.
				document.cookie = cookieName + "=1; path=/; SameSite=Lax";
			}
			document.documentElement.classList.remove( "fw-age-gate-active" );
		} );
	} );
} )();
