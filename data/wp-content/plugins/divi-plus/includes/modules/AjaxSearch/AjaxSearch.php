<?php
/**
 * @author      Elicus <hello@elicus.com>
 * @link        https://www.elicus.com/
 * @copyright   2022 Elicus Technologies Private Limited
 * @version     1.9.6
 */
class DIPL_AjaxSearch extends ET_Builder_Module {

	public $slug       = 'dipl_ajax_search';
	public $vb_support = 'on';

	protected $module_credits = array(
		'module_uri' => 'https://diviextended.com/product/divi-plus/',
		'author'     => 'Elicus',
		'author_uri' => 'https://elicus.com/',
	);

	public function init() {
		$this->name = esc_html__( 'DP Ajax Search', 'divi-plus' );
		$this->main_css_element = '%%order_class%%';
		add_filter( 'et_builder_processed_range_value', array( $this, 'dipl_builder_processed_range_value' ), 10, 3 );
	}

	public function get_settings_modal_toggles() {
		return array(
			'general'  => array(
				'toggles' => array(
					'main_content' => array(
						'title' => esc_html__( 'Configuration', 'divi-plus' ),
					),
					'search_area' => array(
						'title' => esc_html__( 'Search Area', 'divi-plus' ),
					),
					'display' => array(
						'title' => esc_html__( 'Display', 'divi-plus' ),
					),
					'scrollbar' => array(
						'title' => esc_html__( 'Scrollbar', 'divi-plus' ),
					),
					'link' => array(
						'title' => esc_html__( 'Link', 'divi-plus' ),
					),
				),
			),
			'advanced' => array(
				'toggles' => array(
					'search_field_settings' => array(
						'title' => esc_html__( 'Search Field', 'divi-plus' ),
					),
					'search_icon_settings' => array(
						'title' => esc_html__( 'Search Icon', 'divi-plus' ),
					),
					'loader_settings' => array(
						'title' => esc_html__( 'Loader', 'divi-plus' ),
					),
					'search_result_item_text_settings' => array(
						'title' => esc_html__( 'Search Result Item Text', 'divi-plus' ),
						'sub_toggles'   => array(
                            'title' => array(
                                'name' => esc_html__( 'Title', 'divi-plus' )
                            ),
                            'excerpt' => array(
                                'name' => esc_html__( 'Excerpt', 'divi-plus' )
                            ),
                            'price' => array(
                                'name' => esc_html__( 'Price', 'divi-plus' )
                            ),
                            'no_result' => array(
                                'name' => esc_html__( 'No Result', 'divi-plus' )
                            ),
                        ),
                        'tabbed_subtoggles' => true,
					),
					'featured_image_settings' => array(
						'title' => esc_html__( 'Featured Image', 'divi-plus' ),
					),
				),
			),
		);
	}

	public function get_advanced_fields_config() {
		return array(
			'fonts' => array(
				'search_result_item_title' => array(
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
					'hide_text_align' => true,
					'css'            => array(
						'main' => '%%order_class%% .dipl_ajax_search_item .dipl_ajax_search_item_title',
						'important' => 'all',
					),
					'tab_slug' => 'advanced',
					'toggle_slug' => 'search_result_item_text_settings',
					'sub_toggle' => 'title',
				),
				'search_result_item_excerpt' => array(
					'label'          => esc_html__( 'Excerpt', 'divi-plus' ),
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
					'hide_text_align' => true,
					'css'            => array(
						'main' => '%%order_class%% .dipl_ajax_search_item .dipl_ajax_search_item_excerpt',
						'important' => 'all',
					),
					'tab_slug' => 'advanced',
					'toggle_slug' => 'search_result_item_text_settings',
					'sub_toggle' => 'excerpt',
				),
				'search_result_item_price' => array(
					'label'          => esc_html__( 'Price', 'divi-plus' ),
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
					'hide_text_align' => true,
					'css'            => array(
						'main' => '%%order_class%% .dipl_ajax_search_item .dipl_ajax_search_item_price',
						'important' => 'all',
					),
					'tab_slug' => 'advanced',
					'toggle_slug' => 'search_result_item_text_settings',
					'sub_toggle' => 'price',
				),
				'no_result' => array(
					'label'          => esc_html__( 'No Result', 'divi-plus' ),
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
						'default_on_front' => '1.7em',
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
					'css' => array(
						'main' => '%%order_class%% .dipl_ajax_search_no_results',
						'important' => 'all',
					),
					'tab_slug' => 'advanced',
					'toggle_slug' => 'search_result_item_text_settings',
					'sub_toggle' => 'no_result',
				),
			),
			'form_field' => array(
				'form_field' => array(
					'label' => esc_html__( 'Field', 'divi-plus' ),
					'css' => array(
						'main' 	=> '%%order_class%% .dipl_ajax_search_field',
						'hover' => '%%order_class%% .dipl_ajax_search_field:hover',
						'focus' => '%%order_class%% .dipl_ajax_search_field:focus',
						'focus_hover' => '%%order_class%% .dipl_ajax_search_field:focus:hover',
						'important'   => array( 'line-height', 'font-size', 'color' ),
					),
					'font_field' => array(
						'css' => array(
							'main' => implode(', ', array(
								'%%order_class%% .dipl_ajax_search_field',
								'%%order_class%% .dipl_ajax_search_field::placeholder',
								'%%order_class%% .dipl_ajax_search_field::-webkit-input-placeholder',
								'%%order_class%% .dipl_ajax_search_field::-ms-input-placeholder',
								'%%order_class%% .dipl_ajax_search_field::-moz-placeholder',
							) ),
							'hover' => implode(', ', array(
								'%%order_class%% .dipl_ajax_search_field:',
								'%%order_class%% .dipl_ajax_search_field::placeholder',
								'%%order_class%% .dipl_ajax_search_field::-webkit-input-placeholder',
								'%%order_class%% .dipl_ajax_search_field::-ms-input-placeholder',
								'%%order_class%% .dipl_ajax_search_field::-moz-placeholder',
							) ),
							'focus' => implode(', ', array(
								'%%order_class%% .dipl_ajax_search_field',
								'%%order_class%% .dipl_ajax_search_field::placeholder',
								'%%order_class%% .dipl_ajax_search_field::-webkit-input-placeholder',
								'%%order_class%% .dipl_ajax_search_field::-ms-input-placeholder',
								'%%order_class%% .dipl_ajax_search_field::-moz-placeholder',
							) ),
							'focus_hover' => implode(', ', array(
								'%%order_class%% .dipl_ajax_search_field',
								'%%order_class%% .dipl_ajax_search_field::placeholder',
								'%%order_class%% .dipl_ajax_search_field::-webkit-input-placeholder',
								'%%order_class%% .dipl_ajax_search_field::-ms-input-placeholder',
								'%%order_class%% .dipl_ajax_search_field::-moz-placeholder',
							) ),
							'placeholder' => true,
						),
						'line_height'    => array(
							'default' => '1em',
						),
						'font_size'      => array(
							'default' => '14px',
						),
						'letter_spacing' => array(
							'default' => '0px',
						),
					),
					'margin_padding' => array(
						'use_margin' => false,
						'css'        => array(
							'padding' => '%%order_class%% .dipl_ajax_search_field',
							'important' => 'all',
						),
					),
					'box_shadow' => false,
					'border_styles' => array(
						'form_field' => array(
							'fields_after' => array(
								'use_focus_border_color' => false,
							),
							'defaults' => array(
						        'border_radii' => 'on||||',
						        'border_styles' => array(
						            'width' => '2px',
						            'color' => '#333333',
									'style' => 'solid',
						        ),
						    ),
							'css' => array(
								'main' => array(
									'border_radii'  => '%%order_class%% .dipl_ajax_search_field',
									'border_styles' => '%%order_class%% .dipl_ajax_search_field',
								),
								'important' => 'all',
							),
							'label_prefix' => esc_html__( 'Field', 'divi-plus' ),
						),
					),
					'tab_slug' => 'advanced',
					'toggle_slug' => 'search_field_settings',
				),
			),
			'margin_padding' => array(
				'css' => array(
					'main'      => '%%order_class%%',
					'important' => 'all',
				),
			),
			'search_result_margin_padding' => array(
                'search_result_box' => array(
                    'margin_padding' => array(
                        'css' => array(
                        	'use_margin' => false,
                            'padding'    => '%%order_class%% .dipl_ajax_search_results',
                            'important'  => 'all',
                        ),
                    ),
                ),
            ),
			'max_width' => array(
				'default' => array(
					'css' => array(
						'main'             => '%%order_class%%',
						'module_alignment' => '%%order_class%%',
					),
				),
				'extra' => array(
					'featured_image' => array(
						'options' => array(
							'width' => array(
								'label'          => esc_html__( 'Featured Image Width', 'divi-plus' ),
								'range_settings' => array(
									'min'  => 1,
									'max'  => 100,
									'step' => 1,
								),
								'hover'          => false,
								'default_unit'   => 'px',
								'default'		 => '85px',
								'default_tablet' => '',
								'default_phone'  => '',
								'tab_slug'       => 'advanced',
								'toggle_slug'    => 'featured_image_settings',
							),
						),
						'use_max_width'        => false,
						'use_module_alignment' => false,
						'css'                  => array(
							'main' => "{$this->main_css_element} .dipl_ajax_search_item_image",
							'important' => 'all',
						),
					),
				),
			),
			'height' => array(
				'default' => array(
					'css' => array(
						'main' => '%%order_class%%',
					),
				),
				'extra' => array(
					'featured_image' => array(
						'options' => array(
							'height' => array(
								'label'          => esc_html__( 'Featured Image Height', 'divi-plus' ),
								'range_settings' => array(
									'min'  => 1,
									'max'  => 100,
									'step' => 1,
								),
								'hover'          => false,
								'default_unit'   => 'px',
								'default'	     => 'auto',
								'default_tablet' => '',
								'default_phone'  => '',
								'tab_slug'       => 'advanced',
								'toggle_slug'    => 'featured_image_settings',
							),
						),
						'use_max_height' => false,
						'use_min_height' => false,
						'css'            => array(
							'main' => "{$this->main_css_element} .dipl_ajax_search_item_image",
							'important' => 'all',
						),
					),
				),
			),
			'borders' => array(
				'search_result_box' => array(
					'label_prefix' => 'Result Box',
					'css'          => array(
						'main' => array(
							'border_radii'  => '%%order_class%% .dipl_ajax_search_results',
							'border_styles' => '%%order_class%% .dipl_ajax_search_results',
							'important' => 'all',
						),
					),
					'tab_slug'     => 'advanced',
					'toggle_slug'  => 'border',
				),
				'featured_image' => array(
					'label_prefix' => 'Featured Image',
					'css'          => array(
						'main' => array(
							'border_radii'  => '%%order_class%% .dipl_ajax_search_item_image img',
							'border_styles' => '%%order_class%% .dipl_ajax_search_item_image img',
							'important' => 'all',
						),
					),
					'tab_slug'     => 'advanced',
					'toggle_slug'  => 'featured_image_settings',
				),
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
				'search_result_box' => array(
					'label'       => esc_html__( 'Result Box Box Shadow', 'divi-plus' ),
					'css'         => array(
						'main' => '%%order_class%% .dipl_ajax_search_results',
						'important' => 'all',
					),
					'tab_slug'    => 'advanced',
					'toggle_slug' => 'box_shadow',
				),
				'default' => array(
					'css' => array(
						'main' => '%%order_class%%',
					),
				),
			),
			'filters' => false,
			'text' => false,
			'link_options'  => false,
		);
	}

	public function get_fields() {

		$raw_post_types = get_post_types( array(
			'public' => true,
			'show_ui' => true,
			'exclude_from_search' => false,
		), 'objects' );

		$blocklist = array( 'et_pb_layout', 'layout', 'attachment' );

		$post_types = array();

		foreach ( $raw_post_types as $post_type ) {
			$is_blocklisted = in_array( $post_type->name, $blocklist );
			
			if ( ! $is_blocklisted && post_type_exists( $post_type->name ) ) {
				if ( isset( $post_type->label ) ) {
					$label = esc_html( $post_type->label );
				} else if ( isset( $post_type->labels->name ) ) {
					$label = esc_html( $post_type->labels->name );
				} else if ( isset( $post_type->labels->singular_name ) ) {
					$label = esc_html( $post_type->labels->singular_name );
				} else {
					$label = esc_html( $post_type->name );
				}
				$slug  	= sanitize_text_field( $post_type->name );
				$post_types[$slug] = esc_html( $label );
			}
		}

		if ( ! empty( $post_types ) ) {
			$post_types['all'] = esc_html__( 'All of the above', 'divi-plus' );
		}

		$search_in = array( 
			'post_title' => esc_html__( 'Title', 'divi-plus' ),
			'post_content' => esc_html__( 'Content', 'divi-plus' ),
			'post_excerpt' => esc_html__( 'Excerpt', 'divi-plus' ),
		);

		$display_fields = array( 
			'title' => esc_html__( 'Title', 'divi-plus' ),
			'excerpt' => esc_html__( 'Excerpt', 'divi-plus' ),
			'featured_image' => esc_html__( 'Featured Image', 'divi-plus' ),
			'product_price' => esc_html__( 'Product Price', 'divi-plus' ),
		);
		
		return array_merge(
			array(
				'search_placeholder' => array(
					'label'           		=> esc_html__( 'Search Field Placeholder', 'divi-plus' ),
					'type'           		=> 'text',
					'option_category' 		=> 'basic_option',
					'default_on_front' 		=> esc_html__( 'Search', 'divi-plus' ),
					'default'		   		=> esc_html__( 'Search', 'divi-plus' ),
					'tab_slug'        		=> 'general',
					'toggle_slug'     		=> 'main_content',
					'description'     		=> esc_html__( 'Here you can input the placeholder to be used for the search field.', 'divi-plus' ),
				),
				'number_of_results' => array(
					'label'           		=> esc_html__( 'Search Result Number', 'divi-plus' ),
					'type'           		=> 'text',
					'option_category' 		=> 'basic_option',
					'default_on_front' 		=> '10',
					'default'		   		=> '10',
					'tab_slug'        		=> 'general',
					'toggle_slug'     		=> 'main_content',
					'description'     		=> esc_html__( 'Here you can input the number of items to be displayed in the search result. Input -1 for all.', 'divi-plus' ),
				),
				'orderby' => array(
					'label'            => esc_html__( 'Order by', 'divi-plus' ),
					'type'             => 'select',
					'option_category'  => 'configuration',
					'options'          => array(
						'post_date' 	=> esc_html__( 'Date', 'divi-plus' ),
						'post_modified'	=> esc_html__( 'Modified Date', 'divi-plus' ),
						'post_title'   	=> esc_html__( 'Title', 'divi-plus' ),
						'post_name'     => esc_html__( 'Slug', 'divi-plus' ),
						'ID'       		=> esc_html__( 'ID', 'divi-plus' ),
						'rand'     		=> esc_html__( 'Random', 'divi-plus' ),
					),
					'default'          => 'post_date',
					'tab_slug'         => 'general',
					'toggle_slug'      => 'main_content',
					'description'      => esc_html__( 'Here you can choose the order type of your results.', 'divi-plus' ),
				),
				'order' => array(
					'label'            => esc_html__( 'Order', 'divi-plus' ),
					'type'             => 'select',
					'option_category'  => 'configuration',
					'options'          => array(
						'DESC' => esc_html__( 'DESC', 'divi-plus' ),
						'ASC'  => esc_html__( 'ASC', 'divi-plus' ),
					),
					'default'          => 'DESC',
					'show_if_not'      => array(
						'orderby' => 'rand',
					),
					'tab_slug'         => 'general',
					'toggle_slug'      => 'main_content',
					'description'      => esc_html__( 'Here you can choose the order of your results.', 'divi-plus' ),
				),
				'no_result_text' => array(
					'label'           		=> esc_html__( 'No Result Text', 'divi-plus' ),
					'type'           		=> 'text',
					'option_category' 		=> 'basic_option',
					'default_on_front' 		=> esc_html__( 'No results found', 'divi-plus' ),
					'default'		   		=> esc_html__( 'No results found', 'divi-plus' ),
					'tab_slug'        		=> 'general',
					'toggle_slug'     		=> 'main_content',
					'description'     		=> esc_html__( 'Here you can input the custom text to be displayed when no results found.', 'divi-plus' ),
				),
				'search_in' => array(
					'label'            		=> esc_html__( 'Search in', 'divi-plus' ),
					'type'             		=> 'multiple_checkboxes',
					'option_category'  		=> 'basic_option',
					'options'				=> $search_in,
					'default'				=> 'on|on|on',
					'default_on_front'		=> 'on|on|on',
					'tab_slug'         		=> 'general',
					'toggle_slug'      		=> 'search_area',
					'description'      		=> esc_html__( 'Here you can choose where you would like to search in.', 'divi-plus' ),
				),
				'include_post_types' => array(
					'label'            		=> esc_html__( 'Post Types', 'divi-plus' ),
					'type'             		=> 'select',
					'option_category'  		=> 'basic_option',
					'options'				=> $post_types,
					'default'				=> 'post',
					'default_on_front'		=> 'post',
					'tab_slug'         		=> 'general',
					'toggle_slug'      		=> 'search_area',
					'description'      		=> esc_html__( 'Here you can choose which post types you would like to search in.', 'divi-plus' ),
				),
				'show_search_icon' => array(
					'label'            		=> esc_html__( 'Show Search Icon', 'divi-plus' ),
					'type'             		=> 'yes_no_button',
					'option_category'  		=> 'configuration',
					'options'          		=> array(
						'on'  => esc_html__( 'Yes', 'divi-plus' ),
						'off' => esc_html__( 'No', 'divi-plus' ),
					),
					'default'          		=> 'on',
					'tab_slug'         		=> 'general',
					'toggle_slug'      		=> 'display',
					'description'      		=> esc_html__( 'This will turn the search icon on and off.', 'divi-plus' ),
				),
				'display_fields' => array(
					'label'            		=> esc_html__( 'Display Fields', 'divi-plus' ),
					'type'             		=> 'multiple_checkboxes',
					'option_category'  		=> 'basic_option',
					'options'				=> $display_fields,
					'default'				=> 'on|on|on|off',
					'default_on_front'		=> 'on|on|on|off',
					'tab_slug'         		=> 'general',
					'toggle_slug'      		=> 'display',
					'description'      		=> esc_html__( 'Here you can choose which fields you would like to display in search results.', 'divi-plus' ),
				),
				'number_of_columns' => array(
	                'label'             => esc_html__( 'Number Of Columns', 'divi-plus' ),
	                'type'              => 'select',
	                'option_category'   => 'configuration',
	                'options'           => array(
	                    '1'  => esc_html( '1' ),
	                    '2'  => esc_html( '2' ),
	                    '3'  => esc_html( '3' ),
	                    '4'  => esc_html( '4' ),
	                ),
	                'default'           => '1',
	                'tab_slug'          => 'general',
	                'toggle_slug'       => 'display',
	                'description'       => esc_html__( 'Here you can select the number of columns to display result items.', 'divi-plus' ),
	            ),
	            'use_masonry' => array(
					'label'            		=> esc_html__( 'Use Masonry', 'divi-plus' ),
					'type'             		=> 'yes_no_button',
					'option_category'  		=> 'configuration',
					'options'          		=> array(
						'on'  => esc_html__( 'Yes', 'divi-plus' ),
						'off' => esc_html__( 'No', 'divi-plus' ),
					),
					'default'          		=> 'off',
					'show_if_not'      => array(
						'number_of_columns' => '1',
					),
					'tab_slug'         		=> 'general',
					'toggle_slug'      		=> 'display',
					'description'      		=> esc_html__( 'Here you can select whether or not to display the results in masonry design appearance.', 'divi-plus' ),
				),
				'scrollbar' => array(
					'label'             => esc_html__( 'Scrollbar', 'divi-plus' ),
	                'type'              => 'select',
	                'option_category'   => 'configuration',
	                'options'           => array(
	                    'default'  	=> esc_html__( 'Show', 'divi-plus' ),
	                    'hide' 		=> esc_html__( 'Hide', 'divi-plus' ),
	                ),
	                'default'           => 'default',
	                'tab_slug'          => 'general',
	                'toggle_slug'       => 'scrollbar',
	                'description'       => esc_html__( 'Here you can select whether to show or hide scrollbar. This is totally depends on the browser, we can not guarantee the result of it.', 'divi-plus' ),
				),
				'url_new_window' => array(
	                'label'             => esc_html__( 'Result Item Link Target', 'divi-plus' ),
	                'type'              => 'select',
	                'option_category'   => 'configuration',
	                'options'           => array(
	                    'off' => esc_html__( 'In The Same Window', 'divi-plus' ),
	                    'on'  => esc_html__( 'In The New Tab', 'divi-plus' ),
	                ),
	                'default'           => 'off',
	                'default_on_front'  => 'off',
	                'tab_slug'          => 'general',
	                'toggle_slug'       => 'link',
	                'description'       => esc_html__( 'Here you can choose whether or not the search result item opens the link in a new window.', 'divi-plus' ),
	            ),
				'search_result_box_custom_padding' => array(
	                'label'                 => esc_html__( 'Search Result Box Padding', 'divi-plus' ),
	                'type'                  => 'custom_padding',
	                'option_category'       => 'layout',
	                'mobile_options'        => true,
	                'hover'                 => false,
	                'tab_slug'              => 'advanced',
	                'toggle_slug'           => 'margin_padding',
	                'description'           => esc_html__( 'Padding adds extra space to the inside of the element, increasing the distance between the edge of the element and its inner contents.', 'divi-plus' ),
	            ),
	            'search_result_box_bg_color' => array(
	                'label'                 => esc_html__( 'Search Result Box Background', 'divi-plus' ),
	                'type'                  => 'background-field',
	                'base_name'             => 'search_result_box_bg',
	                'context'               => 'search_result_box_bg_color',
	                'option_category'       => 'button',
	                'custom_color'          => true,
	                'background_fields'     => $this->generate_background_options( 'search_result_box_bg', 'button', 'advanced', 'background', 'search_result_box_bg_color' ),
	                'hover'                 => false,
	                'mobile_options'        => true,
	                'tab_slug'              => 'general',
	                'toggle_slug'           => 'background',
	                'description'           => esc_html__( 'Customize the background style of the search result box by adjusting the background color, gradient, and image.', 'divi-plus' ),
	            ),
	            'search_result_item_bg_color' => array(
	                'label'                 => esc_html__( 'Search Result Item Background', 'divi-plus' ),
	                'type'                  => 'background-field',
	                'base_name'             => 'search_result_item_bg',
	                'context'               => 'search_result_item_bg_color',
	                'option_category'       => 'button',
	                'custom_color'          => true,
	                'background_fields'     => $this->generate_background_options( 'search_result_item_bg', 'button', 'advanced', 'background', 'search_result_item_bg_color' ),
	                'hover'                 => 'tabs',
	                'mobile_options'        => true,
	                'tab_slug'              => 'general',
	                'toggle_slug'           => 'background',
	                'description'           => esc_html__( 'Customize the background style of the search result item by adjusting the background color, gradient, and image.', 'divi-plus' ),
	            ),
	            'search_icon_font_size' => array(
					'label'                 => esc_html__( 'Search Icon Font Size', 'divi-plus' ),
					'type'                  => 'range',
					'option_category'       => 'font_option',
					'range_settings'        => array(
						'min'   => '1',
						'max'   => '120',
						'step'  => '1',
					),
					'fixed_unit'			=> 'px',
					'validate_unit'			=> true,
					'show_if'               => array(
						'show_search_icon' => 'on',
					),
					'mobile_options'        => true,
					'default'               => '16px',
					'tab_slug'              => 'advanced',
					'toggle_slug'           => 'search_icon_settings',
					'description'           => esc_html__('Increase or decrease the icon size.', 'divi-plus'),
				),
				'search_icon_color' => array(
					'label'            		=> esc_html__( 'Search Icon Color', 'divi-plus' ),
					'type'             		=> 'color-alpha',
					'default'          		=> '#000',
					'default_on_front' 		=> '#000',
					'show_if'          		=> array(
						'show_search_icon' => 'on',
					),
					'mobile_options'        => true,
					'hover'					=> 'tabs',
					'tab_slug'         		=> 'advanced',
					'toggle_slug'      		=> 'search_icon_settings',
					'description'      		=> esc_html__( 'Here you can define a custom color to be used for the search icon.', 'divi-plus' ),
				),
				'loader_size' => array(
					'label'                 => esc_html__( 'Loader Size', 'divi-plus' ),
					'type'                  => 'range',
					'option_category'       => 'font_option',
					'range_settings'        => array(
						'min'   => '1',
						'max'   => '120',
						'step'  => '1',
					),
					'fixed_unit'			=> 'px',
					'validate_unit'			=> true,
					'mobile_options'        => true,
					'default'               => '16px',
					'tab_slug'              => 'advanced',
					'toggle_slug'           => 'loader_settings',
					'description'           => esc_html__('Increase or decrease the loader size.', 'divi-plus'),
				),
				'loader_color' => array(
					'label'            		=> esc_html__( 'Loader Color', 'divi-plus' ),
					'type'             		=> 'color-alpha',
					'default'          		=> '#000',
					'default_on_front' 		=> '#000',
					'mobile_options'        => true,
					'hover'					=> 'tabs',
					'tab_slug'         		=> 'advanced',
					'toggle_slug'      		=> 'loader_settings',
					'description'      		=> esc_html__( 'Here you can define a custom color to be used for the loader.', 'divi-plus' ),
				),
	        ),
			$this->generate_background_options( 'search_result_box_bg', 'skip', 'general', 'background', 'search_result_box_bg_color' ),
			$this->generate_background_options( 'search_result_item_bg', 'skip', 'general', 'background', 'search_result_item_bg_color' )
		);

	}


	public function render( $attrs, $content, $render_slug ) {

		$search_placeholder 	= $this->props['search_placeholder'];
		$orderby				= $this->props['orderby'];
		$order 					= $this->props['order'];
		$search_in 				= $this->props['search_in'];
		$include_post_types 	= $this->props['include_post_types'];
		$display_fields			= $this->props['display_fields'];
		$use_masonry			= $this->props['use_masonry'];
		$number_of_columns		= $this->props['number_of_columns'];
		$scrollbar 				= $this->props['scrollbar'];
		$show_search_icon		= $this->props['show_search_icon'];
		$url_new_window			= $this->props['url_new_window'];
		$number_of_results		= $this->props['number_of_results'];
		$no_result_text			= $this->props['no_result_text'];

		wp_enqueue_script( 'dipl-ajax-search-custom', PLUGIN_PATH . 'includes/modules/AjaxSearch/dipl-ajax-search-custom.min.js', array('jquery'), '1.0.1', true );
		$file = et_is_builder_plugin_active() ? 'style-dbp' : 'style';
        wp_enqueue_style( 'dipl-ajax-search-style', PLUGIN_PATH . 'includes/modules/AjaxSearch/' . $file . '.min.css', array(), '1.0.0' );

		if ( 'all' === $include_post_types ) {
			$raw_post_types = get_post_types( array(
				'public' => true,
				'show_ui' => true,
				'exclude_from_search' => false,
			), 'objects' );

			$blocklist = array( 'et_pb_layout', 'layout', 'attachment' );
			$whitelisted_post_types = array();

			foreach ( $raw_post_types as $post_type ) {
				$is_blocklisted = in_array( $post_type->name, $blocklist );
				if ( ! $is_blocklisted && post_type_exists( $post_type->name ) ) {
					array_push( $whitelisted_post_types, $post_type->name );
				}
			}

			$include_post_types = implode( ',', $whitelisted_post_types );
		}

		$whitelisted_search_in 	= array( 'post_title', 'post_content', 'post_excerpt' );
		$search_in = DiviPlusHelper::process_multiple_checkboxes_value( $search_in, $whitelisted_search_in );

		$whitelisted_display_fields = array( 'title', 'excerpt', 'featured_image', 'product_price' );
		$display_fields = DiviPlusHelper::process_multiple_checkboxes_value( $display_fields, $whitelisted_display_fields );

		$search_placeholder = '' === $search_placeholder ? esc_html__( 'Search', 'divi-plus' ) : esc_attr( $search_placeholder );

		if ( '1' !== $number_of_columns && 'on' === $use_masonry ) {
			wp_enqueue_script( 'elicus-isotope-script', PLUGIN_PATH . 'includes/assets/js/isotope.pkgd.min.js', array( 'jquery' ), '3.0.6', true );
		}

		$search_field_wrap  = sprintf(
			'<div class="dipl_ajax_search_wrap">
				<div class="dipl_ajax_search_field_wrap %6$s">
					<input type="search" placeholder="%1$s" class="dipl_ajax_search_field" data-search-in="%2$s" data-search-post-type="%3$s" data-display-fields="%4$s" data-url-new-window="%7$s" data-number-of-results="%8$s" data-no-result-text="%9$s" data-orderby="%10$s" data-order="%11$s" data-use-masonry="%13$s" />
					<input type="hidden" class="dipl_ajax_search_nonce" value="%14$s" />
				</div>
				<div class="dipl_ajax_search_results_wrap dipl_ajax_search_result_%12$s_cols %5$s"></div>
			</div>',
			esc_attr( $search_placeholder ),
			esc_attr( $search_in ),
			esc_attr( $include_post_types ),
			esc_attr( $display_fields ),
			'featured_image' === $display_fields ? esc_attr( 'dipl_ajax_search_only_featured_image' ) : '',
			'on' === $show_search_icon ? esc_attr( 'dipl_ajax_search_icon' ) : '',
			esc_attr( $url_new_window ),
			intval( $number_of_results ),
			esc_attr( $no_result_text ),
			esc_attr( $orderby ),
			esc_attr( $order ),
			intval( $number_of_columns ),
			'1' !== $number_of_columns && 'on' === $use_masonry ? 'on' : 'off',
			wp_create_nonce( 'elicus-divi-plus-ajax-search-nonce' )
		);

		/* Search Icon CSS */
		if ( 'on' === $show_search_icon ) {
			$search_icon_font_size 	= et_pb_responsive_options()->get_property_values( $this->props, 'search_icon_font_size' );
			$search_icon_color     	= et_pb_responsive_options()->get_property_values( $this->props, 'search_icon_color' );
			et_pb_responsive_options()->generate_responsive_css( $search_icon_font_size, '%%order_class%% .dipl_ajax_search_icon:after', 'font-size', $render_slug, '!important;', 'range' );
			et_pb_responsive_options()->generate_responsive_css( $search_icon_color, '%%order_class%% .dipl_ajax_search_icon:after', 'color', $render_slug, '!important;', 'color' );
			$search_icon_color_hover    = $this->get_hover_value( 'search_icon_color' );
            if ( $search_icon_color_hover ) {
                self::set_style( $render_slug, array(
                    'selector'    => '%%order_class%% .dipl_ajax_search_icon:hover:after',
                    'declaration' => sprintf(
                        'color: %1$s !important;',
                        esc_attr( $search_icon_color_hover )
                    ),
                ) );
            }
		}

		/* Loader CSS */
		$loader_size_property  = array( 'width', 'height' );
		$loader_color_property = array( 'border-top-color', 'border-bottom-color' );
		$loader_size 	= et_pb_responsive_options()->get_property_values( $this->props, 'loader_size' );
		$loader_color   = et_pb_responsive_options()->get_property_values( $this->props, 'loader_color' );
		et_pb_responsive_options()->generate_responsive_css( $loader_size, '%%order_class%% .dipl_ajax_search_loader:after', $loader_size_property, $render_slug, '!important;', 'range' );
		et_pb_responsive_options()->generate_responsive_css( $loader_color, '%%order_class%% .dipl_ajax_search_loader:after', $loader_color_property, $render_slug, '!important;', 'color' );

		$loader_color_hover    = $this->get_hover_value( 'loader_color' );
        if ( $loader_color_hover ) {
            self::set_style( $render_slug, array(
                'selector'    => '%%order_class%% .dipl_ajax_search_loader:hover:after',
                'declaration' => sprintf(
                    'border-top-color: %1$s !important; border-bottom-color: %1$s !important;',
                    esc_attr( $loader_color_hover )
                ),
            ) );
        }

        $featured_image_width = et_pb_responsive_options()->get_property_values( $this->props, 'featured_image_width' );
        $content_width = array_map( array( $this, 'dipl_filter_content_width' ), $featured_image_width );
        et_pb_responsive_options()->generate_responsive_css( $content_width, '%%order_class%% .dipl_ajax_search_item_image + .dipl_ajax_search_item_content', 'width', $render_slug, '!important;', 'range' );

        /* Scrollbar CSS */
        if ( 'hide' === $scrollbar ) {
        	self::set_style( $render_slug, array(
                'selector'    => '%%order_class%% .dipl_ajax_search_results::-webkit-scrollbar',
                'declaration' => 'display: none;',
            ) );
            self::set_style( $render_slug, array(
                'selector'    => '%%order_class%% .dipl_ajax_search_results',
                'declaration' => 'scrollbar-width: none;',
            ) );
        }

        $args = array(
			'render_slug'	=> $render_slug,
			'props'			=> $this->props,
			'fields'		=> $this->fields_unprocessed,
			'module'		=> $this,
			'backgrounds' 	=> array(
				'search_result_box_bg' => array(
					'normal' => "{$this->main_css_element} .dipl_ajax_search_results",
					'hover' => "{$this->main_css_element} .dipl_ajax_search_results:hover",
	 			),
	 			'search_result_item_bg' => array(
	 				'normal' => "{$this->main_css_element} .dipl_ajax_search_item",
	 				'hover' => "{$this->main_css_element} .dipl_ajax_search_item:hover",
	 			),
			),
		);

		DiviPlusHelper::process_background( $args );
		$fields = array( 'search_result_margin_padding' );
		DiviPlusHelper::process_advanced_margin_padding_css( $this, $render_slug, $this->margin_padding, $fields );

		return $search_field_wrap;
	}

	public function dipl_filter_content_width( $width ) {
		if ( '' !== $width ) {
			return 'calc(100% - ' . $width . ')';
		}

		return $width;
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
    if ( in_array( 'dipl_ajax_search', $modules ) ) {
        new DIPL_AjaxSearch();
    }
} else {
    new DIPL_AjaxSearch();
}