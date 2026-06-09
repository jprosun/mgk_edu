<?php
/**
 * @author      Elicus <hello@elicus.com>
 * @link        https://www.elicus.com/
 * @copyright   2022 Elicus Technologies Private Limited
 * @version     1.9.6
 */
class DIPL_DoubleColorHeading extends ET_Builder_Module {

	public $slug       = 'dipl_double_color_heading';
	public $vb_support = 'on';

	protected $module_credits = array(
		'module_uri' => 'https://diviextended.com/product/divi-plus/',
		'author'     => 'Elicus',
		'author_uri' => 'https://elicus.com/',
	);

	public function init() {
		$this->name = esc_html__( 'DP Fancy Heading', 'divi-plus' );
		$this->main_css_element = '%%order_class%%';
	}

	public function get_settings_modal_toggles() {
		return array(
			'general'  => array(
				'toggles' => array(
					'main_content' => array(
						'title'    => esc_html__( 'Heading Content', 'divi-plus' ),
						'priority' => 1,
					),
				),
			),
			'advanced' => array(
				'toggles' => array(
					'text_settings'         => array(
						'title'             => esc_html__( 'Text Setting', 'divi-plus' ),
						'priority'          => 1,
						'sub_toggles'       => array(
							'global_settings' => array(
								'name' => 'Global',
							),
							'pre_part_text'   => array(
								'name' => 'Pre',
							),
							'main_part_text'  => array(
								'name' => 'Main',
							),
							'post_part_text'  => array(
								'name' => 'Post',
							),
						),
						'tabbed_subtoggles' => true,
					),
					'text_styling_settings' => array(
						'title'             => esc_html__( 'Text Styling', 'divi-plus' ),
						'priority'          => 2,
						'sub_toggles'       => array(
							'pre_part_styling'  => array(
								'name' => 'Pre',
							),
							'main_part_styling' => array(
								'name' => 'Main',
							),
							'post_part_styling' => array(
								'name' => 'Post',
							),
						),
						'tabbed_subtoggles' => true,
					),
				),
			),
		);
	}

	public function get_advanced_fields_config() {
		return array(
			'fonts'                 => array(
				'global_heading_settings' => array(
					'label'               => esc_html__( 'Global', 'divi-plus' ),
					'css'                 => array(
						'main' => '%%order_class%% h1, %%order_class%% h1 a,
									%%order_class%% h2, %%order_class%% h2 a,
									%%order_class%% h3, %%order_class%% h3 a,
									%%order_class%% h4, %%order_class%% h4 a,
									%%order_class%% h5, %%order_class%% h5 a,
									%%order_class%% h6, %%order_class%% h6 a',
					),
					'header_level'        => array(
						'default' => 'h2',
					),
					'hide_font'           => true,
					'hide_font_size'      => true,
					'hide_font_weight'    => true,
					'hide_font_style'     => true,
					'hide_letter_spacing' => true,
					'hide_line_height'    => true,
					'hide_text_color'     => true,
					'hide_text_shadow'    => true,
					'important'           => 'all',
					'tab_slug'            => 'advanced',
					'toggle_slug'         => 'text_settings',
					'sub_toggle'          => 'global_settings',
				),
				'pre_header'              => array(
					'label'           => esc_html__( 'Pre', 'divi-plus' ),
					'font_size'       => array(
						'default'        => '24px',
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
					'css'             => array(
						'main' => '%%order_class%% h1 .dipl_pre_text, %%order_class%% h1 a .dipl_pre_text,
									%%order_class%% h2 .dipl_pre_text, %%order_class%% h2 a .dipl_pre_text,
									%%order_class%% h3 .dipl_pre_text, %%order_class%% h3 a .dipl_pre_text,
									%%order_class%% h4 .dipl_pre_text, %%order_class%% h4 a .dipl_pre_text,
									%%order_class%% h5 .dipl_pre_text, %%order_class%% h5 a .dipl_pre_text,
									%%order_class%% h6 .dipl_pre_text, %%order_class%% h6 a .dipl_pre_text',
						'text_align' => '%%order_class%% .dipl_text_wrapper .dipl_pre_text_stack',
						'important' => 'all',
					),
					'important'       => 'all',
					'tab_slug'        => 'advanced',
					'toggle_slug'     => 'text_settings',
					'sub_toggle'      => 'pre_part_text',
				),
				'main_header'             => array(
					'label'           => esc_html__( 'Main', 'divi-plus' ),
					'font_size'       => array(
						'default'        => '24px',
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
					'css'             => array(
						'main' => '%%order_class%% h1 .dipl_main_text, %%order_class%% h1 a .dipl_main_text,
									%%order_class%% h2 .dipl_main_text, %%order_class%% h2 a .dipl_main_text,
									%%order_class%% h3 .dipl_main_text, %%order_class%% h3 a .dipl_main_text,
									%%order_class%% h4 .dipl_main_text, %%order_class%% h4 a .dipl_main_text,
									%%order_class%% h5 .dipl_main_text, %%order_class%% h5 a .dipl_main_text,
									%%order_class%% h6 .dipl_main_text, %%order_class%% h6 a .dipl_main_text',
						'text_align' => '%%order_class%% .dipl_text_wrapper .dipl_main_text_stack',
						'important' => 'all',
					),
					'important'       => 'all',
					'tab_slug'        => 'advanced',
					'toggle_slug'     => 'text_settings',
					'sub_toggle'      => 'main_part_text',
				),
				'post_header'             => array(
					'label'           => esc_html__( 'Post', 'divi-plus' ),
					'font_size'       => array(
						'default'        => '24px',
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
					'css'             => array(
						'main' => '%%order_class%% h1 .dipl_post_text, %%order_class%% h1 a .dipl_post_text,
									%%order_class%% h2 .dipl_post_text, %%order_class%% h2 a .dipl_post_text,
									%%order_class%% h3 .dipl_post_text, %%order_class%% h3 a .dipl_post_text,
									%%order_class%% h4 .dipl_post_text, %%order_class%% h4 a .dipl_post_text,
									%%order_class%% h5 .dipl_post_text, %%order_class%% h5 a .dipl_post_text,
									%%order_class%% h6 .dipl_post_text, %%order_class%% h6 a .dipl_post_text',
						'text_align' => '%%order_class%% .dipl_text_wrapper .dipl_post_text_stack',
						'important' => 'all',
					),
					'important'       => 'all',
					'tab_slug'        => 'advanced',
					'toggle_slug'     => 'text_settings',
					'sub_toggle'      => 'post_part_text',
				),
			),
			'custom_margin_padding' => array(
				'css' => array(
					'main'      => '%%order_class%%',
					'important' => 'all',
				),
			),
			'max_width'             => array(
				'css' => array(
					'main'             => '%%order_class%%',
					'module_alignment' => '%%order_class%%',
				),
			),
			'filters'               => false,
			'text'                  => false,
			'borders'               => array(
				'default' => array(
					'css' => array(
						'main' => array(
							'border_styles' => '%%order_class%%',
							'border_radii'  => '%%order_class%%',
						),
					),
				),
			),
			'text_padding' => array(
				'pre_part'  => array(
					'label'          => 'Pre Padding',
					'margin_padding' => array(
						'css' => array(
							'main'      => '%%order_class%% .dipl_pre_text',
							'important' => 'all',
						),
					),
					'tab_slug'       => 'advanced',
					'toggle_slug'    => 'text_styling_settings',
					'sub_toggle'     => 'pre_part_styling',
				),
				'main_part' => array(
					'label'          => 'Main Padding',
					'margin_padding' => array(
						'css' => array(
							'main'      => '%%order_class%% .dipl_main_text',
							'important' => 'all',
						),
					),
					'tab_slug'       => 'advanced',
					'toggle_slug'    => 'text_styling_settings',
					'sub_toggle'     => 'main_part_styling',
				),
				'post_part' => array(
					'label'          => 'Post Padding',
					'margin_padding' => array(
						'css' => array(
							'main'      => '%%order_class%% .dipl_post_text',
							'important' => 'all',
						),
					),
					'tab_slug'       => 'advanced',
					'toggle_slug'    => 'text_styling_settings',
					'sub_toggle'     => 'post_part_styling',
				),
			),
		);
	}

	public function get_fields() {
		$et_accent_color = et_builder_accent_color();

		$dipl_heading_fields = array(
			'heading_pre_part'          => array(
				'label'           => esc_html__( 'Pre Heading', 'divi-plus' ),
				'type'            => 'text',
				'dynamic_content' => 'text',
				'option_category' => 'basic_option',
				'tab_slug'        => 'general',
				'toggle_slug'     => 'main_content',
				'description'     => esc_html__( 'Text Before Heading.', 'divi-plus' ),
			),
			'heading_main_part'         => array(
				'label'           => esc_html__( 'Heading', 'divi-plus' ),
				'type'            => 'text',
				'dynamic_content' => 'text',
				'option_category' => 'basic_option',
				'tab_slug'        => 'general',
				'toggle_slug'     => 'main_content',
				'description'     => esc_html__( 'Heading Text.', 'divi-plus' ),
			),
			'heading_post_part'         => array(
				'label'           => esc_html__( 'Post Heading', 'divi-plus' ),
				'type'            => 'text',
				'dynamic_content' => 'text',
				'option_category' => 'basic_option',
				'tab_slug'        => 'general',
				'toggle_slug'     => 'main_content',
				'description'     => esc_html__( 'Text After Heading.', 'divi-plus' ),
			),
			'display_in_stack'          => array(
				'label'           => esc_html__( 'Display in Stack', 'divi-plus' ),
				'type'            => 'yes_no_button',
				'option_category' => 'configuration',
				'options'         => array(
					'off' => esc_html__( 'No', 'divi-plus' ),
					'on'  => esc_html__( 'Yes', 'divi-plus' ),
				),
				'default'         => 'off',
				'tab_slug'        => 'general',
				'toggle_slug'     => 'main_content',
				'description'     => esc_html__( 'To display heading text in stack enable this setting.', 'divi-plus' ),
			),
			'pre_part_text_background'  => array(
				'label'           => esc_html__( 'Use Background', 'divi-plus' ),
				'type'            => 'yes_no_button',
				'option_category' => 'configuration',
				'options'         => array(
					'off' => esc_html__( 'No', 'divi-plus' ),
					'on'  => esc_html__( 'Yes', 'divi-plus' ),
				),
				'default'         => 'off',
				'tab_slug'        => 'advanced',
				'toggle_slug'     => 'text_styling_settings',
				'sub_toggle'      => 'pre_part_styling',
				'description'     => esc_html__( 'Here you can enable background for pre/post text.', 'divi-plus' ),
			),
			'pre_background_color'      => array(
				'label'             => esc_html__( 'Pre Text Background', 'divi-plus' ),
				'type'              => 'background-field',
				'base_name'         => 'pre_background',
				'context'           => 'pre_background_color',
				'option_category'   => 'button',
				'custom_color'      => true,
				'show_if'           => array(
					'pre_part_text_background' => 'on',
				),
				'background_fields' => $this->generate_background_options( 'pre_background', 'button', 'advanced', 'text_styling_settings', 'pre_background_color' ),
				'mobile_options'    => true,
				'tab_slug'          => 'advanced',
				'toggle_slug'       => 'text_styling_settings',
				'sub_toggle'        => 'pre_part_styling',
				'description'       => esc_html__( 'Adjust the background style of the pre/post part text by customizing the background color, gradient, and image.' ),
			),
			'main_part_text_background' => array(
				'label'           => esc_html__( 'Use Background', 'divi-plus' ),
				'type'            => 'yes_no_button',
				'option_category' => 'configuration',
				'options'         => array(
					'off' => esc_html__( 'No', 'divi-plus' ),
					'on'  => esc_html__( 'Yes', 'divi-plus' ),
				),
				'default'         => 'off',
				'tab_slug'        => 'advanced',
				'toggle_slug'     => 'text_styling_settings',
				'sub_toggle'      => 'main_part_styling',
				'description'     => esc_html__( 'Here you can enable background for main text.', 'divi-plus' ),
			),
			'main_background_color'     => array(
				'label'             => esc_html__( 'Main Text Background', 'divi-plus' ),
				'type'              => 'background-field',
				'base_name'         => 'main_background',
				'context'           => 'main_background_color',
				'option_category'   => 'button',
				'custom_color'      => true,
				'show_if'           => array(
					'main_part_text_background' => 'on',
				),
				'background_fields' => $this->generate_background_options( 'main_background', 'button', 'advanced', 'text_styling_settings', 'main_background_color' ),
				'mobile_options'    => true,
				'tab_slug'          => 'advanced',
				'toggle_slug'       => 'text_styling_settings',
				'sub_toggle'        => 'main_part_styling',
				'description'       => esc_html__( 'Adjust the background style of the main part text by customizing the background color, gradient, and image.' ),
			),
			'post_part_text_background' => array(
				'label'           => esc_html__( 'Use Background', 'divi-plus' ),
				'type'            => 'yes_no_button',
				'option_category' => 'configuration',
				'options'         => array(
					'off' => esc_html__( 'No', 'divi-plus' ),
					'on'  => esc_html__( 'Yes', 'divi-plus' ),
				),
				'default'         => 'off',
				'tab_slug'        => 'advanced',
				'toggle_slug'     => 'text_styling_settings',
				'sub_toggle'      => 'post_part_styling',
				'description'     => esc_html__( 'Here you can enable background for pre/post text.', 'divi-plus' ),
			),
			'post_background_color'     => array(
				'label'             => esc_html__( 'Post Text Background', 'divi-plus' ),
				'type'              => 'background-field',
				'base_name'         => 'post_background',
				'context'           => 'post_background_color',
				'option_category'   => 'button',
				'custom_color'      => true,
				'show_if'           => array(
					'post_part_text_background' => 'on',
				),
				'background_fields' => $this->generate_background_options( 'post_background', 'button', 'advanced', 'text_styling_settings', 'post_background_color' ),
				'mobile_options'    => true,
				'tab_slug'          => 'advanced',
				'toggle_slug'       => 'text_styling_settings',
				'sub_toggle'        => 'post_part_styling',
				'description'       => esc_html__( 'Adjust the background style of the pre/post part text by customizing the background color, gradient, and image.' ),
			),
			'pre_part_custom_padding'   => array(
				'label'           => esc_html__( 'Padding', 'divi-plus' ),
				'type'            => 'custom_padding',
				'option_category' => 'layout',
				'mobile_options'  => true,
				'tab_slug'        => 'advanced',
				'toggle_slug'     => 'text_styling_settings',
				'sub_toggle'      => 'pre_part_styling',
				'description'     => esc_html__( 'Here you can adjust Pre/Post Part padding.', 'divi-plus' ),
			),
			'main_part_custom_padding'  => array(
				'label'           => esc_html__( 'Padding', 'divi-plus' ),
				'type'            => 'custom_padding',
				'option_category' => 'layout',
				'mobile_options'  => true,
				'show_if'         => array(
					'main_part_text_background' => 'on',
				),
				'tab_slug'        => 'advanced',
				'toggle_slug'     => 'text_styling_settings',
				'sub_toggle'      => 'main_part_styling',
				'description'     => esc_html__( 'Here you can adjust Main Part padding.', 'divi-plus' ),
			),
			'post_part_custom_padding'  => array(
				'label'           => esc_html__( 'Padding', 'divi-plus' ),
				'type'            => 'custom_padding',
				'option_category' => 'layout',
				'mobile_options'  => true,
				'show_if'         => array(
					'post_part_text_background' => 'on',
				),
				'tab_slug'        => 'advanced',
				'toggle_slug'     => 'text_styling_settings',
				'sub_toggle'      => 'post_part_styling',
				'description'     => esc_html__( 'Here you can adjust Pre/Post Part padding.', 'divi-plus' ),
			),
		);
		$dipl_heading_fields = array_merge( $dipl_heading_fields, $this->generate_background_options( 'pre_background', 'skip', 'advanced', 'text_styling_settings', 'pre_background_color' ) );
		$dipl_heading_fields = array_merge( $dipl_heading_fields, $this->generate_background_options( 'main_background', 'skip', 'advanced', 'text_styling_settings', 'main_background_color' ) );
		$dipl_heading_fields = array_merge( $dipl_heading_fields, $this->generate_background_options( 'post_background', 'skip', 'advanced', 'text_styling_settings', 'post_background_color' ) );

		return $dipl_heading_fields;
	}

	public function render( $attrs, $content, $render_slug ) {
		$heading_pre_part          = $this->props['heading_pre_part'];
		$heading_main_part         = $this->props['heading_main_part'];
		$heading_post_part         = $this->props['heading_post_part'];
		$display_in_stack          = $this->props['display_in_stack'];
		$header_level              = $this->props['global_heading_settings_level'];
		$processed_header_level    = et_pb_process_header_level( $header_level, 'h2' );
		$pre_part_text_background  = $this->props['pre_part_text_background'];
		$main_part_text_background = $this->props['main_part_text_background'];
		$post_part_text_background = $this->props['post_part_text_background'];
		$pre_part                  = '';
		$main_part                 = '';
		$post_part                 = '';

		if ( '' !== $heading_pre_part && 'off' === $display_in_stack ) {
			$pre_part = sprintf(
				'<span class="dipl_pre_text">%1$s</span>',
				esc_html( trim( $heading_pre_part ) )
			);
		}

		if ( '' !== $heading_pre_part && 'on' === $display_in_stack ) {
			$pre_part = sprintf(
				'<span class="dipl_text_stack dipl_pre_text_stack"><span class="dipl_pre_text">%1$s</span></span>',
				esc_html( trim( $heading_pre_part ) )
			);
		}

		if ( '' !== $heading_main_part && 'off' === $display_in_stack ) {
			$main_part = sprintf(
				'<span class="dipl_main_text">%1$s%2$s%3$s</span>',
				( '' !== $heading_pre_part && 'off' === $display_in_stack ) ? '&nbsp;' : '',
				esc_html( trim( $heading_main_part ) ),
				( '' !== $heading_post_part && 'off' === $display_in_stack ) ? '&nbsp;' : ''
			);
		}

		if ( '' !== $heading_main_part && 'on' === $display_in_stack ) {
			$main_part = sprintf(
				'<span class="dipl_text_stack dipl_main_text_stack"><span class="dipl_main_text">%1$s</span></span>',
				esc_html( trim( $heading_main_part ) )
			);
		}

		if ( '' !== $heading_post_part && 'off' === $display_in_stack ) {
			$post_part = sprintf(
				'<span class="dipl_post_text">%1$s</span>',
				esc_html( trim( $heading_post_part ) )
			);
		}

		if ( '' !== $heading_post_part && 'on' === $display_in_stack ) {
			$post_part = sprintf(
				'<span class="dipl_text_stack dipl_post_text_stack"><span class="dipl_post_text">%1$s</span></span>',
				esc_html( trim( $heading_post_part ) )
			);
		}

		$this->add_classname(
			array(
				$this->get_text_orientation_classname(),
			)
		);

		$args = array(
			'render_slug'	=> $render_slug,
			'props'			=> $this->props,
			'fields'		=> $this->fields_unprocessed,
			'module'		=> $this,
		);

		if ( 'on' === $pre_part_text_background ) {
			$args['backgrounds']['pre_background']['normal'] = "{$this->main_css_element} .dipl_pre_text";
			$args['backgrounds']['pre_background']['hover']	= "{$this->main_css_element} .dipl_pre_text:hover";
		}

		if ( 'on' === $main_part_text_background ) {
			$args['backgrounds']['main_background']['normal'] = "{$this->main_css_element} .dipl_main_text";
			$args['backgrounds']['main_background']['hover'] = "{$this->main_css_element} .dipl_main_text:hover";
		}

		if ( 'on' === $post_part_text_background ) {
			$args['backgrounds']['post_background']['normal'] = "{$this->main_css_element} .dipl_post_text";
			$args['backgrounds']['post_background']['hover'] = "{$this->main_css_element} .dipl_post_text:hover";
		}

		DiviPlusHelper::process_background( $args );
		$fields = array( 'text_padding' );
		DiviPlusHelper::process_advanced_margin_padding_css( $this, $render_slug, $this->margin_padding, $fields );

		$output = sprintf(
			'<div class="dipl_text_wrapper"><%1$s>%2$s%3$s%4$s</%1$s></div>',
			$processed_header_level,
			$pre_part,
			$main_part,
			$post_part
		);

		$file = et_is_builder_plugin_active() ? 'style-dbp' : 'style';
        wp_enqueue_style( 'dipl-fancy-heading-style', PLUGIN_PATH . 'includes/modules/DoubleColorHeading/' . $file . '.min.css', array(), '1.0.0' );

		return et_core_intentionally_unescaped( $output, 'html' );
	}
}
$plugin_options = get_option( ELICUS_DIVI_PLUS_OPTION );
if ( isset( $plugin_options['dipl-modules'] ) ) {
	$modules = explode( ',', $plugin_options['dipl-modules'] );
	if ( in_array( 'dipl_double_color_heading', $modules ) ) {
		new DIPL_DoubleColorHeading();
	}
} else {
	new DIPL_DoubleColorHeading();
}
