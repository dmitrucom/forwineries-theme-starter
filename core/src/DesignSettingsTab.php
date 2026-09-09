<?php
// GENERATED FILE — do not edit directly. Synced from forwineries-theme-core.
// Edit the source there and re-run tools/sync-core.sh.
namespace ForWineries\Core;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * "Design" settings tab — lets a client restyle brand colors, typography,
 * corner style, button style, and hero-photo overlay darkness without
 * touching CSS. Ported from Fault Line's version (one of the 4 themes
 * already converged on the canonical 8-editable-color/26-token
 * vocabulary — see docs/DESIGN-TOKEN-SCHEMA.md — rather than Larkhaven's
 * older, smaller, pre-convergence 8-token set).
 *
 * Generalized from the original in one real way: every original theme
 * hand-duplicated its own "Default" palette/font preset as a literal
 * second copy of the exact values already declared in its config's
 * `tokens` array (the same array DesignTokens::css() reads) — a second
 * source of truth that could silently drift out of sync with what's
 * actually shipped. Here, the 'default' preset is derived FROM
 * $config->get('tokens') / $config->get('fonts_url') directly, so it can
 * never disagree with what the site actually ships. Additional named
 * presets (Fault Line's "Coastal Fog", "Terracotta & Olive", etc.) are
 * genuinely per-theme creative content and still come from
 * $config->get('design.palette_presets') / ('design.font_presets').
 *
 * Every option defaults to "as shipped" (empty/'default'), so a site
 * that never opens this tab renders byte-identical CSS to before. CSS
 * overrides are appended to the SAME 'fw-tokens' style handle
 * DesignTokens::register() already enqueues (priority 5) — appending
 * more inline CSS to that handle at a later priority (see register()
 * below) keeps the override printing right after the base :root token
 * block and before the theme's own style.css, so a later, higher-
 * specificity rule in style.css can't accidentally out-cascade it; the
 * button/corner overrides use !important for the same reason the
 * original did, to beat style.css's own component rules regardless of
 * load order.
 */
class DesignSettingsTab {

	/**
	 * The canonical 8 editable color tokens (docs/DESIGN-TOKEN-SCHEMA.md's
	 * 9 minus `line`, which is normally a computed translucent charcoal
	 * rather than something a client hand-picks). Override via
	 * $config->get('design.color_tokens') only for a genuine per-theme
	 * addition (e.g. Heronrest's extra `oatmeal`).
	 */
	public static function color_tokens( Config $config ): array {
		return $config->get( 'design.color_tokens', array(
			'cream'         => __( 'Page background (Cream)', $config->text_domain() ),
			'cream_raised'  => __( 'Alternating section background', $config->text_domain() ),
			'charcoal'      => __( 'Body text / headings', $config->text_domain() ),
			'charcoal_soft' => __( 'Muted/secondary text', $config->text_domain() ),
			'clay'          => __( 'Primary accent', $config->text_domain() ),
			'clay_dark'     => __( 'Primary accent — hover/dark shade', $config->text_domain() ),
			'olive'         => __( 'Secondary accent', $config->text_domain() ),
			'ink'           => __( 'Deepest band (footer, high-contrast)', $config->text_domain() ),
		) );
	}

	private static function palette_presets( Config $config ): array {
		$tokens  = self::color_tokens( $config );
		$default = array();
		foreach ( $tokens as $token => $label ) {
			$hex = $config->get( 'tokens.' . $token, '' );
			if ( $hex ) $default[ $token ] = $hex;
		}

		$presets = array(
			'default' => array(
				/* translators: %s: brand name. */
				'label'  => sprintf( __( '%s Default', $config->text_domain() ), $config->brand_name() ),
				'colors' => $default,
			),
		);
		return array_merge( $presets, $config->get( 'design.palette_presets', array() ) );
	}

	private static function font_presets( Config $config ): array {
		$presets = array(
			'default' => array(
				/* translators: %s: brand name. */
				'label'     => sprintf( __( '%s Default', $config->text_domain() ), $config->brand_name() ),
				'heading'   => $config->get( 'tokens.font_heading', '' ),
				'body'      => $config->get( 'tokens.font_body', '' ),
				'fonts_url' => $config->get( 'fonts_url', '' ),
			),
		);
		return array_merge( $presets, $config->get( 'design.font_presets', array() ) );
	}

	private static function corner_styles( Config $config ): array {
		return array(
			'default' => __( 'Default (as designed)', $config->text_domain() ),
			'sharp'   => __( 'Sharp corners', $config->text_domain() ),
			'rounded' => __( 'Rounded corners', $config->text_domain() ),
		);
	}

	private static function button_styles( Config $config ): array {
		return array(
			'default' => __( 'Default (as designed)', $config->text_domain() ),
			'outline' => __( 'Outline', $config->text_domain() ),
			'solid'   => __( 'Solid', $config->text_domain() ),
		);
	}

	private static function hero_overlays( Config $config ): array {
		return array(
			'default' => __( 'Default', $config->text_domain() ),
			'light'   => __( 'Lighter — for already-dark photos', $config->text_domain() ),
			'dark'    => __( 'Darker — for bright/busy photos', $config->text_domain() ),
		);
	}

	public static function register( Config $config ): void {
		$settings_group = $config->option_key( 'design_settings' );

		add_action( 'admin_init', function () use ( $config, $settings_group ) {
			register_setting( $settings_group, $config->option_key( 'design_palette' ), array(
				'type' => 'string', 'sanitize_callback' => 'sanitize_key', 'default' => 'default',
			) );
			foreach ( self::color_tokens( $config ) as $token => $label ) {
				register_setting( $settings_group, $config->option_key( 'design_color_' . $token ), array(
					'type' => 'string', 'sanitize_callback' => 'sanitize_hex_color', 'default' => '',
				) );
			}
			register_setting( $settings_group, $config->option_key( 'design_font_pairing' ), array(
				'type' => 'string', 'sanitize_callback' => 'sanitize_key', 'default' => 'default',
			) );
			register_setting( $settings_group, $config->option_key( 'design_corner_style' ), array(
				'type' => 'string', 'sanitize_callback' => 'sanitize_key', 'default' => 'default',
			) );
			register_setting( $settings_group, $config->option_key( 'design_button_style' ), array(
				'type' => 'string', 'sanitize_callback' => 'sanitize_key', 'default' => 'default',
			) );
			register_setting( $settings_group, $config->option_key( 'design_hero_overlay' ), array(
				'type' => 'string', 'sanitize_callback' => 'sanitize_key', 'default' => 'default',
			) );
		} );

		add_action( 'admin_enqueue_scripts', function ( $hook ) use ( $config ) {
			if ( $hook !== 'toplevel_page_' . $config->slug() . '-settings' ) return;
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'wp-color-picker' );
			$palette_field = $config->option_key( 'design_palette' );
			wp_add_inline_script( 'wp-color-picker', "
				jQuery(function ($) {
					$('." . esc_js( $config->css( 'design-color-field' ) ) . "').wpColorPicker();
					function toggleCustomColors() {
						$('." . esc_js( $config->css( 'design-custom-colors' ) ) . "').toggle($('#" . esc_js( $palette_field ) . "').val() === 'custom');
					}
					$('#" . esc_js( $palette_field ) . "').on('change', toggleCustomColors);
					toggleCustomColors();
				});
			" );
		} );

		add_action( 'wp_enqueue_scripts', function () use ( $config ) {
			self::print_css_overrides( $config );
		}, 20 );

		SettingsPage::add_tab( 'design', __( 'Design', $config->text_domain() ), function () use ( $config ) {
			self::render_tab_content( $config );
		}, 40 );
	}

	private static function render_tab_content( Config $config ): void {
		if ( ! current_user_can( 'manage_options' ) ) return;

		$palette      = get_option( $config->option_key( 'design_palette' ), 'default' );
		$font_pairing = get_option( $config->option_key( 'design_font_pairing' ), 'default' );
		$corner_style = get_option( $config->option_key( 'design_corner_style' ), 'default' );
		$button_style = get_option( $config->option_key( 'design_button_style' ), 'default' );
		$hero_overlay = get_option( $config->option_key( 'design_hero_overlay' ), 'default' );
		$defaults     = self::palette_presets( $config )['default']['colors'];
		?>
		<form method="post" action="options.php">
			<?php settings_fields( $config->option_key( 'design_settings' ) ); ?>
			<input type="hidden" name="_wp_http_referer" value="<?php echo esc_url( admin_url( 'admin.php?page=' . $config->slug() . '-settings&tab=design' ) ); ?>">

			<h2><?php esc_html_e( 'Colors', $config->text_domain() ); ?></h2>
			<p class="description"><?php esc_html_e( 'Pick a preset palette, or choose Custom to set every color yourself.', $config->text_domain() ); ?></p>
			<table class="form-table" role="presentation">
				<tr>
					<th><label for="<?php echo esc_attr( $config->option_key( 'design_palette' ) ); ?>"><?php esc_html_e( 'Palette', $config->text_domain() ); ?></label></th>
					<td>
						<select id="<?php echo esc_attr( $config->option_key( 'design_palette' ) ); ?>" name="<?php echo esc_attr( $config->option_key( 'design_palette' ) ); ?>">
							<?php foreach ( self::palette_presets( $config ) as $key => $preset ) : ?>
								<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $palette, $key ); ?>><?php echo esc_html( $preset['label'] ); ?></option>
							<?php endforeach; ?>
							<option value="custom" <?php selected( $palette, 'custom' ); ?>><?php esc_html_e( 'Custom', $config->text_domain() ); ?></option>
						</select>
					</td>
				</tr>
				<?php foreach ( self::color_tokens( $config ) as $token => $label ) :
					$option_name = $config->option_key( 'design_color_' . $token );
					$default_hex = isset( $defaults[ $token ] ) ? $defaults[ $token ] : '';
					?>
					<tr class="<?php echo esc_attr( $config->css( 'design-custom-colors' ) ); ?>">
						<th><label for="<?php echo esc_attr( $option_name ); ?>"><?php echo esc_html( $label ); ?></label></th>
						<td>
							<input type="text" id="<?php echo esc_attr( $option_name ); ?>" name="<?php echo esc_attr( $option_name ); ?>" class="<?php echo esc_attr( $config->css( 'design-color-field' ) ); ?>" value="<?php echo esc_attr( get_option( $option_name, '' ) ); ?>" data-default-color="<?php echo esc_attr( $default_hex ); ?>">
						</td>
					</tr>
				<?php endforeach; ?>
			</table>

			<h2><?php esc_html_e( 'Typography', $config->text_domain() ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th><label for="<?php echo esc_attr( $config->option_key( 'design_font_pairing' ) ); ?>"><?php esc_html_e( 'Font pairing', $config->text_domain() ); ?></label></th>
					<td>
						<select id="<?php echo esc_attr( $config->option_key( 'design_font_pairing' ) ); ?>" name="<?php echo esc_attr( $config->option_key( 'design_font_pairing' ) ); ?>">
							<?php foreach ( self::font_presets( $config ) as $key => $preset ) : ?>
								<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $font_pairing, $key ); ?>><?php echo esc_html( $preset['label'] ); ?></option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
			</table>

			<h2><?php esc_html_e( 'Logo', $config->text_domain() ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th><?php esc_html_e( 'Logo', $config->text_domain() ); ?></th>
					<td>
						<a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[control]=custom_logo' ) ); ?>" class="button"><?php esc_html_e( 'Manage Your Logo', $config->text_domain() ); ?></a>
						<p class="description"><?php esc_html_e( 'Your logo is a standard WordPress setting (Appearance → Customize → Site Identity), so it works the same way here as it would on any other WordPress site.', $config->text_domain() ); ?></p>
					</td>
				</tr>
			</table>

			<h2><?php esc_html_e( 'Shape & Style', $config->text_domain() ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th><label for="<?php echo esc_attr( $config->option_key( 'design_corner_style' ) ); ?>"><?php esc_html_e( 'Corner style', $config->text_domain() ); ?></label></th>
					<td>
						<select id="<?php echo esc_attr( $config->option_key( 'design_corner_style' ) ); ?>" name="<?php echo esc_attr( $config->option_key( 'design_corner_style' ) ); ?>">
							<?php foreach ( self::corner_styles( $config ) as $key => $label ) : ?>
								<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $corner_style, $key ); ?>><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
						</select>
						<p class="description"><?php esc_html_e( 'Applies to buttons, cards, and hero photos sitewide.', $config->text_domain() ); ?></p>
					</td>
				</tr>
				<tr>
					<th><label for="<?php echo esc_attr( $config->option_key( 'design_button_style' ) ); ?>"><?php esc_html_e( 'Button style', $config->text_domain() ); ?></label></th>
					<td>
						<select id="<?php echo esc_attr( $config->option_key( 'design_button_style' ) ); ?>" name="<?php echo esc_attr( $config->option_key( 'design_button_style' ) ); ?>">
							<?php foreach ( self::button_styles( $config ) as $key => $label ) : ?>
								<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $button_style, $key ); ?>><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
						</select>
						<p class="description"><?php esc_html_e( "Applies to the theme's plain buttons. Buttons with their own deliberate look are unaffected.", $config->text_domain() ); ?></p>
					</td>
				</tr>
				<tr>
					<th><label for="<?php echo esc_attr( $config->option_key( 'design_hero_overlay' ) ); ?>"><?php esc_html_e( 'Hero photo overlay', $config->text_domain() ); ?></label></th>
					<td>
						<select id="<?php echo esc_attr( $config->option_key( 'design_hero_overlay' ) ); ?>" name="<?php echo esc_attr( $config->option_key( 'design_hero_overlay' ) ); ?>">
							<?php foreach ( self::hero_overlays( $config ) as $key => $label ) : ?>
								<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $hero_overlay, $key ); ?>><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
						</select>
						<p class="description"><?php esc_html_e( 'How dark the gradient scrim over hero photos is — adjust if your own photos read too bright or too dark for the heading text on top.', $config->text_domain() ); ?></p>
					</td>
				</tr>
			</table>

			<?php submit_button(); ?>
		</form>
		<?php
	}

	/**
	 * Emits overrides only for settings the client actually changed from
	 * "Default" — a site that never opens this tab gets zero output
	 * here, byte-identical to before this feature existed.
	 */
	private static function print_css_overrides( Config $config ): void {
		$css = '';

		$palette = get_option( $config->option_key( 'design_palette' ), 'default' );
		if ( $palette === 'custom' ) {
			$vars = array();
			foreach ( self::color_tokens( $config ) as $token => $label ) {
				$hex = get_option( $config->option_key( 'design_color_' . $token ), '' );
				if ( $hex ) $vars[] = '--fw-' . str_replace( '_', '-', $token ) . ': ' . $hex . ';';
			}
			if ( $vars ) $css .= ":root {\n  " . implode( "\n  ", $vars ) . "\n}\n";
		} elseif ( $palette !== 'default' ) {
			$presets = self::palette_presets( $config );
			if ( isset( $presets[ $palette ] ) ) {
				$vars = array();
				foreach ( $presets[ $palette ]['colors'] as $token => $hex ) {
					$vars[] = '--fw-' . str_replace( '_', '-', $token ) . ': ' . $hex . ';';
				}
				if ( $vars ) $css .= ":root {\n  " . implode( "\n  ", $vars ) . "\n}\n";
			}
		}

		$font_pairing = get_option( $config->option_key( 'design_font_pairing' ), 'default' );
		if ( $font_pairing !== 'default' ) {
			$presets = self::font_presets( $config );
			if ( isset( $presets[ $font_pairing ] ) ) {
				$preset = $presets[ $font_pairing ];
				$css   .= ":root {\n  --fw-font-heading: {$preset['heading']};\n  --fw-font-body: {$preset['body']};\n}\n";
			}
		}

		$corner_style = get_option( $config->option_key( 'design_corner_style' ), 'default' );
		if ( $corner_style === 'sharp' ) {
			$css .= ":root { --fw-radius: 0; }\n";
		} elseif ( $corner_style === 'rounded' ) {
			$css .= ":root { --fw-radius: 10px; }\n.fw-hero { border-radius: 20px; overflow: hidden; }\n";
		}

		$button_style = get_option( $config->option_key( 'design_button_style' ), 'default' );
		if ( $button_style === 'solid' ) {
			$css .= "
.fw-btn:not(.fw-btn--solid):not(.fw-btn--accent) { background: var(--fw-clay) !important; color: var(--fw-cream) !important; }
.fw-btn:not(.fw-btn--solid):not(.fw-btn--accent):hover { background: var(--fw-clay-dark) !important; color: var(--fw-cream) !important; }
";
		} elseif ( $button_style === 'outline' ) {
			$css .= "
.fw-btn:not(.fw-btn--accent) { background: transparent !important; color: var(--fw-clay) !important; border-color: var(--fw-clay) !important; }
.fw-btn:not(.fw-btn--accent):hover { background: var(--fw-clay) !important; color: var(--fw-cream) !important; }
";
		}

		$hero_overlay = get_option( $config->option_key( 'design_hero_overlay' ), 'default' );
		if ( $hero_overlay === 'light' ) {
			$css .= ".fw-hero::before { background: linear-gradient(180deg, rgba(20, 8, 11, 0.32) 0%, rgba(20, 8, 11, 0.48) 100%); }\n";
		} elseif ( $hero_overlay === 'dark' ) {
			$css .= ".fw-hero::before { background: linear-gradient(180deg, rgba(20, 8, 11, 0.68) 0%, rgba(20, 8, 11, 0.85) 100%); }\n";
		}

		// Appended to the SAME 'fw-tokens' handle DesignTokens registers
		// (priority 5, this hook runs at priority 20) — see class
		// docblock for why that ordering is what makes this win the
		// cascade without needing !important for the color/font/corner
		// rules (the button/hero rules keep !important regardless, to
		// beat style.css's own component rules specifically).
		if ( $css ) wp_add_inline_style( 'fw-tokens', $css );

		// A non-default font pairing needs its own Google Fonts request.
		// The theme's own font stylesheet handle isn't known to core (it's
		// registered per-theme), so this only swaps fonts when the theme
		// opted in by declaring $config->get('fonts_handle').
		if ( $font_pairing !== 'default' ) {
			$presets      = self::font_presets( $config );
			$fonts_handle = $config->get( 'fonts_handle', '' );
			if ( $fonts_handle && isset( $presets[ $font_pairing ] ) && ! empty( $presets[ $font_pairing ]['fonts_url'] ) ) {
				wp_deregister_style( $fonts_handle );
				wp_register_style( $fonts_handle, $presets[ $font_pairing ]['fonts_url'], array(), null );
				wp_enqueue_style( $fonts_handle );
			}
		}
	}
}
