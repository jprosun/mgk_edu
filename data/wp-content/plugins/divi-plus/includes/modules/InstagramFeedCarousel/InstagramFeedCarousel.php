<?php
class DIPL_InstagramFeedCarousel extends ET_Builder_Module {

	public $slug       = 'dipl_instagram_feed_carousel';
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
		$this->name             = esc_html__( 'DP Instagram Feed - Carousel', 'divi-plus' );
		$this->main_css_element = '%%order_class%%';
	}

	public function get_settings_modal_toggles() {
		return array(
			'general'  => array(
				'toggles' => array(
					'integration'     => esc_html__( 'Configuration', 'divi-plus' ),
					'display'         => esc_html__( 'Display', 'divi-plus' ),
					'slider_settings' => esc_html__( 'Slider', 'divi-plus' ),
				),
			),
			'advanced' => array(
				'toggles' => array(
					'caption'       => array(
						'title' => esc_html__( 'Caption', 'divi-plus' ),
					),
					'post_item'     => array(
						'title' => esc_html__( 'Instagram Item', 'divi-plus' ),
					),
					'follow_button' => array(
						'title' => esc_html__( 'Follow Button', 'divi-plus' ),
					),
					'slider_styles' => array(
						'title' => esc_html__( 'Slider', 'divi-plus' ),
					),
				),
			),
		);
	}

	public function get_advanced_fields_config() {
		return array(
			'fonts'        => array(
				'caption_fonts' => array(
					'label'       => esc_html__( 'Caption', 'divi-plus' ),
					'css'         => array(
						'main'      => "{$this->main_css_element} .dipl_instagram_feed_item .dipl_instagram_item-caption",
						'important' => 'all',
					),
					'tab_slug'    => 'advanced',
					'toggle_slug' => 'caption',
				),
			),
			'image_icon'   => array(
				'image_icon' => array(
					'margin_padding'  => array(
						'css' => array(
							'important' => 'all',
						),
					),
					'option_category' => 'layout',
					'tab_slug'        => 'advanced',
					'toggle_slug'     => 'caption',
					'label'           => esc_html__( 'Caption', 'divi-plus' ),
					'css'             => array(
						'padding' => '%%order_class%% .dipl_instagram_feed_item .dipl_instagram_item-caption',
						'margin'  => '%%order_class%% .dipl_instagram_feed_item .dipl_instagram_item-caption',
						'main'    => '%%order_class%% .dipl_instagram_feed_item .dipl_instagram_item-caption',
					),
				),
			),
			'borders'      => array(
				'item_border' => array(
					'label'       => esc_html__( 'Instagram Item', 'divi-plus' ),
					'css'         => array(
						'main' => array(
							'border_radii'  => '%%order_class%% .dipl_instagram_feed_item',
							'border_styles' => '%%order_class%% .dipl_instagram_feed_item',
							'important'     => 'all',
						),
					),
					'tab_slug'    => 'advanced',
					'toggle_slug' => 'post_item',
				),
				'default'     => array(
					'css' => array(
						'main' => array(
							'border_styles' => '%%order_class%%',
							'border_radii'  => '%%order_class%%',
						),
					),
				),
			),
			'box_shadow'   => array(
				'item_box' => array(
					'label'       => esc_html__( 'Post Item', 'divi-plus' ),
					'css'         => array(
						'main'      => '%%order_class%% .dipl_instagram_feed_item',
						'important' => 'all',
					),
					'tab_slug'    => 'advanced',
					'toggle_slug' => 'post_item',
				),
				'default'  => array(
					'css' => array(
						'main' => '%%order_class%%',
					),
				),
			),
			'button'       => array(
				'follow_button' => array(
					'label'          => esc_html__( 'Follow Button', 'divi-plus' ),
					'css'            => array(
						'main'      => "{$this->main_css_element} .dipl_instagram_follow_button",
						'alignment' => "{$this->main_css_element} .dipl_instagram_feed_button_wrapper .et_pb_button_wrapper",
						'important' => 'all',
					),
					'margin_padding' => array(
						'css' => array(
							'margin'    => "{$this->main_css_element} .dipl_instagram_feed_button_wrapper .et_pb_button_wrapper",
							'padding'   => "{$this->main_css_element} .dipl_instagram_follow_button, {$this->main_css_element} .dipl_instagram_follow_button:hover",
							'important' => 'all',
						),
					),
					'box_shadow'     => array(
						'css' => array(
							'main'      => "{$this->main_css_element} .dipl_instagram_follow_button",
							'important' => 'all',
						),
					),
					'use_alignment'  => false,
					'tab_slug'       => 'advanced',
					'toggle_slug'    => 'follow_button',
				),
			),
			'slider_margin_padding' => array(
				'slider_container' => array(
					'margin_padding' => array(
						'css' => array(
							'use_margin' => false,
							'padding'   => "{$this->main_css_element} .dipl-instagram-feed-carousel-layout > .swiper-container",
							'important' => 'all',
						),
					),
				),
				'arrows' => array(
					'margin_padding' => array(
						'css' => array(
							'use_margin' => false,
							'padding'    => "{$this->main_css_element} .dipl_swiper_navigation .swiper-button-next, {$this->main_css_element} .dipl_swiper_navigation .swiper-button-prev",
							'important'  => 'all',
						),
					),
				),
			),
			'text'         => false,
			'text_shadow'  => false,
			'link_options' => false,
		);
	}

	public function get_fields() {
		$insta_access_token = '';
		$plugin_options     = get_option( ELICUS_DIVI_PLUS_OPTION );
		if ( isset( $plugin_options['dipl-instagram-access-token'] ) && '' !== $plugin_options['dipl-instagram-access-token'] ) {
			$insta_access_token = sanitize_text_field( $plugin_options['dipl-instagram-access-token'] );
		}

		return array(
			'access_token'                  => array(
				'label'            => esc_html__( 'Access Token', 'divi-plus' ),
				'type'             => 'textarea',
				'option_category'  => 'basic_option',
				'default'          => $insta_access_token,
				'tab_slug'         => 'general',
				'toggle_slug'      => 'integration',
				'description'      => et_get_safe_localization(
					sprintf(
						'%1$s <a href="%2$s" title="%3$s" target="_blank">%4$s</a> %5$s <a href="%6$s" title="%7$s" target="_blank">%8$s</a> %9$s',
						esc_html__( 'Here you can enter the instagram access token for the instagram modules. You can use a single access token for all instagram modules and it can be saved in the plugin', 'divi-plus' ),
						esc_url( admin_url( '/options-general.php?page=divi-plus-options&menu=integration&submenu=instagram' ) ),
						esc_html__( 'Divi Plus Settings', 'divi-plus' ),
						esc_html__( 'settings', 'divi-plus' ),
						esc_html__( 'page. Or if you want to use different access token for each instagram module then you can enter here. Click', 'divi-plus' ),
						esc_url( 'https://diviextended.com/documentation/divi-plus/how-to-get-instagram-feed-access-token/' ),
						esc_html__( 'Facebook APP', 'divi-plus' ),
						esc_html__( 'here', 'divi-plus' ),
						esc_html__( 'to know about how to create one.', 'divi-plus' )
					)
				),
				'computed_affects' => array(
					'__instagram_carousel_feed',
				),
			),
			'cache'                         => array(
				'label'            => esc_html__( 'Cache (In Minutes)', 'divi-plus' ),
				'type'             => 'number',
				'default'          => 60,
				'option_category'  => 'basic_option',
				'tab_slug'         => 'general',
				'toggle_slug'      => 'integration',
				'description'      => esc_html__( 'Enter the cache in minutes, it will get data from the cache instead of feching from database.', 'divi-plus' ),
				'computed_affects' => array(
					'__instagram_carousel_feed',
				),
			),
			'no_of_posts'                   => array(
				'label'            => esc_html__( 'Number of Posts', 'divi-plus' ),
				'type'             => 'number',
				'default'          => 12,
				'option_category'  => 'basic_option',
				'tab_slug'         => 'general',
				'toggle_slug'      => 'display',
				'description'      => esc_html__( 'Here you can enter number of posts to display.', 'divi-plus' ),
				'computed_affects' => array(
					'__instagram_carousel_feed',
				),
			),
			'no_result_text'                => array(
				'label'           => esc_html__( 'No Result Text', 'divi-plus' ),
				'type'            => 'text',
				'option_category' => 'configuration',
				'default'         => esc_html__( 'No instagram posts found in your account.', 'divi-plus' ),
				'tab_slug'        => 'general',
				'toggle_slug'     => 'display',
				'description'     => esc_html__( 'Here you can define custom no result text.', 'divi-plus' ),
			),
			'link_post'                     => array(
				'label'            => esc_html__( 'Link Images to Instagram?', 'divi-plus' ),
				'type'             => 'yes_no_button',
				'option_category'  => 'basic_option',
				'options'          => array(
					'off' => esc_html__( 'No', 'divi-plus' ),
					'on'  => esc_html__( 'Yes', 'divi-plus' ),
				),
				'default'          => 'off',
				'description'      => esc_html__( 'Here you can choose to link the image to instagram or not.', 'divi-plus' ),
				'default_on_front' => 'off',
				'tab_slug'         => 'general',
				'toggle_slug'      => 'display',
				'computed_affects' => array(
					'__instagram_carousel_feed',
				),
			),
			'show_caption'                  => array(
				'label'            => esc_html__( 'Display Caption?', 'divi-plus' ),
				'type'             => 'yes_no_button',
				'option_category'  => 'basic_option',
				'options'          => array(
					'off' => esc_html__( 'No', 'divi-plus' ),
					'on'  => esc_html__( 'Yes', 'divi-plus' ),
				),
				'default'          => 'off',
				'description'      => esc_html__( 'Here you can choose whether to hide or show the captions.', 'divi-plus' ),
				'default_on_front' => 'off',
				'tab_slug'         => 'general',
				'toggle_slug'      => 'display',
				'computed_affects' => array(
					'__instagram_carousel_feed',
				),
			),
			'show_follow_btn'               => array(
				'label'            => esc_html__( 'Display Follow Button?', 'divi-plus' ),
				'type'             => 'yes_no_button',
				'option_category'  => 'basic_option',
				'options'          => array(
					'off' => esc_html__( 'No', 'divi-plus' ),
					'on'  => esc_html__( 'Yes', 'divi-plus' ),
				),
				'default'          => 'off',
				'description'      => esc_html__( 'Here you can choose whether to hide or show the follow button.', 'divi-plus' ),
				'default_on_front' => 'off',
				'tab_slug'         => 'general',
				'toggle_slug'      => 'display',
			),
			'follow_btn_text'               => array(
				'label'           => esc_html__( 'Button Text', 'divi-plus' ),
				'type'            => 'text',
				'default'         => 'Follow Now',
				'option_category' => 'basic_option',
				'tab_slug'        => 'general',
				'toggle_slug'     => 'display',
				'description'     => esc_html__( 'The text will display on the follow button.', 'divi-plus' ),
				'show_if'         => array(
					'show_follow_btn' => 'on',
				),
			),
			'item_bg'                       => array(
				'label'       => esc_html__( 'Instagram Item Background Color', 'divi-plus' ),
				'type'        => 'color-alpha',
				'hover'       => 'tabs',
				'default'     => '',
				'tab_slug'    => 'advanced',
				'toggle_slug' => 'post_item',
				'description' => esc_html__( 'Here you can define a custom color for your item background.', 'divi-plus' ),
			),
			'slide_effect'                  => array(
				'label'           => esc_html__( 'Slide Effect', 'divi-plus' ),
				'type'            => 'select',
				'option_category' => 'layout',
				'options'         => array(
					'slide'     => esc_html__( 'Slide', 'divi-plus' ),
					'cube'      => esc_html__( 'Cube', 'divi-plus' ),
					'coverflow' => esc_html__( 'Coverflow', 'divi-plus' ),
					'flip'      => esc_html__( 'Flip', 'divi-plus' ),
					'fade'      => esc_html__( 'Fade', 'divi-plus' ),
				),
				'default'         => 'slide',
				'tab_slug'        => 'general',
				'toggle_slug'     => 'slider_settings',
				'description'     => esc_html__( 'Here you can choose the slide animation effect.', 'divi-plus' ),
			),
			'items_per_slide'               => array(
				'label'            => esc_html__( 'Items Per View', 'divi-plus' ),
				'type'             => 'select',
				'option_category'  => 'layout',
				'options'          => array(
					'1'  => esc_html__( '1', 'divi-plus' ),
					'2'  => esc_html__( '2', 'divi-plus' ),
					'3'  => esc_html__( '3', 'divi-plus' ),
					'4'  => esc_html__( '4', 'divi-plus' ),
					'5'  => esc_html__( '5', 'divi-plus' ),
					'6'  => esc_html__( '6', 'divi-plus' ),
					'7'  => esc_html__( '7', 'divi-plus' ),
					'8'  => esc_html__( '8', 'divi-plus' ),
					'9'  => esc_html__( '9', 'divi-plus' ),
					'10' => esc_html__( '10', 'divi-plus' ),
				),
				'default'          => '4',
				'default_on_front' => '4',
				'mobile_options'   => true,
				'show_if'          => array(
					'slide_effect' => array( 'slide', 'coverflow' ),
				),
				'tab_slug'         => 'general',
				'toggle_slug'      => 'slider_settings',
				'description'      => esc_html__( 'Here you can choose the number of products to display per slide.', 'divi-plus' ),
			),
			'slides_per_group'              => array(
				'label'           => esc_html__( 'Number of Slides Per Group', 'divi-plus' ),
				'type'            => 'select',
				'option_category' => 'layout',
				'options'         => array(
					'1'  => esc_html__( '1', 'divi-plus' ),
					'2'  => esc_html__( '2', 'divi-plus' ),
					'3'  => esc_html__( '3', 'divi-plus' ),
					'4'  => esc_html__( '4', 'divi-plus' ),
					'5'  => esc_html__( '5', 'divi-plus' ),
					'6'  => esc_html__( '6', 'divi-plus' ),
					'7'  => esc_html__( '7', 'divi-plus' ),
					'8'  => esc_html__( '8', 'divi-plus' ),
					'9'  => esc_html__( '9', 'divi-plus' ),
					'10' => esc_html__( '10', 'divi-plus' ),
				),
				'default'         => '1',
				'mobile_options'  => true,
				'show_if'         => array(
					'slide_effect' => array( 'slide' ),
				),
				'tab_slug'        => 'general',
				'toggle_slug'     => 'slider_settings',
				'description'     => esc_html__( 'Here you can choose the number of slides per group to slide by.', 'divi-plus' ),
			),
			'space_between_slides'          => array(
				'label'           => esc_html__( 'Space between Slides', 'divi-plus' ),
				'type'            => 'range',
				'option_category' => 'layout',
				'range_settings'  => array(
					'min'  => '10',
					'max'  => '100',
					'step' => '1',
				),
				'show_if'         => array(
					'slide_effect' => array( 'slide', 'coverflow' ),
				),
				'fixed_unit'      => 'px',
				'default'         => '20px',
				'mobile_options'  => true,
				'tab_slug'        => 'general',
				'toggle_slug'     => 'slider_settings',
				'description'     => esc_html__( 'Move the slider or input the value to increse or decrease the space between slides.', 'divi-plus' ),
			),
			'enable_coverflow_shadow'       => array(
				'label'           => esc_html__( 'Enable Slide Shadow', 'divi-plus' ),
				'type'            => 'yes_no_button',
				'option_category' => 'configuration',
				'options'         => array(
					'on'  => esc_html__( 'Yes', 'divi-plus' ),
					'off' => esc_html__( 'No', 'divi-plus' ),
				),
				'default'         => 'off',
				'show_if'         => array(
					'slide_effect' => 'coverflow',
				),
				'tab_slug'        => 'general',
				'toggle_slug'     => 'slider_settings',
				'description'     => esc_html__( 'Enable Slide Shadow For Coverflow Effect.', 'divi-plus' ),
			),
			'coverflow_shadow_color'        => array(
				'label'        => esc_html__( 'Shadow Color', 'divi-plus' ),
				'type'         => 'color-alpha',
				'custom_color' => true,
				'show_if'      => array(
					'slide_effect'            => 'coverflow',
					'enable_coverflow_shadow' => 'on',
				),
				'default'      => '#ccc',
				'tab_slug'     => 'general',
				'toggle_slug'  => 'slider_settings',
				'description'  => esc_html__( 'Here you can select color for the Shadow.', 'divi-plus' ),
			),
			'coverflow_rotate'              => array(
				'label'           => esc_html__( 'Coverflow Rotate', 'divi-plus' ),
				'type'            => 'range',
				'option_category' => 'font_option',
				'range_settings'  => array(
					'min'  => '1',
					'max'  => '360',
					'step' => '1',
				),
				'unitless'        => true,
				'show_if'         => array(
					'slide_effect' => 'coverflow',
				),
				'default'         => '40',
				'tab_slug'        => 'general',
				'toggle_slug'     => 'slider_settings',
				'description'     => esc_html__( 'Coverflow Rotate Slide.', 'divi-plus' ),
			),
			'coverflow_depth'               => array(
				'label'           => esc_html__( 'Coverflow Depth', 'divi-plus' ),
				'type'            => 'range',
				'option_category' => 'font_option',
				'range_settings'  => array(
					'min'  => '1',
					'max'  => '1000',
					'step' => '1',
				),
				'unitless'        => true,
				'show_if'         => array(
					'slide_effect' => 'coverflow',
				),
				'default'         => '100',
				'tab_slug'        => 'general',
				'toggle_slug'     => 'slider_settings',
				'description'     => esc_html__( 'Coverflow Depth Slide.', 'divi-plus' ),
			),
			'equalize_slides_height'        => array(
				'label'            => esc_html__( 'Equalize Slides Height', 'divi-plus' ),
				'type'             => 'yes_no_button',
				'option_category'  => 'configuration',
				'options'          => array(
					'on'  => esc_html__( 'Yes', 'divi-plus' ),
					'off' => esc_html__( 'No', 'divi-plus' ),
				),
				'default'          => 'off',
				'default_on_front' => 'off',
				'tab_slug'         => 'general',
				'toggle_slug'      => 'slider_settings',
				'description'      => esc_html__( 'Choose whether or not equalize slides height.', 'divi-plus' ),
			),
			'auto_height_slider'            => array(
				'label'           => esc_html__( 'Auto Height Slider', 'divi-plus' ),
				'type'            => 'yes_no_button',
				'option_category' => 'configuration',
				'options'         => array(
					'off' => esc_html__( 'Off', 'divi-plus' ),
					'on'  => esc_html__( 'On', 'divi-plus' ),
				),
				'default'         => 'off',
				'show_if'         => array(
					'equalize_slides_height' => 'off',
				),
				'tab_slug'        => 'general',
				'toggle_slug'     => 'slider_settings',
				'description'     => esc_html__( 'Here you can choose whether or not slider height should adjust according to each slide.', 'divi-plus' ),
			),
			'slider_loop'                   => array(
				'label'           => esc_html__( 'Enable Loop', 'divi-plus' ),
				'type'            => 'yes_no_button',
				'option_category' => 'configuration',
				'options'         => array(
					'on'  => esc_html__( 'Yes', 'divi-plus' ),
					'off' => esc_html__( 'No', 'divi-plus' ),
				),
				'default'         => 'off',
				'tab_slug'        => 'general',
				'toggle_slug'     => 'slider_settings',
				'description'     => esc_html__( 'Here you can enable loop for the slides.', 'divi-plus' ),
			),
			'autoplay'                      => array(
				'label'           => esc_html__( 'Autoplay', 'divi-plus' ),
				'type'            => 'yes_no_button',
				'option_category' => 'configuration',
				'options'         => array(
					'on'  => esc_html__( 'Yes', 'divi-plus' ),
					'off' => esc_html__( 'No', 'divi-plus' ),
				),
				'default'         => 'on',
				'tab_slug'        => 'general',
				'toggle_slug'     => 'slider_settings',
				'description'     => esc_html__( 'This controls the auto play the slider.', 'divi-plus' ),
			),
			'enable_linear_transition'      => array(
				'label'           => esc_html__( 'Enable Linear Transition', 'divi-plus' ),
				'type'            => 'yes_no_button',
				'option_category' => 'configuration',
				'options'         => array(
					'on'  => esc_html__( 'Yes', 'divi-plus' ),
					'off' => esc_html__( 'No', 'divi-plus' ),
				),
				'default'         => 'off',
				'tab_slug'        => 'general',
				'toggle_slug'     => 'slider_settings',
				'description'     => esc_html__( 'Here you can choose whether or not to enable linear transition between slides.', 'divi-plus' ),
			),
			'autoplay_speed'                => array(
				'label'           => esc_html__( 'Autoplay Delay', 'divi-plus' ),
				'type'            => 'text',
				'option_category' => 'configuration',
				'default'         => '3000',
				'show_if'         => array(
					'autoplay' => 'on',
				),
				'tab_slug'        => 'general',
				'toggle_slug'     => 'slider_settings',
				'description'     => esc_html__( 'This controls the time of the slide before the transition.', 'divi-plus' ),
			),
			'pause_on_hover'                => array(
				'label'           => esc_html__( 'Pause On Hover', 'divi-plus' ),
				'type'            => 'yes_no_button',
				'option_category' => 'configuration',
				'options'         => array(
					'on'  => esc_html__( 'Yes', 'divi-plus' ),
					'off' => esc_html__( 'No', 'divi-plus' ),
				),
				'default'         => 'on',
				'show_if'         => array(
					'autoplay' => 'on',
				),
				'tab_slug'        => 'general',
				'toggle_slug'     => 'slider_settings',
				'description'     => esc_html__( 'Control for pausing slides on mouse hover.', 'divi-plus' ),
			),
			'slide_transition_duration'     => array(
				'label'           => esc_html__( 'Transition Duration', 'divi-plus' ),
				'type'            => 'text',
				'option_category' => 'configuration',
				'default'         => '1000',
				'tab_slug'        => 'general',
				'toggle_slug'     => 'slider_settings',
				'description'     => esc_html__( 'Here you can specify the duration of transition for each slide in miliseconds.', 'divi-plus' ),
			),
			'show_arrow'                    => array(
				'label'            => esc_html__( 'Show Arrows', 'divi-plus' ),
				'type'             => 'yes_no_button',
				'option_category'  => 'configuration',
				'options'          => array(
					'on'  => esc_html__( 'Yes', 'divi-plus' ),
					'off' => esc_html__( 'No', 'divi-plus' ),
				),
				'default'          => 'off',
				'default_on_front' => 'off',
				'tab_slug'         => 'general',
				'toggle_slug'      => 'slider_settings',
				'description'      => esc_html__( 'Choose whether or not the previous & next arrows should be visible.', 'divi-plus' ),
			),
			'previous_slide_arrow'          => array(
				'label'           => esc_html__( 'Previous Arrow', 'divi-plus' ),
				'type'            => 'select_icon',
				'option_category' => 'basic_option',
				'class'           => array(
					'et-pb-font-icon',
				),
				'show_if'         => array(
					'show_arrow' => 'on',
				),
				'tab_slug'        => 'general',
				'toggle_slug'     => 'slider_settings',
				'description'     => esc_html__( 'Here you can select the icon to be used for the previous slide navigation.', 'divi-plus' ),
			),
			'next_slide_arrow'              => array(
				'label'           => esc_html__( 'Next Arrow', 'divi-plus' ),
				'type'            => 'select_icon',
				'option_category' => 'basic_option',
				'class'           => array(
					'et-pb-font-icon',
				),
				'show_if'         => array(
					'show_arrow' => 'on',
				),
				'tab_slug'        => 'general',
				'toggle_slug'     => 'slider_settings',
				'description'     => esc_html__( 'Here you can select the icon to be used for the next slide navigation.', 'divi-plus' ),
			),
			'show_arrow_on_hover'           => array(
				'label'            => esc_html__( 'Show Arrows On Hover', 'divi-plus' ),
				'type'             => 'yes_no_button',
				'option_category'  => 'configuration',
				'options'          => array(
					'on'  => esc_html__( 'Yes', 'divi-plus' ),
					'off' => esc_html__( 'No', 'divi-plus' ),
				),
				'show_if'          => array(
					'show_arrow' => 'on',
				),
				'default'          => 'off',
				'default_on_front' => 'off',
				'tab_slug'         => 'general',
				'toggle_slug'      => 'slider_settings',
				'description'      => esc_html__( 'Choose whether or not the previous and next arrows should be visible.', 'divi-plus' ),
			),
			'arrows_position'               => array(
				'label'           => esc_html__( 'Arrows Position', 'divi-plus' ),
				'type'            => 'select',
				'option_category' => 'layout',
				'options'         => array(
					'inside'        => esc_html__( 'Inside', 'divi-plus' ),
					'outside'       => esc_html__( 'Outside', 'divi-plus' ),
					'top_left'      => esc_html__( 'Top Left', 'divi-plus' ),
					'top_right'     => esc_html__( 'Top Right', 'divi-plus' ),
					'top_center'    => esc_html__( 'Top Center', 'divi-plus' ),
					'bottom_left'   => esc_html__( 'Bottom Left', 'divi-plus' ),
					'bottom_right'  => esc_html__( 'Bottom Right', 'divi-plus' ),
					'bottom_center' => esc_html__( 'Bottom Center', 'divi-plus' ),
				),
				'default'         => 'inside',
				'mobile_options'  => true,
				'show_if'         => array(
					'show_arrow' => 'on',
				),
				'tab_slug'        => 'general',
				'toggle_slug'     => 'slider_settings',
				'description'     => esc_html__( 'Here you can choose the arrows position.', 'divi-plus' ),
			),
			'show_control_dot'              => array(
				'label'            => esc_html__( 'Show Dots Pagination', 'divi-plus' ),
				'type'             => 'yes_no_button',
				'option_category'  => 'configuration',
				'options'          => array(
					'on'  => esc_html__( 'Yes', 'divi-plus' ),
					'off' => esc_html__( 'No', 'divi-plus' ),
				),
				'default'          => 'off',
				'default_on_front' => 'off',
				'tab_slug'         => 'general',
				'toggle_slug'      => 'slider_settings',
				'description'      => esc_html__( 'This setting will turn on and off the pagination of the slider.', 'divi-plus' ),
			),
			'control_dot_style'             => array(
				'label'           => esc_html__( 'Dots Pagination Style', 'divi-plus' ),
				'type'            => 'select',
				'option_category' => 'layout',
				'options'         => array(
					'solid_dot'       => esc_html__( 'Solid Dot', 'divi-plus' ),
					'transparent_dot' => esc_html__( 'Transparent Dot', 'divi-plus' ),
					'stretched_dot'   => esc_html__( 'Stretched Dot', 'divi-plus' ),
					'line'            => esc_html__( 'Line', 'divi-plus' ),
					'rounded_line'    => esc_html__( 'Rounded Line', 'divi-plus' ),
					'square_dot'      => esc_html__( 'Squared Dot', 'divi-plus' ),
				),
				'show_if'         => array(
					'show_control_dot' => 'on',
				),
				'default'         => 'solid_dot',
				'tab_slug'        => 'general',
				'toggle_slug'     => 'slider_settings',
				'description'     => esc_html__( 'control dot style', 'divi-plus' ),
			),
			'enable_dynamic_dots'           => array(
				'label'           => esc_html__( 'Enable Dynamic Dots', 'divi-plus' ),
				'type'            => 'yes_no_button',
				'option_category' => 'configuration',
				'options'         => array(
					'on'  => esc_html__( 'Yes', 'divi-plus' ),
					'off' => esc_html__( 'No', 'divi-plus' ),
				),
				'default'         => 'off',
				'show_if'         => array(
					'show_control_dot'  => 'on',
					'control_dot_style' => array(
						'solid_dot',
						'transparent_dot',
						'square_dot',
					),
				),
				'tab_slug'        => 'general',
				'toggle_slug'     => 'slider_settings',
				'description'     => esc_html__( 'This setting will turn on and off the dynamic pagination of the slider.', 'divi-plus' ),
			),
			'arrows_custom_padding'         => array(
				'label'            => esc_html__( 'Arrows Padding', 'divi-plus' ),
				'type'             => 'custom_padding',
				'option_category'  => 'layout',
				'show_if'          => array(
					'show_arrow' => 'on',
				),
				'default'          => '5px|10px|5px|10px|true|true',
				'default_on_front' => '5px|10px|5px|10px|true|true',
				'mobile_options'   => true,
				'hover'            => false,
				'tab_slug'         => 'advanced',
				'toggle_slug'      => 'slider_styles',
				'description'      => esc_html__( 'Padding adds extra space to the inside of the element, increasing the distance between the edge of the element and its inner contents.', 'divi-plus' ),
			),
			'arrow_font_size'               => array(
				'label'            => esc_html__( 'Arrow Font Size', 'divi-plus' ),
				'type'             => 'range',
				'option_category'  => 'layout',
				'range_settings'   => array(
					'min'  => '10',
					'max'  => '100',
					'step' => '1',
				),
				'show_if'          => array(
					'show_arrow' => 'on',
				),
				'default'          => '24px',
				'default_on_front' => '24px',
				'tab_slug'         => 'advanced',
				'toggle_slug'      => 'slider_styles',
				'description'      => esc_html__( 'Here you can choose the arrow font size.', 'divi-plus' ),
			),
			'arrow_color'                   => array(
				'label'        => esc_html__( 'Arrow Color', 'divi-plus' ),
				'type'         => 'color-alpha',
				'custom_color' => true,
				'show_if'      => array(
					'show_arrow' => 'on',
				),
				'hover'        => 'tabs',
				'tab_slug'     => 'advanced',
				'toggle_slug'  => 'slider_styles',
				'description'  => esc_html__( 'Here you can define color for the arrow', 'divi-plus' ),
			),
			'arrow_background_color'        => array(
				'label'        => esc_html__( 'Arrow Background', 'divi-plus' ),
				'type'         => 'color-alpha',
				'custom_color' => true,
				'show_if'      => array(
					'show_arrow' => 'on',
				),
				'hover'        => 'tabs',
				'tab_slug'     => 'advanced',
				'toggle_slug'  => 'slider_styles',
				'description'  => esc_html__( 'Here you can choose a custom color to be used for the shape background of arrows.', 'divi-plus' ),
			),
			'arrow_background_border_size'  => array(
				'label'           => esc_html__( 'Arrow Background Border', 'divi-plus' ),
				'type'            => 'range',
				'option_category' => 'layout',
				'range_settings'  => array(
					'min'  => '1',
					'max'  => '10',
					'step' => '1',
				),
				'show_if'         => array(
					'show_arrow' => 'on',
				),
				'default'         => '0px',
				'tab_slug'        => 'advanced',
				'toggle_slug'     => 'slider_styles',
				'description'     => esc_html__( 'Move the slider or input the value to increase or decrease the border size of the arrow background.', 'divi-plus' ),
			),
			'arrow_background_border_color' => array(
				'label'        => esc_html__( 'Arrow Background Border Color', 'divi-plus' ),
				'type'         => 'color-alpha',
				'custom_color' => true,
				'show_if'      => array(
					'show_arrow' => 'on',
				),
				'hover'        => 'tabs',
				'tab_slug'     => 'advanced',
				'toggle_slug'  => 'slider_styles',
				'description'  => esc_html__( 'Here you can choose a custom color to be used for the arrow border', 'divi-plus' ),
			),
			'control_dot_active_color'      => array(
				'label'        => esc_html__( 'Active Dot Pagination Color', 'divi-plus' ),
				'type'         => 'color-alpha',
				'custom_color' => true,
				'show_if'      => array(
					'show_control_dot' => 'on',
				),
				'default'      => '#000000',
				'tab_slug'     => 'advanced',
				'toggle_slug'  => 'slider_styles',
				'description'  => esc_html__( 'Here you can define color for the active pagination item.', 'divi-plus' ),
			),
			'control_dot_inactive_color'    => array(
				'label'        => esc_html__( 'Inactive Dot Pagination Color', 'divi-plus' ),
				'type'         => 'color-alpha',
				'custom_color' => true,
				'show_if'      => array(
					'show_control_dot' => 'on',
				),
				'default'      => '#cccccc',
				'tab_slug'     => 'advanced',
				'toggle_slug'  => 'slider_styles',
				'description'  => esc_html__( 'Here you can define color for the inactive pagination item.', 'divi-plus' ),
			),
			'slider_container_custom_padding' => array(
				'label'            => esc_html__( 'Slider Container Padding', 'divi-plus' ),
				'type'             => 'custom_padding',
				'option_category'  => 'layout',
				'default'		   => '',
				'default_on_front' => '',
				'mobile_options'   => true,
				'hover'            => false,
				'tab_slug'         => 'advanced',
				'toggle_slug'      => 'margin_padding',
				'description'      => esc_html__( 'Padding adds extra space to the inside of the element, increasing the distance between the edge of the element and its inner contents.', 'divi-plus' ),
			),
			'__instagram_carousel_feed'     => array(
				'type'                => 'computed',
				'computed_callback'   => array( 'DIPL_InstagramFeedCarousel', 'get_compute_carousel_instagram_feed' ),
				'computed_depends_on' => array(
					'access_token',
					'no_of_posts',
					'cache',
					'link_post',
					'show_caption',
				),
			),
		);
	}

	private static function divi_plus_instagram_feed_callback( $cache, $instagram_token, $limit ) {

		$instagram_key       = 'divi_plus_instagram_key_' . wp_hash( $instagram_token );
		$instagram_feed_data = get_transient( $instagram_key );

		if ( ! $instagram_feed_data || $limit > $instagram_feed_data['limit'] ) {
			$request_args                     = array(
				'timeout' => 20,
			);
			$instagram_feed                   = wp_remote_retrieve_body( wp_remote_get( 'https://graph.instagram.com/me/media?fields=id,media_url,caption,media_type,thumbnail_url,timestamp,username,permalink&limit=' . $limit . '&access_token=' . $instagram_token, $request_args ) );
			$instagram_feed_data              = json_decode( $instagram_feed, true );
			$instagram_feed_data['limit']     = $limit;
			$instagram_feed_data['user_link'] = isset( $instagram_feed_data['data'][0]['username'] ) ? $instagram_feed_data['data'][0]['username'] : '';

			if ( ! empty( $instagram_feed_data['data'] ) ) {
				set_transient( $instagram_key, $instagram_feed_data, $cache );
			}
		}

		return $instagram_feed_data;
	}

	public function before_render() {
		$is_responsive = et_pb_responsive_options()->is_responsive_enabled( $this->props, 'items_per_slide' );
		if ( ! $is_responsive ) {
			$items_per_slide = $this->props['items_per_slide'];
			if ( 'slide' === $this->props['slide_effect'] ) {
				$items_per_slide_tablet = $items_per_slide < 3 ? $items_per_slide : 2;
				$items_per_slide_mobile = 1;
			} elseif ( 'coverflow' === $this->props['slide_effect'] ) {
				$items_per_slide_tablet = 3;
				$items_per_slide_mobile = 1;
			}
			if ( isset( $items_per_slide_tablet ) && '' !== $items_per_slide_tablet ) {
				$this->props['items_per_slide_tablet'] = $items_per_slide_tablet;
			}

			if ( isset( $items_per_slide_mobile ) && '' !== $items_per_slide_mobile ) {
				$this->props['items_per_slide_phone'] = $items_per_slide_mobile;
			}
		}
	}

	/**
	 * This function return values to react for front end builder.
	 *
	 * @param array arguments to get instagram items data
	 * @return array
	 * */
	public static function get_compute_carousel_instagram_feed( $args = array(), $conditional_tags = array(), $current_page = array() ) {
		if ( self::$rendering ) {
			// We are trying to render a Blog module while a Blog module is already being rendered
			// which means we have most probably hit an infinite recursion. While not necessarily
			// the case, rendering a post which renders a Blog module which renders a post
			// which renders a Blog module is not a sensible use-case.
			return '';
		}

		$insta_access_token = '';
		$plugin_options     = get_option( ELICUS_DIVI_PLUS_OPTION );
		if ( isset( $plugin_options['dipl-instagram-access-token'] ) && '' !== $plugin_options['dipl-instagram-access-token'] ) {
			$insta_access_token = sanitize_text_field( $plugin_options['dipl-instagram-access-token'] );
		}

		$defaults = array(
			'access_token' => $insta_access_token,
			'no_of_posts'  => 12,
			'cache'        => 60,
			'link_post'    => 'off',
			'show_caption' => 'off',
		);

		$args = wp_parse_args( $args, $defaults );

		foreach ( $defaults as $key => $default ) {
			if ( isset( $args[ $key ] ) && et_()->includes( $args[ $key ], '%' ) ) {
				// phpcs:ignore ET.Sniffs.ValidatedSanitizedInput.InputNotSanitized
				$prepared_value = preg_replace( '/%([a-f0-9]{2})/', '%_$1', $args[ $key ] );
				${$key}         = preg_replace( '/%_([a-f0-9]{2})/', '%$1', trim( sanitize_text_field( wp_unslash( $prepared_value ) ) ) );
			} else {
				${$key} = sanitize_text_field( et_()->array_get( $args, $key, $default ) );
			}
		}

		self::$rendering = true;

		$output_array = array();
		$followLink   = '';

		// Retrieving data form instagram
		$layout = 'layout-1';
		if ( ! empty( $access_token ) ) {

			// Get instagram feed
			$instagram_feed_data = self::divi_plus_instagram_feed_callback( $cache, $access_token, $no_of_posts );
			foreach ( array_slice( $instagram_feed_data['data'], 0, $no_of_posts ) as $data ) {
				// Get the data
				$media_type = isset( $data['media_type'] ) ? $data['media_type'] : '';
				$media_url  = ( 'VIDEO' === $media_type ) ? esc_url( $data['thumbnail_url'] ) : ( isset( $data['media_url'] ) ? esc_url( $data['media_url'] ) : '' );

				// If no URL
				if ( '' === $media_url ) {
					continue;
				}

				$instaFeed = '';
				if ( file_exists( get_stylesheet_directory() . '/divi-plus/layouts/instagram-feed-carousel/' . sanitize_file_name( $layout ) . '.php' ) ) {
					include get_stylesheet_directory() . '/divi-plus/layouts/instagram-feed-carousel/' . sanitize_file_name( $layout ) . '.php';
				} elseif ( file_exists( plugin_dir_path( __FILE__ ) . 'layouts/' . sanitize_file_name( $layout ) . '.php' ) ) {
					include plugin_dir_path( __FILE__ ) . 'layouts/' . sanitize_file_name( $layout ) . '.php';
				}

				array_push( $output_array, $instaFeed );
			}

			if ( $instagram_feed_data['data'][0]['username'] ) {
				$followLink = 'https://www.instagram.com/' . $instagram_feed_data['data'][0]['username'] . '/';
			}
		} else {
			$instaFeed = sprintf(
				'<div><p>%1$s</p></div>',
				esc_attr__( 'Please enter Instagram Token.', 'divi-plus' )
			);
		}

		$output['slides']      = $output_array;
		$output['follow_link'] = esc_url( $followLink );

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

		// Get access token
		$layout      = 'layout-1';
		$accessToken = sanitize_text_field( $this->props['access_token'] );

		self::$rendering = true;

		// Retrieving data form instagram
		$instaFeed    = '';
		$renderScript = '';
		$dotControls  = $navControls = '';
		$followButton = '';
		if ( ! empty( $accessToken ) ) {

			// Get post count
			$postCount = ! empty( $this->props['no_of_posts'] ) ? $this->props['no_of_posts'] : 12;
			$cache     = isset( $this->props['cache'] ) ? $this->props['cache'] : 60;

			$slide_effect      = $this->props['slide_effect'];
			$show_arrow        = $this->props['show_arrow'];
			$show_control_dot  = $this->props['show_control_dot'];
			$control_dot_style = $this->props['control_dot_style'];

			// Get instagram feed
			$instagram_feed_data = self::divi_plus_instagram_feed_callback( $cache, $accessToken, $postCount );

			if ( ! empty( $instagram_feed_data['data'] ) ) {

				wp_enqueue_script( 'elicus-swiper-script' );
				wp_enqueue_style( 'elicus-swiper-style' );
				wp_enqueue_style( 'dipl-swiper-style' );

				// Load css file
				$file = et_is_builder_plugin_active() ? 'style-dbp' : 'style';
				wp_enqueue_style( 'dipl-instagram-carousel-style', PLUGIN_PATH . 'includes/modules/InstagramFeedCarousel/' . $file . '.min.css', array(), '1.0.0' );

				$renderScript = $this->dipl_render_slider_script();

				// this will not work here so just defined as blank
				$multi_view   = et_pb_multi_view_options( $this );
				$link_post    = isset( $this->props['link_post'] ) ? $this->props['link_post'] : '';
				$show_caption = isset( $this->props['show_caption'] ) ? $this->props['show_caption'] : '';

				// Get multi view
				foreach ( array_slice( $instagram_feed_data['data'], 0, $postCount ) as $data ) {

					// Get the data
					$media_type = isset( $data['media_type'] ) ? $data['media_type'] : '';
					$media_url  = ( 'VIDEO' === $media_type ) ? esc_url( $data['thumbnail_url'] ) : ( isset( $data['media_url'] ) ? esc_url( $data['media_url'] ) : '' );

					// If no URL
					if ( '' === $media_url ) {
						continue;
					}

					$instaFeed .= '<div class="swiper-slide">';
					if ( file_exists( get_stylesheet_directory() . '/divi-plus/layouts/instagram-feed-carousel/' . sanitize_file_name( $layout ) . '.php' ) ) {
						include get_stylesheet_directory() . '/divi-plus/layouts/instagram-feed-carousel/' . sanitize_file_name( $layout ) . '.php';
					} elseif ( file_exists( plugin_dir_path( __FILE__ ) . 'layouts/' . sanitize_file_name( $layout ) . '.php' ) ) {
						include plugin_dir_path( __FILE__ ) . 'layouts/' . sanitize_file_name( $layout ) . '.php';
					}
					$instaFeed .= '</div>';
				} // over loop of insta posts

				// Navigation controls
				if ( 'on' === $show_arrow ) {
					$next = sprintf(
						'<div class="swiper-button-next"%1$s></div>',
						'' !== $this->props['next_slide_arrow']
							? sprintf( ' data-next_slide_arrow="%1$s"', esc_attr( et_pb_process_font_icon( $this->props['next_slide_arrow'] ) ) )
						: ''
					);
					$prev = sprintf(
						'<div class="swiper-button-prev"%1$s></div>',
						'' !== $this->props['previous_slide_arrow']
							? sprintf( ' data-previous_slide_arrow="%1$s"', esc_attr( et_pb_process_font_icon( $this->props['previous_slide_arrow'] ) ) )
						: ''
					);

					$arrows_position	= et_pb_responsive_options()->get_property_values( $this->props, 'arrows_position' );
					$arrows_position	= array_filter( $arrows_position );

					if ( ! empty( $arrows_position ) ) {
						wp_enqueue_script( 'dipl-instagram-feed-carousel-custom', PLUGIN_PATH."includes/modules/InstagramFeedCarousel/dipl-instagram-feed-carousel-custom.min.js", array('jquery'), '1.0.0', true );
						$arrows_position_data = '';
						foreach ( $arrows_position as $device => $value ) {
							$arrows_position_data .= ' data-arrows_' . $device . '="' . $value . '"';
						}
					}

					$navControls .= sprintf(
						'<div class="dipl_swiper_navigation" %3$s>%1$s %2$s</div>',
						$next,
						$prev,
						! empty( $arrows_position_data ) ? $arrows_position_data : ''
					);
				}

				// Dot controls
				if ( 'on' === $show_control_dot ) {
					$dotControls .= sprintf(
						'<div class="dipl_swiper_pagination"><div class="swiper-pagination %1$s"></div></div>',
						esc_attr( $control_dot_style )
					);
				}

				// Check follow button
				if ( 'on' === $this->props['show_follow_btn'] && ! empty( $instagram_feed_data['data'][0]['username'] ) ) {
					$btnRender = $this->render_button(
						array(
							'button_text'         => esc_html( $this->props['follow_btn_text'] ),
							'button_text_escaped' => true,
							'button_url'          => esc_url( 'https://www.instagram.com/' . $instagram_feed_data['data'][0]['username'] . '/' ),
							'button_classname'    => array( 'dipl_instagram_follow_element dipl_instagram_follow_button' ),
							'button_custom'       => isset( $this->props['custom_follow_button'] ) ? $this->props['custom_follow_button'] : 'off',
							'custom_icon'         => isset( $this->props['follow_button_icon'] ) ? $this->props['follow_button_icon'] : '',
							'has_wrapper'         => true,
							'url_new_window'      => 'on',
							// 'button_rel'			=> esc_html( $this->props['follow_button_rel'] ),
							'button_rel'          => 'nofollow',
						)
					);

					$followButton = sprintf( '<div class="dipl_instagram_feed_button_wrapper">%1$s</div>', et_core_intentionally_unescaped( $btnRender, 'html' ) );
					if ( ! empty( $this->props['follow_button_icon'] ) ) {
						self::set_style(
							$render_slug,
							array(
								'selector'    => '%%order_class%% .dipl_instagram_follow_button::after',
								'declaration' => 'content: attr(data-icon); top: 50%; transform: translateY(-50%);',
							)
						);
					}
				}

				// Style starts
				$equalize_slides_height = $this->props['equalize_slides_height'];
				if ( 'on' === $equalize_slides_height ) {
					self::set_style(
						$render_slug,
						array(
							'selector'    => '%%order_class%% .swiper-slide',
							'declaration' => 'height: auto;',
						)
					);
					self::set_style(
						$render_slug,
						array(
							'selector'    => '%%order_class%% .dipl_instagram_feed_item',
							'declaration' => 'display: flex; flex-direction: column; height: 100%;',
						)
					);
					self::set_style(
						$render_slug,
						array(
							'selector'    => '%%order_class%% .dipl_instagram_item-caption',
							'declaration' => 'display: flex; flex-direction: column; flex-grow: 1;',
						)
					);
				}

				$enable_coverflow_shadow = $this->props['enable_coverflow_shadow'];
				if ( 'on' === $enable_coverflow_shadow ) {
					self::set_style(
						$render_slug,
						array(
							'selector'    => '%%order_class%% .swiper-container-3d .swiper-slide-shadow-left',
							'declaration' => sprintf( 'background-image: linear-gradient(to left,%1$s,rgba(0,0,0,0)) !important;', esc_attr( $coverflow_shadow_color ) ),
						)
					);
					self::set_style(
						$render_slug,
						array(
							'selector'    => '%%order_class%% .swiper-container-3d .swiper-slide-shadow-right',
							'declaration' => sprintf( 'background-image: linear-gradient(to right,%1$s,rgba(0,0,0,0)) !important;', esc_attr( $coverflow_shadow_color ) ),
						)
					);
				} else {
					self::set_style(
						$render_slug,
						array(
							'selector'    => '%%order_class%% .swiper-container-3d .swiper-slide-shadow-left, %%order_class%% .swiper-container-3d .swiper-slide-shadow-right',
							'declaration' => 'background-image: none !important;',
						)
					);
				}

				if ( 'on' === $show_control_dot ) {
					$control_dot_inactive_color = $this->props['control_dot_inactive_color'];
					if ( $control_dot_inactive_color ) {
						self::set_style(
							$render_slug,
							array(
								'selector'    => '%%order_class%% .dipl_swiper_wrapper > .dipl_swiper_pagination .swiper-pagination-bullet',
								'declaration' => sprintf( 'background: %1$s !important;', esc_attr( $control_dot_inactive_color ) ),
							)
						);
						self::set_style(
							$render_slug,
							array(
								'selector'    => '%%order_class%% .dipl_swiper_wrapper > .dipl_swiper_pagination .transparent_dot .swiper-pagination-bullet',
								'declaration' => sprintf( 'border-color: %1$s;', esc_attr( $control_dot_inactive_color ) ),
							)
						);
					}

					$control_dot_active_color = $this->props['control_dot_active_color'];
					if ( $control_dot_active_color ) {
						self::set_style(
							$render_slug,
							array(
								'selector'    => '%%order_class%% .dipl_swiper_wrapper > .dipl_swiper_pagination .swiper-pagination-bullet.swiper-pagination-bullet-active',
								'declaration' => sprintf( 'background: %1$s !important;', esc_attr( $control_dot_active_color ) ),
							)
						);
					}

					$slide_transition_duration = $this->props['slide_transition_duration'];
					if ( 'stretched_dot' === $control_dot_style && $slide_transition_duration ) {
						self::set_style(
							$render_slug,
							array(
								'selector'    => '%%order_class%% .dipl_swiper_wrapper > .dipl_swiper_pagination .stretched_dot .swiper-pagination-bullet',
								'declaration' => sprintf( 'transition: all %1$sms ease;', intval( $slide_transition_duration ) ),
							)
						);
					}
				}

				$enable_linear_transition = $this->props['enable_linear_transition'];
				if ( 'on' === $enable_linear_transition ) {
					self::set_style(
						$render_slug,
						array(
							'selector'    => '%%order_class%% .swiper-wrapper',
							'declaration' => 'transition-timing-function : linear !important;',
						)
					);
				}

				if ( 'on' === $show_arrow ) {
					$arrow_color = $this->props['arrow_color'];
					if ( $arrow_color ) {
						self::set_style(
							$render_slug,
							array(
								'selector'    => '%%order_class%% .dipl_swiper_navigation .swiper-button-prev, %%order_class%% .dipl_swiper_navigation .swiper-button-next',
								'declaration' => sprintf( 'color: %1$s !important;', esc_attr( $arrow_color ) ),
							)
						);
					}

					$arrow_color_hover = $this->get_hover_value( 'arrow_color' );
					if ( $arrow_color_hover ) {
						self::set_style(
							$render_slug,
							array(
								'selector'    => '%%order_class%% .dipl_swiper_navigation .swiper-button-prev:hover, %%order_class%% .dipl_swiper_navigation .swiper-button-next:hover',
								'declaration' => sprintf( 'color: %1$s !important;', esc_attr( $arrow_color_hover ) ),
							)
						);
					}

					$arrow_font_size = et_pb_responsive_options()->get_property_values( $this->props, 'arrow_font_size' );
					if ( ! empty( array_filter( $arrow_font_size ) ) ) {
						et_pb_responsive_options()->generate_responsive_css( $arrow_font_size, '%%order_class%% .dipl_swiper_navigation .swiper-button-prev, %%order_class%% .dipl_swiper_navigation .swiper-button-next', 'font-size', $render_slug, '!important;', 'range' );
					}

					if ( '' !== $this->props['next_slide_arrow'] ) {
						self::set_style(
							$render_slug,
							array(
								'selector'    => '%%order_class%% .dipl_swiper_navigation .swiper-button-next::after',
								'declaration' => 'display: flex; align-items: center; height: 100%; content: attr(data-next_slide_arrow);',
							)
						);
						if ( class_exists( 'ET_Builder_Module_Helper_Style_Processor' ) && method_exists( 'ET_Builder_Module_Helper_Style_Processor', 'process_extended_icon' ) ) {
							$this->generate_styles(
								array(
									'utility_arg'    => 'icon_font_family',
									'render_slug'    => $render_slug,
									'base_attr_name' => 'next_slide_arrow',
									'important'      => true,
									'selector'       => '%%order_class%% .dipl_swiper_navigation .swiper-button-next::after',
									'processor'      => array(
										'ET_Builder_Module_Helper_Style_Processor',
										'process_extended_icon',
									),
								)
							);
						} else {
							self::set_style(
								$render_slug,
								array(
									'selector'    => '%%order_class%% .dipl_swiper_navigation .swiper-button-next::after',
									'declaration' => 'font-family: "ETmodules";',
								)
							);
						}
					}
					if ( '' !== $this->props['previous_slide_arrow'] ) {
						self::set_style(
							$render_slug,
							array(
								'selector'    => '%%order_class%% .dipl_swiper_navigation .swiper-button-prev::after',
								'declaration' => 'display: flex; align-items: center; height: 100%; content: attr(data-previous_slide_arrow);',
							)
						);
						if ( class_exists( 'ET_Builder_Module_Helper_Style_Processor' ) && method_exists( 'ET_Builder_Module_Helper_Style_Processor', 'process_extended_icon' ) ) {
							$this->generate_styles(
								array(
									'utility_arg'    => 'icon_font_family',
									'render_slug'    => $render_slug,
									'base_attr_name' => 'previous_slide_arrow',
									'important'      => true,
									'selector'       => '%%order_class%% .dipl_swiper_navigation .swiper-button-prev::after',
									'processor'      => array(
										'ET_Builder_Module_Helper_Style_Processor',
										'process_extended_icon',
									),
								)
							);
						} else {
							self::set_style(
								$render_slug,
								array(
									'selector'    => '%%order_class%% .dipl_swiper_navigation .swiper-button-prev::after',
									'declaration' => 'font-family: "ETmodules";',
								)
							);
						}
					}

					$show_arrow_on_hover = $this->props['show_arrow_on_hover'];
					if ( 'on' === $show_arrow_on_hover ) {
						self::set_style(
							$render_slug,
							array(
								'selector'    => '%%order_class%% .dipl_swiper_navigation .swiper-button-prev',
								'declaration' => 'visibility: hidden; opacity: 0; transition: all 300ms ease;',
							)
						);
						self::set_style(
							$render_slug,
							array(
								'selector'    => '%%order_class%% .dipl_swiper_navigation .swiper-button-next',
								'declaration' => 'visibility: hidden; opacity: 0; transition: all 300ms ease;',
							)
						);
						self::set_style(
							$render_slug,
							array(
								'selector'    => '%%order_class%%:hover .dipl_swiper_navigation .swiper-button-prev, %%order_class%%:hover .dipl_swiper_navigation .swiper-button-next',
								'declaration' => 'visibility: visible; opacity: 1;',
							)
						);
						self::set_style(
							$render_slug,
							array(
								'selector'    => '%%order_class%%:hover .dipl_swiper_navigation .swiper-button-prev.swiper-button-disabled, %%order_class%%:hover .dipl_swiper_navigation .swiper-button-next.swiper-button-disabled',
								'declaration' => 'opacity: 0.35;',
							)
						);

						/* Outside Slider */
						self::set_style(
							$render_slug,
							array(
								'selector'    => '%%order_class%% .dipl_arrows_outside .swiper-button-prev',
								'declaration' => 'left: 50px !important;',
							)
						);
						self::set_style(
							$render_slug,
							array(
								'selector'    => '%%order_class%% .dipl_arrows_outside .swiper-button-next',
								'declaration' => 'right: 50px !important;',
							)
						);
						self::set_style(
							$render_slug,
							array(
								'selector'    => '%%order_class%%:hover .dipl_arrows_outside .swiper-button-prev',
								'declaration' => 'left: 0 !important;',
							)
						);
						self::set_style(
							$render_slug,
							array(
								'selector'    => '%%order_class%%:hover .dipl_arrows_outside .swiper-button-next',
								'declaration' => 'right: 0 !important;',
							)
						);
						/* Inside Slider */
						self::set_style(
							$render_slug,
							array(
								'selector'    => '%%order_class%% .dipl_arrows_inside .swiper-button-prev',
								'declaration' => 'left: -50px !important;',
							)
						);
						self::set_style(
							$render_slug,
							array(
								'selector'    => '%%order_class%% .dipl_arrows_inside .swiper-button-next',
								'declaration' => 'right: -50px !important;',
							)
						);
						self::set_style(
							$render_slug,
							array(
								'selector'    => '%%order_class%%:hover .dipl_arrows_inside .swiper-button-prev',
								'declaration' => 'left: 0 !important;',
							)
						);
						self::set_style(
							$render_slug,
							array(
								'selector'    => '%%order_class%%:hover .dipl_arrows_inside .swiper-button-next',
								'declaration' => 'right: 0 !important;',
							)
						);
					}

					$arrow_background_color = $this->props['arrow_background_color'];
					if ( '' !== $arrow_background_color ) {
						self::set_style(
							$render_slug,
							array(
								'selector'    => '%%order_class%% .dipl_swiper_navigation .swiper-button-prev, %%order_class%% .dipl_swiper_navigation .swiper-button-next',
								'declaration' => sprintf( 'background: %1$s !important;', esc_attr( $arrow_background_color ) ),
							)
						);
					}

					$arrow_background_color_hover = $this->get_hover_value( 'arrow_background_color' );
					if ( '' !== $arrow_background_color_hover ) {
						self::set_style(
							$render_slug,
							array(
								'selector'    => '%%order_class%% .dipl_swiper_navigation .swiper-button-prev:hover, %%order_class%% .dipl_swiper_navigation .swiper-button-next:hover',
								'declaration' => sprintf( 'background: %1$s !important;', esc_attr( $arrow_background_color_hover ) ),
							)
						);
					}

					$arrow_background_border_size = $this->get_hover_value( 'arrow_background_border_size' );
					if ( '' !== $arrow_background_border_size ) {
						self::set_style(
							$render_slug,
							array(
								'selector'    => '%%order_class%% .dipl_swiper_navigation .swiper-button-prev, %%order_class%% .dipl_swiper_navigation .swiper-button-next',
								'declaration' => sprintf( 'border-width: %1$s;', esc_attr( $arrow_background_border_size ) ),
							)
						);
					}

					$arrow_background_border_color = $this->props['arrow_background_border_color'];
					if ( '' !== $arrow_background_border_color ) {
						self::set_style(
							$render_slug,
							array(
								'selector'    => '%%order_class%% .dipl_woo_products_carousel_layout > .dipl_swiper_navigation .swiper-button-prev, %%order_class%% .dipl_woo_products_carousel_layout > .dipl_swiper_navigation .swiper-button-next',
								'declaration' => sprintf( 'border-color: %1$s;', esc_attr( $arrow_background_border_color ) ),
							)
						);
					}

					$arrow_background_border_color_hover = $this->get_hover_value( 'arrow_background_border_color' );
					if ( '' !== $arrow_background_border_color_hover ) {
						self::set_style(
							$render_slug,
							array(
								'selector'    => '%%order_class%% .dipl_woo_products_carousel_layout > .dipl_swiper_navigation .swiper-button-prev:hover, %%order_class%% .dipl_woo_products_carousel_layout > .dipl_swiper_navigation .swiper-button-next:hover',
								'declaration' => sprintf( 'border-color: %1$s;', esc_attr( $arrow_background_border_color_hover ) ),
							)
						);
					}

					if ( $this->props['item_bg'] ) {
						self::set_style(
							$render_slug,
							array(
								'selector'    => '%%order_class%% .dipl_instagram_feed_item',
								'declaration' => sprintf(
									'background: %1$s;',
									esc_attr( $this->props['item_bg'] )
								),
							)
						);
					}
				}
			} else {
				$instaFeed = sprintf(
					'<div class="entry"><p>%1$s</p></div>',
					$this->props['no_result_text']
				);
			}
		} else {
			$instaFeed = sprintf(
				'<div class="entry"><p>%1$s</p></div>',
				esc_attr__( 'Please enter Instagram Token.', 'divi-plus' )
			);
		}

		$fields = array( 'slider_margin_padding' );
		DiviPlusHelper::process_advanced_margin_padding_css( $this, $render_slug, $this->margin_padding, $fields );

		$output = sprintf(
			'<div class="dipl_swiper_wrapper dipl-instagram-feed-carousel-wrap">
				<div class="dipl_swiper_inner_wrap dipl-instagram-feed-carousel-layout dipl-carousel-layout-%1$s">
					<div class="swiper-container"><div class="swiper-wrapper">%2$s</div></div>
					%3$s
				</div>
				%4$s
				%5$s
			</div>
			%6$s',
			esc_attr( $layout ),
			et_core_intentionally_unescaped( $instaFeed, 'html' ),
			et_core_intentionally_unescaped( $navControls, 'html' ),
			et_core_intentionally_unescaped( $dotControls, 'html' ),
			et_core_intentionally_unescaped( $followButton, 'html' ),
			$renderScript
		);

		self::$rendering = false;

		return et_core_intentionally_unescaped( $output, 'html' );
	}

	/**
	 * This function dynamically creates script parameters according to the user settings
	 *
	 * @return string
	 * */
	public function dipl_render_slider_script() {

		$order_class            = $this->get_module_order_class( 'dipl_instagram_feed_carousel' );
		$slide_effect           = esc_attr( $this->props['slide_effect'] );
		$show_arrow             = esc_attr( $this->props['show_arrow'] );
		$show_control_dot       = esc_attr( $this->props['show_control_dot'] );
		$autoplay               = esc_attr( $this->props['autoplay'] );
		$autoplay_speed         = intval( $this->props['autoplay_speed'] );
		$pause_on_hover         = esc_attr( $this->props['pause_on_hover'] );
		$dynamic_bullets        = 'on' === $this->props['enable_dynamic_dots'] && in_array( $this->props['control_dot_style'], array( 'solid_dot', 'transparent_dot', 'square_dot' ), true ) ? 'true' : 'false';
		$items_per_slide        = et_pb_responsive_options()->get_property_values( $this->props, 'items_per_slide', '', true );
		$space_between_slides   = et_pb_responsive_options()->get_property_values( $this->props, 'space_between_slides', '', true );
		$slides_per_group       = et_pb_responsive_options()->get_property_values( $this->props, 'slides_per_group', '', true );
		$transition_duration    = intval( $this->props['slide_transition_duration'] );
		$loop                   = esc_attr( $this->props['slider_loop'] );
		$auto_height_slider     = esc_attr( $this->props['auto_height_slider'] );
		$equalize_slides_height = esc_attr( $this->props['equalize_slides_height'] );

		// Some defaults
		$transition_duration = '' !== $transition_duration || 0 !== $transition_duration ? $transition_duration : 1000;
		$loop                = 'on' === $loop ? 'true' : 'false';
		$auto_height_slider  = 'on' === $auto_height_slider ? 'true' : 'false';
		$auto_height_slider  = 'on' === $equalize_slides_height ? 'false' : $auto_height_slider;

		$slidesPerGroup           = 1;
		$slidesPerGroupIpad       = 1;
		$slidesPerGroupMobile     = 1;
		$slidesPerGroupSkip       = 0;
		$slidesPerGroupSkipIpad   = 0;
		$slidesPerGroupSkipMobile = 0;
		if ( in_array( $slide_effect, array( 'slide', 'coverflow' ), true ) ) {

			$items_per_view        = $items_per_slide['desktop'];
			$items_per_view_ipad   = '' !== $items_per_slide['tablet'] ? $items_per_slide['tablet'] : $this->props['items_per_slide_tablet'];
			$items_per_view_mobile = '' !== $items_per_slide['phone'] ? $items_per_slide['phone'] : $this->props['items_per_slide_phone'];

			$items_space_between        = $space_between_slides['desktop'];
			$items_space_between_ipad   = '' !== $space_between_slides['tablet'] ? $space_between_slides['tablet'] : $items_space_between;
			$items_space_between_mobile = '' !== $space_between_slides['phone'] ? $space_between_slides['phone'] : $items_space_between_ipad;

			$slidesPerGroup       = $slides_per_group['desktop'];
			$slidesPerGroupIpad   = '' !== $slides_per_group['tablet'] ? $slides_per_group['tablet'] : $slidesPerGroup;
			$slidesPerGroupMobile = '' !== $slides_per_group['phone'] ? $slides_per_group['phone'] : $slidesPerGroupIpad;

			if ( $items_per_view > $slidesPerGroup && 1 !== $slidesPerGroup ) {
				$slidesPerGroupSkip = $items_per_view - $slidesPerGroup;
			}
			if ( $items_per_view_ipad > $slidesPerGroupIpad && 1 !== $slidesPerGroupIpad ) {
				$slidesPerGroupSkipIpad = $items_per_view_ipad - $slidesPerGroupIpad;
			}
			if ( $items_per_view_mobile > $slidesPerGroupMobile && 1 !== $slidesPerGroupMobile ) {
				$slidesPerGroupSkipMobile = $items_per_view_mobile - $slidesPerGroupMobile;
			}
		} else {

			$items_per_view        = 4;
			$items_per_view_ipad   = 3;
			$items_per_view_mobile = 2;

			$items_space_between        = 0;
			$items_space_between_ipad   = 0;
			$items_space_between_mobile = 0;
		}

		$arrows = 'false';
		if ( 'on' === $show_arrow ) {
			$arrows = "{    
					nextEl: '." . esc_attr( $order_class ) . " .dipl-instagram-feed-carousel-layout > .dipl_swiper_navigation .swiper-button-next',
					prevEl: '." . esc_attr( $order_class ) . " .dipl-instagram-feed-carousel-layout > .dipl_swiper_navigation .swiper-button-prev',
			}";
		}

		$dots = 'false';
		if ( 'on' === $show_control_dot ) {
			$dots = "{
				el: '." . esc_attr( $order_class ) . " .dipl-instagram-feed-carousel-layout + .dipl_swiper_pagination .swiper-pagination',
				dynamicBullets: " . $dynamic_bullets . ',
				clickable: true,
			}';
		}

		$autoplay_speed = '' !== $autoplay_speed || 0 !== $autoplay_speed ? $autoplay_speed : 3000;
		$autoplaySlides = 0;
		if ( 'on' === $autoplay ) {
			if ( 'on' === $pause_on_hover ) {
				$autoplaySlides = '{ delay:' . $autoplay_speed . ', disableOnInteraction: true }';
			} else {
				$autoplaySlides = '{ delay:' . $autoplay_speed . ', disableOnInteraction: false }';
			}
		}

		$cube = 'false';
		if ( 'cube' === $slide_effect ) {
			$cube = '{ shadow: false, slideShadows: false }';
		}

		$coverflow = 'false';
		if ( 'coverflow' === $slide_effect ) {
			$coverflow = '{
				rotate: ' . $coverflow_rotate . ',
				stretch: 0,
				depth: ' . $coverflow_depth . ',
				modifier: 1,
				slideShadows : ' . $enable_coverflow_shadow . '
			}';
		}

		$fade = 'false';
		if ( 'fade' === $slide_effect ) {
			$fade = '{ crossFade: true }';
		}

		$script  = '<script type="text/javascript">';
		$script .= 'jQuery(function($) {';
		$script .= 'var ' . esc_attr( $order_class ) . '_swiper = new Swiper(\'.' . esc_attr( $order_class ) . ' .dipl-instagram-feed-carousel-layout > .swiper-container\', {
			slidesPerView: ' . $items_per_view . ',
			autoplay: ' . $autoplaySlides . ',
			spaceBetween: ' . intval( $items_space_between ) . ',
			slidesPerGroup: ' . $slidesPerGroup . ',
			slidesPerGroupSkip: ' . $slidesPerGroupSkip . ',
			effect: "' . $slide_effect . '",
			cubeEffect: ' . $cube . ',
			coverflowEffect: ' . $coverflow . ',
			fadeEffect: ' . $fade . ',
			speed: ' . $transition_duration . ',
			loop: ' . $loop . ',
			autoHeight: ' . $auto_height_slider . ',
			pagination: ' . $dots . ',
			navigation: ' . $arrows . ',
			grabCursor: \'true\',
			observer: true,
			observeParents: true,
			breakpoints: {
				981: {
					slidesPerView: ' . $items_per_view . ',
					spaceBetween: ' . intval( $items_space_between ) . ',
					slidesPerGroup: ' . $slidesPerGroup . ',
					slidesPerGroupSkip: ' . $slidesPerGroupSkip . ',
				},
				768: {
					slidesPerView: ' . $items_per_view_ipad . ',
					spaceBetween: ' . intval( $items_space_between_ipad ) . ',
					slidesPerGroup: ' . $slidesPerGroupIpad . ',
					slidesPerGroupSkip: ' . $slidesPerGroupSkipIpad . ',
				},
				0: {
					slidesPerView: ' . $items_per_view_mobile . ',
					spaceBetween: ' . intval( $items_space_between_mobile ) . ',
					slidesPerGroup: ' . $slidesPerGroupMobile . ',
					slidesPerGroupSkip: ' . $slidesPerGroupSkipMobile . ',
				}
			},
		});';

		if ( 'on' === $pause_on_hover && 'on' === $autoplay ) {
			$script .= '$(".' . esc_attr( $order_class ) . ' .dipl-instagram-feed-carousel-layout > .swiper-container").on("mouseenter", function(e) {
				if ( typeof ' . esc_attr( $order_class ) . '_swiper.autoplay.stop === "function" ) {
					' . esc_attr( $order_class ) . '_swiper.autoplay.stop();
				}
			});';
			$script .= '$(".' . esc_attr( $order_class ) . ' .dipl-instagram-feed-carousel-layout > .swiper-container").on("mouseleave", function(e) {
				if ( typeof ' . esc_attr( $order_class ) . '_swiper.autoplay.start === "function" ) {
					' . esc_attr( $order_class ) . '_swiper.autoplay.start();
				}
			});';
		}
		if ( 'true' !== $loop ) {
			$script .= esc_attr( $order_class ) . '_swiper.on(\'reachEnd\', function(){
				' . esc_attr( $order_class ) . '_swiper.autoplay = false;
			});';
		}

		// Completed jquery and script tah
		$script .= '});</script>';

		return $script;
	}
}

$plugin_options = get_option( ELICUS_DIVI_PLUS_OPTION );
if ( isset( $plugin_options['dipl-modules'] ) ) {
	$modules = explode( ',', $plugin_options['dipl-modules'] );
	if ( in_array( 'dipl_instagram_feed', $modules ) ) {
		new DIPL_InstagramFeedCarousel();
	}
} else {
	new DIPL_InstagramFeedCarousel();
}
