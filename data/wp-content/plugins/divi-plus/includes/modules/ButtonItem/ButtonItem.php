<?php
/**
 * @author      Elicus <hello@elicus.com>
 * @link        https://www.elicus.com/
 * @copyright   2022 Elicus Technologies Private Limited
 * @version     1.9.6
 */
class DIPL_ButtonItem extends ET_Builder_Module {

    public $slug           = 'dipl_button_item';
    public $type           = 'child';
    public $vb_support     = 'on';
    public $text_shadow    = '';

    protected $module_credits = array(
        'module_uri' => 'https://diviextended.com/product/divi-plus/',
        'author'     => 'Elicus',
        'author_uri' => 'https://elicus.com/',
    );

    public function init() {
        $this->name                         = esc_html__( 'DP Button Item', 'divi-plus' );
        $this->advanced_setting_title_text  = esc_html__( 'Button', 'divi-plus' );
        $this->child_title_var              = 'button_text';
        $this->text_shadow                  = ET_Builder_Module_Fields_Factory::get( 'TextShadow' );
        $this->main_css_element             = '%%order_class%%';
    }

    public function get_settings_modal_toggles() {
        return array(
            'general'  => array(
                'toggles' => array(
                    'main_content'  => esc_html__( 'Button', 'divi-plus' ),
                    'link'          => esc_html__( 'Link', 'divi-plus' ),
                    'background'    => esc_html__( 'Background', 'divi-plus' ),
                ),
            ),
            'advanced'  => array(
                'toggles' => array(
                    'text' => array(
                        'title' => esc_html__( 'Alignment', 'divi-plus' ),
                    ),
                    'secondary_text' => array(
                        'title' => esc_html__( 'Secondary Text', 'divi-plus' ),
                    ),
                    'button' => array(
                        'title' => esc_html__( 'Button', 'divi-plus' ),
                        'sub_toggles'   => array(
                            'text' => array(
                                'name' => 'Text',
                            ),
                            'icon' => array(
                                'name' => 'Icon',
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
                'button' => array(
                    'label'         => esc_html__( 'Button', 'divi-plus' ),
                    'font_size'     => array(
                        'default'        => '18px',
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
                        'main'  => "{$this->main_css_element} .dipl_button_text",
                        'hover' => "{$this->main_css_element} .dipl_button_link:hover .dipl_button_text",
                        'important' => 'all',
                    ),
                    'hide_text_align'       => true,
                    'tab_slug'              => 'advanced',
                    'toggle_slug'           => 'button',
                    'sub_toggle'            => 'text',
                ),
                'secondary_text'    => array(
                    'label'           => esc_html__( 'Secondary Text', 'divi-plus' ),
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
                        'main'  => "{$this->main_css_element} .dipl_button_secondary_text",
                        'hover' => "{$this->main_css_element} .dipl_button_link:hover .dipl_button_secondary_text",
                        'important' => 'all',
                    ),
                    'hide_text_align'   => true,
                    'hide_text_shadow'  => true,
                    'depends_on'        => array( 'button_type' ),
                    'depends_show_if'   => 'conversion',
                    'tab_slug'          => 'advanced',
                    'toggle_slug'       => 'secondary_text',
                ),
            ),
            'button_margin_padding' => array(
                'button' => array(
                    'margin_padding' => array(
                        'css' => array(
                            'padding'   => "{$this->main_css_element} .dipl_button_link",
                            'margin'    => $this->main_css_element,
                            'important' => 'all',
                        ),
                    ),
                ),
            ),
            'borders' => array(
                'default' => array(
                    'css' => array(
                        'main' => array(
                            'border_radii'  => "{$this->main_css_element} .dipl_button_link",
                            'border_styles' => "{$this->main_css_element} .dipl_button_link",
                            'important' => 'all',
                        ),
                        'hover' => array(
                            'border_radii'  => "{$this->main_css_element} .dipl_button_link:hover",
                            'border_styles' => "{$this->main_css_element} .dipl_button_link:hover",
                            'important' => 'all',
                        ),
                    ),
                ),
            ),
            'box_shadow' => array(
                'default' => array(
                    'css' => array(
                        'main' => "{$this->main_css_element} .dipl_button_link",
                        'important' => 'all',
                    ),
                ),
            ),
            'transform' => array(
                'css' => array(
                    'main' => "{$this->main_css_element} .dipl_button_link",
                    'important' => 'all',
                ),
            ),
            'text' => array(
                'css' => array(
                    'text_orientation' => "{$this->main_css_element} .dipl_button_link",
                    'important' => 'all',
                ),
            ),
            'margin_padding'=> false,
            'text_shadow'   => false,
            'link_options'  => false,
            'max_width'     => false,
            'height'        => false,
            'filters'       => false,
            'background'    => false,
        );
    }

    public function get_fields() {
        $fields = array(
            'button_type' => array(
                'label'             => esc_html__( 'Button Type', 'divi-plus' ),
                'type'              => 'select',
                'option_category'   => 'layout',
                'options'           => array(
                    'classic'       => esc_html__( 'Classic', 'divi-plus' ),
                    'conversion'    => esc_html__( 'Conversion', 'divi-plus' ),
                ),
                'default'           => 'classic',
                'default_on_front'  => 'classic',
                'affects'           => array(
                    'secondary_text',
                    'secondary_text_font',
                    'secondary_text_font_size',
                    'secondary_text_letter_spacing',
                    'secondary_text_line_height',
                    'secondary_text_text_color',
                    'secondary_text_text_shadow',
                ),
                'tab_slug'          => 'general',
                'toggle_slug'       => 'main_content',
                'description'       => esc_html__( 'Here you can choose the type of the button you want to use.', 'divi-plus' ),
            ),
            'button_text' => array(
                'label'            => esc_html__( 'Button Text', 'divi-plus' ),
                'type'             => 'text',
                'option_category'  => 'basic_option',
                'dynamic_content'  => 'text',
                'mobile_options'   => true,
                'hover'            => 'tabs',
                'tab_slug'         => 'general',
                'toggle_slug'      => 'main_content',
                'description'      => esc_html__( 'Here you can input the text for the button.', 'divi-plus' ),
            ),
            'secondary_text' => array(
                'label'            => esc_html__( 'Secondary Text', 'divi-plus' ),
                'type'             => 'text',
                'option_category'  => 'basic_option',
                'dynamic_content'  => 'text',
                'mobile_options'   => true,
                'hover'            => 'tabs',
                'show_if'         => array(
                    'button_type' => 'conversion',
                ),
                'tab_slug'         => 'general',
                'toggle_slug'      => 'main_content',
                'description'      => esc_html__( 'Here you can input the secondary text for the button.', 'divi-plus' ),
            ),
            'button_url' => array(
                'label'            => esc_html__( 'Button Link URL', 'divi-plus' ),
                'type'             => 'text',
                'option_category'  => 'basic_option',
                'dynamic_content'  => 'url',
                'tab_slug'         => 'general',
                'toggle_slug'      => 'link',
                'description'      => esc_html__( 'Here you can input the destination URL for the button to open when clicked.', 'divi-plus' ),
            ),
            'url_new_window' => array(
                'label'             => esc_html__( 'Button Link Target', 'divi-plus' ),
                'type'              => 'select',
                'option_category'   => 'configuration',
                'options'           => array(
                    'off' => esc_html__( 'In The Same Window', 'divi-plus' ),
                    'on'  => esc_html__( 'In The New Tab', 'divi-plus' ),
                ),
                'default'           => 'off',
                'default_on_front'  => 'off',
                'tab_slug'          => 'general',
                'toggle_slug'       => 'link',
                'description'       => esc_html__( 'Here you can choose whether or not the button opens the link in a new window.', 'divi-plus' ),
            ),
            'background_fill_style' => array(
                'label'             => esc_html__( 'Background Hover Fill Style', 'divi-plus' ),
                'type'              => 'select',
                'option_category'   => 'layout',
                'options'           => array(
                    'default_fill'              => esc_html__( 'Default', 'divi-plus' ),
                    'wipe_fill'                 => esc_html__( 'Wipe', 'divi-plus' ),
                    'slide_up_fill'             => esc_html__( 'Slide Up', 'divi-plus' ),
                    'slide_down_fill'           => esc_html__( 'Slide Down', 'divi-plus' ),
                    'slide_left_fill'           => esc_html__( 'Slide Left', 'divi-plus' ),
                    'slide_right_fill'          => esc_html__( 'Slide Right', 'divi-plus' ),
                    'vertical_shutter_fill'     => esc_html__( 'Vertical Shutter', 'divi-plus' ),
                    'horizontal_shutter_fill'   => esc_html__( 'Horizontal Shutter', 'divi-plus' ),
                ),
                'default'           => 'default_fill',
                'default_on_front'  => 'default_fill',
                'tab_slug'          => 'general',
                'toggle_slug'       => 'background',
                'description'       => esc_html__( 'Here you can choose the button background animation to be displayed when hovered.', 'divi-plus' ),
            ),
            'button_bg_color' => array(
                'label'                 => esc_html__( 'Button Background', 'divi-plus' ),
                'type'                  => 'background-field',
                'base_name'             => 'button_bg',
                'context'               => 'button_bg_color',
                'option_category'       => 'button',
                'custom_color'          => true,
                'background_fields'     => $this->generate_background_options( 'button_bg', 'button', 'general', 'background', 'button_bg_color' ),
                'hover'                 => 'tabs',
                'mobile_options'        => true,
                'tab_slug'              => 'general',
                'toggle_slug'           => 'background',
                'description'           => esc_html__( 'Customize the background style of the button by adjusting the background color, gradient, and image.', 'divi-plus' ),
            ),
            'button_alignment' => array(
                'label'                 => esc_html__( 'Button Alignment', 'divi-plus' ),
                'type'                  => 'text_align',
                'option_category'       => 'layout',
                'options'               => et_builder_get_text_orientation_options( array( 'justified' ) ),
                'mobile_options'        => true,
                'tab_slug'              => 'advanced',
                'toggle_slug'           => 'text',
                'description'           => esc_html__( 'Here you can choose the alignment of the button in the left, right, or center of the module.', 'divi-plus' ),
            ),
            'button_use_icon' => array(
                'label'                 => esc_html__( 'Use Icon on Button', 'divi-plus' ),
                'type'                  => 'yes_no_button',
                'option_category'       => 'button',
                'options'               => array(
                    'off'   => esc_html__( 'No', 'divi-plus' ),
                    'on'    => esc_html__( 'Yes', 'divi-plus' ),
                ),
                'default'               => 'off',
                'default_on_front'      => 'off',
                'tab_slug'              => 'advanced',
                'toggle_slug'           => 'button',
                'sub_toggle'            => 'icon',
                'description'           => esc_html__( 'Here you can choose whether or not to display a custom icon on the button.', 'divi-plus' ),
            ),
            'button_icon' => array(
                'label'                 => esc_html__( 'Button Icon', 'divi-plus' ),
                'type'                  => 'select_icon',
                'option_category'       => 'button',
                'class'                 => array(
                    'et-pb-font-icon'
                ),
                'show_if'               => array(
                    'button_use_icon' => 'on',
                ),
                'hover'                 => 'tabs',
                'tab_slug'              => 'advanced',
                'toggle_slug'           => 'button',
                'sub_toggle'            => 'icon',
                'description'           => esc_html__( 'Here you can choose an icon to be displayed on the button.', 'divi-plus' ),
            ),
            'button_icon_color' => array(
                'label'                 => esc_html__( 'Button Icon Color', 'divi-plus' ),
                'description'           => esc_html__( 'Here you can define a custom color for the button icon.', 'divi-plus' ),
                'type'                  => 'color-alpha',
                'option_category'       => 'button',
                'custom_color'          => true,
                'show_if'               => array(
                    'button_use_icon' => 'on',
                ),
                'hover'                 => 'tabs',
                'mobile_options'        => true,
                'tab_slug'              => 'advanced',
                'toggle_slug'           => 'button',
                'sub_toggle'            => 'icon',
            ),
            'button_icon_placement' => array(
                'label'                 => esc_html__( 'Button Icon Placement', 'divi-plus' ),
                'type'                  => 'select',
                'option_category'       => 'button',
                'options'               => array(
                    'right' => esc_html__( 'Right', 'divi-plus' ),
                    'left'  => esc_html__( 'Left', 'divi-plus' ),
                ),
                'default'               => 'right',
                'default_on_front'      => 'right',
                'show_if'               => array(
                    'button_use_icon' => 'on',
                ),
                'hover'                 => 'tabs',
                'tab_slug'              => 'advanced',
                'toggle_slug'           => 'button',
                'sub_toggle'            => 'icon',
                'description'           => esc_html__( 'Here you can choose the area where you want to place the button icon within the button.', 'divi-plus' ),
            ),
            'button_on_hover' => array(
                'label'                 => esc_html__( 'Only Show Icon on Hover', 'divi-plus' ),
                'type'                  => 'yes_no_button',
                'option_category'       => 'button',
                'options'               => array(
                    'on'    => esc_html__( 'Yes', 'divi-plus' ),
                    'off'   => esc_html__( 'No', 'divi-plus' ),
                ),
                'default'               => 'on',
                'default_on_front'      => 'on',
                'show_if'               => array(
                    'button_use_icon' => 'on',
                ),
                'tab_slug'              => 'advanced',
                'toggle_slug'           => 'button',
                'sub_toggle'            => 'icon',
                'description'           => esc_html__( 'Here you can choose whether or not to display button icon on hover.', 'divi-plus' ),
            ),
            'button_custom_margin' => array(
                'label'                 => esc_html__( 'Margin', 'divi-plus' ),
                'type'                  => 'custom_margin',
                'option_category'       => 'layout',
                'mobile_options'        => true,
                'hover'                 => 'tabs',
                'tab_slug'              => 'advanced',
                'toggle_slug'           => 'margin_padding',
                'description'           => esc_html__( 'Margin adds extra space to the outside of the element, increasing the distance between the element and other items on the page.', 'divi-plus' ),
            ),
            'button_custom_padding' => array(
                'label'                 => esc_html__( 'Padding', 'divi-plus' ),
                'type'                  => 'custom_padding',
                'option_category'       => 'layout',
                'mobile_options'        => true,
                'hover'                 => 'tabs',
                'tab_slug'              => 'advanced',
                'toggle_slug'           => 'margin_padding',
                'description'           => esc_html__( 'Padding adds extra space to the inside of the element, increasing the distance between the edge of the element and its inner contents.', 'divi-plus' ),
            ),
            'button_id' => array(
                'label'             => esc_html__( 'CSS ID', 'divi-plus' ),
                'type'              => 'text',
                'option_category'   => 'configuration',
                'tab_slug'          => 'custom_css',
                'toggle_slug'       => 'classes',
                'option_class'      => 'et_pb_custom_css_regular',
            ),
            'button_class' => array(
                'label'                 => esc_html__( 'CSS Class', 'divi-plus' ),
                'type'                  => 'text',
                'option_category'       => 'configuration',
                'tab_slug'              => 'custom_css',
                'toggle_slug'           => 'classes',
                'option_class'          => 'et_pb_custom_css_regular',
            ),
        );
        
        $background_options     =  $this->generate_background_options( 'button_bg', 'skip', 'general', 'background', 'button_bg_color' );

        $secondary_text_shadow  = $this->text_shadow->get_fields(array(
            'label'           => 'Secondary',
            'prefix'          => 'secondary_text',
            'option_category' => 'font_option',
            'tab_slug'        => 'advanced',
            'toggle_slug'     => 'secondary_text',
            'show_if'         => array(
                'button_type' => 'conversion',
            ),
        ));

        return array_merge( $fields, $background_options, $secondary_text_shadow );
    }

    public function render( $attrs, $content, $render_slug ) {
        $multi_view             = et_pb_multi_view_options( $this );
        $button_type            = $this->props['button_type'];
        $button_text            = $this->props['button_text'];
        $secondary_text         = $this->props['secondary_text'];
        $button_url             = $this->props['button_url'];
        $url_new_window         = $this->props['url_new_window'];
        $background_fill_style  = $this->props['background_fill_style'];
        $button_use_icon        = $this->props['button_use_icon'];
        $button_icon            = $this->props['button_icon'];
        $button_icon_color      = $this->props['button_icon_color'];
        $button_icon_placement  = $this->props['button_icon_placement'];
        $button_on_hover        = $this->props['button_on_hover'];
        $button_id              = $this->props['button_id'];
        $button_class           = $this->props['button_class'];

        if ( '' === $button_text && 'off' === $button_use_icon ) {
            return '';
        }

        if ( 'on' === $button_use_icon ) {
            $button_icon        = '' === $button_icon ? '5' : $button_icon;
            $button_icon_hover  = $this->get_hover_value( 'button_icon' );
            if ( $button_icon_hover ) {
                $custom_button_icon_hover = sprintf(
                    ' data-icon-hover="%1$s"',
                    esc_attr( et_pb_process_font_icon( $button_icon_hover ) )
                );
            }
            $custom_button_icon = sprintf(
                ' data-icon="%1$s"%2$s',
                esc_attr( et_pb_process_font_icon( $button_icon ) ),
                isset( $custom_button_icon_hover ) ? $custom_button_icon_hover : ''
            );
        }

        $button_inner_wrap = sprintf(
            '<span class="dipl_button_text%3$s"%2$s%4$s>%1$s</span>',
            esc_html( $button_text ),
            isset( $custom_button_icon ) ? et_core_esc_previously( $custom_button_icon ) : '',
            isset( $custom_button_icon ) ? esc_attr( ' dipl_button_icon' ) : '',
            $multi_view->render_attrs(
                array(
                    'content'        => '{{button_text}}',
                    'hover_selector' => "{$this->main_css_element} .dipl_button_link",
                    'visibility'     => array(
                        'button_text' => '__not_empty',
                    ),
                )
            )
        );



        switch ( $button_type ) {
            case 'conversion':
                if ( '' !== $secondary_text ) {
                    $button_inner_wrap .= sprintf(
                        '<span class="dipl_button_secondary_text"%2$s>%1$s</span>',
                        esc_html( $secondary_text ),
                        $multi_view->render_attrs(
                            array(
                                'content'        => '{{secondary_text}}',
                                'hover_selector' => "{$this->main_css_element} .dipl_button_link",
                                'visibility'     => array(
                                    'button_text' => '__not_empty',
                                ),
                            )
                        )
                    );
                }
                break;
                
            default:
                break;
        }

        $button_classes = array(
            'dipl_button_link',
            "dipl_button_{$background_fill_style}",
        );

        if ( '' === $button_text ) {
            array_push( $button_classes, 'dipl_button_no_text' );
        }
        
        if ( 'on' === $button_use_icon && '' !== $button_icon ) {
            if ( 'on' === $button_on_hover && '' !== $button_text ) {
                array_push( $button_classes, 'dipl_button_icon_on_hover' );
            }
            array_push( $button_classes, "dipl_button_icon_{$button_icon_placement}" );
        }

        if ( 'default_fill' !== $background_fill_style ) {
            $background_wrap = '<span class="dipl_background_effect_wrap"></span>';
        }

        if ( '' !== $button_class ) {
            array_push( $button_classes, esc_attr( $button_class ) );
        }

        $button_classes = implode( ' ', $button_classes );

        $button         = sprintf(
            '<a%6$s class="%2$s" href="%3$s" target="%4$s">%1$s%5$s</a>',
            et_core_intentionally_unescaped(  $button_inner_wrap, 'html' ),
            esc_attr( $button_classes ),
            esc_url( $button_url ),
            'on' === $url_new_window ? esc_attr( '_blank' ) : esc_attr( '_self' ),
            'default_fill' !== $background_fill_style ? $background_wrap : '',
            '' !== $button_id ? sprintf( ' id="%1$s"', esc_attr( $button_id ) ) : ''
        );


        $button_wrapper = sprintf(
            '<div class="dipl_button_wrapper %2$s">%1$s</div>',
            et_core_intentionally_unescaped( $button, 'html' ),
            "dipl_button_{$button_type}"
        );

        
        $button_font_size_values = et_pb_responsive_options()->get_property_values( $this->props, 'button_font_size' );
        if ( '' === $button_font_size_values['desktop'] ) {
            $button_font_size_values['desktop'] = '18px';
        }
        if ( empty( array_filter( $button_font_size_values ) ) ) {
            $button_font_size_values['desktop'] = '18px';
        }

        /* Icon Related CSS */
        if ( 'on' === $button_use_icon ) {
            /* Positioning of icon when show icon only on hover */
            $icon_pseudo_selector   = 'left' === $button_icon_placement ? 'before'  : 'after';
            $negation_sign          = 'left' === $button_icon_placement ? '-'       : '';

            if ( 'on' === $button_on_hover && '' !== $button_text ) {
                $placement_selector = "{$this->main_css_element} .dipl_button_icon:{$icon_pseudo_selector}";
                $translate_values   = $this->translate_button_values( $button_font_size_values, $negation_sign );
                $translate_selector = "{$this->main_css_element} .dipl_button_icon";
                et_pb_responsive_options()->generate_responsive_css( $button_font_size_values, $placement_selector, $button_icon_placement, $render_slug, '!important;', 'type' );
                et_pb_responsive_options()->generate_responsive_css( $translate_values, $translate_selector, 'transform', $render_slug, '!important;', 'type' );
                self::set_style( $render_slug, array(
                    'selector'    => $translate_selector,
                    'declaration' => 'transition: color 300ms linear, transform 300ms linear !important'
                ) );
                if ( 'conversion' === $button_type ) {
                    if ( '' !== $secondary_text ) {
                        $orientation_values = et_pb_responsive_options()->get_property_values( $this->props, 'text_orientation' );
                        $translate_values   = $this->translate_secondary_text_values( $orientation_values, $button_font_size_values );
                        if ( is_array( $translate_values ) && ! empty( $translate_values ) ) {
                            $this->process_secondary_text_style( $render_slug, $translate_values );
                        }
                    }
                }
            }

            /* Button Icon Color */
            $button_icon_color_values   = et_pb_responsive_options()->get_property_values( $this->props, 'button_icon_color' );
            $icon_selector              = "{$this->main_css_element} .dipl_button_icon:{$icon_pseudo_selector}";
            et_pb_responsive_options()->generate_responsive_css( $button_icon_color_values, $icon_selector, 'color', $render_slug, '!important;', 'color' );
            $button_icon_color_hover    = $this->get_hover_value( 'button_icon_color' );
            if ( $button_icon_color_hover ) {
                self::set_style( $render_slug, array(
                    'selector'    => "{$this->main_css_element} .dipl_button_link:hover .dipl_button_icon:{$icon_pseudo_selector}",
                    'declaration' => sprintf(
                        'color: %1$s !important;',
                        esc_attr( $button_icon_color_hover )
                    ),
                ) );
            }

            if ( class_exists( 'ET_Builder_Module_Helper_Style_Processor' ) && method_exists( 'ET_Builder_Module_Helper_Style_Processor', 'process_extended_icon' ) ) {
                $this->generate_styles(
                    array(
                        'utility_arg'    => 'icon_font_family',
                        'render_slug'    => $render_slug,
                        'base_attr_name' => 'button_icon',
                        'important'      => true,
                        'selector'       => $icon_selector,
                        'processor'      => array(
                            'ET_Builder_Module_Helper_Style_Processor',
                            'process_extended_icon',
                        ),
                    )
                );
            }

            /* Icon Placement on Hover */
            $button_icon_placement_hover = $this->get_hover_value( 'button_icon_placement' );
            if ( $button_icon_placement_hover ) {
                $placement_selector_hover   = "{$this->main_css_element} .dipl_button_link:not(.dipl_button_no_text):hover .dipl_button_icon:{$icon_pseudo_selector}";
                $translate_selector_hover   = "{$this->main_css_element} .dipl_button_link:not(.dipl_button_no_text):hover .dipl_button_icon";
                $translate_values_hover     = $this->translate_button_values_hover( $button_font_size_values, $negation_sign );
                self::set_style( $render_slug, array(
                    'selector'    => $placement_selector_hover,
                    'declaration' => sprintf(
                        '%1$s: 100%% !important; padding: 0 !important; padding-%1$s: 6px !important;',
                        esc_attr( $button_icon_placement )
                    )
                ) );
                self::set_style( $render_slug, array(
                    'selector'    => "{$this->main_css_element} .dipl_button_link:not(.dipl_button_no_text) .dipl_button_icon:{$icon_pseudo_selector}",
                    'declaration' => sprintf(
                        'transition: %1$s 300ms linear !important',
                        esc_attr( $button_icon_placement )
                    )
                ) );
                self::set_style( $render_slug, array(
                    'selector'    => "{$this->main_css_element} .dipl_button_link:not(.dipl_button_no_text) .dipl_button_icon",
                    'declaration' => 'transition: transform 300ms linear !important'
                ) );
                et_pb_responsive_options()->generate_responsive_css( $translate_values_hover, $translate_selector_hover, 'transform', $render_slug, '!important;', 'type' );
            }

            /* Icon on Hover */
            $button_icon_hover  = $this->get_hover_value( 'button_icon' );
            if ( $button_icon_hover ) {
                $icon_selector_hover = "{$this->main_css_element} .dipl_button_link:hover .dipl_button_icon:{$icon_pseudo_selector}";
                self::set_style( $render_slug, array(
                    'selector'    => $icon_selector_hover,
                    'declaration' => 'animation-name: diplChangeIcon !important; animation-fill-mode: forwards !important; animation-delay: 100ms !important;'
                ) );
            }

        }

        /* Button Alignment */
        $button_alignment_values    = et_pb_responsive_options()->get_property_values( $this->props, 'button_alignment' );
        if ( ! empty( array_filter( $button_alignment_values ) ) ) {
            $alignment_selector     = "{$this->main_css_element} .dipl_button_wrapper";
            et_pb_responsive_options()->generate_responsive_css( $button_alignment_values, $alignment_selector, 'text-align', $render_slug, '!important;', 'type' );
        }

        $args = array(
            'render_slug'   => $render_slug,
            'props'         => $this->props,
            'fields'        => $this->fields_unprocessed,
            'module'        => $this,
            'backgrounds'   => array(
                'button_bg' => array(
                    'normal' => "{$this->main_css_element} .dipl_button_link",
                    'hover' => "{$this->main_css_element} .dipl_button_link:hover .dipl_background_effect_wrap:before",
                ),
            ),
        );

        if ( 'default_fill' === $background_fill_style ) {
            $args['backgrounds']['button_bg']['hover'] = "{$this->main_css_element} .dipl_button_link:hover";
        }

        if ( 'vertical_shutter_fill' === $background_fill_style || 'horizontal_shutter_fill' === $background_fill_style ) {
            $args['backgrounds']['button_bg']['hover'] = "{$this->main_css_element} .dipl_button_link:hover .dipl_background_effect_wrap:before, {$this->main_css_element} .dipl_button_link:hover .dipl_background_effect_wrap:after";
        }

        DiviPlusHelper::process_background( $args );
        $fields = array( 'button_margin_padding' );
        DiviPlusHelper::process_advanced_margin_padding_css( $this, $render_slug, $this->margin_padding, $fields );

        $file = et_is_builder_plugin_active() ? 'style-dbp' : 'style';
        wp_enqueue_style( 'dipl-button-item-style', PLUGIN_PATH . 'includes/modules/ButtonItem/' . $file . '.min.css', array(), '1.0.0' );

        return et_core_intentionally_unescaped( $button_wrapper, 'html' );
    }

    public function translate_button_values( $button_font_size_values = array(), $negation_sign = '' ) {
        if ( empty( $button_font_size_values ) ) {
            return $button_font_size_values;
        }

        $translate_values = $button_font_size_values;

        foreach ( $translate_values as &$value ) {
            if ( '' !== $value ) {
                $unit  = str_replace( floatval( $value ), '', $value );
                $value = ( floatval( $value ) / 2 ) + 3;
                $value = 'translateX(' . $negation_sign . $value . $unit . ')';
            }
        }

        return $translate_values;
    }

    public function translate_button_values_hover( $button_font_size_values = array(), $negation_sign = '' ) {
        if ( empty( $button_font_size_values ) ) {
            return $button_font_size_values;
        }

        $translate_values_hover = $button_font_size_values;

        foreach ( $translate_values_hover as &$value ) {
            if ( '' !== $value ) {
                $unit  = str_replace( floatval( $value ), '', $value );
                $value = floatval( $value ) + 6;
                $value = 'translateX(' . $negation_sign . $value . $unit . ')';
            }
        }

        return $translate_values_hover;
    }

    public function translate_secondary_text_values( $text_orientation_values = array(), $button_font_size_values = array() ) {
        if ( empty( $button_font_size_values ) || empty( $text_orientation_values ) ) {
            return '';
        }

        $translate_values   = array();
        foreach( $text_orientation_values as $key => $value ) {
            if ( 'desktop' === $key && '' !== $value ) {
                $translate_values['orientation'][$key] = $value;
            } else if ( 'desktop' === $key && '' === $value ) {
                $translate_values['orientation'][$key] = 'left';
            }

            if ( 'tablet' === $key && '' !== $value ) {
                $translate_values['orientation'][$key] = $value;
            } else if ( 'tablet' === $key && '' === $value ) {
                $translate_values['orientation'][$key] = $translate_values['orientation']['desktop'];
            }

            if ( 'phone' === $key && '' !== $value ) {
                $translate_values['orientation'][$key] = $value;
            } else if ( 'phone' === $key && '' === $value ) {
                $translate_values['orientation'][$key] = $translate_values['orientation']['tablet'];
            }
        }

        foreach ( $button_font_size_values as $key => $value ) {
            if ( '' !== $value ) {
                $unit  = str_replace( floatval( $value ), '', $value );
                $value = ( floatval( $value ) / 2 ) + 3;
                $value = $value . $unit;
                $translate_values['width'][$key] = $value;
            }
        }

        return $translate_values;
    }

    public function process_secondary_text_style( $render_slug, $translate_values = array() ) {
        if ( empty( $translate_values ) ) {
            return '';
        }
        $orientations   = isset( $translate_values['orientation'] ) ? $translate_values['orientation'] : array();
        $widths         = isset( $translate_values['width'] ) ? $translate_values['width'] : array();
        $devices        = array( 'desktop', 'tablet', 'phone' );
        foreach ( $devices as $device ) {
            if ( isset( $orientations[$device] ) ) {
                if ( 'center' !== $orientations[$device] ) {
                    $left_oriented                      = in_array( $orientations[$device], array( 'left', 'justified' ), true );
                    $secondary_pseudo_selector          = $left_oriented ? 'before' : 'after';
                    $secondary_pseudo_selector_hover    = $left_oriented ? 'after'  : 'before';
                    if ( 'desktop' === $device ) {
                        self::set_style( $render_slug, array(
                            'selector'    => "{$this->main_css_element} .dipl_button_secondary_text:{$secondary_pseudo_selector}",
                            'declaration' => sprintf(
                                'width: %1$s !important;',
                                esc_attr( $widths['desktop'] )
                            ),
                        ) );
                        self::set_style( $render_slug, array(
                            'selector'    => "{$this->main_css_element} .dipl_button_link:hover .dipl_button_secondary_text:{$secondary_pseudo_selector_hover}",
                            'declaration' => sprintf(
                                'width: %1$s !important;',
                                esc_attr( $widths['desktop'] )
                            ),
                        ) );
                        self::set_style( $render_slug, array(
                            'selector'    => "{$this->main_css_element} .dipl_button_link:hover .dipl_button_secondary_text:{$secondary_pseudo_selector}",
                            'declaration' => 'width: 0 !important;',
                        ) );
                    }
                    if ( 'tablet' === $device ) {
                        if ( isset( $widths['tablet'] ) ) {
                            self::set_style( $render_slug, array(
                                'selector'    => "{$this->main_css_element} .dipl_button_secondary_text:{$secondary_pseudo_selector}",
                                'declaration' => sprintf(
                                    'width: %1$s !important;',
                                    esc_attr( $widths['tablet'] )
                                ),
                                'media_query' => ET_Builder_Element::get_media_query( 'max_width_980' ),
                            ) );
                            self::set_style( $render_slug, array(
                                'selector'    => "{$this->main_css_element} .dipl_button_link:hover .dipl_button_secondary_text:{$secondary_pseudo_selector_hover}",
                                'declaration' => sprintf(
                                    'width: %1$s !important;',
                                    esc_attr( $widths['tablet'] )
                                ),
                                'media_query' => ET_Builder_Element::get_media_query( 'max_width_980' ),
                            ) );
                            self::set_style( $render_slug, array(
                                'selector'    => "{$this->main_css_element} .dipl_button_link:hover .dipl_button_secondary_text:{$secondary_pseudo_selector}",
                                'declaration' => 'width: 0 !important;',
                                'media_query' => ET_Builder_Element::get_media_query( 'max_width_980' ),
                            ) );
                        } else if ( $orientations['desktop'] !== $orientations[$device] ) {
                            self::set_style( $render_slug, array(
                                'selector'    => "{$this->main_css_element} .dipl_button_secondary_text:{$secondary_pseudo_selector}",
                                'declaration' => sprintf(
                                    'width: %1$s !important;',
                                    esc_attr( $widths['desktop'] )
                                ),
                                'media_query' => ET_Builder_Element::get_media_query( 'max_width_980' ),
                            ) );
                            self::set_style( $render_slug, array(
                                'selector'    => "{$this->main_css_element} .dipl_button_link:hover .dipl_button_secondary_text:{$secondary_pseudo_selector_hover}",
                                'declaration' => sprintf(
                                    'width: %1$s !important;',
                                    esc_attr( $widths['desktop'] )
                                ),
                                'media_query' => ET_Builder_Element::get_media_query( 'max_width_980' ),
                            ) );
                            self::set_style( $render_slug, array(
                                'selector'    => "{$this->main_css_element} .dipl_button_link:hover .dipl_button_secondary_text:{$secondary_pseudo_selector}",
                                'declaration' => 'width: 0 !important;',
                                'media_query' => ET_Builder_Element::get_media_query( 'max_width_980' ),
                            ) );
                        }
                    }
                    if ( 'phone' === $device ) {
                        if ( isset( $widths['phone'] ) ) {
                            self::set_style( $render_slug, array(
                                'selector'    => "{$this->main_css_element} .dipl_button_secondary_text:{$secondary_pseudo_selector}",
                                'declaration' => sprintf(
                                    'width: %1$s !important;',
                                    esc_attr( $widths['phone'] )
                                ),
                                'media_query' => ET_Builder_Element::get_media_query( 'max_width_767' ),
                            ) );
                            self::set_style( $render_slug, array(
                                'selector'    => "{$this->main_css_element} .dipl_button_link:hover .dipl_button_secondary_text:{$secondary_pseudo_selector_hover}",
                                'declaration' => sprintf(
                                    'width: %1$s !important;',
                                    esc_attr( $widths['phone'] )
                                ),
                                'media_query' => ET_Builder_Element::get_media_query( 'max_width_767' ),
                            ) );
                            self::set_style( $render_slug, array(
                                'selector'    => "{$this->main_css_element} .dipl_button_link:hover .dipl_button_secondary_text:{$secondary_pseudo_selector}",
                                'declaration' => 'width: 0 !important;',
                                'media_query' => ET_Builder_Element::get_media_query( 'max_width_767' ),
                            ) );
                        } else if ( $orientations['tablet'] !== $orientations[$device] ) {
                            if ( isset( $widths['tablet'] ) ) {
                                $width = $widths['tablet'];
                            } else {
                                $width = $widths['desktop'];
                            }
                            self::set_style( $render_slug, array(
                                'selector'    => "{$this->main_css_element} .dipl_button_secondary_text:{$secondary_pseudo_selector}",
                                'declaration' => sprintf(
                                    'width: %1$s !important;',
                                    esc_attr( $width )
                                ),
                                'media_query' => ET_Builder_Element::get_media_query( 'max_width_767' ),
                            ) );
                            self::set_style( $render_slug, array(
                                'selector'    => "{$this->main_css_element} .dipl_button_link:hover .dipl_button_secondary_text:{$secondary_pseudo_selector_hover}",
                                'declaration' => sprintf(
                                    'width: %1$s !important;',
                                    esc_attr( $width )
                                ),
                                'media_query' => ET_Builder_Element::get_media_query( 'max_width_767' ),
                            ) );
                            self::set_style( $render_slug, array(
                                'selector'    => "{$this->main_css_element} .dipl_button_link:hover .dipl_button_secondary_text:{$secondary_pseudo_selector}",
                                'declaration' => 'width: 0 !important;',
                                'media_query' => ET_Builder_Element::get_media_query( 'max_width_767' ),
                            ) );
                        }
                    }
                } else {
                    if ( 'desktop' === $device ) {
                        $media_query = '';
                    } else if ( 'tablet' === $device  ) {
                        $media_query = ET_Builder_Element::get_media_query( 'max_width_980' );
                    } else if ( 'phone' === $device  ) {
                        $media_query = ET_Builder_Element::get_media_query( 'max_width_767' );
                    }
                    $secondory_text_translate_selector  = "{$this->main_css_element} .dipl_button_secondary_text:before, {$this->main_css_element} .dipl_button_secondary_text:after, {$this->main_css_element} .dipl_button_link:hover .dipl_button_secondary_text:before, {$this->main_css_element} .dipl_button_link:hover .dipl_button_secondary_text:after";
                    self::set_style( $render_slug, array(
                        'selector'    => $secondory_text_translate_selector,
                        'declaration' => 'width: 0 !important;',
                        'media_query' => $media_query,
                    ) );
                }
            }
        }
    }

}
$plugin_options = get_option( ELICUS_DIVI_PLUS_OPTION );
if ( isset( $plugin_options['dipl-modules'] ) ) {
    $modules = explode( ',', $plugin_options['dipl-modules'] );
    if ( in_array( 'dipl_button', $modules ) ) {
        new DIPL_ButtonItem();
    }
} else {
    new DIPL_ButtonItem();
}