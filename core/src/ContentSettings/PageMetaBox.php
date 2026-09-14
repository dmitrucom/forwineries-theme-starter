<?php
// GENERATED FILE — do not edit directly. Synced from forwineries-theme-core.
// Edit the source there and re-run tools/sync-core.sh.
namespace ForWineries\Core\ContentSettings;

use ForWineries\Core\Config;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Surfaces a subset of Framework's content fields directly on the
 * WordPress edit screen of the real page they belong to, instead of
 * requiring a trip to Settings > Pages & Content. Reads and writes the
 * exact same options Framework's own settings tab does (via
 * Framework::render_group_fields()/save_field()) — there is no second
 * copy of this data, so a value changed here or there is immediately
 * the same value everywhere, with no sync step needed.
 *
 * Only two kinds of groups get a meta box, both handled generically
 * rather than needing this class to know each theme's field schema:
 *
 * 1. The 6 marketing pages PageSetup::marketing_pages() always creates
 *    as real WordPress Page posts (estate, team, visit, gift-cards,
 *    contact, trade-press) — matched here by post slug against the
 *    corresponding "*_page" content-field group. club_page/
 *    reservation_page/profile_page are deliberately excluded: those
 *    are Commerce7-hosted virtual routes (account/club/reservation),
 *    not real editable Page posts, so they stay Settings-tab-only.
 * 2. Whichever page is the site's static front page (Settings >
 *    Reading), if one is configured — gets every "homepage" category
 *    group (hero, story, flagship wine, etc.), since front-page.php
 *    renders all of them regardless of which theme this is. A fresh
 *    site with no static front page assigned yet (show_on_front =
 *    "posts") simply doesn't get this meta box anywhere — those fields
 *    stay reachable from the settings tab only, same as today.
 */
class PageMetaBox {

	/**
	 * post_name (as created by PageSetup::marketing_pages()) => the
	 * matching content-field group key. Not derived by string
	 * convention (gift-cards/trade-press don't map predictably) —
	 * confirmed identical across all 5 product themes and the starter,
	 * so a fixed table here is the correct amount of shared behavior.
	 */
	private static function marketing_slug_groups(): array {
		return array(
			'estate'      => 'estate_page',
			'team'        => 'team_page',
			'visit'       => 'visit_page',
			'gift-cards'  => 'gift_cards_page',
			'contact'     => 'contact_page',
			'trade-press' => 'trade_page',
		);
	}

	public static function register( Config $config ): void {
		add_action( 'add_meta_boxes_page', function ( $post ) use ( $config ) {
			$groups = self::groups_for_post( $config, $post );
			if ( empty( $groups ) ) return;

			add_meta_box(
				$config->slug() . '-page-content',
				__( 'Theme Content & Images', $config->text_domain() ),
				function ( $post ) use ( $config, $groups ) {
					self::render( $config, $post, $groups );
				},
				'page',
				'normal',
				'high'
			);
		} );

		add_action( 'save_post_page', function ( $post_id, $post ) use ( $config ) {
			self::save( $config, $post_id, $post );
		}, 10, 2 );

		add_action( 'admin_enqueue_scripts', function ( $hook ) use ( $config ) {
			if ( $hook !== 'post.php' ) return;
			$post_id = isset( $_GET['post'] ) ? (int) $_GET['post'] : 0;
			$post    = $post_id ? get_post( $post_id ) : null;
			if ( ! $post || empty( self::groups_for_post( $config, $post ) ) ) return;

			// Same admin CSS/JS the settings tab uses for these exact
			// field types (image picker preview, repeater add/remove/
			// reorder) — see ContentSettings\Framework::register() for
			// why wp_enqueue_media() is required for the picker to work.
			wp_enqueue_media();
			wp_enqueue_style(
				'fw-admin-settings',
				get_stylesheet_directory_uri() . '/core/assets/css/admin-settings.css',
				array(),
				Config::asset_version( '/core/assets/css/admin-settings.css' )
			);
			wp_enqueue_script(
				'fw-repeater-admin',
				get_stylesheet_directory_uri() . '/core/assets/js/repeater-admin.js',
				array( 'jquery' ),
				Config::asset_version( '/core/assets/js/repeater-admin.js' ),
				true
			);
		} );
	}

	/**
	 * Which content-field groups (if any) apply to this specific page
	 * post — at most one marketing-page group plus, if this post is
	 * also the configured static front page, every homepage-category
	 * group. Used both to decide whether to register/render the meta
	 * box and, on save, which fields to actually write.
	 */
	private static function groups_for_post( Config $config, \WP_Post $post ): array {
		$all = Framework::field_groups( $config );
		$out = array();

		$slug_map = self::marketing_slug_groups();
		if ( isset( $slug_map[ $post->post_name ] ) && isset( $all[ $slug_map[ $post->post_name ] ] ) ) {
			$group_id          = $slug_map[ $post->post_name ];
			$out[ $group_id ]  = $all[ $group_id ];
		}

		if ( (int) get_option( 'page_on_front' ) === $post->ID ) {
			foreach ( $all as $group_id => $group ) {
				if ( Framework::group_category( $group_id ) === 'homepage' ) {
					$out[ $group_id ] = $group;
				}
			}
		}

		return $out;
	}

	private static function render( Config $config, \WP_Post $post, array $groups ): void {
		wp_nonce_field( 'fw_page_meta_' . $post->ID, 'fw_page_meta_nonce' );
		?>
		<p class="description">
			<?php esc_html_e( 'These are the same fields as Settings > Pages & Content — editing them here updates the exact same values. Leave any field blank (or clear an image) to fall back to this theme\'s own default.', $config->text_domain() ); ?>
		</p>
		<?php foreach ( $groups as $group ) : ?>
			<?php if ( count( $groups ) > 1 ) : ?>
				<h3><?php echo esc_html( $group['title'] ); ?></h3>
			<?php endif; ?>
			<?php Framework::render_group_fields( $config, $group ); ?>
		<?php endforeach;
	}

	private static function save( Config $config, int $post_id, \WP_Post $post ): void {
		if ( ! isset( $_POST['fw_page_meta_nonce'] ) ) return;
		if ( ! wp_verify_nonce( $_POST['fw_page_meta_nonce'], 'fw_page_meta_' . $post_id ) ) return;
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		if ( wp_is_post_revision( $post_id ) ) return;
		if ( ! current_user_can( 'edit_page', $post_id ) ) return;

		$groups = self::groups_for_post( $config, $post );
		if ( empty( $groups ) ) return;

		foreach ( $groups as $group ) {
			foreach ( $group['fields'] as $key => $field ) {
				Framework::save_field( $config, $key, $field, $_POST );
			}
		}
	}
}
