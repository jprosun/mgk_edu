<?php
/**
 * @author      Elicus <hello@elicus.com>
 * @link        https://www.elicus.com/
 * @copyright   2022 Elicus Technologies Private Limited
 * @version     1.9.6
 */
class DIPL_ImageCard extends ET_Builder_Module {

	public $slug       = 'dipl_image_card';
	public $vb_support = 'on';

	protected $module_credits = array(
		'module_uri' => 'https://diviextended.com/product/divi-plus/',
		'author'     => 'Elicus',
		'author_uri' => 'https://elicus.com/',
	);

	public function init() {
		$this->name = esc_html__( 'DP Image Card', 'divi-plus' );
		$this->main_css_element = '%%order_class%%';
		add_filter( 'et_builder_processed_range_value', array( $this, 'dipl_builder_processed_range_value' ), 10, 3 );
	}

	public function get_settings_modal_toggles() {
		return array(
			'general'  => array(
				'toggles' => array(
					'main_content' => array(
						'title' => esc_html__( 'Content', 'divi-plus' ),
					),
					'image' => array(
						'title' => esc_html__( 'Image', 'divi-plus' ),
					),
					'icon' => array(
						'title' => esc_html__( 'Icon', 'divi-plus' ),
					),
					'button' => array(
						'title' => esc_html__( 'Button', 'divi-plus' ),
					),
				),
			),
			'advanced' => array(
				'toggles' => array(
					'text' => array(
						'title' => esc_html__( 'Text', 'divi-plus' ),
					),
					'text_settings' => array(
						'title' => esc_html__( 'Title & Content', 'divi-plus' ),
						'sub_toggles'   => array(
                            'title' => array(
                                'name' => 'Title',
                            ),
                            'content' => array(
                                'name' => 'Content',
                            ),
                        ),
                        'tabbed_subtoggles' => true,
					),
					'icon' => array(
						'title' => esc_html__( 'Icon', 'divi-plus' ),
					),
					'button' => array(
						'title' => esc_html__( 'Button', 'divi-plus' ),
					),
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
						'default_on_front' => '16px',
						'range_settings'   => array(
							'min'  => '1',
							'max'  => '100',
							'step' => '1',
						),
						'validate_unit'    => true,
					),
					'line_height'    => array(
						'default_on_front' => '1.2em',
						'range_settings'   => array(
							'min'  => '0.1',
							'max'  => '10',
							'step' => '0.1',
						),
					),
					'letter_spacing' => array(
						'default_on_front' => '0px',
						'range_settings'   => array(
							'min'  => '0',
							'max'  => '10',
							'step' => '1',
						),
						'validate_unit'    => true,
					),
					'header_level'  => array(
						'default'   => 'h4',
					),
					'css'            => array(
						'main' => '%%order_class%% .dipl_image_card_title',
						'important' => 'all',
					),
					'tab_slug' => 'advanced',
					'toggle_slug' => 'text_settings',
					'sub_toggle' => 'title',
				),
				'content' => array(
					'label'          => esc_html__( 'Content', 'divi-plus' ),
					'font_size'      => array(
						'default_on_front' => '14px',
						'range_settings'   => array(
							'min'  => '1',
							'max'  => '100',
							'step' => '1',
						),
						'validate_unit'    => true,
					),
					'line_height'    => array(
						'default_on_front' => '1.5em',
						'range_settings'   => array(
							'min'  => '0.1',
							'max'  => '10',
							'step' => '0.1',
						),
					),
					'letter_spacing' => array(
						'default_on_front' => '0px',
						'range_settings'   => array(
							'min'  => '0',
							'max'  => '10',
							'step' => '1',
						),
						'validate_unit'    => true,
					),
					'css'            => array(
						'main' => '%%order_class%% .dipl_image_card_content',
						'important' => 'all',
					),
					'tab_slug' => 'advanced',
					'toggle_slug' => 'text_settings',
					'sub_toggle' => 'content',
				),
			),
			'margin_padding' => array(
				'css' => array(
					'main'      => '%%order_class%%',
					'important' => 'all',
				),
			),
			'image_card_margin_padding' => array(
                'content' => array(
                    'margin_padding' => array(
                        'css' => array(
                        	'use_margin' => false,
                            'padding'    => '%%order_class%% .dipl_image_card_content_wrapper',
                            'important'  => 'all',
                        ),
                    ),
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
					'border_width'		=> array(
						'default' => '2px',
					),
					'use_alignment'   	=> true,
					'box_shadow'      	=> false,
				    'depends_on'        => array( 'show_button' ),
		            'depends_show_if'   => 'on',
				    'tab_slug'          => 'advanced',
				    'toggle_slug'       => 'button',
			    ),
			),
			'max_width' => array(
				'css' => array(
					'main'             => '%%order_class%%',
					'module_alignment' => '%%order_class%%',
				),
			),
			'borders' => array(
				'default' => array(
					'css' => array(
						'main' => array(
							'border_styles' => '%%order_class%%',
							'border_radii'  => '%%order_class%%',
						),
					),
				),
			),
			'box_shadow' => array(
				'default' => array(
					'css' => array(
						'main' => '%%order_class%%',
					),
				),
			),
			'text' => array(
				'use_background_layout' => true,
				'options'               => array(
					'background_layout' => array(
                        'default'           => 'light',
						'default_on_front'  => 'light',
						'hover'             => 'tabs',
					),
					'text_orientation'  => array(
                        'default' => 'left',
						'default_on_front' => 'left',
					),
				),
			),
			'filters' => false,
			'text_shadow' => false,
		);
	}

	public function get_fields() {
		$et_accent_color = et_builder_accent_color();
		return array_merge(
			array(
				'title' => array(
					'label'           		=> esc_html__( 'Title', 'divi-plus' ),
					'type'           		=> 'text',
					'option_category' 		=> 'basic_option',
					'default_on_front' 		=> esc_html__( 'Title', 'divi-plus' ),
					'default'		   		=> esc_html__( 'Title', 'divi-plus' ),
					'tab_slug'        		=> 'general',
					'toggle_slug'     		=> 'main_content',
					'description'     		=> esc_html__( 'Here you can input the text to be used for the title.', 'divi-plus' ),
				),
				'content' => array(
					'label'                 => esc_html__( 'Content', 'divi-plus' ),
					'type'                  => 'tiny_mce',
					'option_category'       => 'basic_option',
					'toggle_slug'           => 'main_content',
					'description'           => esc_html__( 'Here you can input the text to be used for the content.', 'divi-plus' ),
				),
				'image' => array(
					'label'                 => esc_html__( 'Image', 'divi-plus' ),
					'type'                  => 'upload',
					'option_category'       => 'basic_option',
					'upload_button_text'    => esc_attr__( 'Upload an image', 'divi-plus' ),
					'choose_text'           => esc_attr__( 'Choose an Image', 'divi-plus' ),
					'update_text'           => esc_attr__( 'Set As Image', 'divi-plus' ),
					'dynamic_content'  		=> 'image',
					'tab_slug'              => 'general',
					'toggle_slug'           => 'image',
					'description'           => esc_html__( 'Upload an image to display at the top of your blurb.', 'divi-plus' ),
				),
				'image_alt' => array(
					'label'                 => esc_html__( 'Image Alt Text', 'divi-plus' ),
					'type'                  => 'text',
					'option_category'       => 'basic_option',
					'tab_slug'              => 'general',
					'toggle_slug'           => 'image',
					'description'           => esc_html__( 'Here you can input the text to be used for the image as HTML ALT text.', 'divi-plus' ),
				),
				'icon' => array(
					'label'                 => esc_html__( 'Icon', 'divi-plus' ),
					'type'                  => 'select_icon',
					'option_category'       => 'basic_option',
					'class'                 => array(
						'et-pb-font-icon'
					),
					'tab_slug'              => 'general',
					'toggle_slug'           => 'icon',
					'description'           => esc_html__( 'Choose an icon to display.', 'divi-plus' ),
				),
				'icon_alignment' => array(
					'label'                 => esc_html__( 'Icon Alignment', 'divi-plus' ),
					'type'                  => 'text_align',
	                'option_category'       => 'layout',
	                'options'               => et_builder_get_text_orientation_options( array( 'justified' ) ),
	                'mobile_options'		=> true,
					'tab_slug'              => 'advanced',
					'toggle_slug'           => 'icon',
					'description'           => esc_html__( 'Here you can choose where to place an icon on the image card.', 'divi-plus' ),
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
					'default'          => '32px',
					'default_on_front' => '32px',
					'tab_slug'         => 'advanced',
					'toggle_slug'      => 'icon',
					'description'      => esc_html__( 'Control the size of the icon by increasing or decreasing the font size.', 'divi-plus' ),
				),
				'icon_color' => array(
					'label'          	 => esc_html__( 'Icon Color', 'divi-plus' ),
					'type'            	=> 'color-alpha',
					'hover'           	=> 'tabs',
					'mobile_options'  	=> true,
					'default'         	=> esc_attr( $et_accent_color ),
					'default_on_front'  => esc_attr( $et_accent_color ),
					'tab_slug'        	=> 'advanced',
					'toggle_slug'     	=> 'icon',
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
					'tab_slug'              => 'advanced',
					'toggle_slug'           => 'icon',
					'description'           => esc_html__( 'Here you can choose whether icon should display within a shape or not.', 'divi-plus' ),
				),
				'icon_shape' => array(
					'label'                 => esc_html__( 'Shape', 'divi-plus' ),
					'type'                  => 'select',
					'option_category'       => 'configuration',
					'options'               => array(
						'dipl_icon_shape_square'    => esc_html__( 'Square', 'divi-plus' ),
						'dipl_icon_shape_circle'    => esc_html__( 'Circle', 'divi-plus' ),
						'dipl_icon_shape_hexagon'   => esc_html__( 'Hexagon', 'divi-plus' ),
					),
					'show_if'               => array(
					    'style_icon'    => 'on',
					),
					'default' 				=> 'dipl_icon_shape_square',
					'tab_slug'              => 'advanced',
					'toggle_slug'           => 'icon',
					'description'           => esc_html__( 'Here you can choose shape to display icon within.', 'divi-plus' ),
				),
				'shape_bg_color' => array(
					'label'                 => esc_html__( 'Shape Background', 'divi-plus' ),
					'type'                  => 'color-alpha',
					'custom_color'          => true,
					'show_if'               => array(
					    'style_icon'    => 'on',
					),
					'default'               => '#000',
					'default_on_front'      => '#000',
					'hover'           		=> 'tabs',
					'tab_slug'              => 'advanced',
					'toggle_slug'           => 'icon',
					'description'           => esc_html__( 'Pick a color to be used for the icon shape.', 'divi-plus' ),
				),
				'show_button' => array(
					'label'                 => esc_html__( 'Show Button', 'divi-plus' ),
					'type'                  => 'yes_no_button',
					'option_category'       => 'basic_option',
					'options'               => array(
						'off'   => esc_html__( 'No', 'divi-plus' ),
						'on'    => esc_html__( 'Yes', 'divi-plus' ),
					),
					'affects'               => array(
					    'custom_button',
					),
					'default'               => 'off',
					'tab_slug'              => 'general',
					'toggle_slug'           => 'button',
					'description'           => esc_html__( 'Here you can choose whether or not display button.', 'divi-plus' ),
				),
				'button_text' => array(
					'label'                 => esc_html__( 'Button Text', 'divi-plus' ),
					'type'                  => 'text',
					'option_category'       => 'basic_option',
					'show_if'               => array(
					    'show_button'  => 'on',
					),
					'default'				=> esc_html__( 'Read More', 'divi-plus' ),
					'default_on_front'		=> esc_html__( 'Read More', 'divi-plus' ),
					'tab_slug'              => 'general',
					'toggle_slug'           => 'button',
					'description'           => esc_html__( 'Here you can input the text to be used for the Button.', 'divi-plus' ),
				),
				'button_url' => array(
					'label'                 => esc_html__( 'Url', 'divi-plus' ),
					'type'                  => 'text',
					'option_category'       => 'basic_option',
					'default'				=> '#',
					'default_on_front'		=> '#',
					'show_if'               => array(
					    'show_button'  => 'on',
					),
					'dynamic_content' 		=> 'url',
					'tab_slug'              => 'general',
					'toggle_slug'           => 'button',
					'description'           => esc_html__( 'Here you can input the URL that you want to open when the user clicks on the button.', 'divi-plus' ),
				),
				'button_url_new_window' => array(
	                'label'             => esc_html__( 'Link Target', 'divi-plus' ),
	                'type'              => 'select',
	                'option_category'   => 'configuration',
	                'options'           => array(
	                    'off' => esc_html__( 'In The Same Window', 'divi-plus' ),
	                    'on'  => esc_html__( 'In The New Tab', 'divi-plus' ),
	                ),
	                'default'           => 'off',
	                'default_on_front'  => 'off',
	                'show_if'           => array(
					    'show_button'  => 'on',
					),
	                'tab_slug'          => 'general',
	                'toggle_slug'       => 'button',
	                'description'       => esc_html__( 'Here you can choose whether or not the link opens in a new window.', 'divi-plus' ),
	            ),
				'content_custom_padding' => array(
	                'label'                 => esc_html__( 'Content Padding', 'divi-plus' ),
	                'type'                  => 'custom_padding',
	                'option_category'       => 'layout',
	                'mobile_options'        => true,
	                'hover'                 => false,
	                'default'          		=> '20px|20px|20px|20px|true|true',
					'default_on_front' 		=> '20px|20px|20px|20px|true|true',
	                'tab_slug'              => 'advanced',
	                'toggle_slug'           => 'margin_padding',
	                'description'           => esc_html__( 'Padding adds extra space to the inside of the element, increasing the distance between the edge of the element and its inner contents.', 'divi-plus' ),
	            ),
	            'content_bg_color' => array(
	                'label'                 => esc_html__( 'Content Background', 'divi-plus' ),
	                'type'                  => 'background-field',
	                'base_name'             => 'content_bg',
	                'context'               => 'content_bg_color',
	                'option_category'       => 'button',
	                'custom_color'          => true,
	                'background_fields'     => $this->generate_background_options( 'content_bg', 'button', 'advanced', 'background', 'content_bg_color' ),
	                'hover'                 => 'tabs',
	                'tab_slug'              => 'general',
	                'toggle_slug'           => 'background',
	                'description'           => esc_html__( 'Customize the background style of the content box by adjusting the background color, gradient, and image.', 'divi-plus' ),
	            ),
	        ),
			$this->generate_background_options( 'content_bg', 'skip', 'general', 'background', 'content_bg_color' )
		);

	}


	public function render( $attrs, $content, $render_slug ) {
		$multi_view            		= et_pb_multi_view_options( $this );
		$style_icon					= $this->props['style_icon'];
		$button_text           		= sprintf( esc_html__( '%s', 'divi-plus' ), $this->props['button_text'] );
		$button_url_new_window		= $this->props['button_url_new_window'];
		$image_alt 					= sprintf( esc_html__( '%s', 'divi-plus' ), $this->props['image_alt'] );
		$title_level				= $this->props['title_level'];
		$processed_title_level 		= et_pb_process_header_level( $title_level, 'h4' );
		$processed_title_level 		= esc_html( $processed_title_level );

		$icon_shape 	= 'on' === $style_icon ? esc_attr( $this->props['icon_shape'] ) : '';
		$icon_classes	= implode(
			' ',
			array(
				'dipl_image_card_icon',
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

		$image = $multi_view->render_element(
			array(
				'tag'      => 'img',
				'attrs'    => array(
					'src'   => '{{image}}',
					'class' => 'dipl_image_card_image',
					'alt'   => $image_alt,
				),
				'required' => 'image',
			)
		);

		$title = $multi_view->render_element(
			array(
				'tag'      => $processed_title_level,
				'content'  => '{{title}}',
				'attrs'    => array(
					'class' => 'dipl_image_card_title',
				),
				'required' => 'title',
			)
		);

		$content = $multi_view->render_element(
			array(
				'tag'      => 'div',
				'content'  => '{{content}}',
				'attrs'    => array(
					'class' => 'dipl_image_card_content',
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

		if ( '' !== $image ) {
			$image_wrapper = sprintf(
				'<div class="dipl_image_card_image_wrapper">%1$s</div>',
				et_core_intentionally_unescaped( $image, 'html' )
			);
		}

		if ( '' !== $icon ) {
			$icon_wrapper = sprintf(
				'<div class="dipl_image_card_icon_wrapper">%1$s</div>',
				et_core_intentionally_unescaped( $icon, 'html' )
			);
		}

		if ( '' !== $title || '' !== $content || '' !== $icon || '' !== $button ) {
			$content_wrapper = sprintf(
				'<div class="dipl_image_card_content_wrapper">
					%1$s
					<div class="dipl_image_card_inner_content_wrapper">%2$s%3$s%4$s</div>
				</div>',
				'' !== $icon ? et_core_intentionally_unescaped( $icon_wrapper, 'html' ) : '',
				et_core_intentionally_unescaped( $title, 'html' ),
				et_core_intentionally_unescaped( $content, 'html' ),
				et_core_intentionally_unescaped( $button, 'html' )
			);
		}
		
		if ( isset( $image_wrapper ) || isset( $content_wrapper ) ) {
			$image_card_wrapper = sprintf(
				'<div class="dipl_image_card_wrapper">%1$s%2$s</div>',
				isset( $image_wrapper ) ? et_core_intentionally_unescaped( $image_wrapper, 'html' ) : '',
				isset( $content_wrapper ) ? et_core_intentionally_unescaped( $content_wrapper, 'html' ) : ''
			);
		} else {
			$image_card_wrapper = '';
		}

		if ( '' === $image && isset( $this->props['border_radii'] ) && '' !== $this->props['border_radii'] ) {
			$radii = explode( '|', $this->props['border_radii'] );
			if ( count( $radii ) === 5 ) {
				$top_left_radius     = empty( $radii[1] ) ? '0' : esc_html( $radii[1] );
				$top_right_radius    = empty( $radii[2] ) ? '0' : esc_html( $radii[2] );
				$bottom_right_radius = empty( $radii[3] ) ? '0' : esc_html( $radii[3] );
				$bottom_left_radius  = empty( $radii[4] ) ? '0' : esc_html( $radii[4] );

				self::set_style( $render_slug, array(
                    'selector'    => '%%order_class%% .dipl_image_card_content_wrapper',
                    'declaration' => sprintf(
                        'border-radius: %1$s %2$s %3$s %4$s;',
                        esc_attr( $top_left_radius ),
                        esc_attr( $top_right_radius ),
                        esc_attr( $bottom_right_radius ),
                        esc_attr( $bottom_left_radius )
                    ),
                ) );
			}
		}

		if ( '' === $image && isset( $this->props['border_radii_tablet'] ) && '' !== $this->props['border_radii_tablet'] ) {
			$radii = explode( '|', $this->props['border_radii_tablet'] );
			if ( count( $radii ) === 5 ) {
				$top_left_radius     = empty( $radii[1] ) ? '0' : esc_html( $radii[1] );
				$top_right_radius    = empty( $radii[2] ) ? '0' : esc_html( $radii[2] );
				$bottom_right_radius = empty( $radii[3] ) ? '0' : esc_html( $radii[3] );
				$bottom_left_radius  = empty( $radii[4] ) ? '0' : esc_html( $radii[4] );
				
				self::set_style( $render_slug, array(
                    'selector'    => '%%order_class%% .dipl_image_card_content_wrapper',
                    'declaration' => sprintf(
                        'border-radius: %1$s %2$s %3$s %4$s;',
                        esc_attr( $top_left_radius ),
                        esc_attr( $top_right_radius ),
                        esc_attr( $bottom_right_radius ),
                        esc_attr( $bottom_left_radius )
                    ),
                    'media_query' => self::get_media_query( 'max_width_980' ),
                ) );
			}
		}

		if ( '' === $image && isset( $this->props['border_radii_phone'] ) && '' !== $this->props['border_radii_phone'] ) {
			$radii = explode( '|', $this->props['border_radii_phone'] );
			if ( count( $radii ) === 5 ) {
				$top_left_radius     = empty( $radii[1] ) ? '0' : esc_html( $radii[1] );
				$top_right_radius    = empty( $radii[2] ) ? '0' : esc_html( $radii[2] );
				$bottom_right_radius = empty( $radii[3] ) ? '0' : esc_html( $radii[3] );
				$bottom_left_radius  = empty( $radii[4] ) ? '0' : esc_html( $radii[4] );
				
				self::set_style( $render_slug, array(
                    'selector'    => '%%order_class%% .dipl_image_card_content_wrapper',
                    'declaration' => sprintf(
                        'border-radius: %1$s %2$s %3$s %4$s;',
                        esc_attr( $top_left_radius ),
                        esc_attr( $top_right_radius ),
                        esc_attr( $bottom_right_radius ),
                        esc_attr( $bottom_left_radius )
                    ),
                    'media_query' => self::get_media_query( 'max_width_767' ),
                ) );
			}
		}

		/* Icon CSS */
		if ( '' !== $icon ) {
			$icon_font_size 	= et_pb_responsive_options()->get_property_values( $this->props, 'icon_font_size' );
			$icon_color     	= et_pb_responsive_options()->get_property_values( $this->props, 'icon_color' );
			et_pb_responsive_options()->generate_responsive_css( $icon_font_size, '%%order_class%% .dipl_image_card_icon', 'font-size', $render_slug, '!important;', 'range' );
			et_pb_responsive_options()->generate_responsive_css( $icon_color, '%%order_class%% .dipl_image_card_icon', 'color', $render_slug, '!important;', 'color' );
			$icon_color_hover    = $this->get_hover_value( 'icon_color' );
            if ( $icon_color_hover ) {
                self::set_style( $render_slug, array(
                    'selector'    => '%%order_class%% .dipl_image_card_icon:hover',
                    'declaration' => sprintf(
                        'color: %1$s !important;',
                        esc_attr( $icon_color_hover )
                    ),
                ) );
            }

            if ( class_exists( 'ET_Builder_Module_Helper_Style_Processor' ) && method_exists( 'ET_Builder_Module_Helper_Style_Processor', 'process_extended_icon' ) ) {
                $this->generate_styles(
                    array(
                        'utility_arg'    => 'icon_font_family',
                        'render_slug'    => $render_slug,
                        'base_attr_name' => 'icon',
                        'important'      => true,
                        'selector'       => '%%order_class%% .dipl_image_card_icon',
                        'processor'      => array(
                            'ET_Builder_Module_Helper_Style_Processor',
                            'process_extended_icon',
                        ),
                    )
                );
            }

			$content_padding = et_pb_responsive_options()->get_property_values( $this->props, 'content_custom_padding' );
			$content_top_padding_desktop 	= explode( '|', $content_padding['desktop'] )[0];
			$content_top_padding_tablet 	= '' !== $content_padding['tablet'] ? explode( '|', $content_padding['tablet'] )[0] : $content_top_padding_desktop;
			$content_top_padding_phone 		= '' !== $content_padding['phone'] ? explode( '|', $content_padding['phone'] )[0] : $content_top_padding_tablet;

			$icon_font_size_tablet 	= '' !== $icon_font_size['tablet'] ? $icon_font_size['tablet'] : $icon_font_size['desktop'];
			$icon_font_size_phone 	= '' !== $icon_font_size['phone'] ? $icon_font_size['phone'] : $icon_font_size['tablet'];

			$margin_units = array(
				'desktop'   => str_replace( floatval( $icon_font_size['desktop'] ), '', (string) $icon_font_size['desktop'] ),
				'tablet'    => str_replace( floatval( $icon_font_size_tablet ), '', (string) $icon_font_size_tablet ),
				'phone'     => str_replace( floatval( $icon_font_size_phone ), '', (string) $icon_font_size_phone ),
			);

			$margin_values = array(
				'desktop' => sprintf(
								'calc(-%1$s - %2$s)',
								floatval( floatval( $icon_font_size['desktop'] ) / 2 ) . $margin_units['desktop'],
								$content_top_padding_desktop
							),
				'tablet'  => sprintf(
								'calc(-%1$s - %2$s)',
								floatval( floatval( $icon_font_size_tablet ) / 2 ) . $margin_units['tablet'],
								$content_top_padding_tablet
							),
				'phone'   => sprintf(
								'calc(-%1$s - %2$s)',
								floatval( floatval( $icon_font_size_phone ) / 2 ) . $margin_units['phone'],
								$content_top_padding_phone
							),
			);

			if ( '' === $image ) {
				$content_margin_values = array(
					'desktop' => sprintf(
						'%1$s%2$s',
						floatval( floatval( $icon_font_size['desktop'] ) / 2 ) + 2,
						$margin_units['desktop']
					),
					'tablet'  => sprintf(
						'%1$s%2$s',
						floatval( floatval( $icon_font_size_tablet ) / 2 ) + 2,
						$margin_units['tablet']
					),
					'phone'   => sprintf(
						'%1$s%2$s',
						floatval( floatval( $icon_font_size_phone ) / 2 ) + 2,
						$margin_units['phone']
					),
				);
			}

            if ( 'on' === $style_icon ) {
				$shape_bg_color = esc_attr( $this->props['shape_bg_color'] );
				if ( $shape_bg_color ) {
					self::set_style( $render_slug, array(
	                    'selector'    => '%%order_class%% .dipl_image_card_icon:not(.dipl_icon_shape_hexagon)',
	                    'declaration' => sprintf(
	                        'background-color: %1$s !important;',
	                        esc_attr( $shape_bg_color )
	                    ),
	                ) );
	                self::set_style( $render_slug, array(
	                    'selector'    => '%%order_class%% .dipl_icon_hexagon',
	                    'declaration' => sprintf(
	                        'background-color: %1$s !important;',
	                        esc_attr( $shape_bg_color )
	                    ),
	                ) );
				}
				$margin_values = array(
					'desktop' => sprintf(
									'calc(-%1$s - %2$s)',
									floatval( ( floatval( $icon_font_size['desktop'] ) + 24 ) / 2 ) . $margin_units['desktop'],
									$content_top_padding_desktop
								),
					'tablet'  => sprintf(
									'calc(-%1$s - %2$s)',
									floatval( ( floatval( $icon_font_size_tablet ) + 24 ) / 2 ) . $margin_units['tablet'],
									$content_top_padding_tablet
								),
					'phone'   => sprintf(
									'calc(-%1$s - %2$s)',
									floatval( ( floatval( $icon_font_size_phone ) + 24 ) / 2 ) . $margin_units['phone'],
									$content_top_padding_phone
								),
				);

				if ( '' === $image ) {
					$content_margin_values = array(
						'desktop' => sprintf(
							'%1$s%2$s',
							floatval( ( floatval( $icon_font_size['desktop'] ) + 24 ) / 2 ) + 2,
							$margin_units['desktop']
						),
						'tablet'  => sprintf(
							'%1$s%2$s',
							floatval( ( floatval( $icon_font_size_tablet ) + 24 ) / 2 ) + 2,
							$margin_units['tablet']
						),
						'phone'   => sprintf(
							'%1$s%2$s',
							floatval( ( floatval( $icon_font_size_phone ) + 24 ) / 2 ) + 2,
							$margin_units['phone']
						),
					);

					if ( 'dipl_icon_shape_hexagon' === $icon_shape ) {
						$content_margin_values = array(
							'desktop' => sprintf(
								'calc(%1$s%2$s + 1%%)',
								floatval( ( floatval( $icon_font_size['desktop'] ) + 24 ) / 2 ) + 2,
								$margin_units['desktop']
							),
							'tablet'  => sprintf(
								'calc(%1$s%2$s + 1%%)',
								floatval( ( floatval( $icon_font_size_tablet ) + 24 ) / 2 ) + 2,
								$margin_units['tablet']
							),
							'phone'   => sprintf(
								'calc(%1$s%2$s + 1%%)',
								floatval( ( floatval( $icon_font_size_phone ) + 24 ) / 2 ) + 2,
								$margin_units['phone']
							),
						);
					}
				}
            }

            if ( '' === $image ) {
            	et_pb_responsive_options()->generate_responsive_css( $content_margin_values, '%%order_class%% .dipl_image_card_content_wrapper', 'margin-top', $render_slug, '!important;', 'range' );
            }

            $inner_content_margin_values = array(
				'desktop' => $content_top_padding_desktop,
				'tablet'  => $content_top_padding_tablet,
				'phone'   => $content_top_padding_phone
			);

            /* Add margin to icon */
			et_pb_responsive_options()->generate_responsive_css( $margin_values, '%%order_class%% .dipl_image_card_icon_wrapper', 'margin-top', $render_slug, '!important;', 'range' );
			et_pb_responsive_options()->generate_responsive_css( $inner_content_margin_values, '%%order_class%% .dipl_image_card_inner_content_wrapper', 'margin-top', $render_slug, '!important;', 'range' );

			/* Icon Alignment */
			$icon_alignment = et_pb_responsive_options()->get_property_values( $this->props, 'icon_alignment' );
	        if ( ! empty( array_filter( $icon_alignment ) ) ) {
	            et_pb_responsive_options()->generate_responsive_css( $icon_alignment, '%%order_class%% .dipl_image_card_icon_wrapper', 'text-align', $render_slug, '!important;', 'type' );
	        }
		}

		$args = array(
			'render_slug'	=> $render_slug,
			'props'			=> $this->props,
			'fields'		=> $this->fields_unprocessed,
			'module'		=> $this,
			'backgrounds' 	=> array(
				'content_bg' => array(
					'normal' => "{$this->main_css_element} .dipl_image_card_content_wrapper",
					'hover' => "{$this->main_css_element} .dipl_image_card_content_wrapper:hover",
	 			),
			),
		);

		DiviPlusHelper::process_background( $args );
		$fields = array( 'image_card_margin_padding' );
		DiviPlusHelper::process_advanced_margin_padding_css( $this, $render_slug, $this->margin_padding, $fields );

		$background_layout_class_names = et_pb_background_layout_options()->get_background_layout_class( $this->props );
        $this->add_classname(
            array(
                $this->get_text_orientation_classname(),
                $background_layout_class_names[0]
            )
        );

        $file = et_is_builder_plugin_active() ? 'style-dbp' : 'style';
        wp_enqueue_style( 'dipl-image-card-style', PLUGIN_PATH . 'includes/modules/ImageCard/' . $file . '.min.css', array(), '1.0.0' );

		return $image_card_wrapper;
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

	public function dipl_builder_processed_range_value( $result, $range, $range_string ) {
		if ( false !== strpos( $result, '0calc' ) ) {
			return $range;
		}
		return $result;
	}

}
$plugin_options = get_option( ELICUS_DIVI_PLUS_OPTION );
if ( isset( $plugin_options['dipl-modules'] ) ) {
    $modules = explode( ',', $plugin_options['dipl-modules'] );
    if ( in_array( 'dipl_image_card', $modules ) ) {
        new DIPL_ImageCard();
    }
} else {
    new DIPL_ImageCard();
}