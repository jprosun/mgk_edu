<?php
class DIPL_WooProductGallery extends ET_Builder_Module {

	public $slug       = 'dipl_woo_product_gallery';
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
		$this->name = esc_html__( 'DP Woo Product Gallery', 'divi-plus' );
		$this->main_css_element = '%%order_class%%';
	}

	public function get_settings_modal_toggles() {
		return array(
			'general'  => array(
				'toggles' => array(
					'product' => esc_html__( 'Configuration', 'divi-plus' ),
					'display' => esc_html__( 'Display', 'divi-plus' )
				),
			),
			'advanced' => array(
				'toggles' => array(
					'title'			=> array(
						'title'	=> esc_html__( 'Title', 'divi-plus' ),
					),
					'caption'		=> array(
						'title'	=> esc_html__( 'Caption', 'divi-plus' ),
					),
					'gallery_item'	=> array(
						'title'	=> esc_html__( 'Gallery Item', 'divi-plus' ),
					),
					'lightbox' => array(
						'title' => esc_html__( 'Lightbox', 'divi-plus' ),
					),
					'overlay' => array(
						'title' => esc_html__( 'Overlay', 'divi-plus' ),
					),
				)
			)
		);
	}

	public function get_advanced_fields_config() {
		return array(
			'fonts'			=> array(
				'title'	=> array(
					'label'			=> esc_html__( 'Title', 'divi-plus' ),
					'font_size'			=> array(
						'default'			=> '18px',
						'default_on_front'	=> '18px',
						'show_if'			=> array( 'show_title' => 'on' )
					),
					'line_height'	=> array(
						'default'		=> '1.3em',
						'show_if'		=> array( 'show_title' => 'on' )
					),
					'letter_spacing' => array(
						'default'		=> '0px',
						'show_if'		=> array( 'show_title' => 'on' )
					),
					'header_level'	=> array(
						'default'		=> 'h2',
						'label'			=> esc_html__( 'Title Level', 'divi-plus' ),
						'show_if'		=> array( 'show_title' => 'on' )
					),
					'font'			=> array(
						'show_if'		=> array( 'show_title' => 'on' )
					),
					'text_align'	=> array(
						'show_if'		=> array( 'show_title' => 'on' )
					),
					'text_color'	=> array(
						'show_if'		=> array( 'show_title' => 'on' )
					),
					'text_shadow'	=> array(
						'show_if'		=> array( 'show_title' => 'on' )
					),
					'css'	=> array(
						'main' => "{$this->main_css_element} .dipl_woo_product_gallery-title .et_pb_title, {$this->main_css_element}_lightbox .dipl_woo_product_gallery-title .et_pb_title",
					),
					'tab_slug'		=> 'advanced',
					'toggle_slug'	=> 'title',
				),
				'caption'		=> array(
					'label'		=> esc_html__( 'Caption', 'divi-plus' ),
					'font_size'			=> array(
						'default'			=> '14px',
						'default_on_front'	=> '14px',
						'priority'			=> 1,
						'show_if'			=> array( 'show_caption' => 'on' )
					),
					'line_height'		=> array(
						'default'			=> '1.3em',
						'priority'			=> 2,
						'show_if'			=> array( 'show_caption' => 'on' )
					),
					'letter_spacing'	=> array(
						'default'			=> '0px',
						'priority'			=> 3,
						'show_if'			=> array( 'show_caption' => 'on' )
					),
					'font'				=> array(
						'priority'			=> 4,
						'show_if'			=> array( 'show_caption' => 'on' )
					),
					'text_align'		=> array(
						'priority'			=> 5,
						'show_if'			=> array( 'show_caption' => 'on' )
					),
					'text_color'		=> array(
						'priority'			=> 1,
						'show_if'			=> array( 'show_caption' => 'on' )
					),
					'text_shadow'		=> array(
						'priority'			=> 110,
						'show_if'			=> array( 'show_caption' => 'on' )
					),
					'css'	=> array(
						'main'			=> "{$this->main_css_element} .dipl_woo_product_gallery-caption, {$this->main_css_element}_lightbox .dipl_woo_product_gallery-caption",
					),
					'tab_slug'		=> 'advanced',
					'toggle_slug'	=> 'caption',
				)
			),
			'borders'	=> array(
				'item_border' => array(
					'label'	=> esc_html__( 'Gallery Item', 'divi-plus' ),
					'css' => array(
						'main' => array(
							'border_radii'  => '%%order_class%% .dipl_woo_product_gallery-item',
							'border_styles' => '%%order_class%% .dipl_woo_product_gallery-item',
							'important' => 'all',
						),
					),
					'tab_slug'		=> 'advanced',
					'toggle_slug'	=> 'gallery_item',
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
				'item_box' => array(
					'label'			=> esc_html__( 'Gallery Item', 'divi-plus' ),
					'css'			=> array(
						'main' => '%%order_class%% .dipl_woo_product_gallery-item',
						'important' => 'all',
					),
					'tab_slug'		=> 'advanced',
					'toggle_slug'	=> 'gallery_item',
				),
				'default' => array(
					'css' => array(
						'main' => '%%order_class%%',
					),
				),
			),
			'custom_margin_padding' => array(
				'css' => array(
					'main'      => '%%order_class%%',
					'important' => 'all',
				),
			),
			'title_custom_spacing' => array(
				'title_margin_padding' => array(
					'priority'			=> 100,
					'margin_padding' => array(
						'css' => array(
							'margin'    => "%%order_class%% .dipl_woo_product_gallery-title .et_pb_title",
							'padding'   => "%%order_class%% .dipl_woo_product_gallery-title .et_pb_title",
							'important' => 'all',
						),
					),
				),
			),
			'caption_custom_spacing' => array(
				'caption_margin_padding' => array(
					'priority'			=> 100,
					'margin_padding' => array(
						'css' => array(
							'margin'    => "%%order_class%% .dipl_woo_product_gallery-caption",
							'padding'   => "%%order_class%% .dipl_woo_product_gallery-caption",
							'important' => 'all',
						),
					),
				),
			),
			'text'			=> false,
			'text_shadow'	=> false,
			'link_options'	=> false,
		);
	}

	public function get_fields() {
		return array(
			'use_current_product' => array(
				'label'					=> esc_html__( 'Use Current Product?', 'divi-plus' ),
				'type'					=> 'yes_no_button',
				'option_category'		=> 'basic_option',
				'options' => array(
					'off'	=> esc_html__( 'No', 'divi-plus' ),
					'on'	=> esc_html__( 'Yes', 'divi-plus' ),
				),
				'default'				=> 'on',
				'default_on_front'		=> 'on',
				'description'			=> esc_html__( 'Here you can choose to show current product gallery, (this will work on only single product page) or else select the product.', 'divi-plus' ),
				'tab_slug'				=> 'general',
				'toggle_slug'			=> 'product',
				'computed_affects'		=> array(
					'__product_gallery', '__product_gallery_meta'
				),
			),
			'product'			=> array(
				'label'					=> esc_html__( 'Product', 'divi-plus' ),
				'type'					=> 'select_product',
				'option_category'		=> 'basic_option',
				'description'			=> esc_html__( 'Here you can select the Product.', 'divi-plus' ),
				'searchable'			=> true,
				'displayRecent'			=> false,
				// 'include_latest_post'	=> false,
				'default'				=> 'current',
				'post_type'				=> 'product',
				'tab_slug'				=> 'general',
				'toggle_slug'			=> 'product',
				'show_if'         => array(
					'use_current_product' => 'off',
				),
				'computed_affects'		=> array(
					'__product_gallery', '__product_gallery_meta'
				),
			),
			'no_of_columns'		=> array(
				'label'				=> esc_html__( 'Number of Columns', 'divi-plus' ),
				'type'				=> 'select',
				'option_category'	=> 'layout',
				'options'	=> array(
					'1'		=> esc_html__( '1', 'divi-plus' ),
					'2'		=> esc_html__( '2', 'divi-plus' ),
					'3'		=> esc_html__( '3', 'divi-plus' ),
					'4'		=> esc_html__( '4', 'divi-plus' ),
					'5'		=> esc_html__( '5', 'divi-plus' ),
					'6'		=> esc_html__( '6', 'divi-plus' ),
					'7'		=> esc_html__( '7', 'divi-plus' ),
					'8'		=> esc_html__( '8', 'divi-plus' ),
					'9'		=> esc_html__( '9', 'divi-plus' ),
					'10'	=> esc_html__( '10', 'divi-plus' ),
				),
				'default'			=> '4',
				'default_on_front'	=> '4',
				'mobile_options'	=> true,
				'tab_slug'			=> 'general',
				'toggle_slug'		=> 'product',
				'description'		=> esc_html__( 'select the number of columns to display for feed data.', 'divi-plus' ),
			),
			'column_spacing'	=> array(
				'label'				=> esc_html__( 'Column Spacing', 'divi-plus' ),
				'type'				=> 'range',
				'option_category'	=> 'font_option',
				'range_settings'	=> array(
					'min'	=> '1',
					'max'	=> '100',
					'step'	=> '1',
				),
				'fixed_unit'		=> 'px',
				'fixed_range'		=> true,
				'validate_unit'		=> true,
				'default'			=> '4px',
				'default_on_front'	=> '4px',
				'mobile_options'	=> true,
				'tab_slug'			=> 'general',
				'toggle_slug'		=> 'product',
				'description'		=> esc_html__( 'Control the text indent of the list items text by increasing or decreasing it.', 'divi-plus' ),
			),
			'enable_masonry'	=> array(
				'label'				=> esc_html__( 'Enable Masonry?', 'divi-plus' ),
				'type'				=> 'yes_no_button',
				'option_category'	=> 'basic_option',
				'options'			=> array(
					'off'	=> esc_html__( 'No', 'divi-plus' ),
					'on'	=> esc_html__( 'Yes', 'divi-plus' ),
				),
				'default'			=> 'off',
				'description'		=> esc_html__( 'Here you can choose to link the image to instagram or not.', 'divi-plus' ),
				'default_on_front'	=> 'off',
				'tab_slug'			=> 'general',
				'toggle_slug'		=> 'product',
				'description'		=> esc_html__( 'Select to change the layout as masonry or simple grid.', 'divi-plus' ),
			),
			'show_title'		=> array(
				'label'					=> esc_html__( 'Display Title?', 'divi-plus' ),
				'type'					=> 'yes_no_button',
				'option_category'		=> 'basic_option',
				'options' => array(
					'off'	=> esc_html__( 'No', 'divi-plus' ),
					'on'	=> esc_html__( 'Yes', 'divi-plus' ),
				),
				'default'				=> 'off',
				'default_on_front'		=> 'off',
				'description'			=> esc_html__( 'Here you can choose to show product gallery image title or not.', 'divi-plus' ),
				'tab_slug'				=> 'general',
				'toggle_slug'			=> 'display',
			),
			'title_position'	=> array(
				'label'            => esc_html__( 'Display Title In', 'divi-plus' ),
				'type'             => 'select',
				'option_category'  => 'configuration',
				'options'          => array(
					'gallery_only'  => esc_html__( 'Gallery Only', 'divi-plus' ),
					'lightbox_only' => esc_html__( 'Lightbox Only', 'divi-plus' ),
					'both'          => esc_html__( 'Lightbox & Gallery Both', 'divi-plus' ),
				),
				'show_if'			=> array(
					'show_title'		=> 'on',
					'enable_lightbox'	=> 'on',
				),
				'default' 			=> 'gallery_only',
				'default_on_front' 	=> 'gallery_only',
				'tab_slug'          => 'general',
				'toggle_slug'      	=> 'display',
				'description'      	=> esc_html__( 'Select the position of the title to show on the gallery, lightbox, or both.', 'divi-plus' ),
			),
			'show_caption'		=> array(
				'label'					=> esc_html__( 'Display Caption?', 'divi-plus' ),
				'type'					=> 'yes_no_button',
				'option_category'		=> 'basic_option',
				'options' => array(
					'off'	=> esc_html__( 'No', 'divi-plus' ),
					'on'	=> esc_html__( 'Yes', 'divi-plus' ),
				),
				'default'				=> 'off',
				'default_on_front'		=> 'off',
				'description'			=> esc_html__( 'Here you can choose to show gallery product image caption or not.', 'divi-plus' ),
				'tab_slug'				=> 'general',
				'toggle_slug'			=> 'display',
			),
			'caption_position'	=> array(
				'label'            => esc_html__( 'Display Caption In', 'divi-plus' ),
				'type'             => 'select',
				'option_category'  => 'configuration',
				'options'          => array(
					'gallery_only'  => esc_html__( 'Gallery Only', 'divi-plus' ),
					'lightbox_only' => esc_html__( 'Lightbox Only', 'divi-plus' ),
					'both' => esc_html__( 'Lightbox & Gallery Both', 'divi-plus' ),
				),
				'show_if'			=> array(
					'show_caption'		=> 'on',
					'enable_lightbox'	=> 'on',
				),
				'default' 			=> 'gallery_only',
				'default_on_front' 	=> 'gallery_only',
				'tab_slug'          => 'general',
				'toggle_slug'      	=> 'display',
				'description'      	=> esc_html__( 'Select the position of the caption to show on the gallery, lightbox, or both..', 'divi-plus' ),
			),
			'enable_lightbox'	=> array(
				'label'            => esc_html__( 'Enable Lightbox?', 'divi-plus' ),
				'type'             => 'yes_no_button',
				'option_category'  => 'configuration',
				'options'          => array(
					'on'  => esc_html__( 'Yes', 'divi-plus' ),
					'off' => esc_html__( 'No', 'divi-plus' ),
				),
				'default' 			=> 'off',
				'default_on_front' 	=> 'off',
				'tab_slug'          => 'general',
				'toggle_slug'      	=> 'display',
				'description'      	=> esc_html__( 'Whether or not to show images in lightbox.', 'divi-plus' ),
			),
			'enable_overlay'	=> array(
				'label'				=> esc_html__( 'Enable Image Overlay on Hover', 'divi-plus' ),
				'type'				=> 'yes_no_button',
				'option_category'	=> 'configuration',
				'options'		=> array(
					'on'	=> esc_html__( 'Yes', 'divi-plus' ),
					'off'	=> esc_html__( 'No', 'divi-plus' ),
				),
				'default'			=> 'off',
				'default_on_front'	=> 'off',
				'tab_slug'			=> 'general',
				'toggle_slug'		=> 'display',
				'description'		=> esc_html__( 'Whether or not to show image overlay on hover.', 'divi-plus' ),
			),
			'overlay_icon' => array(
				'label'				=> esc_html__( 'Overlay Icon', 'divi-plus' ),
				'type'				=> 'select_icon',
				'option_category'	=> 'configuration',
				'class'				=> array( 'et-pb-font-icon' ),
				'show_if'			=> array(
					'enable_overlay'	=> 'on',
				),
				'tab_slug'			=> 'general',
				'toggle_slug'		=> 'display',
				'description'		=> esc_html__( 'Here you can define a custom icon for the overlay', 'divi-plus' ),
			),
			'title_margin_padding_custom_margin' => array(
				'label'             => esc_html__( 'Title Margin', 'divi-plus' ),
				'type'              => 'custom_padding',
				'option_category'   => 'layout',
				'mobile_options'    => true,
				'hover'             => true,
				'default'           => '',
				'default_on_front'  => '',
				'tab_slug'     		=> 'advanced',
				'toggle_slug'  		=> 'title',
				'priority'          => 100,
				'show_if'			=> array( 'show_caption' => 'on' ),
				'description'       => esc_html__( 'Margin adds extra space to the outside of the element, increasing the distance between the toggle and toggle content.', 'divi-plus' ),
			),
			'title_margin_padding_custom_padding' => array(
				'label'             => esc_html__( 'Title Padding', 'divi-plus' ),
				'type'              => 'custom_padding',
				'option_category'   => 'layout',
				'mobile_options'    => true,
				'hover'             => true,
				'default'           => '10px|10px|10px|10px',
				'default_on_front'  => '10px|10px|10px|10px',
				'tab_slug'     		=> 'advanced',
				'toggle_slug'  		=> 'title',
				'priority'          => 100,
				'show_if'			=> array( 'show_caption' => 'on' ),
				'description'       => esc_html__( 'Padding adds extra space to the inside of the element, increasing the distance between the toggle and toggle content.', 'divi-plus' ),
			),
			'caption_margin_padding_custom_margin' => array(
				'label'             => esc_html__( 'Caption Margin', 'divi-plus' ),
				'type'              => 'custom_padding',
				'option_category'   => 'layout',
				'mobile_options'    => true,
				'hover'             => true,
				'default'           => '',
				'default_on_front'  => '',
				'tab_slug'     		=> 'advanced',
				'toggle_slug'  		=> 'caption',
				'priority'          => 100,
				'show_if'			=> array( 'show_caption' => 'on' ),
				'description'       => esc_html__( 'Margin adds extra space to the outside of the element, increasing the distance between the toggle and toggle content.', 'divi-plus' ),
			),
			'caption_margin_padding_custom_padding' => array(
				'label'             => esc_html__( 'Caption Padding', 'divi-plus' ),
				'type'              => 'custom_padding',
				'option_category'   => 'layout',
				'mobile_options'    => true,
				'hover'             => true,
				'default'           => '0|10px|10px|10px',
				'default_on_front'  => '0|10px|10px|10px',
				'tab_slug'     		=> 'advanced',
				'toggle_slug'  		=> 'caption',
				'priority'          => 100,
				'show_if'			=> array( 'show_caption' => 'on' ),
				'description'       => esc_html__( 'Padding adds extra space to the inside of the element, increasing the distance between the toggle and toggle content.', 'divi-plus' ),
			),
			'item_bg' => array(
				'label'				=> esc_html__( 'Background Color', 'divi-plus' ),
				'type'				=> 'color-alpha',
				'hover'				=> 'tabs',
				'default'			=> '',
				'tab_slug'			=> 'advanced',
				'toggle_slug'		=> 'gallery_item',
				'description'		=> esc_html__( 'Here you can define a custom color for your gallery item background.', 'divi-plus' ),
			),
			'lightbox_background_color' => array(
				'label'				=> esc_html__( 'Lightbox Background Color', 'divi-plus' ),
				'type'				=> 'color-alpha',
				'custom_color'		=> true,
				'default'			=> 'rgba(0,0,0,0.8)',
				'default_on_front'	=> 'rgba(0,0,0,0.8)',
				'show_if'		=> array(
					'enable_lightbox'	=> 'on',
				),
				'tab_slug'			=> 'advanced',
				'toggle_slug'		=> 'lightbox',
				'description'		=> esc_html__( 'Here you can define a custom background color for the lightbox.', 'divi-plus' ),
			),
			'lightbox_close_icon_color'	=> array(
				'label'				=> esc_html__( 'Close Icon Color', 'divi-plus' ),
				'type'				=> 'color-alpha',
				'custom_color'		=> true,
				'default'			=> '#fff',
				'default_on_front'	=> '#fff',
				'show_if'		=> array(
					'enable_lightbox'	=> 'on',
				),
				'tab_slug'			=> 'advanced',
				'toggle_slug'		=> 'lightbox',
				'description'		=> esc_html__( 'Here you can define a custom color for the close icon.', 'divi-plus' ),
			),
			'lightbox_arrows_color'	=> array(
				'label'				=> esc_html__( 'Arrows Color', 'divi-plus' ),
				'type'				=> 'color-alpha',
				'custom_color'		=> true,
				'default'			=> '#fff',
				'default_on_front'	=> '#fff',
				'show_if'		=> array(
					'enable_lightbox'	=> 'on',
				),
				'tab_slug'			=> 'advanced',
				'toggle_slug'		=> 'lightbox',
				'description'		=> esc_html__( 'Here you can define a custom color for the arrows.', 'divi-plus' ),
			),
			'meta_background_color'	=> array(
				'label'				=> esc_html__( 'Title & Caption Background Color', 'divi-plus' ),
				'type'				=> 'color-alpha',
				'custom_color'		=> true,
				'default'			=> 'rgba(0,0,0,0.6)',
				'default_on_front'	=> 'rgba(0,0,0,0.6)',
				'show_if'		=> array(
					'enable_lightbox'	=> 'on',
				),
				'tab_slug'			=> 'advanced',
				'toggle_slug'		=> 'lightbox',
				'description'		=> esc_html__( 'Here you can define a custom overlay color for the title and caption.', 'divi-plus' ),
			),
			'overlay_color' => array(
				'label'				=> esc_html__( 'Overlay Background Color', 'divi-plus' ),
				'type'				=> 'color-alpha',
				'custom_color'		=> true,
				'show_if'		=> array(
					'enable_overlay'	=> 'on',
				),
				'tab_slug'			=> 'advanced',
				'toggle_slug'		=> 'overlay',
				'description'		=> esc_html__( 'Here you can define a custom color for the overlay', 'divi-plus' ),
			),
			'overlay_icon_size' => array(
				'label'				=> esc_html__( 'Icon Size', 'divi-plus' ),
				'type'				=> 'range',
				'option_category'	=> 'layout',
				'range_settings'	=> array(
					'min'	=> '0',
					'max'	=> '100',
					'step'	=> '1',
				),
				'fixed_unit'		=> 'px',
				'fixed_range'		=> true,
				'validate_unit'		=> true,
				'mobile_options'	=> true,
				'default'			=> '32px',
				'default_on_front'	=> '32px',
				'show_if'		=> array(
					'enable_overlay'	=> 'on',
				),
				'tab_slug'			=> 'advanced',
				'toggle_slug'		=> 'overlay',
				'description'		=> esc_html__( 'Increase or decrease icon font size.', 'divi-plus' ),
			),
			'overlay_icon_color'	=> array(
				'label'				=> esc_html__( 'Overlay Icon Color', 'divi-plus' ),
				'type'				=> 'color-alpha',
				'custom_color'		=> true,
				'show_if'		=> array(
					'enable_overlay'	=> 'on',
				),
				'tab_slug'			=> 'advanced',
				'toggle_slug'		=> 'overlay',
				'description'		=> esc_html__( 'Here you can define a custom color for the icon.', 'divi-plus' ),
			),
			'__product_gallery' => array(
				'type'					=> 'computed',
				'computed_callback'		=> array( 'DIPL_WooProductGallery', 'get_compute_woo_product_gallery' ),
				'computed_depends_on'	=> array(
					'use_current_product',
					'product',
					'enable_masonry',
				)
			),
			'__product_gallery_meta' => array(
				'type'					=> 'computed',
				'computed_callback'		=> array( 'DIPL_WooProductGallery', 'get_compute_woo_product_gallery_meta' ),
				'computed_depends_on'	=> array(
					'use_current_product',
					'product',
				)
			)
		);
	}

	/**
	 * Return the products attachments
	 */
	private static function dipl_get_product_attachments( $args ) {

		// Get product first based on args
		if ( 'on' == $args['use_current_product'] || 'current' == $args['product'] ) {
			global $product;
		} else {
			// Generate valid `gallery_ids` value based `product` attribute.
			$product = ET_Builder_Module_Helper_Woocommerce_Modules::get_product( $args['product'] );
		}

		$attachment_ids = array();
		if ( $product ) {
			$featured_image_id	= intval( $product->get_image_id() );
			if ( ! empty( $featured_image_id ) ) {
				$attachment_ids[] = $featured_image_id;
			}

			// Add gallery images to attachment ids
			$attachment_ids = array_merge( $attachment_ids, $product->get_gallery_image_ids() );
		}

		// Get placeholder image
		// Check if attachment ids are empty
		if ( empty($attachment_ids) && function_exists( 'wc_placeholder_img_src' ) ) {
			// Get placeholder image
			$placeholder_src = wc_placeholder_img_src( 'full' );
			$placeholder_id  = attachment_url_to_postid( $placeholder_src );

			if ( 0 != absint( $placeholder_id ) ) {
				$attachment_ids = array( $placeholder_id );
			}
		}

		return $attachment_ids;
	}

	/**
	 * This function return values to react for front end builder.
	 *
	 * @param array arguments to get products data
	 * @return array
	 * */
	public static function get_compute_woo_product_gallery( $args = array(), $conditional_tags = array(), $current_page = array() ) {
		$defaults = array(
			'use_current_product'	=> 'on',
			'product'				=> 'current',
		);

		$args = wp_parse_args( $args, $defaults );

		// Get attachment ids
		$attachment_ids = self::dipl_get_product_attachments( $args );

		$attachments = array();
		if ( !empty($attachment_ids) ) {
			foreach ( $attachment_ids as $attachment_id ) {
				array_push( $attachments, wp_get_attachment_image( $attachment_id, 'full' ) );
			}
		} else {
			// Even not have a default/placeholer image
		}
		
		return $attachments;
	}

	/**
	 * This function return values to react for front end builder.
	 *
	 * @param array arguments to get products data
	 * @return array
	 * */
	public static function get_compute_woo_product_gallery_meta( $args = array(), $conditional_tags = array(), $current_page = array() ) {
		$defaults = array(
			'use_current_product'	=> 'on',
			'product'				=> 'current',
			'title_level'			=> 'h2'
		);

		$args = wp_parse_args( $args, $defaults );

		$metaDatas = array();

		// Get attachment ids
		$attachment_ids = self::dipl_get_product_attachments( $args );
		if ( !empty($attachment_ids) ) {

			foreach ( $attachment_ids as $attachment_id ) {
				array_push( $metaDatas, array(
					'title' => trim( wptexturize( get_the_title( $attachment_id ) ) ),
					'caption' => esc_html( trim( wp_get_attachment_caption( $attachment_id ) ) )
				) );
			}
		}

		return $metaDatas;
	}

	public function render( $attrs, $content, $render_slug ) {
		if ( self::$rendering ) {
			// We are trying to render a DIPL Woo Product module while a DIPL Woo Product module is already being rendered
			// which means we have most probably hit an infinite recursion. While not necessarily
			// the case, rendering a post which renders a Blog module which renders a post
			// which renders a Blog module is not a sensible use-case.
			return '';
		}

		$enable_masonry	= !empty( $this->props['enable_masonry'] ) ? $this->props['enable_masonry'] : 'off';
		$layout			= ( 'on' == $enable_masonry ) ? 'masonry' : 'grid';
		$no_of_columns	= !empty( $this->props['no_of_columns'] ) ? $this->props['no_of_columns'] : 4;

		// Get attachment ids
		$attachment_ids = self::dipl_get_product_attachments( $this->props );

		self::$rendering = true;

		$output = '';
		if ( !empty($attachment_ids) ) {

			// Get the args
			$enable_lightbox = !empty( $this->props['enable_lightbox'] ) ? $this->props['enable_lightbox'] : 'off';
			$show_title      = !empty( $this->props['show_title'] ) ? $this->props['show_title'] : 'off';
			$show_caption    = !empty( $this->props['show_caption'] ) ? $this->props['show_caption'] : 'off';
			$header_level    = et_pb_process_header_level( $this->props['title_level'], 'h2' );
			$enable_overlay  = !empty( $this->props['enable_overlay'] ) ? $this->props['enable_overlay'] : 'off';

			$overlay_output = '';
			if ( 'on' === $enable_overlay ) {
				$overlay_icon   = !empty( $this->props['overlay_icon'] ) ? $this->props['overlay_icon'] : '';
				$overlay_output = ET_Builder_Module_Helper_Overlay::render(
					array( 'icon' => $overlay_icon )
				);
			}

			foreach ( $attachment_ids as $attachment_id ) {
				// Get attachment url
				$attachmentImg = wp_get_attachment_image( $attachment_id, 'full' );
				if ( empty($attachmentImg) ) {
					continue;
				}

				if ( file_exists( get_stylesheet_directory() . '/divi-plus/layouts/woo-product-gallery/layout-1.php' ) ) {
					include get_stylesheet_directory() . '/divi-plus/layouts/woo-product-gallery/layout-1.php';
				} elseif ( file_exists( plugin_dir_path( __FILE__ ) . 'layouts/layout-1.php' ) ) {
					include plugin_dir_path( __FILE__ ) . 'layouts/layout-1.php';
				}
			}
		} else {
			// Even not have a default/placeholder image
		}

		if ( $this->props['item_bg'] ) {
			self::set_style( $render_slug, array(
				'selector'    => '%%order_class%% .dipl_woo_product_gallery-item',
				'declaration' => sprintf( 'background: %1$s;', esc_attr( $this->props['item_bg'] ) ),
			) );
		}

		// Load masonry script
		$masonryGutter = '';
		if ( 'masonry' === $layout ) {
			$masonryGutter = '<div class="dipl-masonry-layout-gutter"></div>';
			wp_enqueue_script( 'elicus-images-loaded-script' );
			wp_enqueue_script( 'elicus-isotope-script' );
		}

		if ( 'on' === $enable_lightbox ) {
			wp_enqueue_script( 'magnific-popup' );
			wp_enqueue_style( 'magnific-popup' );
		}

		if ( 'masonry' === $layout || 'on' === $enable_lightbox ) {
			wp_enqueue_script( 'dipl-woo-product-gallery-script', PLUGIN_PATH . 'includes/modules/WooProductGallery/dipl-woo-product-gallery-custom.min.js', array('jquery'), '1.0.0', true );
		}

		$file = et_is_builder_plugin_active() ? 'style-dbp' : 'style';
		wp_enqueue_style( 'dipl-woo-product-gallery-style', PLUGIN_PATH . 'includes/modules/WooProductGallery/' . $file . '.min.css', array(), '1.0.0' );

		$numberOfColumns = et_pb_responsive_options()->get_property_values( $this->props, 'no_of_columns' );
		$column_spacing  = et_pb_responsive_options()->get_property_values( $this->props, 'column_spacing' );

		// Use default main settings for number of columns
		$numberOfColumns['tablet'] = '' !== $numberOfColumns['tablet'] ? $numberOfColumns['tablet'] : $numberOfColumns['desktop'];
		$numberOfColumns['phone']  = '' !== $numberOfColumns['phone'] ? $numberOfColumns['phone'] : $numberOfColumns['tablet'];

		// Use default main settings for column spacing
		$column_spacing['tablet'] = '' !== $column_spacing['tablet'] ? $column_spacing['tablet'] : $column_spacing['desktop'];
		$column_spacing['phone']  = '' !== $column_spacing['phone'] ? $column_spacing['phone'] : $column_spacing['tablet'];

		$breakpoints 	= array( 'desktop', 'tablet', 'phone' );
		if ( 'masonry' == $layout ) {
			$width = array();
			foreach ( $breakpoints as $breakpoint ) {
				if ( 1 === absint( $numberOfColumns[$breakpoint] ) ) {
					$width[$breakpoint] = '100%';
				} else {
					$divided_width 	= 100 / absint( $numberOfColumns[$breakpoint] );
					if ( 0.0 !== floatval( $column_spacing[$breakpoint] ) ) {
						$gutter = floatval( ( floatval( $column_spacing[$breakpoint] ) * ( absint( $numberOfColumns[$breakpoint] ) - 1 ) ) / absint( $numberOfColumns[$breakpoint] ) );
						$divided_width = str_replace( ',', '.', $divided_width );
						$width[$breakpoint] = 'calc(' . $divided_width . '% - ' . $gutter . 'px)';
					} else {
						$width[$breakpoint] = $divided_width . '%';
					}
				}
			}

			et_pb_responsive_options()->generate_responsive_css( $width, '%%order_class%% .dipl-layout-masonry .dipl-column-item', 'width', $render_slug, '!important;', 'range' );
			et_pb_responsive_options()->generate_responsive_css( $column_spacing, '%%order_class%% .dipl-layout-masonry .dipl-masonry-layout-gutter', 'width', $render_slug, '!important;', 'range' );
		} else {

			// Create responsive variables
			$marginWrapper = $marginItem = $widthItem = array();
			foreach ( $breakpoints as $breakpoint ) {
				$marginWrapper[$breakpoint]	= 'calc(-' . $column_spacing[$breakpoint] . '/2)';
				$marginItem[$breakpoint]	= 'calc(' . $column_spacing[$breakpoint] . '/2)';
				$widthItem[$breakpoint]		= 'calc( calc( 100% / ' . $numberOfColumns[$breakpoint] . ' ) - ' . $column_spacing[$breakpoint] . ' )';
			}

			et_pb_responsive_options()->generate_responsive_css( $marginWrapper, '%%order_class%% .dipl-layout-grid', 'margin-left', $render_slug, '!important;', 'range' );
			et_pb_responsive_options()->generate_responsive_css( $marginWrapper, '%%order_class%% .dipl-layout-grid', 'margin-right', $render_slug, '!important;', 'range' );

			et_pb_responsive_options()->generate_responsive_css( $marginItem, '%%order_class%% .dipl-layout-grid .dipl-column-item', 'margin-left', $render_slug, '!important;', 'range' );
			et_pb_responsive_options()->generate_responsive_css( $marginItem, '%%order_class%% .dipl-layout-grid .dipl-column-item', 'margin-right', $render_slug, '!important;', 'range' );
			et_pb_responsive_options()->generate_responsive_css( $widthItem, '%%order_class%% .dipl-layout-grid .dipl-column-item', 'width', $render_slug, '!important;', 'range' );
		}

		// Set item margin bottom for both layout
		et_pb_responsive_options()->generate_responsive_css( $column_spacing, '%%order_class%% .dipl-column-item', 'margin-bottom', $render_slug, '!important;', 'range' );
		
		if ( !empty($this->props['lightbox_background_color']) ) {
			self::set_style( $render_slug, array(
				'selector'    => '%%order_class%%_lightbox.mfp-bg',
				'declaration' => sprintf(
					'background-color: %1$s !important;',
					esc_attr( $this->props['lightbox_background_color'] )
				)
			) );
		}
		if ( !empty($this->props['meta_background_color']) ) {
			self::set_style( $render_slug, array(
				'selector'    => '%%order_class%%_lightbox .dipl_woo_product_gallery-title .et_pb_title, %%order_class%%_lightbox .dipl_woo_product_gallery-caption',
				'declaration' => sprintf(
					'background-color: %1$s !important;',
					esc_attr( $this->props['meta_background_color'] )
				)
			) );
		}
		if ( !empty($this->props['lightbox_close_icon_color']) ) {
			self::set_style( $render_slug, array(
				'selector'    => '%%order_class%%_lightbox .mfp-close',
				'declaration' => sprintf(
					'color: %1$s !important;',
					esc_attr( $this->props['lightbox_close_icon_color'] )
				)
			) );
		}
		if ( !empty($this->props['lightbox_arrows_color']) ) {
			self::set_style( $render_slug, array(
				'selector'    => '%%order_class%%_lightbox .mfp-arrow:after',
				'declaration' => sprintf(
					'color: %1$s !important;',
					esc_attr( $this->props['lightbox_arrows_color'] )
				)
			) );
		}

		if ( !empty($this->props['overlay_icon_color']) ) {
			self::set_style( $render_slug, array(
				'selector'    => '%%order_class%% .dipl_woo_product_gallery-item .et_overlay:before',
				'declaration' => sprintf(
					'color: %1$s !important;',
					esc_attr( $this->props['overlay_icon_color'] )
				)
			) );
		}
		if ( !empty($this->props['overlay_color']) ) {
			self::set_style( $render_slug, array(
				'selector'    => '%%order_class%% .dipl_woo_product_gallery-item .et_overlay',
				'declaration' => sprintf(
					'background-color: %1$s !important;',
					esc_attr( $this->props['overlay_color'] )
				)
			) );
		}
		$overlay_icon_size 	= et_pb_responsive_options()->get_property_values( $this->props, 'overlay_icon_size' );
		et_pb_responsive_options()->generate_responsive_css( $overlay_icon_size, '%%order_class%% .dipl_woo_product_gallery-item  .et_overlay:before', 'font-size', $render_slug, '!important;', 'range' ); 
		
		// Get the positions
		$title_position   = isset( $this->props['title_position'] ) ? $this->props['title_position'] : 'gallery_only';
		$caption_position = isset( $this->props['caption_position'] ) ? $this->props['caption_position'] : 'gallery_only';

		// Check for gallery
		$item_positions = array( 'gallery_only', 'both' );

		if ( ! in_array( $title_position, $item_positions ) && ! in_array( $caption_position, $item_positions ) ) {
			self::set_style( $render_slug, array(
				'selector'    => '%%order_class%% .dipl_woo_product_gallery_title_caption_wrapper',
				'declaration' => 'display: none;',
			) );
		} else {
			if ( ! in_array( $title_position, $item_positions ) ) {
				self::set_style( $render_slug, array(
					'selector'    => '%%order_class%% .dipl_woo_product_gallery-title',
					'declaration' => 'display: none;',
				) );
			}
			if ( ! in_array( $caption_position, $item_positions ) ) {
				self::set_style( $render_slug, array(
					'selector'    => '%%order_class%% .dipl_woo_product_gallery-caption',
					'declaration' => 'display: none;',
				) );
			}
		}

		// Check for lightbox
		$item_positions = array( 'lightbox_only', 'both' );
		if ( ! in_array( $title_position, $item_positions ) && ! in_array( $caption_position, $item_positions ) ) {
			self::set_style( $render_slug, array(
				'selector'    => '%%order_class%%_lightbox .mfp-bottom-bar',
				'declaration' => 'display: none;',
			) );
		} else {
			if ( ! in_array( $title_position, $item_positions ) ) {
				self::set_style( $render_slug, array(
					'selector'    => '%%order_class%%_lightbox .dipl_woo_product_gallery-title',
					'declaration' => 'display: none;',
				) );
			}
			if ( ! in_array( $caption_position, $item_positions ) ) {
				self::set_style( $render_slug, array(
					'selector'    => '%%order_class%%_lightbox .dipl_woo_product_gallery-caption',
					'declaration' => 'display: none;',
				) );
			}
		}

		$fields = array( 'caption_custom_spacing', 'title_custom_spacing' );
		DiviPlusHelper::process_advanced_margin_padding_css( $this, $render_slug, $this->margin_padding, $fields );
		
		self::$rendering = false;

		return sprintf(
			'<div class="dipl_woo_product_gallery_wrap"><div class="dipl_woo_product_gallry-layout dipl-layout-%1$s dipl-column-%2$s">%3$s %4$s</div></div>',
			esc_attr( $layout ),
			esc_attr( $no_of_columns ),
			et_core_intentionally_unescaped( $masonryGutter, 'html'),
			et_core_intentionally_unescaped( $output, 'html' )
		);
	}
}

$plugin_options = get_option( ELICUS_DIVI_PLUS_OPTION );
if ( isset( $plugin_options['dipl-modules'] ) ) {
	$modules = explode( ',', $plugin_options['dipl-modules'] );
	if (
		in_array( 'dipl_woo_product_gallery', $modules, true ) &&
		et_is_woocommerce_plugin_active()
	) {
		new DIPL_WooProductGallery();
	}
} else {
	if ( et_is_woocommerce_plugin_active() ) {
		new DIPL_WooProductGallery();
	}
}