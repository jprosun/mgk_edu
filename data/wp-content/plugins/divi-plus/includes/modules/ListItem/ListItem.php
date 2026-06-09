<?php
/**
 * @author      Elicus <hello@elicus.com>
 * @link        https://www.elicus.com/
 * @copyright   2024 Elicus Technologies Private Limited
 * @version     1.10.0
 */
class DIPL_ListItem extends ET_Builder_Module {

	public $slug       = 'dipl_list_item';
	public $type       = 'child';
	public $vb_support = 'on';

	protected $module_credits = array(
		'module_uri' => 'https://diviextended.com/product/divi-plus/',
		'author'     => 'Elicus',
		'author_uri' => 'https://elicus.com/',
	);

	public function init() {
		$this->name                        = esc_html__( 'DP List Item', 'divi-plus' );
		$this->advanced_setting_title_text = esc_html__( 'List Item', 'divi-plus' );
		$this->child_title_var             = 'item_name';
		$this->main_css_element            = '.dipl_list %%order_class%%';
	}

	public function get_settings_modal_toggles() {
		return array(
			'general'  => array(
				'toggles' => array(
					'main_content' => array(
						'title'    => esc_html__( 'Content', 'divi-plus' ),
						'priority' => 1,
						'tab'      => 'active',
					),
				),
			),
			'advanced' => array(
				'toggles' => array(
					'item_name' => array(
						'title' => esc_html__( 'Item Text', 'divi-plus' ),
					),
					'icon'      => array(
						'title' => esc_html__( 'Icon', 'divi-plus' ),
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
						'default'        => '',
						'range_settings' => array(
							'min'  => '1',
							'max'  => '100',
							'step' => '1',
						),
						'validate_unit'  => true,
					),
					'line_height'     => array(
						'default'        => '',
						'range_settings' => array(
							'min'  => '0.1',
							'max'  => '10',
							'step' => '0.1',
						),
					),
					'letter_spacing'  => array(
						'default'        => '',
						'range_settings' => array(
							'min'  => '0',
							'max'  => '10',
							'step' => '1',
						),
						'validate_unit'  => true,
					),
					'hide_text_align' => true,
					'css'             => array(
						'main'      => "{$this->main_css_element} .dipl-list-item-wrap .dipl_list-item_text",
						'important' => 'all',
					),
					'tab_slug'        => 'advanced',
					'toggle_slug'     => 'item_name',
				),
			),
			'margin_padding' => array(
				'css'     => array(
					'main'      => '.dipl_list %%order_class%%',
					'important' => 'all',
				),
				'default' => array(
					'css' => array(
						'main'      => '.dipl_list %%order_class%%',
						'important' => 'all',
					),
				),
			),
			'text'           => false,
			'module'         => false,
			'text_shadow'    => false,
		);
	}

	public function get_fields() {
		$et_accent_color = et_builder_accent_color();
		return array(
			'item_name'             => array(
				'label'           => esc_html__( 'Text', 'divi-plus' ),
				// 'type'         => 'tiny_mce',
				'type'            => 'textarea',
				'option_category' => 'basic_option',
				'dynamic_content' => 'text',
				'default'         => esc_html__( 'List Item', 'divi-plus' ),
				'tab_slug'        => 'general',
				'toggle_slug'     => 'main_content',
				'description'     => esc_html__( 'Text entered here will appear inside the list item.', 'divi-plus' ),
			),
			'item_thumbnail_option' => array(
				'label'           => esc_html__( 'Image/Icon as thumbnail', 'divi-plus' ),
				'type'            => 'select',
				'option_category' => 'layout',
				'options'         => array(
					'use_icon'  => esc_html__( 'Use Icon', 'divi-plus' ),
					'use_image' => esc_html__( 'Use Image', 'divi-plus' ),
				),
				'default'         => 'use_icon',
				'tab_slug'        => 'general',
				'toggle_slug'     => 'main_content',
				'description'     => esc_html__( 'Here you can choose either to display Image or Icon as thumbnail.', 'divi-plus' ),
			),
			'item_thumbnail'        => array(
				'label'              => esc_html__( 'Item Thumbnail', 'divi-plus' ),
				'type'               => 'upload',
				'option_category'    => 'basic_option',
				'upload_button_text' => esc_attr__( 'Upload an image', 'divi-plus' ),
				'choose_text'        => esc_attr__( 'Choose an Image', 'divi-plus' ),
				'update_text'        => esc_attr__( 'Set As Image', 'divi-plus' ),
				'show_if'            => array(
					'item_thumbnail_option' => 'use_image',
				),
				'tab_slug'           => 'general',
				'toggle_slug'        => 'main_content',
				'description'        => esc_html__( 'Here you can add an item image.', 'divi-plus' ),
			),
			'item_thumbnail_alt'    => array(
				'label'           => esc_html__( 'Item Thumbnail Alt Text', 'divi-plus' ),
				'type'            => 'text',
				'option_category' => 'basic_option',
				'dynamic_content' => 'text',
				'show_if'         => array(
					'item_thumbnail_option' => 'use_image',
				),
				'tab_slug'        => 'general',
				'toggle_slug'     => 'main_content',
				'description'     => esc_html__( 'Here you can add an item image alt text.', 'divi-plus' ),
			),
			'icon'                  => array(
				'label'           => esc_html__( 'Icon', 'divi-plus' ),
				'type'            => 'select_icon',
				'option_category' => 'basic_option',
				'class'           => array(
					'et-pb-font-icon',
				),
				'default'         => '&#x4e;||divi',
				'show_if'         => array(
					'item_thumbnail_option' => 'use_icon',
				),
				'tab_slug'        => 'general',
				'toggle_slug'     => 'main_content',
				'description'     => esc_html__( 'Choose an icon to display.', 'divi-plus' ),
			),
			'style_icon'            => array(
				'label'           => esc_html__( 'Style Icon', 'divi-plus' ),
				'type'            => 'yes_no_button',
				'option_category' => 'configuration',
				'options'         => array(
					'off' => esc_html__( 'No', 'divi-plus' ),
					'on'  => esc_html__( 'Yes', 'divi-plus' ),
				),
				'default'         => 'off',
				'show_if'         => array(
					'item_thumbnail_option' => 'use_icon',
				),
				'tab_slug'        => 'advanced',
				'toggle_slug'     => 'icon',
				'description'     => esc_html__( 'Here you can choose whether icon set above should display within a shape.', 'divi-plus' ),
			),
			'icon_shape'            => array(
				'label'           => esc_html__( 'Shape', 'divi-plus' ),
				'type'            => 'select',
				'option_category' => 'configuration',
				'options'         => array(
					'use_square'  => esc_html__( 'Square', 'divi-plus' ),
					'use_circle'  => esc_html__( 'Circle', 'divi-plus' ),
					'use_hexagon' => esc_html__( 'Hexagon', 'divi-plus' ),
				),
				'show_if'         => array(
					'item_thumbnail_option' => 'use_icon',
					'style_icon'            => 'on',
				),
				'default'         => 'use_square',
				'tab_slug'        => 'advanced',
				'toggle_slug'     => 'icon',
				'description'     => esc_html__( 'Here you can choose shape.', 'divi-plus' ),
			),
			'shape_color'           => array(
				'label'        => esc_html__( 'Shape Background', 'divi-plus' ),
				'type'         => 'color-alpha',
				'custom_color' => true,
				'show_if'      => array(
					'item_thumbnail_option' => 'use_icon',
					'style_icon'            => 'on',
				),
				'default'      => '#000000',
				'tab_slug'     => 'advanced',
				'toggle_slug'  => 'icon',
				'description'  => esc_html__( 'Here you can define a custom color for the icon shape.', 'divi-plus' ),
			),
			'icon_color'            => array(
				'label'          => esc_html__( 'Icon Color', 'divi-plus' ),
				'type'           => 'color-alpha',
				'hover'          => 'tabs',
				'mobile_options' => true,
				'show_if'        => array(
					'item_thumbnail_option' => 'use_icon',
					'style_icon'            => 'on',
				),
				'tab_slug'       => 'advanced',
				'toggle_slug'    => 'icon',
				'description'    => esc_html__( 'Here you can define a custom color for your icon.', 'divi-plus' ),
			),
			'use_shape_border'      => array(
				'label'           => esc_html__( 'Display Shape Border', 'divi-plus' ),
				'type'            => 'yes_no_button',
				'option_category' => 'layout',
				'options'         => array(
					'off' => esc_html__( 'No', 'divi-plus' ),
					'on'  => esc_html__( 'Yes', 'divi-plus' ),
				),
				'show_if'         => array(
					'item_thumbnail_option' => 'use_icon',
					'style_icon'            => 'on',
				),
				'default'         => 'off',
				'tab_slug'        => 'advanced',
				'toggle_slug'     => 'icon',
				'description'     => esc_html__( 'Here you can choose whether if the icon border should display.', 'divi-plus' ),
			),
			'shape_border_color'    => array(
				'label'            => esc_html__( 'Shape Border Color', 'divi-plus' ),
				'type'             => 'color-alpha',
				'custom_color'     => true,
				'show_if'          => array(
					'item_thumbnail_option' => 'use_icon',
					'style_icon'            => 'on',
					'use_shape_border'      => 'on',
				),
				'default'          => $et_accent_color,
				'default_on_front' => $et_accent_color,
				'tab_slug'         => 'advanced',
				'toggle_slug'      => 'icon',
				'description'      => esc_html__( 'Here you can define a custom color for the icon border.', 'divi-plus' ),
			),
		);
	}

	public function render( $attrs, $content, $render_slug ) {

		$item_thumbnail_option = esc_attr( $this->props['item_thumbnail_option'] );
		$item_thumbnail_alt    = $this->props['item_thumbnail_alt'];

		$multi_view = et_pb_multi_view_options( $this );

		// Default blank
		$item_icon = '';
		if ( 'use_image' === $item_thumbnail_option ) {
			$item_icon = $multi_view->render_element(
				array(
					'tag'      => 'img',
					'attrs'    => array(
						'src'   => '{{item_thumbnail}}',
						'alt'   => esc_attr( $item_thumbnail_alt ),
						'class' => 'dipl_list-img_icon',
					),
					'required' => 'item_thumbnail',
				)
			);
		} elseif ( 'use_icon' === $item_thumbnail_option ) {

			$icon_shape = isset( $this->props['icon_shape'] ) ? $this->props['icon_shape'] : 'square';
			if ( 'on' === $this->props['style_icon'] && ! empty( $this->props['icon'] ) ) {

				$shape_color        = isset( $this->props['shape_color'] ) ? $this->props['shape_color'] : '';
				$use_shape_border   = isset( $this->props['use_shape_border'] ) ? $this->props['use_shape_border'] : '';
				$shape_border_color = isset( $this->props['shape_border_color'] ) ? $this->props['shape_border_color'] : '';

				$icon_style    = '';
				$icon_class    = '';
				$hexagon_start = '';
				$hexagon_end   = '';
				if ( 'use_circle' === $icon_shape ) {
					$icon_class  = ' el-icon-circle';
					$icon_style .= sprintf( ' background-color: %1$s;', esc_attr( $shape_color ) );
					if ( 'on' === $use_shape_border ) {
						$icon_style .= sprintf( ' border-color: %1$s;', esc_attr( $shape_border_color ) );
					}
				} elseif ( 'use_square' === $icon_shape ) {
					$icon_class  = ' el-icon-square';
					$icon_style .= sprintf( ' background-color: %1$s;', esc_attr( $shape_color ) );
					if ( 'on' === $use_shape_border ) {
						$icon_style .= sprintf( ' border-color: %1$s;', esc_attr( $shape_border_color ) );
					}
				} elseif ( 'use_hexagon' === $icon_shape ) {
					$icon_class    = ' el-icon-hexagon';
					$hexagon_style = sprintf( 'background-color: %1$s;', esc_attr( $shape_color ) );
					if ( 'on' === $use_shape_border ) {
						$hexagon_style .= sprintf( ' border-color: %1$s;', esc_attr( $shape_border_color ) );
					}
					$hexagon_start = sprintf( '<div class="hexagon-wrapper"><div class="hex"><div class="hexagon' . ( 'on' === $use_shape_border ? ' et-pb-icon-shape-border' : '' ) . '" style="%1$s">', $hexagon_style );
					$hexagon_end   = '</div></div></div>';
				}

				$item_icon = sprintf(
					$hexagon_start . '<span class="et-pb-icon%2$s%3$s" style="%4$s">%1$s</span>' . $hexagon_end,
					esc_attr( et_pb_process_font_icon( $this->props['icon'] ) ),
					$icon_class,
					( 'on' === $use_shape_border && 'use_hexagon' !== $icon_shape ? ' et-pb-icon-shape-border' : '' ),
					$icon_style
				);
			} else {
				$item_icon = $multi_view->render_element(
					array(
						'content'  => '{{icon}}',
						'attrs'    => array(
							'class' => 'et-pb-icon',
						),
						'required' => 'icon',
					)
				);
			}

			$item_icon = sprintf(
				'<div class="dipl_list-icon use_icon %2$s">%1$s</div>',
				et_core_intentionally_unescaped( $item_icon, 'html' ),
				esc_attr( $icon_shape )
			);

			if ( class_exists( 'ET_Builder_Module_Helper_Style_Processor' ) && method_exists( 'ET_Builder_Module_Helper_Style_Processor', 'process_extended_icon' ) ) {
				$this->generate_styles(
					array(
						'utility_arg'    => 'icon_font_family',
						'render_slug'    => $render_slug,
						'base_attr_name' => 'icon',
						'important'      => true,
						'selector'       => '%%order_class%% .et-pb-icon',
						'processor'      => array(
							'ET_Builder_Module_Helper_Style_Processor',
							'process_extended_icon',
						),
					)
				);
			}

			$icon_color = et_pb_responsive_options()->get_property_values( $this->props, 'icon_color' );
			et_pb_responsive_options()->generate_responsive_css( $icon_color, '%%order_class%% .dipl_list-icon .et-pb-icon', 'color', $render_slug, '!important;', 'color' );
		}

		$item_name = $multi_view->render_element(
			array(
				'tag'      => 'div',
				'content'  => '{{item_name}}',
				'attrs'    => array(
					'class' => 'dipl_list-item_text',
				),
				'required' => 'item_name',
			)
		);

		$item_name = et_pb_prep_code_module_for_wpautop( $item_name );

		$item_inner_cnt = '';

		// Add item icon
		$item_inner_cnt .= et_core_intentionally_unescaped( $item_icon, 'html' );

		// Add item link with name
		if ( ! empty( $this->props['link_option_url'] ) ) {
			$linkTarget      = isset( $this->props['link_option_url_new_window'] ) ? $this->props['link_option_url_new_window'] : '';
			$item_inner_cnt .= sprintf(
				'<a href="%1$s" target="%2$s" class="dipl_list-link">%3$s</a>',
				$this->props['link_option_url'],
				'on' === $linkTarget ? '_blank' : '_self',
				et_core_intentionally_unescaped( $item_name, 'html' )
			);
		} else {
			$item_inner_cnt .= et_core_intentionally_unescaped( $item_name, 'html' );
		}

		// Add item divider
		$item_inner_cnt .= et_core_intentionally_unescaped( '<div class="dipl_list-divider"></div>', 'html' );

		$dipl_list_item_wrap = sprintf(
			'<div class="dipl-list-item-wrap">%1$s</div>',
			et_core_intentionally_unescaped( $item_inner_cnt, 'html' )
		);

		// To remove extra p tags from the tinymce content

		return et_core_intentionally_unescaped( $dipl_list_item_wrap, 'html' );
	}

	/**
	 * Filter multi view value.
	 *
	 * @since 3.27.1
	 *
	 * @see ET_Builder_Module_Helper_MultiViewOptions::filter_value
	 *
	 * @param mixed                                     $raw_value Props raw value.
	 * @param array                                     $args {
	 *                                         Context data.
	 *
	 *     @type string $context      Context param: content, attrs, visibility, classes.
	 *     @type string $name         Module options props name.
	 *     @type string $mode         Current data mode: desktop, hover, tablet, phone.
	 *     @type string $attr_key     Attribute key for attrs context data. Example: src, class, etc.
	 *     @type string $attr_sub_key Attribute sub key that availabe when passing attrs value as array such as styes. Example: padding-top, margin-botton, etc.
	 * }
	 * @param ET_Builder_Module_Helper_MultiViewOptions $multi_view Multiview object instance.
	 *
	 * @return mixed
	 */
	public function multi_view_filter_value( $raw_value, $args, $multi_view ) {
		$name = isset( $args['name'] ) ? $args['name'] : '';
		$mode = isset( $args['mode'] ) ? $args['mode'] : '';

		if ( $raw_value && 'icon' === $name ) {
			$processed_value = html_entity_decode( et_pb_process_font_icon( $raw_value ) );
			if ( '%%1%%' === $raw_value ) {
				$processed_value = '"';
			}

			return $processed_value;
		}

		$fields_need_escape = array(
			'button_text',
		);

		if ( $raw_value && in_array( $name, $fields_need_escape, true ) ) {
			return $this->_esc_attr( $multi_view->get_name_by_mode( $name, $mode ), 'none', $raw_value );
		}

		return $raw_value;
	}
}

$plugin_options = get_option( ELICUS_DIVI_PLUS_OPTION );
if ( isset( $plugin_options['dipl-modules'] ) ) {
	$modules = explode( ',', $plugin_options['dipl-modules'] );
	if ( in_array( 'dipl_list', $modules ) ) {
		new DIPL_ListItem();
	}
} else {
	new DIPL_ListItem();
}