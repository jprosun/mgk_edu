<?php
/**
 * @author      Elicus <hello@elicus.com>
 * @link        https://www.elicus.com/
 * @copyright   2022 Elicus Technologies Private Limited
 * @version     1.9.6
 */
class DIPL_BarCounter extends ET_Builder_Module {

    public $slug       = 'dipl_bar_counter';
    public $vb_support = 'on';

    protected $module_credits = array(
        'module_uri' => 'https://diviextended.com/product/divi-plus/',
        'author'     => 'Elicus',
        'author_uri' => 'https://elicus.com/',
    );

    public function init() {
        $this->name             = esc_html__( 'DP Bar Counter', 'divi-plus' );
        $this->main_css_element = '%%order_class%%';
    }

    public function get_settings_modal_toggles() {
        return array(
            'general'  => array(
                'toggles' => array(
                    'main_content' => esc_html__( 'Configuration', 'divi-plus' ),
                ),
            ),
            'advanced'  => array(
                'toggles' => array(
                    'text' => array(
                        'title' => esc_html__( 'Text', 'divi-plus' ),
                        'sub_toggles'   => array(
                            'title_text' => array(
                                'name' => 'Title',
                            ),
                            'percent_text' => array(
                                'name' => 'Percent',
                            ),
                        ),
                        'tabbed_subtoggles' => true,
                    ),
                ),
            ),
        );
    }

    public function get_advanced_fields_config() {
        return array (
            'fonts' => array(
                'title' => array(
                    'label' => esc_html__( 'Title', 'divi-plus' ),
                    'font_size' => array(
                        'default'        => '18px',
                        'range_settings' => array(
                            'min'  => '1',
                            'max'  => '100',
                            'step' => '1',
                        ),
                        'validate_unit'  => true,
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
                        'validate_unit'  => true,
                    ),
                    'header_level'   => array(
                        'default' => 'h4',
                    ),
                    'hide_text_align' => true,
                    'css' => array(
                        'main' => "{$this->main_css_element} .dipl_bar_counter_title",
                        'important' => 'all',
                    ),
                    'tab_slug'      => 'advanced',
                    'toggle_slug'   => 'text',
                    'sub_toggle'    => 'title_text',
                ),
                'percent' => array(
                    'label' => esc_html__( 'Percent', 'divi-plus' ),
                    'font_size' => array(
                        'default'        => '16px',
                        'range_settings' => array(
                            'min'  => '1',
                            'max'  => '100',
                            'step' => '1',
                        ),
                        'validate_unit'  => true,
                    ),
                    'line_height' => array(
                        'default'        => '1.4em',
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
                        'default' => 'right',
                    ),
                    'css'            => array(
                        'main'  => "{$this->main_css_element} .dipl_bar_counter_percent",
                        'text_align' => "{$this->main_css_element} .layout1 .dipl_bar_counter_filled_bar_wrapper",
                        'important' => 'all',
                    ),
                    'tab_slug'      => 'advanced',
                    'toggle_slug'   => 'text',
                    'sub_toggle'    => 'percent_text',
                ),
            ),
            'borders' => array(
                'bar' => array(
                    'label_prefix' => esc_html__( 'Bar/Chunks', 'divi-plus' ),
                    'css'          => array(
                        'main' => array(
                            'border_radii'  => "{$this->main_css_element} .dipl_bar_counter_bar, {$this->main_css_element} .dipl_bar_counter_chunks",
                            'border_styles' => "{$this->main_css_element} .dipl_bar_counter_bar, {$this->main_css_element} .dipl_bar_counter_chunks",
                        ),
                        'important' => 'all',
                    ),
                    'tab_slug'     => 'advanced',
                    'toggle_slug'  => 'border',
                ),
                'filled_bar' => array(
                    'label_prefix' => esc_html__( 'Filled Bar/Chunks', 'divi-plus' ),
                    'css'          => array(
                        'main' => array(
                            'border_radii'  => "{$this->main_css_element} .dipl_bar_counter_filled_bar, {$this->main_css_element} .dipl_bar_counter_filled_chunks",
                            'border_styles' => "{$this->main_css_element} .dipl_bar_counter_filled_bar, {$this->main_css_element} .dipl_bar_counter_filled_chunks",
                        ),
                        'important' => 'all',
                    ),
                    'tab_slug'     => 'advanced',
                    'toggle_slug'  => 'border',
                ),
                'default' => array(
                    'css' => array(
                        'main' => array(
                            'border_radii'  => $this->main_css_element,
                            'border_styles' => $this->main_css_element,
                        ),
                        'important' => 'all',
                    ),
                ),
            ),
            'box_shadow' => array(
                'default' => array(
                    'css' => array(
                        'main' => $this->main_css_element,
                        'important' => 'all',
                    ),
                ),
            ),
            'max_width' => array(
                'extra' => array(
                    'chunks' => array(
                        'options' => array(
                            'width' => array(
                                'label'             => esc_html__( 'Chunks Width', 'divi-plus' ),
                                'range_settings'    => array(
                                    'min'  => 1,
                                    'max'  => 100,
                                    'step' => 1,
                                ),
                                'hover'             => false,
                                'default_unit'      => 'px',
                                'default_tablet'    => '',
                                'default_phone'     => '',
                                'show_if'           => array(
                                    'enable_custom_chunks_size' => 'on'
                                ),
                                'tab_slug'          => 'general',
                                'toggle_slug'       => 'main_content',
                            ),
                        ),
                        'use_max_width'        => false,
                        'use_module_alignment' => false,
                        'css'                  => array(
                            'main' => "{$this->main_css_element} .dipl_bar_counter_chunks",
                            'important' => 'all',
                        ),
                    ),
                ),
            ),
            'height' => array(
                'extra' => array(
                    'chunks' => array(
                        'options' => array(
                            'height' => array(
                                'label'             => esc_html__( 'Chunks Height', 'divi-plus' ),
                                'range_settings'    => array(
                                    'min'  => 1,
                                    'max'  => 100,
                                    'step' => 1,
                                ),
                                'hover'             => false,
                                'default_unit'      => 'px',
                                'default_tablet'    => '',
                                'default_phone'     => '',
                                'show_if'           => array(
                                    'enable_custom_chunks_size' => 'on'
                                ),
                                'tab_slug'          => 'general',
                                'toggle_slug'       => 'main_content',
                            ),
                        ),
                        'use_max_height' => false,
                        'use_min_height' => false,
                        'css'            => array(
                            'main' => "{$this->main_css_element} .dipl_bar_counter_chunks",
                            'important' => 'all',
                        ),
                    ),
                ),
            ),
            'margin_padding' => array(
                'css' => array(
                    'main'      => $this->main_css_element,
                    'important' => 'all',
                ),
            ),
            'background' => array(
                'css' => array(
                    'main' => $this->main_css_element,
                ),
            ),
            'text' => false,
            'text_shadow' => false,
        );
    }

    public function get_fields() {

        return array_merge(
            array(
                'bar_layout' => array(
                    'label'             => esc_html__( 'Layout', 'divi-plus' ),
                    'type'              => 'select',
                    'option_category'   => 'configuration',
                    'options'           => array(
                        'layout1'   => esc_html__( 'Layout 1', 'divi-plus' ),
                        'layout2'   => esc_html__( 'Layout 2', 'divi-plus' ),
                    ),
                    'default'           => 'layout1',
                    'default_on_front'  => 'layout1',
                    'tab_slug'          => 'general',
                    'toggle_slug'       => 'main_content',
                    'description'       => esc_html__( 'Here you can select the layout for your bar.', 'divi-plus' ),
                ),
                'title' => array(
                    'label'             => esc_html__( 'Title', 'divi-plus' ),
                    'type'              => 'text',
                    'option_category'   => 'basic_option',
                    'tab_slug'          => 'general',
                    'toggle_slug'       => 'main_content',
                    'description'       => esc_html__( 'Define the title for your bar.', 'divi-plus' ),
                ),
                'percent' => array(
                    'label'             => esc_html__( 'Percent', 'divi-plus' ),
                    'type'              => 'range',
                    'option_category'   => 'layout',
                    'range_settings'    => array(
                        'min'  => '1',
                        'max'  => '100',
                        'step' => '1',
                    ),
                    'fixed_unit'        => '%',
                    'default'           => '50%',
                    'default_on_front'  => '50%',
                    'tab_slug'          => 'general',
                    'toggle_slug'       => 'main_content',
                    'description'       => esc_html__( 'Define the percentage for your bar.', 'divi-plus' ),
                ),
                'show_empty_bar' => array(
                    'label'             => esc_html__( 'Display Empty Bar/Chunks', 'divi-plus' ),
                    'type'              => 'yes_no_button',
                    'option_category'   => 'configuration',
                    'options'           => array(
                        'on'  => esc_html__( 'Yes', 'divi-plus' ),
                        'off' => esc_html__( 'No', 'divi-plus' ),
                    ),
                    'default'           => 'off',
                    'tab_slug'          => 'general',
                    'toggle_slug'       => 'main_content',
                    'description'       => esc_html__( 'Here you can choose whether to display empty bar or not.', 'divi-plus' ),
                ),
                'enable_custom_chunks_size' => array(
                    'label'            => esc_html__( 'Enable Custom Chunks Size', 'divi-plus' ),
                    'type'             => 'yes_no_button',
                    'option_category'  => 'configuration',
                    'options'          => array(
                        'on'  => esc_html__( 'Yes', 'divi-plus' ),
                        'off' => esc_html__( 'No', 'divi-plus' ),
                    ),
                    'default'           => 'off',
                    'default_on_front'  => 'off',
                    'show_if'           => array(
                        'bar_layout' => 'layout2',
                    ),
                    'tab_slug'          => 'general',
                    'toggle_slug'       => 'main_content',
                    'description'       => esc_html__( 'Whether or not to enable custom chunks size.', 'divi-plus' ),
                ),
                'use_stripes' => array(
                    'label'             => esc_html__( 'Use Stripes', 'divi-plus' ),
                    'type'              => 'yes_no_button',
                    'option_category'   => 'configuration',
                    'options'           => array(
                        'on'  => esc_html__( 'Yes', 'divi-plus' ),
                        'off' => esc_html__( 'No', 'divi-plus' ),
                    ),
                    'default'           => 'off',
                    'show_if'           => array(
                        'bar_layout' => 'layout1',
                    ),
                    'tab_slug'          => 'general',
                    'toggle_slug'       => 'main_content',
                    'description'       => esc_html__( 'Here you can choose whether to use striped bar or not.', 'divi-plus' ),
                ),
                'stripe_color' => array(
                    'label'            => esc_html__( 'Stripe Color', 'divi-plus' ),
                    'type'             => 'color-alpha',
                    'custom_color'     => true,
                    'show_if'          => array(
                        'bar_layout' => 'layout1',
                        'use_stripes' => 'on',
                    ),
                    'default'          => 'rgba(255,255,255,.15)',
                    'default_on_front' => 'rgba(255,255,255,.15)',
                    'tab_slug'         => 'general',
                    'toggle_slug'      => 'main_content',
                    'description'      => esc_html__( 'Here you can select the color for the stripes.', 'divi-plus' ),
                ),
                'use_animated_stripes' => array(
                    'label'             => esc_html__( 'Enable Stripes animation', 'divi-plus' ),
                    'type'              => 'yes_no_button',
                    'option_category'   => 'configuration',
                    'options'           => array(
                        'on'  => esc_html__( 'Yes', 'divi-plus' ),
                        'off' => esc_html__( 'No', 'divi-plus' ),
                    ),
                    'default'           => 'off',
                    'show_if'           => array(
                        'bar_layout' => 'layout1',
                        'use_stripes' => 'on',
                    ),
                    'tab_slug'          => 'general',
                    'toggle_slug'       => 'main_content',
                    'description'       => esc_html__( 'Here you can choose whether to use animated striped bar or not.', 'divi-plus' ),
                ),
                'animation_speed' => array(
                    'label'                 => esc_html__( 'Animation Speed', 'divi-plus' ),
                    'type'                  => 'range',
                    'option_category'       => 'layout',
                    'range_settings'        => array(
                        'min'  => '1',
                        'max'  => '10',
                        'step' => '1',
                    ),
                    'unitless'              => true,
                    'default'               => '1',
                    'default_on_front'      => '1',
                    'show_if'          => array(
                        'bar_layout' => 'layout1',
                        'use_stripes' => 'on',
                        'use_animated_stripes' => 'on',
                    ),
                    'tab_slug'              => 'general',
                    'toggle_slug'           => 'main_content',
                    'description'           => esc_html__( 'Here you can select the animation speed in seconds.', 'divi-plus' ),
                ),
                'bar_bg_color' => array(
                    'label'                 => esc_html__( 'Bar/Chunks Background', 'divi-plus' ),
                    'type'                  => 'background-field',
                    'base_name'             => 'bar_bg',
                    'context'               => 'bar_bg_color',
                    'option_category'       => 'button',
                    'custom_color'          => true,
                    'background_fields'     => $this->generate_background_options( 'bar_bg', 'button', 'general', 'background', 'bar_bg_color' ),
                    'hover'                 => 'tabs',
                    'tab_slug'              => 'general',
                    'toggle_slug'           => 'background',
                    'description'           => esc_html__( 'Customize the background style of the bar by adjusting the background color, gradient, and image.', 'divi-plus' ),
                ),
                'filled_bar_bg_color' => array(
                    'label'                 => esc_html__( 'Filled Bar/Chunks Background', 'divi-plus' ),
                    'type'                  => 'background-field',
                    'base_name'             => 'filled_bar_bg',
                    'context'               => 'filled_bar_bg_color',
                    'option_category'       => 'button',
                    'custom_color'          => true,
                    'background_fields'     => $this->generate_background_options( 'filled_bar_bg', 'button', 'general', 'background', 'filled_bar_bg_color' ),
                    'hover'                 => 'tabs',
                    'tab_slug'              => 'general',
                    'toggle_slug'           => 'background',
                    'description'           => esc_html__( 'Customize the background style of the bar by adjusting the background color, gradient, and image.', 'divi-plus' ),
                ),
            ),
            $this->generate_background_options( 'bar_bg', 'skip', 'general', 'background', 'bar_bg_color' ),
            $this->generate_background_options( 'filled_bar_bg', 'skip', 'general', 'background', 'filled_bar_bg_color' )
        );
    }

    public function render( $attrs, $content, $render_slug ) {
        $bar_layout                 = $this->props['bar_layout'];
        $title                      = $this->props['title'];
        $percent                    = absint( $this->props['percent'] );
        $show_empty_bar             = $this->props['show_empty_bar'];
        $use_stripes                = $this->props['use_stripes'];
        $use_animated_stripes       = $this->props['use_animated_stripes'];
        $animation_speed            = floatval( $this->props['animation_speed'] );
        $stripe_color               = $this->props['stripe_color'];
        $enable_custom_chunks_size  = $this->props['enable_custom_chunks_size'];

        $title_level                = $this->props['title_level'];
        $processed_title_level      = et_pb_process_header_level( $title_level, 'h4' );
        $processed_title_level      = esc_html( $processed_title_level );
        
        wp_enqueue_script( 'dipl-bar-counter-custom', PLUGIN_PATH."includes/modules/BarCounter/dipl-bar-counter-custom.min.js", array('jquery'), '1.0.0', true );
        $file = et_is_builder_plugin_active() ? 'style-dbp' : 'style';
        wp_enqueue_style( 'dipl-bar-counter-style', PLUGIN_PATH . 'includes/modules/BarCounter/' . $file . '.min.css', array(), '1.0.0' );

        if ( $percent > 100 ) {
            $percent = 100;
        }
        
        if ( 'layout1' === $bar_layout ) {
            $filled_bar = sprintf(
                '<div class="dipl_bar_counter_filled_bar_wrapper" data-percent="%1$s">
                    <div class="dipl_bar_counter_filled_bar %2$s"></div>
                    <span class="dipl_bar_counter_percent">%1$s%%</span>
                </div>',
                esc_attr( $percent ),
                'on' === $use_stripes ? 'on' === $use_animated_stripes ? 'dipl_bar_counter_animated_striped_bar' : 'dipl_bar_counter_striped_bar' : ''

            );
            if ( 'on' === $show_empty_bar ) {
                $bar = sprintf(
                    '<div class="dipl_bar_counter_bar">
                        %1$s
                    </div>',
                    $filled_bar
                );
            }
            $bar_wrapper = sprintf(
                '<div class="dipl_bar_counter_bar_wrapper">
                    %1$s
                </div>',
                'on' === $show_empty_bar ? $bar : $filled_bar
            );
        }

        if ( 'layout2' === $bar_layout ) {
            $filled_chunks = absint( round( $percent/10 ) );
            $filled_chunks = $filled_chunks < 1 ? 1 : $filled_chunks;
            $chunks        = '';
            for ( $i=1; $i <= $filled_chunks; $i++ ) {
                $chunks .= '<div class="dipl_bar_counter_chunks dipl_bar_counter_filled_chunks"></div>';
            }
            
            if ( 'on' === $show_empty_bar ) {
                $empty_chunks  = 10 - $filled_chunks;
                if ( $empty_chunks > 0 ) {
                    for ( $i=1; $i <= $empty_chunks; $i++ ) {
                        $chunks .= '<div class="dipl_bar_counter_chunks dipl_bar_counter_empty_chunks"></div>';
                    }
                }
            }

            $chunks .= sprintf(
                '<span class="dipl_bar_counter_percent">%1$s%%</span>',
                esc_html( $percent )
            );

            $filled_bar = sprintf(
                '<div class="dipl_bar_counter_filled_bar_wrapper" data-percent="%1$s">
                    %2$s
                </div>',
                esc_attr( $percent ),
                $chunks
            );

            $bar_wrapper = sprintf(
                '<div class="dipl_bar_counter_bar_wrapper">
                    %1$s
                </div>',
                $filled_bar
            );
        }

        if ( '' !== $title ) {
            $title = sprintf(
                '<%1$s class="dipl_bar_counter_title">%2$s</%1$s>',
                $processed_title_level,
                esc_html__( $title, 'divi-plus' )
            );
        }

        $wrapper = sprintf(
            '<div class="dipl_bar_counter_wrapper %1$s">
                %2$s
                %3$s
            </div>',
            esc_attr( $bar_layout ),
            '' !== $title ? $title : '',
            $bar_wrapper
        );

        if ( 'layout2' === $bar_layout ) {
            if ( 'off' === $show_empty_bar ) {
                $width = intval( round( $percent/10 ) ) * 10;
                if (
                    'off' === $enable_custom_chunks_size ||
                    (
                        ( ! isset( $this->props['chunks_width'] ) || '' === $this->props['chunks_width'] ) &&
                        ( ! isset( $this->props['chunks_height'] ) || '' === $this->props['chunks_height'] )
                    )
                ) {
                    self::set_style(
                        $render_slug,
                        array(
                            'selector'    => '%%order_class%% .layout2 .dipl_bar_counter_filled_bar_wrapper',
                            'declaration' => sprintf( 'width: %1$s%% !important;', esc_attr( $width ) ),
                        )
                    );
                }
            }

            if (
                'on' === $enable_custom_chunks_size &&
                (
                    ( isset( $this->props['chunks_width'] ) && '' !== $this->props['chunks_width'] && 'auto' !== $this->props['chunks_width'] ) ||
                    ( isset( $this->props['chunks_height'] ) && '' !== $this->props['chunks_height'] && 'auto' !== $this->props['chunks_height'] )
                )
            ) {
                self::set_style(
                    $render_slug,
                    array(
                        'selector'    => '%%order_class%% .layout2 .dipl_bar_counter_filled_bar_wrapper',
                        'declaration' => 'align-items: center;',
                    )
                );
                self::set_style(
                    $render_slug,
                    array(
                        'selector'    => '%%order_class%% .dipl_bar_counter_chunks',
                        'declaration' => 'flex-grow: 0;',
                    )
                );
            }
        }
        
        self::set_style(
            $render_slug,
            array(
                'selector'    => '%%order_class%% .dipl_bar_counter_animated_striped_bar:before',
                'declaration' => sprintf( 'animation-duration: %1$ss;', esc_attr( $animation_speed ) ),
            )
        );

        self::set_style(
            $render_slug,
            array(
                'selector'    => '%%order_class%% .dipl_bar_counter_striped_bar:before, %%order_class%% .dipl_bar_counter_animated_striped_bar:before',
                'declaration' => sprintf( 
                    'background-image: -webkit-linear-gradient(-45deg, %1$s 25%%, transparent 25%%, transparent 50%%, %1$s 50%%, %1$s 75%%, transparent 75%%, transparent) !important;
                    background-image: -moz-linear-gradient(-45deg, %1$s 25%%, transparent 25%%, transparent 50%%, %1$s 50%%, %1$s 75%%, transparent 75%%, transparent) !important;
                    background-image: linear-gradient(-45deg, %1$s 25%%, transparent 25%%, transparent 50%%, %1$s 50%%, %1$s 75%%, transparent 75%%, transparent) !important;',
                    esc_attr( $stripe_color )
                ),
            )
        );

        $args = array(
            'render_slug'   => $render_slug,
            'props'         => $this->props,
            'fields'        => $this->fields_unprocessed,
            'module'        => $this,
            'backgrounds'   => array(
                'bar_bg' => array(
                    'normal'    => "{$this->main_css_element} .dipl_bar_counter_bar, {$this->main_css_element} .dipl_bar_counter_chunks",
                    'hover'     => "{$this->main_css_element} .dipl_bar_counter_bar:hover, {$this->main_css_element} .dipl_bar_counter_filled_bar_wrapper:hover .dipl_bar_counter_chunks",
                ),
                'filled_bar_bg' => array(
                    'normal' => "{$this->main_css_element} .dipl_bar_counter_filled_bar, {$this->main_css_element} .dipl_bar_counter_filled_chunks:before",
                    'hover' => "{$this->main_css_element} .dipl_bar_counter_filled_bar:hover, {$this->main_css_element} .dipl_bar_counter_filled_bar_wrapper:hover .dipl_bar_counter_filled_chunks:before"
                ),
            ),
        );

        DiviPlusHelper::process_background( $args );
        
        return et_core_intentionally_unescaped( $wrapper, 'html' );
    }

}
$plugin_options = get_option( ELICUS_DIVI_PLUS_OPTION );
if ( isset( $plugin_options['dipl-modules'] ) ) {
    $modules = explode( ',', $plugin_options['dipl-modules'] );
    if ( in_array( 'dipl_bar_counter', $modules ) ) {
        new DIPL_BarCounter();
    }
} else {
    new DIPL_BarCounter();
}