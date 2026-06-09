<?php
/**
 * @author      Elicus <hello@elicus.com>
 * @link        https://www.elicus.com/
 * @copyright   2022 Elicus Technologies Private Limited
 * @version     1.9.6
 */
class DIPL_ImageAccordionItem extends ET_Builder_Module {

	public $slug           = 'dipl_image_accordion_item';
    public $type           = 'child';
    public $vb_support     = 'on';

	protected $module_credits = array(
		'module_uri' => 'https://diviextended.com/product/divi-plus/',
		'author'     => 'Elicus',
		'author_uri' => 'https://elicus.com/',
	);

	public function init() {
		$this->name 						= esc_html__( 'DP Image Accordion Item', 'divi-plus' );
		$this->advanced_setting_title_text  = esc_html__( 'Image Accordion Item', 'divi-plus' );
        $this->child_title_var              = 'title';
		$this->main_css_element 			= '.dipl_image_accordion %%order_class%%';
		if ( is_archive() ) {
            $this->main_css_element = '%%order_class%%';
        }
	}
	
	public function get_settings_modal_toggles() {
		return array(
			'general' => array(
				'toggles' => array(
					'main_content' 	=> array(
                        'title' => esc_html__( 'Accordion', 'divi-plus' ),
                        'tabbed_subtoggles' => true,
                        'bb_icons_support'  => true,
                        'sub_toggles'       => array(
                            'accordion'     => array(
                                'name' => 'Accordion',
                            ),
                            'active_accordion' => array(
                                'name' => 'Active Accordion',
                            ),
                        ),
                    ),
					'content_box' => esc_html__( 'Content', 'divi-plus' ),
				),
			),
			'advanced' => array(
				'toggles' => array(
					'text' 		=> esc_html__( 'Text', 'divi-plus' ),
					'title' 	=> esc_html__( 'Title', 'divi-plus' ),
					'desc' 		=> array(
                        'title' => esc_html__( 'Description', 'divi-plus' ),
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
					'icon_settings' => esc_html__( 'Icon', 'divi-plus' ),
					'button' 		=> esc_html__( 'Button', 'divi-plus' ),
				),
			),
		);
	}
	
	public function get_advanced_fields_config() {
		return array(
			'fonts' => array(
				'title' => array(
					'label'          => esc_html__( 'Title', 'divi-plus' ),
					'font_size'      => array(
						'default' => '18px',
						'range_settings' => array(
							'min'  => '1',
							'max'  => '100',
							'step' => '1',
						),
						'validate_unit' => true,
					),
					'line_height' => array(
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
					'header_level' => array(
						'default' => 'h4',
					),
					'css'            => array(
						'main'       => "{$this->main_css_element} .dipl_image_accordion_item_title",
						'hover'      => "{$this->main_css_element} .dipl_image_accordion_item_title:hover",
						'important'	 => 'all',
					),
                    'tab_slug'       => 'advanced',
                    'toggle_slug'    => 'title',
				),
				'desc_text' => array(
                    'label'     => esc_html__( 'Description', 'divi-plus' ),
                    'font_size' => array(
                        'default'           => '14px',
                        'range_settings'    => array(
                            'min'   => '1',
                            'max'   => '100',
                            'step'  => '1',
                        ),
                        'validate_unit'     => true,
                    ),
                    'line_height' => array(
                        'default'           => '1.3',
                        'range_settings'    => array(
                            'min'   => '0.1',
                            'max'   => '10',
                            'step'  => '0.1',
                        ),
                    ),
                    'letter_spacing' => array(
                        'default'           => '0px',
                        'range_settings'    => array(
                            'min'   => '0',
                            'max'   => '10',
                            'step'  => '1',
                        ),
                        'validate_unit'     => true,
                    ),
                    'css' => array(
                        'main' => "{$this->main_css_element} .dipl_image_accordion_item_desc, {$this->main_css_element} .dipl_image_accordion_item_desc p",
                        'important' => 'all',
                    ),
                    'tab_slug'    => 'advanced',
                    'toggle_slug' => 'desc',
                    'sub_toggle'  => 'p',
                ),
                'desc_link' => array(
                    'label'     => esc_html__( 'Description Link', 'divi-plus' ),
                    'font_size' => array(
                        'default'           => '14px',
                        'range_settings'    => array(
                            'min'   => '1',
                            'max'   => '100',
                            'step'  => '1',
                        ),
                        'validate_unit'     => true,
                    ),
                    'line_height' => array(
                        'default'           => '1.3',
                        'range_settings'    => array(
                            'min'   => '0.1',
                            'max'   => '10',
                            'step'  => '0.1',
                        ),
                    ),
                    'letter_spacing' => array(
                        'default'           => '0px',
                        'range_settings'    => array(
                            'min'   => '0',
                            'max'   => '10',
                            'step'  => '1',
                        ),
                        'validate_unit'     => true,
                    ),
                    'css' => array(
                        'main' => "{$this->main_css_element} .dipl_image_accordion_item_desc a",
                        'important' => 'all',
                    ),
                    'tab_slug'    => 'advanced',
                    'toggle_slug' => 'desc',
                    'sub_toggle'  => 'a',
                ),
                'desc_ul' => array(
                    'label'     => esc_html__( 'Description Unordered List', 'divi-plus' ),
                    'font_size' => array(
                        'default'           => '14px',
                        'range_settings'    => array(
                            'min'   => '1',
                            'max'   => '100',
                            'step'  => '1',
                        ),
                        'validate_unit'     => true,
                    ),
                    'line_height' => array(
                        'default'           => '1.3',
                        'range_settings'    => array(
                            'min'   => '0.1',
                            'max'   => '10',
                            'step'  => '0.1',
                        ),
                    ),
                    'letter_spacing' => array(
                        'default'           => '0px',
                        'range_settings'    => array(
                            'min'   => '0',
                            'max'   => '10',
                            'step'  => '1',
                        ),
                        'validate_unit'     => true,
                    ),
                    'css' => array(
                        'main' => "{$this->main_css_element} .dipl_image_accordion_item_desc ul li",
                        'important' => 'all',
                    ),
                    'tab_slug'    => 'advanced',
                    'toggle_slug' => 'desc',
                    'sub_toggle'  => 'ul',
                ),
                'desc_ol' => array(
                    'label'     => esc_html__( 'Description Ordered List', 'divi-plus' ),
                    'font_size' => array(
                        'default'           => '14px',
                        'range_settings'    => array(
                            'min'   => '1',
                            'max'   => '100',
                            'step'  => '1',
                        ),
                        'validate_unit'     => true,
                    ),
                    'line_height' => array(
                        'default'           => '1.3',
                        'range_settings'    => array(
                            'min'   => '0.1',
                            'max'   => '10',
                            'step'  => '0.1',
                        ),
                    ),
                    'letter_spacing' => array(
                        'default'           => '0px',
                        'range_settings'    => array(
                            'min'   => '0',
                            'max'   => '10',
                            'step'  => '1',
                        ),
                        'validate_unit'     => true,
                    ),
                    'css' => array(
                        'main' => "{$this->main_css_element} .dipl_image_accordion_item_desc ol li",
                        'important' => 'all',
                    ),
                    'tab_slug'    => 'advanced',
                    'toggle_slug' => 'desc',
                    'sub_toggle'  => 'ol',
                ),
                'desc_quote' => array(
                    'label'     => esc_html__( 'Description Blockquote', 'divi-plus' ),
                    'font_size' => array(
                        'default'           => '14px',
                        'range_settings'    => array(
                            'min'   => '1',
                            'max'   => '100',
                            'step'  => '1',
                        ),
                        'validate_unit'     => true,
                    ),
                    'line_height' => array(
                        'default'           => '1.3',
                        'range_settings'    => array(
                            'min'   => '0.1',
                            'max'   => '10',
                            'step'  => '0.1',
                        ),
                    ),
                    'letter_spacing' => array(
                        'default'           => '0px',
                        'range_settings'    => array(
                            'min'   => '0',
                            'max'   => '10',
                            'step'  => '1',
                        ),
                        'validate_unit'     => true,
                    ),
                    'css' => array(
                        'main' => "{$this->main_css_element} .dipl_image_accordion_item_desc blockquote",
                        'important' => 'all',
                    ),
                    'tab_slug'    => 'advanced',
                    'toggle_slug' => 'desc',
                    'sub_toggle'  => 'quote',
                ),
			),
			'button' => array(
			    'button' => array(
				    'label' => esc_html__( 'Button', 'divi-plus' ),
				    'css' => array(
						'main'      => "{$this->main_css_element} .et_pb_button",
						'alignment' => "{$this->main_css_element} .et_pb_button_wrapper",
						'important' => 'all',
					),
					'margin_padding'  => array(
						'css' => array(
							'margin'    => "{$this->main_css_element} .et_pb_button_wrapper",
							'padding'   => "{$this->main_css_element} .et_pb_button",
							'important' => 'all',
						),
					),
					'use_alignment'   	=> true,
					'box_shadow'      	=> false,
				    'depends_on'        => array( 'show_button' ),
		            'depends_show_if'   => 'on',
				    'tab_slug'          => 'advanced',
				    'toggle_slug'       => 'button',
			    ),
			),
			'text' => array(
				'use_background_layout' => true,
				'options'               => array(
					'background_layout' => array(
						'hover' => 'tabs',
					),
				),
			),
			'borders'        => array(
				'default' => array(
					'css' => array(
						'main' => array(
							'border_styles' => $this->main_css_element,
							'border_radii'  => $this->main_css_element,
							'important' => 'all',
						),
					),
				),
			),
            'margin_padding' => array(
                'css' => array(
                    'margin'    => $this->main_css_element,
                    'padding'   => "{$this->main_css_element} .dipl_image_accordion_item_content_wrapper",
                    'important' => 'all',
                ),
            ),
			'text_shadow' => false,
			'background' => false,
			'max_width' => false,
			'height' => false,
		);

	}

	public function get_fields() {
		
		$et_accent_color = et_builder_accent_color();
		
		$fields = array(
			'title' => array(
				'label'           => esc_html__( 'Title', 'divi-plus' ),
				'type'            => 'text',
				'option_category' => 'basic_option',
				'dynamic_content' => 'text',
				'tab_slug'        => 'general',
				'toggle_slug'     => 'content_box',
				'description'     => esc_html__( 'Here you can define the title which will appear in the active state.', 'divi-plus' ),
			),
			'content' => array(
				'label'           => esc_html__( 'Description', 'divi-plus' ),
				'type'            => 'tiny_mce',
				'option_category' => 'basic_option',
				'tab_slug'        => 'general',
				'toggle_slug'     => 'content_box',
				'description'     => esc_html__( 'Here you can define the content which will appear in the active state.', 'divi-plus' ),
			),
			'icon' => array(
				'label'    			=> esc_html__( 'Icon', 'divi-plus' ),
				'type'              => 'select_icon',
				'option_category'	=> 'basic_option',
				'class'             => array(
					'et-pb-font-icon'
				),
				'tab_slug'          => 'general',
				'toggle_slug'       => 'content_box',
				'description'       => esc_html__( 'Choose an icon to display with the accordion.', 'divi-plus' ),
			),
			'show_button' => array(
				'label'     		=> esc_html__( 'Show Button', 'divi-plus' ),
				'type'              => 'yes_no_button',
				'option_category'   => 'basic_option',
				'options'           => array(
					'off' => esc_html__( 'No', 'divi-plus' ),
					'on'  => esc_html__( 'Yes', 'divi-plus' ),
				),
				'affects' 			=> array(
					'custom_button',
				),
				'default'           => 'off',
				'tab_slug'          => 'general',
				'toggle_slug'       => 'content_box',
				'description'       => esc_html__( 'Here you can choose whether or not use the button.', 'divi-plus' ),
			),
			'button_text' => array(
				'label'    			=> esc_html__( 'Button Text', 'divi-plus' ),
				'type'              => 'text',
				'option_category'   => 'basic_option',
				'show_if'           => array(
				    'show_button' => 'on',
				),
				'default'			=> esc_html__( 'Learn More', 'divi-plus' ),
				'default_on_front'	=> esc_html__( 'Learn More', 'divi-plus' ),
				'tab_slug'          => 'general',
				'toggle_slug'       => 'content_box',
				'description'       => esc_html__( 'Here you can input the text to be used for the  Read More Button.', 'divi-plus' ),
			),
			'button_url' => array(
				'label'           	=> esc_html__( 'Button Link URL', 'divi-plus' ),
				'type'            	=> 'text',
				'option_category' 	=> 'basic_option',
				'dynamic_content' 	=> 'url',
				'tab_slug'          => 'general',
				'toggle_slug'     	=> 'link_options',
				'description'     	=> esc_html__( 'Here you can input the destination URL for the button to open when clicked.', 'divi-plus' ),
			),
			'button_url_new_window' => array(
				'label'            	=> esc_html__( 'Button Link Target', 'divi-plus' ),
				'type'             	=> 'select',
				'option_category'  	=> 'configuration',
				'options'          	=> array(
					'off' => esc_html__( 'In The Same Window', 'divi-plus' ),
					'on'  => esc_html__( 'In The New Tab', 'divi-plus' ),
				),
				'tab_slug'          => 'general',
				'toggle_slug'      	=> 'link_options',
				'description'      	=> esc_html__( 'Here you can choose whether or not your link opens in a new window', 'divi-plus' ),
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
				'tab_slug'         => 'advanced',
				'toggle_slug'      => 'icon_settings',
				'description'      => esc_html__( 'Control the size of the icon by increasing or decreasing the font size.', 'divi-plus' ),
			),
			'icon_color' => array(
				'label'          	 => esc_html__( 'Icon Color', 'divi-plus' ),
				'type'            	=> 'color-alpha',
				'hover'           	=> 'tabs',
				'mobile_options'  	=> true,
				'tab_slug'        	=> 'advanced',
				'toggle_slug'     	=> 'icon_settings',
				'description'     	=> esc_html__( 'Here you can define a custom color for your icon.', 'divi-plus' ),
			),
			'style_icon' => array(
				'label'                 => esc_html__( 'Style Icon', 'divi-plus' ),
				'type'                  => 'yes_no_button',
				'option_category'       => 'configuration',
				'options'               => array(
					'off'   => esc_html__( 'No', 'divi-plus' ),
					'on'    => esc_html__( 'Yes', 'divi-plus' )
				),
				'default'               => 'off',
				'default_on_front'      => 'off',
				'tab_slug'              => 'advanced',
				'toggle_slug'           => 'icon_settings',
				'description'           => esc_html__( 'Here you can choose whether icon should display within a shape or not.', 'divi-plus' ),
			),
			'icon_shape' => array(
				'label'                 => esc_html__( 'Shape', 'divi-plus' ),
				'type'                  => 'select',
				'option_category'       => 'configuration',
				'options'               => array(
					'dipl_icon_shape_circle'    => esc_html__( 'Circle', 'divi-plus' ),
					'dipl_icon_shape_square' 	=> esc_html__( 'Square', 'divi-plus' ),
                    'dipl_icon_shape_hexagon'   => esc_html__( 'Hexagon', 'divi-plus' ),
				),
				'show_if'               => array(
				    'style_icon' => 'on',
				),
				'default' 				=> 'dipl_icon_shape_square',
				'tab_slug'              => 'advanced',
				'toggle_slug'           => 'icon_settings',
				'description'           => esc_html__( 'Here you can choose shape to display icon within.', 'divi-plus' ),
			),
			'shape_color' => array(
				'label'                 => esc_html__( 'Shape Background', 'divi-plus' ),
				'type'                  => 'color-alpha',
				'custom_color'          => true,
				'show_if'               => array(
				    'style_icon' => 'on',
				),
				'default'               => '#000',
				'tab_slug'              => 'advanced',
				'toggle_slug'           => 'icon_settings',
				'description'           => esc_html__( 'Pick a color to be used for the icon shape.', 'divi-plus' ),
			),
			'accordion_bg_color' => array(
				'label'             => esc_html__( 'Accordion Background', 'divi-plus' ),
				'type'              => 'background-field',
				'base_name'         => 'accordion_bg',
				'context'           => 'accordion_bg_color',
				'option_category'   => 'button',
				'custom_color'      => true,
				'background_fields' => $this->generate_background_options( 'accordion_bg', 'button', 'general', 'main_content', 'accordion_bg_color' ),
				'hover'             => false,
				'tab_slug'          => 'general',
				'toggle_slug'       => 'main_content',
				'sub_toggle'		=> 'accordion',
				'description'       => esc_html__( 'Customize the background of the accordion by adjusting background color, gradient, and image.', 'divi-plus' ),
			),
			'active_accordion_bg_color' => array(
				'label'             => esc_html__( 'Active Accordion Background', 'divi-plus' ),
				'type'              => 'background-field',
				'base_name'         => 'active_accordion_bg',
				'context'           => 'active_accordion_bg_color',
				'option_category'   => 'button',
				'custom_color'      => true,
				'background_fields' => $this->generate_background_options( 'active_accordion_bg', 'button', 'general', 'main_content', 'active_accordion_bg_color' ),
				'hover'             => false,
				'tab_slug'          => 'general',
				'toggle_slug'       => 'main_content',
				'sub_toggle'		=> 'active_accordion',
				'description'       => esc_html__( 'Customize the background of the active accordion by adjusting background color, gradient, and image. Leave empty for same.', 'divi-plus' ),
			),
            'animation' => array(
                'label'             => esc_html__( 'Accordion Content Animation', 'divi-plus' ),
                'type'              => 'select',
                'option_category'   => 'configuration',
                'options'           => array(
                    'top'       => esc_html__( 'Top To Bottom', 'divi-plus' ),
                    'left'      => esc_html__( 'Left To Right', 'divi-plus' ),
                    'right'     => esc_html__( 'Right To Left', 'divi-plus' ),
                    'bottom'    => esc_html__( 'Bottom To Top', 'divi-plus' ),
                    'off'       => esc_html__( 'No Animation', 'divi-plus' ),
                ),
                'default'           => 'off',
                'default_on_front'  => 'off',
                'tab_slug'          => 'advanced',
                'toggle_slug'       => 'animation',
                'description'       => esc_html__( 'This controls the direction of the lazy-loading accordion content animation.', 'divi-plus' ),
            ),
		);
		
		return array_merge(
			$fields,
			$this->generate_background_options( 'accordion_bg', 'skip', 'general', 'main_content', 'accordion_bg_color' ),
			$this->generate_background_options( 'active_accordion_bg', 'skip', 'general', 'main_content', 'active_accordion_bg_color' )
		);
	}	

	public function render( $attrs, $content, $render_slug ) {
		global $dp_ia_parent_title_level;

		$multi_view            	= et_pb_multi_view_options( $this );
        $style_icon             = $this->props['style_icon'];
		$shape_color            = esc_attr( $this->props['shape_color'] );
		$button_text           	= esc_html__( $this->props['button_text'], 'divi-plus' );
        $animation              = $this->props['animation'];
		$title_level			= $this->props['title_level'];
		$title_level            = '' === $title_level && '' !== $dp_ia_parent_title_level ? $dp_ia_parent_title_level : $title_level;
		$processed_title_level 	= et_pb_process_header_level( $title_level, 'h4' );
		$processed_title_level 	= esc_html( $processed_title_level );

		$icon_shape 	= 'on' === $style_icon ? esc_attr( $this->props['icon_shape'] ) : '';
		$icon_classes	= implode(
			' ',
			array(
				'dipl_image_accordion_item_icon',
				'et-pb-icon',
				$icon_shape,
			)
		);

		$icon = $multi_view->render_element(
			array(
				'content'  => '{{icon}}',
				'attrs'    => array(
					'class' => $icon_classes,
				),
				'required' => 'icon',
			)
		);

        if ( 'dipl_icon_shape_hexagon' === $icon_shape ) {
            $icon = sprintf(
                '<div class="dipl_icon_hexagon_wrapper">
                    <div class="dipl_icon_hexagon_inner_wrap">
                        <div class="dipl_icon_hexagon">%1$s</div>
                    </div>
                </div>',
                $icon
            );
        }

		$title = $multi_view->render_element(
			array(
				'tag'      => $processed_title_level,
				'content'  => '{{title}}',
				'attrs'    => array(
					'class' => 'dipl_image_accordion_item_title',
				),
				'required' => 'title',
			)
		);

		$content = $multi_view->render_element(
			array(
				'tag'      => 'div',
				'content'  => '{{content}}',
				'attrs'    => array(
					'class' => 'dipl_image_accordion_item_desc',
				),
				'required' => 'content',
			)
		);

		$button = $this->render_button(
			array(
				'display_button'	  => '' !== $this->props['button_url'] && 'off' !== $this->props['show_button'] ? true : false,
				'button_text'         => $button_text,
				'button_text_escaped' => true,
				'has_wrapper'      	  => true,
				'button_url'          => esc_url( $this->props['button_url'] ),
				'url_new_window'      => esc_attr( $this->props['button_url_new_window'] ),
				'button_custom'       => isset( $this->props['custom_button'] ) ? esc_attr( $this->props['custom_button'] ) : 'off',
				'custom_icon'         => isset( $this->props['button_icon'] ) ? $this->props['button_icon'] : '',
				'button_rel'          => isset( $this->props['button_rel'] ) ? esc_attr( $this->props['button_rel'] ) : '',
			)
		);

		if ( '' !== $icon || '' !== $title || '' !== $content || '' !== $button ) {
			$content_wrapper = sprintf(
				'<div class="dipl_image_accordion_item_content_wrapper">
					<div class="dipl_image_accordion_item_content_inner_wrap et_pb_animation_%5$s">%1$s%2$s%3$s%4$s</div>
				</div>',
				et_core_intentionally_unescaped( $icon, 'html' ),
				et_core_intentionally_unescaped( $title, 'html' ),
				et_core_intentionally_unescaped( $content, 'html' ),
				et_core_intentionally_unescaped( $button, 'html' ),
                esc_attr( $animation )
			);
		} else {
			$content_wrapper = '';
		}

		if ( '' !== $content_wrapper ) {
			$icon_selector  = "{$this->main_css_element} .dipl_image_accordion_item_icon";
			$icon_font_size = et_pb_responsive_options()->get_property_values( $this->props, 'icon_font_size' );
			et_pb_responsive_options()->generate_responsive_css( $icon_font_size, $icon_selector, 'font-size', $render_slug, '!important;', 'range' );

			$icon_color 	= et_pb_responsive_options()->get_property_values( $this->props, 'icon_color' );
			et_pb_responsive_options()->generate_responsive_css( $icon_color, $icon_selector, 'color', $render_slug, '!important;', 'color' );

			$icon_color_hover = $this->get_hover_value( 'icon_color' );
	        if ( $icon_color_hover ) {
	            self::set_style( $render_slug, array(
	                'selector'    => "{$this->main_css_element} .dipl_image_accordion_item_icon:hover",
	                'declaration' => sprintf(
	                    'color: %1$s !important;',
	                    esc_attr( $icon_color_hover )
	                ),
	            ) );
	        }

            if ( 'on' === $this->props['style_icon'] ) {

				if ( $shape_color ) {
					self::set_style( $render_slug, array(
	                    'selector'    => '%%order_class%% .dipl_image_accordion_item_icon:not(.dipl_icon_shape_hexagon)',
	                    'declaration' => sprintf(
	                        'background-color: %1$s !important;',
	                        esc_attr( $shape_color )
	                    ),
	                ) );
					
	                self::set_style( $render_slug, array(
	                    'selector'    => '%%order_class%% .dipl_icon_hexagon',
	                    'declaration' => sprintf(
	                        'background-color: %1$s !important;',
	                        esc_attr( $shape_color )
	                    ),
	                ) );
				}

                $shape_color_hover = $this->get_hover_value( 'shape_color' );
                if ( $shape_color_hover ) {
                    self::set_style( $render_slug, array(
                        'selector'    => "{$this->main_css_element} .dipl_image_accordion_item_icon:hover",
                        'declaration' => sprintf(
                            'background-color: %1$s !important;',
                            esc_attr( $shape_color_hover )
                        ),
                    ) );
                }
            }

		}

		if ( '' !== $icon ) {
			if ( class_exists( 'ET_Builder_Module_Helper_Style_Processor' ) && method_exists( 'ET_Builder_Module_Helper_Style_Processor', 'process_extended_icon' ) ) {
                $this->generate_styles(
                    array(
                        'utility_arg'    => 'icon_font_family',
                        'render_slug'    => $render_slug,
                        'base_attr_name' => 'icon',
                        'important'      => true,
                        'selector'       => '%%order_class%% .dipl_image_accordion_item_icon',
                        'processor'      => array(
                            'ET_Builder_Module_Helper_Style_Processor',
                            'process_extended_icon',
                        ),
                    )
                );
            }
		}

        $args = array(
            'render_slug'   => $render_slug,
            'props'         => $this->props,
            'fields'        => $this->fields_unprocessed,
            'module'        => $this,
            'backgrounds'   => array(
                'accordion_bg' => array(
                    'normal' => "{$this->main_css_element}",
                    'hover' => "{$this->main_css_element}:hover",
                ),
                'active_accordion_bg' => array(
                    'normal' => "{$this->main_css_element}.dipl_active_image_accordion_item:before",
                    'hover' => "{$this->main_css_element}.dipl_active_image_accordion_item:hover:before",
                ),
            ),
        );

        DiviPlusHelper::process_background( $args );

        $background_layout_class_names = et_pb_background_layout_options()->get_background_layout_class( $this->props );
        $this->add_classname(
            array(
                $this->get_text_orientation_classname(),
                $background_layout_class_names[0]
            )
        );

        $file = et_is_builder_plugin_active() ? 'style-dbp' : 'style';
        wp_enqueue_style( 'dipl-image-accordion-item-style', PLUGIN_PATH . 'includes/modules/ImageAccordionItem/' . $file . '.min.css', array(), '1.0.0' );
		
		return et_core_intentionally_unescaped( $content_wrapper, 'html' );
				
	}
	
	//Remove Module inner wrapper
	protected function _render_module_wrapper( $output = '', $render_slug = '' ) {
		$wrapper_settings    = $this->get_wrapper_settings( $render_slug );
		$slug                = $render_slug;
		$outer_wrapper_attrs = $wrapper_settings['attrs'];      /**
		* Filters the HTML attributes for the module's outer wrapper. The dynamic portion of the
		* filter name, '$slug', corresponds to the module's slug.
		*
		* @since 3.23 Add support for responsive video background.
		* @since 3.1
		*
		* @param string[]           $outer_wrapper_attrs
		* @param ET_Builder_Element $module_instance
		*/
		$outer_wrapper_attrs = apply_filters( "et_builder_module_{$slug}_outer_wrapper_attrs", $outer_wrapper_attrs, $this );
		return sprintf(
			'<div%1$s>
				%2$s
			</div>',
			et_html_attrs( $outer_wrapper_attrs ),
			$output
		);
	}

	/**
	 * Filter multi view value.
	 *
	 * @since 3.27.1
	 *
	 * @see ET_Builder_Module_Helper_MultiViewOptions::filter_value
	 *
	 * @param mixed                                     $raw_value Props raw value.
	 * @param array                                     $args {
	 *                                         Context data.
	 *
	 *     @type string $context      Context param: content, attrs, visibility, classes.
	 *     @type string $name         Module options props name.
	 *     @type string $mode         Current data mode: desktop, hover, tablet, phone.
	 *     @type string $attr_key     Attribute key for attrs context data. Example: src, class, etc.
	 *     @type string $attr_sub_key Attribute sub key that availabe when passing attrs value as array such as styes. Example: padding-top, margin-botton, etc.
	 * }
	 * @param ET_Builder_Module_Helper_MultiViewOptions $multi_view Multiview object instance.
	 *
	 * @return mixed
	 */
	public function multi_view_filter_value( $raw_value, $args, $multi_view ) {
		$name = isset( $args['name'] ) ? $args['name'] : '';
		$mode = isset( $args['mode'] ) ? $args['mode'] : '';

		if ( $raw_value && 'icon' === $name ) {
			$processed_value = html_entity_decode( et_pb_process_font_icon( $raw_value ) );
			if ( '%%1%%' === $raw_value ) {
				$processed_value = '"';
			}

			return $processed_value;
		}

		return $raw_value;
	}

	
}
$plugin_options = get_option( ELICUS_DIVI_PLUS_OPTION );
if ( isset( $plugin_options['dipl-modules'] ) ) {
	$modules = explode( ',', $plugin_options['dipl-modules'] );
	if ( in_array( 'dipl_image_accordion', $modules, true ) ) {
		new DIPL_ImageAccordionItem();
	}
} else {
	new DIPL_ImageAccordionItem();
}