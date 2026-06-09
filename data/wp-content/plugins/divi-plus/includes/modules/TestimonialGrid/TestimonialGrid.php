<?php
/**
 * @author      Elicus <hello@elicus.com>
 * @link        https://www.elicus.com/
 * @copyright   2023 Elicus Technologies Private Limited
 * @version     1.9.15
 */
class DIPL_TestimonialGrid extends ET_Builder_Module {
	public $slug       = 'dipl_testimonial_grid';
	public $vb_support = 'on';

	protected $module_credits = array(
		'module_uri' => 'https://diviextended.com/product/divi-plus/',
		'author'     => 'Elicus',
		'author_uri' => 'https://elicus.com/',
	);

	/**
	 * Track if the module is currently rendering to prevent unnecessary rendering and recursion.
	 *
	 * @var bool
	 */
	protected static $rendering = false;

	public function init() {
		$this->name             = esc_html__( 'DP Testimonial Grid', 'divi-plus' );
		$this->main_css_element = '%%order_class%%';
	}

	public function get_settings_modal_toggles() {
		return array(
			'general'  => array(
				'toggles' => array(
					'main_content'    => array(
						'title'    => esc_html__( 'Content', 'divi-plus' ),
						'priority' => 1,
					),
					'display_setting' => array(
						'title'    => esc_html__( 'Display', 'divi-plus' ),
						'priority' => 3,
					),
					'pagination' => array(
						'title' => esc_html__( 'Pagination', 'divi-plus' ),
					),
				),
			),
			'advanced' => array(
				'toggles' => array(
					'text'  => array(
						'title' => esc_html__( 'Text', 'divi-plus' ),
					),
					'body_text' => array(
						'title' => esc_html__( 'Body', 'divi-plus' ),
					),
					'author_text' => array(
						'title'             => esc_html__( 'Author', 'divi-plus' ),
						'sub_toggles'       => array(
							'author_name' => array(
								'name' => 'Name',
							),
							'author_designation' => array(
								'name' => 'Designation',
							),
							'author_company' => array(
								'name' => 'Company',
							),
						),
						'tabbed_subtoggles' => true,
					),
					'rating'      => array(
						'title'    => esc_html__( 'Star Rating', 'divi-plus' ),
					),
					'quote_icon'  => array(
						'title'             => esc_html__( 'Quote Icon', 'divi-plus' ),
						'tabbed_subtoggles' => true,
						'sub_toggles'       => array(
							'quote_icon_opening' => array(
								'name' => 'Opening Quote Icon',
							),
							'quote_icon_closing' => array(
								'name' => 'Closing Quote Icon',
							),
						),
					),
					'meta_settings' => array(
						'title' => esc_html__( 'Meta', 'divi-plus' ),
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
				'body' => array(
					'label' => esc_html__( 'Body', 'divi-plus' ),
					'font_size' => array(
						'default' => '18px',
						'range_settings' => array(
							'min'  => '10',
							'max'  => '100',
							'step' => '1',
						),
					),
					'text_orientation' => array(
						'default' => 'center',
					),
					'line_height' => array(
						'default' => '1.7',
						'range_settings' => array(
							'min'  => '0.1',
							'max'  => '10',
							'step' => '0.1',
						),
					),
					'letter_spacing' => array(
						'range_settings' => array(
							'min'  => '0.1',
							'max'  => '10',
							'step' => '0.1',
						),
					),
					'hide_text_align'  => true,
					'css' => array(
						'main' => "{$this->main_css_element} .dipl_testimonial_desc, {$this->main_css_element} .dipl_testimonial_desc p",
						'important' => 'all',
					),
					'tab_slug' => 'advanced',
					'toggle_slug' => 'body_text',
				),
				'author' => array(
					'label' => esc_html__( 'Author', 'divi-plus' ),
					'font_size' => array(
						'default'        => '16px',
						'range_settings' => array(
							'min'  => '8',
							'max'  => '100',
							'step' => '1',
						),
					),
					'line_height' => array(
						'default' => '1.7',
						'range_settings' => array(
							'min'  => '0.1',
							'max'  => '10',
							'step' => '0.1',
						),
					),
					'letter_spacing' => array(
						'range_settings' => array(
							'min'  => '0.1',
							'max'  => '10',
							'step' => '0.1',
						),
					),
					'hide_text_align' => true,
					'css'             => array(
						'main'        => "{$this->main_css_element} .dipl_testimonial_author_name",
						'important'   => 'all',
					),
					'toggle_slug'     => 'author_text',
					'sub_toggle'      => 'author_name',
				),
				'designation' => array(
					'label'           => esc_html__( 'Designation', 'divi-plus' ),
					'font_size'       => array(
						'default'        => '14px',
						'range_settings' => array(
							'min'  => '8',
							'max'  => '100',
							'step' => '1',
						),
					),
					'line_height'     => array(
						'default'        => '1.7',
						'range_settings' => array(
							'min'  => '0.1',
							'max'  => '10',
							'step' => '0.1',
						),
					),
					'letter_spacing'  => array(
						'default'        => '0',
						'range_settings' => array(
							'min'  => '0.1',
							'max'  => '10',
							'step' => '0.1',
						),
					),
					'hide_text_align' => true,
					'css'             => array(
						'main'        => "{$this->main_css_element} .dipl_testimonial_author_designation",
						'important' => 'all',
					),
					'toggle_slug'     => 'author_text',
					'sub_toggle'      => 'author_designation',
				),
				'company' => array(
					'label'           => esc_html__( 'Company', 'divi-plus' ),
					'font_size'       => array(
						'default'        => '14px',
						'range_settings' => array(
							'min'  => '8',
							'max'  => '100',
							'step' => '1',
						),
					),
					'line_height'     => array(
						'default'        => '1.7',
						'range_settings' => array(
							'min'  => '0.1',
							'max'  => '10',
							'step' => '0.1',
						),
					),
					'letter_spacing'  => array(
						'default'        => '0',
						'range_settings' => array(
							'min'  => '0.1',
							'max'  => '10',
							'step' => '0.1',
						),
					),
					'hide_text_align' => true,
					'css'             => array(
						'main'        => "{$this->main_css_element} .dipl_testimonial_author_company, {$this->main_css_element} .dipl_testimonial_author_company a",
						'important' => 'all',
					),
					'toggle_slug'     => 'author_text',
					'sub_toggle'      => 'author_company',
				),
			),
			'borders' => array(
				'testimonial_card' => array(
					'label_prefix'    => esc_html__( 'Single Testimonial', 'divi-plus' ),
					'css' => array(
						'main' => array(
							'border_radii'  => '%%order_class%% .dipl_single_testimonial_card',
							'border_styles' => '%%order_class%% .dipl_single_testimonial_card',
							'important' => 'all',
						),
					),
				),
				'author_image' => array(
					'label_prefix'    => esc_html__( 'Author Image', 'divi-plus' ),
					'css'             => array(
						'main' => array(
							'border_radii'  => '%%order_class%% .dipl_testimonial_author_image img',
							'border_styles' => '%%order_class%% .dipl_testimonial_author_image img',
							'important' => 'all',
						),
					),
					'depends_show_if' => 'on',
					'depends_on'	  => array( 'show_author_image' ),
				),
				'default' => array(
					'css' => array(
						'main' => array(
							'border_radii'  => $this->main_css_element,
							'border_styles' => $this->main_css_element,
						),
					),
				),
			),
			'box_shadow' => array(
				'testimonial_card' => array(
					'label'       => esc_html__( 'Single Testimonial Box Shadow', 'divi-plus' ),
					'css'         => array(
						'main' => "%%order_class%% .dipl_single_testimonial_card",
						'important' => 'all',
					),
					'tab_slug'    => 'advanced',
					'toggle_slug' => 'box_shadow',
				),
				'default' => array(
					'css' => array(
						'main' => $this->main_css_element,
						'important' => 'all',
					),
				),
			),
			'testimonial_margin_padding' => array(
				'testimonial_card' => array(
					'margin_padding' => array(
						'css' => array(
							'use_margin' => false,
							'padding'   => "{$this->main_css_element} .dipl_single_testimonial_card",
							'important' => 'all',
						),
					),
				),
			),
			'background' => array(
				'css' => array(
					'main' => $this->main_css_element,
				),
			),
			'margin_padding' => array(
				'css' => array(
					'main'      => $this->main_css_element,
					'important' => 'all',
				),
			),
			'text' => array(
				'css' => array(
					'main' => '%%order_class%%',
				),
				'options'               => array(
					'text_orientation'  => array(
						'default' => 'center',
						'default_on_front' => 'center',
					),
				),
			),
			'filters'        => false,
			'link_options'   => false,
		);

	}

	public function get_fields() {
		return array_merge(
			array(
				'testimonial_layout' => array(
					'label'            => esc_html__( 'Testimonials Layout', 'divi-plus' ),
					'type'             => 'select',
					'option_category'  => 'layout',
					'options'          => array(
						'layout1' => esc_html__( 'Layout 1', 'divi-plus' ),
						'layout2' => esc_html__( 'Layout 2', 'divi-plus' ),
					),
					'default'          => 'layout1',
					'tab_slug'         => 'general',
					'toggle_slug'      => 'main_content',
					'description'      => esc_html__( 'Here you can select the layout for the testimonials.', 'divi-plus' ),
					'computed_affects' => array(
						'__testimonial_data',
					),
				),
				'testimonial_number' => array(
					'label'            => esc_html__( 'Number of Testimonials', 'divi-plus' ),
					'type'             => 'text',
					'option_category'  => 'configuration',
					'default'          => 10,
					'tab_slug'         => 'general',
					'toggle_slug'      => 'main_content',
					'description'      => esc_html__( 'Here you can specify the total number of testimonials to display.', 'divi-plus' ),
					'computed_affects' => array(
						'__testimonial_data',
					),
				),
				'offset_number' => array(
	                'label'            => esc_html__( 'Offset Number', 'divi-plus' ),
	                'type'             => 'text',
	                'option_category'  => 'configuration',
	                'default'          => 0,
	                'tab_slug'         => 'general',
	                'toggle_slug'      => 'main_content',
	                'description'      => esc_html__( 'Choose how many testimonials you would like to skip. These testimonials will not be shown.', 'divi-plus' ),
	                'computed_affects' => array(
	                    '__testimonial_data',
	                ),
	            ),
				'testimonial_order' => array(
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
					'description'      => esc_html__( 'Here you can specify the sorting order for the testimonials.', 'divi-plus' ),
					'computed_affects' => array(
						'__testimonial_data',
					),
				),
				'testimonial_order_by' => array(
					'label'            => esc_html__( 'Order by', 'divi-plus' ),
					'type'             => 'select',
					'option_category'  => 'configuration',
					'options'          => array(
						'date'     	=> esc_html__( 'Date', 'divi-plus' ),
						'modified'	=> esc_html__( 'Modified Date', 'divi-plus' ),
						'title'    	=> esc_html__( 'Title', 'divi-plus' ),
						'name'     	=> esc_html__( 'Slug', 'divi-plus' ),
						'ID'       	=> esc_html__( 'ID', 'divi-plus' ),
						'rand'     	=> esc_html__( 'Random', 'divi-plus' ),
						'none'     	=> esc_html__( 'None', 'divi-plus' ),
					),
					'default'          => 'date',
					'tab_slug'         => 'general',
					'toggle_slug'      => 'main_content',
					'description'      => esc_html__( 'Here you can specify the order in which the testimonials will be displayed.', 'divi-plus' ),
					'computed_affects' => array(
						'__testimonial_data',
					),
				),
				'include_categories' => array(
					'label'            => esc_html__( 'Include Categories', 'divi-plus' ),
					'type'             => 'categories',
					'renderer_options' => array(
						'use_terms' => true,
						'term_name' => 'dipl-testimonial-category',
						'field_name' => 'et_pb_include_dipl_testimonial_category',
					),
					'tab_slug'         => 'general',
					'toggle_slug'      => 'main_content',
					'description'      => esc_html__( 'Select Categories. If no category is selected, testimonials from all categories will be displayed.', 'divi-plus' ),
					'computed_affects' => array(
						'__testimonial_data',
					),
				),
				'no_result_text' => array(
					'label'            => esc_html__( 'No Result Text', 'divi-plus' ),
					'type'             => 'text',
					'option_category'  => 'configuration',
					'default'		   => esc_html__( 'The testimonials you requested could not be found. Try changing your module settings or create some new testimonials.', 'divi-plus' ),
					'tab_slug'         => 'general',
					'toggle_slug'      => 'main_content',
					'description'      => esc_html__( 'Here you can define custom no result text.', 'divi-plus' ),
				),
				'number_of_columns' => array(
	                'label'             => esc_html__( 'Number Of Columns', 'divi-plus' ),
	                'type'              => 'select',
	                'option_category'   => 'configuration',
	                'options'           => array(
	                    '1'         => esc_html( '1' ),
	                    '2'         => esc_html( '2' ),
	                    '3'         => esc_html( '3' ),
	                ),
	                'default'           => '2',
	                'tab_slug'          => 'general',
	                'toggle_slug'       => 'display_setting',
	                'description'       => esc_html__( 'Here you can select the number of columns to display testimonials.', 'divi-plus' ),
	                'computed_affects' => array(
						'__testimonial_data',
					),
	            ),
	            'use_masonry' => array(
					'label'            => esc_html__( 'Enable Masonry', 'divi-plus' ),
					'type'             => 'yes_no_button',
					'option_category'  => 'configuration',
					'options'          => array(
						'on'  => esc_html__( 'Yes', 'divi-plus' ),
						'off' => esc_html__( 'No', 'divi-plus' ),
					),
					'default'          => 'off',
					'tab_slug'         => 'general',
					'toggle_slug'      => 'display_setting',
					'description'      => esc_html__( 'Enable Masonry for testimonials.', 'divi-plus' ),
					'computed_affects' => array(
						'__testimonial_data',
					),
				),
				'show_rating' => array(
					'label'           => esc_html__( 'Show Rating', 'divi-plus' ),
					'type'            => 'yes_no_button',
					'option_category' => 'configuration',
					'options'         => array(
						'on'  => esc_html__( 'Yes', 'divi-plus' ),
						'off' => esc_html__( 'No', 'divi-plus' ),
					),
					'default'         => 'on',
					'tab_slug'        => 'general',
					'toggle_slug'     => 'display_setting',
					'description'     => esc_html__( 'Choose whether or not the rating should be visible.', 'divi-plus' ),
					'computed_affects' => array(
						'__testimonial_data',
					),
				),
				'show_author_image' => array(
					'label'           => esc_html__( 'Show Author Image', 'divi-plus' ),
					'type'            => 'yes_no_button',
					'option_category' => 'configuration',
					'options'         => array(
						'on'  => esc_html__( 'Yes', 'divi-plus' ),
						'off' => esc_html__( 'No', 'divi-plus' ),
					),
					'default'         => 'on',
					'tab_slug'        => 'general',
					'toggle_slug'     => 'display_setting',
					'description'     => esc_html__( 'Choose whether or not the author image should be visible.', 'divi-plus' ),
					'computed_affects' => array(
						'__testimonial_data',
					),
				),
				'use_gravatar' => array(
					'label'            => esc_html__( 'Use Gravatar', 'divi-plus' ),
					'type'             => 'yes_no_button',
					'option_category'  => 'configuration',
					'options'          => array(
						'on'  => esc_html__( 'Yes', 'divi-plus' ),
						'off' => esc_html__( 'No', 'divi-plus' ),
					),
					'default'          => 'off',
					'show_if'          => array(
						'show_author_image' => 'on',
					),
					'tab_slug'         => 'general',
					'toggle_slug'      => 'display_setting',
					'description'      => esc_html__( 'Use Gravatar if author image is not uploaded.', 'divi-plus' ),
					'computed_affects' => array(
						'__testimonial_data',
					),
				),
				'author_image_size' => array(
					'label'           	=> esc_html__( 'Author Image Size', 'divi-plus' ),
					'type'             	=> 'select',
					'option_category'  	=> 'configuration',
					'options'          	=> array(
						'thumbnail'	=> esc_html__( 'Thumbnail', 'divi-plus' ),
						'medium'	=> esc_html__( 'Medium', 'divi-plus' ),
						'large'		=> esc_html__( 'Large', 'divi-plus' ),
						'full'		=> esc_html__( 'Full', 'divi-plus' ),
					),
					'default'          	=> 'thumbnail',
					'show_if'          	=> array(
						'show_author_image' => 'on',
						'use_gravatar' => 'off',
					),
					'tab_slug'        	=> 'general',
					'toggle_slug'     	=> 'display_setting',
					'description'     	=> esc_html__( 'Choose the size of author image.', 'divi-plus' ),
					'computed_affects' => array(
						'__testimonial_data',
					),
				),
				'show_author_designation' => array(
					'label'           => esc_html__( 'Show Designation', 'divi-plus' ),
					'type'            => 'yes_no_button',
					'option_category' => 'configuration',
					'options'         => array(
						'on'  => esc_html__( 'Yes', 'divi-plus' ),
						'off' => esc_html__( 'No', 'divi-plus' ),
					),
					'default'         => 'on',
					'tab_slug'        => 'general',
					'toggle_slug'     => 'display_setting',
					'description'     => esc_html__( 'Choose whether or not the designation should be visible.', 'divi-plus' ),
					'computed_affects' => array(
						'__testimonial_data',
					),
				),
				'show_author_company_name' => array(
					'label'           => esc_html__( 'Show Company Name', 'divi-plus' ),
					'type'            => 'yes_no_button',
					'option_category' => 'configuration',
					'options'         => array(
						'on'  => esc_html__( 'Yes', 'divi-plus' ),
						'off' => esc_html__( 'No', 'divi-plus' ),
					),
					'default'         => 'on',
					'tab_slug'        => 'general',
					'toggle_slug'     => 'display_setting',
					'description'     => esc_html__( 'Choose whether or not the company name should be visible.', 'divi-plus' ),
					'computed_affects' => array(
						'__testimonial_data',
					),
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
						'__testimonial_data',
					),
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
					'computed_affects' => array(
						'__testimonial_data',
					),
				),
				'next_text' => array(
					'label'            => esc_html__( 'Next Link Text', 'divi-plus' ),
					'type'             => 'text',
					'option_category'  => 'configuration',
					'default'		   => esc_html__( 'Next', 'divi-plus' ),
					'show_if'      => array(
						'show_pagination' => 'on',
						'show_prev_next'  => 'on',
					),
					'tab_slug'         => 'general',
					'toggle_slug'      => 'pagination',
					'description'      => esc_html__( 'Here you can define Next Link text in numbered pagination.', 'divi-plus' ),
					'computed_affects' => array(
						'__testimonial_data',
					),
				),
				'prev_text' => array(
					'label'            => esc_html__( 'Prev Link Text', 'divi-plus' ),
					'type'             => 'text',
					'option_category'  => 'configuration',
					'default'		   => esc_html__( 'Prev', 'divi-plus' ),
					'show_if'      => array(
						'show_pagination' => 'on',
						'show_prev_next'  => 'on',
					),
					'tab_slug'         => 'general',
					'toggle_slug'      => 'pagination',
					'description'      => esc_html__( 'Here you can define Previous Link text in numbered pagination.', 'divi-plus' ),
					'computed_affects' => array(
						'__testimonial_data',
					),
				),
				'testimonial_card_bg_color' => array(
	                'label'                 => esc_html__( 'Single Testimonial Background', 'divi-plus' ),
	                'type'                  => 'background-field',
	                'base_name'             => 'testimonial_card_bg',
	                'context'               => 'testimonial_card_bg_color',
	                'option_category'       => 'button',
	                'custom_color'          => true,
	                'background_fields'     => $this->generate_background_options( 'testimonial_card_bg', 'button', 'general', 'background', 'testimonial_card_bg_color' ),
	                'hover'                 => 'tabs',
	                'tab_slug'              => 'general',
	                'toggle_slug'           => 'background',
	                'description'           => esc_html__( 'Customize the background style of the testimonial card by adjusting the background color, gradient, and image.', 'divi-plus' ),
	            ),
				'meta_separator_color' => array(
					'label'        => esc_html__( 'Meta Separator Color', 'divi-plus' ),
					'type'         => 'color-alpha',
					'custom_color' => true,
					'show_if'      => array(
						'testimonial_layout' => 'layout1',
					),
					'default'      => '#dddddd',
					'tab_slug'     => 'advanced',
					'toggle_slug'  => 'meta_settings',
					'description'  => esc_html__( 'Here you can specify color for the meta separator.', 'divi-plus' ),
				),
				'show_opening_quote_icon' => array(
					'label'           => esc_html__( 'Show Opening Quote Icon', 'divi-plus' ),
					'type'            => 'yes_no_button',
					'option_category' => 'configuration',
					'options'         => array(
						'on'  => esc_html__( 'Yes', 'divi-plus' ),
						'off' => esc_html__( 'No', 'divi-plus' ),
					),
					'default'         => 'on',
					'tab_slug'        => 'advanced',
					'toggle_slug'     => 'quote_icon',
					'sub_toggle'      => 'quote_icon_opening',
					'description'     => esc_html__( 'Choose whether or not the opening quote icon should be visible.', 'divi-plus' ),
					'computed_affects' => array(
						'__testimonial_data',
					),
				),
				'opening_quote_icon_size' => array(
					'label'           => esc_html__( 'Quote Icon Size', 'divi-plus' ),
					'type'            => 'range',
					'option_category' => 'font_option',
					'range_settings'  => array(
						'min'  => '1',
						'max'  => '350',
						'step' => '1',
					),
					'mobile_options'  => true,
					'show_if'         => array(
						'show_opening_quote_icon' => 'on',
					),
					'default'         => '56px',
					'tab_slug'        => 'advanced',
					'toggle_slug'     => 'quote_icon',
					'sub_toggle'      => 'quote_icon_opening',
					'description'     => esc_html__( 'Here you can choose size of the quote icon.', 'divi-plus' ),
				),
				'opening_quote_icon_color' => array(
					'label'        => esc_html__( 'Quote Icon Color', 'divi-plus' ),
					'type'         => 'color-alpha',
					'custom_color' => true,
					'show_if'      => array(
						'show_opening_quote_icon' => 'on',
					),
					'default'      => 'rgba(0,0,0,0.2)',
					'tab_slug'     => 'advanced',
					'toggle_slug'  => 'quote_icon',
					'sub_toggle'   => 'quote_icon_opening',
					'description'  => esc_html__( 'Here you can define color for the quote icon', 'divi-plus' ),
				),
				'custom_position_opening_quote_icon' => array(
					'label'           => esc_html__( 'Enable Custom Position For Quote Icon', 'divi-plus' ),
					'type'            => 'yes_no_button',
					'option_category' => 'configuration',
					'options'         => array(
						'on'  => esc_html__( 'Yes', 'divi-plus' ),
						'off' => esc_html__( 'No', 'divi-plus' ),
					),
					'show_if'         => array(
						'show_opening_quote_icon' => 'on',
					),
					'default'         => 'off',
					'tab_slug'        => 'advanced',
					'toggle_slug'     => 'quote_icon',
					'sub_toggle'      => 'quote_icon_opening',
					'description'     => esc_html__( 'Choose whether to use the custom position of quote icon.', 'divi-plus' ),
				),
				'opening_quote_icon_position_top' => array(
					'label'           => esc_html__( 'Quote Icon Position From Top', 'divi-plus' ),
					'type'            => 'range',
					'option_category' => 'font_option',
					'range_settings'  => array(
						'min'  => '0',
						'max'  => '100',
						'step' => '1',
					),
					'allowed_units'   => array( '%', 'px', ),
					'mobile_options'  => true,
					'show_if'         => array(
						'show_opening_quote_icon'            => 'on',
						'custom_position_opening_quote_icon' => 'on',
					),
					'default'         => '0',
					'default_unit'	  => '%',
					'tab_slug'        => 'advanced',
					'toggle_slug'     => 'quote_icon',
					'sub_toggle'      => 'quote_icon_opening',
					'description'     => esc_html__( 'Here you can choose the quote icon position from top.', 'divi-plus' ),
				),
				'opening_quote_icon_position' => array(
					'label'           => esc_html__( 'Quote Icon Position', 'divi-plus' ),
					'type'            => 'select',
					'option_category' => 'layout',
					'options'         => array(
						'left'   => esc_html__( 'Left', 'divi-plus' ),
						'center' => esc_html__( 'Center', 'divi-plus' ),
						'right'  => esc_html__( 'Right', 'divi-plus' ),
					),
					'show_if'         => array(
						'show_opening_quote_icon'            => 'on',
						'custom_position_opening_quote_icon' => 'on',
					),
					'default'         => 'left',
					'tab_slug'        => 'advanced',
					'toggle_slug'     => 'quote_icon',
					'description'     => esc_html__( 'opening quote icon position', 'divi-plus' ),
					'sub_toggle'      => 'quote_icon_opening',
				),
				'show_closing_quote_icon' => array(
					'label'           => esc_html__( 'Show Closing Quote Icon', 'divi-plus' ),
					'type'            => 'yes_no_button',
					'option_category' => 'configuration',
					'options'         => array(
						'on'  => esc_html__( 'Yes', 'divi-plus' ),
						'off' => esc_html__( 'No', 'divi-plus' ),
					),
					'default'         => 'off',
					'tab_slug'        => 'advanced',
					'toggle_slug'     => 'quote_icon',
					'sub_toggle'      => 'quote_icon_closing',
					'description'     => esc_html__( 'Choose whether or not the quote icon should be visible.', 'divi-plus' ),
					'computed_affects' => array(
						'__testimonial_data',
					),
				),
				'closing_quote_icon_size' => array(
					'label'           => esc_html__( 'Quote Icon Size', 'divi-plus' ),
					'type'            => 'range',
					'option_category' => 'font_option',
					'range_settings'  => array(
						'min'  => '1',
						'max'  => '350',
						'step' => '1',
					),
					'mobile_options'  => true,
					'show_if'         => array(
						'show_closing_quote_icon' => 'on',
					),
					'default'         => '56px',
					'tab_slug'        => 'advanced',
					'toggle_slug'     => 'quote_icon',
					'sub_toggle'      => 'quote_icon_closing',
					'description'     => esc_html__( 'Here you can choose size of the quote icon.', 'divi-plus' ),
				),
				'closing_quote_icon_color' => array(
					'label'        => esc_html__( 'Quote Icon Color', 'divi-plus' ),
					'type'         => 'color-alpha',
					'custom_color' => true,
					'show_if'      => array(
						'show_closing_quote_icon' => 'on',
					),
					'default'      => 'rgba(0,0,0,0.2)',
					'tab_slug'     => 'advanced',
					'toggle_slug'  => 'quote_icon',
					'sub_toggle'   => 'quote_icon_closing',
					'description'  => esc_html__( 'Here you can define color for the quote icon', 'divi-plus' ),
				),
				'custom_position_closing_quote_icon' => array(
					'label'           => esc_html__( 'Enable Custom Position For Quote Icon', 'divi-plus' ),
					'type'            => 'yes_no_button',
					'option_category' => 'configuration',
					'options'         => array(
						'on'  => esc_html__( 'Yes', 'divi-plus' ),
						'off' => esc_html__( 'No', 'divi-plus' ),
					),
					'show_if'         => array(
						'show_closing_quote_icon' => 'on',
					),
					'default'         => 'off',
					'tab_slug'        => 'advanced',
					'toggle_slug'     => 'quote_icon',
					'sub_toggle'      => 'quote_icon_closing',
					'description'     => esc_html__( 'Choose whether or not the quote icon should be visible.', 'divi-plus' ),
				),
				'closing_quote_icon_position_bottom' => array(
					'label'           => esc_html__( 'Quote Icon Position From Bottom', 'divi-plus' ),
					'type'            => 'range',
					'option_category' => 'font_option',
					'range_settings'  => array(
						'min'  => '0',
						'max'  => '100',
						'step' => '1',
					),
					'allowed_units'   => array( '%', 'px', ),
					'mobile_options'  => true,
					'show_if'         => array(
						'show_closing_quote_icon'            => 'on',
						'custom_position_closing_quote_icon' => 'on',
					),
					'default'         => '0',
					'default_unit'    => '%',
					'tab_slug'        => 'advanced',
					'toggle_slug'     => 'quote_icon',
					'sub_toggle'      => 'quote_icon_closing',
					'description'     => esc_html__( 'Here you can choose the quote icon size from top.', 'divi-plus' ),
				),
				'closing_quote_icon_position' => array(
					'label'           => esc_html__( 'Quote Icon Position', 'divi-plus' ),
					'type'            => 'select',
					'option_category' => 'layout',
					'options'         => array(
						'left'   => esc_html__( 'Left', 'divi-plus' ),
						'center' => esc_html__( 'Center', 'divi-plus' ),
						'right'  => esc_html__( 'Right', 'divi-plus' ),
					),
					'show_if'         => array(
						'show_closing_quote_icon'            => 'on',
						'custom_position_closing_quote_icon' => 'on',
					),
					'default'         => 'right',
					'tab_slug'        => 'advanced',
					'toggle_slug'     => 'quote_icon',
					'sub_toggle'      => 'quote_icon_closing',
					'description'     => esc_html__( 'cloing quote icon position', 'divi-plus' ),
				),
				'star_font_size' => array(
					'label'           => esc_html__( 'Star Font Size', 'divi-plus' ),
					'type'            => 'range',
					'option_category' => 'layout',
					'range_settings'  => array(
						'min'  => '10',
						'max'  => '100',
						'step' => '1',
					),
					'show_if'         => array(
						'show_rating' => 'on',
					),
					'default'         => '24px',
					'tab_slug'        => 'advanced',
					'toggle_slug'     => 'rating',
					'description'     => esc_html__( 'Here you can choose the star font size.', 'divi-plus' ),
				),
				'filled_star_color' => array(
					'label'        => esc_html__( 'Filled Star Color', 'divi-plus' ),
					'type'         => 'color-alpha',
					'custom_color' => true,
					'show_if'      => array(
						'show_rating' => 'on',
					),
					'tab_slug'     => 'advanced',
					'toggle_slug'  => 'rating',
					'description'  => esc_html__( 'Here you can define color for the rated star.', 'divi-plus' ),
				),
				'empty_star_color' => array(
					'label'        => esc_html__( 'Empty Star Color', 'divi-plus' ),
					'type'         => 'color-alpha',
					'custom_color' => true,
					'show_if'      => array(
						'show_rating' => 'on',
					),
					'tab_slug'     => 'advanced',
					'toggle_slug'  => 'rating',
					'description'  => esc_html__( 'Here you can define color for the unrated star.', 'divi-plus' ),
				),
				'testimonial_card_custom_padding' => array(
	                'label'                 => esc_html__( 'Testimonial Padding', 'divi-plus' ),
	                'type'                  => 'custom_padding',
	                'option_category'       => 'layout',
	                'mobile_options'        => true,
	                'hover'                 => false,
	                'tab_slug'              => 'advanced',
	                'toggle_slug'           => 'margin_padding',
	                'description'           => esc_html__( 'Padding adds extra space to the inside of the element, increasing the distance between the edge of the element and its inner contents.', 'divi-plus' ),
	            ),
	            'pagination_link_background_color' => array(
					'label'        => esc_html__( 'Pagination Link Background Color', 'divi-plus' ),
					'type'         => 'color-alpha',
					'custom_color' => true,
					'default'	   => 'transparent',
					'hover'		   => 'tabs',
					'show_if'      => array(
						'show_pagination' => 'on',
					),
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
				'__testimonial_data'                 => array(
					'type'                => 'computed',
					'computed_callback'   => array( 'DIPL_TestimonialGrid', 'get_testimonial_data' ),
					'computed_depends_on' => array(
						'testimonial_layout',
						'testimonial_number',
						'offset_number',
						'testimonial_order',
						'testimonial_order_by',
						'include_categories',
						'number_of_columns',
						'use_masonry',
						'show_author_image',
						'author_image_size',
						'use_gravatar',
						'show_author_designation',
						'show_author_company_name',
						'show_opening_quote_icon',
						'show_closing_quote_icon',
						'show_rating',
						'show_pagination',
						'show_prev_next',
						'prev_text',
						'next_text'
					),
				),
			),
			$this->generate_background_options( 'testimonial_card_bg', 'skip', 'general', 'background', 'testimonial_card_bg_color' )
		);
	}

	/**
	 * This function return values to react for front end builder.
	 *
	 * @param array arguments to get testimonial data
	 * @return array
	 * */
	public static function get_testimonial_data( $args = array(), $conditional_tags = array(), $current_page = array() ) {
		if ( self::$rendering ) {
			// We are trying to render a Blog module while a Blog module is already being rendered
			// which means we have most probably hit an infinite recursion. While not necessarily
			// the case, rendering a post which renders a Blog module which renders a post
			// which renders a Blog module is not a sensible use-case.
			return '';
		}

		$defaults = array(
			'testimonial_layout' => 'layout1',
			'testimonial_number' => '10',
			'offset_number'	=> '0',
			'testimonial_order' => 'DESC',
			'testimonial_order_by' => 'date',
			'include_categories' => '',
			'number_of_columns' => '2',
			'use_masonry' => 'off',
			'show_author_image' => 'on',
			'author_image_size'	=> 'off',
			'use_gravatar' => 'off',
			'show_author_designation' => 'on',
			'show_author_company_name' => 'on',
			'show_opening_quote_icon' => 'on',
			'show_closing_quote_icon' => 'off',
			'show_rating' => 'on',
			'show_pagination' => 'off',
			'show_prev_next' => 'off',
			'prev_text' => esc_html__( 'Prev', 'divi-plus' ),
			'next_text' => esc_html__( 'Next', 'divi-plus' ),

		);

		$args = wp_parse_args( $args, $defaults );

		foreach ( $defaults as $key => $default ) {
			${$key} = sanitize_text_field( et_()->array_get( $args, $key, $default ) );
		}

		$testimonial_number = ( 0 === $testimonial_number ) ? -1 : (int) $testimonial_number;

		$args = array(
			'post_type'      => 'dipl-testimonial',
			'posts_per_page' => intval( $testimonial_number ),
			'offset'		 => intval( $offset_number ),
			'post_status'    => 'publish',
			'orderby'        => $testimonial_order_by,
			'order'          => $testimonial_order,
		);

		if ( is_user_logged_in() ) {
			$args['post_status'] = array(
				'publish',
				'private',
			);
		}

		if ( $include_categories && '' !== $include_categories ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'dipl-testimonial-category',
					'field'    => 'term_id',
					'terms'    => array_map( 'intval', explode( ',', $include_categories ) ),
					'operator' => 'IN',
				),
			);
		}

		global $wp_the_query;
		$query_backup = $wp_the_query;

		$query = new WP_Query( $args );

		self::$rendering = true;

		if ( $query->have_posts() ) {

			$number_of_columns 	= intval( $number_of_columns );
			$column_class 		= " dipl_testimonial_cols_{$number_of_columns}";

			$testimonials = '<div class="dipl_testimonial_layout ' . $testimonial_layout . $column_class . '">';

			if ( 'on' === $use_masonry ) {
				$testimonials .= '<div class="dipl_testimonial_isotope_container">';
			}

			while ( $query->have_posts() ) {
				$query->the_post();
				$testimonial_id = intval( get_the_ID() );

				/* Get Star Rating */
				$rating = floatval( get_post_meta( $testimonial_id, 'dipl_testimonial_author_rating', true ) );
				if ( 'on' === $show_rating && $rating > 0 ) {
					$stars = '';
					
					for ( $i = 1; $i <= absint( $rating ); $i++ ) {
		                $stars .= '<span class="dipl_testimonial_star dipl_testimonial_filled_star"></span>';
		            }
		            if ( $rating != absint( $rating ) ) {
		                $stars .= '<span class="dipl_testimonial_star dipl_testimonial_half_filled_star"></span>';
		                $unfilled_stars  = 5 - absint( $rating ) - 1;
		            } else {
		                $unfilled_stars  = 5 - absint( $rating );
		            }
		            for ( $i = 1; $i <= $unfilled_stars; $i++ ) {
		                $stars .= '<span class="dipl_testimonial_star dipl_testimonial_empty_star"></span>';
		            }

					$testimonial_rating = sprintf(
						'<div class="dipl_testimonial_rating">
							<span itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating">
								<span class="dipl_testimonial_rating_value" itemprop="ratingValue">%1$s</span>
								%2$s
							</span>
						</div>',
						$rating,
						$stars
					);
				}

				/* Get Author Image */
				if ( 'on' === $show_author_image ) {
					if ( has_post_thumbnail( $testimonial_id ) ) {
						$image = get_the_post_thumbnail( $testimonial_id, $author_image_size );
					} else if ( 'on' === $use_gravatar ) {
						$author_email = sanitize_email( get_post_meta( $testimonial_id, 'dipl_testimonial_author_email', true ) );
						if ( '' !== $author_email ) {
							$image = get_avatar( $author_email, '100' );
							if ( ! $image ) {
								$image = '<img src="' . esc_url( plugins_url( 'assets/mystery-person.jpg', dirname( __DIR__ ) ) ) . '" class="dipl_testimonial_mystery_person" alt="Mystery Person" />';	
							}
						} else {
							$image = '<img src="' . esc_url( plugins_url( 'assets/mystery-person.jpg', dirname( __DIR__ ) ) ) . '" class="dipl_testimonial_mystery_person" alt="Mystery Person" />';
						}
					} else {
						$image = '<img src="' . esc_url( plugins_url( 'assets/mystery-person.jpg', dirname( __DIR__ ) ) ) . '" class="dipl_testimonial_mystery_person" alt="Mystery Person" />';
					}

					$author_image = sprintf(
						'<div class="dipl_testimonial_author_image">%1$s</div>',
						et_core_intentionally_unescaped( $image, 'html' )
					);
				}

				/* Get Author Name */
				$name = get_post_meta( $testimonial_id, 'dipl_testimonial_author_name', true );
				if ( '' !== $name ) {
					$author_name = sprintf(
						'<div class="dipl_testimonial_author_name">%1$s</div>',
						esc_html( $name )
					);
				} else {
					$author_name = '';
				}

				/* Get Author Designation */
				if ( 'on' === $show_author_designation ) {
					$desgination = get_post_meta( $testimonial_id, 'dipl_testimonial_author_designation', true );
					if ( '' !== $desgination ) {
						$author_designation = sprintf(
							'<div class="dipl_testimonial_author_designation">%1$s</div>',
							esc_html( $desgination )
						);
					} else {
						$author_designation = '';
					}
				}

				/* Get Company Details */
				if ( 'on' === $show_author_company_name ) {
					$company_name 	= get_post_meta( $testimonial_id, 'dipl_testimonial_author_company', true );
					$company_url  	= get_post_meta( $testimonial_id, 'dipl_testimonial_author_company_url', true );
					$author_company = '';
					if ( '' !== $company_url && '' !== $company_name ) {
						$author_company = sprintf(
							'<div class="dipl_testimonial_author_company">
								<a href="%1$s" target="_blank" rel="nofollow">%2$s</a>
							</div>',
							esc_url( $company_url ),
							esc_html( $company_name )
						);
					} elseif ( '' !== $company_name ) {
						$author_company = sprintf(
							'<div class="dipl_testimonial_author_company">
								%1$s
							</div>',
							esc_html( $company_name )
						);
					}
				}

				$testimonials .= '<div class="dipl_testimonial_isotope_item dipl_testimonial_isotope_item_page_1">';

				if ( file_exists( get_stylesheet_directory() . '/divi-plus/layouts/testimonial-grid/' . sanitize_file_name( $testimonial_layout ) . '.php' ) ) {
					include get_stylesheet_directory() . '/divi-plus/layouts/testimonial-grid/' . sanitize_file_name( $testimonial_layout ) . '.php';
				} elseif ( file_exists( plugin_dir_path( __FILE__ ) . 'layouts/' . sanitize_file_name( $testimonial_layout ) . '.php' ) ) {
					include plugin_dir_path( __FILE__ ) . 'layouts/' . sanitize_file_name( $testimonial_layout ) . '.php';
				}

				$testimonials .= '</div>';
			}

			wp_reset_postdata();
			
			$wp_the_query = $query_backup;

			if ( 'on' === $use_masonry ) {
				$testimonials .= '</div>';
			}

			$testimonials .= '</div>';

			if ( 'on' === $show_pagination ) {
				if ( -1 === $testimonial_number ) {
					$total_pages = 1;
				} else if ( '' !== $offset_number && ! empty( $offset_number ) ) {
					$total_pages = intval( ceil( ( $query->found_posts - $offset_number ) / $testimonial_number ) );
				} else {
					$total_pages = intval( ceil( $query->found_posts / $testimonial_number ) );
				}

				$pagination = sprintf(
					'<div class="dipl_testimonial_grid_pagination_wrapper" data-total_pages="%1$s">
						<ul></ul>
					</div>',
					$total_pages
				);

				$testimonials .= $pagination;
			}

			$output = $testimonials;

		} else {
			$output = '';
		}

		self::$rendering = false;

		return et_core_intentionally_unescaped( $output, 'html' );
	}

	public function render( $attrs, $content, $render_slug ) {

		if ( self::$rendering ) {
			// We are trying to render a Blog module while a Blog module is already being rendered
			// which means we have most probably hit an infinite recursion. While not necessarily
			// the case, rendering a post which renders a Blog module which renders a post
			// which renders a Blog module is not a sensible use-case.
			return '';
		}

		$testimonial_layout 				= $this->props['testimonial_layout'];
		$testimonial_number 				= $this->props['testimonial_number'];
		$offset_number						= $this->props['offset_number'];
		$testimonial_order 					= $this->props['testimonial_order'];
		$testimonial_order_by				= $this->props['testimonial_order_by'];
		$include_categories 				= $this->props['include_categories'];
		$no_result_text						= $this->props['no_result_text'];
		$number_of_columns 					= $this->props['number_of_columns'];
		$use_masonry						= $this->props['use_masonry'];
		$show_author_image					= $this->props['show_author_image'];
		$use_gravatar 						= $this->props['use_gravatar'];
		$author_image_size					= $this->props['author_image_size'];
		$show_author_designation			= $this->props['show_author_designation'];
		$show_author_company_name			= $this->props['show_author_company_name'];
		$testimonial_card_bg_color  		= $this->props['testimonial_card_bg_color'];
		$meta_separator_color 				= $this->props['meta_separator_color'];
		$show_opening_quote_icon 			= $this->props['show_opening_quote_icon'];
		$opening_quote_icon_size 			= $this->props['opening_quote_icon_size'];
		$opening_quote_icon_color 			= $this->props['opening_quote_icon_color'];
		$custom_position_opening_quote_icon = $this->props['custom_position_opening_quote_icon'];
		$opening_quote_icon_position 		= $this->props['opening_quote_icon_position'];
		$show_closing_quote_icon 			= $this->props['show_closing_quote_icon'];
		$closing_quote_icon_size 			= $this->props['closing_quote_icon_size'];
		$closing_quote_icon_color 			= $this->props['closing_quote_icon_color'];
		$custom_position_closing_quote_icon = $this->props['custom_position_closing_quote_icon'];
		$closing_quote_icon_position 		= $this->props['closing_quote_icon_position'];
		$show_rating						= $this->props['show_rating'];
		$star_font_size 					= $this->props['star_font_size'];
		$filled_star_color 					= $this->props['filled_star_color'];
		$empty_star_color 					= $this->props['empty_star_color'];
		$show_pagination					= $this->props['show_pagination'];

		$testimonial_number = ( 0 === $testimonial_number ) ? -1 : (int) $testimonial_number;
		wp_enqueue_script( 'dipl-testimonial-grid-custom', PLUGIN_PATH . 'includes/modules/TestimonialGrid/dipl-testimonial-grid-custom.min.js', array('jquery'), '1.0.1', true );
		wp_localize_script( 'dipl-testimonial-grid-custom', 'DiviPlusTestimonialGridData', array(
            'ajaxurl'   => admin_url( 'admin-ajax.php' ),
            'ajaxnonce' => wp_create_nonce( 'elicus-divi-plus-testimonial-grid-nonce' ),
        ));
		$file = et_is_builder_plugin_active() ? 'style-dbp' : 'style';
        wp_enqueue_style( 'dipl-testimonial-grid-style', PLUGIN_PATH . 'includes/modules/TestimonialGrid/' . $file . '.min.css', array(), '1.0.0' );

		$args = array(
			'post_type'      => 'dipl-testimonial',
			'posts_per_page' => intval( $testimonial_number ),
			'offset'		 => intval( $offset_number ),
			'post_status'    => 'publish',
			'orderby'        => 'date',
			'order'          => 'DESC',
		);

		if ( is_user_logged_in() ) {
			$args['post_status'] = array(
				'publish',
				'private',
			);
		}

		if ( $include_categories && '' !== $include_categories ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'dipl-testimonial-category',
					'field'    => 'term_id',
					'terms'    => array_map( 'intval', explode( ',', $include_categories ) ),
					'operator' => 'IN',
				),
			);
		}

		if ( '' !== $testimonial_order_by ) {
			$args['orderby'] = sanitize_text_field( $testimonial_order_by );
		}

		if ( '' !== $testimonial_order ) {
			$args['order'] = sanitize_text_field( $testimonial_order );
		}

		if ( '1' !== $number_of_columns && 'on' === $use_masonry ) {
			wp_enqueue_script( 'elicus-isotope-script' );
		}

		global $wp_the_query;
		$query_backup = $wp_the_query;

		$query = new WP_Query( $args );

		self::$rendering = true;

		if ( $query->have_posts() ) {

			$number_of_columns 	= intval( $number_of_columns );
			$column_class 		= " dipl_testimonial_cols_{$number_of_columns}";

			$testimonials = '<div class="dipl_testimonial_layout ' . $testimonial_layout . $column_class . '">';

			if ( 'on' === $use_masonry ) {
				$testimonials .= '<div class="dipl_testimonial_isotope_container">';
			}

			while ( $query->have_posts() ) {
				$query->the_post();
				$testimonial_id = esc_attr( get_the_ID() );

				/*Get Star Rating*/
				$rating = floatval( get_post_meta( $testimonial_id, 'dipl_testimonial_author_rating', true ) );
				if ( 'on' === $show_rating && $rating > 0 ) {
					$stars = '';
					
					for ( $i = 1; $i <= absint( $rating ); $i++ ) {
		                $stars .= '<span class="dipl_testimonial_star dipl_testimonial_filled_star"></span>';
		            }
		            if ( $rating != absint( $rating ) ) {
		                $stars .= '<span class="dipl_testimonial_star dipl_testimonial_half_filled_star"></span>';
		                $unfilled_stars  = 5 - absint( $rating ) - 1;
		            } else {
		                $unfilled_stars  = 5 - absint( $rating );
		            }
		            for ( $i = 1; $i <= $unfilled_stars; $i++ ) {
		                $stars .= '<span class="dipl_testimonial_star dipl_testimonial_empty_star"></span>';
		            }

					$testimonial_rating = sprintf(
						'<div class="dipl_testimonial_rating">
							<span itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating">
								<span class="dipl_testimonial_rating_value" itemprop="ratingValue">%1$s</span>
								%2$s
							</span>
						</div>',
						$rating,
						$stars
					);
					
				}

				/* Get Author Image */
				if ( 'on' === $show_author_image ) {
					if ( has_post_thumbnail( $testimonial_id ) ) {
						$image = get_the_post_thumbnail( $testimonial_id, $author_image_size );
					} else if ( 'on' === $use_gravatar ) {
						$author_email = sanitize_email( get_post_meta( $testimonial_id, 'dipl_testimonial_author_email', true ) );
						if ( '' !== $author_email ) {
							$image = get_avatar( $author_email, '100' );
							if ( ! $image ) {
								$image = '<img src="' . esc_url( plugins_url( 'assets/mystery-person.jpg', dirname( __DIR__ ) ) ) . '" class="dipl_testimonial_mystery_person" alt="Mystery Person" />';	
							}
						} else {
							$image = '<img src="' . esc_url( plugins_url( 'assets/mystery-person.jpg', dirname( __DIR__ ) ) ) . '" class="dipl_testimonial_mystery_person" alt="Mystery Person" />';
						}
					} else {
						$image = '<img src="' . esc_url( plugins_url( 'assets/mystery-person.jpg', dirname( __DIR__ ) ) ) . '" class="dipl_testimonial_mystery_person" alt="Mystery Person" />';
					}

					$author_image = sprintf(
						'<div class="dipl_testimonial_author_image">%1$s</div>',
						et_core_intentionally_unescaped( $image, 'html' )
					);
				}

				//* Get Author Name */
				$name = get_post_meta( $testimonial_id, 'dipl_testimonial_author_name', true );
				if ( '' !== $name ) {
					$author_name = sprintf(
						'<div class="dipl_testimonial_author_name">%1$s</div>',
						esc_html( $name )
					);
				} else {
					$author_name = '';
				}

				/* Get Author Designation */
				if ( 'on' === $show_author_designation ) {
					$desgination = get_post_meta( $testimonial_id, 'dipl_testimonial_author_designation', true );
					if ( '' !== $desgination ) {
						$author_designation = sprintf(
							'<div class="dipl_testimonial_author_designation">%1$s</div>',
							esc_html( $desgination )
						);
					} else {
						$author_designation = '';
					}
				}

				/* Get Company Details */
				if ( 'on' === $show_author_company_name ) {
					$company_name 	= get_post_meta( $testimonial_id, 'dipl_testimonial_author_company', true );
					$company_url  	= get_post_meta( $testimonial_id, 'dipl_testimonial_author_company_url', true );
					$author_company = '';
					if ( '' !== $company_url && '' !== $company_name ) {
						$author_company = sprintf(
							'<div class="dipl_testimonial_author_company">
								<a href="%1$s" target="_blank" rel="nofollow">%2$s</a>
							</div>',
							esc_url( $company_url ),
							esc_html( $company_name )
						);
					} elseif ( '' !== $company_name ) {
						$author_company = sprintf(
							'<div class="dipl_testimonial_author_company">
								%1$s
							</div>',
							esc_html( $company_name )
						);
					}
				}

				$testimonials .= '<div class="dipl_testimonial_isotope_item">';

				if ( file_exists( get_stylesheet_directory() . '/divi-plus/layouts/testimonial-grid/' . sanitize_file_name( $testimonial_layout ) . '.php' ) ) {
					include get_stylesheet_directory() . '/divi-plus/layouts/testimonial-grid/' . sanitize_file_name( $testimonial_layout ) . '.php';
				} elseif ( file_exists( plugin_dir_path( __FILE__ ) . 'layouts/' . sanitize_file_name( $testimonial_layout ) . '.php' ) ) {
					include plugin_dir_path( __FILE__ ) . 'layouts/' . sanitize_file_name( $testimonial_layout ) . '.php';
				}

				$testimonials .= '</div>';
			}

			wp_reset_postdata();

			$wp_the_query = $query_backup;

			if ( 'on' === $use_masonry ) {
				$testimonials .= '</div>';
			}

			$testimonials .= '</div>';

			if ( 'on' === $show_pagination ) {
				wp_enqueue_script( 'elicus-twbs-pagination' );

				$data_props = array(
					'testimonial_layout',
					'testimonial_number',
					'offset_number',
					'testimonial_order',
					'testimonial_order_by',
					'include_categories',
					'show_author_image',
					'use_gravatar',
					'author_image_size',
					'show_author_designation',
					'show_author_company_name',
					'show_opening_quote_icon',
					'show_closing_quote_icon',
					'show_rating',
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
					'<div class="dipl_testimonial_grid_pagination_wrapper" data-total_pages="%1$s" data-is_testimonial_category="%2$s" data-is_testimonial_taxonomy="%3$s" data-term_id="%4$s" data-term_slug="%5$s" data-taxonomy="%6$s" data-is_search="%7$s" data-search_query="%8$s" data-is_user_logged_in="%9$s" data-order_class="%10$s" %11$s>
						<ul></ul>
					</div>',
					$total_pages,
					esc_attr( is_tax( 'dipl-testimonial-category' ) ),
					esc_attr( is_tax( get_object_taxonomies( 'dipl-testimonial' ) ) ),
					is_tax( get_object_taxonomies( 'dipl-testimonial' ) ) ? esc_attr( get_queried_object_id() ) : '',
					is_tax( get_object_taxonomies( 'dipl-testimonial' ) ) ? esc_attr( get_queried_object()->slug ) : '',
					is_tax( get_object_taxonomies( 'dipl-testimonial' ) ) ? esc_attr( get_queried_object()->taxonomy ) : '',
					esc_attr( is_search() ),
					esc_attr( get_search_query() ),
					esc_attr( is_user_logged_in() ),
					esc_attr( $this->get_module_order_class( 'dipl_testimonial_grid' ) ),
					$data_atts
				);
			}

			$output = $testimonials;

			if ( 'on' === $show_pagination ) {
				$output .= $pagination;
			}

			if ( 'on' === $show_pagination ) {
				$this->generate_styles(
					array(
						'base_attr_name' => 'pagination_link_background_color',
						'selector'       => '%%order_class%% .dipl_testimonial_grid_pagination_wrapper li a',
						'hover_selector' => '%%order_class%% .dipl_testimonial_grid_pagination_wrapper li a:hover',
						'css_property'   => 'background-color',
						'render_slug'    => $render_slug,
						'type'           => 'color',
						'important'		 => true,
					)
				);

				$this->generate_styles(
					array(
						'base_attr_name' => 'pagination_link_color',
						'selector'       => '%%order_class%% .dipl_testimonial_grid_pagination_wrapper li a',
						'hover_selector' => '%%order_class%% .dipl_testimonial_grid_pagination_wrapper li a:hover',
						'css_property'   => 'color',
						'render_slug'    => $render_slug,
						'type'           => 'color',
						'important'		 => true,
					)
				);

				$this->generate_styles(
					array(
						'base_attr_name' => 'active_pagination_link_background_color',
						'selector'       => '%%order_class%% .dipl_testimonial_grid_pagination_wrapper li.active a',
						'hover_selector' => '%%order_class%% .dipl_testimonial_grid_pagination_wrapper li.active a:hover',
						'css_property'   => 'background-color',
						'render_slug'    => $render_slug,
						'type'           => 'color',
						'important'		 => true,
					)
				);

				$this->generate_styles(
					array(
						'base_attr_name' => 'active_pagination_link_color',
						'selector'       => '%%order_class%% .dipl_testimonial_grid_pagination_wrapper li.active a',
						'hover_selector' => '%%order_class%% .dipl_testimonial_grid_pagination_wrapper li.active a:hover',
						'css_property'   => 'color',
						'render_slug'    => $render_slug,
						'type'           => 'color',
						'important'		 => true,
					)
				);
			}

			if ( 'layout1' === $testimonial_layout ) {
				if ( $meta_separator_color ) {
					self::set_style(
						$render_slug,
						array(
							'selector'    => '%%order_class%% .layout1 .dipl_testimonial_meta',
							'declaration' => sprintf( 'border-top-color: %1$s;', esc_attr( $meta_separator_color ) ),
						)
					);
				}
			}

			if ( 'on' === $show_rating ) {
				if ( $filled_star_color ) {
					self::set_style(
						$render_slug,
						array(
							'selector'    => '%%order_class%% .dipl_testimonial_rating .dipl_testimonial_filled_star, %%order_class%% .dipl_testimonial_rating .dipl_testimonial_half_filled_star',
							'declaration' => sprintf( 'color: %1$s !important;', esc_html( $filled_star_color ) ),
						)
					);
				}

				if ( $empty_star_color ) {
					self::set_style(
						$render_slug,
						array(
							'selector'    => '%%order_class%% .dipl_testimonial_rating .dipl_testimonial_empty_star',
							'declaration' => sprintf( 'color: %1$s !important;', esc_html( $empty_star_color ) ),
						)
					);
				}

				$star_font_size = et_pb_responsive_options()->get_property_values( $this->props, 'star_font_size' );
				et_pb_responsive_options()->generate_responsive_css( $star_font_size, '%%order_class%% .dipl_testimonial_rating .dipl_testimonial_star', 'font-size', $render_slug, '!important;', 'range' );
			}

			if ( 'on' === $show_opening_quote_icon ) {
				if ( $opening_quote_icon_color ) {
					self::set_style(
						$render_slug,
						array(
							'selector'    => '%%order_class%% .dipl_testimonial_opening_quote_icon',
							'declaration' => sprintf( 'color: %1$s !important;', esc_attr( $opening_quote_icon_color ) ),
						)
					);
				}

				if ( 'on' === $custom_position_opening_quote_icon ) {
					self::set_style(
						$render_slug,
						array(
							'selector'    => '%%order_class%% .dipl_testimonial_opening_quote_icon',
							'declaration' => 'position: absolute;',
						)
					);

					$opening_quote_icon_position_top = et_pb_responsive_options()->get_property_values( $this->props, 'opening_quote_icon_position_top' );

					if ( ! empty ( $opening_quote_icon_position_top ) ) {
						et_pb_responsive_options()->generate_responsive_css( $opening_quote_icon_position_top, '%%order_class%% .dipl_testimonial_opening_quote_icon', 'top', $render_slug, '!important;', 'range' );
					}
					
					if ( 'left' === $opening_quote_icon_position ) {
						self::set_style(
							$render_slug,
							array(
								'selector'    => '%%order_class%% .dipl_testimonial_opening_quote_icon',
								'declaration' => 'left: 0 !important;',
							)
						);
					}

					if ( 'right' === $opening_quote_icon_position ) {
						self::set_style(
							$render_slug,
							array(
								'selector'    => '%%order_class%% .dipl_testimonial_opening_quote_icon',
								'declaration' => 'right: 0 !important;',
							)
						);
					}

					if ( 'center' === $opening_quote_icon_position ) {
						self::set_style(
							$render_slug,
							array(
								'selector'    => '%%order_class%% .dipl_testimonial_opening_quote_icon',
								'declaration' => 'left: 50% !important; -webkit-transform: translateX(-50%); transform: translateX(-50%);',
							)
						);
					}
				}

				$opening_quote_icon_size = et_pb_responsive_options()->get_property_values( $this->props, 'opening_quote_icon_size' );
				et_pb_responsive_options()->generate_responsive_css( $opening_quote_icon_size, '%%order_class%% .dipl_testimonial_opening_quote_icon', 'font-size', $render_slug, '!important;', 'range' );
			}

			if ( 'on' === $show_closing_quote_icon ) {
				if ( $closing_quote_icon_color ) {
					self::set_style(
						$render_slug,
						array(
							'selector'    => '%%order_class%% .dipl_testimonial_closing_quote_icon',
							'declaration' => sprintf( 'color: %1$s !important;', esc_attr( $closing_quote_icon_color ) ),
						)
					);
				}

				if ( 'on' === $custom_position_closing_quote_icon ) {
					self::set_style(
						$render_slug,
						array(
							'selector'    => '%%order_class%% .dipl_testimonial_closing_quote_icon',
							'declaration' => 'position: absolute;',
						)
					);

					$closing_quote_icon_position_bottom = et_pb_responsive_options()->get_property_values( $this->props, 'closing_quote_icon_position_bottom' );

					if ( ! empty ( $closing_quote_icon_position_bottom ) ) {
						et_pb_responsive_options()->generate_responsive_css( $closing_quote_icon_position_bottom, '%%order_class%% .dipl_testimonial_closing_quote_icon', 'bottom', $render_slug, '!important;', 'range' );
					}

					if ( 'left' === $closing_quote_icon_position ) {
						self::set_style(
							$render_slug,
							array(
								'selector'    => '%%order_class%% .dipl_testimonial_closing_quote_icon',
								'declaration' => 'left: 0 !important;',
							)
						);
					}

					if ( 'right' === $closing_quote_icon_position ) {
						self::set_style(
							$render_slug,
							array(
								'selector'    => '%%order_class%% .dipl_testimonial_closing_quote_icon',
								'declaration' => 'right: 0 !important;',
							)
						);
					}

					if ( 'center' === $closing_quote_icon_position ) {
						self::set_style(
							$render_slug,
							array(
								'selector'    => '%%order_class%% .dipl_testimonial_closing_quote_icon',
								'declaration' => 'left: 50% !important; -webkit-transform: translateX(-50%); transform: translateX(-50%);',
							)
						);
					}
				}

				$closing_quote_icon_size = et_pb_responsive_options()->get_property_values( $this->props, 'closing_quote_icon_size' );
				et_pb_responsive_options()->generate_responsive_css( $closing_quote_icon_size, '%%order_class%% .dipl_testimonial_closing_quote_icon', 'font-size', $render_slug, '!important;', 'range' );
			}

			$args = array(
				'render_slug'	=> $render_slug,
				'props'			=> $this->props,
				'fields'		=> $this->fields_unprocessed,
				'module'		=> $this,
				'backgrounds' 	=> array(
					'testimonial_card_bg' => array(
						'normal' => "{$this->main_css_element} .dipl_single_testimonial_card",
						'hover' => "{$this->main_css_element} .dipl_single_testimonial_card:hover",
		 			),
				),
			);

			DiviPlusHelper::process_background( $args );
			$fields = array( 'testimonial_margin_padding' );
			DiviPlusHelper::process_advanced_margin_padding_css( $this, $render_slug, $this->margin_padding, $fields );

		} else {
			$output = '<div class="entry">' . esc_html( $no_result_text ) . '</div>';
		}

		$this->add_classname(
			array(
				$this->get_text_orientation_classname(),
			)
		);

		self::$rendering = false;

		return et_core_intentionally_unescaped( $output, 'html' );
	}
}
$plugin_options = get_option( ELICUS_DIVI_PLUS_OPTION );
if ( isset( $plugin_options['dipl-modules'] ) ) {
	$modules = explode( ',', $plugin_options['dipl-modules'] );
	if ( in_array( 'dipl_testimonial_grid', $modules, true ) ) {
		new DIPL_TestimonialGrid();
	}
} else {
	new DIPL_TestimonialGrid();
}