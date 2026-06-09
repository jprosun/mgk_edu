<?php
/**
 * @author      Elicus <hello@elicus.com>
 * @link        https://www.elicus.com/
 * @copyright   2023 Elicus Technologies Private Limited
 * @version     1.9.15
 */

class DIPL_WooProducts extends ET_Builder_Module {
	public $slug       = 'dipl_woo_products';
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
		$this->name             = esc_html__( 'DP Woo Products', 'divi-plus' );
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
					'display_setting' => array(
						'title' => esc_html__( 'Display', 'divi-plus' ),
					),
					'pagination' => array(
						'title' => esc_html__( 'Pagination', 'divi-plus' ),
					),
				),
			),
			'advanced' => array(
				'toggles' => array(
					'title_text' => array(
						'title' => esc_html__( 'Title', 'divi-plus' ),
					),
					'price_text' => array(
						'title' => esc_html__( 'Price', 'divi-plus' ),
						 'sub_toggles' => array(
                            'regular_price_text' => array(
                                'name' => 'Regular Price',
                            ),
                            'sale_price_text' => array(
                                'name' => 'Sale Price',
                            ),
                        ),
                        'tabbed_subtoggles' => true,
					),
					'sale_badge' => array(
						'title' => esc_html__( 'Sale Badge', 'divi-plus' ),
					),
					'add_to_cart_button' => array (
						'title' => esc_html__( 'Add to Cart', 'divi-plus' ),
					),
					'out_of_stock' => array (
						'title' => esc_html__( 'Out of Stock', 'divi-plus' ),
					),
					'star_rating' => array (
						'title' => esc_html__( 'Star Rating', 'divi-plus' ),
					),
					'pagination' => array(
						'title' => esc_html__( 'Pagination', 'divi-plus' ),
					),
					'quickview_popup' => array(
						'title' => esc_html__( 'Quickview Lightbox', 'divi-plus' ),
					),
					'quickview_link' => array(
						'title' => esc_html__( 'Quickview Link', 'divi-plus' ),
					),
					'quickview_title_text' => array(
						'title' => esc_html__( 'Quickview Product Title', 'divi-plus' ),
					),
					'quickview_price_text' => array(
						'title' => esc_html__( 'Quickview Product Price', 'divi-plus' ),
						'sub_toggles' => array(
                            'regular_price_text' => array(
                                'name' => esc_html__( 'Regular Price', 'divi-plus' ),
                            ),
                            'sale_price_text' => array(
                                'name' => esc_html__( 'Sale Price', 'divi-plus' ),
                            ),
                        ),
                        'tabbed_subtoggles' => true,
					),
					'quickview_desc_text' => array(
						'title' => esc_html__( 'Quickview Product Description', 'divi-plus' ),
					),
					'quickview_quantity' => array(
						'title' => esc_html__( 'Quickview Product Quantity', 'divi-plus' ),
					),
					'quickview_add_to_cart_button' => array(
						'title' => esc_html__( 'Quickview Add to Cart', 'divi-plus' ),
					),
					'quickview_meta' => array(
						'title' => esc_html__( 'Quickview Product Meta', 'divi-plus' ),
						'sub_toggles' => array(
                            'label_text' => array(
                                'name' => esc_html__( 'Label', 'divi-plus' ),
                            ),
                            'value_text' => array(
                                'name' => esc_html__( 'Value', 'divi-plus' ),
                            ),
                        ),
                        'tabbed_subtoggles' => true,
					),
					'quickview_attributes' => array(
						'title' => esc_html__( 'Quickview Product Attributes', 'divi-plus' ),
						'sub_toggles' => array(
                            'label_text' => array(
                                'name' => esc_html__( 'Label', 'divi-plus' ),
                            ),
                            'field_text' => array(
                                'name' => esc_html__( 'Field', 'divi-plus' ),
                            ),
                        ),
                        'tabbed_subtoggles' => true,
					),
					'sorting_dropdown' => array(
						'title' => esc_html__( 'Sorting Dropdown', 'divi-plus' ),
					),
					'loader' => array(
						'title' => esc_html__( 'Loader', 'divi-plus' ),
					),
					'woocommerce_notice' => array(
						'title'             => esc_html__( 'Woocommerce Notice', 'divi-plus' ),
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
						),
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
						'main'       => "{$this->main_css_element} .dipl_single_woo_product_title, {$this->main_css_element} .dipl_single_woo_product_title a",
						'important' => 'all',
					),
					'tab_slug'	=> 'advanced',
                    'toggle_slug' => 'title_text',
				),
				'price' => array(
					'label'          => esc_html__( 'Regular Price', 'divi-plus' ),
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
						'main'  => "{$this->main_css_element} .dipl_single_woo_product_price, {$this->main_css_element} .dipl_single_woo_product_price span",
						'important' => 'all',
					),
					'tab_slug'	=> 'advanced',
                    'toggle_slug' => 'price_text',
                    'sub_toggle' => 'regular_price_text',
				),
				'sale_price' => array(
					'label'          => esc_html__( 'Sale Price', 'divi-plus' ),
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
						'main'  => "{$this->main_css_element} .dipl_single_woo_product_price ins span",
						'important' => 'all',
					),
					'tab_slug'	=> 'advanced',
                    'toggle_slug' => 'price_text',
                    'sub_toggle' => 'sale_price_text',
				),
				'sale' => array(
					'label'          => esc_html__( 'Sale Badge', 'divi-plus' ),
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
						'default'        => '1.2em',
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
					'hide_text_align' => true,
					'css' => array(
						'main'  => "{$this->main_css_element} .dipl_single_woo_product_sale_badge",
						'important' => 'all',
					),
					'tab_slug'	=> 'advanced',
                    'toggle_slug' => 'sale_badge',
				),
				'add_to_cart' => array(
					'label' => esc_html__( 'Add to Cart Button', 'divi-plus' ),
					'font_size' => array(
						'default'        => '20px',
						'range_settings' => array(
							'min'  => '1',
							'max'  => '100',
							'step' => '1',
						),
						'validate_unit'  => true,
					),
					'line_height' => array(
						'default'        => '1.2em',
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
					'text_align' => array(
						'default' => 'center',
					),
					'css' => array(
						'main' => "{$this->main_css_element} .dipl_single_woo_product_add_to_cart a.button, {$this->main_css_element} .dipl_single_woo_product_add_to_cart a.add_to_cart_button, {$this->main_css_element} .dipl_single_woo_product_add_to_cart a.added_to_cart",
						'important' => 'all',
					),
					'tab_slug'       => 'advanced',
					'toggle_slug'    => 'add_to_cart_button',
				),
				'out_of_stock_label' => array(
					'label'          => esc_html__( 'Out of Stock', 'divi-plus' ),
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
						'default'        => '1.2em',
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
					'text_color' => array(
						'default' => '#f00',
					),
					'hide_text_align' => true,
					'css' => array(
						'main'  => "{$this->main_css_element} .dipl_out_of_stock_label",
						'important' => 'all',
					),
					'tab_slug'	=> 'advanced',
                    'toggle_slug' => 'out_of_stock',
				),
				'sorting_dropdown_text' => array(
					'label' => esc_html__( 'Sorting Dropdown', 'divi-plus' ),
					'font_size' => array(
						'default'        => '14px',
						'range_settings' => array(
							'min'  => '1',
							'max'  => '100',
							'step' => '1',
						),
						'validate_unit'  => true,
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
					'hide_text_shadow' => true,
					'hide_line_height' => true,
					'options_priority' => array(
						'sorting_dropdown_text_text_color' => 1,
					),
					'css' => array(
						'main' => "{$this->main_css_element} .dipl_sorting_orderby",
						'important' => 'all',
					),
					'depends_on'        => array( 'show_sorting_dropdown' ),
                    'depends_show_if'   => 'on',
					'tab_slug'       => 'advanced',
					'toggle_slug'    => 'sorting_dropdown',
				),
				'star_rating_text' => array(
					'label'          => esc_html__( 'Star Rating', 'divi-plus' ),
					'hide_font'	=> true,
					'font_size' => array(
						'default'        => '14px',
						'range_settings' => array(
							'min'  => '1',
							'max'  => '100',
							'step' => '1',
						),
						'validate_unit'  => true,
					),
					'line_height' => array(
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
					'text_align' => array(
						'default' => 'center',
					),
					'hide_text_color' => true,
					'css' => array(
						'main'  => "{$this->main_css_element} .dipl_single_woo_product_star_rating .star-rating",
						'important' => 'all',
					),
					'tab_slug'	=> 'advanced',
                    'toggle_slug' => 'star_rating',
				),
				'loader_icon_text' => array(
					'label'          => esc_html__( 'Loader Icon', 'divi-plus' ),
					'font_size'      => array(
						'default'        => '36px',
						'range_settings' => array(
							'min'  => '1',
							'max'  => '100',
							'step' => '1',
						),
						'validate_unit'  => true,
					),
					'text_color' => array(
						'default' => '#000',
					),
					'hide_font' => true,
					'hide_text_shadow' => true,
					'hide_text_align' => true,
					'hide_line_height' => true,
					'hide_letter_spacing' => true,
					'css' => array(
						'main'  => "{$this->main_css_element} .dipl_product_lightbox_loader:after",
						'important' => 'all',
					),
					'tab_slug'	=> 'advanced',
                    'toggle_slug' => 'loader',
				),
				'woo_notice_p' => array(
					'label'          => esc_html__( 'Notice', 'divi-plus' ),
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
					'hide_text_align' => true,
					'css' => array(
						'main'  => "{$this->main_css_element} .woocommerce-message",
						'important' => 'all',
					),
					'tab_slug'	=> 'advanced',
                    'toggle_slug' => 'woocommerce_notice',
                    'sub_toggle' => 'p',
				),
				'woo_notice_a' => array(
					'label'          => esc_html__( 'Notice Link', 'divi-plus' ),
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
					'hide_text_align' => true,
					'css' => array(
						'main'  => "{$this->main_css_element} .woocommerce-message a",
						'important' => 'all',
					),
					'tab_slug'	=> 'advanced',
                    'toggle_slug' => 'woocommerce_notice',
                    'sub_toggle' => 'a',
				),
				'quickview_button' => array(
					'label'          => esc_html__( 'Quickview Link', 'divi-plus' ),
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
						'default'        => '1.2em',
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
					'text_align' => array(
						'default' => 'center',
					),
					'css' => array(
						'main'  => "{$this->main_css_element} .dipl_single_woo_product_quickview_wrapper a",
						'important' => 'all',
					),
					'tab_slug'	=> 'advanced',
                    'toggle_slug' => 'quickview_link',
				),
				'quickview_title' => array(
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
						'default' => 'h2',
					),
					'text_align'     => array(
						'default' => 'left',
					),
					'css'            => array(
						'main'       => "{$this->main_css_element}_lightbox .dipl_single_woo_product_title, {$this->main_css_element}_lightbox .dipl_single_woo_product_title a",
						'important' => 'all',
					),
					'tab_slug'	=> 'advanced',
                    'toggle_slug' => 'quickview_title_text',
				),
				'quickview_price' => array(
					'label'          => esc_html__( 'Regular Price', 'divi-plus' ),
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
						'default' => 'left',
					),
					'css' => array(
						'main'  => "{$this->main_css_element}_lightbox .dipl_single_woo_product_price, {$this->main_css_element}_lightbox .dipl_single_woo_product_price span",
						'important' => 'all',
					),
					'tab_slug'	=> 'advanced',
                    'toggle_slug' => 'quickview_price_text',
                    'sub_toggle' => 'regular_price_text',
				),
				'quickview_sale_price' => array(
					'label'          => esc_html__( 'Sale Price', 'divi-plus' ),
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
					'hide_text_align' => true,
					'css' => array(
						'main'  => "{$this->main_css_element}_lightbox .dipl_single_woo_product_price del span",
						'important' => 'all',
					),
					'tab_slug'	=> 'advanced',
                    'toggle_slug' => 'quickview_price_text',
                    'sub_toggle' => 'sale_price_text',
				),
				'quickview_desc' => array(
					'label'          => esc_html__( 'Short Description', 'divi-plus' ),
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
						'default' => 'left',
					),
					'css'            => array(
						'main'       => "{$this->main_css_element}_lightbox .dipl_single_woo_product_desc",
						'important' => 'all',
					),
					'tab_slug'	=> 'advanced',
                    'toggle_slug' => 'quickview_desc_text',
				),
				'quickview_quantity_text' => array(
					'label'          => esc_html__( 'Quantity', 'divi-plus' ),
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
					'css'            => array(
						'main'       => "{$this->main_css_element}_lightbox .product .dipl_product_lightbox_content_wrapper .qty",
						'important' => 'all',
					),
					'tab_slug'	=> 'advanced',
                    'toggle_slug' => 'quickview_quantity',
				),
				'quickview_add_to_cart' => array(
					'label' => esc_html__( 'Add to Cart Button', 'divi-plus' ),
					'font_size' => array(
						'default'        => '20px',
						'range_settings' => array(
							'min'  => '1',
							'max'  => '100',
							'step' => '1',
						),
						'validate_unit'  => true,
					),
					'line_height' => array(
						'default'        => '1.2em',
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
					'hide_text_align' => true,
					'css' => array(
						'main' => "{$this->main_css_element}_lightbox .product .dipl_product_lightbox_content_wrapper .single_add_to_cart_button, {$this->main_css_element}_lightbox .product .dipl_product_lightbox_content_wrapper .single_add_to_cart_button.added_to_cart",
						'important' => 'all',
					),
					'tab_slug'       => 'advanced',
					'toggle_slug'    => 'quickview_add_to_cart_button',
				),
				'quickview_meta_label' => array(
					'label' => esc_html__( 'Label', 'divi-plus' ),
					'font_size' => array(
						'default'        => '14px',
						'range_settings' => array(
							'min'  => '1',
							'max'  => '100',
							'step' => '1',
						),
						'validate_unit'  => true,
					),
					'line_height' => array(
						'default'        => '1.2em',
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
					'hide_text_align' => true,
					'css' => array(
						'main' => "{$this->main_css_element}_lightbox .product .dipl_product_lightbox_content_wrapper .product_meta label",
						'important' => 'all',
					),
					'tab_slug'       => 'advanced',
					'toggle_slug'    => 'quickview_meta',
					'sub_toggle'	 => 'label_text',
				),
				'quickview_meta_value' => array(
					'label' => esc_html__( 'Value', 'divi-plus' ),
					'font_size' => array(
						'default'        => '14px',
						'range_settings' => array(
							'min'  => '1',
							'max'  => '100',
							'step' => '1',
						),
						'validate_unit'  => true,
					),
					'line_height' => array(
						'default'        => '1.2em',
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
					'hide_text_align' => true,
					'css' => array(
						'main' => "{$this->main_css_element}_lightbox .product .dipl_product_lightbox_content_wrapper .product_meta > span span, {$this->main_css_element}_lightbox .product .dipl_product_lightbox_content_wrapper .product_meta > span a",
						'important' => 'all',
					),
					'tab_slug'       => 'advanced',
					'toggle_slug'    => 'quickview_meta',
					'sub_toggle'	 => 'value_text',
				),
				'quickview_attributes_label' => array(
					'label'          => esc_html__( 'Attribute Label', 'divi-plus' ),
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
					'hide_text_align' => true,
					'css' => array(
						'main'  => "{$this->main_css_element}_lightbox .dipl_product_lightbox_content_wrapper .variations label",
						'important' => 'all',
					),
					'tab_slug'	=> 'advanced',
                    'toggle_slug' => 'quickview_attributes',
                    'sub_toggle' => 'label_text',
				),
				'quickview_attributes_field' => array(
					'label'          => esc_html__( 'Attribute Field', 'divi-plus' ),
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
					'hide_text_align' => true,
					'css' => array(
						'main'  => "{$this->main_css_element}_lightbox .product .dipl_product_lightbox_content_wrapper .cart .variations select",
						'important' => 'all',
					),
					'tab_slug'	=> 'advanced',
                    'toggle_slug' => 'quickview_attributes',
                    'sub_toggle' => 'field_text',
				),
			),
			'borders' => array(
				'product' => array(
					'label_prefix'    => esc_html__( 'Product', 'divi-plus' ),
					'css' => array(
						'main' => array(
							'border_radii'  => '%%order_class%% .dipl_single_woo_product',
							'border_styles' => '%%order_class%% .dipl_single_woo_product',
							'important' => 'all',
						),
					),
				),
				'sale_badge' => array(
					'label_prefix'    => esc_html__( 'Sale Badge', 'divi-plus' ),
					'css' => array(
						'main' => array(
							'border_radii'  => '%%order_class%% .dipl_single_woo_product_sale_badge',
							'border_styles' => '%%order_class%% .dipl_single_woo_product_sale_badge',
							'important' => 'all',
						),
					),
					'tab_slug' => 'advanced',
					'toggle_slug' => 'sale_badge',
				),
				'add_to_cart_button' => array(
					'label_prefix'    => esc_html__( 'Add to Cart Button', 'divi-plus' ),
					'css' => array(
						'main' => array(
							'border_radii'  => '%%order_class%% .dipl_single_woo_product_add_to_cart a.button, %%order_class%% .dipl_single_woo_product_add_to_cart a.add_to_cart_button, %%order_class%% .dipl_single_woo_product_add_to_cart a.added_to_cart',
							'border_styles' => '%%order_class%% .dipl_single_woo_product_add_to_cart a.button, %%order_class%% .dipl_single_woo_product_add_to_cart a.add_to_cart_button, %%order_class%% .dipl_single_woo_product_add_to_cart a.added_to_cart',
						),
						'important' => 'all',
					),
					'tab_slug' => 'advanced',
					'toggle_slug' => 'add_to_cart_button',
				),
				'quickview_add_to_cart_button' => array(
					'label_prefix'    => esc_html__( 'Add to Cart Button', 'divi-plus' ),
					'css' => array(
						'main' => array(
							'border_radii'  => "{$this->main_css_element}_lightbox .product .dipl_product_lightbox_content_wrapper .single_add_to_cart_button, {$this->main_css_element}_lightbox .product .dipl_product_lightbox_content_wrapper .single_add_to_cart_button.added_to_cart",
							'border_styles' => "{$this->main_css_element}_lightbox .product .dipl_product_lightbox_content_wrapper .single_add_to_cart_button, {$this->main_css_element}_lightbox .product .dipl_product_lightbox_content_wrapper .single_add_to_cart_button.added_to_cart",
							'important' => 'all',
						),
						'important' => 'all',
					),
					'tab_slug' => 'advanced',
					'toggle_slug' => 'quickview_add_to_cart_button',
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
				'product' => array(
					'label'       => esc_html__( 'Product Box Shadow', 'divi-plus' ),
					'css'         => array(
						'main' => "%%order_class%% .dipl_single_woo_product",
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
			'sorting_dropdown_margin_padding' => array(
				'sorting_dropdown' => array(
					'margin_padding' => array(
						'css' => array(
							'margin' 		=> "{$this->main_css_element} .dipl_woo_products_ordering",
							'padding'		=> "{$this->main_css_element} .dipl_sorting_orderby",
							'important'	 	=> 'all',
						),
					),
				),
			),
			'add_to_cart_margin_padding' => array(
				'add_to_cart' => array(
					'margin_padding' => array(
						'css' => array(
							'use_margin' 	=> false,
							'padding'		=> "{$this->main_css_element} .dipl_single_woo_product_add_to_cart a.add_to_cart_button, {$this->main_css_element} .dipl_single_woo_product_add_to_cart a.added_to_cart, {$this->main_css_element} .dipl_single_woo_product_add_to_cart a.button",
							'important'	 	=> 'all',
						),
					),
				),
			),
			'product_margin_padding' => array(
				'product_content' => array(
					'margin_padding' => array(
						'css' => array(
							'use_margin' 	=> false,
							'padding'		=> "{$this->main_css_element} .dipl_single_woo_product_content",
							'important'	 	=> 'all',
						),
					),
				),
				'product_image' => array(
					'margin_padding' => array(
						'css' => array(
							'use_margin' 	=> false,
							'padding'		=> "{$this->main_css_element} .dipl_single_woo_product_thumbnail",
							'important' 	=> 'all',
						),
					),
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
				'view_type' => array(
					'label'            => esc_html__( 'Product View Type', 'divi-plus' ),
					'type'             => 'select',
					'option_category'  => 'basic_option',
					'options'          => array(
						'default'          => esc_html__( 'Default', 'divi-plus' ),
						'featured'         => esc_html__( 'Featured Products', 'divi-plus' ),
						'sale'             => esc_html__( 'Sale Products', 'divi-plus' ),
						'best_selling'     => esc_html__( 'Best Selling Products', 'divi-plus' ),
						'top_rated'        => esc_html__( 'Top Rated Products', 'divi-plus' ),
					),
					'default_on_front' => 'default',
					'tab_slug'         => 'general',
					'toggle_slug'      => 'main_content',
					'description'      => esc_html__( 'Choose which type of product view you would like to display.', 'divi-plus' ),
					'computed_affects' => array(
						'__products_data',
					),
				),
				'use_current_loop' => array(
					'label'            => esc_html__( 'Use Current Loop', 'divi-plus' ),
					'type'             => 'yes_no_button',
					'option_category'  => 'configuration',
					'options'          => array(
						'on'  => esc_html__( 'Yes', 'divi-plus' ),
						'off' => esc_html__( 'No', 'divi-plus' ),
					),
					'default'          => 'off',
					'default_on_front' => 'off',
					'show_if'          => array(
						'function.isTBLayout' => 'on',
					),
					'tab_slug'         => 'general',
					'toggle_slug'      => 'main_content',
					'description'      => esc_html__( 'Only include products for the current page. Useful on archive and index pages. For example let\'s say you used this module on a Theme Builder layout that is enabled for product categories. Selecting the "Sale Products" view type above and enabling this option would show only products that are on sale when viewing product categories.', 'divi-plus' ),
					'computed_affects' => array(
						'__products_data',
					),
				),
				'product_layout' => array(
	                'label' 			=> esc_html__( 'Layout', 'divi-plus' ),
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
	                'description'       => esc_html__( 'Here you can select the layout to display products.', 'divi-plus' ),
	                'computed_affects' => array(
						'__products_data',
					),
	            ),
				'products_number' => array(
					'label'            => esc_html__( 'Number of Products', 'divi-plus' ),
					'type'             => 'text',
					'option_category'  => 'configuration',
					'default'          => 10,
					'tab_slug'         => 'general',
					'toggle_slug'      => 'main_content',
					'description'      => esc_html__( 'Here you can specify the total number of products to display.', 'divi-plus' ),
					'computed_affects' => array(
						'__products_data',
					),
				),
				'offset_number' => array(
	                'label'            => esc_html__( 'Offset Number', 'divi-plus' ),
	                'type'             => 'text',
	                'option_category'  => 'configuration',
	                'default'          => 0,
	                'tab_slug'         => 'general',
	                'toggle_slug'      => 'main_content',
	                'description'      => esc_html__( 'Choose how many products you would like to skip. These products will not be shown.', 'divi-plus' ),
	                'computed_affects' => array(
	                    '__products_data',
	                ),
	            ),
				'products_order_by' => array(
					'label'            => esc_html__( 'Order by', 'divi-plus' ),
					'type'             => 'select',
					'option_category'  => 'configuration',
					'options'          => array(
						'date'     		=> esc_html__( 'Date', 'divi-plus' ),
						'modified'		=> esc_html__( 'Modified Date', 'divi-plus' ),
						'menu_order'	=> esc_html__( 'Menu Order', 'divi-plus' ),
						'title'    		=> esc_html__( 'Title', 'divi-plus' ),
						'name'     		=> esc_html__( 'Slug', 'divi-plus' ),
						'ID'       		=> esc_html__( 'ID', 'divi-plus' ),
						'rand'     		=> esc_html__( 'Random', 'divi-plus' ),
						'price'			=> esc_html__( 'Price', 'divi-plus' ),
						'stock_status'	=> esc_html__( 'Stock Status', 'divi-plus' ),
						'none'     		=> esc_html__( 'None', 'divi-plus' ),
					),
					'default'          => 'date',
					'show_if_not'      => array(
						'view_type' => array( 'best_selling', 'top_rated', 'featured' ),
					),
					'tab_slug'         => 'general',
					'toggle_slug'      => 'main_content',
					'description'      => esc_html__( 'Here you can specify the order in which the products will be displayed.', 'divi-plus' ),
					'computed_affects' => array(
						'__products_data',
					),
				),
				'products_order' => array(
					'label'            => esc_html__( 'Order', 'divi-plus' ),
					'type'             => 'select',
					'option_category'  => 'configuration',
					'options'          => array(
						'DESC' => esc_html__( 'DESC', 'divi-plus' ),
						'ASC'  => esc_html__( 'ASC', 'divi-plus' ),
					),
					'default'          => 'DESC',
					'show_if_not'      => array(
						'view_type' => array( 'best_selling', 'top_rated', 'featured' ),
					),
					'tab_slug'         => 'general',
					'toggle_slug'      => 'main_content',
					'description'      => esc_html__( 'Here you can specify the sorting order for the products.', 'divi-plus' ),
					'computed_affects' => array(
						'__products_data',
					),
				),
				'hide_out_of_stock' => array(
					'label'            => esc_html__( 'Hide Out of Stock Products', 'divi-plus' ),
					'type'             => 'yes_no_button',
					'option_category'  => 'configuration',
					'options'          => array(
						'on'  => esc_html__( 'Yes', 'divi-plus' ),
						'off' => esc_html__( 'No', 'divi-plus' ),
					),
					'default'          => 'off',
					'default_on_front' => 'off',
					'tab_slug'         => 'general',
					'toggle_slug'      => 'main_content',
					'description'      => esc_html__( 'Hide out of stock products from the loop.', 'divi-plus' ),
					'computed_affects' => array(
						'__products_data',
					),
				),
				'enable_out_of_stock_label' => array(
					'label'            => esc_html__( 'Display Out of Stock Label', 'divi-plus' ),
					'type'             => 'yes_no_button',
					'option_category'  => 'configuration',
					'options'          => array(
						'on'  => esc_html__( 'Yes', 'divi-plus' ),
						'off' => esc_html__( 'No', 'divi-plus' ),
					),
					'show_if'		  => array(
						'hide_out_of_stock' => 'off',
					),
					'default'          => 'off',
					'tab_slug'         => 'general',
					'toggle_slug'      => 'main_content',
					'description'      => esc_html__( 'Display Out of Stock label on products.', 'divi-plus' ),
					'computed_affects' => array(
						'__products_data',
					),
				),
				'out_of_stock_label' => array(
					'label'            => esc_html__( 'Out of Stock Label Text', 'divi-plus' ),
					'type'             => 'text',
					'option_category'  => 'configuration',
					'show_if'		  => array(
						'hide_out_of_stock' => 'off',
						'enable_out_of_stock_label' => 'on',
					),
					'default'		   => esc_html__( 'Out of Stock', 'divi-plus' ),
					'tab_slug'         => 'general',
					'toggle_slug'      => 'main_content',
					'description'      => esc_html__( 'Here you can specify the text for out of stock label.', 'divi-plus' ),
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
					'show_if'      	   => array(
						'use_current_loop' => 'off',
					),
					'tab_slug'         => 'general',
					'toggle_slug'      => 'main_content',
					'description'      => esc_html__( 'Select Categories. If no category is selected, products from all categories will be displayed.', 'divi-plus' ),
					'computed_affects' => array(
						'__products_data',
					),
				),
				'include_tags' => array(
					'label'            => esc_html__( 'Include Tags', 'divi-plus' ),
					'type'             => 'categories',
					'renderer_options' => array(
						'use_terms' => true,
						'term_name' => 'product_tag',
						'field_name' => 'et_pb_include_dipl_product_tags',
					),
					'show_if'      	   => array(
						'use_current_loop' => 'off',
					),
					'tab_slug'         => 'general',
					'toggle_slug'      => 'main_content',
					'description'      => esc_html__( 'Select Tags. If no tag is selected, products from all tags will be displayed.', 'divi-plus' ),
					'computed_affects' => array(
						'__products_data',
					),
				),
				'taxonomies_relation' => array(
					'label'            => esc_html__( 'Taxonomies Relation', 'divi-plus' ),
					'type'             => 'select',
					'option_category'  => 'configuration',
					'options'          => array(
						'OR'   	=> esc_html__( 'OR', 'divi-plus' ),
						'AND'	=> esc_html__( 'AND', 'divi-plus' ),
					),
					'default'          => 'OR',
					'show_if'		   => array(
						'use_current_loop' => 'off',
					),
					'tab_slug'         => 'general',
					'toggle_slug'      => 'main_content',
					'description'      => esc_html__( 'This will set the relationship between taxonomies.', 'divi-plus' ),
					'computed_affects' => array(
						'__products_data',
					),
				),
				'no_result_text' => array(
					'label'            => esc_html__( 'No Result Text', 'divi-plus' ),
					'type'             => 'text',
					'option_category'  => 'configuration',
					'default'		   => esc_html__( 'The products you requested could not be found. Try changing your module settings or create some new products.', 'divi-plus' ),
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
	                'description'       => esc_html__( 'Here you can select the number of columns to display products.', 'divi-plus' ),
	                'computed_affects' => array(
						'__products_data',
					),
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
					'description'      => esc_html__( 'Enable Masonry for products.', 'divi-plus' ),
					'computed_affects' => array(
						'__products_data',
					),
				),
				'show_sorting_dropdown' => array(
					'label'           	=> esc_html__( 'Show Sorting Dropdown', 'divi-plus' ),
					'type'            	=> 'yes_no_button',
					'option_category' 	=> 'configuration',
					'options'         	=> array(
						'on'  => esc_html__( 'Yes', 'divi-plus' ),
						'off' => esc_html__( 'No', 'divi-plus' ),
					),
					'default'         	=> 'off',
					'affects'           => array(
						'sorting_dropdown_text',
						'sorting_dropdown_text_text_align',
						'sorting_dropdown_text_font',
						'sorting_dropdown_text_font_size',
						'sorting_dropdown_text_letter_spacing',
						'sorting_dropdown_text_line_height',
						'sorting_dropdown_text_text_color',
					),
					'tab_slug'        	=> 'general',
					'toggle_slug'     	=> 'display_setting',
					'description'     	=> esc_html__( 'Choose whether or not to display the sorting dropdown.', 'divi-plus' ),
					'computed_affects' 	=> array(
						'__products_data',
					),
				),
				'enable_quickview' => array(
					'label'           => esc_html__( 'Display Quickview Link', 'divi-plus' ),
					'type'            => 'yes_no_button',
					'option_category' => 'configuration',
					'options'         => array(
						'on'  => esc_html__( 'Yes', 'divi-plus' ),
						'off' => esc_html__( 'No', 'divi-plus' ),
					),
					'default'         => 'off',
					'tab_slug'        => 'general',
					'toggle_slug'     => 'display_setting',
					'description'     => esc_html__( 'Choose whether or not the product quickview link should be displayed or not.', 'divi-plus' ),
					'computed_affects' => array(
						'__products_data',
					),
				),
				'quickview_link_text' => array(
					'label'            => esc_html__( 'Quickview Link Text', 'divi-plus' ),
					'type'             => 'text',
					'option_category'  => 'configuration',
					'show_if'		  => array(
						'enable_quickview' => 'on',
					),
					'default'		   => esc_html__( 'Quickview', 'divi-plus' ),
					'tab_slug'         => 'general',
					'toggle_slug'      => 'display_setting',
					'description'      => esc_html__( 'Here you can specify the text for quickview link.', 'divi-plus' ),
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
					'description'      => esc_html__( 'Here you can specify the size of product image.', 'divi-plus' ),
					'computed_affects' => array(
						'__products_data',
					),
				),
				'show_rating' => array(
					'label'           => esc_html__( 'Show Star Rating', 'divi-plus' ),
					'type'            => 'yes_no_button',
					'option_category' => 'configuration',
					'options'         => array(
						'on'  => esc_html__( 'Yes', 'divi-plus' ),
						'off' => esc_html__( 'No', 'divi-plus' ),
					),
					'default'         => 'off',
					'tab_slug'        => 'general',
					'toggle_slug'     => 'display_setting',
					'description'     => esc_html__( 'Choose whether or not the star rating should be visible.', 'divi-plus' ),
					'computed_affects' => array(
						'__products_data',
					),
				),
				'show_price' => array(
					'label'           => esc_html__( 'Show Price', 'divi-plus' ),
					'type'            => 'yes_no_button',
					'option_category' => 'configuration',
					'options'         => array(
						'on'  => esc_html__( 'Yes', 'divi-plus' ),
						'off' => esc_html__( 'No', 'divi-plus' ),
					),
					'default'         => 'on',
					'tab_slug'        => 'general',
					'toggle_slug'     => 'display_setting',
					'description'     => esc_html__( 'Choose whether or not the price should be visible.', 'divi-plus' ),
					'computed_affects' => array(
						'__products_data',
					),
				),
				'show_add_to_cart' => array(
					'label'           => esc_html__( 'Show Add to Cart', 'divi-plus' ),
					'type'            => 'yes_no_button',
					'option_category' => 'configuration',
					'options'         => array(
						'on'  => esc_html__( 'Yes', 'divi-plus' ),
						'off' => esc_html__( 'No', 'divi-plus' ),
					),
					'default'         => 'on',
					'tab_slug'        => 'general',
					'toggle_slug'     => 'display_setting',
					'description'     => esc_html__( 'Choose whether or not the add to cart button should be visible.', 'divi-plus' ),
					'computed_affects' => array(
						'__products_data',
					),
				),
				'show_add_to_cart_on_hover' => array(
					'label'           	=> esc_html__( 'Show Add to Cart on Hover', 'divi-plus' ),
					'type'            	=> 'yes_no_button',
					'option_category' 	=> 'configuration',
					'options'         	=> array(
						'on'  => esc_html__( 'Yes', 'divi-plus' ),
						'off' => esc_html__( 'No', 'divi-plus' ),
					),
					'default'         	=> 'off',
					'show_if'		  	=> array(
						'show_add_to_cart' => 'on',
						'product_layout' => 'layout1'
					),
					'tab_slug'        	=> 'general',
					'toggle_slug'     	=> 'display_setting',
					'description'     	=> esc_html__( 'Choose whether or not the add to cart button should be visible only on hover. This will not work on tablet and mobiles.', 'divi-plus' ),
					'computed_affects' 	=> array(
						'__products_data',
					),
				),
				'simple_add_to_cart_text' => array(
					'label'            => esc_html__( 'Simple Product Add to Cart Text', 'divi-plus' ),
					'type'             => 'text',
					'option_category'  => 'configuration',
					'show_if'		  => array(
						'show_add_to_cart' => 'on',
					),
					'tab_slug'         => 'general',
					'toggle_slug'      => 'display_setting',
					'description'      => esc_html__( 'Here you can specify the text for Simple Product Add to Cart.', 'divi-plus' ),
					'computed_affects' => array(
						'__products_data',
					),
				),
				'variable_add_to_cart_text' => array(
					'label'            => esc_html__( 'Variable Product Add to Cart Text', 'divi-plus' ),
					'type'             => 'text',
					'option_category'  => 'configuration',
					'show_if'		   => array(
						'show_add_to_cart' => 'on',
					),
					'tab_slug'         => 'general',
					'toggle_slug'      => 'display_setting',
					'description'      => esc_html__( 'Here you can specify the text for Variable Product Add to Cart.', 'divi-plus' ),
					'computed_affects' => array(
						'__products_data',
					),
				),
				'grouped_add_to_cart_text' => array(
					'label'            => esc_html__( 'Grouped Product Add to Cart Text', 'divi-plus' ),
					'type'             => 'text',
					'option_category'  => 'configuration',
					'show_if'		   => array(
						'show_add_to_cart' => 'on',
					),
					'tab_slug'         => 'general',
					'toggle_slug'      => 'display_setting',
					'description'      => esc_html__( 'Here you can specify the text for Grouped Product Add to Cart.', 'divi-plus' ),
					'computed_affects' => array(
						'__products_data',
					),
				),
				'external_add_to_cart_text' => array(
					'label'            => esc_html__( 'External Product Add to Cart Text', 'divi-plus' ),
					'type'             => 'text',
					'option_category'  => 'configuration',
					'show_if'		   => array(
						'show_add_to_cart' => 'on',
					),
					'tab_slug'         => 'general',
					'toggle_slug'      => 'display_setting',
					'description'      => esc_html__( 'Here you can specify the text for External Product Add to Cart.', 'divi-plus' ),
					'computed_affects' => array(
						'__products_data',
					),
				),
				'out_of_stock_add_to_cart_text' => array(
					'label'            => esc_html__( 'Out of Stock/Unpurchaseable Add to Cart Text', 'divi-plus' ),
					'type'             => 'text',
					'option_category'  => 'configuration',
					'show_if'		   => array(
						'show_add_to_cart' => 'on',
					),
					'tab_slug'         => 'general',
					'toggle_slug'      => 'display_setting',
					'description'      => esc_html__( 'Here you can specify the text for Out of stock/unpurchaseable Product Add to Cart.', 'divi-plus' ),
					'computed_affects' => array(
						'__products_data',
					),
				),
				'show_sale_badge' => array(
					'label'           	=> esc_html__( 'Show Sale Badge', 'divi-plus' ),
					'type'            	=> 'yes_no_button',
					'option_category' 	=> 'configuration',
					'options'         	=> array(
						'on'  => esc_html__( 'Yes', 'divi-plus' ),
						'off' => esc_html__( 'No', 'divi-plus' ),
					),
					'default'         	=> 'on',
					'show_if'			=> array(
						'show_thumbnail' => 'on',
					),
					'tab_slug'        	=> 'general',
					'toggle_slug'     	=> 'display_setting',
					'description'     	=> esc_html__( 'Choose whether or not the sale badge should be visible.', 'divi-plus' ),
					'computed_affects' 	=> array(
						'__products_data',
					),
				),
				'sale_badge_text' => array(
					'label'           	=> esc_html__( 'Badge Text', 'divi-plus' ),
					'type'              => 'select',
	                'option_category'   => 'configuration',
	                'options'           => array(
	                    'label'         => esc_html__( 'Sale Label', 'divi-plus' ),
	                    'percentage'    => esc_html__( 'Sale Percentage', 'divi-plus' ),
	                ),
	                'default'			=> 'label',
	                'default_on_front'	=> 'label',
					'show_if'			=> array(
						'show_thumbnail' 	=> 'on',
						'show_sale_badge' 	=> 'on',
					),
					'tab_slug'        	=> 'general',
					'toggle_slug'     	=> 'display_setting',
					'description'     	=> esc_html__( 'Choose which sale badge should be visible.', 'divi-plus' ),
					'computed_affects' 	=> array(
						'__products_data',
					),
				),
				'sale_label_text' => array(
					'label'            => esc_html__( 'Sale Label', 'divi-plus' ),
					'type'             => 'text',
					'option_category'  => 'configuration',
					'show_if'		   => array(
						'show_thumbnail' 	=> 'on',
						'show_sale_badge' 	=> 'on',
						'sale_badge_text'	=> 'label',
					),
					'tab_slug'         => 'general',
					'toggle_slug'      => 'display_setting',
					'description'      => esc_html__( 'Here you can specify the text for Sale label.', 'divi-plus' ),
					'computed_affects' => array(
						'__products_data',
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
						'__products_data',
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
				'quickview_link_bg_color' => array(
	                'label'                 => esc_html__( 'Link Background', 'divi-plus' ),
	                'type'         			=> 'color-alpha',
					'custom_color' 			=> true,
	                'show_if'		  		=> array(
						'enable_quickview' => 'on',
					),
					'default'				=> 'rgba(255, 255, 255, 0.6)',
	                'tab_slug'              => 'advanced',
	                'toggle_slug'           => 'quickview_link',
	                'description'           => esc_html__( 'Customize the background style of the quickview link by adjusting the background color.', 'divi-plus' ),
	            ),
				'sale_badge_bg_color' => array(
					'label'        	   => esc_html__( 'Sale Badge Background Color', 'divi-plus' ),
					'type'         	   => 'color-alpha',
					'custom_color' 	   => true,
					'show_if'          => array(
						'show_thumbnail'	=> 'on',
						'show_sale_badge' 	=> 'on',
					),
					'default'      	   => '#000',
					'tab_slug'         => 'advanced',
					'toggle_slug'      => 'sale_badge',
					'description'      => esc_html__( 'Here you can select the backround color for the sale badge.', 'divi-plus' ),
				),
				'product_bg_color' => array(
	                'label'                 => esc_html__( 'Product Background', 'divi-plus' ),
	                'type'                  => 'background-field',
	                'base_name'             => 'product_bg',
	                'context'               => 'product_bg_color',
	                'option_category'       => 'button',
	                'custom_color'          => true,
	                'background_fields'     => $this->generate_background_options( 'product_bg', 'button', 'general', 'background', 'product_bg_color' ),
	                'hover'                 => 'tabs',
	                'tab_slug'              => 'general',
	                'toggle_slug'           => 'background',
	                'description'           => esc_html__( 'Customize the background style of the product by adjusting the background color, gradient, and image.', 'divi-plus' ),
	            ),
	            'add_to_cart_bg_color' => array(
	                'label'                 => esc_html__( 'Add to Cart Button Background', 'divi-plus' ),
	                'type'                  => 'background-field',
	                'base_name'             => 'add_to_cart_bg',
	                'context'               => 'add_to_cart_bg_color',
	                'option_category'       => 'button',
	                'custom_color'          => true,
	                'background_fields'     => $this->generate_background_options( 'add_to_cart_bg', 'button', 'advanced', 'add_to_cart_button', 'add_to_cart_bg_color' ),
	                'hover'                 => 'tabs',
	                'show_if'		  		=> array(
						'show_add_to_cart' => 'on',
					),
	                'tab_slug'              => 'advanced',
	                'toggle_slug'           => 'add_to_cart_button',
	                'description'           => esc_html__( 'Customize the background style of the product by adjusting the background color, gradient, and image.', 'divi-plus' ),
	            ),
	            'quickview_add_to_cart_bg_color' => array(
	                'label'                 => esc_html__( 'Add to Cart Button Background', 'divi-plus' ),
	                'type'                  => 'background-field',
	                'base_name'             => 'quickview_add_to_cart_bg',
	                'context'               => 'quickview_add_to_cart_bg_color',
	                'option_category'       => 'button',
	                'custom_color'          => true,
	                'background_fields'     => $this->generate_background_options( 'quickview_add_to_cart_bg', 'button', 'advanced', 'quickview_add_to_cart_button', 'quickview_add_to_cart_bg_color' ),
	                'hover'                 => 'tabs',
	                'show_if'		  		=> array(
						'enable_quickview' => 'on',
					),
	                'tab_slug'              => 'advanced',
	                'toggle_slug'           => 'quickview_add_to_cart_button',
	                'description'           => esc_html__( 'Customize the background style of the add to cart button by adjusting the background color, gradient, and image.', 'divi-plus' ),
	            ),
	            'quickview_quantity_bg_color' => array(
	                'label'                 => esc_html__( 'Quantity Field Background', 'divi-plus' ),
	                'type'         			=> 'color-alpha',
					'custom_color' 			=> true,
	                'show_if'		  		=> array(
						'enable_quickview' => 'on',
					),
	                'tab_slug'              => 'advanced',
	                'toggle_slug'           => 'quickview_quantity',
	                'description'           => esc_html__( 'Customize the background color of the quantity field by adjusting the background color.', 'divi-plus' ),
	            ),
	            'quickview_attributes_bg_color' => array(
	                'label'                 => esc_html__( 'Attributes Field Background', 'divi-plus' ),
	                'type'         			=> 'color-alpha',
					'custom_color' 			=> true,
	                'show_if'		  		=> array(
						'enable_quickview' => 'on',
					),
	                'tab_slug'              => 'advanced',
	                'toggle_slug'           => 'quickview_attributes',
	                'sub_toggle'			=> 'field_text',
	                'description'           => esc_html__( 'Customize the background style of the attributes field by adjusting the background color.', 'divi-plus' ),
	            ),
	            'star_rating_color' => array(
	                'label'                 => esc_html__( 'Star Rating Color', 'divi-plus' ),
	                'type'         			=> 'color-alpha',
					'custom_color' 			=> true,
	                'show_if'		  		=> array(
						'show_rating' => 'on',
					),
					'default'				=> '#ffca00',
	                'tab_slug'              => 'advanced',
	                'toggle_slug'           => 'star_rating',
	                'description'           => esc_html__( 'Customize the color of star rating.', 'divi-plus' ),
	            ),
	            'add_to_cart_use_icon' => array(
					'label'            		=> esc_html__( 'Show Add to Cart Icon', 'divi-plus' ),
					'type'             		=> 'yes_no_button',
					'option_category'  		=> 'configuration',
					'options'          		=> array(
						'off' => esc_html__( 'No', 'divi-plus' ),
						'on'  => esc_html__( 'Yes', 'divi-plus' ),
					),
					'default'          		=> 'on',
					'show_if'		  		=> array(
						'show_add_to_cart' => 'on',
					),
					'tab_slug'         		=> 'advanced',
					'toggle_slug'      		=> 'add_to_cart_button',
					'description'      		=> esc_html__( 'When enabled, this will add a custom icon within the button.', 'divi-plus' ),
				),
				'add_to_cart_icon' => array(
					'label'               	=> esc_html__( 'Add to Cart Icon', 'divi-plus' ),
					'type'                	=> 'select_icon',
					'option_category'     	=> 'button',
					'class'               	=> array( 'et-pb-font-icon' ),
					'default'             	=> '',
					'mobile_options'      	=> true,
					'show_if'      			=> array(
						'show_add_to_cart' 		=> 'on',
						'add_to_cart_use_icon' 	=> 'on',
					),
					'tab_slug'         		=> 'advanced',
					'toggle_slug'      		=> 'add_to_cart_button',
					'description'         	=> esc_html__( 'Pick an icon to be used for the button.', 'divi-plus' ),
					'computed_affects' 		=> array(
						'__products_data',
					),
				),
				'add_to_cart_on_hover' => array(
					'label'            		=> esc_html__( 'Only Show Icon On Hover', 'divi-plus' ),
					'type'             		=> 'yes_no_button',
					'option_category'  		=> 'configuration',
					'options'          		=> array(
						'off' => esc_html__( 'No', 'divi-plus' ),
						'on'  => esc_html__( 'Yes', 'divi-plus' ),
					),
					'default'          		=> 'on',
					'show_if'      			=> array(
						'show_add_to_cart' 		=> 'on',
						'add_to_cart_use_icon' 	=> 'on',
					),
					'tab_slug'         		=> 'advanced',
					'toggle_slug'      		=> 'add_to_cart_button',
					'description'      		=> esc_html__( 'By default, button icons are displayed on hover. If you would like button icons to always be displayed, then you can disable this option.', 'divi-plus' ),
				),
				'add_to_cart_icon_placement' => array(
					'label'            		=> esc_html__( 'Add to Cart Icon Placement', 'divi-plus' ),
					'type'                	=> 'select',
					'option_category'     	=> 'button',
					'options'             	=> array(
						'right' => esc_html__( 'Right', 'divi-plus' ),
						'left'  => esc_html__( 'Left', 'divi-plus' ),
					),
					'default'             	=> 'right',
					'show_if'      			=> array(
						'show_add_to_cart' 		=> 'on',
						'add_to_cart_use_icon' 	=> 'on',
					),
					'tab_slug'            	=> 'advanced',
					'toggle_slug'        	=> 'add_to_cart_button',
					'description'         	=> esc_html__( 'Choose where the button icon should be displayed within the button.', 'divi-plus' ),
				),
				'quickview_add_to_cart_use_icon' => array(
					'label'            		=> esc_html__( 'Show Add to Cart Icon', 'divi-plus' ),
					'type'             		=> 'yes_no_button',
					'option_category'  		=> 'configuration',
					'options'          		=> array(
						'off' => esc_html__( 'No', 'divi-plus' ),
						'on'  => esc_html__( 'Yes', 'divi-plus' ),
					),
					'default'          		=> 'on',
					'show_if'		  		=> array(
						'enable_quickview' => 'on',
					),
					'tab_slug'         		=> 'advanced',
					'toggle_slug'      		=> 'quickview_add_to_cart_button',
					'description'      		=> esc_html__( 'When enabled, this will add a custom icon within the button.', 'divi-plus' ),
				),
				'quickview_add_to_cart_icon' => array(
					'label'               	=> esc_html__( 'Add to Cart Icon', 'divi-plus' ),
					'type'                	=> 'select_icon',
					'option_category'     	=> 'button',
					'class'               	=> array( 'et-pb-font-icon' ),
					'default'             	=> '',
					'mobile_options'      	=> true,
					'show_if'      			=> array(
						'enable_quickview' 					=> 'on',
						'quickview_add_to_cart_use_icon' 	=> 'on',
					),
					'tab_slug'         		=> 'advanced',
					'toggle_slug'      		=> 'quickview_add_to_cart_button',
					'description'         	=> esc_html__( 'Pick an icon to be used for the button.', 'divi-plus' ),
				),
				'quickview_add_to_cart_on_hover' => array(
					'label'            		=> esc_html__( 'Only Show Icon On Hover', 'divi-plus' ),
					'type'             		=> 'yes_no_button',
					'option_category'  		=> 'configuration',
					'options'          		=> array(
						'off' => esc_html__( 'No', 'divi-plus' ),
						'on'  => esc_html__( 'Yes', 'divi-plus' ),
					),
					'default'          		=> 'on',
					'show_if'      			=> array(
						'enable_quickview' 					=> 'on',
						'quickview_add_to_cart_use_icon' 	=> 'on',
					),
					'tab_slug'         		=> 'advanced',
					'toggle_slug'      		=> 'quickview_add_to_cart_button',
					'description'      		=> esc_html__( 'By default, button icons are displayed on hover. If you would like button icons to always be displayed, then you can disable this option.', 'divi-plus' ),
				),
				'quickview_add_to_cart_icon_placement' => array(
					'label'            		=> esc_html__( 'Add to Cart Icon Placement', 'divi-plus' ),
					'type'                	=> 'select',
					'option_category'     	=> 'button',
					'options'             	=> array(
						'right' => esc_html__( 'Right', 'divi-plus' ),
						'left'  => esc_html__( 'Left', 'divi-plus' ),
					),
					'default'             	=> 'right',
					'show_if'      			=> array(
						'enable_quickview' 					=> 'on',
						'quickview_add_to_cart_use_icon' 	=> 'on',
					),
					'tab_slug'            	=> 'advanced',
					'toggle_slug'        	=> 'quickview_add_to_cart_button',
					'description'         	=> esc_html__( 'Choose where the button icon should be displayed within the button.', 'divi-plus' ),
				),
				'add_to_cart_custom_padding' => array(
	                'label'                 => esc_html__( 'Add to Cart Padding', 'divi-plus' ),
	                'type'                  => 'custom_padding',
	                'option_category'       => 'layout',
	                'mobile_options'        => true,
	                'hover'                 => 'tabs',
	                'default'				=> '10px|10px|10px|10px|true|true',
	                'default_on_front'		=> '10px|10px|10px|10px|true|true',
	                'tab_slug'              => 'advanced',
	                'toggle_slug'           => 'add_to_cart_button',
	                'description'           => esc_html__( 'Padding adds extra space to the inside of the element, increasing the distance between the edge of the element and its inner contents.', 'divi-plus' ),
	            ),
				'product_content_custom_padding' => array(
	                'label'                 => esc_html__( 'Product Content Padding', 'divi-plus' ),
	                'type'                  => 'custom_padding',
	                'option_category'       => 'layout',
	                'mobile_options'        => true,
	                'hover'                 => false,
	                'default'				=> '15px|15px|15px|15px|true|true',
	                'default_on_front'		=> '15px|15px|15px|15px|true|true',
	                'tab_slug'              => 'advanced',
	                'toggle_slug'           => 'margin_padding',
	                'description'           => esc_html__( 'Padding adds extra space to the inside of the element, increasing the distance between the edge of the element and its inner contents.', 'divi-plus' ),
	            ),
	            'product_image_custom_padding' => array(
	                'label'                 => esc_html__( 'Product Image Padding', 'divi-plus' ),
	                'type'                  => 'custom_padding',
	                'option_category'       => 'layout',
	                'mobile_options'        => true,
	                'hover'                 => false,
	                'tab_slug'              => 'advanced',
	                'toggle_slug'           => 'margin_padding',
	                'description'           => esc_html__( 'Padding adds extra space to the inside of the element, increasing the distance between the edge of the element and its inner contents.', 'divi-plus' ),
	            ),
	            'sorting_dropdown_custom_margin' => array(
	                'label'                 => esc_html__( 'Dropdown Margin', 'divi-plus' ),
	                'type'                  => 'custom_padding',
	                'option_category'       => 'layout',
	                'mobile_options'        => true,
	                'hover'                 => false,
	                'default'				=> '||20px|||',
	                'show_if'      			=> array(
						'show_sorting_dropdown' => 'on',
					),
	                'tab_slug'              => 'advanced',
	                'toggle_slug'           => 'sorting_dropdown',
	                'description'           => esc_html__( 'Margin adds extra space to the outside of the element, increasing the distance between the element and other items on the page.', 'divi-plus' ),
	            ),
	            'sorting_dropdown_custom_padding' => array(
	                'label'                 => esc_html__( 'Dropdown Padding', 'divi-plus' ),
	                'type'                  => 'custom_margin',
	                'option_category'       => 'layout',
	                'mobile_options'        => true,
	                'hover'                 => false,
	                'show_if'      			=> array(
						'show_sorting_dropdown' => 'on',
					),
	                'tab_slug'              => 'advanced',
	                'toggle_slug'           => 'sorting_dropdown',
	                'description'           => esc_html__( 'Padding adds extra space to the inside of the element, increasing the distance between the edge of the element and its inner contents.', 'divi-plus' ),
	            ),
	            'sorting_dropdown_alignment' => array(
	                'label'                 => esc_html__( 'Dropdown Alignment', 'divi-plus' ),
	                'type'                  => 'text_align',
	                'option_category'       => 'layout',
	                'options'               => et_builder_get_text_orientation_options( array( 'justified' ) ),
	                'mobile_options'        => true,
	                'default'				=> 'right',
	                'tab_slug'              => 'advanced',
	                'toggle_slug'           => 'sorting_dropdown',
	                'description'           => esc_html__( 'Here you can choose the alignment of the dropdown in the left, right, or center.', 'divi-plus' ),
	            ),
	            'sorting_dropdown_background_color' => array(
					'label'        => esc_html__( 'Dropdown Background Color', 'divi-plus' ),
					'type'         => 'color-alpha',
					'custom_color' => true,
					'default'	   => 'transparent',
					'hover'		   => 'tabs',
					'show_if'      => array(
						'show_sorting_dropdown' => 'on',
					),
					'tab_slug'     => 'advanced',
					'toggle_slug'  => 'sorting_dropdown',
					'description'  => esc_html__( 'Here you can define a custom background color for the dropdown.', 'divi-plus' ),
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
				'lightbox_content_width' => array(
					'label'           => esc_html__( 'Lightbox Content Width', 'divi-plus' ),
					'type'            => 'range',
					'option_category' => 'layout',
					'range_settings'  => array(
						'min'  => '1',
						'max'  => '100',
						'step' => '1',
					),
					'mobile_options' => true,
					'show_if' 		  => array(
						'enable_quickview' => 'on',
					),
					'default'         => '60%',
					'tab_slug'        => 'advanced',
					'toggle_slug'     => 'quickview_popup',
					'description'     => esc_html__( 'Move the slider or input the value to increase or decrease the width of lightbox content.', 'divi-plus' ),
				),
				'lightbox_bg_color' => array(
					'label'        => esc_html__( 'Lightbox Background Color', 'divi-plus' ),
					'type'         => 'color-alpha',
					'custom_color' => true,
					'default'	   => 'rgba(11, 11, 11, 0.8)',
					'show_if'      => array(
						'enable_quickview' => 'on',
					),
					'tab_slug'     => 'advanced',
					'toggle_slug'  => 'quickview_popup',
					'description'  => esc_html__( 'Here you can define a custom background color for the lightbox.', 'divi-plus' ),
				),
				'lightbox_close_icon_color' => array(
					'label'            => esc_html__( 'Close Icon Color', 'divi-plus' ),
					'type'             => 'color-alpha',
					'default'          => '#fff',
					'show_if'          => array(
						'enable_quickview' => 'on',
					),
					'tab_slug'         => 'advanced',
					'toggle_slug'      => 'quickview_popup',
					'description'      => esc_html__( 'Here you can define a custom color to be used for the close icon.', 'divi-plus' ),
				),
				'loader_background_color' => array(
					'label'        => esc_html__( 'Loader Overlay', 'divi-plus' ),
					'type'         => 'color-alpha',
					'custom_color' => true,
					'show_if'      => array(
						'enable_quickview' => 'on',
					),
					'default'	   => 'rgba(255, 255, 255, 0.8)',
					'tab_slug'     => 'advanced',
					'toggle_slug'  => 'loader',
					'description'  => esc_html__( 'Here you can choose a custom color to be used for the loader overlay.', 'divi-plus' ),
				),
				'woo_notice_background_color' => array(
					'label'        => esc_html__( 'Notice Background Color', 'divi-plus' ),
					'type'         => 'color-alpha',
					'custom_color' => true,
					'show_if'      => array(
						'enable_quickview' => 'on',
					),
					'default'	   => '#000',
					'tab_slug'     => 'advanced',
					'toggle_slug'  => 'woocommerce_notice',
					'sub_toggle'   => 'p',
					'description'  => esc_html__( 'Here you can choose a custom color to be used for the woocommerce notice.', 'divi-plus' ),
				),
				'__products_data' => array(
					'type'                => 'computed',
					'computed_callback'   => array( 'DIPL_WooProducts', 'get_products_data' ),
					'computed_depends_on' => array(
						'view_type',
						'use_current_loop',
						'product_layout',
						'products_number',
						'offset_number',
						'products_order' ,
						'products_order_by',
						'hide_out_of_stock',
						'enable_out_of_stock_label',
						'out_of_stock_label',
						'include_categories',
						'include_tags',
						'enable_quickview',
						'quickview_link_text',
						'taxonomies_relation',
						'use_masonry',
						'show_sorting_dropdown',
						'show_thumbnail',
						'thumbnail_size',
						'show_rating',
						'show_price',
						'show_pagination',
						'show_add_to_cart',
						'show_add_to_cart_on_hover',
						'simple_add_to_cart_text',
						'variable_add_to_cart_text',
						'grouped_add_to_cart_text',
						'external_add_to_cart_text',
						'out_of_stock_add_to_cart_text',
						'add_to_cart_icon',
						'add_to_cart_icon_tablet',
						'add_to_cart_icon_phone',
						'show_sale_badge',
						'sale_badge_text',
						'sale_label_text',
						'title_level',
					),
				),
			),
			$this->generate_background_options( 'product_bg', 'skip', 'general', 'background', 'product_bg_color' ),
			$this->generate_background_options( 'add_to_cart_bg', 'skip', 'advanced', 'add_to_cart_button', 'add_to_cart_bg_color' ),
			$this->generate_background_options( 'quickview_add_to_cart_bg', 'skip', 'advanced', 'quickview_add_to_cart_button', 'quickview_add_to_cart_bg_color' )
		);
	}

	/**
	 * This function return values to react for front end builder.
	 *
	 * @param array arguments to get products data
	 * @return array
	 * */
	public static function get_products_data( $args = array(), $conditional_tags = array(), $current_page = array() ) {
		if ( self::$rendering ) {
			// We are trying to render a Blog module while a Blog module is already being rendered
			// which means we have most probably hit an infinite recursion. While not necessarily
			// the case, rendering a post which renders a Blog module which renders a post
			// which renders a Blog module is not a sensible use-case.
			return '';
		}

		$defaults = array(
			'view_type' => 'default',
			'use_current_loop' => 'off',
			'product_layout' => 'layout1',
			'products_number' => '10',
			'offset_number' => 0,
			'products_order' => 'DESC',
			'products_order_by' => 'date',
			'hide_out_of_stock'	=> 'off',
			'enable_out_of_stock_label' => 'off',
			'out_of_stock_label' => esc_html__( 'Out of Stock', 'divi-plus' ),
			'include_categories' => '',
			'include_tags' => '',
			'taxonomies_relation' => 'OR',
			'show_sorting_dropdown' => 'off',
			'enable_quickview' => 'off',
			'quickview_link_text' => 'Quickview',
			'use_masonry' => 'off',
			'show_thumbnail' => 'on',
			'thumbnail_size' => 'woocommerce_thumbnail',
			'show_rating' => 'off',
			'show_price' => 'on',
			'show_pagination' => 'off',
			'show_add_to_cart' => 'on',
			'show_add_to_cart_on_hover' => 'off',
			'simple_add_to_cart_text' => '',
			'variable_add_to_cart_text' => '',
			'grouped_add_to_cart_text' => '',
			'external_add_to_cart_text' => '',
			'out_of_stock_add_to_cart_text' => '',
			'add_to_cart_icon' => '',
			'add_to_cart_icon_tablet' => '',
			'add_to_cart_icon_phone' => '',
			'show_sale_badge' => 'on',
			'sale_badge_text' => 'label',
			'sale_label_text' => '',
			'title_level' => 'h4',
		);

		$args = wp_parse_args( $args, $defaults );

		foreach ( $defaults as $key => $default ) {
			if ( isset( $args[$key] ) && et_()->includes( $args[$key], '%' ) ) {
    			// phpcs:ignore ET.Sniffs.ValidatedSanitizedInput.InputNotSanitized
        		$prepared_value  = preg_replace( '/%([a-f0-9]{2})/', '%_$1', $args[$key] );
				${$key} = preg_replace( '/%_([a-f0-9]{2})/', '%$1', trim( sanitize_text_field( wp_unslash( $prepared_value ) ) ) );
        	} else {
        		${$key} = sanitize_text_field( et_()->array_get( $args, $key, $default ) );
        	}
		}

		$is_single         = (bool) et_fb_conditional_tag( 'is_single', $conditional_tags );
		$is_user_logged_in = (bool) et_fb_conditional_tag( 'is_user_logged_in', $conditional_tags );
		$current_post_id   = isset( $current_page['id'] ) ? (int) $current_page['id'] : 0;

		$processed_title_level 	= et_pb_process_header_level( $title_level, 'h4' );
		$processed_title_level	= esc_html( $processed_title_level );

		$add_to_cart_icon = array(
            'desktop' => $add_to_cart_icon,
            'tablet' => $add_to_cart_icon_tablet,
            'phone' => $add_to_cart_icon_phone
        );

        $add_to_cart_icon = array_filter( $add_to_cart_icon );

		$products_number = ( 0 === $products_number ) ? -1 : (int) $products_number;

		$args = array(
			'post_type'      => 'product',
			'posts_per_page' => intval( $products_number ),
			'offset'		 => intval( $offset_number ),
			'post_status'    => 'publish',
			'orderby'        => $products_order_by,
			'order'          => $products_order,
		);

		if ( 'price' === $products_order_by ) {
			$args['orderby'] 	= 'meta_value_num';
			$args['meta_key'] 	= '_price';
		} else if( 'stock_status' === $products_order_by ) {
			$args['orderby'] 	= 'meta_value';
			$args['meta_key'] 	= '_stock_status';
		}

		if ( $is_user_logged_in ) {
			$args['post_status'] = array(
				'publish',
				'private',
			);
		}

		$use_current_loop = 'on' === $use_current_loop;

		$args['tax_query'] = array(
			array(
				'taxonomy'         => 'product_visibility',
				'terms'            => 'exclude-from-catalog',
				'field'            => 'slug',
				'operator'         => 'NOT IN',
			),
		);

		$tax_query = array();

		if ( $include_categories && '' !== $include_categories && ! $use_current_loop ) {
			$tax_query[] = array(
				'taxonomy' => 'product_cat',
				'field'    => 'term_id',
				'terms'    => array_map( 'intval', explode( ',', $include_categories ) ),
				'operator' => 'IN',
			);
		}

		if ( $include_tags && '' !== $include_tags && ! $use_current_loop ) {
			$tax_query[] = array(
				'taxonomy' => 'product_tag',
				'field'    => 'term_id',
				'terms'    => array_map( 'intval', explode( ',', $include_tags ) ),
				'operator' => 'IN',
			);
		}

		switch( $view_type ) {

			case 'featured':
				$args['tax_query'][] = array(
					'taxonomy'         => 'product_visibility',
					'terms'            => 'featured',
					'field'            => 'name',
					'operator'         => 'IN',
					'include_children' => false,
				);
				break;

			case 'sale':
				if ( function_exists( 'wc_get_product_ids_on_sale' ) ) {
					$args['post__in'] = array_merge( array( 0 ), wc_get_product_ids_on_sale() );
				}
				break;

			case 'best_selling':
				$args['meta_key'] = 'total_sales';
				$args['order']    = 'DESC';
				$args['orderby']  = 'meta_value_num';
				break;

			case 'top_rated':
				$args['meta_key'] = '_wc_average_rating';
				$args['order']    = 'DESC';
				$args['orderby']  = 'meta_value_num';
				break;

			default:
				break;
		}

		if ( 'on' === $hide_out_of_stock ) {
			$args['meta_query'] = array(
				array(
					'key'     => '_stock_status',
		            'value'   => 'instock',
		            'compare' => 'IN',
				),
				array(
					'key'     => '_stock_status',
		            'value'   => 'onbackorder',
		            'compare' => 'IN',
				),
				'relation' => 'OR'
			);
		}

		if ( ! empty( $tax_query ) ) {
			if ( ! $use_current_loop && count( $tax_query ) > 1 ) {
				$tax_query['relation'] = sanitize_text_field( $taxonomies_relation );
			}
			$args['tax_query'][] = $tax_query;
		}
		
		$args['tax_query']['relation'] = 'AND';

		global $wp_the_query;
		$query_backup = $wp_the_query;

		$args = apply_filters( 'dipl_woo_products_args', $args );

		$query = new WP_Query( $args );

		self::$rendering = true;

		if ( $query->have_posts() ) {

			if ( $simple_add_to_cart_text || $variable_add_to_cart_text || $grouped_add_to_cart_text || $external_add_to_cart_text || $out_of_stock_add_to_cart_text ) {
				$add_to_cart_text = array(
					'simple' => esc_html( $simple_add_to_cart_text ),
					'variable' => esc_html( $variable_add_to_cart_text ),
					'grouped' => esc_html( $grouped_add_to_cart_text ),
					'external' => esc_html( $external_add_to_cart_text ),
					'out_of_stock' => esc_html( $out_of_stock_add_to_cart_text ),
				);
			} else {
				$add_to_cart_text = array();
			}

			$output = '';

			if ( 'on' === $show_sorting_dropdown ) {
				$sorting_options = array(
					'title' 		=> esc_html__( 'Default sorting', 'divi-plus' ),
					'popularity' 	=> esc_html__( 'Sort by popularity', 'divi-plus' ),
					'rating' 		=> esc_html__( 'Sort by average rating', 'divi-plus' ),
					'date' 			=> esc_html__( 'Sort by latest', 'divi-plus' ),
					'price'	 		=> esc_html__( 'Sort by price: low to high', 'divi-plus' ),
					'price-desc' 	=> esc_html__( 'Sort by price: high to low', 'divi-plus' ),
				);
				$output .= '<form class="dipl_woo_products_ordering" method="get">
							<select name="orderby" class="dipl_sorting_orderby" aria-label="Shop order">';
				foreach( $sorting_options as $value => $option ) {
					$output .= '<option value="'. $value .'">' . $option . '</option>';
				}
				$output .= '</select>
							<input type="hidden" name="dipl_woo_products_ordering_nonce" value="' . wp_create_nonce( 'dipl-woo-products-ordering-nonce' ) . '" />
							</form>';
			}

			$output .= sprintf(
				'<div class="dipl_woo_products_layout %1$s">',
				esc_attr( $product_layout )
			);

			if ( 'on' === $use_masonry ) {
				$output .= '<div class="dipl_woo_products_isotope_container">';
				$output .= '<div class="dipl_woo_products_isotope_item_gutter"></div>';
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
					'<div class="dipl_woo_products_pagination_wrapper" data-total_pages="%1$s">
						<ul></ul>
					</div>',
					$total_pages
				);
			}

			while ( $query->have_posts() ) {
				$query->the_post();
				$product_id = intval( get_the_ID() );

				$output .= '<div class="dipl_woo_products_isotope_item">';

				if ( file_exists( get_stylesheet_directory() . '/divi-plus/layouts/woo-products/' . sanitize_file_name( $product_layout ) . '.php' ) ) {
					include get_stylesheet_directory() . '/divi-plus/layouts/woo-products/' . sanitize_file_name( $product_layout ) . '.php';
				} elseif ( file_exists( plugin_dir_path( __FILE__ ) . 'layouts/' . sanitize_file_name( $product_layout ) . '.php' ) ) {
					include plugin_dir_path( __FILE__ ) . 'layouts/' . sanitize_file_name( $product_layout ) . '.php';
				}

				$output .= '</div>';
			}

			wp_reset_postdata();

			$wp_the_query = $query_backup;

			if ( 'on' === $use_masonry ) {
				$output .= '</div>';
			}

			$output .= '</div>';

			if ( 'on' === $show_pagination ) {
				$output .= $pagination;
			}

		} else {
			$output = '';
		}

		self::$rendering = false;

		return et_core_intentionally_unescaped( $output, 'html' );
	}

	public function render( $attrs, $content, $render_slug ) {

		if ( self::$rendering ) {
			// We are trying to render a DIPL Woo Product module while a DIPL Woo Product module is already being rendered
			// which means we have most probably hit an infinite recursion. While not necessarily
			// the case, rendering a post which renders a Blog module which renders a post
			// which renders a Blog module is not a sensible use-case.
			return '';
		}

		$view_type								= $this->props['view_type'];
		$use_current_loop						= $this->props['use_current_loop'];
		$product_layout							= $this->props['product_layout'];
		$products_number 						= $this->props['products_number'];
		$offset_number							= $this->props['offset_number'];
		$products_order 						= $this->props['products_order'];
		$products_order_by						= $this->props['products_order_by'];
		$hide_out_of_stock						= $this->props['hide_out_of_stock'];
		$enable_out_of_stock_label				= $this->props['enable_out_of_stock_label'];
		$out_of_stock_label 					= $this->props['out_of_stock_label'];
		$include_categories 					= $this->props['include_categories'];
		$include_tags 							= $this->props['include_tags'];
		$taxonomies_relation					= $this->props['taxonomies_relation'];
		$no_result_text							= $this->props['no_result_text'];
		$number_of_columns 						= $this->props['number_of_columns'];
		$column_spacing							= $this->props['column_spacing'];
		$use_masonry							= $this->props['use_masonry'];
		$show_sorting_dropdown					= $this->props['show_sorting_dropdown'];
		$enable_quickview						= $this->props['enable_quickview'];
		$quickview_link_text 					= $this->props['quickview_link_text'];
		$quickview_link_bg_color				= $this->props['quickview_link_bg_color'];
		$show_thumbnail							= $this->props['show_thumbnail'];
		$thumbnail_size							= $this->props['thumbnail_size'];
		$show_rating 							= $this->props['show_rating'];
		$show_price 							= $this->props['show_price'];
		$show_add_to_cart						= $this->props['show_add_to_cart'];
		$show_add_to_cart_on_hover				= $this->props['show_add_to_cart_on_hover'];
		$simple_add_to_cart_text				= $this->props['simple_add_to_cart_text'];
		$variable_add_to_cart_text				= $this->props['variable_add_to_cart_text'];
		$grouped_add_to_cart_text		    	= $this->props['grouped_add_to_cart_text'];
		$external_add_to_cart_text				= $this->props['external_add_to_cart_text'];
		$out_of_stock_add_to_cart_text			= $this->props['out_of_stock_add_to_cart_text'];
		$add_to_cart_use_icon					= $this->props['add_to_cart_use_icon'];
		$add_to_cart_on_hover 					= $this->props['add_to_cart_on_hover'];
		$add_to_cart_icon_placement				= $this->props['add_to_cart_icon_placement'];
		$quickview_add_to_cart_use_icon			= $this->props['quickview_add_to_cart_use_icon'];
		$quickview_add_to_cart_on_hover 		= $this->props['quickview_add_to_cart_on_hover'];
		$quickview_add_to_cart_icon_placement	= $this->props['quickview_add_to_cart_icon_placement'];
		$show_sale_badge 						= $this->props['show_sale_badge'];
		$sale_badge_text						= $this->props['sale_badge_text'];
		$sale_label_text						= $this->props['sale_label_text'];
		$show_pagination						= $this->props['show_pagination'];
		$star_rating_color						= $this->props['star_rating_color'];
		$loader_background_color				= $this->props['loader_background_color'];
		$woo_notice_background_color			= $this->props['woo_notice_background_color'];
		$add_to_cart_icon 						= et_pb_responsive_options()->get_property_values( $this->props, 'add_to_cart_icon' );
		$add_to_cart_icon 						= array_filter( $add_to_cart_icon );
		$quickview_add_to_cart_icon 			= et_pb_responsive_options()->get_property_values( $this->props, 'quickview_add_to_cart_icon' );
		$quickview_add_to_cart_icon 			= array_filter( $quickview_add_to_cart_icon );
		$lightbox_content_width					= et_pb_responsive_options()->get_property_values( $this->props, 'lightbox_content_width' );
		$title_level							= $this->props['title_level'];
		$processed_title_level 					= et_pb_process_header_level( $title_level, 'h4' );
		$processed_title_level					= esc_html( $processed_title_level );

		$products_number = ( 0 === $products_number ) ? -1 : (int) $products_number;
		wp_enqueue_script( 'dipl-woo-products-custom', PLUGIN_PATH . 'includes/modules/WooProducts/dipl-woo-products-custom.min.js', array('jquery'), '1.1.0', true );
		wp_localize_script( 'dipl-woo-products-custom', 'DiviPlusWooProductsData', array(
            'ajaxurl'   => admin_url( 'admin-ajax.php' ),
            'ajaxnonce' => wp_create_nonce( 'elicus-divi-plus-woo-products-nonce' ),
            'siteurl'   => site_url(),
        ));
        $file = et_is_builder_plugin_active() ? 'style-dbp' : 'style';
        wp_enqueue_style( 'dipl-woo-products-style', PLUGIN_PATH . 'includes/modules/WooProducts/' . $file . '.min.css', array(), '1.0.0' );

		$args = array(
			'post_type'      => 'product',
			'posts_per_page' => intval( $products_number ),
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

		$use_current_loop   = 'on' === $this->prop( 'use_current_loop', 'off' );
		$use_current_loop   = $use_current_loop && ( is_post_type_archive( 'product' ) || is_search() || et_is_product_taxonomy() );

		$args['tax_query'] = array(
			array(
				'taxonomy'         => 'product_visibility',
				'terms'            => 'exclude-from-catalog',
				'field'            => 'slug',
				'operator'         => 'NOT IN',
			),
		);

		$tax_query = array();

		if ( $use_current_loop ) {
			if ( is_product_category() ) {
				$tax_query[] = array(
					'taxonomy' => 'product_cat',
					'field'    => 'term_id',
					'terms'    => intval( get_queried_object_id() ),
					'operator' => 'IN',
				);
			} else if ( is_product_tag() ) {
				$tax_query[] = array(
					'taxonomy' => 'product_tag',
					'field'    => 'slug',
					'terms'    => sanitize_text_field( get_queried_object()->slug ),
					'operator' => 'IN',
				);
			} else if ( is_product_taxonomy() ) {
				$object = get_queried_object();
				$tax_query[] = array(
					'taxonomy' => sanitize_text_field( $object->taxonomy ),
					'field'    => 'term_id',
					'terms'    => intval( $object->term_id ),
					'operator' => 'IN',
				);
			}

			if ( is_search() ) {
				$args['s'] = sanitize_text_field( get_search_query() );
			}
		}

		if ( $include_categories && '' !== $include_categories && ! $use_current_loop ) {
			$tax_query[] = array(
				'taxonomy' => 'product_cat',
				'field'    => 'term_id',
				'terms'    => array_map( 'intval', explode( ',', $include_categories ) ),
				'operator' => 'IN',
			);
		}

		if ( $include_tags && '' !== $include_tags && ! $use_current_loop ) {
			$tax_query[] = array(
				'taxonomy' => 'product_tag',
				'field'    => 'term_id',
				'terms'    => array_map( 'intval', explode( ',', $include_tags ) ),
				'operator' => 'IN',
			);
		}

		if ( '' !== $products_order_by ) {
			if ( 'price' === $products_order_by ) {
				$args['orderby'] 	= 'meta_value_num';
				$args['meta_key'] 	= '_price';
			} else if( 'stock_status' === $products_order_by ) {
				$args['orderby'] 	= 'meta_value';
   				$args['meta_key'] 	= '_stock_status';
			} else {
				$args['orderby'] = sanitize_text_field( $products_order_by );
			}
		}

		if ( '' !== $products_order ) {
			$args['order'] = sanitize_text_field( $products_order );
		}

		switch( $view_type ) {

			case 'featured':
				$args['tax_query'][] = array(
					'taxonomy'         => 'product_visibility',
					'terms'            => 'featured',
					'field'            => 'name',
					'operator'         => 'IN',
					'include_children' => false,
				);
				break;

			case 'sale':
				if ( function_exists( 'wc_get_product_ids_on_sale' ) ) {
					$args['post__in'] = array_merge( array( 0 ), wc_get_product_ids_on_sale() );
				}
				break;

			case 'best_selling':
				$args['meta_key'] = 'total_sales';
				$args['order']    = 'DESC';
				$args['orderby']  = 'meta_value_num';
				break;

			case 'top_rated':
				$args['meta_key'] = '_wc_average_rating';
				$args['order']    = 'DESC';
				$args['orderby']  = 'meta_value_num';
				break;

			default:
				break;
		}

		$sorting = 'off';
		if ( isset( $_GET['orderby'], $_GET['dipl_woo_products_ordering_nonce'] ) ) {
			if ( wp_verify_nonce( sanitize_key( wp_unslash( $_GET['dipl_woo_products_ordering_nonce'] ) ), 'dipl-woo-products-ordering-nonce' ) ) {
				$sort_orderby = et_()->array_get_sanitized( $_GET, 'orderby', '' );
				if ( in_array( $sort_orderby, array( 'title', 'popularity', 'rating', 'date', 'price', 'price-desc' ), true ) ) {
					if ( false !== strpos( strtolower( $sort_orderby ), 'price' ) ) {
						$args['orderby'] 	= 'meta_value_num';
						$args['meta_key']   = '_price';
						$args['order']		= false !== strpos( strtolower( $sort_orderby ), 'desc' ) ? 'DESC' : 'ASC';
					}
					if ( 'title' === $sort_orderby ) {
						$args['orderby'] = 'title';
						$args['order'] = 'ASC';
					}
					if ( 'date' === $sort_orderby ) {
						$args['orderby'] = 'date';
						$args['order'] = 'DESC';
					}
					if ( 'rating' === $sort_orderby ) {
						$args['meta_key'] = '_wc_average_rating';
						$args['order']    = 'DESC';
						$args['orderby']  = 'meta_value_num';
					}
					if ( 'popularity' === $sort_orderby ) {
						$args['meta_key'] = 'total_sales';
						$args['order']    = 'DESC';
						$args['orderby']  = 'meta_value_num';
					}
					$sorting = 'on';
				}
			}
		}

		if ( 'on' === $hide_out_of_stock ) {
			$args['meta_query'] = array(
				array(
					'key'     => '_stock_status',
		            'value'   => 'instock',
		            'compare' => 'IN',
				),
				array(
					'key'     => '_stock_status',
		            'value'   => 'onbackorder',
		            'compare' => 'IN',
				),
				'relation' => 'OR'
			);
		}

		if ( ! empty( $tax_query ) ) {
			if ( ! $use_current_loop && count( $tax_query ) > 1 ) {
				$tax_query['relation'] = sanitize_text_field( $taxonomies_relation );
			}
			$args['tax_query'][] = $tax_query;
		}

		$args['tax_query']['relation'] = 'AND';

		global $wp_the_query;
		$query_backup = $wp_the_query;

		if ( $use_current_loop ) {
			$args = $this->filter_products_query($args);
			add_action( 'pre_get_posts', array( $this, 'apply_woo_widget_filters' ), 10 );
		}

		$args = apply_filters( 'dipl_woo_products_args', $args );

		$query = new WP_Query( $args );

		self::$rendering = true;

		if ( $query->have_posts() ) {

			$output = '';

			$number_of_columns 	= intval( $number_of_columns );

			if ( $simple_add_to_cart_text || $variable_add_to_cart_text || $grouped_add_to_cart_text || $external_add_to_cart_text || $out_of_stock_add_to_cart_text ) {
				$add_to_cart_text = array(
					'simple' => esc_html( $simple_add_to_cart_text ),
					'variable' => esc_html( $variable_add_to_cart_text ),
					'grouped' => esc_html( $grouped_add_to_cart_text ),
					'external' => esc_html( $external_add_to_cart_text ),
					'out_of_stock' => esc_html( $out_of_stock_add_to_cart_text ),
				);
			} else {
				$add_to_cart_text = array();
			}

			if ( 'on' === $use_masonry ) {
				wp_enqueue_script( 'elicus-images-loaded-script' );
				wp_enqueue_script( 'elicus-isotope-script' );
			}

			if ( 'on' === $show_pagination ) {
				wp_enqueue_script( 'elicus-twbs-pagination' );

				$data_props = array(
					'view_type',
					'product_layout',
					'products_number',
					'offset_number',
					'products_order' ,
					'products_order_by',
					'hide_out_of_stock',
					'enable_out_of_stock_label',
					'out_of_stock_label',
					'include_categories',
					'include_tags',
					'taxonomies_relation',
					'use_masonry',
					'enable_quickview',
					'quickview_link_text',
					'show_thumbnail',
					'thumbnail_size',
					'show_price',
					'show_rating',
					'show_add_to_cart',
					'show_add_to_cart_on_hover',
					'simple_add_to_cart_text',
					'variable_add_to_cart_text',
					'grouped_add_to_cart_text',
					'external_add_to_cart_text',
					'out_of_stock_add_to_cart_text',
					'show_sale_badge',
					'sale_badge_text',
					'sale_label_text',
					'title_level',
					'quickview_title_level',
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

				if ( ! empty( $add_to_cart_icon ) ) {
		            $icons = array();
		            foreach ( $add_to_cart_icon as $attr => $value ) {
		                $icons[] = 'data-add_to_cart_icon_' . esc_attr( $attr ) . '="' . esc_attr( et_pb_process_font_icon( $value ) ) . '"';
		            }
		            $icons = implode( ' ', $icons );
		        }

		        if ( ! empty( $quickview_add_to_cart_icon ) ) {
		            $quickview_atc_icons = array();
		            foreach ( $quickview_add_to_cart_icon as $attr => $value ) {
		                $quickview_atc_icons[] = 'data-quickview_add_to_cart_icon_' . esc_attr( $attr ) . '="' . esc_attr( et_pb_process_font_icon( $value ) ) . '"';
		            }
		            $quickview_atc_icons = implode( ' ', $quickview_atc_icons );
		        }

				$pagination = sprintf(
					'<div class="dipl_woo_products_pagination_wrapper" data-total_pages="%1$s" data-use_current_loop="%2$s" data-is_product_category="%3$s" data-is_product_tag="%4$s" data-is_product_taxonomy="%5$s" data-term_id="%6$s" data-term_slug="%7$s" data-taxonomy="%8$s" data-is_search="%9$s" data-search_query="%10$s" data-is_user_logged_in="%11$s" data-order_class="%12$s" %13$s %14$s %15$s data-sorting="%16$s" data-sort_orderby="%17$s">
						<ul></ul>
					</div>',
					$total_pages,
					$use_current_loop,
					esc_attr( is_product_category() ),
					esc_attr( is_product_tag() ),
					esc_attr( is_product_taxonomy() ),
					is_product_taxonomy() ? esc_attr( get_queried_object_id() ) : '',
					is_product_taxonomy() ? esc_attr( get_queried_object()->slug ) : '',
					is_product_taxonomy() ? esc_attr( get_queried_object()->taxonomy ) : '',
					esc_attr( is_search() ),
					esc_attr( get_search_query() ),
					esc_attr( is_user_logged_in() ),
					esc_attr( $this->get_module_order_class( 'dipl_woo_products' ) ),
					$data_atts,
					! empty( $add_to_cart_icon ) ? $icons : '',
					! empty( $quickview_add_to_cart_icon ) ? $quickview_atc_icons : '',
					$sorting,
					'on' === $sorting ? esc_attr( $sort_orderby ) : ''
				);
			}

			if ( 'on' === $enable_quickview ) {
				$order_class = $this->get_module_order_class( 'dipl_woo_products' );
				$order_number = str_replace( 'dipl_woo_products_', '', $order_class );
				$data_props = array(
					'show_sale_badge',
					'sale_badge_text',
					'sale_label_text',
					'quickview_title_level',
				);
				$data_atts = $this->props_to_html_data_attrs( $data_props );

				if ( ! empty( $quickview_add_to_cart_icon ) ) {
		            $quickview_atc_icons = array();
		            foreach ( $quickview_add_to_cart_icon as $attr => $value ) {
		                $quickview_atc_icons[] = 'data-quickview_add_to_cart_icon_' . esc_attr( $attr ) . '="' . esc_attr( et_pb_process_font_icon( $value ) ) . '"';
		            }
		            $quickview_atc_icons = implode( ' ', $quickview_atc_icons );
		            $data_atts .= $quickview_atc_icons;
		        }

				wp_enqueue_script( 'magnific-popup' );
				wp_enqueue_style( 'magnific-popup' );
				wp_enqueue_script( 'elicus-swiper-script' );
				wp_enqueue_style( 'elicus-swiper-style' );
				wp_enqueue_script( 'wc-add-to-cart-variation' );

				$quickview_quantity_bg_color 	= $this->props['quickview_quantity_bg_color'];
				$quickview_attributes_bg_color	= $this->props['quickview_attributes_bg_color'];
				if ( $quickview_quantity_bg_color ) {
					self::set_style( $render_slug, array(
	                    'selector'    => '%%order_class%%_lightbox .product .dipl_product_lightbox_content_wrapper .qty',
	                    'declaration' => sprintf( 'background-color: %1$s !important;', esc_attr( $quickview_quantity_bg_color ) ),
	                ) );
				}
				if ( $quickview_attributes_bg_color ) {
					self::set_style( $render_slug, array(
	                    'selector'    => '%%order_class%%_lightbox .product .dipl_product_lightbox_content_wrapper .variations select',
	                    'declaration' => sprintf( 'background-color: %1$s !important;', esc_attr( $quickview_attributes_bg_color ) ),
	                ) );
				}
				// phpcs:ignore WordPress,NonceVerification,NoNonce.
				if ( isset( $_REQUEST['add-to-cart'] ) ) {
					$output .= wc_print_notices( true );
				}
			}

			if ( 'on' === $show_sorting_dropdown ) {
				$sorting_options = array(
					'title' 		=> esc_html__( 'Default sorting', 'divi-plus' ),
					'popularity' 	=> esc_html__( 'Sort by popularity', 'divi-plus' ),
					'rating' 		=> esc_html__( 'Sort by average rating', 'divi-plus' ),
					'date' 			=> esc_html__( 'Sort by latest', 'divi-plus' ),
					'price'	 		=> esc_html__( 'Sort by price: low to high', 'divi-plus' ),
					'price-desc' 	=> esc_html__( 'Sort by price: high to low', 'divi-plus' ),
				);
				$output .= '<form class="dipl_woo_products_ordering" method="get">';
				$output .= '<select name="orderby" class="dipl_sorting_orderby" aria-label="Shop order">';
				foreach( $sorting_options as $value => $option ) {
					$selected = 'on' === $sorting && isset( $sort_orderby ) ? selected( $sort_orderby, $value, false ) : '';
					$output .= '<option value="'. $value .'"' . $selected . '>' . $option . '</option>';
				}
				$output .= '</select>';
				$output .= '<input type="hidden" name="dipl_woo_products_ordering_nonce" value="' . wp_create_nonce( 'dipl-woo-products-ordering-nonce' ) . '" />';
				$output .= '</form>';
			}

			$output .= sprintf(
				'<div class="dipl_woo_products_layout %1$s">',
				esc_attr( $product_layout )
			);

			if ( 'on' === $use_masonry ) {
				$output .= '<div class="dipl_woo_products_isotope_container">';
				$output .= '<div class="dipl_woo_products_isotope_item_gutter"></div>';
			}

			while ( $query->have_posts() ) {
				$query->the_post();
				$product_id = intval( get_the_ID() );

				$dipl_product = wc_get_product( $product_id );

				$output .= '<div class="dipl_woo_products_isotope_item dipl_woo_products_isotope_item_page_1">';

				if ( file_exists( get_stylesheet_directory() . '/divi-plus/layouts/woo-products/' . sanitize_file_name( $product_layout ) . '.php' ) ) {
					include get_stylesheet_directory() . '/divi-plus/layouts/woo-products/' . sanitize_file_name( $product_layout ) . '.php';
				} elseif ( file_exists( plugin_dir_path( __FILE__ ) . 'layouts/' . sanitize_file_name( $product_layout ) . '.php' ) ) {
					include plugin_dir_path( __FILE__ ) . 'layouts/' . sanitize_file_name( $product_layout ) . '.php';
				}

				$output .= '</div>';
			}

			wp_reset_postdata();

			if ( $use_current_loop ) {
				remove_action( 'pre_get_posts', array( $this, 'apply_woo_widget_filters' ), 10 );
			}

			$wp_the_query = $query_backup;

			if ( 'on' === $use_masonry ) {
				$output .= '</div>';
			}

			$output .= '</div>';

			if ( 'on' === $show_pagination ) {
				$output .= $pagination;
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

			et_pb_responsive_options()->generate_responsive_css( $width, '%%order_class%% .dipl_woo_products_isotope_item', 'width', $render_slug, '!important;', 'range' );
			et_pb_responsive_options()->generate_responsive_css( $column_spacing, '%%order_class%% .dipl_woo_products_isotope_item', array( 'margin-bottom' ), $render_slug, '!important;', 'range' );

			if ( 'on' === $use_masonry ) {
				et_pb_responsive_options()->generate_responsive_css( $column_spacing, '%%order_class%% .dipl_woo_products_isotope_item_gutter', 'width', $render_slug, '!important;', 'range' );
			} else {
				foreach ( $number_of_columns as $device => $cols ) {
					if ( 'desktop' === $device ) {
						self::set_style( $render_slug, array(
		                    'selector'    => '%%order_class%% .dipl_woo_products_isotope_item:not(:nth-child(' . absint( $cols ) . 'n+' . absint( $cols ) . '))',
		                    'declaration' => sprintf( 'margin-right: %1$s;', esc_attr( $column_spacing['desktop'] ) ),
		                    'media_query' => self::get_media_query( 'min_width_981' ),
		                ) );
		                if ( '' !== $cols ) {
							self::set_style( $render_slug, array(
			                    'selector'    => '%%order_class%% .dipl_woo_products_isotope_item:nth-child(' . absint( $cols ) . 'n+1)',
			                    'declaration' => 'clear: left;',
			                    'media_query' => self::get_media_query( 'min_width_981' ),
			                ) );
						}
					} else if ( 'tablet' === $device ) {
						self::set_style( $render_slug, array(
		                    'selector'    => '%%order_class%% .dipl_woo_products_isotope_item:not(:nth-child(' . absint( $cols ) . 'n+' . absint( $cols ) . '))',
		                    'declaration' => sprintf( 'margin-right: %1$s;', esc_attr( $column_spacing['tablet'] ) ),
		                    'media_query' => self::get_media_query( '768_980' ),
		                ) );
		                if ( '' !== $cols ) {
							self::set_style( $render_slug, array(
			                    'selector'    => '%%order_class%% .dipl_woo_products_isotope_item:nth-child(' . absint( $cols ) . 'n+1)',
			                    'declaration' => 'clear: left;',
			                    'media_query' => self::get_media_query( '768_980' ),
			                ) );
						}
					} else if ( 'phone' === $device ) {
						self::set_style( $render_slug, array(
		                    'selector'    => '%%order_class%% .dipl_woo_products_isotope_item:not(:nth-child(' . absint( $cols ) . 'n+' . absint( $cols ) . '))',
		                    'declaration' => sprintf( 'margin-right: %1$s;', esc_attr( $column_spacing['phone'] ) ),
		                    'media_query' => self::get_media_query( 'max_width_767' ),
		                ) );
		                if ( '' !== $cols ) {
							self::set_style( $render_slug, array(
			                    'selector'    => '%%order_class%% .dipl_woo_products_isotope_item:nth-child(' . absint( $cols ) . 'n+1)',
			                    'declaration' => 'clear: left;',
			                    'media_query' => self::get_media_query( 'max_width_767' ),
			                ) );
						}
					}
				}
			}

			if ( 'on' === $show_pagination ) {
				$this->generate_styles(
					array(
						'base_attr_name' => 'pagination_link_background_color',
						'selector'       => '%%order_class%% .dipl_woo_products_pagination_wrapper li a',
						'hover_selector' => '%%order_class%% .dipl_woo_products_pagination_wrapper li a:hover',
						'css_property'   => 'background-color',
						'render_slug'    => $render_slug,
						'type'           => 'color',
						'important'		 => true,
					)
				);

				$this->generate_styles(
					array(
						'base_attr_name' => 'pagination_link_color',
						'selector'       => '%%order_class%% .dipl_woo_products_pagination_wrapper li a',
						'hover_selector' => '%%order_class%% .dipl_woo_products_pagination_wrapper li a:hover',
						'css_property'   => 'color',
						'render_slug'    => $render_slug,
						'type'           => 'color',
						'important'		 => true,
					)
				);

				$this->generate_styles(
					array(
						'base_attr_name' => 'active_pagination_link_background_color',
						'selector'       => '%%order_class%% .dipl_woo_products_pagination_wrapper li.active a',
						'hover_selector' => '%%order_class%% .dipl_woo_products_pagination_wrapper li.active a:hover',
						'css_property'   => 'background-color',
						'render_slug'    => $render_slug,
						'type'           => 'color',
						'important'		 => true,
					)
				);

				$this->generate_styles(
					array(
						'base_attr_name' => 'active_pagination_link_color',
						'selector'       => '%%order_class%% .dipl_woo_products_pagination_wrapper li.active a',
						'hover_selector' => '%%order_class%% .dipl_woo_products_pagination_wrapper li.active a:hover',
						'css_property'   => 'color',
						'render_slug'    => $render_slug,
						'type'           => 'color',
						'important'		 => true,
					)
				);
			}

			if ( '' !== $this->props['sale_badge_bg_color'] ) {
				self::set_style( $render_slug, array(
                    'selector'    => '%%order_class%% .dipl_single_woo_product_sale_badge',
                    'declaration' => sprintf( 'background-color: %1$s !important;', esc_attr( $this->props['sale_badge_bg_color'] ) ),
                ) );
			}

			if ( 'on' === $show_add_to_cart ) {
				$add_to_cart_text_align = et_pb_responsive_options()->get_property_values( $this->props,'add_to_cart_text_align' );
				foreach( $add_to_cart_text_align as &$align ) {
					$align = str_replace( array( 'left', 'right', 'justify' ), array( 'flex-start', 'flex-end', 'flex-start' ), $align );
				}

				et_pb_responsive_options()->generate_responsive_css( $add_to_cart_text_align, '%%order_class%% .dipl_single_woo_product_add_to_cart a.button, %%order_class%% .dipl_single_woo_product_add_to_cart a.add_to_cart_button, %%order_class%% .dipl_single_woo_product_add_to_cart a.added_to_cart', 'justify-content', $render_slug, '!important;', 'type' );

				if ( 'on' === $show_add_to_cart_on_hover ) {
					self::set_style( $render_slug, array(
	                    'selector'    => '%%order_class%% .dipl_single_woo_product .add_to_cart_inline',
	                    'declaration' => 'position: absolute; top: 0; left: 0; width: 100%; height: inherit; visibility: hidden; opacity: 0; transform: translateY(0);',
	                    'media_query' => self::get_media_query( 'min_width_981' ),
	                ) );

	                self::set_style( $render_slug, array(
	                    'selector'    => '%%order_class%% .dipl_single_woo_product:hover .add_to_cart_inline',
	                    'declaration' => 'visibility: visible; opacity: 1;',
	                    'media_query' => self::get_media_query( 'min_width_981' ),
	                ) );
					
					if ( 'layout2' === $product_layout ) {
						self::set_style( $render_slug, array(
							'selector'    => '%%order_class%% .dipl_single_woo_product .add_to_cart_inline',
							'declaration' => 'position: relative;',
							'media_query' => self::get_media_query( 'min_width_981' ),
						) );					
					}
				}

				if ( 'on' === $add_to_cart_use_icon ) {
					$icon_pseudo_selector = 'left' === $add_to_cart_icon_placement ? 'before'  : 'after';

					self::set_style( $render_slug, array(
	                    'selector'    => '%%order_class%% .dipl_single_woo_product_add_to_cart a.button:' . $icon_pseudo_selector . ', %%order_class%% .dipl_single_woo_product_add_to_cart a.add_to_cart_button:' . $icon_pseudo_selector,
	                    'declaration' => 'display: inline-block;',
	                ) );

					if ( ! empty( $add_to_cart_icon ) ) {
						foreach( $add_to_cart_icon as $device => &$icon ) {
							$icon = 'attr(data-icon_' . $device . ')';
						}
						et_pb_responsive_options()->generate_responsive_css( $add_to_cart_icon, '%%order_class%% .dipl_single_woo_product_add_to_cart a.button:not(.loading):' . $icon_pseudo_selector . ', %%order_class%% .dipl_single_woo_product_add_to_cart a.add_to_cart_button:not(.loading):' . $icon_pseudo_selector, 'content', $render_slug, '!important;', 'icon' );
					}

					if ( 'off' === $add_to_cart_on_hover ) {
						self::set_style( $render_slug, array(
		                    'selector'    => '%%order_class%% .dipl_single_woo_product_add_to_cart a.button:' . $icon_pseudo_selector . ', %%order_class%% .dipl_single_woo_product_add_to_cart a.add_to_cart_button:' . $icon_pseudo_selector,
		                    'declaration' => 'opacity: 1; margin-left: 0; margin-right: 0;',
		                ) );
					}

					if ( class_exists( 'ET_Builder_Module_Helper_Style_Processor' ) && method_exists( 'ET_Builder_Module_Helper_Style_Processor', 'process_extended_icon' ) ) {
						$this->generate_styles(
							array(
								'utility_arg'    => 'icon_font_family',
								'render_slug'    => $render_slug,
								'base_attr_name' => 'add_to_cart_icon',
								'important'      => true,
								'selector'       => '%%order_class%% .dipl_single_woo_product_add_to_cart a.button:not(.loading):' . $icon_pseudo_selector . ', %%order_class%% .dipl_single_woo_product_add_to_cart a.add_to_cart_button:not(.loading):' . $icon_pseudo_selector,
								'processor'      => array(
									'ET_Builder_Module_Helper_Style_Processor',
									'process_extended_icon',
								),
							)
						);
					}
				}
			}

			if ( 'on' === $enable_quickview ) {
				if ( 'on' === $quickview_add_to_cart_use_icon ) {
					$icon_pseudo_selector = 'left' === $quickview_add_to_cart_icon_placement ? 'before'  : 'after';

					self::set_style( $render_slug, array(
	                    'selector'    => "{$this->main_css_element}_lightbox .product .dipl_product_lightbox_content_wrapper .single_add_to_cart_button:not(.loading):" . $icon_pseudo_selector . ", {$this->main_css_element}_lightbox .product .dipl_product_lightbox_content_wrapper .single_add_to_cart_button.added_to_cart:not(.loading):" . $icon_pseudo_selector,
	                    'declaration' => 'display: inline-block;',
	                ) );

					if ( ! empty( $quickview_add_to_cart_icon ) ) {
						foreach( $quickview_add_to_cart_icon as $device => &$icon ) {
							$icon = 'attr(data-icon_' . $device . ')';
						}
						et_pb_responsive_options()->generate_responsive_css( $quickview_add_to_cart_icon, "{$this->main_css_element}_lightbox .product .dipl_product_lightbox_content_wrapper .single_add_to_cart_button:not(.loading):" . $icon_pseudo_selector . ", {$this->main_css_element}_lightbox .product .dipl_product_lightbox_content_wrapper .single_add_to_cart_button.added_to_cart:not(.loading):" . $icon_pseudo_selector, 'content', $render_slug, '', 'icon' );
					}

					if ( 'off' === $quickview_add_to_cart_on_hover ) {
						self::set_style( $render_slug, array(
		                    'selector'    => "{$this->main_css_element}_lightbox .product .dipl_product_lightbox_content_wrapper .single_add_to_cart_button:not(.loading):" . $icon_pseudo_selector . ", {$this->main_css_element}_lightbox .product .dipl_product_lightbox_content_wrapper .single_add_to_cart_button.added_to_cart:not(.loading):" . $icon_pseudo_selector,
		                    'declaration' => 'opacity: 1; margin-left: 0; margin-right: 0;',
		                ) );
					}

					if ( class_exists( 'ET_Builder_Module_Helper_Style_Processor' ) && method_exists( 'ET_Builder_Module_Helper_Style_Processor', 'process_extended_icon' ) ) {
						$this->generate_styles(
							array(
								'utility_arg'    => 'icon_font_family',
								'render_slug'    => $render_slug,
								'base_attr_name' => 'quickview_add_to_cart_icon',
								'important'      => true,
								'selector'       => "{$this->main_css_element}_lightbox .product .dipl_product_lightbox_content_wrapper .single_add_to_cart_button:not(.loading):" . $icon_pseudo_selector . ", {$this->main_css_element}_lightbox .product .dipl_product_lightbox_content_wrapper .single_add_to_cart_button.added_to_cart:not(.loading):" . $icon_pseudo_selector,
								'processor'      => array(
									'ET_Builder_Module_Helper_Style_Processor',
									'process_extended_icon',
								),
							)
						);
					}
				}
				$lightbox_bg_color 			= $this->props['lightbox_bg_color'];
				$lightbox_close_icon_color	= $this->props['lightbox_close_icon_color'];
				self::set_style( $render_slug, array(
                    'selector'    => "{$this->main_css_element}_lightbox.mfp-bg",
                    'declaration' => sprintf('background-color: %1$s !important;', esc_attr( $lightbox_bg_color ) ),
                ) );
				self::set_style( $render_slug, array(
                    'selector'    => "{$this->main_css_element}_lightbox .mfp-close",
                    'declaration' => sprintf('color: %1$s !important;', esc_attr( $lightbox_close_icon_color ) ),
                ) );
                self::set_style( $render_slug, array(
                    'selector'    => '%%order_class%% .dipl_single_woo_product_quickview_wrapper a',
                    'declaration' => sprintf( 'background-color: %1$s !important;', esc_attr( $quickview_link_bg_color ) ),
                ) );
                self::set_style( $render_slug, array(
                    'selector'    => '%%order_class%% .dipl_product_lightbox_loader:before',
                    'declaration' => sprintf( 'background-color: %1$s !important;', esc_attr( $loader_background_color ) ),
                ) );
                self::set_style( $render_slug, array(
                    'selector'    => '%%order_class%% .woocommerce-message',
                    'declaration' => sprintf( 'background-color: %1$s !important;', esc_attr( $woo_notice_background_color ) ),
                ) );
                et_pb_responsive_options()->generate_responsive_css( $lightbox_content_width, "{$this->main_css_element}_lightbox .mfp-content", 'width', $render_slug, '!important;', 'range' );
			}

			if ( 'on' === $show_rating ) {
				self::set_style( $render_slug, array(
                    'selector'    => "{$this->main_css_element} .dipl_single_woo_product_star_rating .star-rating span:before",
                    'declaration' => sprintf('color: %1$s !important;', esc_attr( $star_rating_color ) ),
                ) );
                $star_rating_alignment = et_pb_responsive_options()->get_property_values( $this->props,'star_rating_text_text_align' );
				foreach( $star_rating_alignment as &$align ) {
					$align = str_replace( array( 'left', 'right', 'justify' ), array( 'flex-start', 'flex-end', 'flex-start' ), $align);
				}
				if ( ! empty( array_filter( $star_rating_alignment ) ) ) {
					et_pb_responsive_options()->generate_responsive_css( $star_rating_alignment, '%%order_class%% .dipl_single_woo_product_star_rating', 'justify-content', $render_slug, '!important;', 'alignment' );
				}
			}

			if ( 'on' === $show_sorting_dropdown ) {
				/* Alignment */
		        $dropdown_alignment_values   = et_pb_responsive_options()->get_property_values( $this->props, 'sorting_dropdown_alignment' );
		        et_pb_responsive_options()->generate_responsive_css( $dropdown_alignment_values, '%%order_class%% .dipl_woo_products_ordering', 'text-align', $render_slug, '', 'type' );

		        $this->generate_styles(
					array(
						'base_attr_name' => 'sorting_dropdown_background_color',
						'selector'       => '%%order_class%% .dipl_sorting_orderby',
						'hover_selector' => '%%order_class%% .dipl_sorting_orderby:hover',
						'css_property'   => 'background-color',
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
					'product_bg' => array(
						'normal' => "{$this->main_css_element} .dipl_single_woo_product",
						'hover' => "{$this->main_css_element} .dipl_single_woo_product:hover",
		 			),
		 			'add_to_cart_bg' => array(
		 				'normal' => "{$this->main_css_element} .dipl_single_woo_product_add_to_cart a.button, {$this->main_css_element} .dipl_single_woo_product_add_to_cart a.add_to_cart_button, {$this->main_css_element} .dipl_single_woo_product_add_to_cart a.added_to_cart",
		 				'hover' => "{$this->main_css_element} .dipl_single_woo_product_add_to_cart a.button:hover, {$this->main_css_element} .dipl_single_woo_product_add_to_cart a.add_to_cart_button:hover, {$this->main_css_element} .dipl_single_woo_product_add_to_cart a.added_to_cart:hover",
		 			),
					'quickview_add_to_cart_bg' => array(
						'normal' => "{$this->main_css_element}_lightbox .product .dipl_product_lightbox_content_wrapper .button.single_add_to_cart_button, {$this->main_css_element}_lightbox .product .dipl_product_lightbox_content_wrapper .button.single_add_to_cart_button.added_to_cart",
						'hover' => "{$this->main_css_element}_lightbox .product .dipl_product_lightbox_content_wrapper .button.single_add_to_cart_button:hover, {$this->main_css_element}_lightbox .product .dipl_product_lightbox_content_wrapper .button.single_add_to_cart_button.added_to_cart:hover",
					),
				),
			);

			DiviPlusHelper::process_background( $args );
			$fields = array( 'product_margin_padding', 'add_to_cart_margin_padding', 'sorting_dropdown_margin_padding' );
			DiviPlusHelper::process_advanced_margin_padding_css( $this, $render_slug, $this->margin_padding, $fields );

		} else {
			$output = '<div class="entry">' . esc_html( $no_result_text ) . '</div>';
		}

		self::$rendering = false;

		return et_core_intentionally_unescaped( $output, 'html' );
	}

	/**
	 * Filter the products query arguments.
	 * 
	 * @param array $args Query array.
	 *
	 * @return array
	 */
	public function filter_products_query( $args ) {
		if ( is_search() ) {
			$args['s'] = get_search_query();
		}

		if ( function_exists( 'WC' ) ) {
			$args['meta_query'] = WC()->query->get_meta_query( et_()->array_get( $args, 'meta_query', array() ), true );
			$args['tax_query']  = WC()->query->get_tax_query( et_()->array_get( $args, 'tax_query', array() ), true );

			// Add fake cache-busting argument as the filtering is actually done in self::apply_woo_widget_filters().
			$args['nocache'] = microtime( true );
		}

		return $args;
	}

	/* Filter the products shortcode query so Woo widget filters apply.
	 *
	 * @param WP_Query $query WP QUERY object.
	 */
	public function apply_woo_widget_filters( $query ) {
		global $wp_the_query;

		// Trick Woo filters into thinking the products shortcode query is the
		// main page query as some widget filters have is_main_query checks.
		$wp_the_query = $query;

		if ( function_exists( 'WC' ) ) {
			add_filter( 'posts_clauses', array( WC()->query, 'price_filter_post_clauses' ), 10, 2 );
		}
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
		in_array( 'dipl_woo_products', $modules, true ) &&
		et_is_woocommerce_plugin_active()
	) {
		new DIPL_WooProducts();
	}
} else {
	if ( et_is_woocommerce_plugin_active() ) {
		new DIPL_WooProducts();
	}
}