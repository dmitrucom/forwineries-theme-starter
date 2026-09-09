<?php
// GENERATED FILE — do not edit directly. Synced from forwineries-theme-core.
// Edit the source there and re-run tools/sync-core.sh.
namespace ForWineries\Core;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Events — a real custom post type (not a content-settings repeater field)
 * since a winery adds a new event indefinitely over time and each one
 * deserves its own real URL, own image, and its own place in wp-admin.
 * No ACF: a plain add_meta_box() for the three event-specific fields
 * (date, time, RSVP link). Ported near-verbatim from the original 5
 * themes' inc/events.php, which were already ~byte-identical modulo
 * prefix and seed-copy wording (see docs/LESSONS.md).
 *
 * The post type slug itself is the one thing here that stays slug-scoped
 * (via $config->post_type()) rather than using the shared 'fw-*' CSS
 * vocabulary — see Config::post_type()'s docblock for why.
 *
 * Templates: archive-{post_type}.php (/events, upcoming-only,
 * soonest-first) and single-{post_type}.php (/events/{slug}) stay in
 * each product repo, following WordPress's own template-hierarchy
 * naming — these are genuinely per-theme (brand voice/layout), same as
 * front-page.php.
 */
class Events {

	public static function register( Config $config ): void {
		$post_type = $config->post_type( 'event' );

		add_action( 'init', function () use ( $config, $post_type ) {
			register_post_type( $post_type, array(
				'labels' => array(
					'name'          => __( 'Events', $config->text_domain() ),
					'singular_name' => __( 'Event', $config->text_domain() ),
					'add_new_item'  => __( 'Add New Event', $config->text_domain() ),
					'edit_item'     => __( 'Edit Event', $config->text_domain() ),
					'all_items'     => __( 'Events', $config->text_domain() ),
					'search_items'  => __( 'Search Events', $config->text_domain() ),
					'not_found'     => __( 'No events found', $config->text_domain() ),
				),
				'public'        => true,
				'has_archive'   => 'events',
				'rewrite'       => array( 'slug' => 'events' ),
				'show_in_rest'  => true,
				'menu_icon'     => 'dashicons-calendar-alt',
				'menu_position' => 20,
				'supports'      => array( 'title', 'editor', 'thumbnail' ),
			) );
		} );

		/**
		 * Registering the CPT above only takes effect for URLs once
		 * WordPress's rewrite rules are flushed — normally on theme
		 * activation (after_switch_theme), but an already-live site
		 * getting this via a plain git deploy never re-fires that hook.
		 * One-time flag-guarded flush on admin_init instead, same pattern
		 * as every other run-once migration in this codebase (see
		 * docs/LESSONS.md's "WordPress self-heal timing" section).
		 */
		add_action( 'admin_init', function () use ( $config ) {
			$flag = $config->option_key( 'events_rewrite_flushed' );
			if ( get_option( $flag ) ) return;
			flush_rewrite_rules();
			update_option( $flag, 1 );
		} );

		add_action( 'add_meta_boxes', function () use ( $config, $post_type ) {
			add_meta_box(
				'fw_event_details',
				__( 'Event Details', $config->text_domain() ),
				function ( $post ) use ( $config ) {
					self::render_meta_box( $config, $post );
				},
				$post_type,
				'side',
				'default'
			);
		} );

		add_action( 'save_post_' . $post_type, function ( $post_id ) {
			self::save_meta_box( $post_id );
		} );

		/**
		 * The /events archive shows upcoming events only, soonest-first —
		 * once an event's date has passed it drops off the main listing
		 * (still reachable directly by URL). Scoped to this theme's own
		 * post type only.
		 */
		add_action( 'pre_get_posts', function ( $query ) use ( $post_type ) {
			if ( is_admin() || ! $query->is_main_query() ) return;
			if ( ! is_post_type_archive( $post_type ) ) return;

			$query->set( 'meta_key', 'event_date' );
			$query->set( 'orderby', 'meta_value' );
			$query->set( 'order', 'ASC' );
			$query->set( 'meta_query', array(
				array(
					'key'     => 'event_date',
					'value'   => current_time( 'Y-m-d' ),
					'compare' => '>=',
					'type'    => 'DATE',
				),
			) );
		} );

		add_action( 'after_switch_theme', function () use ( $config ) {
			self::seed( $config );
		} );
		add_action( 'admin_init', function () use ( $config ) {
			self::seed( $config );
		} );
	}

	private static function render_meta_box( Config $config, $post ): void {
		wp_nonce_field( 'fw_event_save', 'fw_event_nonce' );
		$date = get_post_meta( $post->ID, 'event_date', true );
		$time = get_post_meta( $post->ID, 'event_time', true );
		$rsvp = get_post_meta( $post->ID, 'event_rsvp_url', true );
		?>
		<p>
			<label for="fw_event_date"><strong><?php esc_html_e( 'Date', $config->text_domain() ); ?></strong></label><br>
			<input type="date" id="fw_event_date" name="fw_event_date" value="<?php echo esc_attr( $date ); ?>" style="width:100%;">
		</p>
		<p>
			<label for="fw_event_time"><strong><?php esc_html_e( 'Time', $config->text_domain() ); ?></strong></label><br>
			<input type="text" id="fw_event_time" name="fw_event_time" value="<?php echo esc_attr( $time ); ?>" placeholder="6:00 PM – 9:00 PM" style="width:100%;">
		</p>
		<p>
			<label for="fw_event_rsvp"><strong><?php esc_html_e( 'RSVP link (optional)', $config->text_domain() ); ?></strong></label><br>
			<input type="url" id="fw_event_rsvp" name="fw_event_rsvp_url" value="<?php echo esc_attr( $rsvp ); ?>" placeholder="<?php echo esc_attr( self::rsvp_url_default( $config ) ); ?>" style="width:100%;">
			<span class="description"><?php esc_html_e( 'Leave blank to use the Reservations link.', $config->text_domain() ); ?></span>
		</p>
		<?php
	}

	private static function save_meta_box( $post_id ): void {
		if ( ! isset( $_POST['fw_event_nonce'] ) || ! wp_verify_nonce( $_POST['fw_event_nonce'], 'fw_event_save' ) ) return;
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		if ( ! current_user_can( 'edit_post', $post_id ) ) return;

		if ( isset( $_POST['fw_event_date'] ) ) update_post_meta( $post_id, 'event_date', sanitize_text_field( wp_unslash( $_POST['fw_event_date'] ) ) );
		if ( isset( $_POST['fw_event_time'] ) ) update_post_meta( $post_id, 'event_time', sanitize_text_field( wp_unslash( $_POST['fw_event_time'] ) ) );
		if ( isset( $_POST['fw_event_rsvp_url'] ) ) update_post_meta( $post_id, 'event_rsvp_url', esc_url_raw( wp_unslash( $_POST['fw_event_rsvp_url'] ) ) );
	}

	/**
	 * The reservation-URL fallback reads the same wp_options key the
	 * (not yet ported) Commerce7 module owns — kept as one shared key
	 * name, option_key('c7_reservation_url'), so the two stay in sync
	 * once Commerce7 lands rather than drifting into two separate keys.
	 */
	private static function rsvp_url_default( Config $config ): string {
		return get_option( $config->option_key( 'c7_reservation_url' ), home_url( '/reservation' ) );
	}

	public static function date_display( $post_id ): string {
		$date = get_post_meta( $post_id, 'event_date', true );
		return $date ? date_i18n( get_option( 'date_format' ), strtotime( $date ) ) : '';
	}

	public static function rsvp_url( Config $config, $post_id ): string {
		$url = get_post_meta( $post_id, 'event_rsvp_url', true );
		return $url ? $url : self::rsvp_url_default( $config );
	}

	/**
	 * Next N upcoming events, soonest-first — for a theme's front-page.php
	 * "Upcoming Events" section. Same upcoming-only filter as the archive
	 * query above, factored out so both stay in sync.
	 */
	public static function upcoming( Config $config, int $limit = 3 ): array {
		return get_posts( array(
			'post_type'      => $config->post_type( 'event' ),
			'posts_per_page' => $limit,
			'meta_key'       => 'event_date',
			'orderby'        => 'meta_value',
			'order'          => 'ASC',
			'meta_query'     => array(
				array(
					'key'     => 'event_date',
					'value'   => current_time( 'Y-m-d' ),
					'compare' => '>=',
					'type'    => 'DATE',
				),
			),
		) );
	}

	/**
	 * Seeds two example events on activation so /events isn't empty on a
	 * fresh install. Flag-guarded so this only ever runs once; a client
	 * is free to delete the seed events afterward without them coming
	 * back. Seed copy comes from $config->get('events.seed') — the
	 * original 5 themes each hand-wrote brand-voice-specific wording
	 * here ("vineyard" vs. "warehouse crush day"), so this is genuinely
	 * per-theme content with a generic fallback, not shared prose.
	 */
	public static function seed( Config $config ): void {
		$flag = $config->option_key( 'events_seeded' );
		if ( get_option( $flag ) ) return;

		$events = $config->get( 'events.seed', array(
			array(
				'title'   => __( 'Harvest Celebration', $config->text_domain() ),
				'content' => __( 'Join us for our annual harvest celebration — live music, a seated dinner, and first pours of the newest vintage.', $config->text_domain() ),
				'months'  => 2,
				'time'    => '5:00 PM – 9:00 PM',
			),
			array(
				'title'   => __( 'Winter Barrel Tasting', $config->text_domain() ),
				'content' => __( 'A rare look inside the cellar — taste young wine straight from the barrel alongside our winemaker, paired with a small bites menu built around the vintage.', $config->text_domain() ),
				'months'  => 4,
				'time'    => '2:00 PM – 4:30 PM',
			),
		) );

		foreach ( $events as $event ) {
			$id = wp_insert_post( array(
				'post_title'   => $event['title'],
				'post_content' => $event['content'],
				'post_status'  => 'publish',
				'post_type'    => $config->post_type( 'event' ),
			) );
			if ( $id && ! is_wp_error( $id ) ) {
				update_post_meta( $id, 'event_date', gmdate( 'Y-m-d', strtotime( '+' . (int) $event['months'] . ' months' ) ) );
				update_post_meta( $id, 'event_time', $event['time'] );
			}
		}

		update_option( $flag, 1 );
	}
}
