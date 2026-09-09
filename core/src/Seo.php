<?php
// GENERATED FILE — do not edit directly. Synced from forwineries-theme-core.
// Edit the source there and re-run tools/sync-core.sh.
namespace ForWineries\Core;

use ForWineries\Core\Commerce7\Catalog;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Per-route SEO fixes for Commerce7's client-rendered pages
 * (/product/{slug} specifically) — WordPress only ever sees ONE static
 * "Wine" page for every product (Commerce7's own JS reads the URL and
 * decides which wine to render into #c7-content), so by default the raw
 * HTML <title>, canonical, and oEmbed discovery links are all whatever
 * that one static page's own title/permalink happen to be: generic, and
 * for canonical specifically, missing the slug entirely — every wine
 * canonicalizes to the bare /product/ page. Fixed here using the same
 * REST wine data Commerce7\Catalog already fetches for other widgets —
 * no new API calls, and nothing prints here at all if Commerce7 REST
 * credentials aren't configured or the product isn't found, same as
 * every other optional REST-backed widget.
 *
 * Scoped to the product-detail route only — every other page already
 * gets correct title/canonical/oEmbed from WordPress core as normal,
 * and Schema.php's own Winery/Product JSON-LD already covers the
 * homepage and Wines Catalog.
 *
 * Ported verbatim from Larkhaven — the only one of the 5 original
 * themes that had this file at all (confirmed: seo.php was added to
 * Larkhaven only and never backported to the other four — exactly the
 * kind of drift this rebuild exists to fix; every theme built on this
 * core gets it automatically now).
 */
class Seo {

	public static function register( Config $config ): void {
		add_action( 'template_redirect', function () use ( $config ) {
			if ( ! self::is_product_route() ) return;

			// WordPress's own defaults are wrong for this route (see
			// class docblock) — removed here, before wp_head fires, and
			// replaced below. Also drops the oEmbed discovery links
			// entirely rather than pointing them at the corrected URL:
			// there's no real embeddable content behind a bare Commerce7
			// mount div for an oEmbed consumer to fetch.
			remove_action( 'wp_head', 'rel_canonical' );
			remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );

			add_action( 'wp_head', function () {
				printf( "<link rel=\"canonical\" href=\"%s\" />\n", esc_url( self::current_url() ) );
			} );

			add_action( 'wp_head', function () use ( $config ) {
				self::output_product_meta( $config );
			}, 5 );
		} );

		/**
		 * <title> override — hooks the same `document_title` filter
		 * WordPress core assembles the tag from, so this doesn't fight
		 * core's title-tag output, it replaces its final result on this
		 * one route only. Falls back to a still-branded "Our Wines |
		 * {Brand}" (not the bare site title) when the product isn't
		 * found — e.g. REST credentials unset, or a genuinely bad slug.
		 */
		add_filter( 'document_title', function ( $title ) use ( $config ) {
			if ( ! self::is_product_route() ) return $title;
			$product = self::current_product( $config );
			$name    = ( $product && $product['title'] ) ? $product['title'] : __( 'Our Wines', $config->text_domain() );
			return $name . ' | ' . $config->brand_name();
		} );
	}

	/**
	 * Cached per-request (not per-call) — this file's hooks (template_redirect
	 * plus two wp_head callbacks) would otherwise each redo the same REST
	 * lookup Catalog::find_wine_by_slug() already caches via transient
	 * anyway, but there's no reason to even repeat the array walk within
	 * one request.
	 */
	private static function current_product( Config $config ) {
		static $product   = null;
		static $looked_up = false;
		if ( ! $looked_up ) {
			$looked_up = true;
			$slug      = Catalog::get_product_slug_from_request();
			if ( $slug ) $product = Catalog::find_wine_by_slug( $config, $slug );
		}
		return $product;
	}

	private static function is_product_route(): bool {
		return (bool) Catalog::get_product_slug_from_request();
	}

	/**
	 * Full, correct canonical URL for the CURRENT request — WordPress's
	 * own rel_canonical() has no idea a client-side router changed what's
	 * being viewed, so it always points at the one static page/permalink.
	 * Built from the raw request path (not the REST-fetched product) so
	 * this is correct even when REST credentials aren't configured at all.
	 */
	private static function current_url(): string {
		global $wp;
		$path = isset( $wp->request ) ? $wp->request : '';
		return home_url( trailingslashit( $path ) );
	}

	/**
	 * OpenGraph/Twitter tags and per-product Product JSON-LD (reuses
	 * Schema::products_jsonld(), which already accepts the exact shape
	 * Catalog::find_wine_by_slug() returns — both go through
	 * Catalog::shape_wine()). Only prints when the REST-cached lookup
	 * actually found this product; a request for a slug that doesn't
	 * resolve just gets no per-product tags rather than wrong/empty ones.
	 */
	private static function output_product_meta( Config $config ): void {
		$product = self::current_product( $config );
		if ( ! $product ) return;

		$url = self::current_url();
		?>
		<meta property="og:type" content="product" />
		<meta property="og:title" content="<?php echo esc_attr( $product['title'] ); ?>" />
		<meta property="og:url" content="<?php echo esc_url( $url ); ?>" />
		<meta property="og:site_name" content="<?php echo esc_attr( $config->brand_name() ); ?>" />
		<?php if ( $product['teaser'] ) : ?>
			<meta property="og:description" content="<?php echo esc_attr( wp_trim_words( $product['teaser'], 30 ) ); ?>" />
		<?php endif; ?>
		<?php if ( $product['image'] ) : ?>
			<meta property="og:image" content="<?php echo esc_url( $product['image'] ); ?>" />
			<meta name="twitter:card" content="summary_large_image" />
		<?php else : ?>
			<meta name="twitter:card" content="summary" />
		<?php endif; ?>
		<?php
		echo Schema::products_jsonld( array( $product ) );
	}
}
