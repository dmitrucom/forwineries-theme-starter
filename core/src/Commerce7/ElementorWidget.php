<?php
// GENERATED FILE — do not edit directly. Synced from forwineries-theme-core.
// Edit the source there and re-run tools/sync-core.sh.
namespace ForWineries\Core\Commerce7;

use ForWineries\Core\Config;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Generic Elementor widget class — one class, instantiated once per
 * entry in Blocks::block_definitions() (ElementorWidgets::register()),
 * each carrying its own def_key AND the theme's Config via the widget
 * constructor's $args, so a single class serves every Commerce7 widget
 * without one near-identical file per widget. Elementor keeps each
 * registered instance alive in its own widget-type registry for the
 * life of the request rather than reconstructing fresh objects from
 * saved page data per render, so baking def_key/config in at
 * registration time (not trying to recover them later) is the correct,
 * original pattern here — not a shortcut.
 *
 * get_name()'s returned string is stored verbatim inside any page's
 * saved Elementor data (_elementor_data postmeta) the moment a widget
 * instance is placed — same migration-risk category as
 * Config::elementor_id(), which is exactly what this uses.
 */
class ElementorWidget extends \Elementor\Widget_Base {

	private Config $fw_config;
	private string $fw_def_key = '';
	private array $fw_def = array();

	public function __construct( $data = array(), $args = null ) {
		if ( is_array( $args ) && isset( $args['fw_config'] ) ) {
			$this->fw_config = $args['fw_config'];
		}
		if ( is_array( $args ) && isset( $args['fw_def_key'] ) ) {
			$this->fw_def_key = $args['fw_def_key'];
		}
		$definitions  = isset( $this->fw_config ) ? Blocks::block_definitions( $this->fw_config ) : array();
		$this->fw_def = isset( $definitions[ $this->fw_def_key ] ) ? $definitions[ $this->fw_def_key ] : array(
			'title'  => __( 'Commerce7 Widget' ),
			'fields' => array(),
			'render' => '__return_empty_string',
		);
		parent::__construct( $data, $args );
	}

	public function get_name() {
		return isset( $this->fw_config ) ? $this->fw_config->elementor_id( $this->fw_def_key ) : 'fw-c7-widget';
	}

	public function get_title() {
		return $this->fw_def['title'];
	}

	public function get_icon() {
		return 'eicon-cart-medium';
	}

	public function get_categories() {
		return array( isset( $this->fw_config ) ? Blocks::category_slug( $this->fw_config ) : 'commerce7' );
	}

	public function get_keywords() {
		return array( 'commerce7', 'wine', 'shop', 'cart' );
	}

	protected function register_controls() {
		$td = isset( $this->fw_config ) ? $this->fw_config->text_domain() : 'default';

		if ( empty( $this->fw_def['fields'] ) ) {
			$this->start_controls_section( 'fw_info', array(
				'label' => __( 'About', $td ),
			) );
			$this->add_control( 'fw_info_text', array(
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw'  => isset( $this->fw_def['description'] ) ? esc_html( $this->fw_def['description'] ) : '',
			) );
			$this->end_controls_section();
		} else {
			$this->start_controls_section( 'fw_content', array(
				'label' => __( 'Content', $td ),
			) );
			foreach ( $this->fw_def['fields'] as $key => $field ) {
				$this->add_control( $key, array(
					'label'   => $field['label'],
					'type'    => $field['type'] === 'number' ? \Elementor\Controls_Manager::NUMBER : \Elementor\Controls_Manager::TEXT,
					'default' => $field['default'],
				) );
			}
			$this->end_controls_section();
		}

		$this->register_style_controls();
	}

	/**
	 * Style-tab controls, declared per-widget in
	 * Blocks::block_definitions() as 'style_fields' (+
	 * 'style_root_selector'). Every control targets the SAME root
	 * selector and only ever sets a CSS custom property there — never a
	 * direct property — because custom properties inherit to
	 * descendants for free, so one selector covers a whole card's
	 * styling without a control-per-descendant. commerce7-overrides.css
	 * defines the real property with `var(--the-prop, <default>)`, so an
	 * untouched control (no value set) falls back to the core design
	 * rather than emitting an empty/zero override — this also means the
	 * same class used OUTSIDE Elementor (e.g. the Wines Catalog page,
	 * which mounts via a shortcode, not this widget) is completely
	 * unaffected by a specific widget instance's overrides.
	 */
	private function register_style_controls() {
		if ( empty( $this->fw_def['style_fields'] ) || empty( $this->fw_def['style_root_selector'] ) ) {
			return;
		}

		$td   = isset( $this->fw_config ) ? $this->fw_config->text_domain() : 'default';
		$root = '{{WRAPPER}} ' . $this->fw_def['style_root_selector'];

		$this->start_controls_section( 'fw_style', array(
			'label' => __( 'Card Style', $td ),
			'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
		) );

		foreach ( $this->fw_def['style_fields'] as $key => $f ) {
			$css_var = $f['css_var'];

			if ( $f['type'] === 'color' ) {
				$this->add_control( $key, array(
					'type'      => \Elementor\Controls_Manager::COLOR,
					'label'     => $f['label'],
					'selectors' => array( $root => $css_var . ': {{VALUE}};' ),
				) );
			} elseif ( $f['type'] === 'slider' ) {
				$this->add_control( $key, array(
					'type'       => \Elementor\Controls_Manager::SLIDER,
					'label'      => $f['label'],
					'size_units' => array( 'px' ),
					'range'      => array(
						'px' => array( 'min' => $f['min'], 'max' => $f['max'], 'step' => 1 ),
					),
					'selectors'  => array( $root => $css_var . ': {{SIZE}}{{UNIT}};' ),
				) );
			} elseif ( $f['type'] === 'select' ) {
				// No explicit 'default' — Elementor selects the first
				// option, which is deliberately written as the real CSS
				// default value (not a blank placeholder) for exactly
				// this reason.
				$this->add_control( $key, array(
					'type'      => \Elementor\Controls_Manager::SELECT,
					'label'     => $f['label'],
					'options'   => $f['options'],
					'selectors' => array( $root => $css_var . ': {{VALUE}};' ),
				) );
			}
		}

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$atts     = array();
		foreach ( $this->fw_def['fields'] as $key => $field ) {
			$atts[ $key ] = isset( $settings[ $key ] ) && $settings[ $key ] !== '' ? $settings[ $key ] : $field['default'];
		}
		echo call_user_func( $this->fw_def['render'], $atts );
	}
}
