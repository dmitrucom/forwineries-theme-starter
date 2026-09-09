<?php
// GENERATED FILE — do not edit directly. Synced from forwineries-theme-core.
// Edit the source there and re-run tools/sync-core.sh.
namespace ForWineries\Core;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Marketing pages, legal pages, and default nav menus — self-healing
 * setup that keeps a site's page/menu structure correct across plain
 * git deploys, not just on first activation. Ported from the original 5
 * themes' inc/page-setup.php, which were byte-identical on page slugs,
 * template paths, and legal-page copy (confirmed by direct diff before
 * porting) — only page TITLES for a couple of routes varied (Larkhaven
 * alone kept "The Estate"; the other four independently converged on
 * "How We Work", same forward-divergence pattern documented in
 * docs/ARCHITECTURE.md for design tokens). Defaults below match the
 * 4-of-5 converged version; $config overrides cover the rest.
 *
 * Page TEMPLATE FILES themselves (page-templates/page-estate.php etc.)
 * stay in each product repo as thin stubs requiring a matching
 * core/templates/page-*.php — genuinely per-theme in look, shared in
 * structure. Not yet ported (still TODO — see docs/ARCHITECTURE.md);
 * this class only handles auto-creating/self-healing the WordPress
 * page rows and their template assignment, which doesn't require the
 * template files themselves to exist yet to be correct.
 *
 * Nav menu location slugs use $config->nav_location() — a live site's
 * chosen menu assignment is stored per-site in the 'nav_menu_locations'
 * theme_mod keyed by this exact string, so it's slug-scoped for the
 * same reason Config::post_type() is. See docs/LESSONS.md.
 */
class PageSetup {

	public static function register( Config $config ): void {
		add_action( 'after_setup_theme', function () use ( $config ) {
			register_nav_menus( array(
				$config->nav_location( 'primary' ) => __( 'Primary Menu', $config->text_domain() ),
				$config->nav_location( 'footer' )  => __( 'Footer Menu', $config->text_domain() ),
			) );
		} );

		add_action( 'after_switch_theme', function () use ( $config ) {
			self::create_marketing_and_legal_pages( $config );
			self::create_default_menus( $config );
		} );

		/**
		 * Automatic version of the same template self-heal
		 * create_marketing_and_legal_pages() already does on
		 * activation/manual "Recreate missing pages" clicks — runs on
		 * every front-end request for these specific pages instead, so
		 * nothing needs a manual fix again. Root cause: "Edit with
		 * Elementor" commonly reassigns a page's WordPress template to
		 * one of Elementor's own layouts, which for these pages
		 * specifically breaks the Elementor-defer mechanism itself — the
		 * page template is what checks is_elementor_built() and hands
		 * off to the_content(); if Elementor swaps the assigned template
		 * to something else entirely, that check never runs and the page
		 * silently stops working as designed. A real client building
		 * this page in Elementor shouldn't have to know any of that — it
		 * should just keep working.
		 */
		add_action( 'template_redirect', function () use ( $config ) {
			if ( ! is_page() ) return;

			$post = get_queried_object();
			if ( ! $post instanceof \WP_Post ) return;

			$routes = self::marketing_pages( $config );
			if ( ! isset( $routes[ $post->post_name ] ) ) return;

			$expected_template = $routes[ $post->post_name ][1];
			if ( get_post_meta( $post->ID, '_wp_page_template', true ) !== $expected_template ) {
				update_post_meta( $post->ID, '_wp_page_template', $expected_template );
			}
		} );

		/**
		 * Same "recreate pages" escape hatch a Commerce7 settings tab
		 * offers (still to be ported) — lets an admin regenerate any of
		 * these pages without deactivating/reactivating the theme, e.g.
		 * if one got deleted by mistake.
		 */
		add_action( 'admin_post_fw_recreate_pages', function () use ( $config ) {
			if ( ! current_user_can( 'edit_theme_options' ) ) wp_die();
			check_admin_referer( 'fw_recreate_pages' );
			self::create_marketing_and_legal_pages( $config );
			self::create_default_menus( $config );
			self::sync_default_menu_items( $config );
			wp_safe_redirect( admin_url( 'admin.php?page=' . $config->slug() . '-settings&tab=content&' . $config->slug() . '_pages_recreated=1' ) );
			exit;
		} );

		/**
		 * Strips the Journal link from every rendered nav menu whenever
		 * the site has zero published posts — a demo/fresh install
		 * shouldn't link to an empty blog archive. Filters the RENDERED
		 * menu rather than the saved menu items, so this applies
		 * immediately as posts are published/unpublished with no menu
		 * re-save needed. Matched by URL, not label.
		 */
		add_filter( 'wp_nav_menu_objects', function ( $items, $args ) use ( $config ) {
			if ( wp_count_posts()->publish >= 1 ) return $items;

			$journal_url = untrailingslashit( self::journal_url() );

			return array_values( array_filter( $items, function ( $item ) use ( $journal_url ) {
				return untrailingslashit( $item->url ) !== $journal_url;
			} ) );
		}, 10, 2 );
	}

	private static function journal_url(): string {
		return get_option( 'page_for_posts' ) ? get_permalink( get_option( 'page_for_posts' ) ) : home_url( '/journal' );
	}

	/**
	 * slug => [ title, template path ]. Default matches the converged
	 * 4-of-5-theme version; override any entry (or add new ones) via
	 * $config->get('pages.marketing').
	 */
	public static function marketing_pages( Config $config ): array {
		return $config->get( 'pages.marketing', array(
			'estate'      => array( __( 'How We Work', $config->text_domain() ), 'page-templates/page-estate.php' ),
			'team'        => array( __( 'Team', $config->text_domain() ), 'page-templates/page-team.php' ),
			'visit'       => array( __( 'Visit', $config->text_domain() ), 'page-templates/page-visit.php' ),
			'gift-cards'  => array( __( 'Gift Cards', $config->text_domain() ), 'page-templates/page-gift-cards.php' ),
			'contact'     => array( __( 'Contact', $config->text_domain() ), 'page-templates/page-contact.php' ),
			'trade-press' => array( __( 'Trade & Press', $config->text_domain() ), 'page-templates/page-trade-press.php' ),
		) );
	}

	/**
	 * slug => [ title, HTML content ]. Confirmed byte-identical
	 * placeholder copy across every original theme (each explicitly
	 * says "replace this placeholder" — this is starter content, not
	 * finished legal advice, by design). Override via
	 * $config->get('pages.legal') for a theme that wants different
	 * starter copy.
	 */
	public static function legal_pages( Config $config ): array {
		return $config->get( 'pages.legal', array(
			'returns' => array(
				__( 'Returns & Cancellations', $config->text_domain() ),
				'<p>' . __( "Because we're shipping alcohol, all sales are final once an order has shipped — we're not able to accept returns for a change of mind. If a bottle arrives damaged, flawed, or heat-affected, contact us within 14 days of delivery with a photo of the bottle and shipping box and we'll replace it or refund that item, our choice.", $config->text_domain() ) . "</p>\n<p>" . __( 'Orders can be cancelled or changed free of charge any time before they ship — email or call us and we\'ll take care of it. Wine Club shipments can be skipped, delayed, or the membership cancelled at any time from your account, or by contacting us directly.', $config->text_domain() ) . "</p>\n<p><em>" . __( 'Replace this placeholder with your actual returns policy — requirements vary by state, and this is not legal advice.', $config->text_domain() ) . '</em></p>',
			),
			'accessibility' => array(
				__( 'Accessibility Statement', $config->text_domain() ),
				'<p>' . sprintf( __( '%s is committed to making our website usable by everyone, including visitors using assistive technology such as screen readers, voice control, or keyboard-only navigation. We aim to meet WCAG 2.1 Level AA guidelines and continue to test and improve this site as it evolves.', $config->text_domain() ), $config->brand_name() ) . "</p>\n<p>" . __( "If you encounter any barrier using this site, or need information in an alternate format, please contact us — we want to know, and we'll do our best to resolve it promptly.", $config->text_domain() ) . "</p>\n<p><em>" . __( "Replace this placeholder with your actual accessibility statement once you've reviewed the finished site — this is not a substitute for an accessibility audit or legal advice.", $config->text_domain() ) . '</em></p>',
			),
			'shipping' => array(
				__( 'Shipping & Compliance', $config->text_domain() ),
				'<p>' . __( "Wine shipping is regulated state by state in the US — we can only ship to states where we're licensed to do so, and the list below can change as regulations do. If your state isn't listed, contact us before ordering; we may still be able to help through a different arrangement.", $config->text_domain() ) . "</p>\n<p><strong>" . __( 'We currently ship to:', $config->text_domain() ) . '</strong><br>' . __( 'California, Oregon, Washington, Nevada, Texas, New York, Florida — replace with your actual licensed states.', $config->text_domain() ) . "</p>\n<p>" . __( 'An adult signature (21+) is required on delivery for every wine shipment — someone of legal drinking age must be present to sign. Carriers will not leave alcohol unattended, and a missed delivery may be rescheduled or returned to us, which can incur a reshipping fee.', $config->text_domain() ) . "</p>\n<p>" . __( 'Orders typically ship within 3–5 business days. See our Returns & Cancellations policy for damaged-bottle claims.', $config->text_domain() ) . "</p>\n<p><em>" . __( 'Replace this placeholder with your actual shipping states and carrier details before a client site goes live — wine shipping law varies by state and changes over time; this is not legal advice.', $config->text_domain() ) . '</em></p>',
			),
			'faq' => array(
				__( 'Frequently Asked Questions', $config->text_domain() ),
				'<p>' . __( "Answers to what we're asked most — email us if yours isn't here.", $config->text_domain() ) . '</p>' .
				'<h3>' . __( 'Which states do you ship to?', $config->text_domain() ) . '</h3><p>' . __( 'See our Shipping & Compliance page for the current list — wine shipping is regulated state by state, and an adult signature (21+) is required on delivery.', $config->text_domain() ) . '</p>' .
				'<h3>' . __( 'How do I cancel or change my Wine Club membership?', $config->text_domain() ) . '</h3><p>' . __( "Skip a shipment, change your preferences, or cancel any time from your account — or contact us directly and we'll take care of it. There's no minimum commitment.", $config->text_domain() ) . '</p>' .
				'<h3>' . __( 'Do I need a reservation to visit for a tasting?', $config->text_domain() ) . '</h3><p>' . __( 'Yes — tastings are by appointment only, in a small room, so we can give every visit proper attention. Reserve a time on our Reservations page.', $config->text_domain() ) . '</p>' .
				'<h3>' . __( "What's your return policy?", $config->text_domain() ) . '</h3><p>' . __( "Because we're shipping alcohol, sales are final once an order has shipped, except for bottles that arrive damaged or flawed — see our Returns & Cancellations page for details.", $config->text_domain() ) . '</p>' .
				'<p><em>' . __( 'Replace or expand these placeholder answers with your own policies before a client site goes live.', $config->text_domain() ) . '</em></p>',
			),
		) );
	}

	public static function create_marketing_and_legal_pages( Config $config ): void {
		foreach ( self::marketing_pages( $config ) as $slug => $conf ) {
			list( $title, $template ) = $conf;
			$existing = get_page_by_path( $slug );

			if ( $existing ) {
				// A restored backup or a stray template change can
				// silently reset _wp_page_template while leaving the page
				// itself untouched — repaired without touching
				// title/slug/content.
				if ( get_post_meta( $existing->ID, '_wp_page_template', true ) !== $template ) {
					update_post_meta( $existing->ID, '_wp_page_template', $template );
				}
				continue;
			}

			$id = wp_insert_post( array(
				'post_title'   => $title,
				'post_name'    => $slug,
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => '',
			) );
			if ( $id && ! is_wp_error( $id ) ) {
				update_post_meta( $id, '_wp_page_template', $template );
			}
		}

		/**
		 * "Journal" — the blog index. Not a custom page-template like the
		 * others above: when a page is set as the site's Posts Page
		 * (Settings > Reading), WordPress automatically routes its URL
		 * through the archive.php/index.php hierarchy instead of
		 * page.php, so no template assignment is needed here. Only ever
		 * set page_for_posts if it's currently unset (0) — primes a
		 * fresh install so the Journal link works out of the box, but
		 * never overwrites a site that already has its Reading settings
		 * configured by hand.
		 */
		$journal = get_page_by_path( 'journal' );
		if ( ! $journal ) {
			$journal_id = wp_insert_post( array(
				'post_title'   => __( 'Journal', $config->text_domain() ),
				'post_name'    => 'journal',
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => '',
			) );
		} else {
			$journal_id = $journal->ID;
		}
		if ( $journal_id && ! is_wp_error( $journal_id ) && ! get_option( 'page_for_posts' ) ) {
			update_option( 'page_for_posts', $journal_id );
		}

		foreach ( self::legal_pages( $config ) as $slug => $conf ) {
			if ( get_page_by_path( $slug ) ) continue;
			list( $title, $content ) = $conf;
			wp_insert_post( array(
				'post_title'   => $title,
				'post_name'    => $slug,
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => $content,
			) );
		}
	}

	/**
	 * Single source of truth for both menus' structure — used to build a
	 * fresh menu (create_default_menus(), a brand new site) AND to sync
	 * genuinely-missing items into a menu that already exists
	 * (sync_default_menu_items(), a site whose menu predates a page this
	 * theme added later). Keeping one array instead of two means an item
	 * added here automatically benefits from both code paths. Default
	 * matches the converged 4-of-5-theme structure; override entirely
	 * via $config->get('menus').
	 */
	public static function default_menu_structure( Config $config ): array {
		$journal_url = self::journal_url();

		return $config->get( 'menus', array(
			$config->nav_location( 'primary' ) => array(
				'name'  => __( 'Primary Menu', $config->text_domain() ),
				'items' => array(
					array( __( 'Wines', $config->text_domain() ), home_url( '/collection/wines/' ), array(
						array( __( 'Our Wines', $config->text_domain() ), home_url( '/collection/wines/' ) ),
						array( __( 'Wine Club', $config->text_domain() ), home_url( '/club' ) ),
						array( __( 'Gift Cards', $config->text_domain() ), home_url( '/gift-cards' ) ),
					) ),
					array( __( 'How We Work', $config->text_domain() ), home_url( '/estate' ), array(
						array( __( 'Our Story', $config->text_domain() ), home_url( '/estate' ) ),
						array( __( 'Our Team', $config->text_domain() ), home_url( '/team' ) ),
					) ),
					array( __( 'Visit', $config->text_domain() ), home_url( '/visit' ), array(
						array( __( 'Plan Your Visit', $config->text_domain() ), home_url( '/visit' ) ),
						array( __( 'Events', $config->text_domain() ), home_url( '/events' ) ),
						array( __( 'Reserve a Tasting', $config->text_domain() ), get_option( $config->option_key( 'c7_reservation_url' ), home_url( '/reservation' ) ) ),
					) ),
					array( __( 'Journal', $config->text_domain() ), $journal_url ),
					array( __( 'Contact', $config->text_domain() ), home_url( '/contact' ) ),
				),
			),
			$config->nav_location( 'footer' ) => array(
				'name'  => __( 'Footer Menu', $config->text_domain() ),
				'items' => array(
					array( __( 'Our Wines', $config->text_domain() ), home_url( '/collection/wines/' ) ),
					array( __( 'How We Work', $config->text_domain() ), home_url( '/estate' ) ),
					array( __( 'Our Team', $config->text_domain() ), home_url( '/team' ) ),
					array( __( 'Wine Club', $config->text_domain() ), home_url( '/club' ) ),
					array( __( 'Gift Cards', $config->text_domain() ), home_url( '/gift-cards' ) ),
					array( __( 'Visit', $config->text_domain() ), home_url( '/visit' ) ),
					array( __( 'Trade & Press', $config->text_domain() ), home_url( '/trade-press' ) ),
				),
			),
		) );
	}

	public static function create_default_menus( Config $config ): void {
		foreach ( self::default_menu_structure( $config ) as $location => $menu ) {
			self::create_default_menu( $location, $menu['name'], $menu['items'] );
		}
	}

	/**
	 * has_nav_menu($location) guards every write — if a menu is already
	 * assigned to a location (created by hand, or a previous run), it's
	 * left completely alone. Existing menu items are never touched
	 * either, only added once when the menu itself is first created
	 * empty — safe to re-run any number of times, e.g. every "Recreate
	 * missing pages" click.
	 */
	private static function create_default_menu( string $location, string $menu_name, array $items ): void {
		if ( has_nav_menu( $location ) ) return;

		$menu    = wp_get_nav_menu_object( $menu_name );
		$menu_id = $menu ? $menu->term_id : wp_create_nav_menu( $menu_name );
		if ( is_wp_error( $menu_id ) || ! $menu_id ) return;

		if ( empty( wp_get_nav_menu_items( $menu_id ) ) ) {
			foreach ( $items as $item ) {
				$parent_id = wp_update_nav_menu_item( $menu_id, 0, array(
					'menu-item-title'  => $item[0],
					'menu-item-url'    => $item[1],
					'menu-item-status' => 'publish',
					'menu-item-type'   => 'custom',
				) );

				$children = isset( $item[2] ) ? $item[2] : array();
				foreach ( $children as $child ) {
					wp_update_nav_menu_item( $menu_id, 0, array(
						'menu-item-title'     => $child[0],
						'menu-item-url'       => $child[1],
						'menu-item-status'    => 'publish',
						'menu-item-type'      => 'custom',
						'menu-item-parent-id' => $parent_id,
					) );
				}
			}
		}

		$locations             = get_theme_mod( 'nav_menu_locations', array() );
		$locations[ $location ] = $menu_id;
		set_theme_mod( 'nav_menu_locations', $locations );
	}

	/**
	 * Adds any item from default_menu_structure() that's missing from a
	 * menu that ALREADY EXISTS at that location — purely additive,
	 * matched by URL so a client's own title customization never causes
	 * a duplicate, and nothing already in the menu is ever reordered,
	 * renamed, or removed. Covers a real, common case: a site's menu was
	 * built before this theme added a page, so that page exists but was
	 * never offered a spot in the nav.
	 */
	public static function sync_default_menu_items( Config $config ): void {
		foreach ( self::default_menu_structure( $config ) as $location => $menu ) {
			self::sync_missing_menu_items( $location, $menu['items'] );
		}
	}

	private static function sync_missing_menu_items( string $location, array $items ): void {
		if ( ! has_nav_menu( $location ) ) return; // no existing menu to sync into — create_default_menu() handles this case instead

		$locations = get_nav_menu_locations();
		$menu_id   = isset( $locations[ $location ] ) ? $locations[ $location ] : 0;
		if ( ! $menu_id ) return;

		$existing = wp_get_nav_menu_items( $menu_id );
		if ( ! $existing ) $existing = array();

		// url => menu item ID, built once and kept up to date as items
		// are added below so a child added in this same pass can still
		// find a parent that was ALSO just added in this same pass.
		$url_map = array();
		foreach ( $existing as $existing_item ) {
			$url_map[ untrailingslashit( $existing_item->url ) ] = $existing_item->ID;
		}

		foreach ( $items as $item ) {
			list( $label, $url ) = $item;
			$children = isset( $item[2] ) ? $item[2] : array();
			$url_key  = untrailingslashit( $url );

			if ( isset( $url_map[ $url_key ] ) ) {
				$parent_id = $url_map[ $url_key ];
			} else {
				$parent_id = wp_update_nav_menu_item( $menu_id, 0, array(
					'menu-item-title'  => $label,
					'menu-item-url'    => $url,
					'menu-item-status' => 'publish',
					'menu-item-type'   => 'custom',
				) );
				$url_map[ $url_key ] = $parent_id;
			}

			foreach ( $children as $child ) {
				list( $child_label, $child_url ) = $child;
				$child_key = untrailingslashit( $child_url );
				if ( isset( $url_map[ $child_key ] ) ) continue; // already in the menu somewhere — leave it exactly where the client put it

				$child_id = wp_update_nav_menu_item( $menu_id, 0, array(
					'menu-item-title'     => $child_label,
					'menu-item-url'       => $child_url,
					'menu-item-status'    => 'publish',
					'menu-item-type'      => 'custom',
					'menu-item-parent-id' => $parent_id,
				) );
				$url_map[ $child_key ] = $child_id;
			}
		}
	}
}
