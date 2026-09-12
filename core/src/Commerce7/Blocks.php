<?php
// GENERATED FILE — do not edit directly. Synced from forwineries-theme-core.
// Edit the source there and re-run tools/sync-core.sh.
namespace ForWineries\Core\Commerce7;

use ForWineries\Core\Config;
use ForWineries\Core\Events;
use ForWineries\Core\PressLogos;
use ForWineries\Core\Testimonials;
use ForWineries\Core\SocialFeed;

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
			'flagship-wine' => array(
				'title'       => __( 'Commerce7 — Flagship Wine', $td ),
				'description' => __( "A live product spotlight — set a Commerce7 product slug to automatically pull its photo, name, and tasting note (with a real Add to Cart button), or override any of them by hand. Needs the optional App ID/Secret Key under Setup to pull live product data; without that, only your own overrides show.", $td ),
				'icon'        => 'star',
				'render'      => function ( $atts ) use ( $config ) { return Catalog::render_flagship_wine( $config, $atts ); },
				'fields'      => array(
					'slug'    => array( 'type' => 'string', 'label' => __( 'Commerce7 product slug', $td ), 'default' => '' ),
					'eyebrow' => array( 'type' => 'string', 'label' => __( 'Eyebrow', $td ), 'default' => '' ),
					'heading' => array( 'type' => 'string', 'label' => __( 'Heading override (optional — defaults to the product\'s own title)', $td ), 'default' => '' ),
					'image'   => array( 'type' => 'image', 'label' => __( 'Photo override (optional — defaults to the product\'s own photo)', $td ), 'default' => '' ),
					'note'    => array( 'type' => 'textarea', 'label' => __( 'Tasting note override (optional — defaults to the product\'s own teaser/subtitle)', $td ), 'default' => '' ),
				),
			),
			'reservation-band' => array(
				'title'       => __( 'Commerce7 — Tastings by Appointment', $td ),
				'description' => __( 'A ready-made full-bleed "Reserve a Tasting" band — heading, optional photo, and the live Commerce7 availability calendar (or a plain button when no reservation type slug is set), pre-fixed for the calendar dropdown\'s known mobile/clipping quirks. Configure a default reservation type slug under Setup, or set one per instance below.', $td ),
				'icon'        => 'calendar-alt',
				'render'      => function ( $atts ) use ( $config ) { return ReservationWidget::render_band( $config, $atts ); },
				'fields'      => array(
					'eyebrow' => array( 'type' => 'string', 'label' => __( 'Eyebrow', $td ), 'default' => __( 'Tastings by Appointment', $td ) ),
					'heading' => array( 'type' => 'string', 'label' => __( 'Heading', $td ), 'default' => __( 'Reserve a Tasting', $td ) ),
					'intro'   => array( 'type' => 'textarea', 'label' => __( 'Intro paragraph', $td ), 'default' => __( 'Pick a date and time below — tastings are seated, by appointment only, and kept small so every visit feels unhurried.', $td ) ),
					'image'   => array( 'type' => 'image', 'label' => __( 'Background photo (optional)', $td ), 'default' => '' ),
					'slug'    => array( 'type' => 'string', 'label' => __( 'Reservation type slug (optional — leave blank for the default from Setup)', $td ), 'default' => '' ),
				),
			),
			'testimonials' => array(
				'title'       => __( 'Testimonials', $td ),
				'description' => __( 'Quote cards — guest reviews, press mentions, ratings. Renders nothing at all until at least one row has a quote.', $td ),
				'icon'        => 'format-quote',
				'render'      => function ( $atts ) use ( $config ) { return Testimonials::render_widget( $config, $atts ); },
				'fields'      => array(
					'eyebrow'      => array( 'type' => 'string', 'label' => __( 'Eyebrow', $td ), 'default' => __( 'In Their Words', $td ) ),
					'heading'      => array( 'type' => 'string', 'label' => __( 'Heading', $td ), 'default' => __( 'What People Are Saying', $td ) ),
					'testimonials' => array(
						'type'        => 'repeater',
						'label'       => __( 'Quotes', $td ),
						'title_field' => '{{{ source }}}',
						'item_fields' => array(
							'quote'  => array( 'type' => 'textarea', 'label' => __( 'Quote', $td ) ),
							'source' => array( 'type' => 'string', 'label' => __( 'Source (guest name, or publication)', $td ) ),
						),
						// Deliberately generic (no wine region/varietal
						// claim tied to any one theme's own fictional
						// estate) — this default has to work as a
						// starting point on all 5 themes, unlike the
						// homepage's own hardcoded per-theme copy.
						'default' => array(
							array( 'quote' => __( 'A beautifully structured wine with real aging potential.', $td ), 'source' => __( 'Wine Spectator', $td ) ),
							array( 'quote' => __( 'The most memorable tasting we\'ve had all year — unhurried, personal, and the wine backs it up.', $td ), 'source' => __( 'Guest review', $td ) ),
						),
					),
				),
			),
			'social-feed' => array(
				'title'       => __( 'Social Feed', $td ),
				'description' => __( 'An Instagram-style photo grid, each tile linking out (defaults to your configured Instagram URL under Setup if a row leaves its own link blank). Renders nothing at all until at least one row has a photo.', $td ),
				'icon'        => 'grid-view',
				'render'      => function ( $atts ) use ( $config ) { return SocialFeed::render_widget( $config, $atts ); },
				'fields'      => array(
					'eyebrow' => array( 'type' => 'string', 'label' => __( 'Eyebrow', $td ), 'default' => __( 'Follow Along', $td ) ),
					'heading' => array( 'type' => 'string', 'label' => __( 'Heading', $td ), 'default' => __( 'From the Estate', $td ) ),
					'photos'  => array(
						'type'        => 'repeater',
						'label'       => __( 'Photos', $td ),
						'item_fields' => array(
							'image' => array( 'type' => 'image', 'label' => __( 'Photo', $td ) ),
							'link'  => array( 'type' => 'string', 'label' => __( 'Link (optional — defaults to your Instagram URL)', $td ) ),
						),
						// No safe generic default here (unlike Testimonials'
						// quotes/PressLogos' publication names) — every
						// original theme's own starter photos are its own
						// bundled theme assets, which core has no path to
						// reference. Empty until a buyer adds real photos.
						'default' => array(),
					),
				),
			),
			'press-logos' => array(
				'title'       => __( 'As Seen In', $td ),
				'description' => __( 'A seamless auto-scrolling press-logo strip — add each publication\'s name, logo, and an optional link. A row with no logo image shows an honest labeled placeholder instead of a fabricated one. Renders nothing at all until at least one row has a name.', $td ),
				'icon'        => 'align-center',
				'render'      => function ( $atts ) use ( $config ) { return PressLogos::render_widget( $config, $atts ); },
				'fields'      => array(
					'eyebrow' => array( 'type' => 'string', 'label' => __( 'Eyebrow', $td ), 'default' => __( 'As Seen In', $td ) ),
					'heading' => array( 'type' => 'string', 'label' => __( 'Heading', $td ), 'default' => __( 'Recognized by the Press We Respect', $td ) ),
					'logos'   => array(
						'type'        => 'repeater',
						'label'       => __( 'Logos', $td ),
						'title_field' => '{{{ name }}}',
						'item_fields' => array(
							'name'  => array( 'type' => 'string', 'label' => __( 'Publication name (alt text)', $td ) ),
							'image' => array( 'type' => 'image', 'label' => __( 'Logo image (optional)', $td ) ),
							'link'  => array( 'type' => 'string', 'label' => __( 'Link (optional)', $td ) ),
						),
						'default' => PressLogos::default_logos(),
					),
				),
			),
			'upcoming-events' => array(
				'title'       => __( 'Upcoming Events', $td ),
				'description' => __( 'The next few upcoming events (soonest-first), each linking to its own event page — pulls from the real Events post type, so it stays in sync with whatever you publish there. Renders nothing at all when there are no upcoming events.', $td ),
				'icon'        => 'calendar-alt',
				'render'      => function ( $atts ) use ( $config ) { return Events::render_upcoming_widget( $config, $atts ); },
				'fields'      => array(
					'eyebrow'       => array( 'type' => 'string', 'label' => __( 'Eyebrow', $td ), 'default' => __( 'Join Us', $td ) ),
					'heading'       => array( 'type' => 'string', 'label' => __( 'Heading', $td ), 'default' => __( 'Upcoming Events', $td ) ),
					'limit'         => array( 'type' => 'number', 'label' => __( 'Number of events to show', $td ), 'default' => 3 ),
					'details_label' => array( 'type' => 'string', 'label' => __( '"Details" button text', $td ), 'default' => __( 'Details & RSVP', $td ) ),
					'see_all_label' => array( 'type' => 'string', 'label' => __( '"See all" button text', $td ), 'default' => __( 'See All Events', $td ) ),
					'see_all_url'   => array( 'type' => 'string', 'label' => __( '"See all" link (optional — defaults to /events)', $td ), 'default' => '' ),
				),
			),
			'membership-tiers' => array(
				'title'       => __( 'Commerce7 — Membership Tiers', $td ),
				'description' => __( 'Wine club tier cards you write yourself (name, price, description) — each card gets its own real "Join the Club" button, independently wired to its own Commerce7 club plan slug (leave a row\'s slug blank to use the default club plan from Setup).', $td ),
				'icon'        => 'awards',
				'render'      => function ( $atts ) use ( $config ) { return Integration::render_membership_tiers( $config, $atts ); },
				'fields'      => array(
					'tiers' => array(
						'type'        => 'repeater',
						'label'       => __( 'Tiers', $td ),
						'title_field' => '{{{ name }}}',
						'item_fields' => array(
							'name'        => array( 'type' => 'string', 'label' => __( 'Tier name', $td ) ),
							'price'       => array( 'type' => 'string', 'label' => __( 'Price', $td ) ),
							'description' => array( 'type' => 'textarea', 'label' => __( 'Description', $td ) ),
							'slug'        => array( 'type' => 'string', 'label' => __( 'Commerce7 club slug (optional — leave blank for the default club plan from Setup)', $td ) ),
						),
						/* translators: same three starter tiers every original theme shipped in ContentSettings\Framework's club_tiers default, kept identical here so a buyer sees familiar copy regardless of which builder (Elementor or the classic page route) they're looking at. */
						'default' => array(
							array( 'name' => __( 'The Explorer', $td ), 'price' => __( '$95 / quarter', $td ), 'description' => __( '4 bottles, mostly current releases — the easy way to always have the estate on hand.', $td ), 'slug' => '' ),
							array( 'name' => __( 'The Connoisseur', $td ), 'price' => __( '$180 / quarter', $td ), 'description' => __( '8 bottles, including early access to limited releases before they\'re offered to the public.', $td ), 'slug' => '' ),
							array( 'name' => __( 'The Reserve', $td ), 'price' => __( '$320 / quarter', $td ), 'description' => __( '12 bottles, first access to library and single-vineyard wines, plus two complimentary tastings a year.', $td ), 'slug' => '' ),
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
						'type'    => self::block_attribute_type( $field['type'] ),
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
				Config::asset_version( '/core/assets/js/blocks-editor.js' ),
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
				'title' => sprintf( __( '%s — Widgets', $config->text_domain() ), $config->brand_name() ),
				'icon'  => 'store',
			) );
			return $categories;
		} );
	}

	public static function category_slug( Config $config ): string {
		return $config->slug() . '-commerce7';
	}

	/**
	 * A 'repeater' field's value is an array of associative arrays (see
	 * ElementorWidget::add_repeater_control()) — registering it as WP's
	 * 'string' attribute type would fail block-validation the moment
	 * Elementor's own repeater default (an array) got compared against a
	 * string schema. Gutenberg's own editor UI has no control for this
	 * type yet (blocks-editor.js skips it and always renders the PHP-side
	 * default), but the schema still has to be correct so the dynamic
	 * block itself registers and previews without erroring.
	 */
	private static function block_attribute_type( string $field_type ): string {
		if ( $field_type === 'number' ) return 'number';
		if ( $field_type === 'repeater' ) return 'array';
		// 'image' stays a plain string (a URL) here — Gutenberg has no
		// media-picker inspector control for it yet either (blocks-
		// editor.js defers it the same way it defers 'repeater'), and the
		// PHP-side default is already a URL string, not the {url,id}
		// shape Elementor's own MEDIA control saves.
		return 'string';
	}
}
