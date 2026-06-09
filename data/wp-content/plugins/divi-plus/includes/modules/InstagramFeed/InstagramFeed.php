<?php
class DIPL_InstagramFeed extends ET_Builder_Module {

	public $slug       = 'dipl_instagram_feed';
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
		$this->name             = esc_html__( 'DP Instagram Feed', 'divi-plus' );
		$this->main_css_element = '%%order_class%%';
	}

	public function get_settings_modal_toggles() {
		return array(
			'general'  => array(
				'toggles' => array(
					'integration' => esc_html__( 'Configuration', 'divi-plus' ),
					'display'     => esc_html__( 'Display', 'divi-plus' ),
				),
			),
			'advanced' => array(
				'toggles' => array(
					'layout'        => array(
						'title' => esc_html__( 'Layout', 'divi-plus' ),
					),
					'caption'       => array(
						'title' => esc_html__( 'Caption', 'divi-plus' ),
					),
					'post_item'     => array(
						'title' => esc_html__( 'Instagram Item', 'divi-plus' ),
					),
					'follow_button' => array(
						'title' => esc_html__( 'Follow Button', 'divi-plus' ),
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
					'label'       => esc_html__( 'Instagram Item', 'divi-plus' ),
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
			'access_token'     => array(
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
					'__instagram_feed',
				),
			),
			'cache'            => array(
				'label'            => esc_html__( 'Cache (In Minutes)', 'divi-plus' ),
				'type'             => 'number',
				'default'          => 60,
				'option_category'  => 'basic_option',
				'tab_slug'         => 'general',
				'toggle_slug'      => 'integration',
				'description'      => esc_html__( 'Enter the cache in minutes, it will get data from the cache instead of feching from database.', 'divi-plus' ),
				'computed_affects' => array(
					'__instagram_feed',
				),
			),
			'no_of_posts'      => array(
				'label'            => esc_html__( 'Number of Posts', 'divi-plus' ),
				'type'             => 'number',
				'default'          => 12,
				'option_category'  => 'basic_option',
				'tab_slug'         => 'general',
				'toggle_slug'      => 'display',
				'description'      => esc_html__( 'The title will display on the header.', 'divi-plus' ),
				'computed_affects' => array(
					'__instagram_feed',
				),
			),
			'no_result_text'   => array(
				'label'           => esc_html__( 'No Result Text', 'divi-plus' ),
				'type'            => 'text',
				'option_category' => 'configuration',
				'default'         => esc_html__( 'No instagram posts found in your account.', 'divi-plus' ),
				'tab_slug'        => 'general',
				'toggle_slug'     => 'display',
				'description'     => esc_html__( 'Here you can define custom no result text.', 'divi-plus' ),
			),
			'link_post'        => array(
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
					'__instagram_feed',
				),
			),
			'show_caption'     => array(
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
					'__instagram_feed',
				),
			),
			'show_follow_btn'  => array(
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
			'follow_btn_text'  => array(
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
			'enable_masonry'   => array(
				'label'            => esc_html__( 'Enable Masonry?', 'divi-plus' ),
				'type'             => 'yes_no_button',
				'option_category'  => 'basic_option',
				'options'          => array(
					'off' => esc_html__( 'No', 'divi-plus' ),
					'on'  => esc_html__( 'Yes', 'divi-plus' ),
				),
				'default'          => 'off',
				'description'      => esc_html__( 'Here you can choose to link the image to instagram or not.', 'divi-plus' ),
				'default_on_front' => 'off',
				'tab_slug'         => 'advanced',
				'toggle_slug'      => 'layout',
				'description'      => esc_html__( 'Select to change the layout as masonry or simple grid.', 'divi-plus' ),
				'computed_affects' => array(
					'__instagram_feed',
				),
			),
			'no_of_columns'    => array(
				'label'            => esc_html__( 'Number of Columns', 'divi-plus' ),
				'type'             => 'select',
				'option_category'  => 'layout',
				'options'          => array(
					'2' => esc_html__( '2', 'divi-plus' ),
					'3' => esc_html__( '3', 'divi-plus' ),
					'4' => esc_html__( '4', 'divi-plus' ),
					'5' => esc_html__( '5', 'divi-plus' ),
					'6' => esc_html__( '6', 'divi-plus' ),
				),
				'default'          => '3',
				'default_on_front' => '3',
				'mobile_options'   => true,
				'tab_slug'         => 'advanced',
				'toggle_slug'      => 'layout',
				'description'      => esc_html__( 'select the number of columns to display for feed data.', 'divi-plus' ),
			),
			'column_spacing'   => array(
				'label'            => esc_html__( 'Column Spacing', 'divi-plus' ),
				'type'             => 'range',
				'option_category'  => 'font_option',
				'range_settings'   => array(
					'min'  => '0',
					'max'  => '100',
					'step' => '1',
				),
				'default'          => '4px',
				'default_on_front' => '4px',
				'fixed_unit'       => 'px',
				'fixed_range'      => true,
				'validate_unit'    => true,
				'mobile_options'   => true,
				'tab_slug'         => 'advanced',
				'toggle_slug'      => 'layout',
				'description'      => esc_html__( 'Control the text indent of the list items text by increasing or decreasing it.', 'divi-plus' ),
			),
			'item_bg'          => array(
				'label'       => esc_html__( 'Item Background Color', 'divi-plus' ),
				'type'        => 'color-alpha',
				'hover'       => 'tabs',
				'default'     => '',
				'tab_slug'    => 'advanced',
				'toggle_slug' => 'post_item',
				'description' => esc_html__( 'Here you can define a custom color for your item background.', 'divi-plus' ),
			),
			'__instagram_feed' => array(
				'type'                => 'computed',
				'computed_callback'   => array( 'DIPL_InstagramFeed', 'get_compute_instagram_feed' ),
				'computed_depends_on' => array(
					'access_token',
					'no_of_posts',
					'cache',
					'link_post',
					'show_caption',
					'enable_masonry',
				),
			),
		);
	}

	private static function divi_plus_instagram_callback( $cache, $instagram_token, $limit ) {

		$instagram_key       = 'divi_plus_instagram_key_' . wp_hash( $instagram_token );
		$instagram_feed_data = get_transient( $instagram_key );

		if ( ! $instagram_feed_data || $limit > $instagram_feed_data['limit'] ) {
			$request_args                 = array(
				'timeout' => 20,
			);
			$instagram_feed               = wp_remote_retrieve_body( wp_remote_get( 'https://graph.instagram.com/me/media?fields=id,media_url,caption,media_type,thumbnail_url,timestamp,username,permalink&limit=' . $limit . '&access_token=' . $instagram_token, $request_args ) );
			$instagram_feed_data          = json_decode( $instagram_feed, true );
			$instagram_feed_data['limit'] = $limit;

			if ( ! empty( $instagram_feed_data['data'] ) ) {
				set_transient( $instagram_key, $instagram_feed_data, $cache );
			}
		}

		return $instagram_feed_data;
	}

	/**
	 * This function return values to react for front end builder.
	 *
	 * @param array arguments to get products data
	 * @return array
	 * */
	public static function get_compute_instagram_feed( $args = array(), $conditional_tags = array(), $current_page = array() ) {
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
			'access_token'   => $insta_access_token,
			'no_of_posts'    => 12,
			'cache'          => 60,
			'link_post'      => 'off',
			'show_caption'   => 'off',
			'enable_masonry' => 'off',
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

		// Select the layout
		$layout = ( 'on' === $enable_masonry ) ? 'masonry' : 'grid';

		// Retrieving data form instagram
		$instaFeed  = '';
		$followLink = '';
		if ( ! empty( $access_token ) ) {

			// Get instagram feed
			$instagram_feed_data = self::divi_plus_instagram_callback( $cache, $access_token, $no_of_posts );
			if ( ! empty( $instagram_feed_data['data'] ) ) {

				// this will not work here so just defined as blank
				$multi_view = '';
				if ( 'masonry' === $layout ) {
					$instaFeed = '<div class="dipl-masonry-instagram-feed-gutter"></div>';
				}

				// Get multi view
				foreach ( array_slice( $instagram_feed_data['data'], 0, $no_of_posts ) as $data ) {

					// Get the data
					$media_type = isset( $data['media_type'] ) ? $data['media_type'] : '';
					$media_url  = ( 'VIDEO' === $media_type ) ? esc_url( $data['thumbnail_url'] ) : ( isset( $data['media_url'] ) ? esc_url( $data['media_url'] ) : '' );

					// If no URL
					if ( '' === $media_url ) {
						continue;
					}

					if ( file_exists( get_stylesheet_directory() . '/divi-plus/layouts/instagram-feed/layout-1.php' ) ) {
						include get_stylesheet_directory() . '/divi-plus/layouts/instagram-feed/layout-1.php';
					} elseif ( file_exists( plugin_dir_path( __FILE__ ) . 'layouts/layout-1.php' ) ) {
						include plugin_dir_path( __FILE__ ) . 'layouts/layout-1.php';
					}
				}

				if ( $instagram_feed_data['data'][0]['username'] ) {
					$followLink = 'https://www.instagram.com/' . $instagram_feed_data['data'][0]['username'] . '/';
				}
			}
		} else {
			$instaFeed = sprintf(
				'<div><p>%1$s</p></div>',
				esc_attr__( 'Please enter Instagram Token.', 'divi-plus' )
			);
		}

		self::$rendering = false;

		$output = array(
			'posts'       => $instaFeed,
			'follow_link' => esc_url( $followLink ),
		);

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
		$accessToken    = sanitize_text_field( $this->props['access_token'] );
		$enable_masonry = ! empty( $this->props['enable_masonry'] ) ? $this->props['enable_masonry'] : 'off';
		$no_of_columns  = ! empty( $this->props['no_of_columns'] ) ? $this->props['no_of_columns'] : 3;

		$layout = ( 'on' === $enable_masonry ) ? 'masonry' : 'grid';

		self::$rendering = true;

		// Retrieving data form instagram
		$instaFeed = $followButton = '';
		if ( ! empty( $accessToken ) ) {

			// Get post count
			$postCount = ! empty( $this->props['no_of_posts'] ) ? $this->props['no_of_posts'] : 12;
			$cache     = isset( $this->props['cache'] ) ? $this->props['cache'] : 60;

			// Get instagram feed
			$instagram_feed_data = self::divi_plus_instagram_callback( $cache, $accessToken, $postCount );

			if ( ! empty( $instagram_feed_data['data'] ) ) {

				$multi_view   = et_pb_multi_view_options( $this );
				$link_post    = isset( $this->props['link_post'] ) ? $this->props['link_post'] : '';
				$show_caption = isset( $this->props['show_caption'] ) ? $this->props['show_caption'] : '';

				foreach ( array_slice( $instagram_feed_data['data'], 0, $postCount ) as $data ) {

					// Get the data
					$media_type = isset( $data['media_type'] ) ? $data['media_type'] : '';
					$media_url  = ( 'VIDEO' === $media_type ) ? esc_url( $data['thumbnail_url'] ) : ( isset( $data['media_url'] ) ? esc_url( $data['media_url'] ) : '' );

					// If no URL
					if ( '' === $media_url ) {
						continue;
					}

					if ( file_exists( get_stylesheet_directory() . '/divi-plus/layouts/instagram-feed/layout-1.php' ) ) {
						include get_stylesheet_directory() . '/divi-plus/layouts/instagram-feed/layout-1.php';
					} elseif ( file_exists( plugin_dir_path( __FILE__ ) . 'layouts/layout-1.php' ) ) {
						include plugin_dir_path( __FILE__ ) . 'layouts/layout-1.php';
					}
				}

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
			} else {
				$instaFeed = sprintf(
					'<div class="entry"><p>%1$s</p></div>',
					esc_html( $this->props['no_result_text'] )
				);
			}
		} else {
			$instaFeed = sprintf(
				'<div class="entry"><p>%1$s</p></div>',
				esc_html__( 'Please enter Instagram Token.', 'divi-plus' )
			);
		}

		$numberOfColumns = et_pb_responsive_options()->get_property_values( $this->props, 'no_of_columns' );
		$column_spacing  = et_pb_responsive_options()->get_property_values( $this->props, 'column_spacing' );

		// Use default main settings for number of columns
		$numberOfColumns['tablet'] = '' !== $numberOfColumns['tablet'] ? $numberOfColumns['tablet'] : $numberOfColumns['desktop'];
		$numberOfColumns['phone']  = '' !== $numberOfColumns['phone'] ? $numberOfColumns['phone'] : $numberOfColumns['tablet'];

		// Use default main settings for column spacing
		$column_spacing['tablet'] = '' !== $column_spacing['tablet'] ? $column_spacing['tablet'] : $column_spacing['desktop'];
		$column_spacing['phone']  = '' !== $column_spacing['phone'] ? $column_spacing['phone'] : $column_spacing['tablet'];

		$breakpoints = array( 'desktop', 'tablet', 'phone' );
		if ( 'masonry' === $layout ) {
			$width = array();
			foreach ( $breakpoints as $breakpoint ) {
				if ( 1 === absint( $numberOfColumns[ $breakpoint ] ) ) {
					$width[ $breakpoint ] = '100%';
				} else {
					$divided_width = 100 / absint( $numberOfColumns[ $breakpoint ] );
					if ( 0.0 !== floatval( $column_spacing[ $breakpoint ] ) ) {
						$gutter               = floatval( ( floatval( $column_spacing[ $breakpoint ] ) * ( absint( $numberOfColumns[ $breakpoint ] ) - 1 ) ) / absint( $numberOfColumns[ $breakpoint ] ) );
						$divided_width        = str_replace( ',', '.', $divided_width );
						$width[ $breakpoint ] = 'calc(' . $divided_width . '% - ' . $gutter . 'px)';
					} else {
						$width[ $breakpoint ] = $divided_width . '%';
					}
				}
			}

			et_pb_responsive_options()->generate_responsive_css( $width, '%%order_class%% .dipl_instagram_feed_item', 'width', $render_slug, '!important;', 'range' );
			et_pb_responsive_options()->generate_responsive_css( $column_spacing, '%%order_class%% .dipl-masonry-wrap .dipl-masonry-instagram-feed-gutter', 'width', $render_slug, '!important;', 'range' );
		} else {

			// Create responsive variables
			$marginWrapper = $marginItem = $widthItem = array();
			foreach ( $breakpoints as $breakpoint ) {
				$marginWrapper[ $breakpoint ] = 'calc(-' . $column_spacing[ $breakpoint ] . '/2)';
				$marginItem[ $breakpoint ]    = 'calc(' . $column_spacing[ $breakpoint ] . '/2)';
				$divided_width = 100 / absint( $numberOfColumns[ $breakpoint ] );
				$widthItem[ $breakpoint ]     = 'calc(' . $divided_width . '% - ' . $column_spacing[ $breakpoint ] . ')';
			}

			et_pb_responsive_options()->generate_responsive_css( $marginWrapper, '%%order_class%% .dipl-grid-wrap', 'margin-left', $render_slug, '!important;', 'range' );
			et_pb_responsive_options()->generate_responsive_css( $marginWrapper, '%%order_class%% .dipl-grid-wrap', 'margin-right', $render_slug, '!important;', 'range' );

			et_pb_responsive_options()->generate_responsive_css( $marginItem, '%%order_class%% .dipl-grid-wrap .dipl_instagram_feed_item', 'margin-left', $render_slug, '!important;', 'range' );
			et_pb_responsive_options()->generate_responsive_css( $marginItem, '%%order_class%% .dipl-grid-wrap .dipl_instagram_feed_item', 'margin-right', $render_slug, '!important;', 'range' );
			et_pb_responsive_options()->generate_responsive_css( $widthItem, '%%order_class%% .dipl-grid-wrap .dipl_instagram_feed_item', 'width', $render_slug, '!important;', 'range' );
		}

		// Set item margin bottom for both layout
		et_pb_responsive_options()->generate_responsive_css( $column_spacing, '%%order_class%% .dipl_instagram_feed_item', 'margin-bottom', $render_slug, '!important;', 'range' );

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

		// Load masonry script
		$masonryGutter = '';
		if ( 'masonry' === $layout ) {
			$masonryGutter = '<div class="dipl-masonry-instagram-feed-gutter"></div>';
			wp_enqueue_script( 'elicus-images-loaded-script' );
			wp_enqueue_script( 'elicus-isotope-script' );
			wp_enqueue_script( 'dipl-instagram-feed-script', PLUGIN_PATH . 'includes/modules/InstagramFeed/instagram-feed.min.js', array( 'jquery' ), '1.0.0', true );
		}

		// Load css file
		$file = et_is_builder_plugin_active() ? 'style-dbp' : 'style';
		wp_enqueue_style( 'dipl-instagram-feed-style', PLUGIN_PATH . 'includes/modules/InstagramFeed/' . $file . '.min.css', array(), '1.0.0' );

		$output = sprintf(
			'<div class="dipl-%1$s-wrap dipl-column-%2$s">%3$s %4$s</div>%5$s',
			esc_attr( $layout ),
			esc_attr( $no_of_columns ),
			et_core_intentionally_unescaped( $masonryGutter, 'html' ),
			et_core_intentionally_unescaped( $instaFeed, 'html' ),
			et_core_intentionally_unescaped( $followButton, 'html' )
		);

		self::$rendering = false;

		return et_core_intentionally_unescaped( $output, 'html' );
	}

}

$plugin_options = get_option( ELICUS_DIVI_PLUS_OPTION );
if ( isset( $plugin_options['dipl-modules'] ) ) {
	$modules = explode( ',', $plugin_options['dipl-modules'] );
	if ( in_array( 'dipl_instagram_feed', $modules ) ) {
		new DIPL_InstagramFeed();
	}
} else {
	new DIPL_InstagramFeed();
}
