<?php
// GENERATED FILE — do not edit directly. Synced from forwineries-theme-core.
// Edit the source there and re-run tools/sync-core.sh.
namespace ForWineries\Core\ContentSettings;

use ForWineries\Core\Config;
use ForWineries\Core\SettingsPage;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Generic field-type engine for a "Pages & Content" settings tab — makes
 * copy hard-coded into a theme's template files editable from wp-admin
 * without ACF or any other plugin, built entirely on WordPress core's
 * own Settings API. Ported from Heronrest's inc/content-settings.php,
 * the most mature of the 5 originals (it alone has the 'image'/'url'
 * field types and the sidebar/search/collapsible-group admin UI the
 * other 4 later copied partially).
 *
 * Split deliberately: this class is the GENERIC engine only (field
 * types, sanitizers, rendering, the repeater/image-picker admin UI). The
 * actual field SCHEMA — Heronrest's ~23 groups of hero/story/stats/...
 * copy, each with its own brand-voice default text — is 100% per-theme
 * content, not shared, so it stays in each product repo and is passed
 * in via $config->get('content_fields'), e.g.:
 *
 *   $content_fields = require get_stylesheet_directory() . '/inc/content-fields.php';
 *   ForWineries\Core\Boot::init( array( ..., 'content_fields' => $content_fields ) );
 *
 * Field shapes (unchanged from the original):
 *   Scalar:   'key' => array( $label, $type, $default )
 *   Repeater: 'key' => array( $label, 'repeater', $subfields, $default_rows )
 *     $subfields is itself   sub_key => array( $sub_label, $type )
 *     $default_rows is an array of  array( sub_key => value, ... )  rows
 * $type: 'text' | 'textarea' | 'url' | 'number' | 'image'
 *
 * Option keys use $config->option_key('content_' . $field_key) — the
 * exact same wp_options naming every original theme's
 * {PREFIX}_content_{key} already used, so a Phase-1-migrated site keeps
 * reading its already-saved content instead of resetting to blank.
 *
 * Group category (for the sidebar ToC) is derived from the key-naming
 * convention every original theme already used consistently — a group
 * key ending "_page" is a marketing/legal page, "_global" applies
 * sitewide, everything else is a homepage section — rather than a
 * second hand-maintained category list that could drift out of sync.
 */
class Framework {

	private static function groups( Config $config ): array {
		return $config->get( 'content_fields', array() );
	}

	/**
	 * Read one scalar field's saved value, falling back to its own
	 * registered default if it's empty/unsaved. $fallback overrides even
	 * the registered default when given. Always safe to call, even for a
	 * key that isn't registered in $config->get('content_fields') at
	 * all. For repeater fields, use field_repeater() instead.
	 */
	public static function field( Config $config, string $key, string $fallback = '' ): string {
		$value = get_option( $config->option_key( 'content_' . $key ), null );
		if ( $value !== null && $value !== '' ) {
			return $value;
		}
		if ( $fallback !== '' ) {
			return $fallback;
		}
		foreach ( self::groups( $config ) as $group ) {
			if ( isset( $group['fields'][ $key ] ) ) {
				return $group['fields'][ $key ][2];
			}
		}
		return '';
	}

	/**
	 * Read a repeater field's saved rows as a plain array. Falls back to
	 * the field's own default rows if nothing's been saved yet (or
	 * everything saved was empty), so a themed section always shows
	 * something sensible out of the box rather than an empty list.
	 */
	public static function field_repeater( Config $config, string $key ): array {
		$raw = get_option( $config->option_key( 'content_' . $key ), null );
		if ( $raw !== null && $raw !== '' && $raw !== '[]' ) {
			$decoded = json_decode( $raw, true );
			if ( is_array( $decoded ) && ! empty( $decoded ) ) {
				return $decoded;
			}
		}
		foreach ( self::groups( $config ) as $group ) {
			if ( isset( $group['fields'][ $key ][3] ) ) {
				return $group['fields'][ $key ][3];
			}
		}
		return array();
	}

	private static function sanitize_value( string $type, $value ): string {
		switch ( $type ) {
			case 'textarea':
				return sanitize_textarea_field( $value );
			case 'url':
			case 'image':
				return esc_url_raw( trim( (string) $value ) );
			default:
				return sanitize_text_field( $value );
		}
	}

	/**
	 * Sanitizes one repeater field's raw submitted value (an array of
	 * rows, each a sub_key => raw_value array) down to a JSON string for
	 * storage. Drops any row that ends up entirely empty (e.g. an "Add
	 * Row" click that was never filled in), so accidental blank rows
	 * don't pile up.
	 */
	private static function sanitize_repeater( $raw, array $subfields ): string {
		/**
		 * update_option() calls sanitize_option() up to TWICE on a
		 * setting's very first save ever: once with the real submitted
		 * value, then a second time via its own internal add_option()
		 * fallback (taken whenever the current value still equals the
		 * registered 'default' — true for a brand-new '[]' repeater) —
		 * and that second pass receives THIS function's own JSON-string
		 * output as $raw, not the original submitted array. Decoding a
		 * string input first makes this function idempotent against that
		 * double pass. Without this, a repeater field's very first save
		 * silently collapsed to empty — confirmed via a real WP bootstrap
		 * smoke test, not assumed; see docs/LESSONS.md.
		 */
		if ( is_string( $raw ) ) {
			$decoded = json_decode( $raw, true );
			$raw     = is_array( $decoded ) ? $decoded : array();
		}
		if ( ! is_array( $raw ) ) {
			return wp_json_encode( array() );
		}
		$clean_rows = array();
		foreach ( $raw as $row ) {
			if ( ! is_array( $row ) ) continue;
			$clean_row = array();
			foreach ( $subfields as $sub_key => $sub_field ) {
				$sub_type              = isset( $sub_field[1] ) ? $sub_field[1] : 'text';
				$val                   = isset( $row[ $sub_key ] ) ? $row[ $sub_key ] : '';
				$clean_row[ $sub_key ] = self::sanitize_value( $sub_type, $val );
			}
			if ( count( array_filter( $clean_row ) ) > 0 ) {
				$clean_rows[] = $clean_row;
			}
		}
		return wp_json_encode( $clean_rows );
	}

	public static function register( Config $config ): void {
		$settings_group = $config->option_key( 'content_settings' );

		add_action( 'admin_init', function () use ( $config, $settings_group ) {
			foreach ( self::groups( $config ) as $group ) {
				foreach ( $group['fields'] as $key => $field ) {
					$type       = $field[1];
					$option_key = $config->option_key( 'content_' . $key );

					if ( $type === 'repeater' ) {
						$subfields = $field[2];
						register_setting( $settings_group, $option_key, array(
							'type'              => 'string',
							'sanitize_callback' => function ( $raw ) use ( $subfields ) {
								return self::sanitize_repeater( $raw, $subfields );
							},
							'default' => '[]',
						) );
						continue;
					}

					register_setting( $settings_group, $option_key, array(
						'type'              => 'string',
						'sanitize_callback' => function ( $raw ) use ( $type ) {
							return self::sanitize_value( $type, $raw );
						},
						'default' => '',
					) );
				}
			}
		} );

		add_action( 'admin_enqueue_scripts', function ( $hook ) use ( $config ) {
			if ( $hook !== 'toplevel_page_' . $config->slug() . '-settings' ) return;
			// Media modal machinery (wp.media) for the 'image' field
			// type's "Choose Image" buttons — without this, the picker JS
			// silently degrades to the plain URL input.
			wp_enqueue_media();
			wp_enqueue_script(
				'fw-repeater-admin',
				get_stylesheet_directory_uri() . '/core/assets/js/repeater-admin.js',
				array( 'jquery' ), // wp.media's frame API depends on jQuery/Backbone being present.
				Config::asset_version( '/core/assets/js/repeater-admin.js' ),
				true
			);
		} );

		SettingsPage::add_tab( 'content', __( 'Pages & Content', $config->text_domain() ), function () use ( $config ) {
			self::render_tab_content( $config );
		}, 20 );
	}

	private static function group_category( string $group_id ): string {
		if ( substr( $group_id, -5 ) === '_page' ) return 'page';
		if ( substr( $group_id, -7 ) === '_global' ) return 'global';
		return 'homepage';
	}

	private static function render_tab_content( Config $config ): void {
		if ( ! current_user_can( 'edit_theme_options' ) ) return;

		$groups     = self::groups( $config );
		$categories = array(
			'homepage' => __( 'Homepage Sections', $config->text_domain() ),
			'page'     => __( 'Marketing & Legal Pages', $config->text_domain() ),
			'global'   => __( 'Site-Wide', $config->text_domain() ),
		);
		?>
			<?php if ( isset( $_GET[ $config->slug() . '_pages_recreated' ] ) ) : ?>
				<div class="notice notice-success"><p><?php esc_html_e( 'Marketing/legal pages recreated, and default menus recreated or synced (anything already there, including your own titles, ordering, and customizations, was left exactly as you had it).', $config->text_domain() ); ?></p></div>
			<?php endif; ?>
			<p class="description">
				<?php printf( esc_html__( 'Text used across the theme\'s built-in pages that isn\'t editable through Elementor. Leave any field blank to use its default %s copy — nothing here can break a page. The moment you build one of these pages yourself in Elementor and it becomes an Elementor page, that page stops reading from here and uses your Elementor content instead.', $config->text_domain() ), esc_html( $config->brand_name() ) ); ?>
			</p>
			<p>
				<a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=fw_recreate_pages' ), 'fw_recreate_pages' ) ); ?>">
					<?php esc_html_e( 'Recreate missing pages & sync default menus (Estate, Team, Visit, Contact, legal pages, Primary/Footer menus)', $config->text_domain() ); ?>
				</a>
				<br><span class="description"><?php esc_html_e( 'A location with no menu yet gets a full default menu created. A location that already has one keeps it exactly as-is, aside from adding any page this theme ships that isn\'t in it yet — nothing already there is ever reordered, renamed, or removed.', $config->text_domain() ); ?></span>
			</p>

			<div class="fw-settings-layout">
				<aside class="fw-settings-sidebar">
					<input type="search" id="fw-settings-filter" class="fw-settings-filter" placeholder="<?php esc_attr_e( 'Search settings…', $config->text_domain() ); ?>" aria-label="<?php esc_attr_e( 'Search settings', $config->text_domain() ); ?>">
					<p class="fw-settings-toc-actions">
						<button type="button" class="button-link" data-fw-expand-all><?php esc_html_e( 'Expand all', $config->text_domain() ); ?></button>
						<button type="button" class="button-link" data-fw-collapse-all><?php esc_html_e( 'Collapse all', $config->text_domain() ); ?></button>
					</p>
					<nav class="fw-settings-toc" aria-label="<?php esc_attr_e( 'Settings sections', $config->text_domain() ); ?>">
						<?php foreach ( $categories as $cat_id => $cat_label ) : ?>
							<p class="fw-settings-toc-heading"><?php echo esc_html( $cat_label ); ?></p>
							<ul>
								<?php foreach ( $groups as $group_id => $group ) :
									if ( self::group_category( $group_id ) !== $cat_id ) continue;
									?>
									<li><a href="#fw-group-<?php echo esc_attr( $group_id ); ?>" data-fw-group-link="<?php echo esc_attr( $group_id ); ?>"><?php echo esc_html( $group['title'] ); ?></a></li>
								<?php endforeach; ?>
							</ul>
						<?php endforeach; ?>
					</nav>
				</aside>

				<div class="fw-settings-main">
					<form method="post" action="options.php" id="fw-content-settings-form">
						<?php settings_fields( $config->option_key( 'content_settings' ) ); ?>
						<input type="hidden" name="_wp_http_referer" value="<?php echo esc_url( admin_url( 'admin.php?page=' . $config->slug() . '-settings&tab=content' ) ); ?>">

						<?php foreach ( $categories as $cat_id => $cat_label ) : ?>
							<?php
							$cat_groups = array_filter( $groups, function ( $group_id ) use ( $cat_id ) {
								return self::group_category( $group_id ) === $cat_id;
							}, ARRAY_FILTER_USE_KEY );
							if ( empty( $cat_groups ) ) continue;
							?>
							<h2 class="fw-settings-category-heading"><?php echo esc_html( $cat_label ); ?></h2>
							<?php foreach ( $cat_groups as $group_id => $group ) : ?>
								<details class="fw-settings-group" id="fw-group-<?php echo esc_attr( $group_id ); ?>" data-fw-group>
									<summary><?php echo esc_html( $group['title'] ); ?></summary>
									<div class="fw-settings-group-body">
										<?php
										$simple_fields   = array();
										$repeater_fields = array();
										foreach ( $group['fields'] as $key => $field ) {
											if ( $field[1] === 'repeater' ) {
												$repeater_fields[ $key ] = $field;
											} else {
												$simple_fields[ $key ] = $field;
											}
										}
										?>
										<?php if ( ! empty( $simple_fields ) ) : ?>
											<table class="form-table" role="presentation">
												<?php foreach ( $simple_fields as $key => $field ) :
													list( $label, $type, $default ) = $field;
													$option_name = $config->option_key( 'content_' . $key );
													$value       = get_option( $option_name, '' );
													?>
													<tr>
														<th><label for="<?php echo esc_attr( $option_name ); ?>"><?php echo esc_html( $label ); ?></label></th>
														<td><?php self::render_field_input( $config->text_domain(), $type, $option_name, $option_name, $value, $default ); ?></td>
													</tr>
												<?php endforeach; ?>
											</table>
										<?php endif; ?>
										<?php foreach ( $repeater_fields as $key => $field ) : self::render_repeater_field( $config, $key, $field ); endforeach; ?>
									</div>
								</details>
							<?php endforeach; ?>
						<?php endforeach; ?>

						<div class="fw-settings-save-bar">
							<?php submit_button( null, 'primary', 'submit', false ); ?>
							<span class="fw-settings-save-bar-hint"><?php esc_html_e( 'Saves everything on this tab, not just the section you were last in.', $config->text_domain() ); ?></span>
						</div>
					</form>
				</div>
			</div>
		<?php
	}

	/**
	 * Renders one repeater field's admin UI: existing saved rows (or the
	 * field's default rows, if nothing's saved yet), each removable,
	 * plus an "Add row" button that clones a hidden <template> via
	 * core/assets/js/repeater-admin.js. Every input is named
	 * "{option_name}[ROW_INDEX][sub_key]" — WordPress's Settings API
	 * hands the whole nested array straight to the sanitize_callback
	 * registered above, no extra plumbing needed.
	 */
	private static function render_repeater_field( Config $config, string $key, array $field ): void {
		list( $label, $type, $subfields, $default_rows ) = array_pad( $field, 4, array() );
		$option_name = $config->option_key( 'content_' . $key );
		$raw         = get_option( $option_name, '' );
		$rows        = array();
		if ( $raw !== '' && $raw !== '[]' ) {
			$decoded = json_decode( $raw, true );
			if ( is_array( $decoded ) ) $rows = $decoded;
		}
		if ( empty( $rows ) ) $rows = $default_rows;
		?>
		<div class="fw-repeater-field" data-repeater-name="<?php echo esc_attr( $option_name ); ?>">
			<h3><?php echo esc_html( $label ); ?></h3>
			<div class="fw-repeater-rows">
				<?php foreach ( $rows as $i => $row ) : ?>
					<?php echo self::repeater_row_markup( $config, $option_name, $i, $subfields, $row ); ?>
				<?php endforeach; ?>
			</div>
			<p><button type="button" class="button fw-repeater-add"><?php esc_html_e( '+ Add row', $config->text_domain() ); ?></button></p>
			<template class="fw-repeater-template"><?php echo self::repeater_row_markup( $config, $option_name, '__INDEX__', $subfields, array() ); ?></template>
		</div>
		<?php
	}

	/**
	 * Renders one input of any field type — the single renderer both the
	 * scalar form-table rows and the repeater rows go through, so a type
	 * behaves identically in both contexts. $id may be '' (repeater
	 * subfields have no per-input <label for>); $default becomes the
	 * placeholder so an empty field always shows what copy the site will
	 * actually fall back to.
	 */
	private static function render_field_input( string $text_domain, string $type, string $name, string $id = '', string $value = '', string $default = '' ): void {
		$id_attr = $id !== '' ? ' id="' . esc_attr( $id ) . '"' : '';
		if ( $type === 'textarea' ) {
			echo '<textarea' . $id_attr . ' name="' . esc_attr( $name ) . '" rows="3" class="large-text" placeholder="' . esc_attr( $default ) . '">' . esc_textarea( $value ) . '</textarea>';
			return;
		}
		if ( $type === 'url' ) {
			echo '<input type="url"' . $id_attr . ' name="' . esc_attr( $name ) . '" class="regular-text code" value="' . esc_attr( $value ) . '" placeholder="' . esc_attr( $default !== '' ? $default : 'https://…' ) . '">';
			return;
		}
		if ( $type === 'number' ) {
			echo '<input type="number" min="1" step="1"' . $id_attr . ' name="' . esc_attr( $name ) . '" class="small-text" value="' . esc_attr( $value ) . '" placeholder="' . esc_attr( $default ) . '">';
			return;
		}
		if ( $type === 'image' ) {
			self::render_image_input( $text_domain, $name, $id, $value );
			return;
		}
		echo '<input type="text"' . $id_attr . ' name="' . esc_attr( $name ) . '" class="regular-text" value="' . esc_attr( $value ) . '" placeholder="' . esc_attr( $default ) . '">';
	}

	/**
	 * The 'image' field type's control: thumbnail preview + URL input +
	 * Media Library picker button. The value stays a plain URL string,
	 * so a URL pasted from anywhere works exactly the same as one picked
	 * from the library. All behavior (opening wp.media, syncing the
	 * preview) is delegated to core/assets/js/repeater-admin.js, so this
	 * same markup works inside cloned repeater rows too.
	 */
	private static function render_image_input( string $text_domain, string $name, string $id = '', string $value = '' ): void {
		$id_attr = $id !== '' ? ' id="' . esc_attr( $id ) . '"' : '';
		?>
		<div class="fw-image-field">
			<div class="fw-image-field-preview">
				<img src="<?php echo esc_url( $value ); ?>" alt=""<?php echo $value === '' ? ' hidden' : ''; ?>>
				<span class="dashicons dashicons-format-image"<?php echo $value !== '' ? ' hidden' : ''; ?>></span>
			</div>
			<div class="fw-image-field-controls">
				<input type="url"<?php echo $id_attr; ?> name="<?php echo esc_attr( $name ); ?>" class="regular-text code fw-image-field-url" value="<?php echo esc_attr( $value ); ?>" placeholder="https://…">
				<span class="fw-image-field-buttons">
					<button type="button" class="button fw-image-field-pick"><?php esc_html_e( 'Choose Image', $text_domain ); ?></button>
					<button type="button" class="button-link-delete fw-image-field-clear"<?php echo $value === '' ? ' hidden' : ''; ?>><?php esc_html_e( 'Clear', $text_domain ); ?></button>
				</span>
			</div>
		</div>
		<?php
	}

	private static function repeater_row_markup( Config $config, string $option_name, $index, array $subfields, array $row ): string {
		ob_start();
		?>
		<div class="fw-repeater-row">
			<div class="fw-repeater-row-toolbar">
				<span class="fw-repeater-row-grip dashicons dashicons-menu" aria-hidden="true"></span>
				<span class="fw-repeater-row-toolbar-buttons">
					<button type="button" class="button-link fw-repeater-move-up" title="<?php esc_attr_e( 'Move up', $config->text_domain() ); ?>"><span class="dashicons dashicons-arrow-up-alt2"></span><span class="screen-reader-text"><?php esc_html_e( 'Move up', $config->text_domain() ); ?></span></button>
					<button type="button" class="button-link fw-repeater-move-down" title="<?php esc_attr_e( 'Move down', $config->text_domain() ); ?>"><span class="dashicons dashicons-arrow-down-alt2"></span><span class="screen-reader-text"><?php esc_html_e( 'Move down', $config->text_domain() ); ?></span></button>
					<button type="button" class="button-link-delete fw-repeater-remove"><?php esc_html_e( 'Remove', $config->text_domain() ); ?></button>
				</span>
			</div>
			<?php foreach ( $subfields as $sub_key => $sub_field ) :
				list( $sub_label, $sub_type ) = $sub_field;
				$field_name = $option_name . '[' . $index . '][' . $sub_key . ']';
				$val        = isset( $row[ $sub_key ] ) ? $row[ $sub_key ] : '';
				?>
				<div class="fw-repeater-subfield">
					<label><?php echo esc_html( $sub_label ); ?></label>
					<?php self::render_field_input( $config->text_domain(), $sub_type, $field_name, '', $val ); ?>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
		return ob_get_clean();
	}
}
