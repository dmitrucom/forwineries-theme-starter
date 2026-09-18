<?php
// GENERATED FILE — do not edit directly. Synced from forwineries-theme-core.
// Edit the source there and re-run tools/sync-core.sh.
namespace ForWineries\Core;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Shared motion layer (core/assets/css/motion.css + core/assets/js/motion.js)
 * and its "Motion" settings tab. See motion.css for the design contract.
 *
 * Priority 20 rather than the default 10: each theme enqueues its own
 * theme.js at 10, and four of the five run their own scroll-reveal there.
 * motion.js reads the {prefix}-reveal-init class those set, so it must be
 * ordered after them.
 *
 * Config, all optional:
 *
 *   'motion' => array(
 *       'enabled'        => true,   // false removes the layer and the tab
 *       'auto_reveal'    => false,  // true for a theme with no reveal of its own
 *       'hidden_effects' => array(),// effect ids this theme has no CSS for
 *       'extra_effects'  => array(),// id => [label, desc, pace] the theme adds
 *   )
 *
 * Client settings live in three options and reach the front end two ways:
 *
 *   - window.fwMotion (printed in <head>, so it exists before any theme.js
 *     runs): enabled flag, disabled effect ids, disabled placement ids.
 *     motion.js turns the effect ids into html.fw-off-{id} classes that
 *     motion.css and each theme's own motion rules gate on, and skips
 *     tagging for disabled placements. Theme JS reads the enabled flag.
 *   - --fw-motion-speed / --fw-speed-{id} custom properties, emitted only
 *     when changed from default. motion.css multiplies every duration by
 *     them, so a site that never opens the tab renders exactly as before.
 *
 * prefers-reduced-motion still wins over everything here: these settings
 * can only take motion away or slow it, never force it on a visitor who
 * asked for none.
 */
class Motion {

	/** Percent of the designed duration. 100 = as shipped. */
	const PACE_CHOICES = array( 60, 80, 100, 125, 150, 200 );

	/**
	 * Effect ids are also CSS class suffixes (html.fw-off-{id}) and custom
	 * property suffixes (--fw-speed-{id}), so they stay hyphenated.
	 */
	public static function effects( Config $config ): array {
		$td  = $config->text_domain();
		$all = array(
			'reveal'       => array(
				'label' => __( 'Scroll reveal', $td ),
				'desc'  => __( 'Sections, headings and cards fade and rise into place as they scroll into view.', $td ),
				'pace'  => true,
			),
			'stagger'      => array(
				'label' => __( 'Cascade', $td ),
				'desc'  => __( 'Items in a grid arrive one after another instead of all at once. Pace here widens or narrows the gap between them.', $td ),
				'pace'  => true,
			),
			'photo'        => array(
				'label' => __( 'Photo settle', $td ),
				'desc'  => __( 'Photos ease out of a slight zoom as they appear.', $td ),
				'pace'  => true,
			),
			'text'         => array(
				'label' => __( 'Text sequence', $td ),
				'desc'  => __( 'In heroes, section headings and copy blocks the heading appears first, then each following element in turn. Hero headlines build word by word. Pace here also sets the gap between elements.', $td ),
				'pace'  => true,
			),
			'hero-photo'   => array(
				'label' => __( 'Hero photo drift', $td ),
				'desc'  => __( 'Hero photos slowly push in from a slight zoom (the Ken Burns effect).', $td ),
				'pace'  => true,
			),
			'hero-lift'    => array(
				'label' => __( 'Hero photo light lift', $td ),
				'desc'  => __( 'Hero photos come up out of shadow as they appear.', $td ),
				'pace'  => true,
			),
			'cards'        => array(
				'label' => __( 'Feature card entrance', $td ),
				'desc'  => __( 'Events, team members and membership tiers use a longer, more spaced entrance than ordinary cards.', $td ),
				'pace'  => true,
			),
			'press'        => array(
				'label' => __( 'Button press feedback', $td ),
				'desc'  => __( 'Buttons compress slightly while pressed.', $td ),
				'pace'  => false,
			),
			'focus'        => array(
				'label' => __( 'Focus ring easing', $td ),
				'desc'  => __( 'The keyboard focus outline eases in instead of snapping on.', $td ),
				'pace'  => false,
			),
		);
		// A theme with motion of its own (a video hero, a pinned section)
		// registers those here so the client gets the same on/off + pace
		// controls. Each id also becomes html.fw-off-{id} and
		// --fw-speed-{id}, which the theme's own CSS/JS gate on.
		foreach ( (array) $config->get( 'motion.extra_effects', array() ) as $id => $def ) {
			$all[ $id ] = array(
				'label' => $def['label'] ?? $id,
				'desc'  => $def['desc'] ?? '',
				'pace'  => ! empty( $def['pace'] ),
			);
		}
		foreach ( (array) $config->get( 'motion.hidden_effects', array() ) as $id ) {
			unset( $all[ $id ] );
		}
		return $all;
	}

	/** Placement ids map to selectors in motion.js. */
	public static function placements( Config $config ): array {
		$td = $config->text_domain();
		return array(
			'headings'     => __( 'Section headings and intro text', $td ),
			'hero'         => __( 'Hero sections (text and photo)', $td ),
			'photos'       => __( 'Photos and split layouts', $td ),
			'stats'        => __( 'Stats', $td ),
			'wines'        => __( 'Wine cards', $td ),
			'club'         => __( 'Club cards and membership tiers', $td ),
			'blog'         => __( 'Journal posts', $td ),
			'gallery'      => __( 'Gallery tiles', $td ),
			'testimonials' => __( 'Testimonials', $td ),
			'team'         => __( 'Team', $td ),
			'events'       => __( 'Upcoming events', $td ),
			'commerce'     => __( 'Shop pages (product, cart, club signup, account)', $td ),
		);
	}

	public static function register( Config $config ): void {
		if ( ! $config->get( 'motion.enabled', true ) ) return;

		add_action( 'admin_init', function () use ( $config ) {
			self::register_settings( $config );
		} );

		SettingsPage::add_tab( 'motion', __( 'Motion', $config->text_domain() ), function () use ( $config ) {
			self::render_tab_content( $config );
		}, 45 );

		// In <head> rather than before motion.js: theme.js is enqueued
		// ahead of motion.js and reads the enabled flag, and this must not
		// depend on either script's position.
		add_action( 'wp_head', function () use ( $config ) {
			$s = self::settings( $config );
			wp_print_inline_script_tag( 'window.fwMotion = ' . wp_json_encode( array(
				'enabled'       => $s['enabled'],
				'autoReveal'    => (bool) $config->get( 'motion.auto_reveal', false ),
				'off'           => array_keys( array_filter( $s['effects'], function ( $e ) { return ! $e['on']; } ) ),
				'placementsOff' => array_keys( array_filter( $s['placements'], function ( $on ) { return ! $on; } ) ),
			) ) . ';' );
		}, 1 );

		add_action( 'wp_enqueue_scripts', function () use ( $config ) {
			$s = self::settings( $config );
			if ( ! $s['enabled'] ) return;

			wp_enqueue_style(
				'fw-motion',
				get_stylesheet_directory_uri() . '/core/assets/css/motion.css',
				array(),
				Config::asset_version( '/core/assets/css/motion.css' )
			);
			wp_enqueue_script(
				'fw-motion',
				get_stylesheet_directory_uri() . '/core/assets/js/motion.js',
				array(),
				Config::asset_version( '/core/assets/js/motion.js' ),
				true
			);

			$vars = array();
			if ( $s['speed'] !== 100 ) $vars[] = '--fw-motion-speed: ' . self::factor( $s['speed'] ) . ';';
			foreach ( $s['effects'] as $id => $effect ) {
				if ( $effect['on'] && $effect['pace'] !== 100 ) {
					$vars[] = '--fw-speed-' . $id . ': ' . self::factor( $effect['pace'] ) . ';';
				}
			}
			if ( $vars ) wp_add_inline_style( 'fw-motion', ":root {\n  " . implode( "\n  ", $vars ) . "\n}" );
		}, 20 );
	}

	private static function factor( int $percent ): string {
		return rtrim( rtrim( number_format( $percent / 100, 2, '.', '' ), '0' ), '.' );
	}

	/**
	 * Resolved settings with every option defaulted: enabled, speed (percent),
	 * effects[id] => ['on' => bool, 'pace' => int], placements[id] => bool.
	 */
	private static function settings( Config $config ): array {
		$effects_raw    = (array) get_option( $config->option_key( 'motion_effects' ), array() );
		$placements_raw = (array) get_option( $config->option_key( 'motion_placements' ), array() );

		$effects = array();
		foreach ( self::effects( $config ) as $id => $def ) {
			$row = isset( $effects_raw[ $id ] ) && is_array( $effects_raw[ $id ] ) ? $effects_raw[ $id ] : array();
			$effects[ $id ] = array(
				'on'   => ! isset( $row['on'] ) || $row['on'] !== '0',
				'pace' => $def['pace'] ? self::pace( $row['pace'] ?? 100 ) : 100,
			);
		}
		$placements = array();
		foreach ( self::placements( $config ) as $id => $label ) {
			$placements[ $id ] = ! isset( $placements_raw[ $id ] ) || $placements_raw[ $id ] !== '0';
		}

		return array(
			'enabled'    => get_option( $config->option_key( 'motion_on' ), '1' ) !== '0',
			'speed'      => self::pace( get_option( $config->option_key( 'motion_speed' ), 100 ) ),
			'effects'    => $effects,
			'placements' => $placements,
		);
	}

	public static function pace( $value ): int {
		$value = (int) $value;
		return in_array( $value, self::PACE_CHOICES, true ) ? $value : 100;
	}

	private static function register_settings( Config $config ): void {
		$group = $config->option_key( 'motion_settings' );

		register_setting( $group, $config->option_key( 'motion_on' ), array(
			'type'              => 'string',
			'default'           => '1',
			'sanitize_callback' => function ( $v ) { return $v === '0' ? '0' : '1'; },
		) );
		register_setting( $group, $config->option_key( 'motion_speed' ), array(
			'type'              => 'integer',
			'default'           => 100,
			'sanitize_callback' => array( self::class, 'pace' ),
		) );
		register_setting( $group, $config->option_key( 'motion_effects' ), array(
			'type'              => 'array',
			'default'           => array(),
			'sanitize_callback' => function ( $input ) use ( $config ) {
				$clean = array();
				foreach ( self::effects( $config ) as $id => $def ) {
					$row = isset( $input[ $id ] ) && is_array( $input[ $id ] ) ? $input[ $id ] : array();
					$clean[ $id ] = array(
						'on'   => ( $row['on'] ?? '1' ) === '0' ? '0' : '1',
						'pace' => self::pace( $row['pace'] ?? 100 ),
					);
				}
				return $clean;
			},
		) );
		register_setting( $group, $config->option_key( 'motion_placements' ), array(
			'type'              => 'array',
			'default'           => array(),
			'sanitize_callback' => function ( $input ) use ( $config ) {
				$clean = array();
				foreach ( self::placements( $config ) as $id => $label ) {
					$clean[ $id ] = ( $input[ $id ] ?? '1' ) === '0' ? '0' : '1';
				}
				return $clean;
			},
		) );
	}

	private static function pace_options( Config $config, int $current ): void {
		$td     = $config->text_domain();
		$labels = array(
			60  => __( 'Quick (0.6×)', $td ),
			80  => __( 'Brisk (0.8×)', $td ),
			100 => __( 'Default', $td ),
			125 => __( 'Relaxed (1.25×)', $td ),
			150 => __( 'Slow (1.5×)', $td ),
			200 => __( 'Very slow (2×)', $td ),
		);
		foreach ( self::PACE_CHOICES as $value ) {
			printf(
				'<option value="%d" %s>%s</option>',
				$value,
				selected( $current, $value, false ),
				esc_html( $labels[ $value ] )
			);
		}
	}

	/**
	 * Every checkbox is preceded by a hidden input of the same name carrying
	 * "0": an unchecked box submits nothing, and the Settings API would then
	 * leave the option untouched. PHP keeps the last value for a repeated
	 * name, so a checked box still wins with "1".
	 */
	private static function checkbox( string $name, bool $checked, string $label = '' ): void {
		printf(
			'<input type="hidden" name="%1$s" value="0"><label><input type="checkbox" name="%1$s" value="1" %2$s> %3$s</label>',
			esc_attr( $name ),
			checked( $checked, true, false ),
			esc_html( $label )
		);
	}

	private static function render_tab_content( Config $config ): void {
		if ( ! current_user_can( 'manage_options' ) ) return;

		$td       = $config->text_domain();
		$s        = self::settings( $config );
		$effects  = $config->option_key( 'motion_effects' );
		$places   = $config->option_key( 'motion_placements' );
		?>
		<form method="post" action="options.php">
			<?php settings_fields( $config->option_key( 'motion_settings' ) ); ?>
			<input type="hidden" name="_wp_http_referer" value="<?php echo esc_url( admin_url( 'admin.php?page=' . $config->slug() . '-settings&tab=motion' ) ); ?>">

			<h2><?php esc_html_e( 'Animations', $td ); ?></h2>
			<p class="description"><?php esc_html_e( 'Visitors whose device is set to reduce motion never see any of these animations, whatever is chosen here.', $td ); ?></p>
			<table class="form-table" role="presentation">
				<tr>
					<th><?php esc_html_e( 'Site animations', $td ); ?></th>
					<td>
						<?php self::checkbox( $config->option_key( 'motion_on' ), $s['enabled'], __( 'Enable animations', $td ) ); ?>
						<p class="description"><?php esc_html_e( 'Turn off to render every page static: no scroll reveals, no hero motion, no hover or press effects.', $td ); ?></p>
					</td>
				</tr>
				<tr>
					<th><label for="<?php echo esc_attr( $config->option_key( 'motion_speed' ) ); ?>"><?php esc_html_e( 'Overall pace', $td ); ?></label></th>
					<td>
						<select id="<?php echo esc_attr( $config->option_key( 'motion_speed' ) ); ?>" name="<?php echo esc_attr( $config->option_key( 'motion_speed' ) ); ?>">
							<?php self::pace_options( $config, $s['speed'] ); ?>
						</select>
						<p class="description"><?php esc_html_e( 'Scales the length of every animation on the site. Slower reads as more considered; quicker as more energetic.', $td ); ?></p>
					</td>
				</tr>
			</table>

			<h2><?php esc_html_e( 'Effects', $td ); ?></h2>
			<p class="description"><?php esc_html_e( 'Switch individual effects off, or give one its own pace relative to the overall setting above.', $td ); ?></p>
			<table class="widefat striped fw-motion-effects">
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'On', $td ); ?></th>
						<th scope="col"><?php esc_html_e( 'Effect', $td ); ?></th>
						<th scope="col"><?php esc_html_e( 'Pace', $td ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( self::effects( $config ) as $id => $def ) : ?>
						<tr>
							<td class="fw-motion-effects__on">
								<?php self::checkbox( $effects . '[' . $id . '][on]', $s['effects'][ $id ]['on'] ); ?>
							</td>
							<td>
								<strong><?php echo esc_html( $def['label'] ); ?></strong>
								<p class="description"><?php echo esc_html( $def['desc'] ); ?></p>
							</td>
							<td class="fw-motion-effects__pace">
								<?php if ( $def['pace'] ) : ?>
									<select name="<?php echo esc_attr( $effects . '[' . $id . '][pace]' ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Pace: %s', $td ), $def['label'] ) ); ?>">
										<?php self::pace_options( $config, $s['effects'][ $id ]['pace'] ); ?>
									</select>
								<?php else : ?>
									<span class="description">&mdash;</span>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<h2><?php esc_html_e( 'Placements', $td ); ?></h2>
			<p class="description"><?php esc_html_e( 'Where scroll animations run. Anything unticked appears in place with no entrance.', $td ); ?></p>
			<div class="fw-motion-placements">
				<?php foreach ( self::placements( $config ) as $id => $label ) : ?>
					<div class="fw-motion-placements__item">
						<?php self::checkbox( $places . '[' . $id . ']', $s['placements'][ $id ], $label ); ?>
					</div>
				<?php endforeach; ?>
			</div>

			<?php submit_button(); ?>
		</form>
		<?php
	}
}
