<?php
/**
 * @author      Elicus <hello@elicus.com>
 * @link        https://www.elicus.com/
 * @copyright   2023 Elicus Technologies Private Limited
 * @version     1.9.11
 */
class DIPL_FilterableGallery extends ET_Builder_Module {

	public $slug       = 'dipl_filterable_gallery';
	public $vb_support = 'on';

	protected $module_credits = array(
		'module_uri' => 'https://diviextended.com/product/divi-plus/',
		'author'     => 'Elicus',
		'author_uri' => 'https://elicus.com/',
	);

	public function init() {
		$this->name = esc_html__( 'DP Filterable Gallery', 'divi-plus' );
		$this->main_css_element = '%%order_class%%';
		add_filter( 'et_builder_processed_range_value', array( $this, 'dipl_builder_processed_range_value' ), 10, 3 );
		add_filter( 'et_required_module_assets', array( $this, 'dipl_required_module_assets' ), 10 );
		add_filter( 'et_dynamic_assets_modules_atf', array( $this, 'dipl_required_module_assets' ), 20 );
		add_filter( 'et_late_global_assets_list', array( $this, 'dipl_late_assets' ), 10, 3 );
	}

	public function dipl_required_module_assets( $modules ) {
		array_push( $modules, 'et_pb_image' );
		return $modules;
	}

	public function dipl_late_assets( $assets_list, $assets_args, $dynamic_assets ) {
		if ( function_exists( 'et_get_dynamic_assets_path' ) && function_exists( 'et_is_cpt' ) ) {
			$cpt_suffix = et_is_cpt() ? '_cpt' : '';
			$assets_list['et_icons_all'] = array(
				'css' => $assets_args['assets_prefix'] . "/css/icons_all.css",
			);
			$assets_list['et_overlay'] = array(
				'css' => $assets_args['assets_prefix'] . "/css/overlay{$cpt_suffix}.css",
			);
			$assets_list['et_icons_fa'] = array(
				'css' => $assets_args['assets_prefix'] . "/css/icons_fa_all.css",
			);
		}
		return $assets_list;
	}

	public function get_settings_modal_toggles() {
		return array(
			'general'  => array(
				'toggles' => array(
					'main_content' => array(
						'title' => esc_html__( 'Configuration', 'divi-plus' ),
					),
					'elements' => array(
						'title' => esc_html__( 'Elements', 'divi-plus' ),
					),
					'lightbox' => array(
						'title' => esc_html__( 'Lightbox', 'divi-plus' ),
					),
					'pagination' => array(
						'title' => esc_html__( 'Pagination', 'divi-plus' ),
					),
				),
			),
			'advanced'   => array(
				'toggles' => array(
					'image_text' => array(
						'title' => esc_html__( 'Text', 'divi-plus' ),
                        'sub_toggles'   => array(
                            'title_text' => array(
                                'name' => 'Title',
                            ),
                            'caption_text' => array(
                                'name' => 'Caption',
                            ),
                        ),
                        'tabbed_subtoggles' => true,
					),
					'category' => array(
                        'title' => esc_html__( 'Category', 'divi-plus' ),
                        'sub_toggles'   => array(
                            'normal'  => array(
                                'name' => 'Normal',
                            ),
                            'active' => array(
                                'name' => 'Active',
                            ),
                        ),
                        'tabbed_subtoggles' => true,
                    ),
					'lightbox' => array(
						'title' => esc_html__( 'Lightbox', 'divi-plus' ),
					),
					'overlay' => array(
						'title' => esc_html__( 'Overlay', 'divi-plus' ),
					),
					'pagination' => array(
						'title' => esc_html__( 'Pagination', 'divi-plus' ),
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
						'default'        => '18px',
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
					'header_level'   => array(
						'default' => 'h4',
					),
					'text_align'     => array(
						'default' => 'left',
					),
					'css'            => array(
						'main'       => "{$this->main_css_element} .dipl_filterable_gallery_item_title, {$this->main_css_element}_lightbox .dipl_filterable_gallery_item_title",
					),
					'tab_slug'	=> 'advanced',
                    'toggle_slug' => 'image_text',
                    'sub_toggle' => 'title_text',
				),
				'caption'    => array(
					'label'          => esc_html__( 'Caption', 'divi-plus' ),
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
					'text_align'     => array(
						'default' => 'left',
					),
					'css'            => array(
						'main'  => "{$this->main_css_element} .dipl_filterable_gallery_item_caption, {$this->main_css_element}_lightbox .dipl_filterable_gallery_item_caption",
					),
					'tab_slug'	=> 'advanced',
                    'toggle_slug' => 'image_text',
                    'sub_toggle' => 'caption_text',
				),
				'category' => array(
                    'label'     => esc_html__( 'Category', 'divi-plus' ),
                    'font_size' => array(
                        'default'           => '16px',
                        'range_settings'    => array(
                            'min'   => '1',
                            'max'   => '100',
                            'step'  => '1',
                        ),
                        'validate_unit'     => true,
                    ),
                    'line_height' => array(
                        'default'           => '1.5em',
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
                        'validate_unit' => true,
                    ),
                    'text_color' => array(
                    	'default' => '#fff',
                    ),
                    'hide_text_align'   => true,
                    'css'       => array(
                        'main'          => "%%order_class%% .dipl_filterable_gallery_filter_categories li:not(.dipl_filterable_gallery_active_category)",
                    ),
                    'toggle_slug'   => 'category',
                    'sub_toggle'    => 'normal',
                    'tab_slug'      => 'advanced',
                ),
                'active_category' => array(
                    'label'             => esc_html__( 'Active Category', 'divi-plus' ),
                    'font_size'         => array(
                        'default'           => '16px',
                        'range_settings'    => array(
                            'min'   => '1',
                            'max'   => '100',
                            'step'  => '1',
                        ),
                        'validate_unit'     => true,
                    ),
                    'line_height'       => array(
                        'default'           => '1.5em',
                        'range_settings'    => array(
                            'min'   => '0.1',
                            'max'   => '10',
                            'step'  => '0.1',
                        ),
                    ),
                    'letter_spacing'    => array(
                        'default'           => '0px',
                        'range_settings'    => array(
                            'min'   => '0',
                            'max'   => '10',
                            'step'  => '1',
                        ),
                        'validate_unit' => true,
                    ),
                    'text_color' => array(
                    	'default' => '#000',
                    ),
                    'hide_text_align'   => true,
                    'css'               => array(
                        'main'      => "%%order_class%% .dipl_filterable_gallery_filter_categories li.dipl_filterable_gallery_active_category",
                    ),
                    'toggle_slug'       => 'category',
                    'sub_toggle'        => 'active',
                    'tab_slug'          => 'advanced',
                ),
                'pagination_link' => array(
					'label'          => esc_html__( 'Pagination Link', 'divi-plus' ),
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
					'hide_text_color' => true,
					'hide_text_shadow' => true,
					'hide_text_align' => true,
					'css'            => array(
						'main'  => "{$this->main_css_element} .dipl_filterable_gallery_pagination li a",
						'text_align' => "{$this->main_css_element} .dipl_filterable_gallery_pagination_wrapper"
					),
					'tab_slug'	=> 'advanced',
                    'toggle_slug' => 'pagination',
				),
			),
			'margin_padding' => array(
				'css' => array(
					'main'      => '%%order_class%%',
					'important' => 'all',
				),
			),
			'max_width' => array(
				'css' => array(
					'main'             => '%%order_class%%',
					'module_alignment' => '%%order_class%%',
				),
			),
			'borders' => array(
				'image' => array(
					'label_prefix' => esc_html__( 'Image', 'divi-plus' ),
					'css'          => array(
						'main' => array(
							'border_radii'  => "{$this->main_css_element} .dipl_filterable_gallery_item img, {$this->main_css_element} .dipl_filterable_gallery_item .et_overlay",
							'border_styles' => "{$this->main_css_element} .dipl_filterable_gallery_item img",
						),
						'important' => 'all',
					),
					'tab_slug'     => 'advanced',
					'toggle_slug'  => 'border',
				),
				'category' => array(
					'label_prefix' => esc_html__( 'Category', 'divi-plus' ),
					'css'          => array(
						'main' => array(
							'border_radii'  => "{$this->main_css_element} .dipl_filterable_gallery_filter_categories li",
							'border_styles' => "{$this->main_css_element} .dipl_filterable_gallery_filter_categories li",
						),
						'important' => 'all',
					),
					'tab_slug'     => 'advanced',
					'toggle_slug'  => 'category',
					'sub_toggle'   => 'normal',
				),
				'active_category' => array(
					'label_prefix' => esc_html__( 'Active Category', 'divi-plus' ),
					'css'          => array(
						'main' => array(
							'border_radii'  => "{$this->main_css_element} .dipl_filterable_gallery_filter_categories li.dipl_filterable_gallery_active_category",
							'border_styles' => "{$this->main_css_element} .dipl_filterable_gallery_filter_categories li.dipl_filterable_gallery_active_category",
						),
						'important' => 'all',
					),
					'tab_slug'     => 'advanced',
					'toggle_slug'  => 'category',
					'sub_toggle'   => 'active',
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
				'image' => array(
					'label'       => esc_html__( 'Image Box Shadow', 'divi-plus' ),
					'css'         => array(
						'main' => "{$this->main_css_element} .dipl_filterable_gallery_item img",
						'important' => 'all',
					),
					'tab_slug'    => 'advanced',
					'toggle_slug' => 'box_shadow',
				),
				'category' => array(
					'label'       => esc_html__( 'Category Box Shadow', 'divi-plus' ),
					'css'         => array(
						'main' => "{$this->main_css_element} .dipl_filterable_gallery_filter_categories li",
					),
					'tab_slug'    => 'advanced',
					'toggle_slug'  => 'category',
					'sub_toggle'   => 'normal',
				),
				'active_category' => array(
					'label'       => esc_html__( 'Active Category Box Shadow', 'divi-plus' ),
					'css'         => array(
						'main' => "{$this->main_css_element} .dipl_filterable_gallery_filter_categories li.dipl_filterable_gallery_active_category",
					),
					'tab_slug'    	=> 'advanced',
					'toggle_slug'  	=> 'category',
					'sub_toggle'   	=> 'active',
				),
				'default' => array(
					'css' => array(
						'main' => $this->main_css_element,
						'important' => 'all',
					),
				),
			),
			'background' => array(
				'use_background_video' => false,
				'options' => array(
					'parallax' => array( 'type' => 'skip' ),
				),
			),
			'text' => false,
			'filters' => false,
		);
	}

	public function get_fields() {
		$accent_color = et_get_option( 'accent_color', '#2ea3f2' );
		return array_merge(
			array(
				'number_of_images' => array(
					'label'            => esc_html__( 'Number of Images', 'divi-plus' ),
					'type'             => 'text',
					'option_category'  => 'configuration',
					'default'          => '20',
					'tab_slug'         => 'general',
					'toggle_slug'      => 'main_content',
					'description'      => esc_html__( 'Choose how many images you would like to display. If pagination is enabled this will be treated as images per page.', 'divi-plus' ),
					'computed_affects' => array(
						'__gallery_data',
					)
				),
				'offset_number' => array(
	                'label'            => esc_html__( 'Offset Number', 'divi-plus' ),
	                'type'             => 'text',
	                'option_category'  => 'configuration',
	                'default'          => 0,
	                'tab_slug'         => 'general',
	                'toggle_slug'      => 'main_content',
	                'description'      => esc_html__( 'Choose how many images you would like to skip. These images will not be shown in the gallery.', 'divi-plus' ),
	                'computed_affects' => array(
	                    '__gallery_data',
	                ),
	            ),
	            'gallery_order_by' => array(
	                'label'            => esc_html__( 'Order by', 'divi-plus' ),
	                'type'             => 'select',
	                'option_category'  => 'configuration',
	                'options'          => array(
	                    'date'      => esc_html__( 'Date', 'divi-plus' ),
	                    'modified'  => esc_html__( 'Modified Date', 'divi-plus' ),
	                    'title'     => esc_html__( 'Title', 'divi-plus' ),
	                    'name'      => esc_html__( 'Slug', 'divi-plus' ),
	                    'ID'        => esc_html__( 'ID', 'divi-plus' ),
	                    'rand'		=> esc_html__( 'Random', 'divi-plus' ),
	                    'none'      => esc_html__( 'None', 'divi-plus' ),
	                ),
	                'default'          => 'date',
	                'tab_slug'         => 'general',
	                'toggle_slug'      => 'main_content',
	                'description'      => esc_html__( 'Here you can choose the order type of your images.', 'divi-plus' ),
	                'computed_affects' => array(
	                    '__gallery_data',
	                ),
	            ),
	            'gallery_order' => array(
	                'label'            => esc_html__( 'Order', 'divi-plus' ),
	                'type'             => 'select',
	                'option_category'  => 'configuration',
	                'options'          => array(
	                    'DESC' => esc_html__( 'DESC', 'divi-plus' ),
	                    'ASC'  => esc_html__( 'ASC', 'divi-plus' ),
	                ),
	                'default'          => 'DESC',
	                'tab_slug'         => 'general',
	                'toggle_slug'      => 'main_content',
	                'description'      => esc_html__( 'Here you can choose the order of your images.', 'divi-plus' ),
	                'computed_affects' => array(
	                    '__gallery_data',
	                ),
	            ),
	            'include_categories' => array(
	                'label'             => esc_html__( 'Select Categories', 'divi-plus' ),
	                'type'              => 'categories',
	                'option_category'   => 'basic_option',
	                'renderer_options'  => array(
	                    'use_terms'  => true,
	                    'term_name'  => 'attachment-category',
	                    'field_name' => 'el_include_attachment_categories',
	                ),
	                'tab_slug'          => 'general',
	                'toggle_slug'       => 'main_content',
	                'description'       => esc_html__( 'Here you can choose which category images you would like to display. If you want to display all images, then leave it unchecked.', 'divi-plus' ),
	                'computed_affects'  => array(
	                    '__gallery_data'
	                )
	            ),
				'number_of_columns' => array(
	                'label'             => esc_html__( 'Number Of Columns', 'divi-plus' ),
	                'type'              => 'select',
	                'option_category'   => 'configuration',
	                'options'           => array(
	                    '1'         => esc_html( '1' ),
	                    '2'         => esc_html( '2' ),
	                    '3'         => esc_html( '3' ),
	                    '4'         => esc_html( '4' ),
	                    '5'         => esc_html( '5' ),
	                    '6'         => esc_html( '6' ),
	                    '7'         => esc_html( '7' ),
	                    '8'         => esc_html( '8' ),
	                    '9'         => esc_html( '9' ),
	                    '10'        => esc_html( '10' ),
	                    '11'        => esc_html( '11' ),
	                    '12'        => esc_html( '12' ),
	                    '13'        => esc_html( '13' ),
	                    '14'        => esc_html( '14' ),
	                    '15'        => esc_html( '15' ),
	                ),
	                'default'           => '4',
	                'mobile_options'    => true,
	                'tab_slug'          => 'general',
	                'toggle_slug'       => 'main_content',
	                'description'       => esc_html__( 'Here you can select the number of columns to display images.', 'divi-plus' ),
	            ),
	            'column_spacing' => array(
	                'label'             => esc_html__( 'Column Spacing', 'divi-plus' ),
					'type'              => 'range',
					'option_category'  	=> 'layout',
					'range_settings'    => array(
						'min'   => '0',
						'max'   => '100',
						'step'  => '1',
					),
					'fixed_unit'		=> 'px',
					'fixed_range'       => true,
					'validate_unit'		=> true,
					'mobile_options'    => true,
					'default'           => '15px',
					'default_on_front'  => '15px',
					'tab_slug'        	=> 'general',
					'toggle_slug'     	=> 'main_content',
					'description'       => esc_html__( 'Increase or decrease spacing between columns.', 'divi-plus' ),
	            ),
	            'image_size' => array(
	                'label'             => esc_html__( 'Image Size', 'divi-plus' ),
	                'type'              => 'select',
	                'option_category'   => 'configuration',
	                'options'           => array(
	                	'thumbnail' => esc_html__( 'Thumbnail', 'divi-plus' ),
	                    'medium' 	=> esc_html__( 'Medium', 'divi-plus' ),
	                    'large' 	=> esc_html__( 'Large', 'divi-plus' ),
	                    'full' 		=> esc_html__( 'Full', 'divi-plus' ),
	                ),
	                'default'           => 'medium',
	                'tab_slug'          => 'general',
	                'toggle_slug'       => 'main_content',
	                'description'       => esc_html__( 'Here you can select the size of images in gallery.', 'divi-plus' ),
	                'computed_affects' 	=> array(
						'__gallery_data',
					),
	            ),
	            'disable_lazy_loading' => array(
					'label'            => esc_html__( 'Disable Lazy Loading', 'divi-plus' ),
					'type'             => 'yes_no_button',
					'option_category'  => 'configuration',
					'options'          => array(
						'on'  => esc_html__( 'Yes', 'divi-plus' ),
						'off' => esc_html__( 'No', 'divi-plus' ),
					),
					'default' 			=> 'on',
					'tab_slug'          => 'general',
					'toggle_slug'      	=> 'main_content',
					'description'      	=> esc_html__( 'Whether or not to lazy load images.', 'divi-plus' ),
				),
				'show_all_filter' => array(
					'label'            => esc_html__( 'Show All Images filter', 'divi-plus' ),
					'type'             => 'yes_no_button',
					'option_category'  => 'configuration',
					'options'          => array(
						'on'  => esc_html__( 'Yes', 'divi-plus' ),
						'off' => esc_html__( 'No', 'divi-plus' ),
					),
					'default' 			=> 'on',
					'tab_slug'          => 'general',
					'toggle_slug'      	=> 'elements',
					'description'      	=> esc_html__( 'Whether or not to show the all images filter.', 'divi-plus' ),
					'computed_affects' 	=> array(
						'__gallery_data',
					),
				),
				'all_images_text' => array(
		            'label'             => esc_html__( 'All Images Text', 'divi-plus' ),
		            'type'              => 'text',
		            'option_category'   => 'configuration',
		            'default'           => esc_html__( 'All', 'divi-plus' ),
		            'show_if'           => array(
		                'show_all_filter' => 'on',
		            ),
		            'tab_slug'          => 'general',
		            'toggle_slug'       => 'elements',
		            'description'       => esc_html__( 'Here you can define the All images text you would like to display.', 'divi-plus' ),
		            'computed_affects'  => array(
		                '__gallery_data'
		            )
		        ),
	            'show_title' => array(
					'label'            => esc_html__( 'Show Title', 'divi-plus' ),
					'type'             => 'yes_no_button',
					'option_category'  => 'configuration',
					'options'          => array(
						'on'  => esc_html__( 'Yes', 'divi-plus' ),
						'off' => esc_html__( 'No', 'divi-plus' ),
					),
					'default' 			=> 'off',
					'tab_slug'          => 'general',
					'toggle_slug'      	=> 'elements',
					'description'      	=> esc_html__( 'Whether or not to show the title for images (if available).', 'divi-plus' ),
				),
				'title_area' => array(
					'label'             => esc_html__( 'Show Title in', 'divi-plus' ),
	                'type'              => 'select',
	                'option_category'   => 'configuration',
	                'options'           => array(
	                    'lightbox'	=> esc_html__( 'Lightbox Only', 'divi-plus' ),
	                    'gallery' 	=> esc_html__( 'Gallery Only', 'divi-plus' ),
	                    'overlay' 	=> esc_html__( 'Overlay', 'divi-plus' ),
	                    'both'		=> esc_html__( 'Both Gallery and Lightbox', 'divi-plus' ),
	                ),
	                'default'           => 'lightbox',
	                'show_if'         	=> array(
	                    'show_title' => 'on',
	                ),
	                'tab_slug'          => 'general',
	                'toggle_slug'       => 'elements',
	                'description'       => esc_html__( 'Here you can select the area where you want to display title.', 'divi-plus' ),
				),
				'show_caption' => array(
					'label'            => esc_html__( 'Show Caption', 'divi-plus' ),
					'type'             => 'yes_no_button',
					'option_category'  => 'configuration',
					'options'          => array(
						'on'  => esc_html__( 'Yes', 'divi-plus' ),
						'off' => esc_html__( 'No', 'divi-plus' ),
					),
					'default' 			=> 'off',
					'tab_slug'          => 'general',
					'toggle_slug'      	=> 'elements',
					'description'      	=> esc_html__( 'Whether or not to show the caption for images (if available).', 'divi-plus' ),
				),
				'caption_area' => array(
					'label'             => esc_html__( 'Show Caption in', 'divi-plus' ),
	                'type'              => 'select',
	                'option_category'   => 'configuration',
	                'options'           => array(
	                    'lightbox'	=> esc_html__( 'Lightbox Only', 'divi-plus' ),
	                    'gallery' 	=> esc_html__( 'Gallery Only', 'divi-plus' ),
	                    'overlay' 	=> esc_html__( 'Overlay', 'divi-plus' ),
	                    'both'		=> esc_html__( 'Both Gallery and Lightbox', 'divi-plus' ),
	                ),
	                'default'           => 'lightbox',
	                'show_if'         	=> array(
	                    'show_caption' => 'on',
	                ),
	                'tab_slug'          => 'general',
	                'toggle_slug'       => 'elements',
	                'description'       => esc_html__( 'Here you can select the area where you want to display caption.', 'divi-plus' ),
				),
	            'on_click_trigger' => array(
					'label'             => esc_html__( 'OnClick Trigger', 'divi-plus' ),
					'type'              => 'select',
	                'option_category'   => 'configuration',
	                'options'           => array(
	                	'none'		=> esc_html__( 'None', 'divi-plus' ),
	                    'lightbox'	=> esc_html__( 'Lightbox', 'divi-plus' ),
	                    'link' 		=> esc_html__( 'Link', 'divi-plus' ),
	                ),
					'default' 			=> 'none',
					'tab_slug'          => 'general',
					'toggle_slug'      	=> 'main_content',
					'description'      	=> esc_html__( 'Choose an action to perform on clicking of an image.', 'divi-plus' ),
				),
				'link_target'   => array(
					'label'            => esc_html__( 'Link Target', 'divi-plus' ),
					'type'             => 'select',
					'option_category'  => 'configuration',
					'options'          => array(
						'off' => esc_html__( 'In The Same Window', 'divi-plus' ),
						'on'  => esc_html__( 'In The New Tab', 'divi-plus' ),
					),
					'show_if'          => array(
	                    'on_click_trigger' => 'link',
	                ),
					'default_on_front' => 'off',
					'tab_slug'         => 'general',
					'toggle_slug'      => 'main_content',
					'description'      => esc_html__( 'Here you can choose whether or not your link opens in a new window', 'divi-plus' ),
				),
				'isotope_transition_duration' => array(
	                'label'             => esc_html__( 'Images Transition Duration', 'divi-plus' ),
					'type'              => 'range',
					'option_category'  	=> 'layout',
					'range_settings'    => array(
						'min'   => '0',
						'max'   => '1000',
						'step'  => '1',
					),
					'unitless'			=> true,
					'fixed_range'       => true,
					'mobile_options'    => true,
					'default'           => '400',
					'tab_slug'        	=> 'general',
					'toggle_slug'     	=> 'main_content',
					'description'       => esc_html__( 'Increase or decrease tranisition time in milliseconds between images displacement.', 'divi-plus' ),
	            ),
	            'no_gallery_text' => array(
					'label'            => esc_html__( 'Empty gallery text', 'divi-plus' ),
					'type'             => 'text',
					'option_category'  => 'configuration',
					'default'          => esc_html__( 'No Gallery Found!!', 'divi-plus' ),
					'tab_slug'         => 'general',
					'toggle_slug'      => 'main_content',
					'description'      => esc_html__( 'Here you can set custom text for the message appears when there is no gallery found.', 'divi-plus' ),
					'computed_affects'  => array(
	                    '__gallery_data'
	                )
				),
				'lightbox_effect' => array(
					'label'            => esc_html__( 'Lighbox Effect', 'divi-plus' ),
					'type'             => 'select',
					'option_category'  => 'configuration',
					'options'          => array(
						'none' => esc_html__( 'None', 'divi-plus' ),
						'zoom' => esc_html__( 'Zoom', 'divi-plus' ),
						'fade' => esc_html__( 'Fade', 'divi-plus' ),
					),
					'show_if'          => array(
	                    'on_click_trigger' => 'lightbox',
	                ),
					'default' 		   => 'none',
					'tab_slug'         => 'general',
					'toggle_slug'      => 'lightbox',
					'description'      => esc_html__( 'Here you can choose opening effect of lightbox.', 'divi-plus' ),
				),
				'lightbox_transition_duration' => array(
	                'label'             => esc_html__( 'Transition Duration', 'divi-plus' ),
	                'type'              => 'range',
	                'option_category'	=> 'layout',
	                'range_settings'        => array(
	                    'min'  => '100',
	                    'max'  => '2000',
	                    'step' => '100',
	                ),
	                'show_if'          	=> array(
	                    'on_click_trigger' => 'lightbox',
	                    'lightbox_effect' => array( 'zoom', 'fade' ),
	                ),
	                'unitless'          => true,
	                'default_on_front'  => '300',
	                'tab_slug'          => 'general',
	                'toggle_slug'       => 'lightbox',
	                'description'       => esc_html__( 'Here you can select the transition duration in miliseconds.', 'divi-plus' ),
	            ),
				'enable_navigation' => array(
					'label'             => esc_html__( 'Enable Navigation', 'divi-plus' ),
					'type'              => 'yes_no_button',
					'option_category'   => 'configuration',
					'options'           => array(
						'on'  => esc_html__( 'Yes', 'divi-plus' ),
						'off' => esc_html__( 'No', 'divi-plus' ),
					),
					'show_if'         	=> array(
	                    'on_click_trigger' => 'lightbox',
	                ),
					'default' 			=> 'on',
					'tab_slug'          => 'general',
					'toggle_slug'      	=> 'lightbox',
					'description'      	=> esc_html__( 'Whether or not to enable navigation in lightbox.', 'divi-plus' ),
				),
				'lightbox_title_and_caption_style' => array(
					'label'             => esc_html__( 'Title & Caption Style', 'divi-plus' ),
	                'type'              => 'select',
	                'option_category'   => 'configuration',
	                'options'           => array(
	                    'image_overlay'	=> esc_html__( 'Image Overlay', 'divi-plus' ),
	                    'below_image' 	=> esc_html__( 'Below Image', 'divi-plus' ),
	                ),
	                'default'           => 'image_overlay',
	                'show_if'         	=> array(
	                    'on_click_trigger' => 'lightbox',
	                ),
	                'tab_slug'          => 'general',
	                'toggle_slug'       => 'lightbox',
	                'description'       => esc_html__( 'Here you can select the display style of title and caption in lightbox.', 'divi-plus' ),
				),
				'enable_overlay' => array(
					'label'            => esc_html__( 'Enable Image Overlay on Hover', 'divi-plus' ),
					'type'             => 'yes_no_button',
					'option_category'  => 'configuration',
					'options'          => array(
						'on'  => esc_html__( 'Yes', 'divi-plus' ),
						'off' => esc_html__( 'No', 'divi-plus' ),
					),
					'default' 			=> 'off',
					'tab_slug'          => 'general',
					'toggle_slug'      	=> 'elements',
					'description'      	=> esc_html__( 'Whether or not to show images in lightbox.', 'divi-plus' ),
					'computed_affects'  => array(
	                    '__gallery_data'
	                )
				),
				'show_overlay_icon' => array(
					'label'            => esc_html__( 'Show Overlay Icon', 'divi-plus' ),
					'type'             => 'yes_no_button',
					'option_category'  => 'configuration',
					'options'          => array(
						'on'  => esc_html__( 'Yes', 'divi-plus' ),
						'off' => esc_html__( 'No', 'divi-plus' ),
					),
					'default' 			=> 'off',
					'tab_slug'          => 'general',
					'toggle_slug'      	=> 'elements',
					'description'      	=> esc_html__( 'Whether or not to show icon on overlay.', 'divi-plus' ),
					'computed_affects'  => array(
	                    '__gallery_data'
	                )
				),
				'overlay_icon' => array(
					'label'           => esc_html__( 'Overlay Icon', 'divi-plus' ),
					'type'            => 'select_icon',
					'option_category' => 'configuration',
					'class'           => array( 'et-pb-font-icon' ),
					'show_if'         => array(
	                    'enable_overlay' => 'on',
	                    'show_overlay_icon' => 'on'
	                ),
					'tab_slug'        => 'general',
					'toggle_slug'     => 'elements',
					'description'     => esc_html__( 'Here you can define a custom icon for the overlay', 'divi-plus' ),
					'computed_affects'  => array(
	                    '__gallery_data'
	                )
				),
				'show_pagination' => array(
					'label'            => esc_html__( 'Show Pagination', 'divi-plus' ),
					'type'             => 'yes_no_button',
					'option_category'  => 'configuration',
					'options'          => array(
						'off' => esc_html__( 'No', 'divi-plus' ),
						'on'  => esc_html__( 'Yes', 'divi-plus' ),
					),
					'default'          => 'off',
					'tab_slug'         => 'general',
					'toggle_slug'      => 'pagination',
					'description'      => esc_html__( 'Show Pagination or not.', 'divi-plus' ),
					'computed_affects' => array(
	                    '__gallery_data'
	                )
				),
				'show_prev_next' => array(
					'label'            => esc_html__( 'Show Previous Next Links', 'divi-plus' ),
					'type'             => 'yes_no_button',
					'option_category'  => 'configuration',
					'options'          => array(
						'off' => esc_html__( 'No', 'divi-plus' ),
						'on'  => esc_html__( 'Yes', 'divi-plus' ),
					),
					'show_if'      => array(
						'show_pagination' => 'on',
					),
					'default'          => 'off',
					'tab_slug'         => 'general',
					'toggle_slug'      => 'pagination',
					'description'      => esc_html__( 'Show Previous Next Links or not.', 'divi-plus' ),
				),
				'next_text' => array(
					'label'            => esc_html__( 'Next Link Text', 'divi-plus' ),
					'type'             => 'text',
					'option_category'  => 'configuration',
					'default'		   => 'Next',
					'show_if'      => array(
						'show_pagination' => 'on',
						'show_prev_next'  => 'on',
					),
					'tab_slug'         => 'general',
					'toggle_slug'      => 'pagination',
					'description'      => esc_html__( 'Here you can define Next Link text in numbered pagination.', 'divi-plus' ),
				),
				'prev_text' => array(
					'label'            => esc_html__( 'Prev Link Text', 'divi-plus' ),
					'type'             => 'text',
					'option_category'  => 'configuration',
					'default'		   => 'Prev',
					'show_if'      => array(
						'show_pagination' => 'on',
						'show_prev_next'  => 'on',
					),
					'tab_slug'         => 'general',
					'toggle_slug'      => 'pagination',
					'description'      => esc_html__( 'Here you can define Previous Link text in numbered pagination.', 'divi-plus' ),
				),
				'meta_background_color_lightbox' => array(
					'label'           	=> esc_html__( 'Title & Caption Background Color', 'divi-plus' ),
					'type'            	=> 'color-alpha',
					'custom_color'    	=> true,
					'default'		  	=> 'rgba(0,0,0,0.6)',
					'show_if'         	=> array(
	                    'on_click_trigger' => 'lightbox',
	                ),
					'tab_slug'        	=> 'advanced',
					'toggle_slug'     	=> 'lightbox',
					'description'     	=> esc_html__( 'Here you can define a custom overlay color for the title and caption.', 'divi-plus' ),
				),
				'lightbox_background_color' => array(
					'label'           	=> esc_html__( 'Lightbox Background Color', 'divi-plus' ),
					'type'            	=> 'color-alpha',
					'custom_color'    	=> true,
					'default'		  	=> 'rgba(0,0,0,0.8)',
					'show_if'         	=> array(
	                    'on_click_trigger' => 'lightbox',
	                ),
					'tab_slug'        	=> 'advanced',
					'toggle_slug'     	=> 'lightbox',
					'description'     	=> esc_html__( 'Here you can define a custom background color for the lightbox.', 'divi-plus' ),
				),
				'lightbox_close_icon_color' => array(
					'label'           	=> esc_html__( 'Close Icon Color', 'divi-plus' ),
					'type'            	=> 'color-alpha',
					'custom_color'    	=> true,
					'default'		  	=> '#fff',
					'show_if'         	=> array(
	                    'on_click_trigger' => 'lightbox',
	                ),
					'tab_slug'        	=> 'advanced',
					'toggle_slug'     	=> 'lightbox',
					'description'     	=> esc_html__( 'Here you can define a custom color for the close icon.', 'divi-plus' ),
				),
				'lightbox_arrows_color' => array(
					'label'           	=> esc_html__( 'Arrows Color', 'divi-plus' ),
					'type'            	=> 'color-alpha',
					'custom_color'    	=> true,
					'default'		  	=> '#fff',
					'show_if'         	=> array(
	                    'on_click_trigger' => 'lightbox',
	                ),
					'tab_slug'        	=> 'advanced',
					'toggle_slug'     	=> 'lightbox',
					'description'     	=> esc_html__( 'Here you can define a custom color for the arrows.', 'divi-plus' ),
				),
				'overlay_icon_size' => array(
	                'label'             => esc_html__( 'Icon Size', 'divi-plus' ),
					'type'              => 'range',
					'option_category'  	=> 'layout',
					'range_settings'    => array(
						'min'   => '0',
						'max'   => '100',
						'step'  => '1',
					),
					'fixed_unit'		=> 'px',
					'fixed_range'       => true,
					'validate_unit'		=> true,
					'mobile_options'    => true,
					'default'           => '32px',
					'show_if'         	=> array(
	                    'enable_overlay' => 'on',
	                    'show_overlay_icon' => 'on'
	                ),
					'tab_slug'        	=> 'advanced',
					'toggle_slug'     	=> 'overlay',
					'description'       => esc_html__( 'Increase or decrease icon font size.', 'divi-plus' ),
	            ),
				'overlay_icon_color' => array(
					'label'           => esc_html__( 'Overlay Icon Color', 'divi-plus' ),
					'type'            => 'color-alpha',
					'custom_color'    => true,
					'show_if'         => array(
	                    'enable_overlay' => 'on',
	                    'show_overlay_icon' => 'on'
	                ),
					'tab_slug'        => 'advanced',
					'toggle_slug'     => 'overlay',
					'description'     => esc_html__( 'Here you can define a custom color for the icon.', 'divi-plus' ),
				),
				'overlay_color' => array(
					'label'           => esc_html__( 'Overlay Background Color', 'divi-plus' ),
					'type'            => 'color-alpha',
					'custom_color'    => true,
					'show_if'         => array(
	                    'enable_overlay' => 'on',
	                    'show_overlay_icon' => 'on'
	                ),
					'tab_slug'        => 'advanced',
					'toggle_slug'     => 'overlay',
					'description'     => esc_html__( 'Here you can define a custom color for the overlay', 'divi-plus' ),
				),
				'category_bg_color' => array(
	                'label'             => esc_html__( 'Category Background', 'divi-plus' ),
	                'type'              => 'background-field',
	                'base_name'         => 'category_bg',
	                'context'           => 'category_bg_color',
	                'option_category'   => 'button',
	                'custom_color'      => true,
	                'background_fields' => $this->generate_background_options( 'category_bg', 'button', 'advanced', 'category', 'category_bg_color' ),
	                'hover'             => 'tabs',
	                'default'			=> '#000',
	                'tab_slug'          => 'advanced',
	                'toggle_slug'       => 'category',
	                'sub_toggle'        => 'normal',
	                'description'       => esc_html__( 'Here you can adjust the background style of the category by customizing the background color, gradient, and image.', 'divi-plus' ),
	            ),
	            'active_category_bg_color' => array(
	                'label'             => esc_html__( 'Active Category Background', 'divi-plus' ),
	                'type'              => 'background-field',
	                'base_name'         => 'active_category_bg',
	                'context'           => 'active_category_bg_color',
	                'option_category'   => 'button',
	                'custom_color'      => true,
	                'background_fields' => $this->generate_background_options( 'active_category_bg', 'button', 'advanced', 'category', 'active_category_bg_color' ),
	                'hover'             => 'tabs',
	                'default'			=> 'transparent',
	                'tab_slug'          => 'advanced',
	                'toggle_slug'       => 'category',
	                'sub_toggle'        => 'active',
	                'description'       => esc_html__( 'Here you can adjust the background style of the active category by customizing the background color, gradient, and image.', 'divi-plus' ),
	            ),
				'pagination_link_background_color' => array(
					'label'        => esc_html__( 'Pagination Link Background Color', 'divi-plus' ),
					'type'         => 'color-alpha',
					'custom_color' => true,
					'default'	   => 'transparent',
					'show_if'      => array(
						'show_pagination' => 'on',
					),
					'hover'		   => 'tabs',
					'tab_slug'     => 'advanced',
					'toggle_slug'  => 'pagination',
					'description'  => esc_html__( 'Here you can define a custom background color for the pagination link.', 'divi-plus' ),
				),
				'pagination_link_color' => array(
					'label'        => esc_html__( 'Pagination Link Color', 'divi-plus' ),
					'type'         => 'color-alpha',
					'custom_color' => true,
					'hover'		   => 'tabs',
					'default'	   => '#000',
					'show_if'      => array(
						'show_pagination' => 'on',
					),
					'tab_slug'     => 'advanced',
					'toggle_slug'  => 'pagination',
					'description'  => esc_html__( 'Here you can define a custom color for the pagination link.', 'divi-plus' ),
				),
				'active_pagination_link_background_color' => array(
					'label'        => esc_html__( 'Active Pagination Link Background Color', 'divi-plus' ),
					'type'         => 'color-alpha',
					'custom_color' => true,
					'default'	   => '#000',
					'hover'		   => 'tabs',
					'show_if'      => array(
						'show_pagination' => 'on',
					),
					'tab_slug'     => 'advanced',
					'toggle_slug'  => 'pagination',
					'description'  => esc_html__( 'Here you can define a custom background color for the active pagination link.', 'divi-plus' ),
				),
				'active_pagination_link_color' => array(
					'label'        => esc_html__( 'Active Pagination Link Color', 'divi-plus' ),
					'type'         => 'color-alpha',
					'custom_color' => true,
					'default'	   => '#fff',
					'hover'		   => 'tabs',
					'show_if'      => array(
						'show_pagination' => 'on',
					),
					'tab_slug'     => 'advanced',
					'toggle_slug'  => 'pagination',
					'description'  => esc_html__( 'Here you can define a custom color for the active pagination link.', 'divi-plus' ),
				),
				'__gallery_data' => array(
					'type'                => 'computed',
					'computed_callback'   => array( 'DIPL_FilterableGallery', 'get_gallery' ),
					'computed_depends_on' => array(
						'number_of_images',
						'offset_number',
						'gallery_order_by',
						'gallery_order',
						'include_categories',
						'image_size',
						'no_gallery_text',
						'show_all_filter',
						'all_images_text',
						'enable_overlay',
						'show_overlay_icon',
						'overlay_icon',
						'show_pagination',
						'title_level',
					),
				),
			),
			$this->generate_background_options( 'category_bg', 'skip', 'advanced', 'category', 'category_bg_color' ),
			$this->generate_background_options( 'active_category_bg', 'skip', 'advanced', 'category', 'active_category_bg_color' )
		);

	}

	public static function get_gallery( $args = array(), $conditional_tags = array(), $current_page = array() ) {
		$defaults = array(
			'number_of_images' 			=> '20',
			'offset_number'		 		=> '0',
			'gallery_order_by' 			=> 'date',
			'gallery_order' 			=> 'DESC',
			'include_categories' 		=> '',
			'image_size' 				=> 'medium',
			'no_gallery_text'			=> 'No Gallery Found!!',
			'show_all_filter'			=> 'on',
			'all_images_text' 			=> 'All',
			'enable_overlay' 			=> 'off',
			'show_overlay_icon'			=> 'on',
			'overlay_icon' 				=> '',
			'show_pagination'			=> 'off',
			'title_level' 				=> 'h4',
		);

		$args = wp_parse_args( $args, $defaults );

		foreach ( $defaults as $key => $default ) {
			${$key} = esc_html( et_()->array_get( $args, $key, $default ) );
		}

		$processed_title_level 	= et_pb_process_header_level( $title_level, 'h4' );
		$processed_title_level	= esc_html( $processed_title_level );

		$number_of_images = ( 0 === $number_of_images || '' === $number_of_images ) ? 20 : (int) $number_of_images;

		$args = array(
			'post_type'      => 'attachment',
			'posts_per_page' => $number_of_images,
			'post_status'	 => 'any',
			'orderby'        => sanitize_text_field( $gallery_order_by ),
			'order'          => sanitize_text_field( $gallery_order ),
			'offset'		 => intval( $offset_number ),
			'post_mime_type' => 'image/jpeg,image/gif,image/jpg,image/png,image/webp',
		);

		if ( '' !== $include_categories ) {
			$include_categories = array_map( 'intval', explode( ',', $include_categories ) );
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'attachment-category',
					'field'    => 'term_id',
					'terms'    => $include_categories,
					'operator' => 'IN',
				),
			);

			$terms = get_terms(array(
	            'taxonomy' => 'attachment-category',
	            'include' => $include_categories,
	            'hide_empty' => true,
	        ));

			if ( $terms && ! is_wp_error( $terms ) && ! empty( $terms ) ) {
				$include_categories = array();
				foreach ( $terms as $term ) {
					$include_categories[$term->slug] = sprintf(
						esc_html__( '%1$s', 'divi-plus' ),
						$term->name
					);
	       		}
	       	}
		}

		$query = new WP_Query( $args );

		if ( $query->have_posts() ) {
			$image_ids = wp_list_pluck( $query->posts, 'ID' );

			if ( 'on' === $enable_overlay ) {
				if ( 'on' === $show_overlay_icon ) {
					$overlay_output = ET_Builder_Module_Helper_Overlay::render(
						array(
							'icon' => $overlay_icon,
						)
					);
				} else {
					$overlay_output = ET_Builder_Module_Helper_Overlay::render( array() );
				}
			}

			$gallery_items = '';
			$gallery_item_class = 'dipl_filterable_gallery_item_page_1';
			$attachment_categories = array();
			$allowed_tags = array(
				'div' => array(
			    	'class' => true,
			        'id' => true,
			    ),
				'p' => array(
			    	'class' => true,
			        'id' => true,
			    ),
				'h1' => array(
			    	'class' => true,
			        'id' => true,
			    ),
				'h2' => array(
			    	'class' => true,
			        'id' => true,
			    ),
				'h3' => array(
			    	'class' => true,
			        'id' => true,
			    ),
				'h4' => array(
			    	'class' => true,
			        'id' => true,
			    ),
				'h5' => array(
			    	'class' => true,
			        'id' => true,
			    ),
				'h6' => array(
			    	'class' => true,
			        'id' => true,
			    ),
				'ul' => array(
			    	'class' => true,
			        'id' => true,
			    ),
				'ol' => array(
			    	'class' => true,
			        'id' => true,
			    ),
				'li' => array(
			    	'class' => true,
			        'id' => true,
			    ),
				'a' => array(
			    	'href'  => true,
			        'title' => true,
			        'class' => true,
			        'id' => true,
			        'target' => true,
			        'rel' => true,
			    ),
			    'abbr' => array(
			        'title' => true,
			    ),
			    'acronym' => array(
			        'title' => true,
			    ),
			    'b' => array(),
			    'blockquote' => array(
			        'cite' => true,
			    ),
			    'cite' => array(),
			    'code' => array(),
			    'del'  => array(
			        'datetime' => true,
			    ),
			    'em' => array(),
			    'i' => array(),
			    'q' => array(
			        'cite' => true,
			    ),
			    's' => array(),
			    'strike' => array(),
			    'strong' => array(),
			    'span' => array(
			    	'class'  => true,
			        'id' => true,
			    ),
			);
			foreach( $image_ids as $image_id ) {
				if ( '' !== trim( wptexturize( get_the_title( $image_id ) ) ) ) {
					$title = sprintf(
						'<%1$s class="dipl_filterable_gallery_item_title">%2$s</%1$s>',
						$processed_title_level,
						wp_kses( get_the_title( $image_id ), $allowed_tags )
					);
				} else {
					$title = '';
				}

				if ( '' !== trim( wp_get_attachment_caption( $image_id ) ) ) {
					$caption = sprintf(
						'<div class="dipl_filterable_gallery_item_caption">%2$s</div>',
						$processed_title_level,
						wp_kses( wp_get_attachment_caption( $image_id ), $allowed_tags )
					);
				} else {
					$caption = '';
				}

				if ( '' !== $title || '' !== $caption ) {
					$title_and_caption = sprintf(
						'<div class="dipl_filterable_gallery_title_caption_wrapper">%1$s %2$s</div>',
						et_core_intentionally_unescaped( $title, 'html' ),
						et_core_intentionally_unescaped( $caption, 'html' )
					);
				} else {
					$title_and_caption = '';
				}

				$categories = get_the_terms( $image_id, 'attachment-category' );
				$image_classes	= array( 
					"attachment-$image_size",
					"size-$image_size",
					"no-lazyload",
					"skip-lazy",
				);
				$image_atts = array();

				if ( $categories && ! is_wp_error( $categories ) && ! empty( $categories ) ) {
					if ( 'on' !== $show_pagination ) {
						foreach ( $categories as $category ) {
							$attachment_categories[$category->term_id] = sprintf(
								esc_html__( '%1$s', 'divi-plus' ),
								$category->name
							);
						}
					}
					$categories_ids = wp_list_pluck( $categories, 'term_id' );
					$categories 	= wp_list_pluck( $categories, 'slug' );
					$image_classes	= array_merge( 
						$image_classes,
						$categories
					);
					$image_atts['data-categories'] = implode( ',', $categories_ids );
				}
				
				$image_atts['class'] = implode( ' ', $image_classes );

				$gallery_items .= sprintf(
					'<div class="dipl_filterable_gallery_item %4$s">
						<div class="dipl_filterable_gallery_image_wrapper">%1$s %2$s</div>
						%3$s
					</div>',
					et_core_intentionally_unescaped( wp_get_attachment_image( intval( $image_id ), sanitize_text_field( $image_size ), false, $image_atts ), 'html' ),
					isset( $overlay_output ) ? $overlay_output : '',
					et_core_intentionally_unescaped( $title_and_caption, 'html' ),
					esc_attr( $gallery_item_class )
				);
			}

			$filter = '';
			$all_images_text = '' === $all_images_text ?
	            esc_html__( 'All', 'divi-plus' ) :
	            sprintf(
	                esc_html__( '%s', 'divi-plus' ),
	                esc_html( $all_images_text )
	            );

			if ( 'on' === $show_all_filter ) {
				$categories = sprintf(
					'<li class="dipl_filterable_gallery_active_category" data-category="">%1$s</li>',
					esc_html( $all_images_text )
				);
			} else {
				$categories = '';
			}

			$terms = get_terms( array(
	            'orderby' => 'term_order',
	            'taxonomy' => 'attachment-category',
	            'hide_empty' => true,
	        ) );

			if ( $terms && ! is_wp_error( $terms ) && ! empty( $terms ) ) {
				$all_categories = array();
				foreach ( $terms as $term ) {
					$all_categories[$term->term_id] = sprintf(
						esc_html__( '%1$s', 'divi-plus' ),
						$term->name
					);
	       		}

       			if ( ! empty( $include_categories ) ) {
   					$attachment_categories = array_intersect( $all_categories, $include_categories );
   				} else {
   					$attachment_categories = $all_categories;
   				}
	       	}

	       	if ( ! empty ( $attachment_categories ) && 1 < count( $attachment_categories ) ) {
	       		foreach ( $attachment_categories as $term_id => $name ) {
					$categories .= sprintf(
						'<li data-category="%1$s">%2$s</li>',
						esc_attr( $term_id ),
						esc_html( $name )
					);
				}

				$filter = sprintf(
					'<div class="dipl_filterable_gallery_filter_wrapper">
						<ul class="dipl_filterable_gallery_filter_categories">
						%1$s
						</ul>
					</div>',
					$categories
				);
	       	}

			if ( 'on' === $show_pagination ) {
				if ( -1 === $args['posts_per_page'] ) {
					$total_pages = 1;
				} else if ( '' !== $args['offset'] && ! empty( $args['offset'] ) ) {
					$total_pages = intval( ceil( ( $query->found_posts - $args['offset'] ) / $args['posts_per_page'] ) );
				} else {
					$total_pages = intval( ceil( $query->found_posts / $args['posts_per_page'] ) );
				}

				$pagination = sprintf(
					'<div class="dipl_filterable_gallery_pagination_wrapper" data-total_pages="%1$s"><ul></ul></div>',
					$total_pages
				);
			}

			$output = sprintf(
				'%1$s
				<div class="dipl_filterable_gallery_wrapper">
					<div class="dipl_filterable_gallery_item_gutter"></div>
					%2$s
				</div>
				%3$s',
				$filter,
				$gallery_items,
				'on' === $show_pagination ? $pagination : ''
			);
		} else {
			$output = sprintf(
				'<div className="entry"><h4>%1$s</h4></div>',
				esc_html( $no_gallery_text )
			);
		}

		return $output;
	}

	public function render( $attrs, $content, $render_slug ) {
		$multi_view   						= et_pb_multi_view_options( $this );
		$number_of_images 					= $this->props['number_of_images'];
		$offset_number 						= $this->props['offset_number'];
		$gallery_order_by 					= $this->props['gallery_order_by'];
		$gallery_order 						= $this->props['gallery_order'];
		$include_categories 				= $this->props['include_categories'];
		$image_size   						= $this->props['image_size'];
		$disable_lazy_loading				= $this->props['disable_lazy_loading'];
		$show_all_filter					= $this->props['show_all_filter'];
		$all_images_text					= $this->props['all_images_text'];
		$on_click_trigger 					= $this->props['on_click_trigger'];
		$link_target						= $this->props['link_target'];
		$lightbox_effect					= $this->props['lightbox_effect'];
		$lightbox_transition_duration		= $this->props['lightbox_transition_duration'];
		$enable_navigation					= $this->props['enable_navigation'];
		$enable_overlay						= $this->props['enable_overlay'];
		$show_title 						= $this->props['show_title'];
		$title_area							= $this->props['title_area'];
		$show_caption 						= $this->props['show_caption'];
		$caption_area						= $this->props['caption_area'];
		$lightbox_title_and_caption_style 	= $this->props['lightbox_title_and_caption_style'];
		$number_of_columns					= $this->props['number_of_columns'];
		$column_spacing 					= $this->props['column_spacing'];
		$show_overlay_icon					= $this->props['show_overlay_icon'];
		$overlay_icon       				= $this->props['overlay_icon'];
		$overlay_icon_color					= $this->props['overlay_icon_color'];
		$overlay_color						= $this->props['overlay_color'];
		$meta_background_color_lightbox		= $this->props['meta_background_color_lightbox'];
		$lightbox_background_color 			= $this->props['lightbox_background_color'];
		$lightbox_close_icon_color			= $this->props['lightbox_close_icon_color'];
		$lightbox_arrows_color 				= $this->props['lightbox_arrows_color'];
		$show_pagination            		= $this->props['show_pagination'];
		$no_gallery_text					= $this->props['no_gallery_text'];

		$title_level           				= $this->props['title_level'];
		$processed_title_level  			= et_pb_process_header_level( $title_level, 'h4' );
		$processed_title_level  			= esc_html( $processed_title_level );

		$number_of_images = ( 0 === $number_of_images ) ? -1 : (int) $number_of_images;

		$args = array(
			'post_type'      => 'attachment',
			'posts_per_page' => $number_of_images,
			'post_status'    => 'any',
			'orderby'        => sanitize_text_field( $gallery_order_by ),
			'order'          => sanitize_text_field( $gallery_order ),
			'offset'		 => absint( $offset_number ),
			'post_mime_type' => 'image/jpeg,image/gif,image/jpg,image/png,image/webp',
		);

		if ( '' !== $include_categories ) {
			$include_categories = array_map( 'intval', explode( ',', $include_categories ) );
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'attachment-category',
					'field'    => 'term_id',
					'terms'    => $include_categories,
					'operator' => 'IN',
				),
			);

			$terms = get_terms(array(
	            'taxonomy' => 'attachment-category',
	            'include' => $include_categories,
	            'hide_empty' => true,
	        ));

			if ( $terms && ! is_wp_error( $terms ) && ! empty( $terms ) ) {
				$include_categories = array();
				foreach ( $terms as $term ) {
					$include_categories[$term->slug] = sprintf(
						esc_html__( '%1$s', 'divi-plus' ),
						$term->name
					);
	       		}
	       	}
		}

		$query = new WP_Query( $args );

		if ( $query->have_posts() ) {

			$image_ids = wp_list_pluck( $query->posts, 'ID' );

			wp_enqueue_script( 'elicus-isotope-script' );
			wp_enqueue_script( 'elicus-images-loaded-script' );

			if ( 'lightbox' === $on_click_trigger ) {
				wp_enqueue_script('magnific-popup');
				wp_enqueue_style('magnific-popup');
			}

			if ( 'on' === $enable_overlay ) {
				if ( 'on' === $show_overlay_icon ) {
					$overlay_output = ET_Builder_Module_Helper_Overlay::render(
						array(
							'icon' => $overlay_icon,
						)
					);
				} else {
					$overlay_output = ET_Builder_Module_Helper_Overlay::render( array() );
				}

				if ( class_exists( 'ET_Builder_Module_Helper_Style_Processor' ) && method_exists( 'ET_Builder_Module_Helper_Style_Processor', 'process_extended_icon' ) ) {
	                $this->generate_styles(
	                    array(
	                        'utility_arg'    => 'icon_font_family',
	                        'render_slug'    => $render_slug,
	                        'base_attr_name' => 'overlay_icon',
	                        'important'      => true,
	                        'selector'       => '%%order_class%% .et_overlay:before',
	                        'processor'      => array(
	                            'ET_Builder_Module_Helper_Style_Processor',
	                            'process_extended_icon',
	                        ),
	                    )
	                );
	            }
			}

			$gallery_items = '';
			$gallery_item_class = 'dipl_filterable_gallery_item_page_1';
			$attachment_categories = array();
			$allowed_tags = array(
				'div' => array(
			    	'class' => true,
			        'id' => true,
			    ),
				'p' => array(
			    	'class' => true,
			        'id' => true,
			    ),
				'h1' => array(
			    	'class' => true,
			        'id' => true,
			    ),
				'h2' => array(
			    	'class' => true,
			        'id' => true,
			    ),
				'h3' => array(
			    	'class' => true,
			        'id' => true,
			    ),
				'h4' => array(
			    	'class' => true,
			        'id' => true,
			    ),
				'h5' => array(
			    	'class' => true,
			        'id' => true,
			    ),
				'h6' => array(
			    	'class' => true,
			        'id' => true,
			    ),
				'ul' => array(
			    	'class' => true,
			        'id' => true,
			    ),
				'ol' => array(
			    	'class' => true,
			        'id' => true,
			    ),
				'li' => array(
			    	'class' => true,
			        'id' => true,
			    ),
				'a' => array(
			    	'href'  => true,
			        'title' => true,
			        'class' => true,
			        'id' => true,
			        'target' => true,
			        'rel' => true,
			    ),
			    'abbr' => array(
			        'title' => true,
			    ),
			    'acronym' => array(
			        'title' => true,
			    ),
			    'b' => array(),
			    'blockquote' => array(
			        'cite' => true,
			    ),
			    'cite' => array(),
			    'code' => array(),
			    'del'  => array(
			        'datetime' => true,
			    ),
			    'em' => array(),
			    'i' => array(),
			    'q' => array(
			        'cite' => true,
			    ),
			    's' => array(),
			    'strike' => array(),
			    'strong' => array(),
			    'span' => array(
			    	'class'  => true,
			        'id' => true,
			    ),
			);
			foreach( $image_ids as $image_id ) {
				$link = get_post_meta( intval( $image_id ), 'dge_attachment_link', true );
				if ( 'on' === $show_title && '' !== trim( wptexturize( get_the_title( $image_id ) ) ) ) {
					if ( '' !== $link ) {
						$title_text = sprintf(
							'<a href="%2$s" target="%3$s">%1$s</a>',
							trim( wptexturize( get_the_title( $image_id ) ) ),
							esc_url( $link ),
							'on' === $link_target ? '_blank' : '_self'
						);
					} else {
						$title_text = wp_kses( get_the_title( $image_id ), $allowed_tags );
					}

					$title = $multi_view->render_element(
						array(
							'tag'        => $processed_title_level,
							'content'    => $title_text,
							'attrs'      => array(
								'class' => 'dipl_filterable_gallery_item_title',
							),
						)
					);
				} else {
					$title = '';
				}

				if ( 'on' === $show_caption && '' !== trim( wp_get_attachment_caption( $image_id ) ) ) {
					if ( '' !== $link ) {
						$caption_text = sprintf(
							'<a href="%2$s" target="%3$s">%1$s</a>',
							wp_kses( wp_get_attachment_caption( $image_id ), $allowed_tags ),
							esc_url( $link ),
							'on' === $link_target ? '_blank' : '_self'
						);
					} else {
						$caption_text = wp_kses( wp_get_attachment_caption( $image_id ), $allowed_tags );
					}
					$caption = $multi_view->render_element(
						array(
							'tag'        => 'p',
							'content'    => $caption_text,
							'attrs'      => array(
								'class' => 'dipl_filterable_gallery_item_caption',
							),
						)
					);
				} else {
					$caption = '';
				}

				if ( '' !== $title || '' !== $caption ) {
					$title_and_caption = sprintf(
						'<div class="dipl_filterable_gallery_title_caption_wrapper">%1$s %2$s</div>',
						et_core_intentionally_unescaped( $title, 'html' ),
						et_core_intentionally_unescaped( $caption, 'html' )
					);
				} else {
					$title_and_caption = '';
				}

				$categories 	= get_the_terms( $image_id, 'attachment-category' );
				$image_classes	= array( 
					"attachment-$image_size",
					"size-$image_size",
				);

				if ( 'on' === $disable_lazy_loading ) {
					array_push( $image_classes, 'no-lazyload', 'skip-lazy' );
				}

				$image_atts = array();
				if ( $categories && ! is_wp_error( $categories ) && ! empty( $categories ) ) {
					if ( 'on' !== $show_pagination ) {
						foreach ( $categories as $category ) {
							$attachment_categories[$category->term_id] = sprintf(
								esc_html__( '%1$s', 'divi-plus' ),
								$category->name
							);
						}
					}

					$categories_ids = wp_list_pluck( $categories, 'term_id' );
					$categories 	= wp_list_pluck( $categories, 'slug' );
					$image_classes	= array_merge( 
						$image_classes,
						$categories
					);
					$image_atts['data-categories'] = implode( ',', $categories_ids );
				}

				$image_atts['class'] = implode( ' ', $image_classes );

				if ( 'lightbox' === $on_click_trigger ) {
					$gallery_items .= sprintf(
						'<div data-mfp-src="%4$s" class="dipl_filterable_gallery_item %5$s">
							<div class="dipl_filterable_gallery_image_wrapper">%1$s %2$s</div>
							%3$s
						</div>',
						et_core_intentionally_unescaped( wp_get_attachment_image( intval( $image_id ), sanitize_text_field( $image_size ), false, $image_atts ), 'html' ),
						isset( $overlay_output ) ? $overlay_output : '',
						et_core_intentionally_unescaped( $title_and_caption, 'html' ),
						esc_url( wp_get_attachment_url( intval( $image_id ) ) ),
						esc_attr( $gallery_item_class )
					);
				} else if ( 'link' === $on_click_trigger ) {
					$link = get_post_meta( intval( $image_id ), 'dge_attachment_link', true );
					if ( '' !== $link ) {
						$image = sprintf(
							'<a href="%1$s"%2$s>%3$s</a>',
							esc_url( $link ),
							'on' === $link_target ? ' target="_blank"' : '',
							et_core_intentionally_unescaped( wp_get_attachment_image( intval( $image_id ), sanitize_text_field( $image_size ), false, $image_atts ), 'html' )
						);
					} else {
						$image = et_core_intentionally_unescaped( wp_get_attachment_image( intval( $image_id ), sanitize_text_field( $image_size ), false, $image_atts ), 'html' );
					}

					$gallery_items .= sprintf(
						'<div class="dipl_filterable_gallery_item %4$s">
							<div class="dipl_filterable_gallery_image_wrapper">%1$s %2$s</div>
							%3$s
						</div>',
						$image,
						isset( $overlay_output ) ? $overlay_output : '',
						et_core_intentionally_unescaped( $title_and_caption, 'html' ),
						esc_attr( $gallery_item_class )
					);
				} else {
					$gallery_items .= sprintf(
						'<div class="dipl_filterable_gallery_item %4$s">
							<div class="dipl_filterable_gallery_image_wrapper">%1$s %2$s</div>
							%3$s
						</div>',
						et_core_intentionally_unescaped( wp_get_attachment_image( intval( $image_id ), sanitize_text_field( $image_size ), false, $image_atts ), 'html' ),
						isset( $overlay_output ) ? $overlay_output : '',
						et_core_intentionally_unescaped( $title_and_caption, 'html' ),
						esc_attr( $gallery_item_class )
					);
				}
			}

			$filter = '';

			$all_images_text = '' === $all_images_text ?
	            esc_html__( 'All', 'divi-plus' ) :
	            sprintf(
	                esc_html__( '%s', 'divi-plus' ),
	                esc_html( $all_images_text )
	            );

			if ( 'on' === $show_all_filter ) {
				$categories = sprintf(
					'<li class="dipl_filterable_gallery_active_category" data-category="">%1$s</li>',
					esc_html( $all_images_text )
				);
			} else {
				$categories = '';
			}

			$terms = get_terms( array(
	            'orderby' => 'term_order',
	            'taxonomy' => 'attachment-category',
	            'hide_empty' => true,
	        ) );

			if ( $terms && ! is_wp_error( $terms ) && ! empty( $terms ) ) {
				$all_categories = array();
				foreach ( $terms as $term ) {
					$all_categories[$term->term_id] = sprintf(
						esc_html__( '%1$s', 'divi-plus' ),
						$term->name
					);
	       		}

	       		if ( ! empty( $include_categories ) ) {
   					$attachment_categories = array_intersect( $all_categories, $include_categories );
   				} else {
   					$attachment_categories = $all_categories;
   				}
	       	}

	       	if ( ! empty ( $attachment_categories ) && 1 < count( $attachment_categories ) ) {
	       		foreach ( $attachment_categories as $term_id => $name ) {
					$categories .= sprintf(
						'<li data-category="%1$s">%2$s</li>',
						esc_attr( $term_id ),
						esc_html( $name )
					);
				}

				$data_props = array(
					'number_of_images',
					'offset_number',
					'gallery_order_by',
					'gallery_order',
					'include_categories',
					'image_size',
					'on_click_trigger',
					'link_target',
					'enable_overlay',
					'show_title',
					'show_caption',
					'disable_lazy_loading',
					'title_level',
				);

				$data_atts = $this->props_to_html_data_attrs( $data_props );

				$filter = sprintf(
					'<div class="dipl_filterable_gallery_filter_wrapper"%2$s data-overlay_icon="%3$s">
						<ul class="dipl_filterable_gallery_filter_categories">
						%1$s
						</ul>
					</div>',
					$categories,
					$data_atts,
					esc_attr( et_pb_process_font_icon( $overlay_icon ) )
				);
	       	}

			if ( 'on' === $show_pagination ) {
				wp_enqueue_script( 'elicus-twbs-pagination' );

				$data_props = array(
					'number_of_images',
					'offset_number',
					'gallery_order_by',
					'gallery_order',
					'include_categories',
					'image_size',
					'on_click_trigger',
					'link_target',
					'enable_overlay',
					'show_overlay_icon',
					'overlay_icon',
					'show_title',
					'show_caption',
					'disable_lazy_loading',
					'title_level',
				);

				if ( 'on' === $this->props['show_prev_next'] ) {
					array_push( $data_props, 'show_prev_next', 'prev_text', 'next_text' );
				}

				$data_atts = $this->props_to_html_data_attrs( $data_props );

				if ( -1 === $args['posts_per_page'] ) {
					$total_pages = 1;
				} else if ( '' !== $args['offset'] && ! empty( $args['offset'] ) ) {
					$total_pages = intval( ceil( ( $query->found_posts - $args['offset'] ) / $args['posts_per_page'] ) );
				} else {
					$total_pages = intval( ceil( $query->found_posts / $args['posts_per_page'] ) );
				}

				$pagination = sprintf(
					'<div class="dipl_filterable_gallery_pagination_wrapper" data-total_pages="%1$s" data-overlay_icon="%3$s" %2$s><ul></ul></div>',
					$total_pages,
					$data_atts,
					esc_attr( et_pb_process_font_icon( $overlay_icon ) )
				);
			}

			$data_props = array(
				'on_click_trigger',
				'lightbox_effect',
				'enable_navigation',
				'isotope_transition_duration',
			);

			if ( 'none' !== $this->props['lightbox_effect'] ) {
				array_push( $data_props, 'lightbox_transition_duration' );
			}

			$data_atts = $this->props_to_html_data_attrs( $data_props );

			$output = sprintf(
				'%1$s
				<div class="dipl_filterable_gallery_wrapper"%4$s>
					<div class="dipl_filterable_gallery_item_gutter"></div>
					%2$s
				</div>
				%3$s',
				$filter,
				$gallery_items,
				'on' === $show_pagination ? $pagination : '',
				$data_atts
			);

			if ( in_array( $on_click_trigger, array( 'lightbox', 'link' ), true ) ) {
				self::set_style( $render_slug, array(
	                'selector'    => '%%order_class%% .dipl_filterable_gallery_item',
	                'declaration' => 'cursor: pointer;',
	            ) );
			}

			$number_of_columns 	= et_pb_responsive_options()->get_property_values( $this->props, 'number_of_columns' );
			$column_spacing 	= et_pb_responsive_options()->get_property_values( $this->props, 'column_spacing' );
			
			$number_of_columns['tablet'] = '' !== $number_of_columns['tablet'] ? $number_of_columns['tablet'] : $number_of_columns['desktop'];
			$number_of_columns['phone']  = '' !== $number_of_columns['phone'] ? $number_of_columns['phone'] : $number_of_columns['tablet'];

			$column_spacing['tablet'] = '' !== $column_spacing['tablet'] ? $column_spacing['tablet'] : $column_spacing['desktop'];
			$column_spacing['phone']  = '' !== $column_spacing['phone'] ? $column_spacing['phone'] : $column_spacing['tablet'];
			
			$breakpoints 	= array( 'desktop', 'tablet', 'phone' );
			$width 			= array();

			foreach ( $breakpoints as $breakpoint ) {
				if ( 1 === absint( $number_of_columns[$breakpoint] ) ) {
					$width[$breakpoint] = '100%';
				} else {
					$divided_width 	= 100 / absint( $number_of_columns[$breakpoint] );
					if ( 0.0 !== floatval( $column_spacing[$breakpoint] ) ) {
						$gutter = floatval( ( floatval( $column_spacing[$breakpoint] ) * ( absint( $number_of_columns[$breakpoint] ) - 1 ) ) / absint( $number_of_columns[$breakpoint] ) );
						$width[$breakpoint] = 'calc(' . $divided_width . '% - ' . $gutter . 'px)';
					} else {
						$width[$breakpoint] = $divided_width . '%';
					}
				}
			}

			et_pb_responsive_options()->generate_responsive_css( $width, '%%order_class%% .dipl_filterable_gallery_item', 'width', $render_slug, '', 'range' );
			et_pb_responsive_options()->generate_responsive_css( $column_spacing, '%%order_class%% .dipl_filterable_gallery_item', array( 'margin-bottom' ), $render_slug, '', 'range' );
			et_pb_responsive_options()->generate_responsive_css( $column_spacing, '%%order_class%% .dipl_filterable_gallery_item_gutter', 'width', $render_slug, '', 'range' );

			$overlay_icon_size 	= et_pb_responsive_options()->get_property_values( $this->props, 'overlay_icon_size' );
			et_pb_responsive_options()->generate_responsive_css( $overlay_icon_size, '%%order_class%% .dipl_filterable_gallery_item .et_overlay:before', 'font-size', $render_slug, '', 'range' );


			if ( ! in_array( $title_area, array( 'gallery', 'overlay', 'both' ), true ) && ! in_array( $caption_area, array( 'gallery', 'overlay', 'both' ), true ) ) {
				self::set_style( $render_slug, array(
	                'selector'    => '%%order_class%% .dipl_filterable_gallery_title_caption_wrapper',
	                'declaration' => 'display: none;',
	            ) );
			} else {
				if ( ! in_array( $title_area, array( 'gallery', 'overlay', 'both' ), true ) ) {
					self::set_style( $render_slug, array(
		                'selector'    => '%%order_class%% .dipl_filterable_gallery_item_title',
		                'declaration' => 'display: none;',
		            ) );
				}
				if ( ! in_array( $caption_area, array( 'gallery', 'overlay', 'both' ), true ) ) {
					self::set_style( $render_slug, array(
		                'selector'    => '%%order_class%% .dipl_filterable_gallery_item_caption',
		                'declaration' => 'display: none;',
		            ) );
				}
			}

			if ( ! in_array( $title_area, array( 'lightbox', 'both' ), true ) && ! in_array( $caption_area, array( 'lightbox', 'both' ), true ) ) {
				self::set_style( $render_slug, array(
	                'selector'    => '%%order_class%%_lightbox .mfp-bottom-bar',
	                'declaration' => 'display: none;',
	            ) );
			} else {
				if ( ! in_array( $title_area, array( 'lightbox', 'both' ), true ) ) {
					self::set_style( $render_slug, array(
		                'selector'    => '%%order_class%%_lightbox .dipl_filterable_gallery_item_title',
		                'declaration' => 'display: none;',
		            ) );
		            self::set_style( $render_slug, array(
		                'selector'    => '%%order_class%%_lightbox .dipl_filterable_gallery_item_title + .dipl_filterable_gallery_item_caption',
		                'declaration' => 'padding: 10px;',
		            ) );
				}
				if ( ! in_array( $caption_area, array( 'lightbox', 'both' ), true ) ) {
					self::set_style( $render_slug, array(
		                'selector'    => '%%order_class%%_lightbox .dipl_filterable_gallery_item_caption',
		                'declaration' => 'display: none;',
		            ) );
				}
			}

			if ( 'below_image' === $lightbox_title_and_caption_style ) {
				self::set_style( $render_slug, array(
	                'selector'    => '%%order_class%%_lightbox .mfp-bottom-bar, %%order_class%%_lightbox.mfp-img-mobile .mfp-bottom-bar',
	                'declaration' => 'bottom: auto; top: 100%;',
	            ) );
			}

			if ( 'overlay' === $title_area || 'overlay' === $caption_area ) {
				if ( 'on' === $enable_overlay && '' === $overlay_icon ) {
	            	self::set_style( $render_slug, array(
		                'selector'    => '%%order_class%% .dipl_filterable_gallery_item .et_overlay:before',
		                'declaration' => 'content: "" !important;',
		            ) );
	            }
			}

			if ( 'overlay' === $title_area && 'overlay' === $caption_area ) {
				self::set_style( $render_slug, array(
	                'selector'    => '%%order_class%% .dipl_filterable_gallery_title_caption_wrapper',
	                'declaration' => 'position: absolute; top: 70%; left: 50%; z-index: 99; width: 100%; padding: 10px; visibility: hidden; opacity: 0; transform: translate(-50%, -50%); transition: all 300ms ease;',
	            ) );
	            self::set_style( $render_slug, array(
	                'selector'    => '%%order_class%% .dipl_filterable_gallery_item:hover .dipl_filterable_gallery_title_caption_wrapper',
	                'declaration' => 'top: 50%; visibility: visible; opacity: 1;',
	            ) );
			} else if ( 'overlay' === $title_area ) {
				self::set_style( $render_slug, array(
	                'selector'    => '%%order_class%% .dipl_filterable_gallery_item_title',
	                'declaration' => 'position: absolute; top: 70%; left: 50%; z-index: 99; width: 100%; padding: 10px; visibility: hidden; opacity: 0; transform: translate(-50%, -50%); transition: all 300ms ease;',
	            ) );
	            self::set_style( $render_slug, array(
	                'selector'    => '%%order_class%% .dipl_filterable_gallery_item:hover .dipl_filterable_gallery_item_title',
	                'declaration' => 'top: 50%; visibility: visible; opacity: 1;',
	            ) );
			} else if ( 'overlay' === $caption_area ) {
				self::set_style( $render_slug, array(
	                'selector'    => '%%order_class%% .dipl_filterable_gallery_item_caption',
	                'declaration' => 'position: absolute; top: 70%; left: 50%; z-index: 99; width: 100%; padding: 10px; visibility: hidden; opacity: 0; transform: translate(-50%, -50%); transition: all 300ms ease;',
	            ) );
	            self::set_style( $render_slug, array(
	                'selector'    => '%%order_class%% .dipl_filterable_gallery_item:hover .dipl_filterable_gallery_item_caption',
	                'declaration' => 'top: 50%; visibility: visible; opacity: 1;',
	            ) );
			}

			if ( '' !== $overlay_icon_color ) {
				self::set_style( $render_slug, array(
	                'selector'    => '%%order_class%% .dipl_filterable_gallery_item .et_overlay:before',
	                'declaration' => sprintf(
	                    'color: %1$s;',
	                    esc_attr( $overlay_icon_color )
	                )
	            ) );
			}

			if ( '' !== $overlay_color ) {
				self::set_style( $render_slug, array(
	                'selector'    => '%%order_class%% .dipl_filterable_gallery_item .et_overlay',
	                'declaration' => sprintf(
	                    'background-color: %1$s;',
	                    esc_attr( $overlay_color )
	                )
	            ) );
			}

			if ( 'lightbox' === $on_click_trigger ) {
				if ( '' !== $meta_background_color_lightbox ) {
					self::set_style( $render_slug, array(
		                'selector'    => '%%order_class%%_lightbox .dipl_filterable_gallery_item_title, %%order_class%%_lightbox .dipl_filterable_gallery_item_caption',
		                'declaration' => sprintf(
		                    'background-color: %1$s;',
		                    esc_attr( $meta_background_color_lightbox )
		                )
		            ) );
				}
				if ( '' !== $lightbox_background_color ) {
					self::set_style( $render_slug, array(
		                'selector'    => '%%order_class%%_lightbox.mfp-bg',
		                'declaration' => sprintf(
		                    'background-color: %1$s;',
		                    esc_attr( $lightbox_background_color )
		                )
		            ) );
				}

				if ( '' !== $lightbox_close_icon_color ) {
					self::set_style( $render_slug, array(
		                'selector'    => '%%order_class%%_lightbox .mfp-close',
		                'declaration' => sprintf(
		                    'color: %1$s;',
		                    esc_attr( $lightbox_close_icon_color )
		                )
		            ) );
				}

				if ( '' !== $lightbox_arrows_color ) {
					self::set_style( $render_slug, array(
		                'selector'    => '%%order_class%%_lightbox .mfp-arrow:after',
		                'declaration' => sprintf(
		                    'color: %1$s;',
		                    esc_attr( $lightbox_arrows_color )
		                )
		            ) );
				}

				if ( 'none' !== $lightbox_effect ) {
					self::set_style( $render_slug, array(
		                'selector'    => '%%order_class%%_lightbox .mfp-container, %%order_class%%_lightbox.mfp-bg, %%order_class%%_lightbox.mfp-wrap .mfp-content',
		                'declaration' => sprintf(
		                    'transition-duration: %1$sms;',
		                    absint( $lightbox_transition_duration )
		                )
		            ) );
				}
			}

			if ( 'on' === $show_pagination ) {
				$this->generate_styles(
					array(
						'base_attr_name' => 'pagination_link_background_color',
						'selector'       => '%%order_class%% .dipl_filterable_gallery_pagination li a',
						'hover_selector' => '%%order_class%% .dipl_filterable_gallery_pagination li a:hover',
						'css_property'   => 'background-color',
						'render_slug'    => $render_slug,
						'type'           => 'color',
					)
				);

				$this->generate_styles(
					array(
						'base_attr_name' => 'pagination_link_color',
						'selector'       => '%%order_class%% .dipl_filterable_gallery_pagination li a',
						'hover_selector' => '%%order_class%% .dipl_filterable_gallery_pagination li a:hover',
						'css_property'   => 'color',
						'render_slug'    => $render_slug,
						'type'           => 'color',
					)
				);

				$this->generate_styles(
					array(
						'base_attr_name' => 'active_pagination_link_background_color',
						'selector'       => '%%order_class%% .dipl_filterable_gallery_pagination li.active a',
						'hover_selector' => '%%order_class%% .dipl_filterable_gallery_pagination li.active a:hover',
						'css_property'   => 'background-color',
						'render_slug'    => $render_slug,
						'type'           => 'color',
					)
				);

				$this->generate_styles(
					array(
						'base_attr_name' => 'active_pagination_link_color',
						'selector'       => '%%order_class%% .dipl_filterable_gallery_pagination li.active a',
						'hover_selector' => '%%order_class%% .dipl_filterable_gallery_pagination li.active a:hover',
						'css_property'   => 'color',
						'render_slug'    => $render_slug,
						'type'           => 'color',
					)
				);
			}

	        $args = array(
				'render_slug'	=> $render_slug,
				'props'			=> $this->props,
				'fields'		=> $this->fields_unprocessed,
				'module'		=> $this,
				'backgrounds' 	=> array(
					'category_bg' => array(
						'normal' => "{$this->main_css_element} .dipl_filterable_gallery_filter_categories li",
						'hover' => "{$this->main_css_element} .dipl_filterable_gallery_filter_categories li:hover:not(.dipl_filterable_gallery_active_category):not(.dipl_filterable_gallery_disabled)",
		 			),
		 			'active_category_bg' => array(
						'normal' => "{$this->main_css_element} .dipl_filterable_gallery_filter_categories li.dipl_filterable_gallery_active_category",
						'hover' => "{$this->main_css_element} .dipl_filterable_gallery_filter_categories li.dipl_filterable_gallery_active_category:hover:not(.dipl_filterable_gallery_disabled)",
		 			),
				),
			);


			wp_enqueue_script( 'dipl-filterable-gallery-custom', PLUGIN_PATH . 'includes/modules/FilterableGallery/dipl-filterable-gallery-custom.min.js', array('jquery'), '1.0.1', true );

			$file = et_is_builder_plugin_active() ? 'style-dbp' : 'style';
        	wp_enqueue_style( 'dipl-filterable-gallery-style', PLUGIN_PATH . 'includes/modules/FilterableGallery/' . $file . '.min.css', array(), '1.0.0' );

			DiviPlusHelper::process_background( $args );

		} else {
			$output = sprintf(
				'<div className="entry"><h4>%1$s</h4></div>',
				esc_html( $no_gallery_text )
			);
		}

		return et_core_intentionally_unescaped( $output, 'html' );

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
	if ( in_array( 'dipl_filterable_gallery', $modules ) ) {
		new DIPL_FilterableGallery();
	}
} else {
	new DIPL_FilterableGallery();
}