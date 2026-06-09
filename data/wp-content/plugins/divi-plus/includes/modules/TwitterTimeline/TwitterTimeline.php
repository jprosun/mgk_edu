<?php
/**
 * @author      Elicus <hello@elicus.com>
 * @link        https://www.elicus.com/
 * @copyright   2023 Elicus Technologies Private Limited
 * @version     1.9.15
 */
class DIPL_TwitterTimeline extends ET_Builder_Module {

	public $slug       = 'dipl_twitter_timeline';
	public $vb_support = 'on';

	protected $module_credits = array(
		'module_uri' => 'https://diviextended.com/product/divi-plus/',
		'author'     => 'Elicus',
		'author_uri' => 'https://elicus.com/',
	);

	public function init() {
		$this->name = esc_html__( 'DP Twitter Timeline', 'divi-plus' );
		$this->main_css_element = '%%order_class%%';
	}

	public function get_settings_modal_toggles() {
		return array(
			'general'  => array(
				'toggles' => array(
					'main_content' => array(
						'title' => esc_html__( 'Configuration', 'divi-plus' ),
					),
					'display' => array(
						'title' => esc_html__( 'Display', 'divi-plus' ),
					),
				),
			),
			'advanced'  => array(
				'toggles' => array(
					'text' => esc_html__( 'Alignment', 'divi-plus' ),
					'fallback_text_settings' => esc_html__( 'Fallback Text', 'divi-plus' ),
					'box_shadow' => esc_html__( 'Box Shadow', 'divi-plus' ),
					'border' => esc_html__( 'Border', 'divi-plus' ),
				),
			),
		);
	}

	public function get_advanced_fields_config() {
		return array(
			'fonts' => array(
                'fallback_text' => array(
                    'label'         => esc_html__( 'Fallback Text', 'divi-plus' ),
                    'font_size'     => array(
                        'default'        => '16px',
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
                    'css'                 => array(
                        'main'  => "{$this->main_css_element} .dipl_twitter_embed_timeline",
                    ),
                    'hide_text_align' => true,
                    'tab_slug' => 'advanced',
                    'toggle_slug' => 'fallback_text_settings',
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
				'default'	=> false,
				'timeline' 	=> array(
					'label_prefix' => 'Timeline',
					'css'          => array(
						'main' => array(
							'border_radii'  => "{$this->main_css_element} .twitter-timeline",
							'border_styles' => "{$this->main_css_element} .twitter-timeline",
						),
						'important' => 'all',
					),
					'tab_slug'     => 'advanced',
					'toggle_slug'  => 'border',
				),
			),
			'box_shadow' => array(
				'default' => false,
				'timeline' => array(
					'label'       => esc_html__( 'Timeline Box Shadow', 'divi-plus' ),
					'css'         => array(
						'main' => "{$this->main_css_element} .twitter-timeline",
						'important' => 'all',
					),
					'tab_slug'    => 'advanced',
					'toggle_slug' => 'box_shadow',
				),
			),
			'background' => false,
			'text' => array(
				'text_orientation' => array(
					'exclude_options' => array( 'justified' ),
				),
				'options' => array(
					'text_orientation' => array(
						'label' => esc_html__( 'Alignment', 'divi-plus' ),
					),
				),
				'css' => array(
					'text_orientation' => $this->main_css_element,
				),
			),
			'text_shadow' => false,
			'filters' => false,
			'link_options'  => false,
		);
	}

	public function get_fields() {

		$twitter_username 	= '';
		$plugin_options		= get_option( ELICUS_DIVI_PLUS_OPTION );
		if ( isset( $plugin_options['dipl-twitter-username'] ) && '' !== $plugin_options['dipl-twitter-username'] ) {
			$twitter_username = sanitize_text_field( $plugin_options['dipl-twitter-username'] );
		}
		
		return array_merge(
			array(
				'twitter_username' => array(
					'label'            	=> esc_html__( 'Twitter Username', 'divi-plus' ),
					'type'             	=> 'text',
					'option_category'  	=> 'basic_option',
					'default' 		   	=> $twitter_username,
					'default_on_front' 	=> $twitter_username,
					'tab_slug'        	=> 'general',
					'toggle_slug'     	=> 'main_content',
					'description'      	=> et_get_safe_localization(
											sprintf(
												'%1$s <a href="%2$s" title="%3$s" target="_blank">%4$s</a> %5$s',
												esc_html__( 'Here you can enter the twitter username without @ for the timeline. If you want to use a single username all over the website and it can be saved in the plugin', 'divi-plus' ),
												esc_url( admin_url( '/options-general.php?page=divi-plus-options&menu=integration&submenu=twitter' ) ),
												esc_html__( 'Divi Plus Settings', 'divi-plus' ),
												esc_html__( 'settings', 'divi-plus' ),
												esc_html__( 'page. Or if you want to use different username everytime then you can enter here.', 'divi-plus' )
											)
										),
				),
				'tweet_limit' => array(
					'label'             => esc_html__( 'Tweet Limit', 'divi-plus' ),
					'type'              => 'range',
					'option_category'  	=> 'layout',
					'range_settings'    => array(
						'min'   => '1',
						'max'   => '20',
						'step'  => '1',
					),
					'unitless'			=> true,
					'fixed_range'       => true,
					'mobile_options'    => false,
					'allow_empty'		=> true,
					'default'			=> '',
					'default_on_front'	=> '',
					'tab_slug'        	=> 'general',
					'toggle_slug'     	=> 'main_content',
					'description'       => esc_html__('Increase or decrease the number of tweets to display in timeline. The height parameter has no effect when a Tweet limit is set.', 'divi-plus'),
				),
				'theme' => array(
					'label'             => esc_html__( 'Theme', 'divi-plus' ),
					'type'              => 'select',
					'option_category'   => 'layout',
					'options'           => array(
						'light'		=> esc_html__( 'Light', 'divi-plus' ),
						'dark'		=> esc_html__( 'Dark', 'divi-plus' ),
					),
					'default'           => 'light',
					'default_on_front'  => 'light',
					'tab_slug'        	=> 'general',
					'toggle_slug'     	=> 'display',
					'description'       => esc_html__('When set to dark, displays Tweet with light text over a dark background.', 'divi-plus'),
				),
				'twitter_chrome' => array(
					'label'           	=> esc_html__( 'Chrome', 'divi-plus' ),
					'type'            	=> 'multiple_checkboxes',
					'option_category' 	=> 'configuration',
					'options'         	=> array(
						'noheader' 		=> esc_html__( 'No Header', 'divi-plus' ),
						'nofooter' 		=> esc_html__( 'No Footer', 'divi-plus' ),
						'noborders' 	=> esc_html__( 'No Borders', 'divi-plus' ),
						'transparent' 	=> esc_html__( 'No Background', 'divi-plus' ),
					),
					'default'         	=> 'off|off|off|off',
					'default_on_front'  => 'off|off|off|off',
					'tab_slug'        	=> 'general',
					'toggle_slug'     	=> 'display',
					'description'     	=> et_get_safe_localization(
											sprintf(
												'%1$s<br/><strong>%2$s</strong>%3$s <a href="%4$s" target="_blank">%5$s</a><br/><strong>%6$s</strong>%7$s<br/><strong>%8$s</strong>%9$s<br/><strong>%10$s</strong>%11$s',
												esc_html__( 'Here you can choose the elements to design the timeline.', 'divi-plus' ),
												esc_html__( 'No Header: ', 'divi-plus' ),
												esc_html__( 'Hides the timeline header. Implementing sites must add their own Twitter attribution, link to the source timeline, and comply with other Twitter', 'divi-plus' ),
												esc_url( 'https://developer.twitter.com/en/developer-terms/display-requirements' ),
												esc_html__( 'display requirements.', 'divi-plus' ),
												esc_html__( 'No Footer: ', 'divi-plus' ),
												esc_html__( 'Hides the timeline footer and Tweet composer link.', 'divi-plus' ),
												esc_html__( 'No Borders: ', 'divi-plus' ),
												esc_html__( 'Removes all borders within the timeline including borders surrounding the timeline area and separating Tweets.', 'divi-plus' ),
												esc_html__( 'No Background: ', 'divi-plus' ),
												esc_html__( 'Removes the timeline\'s background color.', 'divi-plus' )
											)
										),
				),
				'timeline_width' => array(
					'label'             => esc_html__( 'Frame Width', 'divi-plus' ),
					'type'              => 'range',
					'option_category'  	=> 'layout',
					'range_settings'    => array(
						'min'   => '100',
						'max'   => '800',
						'step'  => '1',
					),
					'unitless'			=> true,
					'fixed_range'       => true,
					'mobile_options'    => false,
					'default'           => '350',
					'default_on_front'  => '350',
					'tab_slug'        	=> 'general',
					'toggle_slug'     	=> 'display',
					'description'       => esc_html__('Increase or decrease the timeline max width.', 'divi-plus'),
				),
				'timeline_height' => array(
					'label'             => esc_html__( 'Frame Height', 'divi-plus' ),
					'type'              => 'range',
					'option_category'  	=> 'layout',
					'range_settings'    => array(
						'min'   => '100',
						'max'   => '1000',
						'step'  => '1',
					),
					'unitless'			=> true,
					'fixed_range'       => true,
					'mobile_options'    => false,
					'default'           => '500',
					'default_on_front'  => '500',
					'tab_slug'        	=> 'general',
					'toggle_slug'     	=> 'display',
					'description'       => esc_html__('Increase or decrease the timeline height.', 'divi-plus'),
				),
				'do_not_track' => array(
					'label'           	=> esc_html__( 'Do not track', 'divi-plus' ),
					'type'             	=> 'yes_no_button',
					'option_category'  	=> 'configuration',
					'options'          	=> array(
	                    'off'   => esc_html__( 'Off', 'divi-plus' ),
	                    'on'    => esc_html__( 'On', 'divi-plus' ),
	                ),
	                'default' 			=> 'off',
	                'default_on_front' 	=> 'off',
					'tab_slug'        	=> 'general',
					'toggle_slug'     	=> 'main_content',
					'description'     	=> et_get_safe_localization(
											sprintf(
												'%1$s <a href="%2$s" title="%3$s" target="_blank">%4$s</a> %5$s <a href="%6$s" title="%7$s" target="_blank">%7$s</a>.',
												esc_html__( 'When set to on, the timeline and its embedded page on your site are not used for purposes that include', 'divi-plus' ),
												esc_url( 'https://help.twitter.com/en/using-twitter/tailored-suggestions' ),
												esc_html__( 'Personalized Suggestions', 'divi-plus' ),
												esc_html__( 'personalized suggestions', 'divi-plus' ),
												esc_html__( 'and', 'divi-plus' ),
												esc_url( 'https://help.twitter.com/en/safety-and-security/privacy-controls-for-tailored-ads' ),
												esc_html__( 'Personalized Ads', 'divi-plus' ),
												esc_html__( 'personalized ads', 'divi-plus' )
											)
										),
				),
				'timeline_bg_color' => array(
	                'label'                 => esc_html__( 'Timeline Background', 'divi-plus' ),
	                'type'                  => 'background-field',
	                'base_name'             => 'timeline_bg',
	                'context'               => 'timeline_bg_color',
	                'option_category'       => 'button',
	                'custom_color'          => true,
	                'background_fields'     => $this->generate_background_options( 'timeline_bg', 'button', 'advanced', 'background', 'timeline_bg_color' ),
	                'hover'                 => 'tabs',
	                'mobile_options'        => true,
	                'tab_slug'              => 'general',
	                'toggle_slug'           => 'background',
	                'description'           => esc_html__( 'Customize the background style of the timeline by adjusting the background color, gradient, and image. This only works if \'No Background\'is selected in chrome settings', 'divi-plus' ),
				),
			),
			$this->generate_background_options( 'timeline_bg', 'skip', 'general', 'background', 'timeline_bg_color' )
		);

	}


	public function render( $attrs, $content, $render_slug ) {

		$twitter_username 	= $this->props['twitter_username'];
		$tweet_limit 		= $this->props['tweet_limit'];
		$theme				= $this->props['theme'];
		$twitter_chrome		= $this->props['twitter_chrome'];
		$timeline_width		= $this->props['timeline_width'];
		$timeline_height	= $this->props['timeline_height'];
		$show_replies 		= 1;
		$do_not_track		= 'on' === $this->props['do_not_track'] ? 1 : 0;

		$whitelisted_chrome = array( 'noheader', 'nofooter', 'noborders', 'transparent' );
		$chrome = DiviPlusHelper::process_multiple_checkboxes_value( $twitter_chrome, $whitelisted_chrome );

		if ( '' !== $twitter_username ) {
			//wp_enqueue_script( 'dipl-twitter-timeline-custom', PLUGIN_PATH . 'includes/modules/TwitterTimeline/dipl-twitter-timeline-custom.min.js', array('jquery'), '1.0.0', true );
			$file = et_is_builder_plugin_active() ? 'style-dbp' : 'style';
        	wp_enqueue_style( 'dipl-twitter-timeline-style', PLUGIN_PATH . 'includes/modules/TwitterTimeline/' . $file . '.min.css', array(), '1.0.0' );
			$twitter_timeline = sprintf(
				'<div class="dipl_twitter_embedded_timeline">
					<a class="dipl_twitter_embed_timeline" href="%1$s" data-name="%9$s" data-height="%2$s" data-width="%3$s" data-chrome="%4$s" data-theme="%5$s" data-show-replies="%6$s" data-tweet-limit="%7$s" data-dnt="%8$s" data-aria-polite="assertive" data-source="profile">Tweets by @%9$s</a>
				</div>',
				esc_url( 'https://twitter.com/' . $twitter_username ),
				esc_attr( $timeline_height ),
				esc_attr( $timeline_width ),
				esc_attr( $chrome ),
				esc_attr( $theme ),
				esc_attr( $show_replies ),
				esc_attr( $tweet_limit ),
				esc_attr( $do_not_track ),
				esc_attr( $twitter_username )
			);
		} else {
			$twitter_timeline = '';
		}

		$args = array(
			'render_slug'	=> $render_slug,
			'props'			=> $this->props,
			'fields'		=> $this->fields_unprocessed,
			'module'		=> $this,
			'backgrounds' 	=> array(
				'timeline_bg' => array(
					'normal' => "{$this->main_css_element} .dipl_twitter_embedded_timeline",
					'hover' => "{$this->main_css_element} .dipl_twitter_embedded_timeline:hover",
	 			),
			),
		);

		DiviPlusHelper::process_background( $args );

		return $twitter_timeline;
	}
}
$plugin_options = get_option( ELICUS_DIVI_PLUS_OPTION );
if ( isset( $plugin_options['dipl-modules'] ) ) {
    $modules = explode( ',', $plugin_options['dipl-modules'] );
    if ( in_array( 'dipl_twitter_timeline', $modules ) ) {
        new DIPL_TwitterTimeline();
    }
} else {
    new DIPL_TwitterTimeline();
}
