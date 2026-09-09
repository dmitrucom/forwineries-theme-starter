<?php
// GENERATED FILE — do not edit directly. Synced from forwineries-theme-core.
// Edit the source there and re-run tools/sync-core.sh.
namespace ForWineries\Core\Commerce7;

use ForWineries\Core\Config;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Auto-creates the WordPress pages Commerce7's storefront expects
 * (/collection, /product, /cart, /checkout, /profile, /club,
 * /reservation, plus /privacy + /terms which Commerce7 links to), wires
 * the matching page templates, and adds rewrite rules so deep links
 * like /collection/reds or /product/reserve-2021 still land on the
 * right page — commerce7.js reads the actual URL to decide what to
 * render into #c7-content.
 *
 * Ported near-verbatim from the original 5 themes — confirmed identical
 * route structure across all five.
 */
class Routing {

	public static function register( Config $config ): void {
		add_action( 'init', array( __CLASS__, 'add_rewrite_rules' ) );

		add_action( 'after_switch_theme', function () use ( $config ) {
			self::create_pages( $config );
			self::add_rewrite_rules();
			flush_rewrite_rules();
		} );

		/**
		 * Automatic version of the same template self-heal
		 * create_pages() already does — but that only runs on theme
		 * activation or the "Recreate C7 pages" button. This runs on
		 * every front-end request for these specific pages instead, so
		 * nothing ever needs a manual fix again. Root cause: opening any
		 * of these pages with "Edit with Elementor" (even without adding
		 * a widget) commonly reassigns the page's WordPress template to
		 * one of Elementor's own layouts, which breaks the Commerce7
		 * route silently (no error, just the wrong template, and
		 * #c7-content never mounting).
		 *
		 * Cheap by design: only queries anything on an actual is_page()
		 * request, and only for pages whose slug matches a known
		 * Commerce7 route — a single postmeta read per matching request,
		 * with a write only on an actual mismatch. Runs on
		 * template_redirect, early enough that WordPress hasn't resolved
		 * get_query_template() yet, so the correction takes effect on
		 * the SAME request.
		 */
		add_action( 'template_redirect', function () use ( $config ) {
			if ( ! is_page() ) return;

			$post = get_queried_object();
			if ( ! $post instanceof \WP_Post ) return;

			$routes = self::route_pages( $config );
			if ( ! isset( $routes[ $post->post_name ] ) ) return;

			$expected_template = $routes[ $post->post_name ][1];
			if ( get_post_meta( $post->ID, '_wp_page_template', true ) !== $expected_template ) {
				update_post_meta( $post->ID, '_wp_page_template', $expected_template );
			}
		} );
	}

	public static function route_pages( Config $config ): array {
		$td = $config->text_domain();
		return array(
			'collection'  => array( __( 'Wines', $td ), 'page-templates/page-c7-content.php' ),
			'product'     => array( __( 'Wine', $td ), 'page-templates/page-c7-content.php' ),
			'cart'        => array( __( 'Cart', $td ), 'page-templates/page-c7-content.php' ),
			'checkout'    => array( __( 'Checkout', $td ), 'page-templates/page-c7-checkout.php' ),
			'profile'     => array( __( 'My Account', $td ), 'page-templates/page-c7-content.php' ),
			'club'        => array( __( 'Wine Clubs', $td ), 'page-templates/page-c7-content.php' ),
			'reservation' => array( __( 'Reservations', $td ), 'page-templates/page-c7-content.php' ),
		);
	}

	public static function add_rewrite_rules(): void {
		add_rewrite_rule( '^collection/(.*)$', 'index.php?pagename=collection', 'top' );
		add_rewrite_rule( '^product/(.*)$', 'index.php?pagename=product', 'top' );
		add_rewrite_rule( '^cart/?$', 'index.php?pagename=cart', 'top' );
		add_rewrite_rule( '^checkout/(.*)$', 'index.php?pagename=checkout', 'top' );
		add_rewrite_rule( '^profile/(.*)$', 'index.php?pagename=profile', 'top' );
		add_rewrite_rule( '^club/?$', 'index.php?pagename=club', 'top' );
		add_rewrite_rule( '^reservation/?$', 'index.php?pagename=reservation', 'top' );
	}

	public static function create_pages( Config $config ): void {
		foreach ( self::route_pages( $config ) as $slug => $conf ) {
			list( $title, $template ) = $conf;
			$existing = get_page_by_path( $slug );

			if ( $existing ) {
				// Page already exists — still make sure its template is
				// correct. A restored backup, a manual template-dropdown
				// change, or an import can silently reset
				// _wp_page_template to "Default template" while leaving
				// the page itself untouched; re-applying this is always
				// safe — it never touches title/slug/content.
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

		$simple_pages = array(
			'privacy' => array( __( 'Privacy Policy', $config->text_domain() ), __( 'Replace this with your real privacy policy — Commerce7 links here from checkout.', $config->text_domain() ) ),
			'terms'   => array( __( 'Terms of Sale', $config->text_domain() ), __( 'Replace this with your real terms of sale — Commerce7 links here from checkout.', $config->text_domain() ) ),
		);
		foreach ( $simple_pages as $slug => $conf ) {
			if ( get_page_by_path( $slug ) ) continue;
			list( $title, $placeholder ) = $conf;
			wp_insert_post( array(
				'post_title'   => $title,
				'post_name'    => $slug,
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => '<p>' . esc_html( $placeholder ) . '</p>',
			) );
		}
	}
}
