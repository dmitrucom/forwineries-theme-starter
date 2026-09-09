/**
 * GENERATED FILE — do not edit directly. Synced from forwineries-theme-core.
 * Edit the source there and re-run tools/sync-core.sh.
 *
 * Admin behaviors for ContentSettings\Framework's settings tab. Plain
 * JS, no build step. Three concerns:
 *
 * 1. Repeater rows — add (clone the field's own <template>, swapping its
 *    "__INDEX__" name placeholders for a fresh unique number), remove,
 *    and reorder via the per-row Move up/down buttons. Submitted row
 *    indices don't need to stay sequential OR ordered — the PHP
 *    sanitizer iterates whatever arrives, and PHP preserves the
 *    DOM/submission order of the inputs, which is why plain DOM moves
 *    are enough to persist a reorder.
 *
 * 2. Image fields ('image' field type) — the "Choose Image" button opens
 *    the WordPress Media Library modal (wp.media, enqueued via
 *    wp_enqueue_media) and writes the chosen attachment's URL into the
 *    field's URL input; the thumbnail preview stays in sync whether the
 *    URL came from the picker, was pasted by hand, or was cleared.
 *
 * 3. Everything is event-delegated from the document, not bound per
 *    element, so rows cloned from a <template> after page load get all
 *    behaviors for free.
 */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		// ---------- 1. Repeater add rows (per-field state: next index) ----------
		document.querySelectorAll( '.fw-repeater-field' ).forEach( function ( field ) {
			var rowsContainer = field.querySelector( '.fw-repeater-rows' );
			var template      = field.querySelector( '.fw-repeater-template' );
			var addButton     = field.querySelector( '.fw-repeater-add' );
			var nextIndex     = rowsContainer.querySelectorAll( '.fw-repeater-row' ).length;

			if ( addButton && template ) {
				addButton.addEventListener( 'click', function () {
					var html    = template.innerHTML.replace( /__INDEX__/g, String( nextIndex ) );
					var wrapper = document.createElement( 'div' );
					wrapper.innerHTML = html;
					var newRow = wrapper.firstElementChild;
					rowsContainer.appendChild( newRow );
					nextIndex++;
					newRow.scrollIntoView( { block: 'nearest', behavior: 'smooth' } );
				} );
			}
		} );

		// ---------- 2. Delegated row remove / reorder ----------
		document.addEventListener( 'click', function ( e ) {
			var removeBtn = e.target.closest( '.fw-repeater-remove' );
			if ( removeBtn ) {
				var row = removeBtn.closest( '.fw-repeater-row' );
				if ( row ) row.remove();
				return;
			}

			var upBtn = e.target.closest( '.fw-repeater-move-up' );
			if ( upBtn ) {
				var rowUp = upBtn.closest( '.fw-repeater-row' );
				var prev  = rowUp && rowUp.previousElementSibling;
				if ( prev ) rowUp.parentNode.insertBefore( rowUp, prev );
				return;
			}

			var downBtn = e.target.closest( '.fw-repeater-move-down' );
			if ( downBtn ) {
				var rowDown = downBtn.closest( '.fw-repeater-row' );
				var next    = rowDown && rowDown.nextElementSibling;
				if ( next ) rowDown.parentNode.insertBefore( next, rowDown );
				return;
			}
		} );

		// ---------- 3. Image field: preview sync + media picker + clear ----------
		function syncImageField( fieldEl ) {
			var input  = fieldEl.querySelector( '.fw-image-field-url' );
			var img    = fieldEl.querySelector( '.fw-image-field-preview img' );
			var icon   = fieldEl.querySelector( '.fw-image-field-preview .dashicons' );
			var clear  = fieldEl.querySelector( '.fw-image-field-clear' );
			var hasUrl = input && input.value.trim() !== '';
			if ( img )   { img.src = hasUrl ? input.value.trim() : ''; img.hidden = ! hasUrl; }
			if ( icon )  { icon.hidden = hasUrl; }
			if ( clear ) { clear.hidden = ! hasUrl; }
		}

		document.addEventListener( 'input', function ( e ) {
			if ( e.target.classList && e.target.classList.contains( 'fw-image-field-url' ) ) {
				syncImageField( e.target.closest( '.fw-image-field' ) );
			}
		} );

		document.addEventListener( 'click', function ( e ) {
			var clearBtn = e.target.closest( '.fw-image-field-clear' );
			if ( clearBtn ) {
				var clearField = clearBtn.closest( '.fw-image-field' );
				var clearInput = clearField.querySelector( '.fw-image-field-url' );
				clearInput.value = '';
				syncImageField( clearField );
				return;
			}

			var pickBtn = e.target.closest( '.fw-image-field-pick' );
			if ( ! pickBtn ) return;

			var fieldEl = pickBtn.closest( '.fw-image-field' );

			// wp.media is only present when wp_enqueue_media() ran — but if
			// another plugin broke that, the URL input still works, so fail
			// soft rather than throwing.
			if ( ! window.wp || ! wp.media ) return;

			// One frame per field element, created lazily and cached on the
			// element itself — repeated clicks reuse it (standard wp.media
			// pattern), and separate fields never share selection state.
			if ( ! fieldEl.fwMediaFrame ) {
				fieldEl.fwMediaFrame = wp.media( {
					title:    pickBtn.textContent || 'Choose Image',
					library:  { type: 'image' },
					multiple: false,
					button:   { text: 'Use this image' }
				} );
				fieldEl.fwMediaFrame.on( 'select', function () {
					var attachment = fieldEl.fwMediaFrame.state().get( 'selection' ).first().toJSON();
					// 'large' is plenty for every place these images render;
					// fall back to the original if the size doesn't exist.
					var url = ( attachment.sizes && attachment.sizes.large ) ? attachment.sizes.large.url : attachment.url;
					fieldEl.querySelector( '.fw-image-field-url' ).value = url;
					syncImageField( fieldEl );
				} );
			}
			fieldEl.fwMediaFrame.open();
		} );
	} );
} )();
