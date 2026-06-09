<?php
/**
 * @author      Elicus <hello@elicus.com>
 * @link        https://www.elicus.com/
 * @copyright   2022 Elicus Technologies Private Limited
 * @version     1.9.11
 */
class DIPL_WooProductsCategories extends ET_Builder_Module {
	public $slug       = 'dipl_woo_products_categories';
	public $vb_support = 'on';

	protected $module_credits = array(
		'module_uri' => 'https://diviextended.com/product/divi-plus/',
		'author'     => 'Elicus',
		'author_uri' => 'https://elicus.com/',
	);

	public function init() {
		$this->name             = esc_html__( 'DP Woo Products Categories', 'divi-plus' );
		$this->main_css_element = '%%order_class%%';
		add_filter( 'et_builder_processed_range_value', array( $this, 'dipl_builder_processed_range_value' ), 10, 3 );
	}

	public function get_settings_modal_toggles() {
		return array(
			'general'  => array(
				'toggles' => array(
					'main_content'    => array(
						'title'    => esc_html__( 'Content', 'divi-plus' ),
					),
					'display_setting' => array(
						'title'    => esc_html__( 'Display', 'divi-plus' ),
					),
				),
			),
			'advanced' => array(
				'toggles' => array(
					'category_item'  => array(
						'title' => esc_html__( 'Category Item', 'divi-plus' ),
					),
					'category_img'  => array(
						'title' => esc_html__( 'Thumbnail', 'divi-plus' ),
					),
					'title_text'  => array(
						'title' => esc_html__( 'Title', 'divi-plus' ),
					),
					'product_count' => array(
						'title' => esc_html__( 'Product Count', 'divi-plus' ),
					),
					'sale_badge' => array(
						'title' => esc_html__( 'Sale Badge', 'divi-plus' ),
					),
					'add_to_cart_button' => array (
						'title' => esc_html__( 'Add to Cart', 'divi-plus' ),
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
						'default' => 'h4',
					),
					'text_align'     => array(
						'default' => 'center',
					),
					'css'            => array(
						'main'       => "{$this->main_css_element} .dipl_woo_product_category_name, {$this->main_css_element} .dipl_woo_product_category_name a",
						'important' => 'all',
					),
					'tab_slug'	=> 'advanced',
                    'toggle_slug' => 'title_text',
				),
				'product_count' => array(
					'label'          => esc_html__( 'Product Count', 'divi-plus' ),
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
					'text_align'     => array(
						'default' => 'center',
					),
					'css' => array(
						'main'  => "{$this->main_css_element} .dipl_woo_product_category_count, {$this->main_css_element} .dipl_woo_product_category_count a",
						'important' => 'all',
					),
					'tab_slug'	=> 'advanced',
                    'toggle_slug' => 'product_count',
				),
			),
			'borders' => array(
				'category' => array(
					'label_prefix'    => esc_html__( 'Category Item', 'divi-plus' ),
					'css' => array(
						'main' => array(
							'border_radii'  => '%%order_class%% .dipl_woo_product_category',
							'border_styles' => '%%order_class%% .dipl_woo_product_category',
							'important' => 'all',
						),
					),
					'tab_slug'    => 'advanced',
					'toggle_slug' => 'category_item',
				),
				'category_img' => array(
					'label_prefix'    => esc_html__( 'Category Image', 'divi-plus' ),
					'css' => array(
						'main' => array(
							'border_radii'  => '%%order_class%% .dipl_woo_product_category_thumbnail img',
							'border_styles' => '%%order_class%% .dipl_woo_product_category_thumbnail img',
							'important' => 'all',
						),
					),
					'tab_slug'    => 'advanced',
					'toggle_slug' => 'category_img',
				),
				'category_content_wrapper' => array(
					'label_prefix'    => esc_html__( 'Content Wrapper', 'divi-plus' ),
					'defaults' => array(
				        'border_radii' => 'on||||',
				        'border_styles' => array(
				            'width' => '1px',
				            'color' => '#333333',
							'style' => 'solid',
				        ),
				    ),
					'depends_on' => array( 'category_layout' ),
					'depends_show_if' => 'layout2',
					'css' => array(
						'main' => array(
							'border_radii'  => '%%order_class%% .layout2 .dipl_woo_product_category_content_wrapper',
							'border_styles' => '%%order_class%% .layout2 .dipl_woo_product_category_content_wrapper',
							'important' => 'all',
						),
					),
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
				'product_category' => array(
					'label'       => esc_html__( 'Product Category', 'divi-plus' ),
					'css'         => array(
						'main' => "%%order_class%% .dipl_woo_product_category",
						'important' => 'all',
					),
					'tab_slug'    => 'advanced',
					'toggle_slug' => 'category_item',
				),
				'category_img' => array(
					'label'    => esc_html__( 'Category Image', 'divi-plus' ),
					'css' => array(
						'main' => '%%order_class%% .dipl_woo_product_category_thumbnail img',
						'important' => 'all',
					),
					'tab_slug'    => 'advanced',
					'toggle_slug' => 'category_img',
				),
				'default' => array(
					'css' => array(
						'main' => $this->main_css_element,
						'important' => 'all',
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
					'category_img' => array(
						'options' => array(
							'height' => array(
								'label'          => esc_html__( 'Image Height', 'divi-plus' ),
								'range_settings' => array(
									'min'  => 1,
									'max'  => 1000,
									'step' => 1,
								),
								'hover'          => false,
								'default_unit'   => 'px',
								'default'	     => 'auto',
								'default_tablet' => '',
								'default_phone'  => '',
								'tab_slug'       => 'advanced',
								'toggle_slug'    => 'category_img',
							),
						),
						'use_max_height' => false,
						'use_min_height' => false,
						'css'            => array(
							'main' => "{$this->main_css_element} .dipl_woo_product_category_thumbnail img",
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
			'text' 			=> false,
			'filters'       => false,
			'link_options'  => false,
		);
	}

	public function get_fields() {
		return array_merge(
			array(
				'category_layout' => array(
	                'label'             => esc_html__( 'Layout', 'divi-plus' ),
	                'type'              => 'select',
	                'option_category'   => 'configuration',
	                'options'           => array(
	                    'layout1'	=> esc_html__( 'Layout 1', 'divi-plus' ),
	                    'layout2'   => esc_html__( 'Layout 2', 'divi-plus' ),
	                ),
	                'default'			=> 'layout1',
	                'default_on_front'	=> 'layout1',
	                'tab_slug'          => 'general',
	                'toggle_slug'       => 'main_content',
	                'description'       => esc_html__( 'Here you can select the layout to display categories.', 'divi-plus' ),
	                'computed_affects' => array(
						'__products_data',
					),
	            ),
				'include_categories' => array(
					'label'            => esc_html__( 'Include Categories', 'divi-plus' ),
					'type'             => 'categories',
					'renderer_options' => array(
						'use_terms' => true,
						'term_name' => 'product_cat',
						'field_name' => 'et_pb_include_dipl_product_cat',
					),
					'tab_slug'         => 'general',
					'toggle_slug'      => 'main_content',
					'description'      => esc_html__( 'Select Categories. If no category is selected, all categories will be displayed.', 'divi-plus' ),
					'computed_affects' => array(
						'__products_data',
					),
				),
				'order_by' => array(
					'label'            => esc_html__( 'Order by', 'divi-plus' ),
					'type'             => 'select',
					'option_category'  => 'configuration',
					'options'          => array(
						'name'     		=> esc_html__( 'Name', 'divi-plus' ),
						'slug'      	=> esc_html__( 'Slug', 'divi-plus' ),
						'term_id'   	=> esc_html__( 'Term Id', 'divi-plus' ),
						'count'			=> esc_html__( 'Count', 'divi-plus' ),
						'menu_order'	=> esc_html__( 'Menu Order', 'divi-plus' ),
					),
					'default'          => 'name',
					'tab_slug'         => 'general',
					'toggle_slug'      => 'main_content',
					'description'      => esc_html__( 'Here you can specify the order in which the categories will be displayed.', 'divi-plus' ),
					'computed_affects' => array(
						'__products_data',
					),
				),
				'order' => array(
					'label'            => esc_html__( 'Order', 'divi-plus' ),
					'type'             => 'select',
					'option_category'  => 'configuration',
					'options'          => array(
						'DESC' => esc_html__( 'DESC', 'divi-plus' ),
						'ASC'  => esc_html__( 'ASC', 'divi-plus' ),
					),
					'default'          => 'ASC',
					'tab_slug'         => 'general',
					'toggle_slug'      => 'main_content',
					'description'      => esc_html__( 'Here you can specify the sorting order for the categories.', 'divi-plus' ),
					'computed_affects' => array(
						'__products_data',
					),
				),
				'no_result_text' => array(
					'label'            => esc_html__( 'No Result Text', 'divi-plus' ),
					'type'             => 'text',
					'option_category'  => 'configuration',
					'default'		   => esc_html__( 'The categories you requested could not be found. Try changing your module settings or create some new categories.', 'divi-plus' ),
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
	                    '4'			=> esc_html( '4' ),
	                    '5'			=> esc_html( '5' ),
	                    '6'			=> esc_html( '6' ),
	                ),
	                'mobile_options'	=> true,
	                'default'			=> '4',
	                'default_on_front'	=> '4',
	                'tab_slug'          => 'general',
	                'toggle_slug'       => 'display_setting',
	                'description'       => esc_html__( 'Here you can select the number of columns to display categories.', 'divi-plus' ),
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
					'toggle_slug'     	=> 'display_setting',
					'description'       => esc_html__( 'Increase or decrease spacing between columns.', 'divi-plus' ),
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
					'default_on_front' => 'off',
					'tab_slug'         => 'general',
					'toggle_slug'      => 'display_setting',
					'description'      => esc_html__( 'Enable Masonry for categories.', 'divi-plus' ),
					'computed_affects' => array(
						'__products_data',
					),
				),
				'show_thumbnail' => array(
					'label'           => esc_html__( 'Show Thumbnail', 'divi-plus' ),
					'type'            => 'yes_no_button',
					'option_category' => 'configuration',
					'options'         => array(
						'on'  => esc_html__( 'Yes', 'divi-plus' ),
						'off' => esc_html__( 'No', 'divi-plus' ),
					),
					'default'         => 'on',
					'tab_slug'        => 'general',
					'toggle_slug'     => 'display_setting',
					'description'     => esc_html__( 'Choose whether or not the product thumbnail should be visible.', 'divi-plus' ),
					'computed_affects' => array(
						'__products_data',
					),
				),
				'thumbnail_size' => array(
					'label'            => esc_html__( 'Thumbnail Size', 'divi-plus' ),
					'type'             => 'select',
					'option_category'  => 'configuration',
					'options'          => array(
						'woocommerce_thumbnail'	=> esc_html__( 'Woocommerce Thumbnail', 'divi-plus' ),
						'woocommerce_single'	=> esc_html__( 'Woocommerce Single', 'divi-plus' ),
					),
					'default'          => 'woocommerce_thumbnail',
					'default_on_front' => 'woocommerce_thumbnail',
					'show_if'      	   => array(
						'show_thumbnail' => 'on',
					),
					'tab_slug'         => 'general',
					'toggle_slug'      => 'display_setting',
					'description'      => esc_html__( 'Here you can specify the size of category image.', 'divi-plus' ),
					'computed_affects' => array(
						'__products_data',
					),
				),
				'show_product_count' => array(
					'label'           => esc_html__( 'Show Product Count', 'divi-plus' ),
					'type'            => 'yes_no_button',
					'option_category' => 'configuration',
					'options'         => array(
						'on'  => esc_html__( 'Yes', 'divi-plus' ),
						'off' => esc_html__( 'No', 'divi-plus' ),
					),
					'default'         => 'on',
					'tab_slug'        => 'general',
					'toggle_slug'     => 'display_setting',
					'description'     => esc_html__( 'Choose whether or not the product count should be visible.', 'divi-plus' ),
					'computed_affects' => array(
						'__products_data',
					),
				),
				'show_content_on_hover' => array(
					'label'           => esc_html__( 'Show Content on Hover', 'divi-plus' ),
					'type'            => 'yes_no_button',
					'option_category' => 'configuration',
					'options'         => array(
						'on'  => esc_html__( 'Yes', 'divi-plus' ),
						'off' => esc_html__( 'No', 'divi-plus' ),
					),
					'show_if' 		  => array(
						'category_layout' => 'layout2',
					),
					'default'         => 'off',
					'tab_slug'        => 'general',
					'toggle_slug'     => 'display_setting',
					'description'     => esc_html__( 'Choose whether or not the content should show on hover.', 'divi-plus' ),
				),
				'equal_height' => array(
					'label'           => esc_html__( 'Equal Height', 'divi-plus' ),
					'type'            => 'yes_no_button',
					'option_category' => 'configuration',
					'options'         => array(
						'on'  => esc_html__( 'Yes', 'divi-plus' ),
						'off' => esc_html__( 'No', 'divi-plus' ),
					),
					'show_if' 		  => array(
						'category_layout' => 'layout1',
					),
					'default'         => 'off',
					'tab_slug'        => 'general',
					'toggle_slug'     => 'display_setting',
					'description'     => esc_html__( 'Choose whether or not to equalize column height when category text exceeds.', 'divi-plus' ),
				),
				'category_content_bg_color' => array(
	                'label'                 => esc_html__( 'Category Content Background', 'divi-plus' ),
	                'type'                  => 'background-field',
	                'base_name'             => 'category_content_bg',
	                'context'               => 'category_content_bg_color',
	                'option_category'       => 'button',
	                'custom_color'          => true,
	                'background_fields'     => $this->generate_background_options( 'category_content_bg', 'button', 'general', 'background', 'category_content_bg_color' ),
	                'hover'                 => 'tabs',
	                'tab_slug'              => 'general',
	                'toggle_slug'           => 'background',
	                'description'           => esc_html__( 'Customize the background style of the product by adjusting the background color, gradient, and image.', 'divi-plus' ),
	            ),
				'__products_data' => array(
					'type'                => 'computed',
					'computed_callback'   => array( 'DIPL_WooProductsCategories', 'get_products_data' ),
					'computed_depends_on' => array(
						'category_layout',
						'include_categories',
						'order_by',
						'order',
						'use_masonry',
						'show_thumbnail',
						'thumbnail_size',
						'show_product_count',
						'title_level',
					),
				),
			),
			$this->generate_background_options( 'category_content_bg', 'skip', 'general', 'background', 'category_content_bg_color' )
		);
	}

	/**
	 * This function return values to react for front end builder.
	 *
	 * @param array arguments to get products data
	 * @return array
	 * */
	public static function get_products_data( $args = array(), $conditional_tags = array(), $current_page = array() ) {
		
		$defaults = array(
			'category_layout' => 'layout1',
			'include_categories' => '',
			'order_by' => 'name',
			'order'	=> 'ASC',
			'use_masonry' => 'off',
			'show_thumbnail' => 'on',
			'thumbnail_size' => 'woocommerce_thumbnail',
			'show_product_count' => 'on',
			'title_level' => 'h4',
		);

		$args = wp_parse_args( $args, $defaults );

		foreach ( $defaults as $key => $default ) {
			${$key} = sanitize_text_field( et_()->array_get( $args, $key, $default ) );
		}

		$processed_title_level 	= et_pb_process_header_level( $title_level, 'h4' );
		$processed_title_level	= esc_html( $processed_title_level );

		$cat_args = array(
		    'orderby'    => $order_by,
		    'order'      => $order,
		    'include'    => $include_categories,
		    'hide_empty' => true,
		);

		$product_categories = get_terms( 'product_cat', $cat_args );

		if ( $product_categories && ! is_wp_error( $product_categories ) ) {

			$output = sprintf(
				'<div class="dipl_woo_products_category_layout %1$s">',
				esc_attr( $category_layout )
			);

			if ( 'on' === $use_masonry ) {
				$output .= '<div class="dipl_woo_products_category_isotope_container">';
				$output .= '<div class="dipl_woo_products_category_isotope_item_gutter"></div>';
			}

			foreach( $product_categories as $product_category ) {
				
				$output .= '<div class="dipl_woo_products_category_isotope_item">';

				if ( file_exists( get_stylesheet_directory() . '/divi-plus/layouts/woo-products-categories/' . sanitize_file_name( $category_layout ) . '.php' ) ) {
					include get_stylesheet_directory() . '/divi-plus/layouts/woo-products-categories/' . sanitize_file_name( $category_layout ) . '.php';
				} elseif ( file_exists( plugin_dir_path( __FILE__ ) . 'layouts/' . sanitize_file_name( $category_layout ) . '.php' ) ) {
					include plugin_dir_path( __FILE__ ) . 'layouts/' . sanitize_file_name( $category_layout ) . '.php';
				}

				$output .= '</div>';
			}

			if ( 'on' === $use_masonry ) {
				$output .= '</div>';
			}

			$output .= '</div>';

		} else {
			$output = '';
		}

		return et_core_intentionally_unescaped( $output, 'html' );
	}

	public function render( $attrs, $content, $render_slug ) {

		$category_layout 					= $this->props['category_layout'];
		$include_categories 				= $this->props['include_categories'];
		$order_by 							= $this->props['order_by'];
		$order 								= $this->props['order'];
		$no_result_text						= $this->props['no_result_text'];
		$number_of_columns 					= $this->props['number_of_columns'];
		$column_spacing						= $this->props['column_spacing'];
		$use_masonry						= $this->props['use_masonry'];
		$show_thumbnail						= $this->props['show_thumbnail'];
		$thumbnail_size						= $this->props['thumbnail_size'];
		$show_product_count 				= $this->props['show_product_count'];
		$show_content_on_hover				= $this->props['show_content_on_hover'];
		$title_level						= $this->props['title_level'];
		$processed_title_level 				= et_pb_process_header_level( $title_level, 'h4' );
		$processed_title_level				= esc_html( $processed_title_level );

		wp_enqueue_script( 'dipl-woo-products-categories-custom', PLUGIN_PATH . 'includes/modules/WooProductsCategories/dipl-woo-products-categories-custom.min.js', array('jquery'), '1.0.0', true );
		$file = et_is_builder_plugin_active() ? 'style-dbp' : 'style';
        wp_enqueue_style( 'dipl-woo-products-categories-style', PLUGIN_PATH . 'includes/modules/WooProductsCategories/' . $file . '.min.css', array(), '1.0.0' );

		$cat_args = array(
		    'orderby'    => sanitize_text_field( $order_by ),
		    'order'      => sanitize_text_field( $order ),
		    'include'    => sanitize_text_field( $include_categories ),
		    'hide_empty' => true,
		);

		$product_categories = get_terms( 'product_cat', $cat_args );

		if ( $product_categories && ! is_wp_error( $product_categories ) ) {

			$output = sprintf(
				'<div class="dipl_woo_products_category_layout %1$s">',
				esc_attr( $category_layout )
			);

			if ( 'on' === $use_masonry ) {
				wp_enqueue_script( 'elicus-images-loaded-script' );
				wp_enqueue_script( 'elicus-isotope-script' );

				$output .= '<div class="dipl_woo_products_category_isotope_container">';
				$output .= '<div class="dipl_woo_products_category_isotope_item_gutter"></div>';
			}

			foreach( $product_categories as $product_category ) {
				
				$output .= '<div class="dipl_woo_products_category_isotope_item">';

				if ( file_exists( get_stylesheet_directory() . '/divi-plus/layouts/woo-products-categories/' . sanitize_file_name( $category_layout ) . '.php' ) ) {
					include get_stylesheet_directory() . '/divi-plus/layouts/woo-products-categories/' . sanitize_file_name( $category_layout ) . '.php';
				} elseif ( file_exists( plugin_dir_path( __FILE__ ) . 'layouts/' . sanitize_file_name( $category_layout ) . '.php' ) ) {
					include plugin_dir_path( __FILE__ ) . 'layouts/' . sanitize_file_name( $category_layout ) . '.php';
				}

				$output .= '</div>';
			}

			if ( 'on' === $use_masonry ) {
				$output .= '</div>';
			}

			$output .= '</div>';

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

			et_pb_responsive_options()->generate_responsive_css( $width, '%%order_class%% .dipl_woo_products_category_isotope_item', 'width', $render_slug, '!important;', 'range' );
			et_pb_responsive_options()->generate_responsive_css( $column_spacing, '%%order_class%% .dipl_woo_products_category_isotope_item', array( 'margin-bottom' ), $render_slug, '!important;', 'range' );

			if ( 'on' === $use_masonry ) {
				et_pb_responsive_options()->generate_responsive_css( $column_spacing, '%%order_class%% .dipl_woo_products_category_isotope_item_gutter', 'width', $render_slug, '!important;', 'range' );
			} else {
				foreach ( $number_of_columns as $device => $cols ) {
					if ( 'desktop' === $device ) {
						self::set_style( $render_slug, array(
		                    'selector'    => '%%order_class%% .dipl_woo_products_category_isotope_item:not(:nth-child(' . absint( $cols ) . 'n+' . absint( $cols ) . '))',
		                    'declaration' => sprintf( 'margin-right: %1$s;', esc_attr( $column_spacing['desktop'] ) ),
		                    'media_query' => self::get_media_query( 'min_width_981' ),
		                ) );
		                if ( '' !== $cols ) {
							self::set_style( $render_slug, array(
			                    'selector'    => '%%order_class%% .dipl_woo_products_category_isotope_item:nth-child(' . absint( $cols ) . 'n+1)',
			                    'declaration' => 'clear: left;',
			                    'media_query' => self::get_media_query( 'min_width_981' ),
			                ) );
						}
					} else if ( 'tablet' === $device ) {
						self::set_style( $render_slug, array(
		                    'selector'    => '%%order_class%% .dipl_woo_products_category_isotope_item:not(:nth-child(' . absint( $cols ) . 'n+' . absint( $cols ) . '))',
		                    'declaration' => sprintf( 'margin-right: %1$s;', esc_attr( $column_spacing['tablet'] ) ),
		                    'media_query' => self::get_media_query( '768_980' ),
		                ) );
		                if ( '' !== $cols ) {
							self::set_style( $render_slug, array(
			                    'selector'    => '%%order_class%% .dipl_woo_products_category_isotope_item:nth-child(' . absint( $cols ) . 'n+1)',
			                    'declaration' => 'clear: left;',
			                    'media_query' => self::get_media_query( '768_980' ),
			                ) );
						}
					} else if ( 'phone' === $device ) {
						self::set_style( $render_slug, array(
		                    'selector'    => '%%order_class%% .dipl_woo_products_category_isotope_item:not(:nth-child(' . absint( $cols ) . 'n+' . absint( $cols ) . '))',
		                    'declaration' => sprintf( 'margin-right: %1$s;', esc_attr( $column_spacing['phone'] ) ),
		                    'media_query' => self::get_media_query( 'max_width_767' ),
		                ) );
		                if ( '' !== $cols ) {
							self::set_style( $render_slug, array(
			                    'selector'    => '%%order_class%% .dipl_woo_products_category_isotope_item:nth-child(' . absint( $cols ) . 'n+1)',
			                    'declaration' => 'clear: left;',
			                    'media_query' => self::get_media_query( 'max_width_767' ),
			                ) );
						}
					}
				}
			}

			if ( 'layout1' === $this->props['category_layout'] && 'on' === $this->props['equal_height'] ) {
				self::set_style( $render_slug, array(
					'selector'    => '%%order_class%% .dipl_woo_products_category_layout',
					'declaration' => 'display: flex !important;flex-wrap: wrap;',
				) );
				self::set_style( $render_slug, array(
					'selector'    => '%%order_class%% .dipl_woo_product_category',
					'declaration' => 'height: 100%;display: flex;flex-wrap: wrap;flex-direction: column;',
				) );
				self::set_style( $render_slug, array(
					'selector'    => '%%order_class%% .dipl_woo_product_category_content',
					'declaration' => 'flex-wrap: wrap;justify-content: flex-end;display: flex;flex-direction: column;flex-grow: 1;',
				) );
			}

			if ( 'layout2' === $category_layout && 'on' === $show_content_on_hover ) {
				self::set_style( $render_slug, array(
                    'selector'    => '%%order_class%% .layout2 .dipl_woo_product_category_content',
                    'declaration' => 'visibility: hidden; opacity: 0; transform: translateY(15px); transition: all 500ms cubic-bezier(0.39, 0.58, 0.57, 1);',
                    'media_query' => self::get_media_query( 'min_width_981' ),
                ) );
                self::set_style( $render_slug, array(
                    'selector'    => '%%order_class%% .layout2 .dipl_woo_product_category:hover .dipl_woo_product_category_content',
                    'declaration' => 'visibility: visible; opacity: 1; transform: translateY(0);',
                    'media_query' => self::get_media_query( 'min_width_981' ),
                ) );
			}

			$args = array(
				'render_slug'	=> $render_slug,
				'props'			=> $this->props,
				'fields'		=> $this->fields_unprocessed,
				'module'		=> $this,
				'backgrounds' 	=> array(
					'category_content_bg' => array(
						'normal' => "{$this->main_css_element} .dipl_woo_product_category_content",
						'hover' => "{$this->main_css_element} .dipl_woo_products_category_isotope_item:hover .dipl_woo_product_category_content",
		 			),
				),
			);

			DiviPlusHelper::process_background( $args );

		} else {
			$output = '<div class="entry">' . esc_html( $no_result_text ) . '</div>';
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
	if (
		in_array( 'dipl_woo_products_categories', $modules, true ) &&
		et_is_woocommerce_plugin_active()
	) {
		new DIPL_WooProductsCategories();
	}
} else {
	if ( et_is_woocommerce_plugin_active() ) {
		new DIPL_WooProductsCategories();
	}
}