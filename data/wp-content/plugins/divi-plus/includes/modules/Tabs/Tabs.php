<?php
/**
 * @author      Elicus <hello@elicus.com>
 * @link        https://www.elicus.com/
 * @copyright   2022 Elicus Technologies Private Limited
 * @version     1.9.6
 */
class DIPL_Tabs extends ET_Builder_Module {

	public $slug       = 'dipl_tabs';
    public $child_slug = 'dipl_tabs_item';
    public $vb_support = 'on';

	protected $module_credits = array(
		'module_uri' => 'https://diviextended.com/product/divi-plus/',
		'author'     => 'Elicus',
		'author_uri' => 'https://elicus.com/',
	);

	public function init() {
		$this->name 			= esc_html__( 'DP Tabs', 'divi-plus' );
		$this->child_item_text  = esc_html__( 'Items', 'divi-plus' );
		$this->main_css_element = '%%order_class%%';
	} 
	
	public function get_settings_modal_toggles() {
		return array(
			'general'  => array(
                'toggles' => array(
					'main_content'   => array(
						'title'    => esc_html__( 'Configuration', 'divi-plus' ),
						'priority' => 1,
					),
                ),
            ),
			'advanced'   => array(
				'toggles' => array(
					'icon_setting'   => array(
						'title'    => esc_html__( 'Icon/Image', 'divi-plus' ),
						'priority' => 1,
					),
					'alignment'   => array(
						'title'    => esc_html__( 'Alignment', 'divi-plus' ),
						'priority' => 2,
					),
					'title_settings'   => array(
						'title'    => esc_html__( 'Tab Title', 'divi-plus' ),
						'priority' => 4,
						'tabbed_subtoggles' => true,
						'sub_toggles'       => array(
							'title' => array(
								'name' => 'Title',
							),
							'active_title' => array(
								'name' => 'Active Title',
							),
						),
					),
					'body'           => array(
						'title'             => esc_html__( 'Body', 'divi-plus' ),
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
				),
			),
		);
	}
	
	public function get_advanced_fields_config() {
		return array(
			'fonts'          => array(
				'title'  => array(
					'label'           => esc_html__( 'Tab', 'divi-plus' ),
					'options_priority' => array(
						'title_text_color' => 2,
					),
					'font_size'      => array(
						'default' => '18px',
						'range_settings' => array(
							'min'  => '1',
							'max'  => '100',
							'step' => '1',
						),
						'validate_unit' => true,
					),
					'line_height'     => array(
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
						'validate_unit' => true,
					),
					'css'             => array(
						'main'      => "{$this->main_css_element} .dipl_tab_title",
						'hover'     => "{$this->main_css_element} .dipl_tabs_item_title:hover .dipl_tab_title",
						'important' => 'all',
					),
					'hide_text_align' => true,
					'tab_slug'       => 'advanced',
                    'toggle_slug'    => 'title_settings',
					'sub_toggle'     => 'title',
				),
				'active_title'  => array(
					'label'           => esc_html__( 'Active Tab', 'divi-plus' ),
					'options_priority' => array(
						'active_title_text_color' => 2,
					),
					'font_size'      => array(
						'default' => '18px',
						'range_settings' => array(
							'min'  => '1',
							'max'  => '100',
							'step' => '1',
						),
						'validate_unit' => true,
					),
					'line_height'     => array(
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
						'validate_unit' => true,
					),
					'css'             => array(
						'main'      => "{$this->main_css_element} .dipl_active_tab .dipl_tab_title",
						'hover'     => "{$this->main_css_element} .dipl_active_tab:hover .dipl_tab_title",
						'important' => 'all',
					),
					'hide_text_align' => true,
					'tab_slug'       => 'advanced',
					'toggle_slug'    => 'title_settings',
					'sub_toggle'     => 'active_title',
				),
				'body_text'       => array(
					'label'          => esc_html__( 'Body', 'divi-plus' ),
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
						'main' => "{$this->main_css_element} .dipl_tab_desc, {$this->main_css_element} .dipl_tab_desc p",
						'important' => 'all',
					),
					'tab_slug'       => 'advanced',
					'toggle_slug'    => 'body',
					'sub_toggle'     => 'p',
				),
				'body_text_link'  => array(
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
						'main' => "{$this->main_css_element} .dipl_tab_desc a",
						'important' => 'all',
					),
					'tab_slug'       => 'advanced',
					'toggle_slug'    => 'body',
					'sub_toggle'     => 'a',
				),
				'body_text_ul'    => array(
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
						'main' => "{$this->main_css_element} .dipl_tab_desc ul li",
						'important' => 'all',
					),
					'tab_slug'       => 'advanced',
					'toggle_slug'    => 'body',
					'sub_toggle'     => 'ul',
				),
				'body_text_ol'    => array(
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
						'main' => "{$this->main_css_element} .dipl_tab_desc ol li",
						'important' => 'all',
					),
					'tab_slug'       => 'advanced',
					'toggle_slug'    => 'body',
					'sub_toggle'     => 'ol',
				),
				'body_text_quote' => array(
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
						'main' => "{$this->main_css_element} .dipl_tab_desc blockquote",
						'important' => 'all',
					),
					'tab_slug'       => 'advanced',
					'toggle_slug'    => 'body',
					'sub_toggle'     => 'quote',
				),
			),
			'max_width'      => array(
				'css' => array(
					'main'             => "{$this->main_css_element}",
					'module_alignment' => "{$this->main_css_element}",
				),
			),
			'borders' => array(
				'default' => array(
					'css' => array(
						'main' => array(
							'border_styles' => "{$this->main_css_element}",
							'border_radii'  => "{$this->main_css_element}",
						),
					),
				),
			),
			'box_shadow'     => array(
				'default' => array(
					'css' => array(
						'main'    => "{$this->main_css_element}",
					),
				),
			),
			'margin_padding' => array(
				'css'            => array(
                    'margin'    => "{$this->main_css_element}",
                    'padding'   => "{$this->main_css_element}",
					'important' => 'all',
				),
			),
			'text_shadow' => false,
			'background' => array(
				'use_background_video' => false,
			),
			'text'           => false,
			'link_options'   => false,
		);
	}

	public function get_fields() {
		
		$et_accent_color = et_builder_accent_color();

		$fields = array(
            'tab_trigger' => array(
                'label'             => esc_html__( 'Tab Trigger', 'divi-plus' ),
                'type'              => 'select',
                'option_category'   => 'basic_option',
                'options'           => array(
                    'hover' => esc_html__( 'Hover', 'divi-plus' ),
                    'click' => esc_html__( 'Click', 'divi-plus' ),
                ),
                'default'           => 'hover',
                'default_on_front'  => 'hover',
                'tab_slug'          => 'general',
                'toggle_slug'       => 'main_content',
                'description'       => esc_html__( 'Here you can select the tab trigger.', 'divi-plus' ),
            ),
			'tab_orientation' => array(
                'label'             => esc_html__( 'Tab Orientation', 'divi-plus' ),
                'type'              => 'select',
                'option_category'   => 'basic_option',
                'options'           => array(
                    'horizontal' => esc_html__( 'Horizontal', 'divi-plus' ),
                    'vertical' => esc_html__( 'Vertical', 'divi-plus' ),
                ),
                'default'           => 'horizontal',
                'default_on_front'  => 'horizontal',
                'tab_slug'          => 'general',
                'toggle_slug'       => 'main_content',
                'description'       => esc_html__( 'Here you can select the tab orientation.', 'divi-plus' ),
            ),
            'horizontal_tab_alignment' => array(
                'label'             => esc_html__( 'Horizontal Tab Alignment', 'divi-plus' ),
                'type'              => 'select',
                'option_category'   => 'configuration',
                'options'           => array(
                    'top'      => esc_html__( 'Top', 'divi-plus' ),
                    'bottom'     => esc_html__( 'Bottom', 'divi-plus' ),
                ),
				'show_if'          => array(
					'tab_orientation' => 'horizontal',
				),
                'default'           => 'top',
                'default_on_front'  => 'top',
                'tab_slug'          => 'general',
                'toggle_slug'       => 'main_content',
                'description'       => esc_html__( 'Here you can select the horizontal tab alignment.', 'divi-plus' ),
            ),
			'fullwidth_tabs'     => array(
				'label'            => esc_html__( 'Fullwidth Tabs', 'divi-plus' ),
				'description'      => esc_html__( "When enabled, this will force your tab to extend 100% of the width of the container it's in.", 'divi-plus' ),
				'type'             => 'yes_no_button',
				'option_category'  => 'layout',
				'options'          => array(
					'off' => et_builder_i18n( 'No' ),
					'on'  => et_builder_i18n( 'Yes' ),
				),
				'show_if'          => array(
					'tab_orientation' => 'horizontal',
				),
				'default_on_front' => 'off',
				'tab_slug'         => 'general',
				'toggle_slug'      => 'main_content',
			),
            'vertical_tab_alignment' => array(
                'label'             => esc_html__( 'Vertical Tab Alignment', 'divi-plus' ),
                'type'              => 'select',
                'option_category'   => 'configuration',
                'options'           => array(
                    'left'      => esc_html__( 'Left', 'divi-plus' ),
                    'right'     => esc_html__( 'Right', 'divi-plus' ),
                ),
				'show_if'          => array(
					'tab_orientation' => 'vertical',
				),
                'default'           => 'left',
                'default_on_front'  => 'left',
                'tab_slug'          => 'general',
                'toggle_slug'       => 'main_content',
                'description'       => esc_html__( 'Here you can select the vertical tab alignment.', 'divi-plus' ),
            ),
			'nav_bg_color' => array(
				'label'                 => esc_html__( 'Nav Background', 'divi-plus' ),
				'type'                  => 'background-field',
				'base_name'             => 'nav_bg',
				'context'               => 'nav_bg_color',
				'option_category'       => 'button',
				'custom_color'          => true,
				'background_fields'     => $this->generate_background_options( 'nav_bg', 'button', 'general', 'background', 'nav_bg_color' ),
				'hover'                 => 'tabs',
				'tab_slug'              => 'general',
				'toggle_slug'           => 'background',
				'description'           => esc_html__( 'Customize the background style of the product by adjusting the background color, gradient, and image.', 'divi-plus' ),
			),
			'content_bg_color' => array(
				'label'                 => esc_html__( 'Content Background', 'divi-plus' ),
				'type'                  => 'background-field',
				'base_name'             => 'content_bg',
				'context'               => 'content_bg_color',
				'option_category'       => 'button',
				'custom_color'          => true,
				'background_fields'     => $this->generate_background_options( 'content_bg', 'button', 'general', 'background', 'content_bg_color' ),
				'hover'                 => 'tabs',
				'tab_slug'              => 'general',
				'toggle_slug'           => 'background',
				'description'           => esc_html__( 'Customize the background style of the product by adjusting the background color, gradient, and image.', 'divi-plus' ),
			),
			'icon_color' => array(
				'label'          	=> esc_html__( 'Icon Color', 'divi-plus' ),
				'type'            	=> 'color-alpha',
				'hover'           	=> 'tabs',
				'mobile_options'  	=> true,
				'tab_slug'        	=> 'advanced',
				'toggle_slug'     	=> 'icon_setting',
				'description'     	=> esc_html__( 'Here you can define a custom color for your icon.', 'divi-plus' ),
			),
			'active_tab_icon_color'     => array(
				'label'          => esc_html__( 'Active Tab Icon Color', 'divi-plus' ),
				'type'           => 'color-alpha',
				'custom_color'   => true,
				'hover'          => 'tabs',
				'mobile_options' => true,
				'tab_slug'       => 'advanced',
				'toggle_slug'    => 'icon_setting',
				'description'    => esc_html__( 'Pick a color to use for tab icon within active tabs. You can assign a unique color to active tab icon to differentiate them from inactive tab icons.', 'divi-plus' ),
			),
            'icon_font_size' => array(
				'label'            => esc_html__( 'Icon Font Size', 'divi-plus' ),
				'type'             => 'range',
				'option_category'  => 'font_option',
				'range_settings'   => array(
					'min'  => '1',
					'max'  => '120',
					'step' => '1',
				),
				'mobile_options'   => true,
				'default'          => '28px',
				'default_on_front' => '28px',
				'tab_slug'         => 'advanced',
				'toggle_slug'      => 'icon_setting',
				'description'      => esc_html__( 'Control the size of the icon by increasing or decreasing the font size.', 'divi-plus' ),
			),
			'active_tab_background_color'   => array(
				'label'          => esc_html__( 'Active Tab Background Color', 'divi-plus' ),
				'type'           => 'color-alpha',
				'custom_color'   => true,
				'hover'          => 'tabs',
				'mobile_options' => true,
				'default'        => '#dddddd',
				'tab_slug'       => 'advanced',
				'toggle_slug'    => 'title_settings',
				'sub_toggle'     => 'active_title',
				'priority' 		 => 1,
				'description'    => esc_html__( 'Pick a color to be used for active tab backgrounds. You can assign a unique color to active tabs to differentiate them from inactive tabs.', 'divi-plus' ),
			),
			'tab_alignment' => array(
                'label'             => esc_html__( 'Tab Text Alignment', 'divi-plus' ),
                'type'              => 'select',
                'option_category'   => 'configuration',
                'options'           => array(
                    'left'      => esc_html__( 'Left', 'divi-plus' ),
                    'center'     => esc_html__( 'Center', 'divi-plus' ),
					'right'    => esc_html__( 'Right', 'divi-plus' ),
                ),
				'show_if'          => array(
					'tab_orientation' => 'vertical',
				),
				'mobile_options' 	=> true,
                'default'           => 'center',
                'default_on_front'  => 'center',
                'tab_slug'          => 'advanced',
                'toggle_slug'       => 'tab_alignment',
                'description'       => esc_html__( 'Here you can select the tab content alignment.', 'divi-plus' ),
            ),
            'icon_alignment' => array(
                'label'             => esc_html__( 'Icon/Image Alignment', 'divi-plus' ),
                'type'              => 'select',
                'option_category'   => 'configuration',
                'options'           => array(
					'top'       => esc_html__( 'Top', 'divi-plus' ),
                    'left'      => esc_html__( 'Left', 'divi-plus' ),
                    'right'     => esc_html__( 'Right', 'divi-plus' ),
					'bottom'    => esc_html__( 'Bottom', 'divi-plus' ),
                ),
				'default'           => 'right',
				'default_on_front'  => 'right',
                'tab_slug'          => 'advanced',
                'toggle_slug'       => 'tab_alignment',
                'description'       => esc_html__( 'Here you can select the icon/image alignment.', 'divi-plus' ),
            ),
			'tab_background_color' => array(
				'label'          => esc_html__( 'Tab Background Color', 'divi-plus' ),
				'type'           => 'color-alpha',
				'custom_color'   => true,
				'hover'          => 'tabs',
				'mobile_options' => true,
				'default'        => '#eeeeee',
				'default_on_front'  => '#eeeeee',
				'tab_slug'       => 'advanced',
				'toggle_slug'    => 'title_settings',
				'sub_toggle'     => 'title',
				'priority' 		 => 1,
				'description'    => esc_html__( 'Pick a color to be used for inactive tab backgrounds. You can assign a unique color to inactive tabs to differentiate them from active tabs.', 'divi-plus' ),
			),
			'tab_max_width' => array(
				'label'                 => esc_html__( 'Tab Width', 'divi-plus' ),
				'type'                  => 'range',
				'option_category'       => 'layout',
				'mobile_options'        => true,
				'validate_unit'         => true,
				'allow_empty'           => true,
				'range_settings'        => array(
					'min'   => '0',
					'max'   => '1100',
					'step'  => '1',
				),
				'show_if'          => array(
					'tab_orientation' => 'vertical',
				),
				'default' 				=> '150px',
				'default_on_front'  	=> '150px',
				'tab_slug'              => 'advanced',
				'toggle_slug'           => 'width',
				'description'           => esc_html__( 'Here you can set the width of tab.', 'divi-plus' ),
			),
			'image_width'     => array(
				'label'            => esc_html__( 'Image Width', 'divi-plus' ),
				'type'             => 'range',
				'option_category'  => 'layout',
				'range_settings'   => array(
					'min'  => '32',
					'max'  => '500',
					'step' => '1',
				),
				'mobile_options'   => true,
				'default'          => '32px',
				'tab_slug'         => 'advanced',
				'toggle_slug'      => 'width',
				'description'      => esc_html__( 'Adjust the width of the image in the tab titles.', 'divi-plus' ),
			),
		);

		return array_merge(
			$fields,		
			$this->generate_background_options( 'nav_bg', 'skip', 'general', 'background', 'nav_bg_color' ),
			$this->generate_background_options( 'content_bg', 'skip', 'general', 'background', 'content_bg_color' )
		);
		
	}
	
	// Get Tabs Title
	public function before_render() {
		global $dipl_tabs_item_titles, $dipl_tabs_item_order_class, $dipl_tabs_item_icon_alignment;
		$dipl_tabs_item_titles			= array();
		$dipl_tabs_item_order_class 	= array();
	}
	
	// Render Output
	public function render( $attrs, $content, $render_slug ) {
		global $dipl_tabs_item_titles, $dipl_tabs_item_order_class, $dipl_tabs_item_icon_alignment;

		$tab_trigger        			= esc_attr( $this->props['tab_trigger'] );
		$tab_orientation        		= esc_attr( $this->props['tab_orientation'] );
		$horizontal_tab_alignment 		= esc_attr( $this->props['horizontal_tab_alignment'] );
		$fullwidth_tabs 				= esc_attr( $this->props['fullwidth_tabs'] );
		$vertical_tab_alignment 		= esc_attr( $this->props['vertical_tab_alignment'] );
		$fullwidth_tabs 				= esc_attr( $this->props['fullwidth_tabs'] );
		$tab_max_width 					= esc_attr( $this->props['tab_max_width'] );
		$image_width                	= esc_attr( $this->props['image_width'] );
		$icon_alignment 				= esc_attr( $this->props['icon_alignment'] );
		$tab_alignment					= et_pb_responsive_options()->get_property_values( $this->props,'tab_alignment' );
		
		wp_enqueue_script( 'dipl-tabs-custom', PLUGIN_PATH . 'includes/modules/Tabs/dipl-tabs-custom.min.js', array('jquery'), '1.0.1', true );
		$file = et_is_builder_plugin_active() ? 'style-dbp' : 'style';
        wp_enqueue_style( 'dipl-tabs-style', PLUGIN_PATH . 'includes/modules/Tabs/' . $file . '.min.css', array(), '1.0.0' );

		if ( 'horizontal' === $tab_orientation ) {
			self::set_style(
				$render_slug,
				array(
					'selector'    => '%%order_class%% .dipl_tab_wrapper',
					'declaration' => 'flex-direction: column;',
				)
			);
			
			self::set_style(
				$render_slug,
				array(
					'selector'    => '%%order_class%% .dipl_tab_wrapper .dipl_tabs_controls',
					'declaration' => 'justify-content: center; width: 100%;',
				)
			);
			
			if ( 'bottom' === $horizontal_tab_alignment ) {
				self::set_style(
					$render_slug,
					array(
						'selector'    => '%%order_class%% .dipl_tab_wrapper',
						'declaration' => 'flex-direction: column-reverse;',
					)
				);
			}

			if ( 'on' === $fullwidth_tabs ) {
				self::set_style(
					$render_slug,
					array(
						'selector'    => '%%order_class%% .dipl_tab_wrapper .dipl_tabs_item_title',
						'declaration' => 'flex-grow: 1;',
					)
				);
			}
			
		}

		//Icon Alignment
		if ( 'right' === $icon_alignment ) {
			self::set_style(
				$render_slug,
				array(
					'selector'    => "{$this->main_css_element} .dipl_tabs_item_title .dipl_tab_icon, {$this->main_css_element} .dipl_tabs_item_title img",
					'declaration' => 'margin-left: 10px;'
				)
			);
		}
		
		
		if ( 'left' === $icon_alignment ) {
			self::set_style(
				$render_slug,
				array(
					'selector'    => "{$this->main_css_element} .dipl_tabs_item_title .dipl_tab_icon, {$this->main_css_element} .dipl_tabs_item_title img",
					'declaration' => 'margin-right: 10px; order: -1;'
				)
			);
		}

		if ( 'top' === $icon_alignment ) {
			self::set_style(
				$render_slug,
				array(
					'selector'    => "{$this->main_css_element} .dipl_tabs_item_title_inner_wrap",
					'declaration' => 'flex-direction: column-reverse;'
				)
			);
			self::set_style(
				$render_slug,
				array(
					'selector'    => "{$this->main_css_element} .dipl_tabs_item_title .dipl_tab_icon, {$this->main_css_element} .dipl_tabs_item_title img",
					'declaration' => 'margin-bottom: 10px;'
				)
			);
		}

		if ( 'bottom' === $icon_alignment ) {
			self::set_style(
				$render_slug,
				array(
					'selector'    => "{$this->main_css_element} .dipl_tabs_item_title_inner_wrap",
					'declaration' => 'flex-direction: column;'
				)
			);
			self::set_style(
				$render_slug,
				array(
					'selector'    => "{$this->main_css_element} .dipl_tabs_item_title .dipl_tab_icon, {$this->main_css_element} .dipl_tabs_item_title img",
					'declaration' => 'margin-top: 10px;'
				)
			);
		}
		
		if ( 'vertical' === $tab_orientation ) {		
			self::set_style(
				$render_slug,
				array(
					'selector'    => '%%order_class%% .dipl_tab_wrapper .dipl_tabs_controls',
					'declaration' => 'flex-direction: column;',
				)
			); 
			
			self::set_style(
				$render_slug, array(
					'selector'    => '%%order_class%% .dipl_tab_wrapper',
					'declaration' => 'flex-direction: column;',
					'media_query' => self::get_media_query( 'max_width_767' ),
				)
			);
			
			if ( 'right' === $vertical_tab_alignment ) {
				self::set_style(
					$render_slug,
					array(
						'selector'    => '%%order_class%% .dipl_tab_wrapper',
						'declaration' => 'flex-direction: row-reverse;',
					)
				);
			}

			// Tabs Max Width
			if ( '' !== $tab_max_width ) {
				$this->generate_styles(
					array(
						'base_attr_name' => 'tab_max_width',
						'selector'       => "{$this->main_css_element} .dipl_tabs_item_title",
						'important'		 => true,
						'css_property'   => 'width',
						'render_slug'    => $render_slug,
						'type'           => 'range',
					)
				);
		
			}

			foreach( $tab_alignment as &$align ) {
				$align = str_replace( array( 'left', 'center', 'right' ), array( 'flex-start', 'center', 'flex-end' ), $align);
			}
		
			if ( ! empty( array_filter( $tab_alignment ) ) ) {
				$align_property = in_array( $icon_alignment, array( 'top', 'bottom' ), true ) ? 'align-items' : 'justify-content';
				$tab_alignment_selector	= "{$this->main_css_element} .dipl_tabs_controls .dipl_tabs_item_title_inner_wrap";
				et_pb_responsive_options()->generate_responsive_css( $tab_alignment, $tab_alignment_selector, $align_property, $render_slug, '!important;', 'type' );
			}
		}
		
		// Inactive Tab Background Color
		$this->generate_styles(
			array(
				'base_attr_name' => 'tab_background_color',
				'selector'       => '%%order_class%% .dipl_tabs_item_title:not(.dipl_active_tab)',
				'hover_selector' => '%%order_class%% .dipl_tabs_item_title:not(.dipl_active_tab):hover',
				'css_property'   => 'background-color',
				'important'		 => true,
				'priority'		 => 1000,
				'render_slug'    => $render_slug,
				'type'           => 'color',
			)
		);

		// Active Tab Background Color
		$this->generate_styles(
			array(
				'base_attr_name' => 'active_tab_background_color',
				'selector'       => '%%order_class%% .dipl_tabs_item_title.dipl_active_tab',
				'hover_selector' => '%%order_class%% .dipl_tabs_item_title.dipl_active_tab:hover',
				'css_property'   => 'background-color',
				'important'		 => true,
				'priority'		 => 999,
				'render_slug'    => $render_slug,
				'type'           => 'color',
			)
		);

		//Image Max Width
		if ( '' !== $image_width ) {
			$this->generate_styles(
				array(
					'base_attr_name' => 'image_width',
					'selector'       => "{$this->main_css_element} .dipl_tabs_item_title img",
					'css_property'   => 'width',
					'important'		 => true,
					'render_slug'    => $render_slug,
					'type'           => 'range',
				)
			);
		}

		// Icon Color.
		$this->generate_styles(
			array(
				'base_attr_name' => 'icon_color',
				'selector'       => "{$this->main_css_element} .dipl_tabs_item_title:not(.dipl_active_tab) .dipl_tab_icon",
				'hover_selector' => "{$this->main_css_element} .dipl_tabs_item_title:not(.dipl_active_tab):hover .dipl_tab_icon",
				'css_property'   => 'color',
				'important'		 => true,
				'render_slug'    => $render_slug,
				'type'           => 'color',
			)
		);
			
		$this->generate_styles(
			array(
				'base_attr_name' => 'active_tab_icon_color',
				'selector'       => "{$this->main_css_element} .dipl_tabs_item_title.dipl_active_tab .dipl_tab_icon",
				'hover_selector' => "{$this->main_css_element} .dipl_tabs_item_title.dipl_active_tab:hover .dipl_tab_icon",
				'css_property'   => 'color',
				'important'		 => true,
				'render_slug'    => $render_slug,
				'type'           => 'color',
			)
		);
		
		$this->generate_styles(
			array(
				'base_attr_name' => 'icon_font_size',
				'selector'       => "{$this->main_css_element} .dipl_tab_icon",
				'css_property'   => 'font-size',
				'important'		 => true,
				'render_slug'    => $render_slug,
				'type'           => 'range',
			)
		);

		$all_tabs_content = $this->content;

		$tabs = '';
		if ( ! empty( $dipl_tabs_item_titles ) ) {
			$i = 0;
			foreach ( $dipl_tabs_item_titles as $key => $titles ) {
				$tabs .= sprintf(
					'<div class="%2$s_title dipl_tabs_item_title%1$s" aria-controls="tab-%4$s">
						<div class="dipl_tabs_item_title_inner_wrap">
							%3$s
						</div>
					</div>',
					( 0 === $i ? ' dipl_active_tab' : '' ),
					esc_attr( ltrim( $dipl_tabs_item_order_class[ $i ] ) ),
					$titles,
					intval( $key ) + 1
				);
				++$i;
			}
		}

		// Module Output
		$output = sprintf(
			'<div class="dipl_tab_wrapper" data-trigger="%3$s">				
				<div class="dipl_tabs_controls">
					%1$s
				</div>
				<div class="dipl_tabs_content">
					%2$s
				</div>
			</div>',
			et_core_intentionally_unescaped( $tabs, 'html' ),
			et_core_intentionally_unescaped( $all_tabs_content, 'html' ),
			esc_attr( $tab_trigger )
		);
		
		$args = array(
			'render_slug'	=> $render_slug,
			'props'			=> $this->props,
			'fields'		=> $this->fields_unprocessed,
			'module'		=> $this,
			'backgrounds' 	=> array(
				'nav_bg' => array(
					'normal' => "{$this->main_css_element} .dipl_tabs_controls",
					'hover' => "{$this->main_css_element} .dipl_tabs_controls:hover",
	 			),
	 			'content_bg' => array(
	 				'normal' => "{$this->main_css_element} .dipl_tabs_content",
	 				'hover' => "{$this->main_css_element} .dipl_tabs_content:hover",
	 			),
			),
		);

		DiviPlusHelper::process_background( $args );

		return et_core_intentionally_unescaped( $output, 'html' );
					
	}
	
}

$plugin_options = get_option( ELICUS_DIVI_PLUS_OPTION );
if ( isset( $plugin_options['dipl-modules'] ) ) {
	$modules = explode( ',', $plugin_options['dipl-modules'] );
	if ( in_array( 'dipl_tabs', $modules, true ) ) {
		new DIPL_Tabs();
	}
} else {
	new DIPL_Tabs();
}