<?php
class DIPL_List extends ET_Builder_Module {

	public $slug       = 'dipl_list';
	public $child_slug = 'dipl_list_item';
	public $vb_support = 'on';

	protected $module_credits = array(
		'module_uri' => 'https://diviextended.com/product/divi-plus/',
		'author'     => 'Elicus',
		'author_uri' => 'https://elicus.com/',
	);

	public function init() {
		$this->name             = esc_html__( 'DP List', 'divi-plus' );
		$this->child_item_text  = esc_html__( 'List Item', 'divi-plus' );
		$this->main_css_element = '%%order_class%%';
		add_filter( 'et_builder_processed_range_value', array( $this, 'dipl_builder_processed_range_value' ), 10, 3 );
	}

	public function get_settings_modal_toggles() {
		return array(
			'general'  => array(
				'toggles' => array(
					'main_content' => esc_html__( 'Configuration', 'divi-plus' ),
				),
			),
			'advanced' => array(
				'toggles' => array(
					'list'       => array(
						'title' => esc_html__( 'List', 'divi-plus' ),
					),
					'item_name'  => array(
						'title' => esc_html__( 'Item Text', 'divi-plus' ),
					),
					'icon'       => array(
						'title' => esc_html__( 'Icon', 'divi-plus' ),
					),
					'image_icon' => array(
						'title' => esc_html__( 'Image', 'divi-plus' ),
					),
					'divider'    => array(
						'title' => esc_html__( 'Divider', 'divi-plus' ),
					),
				),
			),
		);
	}

	public function get_advanced_fields_config() {
		return array(
			'fonts'          => array(
				'item_name' => array(
					'label'           => esc_html__( 'Item Text', 'divi-plus' ),
					'font_size'       => array(
						'default'        => '14px',
						'range_settings' => array(
							'min'  => '1',
							'max'  => '100',
							'step' => '1',
						),
						'validate_unit'  => true,
					),
					'line_height'     => array(
						'default'        => '1.2',
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
					'hide_text_align' => true,
					'css'             => array(
						'main'      => "{$this->main_css_element} .dipl_list_item .dipl_list-item_text",
						'important' => 'all',
					),
					'tab_slug'        => 'advanced',
					'toggle_slug'     => 'item_name',
				),
			),
			'image_icon'     => array(
				'image_icon' => array(
					'margin_padding'  => array(
						'use_padding' => false,
						'css'         => array(
							'important' => 'all',
						),
					),
					'option_category' => 'layout',
					'tab_slug'        => 'advanced',
					'toggle_slug'     => 'divider',
					'label'           => esc_html__( 'Divider', 'divi-plus' ),
					'css'             => array(
						'padding' => '%%order_class%% .dipl_list_item .dipl_list-divider',
						'margin'  => '%%order_class%% .dipl_list_item .dipl_list-divider',
						'main'    => '%%order_class%% .dipl_list_item .dipl_list-divider',
					),
				),
			),
			'max_width'      => array(
				'extra'   => array(
					'imgage_icon' => array(
						'options'              => array(
							'width' => array(
								'label'          => esc_html__( 'Thumbnail Width', 'divi-plus' ),
								'range_settings' => array(
									'min'  => 1,
									'max'  => 100,
									'step' => 1,
								),
								'hover'          => false,
								'fixed_unit'     => 'px',
								'default_unit'   => 'px',
								'default'        => '38px',
								'default_tablet' => '',
								'default_phone'  => '',
								'tab_slug'       => 'advanced',
								'toggle_slug'    => 'image_icon',
							),
						),
						'use_max_width'        => false,
						'use_module_alignment' => false,
						'css'                  => array(
							'main'      => "{$this->main_css_element} .dipl_list-img_icon",
							'important' => 'all',
						),
					),
				),
				'default' => array(
					'css' => array(
						'main'             => '%%order_class%%',
						'module_alignment' => '%%order_class%%',
					),
				),
			),
			'margin_padding' => array(
				'css'     => array(
					'important' => 'all',
				),
				'default' => array(
					'css' => array(
						'main'      => '%%order_class%%',
						'important' => 'all',
					),
				),
			),
			'text'           => false,
			'text_shadow'    => false,
			'link_options'   => false,
		);
	}

	public function get_fields() {
		$et_accent_color = et_builder_accent_color();
		return array(
			'layout'            => array(
				'label'           => esc_html__( 'Layout', 'divi-plus' ),
				'type'            => 'select',
				'option_category' => 'layout',
				'options'         => array(
					'default' => esc_html__( 'Default', 'divi-plus' ),
					'inline'  => esc_html__( 'Inline', 'divi-plus' ),
				),
				'default'         => 'default',
				'tab_slug'        => 'general',
				'toggle_slug'     => 'main_content',
				'description'     => esc_html__( 'Content entered here will appear inside the module.', 'divi-plus' ),
			),
			'list_align'        => array(
				'label'           => esc_html__( 'Alignment', 'divi-plus' ),
				'type'            => 'align',
				'option_category' => 'layout',
				'options'         => et_builder_get_text_orientation_options( array( 'justified' ) ),
				'mobile_options'  => true,
				'tab_slug'        => 'advanced',
				'toggle_slug'     => 'list',
				'default'         => '',
				'description'     => esc_html__( 'Control the align of the icon by select the alignment options.', 'divi-plus' ),
			),
			'text_indent'       => array(
				'label'            => esc_html__( 'Text Indent', 'divi-plus' ),
				'type'             => 'range',
				'option_category'  => 'font_option',
				'range_settings'   => array(
					'min'  => '0',
					'max'  => '50',
					'step' => '1',
				),
				'default'          => '0',
				'default_on_front' => '0',
				'tab_slug'         => 'advanced',
				'toggle_slug'      => 'item_name',
				'description'      => esc_html__( 'Control the text indent of the list items text by increasing or decreasing it.', 'divi-plus' ),
			),
			'icon_font_size'    => array(
				'label'            => esc_html__( 'Icon Font Size', 'divi-plus' ),
				'type'             => 'range',
				'option_category'  => 'font_option',
				'range_settings'   => array(
					'min'  => '1',
					'max'  => '100',
					'step' => '1',
				),
				'mobile_options'   => true,
				'default'          => '22px',
				'default_on_front' => '22px',
				'tab_slug'         => 'advanced',
				'toggle_slug'      => 'icon',
				'description'      => esc_html__( 'Control the size of the icon by increasing or decreasing the font size.', 'divi-plus' ),
			),
			'icon_width'    => array(
				'label'            => esc_html__( 'Icon Width', 'divi-plus' ),
				'type'             => 'range',
				'option_category'  => 'font_option',
				'range_settings'   => array(
					'min'  => '1',
					'max'  => '100',
					'step' => '1',
				),
				'mobile_options'   => true,
				'default'          => '40px',
				'default_on_front' => '40px',
				'tab_slug'         => 'advanced',
				'toggle_slug'      => 'icon',
				'description'      => esc_html__( 'Control the width of the icon by increasing or decreasing the width.', 'divi-plus' ),
			),
			'icon_color'        => array(
				'label'          => esc_html__( 'Icon Color', 'divi-plus' ),
				'type'           => 'color-alpha',
				'hover'          => 'tabs',
				'mobile_options' => true,
				'tab_slug'       => 'advanced',
				'toggle_slug'    => 'icon',
				'description'    => esc_html__( 'Here you can define a custom color for your icon.', 'divi-plus' ),
			),
			'divider_size'      => array(
				'label'           => esc_html__( 'Divider Size', 'divi-plus' ),
				'type'            => 'range',
				'option_category' => 'font_option',
				'range_settings'  => array(
					'min'  => '1',
					'max'  => '20',
					'step' => '1',
				),
				'default'         => '0',
				'mobile_options'  => true,
				'tab_slug'        => 'advanced',
				'toggle_slug'     => 'divider',
				'description'     => esc_html__( 'Here you can set the divider width.', 'divi-plus' ),
			),
			'divider_style'     => array(
				'label'           => esc_html__( 'Divider Style', 'divi-plus' ),
				'type'            => 'select',
				'option_category' => 'layout',
				'options'         => array(
					'solid'  => esc_html__( 'Solid', 'divi-plus' ),
					'dashed' => esc_html__( 'Dashed', 'divi-plus' ),
					'dotted' => esc_html__( 'Dotted', 'divi-plus' ),
					'double' => esc_html__( 'Double', 'divi-plus' ),
					'groove' => esc_html__( 'Groove', 'divi-plus' ),
					'ridge'  => esc_html__( 'Ridge', 'divi-plus' ),
					'inset'  => esc_html__( 'Inset', 'divi-plus' ),
					'outset' => esc_html__( 'Outset', 'divi-plus' ),
				),
				'default'         => 'solid',
				'tab_slug'        => 'advanced',
				'toggle_slug'     => 'divider',
				'description'     => esc_html__( 'Here you can select the divider style.', 'divi-plus' ),
			),
			'divider_color'     => array(
				'label'       => esc_html__( 'Divider Color', 'divi-plus' ),
				'type'        => 'color-alpha',
				'hover'       => 'tabs',
				'default'     => '#d3d3d3',
				'tab_slug'    => 'advanced',
				'toggle_slug' => 'divider',
				'description' => esc_html__( 'Here you can define a custom color for your divider.', 'divi-plus' ),
			),
			'divider_hide_last' => array(
				'label'            => esc_html__( 'Hide Last Divider?', 'divi-plus' ),
				'type'             => 'yes_no_button',
				'option_category'  => 'basic_option',
				'options'          => array(
					'off' => esc_html__( 'No', 'divi-plus' ),
					'on'  => esc_html__( 'Yes', 'divi-plus' ),
				),
				'description'      => esc_html__( 'Here you can choose whether the last divider hide or show.', 'divi-plus' ),
				'default_on_front' => 'off',
				'tab_slug'         => 'advanced',
				'toggle_slug'      => 'divider',
			),
		);
	}

	public function render( $attrs, $content, $render_slug ) {

		$icon_font_size = et_pb_responsive_options()->get_property_values( $this->props, 'icon_font_size' );
		$icon_width     = et_pb_responsive_options()->get_property_values( $this->props, 'icon_width' );
		$icon_color     = et_pb_responsive_options()->get_property_values( $this->props, 'icon_color' );

		// Get the list align
		$list_align = et_pb_responsive_options()->get_property_values( $this->props, 'list_align' );

		// Change the css
		foreach ( $list_align as $key => $val ) {
			if ( 'right' === $val ) {
				$list_align[ $key ] = 'flex-end';
			}
			if ( 'left' === $val ) {
				$list_align[ $key ] = 'flex-start';
			}
		}
		if ( 'inline' == $this->props['layout'] ) {
			et_pb_responsive_options()->generate_responsive_css( $list_align, '%%order_class%% .dipl_list_layout.dipl_list_inline', 'justify-content', $render_slug, '!important;', 'flex' );
		} else {
			et_pb_responsive_options()->generate_responsive_css( $list_align, '%%order_class%% .dipl_list_layout.dipl_list_default', 'align-items', $render_slug, '!important;', 'flex' );
		}

		et_pb_responsive_options()->generate_responsive_css( $icon_font_size, '%%order_class%% .dipl_list-icon .et-pb-icon', 'font-size', $render_slug, '!important;', 'range' );
		et_pb_responsive_options()->generate_responsive_css( $icon_width, '%%order_class%% .dipl_list-icon .et-pb-icon', 'width', $render_slug, '!important;', 'range' );
		et_pb_responsive_options()->generate_responsive_css( $icon_color, '%%order_class%% .dipl_list-icon .et-pb-icon', 'color', $render_slug, ';', 'color' );

		$divider_size = et_pb_responsive_options()->get_property_values( $this->props, 'divider_size' );
		$borderType   = ( 'inline' === $this->props['layout'] ) ? 'border-right-width' : 'border-top-width';
		et_pb_responsive_options()->generate_responsive_css( $divider_size, '%%order_class%% .dipl_list-divider', $borderType, $render_slug, 'px!important;', 'border' );

		// Divider/Border other CSS
		$divider_style = ! empty( $this->props['divider_style'] ) ? $this->props['divider_style'] : 'solid';
		$divider_color = ! empty( $this->props['divider_color'] ) ? $this->props['divider_color'] : '#d3d3d3';

		self::set_style(
			$render_slug,
			array(
				'selector'    => '%%order_class%% .dipl_list-divider',
				'declaration' => sprintf(
					'border-style: %1$s; border-color: %2$s;',
					esc_attr( $divider_style ),
					esc_attr( $divider_color )
				),
			)
		);

		$text_indent = ! empty( $this->props['text_indent'] ) ? $this->props['text_indent'] : 0;
		if ( $text_indent > 0 ) {
			self::set_style(
				$render_slug,
				array(
					'selector'    => '%%order_class%% .dipl_list-item_text',
					'declaration' => sprintf(
						'text-indent: %1$spx;',
						esc_attr( $text_indent )
					),
				)
			);
		}

		$file = et_is_builder_plugin_active() ? 'style-dbp' : 'style';
		wp_enqueue_style( 'dipl-list-style', PLUGIN_PATH . 'includes/modules/List/' . $file . '.min.css', array(), '1.0.0' );

		$layoutClasses = sprintf( 'dipl_list_layout dipl_list_%1$s', esc_attr( $this->props['layout'] ) );
		if ( 'on' === $this->props['divider_hide_last'] ) {
			$layoutClasses .= ' dipl_hide_last_divider';
		}

		$list_wrapper = sprintf(
			'<div class="dipl_list_wrapper"><div class="%1$s">%2$s</div></div>',
			esc_attr( $layoutClasses ),
			et_core_intentionally_unescaped( $this->content, 'html' )
		);

		return et_core_intentionally_unescaped( $list_wrapper, 'html' );
	}

	public function add_new_child_text() {
		return esc_html__( 'Add New List Item', 'divi-plus' );
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
	if ( in_array( 'dipl_list', $modules ) ) {
		new DIPL_List();
	}
} else {
	new DIPL_List();
}