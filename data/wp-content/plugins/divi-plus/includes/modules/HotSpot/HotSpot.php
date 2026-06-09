<?php
/**
 * @author      Elicus <hello@elicus.com>
 * @link        https://www.elicus.com/
 * @copyright   2023 Elicus Technologies Private Limited
 * @version     1.9.15
 */
class DIPL_HotSpot extends ET_Builder_Module {

	public $slug       = 'dipl_hotspot';
	public $child_slug = 'dipl_hotspot_item';
	public $vb_support = 'on';

	protected $module_credits = array(
		'module_uri' => 'https://diviextended.com/product/divi-plus/',
		'author'     => 'Elicus',
		'author_uri' => 'https://elicus.com/',
	);

	public function init() {
		$this->name = esc_html__( 'DP Hotspot', 'divi-plus' );
		$this->main_css_element = '%%order_class%%';
	}

	public function get_settings_modal_toggles() {
		return array(
			'general'  => array(
				'toggles' => array(
					'main_content' => esc_html__( 'Hotspot Content', 'divi-plus' ),
				),
			),
			'advanced' => array(
				'toggles' => array(
					'hostspot_settings'               => array(
						'title'    => esc_html__( 'Hotspot Setting', 'divi-plus' ),
						'priority' => 1,
					),
					'global_marker_styling'           => array(
						'title'    => esc_html__( 'Global Marker Styling', 'divi-plus' ),
						'priority' => 2,
					),
					'global_marker_icon_styling'      => array(
						'title'    => esc_html__( 'Global Marker Icon Styling', 'divi-plus' ),
						'priority' => 3,
					),
					'global_marker_image_styling'     => array(
						'title'    => esc_html__( 'Global Marker Image Styling', 'divi-plus' ),
						'priority' => 3,
					),
					'global_marker_text_settings'     => array(
						'title'    => esc_html__( 'Global Marker Text', 'divi-plus' ),
						'priority' => 4,
					),
					'global_tooltip_styling'          => array(
						'title'             => esc_html__( 'Global Tooltip Styling', 'divi-plus' ),
						'priority'          => 5,
						'tabbed_subtoggles' => true,
						'sub_toggles'       => array(
							'global_tooltip_general_tab' => array(
								'name' => 'General',
							),
							'global_tooltip_background_tab' => array(
								'name' => 'Background',
							),
						),
					),
					'global_tooltip_heading_settings' => array(
						'title'             => esc_html__( 'Tooltip Heading', 'divi-plus' ),
						'priority'          => 6,
						'tabbed_subtoggles' => true,
						'sub_toggles'       => array(
							'h1' => array(
								'name' => 'H1',
								'icon' => 'text-h1',
							),
							'h2' => array(
								'name' => 'H2',
								'icon' => 'text-h2',
							),
							'h3' => array(
								'name' => 'H3',
								'icon' => 'text-h3',
							),
							'h4' => array(
								'name' => 'H4',
								'icon' => 'text-h4',
							),
							'h5' => array(
								'name' => 'H5',
								'icon' => 'text-h5',
							),
							'h6' => array(
								'name' => 'H6',
								'icon' => 'text-h6',
							),
						),
					),
					'global_tooltip_text_settings'    => array(
						'title'             => esc_html__( 'Tooltip', 'divi-plus' ),
						'priority'          => 7,
						'tabbed_subtoggles' => true,
						'bb_icons_support'  => true,
						'sub_toggles'       => array(
							'p'     => array(
								'name' => 'P',
								'icon' => 'text-left',
							),
							'a'     => array(
								'name' => 'A',
								'icon' => 'text-link',
							),
							'ul'    => array(
								'name' => 'UL',
								'icon' => 'list',
							),
							'ol'    => array(
								'name' => 'OL',
								'icon' => 'numbered-list',
							),
							'quote' => array(
								'name' => 'QUOTE',
								'icon' => 'text-quote',
							),
						),
					),
					'image_settings'                  => array(
						'title'    => esc_html__( 'Image', 'divi-plus' ),
						'priority' => 8,
					),
				),
			),
		);
	}

	public function get_advanced_fields_config() {
		return array(
			'fonts'           => array(
				'global_tooltip_header'   => array(
					'label'          => esc_html__( 'Heading', 'divi-plus' ),
					'font_size'      => array(
						'default'        => '30px',
						'range_settings' => array(
							'min'  => '1',
							'max'  => '100',
							'step' => '1',
						),
						'validate_unit'  => true,
					),
					'line_height'    => array(
						'default'        => '1em',
						'range_settings' => array(
							'min'  => '0.1',
							'max'  => '10',
							'step' => '0.1',
						),
					),
					'letter_spacing' => array(
						'default'        => '0px',
						'range_settings' => array(
							'min'  => '0',
							'max'  => '10',
							'step' => '1',
						),
						'validate_unit'  => true,
					),
					'css'            => array(
						'main' => '%%order_class%%_0.tooltipster-sidetip .tooltipster-content h1',
						'important' => 'all',
					),
					'toggle_slug'    => 'global_tooltip_heading_settings',
					'sub_toggle'     => 'h1',
				),
				'global_tooltip_header_2' => array(
					'label'          => esc_html__( 'Heading 2', 'divi-plus' ),
					'font_size'      => array(
						'default'        => '26px',
						'range_settings' => array(
							'min'  => '1',
							'max'  => '100',
							'step' => '1',
						),
						'validate_unit'  => true,
					),
					'line_height'    => array(
						'default'        => '1em',
						'range_settings' => array(
							'min'  => '0.1',
							'max'  => '10',
							'step' => '0.1',
						),
					),
					'letter_spacing' => array(
						'default'        => '0px',
						'range_settings' => array(
							'min'  => '0',
							'max'  => '10',
							'step' => '1',
						),
						'validate_unit'  => true,
					),
					'css'            => array(
						'main' => '%%order_class%%_0.tooltipster-sidetip .tooltipster-content h2',
						'important' => 'all',
					),
					'toggle_slug'    => 'global_tooltip_heading_settings',
					'sub_toggle'     => 'h2',
				),
				'global_tooltip_header_3' => array(
					'label'          => esc_html__( 'Heading 3', 'divi-plus' ),
					'font_size'      => array(
						'default'        => '22px',
						'range_settings' => array(
							'min'  => '1',
							'max'  => '100',
							'step' => '1',
						),
						'validate_unit'  => true,
					),
					'line_height'    => array(
						'default'        => '1em',
						'range_settings' => array(
							'min'  => '0.1',
							'max'  => '10',
							'step' => '0.1',
						),
					),
					'letter_spacing' => array(
						'default'        => '0px',
						'range_settings' => array(
							'min'  => '0',
							'max'  => '10',
							'step' => '1',
						),
						'validate_unit'  => true,
					),
					'css'            => array(
						'main' => '%%order_class%%_0.tooltipster-sidetip .tooltipster-content h3',
						'important' => 'all',
					),
					'toggle_slug'    => 'global_tooltip_heading_settings',
					'sub_toggle'     => 'h3',
				),
				'global_tooltip_header_4' => array(
					'label'          => esc_html__( 'Heading 4', 'divi-plus' ),
					'font_size'      => array(
						'default'        => '18px',
						'range_settings' => array(
							'min'  => '1',
							'max'  => '100',
							'step' => '1',
						),
						'validate_unit'  => true,
					),
					'line_height'    => array(
						'default'        => '1.5em',
						'range_settings' => array(
							'min'  => '0.1',
							'max'  => '10',
							'step' => '0.1',
						),
					),
					'letter_spacing' => array(
						'default'        => '0px',
						'range_settings' => array(
							'min'  => '0',
							'max'  => '10',
							'step' => '1',
						),
						'validate_unit'  => true,
					),
					'css'            => array(
						'main' => '%%order_class%%_0.tooltipster-sidetip .tooltipster-content h4',
						'important' => 'all',
					),
					'toggle_slug'    => 'global_tooltip_heading_settings',
					'sub_toggle'     => 'h4',
				),
				'global_tooltip_header_5' => array(
					'label'          => esc_html__( 'Heading 5', 'divi-plus' ),
					'font_size'      => array(
						'default'        => '16px',
						'range_settings' => array(
							'min'  => '1',
							'max'  => '100',
							'step' => '1',
						),
						'validate_unit'  => true,
					),
					'line_height'    => array(
						'default'        => '1em',
						'range_settings' => array(
							'min'  => '0.1',
							'max'  => '10',
							'step' => '0.1',
						),
					),
					'letter_spacing' => array(
						'default'        => '0px',
						'range_settings' => array(
							'min'  => '0',
							'max'  => '10',
							'step' => '1',
						),
						'validate_unit'  => true,
					),
					'css'            => array(
						'main' => '%%order_class%%_0.tooltipster-sidetip .tooltipster-content h5',
						'important' => 'all',
					),
					'toggle_slug'    => 'global_tooltip_heading_settings',
					'sub_toggle'     => 'h5',
				),
				'global_tooltip_header_6' => array(
					'label'          => esc_html__( 'Heading 6', 'divi-plus' ),
					'font_size'      => array(
						'default'        => '14px',
						'range_settings' => array(
							'min'  => '1',
							'max'  => '100',
							'step' => '1',
						),
						'validate_unit'  => true,
					),
					'line_height'    => array(
						'default'        => '1em',
						'range_settings' => array(
							'min'  => '0.1',
							'max'  => '10',
							'step' => '0.1',
						),
					),
					'letter_spacing' => array(
						'default'        => '0px',
						'range_settings' => array(
							'min'  => '0',
							'max'  => '10',
							'step' => '1',
						),
						'validate_unit'  => true,
					),
					'css'            => array(
						'main' => '%%order_class%%_0.tooltipster-sidetip .tooltipster-content h6',
						'important' => 'all',
					),
					'toggle_slug'    => 'global_tooltip_heading_settings',
					'sub_toggle'     => 'h6',
				),
				'global_tooltip_text'     => array(
					'label'           => esc_html__( 'Text', 'divi-plus' ),
					'font_size'       => array(
						'default'        => '18px',
						'range_settings' => array(
							'min'  => '1',
							'max'  => '100',
							'step' => '1',
						),
						'validate_unit'  => true,
					),
					'line_height'     => array(
						'default'        => '1.5em',
						'range_settings' => array(
							'min'  => '0.1',
							'max'  => '10',
							'step' => '0.1',
						),
					),
					'letter_spacing'  => array(
						'default'        => '0px',
						'range_settings' => array(
							'min'  => '0',
							'max'  => '10',
							'step' => '1',
						),
						'validate_unit'  => true,
					),
					'css'             => array(
						'main' => '%%order_class%%_0.tooltipster-sidetip .tooltipster-content',
						'important' => 'all',
					),
					'toggle_slug'     => 'global_tooltip_text_settings',
					'sub_toggle'      => 'p',
					'hide_text_align' => true,
				),
				'global_tooltip_link'     => array(
					'label'          => esc_html__( 'Link', 'divi-plus' ),
					'font_size'      => array(
						'default'        => '18px',
						'range_settings' => array(
							'min'  => '1',
							'max'  => '100',
							'step' => '1',
						),
						'validate_unit'  => true,
					),
					'line_height'    => array(
						'default'        => '1.5em',
						'range_settings' => array(
							'min'  => '0.1',
							'max'  => '10',
							'step' => '0.1',
						),
					),
					'letter_spacing' => array(
						'default'        => '0px',
						'range_settings' => array(
							'min'  => '0',
							'max'  => '10',
							'step' => '1',
						),
						'validate_unit'  => true,
					),
					'css'            => array(
						'main' => '%%order_class%%_0.tooltipster-sidetip .tooltipster-content a',
						'important' => 'all',
					),
					'toggle_slug'    => 'global_tooltip_text_settings',
					'sub_toggle'     => 'a',
				),
				'global_tooltip_ul'       => array(
					'label'          => esc_html__( 'Unordered List', 'divi-plus' ),
					'font_size'      => array(
						'default'        => '18px',
						'range_settings' => array(
							'min'  => '1',
							'max'  => '100',
							'step' => '1',
						),
						'validate_unit'  => true,
					),
					'line_height'    => array(
						'default'        => '1.5em',
						'range_settings' => array(
							'min'  => '0.1',
							'max'  => '10',
							'step' => '0.1',
						),
					),
					'letter_spacing' => array(
						'default'        => '0px',
						'range_settings' => array(
							'min'  => '0',
							'max'  => '10',
							'step' => '1',
						),
						'validate_unit'  => true,
					),
					'css'            => array(
						'main' => '%%order_class%%_0.tooltipster-sidetip .tooltipster-content ul li',
						'important' => 'all',
					),
					'toggle_slug'    => 'global_tooltip_text_settings',
					'sub_toggle'     => 'ul',
				),
				'global_tooltip_ol'       => array(
					'label'          => esc_html__( 'Ordered List', 'divi-plus' ),
					'font_size'      => array(
						'default'        => '18px',
						'range_settings' => array(
							'min'  => '1',
							'max'  => '100',
							'step' => '1',
						),
						'validate_unit'  => true,
					),
					'line_height'    => array(
						'default'        => '1.5em',
						'range_settings' => array(
							'min'  => '0.1',
							'max'  => '10',
							'step' => '0.1',
						),
					),
					'letter_spacing' => array(
						'default'        => '0px',
						'range_settings' => array(
							'min'  => '0',
							'max'  => '10',
							'step' => '1',
						),
						'validate_unit'  => true,
					),
					'css'            => array(
						'main' => '%%order_class%%_0.tooltipster-sidetip .tooltipster-content ol li',
						'important' => 'all',
					),
					'toggle_slug'    => 'global_tooltip_text_settings',
					'sub_toggle'     => 'ol',
				),
				'global_tooltip_quote'    => array(
					'label'          => esc_html__( 'Blockquote', 'divi-plus' ),
					'font_size'      => array(
						'default'        => '18px',
						'range_settings' => array(
							'min'  => '1',
							'max'  => '100',
							'step' => '1',
						),
						'validate_unit'  => true,
					),
					'line_height'    => array(
						'default'        => '1.5em',
						'range_settings' => array(
							'min'  => '0.1',
							'max'  => '10',
							'step' => '0.1',
						),
					),
					'letter_spacing' => array(
						'default'        => '0px',
						'range_settings' => array(
							'min'  => '0',
							'max'  => '10',
							'step' => '1',
						),
						'validate_unit'  => true,
					),
					'css'            => array(
						'main' => '%%order_class%%_0.tooltipster-sidetip .tooltipster-content blockquote',
						'important' => 'all',
					),
					'toggle_slug'    => 'global_tooltip_text_settings',
					'sub_toggle'     => 'quote',
				),
				'global_marker_text'      => array(
					'label'          => esc_html__( 'Marker', 'divi-plus' ),
					'font_size'      => array(
						'default'        => '14px',
						'range_settings' => array(
							'min'  => '1',
							'max'  => '100',
							'step' => '1',
						),
						'validate_unit'  => true,
					),
					'line_height'    => array(
						'default'        => '1.7em',
						'range_settings' => array(
							'min'  => '0',
							'max'  => '5',
							'step' => '0.1',
						),
					),
					'letter_spacing' => array(
						'default'        => '0px',
						'range_settings' => array(
							'min'  => '0',
							'max'  => '10',
							'step' => '1',
						),
						'validate_unit'  => true,
					),
					'css'            => array(
						'main' => '%%order_class%% .dipl_hotspot_wrapper .dipl_text_marker',
						'important' => 'all',
					),
					'tab_slug'       => 'advanced',
					'toggle_slug'    => 'global_marker_text_settings',
				),
			),
			'borders'         => array(
				'global_marker_image_border' => array(
					'css'          => array(
						'main' => array(
							'border_radii'  => '%%order_class%% .dipl_image_marker img',
							'border_styles' => '%%order_class%% .dipl_image_marker img',
						),
						'important' => 'all',
					),
					'label_prefix' => esc_html__( 'Marker Image', 'divi-plus' ),
					'toggle_slug'  => 'global_marker_image_styling',
				),
			),
			'box_shadow'      => array(
				'default' => array(
					'css' => array(
						'main' => '%%order_class%%',
					),
				),
			),
			'margin_padding'  => array(
				'css' => array(
					'main'      => '%%order_class%%',
					'important' => 'all',
				),
			),
			'hotspot_padding' => array(
				'tooltip' => array(
					'label'          => 'Tooltip Padding',
					'margin_padding' => array(
						'css' => array(
							'main'      => '%%order_class%%_0.tooltipster-sidetip .tooltipster-box .tooltipster-content',
							'important' => 'all',
						),
					),
					'tab_slug'       => 'advanced',
					'toggle_slug'    => 'global_tooltip_styling',
				),
			),
			'text'            => false,
		);
	}

	public function get_fields() {

		$et_accent_color     = et_builder_accent_color();
		$dipl_hotspot_fields = array(
			'hotspot_image'                     => array(
				'label'              => esc_html__( 'Image', 'divi-plus' ),
				'type'               => 'upload',
				'option_category'    => 'basic_option',
				'upload_button_text' => esc_attr__( 'Upload an image', 'divi-plus' ),
				'choose_text'        => esc_attr__( 'Choose an Image', 'divi-plus' ),
				'update_text'        => esc_attr__( 'Set As Image', 'divi-plus' ),
				'dynamic_content' 	 => 'image',
				'tab_slug'           => 'general',
				'toggle_slug'        => 'main_content',
				'description'        => esc_html__( 'Here you can upload the image on which you want to display hotspots.', 'divi-plus' ),
			),
			'hotspot_image_alt'                             => array(
				'label'              => esc_html__( 'Hotspot Image Alt Text', 'divi-plus' ),
				'type'               => 'text',
				'option_category'    => 'basic_option',
				'dynamic_content' 	 => 'text',
				'tab_slug'           => 'general',
				'toggle_slug'        => 'main_content',
				'description'        => esc_html__( 'Here you can input the Hotspot Image alt text.', 'divi-plus' ),
			),
			'trigger_event'                     => array(
				'label'           => esc_html__( 'Show Tooltip On', 'divi-plus' ),
				'type'            => 'select',
				'option_category' => 'basic_option',
				'options'         => array(
					'hover' => esc_html__( 'Hover', 'divi-plus' ),
					'click' => esc_html__( 'Click', 'divi-plus' ),
				),
				'default'         => 'hover',
				'tab_slug'        => 'advanced',
				'toggle_slug'     => 'hostspot_settings',
				'description'     => esc_html__( 'Here you can select the option when to display toolpit content of the hotspot.', 'divi-plus' ),
			),
			'use_plusating_animation'           => array(
				'label'           => esc_html__( 'Use Pulse Animation', 'divi-plus' ),
				'type'            => 'yes_no_button',
				'option_category' => 'configuration',
				'options'         => array(
					'off' => esc_html__( 'No', 'divi-plus' ),
					'on'  => esc_html__( 'Yes', 'divi-plus' ),
				),
				'default'         => 'off',
				'tab_slug'        => 'advanced',
				'toggle_slug'     => 'global_marker_styling',
				'description'     => esc_html__( 'Here you can choose whether or not to display plusating animation for the hotspot marker.', 'divi-plus' ),
			),
			'marker_shape'                      => array(
				'label'           => esc_html__( 'Marker Shape', 'divi-plus' ),
				'type'            => 'select',
				'option_category' => 'configuration',
				'options'         => array(
					'none'       => esc_html__( 'None', 'divi-plus' ),
					'use_square' => esc_html__( 'Square', 'divi-plus' ),
					'use_circle' => esc_html__( 'Circle', 'divi-plus' ),
				),
				'default'         => 'none',
				'tab_slug'        => 'advanced',
				'toggle_slug'     => 'global_marker_styling',
				'description'     => esc_html__( 'Here you can select the shape for each hotspot marker displayed on the image.', 'divi-plus' ),
			),
			'marker_shape_color'                => array(
				'label'        => esc_html__( 'Marker Shape Background', 'divi-plus' ),
				'type'         => 'color-alpha',
				'custom_color' => true,
				'show_if_not'  => array(
					'marker_shape' => 'none',
				),
				'default'      => $et_accent_color,
				'hover'        => 'tabs',
				'tab_slug'     => 'advanced',
				'toggle_slug'  => 'global_marker_styling',
				'description'  => esc_html__( 'Here you can select the custom background color for the icon shape.', 'divi-plus' ),
			),
			'marker_use_shape_border'           => array(
				'label'           => esc_html__( 'Display Marker Shape Border', 'divi-plus' ),
				'type'            => 'yes_no_button',
				'option_category' => 'layout',
				'options'         => array(
					'off' => esc_html__( 'No', 'divi-plus' ),
					'on'  => esc_html__( 'Yes', 'divi-plus' ),
				),
				'show_if_not'     => array(
					'marker_shape' => 'none',
				),
				'default'         => 'off',
				'tab_slug'        => 'advanced',
				'toggle_slug'     => 'global_marker_styling',
				'description'     => esc_html__( 'Here you can choose whether or not to display border for the icon shape.', 'divi-plus' ),
			),
			'marker_shape_border_color'         => array(
				'label'        => esc_html__( 'Marker Shape Border Color', 'divi-plus' ),
				'type'         => 'color-alpha',
				'custom_color' => true,
				'show_if_not'  => array(
					'marker_shape' => 'none',
				),
				'show_if'      => array(
					'marker_use_shape_border' => 'on',
				),
				'default'      => $et_accent_color,
				'hover'        => 'tabs',
				'tab_slug'     => 'advanced',
				'toggle_slug'  => 'global_marker_styling',
				'description'  => esc_html__( 'Here you can select the custom border color for the icon shape.', 'divi-plus' ),
			),
			'marker_border_size'                => array(
				'label'           => esc_html__( 'Marker Shape Border Size', 'divi-plus' ),
				'type'            => 'range',
				'option_category' => 'layout',
				'range_settings'  => array(
					'min'  => '1',
					'max'  => '100',
					'step' => '1',
				),
				'show_if_not'     => array(
					'marker_shape' => 'none',
				),
				'show_if'         => array(
					'marker_use_shape_border' => 'on',
				),
				'mobile_options'  => true,
				'default'         => '2px',
				'tab_slug'        => 'advanced',
				'toggle_slug'     => 'global_marker_styling',
				'description'     => esc_html__( 'Move the slider, or input the value to increase or decrease border size of the icon shape.', 'divi-plus' ),
			),
			'marker_icon_color'                 => array(
				'label'       => esc_html__( 'Icon Color', 'divi-plus' ),
				'type'        => 'color-alpha',
				'default'     => '#fff',
				'tab_slug'    => 'advanced',
				'toggle_slug' => 'global_marker_icon_styling',
				'description' => esc_html__( 'Here you can select the custom color for each of the hotspot marker icon displayed on the image.', 'divi-plus' ),
			),
			'marker_icon_font_size'             => array(
				'label'           => esc_html__( 'Icon Font Size', 'divi-plus' ),
				'type'            => 'range',
				'option_category' => 'font_option',
				'range_settings'  => array(
					'min'  => '1',
					'max'  => '120',
					'step' => '1',
				),
				'mobile_options'  => true,
				'default'         => '32px',
				'tab_slug'        => 'advanced',
				'toggle_slug'     => 'global_marker_icon_styling',
				'description'     => esc_html__( 'Move the slider, or input the value to increase or decrease the icon size of each hotspot marker displayed on the image.', 'divi-plus' ),
			),
			'marker_image_size'                 => array(
				'label'           => esc_html__( 'Image Size', 'divi-plus' ),
				'type'            => 'range',
				'option_category' => 'layout',
				'range_settings'  => array(
					'min'  => '32',
					'max'  => '600',
					'step' => '1',
				),
				'mobile_options'  => true,
				'default'         => '32px',
				'tab_slug'        => 'advanced',
				'toggle_slug'     => 'global_marker_image_styling',
				'description'     => esc_html__( 'Move the slider, or input the value to increase or decrease the image size of each hotspot marker displayed on the image.', 'divi-plus' ),
			),
			'tooltip_entrance_animation'        => array(
				'label'           => esc_html__( 'Tooltip Entrance Animation', 'divi-plus' ),
				'type'            => 'select',
				'option_category' => 'configuration',
				'options'         => array(
					'fade'  => esc_html__( 'Fade', 'divi-plus' ),
					'grow'  => esc_html__( 'Grow', 'divi-plus' ),
					'swing' => esc_html__( 'Swing', 'divi-plus' ),
					'slide' => esc_html__( 'Slide', 'divi-plus' ),
					'fall'  => esc_html__( 'Fall', 'divi-plus' ),
				),
				'default'         => 'fade',
				'tab_slug'        => 'advanced',
				'toggle_slug'     => 'global_tooltip_styling',
				'sub_toggle'      => 'global_tooltip_general_tab',
				'description'     => esc_html__( 'Here you can select the animation effect to be used for the tooltip entrance.', 'divi-plus' ),
			),
			'global_animation_durartion'        => array(
				'label'          => esc_html__( 'Animation Duration', 'divi-plus' ),
				'type'           => 'range',
				'range_settings' => array(
					'min'  => '100',
					'max'  => '5000',
					'step' => '10',
				),
				'default'        => '350ms',
				'default_unit'   => 'ms',
				'tab_slug'       => 'advanced',
				'toggle_slug'    => 'global_tooltip_styling',
				'sub_toggle'     => 'global_tooltip_general_tab',
				'description'    => esc_html__( 'Move the slider, or input the value to set the tooltip animation duration.', 'divi-plus' ),
			),
			'global_show_speech_bubble'         => array(
				'label'           => esc_html__( 'Show Speech Bubble', 'divi-plus' ),
				'type'            => 'yes_no_button',
				'option_category' => 'layout',
				'options'         => array(
					'off' => esc_html__( 'No', 'divi-plus' ),
					'on'  => esc_html__( 'Yes', 'divi-plus' ),
				),
				'default'         => 'on',
				'tab_slug'        => 'advanced',
				'toggle_slug'     => 'global_tooltip_styling',
				'sub_toggle'      => 'global_tooltip_general_tab',
				'description'     => esc_html__( 'Here you can choose whether or not to display speech bubble.', 'divi-plus' ),
			),
			'global_tooltip_interactive'        => array(
				'label'           => esc_html__( 'Make Interactive Tooltip', 'divi-plus' ),
				'type'            => 'yes_no_button',
				'option_category' => 'layout',
				'options'         => array(
					'off' => esc_html__( 'No', 'divi-plus' ),
					'on'  => esc_html__( 'Yes', 'divi-plus' ),
				),
				'default'         => 'off',
				'tab_slug'        => 'advanced',
				'toggle_slug'     => 'global_tooltip_styling',
				'sub_toggle'      => 'global_tooltip_general_tab',
				'description'     => esc_html__( 'Here you can choose whether or not to display interactive toolpit. This would provide users the possibility to interact with the content of the tooltip.', 'divi-plus' ),
			),
			'tooltip_width'                     => array(
				'label'          => esc_html__( 'Tooltip Width', 'divi-plus' ),
				'type'           => 'range',
				'range_settings' => array(
					'min'  => '1',
					'max'  => '1000',
					'step' => '1',
				),
				'mobile_options' => true,
				'default'        => '200px',
				'default_unit'   => 'px',
				'allowed_units'  => array( '%', 'em', 'rem', 'px', 'vh', 'vw', 'cm', 'mm', 'in', 'pt', 'pc', 'ex' ),
				'tab_slug'       => 'advanced',
				'toggle_slug'    => 'global_tooltip_styling',
				'sub_toggle'     => 'global_tooltip_general_tab',
				'description'    => esc_html__( 'Move the slider, or input the value to set the maximum width of the tooltip.', 'divi-plus' ),
			),
			'tooltip_custom_padding'            => array(
				'label'           => esc_html__( 'Tooltip Padding', 'divi-plus' ),
				'type'            => 'custom_padding',
				'option_category' => 'layout',
				'mobile_options'  => true,
				'tab_slug'        => 'advanced',
				'toggle_slug'     => 'global_tooltip_styling',
				'sub_toggle'      => 'global_tooltip_general_tab',
				'description'     => esc_html__( 'Padding adds extra space to the inside of the element, increasing the distance between the edge of the element and its inner contents.', 'divi-plus' ),
			),
			'tooltip_background_color'          => array(
				'label'             => esc_html__( 'Tooltip Background', 'divi-plus' ),
				'type'              => 'background-field',
				'base_name'         => 'tooltip_background',
				'context'           => 'tooltip_background_color',
				'option_category'   => 'button',
				'custom_color'      => true,
				'background_fields' => $this->generate_background_options( 'tooltip_background', 'button', 'advanced', 'global_tooltip_styling', 'tooltip_background_color' ),
				'mobile_options'    => true,
				'tab_slug'          => 'advanced',
				'toggle_slug'       => 'global_tooltip_styling',
				'sub_toggle'        => 'global_tooltip_background_tab',
				'description'       => esc_html__( 'Adjust color, gradient, and image to customize the background style of the tooltip.' ),
			),
		);

		$dipl_hotspot_fields = array_merge( $dipl_hotspot_fields, $this->generate_background_options( 'tooltip_background', 'skip', 'advanced', 'global_tooltip_styling', 'tooltip_background_color' ) );

		return $dipl_hotspot_fields;
	}

	public function add_tooltip_dependencies() {
		wp_enqueue_script( 'elicus-tooltipster-script' );
		wp_enqueue_style( 'elicus-tooltipster-style' );
	}

	public function init_tooltip( $props ) {
		$order_class                = esc_attr( $this->get_module_order_class( 'dipl_hotspot' ) );
		$theme_class                = $order_class . '_0';
		$order_number               = str_replace( '_', '', str_replace( 'dipl_hotspot', '', $order_class ) );
		$element_id                 = '.dipl_hotspot_' . $order_number;
		$tooltip_entrance_animation = esc_attr( $props['tooltip_entrance_animation'] );
		$animation_durartion        = intval( esc_attr( $props['global_animation_durartion'] ) );
		$tooltip_width              = intval( esc_attr( $props['tooltip_width'] ) );
		$show_speech_bubble         = ( 'on' === esc_attr( $props['global_show_speech_bubble'] ) ) ? 'true' : 'false';
		$tooltip_interactive        = ( 'on' === esc_attr( $props['global_tooltip_interactive'] ) ) ? 'true' : 'false';

		return '<script type="text/javascript">
                jQuery(document).ready(function($){
            		$(\'.' . et_core_esc_previously( $order_class ) . '\').find(\'.dipl_marker\').each(function(){
                        $(this).tooltipster({
                            contentCloning: false,
                            trigger: "' . et_core_esc_previously( $props['trigger_event'] ) . '",
                            animation: "' . et_core_esc_previously( $tooltip_entrance_animation ) . '",
                            animationDuration: ' . et_core_esc_previously( $animation_durartion ) . ',
                            arrow: false,
                            interactive: ' . et_core_esc_previously( $tooltip_interactive ) . ',
                            maxWidth: ' . et_core_esc_previously( $tooltip_width ) . ',
                            theme: ["dipl_tooltip","' . et_core_esc_previously( $theme_class ) . '"],
                        });
                    });
                });
            </script>';
	}

	public function render( $attrs, $content, $render_slug ) {
		$hotspot_image                   = esc_attr( $this->props['hotspot_image'] );
		$use_plusating_animation         = esc_attr( $this->props['use_plusating_animation'] );
		$marker_shape                    = esc_attr( $this->props['marker_shape'] );
		$marker_shape_color              = esc_attr( $this->props['marker_shape_color'] );
		$marker_shape_color_hover        = esc_attr( $this->get_hover_value( 'marker_shape_color' ) );
		$marker_use_shape_border         = esc_attr( $this->props['marker_use_shape_border'] );
		$marker_shape_border_color       = esc_attr( $this->props['marker_shape_border_color'] );
		$marker_shape_border_color_hover = esc_attr( $this->get_hover_value( 'marker_shape_border_color' ) );
		$marker_border_size              = esc_attr( $this->props['marker_border_size'] );
		$marker_icon_color               = esc_attr( $this->props['marker_icon_color'] );
		$marker_icon_font_size           = et_pb_responsive_options()->get_property_values( $this->props, 'marker_icon_font_size' );
		$marker_image_size               = et_pb_responsive_options()->get_property_values( $this->props, 'marker_image_size' );
		$tooltip_width                   = esc_attr( $this->props['tooltip_width'] );
		$show_speech_bubble              = esc_attr( $this->props['global_show_speech_bubble'] );
		$hotspot_image_alt				 = esc_attr( $this->props['hotspot_image_alt'] );

		$this->add_tooltip_dependencies();

		$file = et_is_builder_plugin_active() ? 'style-dbp' : 'style';
        wp_enqueue_style( 'dipl-hotspot-style', PLUGIN_PATH . 'includes/modules/HotSpot/' . $file . '.min.css', array(), '1.0.0' );

		if ( ! empty( array_filter( $marker_icon_font_size ) ) ) {
			et_pb_responsive_options()->generate_responsive_css( $marker_icon_font_size, '%%order_class%% .et-pb-icon', 'font-size', $render_slug, '!important;', 'type' );
		}

		if ( ! empty( array_filter( $marker_image_size ) ) ) {
			et_pb_responsive_options()->generate_responsive_css( $marker_icon_font_size, '%%order_class%% .dipl_marker_wrapper img', 'width', $render_slug, '!important;', 'type' );
		}

		if ( '' !== $marker_icon_color ) {
			self::set_style(
				$render_slug,
				array(
					'selector'    => '%%order_class%% .et-pb-icon',
					'declaration' => sprintf( 'color: %1$s !important;', esc_html( $marker_icon_color ) ),
				)
			);
		}

		if ( 'none' !== $marker_shape ) {
			if ( 'use_circle' === $marker_shape ) {
				self::set_style(
					$render_slug,
					array(
						'selector'    => '%%order_class%% .dipl_hotspot_item',
						'declaration' => 'border-radius: 50%;',
					)
				);
			} else {
				self::set_style(
					$render_slug,
					array(
						'selector'    => '%%order_class%% .dipl_hotspot_item',
						'declaration' => 'border-radius: 0;',
					)
				);
			}
			if ( '' !== $marker_shape_color ) {
				self::set_style(
					$render_slug,
					array(
						'selector'    => '%%order_class%% .dipl_hotspot_item',
						'declaration' => sprintf( 'background-color: %1$s !important;', esc_html( $marker_shape_color ) ),
					)
				);
			}
			if ( isset( $marker_shape_color_hover ) ) {
				self::set_style(
					$render_slug,
					array(
						'selector'    => '%%order_class%% .dipl_hotspot_item:hover',
						'declaration' => sprintf( 'background-color: %1$s !important;', esc_html( $marker_shape_color_hover ) ),
					)
				);
			}
			if ( 'on' === $marker_use_shape_border ) {
				self::set_style(
					$render_slug,
					array(
						'selector'    => '%%order_class%% .dipl_hotspot_item',
						'declaration' => 'border-style: solid;',
					)
				);
				if ( '' !== $marker_shape_border_color ) {
					self::set_style(
						$render_slug,
						array(
							'selector'    => '%%order_class%% .dipl_hotspot_item',
							'declaration' => sprintf( 'border-color: %1$s;', esc_html( $marker_shape_border_color ) ),
						)
					);
				}
				if ( isset( $marker_shape_border_color_hover ) ) {
					self::set_style(
						$render_slug,
						array(
							'selector'    => '%%order_class%% .dipl_hotspot_item:hover',
							'declaration' => sprintf( 'border-color: %1$s;', esc_html( $marker_shape_border_color_hover ) ),
						)
					);
				}
				if ( '' !== $marker_border_size ) {
					self::set_style(
						$render_slug,
						array(
							'selector'    => '%%order_class%% .dipl_hotspot_item',
							'declaration' => sprintf( 'border-width: %1$s;', esc_html( $marker_border_size ) ),
						)
					);
				}
			}
		}

		if ( 'on' === $show_speech_bubble ) {
			self::set_style(
				$render_slug,
				array(
					'selector'    => '%%order_class%%_0.tooltipster-sidetip .tooltipster-box:before',
					'declaration' => 'content: "" !important;',
				)
			);
			self::set_style(
				$render_slug,
				array(
					'selector'    => '%%order_class%%_0.tooltipster-sidetip.tooltipster-top',
					'declaration' => 'padding-bottom:15px !important;',
				)
			);
			self::set_style(
				$render_slug,
				array(
					'selector'    => '%%order_class%%_0.tooltipster-sidetip.tooltipster-bottom',
					'declaration' => 'padding-top:15px !important;',
				)
			);
			self::set_style(
				$render_slug,
				array(
					'selector'    => '%%order_class%%_0.tooltipster-sidetip.tooltipster-right',
					'declaration' => 'padding-left:15px !important;',
				)
			);
			self::set_style(
				$render_slug,
				array(
					'selector'    => '%%order_class%%_0.tooltipster-sidetip.tooltipster-left',
					'declaration' => 'padding-right:15px !important;',
				)
			);
		}


		$args = array(
			'render_slug'	=> $render_slug,
			'props'			=> $this->props,
			'fields'		=> $this->fields_unprocessed,
			'module'		=> $this,
			'backgrounds' 	=> array(
				'tooltip_background' => array(
					'normal' => "{$this->main_css_element}_0 .tooltipster-box",
					'hover' => "{$this->main_css_element}_0 .tooltipster-box:hover",
	 			),
			),
		);

		DiviPlusHelper::process_background( $args );
		$fields = array( 'hotspot_padding' );
		DiviPlusHelper::process_advanced_margin_padding_css( $this, $render_slug, $this->margin_padding, $fields );

		$tooltip_script = $this->init_tooltip( $this->props );
		return sprintf(
			'<div class="dipl_hotspot_wrapper %3$s">
                            <div class="dipl_image_wrapper"><img src="%1$s" alt="%5$s"/></div>
                            %2$s
                        </div>%4$s',
			$hotspot_image,
			$this->content,
			( 'on' === $use_plusating_animation ? 'pulsating' : '' ),
			et_core_intentionally_unescaped( $tooltip_script, 'html' ),
			esc_attr( $hotspot_image_alt )
		);
	}

	public function add_new_child_text() {
		return esc_html__( 'Add New Hotspot Marker', 'divi-plus' );
	}
}
$plugin_options = get_option( ELICUS_DIVI_PLUS_OPTION );
if ( isset( $plugin_options['dipl-modules'] ) ) {
    $modules = explode( ',', $plugin_options['dipl-modules'] );
    if ( in_array( 'dipl_hot_spot', $modules ) ) {
        new DIPL_HotSpot();
    }
} else {
    new DIPL_HotSpot();
}