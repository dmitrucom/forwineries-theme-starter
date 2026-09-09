<?php
// GENERATED FILE — do not edit directly. Synced from forwineries-theme-core.
// Edit the source there and re-run tools/sync-core.sh.
namespace ForWineries\Core;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Age verification gate — a full-viewport overlay shown on first visit.
 * Ported near-verbatim from the original 5 themes' inc/age-gate.php,
 * which were already ~byte-identical modulo prefix (confirmed via
 * normalized diff before porting — see docs/LESSONS.md). Fail-open by
 * design: a tiny inline script hooked to wp_head (priority 1, before any
 * non-essential asset) decides before first paint whether to add
 * "fw-age-gate-active" to <html>, which is the ONLY thing that makes the
 * gate visible (core/assets/css/age-gate.css sets display:none by
 * default) — a visitor with JS disabled never gets that class, so the
 * site loads normally rather than hard-blocking. The date-of-birth math
 * and cookie-setting live in core/assets/js/age-gate.js.
 *
 * All markup uses $config->css() — unprefixed "fw-*" class/id names, not
 * per-theme ones — so core/assets/css/age-gate.css and age-gate.js are
 * genuinely byte-identical across every theme (see the reasoning in
 * Config::css()'s docblock). Only the age-verified cookie is slug-scoped
 * (via $config->cookie_name()), since that one carries real migration
 * risk: renaming it would make an already-verified visitor see the gate
 * again the moment a site migrates onto this core.
 *
 * Copy/background/minimum-age come from $config['age_gate'] with sane
 * defaults below — NOT yet wired to admin-editable wp_options fields.
 * That wiring is added once ContentSettings\Framework exists (see
 * docs/ARCHITECTURE.md's Phase 0 ordering): this class works standalone
 * today, and gains "editable in wp-admin" as a later, additive step
 * rather than depending on it to function at all.
 */
class AgeGate {

	public static function register( Config $config ): void {
		add_action( 'wp_enqueue_scripts', function () use ( $config ) {
			wp_enqueue_style(
				'fw-age-gate',
				get_stylesheet_directory_uri() . '/core/assets/css/age-gate.css',
				array(),
				null
			);

			$handle = 'fw-age-gate';
			wp_enqueue_script(
				$handle,
				get_stylesheet_directory_uri() . '/core/assets/js/age-gate.js',
				array(),
				null,
				true
			);
			// age-gate.js is a static asset (not PHP-templated) but the
			// verified-cookie name is slug-scoped (see class docblock), so
			// it's threaded in as a small inline global printed immediately
			// before the script itself.
			wp_add_inline_script(
				$handle,
				'window.fwAgeGateCookie = ' . wp_json_encode( $config->cookie_name( 'age_verified' ) ) . ';',
				'before'
			);
		} );

		add_action( 'wp_head', function () use ( $config ) {
			self::print_gate_decision_script( $config );
		}, 1 );
	}

	private static function print_gate_decision_script( Config $config ): void {
		$cookie_name  = $config->cookie_name( 'age_verified' );
		$active_class = $config->css( 'age-gate-active' );
		?>
		<script>
		if ( document.cookie.indexOf( '<?php echo esc_js( $cookie_name ); ?>=1' ) === -1 ) {
			// forwineries.com preview bypass — catalog screenshots/embeds of
			// a demo skip the gate; real visitors (no ?fw_preview=1, no
			// forwineries.com referrer) always still see it. Never sets the
			// "remembered" cookie, so this only ever affects the one request.
			var fwIsPreview = false;
			try {
				fwIsPreview = new URLSearchParams( window.location.search ).get( 'fw_preview' ) === '1';
				if ( ! fwIsPreview && document.referrer ) {
					var fwRefHost = new URL( document.referrer ).hostname;
					fwIsPreview = fwRefHost === 'forwineries.com' || fwRefHost.slice( -15 ) === '.forwineries.com';
				}
			} catch ( e ) {}

			if ( ! fwIsPreview ) {
				document.documentElement.classList.add( '<?php echo esc_js( $active_class ); ?>' );
			}
		}
		</script>
		<?php
	}

	/**
	 * Plain country/region list for the gate's dropdown — cosmetic, not
	 * tied to any per-region legal drinking age (the configured minimum
	 * age applies uniformly regardless of what's selected here). Filtered
	 * per-slug so a theme can override without touching this file.
	 */
	public static function regions( Config $config ): array {
		$default = array(
			__( 'United States', $config->text_domain() ),
			__( 'Canada', $config->text_domain() ),
			__( 'United Kingdom', $config->text_domain() ),
			__( 'Ireland', $config->text_domain() ),
			__( 'Australia', $config->text_domain() ),
			__( 'New Zealand', $config->text_domain() ),
			__( 'France', $config->text_domain() ),
			__( 'Germany', $config->text_domain() ),
			__( 'Italy', $config->text_domain() ),
			__( 'Spain', $config->text_domain() ),
			__( 'Netherlands', $config->text_domain() ),
			__( 'Belgium', $config->text_domain() ),
			__( 'Switzerland', $config->text_domain() ),
			__( 'Sweden', $config->text_domain() ),
			__( 'Norway', $config->text_domain() ),
			__( 'Denmark', $config->text_domain() ),
			__( 'Japan', $config->text_domain() ),
			__( 'Singapore', $config->text_domain() ),
			__( 'Hong Kong', $config->text_domain() ),
			__( 'Other', $config->text_domain() ),
		);
		return apply_filters( $config->slug() . '_age_gate_regions', $default );
	}

	/**
	 * Markup only. Call from header.php right after wp_body_open(), before
	 * the skip link, so a keyboard/screen-reader user tabbing from the top
	 * of the page reaches the gate first rather than a skip link that would
	 * jump them past it into inaccessible content.
	 */
	public static function render( Config $config ): void {
		$defaults = wp_parse_args( $config->get( 'age_gate', array() ), array(
			'eyebrow'          => __( 'Please Confirm', $config->text_domain() ),
			'heading'          => sprintf( __( 'Welcome to %s', $config->text_domain() ), $config->brand_name() ),
			'intro'            => __( 'You must be of legal drinking age in your country of residence to enter this site.', $config->text_domain() ),
			'bg_image'         => get_stylesheet_directory_uri() . '/assets/images/age-gate-bg.jpg',
			'min_age'          => 21,
			'region_label'     => __( 'Country / Region', $config->text_domain() ),
			'remember_label'   => __( 'Remember me for 30 days', $config->text_domain() ),
			'submit_label'     => __( 'Enter', $config->text_domain() ),
			'disclaimer'       => __( 'By entering this site you are certifying that you are of legal drinking age.', $config->text_domain() ),
			'refusal_heading'  => __( "We're sorry", $config->text_domain() ),
			'refusal_message'  => __( 'You must be of legal drinking age to view this site.', $config->text_domain() ),
		) );

		/**
		 * A theme's ContentSettings\Framework schema MAY register an
		 * 'age_gate_global' group (admin-editable copy for this exact
		 * gate) — when it does, those wp-admin-saved values win over the
		 * $config['age_gate'] array default above, same override order
		 * every other Framework-backed field already follows. When a
		 * theme's schema has no such group, Framework::field() just
		 * returns each $defaults value straight back unchanged, so this
		 * is a no-op for a theme that hasn't wired this up. Class-exists
		 * guarded rather than a hard `use` import: AgeGate registers
		 * before ContentSettings\Framework in Boot::init() (fine, since
		 * this only runs at render() time, well after both have
		 * registered) but must not hard-fail for a config with no
		 * 'content_fields' key at all.
		 *
		 * Framework::field()'s own $fallback parameter can't be used for
		 * this — passing a non-empty fallback makes it win over a
		 * theme's own registered content_fields default (fallback is
		 * checked before the registered-default lookup), which would
		 * make every theme's 'age_gate_global' group permanently
		 * unreachable except after a client explicitly re-saves that
		 * exact field. Called with no fallback instead, so the 3-tier
		 * order is: saved value -> theme's content_fields default ->
		 * this class's own generic default (applied manually below).
		 */
		$copy = $defaults;
		if ( class_exists( '\\ForWineries\\Core\\ContentSettings\\Framework' ) ) {
			foreach ( $defaults as $key => $default_value ) {
				$value = \ForWineries\Core\ContentSettings\Framework::field( $config, 'age_gate_' . $key );
				$copy[ $key ] = $value !== '' ? $value : (string) $default_value;
			}
		}

		$min_age = (int) $copy['min_age'];
		if ( $min_age < 1 ) $min_age = 21;
		?>
		<div class="<?php echo esc_attr( $config->css( 'age-gate' ) ); ?>" id="<?php echo esc_attr( $config->css( 'age-gate' ) ); ?>" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Age verification', $config->text_domain() ); ?>" data-min-age="<?php echo esc_attr( $min_age ); ?>">
			<div class="<?php echo esc_attr( $config->css( 'age-gate-bg' ) ); ?>" style="background-image:url('<?php echo esc_url( $copy['bg_image'] ); ?>');"></div>
			<div class="<?php echo esc_attr( $config->css( 'age-gate-overlay' ) ); ?>"></div>
			<div class="<?php echo esc_attr( $config->css( 'age-gate-panel' ) ); ?>">
				<form class="<?php echo esc_attr( $config->css( 'age-gate-card' ) . ' ' . $config->css( 'age-gate-form' ) ); ?>" id="<?php echo esc_attr( $config->css( 'age-gate-form' ) ); ?>" novalidate>
					<span class="<?php echo esc_attr( $config->css( 'eyebrow' ) ); ?>"><?php echo esc_html( $copy['eyebrow'] ); ?></span>
					<h2><?php echo esc_html( $copy['heading'] ); ?></h2>
					<p class="<?php echo esc_attr( $config->css( 'age-gate-intro' ) ); ?>"><?php echo esc_html( $copy['intro'] ); ?></p>

					<div class="<?php echo esc_attr( $config->css( 'age-gate-field' ) ); ?>">
						<label for="<?php echo esc_attr( $config->css( 'age-gate-region' ) ); ?>"><?php echo esc_html( $copy['region_label'] ); ?></label>
						<select id="<?php echo esc_attr( $config->css( 'age-gate-region' ) ); ?>" name="region">
							<option value=""><?php esc_html_e( 'Select One', $config->text_domain() ); ?></option>
							<?php foreach ( self::regions( $config ) as $region ) : ?>
								<option value="<?php echo esc_attr( $region ); ?>"><?php echo esc_html( $region ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>

					<div class="<?php echo esc_attr( $config->css( 'age-gate-dob' ) ); ?>">
						<div class="<?php echo esc_attr( $config->css( 'age-gate-dob-field' ) ); ?>">
							<label for="<?php echo esc_attr( $config->css( 'age-gate-year' ) ); ?>"><?php esc_html_e( 'Year', $config->text_domain() ); ?></label>
							<input type="number" inputmode="numeric" id="<?php echo esc_attr( $config->css( 'age-gate-year' ) ); ?>" name="year" placeholder="YYYY" min="1900" max="<?php echo esc_attr( gmdate( 'Y' ) ); ?>">
						</div>
						<div class="<?php echo esc_attr( $config->css( 'age-gate-dob-field' ) ); ?>">
							<label for="<?php echo esc_attr( $config->css( 'age-gate-month' ) ); ?>"><?php esc_html_e( 'Month', $config->text_domain() ); ?></label>
							<input type="number" inputmode="numeric" id="<?php echo esc_attr( $config->css( 'age-gate-month' ) ); ?>" name="month" placeholder="MM" min="1" max="12">
						</div>
						<div class="<?php echo esc_attr( $config->css( 'age-gate-dob-field' ) ); ?>">
							<label for="<?php echo esc_attr( $config->css( 'age-gate-day' ) ); ?>"><?php esc_html_e( 'Day', $config->text_domain() ); ?></label>
							<input type="number" inputmode="numeric" id="<?php echo esc_attr( $config->css( 'age-gate-day' ) ); ?>" name="day" placeholder="DD" min="1" max="31">
						</div>
					</div>

					<p class="<?php echo esc_attr( $config->css( 'age-gate-error' ) ); ?>" id="<?php echo esc_attr( $config->css( 'age-gate-error' ) ); ?>" role="alert" hidden></p>

					<label class="<?php echo esc_attr( $config->css( 'age-gate-remember' ) ); ?>">
						<input type="checkbox" id="<?php echo esc_attr( $config->css( 'age-gate-remember' ) ); ?>" name="remember">
						<?php echo esc_html( $copy['remember_label'] ); ?>
					</label>

					<button type="submit" class="<?php echo esc_attr( $config->css( 'age-gate-submit' ) ); ?>"><?php echo esc_html( $copy['submit_label'] ); ?></button>

					<p class="<?php echo esc_attr( $config->css( 'age-gate-disclaimer' ) ); ?>"><?php echo esc_html( $copy['disclaimer'] ); ?></p>
				</form>

				<div class="<?php echo esc_attr( $config->css( 'age-gate-card' ) . ' ' . $config->css( 'age-gate-refusal' ) ); ?>">
					<span class="<?php echo esc_attr( $config->css( 'eyebrow' ) ); ?>"><?php echo esc_html( $copy['eyebrow'] ); ?></span>
					<h2><?php echo esc_html( $copy['refusal_heading'] ); ?></h2>
					<p><?php echo esc_html( $copy['refusal_message'] ); ?></p>
				</div>
			</div>
		</div>
		<?php
	}
}
