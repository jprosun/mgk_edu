<?php
/**
 * @author      Elicus <hello@elicus.com>
 * @link        https://www.elicus.com/
 * @copyright   2023 Elicus Technologies Private Limited
 * @version     1.1.1
 */
class DIPL_WooProductsAccordion extends ET_Builder_Module {
	public $slug       = 'dipl_woo_products_accordion';
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
		$this->name             = esc_html__( 'DP Woo Products Accordion', 'divi-plus' );
		$this->main_css_element = '%%order_class%%';
	}

	public function get_settings_modal_toggles() {
		return array(
			'general'  => array(
				'toggles' => array(
					'main_content'      => array(
						'title' => esc_html__( 'Content', 'divi-plus' ),
					),
					'display_settings'  => array(
						'title' => esc_html__( 'Display', 'divi-plus' ),
					),
					'accordion_settings' => array(
						'title' => esc_html__( 'Accordion', 'divi-plus' ),
					),
				)
			),
			'advanced' => array(
				'toggles' => array(
					'accordion' 	=> array(
						'title' => esc_html__( 'Accordion', 'divi-plus' ),
						'tabbed_subtoggles' => true,
						'bb_icons_support'  => true,
						'sub_toggles'       => array(
							'normal'     => array(
								'name' => esc_html__( 'Normal', 'divi-plus' ),
							),
							'active' => array(
								'name' => esc_html__( 'Active', 'divi-plus' ),
							),
						),
					),
					'product_settings' => array(
						'title' => esc_html__( 'Product', 'divi-plus' ),
					),
					'text' 		=> array(
						'title' => esc_html__( 'Text', 'divi-plus' ),
					),
					'categories' => array(
						'title' => esc_html__( 'Categories', 'divi-plus' ),
					),
					'title' 	=> array(
						'title' => esc_html__( 'Title', 'divi-plus' ),
					),
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
					'star_rating' => array (
						'title' => esc_html__( 'Star Rating', 'divi-plus' ),
					),
					'price_text' => array(
						'title' => esc_html__( 'Price', 'divi-plus' ),
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
					'sale_badge' => array(
						'title' => esc_html__( 'Sale Badge', 'divi-plus' ),
					),
					'out_of_stock' => array (
						'title' => esc_html__( 'Out of Stock', 'divi-plus' ),
					),
					'add_to_cart_button' => array (
						'title' => esc_html__( 'Add to Cart Button', 'divi-plus' ),
					),
				)
			)
		);
	}

	public function get_advanced_fields_config() {
		return array(
			'fonts' => array(
				'categories' => array(
					'label'          => esc_html__( 'Category', 'divi-plus' ),
					'font_size'      => array(
						'default' => '14px',
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
					'text_color' => array(
						'default' => ''
					),
					'css'            => array(
						'text_align' => "{$this->main_css_element} .dipl-woo-product__categories",
						'main'       => "{$this->main_css_element} .dipl-woo-product__categories a",
						'hover'      => "{$this->main_css_element} .dipl-woo-product__categories a:hover",
                        'important'  => 'all',
					),
                    'tab_slug'       => 'advanced',
                    'toggle_slug'    => 'categories',
				),
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
						'default' => 'h2',
					),
					'text_color' => array(
						'default' => '',
					),
					'css'            => array(
						'main'       => "{$this->main_css_element} .dipl-woo-product__title a",
						'hover'      => "{$this->main_css_element} .dipl-woo-product__title a:hover",
                        'important'  => 'all',
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
                        'main' => "{$this->main_css_element} .dipl-woo-product__description, {$this->main_css_element} .dipl-woo-product__description p",
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
                        'main' => "{$this->main_css_element} .dipl-woo-product__description a",
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
                        'main' => "{$this->main_css_element} .dipl-woo-product__description ul li",
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
                        'main' => "{$this->main_css_element} .dipl-woo-product__description ol li",
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
                        'main' => "{$this->main_css_element} .dipl-woo-product__description blockquote",
                        'important' => 'all',
                    ),
                    'tab_slug'    => 'advanced',
                    'toggle_slug' => 'desc',
                    'sub_toggle'  => 'quote',
                ),
				'star_rating_text' => array(
					'label'          => esc_html__( 'Star Rating', 'divi-plus' ),
					'text_color' => array(
						'default' => ''
					),
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
					'text_align' => array(
						'default' => '',
						'options' => et_builder_get_text_orientation_options( array( 'justified' ), array() ),
					),
					'hide_line_height' => true,
					'hide_font'	=> true,
					'hide_text_shadow' => true,
					'css' => array(
						'text_align' => "{$this->main_css_element} .dipl-products-accordion-item .dipl-woo-product__rating",
						'color'      => "{$this->main_css_element} .dipl-products-accordion-item .star-rating span:before",
						'main'       => "{$this->main_css_element} .dipl-products-accordion-item .star-rating",
						'important'  => 'all',
					),
					'tab_slug'	=> 'advanced',
					'toggle_slug' => 'star_rating',
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
					'text_align' => array(
						'default' => '',
						'options' => et_builder_get_text_orientation_options( array( 'justified' ), array() ),
					),
					'css' => array(
						'text_align' => "{$this->main_css_element} .dipl-products-accordion-item .dipl-woo-product__price",
						'main'  => "{$this->main_css_element} .dipl-products-accordion-item .dipl-woo-product__price span, {$this->main_css_element} .dipl-products-accordion-item .dipl-woo-product__price .price del",
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
					'hide_text_align' => true,
					'css' => array(
						'main'  => "{$this->main_css_element} .dipl-products-accordion-item .dipl-woo-product__price .price ins span",
						'important' => 'all',
					),
					'tab_slug'	=> 'advanced',
					'toggle_slug' => 'price_text',
					'sub_toggle' => 'sale_price_text',
				),
				'sale_badge' => array(
					'label'          => esc_html__( 'Sale Badge', 'divi-plus' ),
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
						'main'  => "{$this->main_css_element} .dipl-products-accordion-item .dipl-onsale",
						'important' => 'all',
					),
					'tab_slug'	=> 'advanced',
					'toggle_slug' => 'sale_badge',
				),
				'outofstock_label' => array(
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
					'text_align'     => array(
						'default' => '',
						'options' => et_builder_get_text_orientation_options( array( 'justified' ), array() ),
					),
					'css' => array(
						'text_align' => "{$this->main_css_element} .dipl-woo-product__soldout",
						'main'  => "{$this->main_css_element} .dipl-woo-product__soldout .out-of-stock",
						'important' => 'all',
					),
					'tab_slug'	=> 'advanced',
					'toggle_slug' => 'out_of_stock',
				),
			),
			'button' => array(
			    'add_to_cart' => array(
				    'label' => esc_html__( 'Add to Cart Button', 'divi-plus' ),
				    'css' => array(
						'main'      => "{$this->main_css_element} .dipl-woo-product__add_to_cart a.button",
						'alignment' => false,
						'important' => 'all',
					),
		            'margin_padding' => array(
						'css' => array(
							'margin'    => "{$this->main_css_element} .dipl-woo-product__add_to_cart a.button",
							'padding'   => "{$this->main_css_element} .dipl-woo-product__add_to_cart a.button",
							'important' => 'all',
						),
		            ),
					'border_width'		=> array(
						'default' => '2px',
					),
					'box_shadow'      	=> false,
				    'depends_on'        => array( 'show_add_to_cart' ),
		            'depends_show_if'   => 'on',
				    'tab_slug'          => 'advanced',
				    'toggle_slug'       => 'add_to_cart_button',
			    ),
			),
			'custom_product_margin' => array(
				'product_spacing' => array(
					'label'          => esc_html__( 'Product Spacing', 'divi-plus' ),
					'margin_padding' => array(
						'use_margin' => false,
						'css' => array(
							// 'margin' => '%%order_class%% .dipl-products-accordion-wrap .dipl-products-accordion-item',
							'main'      => '%%order_class%% .dipl-products-accordion-item .dipl-products-accordion-item-inner',
							'important' => 'all',
						),
					),
					'tab_slug'       => 'advanced',
					'toggle_slug'    => 'product_settings',
				)
			),
			'margin_padding'	=> array(
				'css' => array(
					'main'      => '%%order_class%%',
					'important' => 'all',
				),
			),
			'borders'	=> array(
				'product_border' => array(
					'label'	=> esc_html__( 'Product Border', 'divi-plus' ),
					'css' => array(
						'main' => array(
							'border_radii'  => '%%order_class%% .dipl-products-accordion-wrap .dipl-products-accordion-item',
							'border_styles' => '%%order_class%% .dipl-products-accordion-wrap .dipl-products-accordion-item',
							'important' => 'all',
						),
					),
					'tab_slug'		=> 'advanced',
					'toggle_slug'	=> 'product_settings',
				),
				'sale_badge' => array(
					'label_prefix'    => esc_html__( 'Sale Badge', 'divi-plus' ),
					'css' => array(
						'main' => array(
							'border_radii'  => '%%order_class%% .dipl-products-accordion-item .dipl-onsale, %%order_class%% .dipl-products-accordion-item .dipl-onsale:hover',
							'border_styles' => '%%order_class%% .dipl-products-accordion-item .dipl-onsale, %%order_class%% .dipl-products-accordion-item .dipl-onsale:hover',
							'important' => 'all',
						),
					),
					'show_if'          => array(
						'show_sale_badge' => 'on',
					),
					'tab_slug' => 'advanced',
					'toggle_slug' => 'sale_badge',
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
				'product_box' => array(
					'label'			=> esc_html__( 'Product Box Shadow', 'divi-plus' ),
					'css'			=> array(
						'main' => '%%order_class%% .dipl-products-accordion-wrap .dipl-products-accordion-item',
						'important' => 'all',
					),
					'tab_slug'		=> 'advanced',
					'toggle_slug'	=> 'product_settings',
				),
				'default' => array(
					'css' => array(
						'main' => '%%order_class%%',
					),
				),
			),
			'height' => array(
				'options' => array(
					'height' => array(
						'label'          => esc_html__( 'Height', 'divi-plus' ),
						'range_settings' => array(
							'min'  => 1,
							'max'  => 500,
							'step' => 1,
						),
						'hover'          	=> false,
						'validate_unit'	 	=> true,
						'default'		 	=> '350px',
						'default_on_front' 	=> '350px',
					),
					'min_height' => array(
						'label'          => esc_html__( 'Min Height', 'divi-plus' ),
						'range_settings' => array(
							'min'  => 1,
							'max'  => 500,
							'step' => 1,
						),
						'hover'          => false,
						'validate_unit'	 => true,
					),
					'max_height' => array(
						'label'          => esc_html__( 'Max Height', 'divi-plus' ),
						'range_settings' => array(
							'min'  => 1,
							'max'  => 500,
							'step' => 1,
						),
						'hover'          => false,
						'validate_unit'	 => true,
					),
				),
				'css' => array(
					'main' => "{$this->main_css_element} .dipl-products-accordion-wrap",
                    'important' => 'all',
				),
			),
			'text' => array(
				'use_background_layout' => true,
				'options'               => array(
					'background_layout' => array(
                        'default'           => 'dark',
						'default_on_front'  => 'dark',
						'hover'             => 'tabs',
					),
					'text_orientation'  => array(
                        'default'          => '',
						'default_on_front' => '',
					),
				),
			),
			'text_shadow'   => false,
			'filters'       => false,
			'link_options'  => false,
			'background' => array(
				'use_background_video' => false,
			),
		);
	}

	public function get_fields() {
		$search_in = array( 
			'categories'  => esc_html__( 'Categories', 'divi-plus' ),
			'title'       => esc_html__( 'Title', 'divi-plus' ),
			'description' => esc_html__( 'Description', 'divi-plus' ),
		);

		// Generate background options
		$acc_bg_option = array_merge(
			$this->generate_background_options( 'accordion_bg', 'color', 'advanced', 'accordion', 'accordion_bg_color' ),
			$this->generate_background_options( 'accordion_bg', 'gradient', 'advanced', 'accordion', 'accordion_bg_color' )
		);
		unset( $acc_bg_option['accordion_bg_color_gradient_overlays_image'] );

		$active_acc_bg_option = array_merge(
			$this->generate_background_options( 'active_accordion_bg', 'color', 'advanced', 'accordion', 'active_accordion_bg_color' ),
			$this->generate_background_options( 'active_accordion_bg', 'gradient', 'advanced', 'accordion', 'active_accordion_bg_color' )
		);
		unset( $active_acc_bg_option['active_accordion_bg_color_gradient_overlays_image'] );

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
				'products_number' => array(
					'label'            => esc_html__( 'Number of Products', 'divi-plus' ),
					'type'             => 'text',
					'option_category'  => 'configuration',
					'default'          => 4,
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
						'stock_status'	=> esc_html__( 'Stock Status', 'divi-plus' ),
						'price'			=> esc_html__( 'Price', 'divi-plus' ), 
						'none'     		=> esc_html__( 'None', 'divi-plus' ),
					),
					'default'          => 'title',
					'show_if_not'      => array(
						'view_type' => array( 'best_selling', 'top_rated' ),
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
					'default'          => 'ASC',
					'show_if_not'      => array(
						'view_type' => array( 'best_selling', 'top_rated' ),
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
					'tab_slug'         => 'general',
					'toggle_slug'      => 'main_content',
					'description'      => esc_html__( 'Hide out of stock products from the loop.', 'divi-plus' ),
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

				'show_categories' => array(
					'label'     		=> esc_html__( 'Show Categories', 'divi-plus' ),
					'type'              => 'yes_no_button',
					'option_category'   => 'basic_option',
					'options'           => array(
						'off' => esc_html__( 'No', 'divi-plus' ),
						'on'  => esc_html__( 'Yes', 'divi-plus' ),
					),
					'default'           => 'on',
					'tab_slug'          => 'general',
					'toggle_slug'       => 'display_settings',
					'description'       => esc_html__( 'Here you can choose whether or not show product categories.', 'divi-plus' ),
					'computed_affects'  => array(
						'__products_data',
					),
				),
				'show_title' => array(
					'label'     		=> esc_html__( 'Show Title', 'divi-plus' ),
					'type'              => 'yes_no_button',
					'option_category'   => 'basic_option',
					'options'           => array(
						'off' => esc_html__( 'No', 'divi-plus' ),
						'on'  => esc_html__( 'Yes', 'divi-plus' ),
					),
					'default'           => 'on',
					'tab_slug'          => 'general',
					'toggle_slug'       => 'display_settings',
					'description'       => esc_html__( 'Here you can choose whether or not show product title.', 'divi-plus' ),
					'computed_affects'  => array(
						'__products_data',
					),
				),
				'show_description' => array(
					'label'     		=> esc_html__( 'Show Description', 'divi-plus' ),
					'type'              => 'yes_no_button',
					'option_category'   => 'basic_option',
					'options'           => array(
						'off' => esc_html__( 'No', 'divi-plus' ),
						'on'  => esc_html__( 'Yes', 'divi-plus' ),
					),
					'default'           => 'off',
					'tab_slug'          => 'general',
					'toggle_slug'       => 'display_settings',
					'description'       => esc_html__( 'Here you can choose whether or not show product description.', 'divi-plus' ),
					'computed_affects'  => array(
						'__products_data',
					),
				),
				'inactive_state' => array(
					'label'            => esc_html__( 'Display in Inactive State', 'divi-plus' ),
					'type'             => 'multiple_checkboxes',
					'option_category'  => 'basic_option',
					'options'          => $search_in,
					'default'          => 'off|off|off',
					'default_on_front' => 'off|off|off',
					'tab_slug'         => 'general',
					'toggle_slug'      => 'display_settings',
					'description'      => esc_html__( 'Here you can choose whether or not to show the title and icon in default state.', 'divi-plus' ),
					'computed_affects' 	=> array(
						'__products_data',
					),
				),
				'description_length' => array(
					'label'           => esc_html__( 'Description Length', 'divi-plus' ),
					'type'            => 'range',
					'option_category' => 'basic_option',
					'range_settings'  => array(
						'min'  => '0',
						'max'  => '500',
						'step' => '1',
					),
					'unitless'         => true,
					'default'          => '100',
					'default_on_front' => '100',
					'mobile_options'   => false,
					
					'tab_slug'         => 'general',
					'toggle_slug'      => 'display_settings',
					'description'      => esc_html__( 'Move the slider or input the value to increase or decrease the length of description, select zero to remove length limit.', 'divi-plus' ),
					'computed_affects' 	=> array(
						'__products_data',
					),
				),
				'show_rating' => array(
					'label'     		=> esc_html__( 'Show Rating', 'divi-plus' ),
					'type'              => 'yes_no_button',
					'option_category'   => 'basic_option',
					'options'           => array(
						'off' => esc_html__( 'No', 'divi-plus' ),
						'on'  => esc_html__( 'Yes', 'divi-plus' ),
					),
					'default'           => 'off',
					'tab_slug'          => 'general',
					'toggle_slug'       => 'display_settings',
					'description'       => esc_html__( 'Here you can choose whether or not show product rating.', 'divi-plus' ),
					'computed_affects'  => array(
						'__products_data',
					),
				),
				'show_price' => array(
					'label'     		=> esc_html__( 'Show Price', 'divi-plus' ),
					'type'              => 'yes_no_button',
					'option_category'   => 'basic_option',
					'options'           => array(
						'off' => esc_html__( 'No', 'divi-plus' ),
						'on'  => esc_html__( 'Yes', 'divi-plus' ),
					),
					'default'           => 'on',
					'tab_slug'          => 'general',
					'toggle_slug'       => 'display_settings',
					'description'       => esc_html__( 'Here you can choose whether or not show product price.', 'divi-plus' ),
					'computed_affects'  => array(
						'__products_data',
					),
				),
				'show_add_to_cart' => array(
					'label'     		=> esc_html__( 'Show Add to Cart Button', 'divi-plus' ),
					'type'              => 'yes_no_button',
					'option_category'   => 'basic_option',
					'options'           => array(
						'off' => esc_html__( 'No', 'divi-plus' ),
						'on'  => esc_html__( 'Yes', 'divi-plus' ),
					),
					'default'           => 'on',
					'tab_slug'          => 'general',
					'toggle_slug'       => 'display_settings',
					'description'       => esc_html__( 'Here you can choose whether or not show product add to cart button.', 'divi-woocommerce-extended' ),
					'computed_affects'  => array(
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
					'tab_slug'        	=> 'general',
					'toggle_slug'     	=> 'display_settings',
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
						'show_sale_badge' 	=> 'on',
					),
					'tab_slug'        	=> 'general',
					'toggle_slug'     	=> 'display_settings',
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
						'show_sale_badge' 	=> 'on',
						'sale_badge_text'	=> 'label',
					),
					'default'		   => esc_html__( 'Sale!', 'divi-plus' ),
					'tab_slug'         => 'general',
					'toggle_slug'      => 'display_settings',
					'description'      => esc_html__( 'Here you can specify the text for Sale label.', 'divi-plus' ),
					'computed_affects' => array(
						'__products_data',
					),
				),
				'show_outofstock_label' => array(
					'label'           	=> esc_html__( 'Show Out of Stock Label', 'divi-plus' ),
					'type'            	=> 'yes_no_button',
					'option_category' 	=> 'configuration',
					'options'         	=> array(
						'on'  => esc_html__( 'Yes', 'divi-plus' ),
						'off' => esc_html__( 'No', 'divi-plus' ),
					),
					'default'         	=> 'off',
					'show_if'		   => array(
						'hide_out_of_stock' => 'off',
					),
					'tab_slug'        	=> 'general',
					'toggle_slug'     	=> 'display_settings',
					'description'     	=> esc_html__( 'Choose whether or not the Out of Stock badge should be visible.', 'divi-plus' ),
					'computed_affects' 	=> array(
						'__products_data',
					),
				),
				'out_of_stock_label' => array(
					'label'            => esc_html__( 'Out of Stock Label Text', 'divi-plus' ),
					'type'             => 'text',
					'option_category'  => 'configuration',
					'show_if'        => array(
						'hide_out_of_stock'     => 'off',
						'show_outofstock_label' => 'on',
					),
					'default'		   => esc_html__( 'Out of Stock', 'divi-plus' ),
					'tab_slug'         => 'general',
					'toggle_slug'      => 'display_settings',
					'description'      => esc_html__( 'Here you can specify the text for out of stock label.', 'divi-plus' ),
					'computed_affects' => array(
						'__products_data',
					),
				),

				'accordion_trigger' => array(
					'label'             => esc_html__( 'Accordion Trigger', 'divi-plus' ),
					'type'              => 'select',
					'option_category'   => 'basic_option',
					'options'           => array(
						'hover' => esc_html__( 'Hover', 'divi-plus' ),
						'click' => esc_html__( 'Click', 'divi-plus' ),
					),
					'default'           => 'hover',
					'default_on_front'  => 'hover',
					'tab_slug'          => 'general',
					'toggle_slug'       => 'accordion_settings',
					'description'       => esc_html__( 'Here you can select the accordion trigger.', 'divi-plus' ),
				),
				'accordion_orientation' => array(
					'label'             => esc_html__( 'Accordion Orientation', 'divi-plus' ),
					'type'              => 'select',
					'option_category'   => 'basic_option',
					'options'           => array(
						'horizontal' => esc_html__( 'Horizontal', 'divi-plus' ),
						'vertical'   => esc_html__( 'Vertical', 'divi-plus' ),
					),
					'mobile_options'    => true,
					'default'           => 'horizontal',
					'default_on_front'  => 'horizontal',
					'tab_slug'          => 'general',
					'toggle_slug'       => 'accordion_settings',
					'description'       => esc_html__( 'Here you can select the accordion orientation.', 'divi-plus' ),
				),
				'content_alignment' => array(
					'label'             => esc_html__( 'Content Alignment', 'divi-plus' ),
					'type'              => 'select',
					'option_category'   => 'configuration',
					'options'           => array(
						'top_left'      => esc_html__( 'Top Left', 'divi-plus' ),
						'top_right'     => esc_html__( 'Top Right', 'divi-plus' ),
						'top_center'    => esc_html__( 'Top Center', 'divi-plus' ),
						'center'        => esc_html__( 'Center', 'divi-plus' ),
						'bottom_left'   => esc_html__( 'Bottom Left', 'divi-plus' ),
						'bottom_right'  => esc_html__( 'Bottom Right', 'divi-plus' ),
						'bottom_center' => esc_html__( 'Bottom Center', 'divi-plus' ),
					),
					'default'           => 'center',
					'default_on_front'  => 'center',
					'tab_slug'          => 'general',
					'toggle_slug'       => 'accordion_settings',
					'description'       => esc_html__( 'Here you can select the content alignment.', 'divi-plus' ),
				),
				'active_accordion_size' => array(
					'label'           => esc_html__( 'Active Accordion Image Size', 'divi-plus' ),
					'type'            => 'range',
					'option_category' => 'basic_option',
					'range_settings'  => array(
						'min'  => '1',
						'max'  => '10',
						'step' => '1',
					),
					'unitless'         => true,
					'default'          => '4',
					'default_on_front' => '4',
					'mobile_options'   => true,
					'tab_slug'         => 'general',
					'toggle_slug'      => 'accordion_settings',
					'description'      => esc_html__( 'Move the slider or input the value to increase or decrease the size of active accordion.', 'divi-plus' ),
				),
				'item_spacing' => array(
					'label'           => esc_html__( 'Item Spacing', 'divi-plus' ),
					'type'            => 'range',
					'option_category' => 'basic_option',
					'range_settings'  => array(
						'min'  => '1',
						'max'  => '100',
						'step' => '1',
					),
					'unitless'         => false,
					'default'          => '15px',
					'default_on_front' => '15px',
					'mobile_options'   => false,
					'tab_slug'         => 'general',
					'toggle_slug'      => 'accordion_settings',
					'description'      => esc_html__( 'Move the slider or input the value to increase or decrease the spacing between the accordion items.', 'divi-plus' ),
				),
				'active_accordion' => array(
					'label'           	=> esc_html__( 'Default Active Accordion', 'divi-plus' ),
					'type'            	=> 'range',
					'option_category'	=> 'basic_option',
					'range_settings'  => array(
						'min'  => 0,
						'max'  => 10,
						'step' => 1,
					),
					'unitless'		  	=> true,
					'default'         	=> '0',
					'default_on_front'  => '0',
					'tab_slug'        	=> 'general',
					'toggle_slug'     	=> 'accordion_settings',
					'description'     	=> esc_html__( 'Here you can enter the accordion number which you want to set as active in default state. Set 0 for no accordion to be in active state.', 'divi-plus' ),
				),
				'accordion_transition_duration' => array(
					'label'             => esc_html__( 'Transition Duration', 'divi-plus' ),
					'type'              => 'range',
					'option_category'   => 'basic_option',
					'range_settings'  => array(
						'min'  => '100',
						'max'  => '2000',
						'step' => '100',
					),
					'fixed_unit'        => 'ms',
					'default'           => '500ms',
					'default_on_front'  => '500ms',
					'tab_slug'          => 'general',
					'toggle_slug'       => 'accordion_settings',
					'description'       => esc_html__( 'Here you can enter the transition duration.', 'divi-plus' ),
				),
				
				'sale_badge_bg_color' => array(
					'label'        	   => esc_html__( 'Sale Badge Background Color', 'divi-plus' ),
					'type'         	   => 'color-alpha',
					'custom_color' 	   => true,
					'show_if'          => array(
						'show_sale_badge' 	=> 'on',
					),
					'default'      	   => '',
					'default_on_front' => '',
					'tab_slug'         => 'advanced',
					'toggle_slug'      => 'sale_badge',
					'description'      => esc_html__( 'Here you can select the backround color for the sale badge.', 'divi-plus' ),
				),
				'accordion_bg_color' => array(
					'label'             => esc_html__( 'Accordion Overlay Background', 'divi-plus' ),
					'type'              => 'background-field',
					'base_name'         => 'accordion_bg',
					'context'           => 'accordion_bg_color',
					'option_category'   => 'button',
					'custom_color'      => true,
					'background_fields' => $acc_bg_option,
					'hover'             => false,
					'default'      		=> 'rgba(0, 0, 0, 0.5)',
					'default_on_front'  => 'rgba(0, 0, 0, 0.5)',
					'tab_slug'          => 'advanced',
					'toggle_slug'       => 'accordion',
					'sub_toggle'		=> 'normal',
					'description'       => esc_html__( 'Customize the background of the accordion by adjusting background color, gradient, and image.', 'divi-plus' ),
				),
				'active_accordion_bg_color' => array(
					'label'             => esc_html__( 'Active Accordion Overlay Background', 'divi-plus' ),
					'type'              => 'background-field',
					'base_name'         => 'active_accordion_bg',
					'context'           => 'active_accordion_bg_color',
					'option_category'   => 'button',
					'custom_color'      => true,
					'background_fields' => $active_acc_bg_option,
					'hover'             => false,
					'default'      		=> 'rgba(0, 0, 0, 0.5)',
					'default_on_front'  => 'rgba(0, 0, 0, 0.5)',
					'tab_slug'          => 'advanced',
					'toggle_slug'       => 'accordion',
					'sub_toggle'		=> 'active',
					'description'       => esc_html__( 'Customize the background of the active accordion by adjusting background color, gradient, and image. Leave empty for same.', 'divi-plus' ),
				),

				/*'product_spacing_custom_margin' => array(
					'label'           => esc_html__( 'Product Margin', 'divi-plus' ),
					'type'            => 'custom_margin',
					'option_category' => 'layout',
					'mobile_options'  => true,
					'tab_slug'        => 'advanced',
					'toggle_slug'     => 'product_settings',
					'description'     => esc_html__( 'Margin adds extra space to the outside of the element, increasing the distance between the element and other items on the page.', 'divi-plus' ),
				),*/
				'product_spacing_custom_padding' => array(
					'label'            => esc_html__( 'Product Content Padding', 'divi-plus' ),
					'type'             => 'custom_padding',
					'option_category'  => 'layout',
					'mobile_options'   => true,
					'default'          => '20px|20px|20px|20px|true|true',
					'default_on_front' => '20px|20px|20px|20px|true|true',
					'tab_slug'         => 'advanced',
					'toggle_slug'      => 'product_settings',
					'description'      => esc_html__( 'Padding adds extra space to the inside of the element, increasing the distance between the edge of the element and its inner contents.', 'divi-plus' ),
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
					'toggle_slug'       => 'text',
					'description'       => esc_html__( 'This controls the direction of the lazy-loading accordion content animation.', 'divi-plus' ),
					'computed_affects' => array(
						'__products_data',
					),
				),

				'__products_data' => array(
					'type'                => 'computed',
					'computed_callback'   => array( 'DIPL_WooProductsAccordion', 'get_computed_products_data' ),
					'computed_depends_on' => array_merge(
						array(
							'view_type',
							'use_current_loop',
							'products_number',
							'offset_number',
							'products_order_by',
							'products_order' ,
							'hide_out_of_stock',
							'include_categories',
							'include_tags',
							'taxonomies_relation',
							'title_level',
							'show_categories',
							'show_title',
							'show_description',
							'show_rating',
							'show_price',
							'show_add_to_cart',
							'show_sale_badge',
							'sale_badge_text',
							'sale_label_text',
							'show_outofstock_label',
							'out_of_stock_label',
							'inactive_state',
							'description_length',
							'animation'
						)
					)
				)
			),
			$this->generate_background_options( 'accordion_bg', 'skip', 'advanced', 'accordion', 'accordion_bg_color' ),
			$this->generate_background_options( 'active_accordion_bg', 'skip', 'advanced', 'accordion', 'active_accordion_bg_color' )
		);
	}

	/**
	 * This function return values to react for front end builder.
	 *
	 * @param array arguments to get products data
	 * @return array
	 * */
	public static function get_computed_products_data( $args = array(), $conditional_tags = array(), $current_page = array() ) {
		if ( self::$rendering ) {
			// We are trying to render a Blog module while a Blog module is already being rendered
			// which means we have most probably hit an infinite recursion. While not necessarily
			// the case, rendering a post which renders a Blog module which renders a post
			// which renders a Blog module is not a sensible use-case.
			return '';
		}

		$defaults = array(
			'view_type'             => 'default',
			'use_current_loop'      => 'off',
			'products_number'       => '4',
			'offset_number'         => 0,
			'products_order_by'     => 'title',
			'products_order'        => 'ASC',
			'hide_out_of_stock'     => 'off',
			'include_categories'    => '',
			'include_tags'          => '',
			'taxonomies_relation'   => 'OR',
			
			'title_level'           => 'h2',
			'show_categories'       => 'on',
			'show_title'            => 'on',
			'show_description'      => 'off',
			'show_rating'           => 'off',
			'show_price'            => 'on',
			'show_add_to_cart'      => 'on',
			'show_sale_badge'	    => 'on',
			'sale_badge_text'       => 'label',
			'sale_label_text'       => '',
			'show_outofstock_label' => 'off',
			'out_of_stock_label'    => '',
			'inactive_state'        => '',
			'description_length'    => '100',
			'animation'			    => 'off'
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
		$products_number   = ( 0 === $products_number ) ? -1 : (int) $products_number;

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

		$args = apply_filters( 'dipl_woo_products_accordion_args', $args );

		$query = new WP_Query( $args );

		self::$rendering = true;
		if ( ! $query->have_posts() ) {
			return sprintf(
				'<div class="entry">
					<h1>%1$s</h1><p>%2$s</p>
				</div>',
				esc_html__( 'No Result Found!', 'divi-plus' ),
				esc_html__( 'The products you requested could not be found. Try changing your module settings or add some new products.', 'divi-plus' )
			);
		}

		// Get inactive state
		$inactive_state_array = array( 'categories', 'title', 'description' );
		$inactive_state = DiviPlusHelper::process_multiple_checkboxes_value( $inactive_state, $inactive_state_array );
        $inactive_state = explode( ',', $inactive_state );

		// Default layout
		$product_layout   = 'layout1';

		$items = '';
		while ( $query->have_posts() ) {
			$query->the_post();

			global $product;

			// Get the product
			$product = wc_get_product( get_the_ID() );

			// Get image to set as background
			$image = get_the_post_thumbnail_url( get_the_ID(), 'single-post-thumbnail' );

			$accordion_background = '';
			if ( ! empty( $image ) ) {
				$accordion_background = sprintf( 'style="background-image: url(%1$s);"', $image );
			}

			if ( file_exists( get_stylesheet_directory() . '/divi-plus/layouts/woo-products-accordion/' . sanitize_file_name( $product_layout ) . '.php' ) ) {
				include get_stylesheet_directory() . '/divi-plus/layouts/woo-products-accordion/' . sanitize_file_name( $product_layout ) . '.php';
			} else {
				include plugin_dir_path( __FILE__ ) . 'layouts/' . sanitize_file_name( $product_layout ) . '.php';
			}
		}

		wp_reset_postdata();

		self::$rendering = false;
		return et_core_intentionally_unescaped( $items, 'html' );
	}

	public function render( $attrs, $content, $render_slug ) {
		if ( self::$rendering ) {
			// We are trying to render a DIPL Woo Product module while a DIPL Woo Product module is already being rendered
			// which means we have most probably hit an infinite recursion. While not necessarily
			// the case, rendering a post which renders a Blog module which renders a post
			// which renders a Blog module is not a sensible use-case.
			return '';
		}

		global $wp_the_query;
		$query_backup = $wp_the_query;

		// Get product queries
		$args = $this->get_product_query();

		$query = new WP_Query( $args );

		// If no data return
		if ( ! $query->have_posts() ) {
			return sprintf(
				'<div class="entry">
					<h1>%1$s</h1><p>%2$s</p>
				</div>',
				esc_html__( 'No Result Found!', 'divi-plus' ),
				esc_html__( 'The products you requested could not be found. Try changing your module settings or add some new products.', 'divi-plus' )
			);
		}

		self::$rendering = true;

		// Get the props
		$product_layout        = 'layout1';
		$title_level           = ! empty( $this->props['title_level'] ) ? $this->props['title_level'] : 'h2';
		$show_categories       = ! empty( $this->props['show_categories'] ) ? $this->props['show_categories'] : 'on';
		$show_title            = ! empty( $this->props['show_title'] ) ? $this->props['show_title'] : 'on';
		$show_description      = ! empty( $this->props['show_description'] ) ? $this->props['show_description'] : 'off';
		$show_rating           = ! empty( $this->props['show_rating'] ) ? $this->props['show_rating'] : 'off';
		$show_price            = ! empty( $this->props['show_price'] ) ? $this->props['show_price'] : 'on';
		$show_add_to_cart      = ! empty( $this->props['show_add_to_cart'] ) ? $this->props['show_add_to_cart'] : 'on';
		$show_sale_badge       = ! empty( $this->props['show_sale_badge'] ) ? $this->props['show_sale_badge'] : 'on';
		$sale_badge_text       = ! empty( $this->props['sale_badge_text'] ) ? $this->props['sale_badge_text'] : 'label';
		$sale_label_text       = ! empty( $this->props['sale_label_text'] ) ? $this->props['sale_label_text'] : '';
		$show_outofstock_label = ! empty( $this->props['show_outofstock_label'] ) ? $this->props['show_outofstock_label'] : 'off';
		$out_of_stock_label    = ! empty( $this->props['out_of_stock_label'] ) ? $this->props['out_of_stock_label'] : '';
		$description_length    = ! empty( $this->props['description_length'] ) ? $this->props['description_length'] : '0';
		$animation             = ! empty( $this->props['animation'] ) ? $this->props['animation'] : '';

		$inactive_state_array = array( 'categories', 'title', 'description' );
		$inactive_state = $this->props['inactive_state'];
		$inactive_state = DiviPlusHelper::process_multiple_checkboxes_value( $inactive_state, $inactive_state_array );
        $inactive_state = explode( ',', $inactive_state );
		
		$accordion_background = ''; // do not set background here, it uses for compute data

		$items = '';
		while ( $query->have_posts() ) {
			$query->the_post();

			if ( file_exists( get_stylesheet_directory() . '/divi-plus/layouts/woo-products-accordion/' . sanitize_file_name( $product_layout ) . '.php' ) ) {
				include get_stylesheet_directory() . '/divi-plus/layouts/woo-products-accordion/' . sanitize_file_name( $product_layout ) . '.php';
			} else {
				include plugin_dir_path( __FILE__ ) . 'layouts/' . sanitize_file_name( $product_layout ) . '.php';
			}

			// Get image to set as background
			$image = get_the_post_thumbnail_url( get_the_ID(), 'single-post-thumbnail' );
			self::set_style( $render_slug, array(
				'selector'    => sprintf( '%%order_class%% .dipl-products-accordion-item.post-%1$s', get_the_ID() ),
				'declaration' => sprintf( 'background-image: url( %1$s ) !important;', esc_url( $image ) ),
			) );
		}

		wp_reset_postdata();

		$file = et_is_builder_plugin_active() ? 'style-dbp' : 'style';
        	wp_enqueue_style( 'dipl-woo-accordion-carousel-style', PLUGIN_PATH . 'includes/modules/WooProductsAccordion/' . $file . '.min.css', array(), '1.0.0' );

		$fields = array( 'custom_product_margin' );
		DiviPlusHelper::process_advanced_margin_padding_css( $this, $render_slug, $this->margin_padding, $fields );

		if ( ! empty( $inactive_state ) ) {		
			self::set_style( $render_slug, array(
				'selector'    => '%%order_class%% .dipl-products-accordion-item .dipl-products-accordion-item-inner',
				'declaration' => 'visibility: visible; opacity: 1;'
			) );
			self::set_style( $render_slug, array(
				'selector'    => '%%order_class%% .dipl-products-accordion-item:not(.dipl-active-accordion-item) .dipl-woo-product__categories, 
								  %%order_class%% .dipl-products-accordion-item:not(.dipl-active-accordion-item) .dipl-woo-product__title,
								  %%order_class%% .dipl-products-accordion-item:not(.dipl-active-accordion-item) .dipl-woo-product__description,
								  %%order_class%% .dipl-products-accordion-item:not(.dipl-active-accordion-item) .dipl-woo-product__soldout,
								  %%order_class%% .dipl-products-accordion-item:not(.dipl-active-accordion-item) .dipl-woo-product__rating,
								  %%order_class%% .dipl-products-accordion-item:not(.dipl-active-accordion-item) .dipl-woo-product__price,
								  %%order_class%% .dipl-products-accordion-item:not(.dipl-active-accordion-item) .dipl-woo-product__add_to_cart',
				'declaration' => 'display: none;',
			) );
			
			if ( in_array( 'categories', $inactive_state, true ) ) { 
				self::set_style( $render_slug, array(
					'selector'    => '%%order_class%% .dipl-products-accordion-item .dipl-woo-product__categories',
					'declaration' => 'display: block !important;',
				) );
				if ( 'off' === $show_categories ) {
					self::set_style( $render_slug, array(
						'selector'    => '%%order_class%% .dipl-products-accordion-item.dipl-active-accordion-item .dipl-woo-product__categories',
						'declaration' => 'display: none !important;',
					) );
				}
			}
			if ( in_array( 'title', $inactive_state, true ) ) {
				self::set_style( $render_slug, array(
					'selector'    => '%%order_class%% .dipl-products-accordion-item .dipl-woo-product__title',
					'declaration' => 'display: block !important;',
				) );
				if ( 'off' === $show_title ) {
					self::set_style( $render_slug, array(
						'selector'    => '%%order_class%% .dipl-products-accordion-item.dipl-active-accordion-item .dipl-woo-product__title',
						'declaration' => 'display: none !important;',
					) );
				}
			}
			if ( in_array( 'description', $inactive_state, true ) ) {
				self::set_style( $render_slug, array(
					'selector'    => '%%order_class%% .dipl-products-accordion-item .dipl-woo-product__description',
					'declaration' => 'display: block !important;',
				) );
				if ( 'off' === $show_description ) {
					self::set_style( $render_slug, array(
						'selector'    => '%%order_class%% .dipl-products-accordion-item.dipl-active-accordion-item .dipl-woo-product__description',
						'declaration' => 'display: none !important;',
					) );
				}
			}
		}

		$sale_badge_bg_color = $this->props['sale_badge_bg_color'];
		if ( !empty( $sale_badge_bg_color ) && 'on' === $this->props['show_sale_badge'] ) {
			self::set_style( $render_slug, array(
				'selector'    => '%%order_class%% .dipl-products-accordion-item .dipl-onsale',
				'declaration' => sprintf( 'background-color: %1$s !important;', esc_attr( $sale_badge_bg_color ) ),
			) );
		}

		// Check orientation.
		$accordion_orientation = et_pb_responsive_options()->get_property_values( $this->props, 'accordion_orientation' );
		foreach ( $accordion_orientation as &$orientation ) {
			$orientation = str_replace( array( 'horizontal', 'vertical' ), array( 'row', 'column' ), $orientation);
		}
		if ( ! empty( array_filter( $accordion_orientation ) ) ) {
			$accordion_orientation_selector = "%%order_class%% .dipl-products-accordion-wrap";
			et_pb_responsive_options()->generate_responsive_css( $accordion_orientation, $accordion_orientation_selector, 'flex-direction', $render_slug, '', 'type' );
		}

		// Item space
		$item_spacing = isset( $this->props['item_spacing'] ) ? $this->props['item_spacing'] : '15px';
		if ( ! empty( array_filter( $accordion_orientation ) ) ) {
			$orientation_spacing = array( 'row' => array(), 'column' => array() );
			foreach ( $accordion_orientation as $device => $orientation ) {
				if ( 'row' === $orientation ) {
					$orientation_spacing['row'][$device]    = $item_spacing;
				} elseif ( 'column' === $orientation ) {
					$orientation_spacing['column'][$device] = $item_spacing;
				}
			}

			if ( ! empty( $orientation_spacing['row'] ) ) {
				// Item spacing
				et_pb_responsive_options()->generate_responsive_css( $orientation_spacing['row'], '%%order_class%% .dipl-products-accordion-wrap .dipl-products-accordion-item', 'margin-right', $render_slug, ' !important;', 'range' );

				// When row layout, remove bottom spaces
				$bottom_spacing = array_map( function($value) { return '0px'; }, $orientation_spacing['row'] );
				et_pb_responsive_options()->generate_responsive_css( $bottom_spacing, '%%order_class%% .dipl-products-accordion-wrap .dipl-products-accordion-item', 'margin-bottom', $render_slug, ' !important;', 'range' );

				self::set_style( $render_slug, array(
					'selector'    => '%%order_class%% .dipl-products-accordion-wrap .dipl-products-accordion-item:last-child',
					'declaration' => 'margin-right: 0 !important;',
				) );
			}
			if ( ! empty( $orientation_spacing['column'] ) ) {
				// Item spacing
				et_pb_responsive_options()->generate_responsive_css( $orientation_spacing['column'], '%%order_class%% .dipl-products-accordion-wrap .dipl-products-accordion-item', 'margin-bottom', $render_slug, ' !important;', 'range' );
				
				// When column layout, remove right spaces
				$right_spacing = array_map( function($value) { return '0px'; }, $orientation_spacing['column'] );
				et_pb_responsive_options()->generate_responsive_css( $right_spacing, '%%order_class%% .dipl-products-accordion-wrap .dipl-products-accordion-item', 'margin-right', $render_slug, ' !important;', 'range' );

				self::set_style( $render_slug, array(
					'selector'    => '%%order_class%% .dipl-products-accordion-wrap .dipl-products-accordion-item:last-child',
					'declaration' => 'margin-bottom: 0 !important;',
				) );
			}
		} elseif ( ! empty( $item_spacing ) ) {
			self::set_style( $render_slug, array(
				'selector'    => '%%order_class%% .dipl-products-accordion-wrap .dipl-products-accordion-item',
				'declaration' => sprintf( 'margin-right: %1$s !important;', esc_attr( $item_spacing ) ),
			) );
			self::set_style( $render_slug, array(
				'selector'    => '%%order_class%% .dipl-products-accordion-wrap .dipl-products-accordion-item:last-child',
				'declaration' => 'margin-right: 0 !important;',
			) );
		}

		$active_accordion_size = $this->props['active_accordion_size'];
		self::set_style( $render_slug, array(
			'selector'      => '%%order_class%% .dipl-products-accordion-wrap .dipl-active-accordion-item',
			'declaration'   => sprintf( 'flex: %1$s 0 auto !important;', floatval( $active_accordion_size ) ),
		) );

		$accordion_transition_duration = $this->props['accordion_transition_duration'];
		self::set_style( $render_slug, array(
            'selector'      => '%%order_class%% .dipl-products-accordion-item',
            'declaration'   => sprintf( 'transition-duration: %1$s !important;', esc_attr( $accordion_transition_duration ) ),
        ) );

		// Add to cart button icons
		if ( 'on' === $this->props['show_add_to_cart'] && 'on' === $this->props['add_to_cart_use_icon'] ) {
			$add_to_cart_icon = ! empty( $this->props['add_to_cart_icon'] ) ? et_pb_extended_process_font_icon( $this->props['add_to_cart_icon'] ) : '';
			if ( ! empty( $add_to_cart_icon ) ) {
				if ( 'left' === $this->props['add_to_cart_icon_placement'] ) {
					self::set_style( $render_slug, array(
						'selector'    => '%%order_class%% .dipl-products-accordion-item .dipl-woo-product__add_to_cart a:before',
						'declaration' => sprintf( 'content: "%1$s" !important;', esc_attr( $add_to_cart_icon ) ),
					) );
				} else {
					self::set_style( $render_slug, array(
						'selector'    => '%%order_class%% .dipl-products-accordion-item .dipl-woo-product__add_to_cart a:after',
						'declaration' => sprintf( 'content: "%1$s" !important;', esc_attr( $add_to_cart_icon ) ),
					) );
				}
			}
		}

		$args = array(
            'render_slug' => $render_slug,
            'props'       => $this->props,
            'fields'      => $this->fields_unprocessed,
            'module'      => $this,
            'backgrounds' => array(
                'accordion_bg' => array(
                    'normal' => "%%order_class%% .dipl-products-accordion-item:before",
                    'hover'  => "%%order_class%% .dipl-products-accordion-item:hover:before",
                ),
                'active_accordion_bg' => array(
                    'normal' => "%%order_class%% .dipl-products-accordion-item.dipl-active-accordion-item:after",
                    'hover'  => "%%order_class%% .dipl-products-accordion-item.dipl-active-accordion-item:hover:after",
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

		// Load the JS file
		wp_enqueue_script( 'dipl-woo-products-accordion', PLUGIN_PATH . 'includes/modules/WooProductsAccordion/dipl-woo-products-accordion-custom.min.js', array('jquery'), '1.1.0', true );

		$output = sprintf(
			'<div class="dipl-products-accordion-wrap woocommerce dipl-products-accordion-content-align-%4$s" data-trigger="%2$s" data-default-active="%3$s">
				%1$s
			</div>',
			et_core_intentionally_unescaped( $items, 'html' ),
			! empty( $this->props['accordion_trigger'] ) ? esc_attr( $this->props['accordion_trigger'] ) : 'hover',
			! empty( $this->props['active_accordion'] ) ? esc_attr( $this->props['active_accordion'] ) : '0',
			! empty( $this->props['content_alignment'] ) ? esc_attr( $this->props['content_alignment'] ) : 'center'
		);

		self::$rendering = false;
		return et_core_intentionally_unescaped( $output, 'html' );
	}

	public function get_product_query() {
		$view_type           = $this->props['view_type'];
		$use_current_loop    = $this->props['use_current_loop'];
		$products_number     = $this->props['products_number'];
		$offset_number       = $this->props['offset_number'];
		$include_categories  = $this->props['include_categories'];
		$include_tags        = $this->props['include_tags'];
		$taxonomies_relation = $this->props['taxonomies_relation'];
		$hide_out_of_stock   = $this->props['hide_out_of_stock'];
		$products_order_by   = $this->props['products_order_by'];
		$products_order      = $this->props['products_order'];

		$products_number = ( 0 === $products_number ) ? -1 : (int) $products_number;

		$args = array(
			'post_type'      => 'product',
			'posts_per_page' => intval( $products_number ),
			'offset'		 => intval( $offset_number ),
			'post_status'    => 'publish',
			'orderby'        => $products_order_by,
			'order'          => $products_order,
		);

		if ( '' !== $products_order_by ) {
			if ( 'price' === $products_order_by ) {
				$args['orderby'] 	= 'meta_value_num';
				$args['meta_key'] 	= '_price';
			} else if ( 'stock_status' === $products_order_by ) {
				$args['orderby'] 	= 'meta_value';
   				$args['meta_key'] 	= '_stock_status';
			} else {
				$args['orderby'] = sanitize_text_field( $products_order_by );
			}
		}

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
		
		//phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['product_category'] ) && ! is_search() && ! is_archive() && ! $use_current_loop && 'on' === $enable_category_filter && 0 !== absint( $_GET['product_category'] ) && '-1' !== $_GET['product_category'] ) {
			//phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$include_categories = absint( wp_unslash( $_GET['product_category'] ) );
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

		$args = $this->filter_products_query( $args );
		add_action( 'pre_get_posts', array( $this, 'apply_woo_widget_filters' ), 10 );

		$args = apply_filters( 'dipl_woo_products_accordion_args', $args );
		
		return $args;
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
		// phpcs:ignore WordPress,Variables,GlobalVariables,OverrideProhibited.
		$wp_the_query = $query;

		if ( function_exists( 'WC' ) ) {
			add_filter( 'posts_clauses', array( WC()->query, 'price_filter_post_clauses' ), 10, 2 );
		}
	}

	/**
	 * Create sale badge for the product
	 */
	public static function get_product_sale_badge( $post, $product, $sale_badge_text = 'label', $sale_label_text = '' ) {

		// Default sale bage text
		$sale_text = esc_html__( 'Sale!', 'divi-plus' );

		if ( 'percentage' === $sale_badge_text ) {
			$percentages = array();
			$percentage  = '';
			if ( $product->is_type( 'variable' ) ) {
				$prices = $product->get_variation_prices();
				foreach ( $prices['price'] as $key => $price ) {
					if ( $prices['regular_price'][ $key ] !== $price ) {
						if ( $prices['sale_price'][ $key ] >= 0 ) {
							$percentages[] = round( 100 - ( $prices['sale_price'][ $key ] / $prices['regular_price'][ $key ] * 100 ) );
						} else {
							$percentages[] = 100;
						}
					}
				}
			} elseif ( $product->is_type( 'grouped' ) ) {
				$percentages = array();
				$children    = array_filter( array_map( 'wc_get_product', $product->get_children() ), 'wc_products_array_filter_visible_grouped' );
				foreach ( $children as $child ) {
					if ( $child->is_purchasable() && ! $child->is_type( 'grouped' ) && $child->is_on_sale() ) {
						$regular_price = (float) $child->get_regular_price();
						$sale_price    = (float) $child->get_sale_price();

						if ( $sale_price >= 0 ) {
							$percentages[] = round( 100 - ( $sale_price / $regular_price * 100 ) );
						} else {
							$percentages[] = 100;
						}
					}
				}
			} else {
				$regular_price = (float) $product->get_regular_price();
				$sale_price    = (float) $product->get_sale_price();
				$percentage = '100%';
				if ( $sale_price >= 0 ) {
					$percentage = round( 100 - ( $sale_price / $regular_price * 100 ) ) . '%';
				}
			}

			if ( empty( $percentage ) && ! empty( $percentages ) ) {
				$max_percentage = max( $percentages );
				$min_percentage = min( $percentages );
				$percentage     = sprintf( '%s%%', $max_percentage );
			}

			$sale_text = $percentage;

		} elseif ( ! empty( $sale_label_text ) ) {
			$sale_text = esc_html( $sale_label_text );
		}

		$html = sprintf( '<span class="dipl-onsale">%1$s</span>', esc_html( $sale_text ) );
		return apply_filters( 'dipl_product_accordion_sale_badge', $html, $post, $product, $sale_badge_text, $sale_label_text );
	}

	/**
	 * Display custom out of stock badge.
	 *
	 * @since 1.0.0
	 */
	public static function get_product_outofstock_badge( $product, $out_of_stock_label = '' ) {
		$html = '';
		if ( ! $product->is_in_stock() ) {
			$availability = $product->get_availability();
			$class        = esc_attr( $availability['class'] );
			if ( ! empty( $out_of_stock_label ) ) {
				$out_text = esc_html( $out_of_stock_label );
			} else {
				$all_status_text = wc_get_product_stock_status_options();
				$out_text        = $all_status_text['outofstock'];
			}

			$html = sprintf( '<div class="dipl-woo-product__soldout"><span class="soldout-text stock %s">%s</span></div>', $class, $out_text );
			$html = apply_filters( 'woocommerce_get_stock_html', $html, $product );
		}

		return apply_filters( 'dipl_product_accordion_outofstock_badge', $html, $product );
	}

	/**
	 * Limit the excerpt/content
	 */
	public static function dipl_trim_content( $content, $amount = 100, $end_width = '...' ) {
        // Decide if we need to append dots at the end of the string.
        if ( strlen( $content ) <= $amount ) {
            $echo_out = '';
        } else {
            $echo_out = $end_width;
            if ( $amount > 3 ) {
                $amount = $amount - 3;
            }
        }

        // trim text to a certain number of characters, also remove spaces from the end of a string ( space counts as a character ).
        $truncate = rtrim( et_wp_trim_words( $content, $amount, '' ) );

        // remove the last word to make sure we display all words correctly.
        if ( '' !== $echo_out ) {
            $new_words_array = (array) explode( ' ', $truncate );
            array_pop( $new_words_array );

            $truncate = implode( ' ', $new_words_array );

            // append dots to the end of the string.
            if ( '' !== $truncate ) {
                $truncate .= $echo_out;
            }
        }

        return et_core_intentionally_unescaped( $truncate, 'html' );
    }
}

$plugin_options = get_option( ELICUS_DIVI_PLUS_OPTION );
if ( isset( $plugin_options['dipl-modules'] ) ) {
	$modules = explode( ',', $plugin_options['dipl-modules'] );
	if (
		in_array( 'dipl_woo_products_accordion', $modules, true ) &&
		et_is_woocommerce_plugin_active()
	) {
		new DIPL_WooProductsAccordion();
	}
} else {
	if ( et_is_woocommerce_plugin_active() ) {
		new DIPL_WooProductsAccordion();
	}
}