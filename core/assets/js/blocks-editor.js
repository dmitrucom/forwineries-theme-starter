/**
 * GENERATED FILE — do not edit directly. Synced from forwineries-theme-core.
 * Edit the source there and re-run tools/sync-core.sh.
 *
 * Editor-side registration for the Commerce7 blocks defined in
 * Commerce7\Blocks::block_definitions(). Plain JS (no JSX, no build
 * step) using WordPress's own built-in wp.blocks/wp.element/
 * wp.components globals — every block is server-side-rendered (save()
 * returns null), so the actual widget markup always comes from PHP,
 * previewed live in the editor via ServerSideRender. window.fwC7Blocks
 * (localized in Commerce7\Blocks::register()) drives this loop, so
 * adding a new widget only ever means editing the one definitions array
 * in PHP.
 */
( function ( blocks, element, blockEditor, components, ServerSideRender, i18n ) {
	'use strict';

	var el = element.createElement;
	var Fragment = element.Fragment;
	var __ = i18n.__;
	var InspectorControls = blockEditor.InspectorControls;
	var PanelBody = components.PanelBody;
	var TextControl = components.TextControl;

	( window.fwC7Blocks || [] ).forEach( function ( def ) {
		var attributes = {};
		def.fields.forEach( function ( field ) {
			attributes[ field.key ] = {
				type: field.type === 'number' ? 'number' : ( field.type === 'repeater' ? 'array' : 'string' ),
				default: field.default,
			};
		} );

		blocks.registerBlockType( def.name, {
			title: def.title,
			description: def.description,
			icon: def.icon,
			category: def.category,
			attributes: attributes,

			edit: function ( props ) {
				var blockAttributes = props.attributes;
				var setAttributes = props.setAttributes;

				// Repeater fields (e.g. Membership Tiers) have no Gutenberg
				// inspector control yet — Elementor's own Repeater control
				// covers them (ElementorWidget::add_repeater_control()),
				// and the block simply renders with the PHP-side default
				// rows here, same as any other field a buyer hasn't
				// touched. window.fwC7Blocks already carries the right
				// 'array' attribute type for these (see blocks.php's
				// block_attribute_type()), so the block still registers
				// and previews correctly; only its own inspector edit UI
				// is deferred.
				var editableFields = def.fields.filter( function ( field ) {
					return field.type !== 'repeater' && field.type !== 'image';
				} );

				var controls = editableFields.map( function ( field ) {
					return el( TextControl, {
						key: field.key,
						label: field.label,
						type: field.type === 'number' ? 'number' : 'text',
						value: blockAttributes[ field.key ],
						onChange: function ( value ) {
							var next = {};
							next[ field.key ] = field.type === 'number' ? Number( value ) : value;
							setAttributes( next );
						},
					} );
				} );

				return el(
					Fragment,
					{},
					editableFields.length > 0
						? el(
							InspectorControls,
							{},
							el( PanelBody, { title: __( 'Settings' ) }, controls )
						)
						: null,
					el(
						'div',
						{ className: 'fw-c7-block-preview' },
						el( ServerSideRender, {
							block: def.name,
							attributes: blockAttributes,
						} )
					)
				);
			},

			save: function () {
				return null;
			},
		} );
	} );
} )(
	window.wp.blocks,
	window.wp.element,
	window.wp.blockEditor,
	window.wp.components,
	window.wp.serverSideRender,
	window.wp.i18n
);
