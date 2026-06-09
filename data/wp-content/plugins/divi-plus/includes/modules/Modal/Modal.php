<?php
/**
 * @author      Elicus <hello@elicus.com>
 * @link        https://www.elicus.com/
 * @copyright   2023 Elicus Technologies Private Limited
 * @version     1.9.15
 */
class DIPL_Modal extends ET_Builder_Module {

	public $slug       = 'dipl_modal';
	public $vb_support = 'on';

	protected $module_credits = array(
		'module_uri' => 'https://diviextended.com/product/divi-plus/',
		'author'     => 'Elicus',
		'author_uri' => 'https://elicus.com/',
	);

	public function init() {
		$this->name             = esc_html__( 'DP Modal', 'divi-plus' );
		$this->main_css_element = '%%order_class%%';
	}

	public function get_settings_modal_toggles() {
		return array(
			'general'  => array(
				'toggles' => array(
					'main_content' 		=> esc_html__( 'Configuration', 'divi-plus' ),
					'modal_header'	 	=> esc_html__( 'Modal Header', 'divi-plus' ),
					'modal_body'   		=> esc_html__( 'Modal Body', 'divi-plus' ),
					'modal_footer' 		=> esc_html__( 'Modal Footer', 'divi-plus' ),
					'scroll_settings'  	=> esc_html__( 'Scroll Settings', 'divi-plus' ),
					'background'   		=> esc_html__( 'Background', 'divi-plus' ),
				),
			),
			'advanced' => array(
				'toggles' => array(
					'text'               => esc_html__( 'Alignment', 'divi-plus' ),
					'trigger_button'     => esc_html__( 'Trigger Button', 'divi-plus' ),
					'trigger_text'       => esc_html__( 'Trigger Text', 'divi-plus' ),
					'trigger_icon'       => esc_html__( 'Trigger Icon', 'divi-plus' ),
					'trigger_image'      => esc_html__( 'Trigger Image', 'divi-plus' ),
					'modal_title_text'   => esc_html__( 'Modal Title Text', 'divi-plus' ),
					'modal_body_text'    => esc_html__( 'Modal Body Text', 'divi-plus' ),
					'modal_close_icon'   => esc_html__( 'Modal Close Icon', 'divi-plus' ),
					'modal_close_button' => esc_html__( 'Modal Close Button', 'divi-plus' ),
					'box_shadow'         => esc_html__( 'Box Shadow', 'divi-plus' ),
					'border'             => esc_html__( 'Border', 'divi-plus' ),
					'modal_sizing'       => esc_html__( 'Modal Sizing & Alignment', 'divi-plus' ),
				),
			),
		);
	}

	public function get_advanced_fields_config() {
		return array(
			'fonts'                => array(
				'modal_title_text'   => array(
					'label'          => esc_html__( 'Modal Title', 'divi-plus' ),
					'font_size'      => array(
						'default'        => '20px',
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
					'header_level'   => array(
						'default' => 'h2',
					),
					'text_align'     => array(
						'default' => 'left',
					),
					'css'            => array(
						'main'       => "{$this->main_css_element}_module .dipl_modal_header_title",
						'hover'      => "{$this->main_css_element}_module .dipl_modal_header_title:hover",
						'text_align' => "{$this->main_css_element}_module .dipl_modal_header_title_container",
						'important'	 => 'all',
					),
					'tab_slug'       => 'advanced',
					'toggle_slug'    => 'modal_title_text',
				),
				'modal_body_text'    => array(
					'label'          => esc_html__( 'Modal Body', 'divi-plus' ),
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
					'text_align'     => array(
						'default' => 'left',
					),
					'css'            => array(
						'main'  => "{$this->main_css_element}_module .dipl_modal_body",
						'hover' => "{$this->main_css_element}_module .dipl_modal_body:hover",
						'important' => 'all',
					),
					'tab_slug'       => 'advanced',
					'toggle_slug'    => 'modal_body_text',
				),
				'modal_trigger_text' => array(
					'label'           => esc_html__( 'Trigger Text', 'divi-plus' ),
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
					'hide_text_align' => true,
					'css'             => array(
						'main'  => "{$this->main_css_element} .dipl_modal_trigger_text",
						'hover' => "{$this->main_css_element} .dipl_modal_trigger_text:hover",
						'important'	=> 'all',
					),
					'tab_slug'        => 'advanced',
					'toggle_slug'     => 'trigger_text',
				),
			),
			'button'               => array(
				'trigger_button' => array(
					'label'          => esc_html__( 'Trigger Button', 'divi-plus' ),
					'css'            => array(
						'main'      => "{$this->main_css_element} .dipl_modal_trigger_button",
						'alignment' => "{$this->main_css_element} .dipl_modal_trigger_element_wrapper .et_pb_button_wrapper",
						'important' => 'all',
					),
					'margin_padding' => array(
						'css' => array(
							'margin'    => "{$this->main_css_element} .dipl_modal_trigger_element_wrapper .et_pb_button_wrapper",
							'padding'   => "{$this->main_css_element} .dipl_modal_trigger_button, {$this->main_css_element} .dipl_modal_trigger_button:hover",
							'important' => 'all',
						),
					),
					'box_shadow'     => array(
						'css' => array(
							'main' => "{$this->main_css_element} .dipl_modal_trigger_button",
							'important' => 'all',
						),
					),
					'use_alignment'  => false,
					'tab_slug'       => 'advanced',
					'toggle_slug'    => 'trigger_button',
				),
				'close_button'   => array(
					'label'          => esc_html__( 'Close Button', 'divi-plus' ),
					'css'            => array(
						'main'      => "{$this->main_css_element}_module .dipl_modal_close_button",
						'alignment' => "{$this->main_css_element}_module .dipl_modal_footer .et_pb_button_wrapper",
						'important' => 'all',
					),
					'margin_padding' => array(
						'css' => array(
							'margin'    => "{$this->main_css_element}_module .dipl_modal_footer .et_pb_button_wrapper",
							'padding'   => "{$this->main_css_element}_module .dipl_modal_close_button, {$this->main_css_element}_module .dipl_modal_close_button:hover",
							'important' => 'all',
						),
					),
					'box_shadow'     => array(
						'css' => array(
							'main' => "{$this->main_css_element}_module .dipl_modal_close_button",
							'important' => 'all',
						),
					),
					'use_alignment'  => true,
					'tab_slug'       => 'advanced',
					'toggle_slug'    => 'modal_close_button',
				),
			),
			'borders'              => array(
				'default'         => false,
				'trigger_element' => array(
					'label_prefix' => 'Trigger Element',
					'css'          => array(
						'main' => array(
							'border_radii'  => "{$this->main_css_element} .dipl_modal_trigger_element_wrapper .dipl_modal_trigger_element",
							'border_styles' => "{$this->main_css_element} .dipl_modal_trigger_element_wrapper .dipl_modal_trigger_element",
						),
						'important' => 'all',
					),
					'tab_slug'     => 'advanced',
					'toggle_slug'  => 'border',
				),
				'modal'           => array(
					'label_prefix' => 'Modal',
					'css'          => array(
						'main' => array(
							'border_radii'  => "{$this->main_css_element}_module .dipl_modal_inner_wrap",
							'border_styles' => "{$this->main_css_element}_module .dipl_modal_inner_wrap",
						),
						'important' => 'all',
					),
					'tab_slug'     => 'advanced',
					'toggle_slug'  => 'border',
				),
			),
			'box_shadow'           => array(
				'default'         => false,
				'trigger_element' => array(
					'label'       => esc_html__( 'Trigger Element Box Shadow', 'divi-plus' ),
					'css'         => array(
						'main' => "{$this->main_css_element} .dipl_modal_trigger_element",
						'important' => 'all',
					),
					'tab_slug'    => 'advanced',
					'toggle_slug' => 'box_shadow',
				),
				'modal'           => array(
					'label'       => esc_html__( 'Modal Box Shadow', 'divi-plus' ),
					'css'         => array(
						'main' => "{$this->main_css_element}_module .dipl_modal_inner_wrap",
						'important' => 'all',
					),
					'tab_slug'    => 'advanced',
					'toggle_slug' => 'box_shadow',
				),
				'modal_header'    => array(
					'label'       => esc_html__( 'Modal Header Box Shadow', 'divi-plus' ),
					'css'         => array(
						'main' => "{$this->main_css_element}_module .dipl_modal_header",
						'important' => 'all',
					),
					'tab_slug'    => 'advanced',
					'toggle_slug' => 'box_shadow',
				),
				'modal_footer'    => array(
					'label'       => esc_html__( 'Modal Footer Box Shadow', 'divi-plus' ),
					'css'         => array(
						'main' => "{$this->main_css_element}_module .dipl_modal_footer",
						'important' => 'all',
					),
					'priority'    => 0,
					'tab_slug'    => 'advanced',
					'toggle_slug' => 'box_shadow',
				),
			),
			'max_width'            => array(
				'extra' => array(
					'modal'         => array(
						'options'              => array(
							'width' => array(
								'label'          => esc_html__( 'Modal Width', 'divi-plus' ),
								'range_settings' => array(
									'min'  => 1,
									'max'  => 100,
									'step' => 1,
								),
								'hover'          => false,
								'default_unit'   => '%',
								'default_tablet' => '',
								'default_phone'  => '',
								'tab_slug'       => 'advanced',
								'toggle_slug'    => 'modal_sizing',
							),
						),
						'use_max_width'        => false,
						'use_module_alignment' => false,
						'css'                  => array(
							'main' => "{$this->main_css_element}_module .dipl_modal_inner_wrap",
							'important' => 'all',
						),
					),
					'trigger_image' => array(
						'options'              => array(
							'width' => array(
								'label'          => esc_html__( 'Image Width', 'divi-plus' ),
								'range_settings' => array(
									'min'  => 1,
									'max'  => 100,
									'step' => 1,
								),
								'hover'          => false,
								'default_unit'   => '%',
								'default_tablet' => '',
								'default_phone'  => '',
								'show_if'        => array(
									'trigger_element_type' => 'image',
								),
								'tab_slug'       => 'advanced',
								'toggle_slug'    => 'trigger_image',
							),
						),
						'use_max_width'        => false,
						'use_module_alignment' => false,
						'css'                  => array(
							'main' => "{$this->main_css_element} .dipl_modal_trigger_image",
							'important' => 'all',
						),
					),
				),
			),
			'height'               => array(
				'extra' => array(
					'modal' => array(
						'options'        => array(
							'height' => array(
								'label'          => esc_html__( 'Modal Height', 'divi-plus' ),
								'range_settings' => array(
									'min'  => 1,
									'max'  => 100,
									'step' => 1,
								),
								'hover'          => false,
								'default_unit'   => '%',
								'default_tablet' => '',
								'default_phone'  => '',
								'tab_slug'       => 'advanced',
								'toggle_slug'    => 'modal_sizing',
							),
						),
						'use_max_height' => false,
						'use_min_height' => false,
						'css'            => array(
							'main' => "{$this->main_css_element}_module .dipl_modal_inner_wrap",
							'important' => 'all',
						),
					),
				),
			),
			'text'                 => array(
				'text_orientation' => array(
					'exclude_options' => array( 'justified' ),
				),
				'options'          => array(
					'text_orientation' => array(
						'label' => esc_html__( 'Trigger Element Alignment', 'divi-plus' ),
					),
				),
				'css'              => array(
					'text_orientation' => $this->main_css_element,
				),
			),
			'modal_margin_padding' => array(
				'modal'        => array(
					'margin_padding' => array(
						'css' => array(
							'margin'    => "{$this->main_css_element}_module .dipl_modal_inner_wrap",
							'padding'   => "{$this->main_css_element}_module .dipl_modal_inner_wrap",
							'important' => 'all',
						),
					),
				),
				'modal_header' => array(
					'margin_padding' => array(
						'css' => array(
							'margin'    => "{$this->main_css_element}_module .dipl_modal_header",
							'padding'   => "{$this->main_css_element}_module .dipl_modal_header",
							'important' => 'all',
						),
					),
				),
				'modal_body'   => array(
					'margin_padding' => array(
						'css' => array(
							'margin'    => "{$this->main_css_element}_module .dipl_modal_body",
							'padding'   => "{$this->main_css_element}_module .dipl_modal_body",
							'important' => 'all',
						),
					),
				),
				'modal_footer' => array(
					'margin_padding' => array(
						'css' => array(
							'margin'    => "{$this->main_css_element}_module .dipl_modal_footer",
							'padding'   => "{$this->main_css_element}_module .dipl_modal_footer",
							'important' => 'all',
						),
					),
				),
			),
			'margin_padding'       => array(
				'css' => array(
					'main'      => $this->main_css_element,
					'important' => 'all',
				),
			),
			'text_shadow'          => false,
			'link_options'         => false,
			'filters'              => false,
			'background'           => false,
		);
	}

	public function get_fields() {
		$query_args     = array(
			'post_type'      => 'et_pb_layout',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'meta_query'     => array(
				array(
					'key'     => '_et_pb_predefined_layout',
					'value'   => 'on',
					'compare' => 'NOT EXISTS',
				),
			),
		);
		$layout_list    = array();
		$layout_list[0] = esc_html__( 'Select', 'divi-plus' );
		$query          = new WP_Query( $query_args );
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$layout_list[ intval( get_the_ID() ) ] = ucwords( esc_html( get_the_title() ) );
			}
			wp_reset_postdata();
		}
		$et_accent_color = et_builder_accent_color();
		return array_merge(
			array(
				'modal_id_notice' 			=> array(
					'label'           => '',
					'type'            => 'warning',
					'option_category' => 'configuration',
					'value'           => true,
					'display_if'      => true,
					'message'         => esc_html__( 'In order to make the modal work perfectly, please enter a unique ID for it.', 'divi-plus' ),
					'show_if'     	  => array(
						'modal_id' => array( '' ),
					),
					'tab_slug'         => 'general',
					'toggle_slug'      => 'main_content',
				),
				'modal_id'  				=> array(
					'label'            => esc_html__( 'Unique Modal ID', 'divi-plus' ),
					'type'             => 'text',
					'option_category'  => 'basic_option',
					'default' 		   => '',
					'default_on_front' => '',
					'tab_slug'         => 'general',
					'toggle_slug'      => 'main_content',
					'description'      => esc_html__( 'Enter a unique ID for the modal to make it work perfectly.', 'divi-plus' ),
				),
				'trigger_type'                     => array(
					'label'            => esc_html__( 'Trigger Type', 'divi-plus' ),
					'type'             => 'select',
					'option_category'  => 'configuration',
					'options'          => array(
						'element'      => esc_html__( 'Element', 'divi-plus' ),
						'on_page_load' => esc_html__( 'On Page Load', 'divi-plus' ),
						'exit_intent'  => esc_html__( 'Exit Intent', 'divi-plus' ),
					),
					'default'          => 'element',
					'default_on_front' => 'element',
					'tab_slug'         => 'general',
					'toggle_slug'      => 'main_content',
					'description'      => esc_html__( 'Here you can select how you want to activate modal on the page.', 'divi-plus' ),
				),
				'trigger_delay'                    => array(
					'label'            => esc_html__( 'Trigger Delay', 'divi-plus' ),
					'type'             => 'range',
					'option_category'  => 'layout',
					'range_settings'   => array(
						'min'  => '100',
						'max'  => '15000',
						'step' => '100',
					),
					'validate_unit'    => true,
					'fixed_unit'       => 'ms',
					'fixed_range'      => true,
					'default'          => '3000ms',
					'default_on_front' => '3000ms',
					'show_if'          => array(
						'trigger_type' => 'on_page_load',
					),
					'tab_slug'         => 'general',
					'toggle_slug'      => 'main_content',
					'description'      => esc_html__( 'Move the slider or input the value to apply delay in activating the modal.', 'divi-plus'),
				),
				're_rendering'               		=> array(
					'label'            => esc_html__( 'Re-render modal on reloading/revisiting the page', 'divi-plus' ),
					'type'             => 'yes_no_button',
					'option_category'  => 'configuration',
					'options'          => array(
						'off' => esc_html__( 'No', 'divi-plus' ),
						'on'  => esc_html__( 'Yes', 'divi-plus' ),
					),
					'default'          => 'on',
					'default_on_front' => 'on',
					'show_if'          => array(
						'trigger_type' => 'on_page_load',
					),
					'tab_slug'         => 'general',
					'toggle_slug'      => 'main_content',
					'description'      => esc_html__( 'Here you can choose whether to re-render modal on reload or revisiting the page after user closed the modal.', 'divi-plus'),
				),
				'trigger_element_type'             => array(
					'label'            => esc_html__( 'Trigger Element Type', 'divi-plus' ),
					'type'             => 'select',
					'option_category'  => 'configuration',
					'options'          => array(
						'button'     	=> esc_html__( 'Button', 'divi-plus' ),
						'image'      	=> esc_html__( 'Image', 'divi-plus' ),
						'icon'       	=> esc_html__( 'Icon', 'divi-plus' ),
						'text'       	=> esc_html__( 'Text', 'divi-plus' ),
						'element_id' 	=> esc_html__( 'Element CSS ID', 'divi-plus' ),
						'element_class' => esc_html__( 'Element CSS Class', 'divi-plus' ),
					),
					'default'          => 'button',
					'default_on_front' => 'button',
					'show_if'          => array(
						'trigger_type' => 'element',
					),
					'tab_slug'         => 'general',
					'toggle_slug'      => 'main_content',
					'description'      => esc_html__( 'Here you can select the trigger element you want to use for activating the modal.', 'divi-plus'),
				),
				'trigger_element_id'               => array(
					'label'           => esc_html__( 'Element CSS ID', 'divi-plus' ),
					'type'            => 'text',
					'option_category' => 'basic_option',
					'show_if'         => array(
						'trigger_type'         => 'element',
						'trigger_element_type' => 'element_id',
					),
					'tab_slug'        => 'general',
					'toggle_slug'     => 'main_content',
					'description'     => esc_html__( 'Here you can input the CSS ID(without #) of the element to be used as a trigger for activating the modal.', 'divi-plus'),
				),
				'trigger_element_class'               => array(
					'label'           => esc_html__( 'Element CSS Class', 'divi-plus' ),
					'type'            => 'text',
					'option_category' => 'basic_option',
					'show_if'         => array(
						'trigger_type'         => 'element',
						'trigger_element_type' => 'element_class',
					),
					'tab_slug'        => 'general',
					'toggle_slug'     => 'main_content',
					'description'     => esc_html__( 'Here you can input the CSS Class(without .) of the element to be used as a trigger for activating the modal.', 'divi-plus'),
				),
				'trigger_button_text'              => array(
					'label'            => esc_html__( 'Button Text', 'divi-plus' ),
					'type'             => 'text',
					'option_category'  => 'basic_option',
					'default'          => 'Click Me',
					'default_on_front' => 'Click Me',
					'show_if'          => array(
						'trigger_type'         => 'element',
						'trigger_element_type' => 'button',
					),
					'tab_slug'         => 'general',
					'toggle_slug'      => 'main_content',
					'description'        => esc_html__( 'Here you can input the text to be used for the button of modal trigger.', 'divi-plus'),
				),
				'trigger_icon'                     => array(
					'label'            => esc_html__( 'Icon', 'divi-plus' ),
					'type'             => 'select_icon',
					'option_category'  => 'button',
					'class'            => array( 'et-pb-font-icon' ),
					'default'          => ET_BUILDER_PRODUCT_VERSION < '4.13.0' ? '%%40%%' : '&#x49;||divi||400',
					'show_if'          => array(
						'trigger_type'         => 'element',
						'trigger_element_type' => 'icon',
					),
					'tab_slug'         => 'general',
					'toggle_slug'      => 'main_content',
					'description'      => esc_html__( 'Here you can select an icon to be used for the modal trigger.', 'divi-plus'),
				),
				'trigger_image'                    => array(
					'label'              => esc_html__( 'Image', 'divi-plus' ),
					'type'               => 'upload',
					'option_category'    => 'basic_option',
					'upload_button_text' => esc_attr__( 'Upload an image', 'divi-plus' ),
					'choose_text'        => esc_attr__( 'Choose an Image', 'divi-plus' ),
					'update_text'        => esc_attr__( 'Set As Image', 'divi-plus' ),
					'show_if'            => array(
						'trigger_type'         => 'element',
						'trigger_element_type' => 'image',
					),
					'tab_slug'           => 'general',
					'toggle_slug'        => 'main_content',
					'description'        => esc_html__( 'Here you can add an image to be used for the modal trigger.', 'divi-plus'),
				),
				'trigger_image_alt'                => array(
					'label'           => esc_html__( 'Image Alternative Text', 'divi-plus' ),
					'type'            => 'text',
					'option_category' => 'basic_option',
					'show_if'         => array(
						'trigger_type'         => 'element',
						'trigger_element_type' => 'image',
					),
					'tab_slug'        => 'general',
					'toggle_slug'     => 'main_content',
					'description'     => esc_html__( 'Here you can input the text to be used for the image of modal trigger as alternative text.', 'divi-plus'),
				),
				'trigger_text'                     => array(
					'label'            => esc_html__( 'Text', 'divi-plus' ),
					'type'             => 'text',
					'option_category'  => 'basic_option',
					'default'          => 'Enter modal trigger text here',
					'default_on_front' => 'Enter modal trigger text here',
					'show_if'          => array(
						'trigger_type'         => 'element',
						'trigger_element_type' => 'text',
					),
					'tab_slug'         => 'general',
					'toggle_slug'      => 'main_content',
					'description'      => esc_html__( 'Here you can input the text to be used as modal trigger.', 'divi-plus'),
				),
				'close_on_esc'                      => array(
					'label'            => esc_html__( 'Close Modal On ESC', 'divi-plus' ),
					'type'             => 'yes_no_button',
					'option_category'  => 'configuration',
					'options'          => array(
						'off' => esc_html__( 'No', 'divi-plus' ),
						'on'  => esc_html__( 'Yes', 'divi-plus' ),
					),
					'default'          => 'off',
					'default_on_front' => 'off',
					'tab_slug'         => 'general',
					'toggle_slug'      => 'main_content',
					'description'      => esc_html__( 'Here you can choose whether or not to close the modal on ESC key.', 'divi-plus'),
				),
				'show_header'                      => array(
					'label'            => esc_html__( 'Show Header', 'divi-plus' ),
					'type'             => 'yes_no_button',
					'option_category'  => 'configuration',
					'options'          => array(
						'off' => esc_html__( 'No', 'divi-plus' ),
						'on'  => esc_html__( 'Yes', 'divi-plus' ),
					),
					'default'          => 'on',
					'default_on_front' => 'on',
					'tab_slug'         => 'general',
					'toggle_slug'      => 'modal_header',
					'description'      => esc_html__( 'Here you can choose whether or not to display header with modal.', 'divi-plus'),
				),
				'show_modal_title'                 => array(
					'label'            => esc_html__( 'Show Modal Title', 'divi-plus' ),
					'type'             => 'yes_no_button',
					'option_category'  => 'configuration',
					'options'          => array(
						'off' => esc_html__( 'No', 'divi-plus' ),
						'on'  => esc_html__( 'Yes', 'divi-plus' ),
					),
					'default'          => 'on',
					'default_on_front' => 'on',
					'show_if'          => array(
						'show_header' => 'on',
					),
					'tab_slug'         => 'general',
					'toggle_slug'      => 'modal_header',
					'description'      => esc_html__( 'Here you can choose whether or not to display title on the modal.', 'divi-plus')
				),
				'modal_title'                      => array(
					'label'           => esc_html__( 'Modal Title', 'divi-plus' ),
					'type'            => 'text',
					'option_category' => 'basic_option',
					'dynamic_content'  => 'text',
					'show_if'         => array(
						'show_header'      => 'on',
						'show_modal_title' => 'on',
					),
					'tab_slug'        => 'general',
					'toggle_slug'     => 'modal_header',
					'description'     => esc_html__( 'Here you can input the text to be used for the modal title.', 'divi-plus')
				),
				'show_close_icon'                  => array(
					'label'            => esc_html__( 'Show Close Icon', 'divi-plus' ),
					'type'             => 'yes_no_button',
					'option_category'  => 'configuration',
					'options'          => array(
						'off' => esc_html__( 'No', 'divi-plus' ),
						'on'  => esc_html__( 'Yes', 'divi-plus' ),
					),
					'default'          => 'on',
					'default_on_front' => 'on',
					'show_if'          => array(
						'show_header' => 'on',
					),
					'tab_slug'         => 'general',
					'toggle_slug'      => 'modal_header',
					'description'      => esc_html__( 'Here you can choose whether or not to display close icon on the modal.', 'divi-plus')
				),
				'show_footer'                      => array(
					'label'            => esc_html__( 'Show Footer', 'divi-plus' ),
					'type'             => 'yes_no_button',
					'option_category'  => 'configuration',
					'options'          => array(
						'off' => esc_html__( 'No', 'divi-plus' ),
						'on'  => esc_html__( 'Yes', 'divi-plus' ),
					),
					'default'          => 'on',
					'default_on_front' => 'on',
					'tab_slug'         => 'general',
					'toggle_slug'      => 'modal_footer',
					'description'      => esc_html__( 'Here you can choose whether or not to display footer with modal.', 'divi-plus'),
				),
				'close_button_text'                => array(
					'label'            => esc_html__( 'Close Button Text', 'divi-plus' ),
					'type'             => 'text',
					'option_category'  => 'basic_option',
					'default'          => 'Close',
					'default_on_front' => 'Close',
					'show_if'          => array(
						'show_footer' => 'on',
					),
					'tab_slug'         => 'general',
					'toggle_slug'      => 'modal_footer',
					'description'      => esc_html__( 'Here you can input the text to be used for the modal close button.', 'divi-plus'),
				),
				'modal_content_type'               => array(
					'label'            => esc_html__( 'Modal Content Type', 'divi-plus' ),
					'type'             => 'select',
					'option_category'  => 'configuration',
					'options'          => array(
						'text'   => esc_html__( 'Text', 'divi-plus' ),
						'image'  => esc_html__( 'Image', 'divi-plus' ),
						'video'  => esc_html__( 'Video', 'divi-plus' ),
						'layout' => esc_html__( 'Divi Library Layout', 'divi-plus' ),
					),
					'default'          => 'text',
					'default_on_front' => 'text',
					'tab_slug'         => 'general',
					'toggle_slug'      => 'modal_body',
					'description'      => esc_html__( 'Here you can choose what type of content you want to display inside modal body.', 'divi-plus'),
				),
				'content'                          => array(
					'label'           => esc_html__( 'Text', 'divi-plus' ),
					'type'            => 'tiny_mce',
					'option_category' => 'basic_option',
					'dynamic_content' => 'text',
					'show_if'         => array(
						'modal_content_type' => 'text',
					),
					'tab_slug'        => 'general',
					'toggle_slug'     => 'modal_body',
					'description'     => esc_html__( 'Here you can input the text to be used for the modal content.', 'divi-plus'),
				),
				'modal_content_image'              => array(
					'label'              => esc_html__( 'Image', 'divi-plus' ),
					'type'               => 'upload',
					'option_category'    => 'basic_option',
					'upload_button_text' => esc_attr__( 'Upload an image', 'divi-plus' ),
					'choose_text'        => esc_attr__( 'Choose an Image', 'divi-plus' ),
					'update_text'        => esc_attr__( 'Set As Image', 'divi-plus' ),
					'show_if'            => array(
						'modal_content_type' => 'image',
					),
					'tab_slug'           => 'general',
					'toggle_slug'        => 'modal_body',
					'description'        => esc_html__( 'Here you can add an image to be used for the modal content.', 'divi-plus'),
				),
				'modal_content_image_alt'          => array(
					'label'           => esc_html__( 'Image Alternative Text', 'divi-plus' ),
					'type'            => 'text',
					'option_category' => 'basic_option',
					'show_if'         => array(
						'modal_content_type' => 'image',
					),
					'tab_slug'        => 'general',
					'toggle_slug'     => 'modal_body',
					'description'     => esc_html__( 'Here you can input the text to be used for the modal content image as alternative text.', 'divi-plus'),
				),
				'modal_content_video'              => array(
					'label'              => esc_html__( 'Video MP4 File Or URL', 'divi-plus' ),
					'type'               => 'upload',
					'option_category'    => 'basic_option',
					'data_type'          => 'video',
					'upload_button_text' => esc_attr__( 'Upload a video', 'divi-plus' ),
					'choose_text'        => esc_attr__( 'Choose a Video MP4 File', 'divi-plus' ),
					'update_text'        => esc_attr__( 'Set As Video', 'divi-plus' ),
					'show_if'            => array(
						'modal_content_type' => 'video',
					),
					'tab_slug'           => 'general',
					'toggle_slug'        => 'modal_body',
					'description'        => esc_html__( 'Upload your desired video in .MP4 format, or enter in the URL of the video you would like to display inside modal content.', 'divi-plus' ),
				),
				'autoplay_video'                   => array(
					'label'            => esc_html__( 'Autoplay Video', 'divi-plus' ),
					'type'             => 'yes_no_button',
					'option_category'  => 'configuration',
					'options'          => array(
						'off' => esc_html__( 'No', 'divi-plus' ),
						'on'  => esc_html__( 'Yes', 'divi-plus' ),
					),
					'default'          => 'off',
					'default_on_front' => 'off',
					'show_if'          => array(
						'modal_content_type' => 'video',
					),
					'tab_slug'         => 'general',
					'toggle_slug'      => 'modal_body',
					'description'      => esc_html__( 'Here you can choose whether or not to autoplay video. We can not guarantee autoplay for all platforms video. Videos which support \'autoplay=1\' in their url structure can be autoplayed eg. YouTube, Vimeo. Videos uploaded in media library can be supported.', 'divi-plus'),
				),
				'modal_content_layout'             => array(
					'label'            => esc_html__( 'Divi Library Layout', 'divi-plus' ),
					'type'             => 'select',
					'option_category'  => 'configuration',
					'options'          => $layout_list,
					'show_if'          => array(
						'modal_content_type' => 'layout',
					),
					'default'          => '0',
					'default_on_front' => '0',
					'tab_slug'         => 'general',
					'toggle_slug'      => 'modal_body',
					'description'      => esc_html__( 'Here you can select the layouts saved in your Divi library to be used for the modal content.', 'divi-plus' ),
				),
				'disable_website_scroll' => array(
					'label'             => esc_html__( 'Disable Website scroll', 'divi-plus' ),
	                'type'              => 'yes_no_button',
					'option_category'   => 'configuration',
					'options'           => array(
						'off' => esc_html__( 'No', 'divi-plus' ),
						'on'  => esc_html__( 'Yes', 'divi-plus' ),
					),
					'default'           => 'on',
					'tab_slug'          => 'general',
	                'toggle_slug'       => 'scroll_settings',
	                'description'       => esc_html__( 'Here you can select whether to disable webpage scroll when the modal pops up.', 'divi-plus' ),
				),
				'modal_body_scrollbar' => array(
					'label'             => esc_html__( 'Modal Scrollbar', 'divi-plus' ),
	                'type'              => 'select',
	                'option_category'   => 'configuration',
	                'options'           => array(
	                    'default'  	=> esc_html__( 'Show', 'divi-plus' ),
	                    'hide' 		=> esc_html__( 'Hide', 'divi-plus' ),
	                ),
	                'default'           => 'default',
	                'tab_slug'          => 'general',
	                'toggle_slug'       => 'scroll_settings',
	                'description'       => esc_html__( 'Here you can select whether to show or hide scrollbar. This is totally depends on the browser, we can not guarantee the result of it.', 'divi-plus' ),
				),
				'trigger_icon_font_size'           => array(
					'label'            => esc_html__( 'Trigger Icon Font Size', 'divi-plus' ),
					'type'             => 'range',
					'option_category'  => 'font_option',
					'range_settings'   => array(
						'min'  => '1',
						'max'  => '120',
						'step' => '1',
					),
					'default'          => '52px',
					'default_on_front' => '52px',
					'mobile_options'   => true,
					'show_if'          => array(
						'trigger_type'         => 'element',
						'trigger_element_type' => 'icon',
					),
					'tab_slug'         => 'advanced',
					'toggle_slug'      => 'trigger_icon',
					'description'      => esc_html__( 'Move the slider or input the value to increase or decrease trigger icon size.', 'divi-plus' ),

				),
				'trigger_icon_color'               => array(
					'label'            => esc_html__( 'Trigger Icon Color', 'divi-plus' ),
					'type'             => 'color-alpha',
					'default'          => $et_accent_color,
					'default_on_front' => $et_accent_color,
					'show_if'          => array(
						'trigger_type'         => 'element',
						'trigger_element_type' => 'icon',
					),
					'tab_slug'         => 'advanced',
					'toggle_slug'      => 'trigger_icon',
					'description'      => esc_html__( 'Here you can define a custom color to be used for the trigger icon.', 'divi-plus' ),
				),
				'modal_custom_margin'              => array(
					'label'            => esc_html__( 'Modal Margin', 'divi-plus' ),
					'type'             => 'custom_margin',
					'option_category'  => 'layout',
					'mobile_options'   => true,
					'default'          => '',
					'default_on_front' => '',
					'tab_slug'         => 'advanced',
					'toggle_slug'      => 'margin_padding',
					'description'      => esc_html__( 'Margin adds extra space to the outside of the element, increasing the distance between the element and other items on the page.', 'divi-plus' ),
				),
				'modal_custom_padding'             => array(
					'label'            => esc_html__( 'Modal Padding', 'divi-plus' ),
					'type'             => 'custom_padding',
					'option_category'  => 'layout',
					'mobile_options'   => true,
					'default'          => '',
					'default_on_front' => '',
					'tab_slug'         => 'advanced',
					'toggle_slug'      => 'margin_padding',
					'description'      => esc_html__( 'Padding adds extra space to the inside of the element, increasing the distance between the edge of the element and its inner contents.', 'divi-plus' ),
				),
				'modal_header_custom_margin'       => array(
					'label'            => esc_html__( 'Modal Header Margin', 'divi-plus' ),
					'type'             => 'custom_margin',
					'option_category'  => 'layout',
					'mobile_options'   => true,
					'default'          => '',
					'default_on_front' => '',
					'show_if'          => array(
						'show_header' => 'on',
					),
					'tab_slug'         => 'advanced',
					'toggle_slug'      => 'margin_padding',
					'description'      => esc_html__( 'Margin adds extra space to the outside of the element, increasing the distance between the element and other items on the page.', 'divi-plus' ),
				),
				'modal_header_custom_padding'      => array(
					'label'            => esc_html__( 'Modal Header Padding', 'divi-plus' ),
					'type'             => 'custom_padding',
					'option_category'  => 'layout',
					'mobile_options'   => true,
					'default'          => '20px|20px|20px|20px|true|true',
					'default_on_front' => '20px|20px|20px|20px|true|true',
					'show_if'          => array(
						'show_header' => 'on',
					),
					'tab_slug'         => 'advanced',
					'toggle_slug'      => 'margin_padding',
					'description'      => esc_html__( 'Padding adds extra space to the inside of the element, increasing the distance between the edge of the element and its inner contents.', 'divi-plus' ),
				),
				'modal_body_custom_margin'         => array(
					'label'            => esc_html__( 'Modal Body Margin', 'divi-plus' ),
					'type'             => 'custom_margin',
					'option_category'  => 'layout',
					'mobile_options'   => true,
					'default'          => '',
					'default_on_front' => '',
					'tab_slug'         => 'advanced',
					'toggle_slug'      => 'margin_padding',
					'description'      => esc_html__( 'Margin adds extra space to the outside of the element, increasing the distance between the element and other items on the page.', 'divi-plus' ),
				),
				'modal_body_custom_padding'        => array(
					'label'            => esc_html__( 'Modal Body Padding', 'divi-plus' ),
					'type'             => 'custom_padding',
					'option_category'  => 'layout',
					'mobile_options'   => true,
					'default'          => '20px|20px|20px|20px|true|true',
					'default_on_front' => '20px|20px|20px|20px|true|true',
					'tab_slug'         => 'advanced',
					'toggle_slug'      => 'margin_padding',
					'description'      => esc_html__( 'Padding adds extra space to the inside of the element, increasing the distance between the edge of the element and its inner contents.', 'divi-plus' ),
				),
				'modal_footer_custom_margin'       => array(
					'label'            => esc_html__( 'Modal Footer Margin', 'divi-plus' ),
					'type'             => 'custom_margin',
					'option_category'  => 'layout',
					'mobile_options'   => true,
					'default'          => '',
					'default_on_front' => '',
					'show_if'          => array(
						'show_footer' => 'on',
					),
					'tab_slug'         => 'advanced',
					'toggle_slug'      => 'margin_padding',
					'description'      => esc_html__( 'Margin adds extra space to the outside of the element, increasing the distance between the element and other items on the page.', 'divi-plus' ),
				),
				'modal_footer_custom_padding'      => array(
					'label'            => esc_html__( 'Modal Footer Padding', 'divi-plus' ),
					'type'             => 'custom_padding',
					'option_category'  => 'layout',
					'mobile_options'   => true,
					'default'          => '20px|20px|20px|20px|true|true',
					'default_on_front' => '20px|20px|20px|20px|true|true',
					'show_if'          => array(
						'show_footer' => 'on',
					),
					'tab_slug'         => 'advanced',
					'toggle_slug'      => 'margin_padding',
					'description'      => esc_html__( 'Padding adds extra space to the inside of the element, increasing the distance between the edge of the element and its inner contents.', 'divi-plus' ),
				),
				'trigger_element_bg_color'         => array(
					'label'             => esc_html__( 'Trigger Element Background', 'divi-plus' ),
					'type'              => 'background-field',
					'base_name'         => 'trigger_element_bg',
					'context'           => 'trigger_element_bg_color',
					'option_category'   => 'button',
					'custom_color'      => true,
					'background_fields' => $this->generate_background_options( 'trigger_element_bg', 'button', 'general', 'background', 'trigger_element_bg_color' ),
					'hover'             => false,
					'mobile_options'    => true,
					'show_if_not'       => array(
						'trigger_element_type' => 'element_id',
						'trigger_type'         => 'on_page_load',
					),
					'tab_slug'          => 'general',
					'toggle_slug'       => 'background',
					'description'       => esc_html__( 'Customize the background of the Trigger Element by adjusting background color, gradient, and image.', 'divi-plus' ),
				),
				'modal_overlay_bg_color'                   => array(
					'label'             => esc_html__( 'Modal Background Overlay', 'divi-plus' ),
					'type'              => 'background-field',
					'base_name'         => 'modal_overlay_bg',
					'context'           => 'modal_overlay_bg_color',
					'option_category'   => 'button',
					'custom_color'      => true,
					'background_fields' => $this->generate_background_options( 'modal_overlay_bg', 'button', 'general', 'background', 'modal_overlay_bg_color' ),
					'hover'             => false,
					'mobile_options'    => true,
					'tab_slug'          => 'general',
					'toggle_slug'       => 'background',
					'description'       => esc_html__( 'Customize the background of the modal background overlay by adjusting background color, gradient, and image.', 'divi-plus' ),
				),
				'modal_bg_color'                   => array(
					'label'             => esc_html__( 'Modal Background', 'divi-plus' ),
					'type'              => 'background-field',
					'base_name'         => 'modal_bg',
					'context'           => 'modal_bg_color',
					'option_category'   => 'button',
					'custom_color'      => true,
					'background_fields' => $this->generate_background_options( 'modal_bg', 'button', 'general', 'background', 'modal_bg_color' ),
					'hover'             => false,
					'mobile_options'    => true,
					'tab_slug'          => 'general',
					'toggle_slug'       => 'background',
					'description'       => esc_html__( 'Customize the background of the modal by adjusting background color, gradient, and image.', 'divi-plus' ),
				),
				'modal_header_bg_color'            => array(
					'label'             => esc_html__( 'Modal Header Background', 'divi-plus' ),
					'type'              => 'background-field',
					'base_name'         => 'modal_header_bg',
					'context'           => 'modal_header_bg_color',
					'option_category'   => 'button',
					'custom_color'      => true,
					'background_fields' => $this->generate_background_options( 'modal_header_bg', 'button', 'general', 'background', 'modal_header_bg_color' ),
					'hover'             => false,
					'mobile_options'    => true,
					'show_if'           => array(
						'show_header' => 'on',
					),
					'tab_slug'          => 'general',
					'toggle_slug'       => 'background',
					'description'       => esc_html__( 'Customize the background of the modal header by adjusting background color, gradient, and image.', 'divi-plus' ),
				),
				'modal_body_bg_color'              => array(
					'label'             => esc_html__( 'Modal Body Background', 'divi-plus' ),
					'type'              => 'background-field',
					'base_name'         => 'modal_body_bg',
					'context'           => 'modal_body_bg_color',
					'option_category'   => 'button',
					'custom_color'      => true,
					'background_fields' => $this->generate_background_options( 'modal_body_bg', 'button', 'general', 'background', 'modal_body_bg_color' ),
					'hover'             => false,
					'mobile_options'    => true,
					'tab_slug'          => 'general',
					'toggle_slug'       => 'background',
					'description'       => esc_html__( 'Customize the background of the modal body by adjusting background color, gradient, and image.', 'divi-plus' ),
				),
				'modal_footer_bg_color'            => array(
					'label'             => esc_html__( 'Modal Footer Background', 'divi-plus' ),
					'type'              => 'background-field',
					'base_name'         => 'modal_footer_bg',
					'context'           => 'modal_footer_bg_color',
					'option_category'   => 'button',
					'custom_color'      => true,
					'background_fields' => $this->generate_background_options( 'modal_footer_bg', 'button', 'general', 'background', 'modal_footer_bg_color' ),
					'hover'             => false,
					'mobile_options'    => true,
					'show_if'           => array(
						'show_footer' => 'on',
					),
					'tab_slug'          => 'general',
					'toggle_slug'       => 'background',
					'description'       => esc_html__( 'Customize the background of the modal footer by adjusting background color, gradient, and image.', 'divi-plus' ),
				),
				'modal_close_icon'                 => array(
					'type'             => 'skip',
					'default'          => ET_BUILDER_PRODUCT_VERSION < '4.13.0' ? '%%48%%' : '&#x51;||divi||400',
					'show_if'          => array(
						'show_header'     => 'on',
						'show_close_icon' => 'on',
					),
					'tab_slug'         => 'advanced',
					'toggle_slug'      => 'modal_close_icon',
				),
				'modal_close_icon_font_size'       => array(
					'label'            => esc_html__( 'Close Icon Font Size', 'divi-plus' ),
					'type'             => 'range',
					'option_category'  => 'font_option',
					'range_settings'   => array(
						'min'  => '1',
						'max'  => '120',
						'step' => '1',
					),
					'default'          => '32px',
					'default_on_front' => '32px',
					'mobile_options'   => true,
					'show_if'          => array(
						'show_header'     => 'on',
						'show_close_icon' => 'on',
					),
					'tab_slug'         => 'advanced',
					'toggle_slug'      => 'modal_close_icon',
					'description'      => esc_html__( 'Move the slider or input the value to increase or decrease close icon size.', 'divi-plus' ),
				),
				'modal_close_icon_color'           => array(
					'label'            => esc_html__( 'Close Icon Color', 'divi-plus' ),
					'type'             => 'color-alpha',
					'default'          => '#000',
					'default_on_front' => '#000',
					'show_if'          => array(
						'show_header'     => 'on',
						'show_close_icon' => 'on',
					),
					'tab_slug'         => 'advanced',
					'toggle_slug'      => 'modal_close_icon',
					'description'      => esc_html__( 'Here you can define a custom color to be used for the close icon.', 'divi-plus' ),
				),
				'modal_position'                   => array(
					'label'            => esc_html__( 'Modal Position', 'divi-plus' ),
					'type'             => 'select',
					'option_category'  => 'configuration',
					'options'          => array(
						'top_left'      => esc_html__( 'Top Left', 'divi-plus' ),
						'top_right'     => esc_html__( 'Top Right', 'divi-plus' ),
						'top_center'    => esc_html__( 'Top Center', 'divi-plus' ),
						'bottom_left'   => esc_html__( 'Bottom Left', 'divi-plus' ),
						'bottom_right'  => esc_html__( 'Bottom Right', 'divi-plus' ),
						'bottom_center' => esc_html__( 'Bottom Center', 'divi-plus' ),
						'center'        => esc_html__( 'Center', 'divi-plus' ),
					),
					'default'          => 'center',
					'default_on_front' => 'center',
					'tab_slug'         => 'advanced',
					'toggle_slug'      => 'modal_sizing',
					'description'      => esc_html__( 'Here you can select where you want to place the modal on the page once triggered.', 'divi-plus' ),
				),
				'modal_animation_style'            => array(
					'label'           => esc_html__( 'Modal Animation Style', 'divi-plus' ),
					'type'            => 'select_animation',
					'option_category' => 'configuration',
					'options'         => array(
						'none'   => esc_html__( 'None', 'divi-plus' ),
						'fade'   => esc_html__( 'Fade', 'divi-plus' ),
						'slide'  => esc_html__( 'Slide', 'et_builder' ),
						'bounce' => esc_html__( 'Bounce', 'et_builder' ),
						'zoom'   => esc_html__( 'Zoom', 'et_builder' ),
						'flip'   => esc_html__( 'Flip', 'et_builder' ),
						'fold'   => esc_html__( 'Fold', 'et_builder' ),
						'roll'   => esc_html__( 'Roll', 'et_builder' ),
					),
					'default'         => 'fade',
					'tab_slug'        => 'advanced',
					'toggle_slug'     => 'animation',
					'description'     => esc_html__( 'Pick an animation style to enable animations for the modal. Once enabled, you will be able to customize your animation style further.', 'divi-plus' ),
				),
				'modal_animation_direction'        => array(
					'label'           => esc_html__( 'Modal Animation Direction', 'divi-plus' ),
					'type'            => 'select',
					'option_category' => 'configuration',
					'options'         => array(
						'center' => esc_html__( 'Center', 'et_builder' ),
						'right'  => esc_html__( 'Right', 'et_builder' ),
						'left'   => esc_html__( 'Left', 'et_builder' ),
						'up'     => esc_html__( 'Up', 'et_builder' ),
						'down'   => esc_html__( 'Down', 'et_builder' ),
					),
					'default'         => 'center',
					'show_if_not'     => array(
						'modal_animation_style' => array( 'none', 'fade' ),
					),
					'mobile_options'  => true,
					'tab_slug'        => 'advanced',
					'toggle_slug'     => 'animation',
					'description'     => esc_html__( 'Pick from up to five different animation directions, each of which will adjust the starting and ending position of your animated element.', 'divi-plus' ),
				),
				'modal_animation_duration'         => array(
					'label'           => esc_html__( 'Modal Animation Duration', 'divi-plus' ),
					'type'            => 'range',
					'option_category' => 'configuration',
					'range_settings'  => array(
						'min'  => 0,
						'max'  => 2000,
						'step' => 50,
					),
					'default'         => '1000ms',
					'validate_unit'   => true,
					'fixed_unit'      => 'ms',
					'fixed_range'     => true,
					'show_if_not'     => array(
						'modal_animation_style' => 'none',
					),
					'reset_animation' => true,
					'mobile_options'  => true,
					'tab_slug'        => 'advanced',
					'toggle_slug'     => 'animation',
					'description'     => esc_html__( 'Speed up or slow down your animation by adjusting the animation duration. Units are in milliseconds and the default animation duration is one second.', 'divi-plus' ),
				),
				'modal_animation_intensity'        => array(
					'label'           => esc_html__( 'Modal Animation Intensity', 'divi-plus' ),
					'type'            => 'range',
					'option_category' => 'configuration',
					'range_settings'  => array(
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					),
					'default'         => '50%',
					'validate_unit'   => true,
					'fixed_unit'      => '%',
					'fixed_range'     => true,
					'show_if'         => array(
						'modal_animation_style' => array( 'slide', 'zoom', 'flip', 'fold', 'roll' ),
					),
					'reset_animation' => true,
					'mobile_options'  => true,
					'tab_slug'        => 'advanced',
					'toggle_slug'     => 'animation',
					'description'     => esc_html__( 'Intensity effects how subtle or aggressive your animation will be. Lowering the intensity will create a smoother and more subtle animation while increasing the intensity will create a snappier more aggressive animation.', 'divi-plus' ),
				),
				'modal_animation_starting_opacity' => array(
					'label'           => esc_html__( 'Modal Animation Starting Opacity', 'divi-plus' ),
					'type'            => 'range',
					'option_category' => 'configuration',
					'range_settings'  => array(
						'min'       => 0,
						'max'       => 100,
						'step'      => 1,
						'min_limit' => 0,
						'max_limit' => 100,
					),
					'default'         => '0%',
					'validate_unit'   => true,
					'fixed_unit'      => '%',
					'fixed_range'     => true,
					'reset_animation' => true,
					'mobile_options'  => true,
					'show_if_not'     => array(
						'modal_animation_style' => 'none',
					),
					'tab_slug'        => 'advanced',
					'toggle_slug'     => 'animation',
					'description'     => esc_html__( 'By increasing the starting opacity, you can reduce or remove the fade effect that is applied to all animation styles.', 'divi-plus' ),
				),
				'modal_animation_speed_curve'      => array(
					'label'           => esc_html__( 'Modal Animation Speed Curve', 'divi-plus' ),
					'type'            => 'select',
					'option_category' => 'configuration',
					'options'         => array(
						'ease-in-out' => esc_html__( 'Ease-In-Out', 'divi-plus' ),
						'ease'        => esc_html__( 'Ease', 'divi-plus' ),
						'ease-in'     => esc_html__( 'Ease-In', 'divi-plus' ),
						'ease-out'    => esc_html__( 'Ease-Out', 'divi-plus' ),
						'linear'      => esc_html__( 'Linear', 'divi-plus' ),
					),
					'mobile_options'  => true,
					'default'         => 'ease-in-out',
					'show_if_not'     => array(
						'modal_animation_style' => 'none',
					),
					'tab_slug'        => 'advanced',
					'toggle_slug'     => 'animation',
					'description'     => esc_html__( 'Here you can adjust the easing method of your animation. Easing your animation in and out will create a smoother effect when compared to a linear speed curve.', 'divi-plus' ),
				),
			),
			$this->generate_background_options( 'trigger_element_bg', 'skip', 'general', 'background', 'trigger_element_bg_color' ),
			$this->generate_background_options( 'modal_overlay_bg', 'skip', 'general', 'background', 'modal_overlay_bg_color' ),
			$this->generate_background_options( 'modal_bg', 'skip', 'general', 'background', 'modal_bg_color' ),
			$this->generate_background_options( 'modal_header_bg', 'skip', 'general', 'background', 'modal_header_bg_color' ),
			$this->generate_background_options( 'modal_body_bg', 'skip', 'general', 'background', 'modal_body_bg_color' ),
			$this->generate_background_options( 'modal_footer_bg', 'skip', 'general', 'background', 'modal_footer_bg_color' )
		);
	}

	public static function get_video( $args = array() ) {
		$defaults = array(
			'modal_content_video' => '',
		);

		$args = wp_parse_args( $args, $defaults );

		if ( empty( $args['modal_content_video'] ) ) {
			return '';
		}

		$video_src = '';

		if ( false !== et_pb_check_oembed_provider( esc_url( $args['modal_content_video'] ) ) ) {
			$video_src = wp_oembed_get( esc_url( $args['modal_content_video'] ) );
		} else {
			$video_src = sprintf(
				'
                <video controls>
                    %1$s
                </video>',
				( '' !== $args['modal_content_video'] ? sprintf( '<source type="video/mp4" src="%s" />', esc_url( $args['modal_content_video'] ) ) : '' )
			);

			wp_enqueue_style( 'wp-mediaelement' );
			wp_enqueue_script( 'wp-mediaelement' );
		}

		return $video_src;
	}

	public static function get_library_layout( $args = array() ) {
		$defaults = array(
			'modal_content_layout' => 0,
		);

		$args = wp_parse_args( $args, $defaults );

		if ( 0 === intval( $args['modal_content_layout'] ) ) {
			return '';
		}

		$layout = do_shortcode( dipl_modal_remove_shortcode( get_the_content( null, false, intval( $args['modal_content_layout'] ) ) ) );

		return $layout;
	}

	public function render( $attrs, $content, $render_slug ) {
		$multi_view                  = et_pb_multi_view_options( $this );
		$modal_id 				     = $this->props['modal_id'];
		$trigger_type                = $this->props['trigger_type'];
		$trigger_delay               = $this->props['trigger_delay'];
		$re_rendering				 = $this->props['re_rendering'];
		$trigger_element_type        = $this->props['trigger_element_type'];
		$trigger_button_text         = $this->props['trigger_button_text'];
		$trigger_image_alt           = $this->props['trigger_image_alt'];
		$show_header                 = $this->props['show_header'];
		$show_footer                 = $this->props['show_footer'];
		$close_button_text           = $this->props['close_button_text'];
		$modal_content_type          = $this->props['modal_content_type'];
		$modal_content_image_alt     = $this->props['modal_content_image_alt'];
		$autoplay_video				 = $this->props['autoplay_video'];
		$modal_content_layout        = $this->props['modal_content_layout'];
		$trigger_element_id          = $this->props['trigger_element_id'];
		$trigger_element_class       = $this->props['trigger_element_class'];
		$custom_trigger_button       = $this->props['custom_trigger_button'];
		$trigger_button_icon         = $this->props['trigger_button_icon'];
		$custom_close_button         = $this->props['custom_close_button'];
		$close_button_icon           = $this->props['close_button_icon'];
		$modal_body_scrollbar		 = $this->props['modal_body_scrollbar'];
		$disable_website_scroll		 = $this->props['disable_website_scroll'];
		$modal_title_level           = $this->props['modal_title_text_level'];
		$modal_position              = $this->props['modal_position'];
		$animation_style             = $this->props['modal_animation_style'];
		$processed_modal_title_level = et_pb_process_header_level( $modal_title_level, 'h2' );
		$animation_durations         = et_pb_responsive_options()->get_property_values( $this->props, 'modal_animation_duration' );

		wp_enqueue_script( 'dipl-modal-custom', PLUGIN_PATH . 'includes/modules/Modal/dipl-modal-custom.min.js', array('jquery'), '1.0.4', true );
		if ( function_exists( 'et_get_dynamic_assets_path' ) ) {
			wp_enqueue_script( 'fitvids', et_get_dynamic_assets_path(true) . '/js/jquery.fitvids.js', array( 'jquery' ), ET_CORE_VERSION, true );
		}

		$file = et_is_builder_plugin_active() ? 'style-dbp' : 'style';
        wp_enqueue_style( 'dipl-modal-style', PLUGIN_PATH . 'includes/modules/Modal/' . $file . '.min.css', array(), '1.0.0' );

		$trigger_element_wrapper = '';
		if ( 'element' === $trigger_type && 'element_id' !== $trigger_element_type && 'element_class' !== $trigger_element_type ) {
			switch ( $trigger_element_type ) {
				case 'button':
					$trigger_element = $this->render_button(
						array(
							'button_text'         => esc_html( $trigger_button_text ),
							'button_text_escaped' => true,
							'button_url'          => '#',
							'button_classname'    => array( 'dipl_modal_trigger_element dipl_modal_trigger_button' ),
							'button_custom'       => isset( $custom_trigger_button ) ? $custom_trigger_button : 'off',
							'custom_icon'         => isset( $trigger_button_icon ) ? $trigger_button_icon : '',
							'has_wrapper'         => true,
							'button_rel'          => esc_html( $this->props['trigger_button_rel'] ),
						)
					);
					break;

				case 'image':
					$trigger_element = $multi_view->render_element(
						array(
							'tag'      => 'img',
							'attrs'    => array(
								'src'   => '{{trigger_image}}',
								'class' => 'dipl_modal_trigger_element dipl_modal_trigger_image',
								'alt'   => esc_html( $trigger_image_alt ),
							),
							'required' => 'trigger_image',
						)
					);
					break;

				case 'icon':
					$trigger_element = $multi_view->render_element(
						array(
							'content'  => '{{trigger_icon}}',
							'attrs'    => array(
								'class' => 'dipl_modal_trigger_element dipl_modal_trigger_icon et-pb-icon',
							),
							'required' => 'trigger_icon',
						)
					);
					break;

				case 'text':
					$trigger_element = $multi_view->render_element(
						array(
							'tag'      => 'div',
							'content'  => '{{trigger_text}}',
							'attrs'    => array(
								'class' => 'dipl_modal_trigger_element dipl_modal_trigger_text',
							),
							'required' => 'trigger_text',
						)
					);
					break;

				default:
					break;
			}
			if ( isset( $trigger_element ) && $trigger_element && '' !== $trigger_element ) {
				$trigger_element_wrapper = sprintf(
					'<div class="dipl_modal_trigger_element_wrapper">%1$s</div>',
					et_core_intentionally_unescaped( $trigger_element, 'html' )
				);
			}
		}

		$modal_title = $multi_view->render_element(
			array(
				'tag'      => esc_html( $processed_modal_title_level ),
				'content'  => '{{modal_title}}',
				'attrs'    => array(
					'class' => 'dipl_modal_header_title',
				),
				'required' => array(
					'show_header'      => 'on',
					'show_modal_title' => 'on',
					'modal_title',
				),
			)
		);

		$modal_close_icon = $multi_view->render_element(
			array(
				'content'  => '{{modal_close_icon}}',
				'attrs'    => array(
					'class' => 'dipl_modal_close dipl_modal_close_icon et-pb-icon',
				),
				'required' => array(
					'show_header'     => 'on',
					'show_close_icon' => 'on',
				),
			)
		);

		if ( '' !== $modal_title ) {
			$modal_title = sprintf(
				'<div class="dipl_modal_header_title_container">%1$s</div>',
				$modal_title
			);
		}

		if ( '' !== $modal_title || '' !== $modal_close_icon ) {
			$modal_header = sprintf(
				'<div class="dipl_modal_header">%1$s%2$s</div>',
				'' !== $modal_close_icon ? $modal_close_icon : '',
				'' !== $modal_title ? $modal_title : ''
			);
		}

		if ( 'on' === $show_footer ) {
			$close_button_text  = '' !== $close_button_text ? $close_button_text : esc_html__( 'Close', 'divi-plus' );
			$modal_close_button = $this->render_button(
				array(
					'button_text'         => esc_html( $close_button_text ),
					'button_text_escaped' => true,
					'button_url'          => '#',
					'button_classname'    => array( 'dipl_modal_close dipl_modal_close_button' ),
					'button_custom'       => isset( $custom_close_button ) ? $custom_close_button : 'off',
					'custom_icon'         => isset( $close_button_icon ) ? $close_button_icon : '',
					'has_wrapper'         => true,
					'button_rel'          => esc_html( $this->props['close_button_rel'] ),
				)
			);

			if ( '' !== $modal_close_button ) {
				$modal_footer = sprintf(
					'<div class="dipl_modal_footer">%1$s</div>',
					$modal_close_button
				);
			}
		}

		switch ( $modal_content_type ) {
			case 'text':
				$modal_body_content = $multi_view->render_element(
					array(
						'tag'      => 'div',
						'content'  => '{{content}}',
						'attrs'    => array(
							'class' => 'dipl_modal_content_text',
						),
						'required' => 'content',
					)
				);
				break;

			case 'image':
				$modal_body_content = $multi_view->render_element(
					array(
						'tag'      => 'img',
						'attrs'    => array(
							'src'   => '{{modal_content_image}}',
							'class' => 'dipl_modal_content_image',
							'alt'   => esc_html( $modal_content_image_alt ),
						),
						'required' => 'modal_content_image',
					)
				);
				break;

			case 'video':
				$video_srcs = self::get_video(
					array(
						'modal_content_video' => $multi_view->get_inherit_value( 'modal_content_video', 'desktop' ),
					)
				);
				$multi_view->set_custom_prop( 'video_srcs', $video_srcs );
				$modal_body_content = $multi_view->render_element(
					array(
						'tag'      => 'div',
						'content'  => '{{video_srcs}}',
						'attrs'    => array(
							'class' => 'dipl_modal_content_video et_pb_video_box',
						),
						'required' => 'modal_content_video',
					)
				);
				break;

			case 'layout':
				if ( 0 !== intval( $modal_content_layout ) ) {
					$modal_body_content = self::get_library_layout(
						array(
							'modal_content_layout' => intval( $modal_content_layout ),
						)
					);
				}
				break;

			default:
				break;
		}

		if ( isset( $modal_body_content ) && '' !== $modal_body_content ) {
			$modal_body = sprintf(
				'<div class="dipl_modal_body">%1$s</div>',
				$modal_body_content
			);
		}

		if ( 'none' !== $animation_style ) {
			$data_animation_duration        = sprintf(
				' data-animation-duration="%1$s"',
				esc_attr( $animation_durations['desktop'] )
			);
			$data_animation_duration_tablet = sprintf(
				' data-animation-duration-tablet="%1$s"',
				esc_attr( $animation_durations['tablet'] )
			);
			$data_animation_duration_phone  = sprintf(
				' data-animation-duration-phone="%1$s"',
				esc_attr( $animation_durations['phone'] )
			);
		}

		if ( 'element' === $trigger_type && 'element_id' === $trigger_element_type ) {
			$data_trigger_element_id = sprintf(
				' data-trigger-element-id="%1$s"',
				esc_attr( $trigger_element_id )
			);
		}

		if ( 'element' === $trigger_type && 'element_class' === $trigger_element_type ) {
			$data_trigger_element_class = sprintf(
				' data-trigger-element-class="%1$s"',
				esc_attr( $trigger_element_class )
			);
		}

		$modal_wrapper_classes = array( 'dipl_modal_' . $modal_position );

		if ( 'on' === $this->props['close_on_esc'] ) {
			array_push( $modal_wrapper_classes, 'dipl_modal_close_on_esc' );
		}

		$modal_wrapper_classes = implode( ' ', $modal_wrapper_classes );

		$modal_wrapper = sprintf(
			'<div class="dipl_modal_wrapper %4$s" data-id="%12$s" data-re-render="%13$s" data-disable-website-scroll="%16$s" data-autoplay-video="%14$s" data-trigger-type="%6$s" data-trigger-delay="%7$s" %8$s %9$s %10$s %11$s %15$s>
                <div class="dipl_modal_inner_wrap%5$s">
                    %1$s
                    %2$s
                    %3$s
                </div>
            </div>',
			isset( $modal_header ) ? $modal_header : '',
			isset( $modal_body ) ? $modal_body : '',
			isset( $modal_footer ) ? $modal_footer : '',
			esc_attr( $modal_wrapper_classes ),
			'none' !== $animation_style ? ' dipl_animated' : '',
			esc_attr( $trigger_type ),
			esc_attr( $trigger_delay ),
			isset( $data_animation_duration ) ? $data_animation_duration : '',
			isset( $data_animation_duration_tablet ) ? $data_animation_duration_tablet : '',
			isset( $data_animation_duration_phone ) ? $data_animation_duration_phone : '',
			isset( $data_trigger_element_id ) ? $data_trigger_element_id : '',
			esc_attr( $modal_id ),
			esc_attr( $re_rendering ),
			'video' === $modal_content_type && 'on' === $autoplay_video ? 'on' : 'off',
			isset( $data_trigger_element_class ) ? $data_trigger_element_class : '',
			esc_attr( $disable_website_scroll )
		);

		if ( 'element' === $trigger_type && 'element_id' !== $trigger_element_type && 'element_class' !== $trigger_element_type ) {
			$output = $trigger_element_wrapper . $modal_wrapper;
		} else {
			$output = $modal_wrapper;
		}

		if (
			'on_page_load' === $trigger_type ||
			'exit_intent' === $trigger_type ||
			( 'element' === $trigger_type && 'element_id' === $trigger_element_type ) ||
			( 'element' === $trigger_type && 'element_class' === $trigger_element_type )
		) {
			self::set_style(
				$render_slug,
				array(
					'selector'    => $this->main_css_element,
					'declaration' => 'margin-bottom: 0 !important;',
				)
			);
		}

		if ( 'element' === $trigger_type && 'icon' === $trigger_element_type ) {
			$trigger_icon_font_sizes = et_pb_responsive_options()->get_property_values( $this->props, 'trigger_icon_font_size' );
			$trigger_icon_colors     = et_pb_responsive_options()->get_property_values( $this->props, 'trigger_icon_color' );
			et_pb_responsive_options()->generate_responsive_css( $trigger_icon_font_sizes, '%%order_class%% .dipl_modal_trigger_icon', 'font-size', $render_slug, '!important;', 'range' );
			et_pb_responsive_options()->generate_responsive_css( $trigger_icon_colors, '%%order_class%% .dipl_modal_trigger_icon', 'color', $render_slug, '!important;', 'type' );
			if ( class_exists( 'ET_Builder_Module_Helper_Style_Processor' ) && method_exists( 'ET_Builder_Module_Helper_Style_Processor', 'process_extended_icon' ) ) {
                $this->generate_styles(
                    array(
                        'utility_arg'    => 'icon_font_family',
                        'render_slug'    => $render_slug,
                        'base_attr_name' => 'trigger_icon',
                        'important'      => true,
                        'selector'       => '%%order_class%% .dipl_modal_trigger_icon',
                        'processor'      => array(
                            'ET_Builder_Module_Helper_Style_Processor',
                            'process_extended_icon',
                        ),
                    )
                );
            }
		}

		$modal_close_icon_colors = et_pb_responsive_options()->get_property_values( $this->props, 'modal_close_icon_color' );
		et_pb_responsive_options()->generate_responsive_css( $modal_close_icon_colors, '%%order_class%%_module .dipl_modal_close_icon', 'color', $render_slug, '!important;', 'type' );

		$modal_close_icon_font_sizes = et_pb_responsive_options()->get_property_values( $this->props, 'modal_close_icon_font_size' );
		et_pb_responsive_options()->generate_responsive_css( $modal_close_icon_font_sizes, '%%order_class%%_module .dipl_modal_close_icon', 'font-size', $render_slug, '!important;', 'range' );

		if ( class_exists( 'ET_Builder_Module_Helper_Style_Processor' ) && method_exists( 'ET_Builder_Module_Helper_Style_Processor', 'process_extended_icon' ) ) {
            $this->generate_styles(
                array(
                    'utility_arg'    => 'icon_font_family',
                    'render_slug'    => $render_slug,
                    'base_attr_name' => 'modal_close_icon',
                    'important'      => true,
                    'selector'       => '%%order_class%%_module .dipl_modal_close_icon',
                    'processor'      => array(
                        'ET_Builder_Module_Helper_Style_Processor',
                        'process_extended_icon',
                    ),
                )
            );
        }

		if ( isset( $this->props['disabled_on'] ) && $this->props['disabled_on']  ) {
			$disabled_on = str_replace( array( 'on', 'off' ), array( 'none', 'block' ), $this->props['disabled_on'] );
			$disabled_on = array_combine( array( 'phone', 'tablet', 'desktop' ), explode( '|', $disabled_on ) );
			$disabled_on = array_reverse( $disabled_on );
			et_pb_responsive_options()->generate_responsive_css( $disabled_on, '%%order_class%%_module', 'display', $render_slug, '!important;', 'display' );
		}

		/* Scrollbar CSS */
        if ( 'hide' === $modal_body_scrollbar ) {
        	self::set_style( $render_slug, array(
                'selector'    => '%%order_class%%_module .dipl_modal_body::-webkit-scrollbar',
                'declaration' => 'display: none;',
            ) );
            self::set_style( $render_slug, array(
                'selector'    => '%%order_class%%_module .dipl_modal_body',
                'declaration' => 'scrollbar-width: none;',
            ) );
        }

		$this->process_modal_animation_css( $animation_style, $render_slug );

		$args = array(
			'render_slug'	=> $render_slug,
			'props'			=> $this->props,
			'fields'		=> $this->fields_unprocessed,
			'module'		=> $this,
			'backgrounds' 	=> array(
				'trigger_element_bg' => array(
					'normal' => "{$this->main_css_element} .dipl_modal_trigger_element",
					'hover' => "{$this->main_css_element} .dipl_modal_trigger_element:hover",
	 			),
	 			'modal_overlay_bg' => array(
	 				'normal' => "{$this->main_css_element}_module .dipl_modal_wrapper",
	 				'hover' => "{$this->main_css_element}_module .dipl_modal_wrapper:hover",
	 			),
	 			'modal_bg' => array(
	 				'normal' => "{$this->main_css_element}_module .dipl_modal_inner_wrap",
	 				'hover' => "{$this->main_css_element}_module .dipl_modal_inner_wrap:hover",
	 			),
	 			'modal_header_bg' => array(
	 				'normal' => "{$this->main_css_element}_module .dipl_modal_header",
	 				'hover' => "{$this->main_css_element}_module .dipl_modal_header:hover",
	 			),
	 			'modal_body_bg' => array(
	 				'normal' => "{$this->main_css_element}_module .dipl_modal_body",
	 				'hover' => "{$this->main_css_element}_module .dipl_modal_body:hover",
	 			),
	 			'modal_footer_bg' => array(
	 				'normal' => "{$this->main_css_element}_module .dipl_modal_footer",
	 				'hover' => "{$this->main_css_element}_module .dipl_modal_footer:hover",
	 			),
			),
		);

		DiviPlusHelper::process_background( $args );
		$fields = array( 'modal_margin_padding' );
		DiviPlusHelper::process_advanced_margin_padding_css( $this, $render_slug, $this->margin_padding, $fields );

		return et_core_intentionally_unescaped( $output, 'html' );
	}

	public function process_modal_animation_css( $animation_style, $render_slug ) {
		// Animation Styles.
		if ( $animation_style && 'none' !== $animation_style ) {
			$animation_styles = et_pb_responsive_options()->get_property_values( $this->props, 'modal_animation_style' );
			if ( 'fade' !== $animation_style ) {
				$directions_list     = array( 'center', 'up', 'right', 'down', 'left' );
				$animation_direction = et_pb_responsive_options()->get_property_values( $this->props, 'modal_animation_direction' );
				// Desktop animation style.
				if ( ! empty( $animation_direction['desktop'] ) ) {
					$animation_style_desktop = in_array( $animation_direction['desktop'], $directions_list, true ) ? $animation_direction['desktop'] : '';
					if ( '' !== $animation_style_desktop ) {
						$animation_styles['desktop'] = $animation_style . '_' . $animation_style_desktop;
					}
				}

				if ( et_pb_responsive_options()->is_responsive_enabled( $this->props, 'modal_animation_direction' ) ) {
					// Tablet animation style.
					if ( ! empty( $animation_direction['tablet'] ) ) {
						$animation_style_tablet = in_array( $animation_direction['tablet'], $directions_list, true ) ? $animation_direction['tablet'] : '';
						if ( '' !== $animation_style_tablet ) {
							$animation_styles['tablet'] = $animation_style . '_' . $animation_style_tablet;
						}
					}
					// Phone animation style.
					if ( ! empty( $animation_direction['phone'] ) ) {
						$animation_style_phone = in_array( $animation_direction['phone'], $directions_list, true ) ? $animation_direction['phone'] : '';
						if ( '' !== $animation_style_phone ) {
							$animation_styles['phone'] = $animation_style . '_' . $animation_style_phone;
						}
					} elseif ( ! empty( $animation_styles['tablet'] ) ) {
						$animation_styles['phone'] = $animation_styles['tablet'];
					}
				}
			}

			$starting_opacities     = et_pb_responsive_options()->get_property_values( $this->props, 'modal_animation_starting_opacity' );
			$animation_durations    = et_pb_responsive_options()->get_property_values( $this->props, 'modal_animation_duration' );
			$animation_speed_curves = et_pb_responsive_options()->get_property_values( $this->props, 'modal_animation_speed_curve' );

			et_pb_responsive_options()->generate_responsive_css( $starting_opacities, '%%order_class%%_module .dipl_animated', 'opacity', $render_slug, '', 'range' );
			et_pb_responsive_options()->generate_responsive_css( $animation_durations, '%%order_class%%_module .dipl_animated', 'animation-duration', $render_slug, '', 'range' );
			et_pb_responsive_options()->generate_responsive_css( $animation_speed_curves, '%%order_class%%_module .dipl_animated', 'animation-timing-function', $render_slug, '', 'type' );

			if ( in_array( $animation_style, array( 'slide', 'zoom', 'flip', 'fold', 'roll' ), true ) ) {
				$animation_intensities = et_pb_responsive_options()->get_property_values( $this->props, 'modal_animation_intensity' );
				if ( empty( $animation_intensities['tablet'] ) ) {
					$animation_intensities['tablet'] = $animation_intensities['desktop'];
				}
				if ( empty( $animation_intensities['phone'] ) ) {
					$animation_intensities['phone'] = $animation_intensities['tablet'];
				}
				$translate_values = array();
				foreach ( $animation_intensities as $device => $animation_intensity ) {
					if ( '' !== $animation_intensity ) {
						$animation_values = explode( '_', $animation_styles[ $device ] );
						if ( ! empty( array_filter( $animation_values ) ) ) {
							$anim_style       = isset( $animation_values[0] ) ? $animation_values[0] : 'fade';
							$anim_direction   = isset( $animation_values[1] ) ? $animation_values[1] : 'center';
							switch ( $anim_style ) {
								case 'slide':
									$translate_value = floatVal( $animation_intensity ) * 2 . '%';
									switch ( $anim_direction ) {
										case 'center':
											$translate_value             = floatval( ( 100 - floatVal( $animation_intensity ) ) / 100 );
											$translate_values[ $device ] = "scale3d( {$translate_value}, {$translate_value}, {$translate_value} )";
											break;

										case 'up':
											$translate_values[ $device ] = "translate3d( 0, {$translate_value}, 0 )";
											break;

										case 'down':
											$translate_values[ $device ] = "translate3d( 0, -{$translate_value}, 0 )";
											break;

										case 'left':
											$translate_values[ $device ] = "translate3d( {$translate_value}, 0, 0 )";
											break;

										case 'right':
											$translate_values[ $device ] = "translate3d( -{$translate_value}, 0, 0 )";
											break;

										default:
											break;
									}
									break;

								case 'flip':
									$translate_value = ceil( ( floatval( $animation_intensity ) * 90 ) / 100 ) . 'deg';
									switch ( $anim_direction ) {
										case 'center':
											$translate_values[ $device ] = "perspective(2000px)rotateX({$translate_value})";
											break;

										case 'up':
											$translate_values[ $device ] = "perspective(2000px)rotateX(-{$translate_value})";
											break;

										case 'down':
											$translate_values[ $device ] = "perspective(2000px)rotateX({$translate_value})";
											break;

										case 'left':
											$translate_values[ $device ] = "perspective(2000px)rotateY({$translate_value})";
											break;

										case 'right':
											$translate_values[ $device ] = "perspective(2000px)rotateY(-{$translate_value})";
											break;

										default:
											break;
									}
									break;

								case 'fold':
									$translate_value = ceil( ( floatval( $animation_intensity ) * 90 ) / 100 ) . 'deg';
									switch ( $anim_direction ) {
										case 'center':
											$translate_values[ $device ] = "perspective(2000px)rotateY({$translate_value})";
											break;

										case 'up':
											$translate_values[ $device ] = "perspective(2000px)rotateX({$translate_value})";
											break;

										case 'down':
											$translate_values[ $device ] = "perspective(2000px)rotateX(-{$translate_value})";
											break;

										case 'left':
											$translate_values[ $device ] = "perspective(2000px)rotateY(-{$translate_value})";
											break;

										case 'right':
											$translate_values[ $device ] = "perspective(2000px)rotateY({$translate_value})";
											break;

										default:
											break;
									}
									break;

								case 'roll':
									$translate_value = ceil( ( floatval( $animation_intensity ) * 360 ) / 100 ) . 'deg';
									switch ( $anim_direction ) {
										case 'center':
											$translate_values[ $device ] = "perspective(2000px)rotateZ({$translate_value})";
											break;

										case 'up':
											$translate_values[ $device ] = "perspective(2000px)rotateZ(-{$translate_value})";
											break;

										case 'down':
											$translate_values[ $device ] = "perspective(2000px)rotateZ({$translate_value})";
											break;

										case 'left':
											$translate_values[ $device ] = "perspective(2000px)rotateZ(-{$translate_value})";
											break;

										case 'right':
											$translate_values[ $device ] = "perspective(2000px)rotateZ({$translate_value})";
											break;

										default:
											break;
									}
									break;

								case 'zoom':
									$translate_value             = floatval( ( 100 - floatVal( $animation_intensity ) ) / 100 );
									$translate_values[ $device ] = "scale3d( {$translate_value}, {$translate_value}, {$translate_value} )";
									break;

								default:
									break;
							}
						}
					}
				}

				et_pb_responsive_options()->generate_responsive_css( $translate_values, '%%order_class%%_module .dipl_animated', 'transform', $render_slug, '', 'type' );
			}

			$animation_names = preg_filter( '/^/', 'dipl_animate_', array_filter( $animation_styles ) );
			et_pb_responsive_options()->generate_responsive_css( $animation_names, '%%order_class%%_module.dipl_active_modal .dipl_animated', 'animation-name', $render_slug, '', 'type' );
			et_pb_responsive_options()->generate_responsive_css( $animation_names, '%%order_class%%_module.dipl_animate_reverse .dipl_animated', 'animation-name', $render_slug, '', 'type' );
		}
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

		if ( ( $raw_value && 'trigger_icon' === $name ) || ( $raw_value && 'modal_close_icon' === $name ) ) {
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
	if ( in_array( 'dipl_modal', $modules, true ) ) {
		new DIPL_Modal();
	}
} else {
	new DIPL_Modal();
}
