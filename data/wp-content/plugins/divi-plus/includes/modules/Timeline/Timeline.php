<?php
/**
 * @author      Elicus <hello@elicus.com>
 * @link        https://www.elicus.com/
 * @copyright   2022 Elicus Technologies Private Limited
 * @version     1.9.6
 */
class DIPL_Timeline extends ET_Builder_Module {

	public $slug       = 'dipl_timeline';
	public $child_slug = 'dipl_timeline_item';
	public $vb_support = 'on';

	protected $module_credits = array(
		'module_uri' => 'https://diviextended.com/product/divi-plus/',
		'author'     => 'Elicus',
		'author_uri' => 'https://elicus.com/',
	);

	public function init() {
		$this->name = esc_html__( 'DP Timeline', 'divi-plus' );
	}

	public function get_settings_modal_toggles() {
		return array(
			'advanced' => array(
				'toggles' => array(
					'timeline_layout'                  => array(
						'title'    => esc_html__( 'Timeline Layout', 'divi-plus' ),
						'priority' => 1,
					),
					'timeline_icon_styling'            => array(
						'title'             => esc_html__( 'Timeline Icon/Image Styling', 'divi-plus' ),
						'priority'          => 2,
						'tabbed_subtoggles' => true,
						'sub_toggles'       => array(
							'timeline_item_icon'  => array(
								'name' => 'Icon',
							),
							'timeline_item_image' => array(
								'name' => 'Image',
							),
						),
					),
					'timeline_bar_styling'             => array(
						'title'    => esc_html__( 'Timeline Stem Styling', 'divi-plus' ),
						'priority' => 2,
					),
					'global_timeline_item_styling'     => array(
						'title'             => esc_html__( 'Timeline Item Styling', 'divi-plus' ),
						'priority'          => 3,
						'tabbed_subtoggles' => true,
						'sub_toggles'       => array(
							'timeline_item_background' => array(
								'name' => 'Background',
							),
							'timeline_item_padding'    => array(
								'name' => 'Padding',
							),
						),
					),
					'global_timeline_heading_settings' => array(
						'title'    => esc_html__( 'Timeline Heading', 'divi-plus' ),
						'priority' => 4,
					),
					'global_timeline_text_settings'    => array(
						'title'             => esc_html__( 'Timeline Text', 'divi-plus' ),
						'priority'          => 5,
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
					'global_timeline_date_settings'    => array(
						'title'    => esc_html__( 'Date', 'divi-plus' ),
						'priority' => 4,
					),
				),
			),
		);
	}

	public function get_advanced_fields_config() {
		return array(
			'fonts'            => array(
				'global_timeline_header' => array(
					'label'          => esc_html__( 'Heading', 'divi-plus' ),
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
						'main' => '%%order_class%% .dipl_item_content h1, %%order_class%% .dipl_item_content h2, %%order_class%% .dipl_item_content h3, %%order_class%% .dipl_item_content h4, %%order_class%% .dipl_item_content h5, %%order_class%% .dipl_item_content h6, %%order_class%% .dipl_item_content .dipl_icon_wrapper',
						'important' => 'all',
					),
					'toggle_slug'    => 'global_timeline_heading_settings',
				),
				'global_timeline_text'   => array(
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
						'main' => '%%order_class%% .dipl_item_content .dipl_item_desc, %%order_class%% .dipl_item_content .dipl_item_desc p',
						'important' => 'all',
					),
					'toggle_slug'    => 'global_timeline_text_settings',
					'sub_toggle'     => 'p',
				),
				'global_timeline_link'   => array(
					'label'           => esc_html__( 'Link', 'divi-plus' ),
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
						'main' => '%%order_class%% .dipl_item_content .dipl_item_desc a',
						'important' => 'all',
					),
					'hide_text_align' => true,
					'toggle_slug'     => 'global_timeline_text_settings',
					'sub_toggle'      => 'a',
				),
				'global_timeline_ul'     => array(
					'label'           => esc_html__( 'Unordered List', 'divi-plus' ),
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
						'main' => '%%order_class%% .dipl_item_content .dipl_item_desc ul li',
						'important' => 'all',
					),
					'hide_text_align' => true,
					'toggle_slug'     => 'global_timeline_text_settings',
					'sub_toggle'      => 'ul',
				),
				'global_timeline_ol'     => array(
					'label'           => esc_html__( 'Ordered List', 'divi-plus' ),
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
						'main' => '%%order_class%% .dipl_item_content .dipl_item_desc ol li',
						'important' => 'all',
					),
					'hide_text_align' => true,
					'toggle_slug'     => 'global_timeline_text_settings',
					'sub_toggle'      => 'ol',
				),
				'global_timeline_quote'  => array(
					'label'           => esc_html__( 'Blockquote', 'divi-plus' ),
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
						'main' => '%%order_class%% .dipl_item_content .dipl_item_desc blockquote',
						'important' => 'all',
					),
					'hide_text_align' => true,
					'toggle_slug'     => 'global_timeline_text_settings',
					'sub_toggle'      => 'quote',
				),
				'global_timeline_date'   => array(
					'label'          => esc_html__( 'Date', 'divi-plus' ),
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
						'main' => '%%order_class%% .dipl_item_time',
						'important' => 'all',
					),
					'toggle_slug'    => 'global_timeline_date_settings',
				),
			),
			'box_shadow'       => array(
				'default' => array(
					'css' => array(
						'main' => '%%order_class%%',
					),
				),
			),
			'margin_padding'   => array(
				'css' => array(
					'main'      => '%%order_class%%',
					'important' => 'all',
				),
			),
			'timeline_padding' => array(
				'timeline' => array(
					'label'          => 'Timeline Padding',
					'margin_padding' => array(
						'css' => array(
							'main'      => '%%order_class%% .dipl_item_content_inner',
							'important' => 'all',
						),
					),
					'tab_slug'       => 'advanced',
					'toggle_slug'    => 'global_timeline_item_styling',
					'sub_toggle'     => 'timeline_item_padding',
				),
			),
			'text'             => false,
		);
	}

	public function get_fields() {

		$dipl_timeline_fields = array(
			'select_timeline_layout'                     => array(
				'label'           => esc_html__( 'Select Layout', 'divi-plus' ),
				'type'            => 'select',
				'option_category' => 'configuration',
				'options'         => array(
					'dipl_icon_tree' => esc_html__( 'Icon Tree', 'divi-plus' ),
					'dipl_date_tree' => esc_html__( 'Date Tree', 'divi-plus' ),
				),
				'default'         => 'dipl_icon_tree',
				'tab_slug'        => 'advanced',
				'toggle_slug'     => 'timeline_layout',
				'description'     => esc_html__( 'Here you can choose the layout for the timeline.', 'divi-plus' ),
			),
			'select_timeline_layout_option'              => array(
				'label'           => esc_html__( 'Select Option', 'divi-plus' ),
				'type'            => 'select',
				'option_category' => 'configuration',
				'options'         => array(
					'dipl_timeline_alternate' => esc_html__( 'Alternate', 'divi-plus' ),
					'dipl_timeline_right'     => esc_html__( 'Content Right', 'divi-plus' ),
					'dipl_timeline_left'      => esc_html__( 'Content Left', 'divi-plus' ),
				),
				'default'         => 'dipl_timeline_alternate',
				'tab_slug'        => 'advanced',
				'toggle_slug'     => 'timeline_layout',
				'description'     => esc_html__( 'Here you can choose the placement style of the timeline content.', 'divi-plus' ),
			),
			'global_timeline_icon_color'                 => array(
				'label'       => esc_html__( 'Icon Color', 'divi-plus' ),
				'type'        => 'color-alpha',
				'default'     => '#000',
				'hover'       => 'tabs',
				'tab_slug'    => 'advanced',
				'toggle_slug' => 'timeline_icon_styling',
				'sub_toggle'  => 'timeline_item_icon',
				'description' => esc_html__( 'Here you can choose a custom color to be used for the timeline icon when not scrolled.', 'divi-plus' ),
			),
			'global_icon_fill_color'                     => array(
				'label'       => esc_html__( 'Icon Fill Color(On Scroll)', 'divi-plus' ),
				'type'        => 'color-alpha',
				'default'     => '#eee',
				'show_if'     => array(
					'select_timeline_layout' => 'dipl_icon_tree',
				),
				'tab_slug'    => 'advanced',
				'toggle_slug' => 'timeline_icon_styling',
				'sub_toggle'  => 'timeline_item_icon',
				'description' => esc_html__( 'Here you can choose a custom color to be used for the timeline icon after scrolled', 'divi-plus' ),
			),
			'global_timeline_icon_font_size'             => array(
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
				'toggle_slug'     => 'timeline_icon_styling',
				'sub_toggle'      => 'timeline_item_icon',
				'description'     => esc_html__( 'Here you can increase or decrease the size of the timeline icon by moving the slider or entering the value.', 'divi-plus' ),
			),
			'global_icon_shape'                          => array(
				'label'           => esc_html__( 'Icon Shape', 'divi-plus' ),
				'type'            => 'select',
				'option_category' => 'configuration',
				'options'         => array(
					'none'       => esc_html__( 'None', 'divi-plus' ),
					'use_square' => esc_html__( 'Square', 'divi-plus' ),
					'use_circle' => esc_html__( 'Circle', 'divi-plus' ),
				),
				'default'         => 'none',
				'mobile_options'  => false,
				'tab_slug'        => 'advanced',
				'toggle_slug'     => 'timeline_icon_styling',
				'sub_toggle'      => 'timeline_item_icon',
				'description'     => esc_html__( 'Here you can choose the shape to be used for the timeline icon.', 'divi-plus' ),
			),
			'global_icon_shape_color'                    => array(
				'label'        => esc_html__( 'Icon Shape Background', 'divi-plus' ),
				'type'         => 'color-alpha',
				'custom_color' => true,
				'show_if_not'  => array(
					'global_icon_shape' => 'none',
				),
				'default'      => '#eee',
				'hover'        => 'tabs',
				'tab_slug'     => 'advanced',
				'toggle_slug'  => 'timeline_icon_styling',
				'sub_toggle'   => 'timeline_item_icon',
				'description'  => esc_html__( 'Here you can select a custom color to be used for the icon shape background when not scrolled.', 'divi-plus' ),
			),
			'global_icon_shape_fill_color'               => array(
				'label'        => esc_html__( 'Icon Shape Background Fill Color(On Scroll)', 'divi-plus' ),
				'type'         => 'color-alpha',
				'custom_color' => true,
				'show_if'      => array(
					'select_timeline_layout' => 'dipl_icon_tree',
				),
				'show_if_not'  => array(
					'global_icon_shape' => 'none',
				),
				'default'      => '#000',
				'hover'        => 'tabs',
				'tab_slug'     => 'advanced',
				'toggle_slug'  => 'timeline_icon_styling',
				'sub_toggle'   => 'timeline_item_icon',
				'description'  => esc_html__( 'Here you can select a custom color to be used for the icon shape background after scrolled.', 'divi-plus' ),
			),
			'global_icon_use_shape_border'               => array(
				'label'           => esc_html__( 'Icon Shape Border', 'divi-plus' ),
				'type'            => 'yes_no_button',
				'option_category' => 'layout',
				'options'         => array(
					'off' => esc_html__( 'No', 'divi-plus' ),
					'on'  => esc_html__( 'Yes', 'divi-plus' ),
				),
				'show_if_not'     => array(
					'global_icon_shape' => 'none',
				),
				'default'         => 'off',
				'tab_slug'        => 'advanced',
				'toggle_slug'     => 'timeline_icon_styling',
				'sub_toggle'      => 'timeline_item_icon',
				'description'     => esc_html__( 'Here you can choose whether or not to use border on the icon shape.', 'divi-plus' ),
			),
			'global_icon_shape_border_color'             => array(
				'label'        => esc_html__( 'Icon Shape Border Color', 'divi-plus' ),
				'type'         => 'color-alpha',
				'custom_color' => true,
				'show_if_not'  => array(
					'global_icon_shape' => 'none',
				),
				'show_if'      => array(
					'global_icon_use_shape_border' => 'on',
				),
				'default'      => '#eee',
				'hover'        => 'tabs',
				'tab_slug'     => 'advanced',
				'toggle_slug'  => 'timeline_icon_styling',
				'sub_toggle'   => 'timeline_item_icon',
				'description'  => esc_html__( 'Here you can choose a custom color to be used for the icon shape border when not scrolled.', 'divi-plus' ),
			),
			'global_icon_shape_border_fill_color'        => array(
				'label'        => esc_html__( 'Icon Shape Border Fill Color(On Scroll)', 'divi-plus' ),
				'type'         => 'color-alpha',
				'custom_color' => true,
				'show_if_not'  => array(
					'global_icon_shape' => 'none',
				),
				'show_if'      => array(
					'global_icon_use_shape_border' => 'on',
					'select_timeline_layout'       => 'dipl_icon_tree',
				),
				'default'      => '#000',
				'hover'        => 'tabs',
				'tab_slug'     => 'advanced',
				'toggle_slug'  => 'timeline_icon_styling',
				'sub_toggle'   => 'timeline_item_icon',
				'description'  => esc_html__( 'Here you can choose a custom color to be used for the icon shape border after scrolled.', 'divi-plus' ),
			),
			'global_icon_shape_border_size'              => array(
				'label'           => esc_html__( 'Icon Shape Border Size', 'divi-plus' ),
				'type'            => 'range',
				'option_category' => 'layout',
				'range_settings'  => array(
					'min'  => '1',
					'max'  => '100',
					'step' => '1',
				),
				'show_if_not'     => array(
					'global_icon_shape' => 'none',
				),
				'show_if'         => array(
					'global_icon_use_shape_border' => 'on',
				),
				'mobile_options'  => false,
				'default'         => '2px',
				'tab_slug'        => 'advanced',
				'toggle_slug'     => 'timeline_icon_styling',
				'sub_toggle'      => 'timeline_item_icon',
				'description'     => esc_html__( 'Here you can increase or decrease the size of the timeline icon shape border by moving the slider or entering the value.', 'divi-plus' ),
			),
			'global_item_image_size'                     => array(
				'label'           => esc_html__( 'Image Size', 'divi-plus' ),
				'type'            => 'range',
				'option_category' => 'layout',
				'range_settings'  => array(
					'min'  => '16',
					'max'  => '300',
					'step' => '1',
				),
				'show_if'         => array(
					'select_timeline_layout' => 'dipl_icon_tree',
				),
				'mobile_options'  => true,
				'default'         => '32px',
				'fixed_unit'      => 'px',
				'tab_slug'        => 'advanced',
				'toggle_slug'     => 'timeline_icon_styling',
				'sub_toggle'      => 'timeline_item_image',
				'description'     => esc_html__( 'Here you can increase or decrease the size of the timeline image by moving the slider or entering the value.', 'divi-plus' ),
			),
			'global_date_tree_image_size'                     => array(
				'label'           => esc_html__( 'Image Size', 'divi-plus' ),
				'type'            => 'range',
				'option_category' => 'layout',
				'range_settings'  => array(
					'min'  => '16',
					'max'  => '2000',
					'step' => '1',
				),
				'show_if'         => array(
					'select_timeline_layout' => 'dipl_date_tree',
				),
				'mobile_options'  => true,
				'default'         => '32px',
				'tab_slug'        => 'advanced',
				'toggle_slug'     => 'timeline_icon_styling',
				'sub_toggle'      => 'timeline_item_image',
				'description'     => esc_html__( 'Here you can increase or decrease the size of the timeline image by moving the slider or entering the value.', 'divi-plus' ),
			),
			'image_alignment' => array(
                'label'                 => esc_html__( 'Image Alignment', 'divi-plus' ),
                'type'                  => 'text_align',
                'option_category'       => 'layout',
                'options'               => et_builder_get_text_orientation_options( array( 'justified' ) ),
                'mobile_options'        => true,
                'show_if'         => array(
					'select_timeline_layout' => 'dipl_date_tree',
				),
                'tab_slug'        => 'advanced',
                'toggle_slug'     => 'timeline_icon_styling',
				'sub_toggle'      => 'timeline_item_image',
                'description'           => esc_html__( 'Here you can choose the alignment of the image in the left, right, or center of the module.', 'divi-plus' ),
            ),
			'timeline_bar_size'                          => array(
				'label'           => esc_html__( 'Timeline Stem Width', 'divi-plus' ),
				'type'            => 'range',
				'option_category' => 'layout',
				'range_settings'  => array(
					'min'  => '1',
					'max'  => '10',
					'step' => '1',
				),
				'mobile_options'  => false,
				'default'         => '2px',
				'tab_slug'        => 'advanced',
				'toggle_slug'     => 'timeline_bar_styling',
				'description'     => esc_html__( 'Here you can increase or decrease the thickness of the timeline stem by moving the slider or entering the value.', 'divi-plus' ),
			),
			'timeline_bar_color'                         => array(
				'label'       => esc_html__( 'Stem Color', 'divi-plus' ),
				'type'        => 'color-alpha',
				'default'     => '#eee',
				'tab_slug'    => 'advanced',
				'toggle_slug' => 'timeline_bar_styling',
				'description' => esc_html__( 'Here you can choose a custom color to be used for the timiline stem when not scrolled.', 'divi-plus' ),
			),
			'timeline_bar_fill_color'                    => array(
				'label'       => esc_html__( 'Stem Fill Color(On Scroll)', 'divi-plus' ),
				'type'        => 'color-alpha',
				'default'     => '#000',
				'tab_slug'    => 'advanced',
				'toggle_slug' => 'timeline_bar_styling',
				'description' => esc_html__( 'Here you can choose a custom color to be used for the timiline stem after scrolled.', 'divi-plus' ),
			),
			'timeline_bar_shape_border_size'             => array(
				'label'           => esc_html__( 'Stem Shape Border', 'divi-plus' ),
				'type'            => 'range',
				'option_category' => 'layout',
				'range_settings'  => array(
					'min'  => '1',
					'max'  => '60',
					'step' => '1',
				),
				'show_if'         => array(
					'select_timeline_layout' => 'dipl_date_tree',
				),
				'mobile_options'  => false,
				'default'         => '8px',
				'tab_slug'        => 'advanced',
				'toggle_slug'     => 'timeline_bar_styling',
				'description'     => esc_html__( 'Here you can choose whether or not to use a border on the timeline stem shape.', 'divi-plus' ),
			),
			'timeline_bar_shape_color'                   => array(
				'label'       => esc_html__( 'Stem Shape Color', 'divi-plus' ),
				'type'        => 'color-alpha',
				'default'     => '#fff',
				'show_if'     => array(
					'select_timeline_layout' => 'dipl_date_tree',
				),
				'tab_slug'    => 'advanced',
				'toggle_slug' => 'timeline_bar_styling',
				'description' => esc_html__( 'Here you can choose a custom color to be used for the shape of the timeline stem when not scrolled.', 'divi-plus' ),
			),
			'timeline_bar_shape_fill_color'              => array(
				'label'       => esc_html__( 'Stem Shape Fill Color(On Scroll)', 'divi-plus' ),
				'type'        => 'color-alpha',
				'default'     => '#eee',
				'show_if'     => array(
					'select_timeline_layout' => 'dipl_date_tree',
				),
				'tab_slug'    => 'advanced',
				'toggle_slug' => 'timeline_bar_styling',
				'description' => esc_html__( 'Here you can choose a custom color to be used for the shape of the timeline stem after scrolled.', 'divi-plus' ),
			),
			'timeline_bar_shape_border_color'            => array(
				'label'       => esc_html__( 'Stem Shape Border Color', 'divi-plus' ),
				'type'        => 'color-alpha',
				'default'     => '#eee',
				'show_if'     => array(
					'select_timeline_layout' => 'dipl_date_tree',
				),
				'tab_slug'    => 'advanced',
				'toggle_slug' => 'timeline_bar_styling',
				'description' => esc_html__( 'Here you can choose a custom color to be used for the shape border of the timeline stem when not scrolled.', 'divi-plus' ),
			),
			'timeline_bar_shape_border_fill_color'       => array(
				'label'       => esc_html__( 'Stem Shape Border Fill Color(On Scroll)', 'divi-plus' ),
				'type'        => 'color-alpha',
				'default'     => '#000',
				'show_if'     => array(
					'select_timeline_layout' => 'dipl_date_tree',
				),
				'tab_slug'    => 'advanced',
				'toggle_slug' => 'timeline_bar_styling',
				'description' => esc_html__( 'Here you can choose a custom color to be used for the shape border of the timeline stem after scrolled.', 'divi-plus' ),
			),
			'timeline_custom_padding'                    => array(
				'label'           => esc_html__( 'Timeline Padding', 'divi-plus' ),
				'type'            => 'custom_padding',
				'option_category' => 'layout',
				'mobile_options'  => true,
				'tab_slug'        => 'advanced',
				'toggle_slug'     => 'global_timeline_item_styling',
				'sub_toggle'      => 'timeline_item_padding',
				'description'     => esc_html__( 'Padding adds extra space to the inside of the element, increasing the distance between the edge of the element and its inner contents.', 'divi-plus' ),
			),
			'global_timeline_background_color'           => array(
				'label'             => esc_html__( 'Timeline Background', 'divi-plus' ),
				'type'              => 'background-field',
				'base_name'         => 'global_timeline_background',
				'context'           => 'global_timeline_background_color',
				'option_category'   => 'button',
				'custom_color'      => true,
				'background_fields' => $this->generate_background_options( 'global_timeline_background', 'button', 'advanced', 'global_timeline_item_styling', 'global_timeline_background_color' ),
				'mobile_options'    => true,
				'tab_slug'          => 'advanced',
				'toggle_slug'       => 'global_timeline_item_styling',
				'sub_toggle'        => 'timeline_item_background',
				'description'       => esc_html__( 'Customize the background style of the tooltip by adjusting the background color, gradient, and image.', 'divi-plus' ),
			),
		);

		$dipl_timeline_fields = array_merge( $dipl_timeline_fields, $this->generate_background_options( 'global_timeline_background', 'skip', 'advanced', 'global_timeline_item_styling', 'global_timeline_background_color' ) );

		return $dipl_timeline_fields;
	}

	public function before_render() {
		global $dipl_timeline_layout;
		$dipl_timeline_layout = $this->props['select_timeline_layout'];
	}

	public function render( $attrs, $content, $render_slug ) {

		$select_timeline_layout                    = esc_attr( $this->props['select_timeline_layout'] );
		$select_timeline_layout_option             = esc_attr( $this->props['select_timeline_layout_option'] );
		$global_timeline_icon_color                = esc_attr( $this->props['global_timeline_icon_color'] );
		$global_timeline_icon_color_hover          = esc_attr( $this->get_hover_value( 'global_timeline_icon_color' ) );
		$global_timeline_icon_font_size            = esc_attr( $this->props['global_timeline_icon_font_size'] );
		$global_icon_shape                         = esc_attr( $this->props['global_icon_shape'] );
		$global_icon_shape_color                   = esc_attr( $this->props['global_icon_shape_color'] );
		$global_icon_shape_color_hover             = esc_attr( $this->get_hover_value( 'global_icon_shape_color' ) );
		$global_icon_shape_fill_color              = esc_attr( $this->props['global_icon_shape_fill_color'] );
		$global_icon_shape_fill_color_hover        = esc_attr( $this->get_hover_value( 'global_icon_shape_color' ) );
		$global_icon_use_shape_border              = esc_attr( $this->props['global_icon_use_shape_border'] );
		$global_icon_shape_border_color            = esc_attr( $this->props['global_icon_shape_border_color'] );
		$global_icon_shape_border_color_hover      = esc_attr( $this->get_hover_value( 'global_icon_shape_border_color' ) );
		$global_icon_shape_border_fill_color       = esc_attr( $this->props['global_icon_shape_border_fill_color'] );
		$global_icon_shape_border_fill_color_hover = esc_attr( $this->get_hover_value( 'global_icon_shape_border_fill_color' ) );
		$global_icon_shape_border_size             = esc_attr( $this->props['global_icon_shape_border_size'] );
		$global_item_image_size                    = et_pb_responsive_options()->get_property_values( $this->props, 'global_item_image_size' );
		$global_date_tree_image_size 			   = et_pb_responsive_options()->get_property_values( $this->props, 'global_date_tree_image_size' );
		$image_alignment_values  	 			   = et_pb_responsive_options()->get_property_values( $this->props, 'image_alignment' );
		$timeline_bar_color                        = esc_attr( $this->props['timeline_bar_color'] );
		$timeline_bar_size                         = esc_attr( $this->props['timeline_bar_size'] );
		$timeline_bar_fill_color                   = esc_attr( $this->props['timeline_bar_fill_color'] );
		$timeline_bar_shape_border_size            = esc_attr( $this->props['timeline_bar_shape_border_size'] );
		$timeline_bar_shape_color                  = esc_attr( $this->props['timeline_bar_shape_color'] );
		$timeline_bar_shape_fill_color             = esc_attr( $this->props['timeline_bar_shape_fill_color'] );
		$timeline_bar_shape_border_color           = esc_attr( $this->props['timeline_bar_shape_border_color'] );
		$timeline_bar_shape_border_fill_color      = esc_attr( $this->props['timeline_bar_shape_border_fill_color'] );
		$global_icon_fill_color                    = esc_attr( $this->props['global_icon_fill_color'] );

		wp_enqueue_script( 'dipl-timeline-custom', PLUGIN_PATH."includes/modules/Timeline/dipl-timeline-custom.min.js", array('jquery'), '1.0.0', true );
		$file = et_is_builder_plugin_active() ? 'style-dbp' : 'style';
        wp_enqueue_style( 'dipl-timeline-style', PLUGIN_PATH . 'includes/modules/Timeline/' . $file . '.min.css', array(), '1.0.0' );

		foreach( $image_alignment_values as &$align ) {
			$align = str_replace( array( 'left', 'right' ), array( 'flex-start', 'flex-end' ), $align);
		}

		if ( '' !== $global_timeline_icon_font_size ) {
			$font_size_values = et_pb_responsive_options()->get_property_values( $this->props, 'global_timeline_icon_font_size' );
			et_pb_responsive_options()->generate_responsive_css( $font_size_values, '%%order_class%% .dipl_item_icon.et-pb-icon', 'font-size', $render_slug, '!important;', 'type' );

			if ( 'dipl_icon_tree' === $select_timeline_layout ) {
				$icon_shape_border_size = ( '' !== $global_icon_shape_border_size ) ? intval( $global_icon_shape_border_size ) : 0;

				$font_size_values['tablet'] = ( '' !== $font_size_values['tablet'] ) ? $font_size_values['tablet'] : $font_size_values['desktop'];
				$font_size_values['phone'] = ( '' !== $font_size_values['phone'] ) ? $font_size_values['phone'] : $font_size_values['desktop'];
				$global_item_image_size_desktop = ( '' !== $global_item_image_size['desktop'] ) ? $global_item_image_size['desktop'] : $global_item_image_size['desktop'];
				$global_item_image_size_tablet = ( '' !== $global_item_image_size['tablet'] ) ? $global_item_image_size['tablet'] : $global_item_image_size['desktop'];
				$global_item_image_size_phone = ( '' !== $global_item_image_size['phone'] ) ? $global_item_image_size['phone'] : $global_item_image_size['desktop'];

				if ( 'none' === $global_icon_shape ) {
					$circle_icon_desktop = intval( $font_size_values['desktop'] ) . 'px';
					$circle_icon_tablet  = intval( $font_size_values['tablet'] ) . 'px';
					$circle_icon_phone   = intval( $font_size_values['phone'] ) . 'px';
				} else {
					$circle_icon_desktop = ( intval( $font_size_values['desktop'] ) + ( 2 * intval( $icon_shape_border_size ) ) + 20 ) . 'px';
					$circle_icon_tablet  = ( intval( $font_size_values['tablet'] ) + ( 2 * intval( $icon_shape_border_size ) ) + 20 ) . 'px';
					$circle_icon_phone   = ( intval( $font_size_values['phone'] ) + ( 2 * intval( $icon_shape_border_size ) ) + 20 ) . 'px';
				}

				ET_Builder_Element::set_style(
					$render_slug,
					array(
						'selector'    => '%%order_class%% .dipl_item_circle',
						'declaration' => sprintf( 'width: %1$s !important;', esc_html( $circle_icon_desktop ) ),
					)
				);
				ET_Builder_Element::set_style(
					$render_slug,
					array(
						'selector'    => '%%order_class%% .dipl_item_circle',
						'declaration' => sprintf( 'width: %1$s !important;', esc_html( $circle_icon_tablet ) ),
						'media_query' => ET_Builder_Element::get_media_query( 'max_width_980' ),
					)
				);
				ET_Builder_Element::set_style(
					$render_slug,
					array(
						'selector'    => '%%order_class%% .dipl_item_circle',
						'declaration' => sprintf( 'width: %1$s !important;', esc_html( $circle_icon_phone ) ),
						'media_query' => ET_Builder_Element::get_media_query( 'max_width_767' ),
					)
				);
				ET_Builder_Element::set_style(
					$render_slug,
					array(
						'selector'    => '%%order_class%% .dipl_item_circle',
						'declaration' => sprintf( 'height: %1$s !important;', esc_html( $circle_icon_desktop ) ),
					)
				);
				ET_Builder_Element::set_style(
					$render_slug,
					array(
						'selector'    => '%%order_class%% .dipl_item_circle',
						'declaration' => sprintf( 'height: %1$s !important;', esc_html( $circle_icon_tablet ) ),
						'media_query' => ET_Builder_Element::get_media_query( 'max_width_980' ),
					)
				);
				ET_Builder_Element::set_style(
					$render_slug,
					array(
						'selector'    => '%%order_class%% .dipl_item_circle',
						'declaration' => sprintf( 'height: %1$s !important;', esc_html( $circle_icon_phone ) ),
						'media_query' => ET_Builder_Element::get_media_query( 'max_width_767' ),
					)
				);

				if ( floatval( $global_item_image_size_desktop ) > floatval( $circle_icon_desktop ) ) {
					ET_Builder_Element::set_style(
						$render_slug,
						array(
							'selector'    => '%%order_class%% .dipl_timeline_left .dipl_stem_center, %%order_class%% .dipl_timeline_right .dipl_stem_center',
							'declaration' => sprintf( 'width: %1$s !important;', esc_html( $global_item_image_size_desktop ) ),
						)
					);
					if ( 'dipl_timeline_right' === $select_timeline_layout_option ) {
						ET_Builder_Element::set_style(
							$render_slug,
							array(
								'selector'    => '%%order_class%% .dipl_timeline_right .dipl_stem_wrapper, %%order_class%% .dipl_timeline_right .dipl_stem_center',
								'declaration' => sprintf( 'left: %1$spx !important;', esc_html( floatval( $global_item_image_size_desktop ) / 2 ) ),
							)
						);
					}
					if ( 'dipl_timeline_left' === $select_timeline_layout_option ) {
						ET_Builder_Element::set_style(
							$render_slug,
							array(
								'selector'    => '%%order_class%% .dipl_timeline_left .dipl_stem_wrapper, %%order_class%% .dipl_timeline_left .dipl_stem_center',
								'declaration' => sprintf( 'right: %1$spx !important;', esc_html( floatval( $global_item_image_size_desktop ) / 2 ) ),
							)
						);
					}
				} else {
					ET_Builder_Element::set_style(
						$render_slug,
						array(
							'selector'    => '%%order_class%% .dipl_timeline_left .dipl_stem_center, %%order_class%% .dipl_timeline_right .dipl_stem_center',
							'declaration' => sprintf( 'width: %1$s !important;', esc_html( $circle_icon_desktop ) ),
						)
					);
					if ( 'dipl_timeline_right' === $select_timeline_layout_option ) {
						ET_Builder_Element::set_style(
							$render_slug,
							array(
								'selector'    => '%%order_class%% .dipl_timeline_right .dipl_stem_wrapper, %%order_class%% .dipl_timeline_right .dipl_stem_center',
								'declaration' => sprintf( 'left: %1$spx !important;', esc_html( floatval( $circle_icon_desktop ) / 2 ) ),
							)
						);
					}
					if ( 'dipl_timeline_left' === $select_timeline_layout_option ) {
						ET_Builder_Element::set_style(
							$render_slug,
							array(
								'selector'    => '%%order_class%% .dipl_timeline_left .dipl_stem_wrapper, %%order_class%% .dipl_timeline_left .dipl_stem_center',
								'declaration' => sprintf( 'right: %1$spx !important;', esc_html( floatval( $circle_icon_desktop ) / 2 ) ),
							)
						);
					}
				}

				if ( floatval( $global_item_image_size_tablet ) > floatval( $circle_icon_tablet ) ) {
					ET_Builder_Element::set_style(
						$render_slug,
						array(
							'selector'    => '%%order_class%% .dipl_stem_center',
							'declaration' => sprintf( 'width: %1$s !important;', esc_html( $global_item_image_size_tablet ) ),
							'media_query' => ET_Builder_Element::get_media_query( 'max_width_980' ),
						)
					);
					if ( 'dipl_timeline_right' === $select_timeline_layout_option ) {
						ET_Builder_Element::set_style(
							$render_slug,
							array(
								'selector'    => '%%order_class%% .dipl_stem_wrapper',
								'declaration' => sprintf( 'left: %1$spx !important;', esc_html( floatval( $global_item_image_size_tablet ) / 2 ) ),
								'media_query' => ET_Builder_Element::get_media_query( 'max_width_980' ),
							)
						);
					}
					if ( 'dipl_timeline_left' === $select_timeline_layout_option ) {
						ET_Builder_Element::set_style(
							$render_slug,
							array(
								'selector'    => '%%order_class%% .dipl_stem_wrapper',
								'declaration' => sprintf( 'right: %1$spx !important;', esc_html( floatval( $global_item_image_size_tablet ) / 2 ) ),
								'media_query' => ET_Builder_Element::get_media_query( 'max_width_980' ),
							)
						);
					}
				} else {
					ET_Builder_Element::set_style(
						$render_slug,
						array(
							'selector'    => '%%order_class%% .dipl_stem_center',
							'declaration' => sprintf( 'width: %1$s !important;', esc_html( $circle_icon_tablet ) ),
							'media_query' => ET_Builder_Element::get_media_query( 'max_width_980' ),
						)
					);
					if ( 'dipl_timeline_right' === $select_timeline_layout_option ) {
						ET_Builder_Element::set_style(
							$render_slug,
							array(
								'selector'    => '%%order_class%% .dipl_stem_wrapper',
								'declaration' => sprintf( 'left: %1$spx !important;', esc_html( floatval( $circle_icon_tablet ) / 2 ) ),
								'media_query' => ET_Builder_Element::get_media_query( 'max_width_980' ),
							)
						);
					}
					if ( 'dipl_timeline_left' === $select_timeline_layout_option ) {
						ET_Builder_Element::set_style(
							$render_slug,
							array(
								'selector'    => '%%order_class%% .dipl_stem_wrapper',
								'declaration' => sprintf( 'right: %1$spx !important;', esc_html( floatval( $circle_icon_tablet ) / 2 ) ),
								'media_query' => ET_Builder_Element::get_media_query( 'max_width_980' ),
							)
						);
					}
				}

				if ( floatval( $global_item_image_size_phone ) > floatval( $circle_icon_phone ) ) {
					ET_Builder_Element::set_style(
						$render_slug,
						array(
							'selector'    => '%%order_class%% .dipl_stem_center',
							'declaration' => sprintf( 'width: %1$s !important;', esc_html( $global_item_image_size_phone ) ),
							'media_query' => ET_Builder_Element::get_media_query( 'max_width_767' ),
						)
					);
					if ( 'dipl_timeline_right' === $select_timeline_layout_option ) {
						ET_Builder_Element::set_style(
							$render_slug,
							array(
								'selector'    => '%%order_class%% .dipl_stem_wrapper',
								'declaration' => sprintf( 'left: %1$spx !important;', esc_html( floatval( $global_item_image_size_phone ) / 2 ) ),
								'media_query' => ET_Builder_Element::get_media_query( 'max_width_767' ),
							)
						);
					}
					if ( 'dipl_timeline_left' === $select_timeline_layout_option ) {
						ET_Builder_Element::set_style(
							$render_slug,
							array(
								'selector'    => '%%order_class%% .dipl_stem_wrapper',
								'declaration' => sprintf( 'right: %1$spx !important;', esc_html( floatval( $global_item_image_size_phone ) / 2 ) ),
								'media_query' => ET_Builder_Element::get_media_query( 'max_width_767' ),
							)
						);
					}
					if ( 'dipl_timeline_alternate' === $select_timeline_layout_option ) {
						ET_Builder_Element::set_style(
							$render_slug,
							array(
								'selector'    => '%%order_class%% .dipl_stem_wrapper',
								'declaration' => sprintf( 'left: %1$spx !important;', esc_html( floatval( $global_item_image_size_phone ) / 2 ) ),
								'media_query' => ET_Builder_Element::get_media_query( 'max_width_767' ),
							)
						);
					}
				} else {
					ET_Builder_Element::set_style(
						$render_slug,
						array(
							'selector'    => '%%order_class%% .dipl_stem_center',
							'declaration' => sprintf( 'width: %1$s !important;', esc_html( $circle_icon_phone ) ),
							'media_query' => ET_Builder_Element::get_media_query( 'max_width_767' ),
						)
					);
					if ( 'dipl_timeline_right' === $select_timeline_layout_option ) {
						ET_Builder_Element::set_style(
							$render_slug,
							array(
								'selector'    => '%%order_class%% .dipl_stem_wrapper',
								'declaration' => sprintf( 'left: %1$spx !important;', esc_html( floatval( $circle_icon_phone ) / 2 ) ),
								'media_query' => ET_Builder_Element::get_media_query( 'max_width_767' ),
							)
						);
					}
					if ( 'dipl_timeline_left' === $select_timeline_layout_option ) {
						ET_Builder_Element::set_style(
							$render_slug,
							array(
								'selector'    => '%%order_class%% .dipl_stem_wrapper',
								'declaration' => sprintf( 'right: %1$spx !important;', esc_html( floatval( $circle_icon_phone ) / 2 ) ),
								'media_query' => ET_Builder_Element::get_media_query( 'max_width_767' ),
							)
						);
					}
					if ( 'dipl_timeline_alternate' === $select_timeline_layout_option ) {
						ET_Builder_Element::set_style(
							$render_slug,
							array(
								'selector'    => '%%order_class%% .dipl_stem_wrapper',
								'declaration' => sprintf( 'left: %1$spx !important;', esc_html( floatval( $circle_icon_phone ) / 2 ) ),
								'media_query' => ET_Builder_Element::get_media_query( 'max_width_767' ),
							)
						);
					}
				}
			}
		}

		if ( ! empty( array_filter( $global_item_image_size ) ) ) {
			et_pb_responsive_options()->generate_responsive_css( $global_item_image_size, '%%order_class%% .dipl_icon_tree .dipl_image_wrapper img', 'width', $render_slug, '!important;', 'type' );
		}

		if ( ! empty( array_filter( $global_date_tree_image_size ) ) ) {
			et_pb_responsive_options()->generate_responsive_css( $global_date_tree_image_size, '%%order_class%% .dipl_date_tree .dipl_image_wrapper img', 'width', $render_slug, '!important;', 'type' );
		}

		if ( ! empty( array_filter( $image_alignment_values ) ) && 'dipl_date_tree' === $select_timeline_layout ) {
			et_pb_responsive_options()->generate_responsive_css( $image_alignment_values, '%%order_class%% .dipl_image_wrapper', 'justify-content', $render_slug, '!important', 'type' );
		}

		if ( '' !== $global_timeline_icon_color ) {
			ET_Builder_Element::set_style(
				$render_slug,
				array(
					'selector'    => '%%order_class%% .dipl_item_icon',
					'declaration' => sprintf( 'color: %1$s !important;', esc_html( $global_timeline_icon_color ) ),
				)
			);
			ET_Builder_Element::set_style(
				$render_slug,
				array(
					'selector'    => '%%order_class%% .dipl_item_icon.dipl_item_circle',
					'declaration' => sprintf( 'border-color: %1$s;', esc_html( $global_timeline_icon_color ) ),
				)
			);
		}

		if ( isset( $global_timeline_icon_color_hover ) ) {
			ET_Builder_Element::set_style(
				$render_slug,
				array(
					'selector'    => '%%order_class%% .dipl_item_icon:hover',
					'declaration' => sprintf( 'color: %1$s !important;', esc_html( $global_timeline_icon_color_hover ) ),
				)
			);
			ET_Builder_Element::set_style(
				$render_slug,
				array(
					'selector'    => '%%order_class%% .dipl_item_icon.dipl_item_circle:hover',
					'declaration' => sprintf( 'border-color: %1$s;', esc_html( $global_timeline_icon_color_hover ) ),
				)
			);
		}

		if ( '' !== $global_icon_fill_color ) {
			ET_Builder_Element::set_style(
				$render_slug,
				array(
					'selector'    => '%%order_class%% .dipl_item_icon.dipl_icon_fill',
					'declaration' => sprintf( 'color: %1$s !important;', $global_icon_fill_color ),
				)
			);
			ET_Builder_Element::set_style(
				$render_slug,
				array(
					'selector'    => '%%order_class%% .dipl_item_icon.dipl_item_circle.dipl_icon_fill',
					'declaration' => sprintf( 'border-color: %1$s;', esc_html( $global_icon_fill_color ) ),
				)
			);
		}

		if ( 'none' !== $global_icon_shape ) {
			if ( 'use_circle' === $global_icon_shape ) {
				ET_Builder_Element::set_style(
					$render_slug,
					array(
						'selector'    => '%%order_class%% .dipl_item_icon',
						'declaration' => 'border-radius: 50%;',
					)
				);
				ET_Builder_Element::set_style(
					$render_slug,
					array(
						'selector'    => '%%order_class%% .dipl_item_icon',
						'declaration' => 'padding: 10px;',
					)
				);
			} else {
				ET_Builder_Element::set_style(
					$render_slug,
					array(
						'selector'    => '%%order_class%% .dipl_item_icon',
						'declaration' => 'border-radius: 0;',
					)
				);
				ET_Builder_Element::set_style(
					$render_slug,
					array(
						'selector'    => '%%order_class%% .dipl_item_icon',
						'declaration' => 'padding: 10px;',
					)
				);
			}

			if ( '' !== $global_icon_shape_color ) {
				ET_Builder_Element::set_style(
					$render_slug,
					array(
						'selector'    => '%%order_class%% .dipl_item_icon',
						'declaration' => sprintf( 'background-color: %1$s !important;', esc_html( $global_icon_shape_color ) ),
					)
				);
			}
			if ( isset( $global_icon_shape_color_hover ) ) {
				ET_Builder_Element::set_style(
					$render_slug,
					array(
						'selector'    => '%%order_class%% .dipl_item_icon:hover',
						'declaration' => sprintf( 'background-color: %1$s !important;', esc_html( $global_icon_shape_color_hover ) ),
					)
				);
			}
			if ( '' !== $global_icon_shape_fill_color ) {
				ET_Builder_Element::set_style(
					$render_slug,
					array(
						'selector'    => '%%order_class%% .dipl_item_icon.dipl_icon_fill',
						'declaration' => sprintf( 'background-color: %1$s !important;', esc_html( $global_icon_shape_fill_color ) ),
					)
				);
			}
			if ( isset( $global_icon_shape_fill_color_hover ) ) {
				ET_Builder_Element::set_style(
					$render_slug,
					array(
						'selector'    => '%%order_class%% .dipl_item_icon.dipl_icon_fill:hover',
						'declaration' => sprintf( 'background-color: %1$s !important;', esc_html( $global_icon_shape_fill_color_hover ) ),
					)
				);
			}
			if ( 'on' === $global_icon_use_shape_border ) {
				ET_Builder_Element::set_style(
					$render_slug,
					array(
						'selector'    => '%%order_class%% .dipl_item_icon',
						'declaration' => 'border-style: solid;',
					)
				);
				if ( '' !== $global_icon_shape_border_color ) {
					ET_Builder_Element::set_style(
						$render_slug,
						array(
							'selector'    => '%%order_class%% .dipl_item_icon',
							'declaration' => sprintf( 'border-color: %1$s;', esc_html( $global_icon_shape_border_color ) ),
						)
					);
				}
				if ( isset( $global_icon_shape_border_color_hover ) ) {
					ET_Builder_Element::set_style(
						$render_slug,
						array(
							'selector'    => '%%order_class%% .dipl_item_icon:hover',
							'declaration' => sprintf( 'border-color: %1$s;', esc_html( $global_icon_shape_border_color_hover ) ),
						)
					);
				}
				if ( '' !== $global_icon_shape_border_fill_color ) {
					ET_Builder_Element::set_style(
						$render_slug,
						array(
							'selector'    => '%%order_class%% .dipl_item_icon.dipl_icon_fill',
							'declaration' => sprintf( 'border-color: %1$s;', esc_html( $global_icon_shape_border_fill_color ) ),
						)
					);
				}
				if ( isset( $global_icon_shape_border_fill_color_hover ) ) {
					ET_Builder_Element::set_style(
						$render_slug,
						array(
							'selector'    => '%%order_class%% .dipl_item_icon.dipl_icon_fill:hover',
							'declaration' => sprintf( 'border-color: %1$s;', esc_html( $global_icon_shape_border_fill_color_hover ) ),
						)
					);
				}
				if ( '' !== $global_icon_shape_border_size ) {
					ET_Builder_Element::set_style(
						$render_slug,
						array(
							'selector'    => '%%order_class%% .dipl_item_icon',
							'declaration' => sprintf( 'border-width: %1$s;', esc_html( $global_icon_shape_border_size ) ),
						)
					);
				}
			}
		}

		if ( '' !== $timeline_bar_size ) {
			ET_Builder_Element::set_style(
				$render_slug,
				array(
					'selector'    => '%%order_class%% .dipl_stem_wrapper',
					'declaration' => sprintf( 'width: %1$s !important;', esc_html( $timeline_bar_size ) ),
				)
			);

			if ( '' !== $timeline_bar_color ) {
				ET_Builder_Element::set_style(
					$render_slug,
					array(
						'selector'    => '%%order_class%% .dipl_stem_wrapper',
						'declaration' => sprintf( 'background: %1$s !important;', esc_html( $timeline_bar_color ) ),
					)
				);
			}

			if ( '' !== $timeline_bar_fill_color ) {
				ET_Builder_Element::set_style(
					$render_slug,
					array(
						'selector'    => '%%order_class%% .dipl_stem',
						'declaration' => sprintf( 'background: %1$s !important;', esc_html( $timeline_bar_fill_color ) ),
					)
				);
			}
		}

		if ( 'dipl_date_tree' === $select_timeline_layout ) {
			if ( '' !== $timeline_bar_shape_border_size ) {
				ET_Builder_Element::set_style(
					$render_slug,
					array(
						'selector'    => '%%order_class%% .dipl_time_wrapper',
						'declaration' => sprintf( 'border-width: %1$s;', esc_html( $timeline_bar_shape_border_size ) ),
					)
				);
			}

			if ( '' !== $timeline_bar_shape_color ) {
				ET_Builder_Element::set_style(
					$render_slug,
					array(
						'selector'    => '%%order_class%% .dipl_time_wrapper',
						'declaration' => sprintf( 'background-color: %1$s !important;', esc_html( $timeline_bar_shape_color ) ),
					)
				);
			}

			if ( '' !== $timeline_bar_shape_fill_color ) {
				ET_Builder_Element::set_style(
					$render_slug,
					array(
						'selector'    => '%%order_class%% .dipl_time_wrapper.dipl_border_fill',
						'declaration' => sprintf( 'background-color: %1$s !important;', esc_html( $timeline_bar_shape_fill_color ) ),
					)
				);
			}

			if ( '' !== $timeline_bar_shape_border_color ) {
				ET_Builder_Element::set_style(
					$render_slug,
					array(
						'selector'    => '%%order_class%% .dipl_time_wrapper',
						'declaration' => sprintf( 'border-color: %1$s;', esc_html( $timeline_bar_shape_border_color ) ),
					)
				);
			}

			if ( '' !== $timeline_bar_shape_border_fill_color ) {
				ET_Builder_Element::set_style(
					$render_slug,
					array(
						'selector'    => '%%order_class%% .dipl_time_wrapper.dipl_border_fill',
						'declaration' => sprintf( 'border-color: %1$s;', esc_html( $timeline_bar_shape_border_fill_color ) ),
					)
				);
			}
		}

		$args = array(
			'render_slug'	=> $render_slug,
			'props'			=> $this->props,
			'fields'		=> $this->fields_unprocessed,
			'module'		=> $this,
			'backgrounds' 	=> array(
				'global_timeline_background' => array(
					'normal' => "{$this->main_css_element} .dipl_item_content_inner",
					'hover' => "{$this->main_css_element} .dipl_item_content_inner:hover",
	 			),
			),
		);

		DiviPlusHelper::process_background( $args );
		$fields = array( 'timeline_padding' );
		DiviPlusHelper::process_advanced_margin_padding_css( $this, $render_slug, $this->margin_padding, $fields );


		return sprintf(
			'<div class="%2$s"><div class="dipl_timeline_wrapper">
                            %1$s
                        </div><div class="%2$s_stem %3$s_stem"><div class="dipl_stem_wrapper"><div class="dipl_stem"></div></div></div></div>',
			$this->content,
			$select_timeline_layout_option,
			$select_timeline_layout
		);
	}

	public function add_new_child_text() {
		return esc_html__( 'Add New Timeline Item', 'divi-plus' );
	}
}
$plugin_options = get_option( ELICUS_DIVI_PLUS_OPTION );
if ( isset( $plugin_options['dipl-modules'] ) ) {
	$modules = explode( ',', $plugin_options['dipl-modules'] );
	if ( in_array( 'dipl_timeline', $modules ) ) {
		new DIPL_Timeline();
	}
} else {
	new DIPL_Timeline();
}
