<?php
// GENERATED FILE — do not edit directly. Synced from forwineries-theme-core.
// Edit the source there and re-run tools/sync-core.sh.
namespace ForWineries\Core\Commerce7;

use ForWineries\Core\Config;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Native Gutenberg blocks for the Commerce7 widgets. Server-side-
 * rendered ("dynamic") blocks — no build step, no npm/webpack, no React
 * JSX — just register_block_type() on the PHP side plus a small plain-
 * JS editor script (core/assets/js/blocks-editor.js) using WordPress's
 * own built-in wp.blocks/wp.element/wp.components globals.
 *
 * block_definitions() is the single source of truth: PHP reads it to
 * register each block's render callback, the same array is handed to
 * the editor JS so it can register matching blocks with the right
 * inspector fields, AND ElementorWidgets.php reads the same 'fields'/
 * 'style_fields' to build the Elementor Widget_Base version — add a
 * widget here once and it appears correctly in all three places.
 *
 * Block names use $config->slug() as the namespace (e.g. 'lh/wines') —
 * unlike core's usual unprefixed CSS vocabulary, a block's namespaced
 * name is stored verbatim in a page's post_content (`<!-- wp:lh/wines
 * /-->`), so renaming it on an already-migrated site would silently
 * break every already-placed block of that type. The category slug
 * (grouping in the block inserter) carries no such risk — it's cosmetic
 * UI metadata only, never saved into content.
 */
class Blocks {

	/**
	 * slug => definition. 'render' is a closure (not the original's
	 * plain function-name string, since these are now static class
	 * methods needing $config threaded through) accepting one $atts
	 * array and returning markup, same contract every block/shortcode/
	 * Elementor-widget render callback in this codebase already uses.
	 */
	public static function block_definitions( Config $config ): array {
		$td = $config->text_domain();
		return array(
			'collection' => array(
				'title'       => __( 'Commerce7 — Wine Collection', $td ),
				'description' => __( 'Live Commerce7 product grid for a collection — real inventory, pricing, and add-to-cart.', $td ),
				'icon'        => 'grid-view',
				'render'      => function ( $atts ) use ( $config ) { return Integration::render_collection( $config, $atts ); },
				'fields'      => array(
					'slug' => array( 'type' => 'string', 'label' => __( 'Collection slug', $td ), 'default' => '' ),
				),
			),
			'buy' => array(
				'title'       => __( 'Commerce7 — Buy Button', $td ),
				'description' => __( 'Add-to-cart button for one specific wine.', $td ),
				'icon'        => 'cart',
				'render'      => function ( $atts ) use ( $config ) { return Integration::render_buy( $config, $atts ); },
				'fields'      => array(
					'slug' => array( 'type' => 'string', 'label' => __( 'Product slug', $td ), 'default' => '' ),
				),
			),
			'club-join' => array(
				'title'       => __( 'Commerce7 — Club Join Button', $td ),
				'description' => __( 'Real "Join the Club" / "Manage Membership" button.', $td ),
				'icon'        => 'groups',
				'render'      => function ( $atts ) use ( $config ) { return Integration::render_club_join( $config, $atts ); },
				'fields'      => array(
					'slug'      => array( 'type' => 'string', 'label' => __( 'Club slug', $td ), 'default' => '' ),
					'join_text' => array( 'type' => 'string', 'label' => __( 'Join button text', $td ), 'default' => sprintf( __( 'Join the %s', $td ), $config->get( 'club.name', sprintf( __( '%s Club', $td ), $config->brand_name() ) ) ) ),
					'edit_text' => array( 'type' => 'string', 'label' => __( 'Manage-membership text', $td ), 'default' => __( 'Manage Your Membership', $td ) ),
				),
			),
			'subscribe' => array(
				'title'       => __( 'Commerce7 — Newsletter Signup', $td ),
				'description' => __( 'Real email signup for your Commerce7 marketing list — separate from the wine club, no purchase or membership required.', $td ),
				'icon'        => 'email',
				'render'      => function () use ( $config ) { return Integration::render_subscribe( $config ); },
				'fields'      => array(),
			),
			'reservation-availability' => array(
				'title'       => __( 'Commerce7 — Reservation Availability', $td ),
				'description' => __( 'Live reservation booking calendar for one reservation type — optional; leave the slug blank and nothing renders. Configure a default under Setup.', $td ),
				'icon'        => 'calendar-alt',
				'render'      => function ( $atts ) use ( $config ) { return ReservationWidget::render( $config, $atts ); },
				'fields'      => array(
					'slug' => array( 'type' => 'string', 'label' => __( 'Reservation type slug', $td ), 'default' => '' ),
				),
			),
			'account' => array(
				'title'       => __( 'Commerce7 — Account Widget', $td ),
				'description' => __( 'Login link / account greeting. Already included automatically in the header.', $td ),
				'icon'        => 'admin-users',
				'render'      => function () use ( $config ) { return Integration::render_account( $config ); },
				'fields'      => array(),
			),
			'cart-trigger' => array(
				'title'       => __( 'Commerce7 — Cart Icon', $td ),
				'description' => __( 'Real cart icon + live item count. Already included automatically in the header — only add this if you removed it from there and want it somewhere else.', $td ),
				'icon'        => 'cart',
				'render'      => function () use ( $config ) { return Integration::render_cart_trigger( $config ); },
				'fields'      => array(),
			),
			'wines' => array(
				'title'       => __( 'Commerce7 — Featured Wines Teaser', $td ),
				'description' => __( "Hand-styled wine cards (the theme's own design, real add-to-cart on each). Needs the optional App ID/Secret Key under Setup.", $td ),
				'icon'        => 'layout',
				'render'      => function ( $atts ) use ( $config ) { return Catalog::render_wines( $config, $atts ); },
				'fields'      => array(
					'limit' => array( 'type' => 'number', 'label' => __( 'Number of wines', $td ), 'default' => 8 ),
				),
				// Style tab — every control targets the grid wrapper and
				// only ever sets a CSS custom property there (custom
				// properties inherit to .fw-wine-card/.fw-wine-img for
				// free). See ElementorWidgets.php's register_style_controls().
				'style_root_selector' => '.fw-wine-grid',
				'style_fields'        => array(
					'card_bg'      => array( 'type' => 'color', 'label' => __( 'Card background', $td ), 'css_var' => '--fw-wine-card-bg' ),
					'card_radius'  => array( 'type' => 'slider', 'label' => __( 'Card corner radius', $td ), 'css_var' => '--fw-wine-card-radius', 'min' => 0, 'max' => 40 ),
					'card_padding' => array( 'type' => 'slider', 'label' => __( 'Card padding', $td ), 'css_var' => '--fw-wine-card-padding', 'min' => 0, 'max' => 64 ),
					'img_size'     => array( 'type' => 'slider', 'label' => __( 'Bottle image width', $td ), 'css_var' => '--fw-wine-img-size', 'min' => 80, 'max' => 320 ),
					'gap'          => array( 'type' => 'slider', 'label' => __( 'Gap between cards', $td ), 'css_var' => '--fw-wine-grid-gap', 'min' => 0, 'max' => 100 ),
					'columns'      => array(
						'type'    => 'select',
						'label'   => __( 'Columns', $td ),
						'css_var' => '--fw-wine-grid-columns',
						// The "Auto" option's value IS the real CSS default
						// (not an empty string) — safe even if Elementor
						// emits the rule for an untouched control, since
						// the emitted value would just match the CSS
						// file's own fallback exactly.
						'options' => array(
							'repeat(auto-fit, minmax(440px, 1fr))' => __( 'Auto (fit to width)', $td ),
							'repeat(1, 1fr)' => '1',
							'repeat(2, 1fr)' => '2',
							'repeat(3, 1fr)' => '3',
							'repeat(4, 1fr)' => '4',
						),
					),
				),
			),
			'club-teaser' => array(
				'title'       => __( 'Commerce7 — Club Teaser', $td ),
				'description' => __( "Hand-styled club plan cards (the theme's own design, real \"Join\" button on each). Needs the optional App ID/Secret Key under Setup.", $td ),
				'icon'        => 'layout',
				'render'      => function ( $atts ) use ( $config ) { return Catalog::render_club_teaser( $config, $atts ); },
				'fields'      => array(
					'limit' => array( 'type' => 'number', 'label' => __( 'Number of club plans', $td ), 'default' => 6 ),
				),
				'style_root_selector' => '.fw-club-grid',
				'style_fields'        => array(
					'card_bg'     => array( 'type' => 'color', 'label' => __( 'Card background', $td ), 'css_var' => '--fw-club-card-bg' ),
					'card_color'  => array( 'type' => 'color', 'label' => __( 'Card text color', $td ), 'css_var' => '--fw-club-card-color' ),
					'card_radius' => array( 'type' => 'slider', 'label' => __( 'Card corner radius', $td ), 'css_var' => '--fw-club-card-radius', 'min' => 0, 'max' => 40 ),
					'gap'         => array( 'type' => 'slider', 'label' => __( 'Gap between cards', $td ), 'css_var' => '--fw-club-grid-gap', 'min' => 0, 'max' => 100 ),
					'columns'     => array(
						'type'    => 'select',
						'label'   => __( 'Columns', $td ),
						'css_var' => '--fw-club-grid-columns',
						'options' => array(
							'repeat(auto-fit, minmax(280px, 1fr))' => __( 'Auto (fit to width)', $td ),
							'repeat(1, 1fr)' => '1',
							'repeat(2, 1fr)' => '2',
							'repeat(3, 1fr)' => '3',
							'repeat(4, 1fr)' => '4',
						),
					),
				),
			),
			'wines-catalog' => array(
				'title'       => __( 'Commerce7 — Wines Catalog (Filter & Sort)', $td ),
				'description' => __( 'The full available wine list with filter-by-type, filter-by-varietal, and sort-by-price/vintage controls — real add-to-cart on each card. Needs the optional App ID/Secret Key under Setup.', $td ),
				'icon'        => 'filter',
				'render'      => function () use ( $config ) { return Catalog::render_wines_catalog( $config ); },
				'fields'      => array(),
			),
		);
	}

	public static function register( Config $config ): void {
		add_action( 'init', function () use ( $config ) {
			foreach ( self::block_definitions( $config ) as $slug => $def ) {
				$attributes = array();
				foreach ( $def['fields'] as $key => $field ) {
					$attributes[ $key ] = array(
						'type'    => $field['type'] === 'number' ? 'number' : 'string',
						'default' => $field['default'],
					);
				}

				register_block_type( $config->slug() . '/' . $slug, array(
					'attributes'      => $attributes,
					'render_callback' => function ( $block_attrs ) use ( $def ) {
						return call_user_func( $def['render'], $block_attrs );
					},
				) );
			}
		} );

		add_action( 'enqueue_block_editor_assets', function () use ( $config ) {
			wp_enqueue_script(
				'fw-c7-blocks-editor',
				get_stylesheet_directory_uri() . '/core/assets/js/blocks-editor.js',
				array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render', 'wp-i18n' ),
				null,
				true
			);

			$definitions = array();
			foreach ( self::block_definitions( $config ) as $slug => $def ) {
				$definitions[] = array(
					'name'        => $config->slug() . '/' . $slug,
					'title'       => $def['title'],
					'description' => $def['description'],
					'icon'        => $def['icon'],
					'category'    => self::category_slug( $config ),
					'fields'      => array_map( function ( $key, $field ) {
						return array(
							'key'     => $key,
							'type'    => $field['type'],
							'label'   => $field['label'],
							'default' => $field['default'],
						);
					}, array_keys( $def['fields'] ), $def['fields'] ),
				);
			}

			wp_add_inline_script(
				'fw-c7-blocks-editor',
				'window.fwC7Blocks = ' . wp_json_encode( $definitions ) . ';',
				'before'
			);
		} );

		/**
		 * Groups every Commerce7 block under its own section in the
		 * inserter instead of scattering them across "Widgets"/"Common".
		 */
		add_filter( 'block_categories_all', function ( $categories ) use ( $config ) {
			array_unshift( $categories, array(
				'slug'  => self::category_slug( $config ),
				'title' => sprintf( __( '%s — Commerce7', $config->text_domain() ), $config->brand_name() ),
				'icon'  => 'store',
			) );
			return $categories;
		} );
	}

	public static function category_slug( Config $config ): string {
		return $config->slug() . '-commerce7';
	}
}
