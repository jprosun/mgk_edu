<?php
/**
 * Register MGK sections as native Elementor widgets.
 *
 * This is the Elementor counterpart of the Flatsome build's inc/mgk-ux-builder.php.
 * It is the bridge that lets a site owner drag / drop / delete / reorder MGK
 * sections visually in the Elementor editor, while developers keep authoring them
 * as plain PHP shortcodes (inc/mgk-sections.php, inc/mgk-content-sections.php).
 *
 * HOW IT WORKS
 *   - add_shortcode() (in mgk-sections.php / mgk-content-sections.php) handles
 *     the actual front-end rendering — the SINGLE SOURCE OF TRUTH for markup.
 *   - Each MGK section is exposed as one Elementor widget (a config-driven
 *     instance of MGK_Elementor_Section_Widget below). The widget's controls map
 *     1:1 to the shortcode's text atts; render() rebuilds the shortcode from the
 *     widget settings and echoes do_shortcode(). So the editor canvas and the
 *     live front end render through the EXACT same partial — markup never diverges.
 *   - Defaults are pulled from mgk_site_setting(), so a freshly-dropped widget is
 *     pre-filled with the real site copy (same behaviour as the UX Builder build).
 *
 * WHY ONE WIDGET PER SECTION (not per hero micro-element)
 *   The Flatsome build also registered hero "micro" containers
 *   (mgk_hero_copy / mgk_hero_eyebrow / ...). In Elementor that nesting is done
 *   natively with Sections/Columns/Containers, so we collapse the hero into a
 *   single composite "MGK · Hero" widget (the same composite the UX Builder file
 *   exposed as `mgk_hero`). Owners who want to recompose the hero internals use
 *   Elementor containers; the individual hero pieces remain available as
 *   shortcodes for advanced/manual use.
 *
 * SAFETY
 *   - Only registers when Elementor is active (hooks fire only then).
 *   - Adding/removing a section here changes the panel with no DB migration.
 *   - Data-heavy pages split into DATA CORE and DATA SHELL. The core data
 *     source/query/order/review logic stays in PHP/wp-admin; shell widgets may
 *     expose filters, toolbar labels, buttons, spacing, columns, and card/form
 *     styling so owners can edit presentation without turning records into
 *     static builder content.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Define the MGK widget class — lazily, only once Elementor's Widget_Base is
 * loaded.
 *
 * WHY A FUNCTION (not a top-level class): functions.php require()s this file very
 * early (before `after_setup_theme` finishes), at which point \Elementor\Widget_Base
 * is NOT loaded yet. A top-level `class X extends \Elementor\Widget_Base` would
 * fatal, and guarding it with class_exists() at require time would silently skip
 * the definition forever. So we define the class on demand from the
 * `elementor/widgets/register` hook (and `elementor/loaded`), when Widget_Base is
 * guaranteed present.
 *
 * One instance is registered per entry in mgk_elementor_sections(); the whole
 * behaviour (name, title, icon, controls, render) is derived from that config,
 * so there is no per-section subclass to maintain.
 */
function mgk_elementor_define_widget_class() {
    if ( class_exists( 'MGK_Elementor_Section_Widget' ) || ! class_exists( '\Elementor\Widget_Base' ) ) {
        return;
    }

class MGK_Elementor_Section_Widget extends \Elementor\Widget_Base {

    /**
     * Per-instance section config. Elementor instantiates widgets with no args
     * when it rehydrates them from saved data, so we cannot rely on the
     * constructor alone — config is also resolved by name via
     * mgk_elementor_section_config() as a fallback.
     *
     * @var array
     */
    protected $mgk_config = [];

    /**
     * @param array      $data    Elementor widget data (may be empty on registration).
     * @param array|null $args    Elementor args; we smuggle our config in $args['mgk_config'].
     */
    public function __construct( $data = [], $args = null ) {
        if ( is_array( $args ) && ! empty( $args['mgk_config'] ) ) {
            $this->mgk_config = $args['mgk_config'];
        }
        parent::__construct( $data, $args );
    }

    /** Resolve config from the smuggled args or, on rehydration, by widget name. */
    protected function mgk_get_config() {
        if ( ! empty( $this->mgk_config ) ) {
            return $this->mgk_config;
        }
        $name = $this->get_name();
        $cfg  = function_exists( 'mgk_elementor_section_config' ) ? mgk_elementor_section_config( $name ) : null;
        return is_array( $cfg ) ? $cfg : [ 'tag' => $name, 'title' => $name, 'icon' => 'eicon-shortcode', 'controls' => [] ];
    }

    public function get_name() {
        if ( ! empty( $this->mgk_config['tag'] ) ) {
            return $this->mgk_config['tag'];
        }
        // Elementor reads get_name() during rehydration before our config is set;
        // it passes the saved widgetType in $data, which parent stores.
        $settings = $this->get_data( 'widgetType' );
        return $settings ? $settings : 'mgk_section';
    }

    public function get_title() {
        $cfg = $this->mgk_get_config();
        return isset( $cfg['title'] ) ? $cfg['title'] : $this->get_name();
    }

    public function get_icon() {
        $cfg = $this->mgk_get_config();
        return ! empty( $cfg['icon'] ) ? $cfg['icon'] : 'eicon-shortcode';
    }

    public function get_categories() {
        return [ 'mgk-edu' ];
    }

    /** Keywords help owners find the block in the panel search. */
    public function get_keywords() {
        return [ 'mgk', 'edu', 'margick', 'tutor', 'section' ];
    }

    /**
     * Build the Elementor controls panel from the section config.
     * Each control maps to a single shortcode att of the same key.
     */
    protected function register_controls() {
        $cfg = $this->mgk_get_config();

        $this->start_controls_section( 'mgk_content', [
            'label' => __( 'Content', 'mgk-edu' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ] );

        if ( empty( $cfg['controls'] ) && empty( $cfg['repeater'] ) ) {
            $this->add_control( 'mgk_notice', [
                'type'            => \Elementor\Controls_Manager::RAW_HTML,
                'raw'             => __( 'This section pulls its content from MGK Site Settings / the page fields. Drag it where you want it; edit the copy in Customizer → MGK Site Settings.', 'mgk-edu' ),
                'content_classes' => 'elementor-descriptor',
            ] );
        } else {
            foreach ( $cfg['controls'] as $key => $control ) {
                $this->add_control( $key, $this->mgk_map_control( $control ) );
            }
        }

        // Repeater — lets owners add/remove/reorder/edit each sub-item (each stat,
        // step, card, FAQ…). Leaving the repeater empty falls back to the section's
        // default data (MGK Site Settings), so out-of-the-box output is unchanged.
        if ( ! empty( $cfg['repeater'] ) && is_array( $cfg['repeater'] ) ) {
            $this->mgk_register_repeater( $cfg['repeater'] );
        }

        $this->end_controls_section();

        // STYLE TAB — per-element typography / alignment / color / spacing.
        // Each target maps to a CSS selector inside the section's partial markup;
        // Elementor scopes it under {{WRAPPER}} (this widget's wrapper) so it only
        // ever restyles THIS instance. Targets are declared in the section config
        // (mgk_elementor_sections()), so adding style to a widget = data, no code.
        if ( ! empty( $cfg['style_targets'] ) && is_array( $cfg['style_targets'] ) ) {
            foreach ( $cfg['style_targets'] as $tkey => $target ) {
                $this->mgk_register_style_section( $tkey, $target );
            }
        }
    }

    /**
     * Register the REPEATER control for sections with sub-items.
     *
     * $rep = [
     *   'control'    => 'items',                       // control name
     *   'label'      => 'Stats', 'item_label' => 'Stat',
     *   'title_field'=> 'value',                        // which field labels the row
     *   'fields'     => [ 'value'=>['type'=>'text','label'=>'Value'], ... ],
     *   'defaults'   => [ ['value'=>'50,000+','label'=>'Verified tutors'], ... ],
     * ]
     */
    protected function mgk_register_repeater( array $rep ) {
        if ( ! class_exists( '\Elementor\Repeater' ) ) {
            return;
        }
        $control = isset( $rep['control'] ) ? $rep['control'] : 'items';
        $fields  = isset( $rep['fields'] ) && is_array( $rep['fields'] ) ? $rep['fields'] : [];

        $repeater = new \Elementor\Repeater();
        foreach ( $fields as $fkey => $fspec ) {
            $repeater->add_control( $fkey, $this->mgk_map_control( $fspec ) );
        }

        // default_label hint + a separator so it reads as its own block.
        $args = [
            'label'   => isset( $rep['label'] ) ? $rep['label'] : __( 'Items', 'mgk-edu' ),
            'type'    => \Elementor\Controls_Manager::REPEATER,
            'fields'  => $repeater->get_controls(),
            'prevent_empty' => false, // empty is allowed → falls back to default data
        ];
        if ( ! empty( $rep['defaults'] ) && is_array( $rep['defaults'] ) ) {
            $args['default'] = $rep['defaults'];
        }
        if ( ! empty( $rep['title_field'] ) ) {
            $args['title_field'] = '{{{ ' . $rep['title_field'] . ' }}}';
        }
        $this->add_control( $control, $args );

        // A short hint so owners understand the empty-state behaviour.
        $this->add_control( $control . '_hint', [
            'type'            => \Elementor\Controls_Manager::RAW_HTML,
            'raw'             => __( 'Leave the list empty to use the site default content.', 'mgk-edu' ),
            'content_classes' => 'elementor-descriptor',
        ] );
    }

    /**
     * Register one Style-tab section for a target element of the partial.
     *
     * @param string $tkey   Unique key (control-name prefix).
     * @param array  $target [ 'label'=>, 'selector'=>'.mgk-eyebrow', 'is_section'=>bool,
     *                          'features'=>['typography','align','color','background','padding','margin'] ]
     *                        `selector` is relative to {{WRAPPER}}.
     */
    protected function mgk_register_style_section( $tkey, array $target ) {
        $label    = isset( $target['label'] ) ? $target['label'] : ucfirst( $tkey );
        $sel      = isset( $target['selector'] ) ? $target['selector'] : '';
        $full     = '{{WRAPPER}} ' . $sel;
        $features = isset( $target['features'] ) ? (array) $target['features'] : [ 'typography', 'align', 'color' ];
        $has      = function ( $f ) use ( $features ) { return in_array( $f, $features, true ); };

        if ( $sel === '' ) {
            return;
        }

        $this->start_controls_section( 'mgk_style_' . $tkey, [
            'label' => $label,
            'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
        ] );

        if ( $has( 'align' ) ) {
            $this->add_responsive_control( $tkey . '_align', [
                'label'     => __( 'Alignment', 'mgk-edu' ),
                'type'      => \Elementor\Controls_Manager::CHOOSE,
                'options'   => [
                    'left'    => [ 'title' => __( 'Left', 'mgk-edu' ),    'icon' => 'eicon-text-align-left' ],
                    'center'  => [ 'title' => __( 'Center', 'mgk-edu' ),  'icon' => 'eicon-text-align-center' ],
                    'right'   => [ 'title' => __( 'Right', 'mgk-edu' ),   'icon' => 'eicon-text-align-right' ],
                    'justify' => [ 'title' => __( 'Justify', 'mgk-edu' ), 'icon' => 'eicon-text-align-justify' ],
                ],
                'selectors' => [ $full => 'text-align: {{VALUE}};' ],
            ] );
        }

        if ( $has( 'color' ) ) {
            $this->add_control( $tkey . '_color', [
                'label'     => __( 'Text Color', 'mgk-edu' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [ $full => 'color: {{VALUE}};' ],
            ] );
        }

        if ( $has( 'typography' ) && class_exists( '\Elementor\Group_Control_Typography' ) ) {
            $this->add_group_control( \Elementor\Group_Control_Typography::get_type(), [
                'name'     => $tkey . '_typography',
                'selector' => $full,
            ] );
        }

        if ( $has( 'background' ) ) {
            $this->add_control( $tkey . '_bg', [
                'label'     => __( 'Background Color', 'mgk-edu' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [ $full => 'background-color: {{VALUE}};' ],
            ] );
        }

        if ( $has( 'padding' ) ) {
            $this->add_responsive_control( $tkey . '_padding', [
                'label'      => __( 'Padding', 'mgk-edu' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%', 'rem' ],
                'selectors'  => [ $full => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
            ] );
        }

        if ( $has( 'margin' ) ) {
            $this->add_responsive_control( $tkey . '_margin', [
                'label'      => __( 'Margin', 'mgk-edu' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%', 'rem' ],
                'selectors'  => [ $full => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
            ] );
        }

        if ( $has( 'width' ) ) {
            $this->add_responsive_control( $tkey . '_width', [
                'label'      => __( 'Width', 'mgk-edu' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'range'      => [
                    'px' => [ 'min' => 40, 'max' => 600 ],
                    '%'  => [ 'min' => 10, 'max' => 100 ],
                    'em' => [ 'min' => 2,  'max' => 40 ],
                ],
                'selectors'  => [ $full => 'width: {{SIZE}}{{UNIT}};' ],
            ] );
            $this->add_responsive_control( $tkey . '_max_width', [
                'label'      => __( 'Max width', 'mgk-edu' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'range'      => [
                    'px' => [ 'min' => 40, 'max' => 800 ],
                    '%'  => [ 'min' => 10, 'max' => 100 ],
                ],
                'selectors'  => [ $full => 'max-width: {{SIZE}}{{UNIT}};' ],
            ] );
        }

        // ── Advanced (CONTENT only) — border, radius, box-shadow, hover. ──
        if ( $has( 'border' ) && class_exists( '\Elementor\Group_Control_Border' ) ) {
            $this->add_group_control( \Elementor\Group_Control_Border::get_type(), [
                'name'     => $tkey . '_border',
                'selector' => $full,
            ] );
            $this->add_responsive_control( $tkey . '_radius', [
                'label'      => __( 'Border Radius', 'mgk-edu' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors'  => [ $full => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
            ] );
        }

        if ( $has( 'shadow' ) && class_exists( '\Elementor\Group_Control_Box_Shadow' ) ) {
            $this->add_group_control( \Elementor\Group_Control_Box_Shadow::get_type(), [
                'name'     => $tkey . '_shadow',
                'selector' => $full,
            ] );
        }

        if ( $has( 'hover' ) ) {
            $this->add_control( $tkey . '_hover_heading', [
                'label'     => __( 'Hover', 'mgk-edu' ),
                'type'      => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ] );
            $this->add_control( $tkey . '_hover_color', [
                'label'     => __( 'Text Color (hover)', 'mgk-edu' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [ $full . ':hover' => 'color: {{VALUE}};' ],
            ] );
            $this->add_control( $tkey . '_hover_bg', [
                'label'     => __( 'Background (hover)', 'mgk-edu' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [ $full . ':hover' => 'background-color: {{VALUE}};' ],
            ] );
        }

        $this->end_controls_section();
    }

    /**
     * Translate our compact control spec into an Elementor add_control() array.
     *
     * Spec shape: [ 'type' => 'text'|'textarea'|'number'|'image', 'label' => '', 'default' => '', ... ]
     */
    protected function mgk_map_control( array $c ) {
        $type    = isset( $c['type'] ) ? $c['type'] : 'text';
        $label   = isset( $c['label'] ) ? $c['label'] : '';
        $default = isset( $c['default'] ) ? $c['default'] : '';

        switch ( $type ) {
            case 'textarea':
                return [
                    'label'   => $label,
                    'type'    => \Elementor\Controls_Manager::TEXTAREA,
                    'default' => $default,
                    'rows'    => isset( $c['rows'] ) ? $c['rows'] : 3,
                ];

            case 'number':
                return [
                    'label'   => $label,
                    'type'    => \Elementor\Controls_Manager::NUMBER,
                    'default' => $default,
                    'min'     => isset( $c['min'] ) ? $c['min'] : 1,
                    'max'     => isset( $c['max'] ) ? $c['max'] : 12,
                    'step'    => 1,
                ];

            case 'image':
                // Elementor MEDIA control returns [id,url]; we forward the id to the shortcode.
                return [
                    'label'   => $label,
                    'type'    => \Elementor\Controls_Manager::MEDIA,
                    'default' => [ 'url' => '' ],
                ];

            case 'switcher':
                // ON → returns 'yes' (forwarded to the shortcode att); OFF → '' (dropped
                // by build_shortcode, so the partial sees no att). Phrase the control as
                // an opt-IN ("Hide …") so the dropped-empty default means "show".
                return [
                    'label'        => $label,
                    'type'         => \Elementor\Controls_Manager::SWITCHER,
                    'label_on'     => isset( $c['label_on'] ) ? $c['label_on'] : 'Yes',
                    'label_off'    => isset( $c['label_off'] ) ? $c['label_off'] : 'No',
                    'return_value' => 'yes',
                    'default'      => $default,
                ];

            case 'select':
                return [
                    'label'   => $label,
                    'type'    => \Elementor\Controls_Manager::SELECT,
                    'default' => $default,
                    'options' => isset( $c['options'] ) ? (array) $c['options'] : [],
                ];

            case 'text':
            default:
                return [
                    'label'       => $label,
                    'type'        => \Elementor\Controls_Manager::TEXT,
                    'default'     => $default,
                    'label_block' => true,
                ];
        }
    }

    /**
     * Render on the front end AND in the editor canvas.
     *
     * Rebuilds the shortcode `[tag att="value" ...]` from the widget settings and
     * runs it through do_shortcode(), so output is byte-identical to the partial.
     * Only non-empty atts are emitted; an empty att lets the shortcode fall back
     * to mgk_site_setting() (exactly like the UX Builder build).
     */
    protected function render() {
        $cfg      = $this->mgk_get_config();
        $tag      = isset( $cfg['tag'] ) ? $cfg['tag'] : $this->get_name();
        $settings = $this->get_settings_for_display();

        // Repeater path: if this section has a repeater AND the owner added rows,
        // render the partial DIRECTLY with the per-instance items (the same partial
        // the shortcode uses — still one source of HTML). Text atts (e.g. heading)
        // are passed through too. Empty repeater → fall through to the normal
        // shortcode path, which uses the section's default data.
        if ( ! empty( $cfg['repeater'] ) && ! empty( $cfg['repeater']['partial'] ) ) {
            $items = mgk_elementor_repeater_items( $cfg['repeater'], $settings );
            if ( ! empty( $items ) ) {
                $part_args = $this->mgk_text_atts( $cfg, $settings ); // heading/body if any
                $part_args['items'] = $items;
                echo mgk_render_part( 'template-parts/sections/home/' . $cfg['repeater']['partial'], $part_args );
                return;
            }
        }

        if ( ! shortcode_exists( $tag ) ) {
            if ( current_user_can( 'edit_posts' ) ) {
                echo '<div class="mgk-elementor-missing">' . esc_html( sprintf(
                    /* translators: %s: shortcode tag */
                    __( 'MGK section [%s] is not available.', 'mgk-edu' ),
                    $tag
                ) ) . '</div>';
            }
            return;
        }

        echo do_shortcode( mgk_elementor_build_shortcode( $tag, $cfg, $settings ) );
    }

    /**
     * Collect the section's text-att values (the non-repeater controls) keyed by
     * att name — used to forward heading/body to the partial on the repeater path.
     */
    protected function mgk_text_atts( array $cfg, array $settings ) {
        $out      = [];
        $controls = isset( $cfg['controls'] ) && is_array( $cfg['controls'] ) ? $cfg['controls'] : [];
        foreach ( $controls as $key => $control ) {
            if ( isset( $control['type'] ) && $control['type'] === 'image' ) {
                continue;
            }
            $att = isset( $control['att'] ) ? $control['att'] : $key;
            if ( isset( $settings[ $key ] ) && $settings[ $key ] !== '' ) {
                $out[ $att ] = $settings[ $key ];
            }
        }
        return $out;
    }

    /**
     * No JS template — render() (PHP/server-side) is the single source of truth,
     * so the editor preview matches the front end exactly. Returning an empty
     * content_template() makes Elementor fall back to the server render via AJAX.
     */
    protected function content_template() {}
}

} // end mgk_elementor_define_widget_class()


/**
 * Convert Elementor repeater rows into the item array shape a partial expects.
 * Pulled out of the widget so it is unit-testable without Elementor loaded.
 *
 * $rep['map'] controls the output shape:
 *   'assoc' → each item is an assoc array of the field keys, e.g. ['value'=>,'label'=>] or ['q'=>,'a'=>].
 *   'pairs' → each item is a 0/1-indexed array [field0, field1] (for partials that read $step[0]/$step[1]).
 * $rep['pair_order'] lists field keys in index order for 'pairs' (e.g. ['title','body']).
 *
 * A row is skipped if ALL its mapped values are empty (so a stray blank row
 * doesn't inject an empty card). Returns [] when there are no usable rows, which
 * tells render() to fall back to the section's default data.
 *
 * @param array $rep      The section config's 'repeater' spec.
 * @param array $settings Elementor widget settings (contains the repeater control value).
 * @return array
 */
function mgk_elementor_repeater_items( array $rep, array $settings ) {
    $control = isset( $rep['control'] ) ? $rep['control'] : 'items';
    $rows    = isset( $settings[ $control ] ) && is_array( $settings[ $control ] ) ? $settings[ $control ] : [];
    if ( empty( $rows ) ) {
        return [];
    }

    $map        = isset( $rep['map'] ) ? $rep['map'] : 'assoc';
    $field_keys = isset( $rep['fields'] ) ? array_keys( $rep['fields'] ) : [];
    $pair_order = isset( $rep['pair_order'] ) ? $rep['pair_order'] : $field_keys;

    $items = [];
    foreach ( $rows as $row ) {
        if ( ! is_array( $row ) ) {
            continue;
        }

        if ( $map === 'pairs' ) {
            $item    = [];
            $nonEmpty = false;
            foreach ( $pair_order as $fk ) {
                $val    = isset( $row[ $fk ] ) ? (string) $row[ $fk ] : '';
                $item[] = $val;
                if ( $val !== '' ) {
                    $nonEmpty = true;
                }
            }
            if ( $nonEmpty ) {
                $items[] = $item;
            }
        } else { // assoc
            $item     = [];
            $nonEmpty = false;
            foreach ( $field_keys as $fk ) {
                $val          = isset( $row[ $fk ] ) ? (string) $row[ $fk ] : '';
                $item[ $fk ]  = $val;
                if ( $val !== '' ) {
                    $nonEmpty = true;
                }
            }
            if ( $nonEmpty ) {
                $items[] = $item;
            }
        }
    }

    return $items;
}


/**
 * Assemble a shortcode string from a section config + Elementor settings.
 * Pulled out of the widget so it is unit-testable without Elementor loaded.
 *
 * @param string $tag      Shortcode tag (e.g. 'mgk_hero').
 * @param array  $cfg      Section config (its 'controls' define which atts exist).
 * @param array  $settings Elementor widget settings (control_key => value).
 * @return string          e.g. [mgk_hero eyebrow="..." proof="..."]
 */
function mgk_elementor_build_shortcode( $tag, array $cfg, array $settings ) {
    $atts     = [];
    $controls = isset( $cfg['controls'] ) && is_array( $cfg['controls'] ) ? $cfg['controls'] : [];

    foreach ( $controls as $key => $control ) {
        $type = isset( $control['type'] ) ? $control['type'] : 'text';

        if ( $type === 'image' ) {
            // Map Elementor MEDIA control -> the shortcode's *_id att.
            $att_key = isset( $control['att'] ) ? $control['att'] : ( $key . '_id' );
            $id      = isset( $settings[ $key ]['id'] ) ? (int) $settings[ $key ]['id'] : 0;
            if ( $id > 0 ) {
                $atts[ $att_key ] = (string) $id;
            }
            continue;
        }

        $att_key = isset( $control['att'] ) ? $control['att'] : $key;
        $value   = isset( $settings[ $key ] ) ? $settings[ $key ] : '';

        // Leave empty values out entirely so the shortcode falls back to its
        // mgk_site_setting() default (same contract as the UX Builder build).
        if ( $value === '' || $value === null ) {
            continue;
        }

        // Shortcode atts can't contain a double-quote or square bracket safely.
        $value = str_replace( [ '"', '[', ']' ], [ '”', '(', ')' ], (string) $value );
        $atts[ $att_key ] = $value;
    }

    if ( empty( $atts ) ) {
        return '[' . $tag . ']';
    }

    $pairs = [];
    foreach ( $atts as $k => $v ) {
        $pairs[] = $k . '="' . $v . '"';
    }

    return '[' . $tag . ' ' . implode( ' ', $pairs ) . ']';
}


/**
 * Canonical list of MGK sections exposed as Elementor widgets.
 *
 * This is the Elementor analogue of the add_ux_builder_shortcode() calls in the
 * Flatsome build. Each entry:
 *   tag      => shortcode tag (also the Elementor widget name)
 *   title    => label in the Elementor panel
 *   icon     => eicon-* panel icon
 *   controls => [ att_key => spec ]  (omit for content-driven sections)
 *
 * `spec` is the compact form consumed by mgk_map_control()/build_shortcode():
 *   [ 'type' => 'text'|'textarea'|'number'|'image', 'label' => '', 'default' => '', ... ]
 *
 * Defaults call mgk_site_setting() so a dropped widget shows real copy. Calling
 * mgk_site_setting() here is safe: this function runs at Elementor register time
 * (init or later), by which point site settings are available.
 */

/**
 * Build Style-tab targets for a CONTENT (marketing) section.
 *
 * Owners get FULL control: typography + alignment + color on the heading and
 * sub-heading text, plus a "Section Box" for background / padding / margin.
 * Selectors are relative to {{WRAPPER}} (Elementor scopes them to the instance).
 *
 * @param string      $outer   Outer wrapper class incl. dot, e.g. '.mgk-section'.
 * @param string|null $heading Heading selector relative to wrapper, e.g. '.mgk-section-head h2'. Null = skip.
 * @param string|null $sub     Sub-heading/body selector, e.g. '.mgk-section-head p'. Null = skip.
 * @return array
 */
function mgk_content_targets( $outer, $heading = null, $sub = null, $extra = [] ) {
    $t = [];
    if ( $heading ) {
        $t['heading'] = [ 'label' => 'Heading', 'selector' => $heading, 'features' => [ 'typography', 'align', 'color' ] ];
    }
    if ( $sub ) {
        $t['subheading'] = [ 'label' => 'Sub-heading', 'selector' => $sub, 'features' => [ 'typography', 'align', 'color' ] ];
    }
    // Per-element extra targets (item titles/bodies, buttons, eyebrows, …) declared
    // by the caller, inserted between the headings and the Section Box.
    foreach ( (array) $extra as $key => $spec ) {
        $t[ $key ] = $spec;
    }
    // CONTENT Section Box gets the advanced set too (border / radius / shadow / hover).
    $t['section'] = [ 'label' => 'Section Box', 'selector' => $outer, 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow', 'hover' ] ];
    return $t;
}

/**
 * Helper to build a "text element" target spec (typography + align + color),
 * with an optional override of the feature list.
 *
 * @param string $label
 * @param string $selector Relative to {{WRAPPER}}.
 * @param array  $features
 * @return array
 */
function mgk_style_text( $label, $selector, $features = [ 'typography', 'align', 'color' ] ) {
    return [ 'label' => $label, 'selector' => $selector, 'features' => $features ];
}

/**
 * Helper to build a "button" target spec (full button styling incl. hover).
 */
function mgk_style_button( $label, $selector ) {
    return [ 'label' => $label, 'selector' => $selector, 'features' => [ 'typography', 'color', 'background', 'width', 'padding', 'border', 'shadow', 'hover' ] ];
}

/**
 * Build Style-tab targets for a DATA-driven section (tutors, reviews, calculator,
 * query grids, …). Logic-heavy, so owners are LIMITED: they may restyle the
 * HEADING text (marketing copy, safe) and the whole box (background / padding /
 * margin / block-level alignment), but NOT touch per-item typography or structure
 * — keeping the data presentation consistent and the logic intact.
 *
 * @param string      $outer   Outer wrapper class incl. dot.
 * @param string|null $heading Optional heading selector — gives a Heading style section.
 * @param string|null $sub     Optional sub-heading selector.
 * @return array
 */
function mgk_data_targets( $outer, $heading = null, $sub = null ) {
    $t = [];
    if ( $heading ) {
        $t['heading'] = [ 'label' => 'Heading', 'selector' => $heading, 'features' => [ 'typography', 'align', 'color' ] ];
    }
    if ( $sub ) {
        $t['subheading'] = [ 'label' => 'Sub-heading', 'selector' => $sub, 'features' => [ 'typography', 'align', 'color' ] ];
    }
    $t['section'] = [
        'label'    => 'Section Box',
        'selector' => $outer,
        'features' => [ 'background', 'padding', 'margin', 'align' ],
    ];
    return $t;
}

function mgk_elementor_sections() {
    $s = function ( $key ) {
        return function_exists( 'mgk_site_setting' ) ? (string) mgk_site_setting( $key ) : '';
    };

    // Repeater default rows, prefilled from the section's current data so a
    // freshly-dropped widget shows the real content and owners edit from there.
    $stat_rows = [];
    if ( function_exists( 'mgk_site_home_stats' ) ) {
        foreach ( mgk_site_home_stats() as $r ) {
            $stat_rows[] = [ 'value' => $r['value'] ?? '', 'label' => $r['label'] ?? '' ];
        }
    }
    $step_rows = [];
    if ( function_exists( 'mgk_site_home_steps' ) ) {
        foreach ( mgk_site_home_steps() as $r ) {
            $step_rows[] = [ 'title' => $r[0] ?? '', 'body' => $r[1] ?? '' ];
        }
    }
    $why_rows = [];
    if ( function_exists( 'mgk_site_home_why_items' ) ) {
        foreach ( mgk_site_home_why_items() as $r ) {
            $why_rows[] = [ 'title' => $r[0] ?? '', 'body' => $r[1] ?? '' ];
        }
    }
    $faq_rows = [];
    if ( function_exists( 'mgk_get_faqs' ) ) {
        foreach ( mgk_get_faqs() as $r ) {
            $faq_rows[] = [ 'q' => $r['q'] ?? '', 'a' => $r['a'] ?? '' ];
        }
    }

    $sections = [

        /* ── S01 Home: composite section widgets ─────────────── */
        [
            'tag'   => 'mgk_hero',
            'title' => 'MGK · Hero',
            'icon'  => 'eicon-banner',
            'controls' => [
                'eyebrow'         => [ 'type' => 'text',     'label' => 'Eyebrow',               'default' => $s( 'hero_eyebrow' ) ],
                'title_before'    => [ 'type' => 'text',     'label' => 'Title — before highlight','default' => $s( 'hero_title_before' ) ],
                'title_highlight' => [ 'type' => 'text',     'label' => 'Title — highlight',      'default' => $s( 'hero_title_highlight' ) ],
                'title_after'     => [ 'type' => 'text',     'label' => 'Title — after highlight', 'default' => $s( 'hero_title_after' ) ],
                'search_button'   => [ 'type' => 'text',     'label' => 'Search button text',     'default' => $s( 'hero_search_button' ) ],
                'proof'           => [ 'type' => 'textarea', 'label' => 'Proof line',             'default' => $s( 'hero_proof' ) ],
            ],
            // Style tab — CONTENT (marketing) section, broken down into EACH
            // sub-element so owners style every piece independently. Selectors map
            // to the hero partial markup; Elementor scopes them under this widget's
            // {{WRAPPER}}, so styling never leaks to other instances.
            'style_targets' => [
                'eyebrow'    => [ 'label' => 'Eyebrow',         'selector' => '.mgk-eyebrow',           'features' => [ 'typography', 'align', 'color' ] ],
                'title'      => [ 'label' => 'Title',           'selector' => '.mgk-hero h1',           'features' => [ 'typography', 'align', 'color' ] ],
                'highlight'  => [ 'label' => 'Title Highlight', 'selector' => '.mgk-accent-text',       'features' => [ 'typography', 'color' ] ],
                'proof'      => [ 'label' => 'Proof Line',      'selector' => '.mgk-hero-proof',        'features' => [ 'typography', 'align', 'color' ] ],
                'searchbtn'  => [ 'label' => 'Search Button',   'selector' => '.mgk-search-panel button','features' => [ 'typography', 'color', 'background', 'padding', 'border', 'shadow', 'hover' ] ],
                'formlabel'  => [ 'label' => 'Form Labels',     'selector' => '.mgk-search-grid label span', 'features' => [ 'typography', 'color' ] ],
                'formfield'  => [ 'label' => 'Form Fields',     'selector' => '.mgk-search-grid select', 'features' => [ 'typography', 'color', 'background', 'border' ] ],
                'searchbox'  => [ 'label' => 'Search Panel Box','selector' => '.mgk-search-panel',       'features' => [ 'background', 'padding', 'border', 'shadow' ] ],
                'media'      => [ 'label' => 'Hero Media Box',  'selector' => '.mgk-hero-media',         'features' => [ 'background', 'border', 'shadow' ] ],
                'chip'       => [ 'label' => 'Video Chip',      'selector' => '.mgk-video-chip',         'features' => [ 'typography', 'color', 'background', 'padding', 'border' ] ],
                'section'    => [ 'label' => 'Section Box',     'selector' => '.mgk-hero',               'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
            ],
        ],
        [
            'tag'   => 'mgk_trust_stats',
            'title' => 'MGK · Trust Stats',
            'icon'  => 'eicon-counter',
            'controls' => [],
            'repeater' => [
                'control'     => 'items',
                'label'       => 'Stats',
                'partial'     => 'trust-stats',
                'map'         => 'assoc',
                'title_field' => 'value',
                'fields'      => [
                    'value' => [ 'type' => 'text', 'label' => 'Value' ],
                    'label' => [ 'type' => 'text', 'label' => 'Label' ],
                ],
                'defaults'    => $stat_rows,
            ],
            // DATA, but the stat value/label ARE the content (safe text styling).
            'style_targets' => [
                'value'   => mgk_style_text( 'Stat Value', '.mgk-stat strong', [ 'typography', 'color' ] ),
                'label'   => mgk_style_text( 'Stat Label', '.mgk-stat span',   [ 'typography', 'color' ] ),
                'section' => [ 'label' => 'Section Box', 'selector' => '.mgk-section-sm', 'features' => [ 'background', 'padding', 'margin', 'align' ] ],
            ],
        ],
        [
            'tag'   => 'mgk_live_feed',
            'title' => 'MGK · Live Feed',
            'icon'  => 'eicon-post-list',
            'controls' => [], // edited in MGK Site Settings
            'style_targets' => mgk_data_targets( '.mgk-live-feed' ), // DATA: live events
        ],
        [
            'tag'   => 'mgk_steps',
            'title' => 'MGK · How It Works (4 steps)',
            'icon'  => 'eicon-number-field',
            'controls' => [
                'heading' => [ 'type' => 'text',     'label' => 'Heading', 'default' => $s( 'steps_heading' ) ],
                'body'    => [ 'type' => 'textarea', 'label' => 'Body',    'default' => $s( 'steps_body' ) ],
            ],
            'repeater' => [
                'control'     => 'items',
                'label'       => 'Steps',
                'partial'     => 'steps',
                'map'         => 'pairs',
                'pair_order'  => [ 'title', 'body' ],
                'title_field' => 'title',
                'fields'      => [
                    'title' => [ 'type' => 'text',     'label' => 'Step title' ],
                    'body'  => [ 'type' => 'textarea', 'label' => 'Step text' ],
                ],
                'defaults'    => $step_rows,
            ],
            'style_targets' => mgk_content_targets( '.mgk-home-steps', '.mgk-section-head h2', '.mgk-section-head p', [
                'stepnum'  => mgk_style_text( 'Step Number', '.mgk-step .mgk-step-num', [ 'typography', 'color', 'background' ] ),
                'steptitle'=> mgk_style_text( 'Step Title',  '.mgk-step h3' ),
                'stepbody' => mgk_style_text( 'Step Body',   '.mgk-step p' ),
            ] ),
        ],
        [
            'tag'   => 'mgk_subjects',
            'title' => 'MGK · Subjects Grid',
            'icon'  => 'eicon-gallery-grid',
            'controls' => [
                'heading' => [ 'type' => 'text', 'label' => 'Heading', 'default' => $s( 'subjects_heading' ) ],
            ],
            // DATA: catalog grid logic locked; Heading text is editable.
            'style_targets' => mgk_data_targets( '.mgk-home-subjects', '.mgk-section-head h2' ),
        ],
        [
            'tag'   => 'mgk_featured_tutors',
            'title' => 'MGK · Featured Tutors',
            'icon'  => 'eicon-person',
            'controls' => [
                'heading' => [ 'type' => 'text',   'label' => 'Heading',     'default' => $s( 'tutors_heading' ) ],
                'body'    => [ 'type' => 'text',   'label' => 'Sub-heading', 'default' => $s( 'tutors_body' ) ],
                'limit'   => [ 'type' => 'number', 'label' => 'Number of tutors', 'default' => 8, 'min' => 1, 'max' => 12 ],
            ],
            // DATA: tutor query + card TEXT locked (from wp-admin). Owners may
            // restyle the heading + every card element (typography / color /
            // border / radius / shadow) — styling can't change the data.
            'style_targets' => mgk_content_targets( '.mgk-home-tutors', '.mgk-section-head h2', '.mgk-section-head p', [
                'pill'     => [ 'label' => 'Filter Pills', 'selector' => '.mgk-filter-pills .mgk-pill', 'features' => [ 'typography', 'color', 'background', 'border', 'shadow' ] ],
                'card'     => [ 'label' => 'Card Box', 'selector' => '.mgk-tutor-card', 'features' => [ 'background', 'padding', 'border', 'shadow' ] ],
                'avatar'   => [ 'label' => 'Avatar', 'selector' => '.mgk-tutor-card .mgk-avatar', 'features' => [ 'border', 'shadow' ] ],
                'name'     => mgk_style_text( 'Card · Name', '.mgk-tutor-card h3' ),
                'verified' => mgk_style_text( 'Card · Verified line', '.mgk-tutor-card .mgk-check' ),
                'rate'     => mgk_style_text( 'Card · Rate', '.mgk-tutor-card .mgk-rate', [ 'typography', 'color' ] ),
                'subjects' => mgk_style_text( 'Card · Subjects', '.mgk-tutor-meta span' ),
                'bookbtn'  => mgk_style_button( 'Card · Book Trial Button', '.mgk-tutor-card .mgk-btn-accent' ),
                'viewall'  => mgk_style_text( '"View All Tutors" Link', '.mgk-tablet-view-all-tutors' ),
            ] ),
        ],
        [
            'tag'   => 'mgk_why',
            'title' => 'MGK · Why Choose Us',
            'icon'  => 'eicon-check-circle',
            'controls' => [
                'heading' => [ 'type' => 'text',     'label' => 'Heading', 'default' => $s( 'why_heading' ) ],
                'body'    => [ 'type' => 'textarea', 'label' => 'Body',    'default' => $s( 'why_body' ) ],
            ],
            'repeater' => [
                'control'     => 'items',
                'label'       => 'Reasons',
                'partial'     => 'why',
                'map'         => 'pairs',
                'pair_order'  => [ 'title', 'body' ],
                'title_field' => 'title',
                'fields'      => [
                    'title' => [ 'type' => 'text',     'label' => 'Reason title' ],
                    'body'  => [ 'type' => 'textarea', 'label' => 'Reason text' ],
                ],
                'defaults'    => $why_rows,
            ],
            'style_targets' => mgk_content_targets( '.mgk-home-why', '.mgk-section-head h2', '.mgk-section-head p', [
                'cardtitle' => mgk_style_text( 'Card Title', '.mgk-why-card h3' ),
                'cardbody'  => mgk_style_text( 'Card Body',  '.mgk-why-card p' ),
            ] ),
        ],
        [
            'tag'   => 'mgk_spotlight',
            'title' => 'MGK · Tutor Spotlight',
            'icon'  => 'eicon-star',
            'controls' => [
                'eyebrow'       => [ 'type' => 'text', 'label' => 'Eyebrow',              'default' => $s( 'spotlight_eyebrow' ) ],
                'name'          => [ 'type' => 'text', 'label' => 'Tutor name',           'default' => $s( 'spotlight_name' ) ],
                'meta'          => [ 'type' => 'text', 'label' => 'Meta line',            'default' => $s( 'spotlight_meta' ) ],
                'profile_label' => [ 'type' => 'text', 'label' => 'Profile button label', 'default' => $s( 'spotlight_profile_label' ) ],
                'trial_label'   => [ 'type' => 'text', 'label' => 'Trial button label',   'default' => $s( 'spotlight_trial_label' ) ],
            ],
            // Curated single-tutor block (no query grid): allow the marketing text +
            // the two buttons, keep the rest as Section Box.
            'style_targets' => [
                'eyebrow'  => mgk_style_text( 'Eyebrow', '.mgk-home-spotlight .mgk-eyebrow' ),
                'name'     => mgk_style_text( 'Tutor Name', '.mgk-home-spotlight h2' ),
                'profbtn'  => mgk_style_button( 'Profile Button', '.mgk-home-spotlight .mgk-btn-outline' ),
                'trialbtn' => mgk_style_button( 'Trial Button', '.mgk-home-spotlight .mgk-btn-accent' ),
                'section'  => [ 'label' => 'Section Box', 'selector' => '.mgk-home-spotlight', 'features' => [ 'background', 'padding', 'margin', 'align' ] ],
            ],
        ],
        [
            'tag'   => 'mgk_results',
            'title' => 'MGK · Success Stories',
            'icon'  => 'eicon-testimonial',
            'controls' => [
                'heading' => [ 'type' => 'text', 'label' => 'Heading', 'default' => $s( 'results_heading' ) ],
            ],
            // DATA: story text locked (from wp-admin). Per-element Style only.
            'style_targets' => mgk_content_targets( '.mgk-home-results', '.mgk-section-head h2', null, [
                'card'      => [ 'label' => 'Story Card Box', 'selector' => '.mgk-story-card', 'features' => [ 'background', 'padding', 'border', 'shadow' ] ],
                'chart'     => [ 'label' => 'Chart Placeholder', 'selector' => '.mgk-story-card .mgk-placeholder', 'features' => [ 'background', 'border', 'shadow' ] ],
                'cardtitle' => mgk_style_text( 'Card · Quote', '.mgk-story-card h3' ),
                'cardmeta'  => mgk_style_text( 'Card · Parent line', '.mgk-story-card .mgk-muted' ),
                'cardlink'  => mgk_style_text( 'Card · "Read story" Link', '.mgk-story-card .mgk-check' ),
            ] ),
        ],
        [
            'tag'   => 'mgk_reviews',
            'title' => 'MGK · Parent Reviews',
            'icon'  => 'eicon-review',
            'controls' => [
                'heading' => [ 'type' => 'text', 'label' => 'Heading',     'default' => $s( 'reviews_heading' ) ],
                'body'    => [ 'type' => 'text', 'label' => 'Sub-heading', 'default' => $s( 'reviews_body' ) ],
            ],
            // DATA: review text locked (from wp-admin). Per-element Style only.
            'style_targets' => mgk_content_targets( '.mgk-home-reviews', '.mgk-section-head h2', '.mgk-section-head p', [
                'card'      => [ 'label' => 'Review Card Box', 'selector' => '.mgk-home-reviews .mgk-card', 'features' => [ 'background', 'padding', 'border', 'shadow' ] ],
                'avatar'    => [ 'label' => 'Card · Avatar', 'selector' => '.mgk-review-avatar', 'features' => [ 'border', 'shadow' ] ],
                'cardname'  => mgk_style_text( 'Card · Name', '.mgk-review-head h3' ),
                'cardrate'  => mgk_style_text( 'Card · Stars / Date', '.mgk-review-head p' ),
                'cardcopy'  => mgk_style_text( 'Card · Review text', '.mgk-home-reviews .mgk-card > p:not(.mgk-check)' ),
                'cardtag'   => mgk_style_text( 'Card · Verified line', '.mgk-home-reviews .mgk-check' ),
                'viewall'   => mgk_style_text( '"View All Reviews" Link', '.mgk-mobile-reviews-link' ),
            ] ),
        ],
        [
            'tag'   => 'mgk_faq',
            'title' => 'MGK · FAQ',
            'icon'  => 'eicon-help-o',
            'controls' => [
                'heading' => [ 'type' => 'text', 'label' => 'Heading', 'default' => $s( 'faq_heading' ) ],
            ],
            'repeater' => [
                'control'     => 'items',
                'label'       => 'Questions',
                'partial'     => 'faq',
                'map'         => 'assoc',
                'title_field' => 'q',
                'fields'      => [
                    'q' => [ 'type' => 'text',     'label' => 'Question' ],
                    'a' => [ 'type' => 'textarea', 'label' => 'Answer' ],
                ],
                'defaults'    => $faq_rows,
            ],
            // FAQ copy is owner-editable (has a repeater) → allow question/answer text.
            'style_targets' => mgk_content_targets( '.mgk-home-faq', '.mgk-section-head h2', null, [
                'question' => mgk_style_text( 'Question', '.mgk-faq-item button span' ),
                'answer'   => mgk_style_text( 'Answer',   '.mgk-faq-answer' ),
            ] ),
        ],
        [
            'tag'   => 'mgk_pricing_teaser',
            'title' => 'MGK · Pricing Teaser',
            'icon'  => 'eicon-price-table',
            'controls' => [
                'heading'          => [ 'type' => 'text',     'label' => 'Heading',          'default' => $s( 'pricing_heading' ) ],
                'body'             => [ 'type' => 'textarea', 'label' => 'Body',             'default' => $s( 'pricing_body' ) ],
                'cta'              => [ 'type' => 'text',     'label' => 'CTA label',        'default' => $s( 'pricing_cta' ) ],
                'calculator_title' => [ 'type' => 'text',     'label' => 'Calculator title', 'default' => $s( 'calculator_title' ) ],
            ],
            // CONTENT copy with an embedded calculator preview.
            'style_targets' => mgk_content_targets( '.mgk-home-pricing', '.mgk-section-head h2', '.mgk-section-head p', [
                'feature' => mgk_style_text( 'Feature Line', '.mgk-home-pricing ul li' ),
                'cta'     => mgk_style_button( 'CTA Button', '.mgk-home-pricing .mgk-btn-outline' ),
                'calctitle' => mgk_style_text( 'Calculator Title', '.mgk-calculator h3' ),
            ] ),
        ],
        [
            'tag'   => 'mgk_press',
            'title' => 'MGK · Press Logos',
            'icon'  => 'eicon-logo',
            'controls' => [
                'label' => [ 'type' => 'text', 'label' => 'Label', 'default' => $s( 'press_label' ) ],
            ],
            'style_targets' => mgk_data_targets( '.mgk-home-press', 'p.mgk-muted' ), // label as heading
        ],
        [
            'tag'   => 'mgk_final_cta',
            'title' => 'MGK · Final CTA',
            'icon'  => 'eicon-call-to-action',
            'controls' => [
                'heading'   => [ 'type' => 'text',     'label' => 'Heading',          'default' => $s( 'final_cta_heading' ) ],
                'body'      => [ 'type' => 'textarea', 'label' => 'Body',             'default' => $s( 'final_cta_body' ) ],
                'primary'   => [ 'type' => 'text',     'label' => 'Primary button',   'default' => $s( 'final_cta_primary' ) ],
                'secondary' => [ 'type' => 'text',     'label' => 'Secondary button', 'default' => $s( 'final_cta_secondary' ) ],
            ],
            'style_targets' => mgk_content_targets( '.mgk-final-cta', '.mgk-final-cta h2', '.mgk-final-cta p', [
                'primarybtn'   => mgk_style_button( 'Primary Button',   '.mgk-final-cta .mgk-btn-light' ),
                'secondarybtn' => mgk_style_button( 'Secondary Button', '.mgk-final-cta .mgk-btn-accent' ),
            ] ),
        ],
        [
            'tag'   => 'mgk_newsletter',
            'title' => 'MGK · Newsletter',
            'icon'  => 'eicon-mail',
            'controls' => [
                'heading' => [ 'type' => 'text', 'label' => 'Heading',     'default' => $s( 'newsletter_heading' ) ],
                'body'    => [ 'type' => 'text', 'label' => 'Body',        'default' => $s( 'newsletter_body' ) ],
                'button'  => [ 'type' => 'text', 'label' => 'Button label', 'default' => $s( 'newsletter_button' ) ],
            ],
            'style_targets' => mgk_content_targets( '.mgk-home-newsletter', 'section h2', 'p.mgk-muted', [
                'input'  => mgk_style_text( 'Email Input', '.mgk-home-newsletter input[type="email"]', [ 'typography', 'color', 'background', 'border' ] ),
                'button' => mgk_style_button( 'Submit Button', '.mgk-home-newsletter .mgk-btn-accent' ),
            ] ),
        ],
    ];

    /* ── S02 Teacher Listing (DATA page, locked layout) ──────────────
       The whole tutor-listing page as ONE widget. Dropping it renders the
       real listing (search bar, filter sidebar, sortable toolbar, result
       grid, pagination, ad banner, related searches) with the live query
       running. Owners may edit the DISPLAY LABELS (Content tab) and restyle
       the filter / toolbar / buttons / ad (Style tab) — but the query, the
       tutor cards and the pagination logic stay LOCKED in PHP.            */
    if ( shortcode_exists( 'mgk_tutor_listing' ) ) {
        $sections[] = [
            'tag'      => 'mgk_tutor_listing',
            'title'    => 'MGK · Teacher Listing',
            'icon'     => 'eicon-posts-grid',
            'controls' => [
                // Search bar labels.
                'label_subject'   => [ 'type' => 'text', 'label' => 'Search · Subject label',   'default' => 'Subject' ],
                'label_level'     => [ 'type' => 'text', 'label' => 'Search · Level label',     'default' => 'Level' ],
                'label_area'      => [ 'type' => 'text', 'label' => 'Search · Area label',      'default' => 'Area / Online' ],
                'label_budget'    => [ 'type' => 'text', 'label' => 'Search · Budget label',    'default' => 'Budget' ],
                'update_label'    => [ 'type' => 'text', 'label' => 'Search · Update button',   'default' => 'Update Search' ],
                // Result toolbar.
                'found_suffix'    => [ 'type' => 'text', 'label' => 'Toolbar · "tutors found" text', 'default' => 'tutors found' ],
                'filter_label'    => [ 'type' => 'text', 'label' => 'Toolbar · Filter button',  'default' => 'Filter' ],
                'sort_label'      => [ 'type' => 'text', 'label' => 'Toolbar · Sort label',     'default' => 'Sort' ],
                // Filter sidebar.
                'filters_heading' => [ 'type' => 'text', 'label' => 'Filters · Heading',        'default' => 'Filters' ],
                'apply_label'     => [ 'type' => 'text', 'label' => 'Filters · Apply (top)',    'default' => 'Apply' ],
                'apply_all_label' => [ 'type' => 'text', 'label' => 'Filters · Apply (bottom)', 'default' => 'Apply Filters' ],
                'clear_label'     => [ 'type' => 'text', 'label' => 'Filters · Clear all',      'default' => 'Clear All Filters' ],
                // Promo banner.
                'ad_title'        => [ 'type' => 'text',     'label' => 'Promo · Title',  'default' => 'Try our PSLE Crash Course' ],
                'ad_body'         => [ 'type' => 'textarea', 'label' => 'Promo · Body',   'default' => '10-week intensive · 92% A* rate · Limited slots' ],
                'ad_button'       => [ 'type' => 'text',     'label' => 'Promo · Button', 'default' => 'Learn More' ],
                'ad_url'          => [ 'type' => 'text',     'label' => 'Promo · Button URL (path)', 'default' => '/psle-crash-course/' ],
                // Related searches.
                'related_heading' => [ 'type' => 'text', 'label' => 'Related searches · Heading', 'default' => 'Related searches' ],
            ],
            // Style: restyle the controls/marketing, NOT the tutor cards (data).
            'style_targets' => [
                'toolbar_title' => mgk_style_text( 'Toolbar Heading', '.mgk-listing-toolbar h1' ),
                'toolbar_sub'   => mgk_style_text( 'Toolbar Sub-text', '.mgk-listing-toolbar p' ),
                'searchbox'     => [ 'label' => 'Search Bar Box', 'selector' => '.mgk-listing-search', 'features' => [ 'background', 'padding', 'border', 'shadow' ] ],
                'searchbtn'     => mgk_style_button( 'Search "Update" Button', '.mgk-listing-search .mgk-btn-accent' ),
                'filterbox'     => [ 'label' => 'Filter Sidebar Box', 'selector' => '.mgk-filter-panel', 'features' => [ 'background', 'padding', 'border', 'shadow' ] ],
                'filterhead'    => mgk_style_text( 'Filter Heading', '.mgk-filter-head h2' ),
                'filterlegend'  => mgk_style_text( 'Filter Group Labels', '.mgk-filter-group legend' ),
                'filteritem'    => mgk_style_text( 'Filter Option Labels', '.mgk-check-row span' ),
                'applybtn'      => mgk_style_button( 'Apply Filters Button', '.mgk-filter-apply-main' ),
                'clearbtn'      => mgk_style_button( 'Clear Filters Button', '.mgk-filter-reset' ),
                'adbox'         => [ 'label' => 'Promo Banner Box', 'selector' => '.mgk-listing-ad', 'features' => [ 'background', 'padding', 'border', 'shadow' ] ],
                'adtitle'       => mgk_style_text( 'Promo Title', '.mgk-listing-ad h2' ),
                'adbtn'         => mgk_style_button( 'Promo Button', '.mgk-listing-ad .mgk-btn-accent' ),
                'related'       => mgk_style_text( 'Related Searches Heading', '.mgk-related-searches h2' ),
                'section'       => [ 'label' => 'Section Box', 'selector' => '.mgk-listing-page', 'features' => [ 'background', 'padding', 'margin' ] ],
            ],
        ];
    }

    /* ── S02 Teacher Listing — SPLIT into 6 editable sub-section widgets ──────
       Owners drag / reorder / hide / style each block independently. The results
       GRID widget keeps the query + cards + pagination LOCKED; the others are
       chrome (labels + Style). All derive from the same URL filters so state can't
       drift. The composite "MGK · Teacher Listing" above still exists for pages
       that want the whole listing in one drop.                                  */
    if ( shortcode_exists( 'mgk_listing_search' ) ) {
        $sections[] = [
            'tag'      => 'mgk_listing_search',
            'title'    => 'MGK Listing · Search Bar',
            'icon'     => 'eicon-search',
            'controls' => [
                'label_subject' => [ 'type' => 'text', 'label' => 'Subject label', 'default' => 'Subject' ],
                'label_level'   => [ 'type' => 'text', 'label' => 'Level label',   'default' => 'Level' ],
                'label_area'    => [ 'type' => 'text', 'label' => 'Area label',    'default' => 'Area / Online' ],
                'label_budget'  => [ 'type' => 'text', 'label' => 'Budget label',  'default' => 'Budget' ],
                'update_label'  => [ 'type' => 'text', 'label' => 'Update button',  'default' => 'Update Search' ],
            ],
            'style_targets' => [
                'box'        => [ 'label' => 'Search Bar Box', 'selector' => '.mgk-listing-search', 'features' => [ 'background', 'padding', 'border', 'shadow' ] ],
                'fieldlabel' => mgk_style_text( 'Field Labels (Subject/Level…)', '.mgk-listing-search-form label span' ),
                'dropdown'   => [ 'label' => 'Dropdowns', 'selector' => '.mgk-listing-search-form select', 'features' => [ 'typography', 'color', 'background', 'width', 'padding', 'border', 'shadow' ] ],
                'button'     => mgk_style_button( '"Update" Button', '.mgk-listing-search .mgk-btn-accent' ),
                'breadcrumb' => mgk_style_text( 'Breadcrumb', '.mgk-breadcrumb' ),
            ],
        ];
    }
    if ( shortcode_exists( 'mgk_listing_toolbar' ) ) {
        $sections[] = [
            'tag'      => 'mgk_listing_toolbar',
            'title'    => 'MGK Listing · Toolbar (count + sort)',
            'icon'     => 'eicon-toggle',
            'controls' => [
                'found_suffix' => [ 'type' => 'text', 'label' => '"tutors found" text', 'default' => 'tutors found' ],
                'filter_label' => [ 'type' => 'text', 'label' => 'Filter button',       'default' => 'Filter' ],
                'sort_label'   => [ 'type' => 'text', 'label' => 'Sort label',          'default' => 'Sort' ],
            ],
            'style_targets' => [
                'title'      => mgk_style_text( 'Heading ("N tutors found")', '.mgk-listing-toolbar h1' ),
                'sub'        => mgk_style_text( 'Sub-text', '.mgk-listing-toolbar p' ),
                'filterbtn'  => mgk_style_button( 'Filter Button', '.mgk-filter-open' ),
                'sortlabel'  => mgk_style_text( 'Sort Label', '.mgk-toolbar-controls > label' ),
                'sortselect' => [ 'label' => 'Sort Dropdown', 'selector' => '.mgk-toolbar-controls select', 'features' => [ 'typography', 'color', 'background', 'border', 'shadow' ] ],
                'viewtoggle' => [ 'label' => 'Grid/List Toggle', 'selector' => '.mgk-view-toggle button', 'features' => [ 'typography', 'color', 'background', 'border', 'shadow' ] ],
                'chip'       => [ 'label' => 'Active Filter Chips', 'selector' => '.mgk-filter-chip', 'features' => [ 'typography', 'color', 'background', 'border', 'shadow' ] ],
                'clearall'   => mgk_style_text( '"Clear all" Link', '.mgk-clear-all' ),
                'section'    => [ 'label' => 'Toolbar Box', 'selector' => '.mgk-listing-toolbar', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
            ],
        ];
    }
    if ( shortcode_exists( 'mgk_listing_filters' ) ) {
        $sections[] = [
            'tag'      => 'mgk_listing_filters',
            'title'    => 'MGK Listing · Filter Sidebar',
            'icon'     => 'eicon-filter',
            'controls' => [
                'filters_heading' => [ 'type' => 'text', 'label' => 'Heading',    'default' => 'Filters' ],
                'apply_label'     => [ 'type' => 'text', 'label' => 'Apply (top)', 'default' => 'Apply' ],
            ],
            'style_targets' => [
                'box'       => [ 'label' => 'Sidebar Box', 'selector' => '.mgk-filter-panel', 'features' => [ 'background', 'padding', 'border', 'shadow' ] ],
                'head'      => mgk_style_text( 'Heading', '.mgk-filter-head h2' ),
                'applyhead' => mgk_style_button( 'Apply (top) Button', '.mgk-filter-apply-head' ),
                'legend'    => mgk_style_text( 'Group Labels', '.mgk-filter-group legend' ),
                'item'      => mgk_style_text( 'Option Labels', '.mgk-check-row span' ),
                'checkbox'  => [ 'label' => 'Checkboxes', 'selector' => '.mgk-check-row input[type="checkbox"]', 'features' => [ 'background', 'border', 'shadow' ] ],
            ],
        ];
    }
    if ( shortcode_exists( 'mgk_listing_results' ) ) {
        $sections[] = [
            'tag'      => 'mgk_listing_results',
            'title'    => 'MGK Listing · Results Grid',
            'icon'     => 'eicon-posts-grid',
            'controls' => [
                'filter_label'    => [ 'type' => 'text', 'label' => 'Filter button text', 'default' => 'Filter' ],
                'related_heading' => [ 'type' => 'text', 'label' => 'Related heading', 'default' => 'Related searches' ],
            ],
            // DATA: query + pagination + card TEXT locked (from wp-admin). Owners may
            // restyle every card element (typography / color / border / radius /
            // shadow) — styling can't change the data.
            'style_targets' => [
                'chipsrow' => [ 'label' => 'Active Filter Row', 'selector' => '.mgk-active-chips', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'chip'     => [ 'label' => 'Active Filter Chips', 'selector' => '.mgk-filter-chip', 'features' => [ 'typography', 'color', 'background', 'border', 'shadow' ] ],
                'emptychip'=> mgk_style_text( 'No Filter Text', '.mgk-chip-empty' ),
                'chipbtn'  => mgk_style_button( 'Filter Button', '.mgk-chip-filter-button' ),
                'card'     => [ 'label' => 'Card Box', 'selector' => '.mgk-result-card', 'features' => [ 'background', 'padding', 'border', 'shadow' ] ],
                'avatar'   => [ 'label' => 'Card · Avatar', 'selector' => '.mgk-result-avatar', 'features' => [ 'border', 'shadow' ] ],
                'name'     => mgk_style_text( 'Card · Name', '.mgk-result-main h2' ),
                'verified' => mgk_style_text( 'Card · Verified line', '.mgk-result-main .mgk-check' ),
                'meta'     => mgk_style_text( 'Card · Rating / Meta', '.mgk-result-main p:not(.mgk-check)' ),
                'rate'     => mgk_style_text( 'Card · Rate', '.mgk-result-rate', [ 'typography', 'color' ] ),
                'tags'     => [ 'label' => 'Card · Subject Tags', 'selector' => '.mgk-result-tags span', 'features' => [ 'typography', 'color', 'background', 'border', 'shadow' ] ],
                'bio'      => mgk_style_text( 'Card · Bio', '.mgk-result-bio' ),
                'viewbtn'  => mgk_style_button( 'Card · View Profile Button', '.mgk-result-actions .mgk-btn-outline' ),
                'bookbtn'  => mgk_style_button( 'Card · Book Trial Button', '.mgk-result-actions .mgk-btn-accent' ),
                'compare'  => mgk_style_text( 'Card · Compare label', '.mgk-compare-check' ),
                'page'     => mgk_style_text( 'Pagination', '.mgk-pagination' ),
                'relatedbox' => [ 'label' => 'Related Searches Box', 'selector' => '.mgk-related-searches', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'relatedhead' => mgk_style_text( 'Related · Heading', '.mgk-related-searches h2' ),
                'relatedlink' => [ 'label' => 'Related · Link Items', 'selector' => '.mgk-related-searches a', 'features' => [ 'typography', 'color', 'background', 'padding', 'border', 'shadow' ] ],
                'section'  => [ 'label' => 'Results Box', 'selector' => '.mgk-listing-results', 'features' => [ 'background', 'padding', 'margin' ] ],
            ],
        ];
    }
    if ( shortcode_exists( 'mgk_listing_promo' ) ) {
        $sections[] = [
            'tag'      => 'mgk_listing_promo',
            'title'    => 'MGK Listing · Promo Banner',
            'icon'     => 'eicon-megaphone',
            'controls' => [
                'ad_title'  => [ 'type' => 'text',     'label' => 'Title',  'default' => 'Try our PSLE Crash Course' ],
                'ad_body'   => [ 'type' => 'textarea', 'label' => 'Body',   'default' => '10-week intensive · 92% A* rate · Limited slots' ],
                'ad_button' => [ 'type' => 'text',     'label' => 'Button', 'default' => 'Learn More' ],
                'ad_url'    => [ 'type' => 'text',     'label' => 'Button URL (path)', 'default' => '/psle-crash-course/' ],
            ],
            'style_targets' => mgk_content_targets( '.mgk-listing-ad', '.mgk-listing-ad h2', '.mgk-listing-ad p', [
                'button' => mgk_style_button( 'Button', '.mgk-listing-ad .mgk-btn-accent' ),
            ] ),
        ];
    }
    if ( shortcode_exists( 'mgk_listing_related' ) ) {
        $sections[] = [
            'tag'      => 'mgk_listing_related',
            'title'    => 'MGK Listing · Related Searches',
            'icon'     => 'eicon-tags',
            'controls' => [
                'related_heading' => [ 'type' => 'text', 'label' => 'Heading', 'default' => 'Related searches' ],
            ],
            'style_targets' => mgk_data_targets( '.mgk-related-searches', '.mgk-related-searches h2' ),
        ];
    }

    /* ── Lead capture form (useful on landing pages) ─────── */
    if ( shortcode_exists( 'mgk_request_form' ) ) {
        $sections[] = [
            'tag'      => 'mgk_request_form',
            'title'    => 'MGK · Request a Tutor (form)',
            'icon'     => 'eicon-form-horizontal',
            'controls' => [],
            // Form has submission logic — limit to box-level styling.
            'style_targets' => mgk_data_targets( '.mgk-section' ),
        ];
    }

    if ( shortcode_exists( 'mgk_tutor_apply' ) ) {
        $sections[] = [
            'tag'      => 'mgk_tutor_apply',
            'title'    => 'MGK Tutor · Apply Wizard',
            'icon'     => 'eicon-form-vertical',
            'controls' => [
                'hidden'                 => [ 'type' => 'switcher', 'label' => 'Hide entire section' ],
                'preview_step'           => [ 'type' => 'select', 'label' => 'Preview step', 'default' => '3', 'options' => [
                    '1' => '1 · Basic info',
                    '2' => '2 · Subjects / levels',
                    '3' => '3 · Education',
                    '4' => '4 · Experience',
                    '5' => '5 · Bank / PayNow',
                    '6' => '6 · Docs upload',
                ] ],
                'hide_topbar'            => [ 'type' => 'switcher', 'label' => 'Hide topbar' ],
                'topbar_logo'            => [ 'type' => 'text', 'label' => 'Topbar logo text', 'default' => '[LOGO]' ],
                'topbar_title'           => [ 'type' => 'text', 'label' => 'Topbar title', 'default' => 'Become a Tutor' ],
                'topbar_help'            => [ 'type' => 'text', 'label' => 'Topbar help', 'default' => 'NEED HELP? SUPPORT' ],
                'hide_stepbar'           => [ 'type' => 'switcher', 'label' => 'Hide stepbar' ],
                'stepbar_tag'            => [ 'type' => 'text', 'label' => 'Stepbar tag', 'default' => 'STEP BAR' ],
                'mobile_title'           => [ 'type' => 'text', 'label' => 'Mobile title', 'default' => 'Apply to teach' ],
                'step_prefix'            => [ 'type' => 'text', 'label' => 'Step prefix', 'default' => 'STEP' ],
                'step_of_label'          => [ 'type' => 'text', 'label' => 'Step of label', 'default' => 'OF' ],
                'hide_preview'           => [ 'type' => 'switcher', 'label' => 'Hide desktop preview rail' ],
                'preview_title'          => [ 'type' => 'text', 'label' => 'Preview title', 'default' => 'Live profile preview' ],
                'preview_completeness'   => [ 'type' => 'text', 'label' => 'Preview completeness label', 'default' => 'COMPLETENESS' ],
                'preview_note'           => [ 'type' => 'textarea', 'label' => 'Preview note', 'default' => 'Submit unlocks at 100%. On submit -> status UNDER_REVIEW.' ],
                'hide_education'         => [ 'type' => 'switcher', 'label' => 'Hide education section' ],
                'sec_education'          => [ 'type' => 'text', 'label' => 'Education section tag', 'default' => 'STEP 3 Education' ],
                'education_title'        => [ 'type' => 'text', 'label' => 'Mobile education title', 'default' => 'Your education' ],
                'education_desktop_title'=> [ 'type' => 'text', 'label' => 'Desktop education title', 'default' => 'Step 3 · Education' ],
                'education_intro'        => [ 'type' => 'textarea', 'label' => 'Education intro', 'default' => 'UPLOAD YOUR HIGHEST QUALIFICATION - OCR PRE-FILLS, YOU VERIFY.' ],
                'degree_upload_title'    => [ 'type' => 'text', 'label' => 'Degree upload desktop title', 'default' => 'Drag degree certificate here' ],
                'degree_upload_mobile'   => [ 'type' => 'text', 'label' => 'Degree upload mobile title', 'default' => 'UPLOAD DEGREE CERT' ],
                'degree_upload_hint'     => [ 'type' => 'textarea', 'label' => 'Degree upload hint', 'default' => 'PDF / JPG / PNG · MAX 10MB · OCR AUTO-EXTRACT' ],
                'choose_file_label'      => [ 'type' => 'text', 'label' => 'Choose file button', 'default' => 'Choose file' ],
                'ocr_tag'                => [ 'type' => 'text', 'label' => 'OCR extracted tag', 'default' => '+ OCR EXTRACTED · VERIFY & EDIT' ],
                'university_label'       => [ 'type' => 'text', 'label' => 'University label', 'default' => 'UNIVERSITY' ],
                'degree_label'           => [ 'type' => 'text', 'label' => 'Degree label', 'default' => 'DEGREE' ],
                'year_label'             => [ 'type' => 'text', 'label' => 'Year label', 'default' => 'YEAR' ],
                'hide_docs_preview'      => [ 'type' => 'switcher', 'label' => 'Hide docs preview' ],
                'sec_docs'               => [ 'type' => 'text', 'label' => 'Docs section tag', 'default' => 'STEP 6 Docs' ],
                'docs_title'             => [ 'type' => 'text', 'label' => 'Mobile docs title', 'default' => 'Identity documents' ],
                'docs_desktop_title'     => [ 'type' => 'text', 'label' => 'Desktop docs title', 'default' => 'Identity · NRIC OCR (step 6 preview)' ],
                'nric_upload_label'      => [ 'type' => 'text', 'label' => 'NRIC upload label', 'default' => 'UPLOAD NRIC (FRONT)' ],
                'nric_scan_label'        => [ 'type' => 'text', 'label' => 'NRIC scan label', 'default' => 'NRIC front scan' ],
                'nric_extracting'        => [ 'type' => 'text', 'label' => 'NRIC extracting label', 'default' => '+ OCR EXTRACTING... 2S' ],
                'other_docs_label'       => [ 'type' => 'textarea', 'label' => 'Other docs label', 'default' => '+ OTHER DOCS: TESTIMONIALS, TRANSCRIPT (OPTIONAL)' ],
                'name_label'             => [ 'type' => 'text', 'label' => 'NRIC name label', 'default' => 'NAME' ],
                'dob_label'              => [ 'type' => 'text', 'label' => 'NRIC DOB label', 'default' => 'DOB' ],
                'nric_label'             => [ 'type' => 'text', 'label' => 'NRIC label', 'default' => 'NRIC' ],
                'hide_consent'           => [ 'type' => 'switcher', 'label' => 'Hide background consent' ],
                'consent_text'           => [ 'type' => 'textarea', 'label' => 'Background consent', 'default' => 'Background check consent (BR-19): submitting authorises Margick to run a criminal-record / identity check before activation.' ],
                'bank_warning'           => [ 'type' => 'textarea', 'label' => 'Bank warning', 'default' => '+ BANK ACCOUNT NO. INVALID - RE-CHECK (STEP 5)' ],
                'back_label'             => [ 'type' => 'text', 'label' => 'Back button', 'default' => '< Back' ],
                'continue_label'         => [ 'type' => 'text', 'label' => 'Continue button', 'default' => 'Save & continue >' ],
                'autosave_label'         => [ 'type' => 'text', 'label' => 'Autosave label', 'default' => 'AUTO-SAVED 8S AGO' ],
                'mobile_autosave_label'  => [ 'type' => 'text', 'label' => 'Mobile autosave label', 'default' => 'Progress auto-saved · resume anytime' ],
                'footer_text'            => [ 'type' => 'text', 'label' => 'Footer text', 'default' => '© 2026 Margick · Tutor onboarding · MOE Registered partner' ],
                'basic_title'            => [ 'type' => 'text', 'label' => 'Step 1 title', 'default' => 'Basic info' ],
                'basic_body'             => [ 'type' => 'textarea', 'label' => 'Step 1 body', 'default' => 'Tell us who you are. Email and phone will be verified before activation.' ],
                'subjects_title'         => [ 'type' => 'text', 'label' => 'Step 2 title', 'default' => 'Subjects / levels' ],
                'subjects_body'          => [ 'type' => 'textarea', 'label' => 'Step 2 body', 'default' => 'Choose the subjects, levels and exam tracks you can teach confidently.' ],
                'experience_title'       => [ 'type' => 'text', 'label' => 'Step 4 title', 'default' => 'Experience' ],
                'experience_body'        => [ 'type' => 'textarea', 'label' => 'Step 4 body', 'default' => 'Add tutoring history, school experience, achievements and availability notes.' ],
                'payout_title'           => [ 'type' => 'text', 'label' => 'Step 5 title', 'default' => 'Bank / PayNow' ],
                'payout_body'            => [ 'type' => 'textarea', 'label' => 'Step 5 body', 'default' => 'Add payout details. PayNow or bank account will be verified before first payout.' ],
                'docs_title_step'        => [ 'type' => 'text', 'label' => 'Step 6 title', 'default' => 'Docs upload' ],
                'docs_body_step'         => [ 'type' => 'textarea', 'label' => 'Step 6 body', 'default' => 'Upload NRIC and certificates. OCR extracts fields, then you verify before submit.' ],
            ],
            'style_targets' => [
                'section'     => [ 'label' => 'Section Box', 'selector' => '.mgk-tutor-apply', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'shell'       => [ 'label' => 'Wizard Shell', 'selector' => '.mgk-tutor-apply__shell', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'topbar'      => [ 'label' => 'Topbar', 'selector' => '.mgk-tutor-apply-topbar', 'features' => [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ],
                'mobiletitle' => [ 'label' => 'Mobile Title Row', 'selector' => '.mgk-tutor-apply-mobile-title', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'steptabs'    => mgk_style_button( 'Step Tabs', '.mgk-tutor-apply-steps a' ),
                'sectag'      => mgk_style_text( 'Section Tags', '.mgk-tutor-apply-sec, .mgk-tutor-apply-steps > span, .mgk-tutor-apply-mobile-title span', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border', 'shadow' ] ),
                'layout'      => [ 'label' => 'Content Layout', 'selector' => '.mgk-tutor-apply-layout', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'heading'     => mgk_style_text( 'Step Headings', '.mgk-tutor-apply h2, .mgk-tutor-apply-mobile-title h1', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'body'        => mgk_style_text( 'Helper Copy', '.mgk-tutor-apply p, .mgk-tutor-apply-upload span, .mgk-tutor-apply-doc-upload span, .mgk-tutor-apply-file-status, .mgk-tutor-apply-other-docs, .mgk-tutor-apply-bank-warning', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'upload'      => [ 'label' => 'Degree Upload Box', 'selector' => '.mgk-tutor-apply-upload', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'uploadtitle' => mgk_style_text( 'Upload Title', '.mgk-tutor-apply-upload strong', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'buttons'     => mgk_style_button( 'Buttons', '.mgk-tutor-apply-upload button, .mgk-tutor-apply-file-control span, .mgk-tutor-apply-doc-control strong, .mgk-tutor-apply-actions a' ),
                'ocrbox'      => [ 'label' => 'OCR Extracted Box', 'selector' => '.mgk-tutor-apply-ocr', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'ocrtag'      => mgk_style_text( 'OCR Tag', '.mgk-tutor-apply-ocr > strong', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'ocrfield'    => [ 'label' => 'OCR Field Cards', 'selector' => '.mgk-tutor-apply-ocr article, .mgk-tutor-apply-doc-upload article', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'ocrvalue'    => mgk_style_text( 'OCR Field Text', '.mgk-tutor-apply-ocr article b, .mgk-tutor-apply-doc-upload article b', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'matchbox'    => mgk_style_text( 'MOE Match Box', '.mgk-tutor-apply-ocr p', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'docsbox'     => [ 'label' => 'Docs Box', 'selector' => '.mgk-tutor-apply-docs', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'scan'        => [ 'label' => 'Document Scan Placeholder', 'selector' => '.mgk-tutor-apply-doc-upload > div', 'features' => [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ],
                'consent'     => mgk_style_text( 'Background Consent', '.mgk-tutor-apply-consent', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'actions'     => [ 'label' => 'Action Row', 'selector' => '.mgk-tutor-apply-actions', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'preview'     => [ 'label' => 'Desktop Preview Rail', 'selector' => '.mgk-tutor-apply-preview', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'previewtext' => mgk_style_text( 'Preview Text', '.mgk-tutor-apply-preview h2, .mgk-tutor-apply-preview strong, .mgk-tutor-apply-preview span, .mgk-tutor-apply-preview em, .mgk-tutor-apply-preview p, .mgk-tutor-apply-preview footer', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'footer'      => mgk_style_text( 'Footer / Autosave', '.mgk-tutor-apply-footer, .mgk-tutor-apply-mobile-autosave, .mgk-tutor-apply-actions span', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
            ],
        ];
    }

    if ( shortcode_exists( 'mgk_tutor_verification' ) ) {
        $sections[] = [
            'tag'      => 'mgk_tutor_verification',
            'title'    => 'MGK Tutor · Verification',
            'icon'     => 'eicon-video-camera',
            'controls' => [
                'preview_variant'     => [ 'type' => 'select', 'label' => 'Preview state', 'default' => '', 'options' => [
                    ''         => 'Use URL / live data',
                    'default'  => 'Demo pending',
                    'rejected' => 'Rejected',
                    'approved' => 'Approved',
                ] ],
                'hide_status'         => [ 'type' => 'switcher', 'label' => 'Hide status header' ],
                'sec_status'          => [ 'type' => 'text', 'label' => 'Status section tag', 'default' => 'SEC 1 Status' ],
                'status_title'        => [ 'type' => 'text', 'label' => 'Status title', 'default' => 'Application under verification' ],
                'status_meta'         => [ 'type' => 'textarea', 'label' => 'Status meta', 'default' => "HI {name} · SUBMITTED {date} · WE'LL NOTIFY BY EMAIL + SMS AT EACH STEP" ],
                'current_state_label' => [ 'type' => 'text', 'label' => 'Current state label', 'default' => 'CURRENT STATE' ],
                'hide_video'          => [ 'type' => 'switcher', 'label' => 'Hide demo video upload' ],
                'video_title'         => [ 'type' => 'text', 'label' => 'Video title', 'default' => 'Upload your demo lesson' ],
                'video_intro'         => [ 'type' => 'textarea', 'label' => 'Video intro', 'default' => 'SHOW YOUR TEACHING STYLE — REVIEWERS APPROVE BEFORE YOU GO LIVE.' ],
                'uploading_label'     => [ 'type' => 'text', 'label' => 'Uploading label', 'default' => 'Uploading...' ],
                'hide_requirements'   => [ 'type' => 'switcher', 'label' => 'Hide video requirements' ],
                'requirements_title'  => [ 'type' => 'text', 'label' => 'Requirements title', 'default' => 'Video requirements (FR-TUTOR-04)' ],
                'requirements_tip'    => [ 'type' => 'textarea', 'label' => 'Requirements tip', 'default' => 'TIP: PICK ONE CONCEPT, TEACH TO CAMERA ~90S. APPROVED VIDEO APPEARS ON YOUR PUBLIC PROFILE (S03 DEMO SLOT).' ],
                'hide_timeline'       => [ 'type' => 'switcher', 'label' => 'Hide verification timeline' ],
                'timeline_title'      => [ 'type' => 'text', 'label' => 'Timeline title', 'default' => 'Verification timeline' ],
                'timeline_meta'       => [ 'type' => 'text', 'label' => 'Timeline meta', 'default' => 'REAL-TIME · FR-TUTOR-05' ],
                'reviewer_label'      => [ 'type' => 'text', 'label' => 'Reviewer message label', 'default' => 'REVIEWER MESSAGE (REQUEST-MORE-INFO VARIANT)' ],
                'reviewer_message'    => [ 'type' => 'textarea', 'label' => 'Reviewer message', 'default' => '"Please re-upload degree cert — page 2 unclear."' ],
                'reviewer_cta'        => [ 'type' => 'text', 'label' => 'Reviewer CTA', 'default' => 'Re-submit →' ],
                'rejected_title'      => [ 'type' => 'text', 'label' => 'Rejected box title', 'default' => 'REJECTED VARIANT' ],
                'rejected_body'       => [ 'type' => 'textarea', 'label' => 'Rejected box body', 'default' => 'REASON SHOWN + APPEAL LINK. RE-APPLY AFTER 30 DAYS. PM TO CONFIRM COOLDOWN.' ],
                'hide_actions'        => [ 'type' => 'switcher', 'label' => 'Hide action panel' ],
                'submit_label'        => [ 'type' => 'text', 'label' => 'Submit button', 'default' => 'Submit demo for review' ],
                'contact_label'       => [ 'type' => 'text', 'label' => 'Contact button', 'default' => 'Contact verification team' ],
                'avg_time'            => [ 'type' => 'textarea', 'label' => 'Average review time', 'default' => 'AVG REVIEW TIME: 2-3 BUSINESS DAYS · PM TO CONFIRM' ],
            ],
            'style_targets' => [
                'section'       => [ 'label' => 'Section Box', 'selector' => '.mgk-tutor-verification', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'shell'         => [ 'label' => 'Verification Shell', 'selector' => '.mgk-tutor-verification__shell', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'statusbox'     => [ 'label' => 'Status Header Box', 'selector' => '.mgk-tutor-verification-status', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'sectags'       => mgk_style_text( 'Section Tags', '.mgk-tutor-verification-sec', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border', 'shadow' ] ),
                'statustitle'   => mgk_style_text( 'Status Title', '.mgk-tutor-verification-status h1', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'statusmeta'    => mgk_style_text( 'Status Meta', '.mgk-tutor-verification-status p', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'statebox'      => [ 'label' => 'Current State Box', 'selector' => '.mgk-tutor-verification-status aside', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'statetext'     => mgk_style_text( 'Current State Text', '.mgk-tutor-verification-status aside span, .mgk-tutor-verification-status aside strong', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'layout'        => [ 'label' => 'Desktop Layout Grid', 'selector' => '.mgk-tutor-verification-grid', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'video'         => [ 'label' => 'Demo Video Section', 'selector' => '.mgk-tutor-verification-video', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'videoheading'  => mgk_style_text( 'Video Heading', '.mgk-tutor-verification-video h2', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'videocopy'     => mgk_style_text( 'Video Helper Copy', '.mgk-tutor-verification-video > p', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'uploadbox'     => [ 'label' => 'Upload Placeholder Box', 'selector' => '.mgk-tutor-verification-upload', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'uploadmedia'   => [ 'label' => 'Upload Stripe Area', 'selector' => '.mgk-tutor-verification-upload > div', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'uploadchip'    => mgk_style_text( 'Uploading Chip', '.mgk-tutor-verification-upload > div strong', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border', 'shadow' ] ),
                'progressbar'   => [ 'label' => 'Progress Bar', 'selector' => '.mgk-tutor-verification-upload > span, .mgk-tutor-verification-upload > span b', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'uploadmeta'    => mgk_style_text( 'Upload Metadata', '.mgk-tutor-verification-upload em', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'reqbox'        => [ 'label' => 'Requirements Box', 'selector' => '.mgk-tutor-verification-req', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'reqtitle'      => mgk_style_text( 'Requirements Title', '.mgk-tutor-verification-req h2', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'reqcards'      => [ 'label' => 'Requirement Cards', 'selector' => '.mgk-tutor-verification-req-grid article', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow', 'hover' ] ],
                'reqcardtext'   => mgk_style_text( 'Requirement Card Text', '.mgk-tutor-verification-req-grid strong, .mgk-tutor-verification-req-grid span', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'reqtip'        => mgk_style_text( 'Requirement Tip', '.mgk-tutor-verification-req p', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'timelinebox'   => [ 'label' => 'Timeline Box', 'selector' => '.mgk-tutor-verification-timeline', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'timelinehead'  => mgk_style_text( 'Timeline Heading', '.mgk-tutor-verification-timeline h2, .mgk-tutor-verification-timeline > p', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'timelinerows'  => [ 'label' => 'Timeline Rows', 'selector' => '.mgk-tutor-verification-timeline li', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'timelineactive'=> [ 'label' => 'Active Timeline Row', 'selector' => '.mgk-tutor-verification-timeline li.is-active', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'timelinetext'  => mgk_style_text( 'Timeline Text', '.mgk-tutor-verification-timeline li strong, .mgk-tutor-verification-timeline li em', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'reviewerbox'   => [ 'label' => 'Reviewer Message Box', 'selector' => '.mgk-tutor-verification-message', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'reviewertext'  => mgk_style_text( 'Reviewer Message Text', '.mgk-tutor-verification-message strong, .mgk-tutor-verification-message p, .mgk-tutor-verification-message a', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'rejectedbox'   => [ 'label' => 'Rejected Variant Box', 'selector' => '.mgk-tutor-verification-rejected', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'rejectedtext'  => mgk_style_text( 'Rejected Variant Text', '.mgk-tutor-verification-rejected strong, .mgk-tutor-verification-rejected p', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'actions'       => [ 'label' => 'Action Panel Box', 'selector' => '.mgk-tutor-verification-actions', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'buttons'       => mgk_style_button( 'Action Buttons', '.mgk-tutor-verification-actions a' ),
                'avgtime'       => mgk_style_text( 'Average Review Time', '.mgk-tutor-verification-actions span', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
            ],
        ];
    }

    if ( shortcode_exists( 'mgk_tutor_dashboard' ) ) {
        $sections[] = [
            'tag'      => 'mgk_tutor_dashboard',
            'title'    => 'MGK Tutor · Dashboard',
            'icon'     => 'eicon-dashboard',
            'controls' => [
                'hidden'            => [ 'type' => 'switcher', 'label' => 'Hide entire dashboard' ],
                'hide_mobilebar'    => [ 'type' => 'switcher', 'label' => 'Hide mobile app bar' ],
                'mobile_greeting'   => [ 'type' => 'text', 'label' => 'Mobile greeting', 'default' => 'Hi, Ms Lee 👋' ],
                'mobile_tools'      => [ 'type' => 'text', 'label' => 'Mobile toolbar right', 'default' => '🔔3 · ☰' ],
                'mobile_job_title'  => [ 'type' => 'text', 'label' => 'Mobile job heading', 'default' => 'New job · 1 proposal' ],
                'hide_mobile_nav'   => [ 'type' => 'switcher', 'label' => 'Hide mobile bottom nav' ],
                'mobile_nav_home'   => [ 'type' => 'text', 'label' => 'Mobile nav home', 'default' => 'Home' ],
                'mobile_nav_schedule' => [ 'type' => 'text', 'label' => 'Mobile nav schedule', 'default' => 'Schedule' ],
                'mobile_nav_log'    => [ 'type' => 'text', 'label' => 'Mobile nav log', 'default' => '+ Log' ],
                'mobile_nav_earn'   => [ 'type' => 'text', 'label' => 'Mobile nav earn', 'default' => '$ Earn' ],
                'mobile_nav_more'   => [ 'type' => 'text', 'label' => 'Mobile nav more', 'default' => 'More' ],
                'hide_welcome'      => [ 'type' => 'switcher', 'label' => 'Hide welcome header' ],
                'sec_welcome'       => [ 'type' => 'text', 'label' => 'Welcome section tag', 'default' => 'W1 Welcome' ],
                'welcome_prefix'    => [ 'type' => 'text', 'label' => 'Welcome prefix', 'default' => 'Welcome back,' ],
                'log_lesson_label'  => [ 'type' => 'text', 'label' => 'Log lesson button', 'default' => '+ Log a lesson' ],
                'schedule_label'    => [ 'type' => 'text', 'label' => 'Schedule button', 'default' => 'View schedule' ],
                'hide_job'          => [ 'type' => 'switcher', 'label' => 'Hide W3 Job inbox' ],
                'sec_job'           => [ 'type' => 'text', 'label' => 'Job inbox tag', 'default' => 'W3 · Job inbox' ],
                'accept_label'      => [ 'type' => 'text', 'label' => 'Accept button', 'default' => 'Accept proposal' ],
                'decline_label'     => [ 'type' => 'text', 'label' => 'Decline button', 'default' => 'Decline (reason ▾)' ],
                'decline_note'      => [ 'type' => 'textarea', 'label' => 'Decline helper note', 'default' => 'DECLINE ASKS A REASON (TOO FAR / CLASH / RATE) · FEEDS RE-MATCH.' ],
                'hide_today'        => [ 'type' => 'switcher', 'label' => 'Hide W2 Today schedule' ],
                'sec_today'         => [ 'type' => 'text', 'label' => 'Today tag', 'default' => 'W2 · Today’s schedule' ],
                'hide_week'         => [ 'type' => 'switcher', 'label' => 'Hide W4 Next 7 days' ],
                'sec_week'          => [ 'type' => 'text', 'label' => 'Week tag', 'default' => 'W4 · Next 7 days' ],
                'week_note'         => [ 'type' => 'text', 'label' => 'Week note', 'default' => '12 LESSONS BOOKED THIS WEEK' ],
                'hide_logs'         => [ 'type' => 'switcher', 'label' => 'Hide W5 Pending logs' ],
                'sec_logs'          => [ 'type' => 'text', 'label' => 'Logs tag', 'default' => 'W5 · Pending lesson logs' ],
                'hide_earnings'     => [ 'type' => 'switcher', 'label' => 'Hide W6 Earnings' ],
                'sec_earnings'      => [ 'type' => 'text', 'label' => 'Earnings tag', 'default' => 'W6 · Monthly earnings' ],
                'hide_payout'       => [ 'type' => 'switcher', 'label' => 'Hide W7 Payout' ],
                'sec_payout'        => [ 'type' => 'text', 'label' => 'Payout tag', 'default' => 'W7 · Payout status' ],
                'hide_leaderboard'  => [ 'type' => 'switcher', 'label' => 'Hide W8 Leaderboard' ],
                'sec_leaderboard'   => [ 'type' => 'text', 'label' => 'Leaderboard tag', 'default' => 'W8 · Leaderboard' ],
                'leaderboard_note'  => [ 'type' => 'textarea', 'label' => 'Leaderboard note', 'default' => 'REGION · TOP 4% · PM TO CONFIRM METRIC' ],
                'hide_ratings'      => [ 'type' => 'switcher', 'label' => 'Hide W9 Ratings' ],
                'sec_ratings'       => [ 'type' => 'text', 'label' => 'Ratings tag', 'default' => 'W9 · Ratings received' ],
                'hide_messages'     => [ 'type' => 'switcher', 'label' => 'Hide W10 Messages' ],
                'sec_messages'      => [ 'type' => 'text', 'label' => 'Messages tag', 'default' => 'W10 · Messages (2)' ],
                'hide_profile'      => [ 'type' => 'switcher', 'label' => 'Hide W11 Profile completeness' ],
                'sec_profile'       => [ 'type' => 'text', 'label' => 'Profile tag', 'default' => 'W11 · Profile completeness' ],
                'hide_quick'        => [ 'type' => 'switcher', 'label' => 'Hide W12 Quick actions' ],
                'sec_quick'         => [ 'type' => 'text', 'label' => 'Quick actions tag', 'default' => 'W12 · Quick actions' ],
            ],
            'style_targets' => [
                'section'       => [ 'label' => 'Section Box', 'selector' => '.mgk-tutor-dash', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'shell'         => [ 'label' => 'Dashboard Shell', 'selector' => '.mgk-tutor-dash__shell', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'mobilebar'     => [ 'label' => 'Mobile App Bar', 'selector' => '.mgk-tutor-dash-mobilebar', 'features' => [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'mobilejobhead' => [ 'label' => 'Mobile Job Heading', 'selector' => '.mgk-tutor-dash-mobile-job-head', 'features' => [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'mobilenav'     => [ 'label' => 'Mobile Bottom Nav', 'selector' => '.mgk-tutor-dash-mobile-nav, .mgk-tutor-dash-mobile-nav a', 'features' => [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'welcome'       => [ 'label' => 'Welcome Header Box', 'selector' => '.mgk-tutor-dash-welcome', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'welcometitle'  => mgk_style_text( 'Welcome Title', '.mgk-tutor-dash-welcome h1', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'welcomemeta'   => mgk_style_text( 'Welcome Meta', '.mgk-tutor-dash-welcome p', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'welcomeactions'=> mgk_style_button( 'Welcome Buttons', '.mgk-tutor-dash-welcome nav a' ),
                'grid'          => [ 'label' => 'Widget Grid', 'selector' => '.mgk-tutor-dash-grid', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'cards'         => [ 'label' => 'All Widget Cards', 'selector' => '.mgk-tutor-dash-card', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow', 'hover' ] ],
                'sectags'       => mgk_style_text( 'Widget Tags', '.mgk-tutor-dash-sec', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border', 'shadow' ] ),
                'cardtext'      => mgk_style_text( 'Card Text', '.mgk-tutor-dash-card h2, .mgk-tutor-dash-card p, .mgk-tutor-dash-card small, .mgk-tutor-dash-card em, .mgk-tutor-dash-card span, .mgk-tutor-dash-card b', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'jobbox'        => [ 'label' => 'Job Inbox Box', 'selector' => '.mgk-tutor-dash-job', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'jobtitle'      => mgk_style_text( 'Job Inbox Title / SLA', '.mgk-tutor-dash-job h2, .mgk-tutor-dash-job strong, .mgk-tutor-dash-job > b', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'jobbuttons'    => mgk_style_button( 'Job Inbox Buttons', '.mgk-tutor-dash-job a' ),
                'todaybox'      => [ 'label' => 'Today Schedule Box', 'selector' => '.mgk-tutor-dash-today', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'todayitems'    => [ 'label' => 'Today Lesson Items', 'selector' => '.mgk-tutor-dash-today > div', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'weekbox'       => [ 'label' => 'Next 7 Days Box', 'selector' => '.mgk-tutor-dash-week', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'weekcells'     => [ 'label' => 'Calendar Cells', 'selector' => '.mgk-tutor-dash-week > div span', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'logsbox'       => [ 'label' => 'Pending Logs Box', 'selector' => '.mgk-tutor-dash-logs', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'logitems'      => [ 'label' => 'Log Items', 'selector' => '.mgk-tutor-dash-logs p', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'earningsbox'   => [ 'label' => 'Monthly Earnings Box', 'selector' => '.mgk-tutor-dash-earnings', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'earningvalue'  => mgk_style_text( 'Earnings Value', '.mgk-tutor-dash-earnings strong', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'chart'         => [ 'label' => 'Mini Chart', 'selector' => '.mgk-tutor-dash-earnings div', 'features' => [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ],
                'payoutbox'     => [ 'label' => 'Payout Status Box', 'selector' => '.mgk-tutor-dash-payout', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'payoutinner'   => [ 'label' => 'Payout Dark Panel', 'selector' => '.mgk-tutor-dash-payout div', 'features' => [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ],
                'leaderbox'     => [ 'label' => 'Leaderboard Box', 'selector' => '.mgk-tutor-dash-leaderboard', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'leaderrows'    => [ 'label' => 'Leaderboard Rows', 'selector' => '.mgk-tutor-dash-leaderboard p', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'ratingsbox'    => [ 'label' => 'Ratings Box', 'selector' => '.mgk-tutor-dash-ratings', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'ratingvalue'   => mgk_style_text( 'Rating Value', '.mgk-tutor-dash-ratings strong', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'quote'         => [ 'label' => 'Rating Quote', 'selector' => '.mgk-tutor-dash-ratings blockquote', 'features' => [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ],
                'messagesbox'   => [ 'label' => 'Messages Box', 'selector' => '.mgk-tutor-dash-messages', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'messagerows'   => [ 'label' => 'Message Rows', 'selector' => '.mgk-tutor-dash-messages p', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'profilebox'    => [ 'label' => 'Profile Completeness Box', 'selector' => '.mgk-tutor-dash-profile', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'profilebar'    => [ 'label' => 'Profile Progress Bar', 'selector' => '.mgk-tutor-dash-profile div, .mgk-tutor-dash-profile div span', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'quickbox'      => [ 'label' => 'Quick Actions Box', 'selector' => '.mgk-tutor-dash-quick', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'quickbuttons'  => mgk_style_button( 'Quick Action Buttons', '.mgk-tutor-dash-quick a' ),
            ],
        ];
    }

    if ( shortcode_exists( 'mgk_tutor_lesson_log' ) ) {
        $sections[] = [
            'tag'      => 'mgk_tutor_lesson_log',
            'title'    => 'MGK Tutor · Lesson Log',
            'icon'     => 'eicon-form-horizontal',
            'controls' => [
                'hidden'           => [ 'type' => 'switcher', 'label' => 'Hide entire lesson log' ],
                'hide_topbar'      => [ 'type' => 'switcher', 'label' => 'Hide top bar' ],
                'back_label'       => [ 'type' => 'text', 'label' => 'Back label', 'default' => '‹ Lesson log' ],
                'entry_label'      => [ 'type' => 'text', 'label' => 'Entry label', 'default' => '' ],
                'autosave_label'   => [ 'type' => 'text', 'label' => 'Top autosave status', 'default' => '• Saved 12s' ],
                'hide_header'      => [ 'type' => 'switcher', 'label' => 'Hide lesson header' ],
                'sec_lesson'       => [ 'type' => 'text', 'label' => 'Lesson header tag', 'default' => 'SEC 1 Lesson' ],
                'package_label'    => [ 'type' => 'text', 'label' => 'Package label', 'default' => 'PACKAGE' ],
                'hide_attendance'  => [ 'type' => 'switcher', 'label' => 'Hide attendance' ],
                'sec_attendance'   => [ 'type' => 'text', 'label' => 'Attendance tag', 'default' => 'SEC 2 Attendance' ],
                'attendance_title' => [ 'type' => 'text', 'label' => 'Attendance title', 'default' => 'Attendance' ],
                'hide_notes'       => [ 'type' => 'switcher', 'label' => 'Hide voice note' ],
                'sec_notes'        => [ 'type' => 'text', 'label' => 'Voice note tag', 'default' => 'SEC 3 Voice-Text' ],
                'note_title'       => [ 'type' => 'text', 'label' => 'Voice note title', 'default' => 'Lesson note' ],
                'recording_label'  => [ 'type' => 'text', 'label' => 'Recording chip', 'default' => '▾ Recording…' ],
                'transcript_label' => [ 'type' => 'textarea', 'label' => 'Transcript label', 'default' => '• TRANSCRIBING · AUTO-SORTS INTO FIELDS' ],
                'note_footer'      => [ 'type' => 'textarea', 'label' => 'Voice note footer', 'default' => 'EACH FIELD EDITABLE — VOICE FILLS A DRAFT, TUTOR CORRECTS.' ],
                'hide_photos'      => [ 'type' => 'switcher', 'label' => 'Hide photos' ],
                'sec_photos'       => [ 'type' => 'text', 'label' => 'Photos tag', 'default' => 'SEC 4 Photos' ],
                'photos_title'     => [ 'type' => 'text', 'label' => 'Photos title', 'default' => 'Photos (1/3)' ],
                'photo_existing'   => [ 'type' => 'text', 'label' => 'Existing photo slot', 'default' => '▣' ],
                'photo_add_label'  => [ 'type' => 'text', 'label' => 'Photo add slot', 'default' => '+' ],
                'photo_plus_label' => [ 'type' => 'text', 'label' => 'Photo plus slot', 'default' => '+' ],
                'photos_note'      => [ 'type' => 'textarea', 'label' => 'Photos note', 'default' => 'MAX 2 · ≤200KB EACH · AUTO-COMPRESS' ],
                'hide_save'        => [ 'type' => 'switcher', 'label' => 'Hide autosave panel' ],
                'sec_save'         => [ 'type' => 'text', 'label' => 'Autosave tag', 'default' => '5' ],
                'autosave_title'   => [ 'type' => 'text', 'label' => 'Autosave title', 'default' => 'Draft auto-saved every 30s · resume from dashboard if you close' ],
                'autosave_meta'    => [ 'type' => 'text', 'label' => 'Autosave meta', 'default' => '' ],
                'hide_sla'         => [ 'type' => 'switcher', 'label' => 'Hide SLA panel' ],
                'sla_title'        => [ 'type' => 'text', 'label' => 'SLA title', 'default' => '' ],
                'sla_body'         => [ 'type' => 'text', 'label' => 'SLA body', 'default' => 'SUBMIT IN 24H (BY TUE 16:00)' ],
                'hide_actions'     => [ 'type' => 'switcher', 'label' => 'Hide submit buttons' ],
                'submit_label'     => [ 'type' => 'text', 'label' => 'Submit button', 'default' => 'Submit log' ],
                'draft_label'      => [ 'type' => 'text', 'label' => 'Draft button', 'default' => 'Save draft & close' ],
            ],
            'style_targets' => [
                'section'       => [ 'label' => 'Section Box', 'selector' => '.mgk-lesson-log', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'shell'         => [ 'label' => 'Log Shell', 'selector' => '.mgk-lesson-log__shell', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'topbar'        => [ 'label' => 'Top Bar Box', 'selector' => '.mgk-lesson-log-topbar', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'topbartext'    => mgk_style_text( 'Top Bar Text', '.mgk-lesson-log-topbar a, .mgk-lesson-log-topbar strong, .mgk-lesson-log-topbar span', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'headerbox'     => [ 'label' => 'Lesson Header Box', 'selector' => '.mgk-lesson-log-head', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'lessontitle'   => mgk_style_text( 'Lesson Title', '.mgk-lesson-log-head h1', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'lessonmeta'    => mgk_style_text( 'Lesson Meta', '.mgk-lesson-log-head p', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'packagebox'    => [ 'label' => 'Package Box', 'selector' => '.mgk-lesson-log-head aside', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'packagetext'   => mgk_style_text( 'Package Text', '.mgk-lesson-log-head aside span, .mgk-lesson-log-head aside strong', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'sectags'       => mgk_style_text( 'Section Tags', '.mgk-lesson-log-sec', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border', 'shadow' ] ),
                'layout'        => [ 'label' => 'Two-column Layout', 'selector' => '.mgk-lesson-log-layout', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'cards'         => [ 'label' => 'All White Cards', 'selector' => '.mgk-lesson-log-card', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow', 'hover' ] ],
                'cardheads'     => mgk_style_text( 'Card Headings', '.mgk-lesson-log-card h2', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'attendancebox' => [ 'label' => 'Attendance Box', 'selector' => '.mgk-lesson-log-attendance', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'attendancegrid'=> [ 'label' => 'Attendance Button Grid', 'selector' => '.mgk-lesson-log-attendance-grid', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'attendancebtn' => mgk_style_button( 'Attendance Buttons', '.mgk-lesson-log-attendance-grid button' ),
                'attendanceactive' => [ 'label' => 'Selected Attendance Button', 'selector' => '.mgk-lesson-log-attendance-grid button.is-active', 'features' => [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'notesbox'      => [ 'label' => 'Voice Note Box', 'selector' => '.mgk-lesson-log-notes', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'notehead'      => [ 'label' => 'Voice Note Header', 'selector' => '.mgk-lesson-log-notes header', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'recordchip'    => [ 'label' => 'Recording Chip', 'selector' => '.mgk-lesson-log-notes header span', 'features' => [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'transcript'    => [ 'label' => 'Transcript Box', 'selector' => '.mgk-lesson-log-transcript', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'transcripttext'=> mgk_style_text( 'Transcript Text', '.mgk-lesson-log-transcript b', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'transcriptline'=> [ 'label' => 'Transcript Skeleton Lines', 'selector' => '.mgk-lesson-log-transcript i', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'fieldgrid'     => [ 'label' => 'Structured Field Grid', 'selector' => '.mgk-lesson-log-field-grid', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'fieldcards'    => [ 'label' => 'Structured Field Cards', 'selector' => '.mgk-lesson-log-field-grid article', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow', 'hover' ] ],
                'fieldtext'     => mgk_style_text( 'Structured Field Text', '.mgk-lesson-log-field-grid span, .mgk-lesson-log-field-grid strong', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'notefooter'    => mgk_style_text( 'Voice Note Footer', '.mgk-lesson-log-notes > p', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'photosbox'     => [ 'label' => 'Photos Box', 'selector' => '.mgk-lesson-log-photos', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'photogrid'     => [ 'label' => 'Photo Slot Grid', 'selector' => '.mgk-lesson-log-photo-grid', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'photoslots'    => [ 'label' => 'Photo Upload Slots', 'selector' => '.mgk-lesson-log-photo-grid label', 'features' => [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border', 'shadow', 'hover' ] ],
                'photonote'     => mgk_style_text( 'Photos Note', '.mgk-lesson-log-photos p', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'autosavebox'   => [ 'label' => 'Autosave Dark Box', 'selector' => '.mgk-lesson-log-autosave', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'autosavetext'  => mgk_style_text( 'Autosave Text', '.mgk-lesson-log-autosave strong, .mgk-lesson-log-autosave p', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'slabox'        => [ 'label' => 'SLA Box', 'selector' => '.mgk-lesson-log-sla', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'slatext'       => mgk_style_text( 'SLA Text', '.mgk-lesson-log-sla span, .mgk-lesson-log-sla strong', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'actions'       => [ 'label' => 'Submit Bar Box', 'selector' => '.mgk-lesson-log-actions', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'submitbutton'  => mgk_style_button( 'Submit Button', '.mgk-lesson-log-actions button.is-primary' ),
                'draftbutton'   => mgk_style_button( 'Save Draft Button', '.mgk-lesson-log-actions button:not(.is-primary)' ),
            ],
        ];
    }

    if ( shortcode_exists( 'mgk_tutor_earnings' ) ) {
        $sections[] = [
            'tag'      => 'mgk_tutor_earnings',
            'title'    => 'MGK Tutor · Earnings',
            'icon'     => 'eicon-price-table',
            'controls' => [
                'hidden'              => [ 'type' => 'switcher', 'label' => 'Hide entire earnings page' ],
                'hide_topbar'         => [ 'type' => 'switcher', 'label' => 'Hide portal top bar' ],
                'brand_label'         => [ 'type' => 'text', 'label' => 'Portal label', 'default' => '[LOGO] Tutor Portal · Earnings' ],
                'nav_dashboard'       => [ 'type' => 'text', 'label' => 'Nav dashboard', 'default' => 'Dashboard' ],
                'nav_earnings'        => [ 'type' => 'text', 'label' => 'Nav earnings', 'default' => 'Earnings' ],
                'nav_schedule'        => [ 'type' => 'text', 'label' => 'Nav schedule', 'default' => 'Schedule' ],
                'nav_profile'         => [ 'type' => 'text', 'label' => 'Nav profile', 'default' => 'Profile' ],
                'hide_summary'        => [ 'type' => 'switcher', 'label' => 'Hide summary' ],
                'sec_summary'         => [ 'type' => 'text', 'label' => 'Summary tag', 'default' => 'SEC 1 Summary' ],
                'page_title'          => [ 'type' => 'text', 'label' => 'Page title', 'default' => 'Earnings' ],
                'month_label'         => [ 'type' => 'text', 'label' => 'Month label', 'default' => 'June 2026' ],
                'prev_month_label'    => [ 'type' => 'text', 'label' => 'Previous month button', 'default' => '‹ May' ],
                'current_month_label' => [ 'type' => 'text', 'label' => 'Current month button', 'default' => 'June 2026' ],
                'next_month_label'    => [ 'type' => 'text', 'label' => 'Next month button', 'default' => 'Jul ›' ],
                'hide_commission'     => [ 'type' => 'switcher', 'label' => 'Hide commission breakdown' ],
                'sec_commission'      => [ 'type' => 'text', 'label' => 'Commission tag', 'default' => '2' ],
                'commission_title'    => [ 'type' => 'text', 'label' => 'Commission title', 'default' => 'Commission breakdown' ],
                'model_a_label'       => [ 'type' => 'text', 'label' => 'Model A button', 'default' => 'Model A' ],
                'model_b_label'       => [ 'type' => 'text', 'label' => 'Model B button', 'default' => 'Model B' ],
                'model_a_title'       => [ 'type' => 'text', 'label' => 'Model A title', 'default' => 'MODEL A · ACTIVE (BR-17)' ],
                'model_a_body'        => [ 'type' => 'textarea', 'label' => 'Model A body', 'default' => 'Agency keeps 50% of first month, balance paid end of month' ],
                'model_a_note'        => [ 'type' => 'textarea', 'label' => 'Model A note', 'default' => 'FIRST-MONTH SPLIT APPLIES TO NEW STUDENT-TUTOR PAIRINGS; SUBSEQUENT MONTHS AT STANDARD RATE. EXACT % & DURATION PM TO CONFIRM.' ],
                'model_b_title'       => [ 'type' => 'text', 'label' => 'Model B title', 'default' => 'MODEL B · ALTERNATE (BR-18)' ],
                'model_b_body'        => [ 'type' => 'textarea', 'label' => 'Model B body', 'default' => 'Tutor take-rate · default 70%' ],
                'model_b_note'        => [ 'type' => 'textarea', 'label' => 'Model B note', 'default' => 'CONFIGURABLE 60-80% (AGENCY-SET). AT 70%: NET = $2,338. SWITCH MODEL = AGENCY APPROVAL.' ],
                'hide_payouts'        => [ 'type' => 'switcher', 'label' => 'Hide payout history' ],
                'sec_payouts'         => [ 'type' => 'text', 'label' => 'Payout tag', 'default' => '3' ],
                'payout_title'        => [ 'type' => 'text', 'label' => 'Payout title', 'default' => 'Payout history' ],
                'pending_label'       => [ 'type' => 'text', 'label' => 'Pending label', 'default' => 'PENDING' ],
                'pending_body'        => [ 'type' => 'text', 'label' => 'Pending payout body', 'default' => '$2,340 · scheduled 30 Jun · PayNow ••• 26' ],
                'hide_ledger'         => [ 'type' => 'switcher', 'label' => 'Hide earnings ledger' ],
                'sec_ledger'          => [ 'type' => 'text', 'label' => 'Ledger tag', 'default' => '4' ],
                'ledger_title'        => [ 'type' => 'text', 'label' => 'Ledger title', 'default' => 'Earnings ledger · June (per lesson)' ],
                'ledger_note'         => [ 'type' => 'textarea', 'label' => 'Ledger note', 'default' => 'NET PER LESSON REFLECTS ACTIVE COMMISSION MODEL. EARNINGS CONFIRMED ONLY AFTER LESSON LOG SUBMITTED (LINKS S22).' ],
                'hide_invoices'       => [ 'type' => 'switcher', 'label' => 'Hide invoices' ],
                'sec_invoices'        => [ 'type' => 'text', 'label' => 'Invoices tag', 'default' => '5' ],
                'invoice_title'       => [ 'type' => 'text', 'label' => 'Invoice title', 'default' => 'Invoices & statements' ],
                'download_all_label'  => [ 'type' => 'text', 'label' => 'Download all button', 'default' => 'Download all (PDF)' ],
                'hide_empty'          => [ 'type' => 'switcher', 'label' => 'Hide empty state' ],
                'empty_title'         => [ 'type' => 'text', 'label' => 'Empty state title', 'default' => 'EMPTY STATE · new tutor' ],
                'empty_body'          => [ 'type' => 'textarea', 'label' => 'Empty state body', 'default' => '"NO EARNINGS YET — COMPLETE YOUR FIRST LESSON & LOG TO SEE PAYOUTS HERE." · CTA — SET AVAILABILITY (S24).' ],
            ],
            'style_targets' => [
                'section'       => [ 'label' => 'Section Box', 'selector' => '.mgk-tutor-earnings', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'shell'         => [ 'label' => 'Earnings Shell', 'selector' => '.mgk-tutor-earnings__shell', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'topbar'        => [ 'label' => 'Portal Top Bar', 'selector' => '.mgk-tutor-earnings-topbar', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'topbartext'    => mgk_style_text( 'Portal Top Bar Text', '.mgk-tutor-earnings-topbar strong, .mgk-tutor-earnings-topbar a', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'summarybox'    => [ 'label' => 'Summary Section Box', 'selector' => '.mgk-tutor-earnings-summary', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'summaryhead'   => mgk_style_text( 'Summary Heading', '.mgk-tutor-earnings-summary h1', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'monthbuttons'  => mgk_style_button( 'Month Switcher Buttons', '.mgk-tutor-earnings-months a' ),
                'kpisgrid'      => [ 'label' => 'Summary KPI Grid', 'selector' => '.mgk-tutor-earnings-kpis', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'kpicards'      => [ 'label' => 'Summary KPI Cards', 'selector' => '.mgk-tutor-earnings-kpis article', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow', 'hover' ] ],
                'kpitext'       => mgk_style_text( 'Summary KPI Text', '.mgk-tutor-earnings-kpis span, .mgk-tutor-earnings-kpis strong', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'splitlayout'   => [ 'label' => 'Commission/Payout Split Layout', 'selector' => '.mgk-tutor-earnings-split', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'panels'        => [ 'label' => 'All Panels', 'selector' => '.mgk-tutor-earnings-panel', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow', 'hover' ] ],
                'sectags'       => mgk_style_text( 'Section Tags', '.mgk-tutor-earnings-sec', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border', 'shadow' ] ),
                'panelheads'    => mgk_style_text( 'Panel Headings', '.mgk-tutor-earnings-panel h2', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'modelbuttons'  => mgk_style_button( 'Model Toggle Buttons', '.mgk-tutor-earnings-commission button' ),
                'modelabox'     => [ 'label' => 'Model A Box', 'selector' => '.mgk-tutor-earnings-model-a', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'modelbbox'     => [ 'label' => 'Model B Box', 'selector' => '.mgk-tutor-earnings-model-b', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'modeltext'     => mgk_style_text( 'Model Text', '.mgk-tutor-earnings-model-a span, .mgk-tutor-earnings-model-a h3, .mgk-tutor-earnings-model-a small, .mgk-tutor-earnings-model-b span, .mgk-tutor-earnings-model-b h3, .mgk-tutor-earnings-model-b small', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'breakdownrows' => [ 'label' => 'Commission Breakdown Rows', 'selector' => '.mgk-tutor-earnings-breakdown p', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'breakdowntext' => mgk_style_text( 'Commission Row Text', '.mgk-tutor-earnings-breakdown span, .mgk-tutor-earnings-breakdown b', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'sliderbox'     => [ 'label' => 'Model B Slider', 'selector' => '.mgk-tutor-earnings-slider, .mgk-tutor-earnings-slider i, .mgk-tutor-earnings-slider i b, .mgk-tutor-earnings-slider strong', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'payoutbox'     => [ 'label' => 'Payout History Box', 'selector' => '.mgk-tutor-earnings-payouts', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'pendingbox'    => [ 'label' => 'Pending Payout Dark Bar', 'selector' => '.mgk-tutor-earnings-pending', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'pendingtext'   => mgk_style_text( 'Pending Payout Text', '.mgk-tutor-earnings-pending span, .mgk-tutor-earnings-pending strong', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'tables'        => [ 'label' => 'Tables Base', 'selector' => '.mgk-tutor-earnings-table', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'tablecells'    => [ 'label' => 'Table Cells', 'selector' => '.mgk-tutor-earnings-table > *', 'features' => [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ],
                'ledgerbox'     => [ 'label' => 'Ledger Box', 'selector' => '.mgk-tutor-earnings-ledger', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'ledgernote'    => mgk_style_text( 'Ledger Note', '.mgk-tutor-earnings-ledger p', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'invoicebox'    => [ 'label' => 'Invoices Box', 'selector' => '.mgk-tutor-earnings-invoices', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'downloadbtn'   => mgk_style_button( 'Download All Button', '.mgk-tutor-earnings-invoices header a' ),
                'invoicegrid'   => [ 'label' => 'Invoice Grid', 'selector' => '.mgk-tutor-earnings-invoices > div', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'invoicecards'  => [ 'label' => 'Invoice Cards', 'selector' => '.mgk-tutor-earnings-invoices article', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow', 'hover' ] ],
                'invoicetext'   => mgk_style_text( 'Invoice Text', '.mgk-tutor-earnings-invoices article span, .mgk-tutor-earnings-invoices article b', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'emptybox'      => [ 'label' => 'Empty State Box', 'selector' => '.mgk-tutor-earnings-empty', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'emptytext'     => mgk_style_text( 'Empty State Text', '.mgk-tutor-earnings-empty strong, .mgk-tutor-earnings-empty p', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
            ],
        ];
    }

    if ( shortcode_exists( 'mgk_tutor_schedule_profile' ) ) {
        $sections[] = [
            'tag'      => 'mgk_tutor_schedule_profile',
            'title'    => 'MGK Tutor · Schedule & Profile',
            'icon'     => 'eicon-calendar',
            'controls' => [
                'hidden'              => [ 'type' => 'switcher', 'label' => 'Hide entire schedule/profile' ],
                'hide_topbar'         => [ 'type' => 'switcher', 'label' => 'Hide top tabs' ],
                'brand_label'         => [ 'type' => 'text', 'label' => 'Portal label', 'default' => '[LOGO] Tutor Portal · Schedule & Profile' ],
                'schedule_tab'        => [ 'type' => 'text', 'label' => 'Schedule tab', 'default' => 'Schedule' ],
                'profile_tab'         => [ 'type' => 'text', 'label' => 'Profile tab', 'default' => 'Profile' ],
                'hide_availability'   => [ 'type' => 'switcher', 'label' => 'Hide weekly availability' ],
                'sec_availability'    => [ 'type' => 'text', 'label' => 'Availability tag', 'default' => 'A · Availability template' ],
                'availability_title'  => [ 'type' => 'text', 'label' => 'Availability title', 'default' => 'Weekly availability (recurring)' ],
                'availability_sub'    => [ 'type' => 'textarea', 'label' => 'Availability subcopy', 'default' => 'SET ONCE · REPEATS EVERY WEEK · POWERS PARENT SLOT PICKER (S10)' ],
                'reset_label'         => [ 'type' => 'text', 'label' => 'Reset button', 'default' => '↻ Reset' ],
                'edit_avail_label'    => [ 'type' => 'text', 'label' => 'Edit availability button', 'default' => 'Edit availability' ],
                'legend_available'    => [ 'type' => 'text', 'label' => 'Legend available', 'default' => 'Available (recurring)' ],
                'legend_block'        => [ 'type' => 'text', 'label' => 'Legend ad-hoc block', 'default' => 'Ad-hoc block' ],
                'legend_off'          => [ 'type' => 'text', 'label' => 'Legend off', 'default' => 'Off' ],
                'hide_block'          => [ 'type' => 'switcher', 'label' => 'Hide ad-hoc block' ],
                'sec_block'           => [ 'type' => 'text', 'label' => 'Ad-hoc block title/tag', 'default' => 'B · Add ad-hoc block (override)' ],
                'block_sub'           => [ 'type' => 'textarea', 'label' => 'Ad-hoc subcopy', 'default' => 'ONE-OFF EXCEPTION ON TOP OF THE RECURRING TEMPLATE — E.G. HOLIDAY, EXAM WEEK.' ],
                'block_date_label'    => [ 'type' => 'text', 'label' => 'Date label', 'default' => 'DATE' ],
                'block_date'          => [ 'type' => 'text', 'label' => 'Date value', 'default' => 'Sat 14 Jun' ],
                'block_type_label'    => [ 'type' => 'text', 'label' => 'Type label', 'default' => 'TYPE' ],
                'block_type'          => [ 'type' => 'text', 'label' => 'Type value', 'default' => 'Block (unavailable)' ],
                'block_from_label'    => [ 'type' => 'text', 'label' => 'From label', 'default' => 'FROM' ],
                'block_from'          => [ 'type' => 'text', 'label' => 'From value', 'default' => '17:00' ],
                'block_to_label'      => [ 'type' => 'text', 'label' => 'To label', 'default' => 'TO' ],
                'block_to'            => [ 'type' => 'text', 'label' => 'To value', 'default' => '21:00' ],
                'add_block_label'     => [ 'type' => 'text', 'label' => 'Add block button', 'default' => 'Add block' ],
                'hide_sync'           => [ 'type' => 'switcher', 'label' => 'Hide S10 sync note' ],
                'sec_sync'            => [ 'type' => 'text', 'label' => 'Sync tag', 'default' => 'C · Sync' ],
                'sync_title'          => [ 'type' => 'text', 'label' => 'Sync title', 'default' => 'Availability feeds the parent slot picker (S10)' ],
                'sync_body'           => [ 'type' => 'textarea', 'label' => 'Sync body', 'default' => 'RECURRING TEMPLATE + AD-HOC BLOCKS RESOLVE INTO BOOKABLE SLOTS IN REAL TIME (FR-TUTOR-09). BOOKED SLOTS AUTO-REMOVED; BLOCKS HIDE SLOTS.' ],
                'sync_status'         => [ 'type' => 'text', 'label' => 'Sync status', 'default' => 'LAST SYNCED TO S10: JUST NOW ✓' ],
                'hide_profile'        => [ 'type' => 'switcher', 'label' => 'Hide profile editor' ],
                'sec_profile'         => [ 'type' => 'text', 'label' => 'Profile tag', 'default' => 'D · Profile edit' ],
                'profile_title'       => [ 'type' => 'text', 'label' => 'Profile title', 'default' => 'Profile edit (FR-TUTOR-10)' ],
                'bio_label'           => [ 'type' => 'text', 'label' => 'Bio label', 'default' => 'BIO ↘' ],
                'change_photo_label'  => [ 'type' => 'text', 'label' => 'Change photo button', 'default' => 'Change photo' ],
                'demo_title'          => [ 'type' => 'text', 'label' => 'Demo title', 'default' => 'Demo video' ],
                'current_label'       => [ 'type' => 'text', 'label' => 'Current video chip', 'default' => '▶ Current' ],
                'replace_video_label' => [ 'type' => 'text', 'label' => 'Replace video button', 'default' => 'Replace video' ],
                'demo_note'           => [ 'type' => 'textarea', 'label' => 'Demo note', 'default' => '△ NEW DEMO VIDEO — RE-APPROVAL REQUIRED BEFORE IT GOES LIVE' ],
                'subjects_label'      => [ 'type' => 'text', 'label' => 'Subjects label', 'default' => 'SUBJECTS & LEVELS ↘' ],
                'add_subject_label'   => [ 'type' => 'text', 'label' => 'Add subject chip', 'default' => '+ Add' ],
                'rate_label'          => [ 'type' => 'text', 'label' => 'Rate label', 'default' => 'HOURLY RATE ↘' ],
                'rate_note'           => [ 'type' => 'textarea', 'label' => 'Rate approval note', 'default' => '⏳ Rate change pending agency approval (Model A). Current rate stays live until approved. Model B may self-set within band — PM to confirm.' ],
                'save_label'          => [ 'type' => 'text', 'label' => 'Save button', 'default' => 'Save profile' ],
                'preview_label'       => [ 'type' => 'text', 'label' => 'Preview button', 'default' => 'Preview public profile (S03)' ],
            ],
            'style_targets' => [
                'section'       => [ 'label' => 'Section Box', 'selector' => '.mgk-tutor-schedule', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'shell'         => [ 'label' => 'Schedule Shell', 'selector' => '.mgk-tutor-schedule__shell', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'topbar'        => [ 'label' => 'Top Tabs Bar', 'selector' => '.mgk-tutor-schedule-topbar', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'topbartext'    => mgk_style_text( 'Top Bar Text', '.mgk-tutor-schedule-topbar strong, .mgk-tutor-schedule-topbar a', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'buttons'       => mgk_style_button( 'All Buttons', '.mgk-tutor-schedule a' ),
                'sectags'       => mgk_style_text( 'Section Tags', '.mgk-tutor-schedule-sec', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border', 'shadow' ] ),
                'availability'  => [ 'label' => 'Availability Section', 'selector' => '.mgk-tutor-schedule-availability', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'availhead'     => mgk_style_text( 'Availability Heading', '.mgk-tutor-schedule-availability h1, .mgk-tutor-schedule-availability header p', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'availgrid'     => [ 'label' => 'Weekly Grid', 'selector' => '.mgk-tutor-schedule-grid', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'gridheads'     => [ 'label' => 'Day/Time Labels', 'selector' => '.mgk-tutor-schedule-grid b, .mgk-tutor-schedule-grid em', 'features' => [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ],
                'gridcells'     => [ 'label' => 'Availability Cells', 'selector' => '.mgk-tutor-schedule-grid span', 'features' => [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border', 'shadow', 'hover' ] ],
                'legend'        => mgk_style_text( 'Availability Legend', '.mgk-tutor-schedule-availability footer, .mgk-tutor-schedule-availability footer span', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'rowlayout'     => [ 'label' => 'Block/Sync Row Layout', 'selector' => '.mgk-tutor-schedule-row', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'blockbox'      => [ 'label' => 'Ad-hoc Block Box', 'selector' => '.mgk-tutor-schedule-block', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'blocktext'     => mgk_style_text( 'Ad-hoc Text', '.mgk-tutor-schedule-block h2, .mgk-tutor-schedule-block p, .mgk-tutor-schedule-block label span, .mgk-tutor-schedule-block label strong', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'blockfields'   => [ 'label' => 'Ad-hoc Field Boxes', 'selector' => '.mgk-tutor-schedule-block label', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'syncbox'       => [ 'label' => 'S10 Sync Dark Box', 'selector' => '.mgk-tutor-schedule-sync', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'synctext'      => mgk_style_text( 'S10 Sync Text', '.mgk-tutor-schedule-sync h2, .mgk-tutor-schedule-sync p, .mgk-tutor-schedule-sync strong', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'profilebox'    => [ 'label' => 'Profile Edit Section', 'selector' => '.mgk-tutor-schedule-profile', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'profiletitle'  => mgk_style_text( 'Profile Title', '.mgk-tutor-schedule-profile h2', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'profilelayout' => [ 'label' => 'Profile Two-column Layout', 'selector' => '.mgk-tutor-schedule-profile-grid', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'profilecards'  => [ 'label' => 'Profile Field Cards', 'selector' => '.mgk-tutor-schedule-photo, .mgk-tutor-schedule-demo, .mgk-tutor-schedule-bio, .mgk-tutor-schedule-subjects, .mgk-tutor-schedule-rate', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow', 'hover' ] ],
                'photoarea'     => [ 'label' => 'Photo Placeholder', 'selector' => '.mgk-tutor-schedule-photo div span', 'features' => [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'demoarea'      => [ 'label' => 'Demo Video Placeholder', 'selector' => '.mgk-tutor-schedule-demo div', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'demochip'      => [ 'label' => 'Demo Current Chip', 'selector' => '.mgk-tutor-schedule-demo div span', 'features' => [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'profiletext'   => mgk_style_text( 'Profile Field Text', '.mgk-tutor-schedule-bio span, .mgk-tutor-schedule-subjects span, .mgk-tutor-schedule-rate span, .mgk-tutor-schedule-demo h3, .mgk-tutor-schedule-demo p', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'biofields'     => [ 'label' => 'Bio Skeleton Lines', 'selector' => '.mgk-tutor-schedule-bio i', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'subjectchips'  => [ 'label' => 'Subject Chips', 'selector' => '.mgk-tutor-schedule-subjects b', 'features' => [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border', 'shadow', 'hover' ] ],
                'ratevalue'     => mgk_style_text( 'Rate Value', '.mgk-tutor-schedule-rate strong, .mgk-tutor-schedule-rate em', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'ratenote'      => [ 'label' => 'Rate Approval Note', 'selector' => '.mgk-tutor-schedule-rate p', 'features' => [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ],
                'actions'       => [ 'label' => 'Profile Action Buttons', 'selector' => '.mgk-tutor-schedule-fields nav', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
            ],
        ];
    }

    /* ── S07 Request Match (one route, two states) + SPLIT widgets ────────────
       Per the MGK 3-layer rule, owners may edit SAFE marketing copy + every
       element's Style; the field enums, validation, SG-phone logic, PDPA-
       required, lead/SLA/mask logic and submit endpoint are LOCKED in
       inc/mgk-forms.php — NOT exposed here.

       Shipped as ONE composite widget (renders form OR confirmation on
       ?mgk_lead) AND as three standalone widgets so owners can drag / reorder /
       hide / restyle each block independently. The <form> is never split — it
       lives whole inside the Form-fields widget so it submits in one POST. */

    // Reusable control + style-target groups (shared by composite + split widgets).
    // Content defaults are pre-filled (like MGK · Hero) so the Content tab shows
    // the live copy; switchers toggle decorative pieces. Data stays locked.
    $rq_intro_controls = [
        'intro_pre'     => [ 'type' => 'text', 'label' => 'Intro · Lead word (e.g. Get)', 'default' => 'Get' ],
        'intro_em1'     => [ 'type' => 'text', 'label' => 'Intro · Emphasis 1 (e.g. 3-5)', 'default' => '3-5' ],
        'intro_mid1'    => [ 'type' => 'text', 'label' => 'Intro · Emphasis 2 (e.g. tutor matches)', 'default' => 'tutor matches' ],
        'intro_mid2'    => [ 'type' => 'text', 'label' => 'Intro · Join word (e.g. in)', 'default' => 'in' ],
        'intro_em2'     => [ 'type' => 'text', 'label' => 'Intro · Emphasis 3 (e.g. 6 hours)', 'default' => '6 hours' ],
        'trust_1'       => [ 'type' => 'text', 'label' => 'Intro · Trust 1', 'default' => 'Free' ],
        'trust_2'       => [ 'type' => 'text', 'label' => 'Intro · Trust 2', 'default' => 'No obligation' ],
        'trust_3'       => [ 'type' => 'text', 'label' => 'Intro · Trust 3', 'default' => 'No account needed' ],
        'trust_4'       => [ 'type' => 'text', 'label' => 'Intro · Trust 4', 'default' => 'Hand-picked by our team' ],
        'hide_progress' => [ 'type' => 'switcher', 'label' => 'Hide progress bar', 'label_on' => 'Hidden', 'label_off' => 'Shown', 'default' => '' ],
        'hide_trust'    => [ 'type' => 'switcher', 'label' => 'Hide trust row', 'label_on' => 'Hidden', 'label_off' => 'Shown', 'default' => '' ],
    ];
    $rq_fields_controls = [
        'form_heading' => [ 'type' => 'text', 'label' => 'Form Heading', 'default' => 'Tell us what you need' ],
        'form_note'    => [ 'type' => 'text', 'label' => 'Form Heading Note', 'default' => '(TAKES ~60S)' ],
        'submit_label' => [ 'type' => 'text', 'label' => 'Submit Button Label', 'default' => 'Get My Matches →' ],
        'submit_note'  => [ 'type' => 'text', 'label' => 'Note under Submit', 'default' => '' ],
    ];
    $rq_confirm_controls = [
        'heading'    => [ 'type' => 'text',     'label' => 'Confirm · Heading', 'default' => 'Request received!' ],
        'subheading' => [ 'type' => 'text',     'label' => 'Confirm · Subheading', 'default' => 'WE’RE HAND-PICKING YOUR MATCHES NOW.' ],
        'reassure'   => [ 'type' => 'textarea', 'label' => 'Confirm · Reassurance line', 'default' => 'You’ll receive 3-5 tutor proposals within 6 hours via email + SMS.' ],
        'btn_browse' => [ 'type' => 'text',     'label' => 'Confirm · Browse Button', 'default' => 'Browse tutors meanwhile →' ],
        'btn_how'    => [ 'type' => 'text',     'label' => 'Confirm · How-it-works Button', 'default' => 'How matching works' ],
    ];

    $rq_intro_targets = [
        'intro'    => [ 'label' => 'Intro Band', 'selector' => '.mgk-rq-intro', 'features' => [ 'background', 'padding' ] ],
        'headline' => mgk_style_text( 'Intro · Headline', '.mgk-rq-headline' ),
        'progress' => [ 'label' => 'Intro · Progress Bar', 'selector' => '.mgk-rq-progress, .mgk-rq-progress span', 'features' => [ 'background' ] ],
        'trust'    => mgk_style_text( 'Intro · Trust Row', '.mgk-rq-trust' ),
    ];
    $rq_fields_targets = [
        'formhead'  => mgk_style_text( 'Form Heading', '.mgk-rq-formhead h2' ),
        'label'     => mgk_style_text( 'Field Labels', '.mgk-rq-label' ),
        'select'    => [ 'label' => 'Select / Input Fields', 'selector' => '.mgk-rq-select select, .mgk-rq-phone-input, .mgk-rq-note', 'features' => [ 'typography', 'color', 'background', 'border' ] ],
        'chip'      => [ 'label' => 'Day Chips', 'selector' => '.mgk-rq-chip', 'features' => [ 'typography', 'color', 'background', 'border' ] ],
        'phonehelp' => [ 'label' => 'Phone Helper Box', 'selector' => '.mgk-rq-phonehelp', 'features' => [ 'typography', 'color', 'background', 'border' ] ],
        'consent'   => [ 'label' => 'PDPA Consent Box', 'selector' => '.mgk-rq-consent', 'features' => [ 'typography', 'color', 'background', 'border' ] ],
        'submit'    => mgk_style_button( 'Submit Button', '.mgk-rq-submit' ),
        'subnote'   => mgk_style_text( 'Submit Note', '.mgk-rq-subnote' ),
    ];
    $rq_confirm_targets = [
        'success'  => [ 'label' => 'Confirm · Success Circle', 'selector' => '.mgk-rq-success', 'features' => [ 'background', 'border' ] ],
        'cfhead'   => mgk_style_text( 'Confirm · Heading', '.mgk-rq-confirm-head' ),
        'countbox' => [ 'label' => 'Confirm · Countdown Box', 'selector' => '.mgk-rq-countdown', 'features' => [ 'background', 'padding', 'border' ] ],
        'timer'    => mgk_style_text( 'Confirm · Timer', '.mgk-rq-timer', [ 'typography', 'color' ] ),
        'reassbox' => [ 'label' => 'Confirm · Reassurance Box', 'selector' => '.mgk-rq-reassure', 'features' => [ 'background', 'padding', 'border' ] ],
        'outbtn'   => mgk_style_button( 'Confirm · Action Buttons', '.mgk-rq-outbtn' ),
    ];
    $rq_box = [ 'section' => [ 'label' => 'Whole Section Box', 'selector' => '.mgk-rq, .mgk-rq-confirm', 'features' => [ 'background', 'padding', 'margin' ] ] ];

    // Composite widget — whole flow in one drop (form ⇄ confirmation by ?mgk_lead).
    if ( shortcode_exists( 'mgk_request_match' ) ) {
        $sections[] = [
            'tag'           => 'mgk_request_match',
            'title'         => 'MGK · Request Match (S07 — whole page)',
            'icon'          => 'eicon-form-horizontal',
            'controls'      => array_merge( $rq_intro_controls, $rq_fields_controls, $rq_confirm_controls ),
            'style_targets' => array_merge( $rq_intro_targets, $rq_fields_targets, $rq_confirm_targets, $rq_box ),
        ];
    }

    // Split widget 1 — Intro band only.
    if ( shortcode_exists( 'mgk_request_intro' ) ) {
        $sections[] = [
            'tag'           => 'mgk_request_intro',
            'title'         => 'MGK · Request — Intro band',
            'icon'          => 'eicon-banner',
            'controls'      => $rq_intro_controls,
            'style_targets' => array_merge( $rq_intro_targets, $rq_box ),
        ];
    }

    // Split widget 2 — the complete form (heading + single <form>). Logic locked.
    if ( shortcode_exists( 'mgk_request_fields' ) ) {
        $sections[] = [
            'tag'           => 'mgk_request_fields',
            'title'         => 'MGK · Request — Form fields',
            'icon'          => 'eicon-form-horizontal',
            'controls'      => $rq_fields_controls,
            'style_targets' => array_merge( $rq_fields_targets, $rq_box ),
        ];
    }

    // Split widget 3 — confirmation state (countdown + masked recipient).
    if ( shortcode_exists( 'mgk_request_confirm' ) ) {
        $sections[] = [
            'tag'           => 'mgk_request_confirm',
            'title'         => 'MGK · Request — Confirmation',
            'icon'          => 'eicon-check-circle',
            'controls'      => $rq_confirm_controls,
            'style_targets' => array_merge( $rq_confirm_targets, $rq_box ),
        ];
    }

    /* ── S07 PER-FIELD widgets — each form field as its OWN draggable widget ───
       Drop these (in any order) inside ONE Elementor Section, then add the
       "Submit" widget last; its JS collects every field in the Section and
       POSTs to the locked endpoint. Owners may Show/Hide (optional fields only),
       edit the static label / helper / placeholder copy, and Style each field.
       The OPTIONS, validation, SG-phone rule, PDPA-required and lead/SLA logic
       stay LOCKED in PHP — never exposed as controls. */
    // Default copy pulled from the LOCKED enums so the Content tab shows the live
    // text (same pattern as MGK · Hero), while the options themselves stay locked.
    $rq_enums    = function_exists( 'mgk_request_enums' ) ? mgk_request_enums() : [];
    $rq_lvl_enum = ! empty( $rq_enums['levels'] )
        ? 'ENUM: ' . implode( ' · ', array_map( function ( $o ) { return $o['label']; }, $rq_enums['levels'] ) ) : '';
    $rq_sub_enum = ! empty( $rq_enums['subjects'] )
        ? 'ENUM: ' . implode( ' · ', array_map( function ( $o ) { return $o['label']; }, $rq_enums['subjects'] ) ) : '';
    // Reusable "Hide decorative element" switcher spec.
    $rq_hide_sw = function ( $label ) {
        return [ 'type' => 'switcher', 'label' => $label, 'label_on' => 'Hidden', 'label_off' => 'Shown', 'default' => '' ];
    };

    $rq_field_widgets = [
        'mgk_request_field_level' => [
            'title' => 'MGK · Field — Child Level', 'icon' => 'eicon-select',
            'controls' => [
                'label'       => [ 'type' => 'text', 'label' => 'Label', 'default' => "1 · CHILD'S LEVEL" ],
                'placeholder' => [ 'type' => 'text', 'label' => 'Placeholder', 'default' => 'SELECT LEVEL...' ],
                'helper'      => [ 'type' => 'text', 'label' => 'Helper line (under field)', 'default' => $rq_lvl_enum ],
                'hide_helper' => $rq_hide_sw( 'Hide helper line' ),
            ],
            'targets' => [
                'label'  => mgk_style_text( 'Label', '.mgk-rq-label' ),
                'select' => [ 'label' => 'Select Field', 'selector' => '.mgk-rq-select select', 'features' => [ 'typography', 'color', 'background', 'border' ] ],
                'helper' => mgk_style_text( 'Helper / Enum line', '.mgk-rq-enum' ),
            ],
        ],
        'mgk_request_field_subject' => [
            'title' => 'MGK · Field — Subject', 'icon' => 'eicon-select',
            'controls' => [
                'label'       => [ 'type' => 'text', 'label' => 'Label', 'default' => '2 · SUBJECT' ],
                'placeholder' => [ 'type' => 'text', 'label' => 'Placeholder', 'default' => 'SELECT SUBJECT...' ],
                'helper'      => [ 'type' => 'text', 'label' => 'Helper line (under field)', 'default' => $rq_sub_enum ],
                'hide_helper' => $rq_hide_sw( 'Hide helper line' ),
            ],
            'targets' => [
                'label'  => mgk_style_text( 'Label', '.mgk-rq-label' ),
                'select' => [ 'label' => 'Select Field', 'selector' => '.mgk-rq-select select', 'features' => [ 'typography', 'color', 'background', 'border' ] ],
                'helper' => mgk_style_text( 'Helper / Enum line', '.mgk-rq-enum' ),
            ],
        ],
        'mgk_request_field_schedule' => [
            'title' => 'MGK · Field — Preferred Schedule', 'icon' => 'eicon-calendar',
            'controls' => [
                'label'      => [ 'type' => 'text', 'label' => 'Label', 'default' => '3 · PREFERRED SCHEDULE' ],
                'multi_note' => [ 'type' => 'text', 'label' => 'Multi-select note', 'default' => '(MULTI-SELECT)' ],
                'hide_multi' => $rq_hide_sw( 'Hide multi-select note' ),
            ],
            'targets' => [
                'label' => mgk_style_text( 'Label', '.mgk-rq-label' ),
                'chip'  => [ 'label' => 'Day Chips', 'selector' => '.mgk-rq-chip', 'features' => [ 'typography', 'color', 'background', 'border' ] ],
                'time'  => [ 'label' => 'Time Selects', 'selector' => '.mgk-rq-times select', 'features' => [ 'typography', 'color', 'background', 'border' ] ],
            ],
        ],
        'mgk_request_field_budget' => [
            'title' => 'MGK · Field — Budget Range', 'icon' => 'eicon-slider-3d',
            'controls' => [
                'hide'  => $rq_hide_sw( 'Hide this field' ),
                'label' => [ 'type' => 'text', 'label' => 'Label', 'default' => '4 · BUDGET RANGE' ],
            ],
            'targets' => [
                'label'  => mgk_style_text( 'Label', '.mgk-rq-label' ),
                'budget' => [ 'label' => 'Budget Box', 'selector' => '.mgk-rq-budget', 'features' => [ 'background', 'padding', 'border' ] ],
                'value'  => mgk_style_text( 'Range Value', '.mgk-rq-budget-value', [ 'typography', 'color' ] ),
            ],
        ],
        'mgk_request_field_note' => [
            'title' => 'MGK · Field — Note', 'icon' => 'eicon-text-area',
            'controls' => [
                'hide'        => $rq_hide_sw( 'Hide this field' ),
                'label'       => [ 'type' => 'text', 'label' => 'Label', 'default' => '5 · NOTE TO US' ],
                'placeholder' => [ 'type' => 'text', 'label' => 'Placeholder', 'default' => 'E.G. "PSLE IN OCT, NEEDS HELP WITH WORD PROBLEMS"' ],
            ],
            'targets' => [
                'label'   => mgk_style_text( 'Label', '.mgk-rq-label' ),
                'note'    => [ 'label' => 'Textarea', 'selector' => '.mgk-rq-note', 'features' => [ 'typography', 'color', 'background', 'border' ] ],
                'counter' => mgk_style_text( 'Char Counter', '.mgk-rq-counter' ),
            ],
        ],
        'mgk_request_field_phone' => [
            'title' => 'MGK · Field — Mobile Number', 'icon' => 'eicon-phone-field',
            'controls' => [
                'label'       => [ 'type' => 'text', 'label' => 'Label', 'default' => 'MOBILE NUMBER' ],
                'placeholder' => [ 'type' => 'text', 'label' => 'Placeholder', 'default' => '9XXX XXXX (8 DIGITS)' ],
                'helper'      => [ 'type' => 'textarea', 'label' => 'Helper box copy', 'default' => 'We only send an OTP when you accept a proposal — not now. Starts with 6/8/9, 8 digits.' ],
                'hide_helper' => $rq_hide_sw( 'Hide helper box' ),
            ],
            'targets' => [
                'label'     => mgk_style_text( 'Label', '.mgk-rq-label' ),
                'phone'     => [ 'label' => 'Phone Input', 'selector' => '.mgk-rq-phone-input', 'features' => [ 'typography', 'color', 'background', 'border' ] ],
                'pre'       => [ 'label' => '+65 Prefix Box', 'selector' => '.mgk-rq-phone-pre', 'features' => [ 'typography', 'color', 'background', 'border' ] ],
                'phonehelp' => [ 'label' => 'Helper Box', 'selector' => '.mgk-rq-phonehelp', 'features' => [ 'typography', 'color', 'background', 'border' ] ],
            ],
        ],
        'mgk_request_field_pdpa' => [
            'title' => 'MGK · Field — PDPA Consent', 'icon' => 'eicon-check-circle',
            'controls' => [
                'label'      => [ 'type' => 'textarea', 'label' => 'Consent text', 'default' => 'I agree to receive tutor proposals & updates by email/SMS, and to the processing of my data per the' ],
                'link_label' => [ 'type' => 'text', 'label' => 'Link label', 'default' => 'PDPA Notice' ],
            ],
            'targets' => [
                'consent' => [ 'label' => 'Consent Box', 'selector' => '.mgk-rq-consent', 'features' => [ 'typography', 'color', 'background', 'border' ] ],
            ],
        ],
    ];
    foreach ( $rq_field_widgets as $tag => $w ) {
        if ( ! shortcode_exists( $tag ) ) continue;
        $sections[] = [
            'tag'           => $tag,
            'title'         => $w['title'],
            'icon'          => $w['icon'],
            'controls'      => $w['controls'],
            'style_targets' => array_merge( $w['targets'], $rq_box ),
        ];
    }

    // Submit button widget — drop LAST inside the field Section.
    if ( shortcode_exists( 'mgk_request_submit' ) ) {
        $sections[] = [
            'tag'      => 'mgk_request_submit',
            'title'    => 'MGK · Request — Submit Button',
            'icon'     => 'eicon-button',
            'controls' => [
                'submit_label' => [ 'type' => 'text', 'label' => 'Button Label', 'default' => 'Get My Matches →' ],
                'submit_note'  => [ 'type' => 'text', 'label' => 'Note under button', 'default' => 'NO ACCOUNT CREATED · YOU’LL HEAR BACK WITHIN 6 HOURS' ],
            ],
            'style_targets' => array_merge( [
                'submit'  => mgk_style_button( 'Submit Button', '.mgk-rq-submit' ),
                'subnote' => mgk_style_text( 'Note', '.mgk-rq-subnote' ),
            ], $rq_box ),
        ];
    }

    /* ── S09 Trial Booking — Select Tutor — per-section widgets ───────────────
       Step 1 of the booking flow. DATA (selected tutor, trial price/discount/GST,
       booking + lead/proposal state, S10 route, resume token) stays LOCKED in
       inc/mgk-select-tutor.php. Owners may edit SAFE copy + Style + a few
       decorative toggles. Pre-filled Content like MGK · Hero. */
    $bk_box = [ 'section' => [ 'label' => 'Section Box', 'selector' => '.mgk-bk-nav, .mgk-bk-progress, .mgk-bk-main, .mgk-bk-chosen, .mgk-bk-included, .mgk-bk-offer, .mgk-bk-breakdown, .mgk-bk-cta-wrap', 'features' => [ 'background', 'padding', 'margin' ] ] ];

    $bk_widgets = [
        'mgk_booking_nav' => [
            'title' => 'MGK · Booking — Nav', 'icon' => 'eicon-nav-menu',
            'controls' => [
                'utility'      => [ 'type' => 'text', 'label' => 'Utility bar text', 'default' => 'Secure booking · SG/EN' ],
                'logo_label'   => [ 'type' => 'text', 'label' => 'Logo label', 'default' => '[LOGO]' ],
                'secure_label' => [ 'type' => 'text', 'label' => 'Secure label (blank = auto "Booking trial with <tutor>")', 'default' => '' ],
                'signin_label' => [ 'type' => 'text', 'label' => 'Sign In label', 'default' => 'Sign In' ],
                'hide_secure'  => [ 'type' => 'switcher', 'label' => 'Hide secure label', 'label_on' => 'Hidden', 'label_off' => 'Shown', 'default' => '' ],
            ],
            'targets' => [
                'utility' => [ 'label' => 'Utility Bar', 'selector' => '.mgk-bk-utility', 'features' => [ 'typography', 'color', 'background' ] ],
                'logo'    => mgk_style_text( 'Logo', '.mgk-bk-logo' ),
                'secure'  => mgk_style_text( 'Secure Label', '.mgk-bk-secure' ),
                'signin'  => mgk_style_button( 'Sign In Button', '.mgk-bk-signin' ),
                'navbar'  => [ 'label' => 'Nav Bar', 'selector' => '.mgk-bk-mainnav', 'features' => [ 'background', 'border', 'padding' ] ],
            ],
        ],
        'mgk_booking_progress' => [
            'title' => 'MGK · Booking — Progress', 'icon' => 'eicon-number-field',
            'controls' => [
                'select' => [ 'type' => 'text', 'label' => 'Step 1 label', 'default' => 'Select tutor' ],
                'slot'   => [ 'type' => 'text', 'label' => 'Step 2 label', 'default' => 'Pick slot' ],
                'pay'    => [ 'type' => 'text', 'label' => 'Step 3 label', 'default' => 'Pay' ],
            ],
            'targets' => [
                'num'    => [ 'label' => 'Step Circles', 'selector' => '.mgk-bk-step-num', 'features' => [ 'typography', 'color', 'background', 'border' ] ],
                'label'  => mgk_style_text( 'Step Labels', '.mgk-bk-step-label' ),
                'line'   => [ 'label' => 'Connector Line', 'selector' => '.mgk-bk-progress-line', 'features' => [ 'background' ] ],
            ],
        ],
        'mgk_chosen_tutor' => [
            'title' => 'MGK · Booking — Chosen Tutor', 'icon' => 'eicon-person',
            'controls' => [
                'heading'      => [ 'type' => 'text', 'label' => 'Heading', 'default' => 'You’re booking a trial with:' ],
                'subjects'     => [ 'type' => 'text', 'label' => 'Rating / subjects line (display copy)', 'default' => '' ],
                'location'     => [ 'type' => 'text', 'label' => 'Location line', 'default' => '📍 STUDENT HOME (CENTRAL) · ONLINE AVAILABLE' ],
                'change_label' => [ 'type' => 'text', 'label' => 'Change-tutor link', 'default' => '← CHANGE TUTOR' ],
                'back_label'   => [ 'type' => 'text', 'label' => 'Back-to-proposals link', 'default' => '/ BACK TO PROPOSALS' ],
                'avatar_label' => [ 'type' => 'text', 'label' => 'Avatar placeholder text', 'default' => 'Avatar' ],
                'hide_heading' => [ 'type' => 'switcher', 'label' => 'Hide heading', 'label_on' => 'Hidden', 'label_off' => 'Shown', 'default' => '' ],
            ],
            'targets' => [
                'heading' => mgk_style_text( 'Heading', '.mgk-bk-chosen-heading' ),
                'card'    => [ 'label' => 'Card Box', 'selector' => '.mgk-bk-tutor-card', 'features' => [ 'background', 'padding', 'border' ] ],
                'avatar'  => [ 'label' => 'Avatar', 'selector' => '.mgk-bk-tutor-avatar', 'features' => [ 'border' ] ],
                'badge'   => [ 'label' => 'Verified Badge', 'selector' => '.mgk-bk-verified', 'features' => [ 'typography', 'color', 'background' ] ],
                'name'    => mgk_style_text( 'Tutor Name', '.mgk-bk-tutor-name' ),
                'meta'    => mgk_style_text( 'Tutor Meta', '.mgk-bk-tutor-meta' ),
                'detail'  => mgk_style_text( 'Detail Lines', '.mgk-bk-tutor-detail' ),
                'link'    => mgk_style_text( 'Action Links', '.mgk-bk-link' ),
            ],
        ],
        'mgk_trial_included' => [
            'title' => 'MGK · Booking — Trial Included', 'icon' => 'eicon-bullet-list',
            'controls' => [
                'heading'  => [ 'type' => 'text', 'label' => 'Heading', 'default' => 'What’s in the trial lesson' ],
                'bullet_1' => [ 'type' => 'text', 'label' => 'Bullet 1', 'default' => '1.5h one-to-one level diagnostic + sample teaching' ],
                'bullet_2' => [ 'type' => 'text', 'label' => 'Bullet 2', 'default' => 'Written feedback on strengths & gaps' ],
                'bullet_3' => [ 'type' => 'text', 'label' => 'Bullet 3', 'default' => 'No commitment — continue with a package only if happy' ],
            ],
            'targets' => [
                'box'     => [ 'label' => 'Box', 'selector' => '.mgk-bk-included', 'features' => [ 'background', 'padding', 'border' ] ],
                'heading' => mgk_style_text( 'Heading', '.mgk-bk-included-heading' ),
                'list'    => mgk_style_text( 'Bullets', '.mgk-bk-included-list' ),
            ],
        ],
        'mgk_trial_offer' => [
            'title' => 'MGK · Booking — Trial Offer', 'icon' => 'eicon-price-table',
            'controls' => [
                'label' => [ 'type' => 'text', 'label' => 'Top label', 'default' => 'TRIAL LESSON · FIRST LESSON' ],
                'note'  => [ 'type' => 'text', 'label' => 'Bottom note', 'default' => 'SGD · INCL. GST · ONE TRIAL PER TUTOR' ],
            ],
            'targets' => [
                'box'   => [ 'label' => 'Offer Box', 'selector' => '.mgk-bk-offer', 'features' => [ 'background', 'padding', 'border' ] ],
                'label' => mgk_style_text( 'Top Label', '.mgk-bk-offer-label' ),
                'now'   => mgk_style_text( 'Price', '.mgk-bk-offer-now', [ 'typography', 'color' ] ),
                'was'   => mgk_style_text( 'Old Price', '.mgk-bk-offer-was', [ 'typography', 'color' ] ),
                'badge' => [ 'label' => 'Discount Badge', 'selector' => '.mgk-bk-offer-badge', 'features' => [ 'typography', 'color', 'background' ] ],
                'note'  => mgk_style_text( 'Note', '.mgk-bk-offer-note' ),
            ],
        ],
        'mgk_trial_breakdown' => [
            'title' => 'MGK · Booking — Price Breakdown', 'icon' => 'eicon-table',
            'controls' => [],
            'targets' => [
                'box'   => [ 'label' => 'Box', 'selector' => '.mgk-bk-breakdown', 'features' => [ 'background', 'padding', 'border' ] ],
                'row'   => mgk_style_text( 'Rows', '.mgk-bk-bd-row' ),
                'due'   => mgk_style_text( 'Due Row', '.mgk-bk-bd-row.is-due', [ 'typography', 'color' ] ),
                'gst'   => mgk_style_text( 'GST Note', '.mgk-bk-bd-gst' ),
            ],
        ],
        'mgk_booking_cta' => [
            'title' => 'MGK · Booking — Continue CTA', 'icon' => 'eicon-button',
            'controls' => [
                'cta_label'    => [ 'type' => 'text', 'label' => 'Button label', 'default' => 'Continue to pick a slot →' ],
                'resume_label' => [ 'type' => 'text', 'label' => 'Save & resume link', 'default' => '💾 SAVE & RESUME LATER (WE’LL EMAIL YOU A LINK)' ],
                'nopay_label'  => [ 'type' => 'text', 'label' => 'No-payment note', 'default' => 'NO PAYMENT YET · PAY AT STEP 3' ],
            ],
            'targets' => [
                'button' => mgk_style_button( 'Continue Button', '.mgk-bk-continue' ),
                'resume' => mgk_style_text( 'Save & Resume Link', '.mgk-bk-resume' ),
                'nopay'  => mgk_style_text( 'No-payment Note', '.mgk-bk-nopay' ),
            ],
        ],
    ];
    foreach ( $bk_widgets as $tag => $w ) {
        if ( ! shortcode_exists( $tag ) ) continue;
        $sections[] = [
            'tag'           => $tag,
            'title'         => $w['title'],
            'icon'          => $w['icon'],
            'controls'      => $w['controls'],
            'style_targets' => array_merge( $w['targets'], $bk_box ),
        ];
    }

    /* ── S10 Trial Booking — Pick Slot — per-section widgets ──────────────────
       Step 2 of the booking flow. DATA (slot availability, hold timer, slot
       status/conflict logic, selected-slot payload, booking state, pay route)
       stays LOCKED in inc/mgk-slots.php. Owners edit SAFE copy + Style only.
       Pre-filled Content like MGK · Hero. */
    $sl_box = [ 'section' => [ 'label' => 'Section Box', 'selector' => '.mgk-bk-hold, .mgk-bk-calendar, .mgk-bk-times, .mgk-bk-confirm', 'features' => [ 'background', 'padding', 'margin' ] ] ];

    $sl_widgets = [
        'mgk_slot_hold_banner' => [
            'title' => 'MGK · Slot — Hold Banner', 'icon' => 'eicon-countdown',
            'controls' => [
                'title' => [ 'type' => 'text',     'label' => 'Banner title', 'default' => '⏱ Slot held for you' ],
                'note'  => [ 'type' => 'textarea', 'label' => 'Banner note', 'default' => 'COMPLETE PAYMENT BEFORE THE TIMER ENDS OR THE SLOT IS RELEASED BACK TO OTHERS.' ],
            ],
            'targets' => [
                'banner' => [ 'label' => 'Banner Box', 'selector' => '.mgk-bk-hold', 'features' => [ 'background', 'padding', 'border' ] ],
                'title'  => mgk_style_text( 'Title', '.mgk-bk-hold__title' ),
                'note'   => mgk_style_text( 'Note', '.mgk-bk-hold__note' ),
                'timer'  => mgk_style_text( 'Timer', '.mgk-bk-hold__timer', [ 'typography', 'color' ] ),
            ],
        ],
        'mgk_live_calendar' => [
            'title' => 'MGK · Slot — Live Calendar', 'icon' => 'eicon-calendar',
            'controls' => [
                'heading'    => [ 'type' => 'text', 'label' => 'Heading', 'default' => 'Pick a trial slot' ],
                'live_note'  => [ 'type' => 'text', 'label' => 'Live availability note', 'default' => '• LIVE AVAILABILITY · UPDATES IN REAL TIME' ],
                'prev_label' => [ 'type' => 'text', 'label' => 'Prev button', 'default' => '‹ Prev' ],
                'next_label' => [ 'type' => 'text', 'label' => 'Next button', 'default' => 'Next ›' ],
            ],
            'targets' => [
                'heading' => mgk_style_text( 'Heading', '.mgk-bk-cal-title h1' ),
                'live'    => mgk_style_text( 'Live Note', '.mgk-bk-cal-live' ),
                'weeknav' => mgk_style_button( 'Week Nav Buttons', '.mgk-bk-week-btn' ),
                'day'     => [ 'label' => 'Day Boxes', 'selector' => '.mgk-bk-day', 'features' => [ 'typography', 'color', 'background', 'border' ] ],
                'daylabel'=> mgk_style_text( 'Day Label', '.mgk-bk-day-label' ),
                'daycount'=> mgk_style_text( 'Day Count', '.mgk-bk-day-count' ),
            ],
        ],
        'mgk_available_times' => [
            'title' => 'MGK · Slot — Available Times', 'icon' => 'eicon-clock-o',
            'controls' => [
                'heading'          => [ 'type' => 'text', 'label' => 'Heading (display copy)', 'default' => '' ],
                'legend_available' => [ 'type' => 'text', 'label' => 'Legend · Available', 'default' => 'AVAILABLE' ],
                'legend_taken'     => [ 'type' => 'text', 'label' => 'Legend · Taken', 'default' => 'TAKEN (LIVE)' ],
                'legend_hold'      => [ 'type' => 'text', 'label' => 'Legend · Your hold', 'default' => 'YOUR HOLD' ],
            ],
            'targets' => [
                'box'     => [ 'label' => 'Box', 'selector' => '.mgk-bk-times', 'features' => [ 'background', 'padding', 'border' ] ],
                'heading' => mgk_style_text( 'Heading', '.mgk-bk-times-head' ),
                'slot'    => [ 'label' => 'Time Slots', 'selector' => '.mgk-bk-slot', 'features' => [ 'typography', 'color', 'background', 'border' ] ],
                'legend'  => mgk_style_text( 'Legend', '.mgk-bk-legend' ),
            ],
        ],
        'mgk_selected_slot' => [
            'title' => 'MGK · Slot — Selected + Confirm', 'icon' => 'eicon-check-circle',
            'controls' => [
                'eyebrow'   => [ 'type' => 'text', 'label' => 'Eyebrow label', 'default' => 'YOUR TRIAL SLOT' ],
                'location'  => [ 'type' => 'text', 'label' => 'Location line (display copy)', 'default' => '' ],
                'cta_label' => [ 'type' => 'text', 'label' => 'Confirm button label', 'default' => 'Confirm slot & pay →' ],
            ],
            'targets' => [
                'summary' => [ 'label' => 'Summary Box', 'selector' => '.mgk-bk-confirm-summary', 'features' => [ 'background', 'padding', 'border' ] ],
                'eyebrow' => mgk_style_text( 'Eyebrow', '.mgk-bk-confirm-eyebrow' ),
                'main'    => mgk_style_text( 'Main Line', '.mgk-bk-confirm-main' ),
                'sub'     => mgk_style_text( 'Sub Line', '.mgk-bk-confirm-sub' ),
                'cta'     => mgk_style_button( 'Confirm Button', '.mgk-bk-confirm-cta' ),
            ],
        ],
    ];
    foreach ( $sl_widgets as $tag => $w ) {
        if ( ! shortcode_exists( $tag ) ) continue;
        $sections[] = [
            'tag'           => $tag,
            'title'         => $w['title'],
            'icon'          => $w['icon'],
            'controls'      => $w['controls'],
            'style_targets' => array_merge( $w['targets'], $sl_box ),
        ];
    }

    /* ── S11 Trial Booking — Pay — per-section widgets ────────────────────────
       Step 3 (final) of the booking flow. DATA (order summary, discount stack +
       cap, GST, payment reference, corporate UEN, card surcharge / 3DS rule,
       payment-status state machine, account auto-create OTP rule, terms refs,
       S12 route) stays LOCKED in inc/mgk-pay.php. Owners edit SAFE copy + Style
       only. Pre-filled Content like MGK · Hero. Reuses MGK · Slot — Hold Banner
       (the countdown) + MGK · Booking — Nav / Progress. */
    $pay_box = [ 'section' => [ 'label' => 'Section Box', 'selector' => '.mgk-pay-account, .mgk-pay-summary, .mgk-pay-method, .mgk-pay-terms, .mgk-pay-cta-wrap', 'features' => [ 'background', 'padding', 'margin', 'border' ] ] ];

    $pay_widgets = [
        'mgk_pay_account' => [
            'title' => 'MGK · Pay — Account Create', 'icon' => 'eicon-mail',
            'controls' => [
                'section_tag' => [ 'type' => 'text',     'label' => 'Section tag', 'default' => 'SEC 3 Account auto-create' ],
                'heading'     => [ 'type' => 'text',     'label' => 'Heading', 'default' => 'Where should we send your booking?' ],
                'subnote'     => [ 'type' => 'text',     'label' => 'Sub-note', 'default' => 'WE’LL CREATE YOUR ACCOUNT AUTOMATICALLY — NO PASSWORD TO REMEMBER.' ],
                'placeholder' => [ 'type' => 'text',     'label' => 'Email placeholder', 'default' => 'you.parent@example.sg' ],
                'otp_note'    => [ 'type' => 'textarea', 'label' => 'OTP note (display copy; rule locked)', 'default' => '' ],
            ],
            'targets' => [
                'heading' => mgk_style_text( 'Heading', '.mgk-pay-account-heading' ),
                'sub'     => mgk_style_text( 'Sub-note', '.mgk-pay-account-sub' ),
                'field'   => [ 'label' => 'Email Field', 'selector' => '.mgk-pay-account .mgk-pay-field', 'features' => [ 'background', 'border', 'padding' ] ],
                'otp'     => [ 'label' => 'OTP Note Box', 'selector' => '.mgk-pay-otp-note', 'features' => [ 'typography', 'color', 'background', 'border' ] ],
            ],
        ],
        'mgk_pay_summary' => [
            'title' => 'MGK · Pay — Order Summary', 'icon' => 'eicon-cart-medium',
            'controls' => [
                'section_tag'    => [ 'type' => 'text', 'label' => 'Section tag', 'default' => 'SEC 4 Price breakdown' ],
                'heading'        => [ 'type' => 'text', 'label' => 'Heading', 'default' => 'Order summary' ],
                'subtotal_label' => [ 'type' => 'text', 'label' => 'Subtotal label', 'default' => 'Subtotal' ],
                'total_label'    => [ 'type' => 'text', 'label' => 'Total label', 'default' => 'Total' ],
            ],
            'targets' => [
                'heading' => mgk_style_text( 'Heading', '.mgk-pay-summary-heading' ),
                'tutor'   => mgk_style_text( 'Tutor Line', '.mgk-pay-summary-tutor-name' ),
                'rows'    => [ 'label' => 'Breakdown Rows', 'selector' => '.mgk-pay-breakdown .mgk-bk-bd-row', 'features' => [ 'typography', 'color' ] ],
                'total'   => mgk_style_text( 'Total Value', '.mgk-pay-total .mgk-bk-bd-value' ),
                'gst'     => mgk_style_text( 'GST Note', '.mgk-pay-gst' ),
                'cap'     => [ 'label' => 'Cap Note Box', 'selector' => '.mgk-pay-cap-note', 'features' => [ 'typography', 'color', 'background', 'border' ] ],
            ],
        ],
        'mgk_pay_method' => [
            'title' => 'MGK · Pay — Payment Method', 'icon' => 'eicon-barcode',
            'controls' => [
                'section_tag'  => [ 'type' => 'text',     'label' => 'Section tag', 'default' => 'SEC 5 Payment method' ],
                'paynow_help'  => [ 'type' => 'text',     'label' => 'PayNow help line', 'default' => 'Scan with any SG banking app' ],
                'waiting_note' => [ 'type' => 'textarea', 'label' => 'Waiting note', 'default' => '⏳ Waiting for payment… we’ll confirm automatically (webhook).' ],
                'state'        => [ 'type' => 'select',   'label' => 'Preview state (editor only)', 'default' => '', 'options' => [
                    '' => 'PayNow (default)', 'card' => 'Card fallback', 'processing' => 'Processing', 'success' => 'Success', 'failed' => 'Failed', 'mismatch' => 'Reference mismatch',
                ] ],
            ],
            'targets' => [
                'tabs'    => mgk_style_button( 'Method Buttons', '.mgk-pay-method-btn' ),
                'panel'   => [ 'label' => 'Panel Box', 'selector' => '.mgk-pay-panel', 'features' => [ 'background', 'border', 'padding' ] ],
                'qr'      => [ 'label' => 'QR Box', 'selector' => '.mgk-pay-qr', 'features' => [ 'background', 'border', 'width' ] ],
                'payee'   => mgk_style_text( 'Payee Line', '.mgk-pay-payee' ),
                'waiting' => [ 'label' => 'Waiting Box', 'selector' => '.mgk-pay-waiting', 'features' => [ 'typography', 'color', 'background', 'border' ] ],
                'status'  => mgk_style_text( 'Status Title', '.mgk-pay-status-title' ),
                'retry'   => mgk_style_button( 'Retry Button', '.mgk-pay-retry' ),
            ],
        ],
        'mgk_pay_terms' => [
            'title' => 'MGK · Pay — Terms Consent', 'icon' => 'eicon-checkbox',
            'controls' => [
                'lead_text' => [ 'type' => 'text', 'label' => 'Lead-in text (links locked)', 'default' => 'I agree to the' ],
            ],
            'targets' => [
                'text' => mgk_style_text( 'Terms Text', '.mgk-pay-terms-text' ),
                'box'  => [ 'label' => 'Terms Box', 'selector' => '.mgk-pay-terms', 'features' => [ 'background', 'border', 'padding' ] ],
            ],
        ],
        'mgk_pay_cta' => [
            'title' => 'MGK · Pay — Pay CTA', 'icon' => 'eicon-button',
            'controls' => [
                'cta_label' => [ 'type' => 'text', 'label' => 'CTA label ({amount} = total)', 'default' => 'Pay {amount} with PayNow →' ],
                'reassure'  => [ 'type' => 'text', 'label' => 'Reassurance line', 'default' => '🔒 YOU WON’T BE CHARGED UNTIL PAYMENT IS CONFIRMED' ],
            ],
            'targets' => [
                'cta'      => mgk_style_button( 'Pay Button', '.mgk-pay-cta' ),
                'reassure' => mgk_style_text( 'Reassurance', '.mgk-pay-reassure' ),
            ],
        ],
    ];
    foreach ( $pay_widgets as $tag => $w ) {
        if ( ! shortcode_exists( $tag ) ) continue;
        $sections[] = [
            'tag'           => $tag,
            'title'         => $w['title'],
            'icon'          => $w['icon'],
            'controls'      => $w['controls'],
            'style_targets' => array_merge( $w['targets'], $pay_box ),
        ];
    }

    /* ── S12 Trial Booking — Confirmation — per-section widgets ───────────────
       The success page. DATA (confirmation #, booking/payment summary, tutor
       contact UNLOCK + masking NFR-10, lesson/Zoom, calendar generation,
       e-invoice, reschedule limits + refund tiers BR-07/FR-PAY-10/FR-BOOK-09,
       message route) stays LOCKED in inc/mgk-confirmation.php. Owners edit SAFE
       copy + Style only. Pre-filled Content like MGK · Hero. */
    $cf_box = [ 'section' => [ 'label' => 'Section Box', 'selector' => '.mgk-cf-card, .mgk-cf-manage', 'features' => [ 'background', 'padding', 'margin', 'border' ] ] ];

    $cf_widgets = [
        'mgk_success_hero' => [
            'title' => 'MGK · Confirm — Success Hero', 'icon' => 'eicon-check-circle',
            'controls' => [
                'heading'     => [ 'type' => 'text', 'label' => 'Heading', 'default' => 'Trial lesson booked!' ],
                'sent_prefix' => [ 'type' => 'text', 'label' => 'Confirmation prefix', 'default' => 'CONFIRMATION' ],
            ],
            'targets' => [
                'hero'  => [ 'label' => 'Hero Band', 'selector' => '.mgk-cf-hero', 'features' => [ 'background', 'padding', 'border' ] ],
                'check' => [ 'label' => 'Check Icon', 'selector' => '.mgk-cf-hero-check', 'features' => [ 'color', 'background', 'border' ] ],
                'title' => mgk_style_text( 'Title', '.mgk-cf-hero-title' ),
                'conf'  => mgk_style_text( 'Confirmation Line', '.mgk-cf-hero-conf' ),
            ],
        ],
        'mgk_booking_summary' => [
            'title' => 'MGK · Confirm — Booking Summary', 'icon' => 'eicon-info-circle-o',
            'controls' => [
                'l_tutor'    => [ 'type' => 'text', 'label' => 'Label · Tutor', 'default' => 'TUTOR' ],
                'l_subject'  => [ 'type' => 'text', 'label' => 'Label · Subject/Level', 'default' => 'SUBJECT/LEVEL' ],
                'l_datetime' => [ 'type' => 'text', 'label' => 'Label · Date/Time', 'default' => 'DATE/TIME' ],
                'l_format'   => [ 'type' => 'text', 'label' => 'Label · Format', 'default' => 'FORMAT' ],
                'l_paid'     => [ 'type' => 'text', 'label' => 'Label · Paid', 'default' => 'PAID' ],
                'l_method'   => [ 'type' => 'text', 'label' => 'Label · Method', 'default' => 'METHOD' ],
            ],
            'targets' => [
                'title' => mgk_style_text( 'Card Title', '.mgk-cf-summary .mgk-cf-card-title' ),
                'label' => mgk_style_text( 'Field Labels', '.mgk-cf-sum-label' ),
                'value' => mgk_style_text( 'Field Values', '.mgk-cf-sum-value' ),
            ],
        ],
        'mgk_tutor_contact' => [
            'title' => 'MGK · Confirm — Tutor Contact', 'icon' => 'eicon-lock-user',
            'controls' => [
                'unlocked_label' => [ 'type' => 'text', 'label' => 'Unlocked status', 'default' => '🔓 CONTACT NOW UNLOCKED' ],
                'cta_label'      => [ 'type' => 'text', 'label' => 'Message button (blank = auto)', 'default' => '' ],
                'masked_note'    => [ 'type' => 'text', 'label' => 'Masked note', 'default' => 'CONTACT WAS MASKED BEFORE BOOKING (NFR-10).' ],
            ],
            'targets' => [
                'card'   => [ 'label' => 'Contact Box', 'selector' => '.mgk-cf-contact', 'features' => [ 'background', 'border', 'padding' ] ],
                'status' => mgk_style_text( 'Status', '.mgk-cf-contact-status' ),
                'name'   => mgk_style_text( 'Tutor Name', '.mgk-cf-contact-name' ),
                'cta'    => mgk_style_button( 'Message Button', '.mgk-cf-contact-cta' ),
                'note'   => mgk_style_text( 'Masked Note', '.mgk-cf-contact-note' ),
            ],
        ],
        'mgk_first_lesson' => [
            'title' => 'MGK · Confirm — First Lesson', 'icon' => 'eicon-calendar',
            'controls' => [
                'heading'       => [ 'type' => 'text', 'label' => 'Heading', 'default' => 'Your first lesson' ],
                'ics_label'     => [ 'type' => 'text', 'label' => 'Add-to-calendar button', 'default' => '📅 Add to calendar (.ics)' ],
                'google_label'  => [ 'type' => 'text', 'label' => 'Google button', 'default' => 'Google' ],
                'outlook_label' => [ 'type' => 'text', 'label' => 'Outlook button', 'default' => 'Outlook' ],
            ],
            'targets' => [
                'title'   => mgk_style_text( 'Heading', '.mgk-cf-lesson .mgk-cf-card-title' ),
                'items'   => [ 'label' => 'Lesson Items', 'selector' => '.mgk-cf-lesson-item', 'features' => [ 'typography', 'color', 'background', 'border' ] ],
                'primary' => mgk_style_button( 'Add-to-calendar Button', '.mgk-cf-cal-primary' ),
                'alt'     => mgk_style_button( 'Google / Outlook Buttons', '.mgk-cf-cal-alt' ),
            ],
        ],
        'mgk_next_steps' => [
            'title' => 'MGK · Confirm — Next Steps + Invoice', 'icon' => 'eicon-checkbox',
            'controls' => [
                'heading'        => [ 'type' => 'text', 'label' => 'Heading', 'default' => 'Next steps' ],
                'download_label' => [ 'type' => 'text', 'label' => 'Invoice download label', 'default' => 'DOWNLOAD PDF →' ],
            ],
            'targets' => [
                'title'   => mgk_style_text( 'Heading', '.mgk-cf-next .mgk-cf-card-title' ),
                'items'   => mgk_style_text( 'Checklist Items', '.mgk-cf-next-label' ),
                'box'     => [ 'label' => 'Checkbox', 'selector' => '.mgk-cf-next-box', 'features' => [ 'color', 'background', 'border' ] ],
                'invoice' => [ 'label' => 'Invoice Row', 'selector' => '.mgk-cf-invoice', 'features' => [ 'background', 'border', 'padding' ] ],
                'link'    => mgk_style_text( 'Download Link', '.mgk-cf-invoice-link' ),
            ],
        ],
        'mgk_manage_booking' => [
            'title' => 'MGK · Confirm — Manage Booking', 'icon' => 'eicon-settings',
            'controls' => [
                'reschedule_label' => [ 'type' => 'text', 'label' => 'Reschedule button', 'default' => '↻ Reschedule' ],
                'cancel_label'     => [ 'type' => 'text', 'label' => 'Cancel button', 'default' => '× Cancel & refund' ],
            ],
            'targets' => [
                'buttons' => mgk_style_button( 'Manage Buttons', '.mgk-cf-manage-btn' ),
                'modal'   => [ 'label' => 'Modal Panel', 'selector' => '.mgk-cf-modal__panel', 'features' => [ 'background', 'border' ] ],
                'danger'  => mgk_style_button( 'Confirm / Danger Buttons', '.mgk-cf-btn-danger' ),
            ],
        ],
    ];
    foreach ( $cf_widgets as $tag => $w ) {
        if ( ! shortcode_exists( $tag ) ) continue;
        $sections[] = [
            'tag'           => $tag,
            'title'         => $w['title'],
            'icon'          => $w['icon'],
            'controls'      => $w['controls'],
            'style_targets' => array_merge( $w['targets'], $cf_box ),
        ];
    }

    /* ── S03 Teacher Profile (DATA page, locked layout) ──────────────
       The whole tutor profile as ONE widget (same pattern as S02 listing).
       Dropping it on a mg_teacher single renders the real profile (hero with
       booking widget, demo video, about, qualifications, track record,
       availability, packages, reviews, gallery, FAQ, similar tutors, sticky
       CTA) from the queried tutor. The tutor data, the section partials and
       the booking form stay LOCKED in PHP; owners may restyle the section
       headings + the Book / Trial / Message buttons + the week switcher /
       review tabs (Style tab). Tutor content is edited in wp-admin.          */
    if ( shortcode_exists( 'mgk_tutor_profile' ) ) {
        $sections[] = [
            'tag'           => 'mgk_tutor_profile',
            'title'         => 'MGK · Teacher Profile',
            'icon'          => 'eicon-user-circle-o',
            'controls'      => [],
            'style_targets' => [
                'name'        => mgk_style_text( 'Tutor Name (hero)', '.mgk-profile-hero h1' ),
                'savebtn'     => mgk_style_button( 'Hero Action Buttons', '.mgk-teacher-actions .mgk-btn' ),
                'sectionhead' => mgk_style_text( 'Section Headings', '.mgk-section-head h2' ),
                'demohead'    => mgk_style_text( 'Demo Video Heading', '.mgk-teacher-demo h2' ),
                'demobtn'     => mgk_style_button( 'Play Demo Button', '.mgk-teacher-demo-video' ),
                'weekswitch'  => mgk_style_button( 'Week Switcher', '.mgk-week-switcher button' ),
                'reviewtabs'  => mgk_style_button( 'Review Filter Tabs', '.mgk-review-tabs button' ),
                'bookbox'     => [ 'label' => 'Booking Card Box', 'selector' => '.mgk-booking-card', 'features' => [ 'background', 'padding', 'border', 'shadow' ] ],
                'bookprice'   => mgk_style_text( 'Booking Card Price', '.mgk-booking-card b' ),
                'bookbtn'     => mgk_style_button( 'Book Trial Button', '.mgk-booking-card .mgk-btn-accent' ),
                'bookalt'     => mgk_style_button( 'Message / Schedule Buttons', '.mgk-booking-card .mgk-btn-outline' ),
                'stickybtn'   => mgk_style_button( 'Mobile Sticky CTA', '.mgk-profile-sticky' ),
                'section'     => [ 'label' => 'Profile Box', 'selector' => '.mgk-profile-body', 'features' => [ 'background', 'padding', 'margin' ] ],
            ],
        ];
    }

    /* ── S03 Profile — SPLIT into per-section widgets ────────────────────────
       Each tutor-profile section as its OWN widget so owners can drag / reorder /
       hide / style sections independently (audit requirement). Tutor data stays
       locked; only headings + buttons + box are restyled. The composite widget
       above still exists for whole-profile drops.                              */
    $profile_subwidgets = [
        'mgk_profile_hero' => [
            'MGK Profile · Hero', 'eicon-header', [
                'name'    => mgk_style_text( 'Tutor Name', '.mgk-profile-hero h1' ),
                'actions' => mgk_style_button( 'Action Buttons (Save/Share/Report)', '.mgk-teacher-actions .mgk-btn' ),
                'section' => [ 'label' => 'Hero Box', 'selector' => '.mgk-profile-hero', 'features' => [ 'background', 'padding', 'margin' ] ],
            ],
        ],
        'mgk_profile_demo_video' => [
            'MGK Profile · Demo Video', 'eicon-play', [
                'heading' => mgk_style_text( 'Heading', '.mgk-teacher-demo h2' ),
                'playbtn' => mgk_style_button( 'Play Demo Button', '.mgk-teacher-demo-video' ),
                'section' => [ 'label' => 'Box', 'selector' => '.mgk-teacher-demo', 'features' => [ 'background', 'padding', 'margin' ] ],
            ],
        ],
        'mgk_profile_quick_info' => [
            'MGK Profile · Quick Info', 'eicon-info-circle-o', [
                'value'   => mgk_style_text( 'Value', '.mgk-profile-quick b' ),
                'label'   => mgk_style_text( 'Label', '.mgk-profile-quick span' ),
                'section' => [ 'label' => 'Box', 'selector' => '.mgk-profile-quick', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
            ],
        ],
        'mgk_profile_about' => [
            'MGK Profile · About', 'eicon-align-left',
            mgk_data_targets( '.mgk-section', '.mgk-section-head h2' ),
        ],
        'mgk_profile_qualifications' => [
            'MGK Profile · Qualifications', 'eicon-document-file', [
                'heading' => mgk_style_text( 'Heading', '.mgk-section-head h2' ),
                'card'    => [ 'label' => 'Card Box', 'selector' => '.mgk-qualification', 'features' => [ 'background', 'padding', 'border', 'shadow' ] ],
                'title'   => mgk_style_text( 'Card · Title', '.mgk-qualification h3' ),
                'cert'    => mgk_style_text( 'Card · Cert ID', '.mgk-qualification strong' ),
                'section' => [ 'label' => 'Box', 'selector' => '.mgk-section', 'features' => [ 'background', 'padding', 'margin' ] ],
            ],
        ],
        'mgk_profile_track_record' => [
            'MGK Profile · Track Record', 'eicon-counter', [
                'heading' => mgk_style_text( 'Heading', '.mgk-section-head h2' ),
                'value'   => mgk_style_text( 'Stat Value', '.mgk-profile-track strong', [ 'typography', 'color' ] ),
                'label'   => mgk_style_text( 'Stat Label', '.mgk-profile-track span' ),
                'section' => [ 'label' => 'Box', 'selector' => '.mgk-section', 'features' => [ 'background', 'padding', 'margin' ] ],
            ],
        ],
        'mgk_profile_availability' => [
            'MGK Profile · Availability', 'eicon-calendar', [
                'heading'    => mgk_style_text( 'Heading', '.mgk-section-head h2' ),
                'weekswitch' => mgk_style_button( 'Week Switcher', '.mgk-week-switcher button' ),
                'section'    => [ 'label' => 'Box', 'selector' => '.mgk-section', 'features' => [ 'background', 'padding', 'margin' ] ],
            ],
        ],
        'mgk_profile_packages' => [
            'MGK Profile · Packages', 'eicon-price-list', [
                'heading'  => mgk_style_text( 'Heading', '.mgk-section-head h2' ),
                'card'     => [ 'label' => 'Package Card Box', 'selector' => '.mgk-package-card', 'features' => [ 'background', 'padding', 'border', 'shadow' ] ],
                'cardname' => mgk_style_text( 'Card · Name', '.mgk-package-card h3' ),
                'cardprice'=> mgk_style_text( 'Card · Price', '.mgk-package-card strong', [ 'typography', 'color' ] ),
                'eyebrow'  => mgk_style_text( 'Card · Badge', '.mgk-package-card .mgk-eyebrow' ),
                'choosebtn'=> mgk_style_button( '"Choose" Buttons', '.mgk-package-card .mgk-btn-accent' ),
                'section'  => [ 'label' => 'Box', 'selector' => '.mgk-section', 'features' => [ 'background', 'padding', 'margin' ] ],
            ],
        ],
        'mgk_profile_reviews' => [
            'MGK Profile · Reviews', 'eicon-review', [
                'heading'  => mgk_style_text( 'Heading', '.mgk-section h2' ),
                'tabs'     => mgk_style_button( 'Filter Tabs', '.mgk-review-tabs button' ),
                'scorebox' => [ 'label' => 'Score Card Box', 'selector' => '.mgk-review-score', 'features' => [ 'background', 'padding', 'border', 'shadow' ] ],
                'score'    => mgk_style_text( 'Score Number', '.mgk-review-score strong', [ 'typography', 'color' ] ),
                'seeall'   => mgk_style_text( '"View All" Link', '.mgk-review-see-all' ),
                'section'  => [ 'label' => 'Box', 'selector' => '.mgk-section', 'features' => [ 'background', 'padding', 'margin' ] ],
            ],
        ],
        'mgk_profile_gallery' => [
            'MGK Profile · Gallery', 'eicon-gallery-grid',
            mgk_data_targets( '.mgk-section', '.mgk-section-head h2' ),
        ],
        'mgk_profile_faq' => [
            'MGK Profile · FAQ', 'eicon-help-o',
            mgk_data_targets( '.mgk-section', '.mgk-section-head h2' ),
        ],
        'mgk_profile_similar' => [
            'MGK Profile · Similar Tutors', 'eicon-person', [
                'heading'  => mgk_style_text( 'Heading', '.mgk-section-head h2' ),
                'card'     => [ 'label' => 'Card Box', 'selector' => '.mgk-similar-card', 'features' => [ 'background', 'padding', 'border', 'shadow' ] ],
                'avatar'   => [ 'label' => 'Card · Avatar', 'selector' => '.mgk-similar-card .mgk-avatar', 'features' => [ 'border', 'shadow' ] ],
                'name'     => mgk_style_text( 'Card · Name', '.mgk-similar-card h3' ),
                'verified' => mgk_style_text( 'Card · Verified line', '.mgk-similar-card .mgk-check' ),
                'section'  => [ 'label' => 'Box', 'selector' => '.mgk-section', 'features' => [ 'background', 'padding', 'margin' ] ],
            ],
        ],
        'mgk_profile_booking' => [
            'MGK Profile · Booking Card', 'eicon-cart-medium', [
                'box'     => [ 'label' => 'Card Box', 'selector' => '.mgk-booking-card', 'features' => [ 'background', 'padding', 'border', 'shadow' ] ],
                'price'   => mgk_style_text( 'Price', '.mgk-booking-card b' ),
                'bookbtn' => mgk_style_button( 'Book Trial Button', '.mgk-booking-card .mgk-btn-accent' ),
                'altbtn'  => mgk_style_button( 'Message / Schedule Buttons', '.mgk-booking-card .mgk-btn-outline' ),
            ],
        ],
        'mgk_profile_sticky_cta' => [
            'MGK Profile · Mobile Sticky CTA', 'eicon-footer', [
                'cta' => mgk_style_button( 'Sticky CTA', '.mgk-profile-sticky' ),
            ],
        ],
    ];
    foreach ( $profile_subwidgets as $tag => $meta ) {
        if ( shortcode_exists( $tag ) ) {
            $sections[] = [
                'tag'           => $tag,
                'title'         => $meta[0],
                'icon'          => $meta[1],
                'controls'      => [],
                'style_targets' => $meta[2],
            ];
        }
    }

    /* ── Parent dashboard empty state (STATE shell) ─────────────────────── */
    if ( shortcode_exists( 'mgk_parent_empty_dashboard' ) ) {
        $sections[] = [
            'tag'      => 'mgk_parent_empty_dashboard',
            'title'    => 'MGK Parent · Empty Dashboard',
            'icon'     => 'eicon-user-circle-o',
            'controls' => [
                'hidden'                  => [ 'type' => 'switcher', 'label' => 'Hide entire section' ],
                'hide_greeting'           => [ 'type' => 'switcher', 'label' => 'Hide greeting' ],
                'greeting'                => [ 'type' => 'text', 'label' => 'Greeting', 'default' => 'Welcome,' ],
                'hide_parent_name'        => [ 'type' => 'switcher', 'label' => 'Hide parent name' ],
                'parent_name'             => [ 'type' => 'text', 'label' => 'Parent name', 'default' => 'Mrs Tan' ],
                'hide_wave'               => [ 'type' => 'switcher', 'label' => 'Hide wave' ],
                'wave'                    => [ 'type' => 'text', 'label' => 'Wave / icon', 'default' => '&#128075;' ],
                'hide_subline'            => [ 'type' => 'switcher', 'label' => 'Hide subline' ],
                'subline'                 => [ 'type' => 'textarea', 'label' => 'Subline', 'default' => "NO LESSONS BOOKED YET - LET'S FIND EMMA A TUTOR." ],
                'hide_illustration'       => [ 'type' => 'switcher', 'label' => 'Hide illustration box' ],
                'hide_illustration_label' => [ 'type' => 'switcher', 'label' => 'Hide illustration label' ],
                'illustration_label'      => [ 'type' => 'text', 'label' => 'Illustration label', 'default' => 'Empty illustration' ],
                'hide_ready_title'        => [ 'type' => 'switcher', 'label' => 'Hide ready title' ],
                'ready_title'             => [ 'type' => 'text', 'label' => 'Ready title', 'default' => 'Your dashboard is ready' ],
                'hide_ready_body'         => [ 'type' => 'switcher', 'label' => 'Hide ready body' ],
                'ready_body'              => [ 'type' => 'textarea', 'label' => 'Ready body', 'default' => 'KPIS, LESSON LOGS & PROGRESS APPEAR AFTER YOUR FIRST LESSON.' ],
                'hide_primary'            => [ 'type' => 'switcher', 'label' => 'Hide primary button' ],
                'primary_label'           => [ 'type' => 'text', 'label' => 'Primary button label', 'default' => 'Find a Tutor - S02' ],
                'primary_url'             => [ 'type' => 'text', 'label' => 'Primary button URL', 'default' => '/student/teachers/' ],
                'hide_secondary'          => [ 'type' => 'switcher', 'label' => 'Hide secondary button' ],
                'secondary_label'         => [ 'type' => 'text', 'label' => 'Secondary button label', 'default' => '+ Add another child' ],
                'secondary_url'           => [ 'type' => 'text', 'label' => 'Secondary button URL', 'default' => '#' ],
                'hide_note'               => [ 'type' => 'switcher', 'label' => 'Hide bottom note' ],
                'note'                    => [ 'type' => 'textarea', 'label' => 'Bottom note', 'default' => 'EMPTY STATE REPLACES ALL DATA WIDGETS WITH ONBOARDING CTA. CHILD SWITCHER + ACCOUNT REMAIN AVAILABLE.' ],
            ],
            'style_targets' => [
                'shell'       => [ 'label' => 'Dashboard Shell Box', 'selector' => '.mgk-parent-empty-dashboard__shell', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'headerbox'   => [ 'label' => 'Header Box', 'selector' => '.mgk-parent-empty-dashboard__header', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'headline'    => [ 'label' => 'Headline Row', 'selector' => '.mgk-parent-empty-dashboard__header h1', 'features' => [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ],
                'greeting'    => mgk_style_text( 'Greeting', '.mgk-parent-empty-dashboard__greeting', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'name'        => mgk_style_text( 'Parent Name', '.mgk-parent-empty-dashboard__name', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'wave'        => mgk_style_text( 'Wave / Icon', '.mgk-parent-empty-dashboard__wave', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'subline'     => mgk_style_text( 'Subline', '.mgk-parent-empty-dashboard__subline', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'bodybox'     => [ 'label' => 'Body Box', 'selector' => '.mgk-parent-empty-dashboard__body', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'illustration'=> [ 'label' => 'Illustration Box', 'selector' => '.mgk-parent-empty-dashboard__illustration', 'features' => [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'copybox'     => [ 'label' => 'Ready Copy Box', 'selector' => '.mgk-parent-empty-dashboard__copy', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'readytitle'  => mgk_style_text( 'Ready Title', '.mgk-parent-empty-dashboard__copy strong', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'readybody'   => mgk_style_text( 'Ready Body', '.mgk-parent-empty-dashboard__copy p', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'actions'     => [ 'label' => 'Actions Box', 'selector' => '.mgk-parent-empty-dashboard__actions', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'primary'     => mgk_style_button( 'Find Tutor Button', '.mgk-parent-empty-dashboard__primary' ),
                'secondary'   => mgk_style_button( 'Add Child Button', '.mgk-parent-empty-dashboard__secondary' ),
                'note'        => mgk_style_text( 'Bottom Note', '.mgk-parent-empty-dashboard__note', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'section'     => [ 'label' => 'Section Box', 'selector' => '.mgk-parent-empty-dashboard', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
            ],
        ];
    }

    $parent_dashboard_widgets = [
        'mgk_parent_dash_welcome' => [
            'MGK Parent · Welcome + Child Switcher',
            'eicon-user-preferences',
            [
                'hidden'          => [ 'type' => 'switcher', 'label' => 'Hide entire section' ],
                'welcome_prefix'  => [ 'type' => 'text', 'label' => 'Welcome prefix', 'default' => 'Welcome back,' ],
                'hide_subline'    => [ 'type' => 'switcher', 'label' => 'Hide date/status line' ],
                'hide_switcher'   => [ 'type' => 'switcher', 'label' => 'Hide child switcher' ],
                'viewing_label'   => [ 'type' => 'text', 'label' => 'Viewing label', 'default' => 'VIEWING' ],
                'add_child_label' => [ 'type' => 'text', 'label' => 'Add child label', 'default' => '+ Add child' ],
            ],
            [
                'shell'     => [ 'label' => 'Section Shell', 'selector' => '.mgk-parent-dashboard-welcome .mgk-parent-dashboard__shell', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'heading'   => mgk_style_text( 'Welcome Heading', '.mgk-parent-dashboard-welcome__copy h1', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'prefix'    => mgk_style_text( 'Welcome Prefix', '.mgk-parent-dashboard-welcome__copy h1 span', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'name'      => mgk_style_text( 'Parent Name', '.mgk-parent-dashboard-welcome__copy h1 strong', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'subline'   => mgk_style_text( 'Date / Status Line', '.mgk-parent-dashboard-welcome__copy p', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'switcher'  => [ 'label' => 'Child Switcher Box', 'selector' => '.mgk-parent-dashboard-switcher', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'child'     => [ 'label' => 'Child Cards', 'selector' => '.mgk-parent-dashboard-switcher button, .mgk-parent-dashboard-switcher__add', 'features' => [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'active'    => [ 'label' => 'Active Child Card', 'selector' => '.mgk-parent-dashboard-switcher button.is-active', 'features' => [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'section'   => [ 'label' => 'Section Box', 'selector' => '.mgk-parent-dashboard-welcome', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
            ],
        ],
        'mgk_parent_dash_renewal' => [
            'MGK Parent · Renewal Nudge',
            'eicon-alert',
            [
                'hidden'       => [ 'type' => 'switcher', 'label' => 'Hide entire section' ],
                'renew_label'  => [ 'type' => 'text', 'label' => 'Renew button label', 'default' => 'Renew Package →' ],
                'hide_snooze'  => [ 'type' => 'switcher', 'label' => 'Hide snooze button' ],
                'snooze_label' => [ 'type' => 'text', 'label' => 'Snooze button label', 'default' => 'Snooze 7d ×' ],
            ],
            [
                'shell'   => [ 'label' => 'Nudge Box', 'selector' => '.mgk-parent-dashboard-renewal .mgk-parent-dashboard__shell', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'title'   => mgk_style_text( 'Nudge Title', '.mgk-parent-dashboard-renewal__copy strong', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'subline' => mgk_style_text( 'Nudge Subline', '.mgk-parent-dashboard-renewal__copy p', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'actions' => [ 'label' => 'Actions Box', 'selector' => '.mgk-parent-dashboard-renewal__actions', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'renew'   => mgk_style_button( 'Renew Button', '.mgk-parent-dashboard-renewal .mgk-parent-dashboard-btn--red' ),
                'snooze'  => mgk_style_button( 'Snooze Button', '.mgk-parent-dashboard-renewal .mgk-parent-dashboard-btn--outline' ),
                'section' => [ 'label' => 'Section Box', 'selector' => '.mgk-parent-dashboard-renewal', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
            ],
        ],
        'mgk_parent_dash_kpis' => [
            'MGK Parent · KPI Tiles',
            'eicon-counter',
            [ 'hidden' => [ 'type' => 'switcher', 'label' => 'Hide entire section' ] ],
            [
                'shell'   => [ 'label' => 'Section Shell', 'selector' => '.mgk-parent-dashboard-kpis .mgk-parent-dashboard__shell', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'grid'    => [ 'label' => 'KPI Grid', 'selector' => '.mgk-parent-dashboard-kpis__grid', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'tile'    => [ 'label' => 'KPI Tile Box', 'selector' => '.mgk-parent-dashboard-kpi', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'value'   => mgk_style_text( 'KPI Value', '.mgk-parent-dashboard-kpi strong', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'label'   => mgk_style_text( 'KPI Label', '.mgk-parent-dashboard-kpi span', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'section' => [ 'label' => 'Section Box', 'selector' => '.mgk-parent-dashboard-kpis', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
            ],
        ],
        'mgk_parent_dash_progress_logs' => [
            'MGK Parent · Progress + Lesson Log',
            'eicon-post-list',
            [
                'hidden'      => [ 'type' => 'switcher', 'label' => 'Hide entire section' ],
                'logs_button' => [ 'type' => 'text', 'label' => 'Lesson logs button label', 'default' => 'View all lesson logs →' ],
            ],
            [
                'shell'      => [ 'label' => 'Two Column Shell', 'selector' => '.mgk-parent-dashboard-progress-logs .mgk-parent-dashboard__shell', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'card'       => [ 'label' => 'Card Boxes', 'selector' => '.mgk-parent-dashboard-progress-card, .mgk-parent-dashboard-log-card', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'head'       => [ 'label' => 'Card Headers', 'selector' => '.mgk-parent-dashboard-card-head', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'heading'    => mgk_style_text( 'Card Heading', '.mgk-parent-dashboard-card-head h2', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'range'      => mgk_style_button( 'Range Button', '.mgk-parent-dashboard-card-head button' ),
                'chart'      => [ 'label' => 'Chart Placeholder', 'selector' => '.mgk-parent-dashboard-chart', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'chartnote'  => mgk_style_text( 'Chart Description', '.mgk-parent-dashboard-chart-note', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'legend'     => mgk_style_text( 'Chart Legend', '.mgk-parent-dashboard-legend', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'logpreview' => [ 'label' => 'Lesson Log Preview Box', 'selector' => '.mgk-parent-dashboard-log-preview', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'logtext'    => mgk_style_text( 'Lesson Log Text', '.mgk-parent-dashboard-log-preview', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'button'     => mgk_style_button( 'View Logs Button', '.mgk-parent-dashboard-log-card .mgk-parent-dashboard-btn' ),
                'section'    => [ 'label' => 'Section Box', 'selector' => '.mgk-parent-dashboard-progress-logs', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
            ],
        ],
        'mgk_parent_dash_upcoming' => [
            'MGK Parent · Upcoming Lessons',
            'eicon-calendar',
            [
                'hidden'         => [ 'type' => 'switcher', 'label' => 'Hide entire section' ],
                'heading'        => [ 'type' => 'text', 'label' => 'Heading', 'default' => 'Upcoming lessons' ],
                'all_label'      => [ 'type' => 'text', 'label' => 'All children label', 'default' => 'ALL CHILDREN' ],
                'calendar_label' => [ 'type' => 'text', 'label' => 'Calendar sync label', 'default' => '+ CALENDAR SYNC' ],
                'book_label'     => [ 'type' => 'text', 'label' => 'Book next label', 'default' => '+ BOOK NEXT LESSON' ],
                'reschedule'     => [ 'type' => 'text', 'label' => 'Reschedule label', 'default' => 'Reschedule' ],
                'message'        => [ 'type' => 'text', 'label' => 'Message label', 'default' => 'Message' ],
            ],
            [
                'shell'    => [ 'label' => 'Section Shell', 'selector' => '.mgk-parent-dashboard-upcoming .mgk-parent-dashboard__shell', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'head'     => [ 'label' => 'Section Header', 'selector' => '.mgk-parent-dashboard-section-head', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'heading'  => mgk_style_text( 'Heading', '.mgk-parent-dashboard-section-head h2', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'filters'  => mgk_style_button( 'Header Filter Buttons', '.mgk-parent-dashboard-section-head button' ),
                'grid'     => [ 'label' => 'Lessons Grid', 'selector' => '.mgk-parent-dashboard-upcoming__grid', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'card'     => [ 'label' => 'Lesson Cards', 'selector' => '.mgk-parent-dashboard-lesson-card', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'cardtext' => mgk_style_text( 'Lesson Card Text', '.mgk-parent-dashboard-lesson-card', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'buttons'  => mgk_style_button( 'Lesson Action Buttons', '.mgk-parent-dashboard-lesson-card a' ),
                'book'     => mgk_style_button( 'Book Next Card', '.mgk-parent-dashboard-book-next' ),
                'section'  => [ 'label' => 'Section Box', 'selector' => '.mgk-parent-dashboard-upcoming', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
            ],
        ],
        'mgk_parent_dash_action_cards' => [
            'MGK Parent · Billing Message Buy',
            'eicon-price-table',
            [
                'hidden'          => [ 'type' => 'switcher', 'label' => 'Hide entire section' ],
                'billing_heading' => [ 'type' => 'text', 'label' => 'Billing heading', 'default' => 'Billing & package' ],
                'invoice_label'   => [ 'type' => 'text', 'label' => 'Invoice button label', 'default' => 'View invoices / receipts' ],
                'message_heading' => [ 'type' => 'text', 'label' => 'Message heading', 'default' => 'Message tutor' ],
                'chat_label'      => [ 'type' => 'text', 'label' => 'Chat button label', 'default' => 'Open chat →' ],
                'message_note'    => [ 'type' => 'text', 'label' => 'Message note', 'default' => 'AGENCY-MONITORED · PHONE MASKED' ],
                'buy_heading'     => [ 'type' => 'text', 'label' => 'Buy heading', 'default' => 'Need more lessons?' ],
                'buy_copy'        => [ 'type' => 'textarea', 'label' => 'Buy copy', 'default' => 'BUY A NEW PACKAGE & SAVE UP TO 10%' ],
                'buy_label'       => [ 'type' => 'text', 'label' => 'Buy button label', 'default' => 'Buy Package (FR-BOOK-08) →' ],
                'buy_note'        => [ 'type' => 'text', 'label' => 'Buy note', 'default' => 'RETURNING-STUDENT 5% MAY APPLY (BR-06)' ],
            ],
            [
                'shell'   => [ 'label' => 'Cards Row Shell', 'selector' => '.mgk-parent-dashboard-actions-row .mgk-parent-dashboard__shell', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'card'    => [ 'label' => 'Card Boxes', 'selector' => '.mgk-parent-dashboard-action-card', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'buycard' => [ 'label' => 'Buy Package Card', 'selector' => '.mgk-parent-dashboard-action-card--buy', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'heading' => mgk_style_text( 'Card Headings', '.mgk-parent-dashboard-action-card h2', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'body'    => mgk_style_text( 'Card Body Text', '.mgk-parent-dashboard-action-card p, .mgk-parent-dashboard-billing-line, .mgk-parent-dashboard-message-tutor', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'bar'     => [ 'label' => 'Package Progress Bar', 'selector' => '.mgk-parent-dashboard-package-bar', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'button'  => mgk_style_button( 'Action Buttons', '.mgk-parent-dashboard-action-card .mgk-parent-dashboard-btn' ),
                'section' => [ 'label' => 'Section Box', 'selector' => '.mgk-parent-dashboard-actions-row', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
            ],
        ],
        'mgk_parent_dash_quick_links' => [
            'MGK Parent · Quick Links',
            'eicon-link',
            [ 'hidden' => [ 'type' => 'switcher', 'label' => 'Hide entire section' ] ],
            [
                'shell'   => [ 'label' => 'Quick Links Shell', 'selector' => '.mgk-parent-dashboard-quick-links .mgk-parent-dashboard__shell', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'card'    => [ 'label' => 'Quick Link Cards', 'selector' => '.mgk-parent-dashboard-quick-links a', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow', 'hover' ] ],
                'icon'    => mgk_style_text( 'Icons', '.mgk-parent-dashboard-quick-links span', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'label'   => mgk_style_text( 'Labels', '.mgk-parent-dashboard-quick-links strong', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'note'    => mgk_style_text( 'Route Notes', '.mgk-parent-dashboard-quick-links em', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'section' => [ 'label' => 'Section Box', 'selector' => '.mgk-parent-dashboard-quick-links', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
            ],
        ],
        'mgk_parent_dash_footer' => [
            'MGK Parent · Dashboard Footer',
            'eicon-footer',
            [
                'hidden' => [ 'type' => 'switcher', 'label' => 'Hide entire section' ],
                'logo'   => [ 'type' => 'text', 'label' => 'Footer logo text', 'default' => '[AGENCY LOGO]' ],
                'line'   => [ 'type' => 'textarea', 'label' => 'Footer line', 'default' => '© 2026 · Powered by Margick · MOE Registered · PDPA compliant' ],
            ],
            [
                'shell'   => [ 'label' => 'Footer Box', 'selector' => '.mgk-parent-dashboard-footer .mgk-parent-dashboard__shell', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'logo'    => mgk_style_text( 'Footer Logo Text', '.mgk-parent-dashboard-footer strong', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'line'    => mgk_style_text( 'Footer Compliance Line', '.mgk-parent-dashboard-footer p', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'section' => [ 'label' => 'Section Box', 'selector' => '.mgk-parent-dashboard-footer', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
            ],
        ],
    ];

    foreach ( $parent_dashboard_widgets as $tag => $meta ) {
        if ( shortcode_exists( $tag ) ) {
            $sections[] = [
                'tag'           => $tag,
                'title'         => $meta[0],
                'icon'          => $meta[1],
                'controls'      => $meta[2],
                'style_targets' => $meta[3],
            ];
        }
    }

    $parent_package_widgets = [
        'mgk_parent_package_context' => [
            'MGK Parent · Package Context',
            'eicon-info-circle-o',
            [
                'hidden'         => [ 'type' => 'switcher', 'label' => 'Hide entire section' ],
                'hide_sec_label' => [ 'type' => 'switcher', 'label' => 'Hide section tag' ],
                'sec_label'      => [ 'type' => 'text', 'label' => 'Section tag', 'default' => 'SEC 1 Context' ],
                'hide_avatar'    => [ 'type' => 'switcher', 'label' => 'Hide child avatar' ],
                'hide_headline'  => [ 'type' => 'switcher', 'label' => 'Hide headline' ],
                'hide_meta'      => [ 'type' => 'switcher', 'label' => 'Hide package meta' ],
                'hide_prompt'    => [ 'type' => 'switcher', 'label' => 'Hide prompt line' ],
            ],
            [
                'shell'    => [ 'label' => 'Context Shell', 'selector' => '.mgk-parent-package-context .mgk-parent-package__shell', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'tag'      => mgk_style_text( 'Section Tag', '.mgk-parent-package-context .mgk-parent-package-sec', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border', 'shadow' ] ),
                'row'      => [ 'label' => 'Context Row', 'selector' => '.mgk-parent-package-context__row', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'avatar'   => [ 'label' => 'Child Avatar', 'selector' => '.mgk-parent-package-avatar', 'features' => [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'headline' => mgk_style_text( 'Headline', '.mgk-parent-package-context h1', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'meta'     => mgk_style_text( 'Package Meta', '.mgk-parent-package-context__meta', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'prompt'   => mgk_style_text( 'Prompt Line', '.mgk-parent-package-context__prompt', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'section'  => [ 'label' => 'Section Box', 'selector' => '.mgk-parent-package-context', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
            ],
        ],
        'mgk_parent_package_options' => [
            'MGK Parent · Package Options',
            'eicon-price-table',
            [
                'hidden'          => [ 'type' => 'switcher', 'label' => 'Hide entire section' ],
                'hide_sec_label'  => [ 'type' => 'switcher', 'label' => 'Hide section tag' ],
                'sec_label'       => [ 'type' => 'text', 'label' => 'Section tag', 'default' => 'SEC 2 Five Options (equal weight)' ],
                'hide_card_icons' => [ 'type' => 'switcher', 'label' => 'Hide card icons' ],
                'hide_prices'     => [ 'type' => 'switcher', 'label' => 'Hide price/value rows' ],
                'hide_details'    => [ 'type' => 'switcher', 'label' => 'Hide detail rows' ],
                'hide_buttons'    => [ 'type' => 'switcher', 'label' => 'Hide option buttons' ],
                'hide_note'       => [ 'type' => 'switcher', 'label' => 'Hide review note' ],
                'note'            => [ 'type' => 'textarea', 'label' => 'Review note', 'default' => '☼ ALL 5 BUTTONS SAME SIZE/CONTRAST. "END" IS NOT GREYED, HIDDEN, OR BURIED (FR-REVIEW-05). NO COUNTDOWN TIMERS, NO PRE-TICKED ADD-ONS.' ],
            ],
            [
                'shell'    => [ 'label' => 'Options Shell', 'selector' => '.mgk-parent-package-options .mgk-parent-package__shell', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'tag'      => mgk_style_text( 'Section Tag', '.mgk-parent-package-options .mgk-parent-package-sec', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border', 'shadow' ] ),
                'grid'     => [ 'label' => 'Options Grid', 'selector' => '.mgk-parent-package-options__grid', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'card'     => [ 'label' => 'Option Card Box', 'selector' => '.mgk-parent-package-option', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow', 'hover' ] ],
                'featured' => [ 'label' => 'Featured Option Card', 'selector' => '.mgk-parent-package-option.is-featured', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'head'     => [ 'label' => 'Card Header Row', 'selector' => '.mgk-parent-package-option header', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'icon'     => mgk_style_text( 'Option Icons', '.mgk-parent-package-option header span', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'title'    => mgk_style_text( 'Option Titles', '.mgk-parent-package-option h2', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'summary'  => mgk_style_text( 'Option Summary', '.mgk-parent-package-option p', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'price'    => mgk_style_text( 'Price / Value', '.mgk-parent-package-option strong', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'detail'   => mgk_style_text( 'Option Detail', '.mgk-parent-package-option em', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'button'   => mgk_style_button( 'Option Buttons', '.mgk-parent-package-option a' ),
                'note'     => mgk_style_text( 'Review Note', '.mgk-parent-package-options__note', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'section'  => [ 'label' => 'Section Box', 'selector' => '.mgk-parent-package-options', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
            ],
        ],
        'mgk_parent_package_pause' => [
            'MGK Parent · Pause Detail',
            'eicon-pause',
            [
                'hidden'             => [ 'type' => 'switcher', 'label' => 'Hide entire section' ],
                'hide_sec_label'     => [ 'type' => 'switcher', 'label' => 'Hide section tag' ],
                'sec_label'          => [ 'type' => 'text', 'label' => 'Section tag', 'default' => 'SEC 3 Pause detail (BR-16)' ],
                'hide_heading'       => [ 'type' => 'switcher', 'label' => 'Hide heading' ],
                'heading'            => [ 'type' => 'text', 'label' => 'Heading', 'default' => 'II Pause conditions (BR-16)' ],
                'hide_conditions'    => [ 'type' => 'switcher', 'label' => 'Hide condition cards' ],
                'hide_date_controls' => [ 'type' => 'switcher', 'label' => 'Hide date controls' ],
                'pause_from_label'   => [ 'type' => 'text', 'label' => 'Pause from label', 'default' => 'Pause from:' ],
                'resume_label'       => [ 'type' => 'text', 'label' => 'Resume label', 'default' => 'Resume:' ],
                'confirm_label'      => [ 'type' => 'text', 'label' => 'Confirm button label', 'default' => 'Confirm pause' ],
                'hide_footer'        => [ 'type' => 'switcher', 'label' => 'Hide footer note' ],
                'footer'             => [ 'type' => 'textarea', 'label' => 'Footer note', 'default' => 'Need help deciding? Message the agency · No charge until you confirm a choice.' ],
            ],
            [
                'shell'      => [ 'label' => 'Pause Shell', 'selector' => '.mgk-parent-package-pause .mgk-parent-package__shell', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'tag'        => mgk_style_text( 'Section Tag', '.mgk-parent-package-pause .mgk-parent-package-sec', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border', 'shadow' ] ),
                'box'        => [ 'label' => 'Pause Detail Box', 'selector' => '.mgk-parent-package-pause__box', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'heading'    => mgk_style_text( 'Pause Heading', '.mgk-parent-package-pause__box h2', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'rulesgrid'  => [ 'label' => 'Condition Grid', 'selector' => '.mgk-parent-package-pause__rules', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'rules'      => mgk_style_text( 'Condition Cards', '.mgk-parent-package-pause__rules span', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border', 'shadow' ] ),
                'actions'    => [ 'label' => 'Pause Actions Row', 'selector' => '.mgk-parent-package-pause__actions', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'datebutton' => mgk_style_button( 'Date Buttons', '.mgk-parent-package-pause__actions button' ),
                'confirm'    => mgk_style_button( 'Confirm Button', '.mgk-parent-package-pause__confirm' ),
                'footer'     => mgk_style_text( 'Footer Note', '.mgk-parent-package-pause__footer', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'section'    => [ 'label' => 'Section Box', 'selector' => '.mgk-parent-package-pause', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
            ],
        ],
        'mgk_parent_package_switch' => [
            'MGK Parent · Switch Tutor',
            'eicon-sync',
            [
                'hidden'              => [ 'type' => 'switcher', 'label' => 'Hide entire section' ],
                'hide_heading'        => [ 'type' => 'switcher', 'label' => 'Hide heading' ],
                'heading'             => [ 'type' => 'text', 'label' => 'Heading', 'default' => 'Switch to a new tutor' ],
                'hide_subline'        => [ 'type' => 'switcher', 'label' => 'Hide subline' ],
                'subline'             => [ 'type' => 'textarea', 'label' => 'Subline', 'default' => "KEEP EMMA'S REMAINING 2 LESSONS · WE RE-MATCH TO A SIMILAR TUTOR (FR-REVIEW-06)" ],
                'hide_reason_heading' => [ 'type' => 'switcher', 'label' => 'Hide reason heading' ],
                'reason_heading'      => [ 'type' => 'text', 'label' => 'Reason heading', 'default' => 'Why are you switching? (optional, helps re-match)' ],
                'hide_chips'          => [ 'type' => 'switcher', 'label' => 'Hide reason chips' ],
                'chip_1'              => [ 'type' => 'text', 'label' => 'Chip 1', 'default' => 'Scheduling' ],
                'chip_2'              => [ 'type' => 'text', 'label' => 'Chip 2', 'default' => 'Teaching style' ],
                'chip_3'              => [ 'type' => 'text', 'label' => 'Chip 3', 'default' => 'Location' ],
                'chip_4'              => [ 'type' => 'text', 'label' => 'Chip 4', 'default' => 'Other' ],
                'hide_tutors'         => [ 'type' => 'switcher', 'label' => 'Hide tutor suggestions' ],
                'hide_button'         => [ 'type' => 'switcher', 'label' => 'Hide request button' ],
                'button'              => [ 'type' => 'text', 'label' => 'Button label', 'default' => 'Request re-match (free) →' ],
                'button_url'          => [ 'type' => 'text', 'label' => 'Button URL', 'default' => '#' ],
                'hide_note'           => [ 'type' => 'switcher', 'label' => 'Hide bottom note' ],
                'note'                => [ 'type' => 'textarea', 'label' => 'Bottom note', 'default' => 'REMAINING LESSONS TRANSFER TO NEW TUTOR. REASON CHIPS FEED MATCHING ALGORITHM. NO FEE TO SWITCH.' ],
            ],
            [
                'shell'      => [ 'label' => 'Switch Shell', 'selector' => '.mgk-parent-package-switch__shell', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'header'     => [ 'label' => 'Header Box', 'selector' => '.mgk-parent-package-switch__header', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'heading'    => mgk_style_text( 'Heading', '.mgk-parent-package-switch__header h1', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'subline'    => mgk_style_text( 'Subline', '.mgk-parent-package-switch__header p', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'body'       => [ 'label' => 'Body Box', 'selector' => '.mgk-parent-package-switch__body', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'reason'     => mgk_style_text( 'Reason Heading', '.mgk-parent-package-switch__body h2', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'chipsrow'   => [ 'label' => 'Reason Chips Row', 'selector' => '.mgk-parent-package-switch__chips', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'chips'      => mgk_style_button( 'Reason Chips', '.mgk-parent-package-switch__chips button' ),
                'grid'       => [ 'label' => 'Tutor Suggestions Grid', 'selector' => '.mgk-parent-package-switch__tutors', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'card'       => [ 'label' => 'Tutor Card Box', 'selector' => '.mgk-parent-package-switch-card', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow', 'hover' ] ],
                'image'      => [ 'label' => 'Tutor Image Placeholder', 'selector' => '.mgk-parent-package-switch-card__image', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'name'       => mgk_style_text( 'Tutor Name', '.mgk-parent-package-switch-card strong', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'rating'     => mgk_style_text( 'Tutor Rating / Subject', '.mgk-parent-package-switch-card p', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'cta'        => mgk_style_button( 'Request Re-match Button', '.mgk-parent-package-switch__cta' ),
                'note'       => mgk_style_text( 'Bottom Note', '.mgk-parent-package-switch__note', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'section'    => [ 'label' => 'Section Box', 'selector' => '.mgk-parent-package-switch', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
            ],
        ],
        'mgk_parent_package_end' => [
            'MGK Parent · End Tuition',
            'eicon-close-circle',
            [
                'hidden'           => [ 'type' => 'switcher', 'label' => 'Hide entire section' ],
                'hide_heading'     => [ 'type' => 'switcher', 'label' => 'Hide heading' ],
                'heading'          => [ 'type' => 'text', 'label' => 'Heading', 'default' => 'End tuition for Emma?' ],
                'hide_subline'     => [ 'type' => 'switcher', 'label' => 'Hide subline' ],
                'subline'          => [ 'type' => 'textarea', 'label' => 'Subline', 'default' => "YOU'RE CHOOSING TO STOP AFTER THE CURRENT PACKAGE. THAT'S COMPLETELY FINE." ],
                'hide_facts'       => [ 'type' => 'switcher', 'label' => 'Hide facts box' ],
                'hide_actions'     => [ 'type' => 'switcher', 'label' => 'Hide action buttons' ],
                'keep_label'       => [ 'type' => 'text', 'label' => 'Keep button label', 'default' => 'Keep my package' ],
                'keep_url'         => [ 'type' => 'text', 'label' => 'Keep button URL', 'default' => '/parent/trial/' ],
                'confirm_label'    => [ 'type' => 'text', 'label' => 'Confirm button label', 'default' => 'Confirm end' ],
                'confirm_url'      => [ 'type' => 'text', 'label' => 'Confirm button URL', 'default' => '/parent/trial/lapsed/' ],
                'hide_equal_note'  => [ 'type' => 'switcher', 'label' => 'Hide equal buttons note' ],
                'equal_note'       => [ 'type' => 'textarea', 'label' => 'Equal buttons note', 'default' => 'NO "ARE YOU SURE YOU\'LL REGRET THIS?" COPY. BOTH BUTTONS EQUAL. OPTIONAL 1-TAP REASON (SKIPPABLE).' ],
                'hide_bottom_note' => [ 'type' => 'switcher', 'label' => 'Hide bottom note' ],
                'bottom_note'      => [ 'type' => 'textarea', 'label' => 'Bottom note', 'default' => 'FR-REVIEW-05: CONFIRM STEP GIVES FACTS (REFUND, DATA, RETURN), NOT FRICTION OR GUILT.' ],
            ],
            [
                'shell'      => [ 'label' => 'End Shell', 'selector' => '.mgk-parent-package-end__shell', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'header'     => [ 'label' => 'Header Box', 'selector' => '.mgk-parent-package-end__header', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'heading'    => mgk_style_text( 'Heading', '.mgk-parent-package-end__header h1', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'subline'    => mgk_style_text( 'Subline', '.mgk-parent-package-end__header p', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'body'       => [ 'label' => 'Body Box', 'selector' => '.mgk-parent-package-end__body', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'facts'      => mgk_style_text( 'Facts Box', '.mgk-parent-package-end__facts', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border', 'shadow' ] ),
                'actions'    => [ 'label' => 'Buttons Row', 'selector' => '.mgk-parent-package-end__actions', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'keep'       => mgk_style_button( 'Keep Package Button', '.mgk-parent-package-end__keep' ),
                'confirm'    => mgk_style_button( 'Confirm End Button', '.mgk-parent-package-end__confirm' ),
                'equalnote'  => mgk_style_text( 'Equal Buttons Note', '.mgk-parent-package-end__equal-note', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'bottomnote' => mgk_style_text( 'Bottom Note', '.mgk-parent-package-end__bottom-note', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'section'    => [ 'label' => 'Section Box', 'selector' => '.mgk-parent-package-end', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
            ],
        ],
        'mgk_parent_package_lapsed' => [
            'MGK Parent · Lapsed Package',
            'eicon-history',
            [
                'hidden'           => [ 'type' => 'switcher', 'label' => 'Hide entire section' ],
                'hide_badge'       => [ 'type' => 'switcher', 'label' => 'Hide variant badge' ],
                'badge'            => [ 'type' => 'text', 'label' => 'Variant badge', 'default' => 'Lapsed variant' ],
                'hide_heading'     => [ 'type' => 'switcher', 'label' => 'Hide heading' ],
                'heading'          => [ 'type' => 'text', 'label' => 'Heading', 'default' => 'Welcome back — pick up where Emma left off?' ],
                'hide_subline'     => [ 'type' => 'switcher', 'label' => 'Hide subline' ],
                'subline'          => [ 'type' => 'textarea', 'label' => 'Subline', 'default' => 'YOUR PACKAGE LAPSED 14 DAYS AGO. AS A RETURNING STUDENT, ENJOY 5% OFF YOUR NEXT PACKAGE (BR-06).' ],
                'hide_tutor'       => [ 'type' => 'switcher', 'label' => 'Hide tutor availability' ],
                'hide_primary'     => [ 'type' => 'switcher', 'label' => 'Hide reactivate button' ],
                'primary_label'    => [ 'type' => 'text', 'label' => 'Reactivate button label', 'default' => 'Reactivate with Ms Lee →' ],
                'hide_secondary'   => [ 'type' => 'switcher', 'label' => 'Hide different tutor button' ],
                'secondary_label'  => [ 'type' => 'text', 'label' => 'Different tutor button label', 'default' => 'Choose a different tutor' ],
                'hide_discount'    => [ 'type' => 'switcher', 'label' => 'Hide discount note' ],
                'hide_bottom_note' => [ 'type' => 'switcher', 'label' => 'Hide bottom note' ],
                'bottom_note'      => [ 'type' => 'textarea', 'label' => 'Bottom note', 'default' => 'FR-REVIEW-07 WIN-BACK: GENTLE RE-ENGAGEMENT FOR LAPSED PARENTS. 5% RETURNING DISCOUNT (BR-06). STILL NO DARK PATTERNS - OPT-IN, DISMISSABLE.' ],
            ],
            [
                'shell'      => [ 'label' => 'Lapsed Shell', 'selector' => '.mgk-parent-package-lapsed__shell', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'panel'      => [ 'label' => 'Lapsed Panel Box', 'selector' => '.mgk-parent-package-lapsed__panel', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'badge'      => mgk_style_text( 'Variant Badge', '.mgk-parent-package-lapsed__badge', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'header'     => [ 'label' => 'Header Box', 'selector' => '.mgk-parent-package-lapsed__header', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'heading'    => mgk_style_text( 'Heading', '.mgk-parent-package-lapsed__header h1', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'subline'    => mgk_style_text( 'Subline', '.mgk-parent-package-lapsed__header p', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'tutorrow'   => [ 'label' => 'Tutor Availability Row', 'selector' => '.mgk-parent-package-lapsed__tutor', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'avatar'     => [ 'label' => 'Tutor Avatar', 'selector' => '.mgk-parent-package-lapsed__avatar', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'availability' => mgk_style_text( 'Availability Title', '.mgk-parent-package-lapsed__tutor strong', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'slotnote'   => mgk_style_text( 'Slot Note', '.mgk-parent-package-lapsed__tutor p', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'actions'    => [ 'label' => 'Actions Row', 'selector' => '.mgk-parent-package-lapsed__actions', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'primary'    => mgk_style_button( 'Reactivate Button', '.mgk-parent-package-lapsed__primary' ),
                'secondary'  => mgk_style_button( 'Different Tutor Button', '.mgk-parent-package-lapsed__secondary' ),
                'discount'   => mgk_style_text( 'Discount Note', '.mgk-parent-package-lapsed__discount', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'bottomnote' => mgk_style_text( 'Bottom Note', '.mgk-parent-package-lapsed__bottom-note', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'section'    => [ 'label' => 'Section Box', 'selector' => '.mgk-parent-package-lapsed', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
            ],
        ],
    ];

    foreach ( $parent_package_widgets as $tag => $meta ) {
        if ( shortcode_exists( $tag ) ) {
            $sections[] = [
                'tag'           => $tag,
                'title'         => $meta[0],
                'icon'          => $meta[1],
                'controls'      => $meta[2],
                'style_targets' => $meta[3],
            ];
        }
    }

    if ( shortcode_exists( 'mgk_parent_review' ) ) {
        $sections[] = [
            'tag'      => 'mgk_parent_review',
            'title'    => 'MGK Parent · Review',
            'icon'     => 'eicon-star',
            'controls' => [
                'hidden'                 => [ 'type' => 'switcher', 'label' => 'Hide entire section' ],
                'preview_state'          => [ 'type' => 'select', 'label' => 'Preview state', 'default' => '', 'options' => [
                    ''             => 'Use URL state',
                    'post-trial'   => 'Post-trial form',
                    'post-package' => 'Post-package form',
                    'submitted'    => 'Submitted state',
                    'not-eligible' => 'Not-eligible state',
                ] ],
                'hide_prompt'            => [ 'type' => 'switcher', 'label' => 'Hide prompt section' ],
                'hide_avatar'            => [ 'type' => 'switcher', 'label' => 'Hide avatar' ],
                'sec_prompt'             => [ 'type' => 'text', 'label' => 'Prompt section tag', 'default' => 'SEC 1 Prompt' ],
                'prompt_title'           => [ 'type' => 'text', 'label' => 'Prompt title', 'default' => "How was {child}'s trial with {teacher}?" ],
                'prompt_meta'            => [ 'type' => 'textarea', 'label' => 'Prompt meta', 'default' => 'TRIAL COMPLETED {date} · PROMPT AVAILABLE 24H AFTER (FR-REVIEW-01)' ],
                'hide_post_trial'        => [ 'type' => 'switcher', 'label' => 'Hide post-trial form' ],
                'sec_post_trial'         => [ 'type' => 'text', 'label' => 'Post-trial section tag', 'default' => 'SEC 2 Post-trial form' ],
                'rating_label'           => [ 'type' => 'text', 'label' => 'Rating label', 'default' => 'Your rating' ],
                'rating_hint'            => [ 'type' => 'text', 'label' => 'Rating hint', 'default' => 'TAP TO RATE 1-5' ],
                'comment_label'          => [ 'type' => 'text', 'label' => 'Comment label', 'default' => 'Add a comment' ],
                'comment_optional'       => [ 'type' => 'text', 'label' => 'Optional text', 'default' => '(optional)' ],
                'comment_placeholder'    => [ 'type' => 'textarea', 'label' => 'Comment placeholder', 'default' => 'WHAT WENT WELL? ANYTHING TO IMPROVE?' ],
                'submit_label'           => [ 'type' => 'text', 'label' => 'Submit button label', 'default' => 'Submit review' ],
                'skip_label'             => [ 'type' => 'text', 'label' => 'Skip button label', 'default' => 'Skip for now' ],
                'hide_post_package'      => [ 'type' => 'switcher', 'label' => 'Hide post-package form' ],
                'sec_post_package'       => [ 'type' => 'text', 'label' => 'Post-package section tag', 'default' => '4 dimensions' ],
                'package_heading'        => [ 'type' => 'text', 'label' => 'Post-package heading', 'default' => 'Rate your full experience with {teacher}' ],
                'package_subline'        => [ 'type' => 'textarea', 'label' => 'Post-package meta', 'default' => 'PACKAGE COMPLETE · 16 LESSONS · {child} · {subject}' ],
                'dimension_1'            => [ 'type' => 'text', 'label' => 'Dimension 1', 'default' => 'Teaching' ],
                'dimension_2'            => [ 'type' => 'text', 'label' => 'Dimension 2', 'default' => 'Patience' ],
                'dimension_3'            => [ 'type' => 'text', 'label' => 'Dimension 3', 'default' => 'Punctuality' ],
                'dimension_4'            => [ 'type' => 'text', 'label' => 'Dimension 4', 'default' => 'Communication' ],
                'package_review_label'   => [ 'type' => 'text', 'label' => 'Package review label', 'default' => 'Your review' ],
                'package_review_optional'=> [ 'type' => 'text', 'label' => 'Package review optional text', 'default' => '(free text)' ],
                'package_comment_placeholder' => [ 'type' => 'textarea', 'label' => 'Package comment placeholder', 'default' => 'SHARE YOUR EXPERIENCE FOR OTHER PARENTS_' ],
                'photo_heading'          => [ 'type' => 'text', 'label' => 'Photo heading', 'default' => 'Add a photo' ],
                'photo_optional'         => [ 'type' => 'text', 'label' => 'Photo optional text', 'default' => '(optional)' ],
                'photo_label'            => [ 'type' => 'text', 'label' => 'Photo icon label', 'default' => '+' ],
                'photo_note'             => [ 'type' => 'text', 'label' => 'Photo note', 'default' => 'PARENT-PERMISSIONED · PDPA' ],
                'package_submit_label'   => [ 'type' => 'text', 'label' => 'Package submit label', 'default' => 'Submit full review' ],
                'hide_moderation'        => [ 'type' => 'switcher', 'label' => 'Hide moderation notice' ],
                'sec_moderation'         => [ 'type' => 'text', 'label' => 'Moderation section tag', 'default' => 'SEC 4 Moderation' ],
                'moderation_notice'      => [ 'type' => 'textarea', 'label' => 'Moderation notice', 'default' => '● REVIEWS ARE SCREENED FOR ABUSE/PII BEFORE PUBLISHING (FR-REVIEW-03). PUBLISHED AS "VERIFIED PARENT · {subject}". PDPA: CHILD\'S NAME NEVER SHOWN PUBLICLY.' ],
                'hide_submitted'         => [ 'type' => 'switcher', 'label' => 'Hide submitted state' ],
                'sec_submitted'          => [ 'type' => 'text', 'label' => 'Submitted section tag', 'default' => 'SEC 5 Submitted state' ],
                'submitted_title'        => [ 'type' => 'text', 'label' => 'Submitted title', 'default' => 'Review submitted' ],
                'submitted_body'         => [ 'type' => 'textarea', 'label' => 'Submitted body', 'default' => 'THANK YOU. YOUR REVIEW IS SENT FOR MODERATION BEFORE IT APPEARS ON THE TUTOR PROFILE.' ],
                'submitted_button'       => [ 'type' => 'text', 'label' => 'Submitted button', 'default' => 'Back to tutor profile' ],
                'hide_not_eligible'      => [ 'type' => 'switcher', 'label' => 'Hide not-eligible state' ],
                'sec_not_eligible'       => [ 'type' => 'text', 'label' => 'Not-eligible section tag', 'default' => 'SEC 6 Not-eligible state' ],
                'not_eligible_title'     => [ 'type' => 'text', 'label' => 'Not-eligible title', 'default' => 'Review unlocks after the first logged lesson' ],
                'not_eligible_body'      => [ 'type' => 'textarea', 'label' => 'Not-eligible body', 'default' => 'BR-20: PARENTS CAN ONLY REVIEW AFTER THE TUTOR LOGS AT LEAST 1 LESSON.' ],
                'not_eligible_button'    => [ 'type' => 'text', 'label' => 'Not-eligible button', 'default' => 'Back to dashboard' ],
                'dashboard_url'          => [ 'type' => 'text', 'label' => 'Dashboard URL', 'default' => '/parent/dashboard/' ],
                'bottom_note'            => [ 'type' => 'textarea', 'label' => 'Bottom note', 'default' => 'BR-20 ELIGIBILITY + FR-REVIEW-03 MODERATION PROTECT TRUST SIGNALS ON S03.' ],
            ],
            'style_targets' => [
                'shell'       => [ 'label' => 'Review Shell', 'selector' => '.mgk-parent-review__shell', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'sectags'     => mgk_style_text( 'Section Tags', '.mgk-parent-review-sec', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border', 'shadow' ] ),
                'prompt'      => [ 'label' => 'Prompt Box', 'selector' => '.mgk-parent-review-prompt', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'avatar'      => [ 'label' => 'Avatar Placeholder', 'selector' => '.mgk-parent-review-avatar', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'title'       => mgk_style_text( 'Prompt Title', '.mgk-parent-review-prompt h1', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'meta'        => mgk_style_text( 'Prompt Meta', '.mgk-parent-review-prompt p', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'form'        => [ 'label' => 'Form Box', 'selector' => '.mgk-parent-review-form', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'labels'      => mgk_style_text( 'Form Labels', '.mgk-parent-review-label', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'optional'    => mgk_style_text( 'Optional Label Text', '.mgk-parent-review-label span', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'stars'       => mgk_style_button( 'Rating Stars', '.mgk-parent-review-stars button' ),
                'hint'        => mgk_style_text( 'Rating Hint', '.mgk-parent-review-hint', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'textarea'    => mgk_style_text( 'Textarea / Comment Field', '.mgk-parent-review textarea', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border', 'shadow' ] ),
                'actions'     => [ 'label' => 'Action Row', 'selector' => '.mgk-parent-review-actions', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'submit'      => mgk_style_button( 'Submit Button', '.mgk-parent-review-submit' ),
                'skip'        => mgk_style_button( 'Skip / Secondary Button', '.mgk-parent-review-skip, .mgk-parent-review-photo' ),
                'pkghead'     => mgk_style_text( 'Post-package Heading', '.mgk-parent-review--post-package .mgk-parent-review-prompt h1', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'pkgsub'      => mgk_style_text( 'Post-package Meta', '.mgk-parent-review--post-package .mgk-parent-review-prompt p', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'dimensions'  => [ 'label' => 'Dimension Grid', 'selector' => '.mgk-parent-review-dimensions', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'dimension'   => [ 'label' => 'Dimension Card', 'selector' => '.mgk-parent-review-dimension', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'dimlabel'    => mgk_style_text( 'Dimension Labels', '.mgk-parent-review-dimension span', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'packagelabel'=> mgk_style_text( 'Package Review Label', '.mgk-parent-review-package-review-label', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'photoblock'  => [ 'label' => 'Photo Block', 'selector' => '.mgk-parent-review-photo-block', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'photolabel'  => mgk_style_text( 'Photo Label', '.mgk-parent-review-photo-label', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'photoicon'   => [ 'label' => 'Photo Preview Icon', 'selector' => '.mgk-parent-review-photo span', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'photonote'   => mgk_style_text( 'Photo Note', '.mgk-parent-review-photo em', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'moderation'  => [ 'label' => 'Moderation Box', 'selector' => '.mgk-parent-review-moderation', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'modtext'     => mgk_style_text( 'Moderation Text', '.mgk-parent-review-moderation p', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'statebox'    => [ 'label' => 'Submitted / Not Eligible Box', 'selector' => '.mgk-parent-review-state', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'statetitle'  => mgk_style_text( 'State Title', '.mgk-parent-review-state strong', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'statebody'   => mgk_style_text( 'State Body', '.mgk-parent-review-state p', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'statebtn'    => mgk_style_button( 'State Button', '.mgk-parent-review-state a' ),
                'bottomnote'  => mgk_style_text( 'Bottom Note', '.mgk-parent-review-bottom-note', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'section'     => [ 'label' => 'Section Box', 'selector' => '.mgk-parent-review', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
            ],
        ];
    }

    if ( shortcode_exists( 'mgk_parent_referral' ) ) {
        $sections[] = [
            'tag'      => 'mgk_parent_referral',
            'title'    => 'MGK Parent · Referral',
            'icon'     => 'eicon-share',
            'controls' => [
                'hidden'                 => [ 'type' => 'switcher', 'label' => 'Hide entire section' ],
                'preview_state'          => [ 'type' => 'select', 'label' => 'Preview state', 'default' => '', 'options' => [
                    ''                => 'Use URL state',
                    'default'         => 'Default dashboard',
                    'invitee-pending' => 'Invitee pending',
                    'reward-earned'   => 'Reward earned',
                    'empty'           => 'Empty no invites',
                ] ],
                'hide_hero'              => [ 'type' => 'switcher', 'label' => 'Hide hero' ],
                'sec_hero'               => [ 'type' => 'text', 'label' => 'Hero section tag', 'default' => 'SEC 1 Hero' ],
                'hero_title'             => [ 'type' => 'text', 'label' => 'Hero title', 'default' => 'Give a friend, get rewarded' ],
                'hero_body'              => [ 'type' => 'textarea', 'label' => 'Hero body', 'default' => 'WHEN A FRIEND JOINS AND COMPLETES THEIR FIRST PAID PACKAGE, YOU BOTH GET A REWARD (BR-24).' ],
                'reward_line'            => [ 'type' => 'textarea', 'label' => 'Reward explainer', 'default' => 'REWARD AMOUNT: $XX CREDIT · PER-TENANT CONFIG · PM TO CONFIRM' ],
                'hide_code_share'        => [ 'type' => 'switcher', 'label' => 'Hide code/share' ],
                'sec_code_share'         => [ 'type' => 'text', 'label' => 'Code/share section tag', 'default' => 'SEC 2 Code + Share' ],
                'code_label'             => [ 'type' => 'text', 'label' => 'Code label', 'default' => 'YOUR REFERRAL CODE' ],
                'share_heading'          => [ 'type' => 'text', 'label' => 'Share heading', 'default' => 'Share via' ],
                'whatsapp_label'         => [ 'type' => 'text', 'label' => 'WhatsApp label', 'default' => 'WhatsApp' ],
                'copy_label'             => [ 'type' => 'text', 'label' => 'Copy label', 'default' => 'Copy link' ],
                'email_label'            => [ 'type' => 'text', 'label' => 'Email label', 'default' => 'Email' ],
                'preview_label'          => [ 'type' => 'text', 'label' => 'Preview label', 'default' => 'PREVIEW:' ],
                'preview_text'           => [ 'type' => 'textarea', 'label' => 'Share preview text', 'default' => '"Emma loves her tutor on [agency]! Get $XX off your first package with my code {code}."' ],
                'hide_invitees'          => [ 'type' => 'switcher', 'label' => 'Hide invitee list' ],
                'sec_invitees'           => [ 'type' => 'text', 'label' => 'Invitee section tag', 'default' => 'SEC 3 Invitee List' ],
                'invitees_heading'       => [ 'type' => 'text', 'label' => 'Invitee heading', 'default' => 'Your invites ({count})' ],
                'invitees_note'          => [ 'type' => 'text', 'label' => 'Invitee note', 'default' => 'STATUS UPDATES AUTOMATICALLY' ],
                'hide_tracking'          => [ 'type' => 'switcher', 'label' => 'Hide reward tracking' ],
                'sec_tracking'           => [ 'type' => 'text', 'label' => 'Tracking section tag', 'default' => 'SEC 4 Reward Tracking' ],
                'tracking_note'          => [ 'type' => 'textarea', 'label' => 'Tracking note', 'default' => 'CREDIT AUTO-APPLIES TO NEXT PACKAGE · OR PER-TENANT PAYOUT RULE · PM TO CONFIRM' ],
                'hide_pending'           => [ 'type' => 'switcher', 'label' => 'Hide pending state' ],
                'pending_title'          => [ 'type' => 'text', 'label' => 'Pending title', 'default' => 'INVITEE PENDING' ],
                'pending_kicker'         => [ 'type' => 'text', 'label' => 'Pending kicker', 'default' => 'STATUS DETAIL' ],
                'pending_body'           => [ 'type' => 'textarea', 'label' => 'Pending body', 'default' => 'REWARD RELEASES TO BOTH PARTIES ONLY AFTER FIRST PAID PACKAGE (BR-24). NO REWARD ON TRIAL ALONE.' ],
                'pending_note'           => [ 'type' => 'textarea', 'label' => 'Pending note', 'default' => 'PER-INVITEE FUNNEL. PDPA: INVITEE IDENTITY SHOWN TO REFERRER ONLY AFTER THEY CONSENT / JOIN.' ],
                'hide_earned'            => [ 'type' => 'switcher', 'label' => 'Hide earned state' ],
                'earned_title'           => [ 'type' => 'text', 'label' => 'Earned title', 'default' => 'REWARD EARNED' ],
                'earned_kicker'          => [ 'type' => 'text', 'label' => 'Earned kicker', 'default' => 'SUCCESS' ],
                'earned_heading'         => [ 'type' => 'text', 'label' => 'Earned heading', 'default' => 'You earned a reward!' ],
                'earned_credit'          => [ 'type' => 'text', 'label' => 'Earned credit banner', 'default' => '+ $XX added to your credit balance' ],
                'earned_primary'         => [ 'type' => 'text', 'label' => 'Earned primary button', 'default' => 'Apply to next package →' ],
                'earned_secondary'       => [ 'type' => 'text', 'label' => 'Earned secondary button', 'default' => 'Invite more friends' ],
                'earned_note'            => [ 'type' => 'textarea', 'label' => 'Earned note', 'default' => 'REWARD AMOUNT + PAYOUT VS CREDIT PER-TENANT CONFIG · PM TO CONFIRM.' ],
                'hide_empty'             => [ 'type' => 'switcher', 'label' => 'Hide empty state' ],
                'empty_title'            => [ 'type' => 'text', 'label' => 'Empty title', 'default' => 'EMPTY · NO INVITES' ],
                'empty_kicker'           => [ 'type' => 'text', 'label' => 'Empty kicker', 'default' => 'FIRST-USE' ],
                'empty_heading'          => [ 'type' => 'text', 'label' => 'Empty heading', 'default' => 'No invites yet' ],
                'empty_body'             => [ 'type' => 'textarea', 'label' => 'Empty body', 'default' => 'SHARE YOUR CODE TO START EARNING. YOUR INVITES & REWARDS WILL APPEAR HERE.' ],
                'empty_note'             => [ 'type' => 'textarea', 'label' => 'Empty note', 'default' => 'EMPTY HIDES LIST + TRACKING, KEEPS CODE + SHARE FRONT-AND-CENTRE.' ],
            ],
            'style_targets' => [
                'section'       => [ 'label' => 'Section Box', 'selector' => '.mgk-parent-referral', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'shell'         => [ 'label' => 'Main Shell', 'selector' => '.mgk-parent-referral__shell', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'sectag'        => mgk_style_text( 'Section Tags', '.mgk-parent-referral-sec', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border', 'shadow' ] ),
                'hero'          => [ 'label' => 'Hero Box', 'selector' => '.mgk-parent-referral-hero', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'herotitle'     => mgk_style_text( 'Hero Title', '.mgk-parent-referral-hero h1', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'herobody'      => mgk_style_text( 'Hero Body', '.mgk-parent-referral-hero p', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'rewardline'    => mgk_style_text( 'Reward Explainer', '.mgk-parent-referral-hero div', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'sharegrid'     => [ 'label' => 'Code + Share Grid', 'selector' => '.mgk-parent-referral-share', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'codebox'       => [ 'label' => 'Referral Code Box', 'selector' => '.mgk-parent-referral-code', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'codelabel'     => mgk_style_text( 'Code Label', '.mgk-parent-referral-code span', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'codevalue'     => mgk_style_text( 'Code Value', '.mgk-parent-referral-code strong', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'codeurl'       => mgk_style_text( 'Referral URL', '.mgk-parent-referral-code em', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'shareheading'  => mgk_style_text( 'Share Heading', '.mgk-parent-referral-actions h2', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'sharebuttons'  => mgk_style_button( 'Share Buttons', '.mgk-parent-referral-actions a, .mgk-parent-referral-actions button' ),
                'sharepreview'  => mgk_style_text( 'Share Preview', '.mgk-parent-referral-actions p', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'inviteebox'    => [ 'label' => 'Invitee Section', 'selector' => '.mgk-parent-referral-invitees', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'inviteetitle'  => mgk_style_text( 'Invitee Heading', '.mgk-parent-referral-invitees h2', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'inviteenote'   => mgk_style_text( 'Invitee Note', '.mgk-parent-referral-invitees header span', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'inviteecard'   => [ 'label' => 'Invitee Row', 'selector' => '.mgk-parent-referral-invitee', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'inviteename'   => mgk_style_text( 'Invitee Name', '.mgk-parent-referral-invitee strong', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'inviteestatus' => mgk_style_text( 'Invitee Status', '.mgk-parent-referral-invitee span', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'avatar'        => [ 'label' => 'Invitee Avatar', 'selector' => '.mgk-parent-referral-avatar', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'tracking'      => [ 'label' => 'Tracking Section', 'selector' => '.mgk-parent-referral-tracking', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'trackcard'     => [ 'label' => 'Tracking Card', 'selector' => '.mgk-parent-referral-track-grid article', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'trackvalue'    => mgk_style_text( 'Tracking Value', '.mgk-parent-referral-track-grid strong', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'tracklabel'    => mgk_style_text( 'Tracking Label', '.mgk-parent-referral-track-grid span', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'statehead'     => [ 'label' => 'State Header', 'selector' => '.mgk-parent-referral-state-head, .mgk-parent-referral-empty-head', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'stateshell'    => [ 'label' => 'State Shell', 'selector' => '.mgk-parent-referral-state-shell, .mgk-parent-referral-empty', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'statetitle'    => mgk_style_text( 'State Title', '.mgk-parent-referral-state-head strong, .mgk-parent-referral-empty-head strong, .mgk-parent-referral-state-shell h2, .mgk-parent-referral-empty h2', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'statebody'     => mgk_style_text( 'State Body', '.mgk-parent-referral-state-shell p, .mgk-parent-referral-empty p, .mgk-parent-referral-callout', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'statebuttons'  => mgk_style_button( 'State Buttons', '.mgk-parent-referral-primary, .mgk-parent-referral-secondary' ),
                'emptyart'      => [ 'label' => 'Empty Illustration', 'selector' => '.mgk-parent-referral-empty-art, .mgk-parent-referral-earned-icon', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'footnote'      => mgk_style_text( 'Footnote', '.mgk-parent-referral-state-shell footer, .mgk-parent-referral-empty-note, .mgk-parent-referral-tracking p', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
            ],
        ];
    }

    if ( shortcode_exists( 'mgk_notification_center' ) ) {
        $sections[] = [
            'tag'      => 'mgk_notification_center',
            'title'    => 'MGK · Notification Center',
            'icon'     => 'eicon-bell',
            'controls' => [
                'hidden'          => [ 'type' => 'switcher', 'label' => 'Hide entire notification center' ],
                'hide_header'     => [ 'type' => 'switcher', 'label' => 'Hide header/master toggles' ],
                'sec_header'      => [ 'type' => 'text', 'label' => 'Header tag', 'default' => 'SEC 1 Header' ],
                'title'           => [ 'type' => 'text', 'label' => 'Title', 'default' => 'Notification preferences' ],
                'subtitle'        => [ 'type' => 'textarea', 'label' => 'Subtitle', 'default' => 'CHOOSE HOW WE REACH YOU PER EVENT. DEFAULTS ARE PDPA-SAFE.' ],
                'profile_label'   => [ 'type' => 'text', 'label' => 'Profile selector', 'default' => 'PROFILE: PARENT ▾' ],
                'master_label'    => [ 'type' => 'text', 'label' => 'Master label', 'default' => 'MASTER:' ],
                'all_on_label'    => [ 'type' => 'text', 'label' => 'All on button', 'default' => 'ALL ON ●' ],
                'essential_label' => [ 'type' => 'text', 'label' => 'Essential button', 'default' => 'ESSENTIAL ONLY ○' ],
                'hide_matrix'     => [ 'type' => 'switcher', 'label' => 'Hide channel matrix' ],
                'sec_matrix'      => [ 'type' => 'text', 'label' => 'Matrix tag', 'default' => 'SEC 2 Matrix' ],
                'event_col'       => [ 'type' => 'text', 'label' => 'Event column', 'default' => 'EVENT TYPE' ],
                'push_col'        => [ 'type' => 'text', 'label' => 'Push column', 'default' => 'PUSH' ],
                'email_col'       => [ 'type' => 'text', 'label' => 'Email column', 'default' => 'EMAIL' ],
                'sms_col'         => [ 'type' => 'text', 'label' => 'SMS column', 'default' => 'SMS' ],
                'whatsapp_col'    => [ 'type' => 'text', 'label' => 'WhatsApp column', 'default' => 'WHATSAPP' ],
                'matrix_note'     => [ 'type' => 'textarea', 'label' => 'Matrix note', 'default' => '• on    ○ off    ◐ on, batched (quiet hours)    Promotions opt-in only (PDPA)' ],
                'hide_quiet'      => [ 'type' => 'switcher', 'label' => 'Hide quiet hours' ],
                'sec_quiet'       => [ 'type' => 'text', 'label' => 'Quiet hours tag', 'default' => 'SEC 3 Quiet Hours' ],
                'quiet_title'     => [ 'type' => 'text', 'label' => 'Quiet title', 'default' => 'Quiet hours' ],
                'quiet_body'      => [ 'type' => 'textarea', 'label' => 'Quiet body', 'default' => 'NO PUSH/SMS/WHATSAPP IN THIS WINDOW (EMAIL STILL QUEUED). BR-14 / FR-SYS-07.' ],
                'quiet_toggle'    => [ 'type' => 'text', 'label' => 'Quiet toggle', 'default' => 'ON ●' ],
                'quiet_from'      => [ 'type' => 'text', 'label' => 'Quiet from', 'default' => '10:00 PM ▾' ],
                'quiet_to'        => [ 'type' => 'text', 'label' => 'Quiet to', 'default' => '7:00 AM ▾' ],
                'quiet_tz'        => [ 'type' => 'text', 'label' => 'Quiet timezone', 'default' => 'SGT' ],
                'quiet_alert'     => [ 'type' => 'textarea', 'label' => 'Quiet alert', 'default' => '△ CRITICAL ALERTS (NO-SHOW, URGENT DISPUTE) OVERRIDE QUIET HOURS — PM TO CONFIRM SCOPE.' ],
                'hide_preview'    => [ 'type' => 'switcher', 'label' => 'Hide rich push preview' ],
                'sec_preview'     => [ 'type' => 'text', 'label' => 'Rich push tag', 'default' => 'SEC 4 Rich Push' ],
                'preview_title'   => [ 'type' => 'text', 'label' => 'Rich push title', 'default' => 'Rich push with inline actions (FR-SYS-02)' ],
                'preview_note'    => [ 'type' => 'textarea', 'label' => 'Rich push note', 'default' => 'TUTOR ACCEPT/DECLINE + PARENT VIEW PROPOSAL FIRE FROM THE NOTIFICATION ITSELF.' ],
                'hide_pdpa'       => [ 'type' => 'switcher', 'label' => 'Hide PDPA footer' ],
                'sec_pdpa'        => [ 'type' => 'text', 'label' => 'PDPA tag', 'default' => 'SEC 5 PDPA' ],
                'pdpa_body'       => [ 'type' => 'textarea', 'label' => 'PDPA body', 'default' => '▣ PDPA: MARKETING OFF BY DEFAULT; TRANSACTIONAL ALWAYS ON. CONSENT + WHATSAPP OPT-IN LOGGED.' ],
                'pdpa_link'       => [ 'type' => 'text', 'label' => 'PDPA link', 'default' => 'MANAGE CONSENT / DATA →' ],
            ],
            'style_targets' => [
                'section'       => [ 'label' => 'Section Box', 'selector' => '.mgk-notif', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'shell'         => [ 'label' => 'Notification Shell', 'selector' => '.mgk-notif__shell', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'sectags'       => mgk_style_text( 'Section Tags', '.mgk-notif-sec', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border', 'shadow' ] ),
                'headerbox'     => [ 'label' => 'Header Box', 'selector' => '.mgk-notif-head', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'headtitle'     => mgk_style_text( 'Header Title', '.mgk-notif-head h1', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'headcopy'      => mgk_style_text( 'Header Copy', '.mgk-notif-head p', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'masterrow'     => [ 'label' => 'Master Toggle Row', 'selector' => '.mgk-notif-head nav', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'buttons'       => mgk_style_button( 'All Buttons', '.mgk-notif button' ),
                'matrixbox'     => [ 'label' => 'Matrix Section', 'selector' => '.mgk-notif-matrix', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'matrixgrid'    => [ 'label' => 'Matrix Grid', 'selector' => '.mgk-notif-table', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'matrixhead'    => [ 'label' => 'Matrix Header Cells', 'selector' => '.mgk-notif-table b', 'features' => [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ],
                'matrixevents'  => [ 'label' => 'Matrix Event Cells', 'selector' => '.mgk-notif-table span', 'features' => [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ],
                'matrixmarks'   => [ 'label' => 'Matrix Channel Marks', 'selector' => '.mgk-notif-table i', 'features' => [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ],
                'matrixnote'    => mgk_style_text( 'Matrix Note', '.mgk-notif-matrix p', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'quietbox'      => [ 'label' => 'Quiet Hours Section', 'selector' => '.mgk-notif-quiet', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'quiettext'     => mgk_style_text( 'Quiet Hours Text', '.mgk-notif-quiet h2, .mgk-notif-quiet p, .mgk-notif-quiet nav span', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'quietcontrols' => [ 'label' => 'Quiet Hour Controls', 'selector' => '.mgk-notif-quiet nav, .mgk-notif-quiet button', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'quietalert'    => [ 'label' => 'Quiet Critical Alert', 'selector' => '.mgk-notif-quiet strong', 'features' => [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'previewbox'    => [ 'label' => 'Rich Push Section', 'selector' => '.mgk-notif-preview', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'previewtitle'  => mgk_style_text( 'Rich Push Title', '.mgk-notif-preview h2', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'previewgrid'   => [ 'label' => 'Rich Push Grid', 'selector' => '.mgk-notif-preview > div', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'previewcards'  => [ 'label' => 'Rich Push Cards', 'selector' => '.mgk-notif-preview article', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow', 'hover' ] ],
                'previewtext'   => mgk_style_text( 'Rich Push Card Text', '.mgk-notif-preview article span, .mgk-notif-preview article h3, .mgk-notif-preview p', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'previewbars'   => [ 'label' => 'Rich Push Skeleton Bars', 'selector' => '.mgk-notif-preview article i', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'previewactions'=> [ 'label' => 'Rich Push Action Row', 'selector' => '.mgk-notif-preview article nav', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'pdpabox'       => [ 'label' => 'PDPA Footer Box', 'selector' => '.mgk-notif-pdpa', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'pdpatext'      => mgk_style_text( 'PDPA Footer Text', '.mgk-notif-pdpa p, .mgk-notif-pdpa a', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
            ],
        ];
    }

    if ( shortcode_exists( 'mgk_parent_account' ) ) {
        $sections[] = [
            'tag'      => 'mgk_parent_account',
            'title'    => 'MGK Parent · Account Settings',
            'icon'     => 'eicon-user-circle-o',
            'controls' => [
                'hidden'                 => [ 'type' => 'switcher', 'label' => 'Hide entire section' ],
                'preview_state'          => [ 'type' => 'select', 'label' => 'Preview state', 'default' => '', 'options' => [
                    ''               => 'Use URL state',
                    'default'        => 'Settings dashboard',
                    'otp'            => 'OTP re-verify',
                    'dsar-export'    => 'DSAR export',
                    'delete-account' => 'Delete account',
                ] ],
                'hide_nav'               => [ 'type' => 'switcher', 'label' => 'Hide nav' ],
                'sec_nav'                => [ 'type' => 'text', 'label' => 'Nav section tag', 'default' => 'SEC 1 Nav' ],
                'nav_profile'            => [ 'type' => 'text', 'label' => 'Nav profile', 'default' => 'Profile & contact' ],
                'nav_payment'            => [ 'type' => 'text', 'label' => 'Nav payment', 'default' => 'Payment methods' ],
                'nav_children'           => [ 'type' => 'text', 'label' => 'Nav children', 'default' => 'Children' ],
                'nav_notifications'      => [ 'type' => 'text', 'label' => 'Nav notifications', 'default' => 'Notifications -> S27' ],
                'nav_language'           => [ 'type' => 'text', 'label' => 'Nav language', 'default' => 'Language' ],
                'nav_dsar'               => [ 'type' => 'text', 'label' => 'Nav DSAR', 'default' => 'Privacy & data (DSAR)' ],
                'hide_profile'           => [ 'type' => 'switcher', 'label' => 'Hide profile/contact' ],
                'sec_profile'            => [ 'type' => 'text', 'label' => 'Profile section tag', 'default' => 'SEC 2 Profile + Contact' ],
                'profile_title'          => [ 'type' => 'text', 'label' => 'Profile title', 'default' => 'Profile & contact' ],
                'full_name_label'        => [ 'type' => 'text', 'label' => 'Full name label', 'default' => 'FULL NAME' ],
                'email_label'            => [ 'type' => 'text', 'label' => 'Email label', 'default' => 'EMAIL' ],
                'phone_label'            => [ 'type' => 'text', 'label' => 'Phone label', 'default' => 'PHONE' ],
                'password_label'         => [ 'type' => 'text', 'label' => 'Password label', 'default' => 'PASSWORD' ],
                'change_otp_label'       => [ 'type' => 'text', 'label' => 'Change OTP label', 'default' => 'change (OTP)' ],
                'password_update_label'  => [ 'type' => 'text', 'label' => 'Password update label', 'default' => 'update' ],
                'edit_profile_label'     => [ 'type' => 'text', 'label' => 'Edit profile button', 'default' => 'Edit profile' ],
                'hide_payment'           => [ 'type' => 'switcher', 'label' => 'Hide payment methods' ],
                'sec_payment'            => [ 'type' => 'text', 'label' => 'Payment section tag', 'default' => 'SEC 3 Payment' ],
                'payment_title'          => [ 'type' => 'text', 'label' => 'Payment title', 'default' => 'Payment methods' ],
                'add_payment_label'      => [ 'type' => 'text', 'label' => 'Add payment label', 'default' => '+ Add payment method' ],
                'hide_children'          => [ 'type' => 'switcher', 'label' => 'Hide children' ],
                'sec_children'           => [ 'type' => 'text', 'label' => 'Children section tag', 'default' => 'SEC 4 Children' ],
                'children_title'         => [ 'type' => 'text', 'label' => 'Children title', 'default' => 'Children' ],
                'child_edit_label'       => [ 'type' => 'textarea', 'label' => 'Child edit text', 'default' => 'EDIT · SCHOOL, LEVEL, SUBJECTS' ],
                'add_child_label'        => [ 'type' => 'text', 'label' => 'Add child label', 'default' => '+ ADD A CHILD' ],
                'children_note'          => [ 'type' => 'textarea', 'label' => 'Children note', 'default' => 'CHILDREN FEED THE DASHBOARD SWITCHER (FR-SYS-10). PDPA: MINOR DATA MINIMISED.' ],
                'hide_pref_lang'         => [ 'type' => 'switcher', 'label' => 'Hide notification/language' ],
                'sec_pref_lang'          => [ 'type' => 'text', 'label' => 'Preference section tag', 'default' => 'SEC 5 Notif + SEC 7 Lang' ],
                'notifications_title'    => [ 'type' => 'text', 'label' => 'Notifications title', 'default' => 'Notification preferences' ],
                'notifications_body'     => [ 'type' => 'textarea', 'label' => 'Notifications body', 'default' => 'EMAIL / PUSH / SMS · DIGESTS' ],
                'notifications_button'   => [ 'type' => 'text', 'label' => 'Notifications button', 'default' => 'Manage -> S27' ],
                'language_title'         => [ 'type' => 'text', 'label' => 'Language title', 'default' => 'Language' ],
                'hide_dsar'              => [ 'type' => 'switcher', 'label' => 'Hide DSAR section' ],
                'sec_dsar'               => [ 'type' => 'text', 'label' => 'DSAR section tag', 'default' => 'SEC 6 DSAR (NFR 10.3)' ],
                'dsar_title'             => [ 'type' => 'text', 'label' => 'DSAR title', 'default' => 'Privacy & your data' ],
                'export_title'           => [ 'type' => 'text', 'label' => 'Export card title', 'default' => 'Export my data' ],
                'export_body'            => [ 'type' => 'textarea', 'label' => 'Export card body', 'default' => 'DOWNLOAD ALL YOUR DATA (PDPA ACCESS REQUEST · NFR 10.3)' ],
                'export_button'          => [ 'type' => 'text', 'label' => 'Export button', 'default' => 'Request export' ],
                'delete_title'           => [ 'type' => 'text', 'label' => 'Delete card title', 'default' => 'Delete account' ],
                'delete_body'            => [ 'type' => 'textarea', 'label' => 'Delete card body', 'default' => 'ERASE YOUR ACCOUNT & PERSONAL DATA (NFR 10.3)' ],
                'delete_button'          => [ 'type' => 'text', 'label' => 'Delete button', 'default' => 'Delete account...' ],
                'hide_otp'               => [ 'type' => 'switcher', 'label' => 'Hide OTP state' ],
                'otp_title'              => [ 'type' => 'text', 'label' => 'OTP state title', 'default' => 'EDIT PROFILE · OTP RE-VERIFY' ],
                'otp_kicker'             => [ 'type' => 'text', 'label' => 'OTP kicker', 'default' => 'SECURITY' ],
                'otp_heading'            => [ 'type' => 'text', 'label' => 'OTP heading', 'default' => 'Change phone number' ],
                'otp_new_label'          => [ 'type' => 'text', 'label' => 'OTP new contact label', 'default' => 'NEW NUMBER: +65 8•• ••••' ],
                'otp_verify_title'       => [ 'type' => 'text', 'label' => 'OTP verify title', 'default' => 'Verify it is you' ],
                'otp_verify_body'        => [ 'type' => 'textarea', 'label' => 'OTP verify body', 'default' => 'WE SENT A 6-DIGIT OTP TO THE NEW NUMBER.' ],
                'otp_button'             => [ 'type' => 'text', 'label' => 'OTP button', 'default' => 'Verify & save' ],
                'otp_resend'             => [ 'type' => 'text', 'label' => 'OTP resend text', 'default' => 'RESEND IN 0:38' ],
                'otp_note'               => [ 'type' => 'textarea', 'label' => 'OTP note', 'default' => 'EMAIL + PHONE CHANGES BOTH REQUIRE OTP RE-VERIFY BEFORE COMMIT. OLD CONTACT NOTIFIED OF CHANGE.' ],
                'hide_export_state'      => [ 'type' => 'switcher', 'label' => 'Hide export state' ],
                'export_state_title'     => [ 'type' => 'text', 'label' => 'Export state title', 'default' => 'DSAR EXPORT' ],
                'export_state_kicker'    => [ 'type' => 'text', 'label' => 'Export state kicker', 'default' => 'NFR 10.3' ],
                'export_state_heading'   => [ 'type' => 'text', 'label' => 'Export heading', 'default' => 'Export my data' ],
                'export_state_body'      => [ 'type' => 'textarea', 'label' => 'Export body', 'default' => "WE'LL COMPILE YOUR PROFILE, CHILDREN, BOOKINGS, LESSON LOGS, MESSAGES & PAYMENTS." ],
                'export_format'          => [ 'type' => 'text', 'label' => 'Export format', 'default' => 'FORMAT: JSON + PDF' ],
                'export_delivery'        => [ 'type' => 'textarea', 'label' => 'Export delivery', 'default' => 'DELIVERED TO YOUR VERIFIED EMAIL WITHIN 30 DAYS (PDPA / NFR 10.3)' ],
                'export_status'          => [ 'type' => 'textarea', 'label' => 'Export status', 'default' => 'STATUS: EXPORT REQUESTED 3 JUN · READY BY 8 JUN · YOU WILL BE EMAILED A SECURE LINK' ],
                'export_state_button'    => [ 'type' => 'text', 'label' => 'Export state button', 'default' => 'Request export' ],
                'export_state_note'      => [ 'type' => 'textarea', 'label' => 'Export state note', 'default' => 'PDPA ACCESS REQUEST. RATE-LIMITED. LINK EXPIRES; RE-AUTH TO DOWNLOAD.' ],
                'hide_delete_state'      => [ 'type' => 'switcher', 'label' => 'Hide delete state' ],
                'delete_state_title'     => [ 'type' => 'text', 'label' => 'Delete state title', 'default' => 'DELETE-ACCOUNT CONFIRM' ],
                'delete_state_kicker'    => [ 'type' => 'text', 'label' => 'Delete state kicker', 'default' => 'EDGE · NFR 10.3' ],
                'delete_state_heading'   => [ 'type' => 'text', 'label' => 'Delete heading', 'default' => 'Delete your account?' ],
                'delete_warning'         => [ 'type' => 'textarea', 'label' => 'Delete warning', 'default' => 'THIS PERMANENTLY ERASES YOUR PROFILE, CHILDREN & PERSONAL DATA (NFR 10.3). ACTIVE PACKAGE? YOU WILL SEE REFUND PREVIEW (BR-07) FIRST. SOME RECORDS KEPT FOR LEGAL/FINANCE RETENTION (ANONYMISED).' ],
                'delete_confirm_label'   => [ 'type' => 'text', 'label' => 'Delete confirm label', 'default' => 'TYPE DELETE TO CONFIRM:' ],
                'delete_cancel'          => [ 'type' => 'text', 'label' => 'Delete cancel button', 'default' => 'Cancel, keep account' ],
                'delete_permanent'       => [ 'type' => 'text', 'label' => 'Delete permanent button', 'default' => 'Delete permanently' ],
                'delete_state_note'      => [ 'type' => 'textarea', 'label' => 'Delete state note', 'default' => 'TYPE-TO-CONFIRM + OTP RE-VERIFY GATE. GRACE PERIOD (E.G. 14D) BEFORE HARD-DELETE, PER-TENANT CONFIG · PM TO CONFIRM.' ],
            ],
            'style_targets' => [
                'section'       => [ 'label' => 'Section Box', 'selector' => '.mgk-parent-account', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'shell'         => [ 'label' => 'Settings Shell', 'selector' => '.mgk-parent-account__shell', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'sectag'        => mgk_style_text( 'Section Tags', '.mgk-parent-account-sec', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border', 'shadow' ] ),
                'nav'           => [ 'label' => 'Settings Nav', 'selector' => '.mgk-parent-account-nav', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'navitem'       => mgk_style_text( 'Nav Items', '.mgk-parent-account-nav a', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'main'          => [ 'label' => 'Main Content Area', 'selector' => '.mgk-parent-account-main', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'profile'       => [ 'label' => 'Profile Section', 'selector' => '.mgk-parent-account-profile', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'heading'       => mgk_style_text( 'Section Headings', '.mgk-parent-account h1, .mgk-parent-account h2', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'fieldcard'     => [ 'label' => 'Profile Field Cards', 'selector' => '.mgk-parent-account-field-grid article', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'fieldlabel'    => mgk_style_text( 'Field Labels', '.mgk-parent-account-field-grid span', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'fieldvalue'    => mgk_style_text( 'Field Values', '.mgk-parent-account-field-grid strong', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'fieldlink'     => mgk_style_text( 'Field Action Links', '.mgk-parent-account-field-grid a', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'button'        => mgk_style_button( 'Buttons', '.mgk-parent-account button, .mgk-parent-account a.mgk-parent-account-secondary, .mgk-parent-account-dsar-grid a, .mgk-parent-account-pref-lang a, .mgk-parent-account-state-shell a' ),
                'payment'       => [ 'label' => 'Payment Section', 'selector' => '.mgk-parent-account-payment', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'paymentcard'   => [ 'label' => 'Payment Cards', 'selector' => '.mgk-parent-account-payment-grid article, .mgk-parent-account-payment-grid button', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'children'      => [ 'label' => 'Children Section', 'selector' => '.mgk-parent-account-children', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'childcard'     => [ 'label' => 'Child Cards', 'selector' => '.mgk-parent-account-child-grid article, .mgk-parent-account-child-grid button', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'avatar'        => [ 'label' => 'Child Avatar', 'selector' => '.mgk-parent-account-child-grid article div', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'note'          => mgk_style_text( 'Notes / Helper Text', '.mgk-parent-account-children > p, .mgk-parent-account-state-shell footer', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'pref'          => [ 'label' => 'Notification + Language Cards', 'selector' => '.mgk-parent-account-pref-lang article', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'langbuttons'   => mgk_style_button( 'Language Buttons', '.mgk-parent-account-lang-buttons button' ),
                'dsar'          => [ 'label' => 'DSAR Section', 'selector' => '.mgk-parent-account-dsar', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'dsarcard'      => [ 'label' => 'DSAR Cards', 'selector' => '.mgk-parent-account-dsar-grid article', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'statehead'     => [ 'label' => 'State Header', 'selector' => '.mgk-parent-account-state-head', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'stateshell'    => [ 'label' => 'State Shell', 'selector' => '.mgk-parent-account-state-shell', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'statetitle'    => mgk_style_text( 'State Title', '.mgk-parent-account-state-shell h1, .mgk-parent-account-state-shell h2', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'statebody'     => mgk_style_text( 'State Body', '.mgk-parent-account-state-shell p, .mgk-parent-account-export-format, .mgk-parent-account-export-status, .mgk-parent-account-delete-warning, .mgk-parent-account-otp-new', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'otpbox'        => [ 'label' => 'OTP Verification Box', 'selector' => '.mgk-parent-account-otp-box', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'otpinput'      => [ 'label' => 'OTP Digit Inputs', 'selector' => '.mgk-parent-account-otp-digits input', 'features' => [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'danger'        => [ 'label' => 'Danger Elements', 'selector' => '.mgk-parent-account-dsar-grid article.is-danger, .mgk-parent-account-delete-warning, .mgk-parent-account-destructive', 'features' => [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border', 'shadow' ] ],
            ],
        ];
    }

    if ( shortcode_exists( 'mgk_parent_messages_page' ) ) {
        $sections[] = [
            'tag'      => 'mgk_parent_messages_page',
            'title'    => 'MGK Parent · Message Thread',
            'icon'     => 'eicon-comments',
            'controls' => [
                'hidden'             => [ 'type' => 'switcher', 'label' => 'Hide entire section' ],
                'hide_utility'       => [ 'type' => 'switcher', 'label' => 'Hide utility bar' ],
                'utility'            => [ 'type' => 'text', 'label' => 'Utility text', 'default' => '[AGENCY LOGO] · Dashboard · Messages · SG/EN' ],
                'dashboard_url'      => [ 'type' => 'text', 'label' => 'Dashboard URL', 'default' => '/parent/dashboard/' ],
                'messages_url'       => [ 'type' => 'text', 'label' => 'Messages URL', 'default' => '/parent/messages/' ],
                'hide_search'        => [ 'type' => 'switcher', 'label' => 'Hide search box' ],
                'search_placeholder' => [ 'type' => 'text', 'label' => 'Search placeholder', 'default' => '⌕ SEARCH MESSAGES' ],
                'hide_monitor'       => [ 'type' => 'switcher', 'label' => 'Hide agency monitor box' ],
                'monitor_label'      => [ 'type' => 'text', 'label' => 'Monitor label', 'default' => 'Agency-monitored' ],
                'report_label'       => [ 'type' => 'text', 'label' => 'Report label', 'default' => '⚠ Report' ],
                'date_label'         => [ 'type' => 'text', 'label' => 'Date separator', 'default' => '— TODAY —' ],
                'hide_privacy'       => [ 'type' => 'switcher', 'label' => 'Hide privacy notice' ],
                'privacy_notice'     => [ 'type' => 'textarea', 'label' => 'Privacy notice', 'default' => '🔒 FOR YOUR SAFETY, PHONE NUMBERS & EMAILS ARE HIDDEN. "CALL ME AT xxxx" → (FR-SYS-03)' ],
                'lesson_chip'        => [ 'type' => 'text', 'label' => 'Lesson chip label', 'default' => 'Lesson' ],
                'input_placeholder'  => [ 'type' => 'text', 'label' => 'Input placeholder', 'default' => 'TYPE A MESSAGE...' ],
                'send_label'         => [ 'type' => 'text', 'label' => 'Send button label', 'default' => 'Send' ],
                'hide_compose_modal' => [ 'type' => 'switcher', 'label' => 'Hide attach modal' ],
                'compose_heading'    => [ 'type' => 'text', 'label' => 'Attach modal heading', 'default' => 'PHOTO + LESSON-REF COMPOSE' ],
                'compose_kicker'     => [ 'type' => 'text', 'label' => 'Attach modal kicker', 'default' => 'MESSAGE TYPES' ],
                'photo_heading'      => [ 'type' => 'text', 'label' => 'Photo section heading', 'default' => 'Attaching a photo' ],
                'preview_label'      => [ 'type' => 'text', 'label' => 'Preview label', 'default' => 'preview ·' ],
                'remove_label'       => [ 'type' => 'text', 'label' => 'Remove button label', 'default' => '× Remove' ],
                'lesson_heading'     => [ 'type' => 'text', 'label' => 'Lesson section heading', 'default' => 'Sharing a lesson reference' ],
                'pick_label'         => [ 'type' => 'text', 'label' => 'Pick lesson label', 'default' => '📎 PICK A LESSON TO LINK' ],
                'compose_note'       => [ 'type' => 'textarea', 'label' => 'Attach modal note', 'default' => 'THREE MESSAGE TYPES: TEXT, PHOTO, LESSON-REFERENCE. READ RECEIPTS (✓ SENT / ✓✓ READ) ON EVERY MESSAGE.' ],
            ],
            'style_targets' => [
                'shell'      => [ 'label' => 'Outer Shell', 'selector' => '.mgk-parent-messages__shell', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'utility'    => mgk_style_text( 'Utility Bar', '.mgk-parent-messages-utility', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'app'        => [ 'label' => 'Two Column App Box', 'selector' => '.mgk-parent-messages-app', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'sidebar'    => [ 'label' => 'Thread Sidebar', 'selector' => '.mgk-parent-messages-sidebar', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'search'     => [ 'label' => 'Search Input', 'selector' => '.mgk-parent-messages-search input', 'features' => [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ],
                'thread'     => [ 'label' => 'Thread Rows', 'selector' => '.mgk-parent-messages-thread', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow', 'hover' ] ],
                'active'     => [ 'label' => 'Active Thread Row', 'selector' => '.mgk-parent-messages-thread.is-active', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'threadtext' => mgk_style_text( 'Thread Text', '.mgk-parent-messages-thread strong, .mgk-parent-messages-thread em', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'unread'     => mgk_style_text( 'Unread Badge', '.mgk-parent-messages-thread b', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'main'       => [ 'label' => 'Conversation Main', 'selector' => '.mgk-parent-messages-main', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'head'       => [ 'label' => 'Conversation Header', 'selector' => '.mgk-parent-messages-head', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'avatar'     => [ 'label' => 'Avatars', 'selector' => '.mgk-parent-messages-thread i, .mgk-parent-messages-head > i, .mgk-parent-message > i', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'title'      => mgk_style_text( 'Conversation Title', '.mgk-parent-messages-head h1', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'status'     => mgk_style_text( 'Online Status', '.mgk-parent-messages-head p', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'monitor'    => [ 'label' => 'Agency Monitor Box', 'selector' => '.mgk-parent-messages-monitor span, .mgk-parent-messages-monitor a', 'features' => [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'stream'     => [ 'label' => 'Message Stream', 'selector' => '.mgk-parent-messages-stream', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'date'       => mgk_style_text( 'Date Separator', '.mgk-parent-messages-date', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'inbubble'   => [ 'label' => 'Incoming Text Bubble', 'selector' => '.mgk-parent-message-bubble--text', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'photo'      => [ 'label' => 'Photo Attachment Box', 'selector' => '.mgk-parent-message-photo', 'features' => [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'lesson'     => [ 'label' => 'Lesson Reference Card', 'selector' => '.mgk-parent-message-lesson', 'features' => [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'outbubble'  => [ 'label' => 'Outgoing Bubble', 'selector' => '.mgk-parent-message-bubble--out, .mgk-parent-message--out time', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'time'       => mgk_style_text( 'Message Time Text', '.mgk-parent-message time', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'privacy'    => mgk_style_text( 'Privacy Notice', '.mgk-parent-messages-privacy', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border', 'shadow' ] ),
                'composer'   => [ 'label' => 'Composer Box', 'selector' => '.mgk-parent-messages-composer', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'attach'     => mgk_style_button( 'Attachment Buttons', '.mgk-parent-messages-attach, .mgk-parent-messages-lesson-chip' ),
                'input'      => [ 'label' => 'Message Input', 'selector' => '.mgk-parent-messages-composer textarea', 'features' => [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ],
                'send'       => mgk_style_button( 'Send Button', '.mgk-parent-messages-send' ),
                'modal'      => [ 'label' => 'Attach Modal Box', 'selector' => '.mgk-parent-message-compose', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'modaltop'   => [ 'label' => 'Attach Modal Top Bar', 'selector' => '.mgk-parent-message-compose__top', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'modalhead'  => mgk_style_text( 'Attach Modal Heading', '.mgk-parent-message-compose__top h2', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'modalkicker'=> mgk_style_text( 'Attach Modal Kicker', '.mgk-parent-message-compose__top span', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'modalbody'  => [ 'label' => 'Attach Modal Body', 'selector' => '.mgk-parent-message-compose__shell', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'photoarea'  => [ 'label' => 'Photo Attach Box', 'selector' => '.mgk-parent-message-compose__photo-box', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'preview'    => [ 'label' => 'Photo Preview', 'selector' => '.mgk-parent-message-compose__preview', 'features' => [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'lessonbox'  => [ 'label' => 'Lesson Reference Picker', 'selector' => '.mgk-parent-message-compose__lesson-box', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'lessonpick' => mgk_style_text( 'Pick Lesson Label', '.mgk-parent-message-compose__lesson-box strong', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'lessonbtn'  => mgk_style_button( 'Lesson Ref Buttons', '.mgk-parent-message-compose__lesson-box button' ),
                'modalnote'  => mgk_style_text( 'Attach Modal Note', '.mgk-parent-message-compose__note', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'section'    => [ 'label' => 'Section Box', 'selector' => '.mgk-parent-messages', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
            ],
        ];
    }

    if ( shortcode_exists( 'mgk_parent_messages_empty' ) ) {
        $sections[] = [
            'tag'      => 'mgk_parent_messages_empty',
            'title'    => 'MGK Parent · Messages Empty',
            'icon'     => 'eicon-inbox',
            'controls' => [
                'hidden'             => [ 'type' => 'switcher', 'label' => 'Hide entire section' ],
                'hide_heading'       => [ 'type' => 'switcher', 'label' => 'Hide heading' ],
                'heading'            => [ 'type' => 'text', 'label' => 'Heading', 'default' => 'EMPTY · NO MESSAGES' ],
                'hide_kicker'        => [ 'type' => 'switcher', 'label' => 'Hide top-right label' ],
                'kicker'             => [ 'type' => 'text', 'label' => 'Top-right label', 'default' => 'FIRST-USE' ],
                'hide_illustration'  => [ 'type' => 'switcher', 'label' => 'Hide illustration' ],
                'illustration_label' => [ 'type' => 'text', 'label' => 'Illustration label/icon', 'default' => '☏' ],
                'hide_title'         => [ 'type' => 'switcher', 'label' => 'Hide title' ],
                'title'              => [ 'type' => 'text', 'label' => 'Title', 'default' => 'No messages yet' ],
                'hide_message'       => [ 'type' => 'switcher', 'label' => 'Hide message' ],
                'message'            => [ 'type' => 'textarea', 'label' => 'Message', 'default' => 'ONCE YOU BOOK A TUTOR YOU CAN MESSAGE THEM HERE. ALL CHATS ARE AGENCY-MONITORED & SECURE.' ],
                'hide_button'        => [ 'type' => 'switcher', 'label' => 'Hide CTA button' ],
                'button'             => [ 'type' => 'text', 'label' => 'Button label', 'default' => 'Find a Tutor → S02' ],
                'button_url'         => [ 'type' => 'text', 'label' => 'Button URL', 'default' => '/student/teachers/' ],
                'hide_note'          => [ 'type' => 'switcher', 'label' => 'Hide bottom note' ],
                'note'               => [ 'type' => 'textarea', 'label' => 'Bottom note', 'default' => 'EMPTY THREAD LIST + EMPTY CONVERSATION PANE SHARE ONE CTA. NO COMPOSER UNTIL A THREAD EXISTS.' ],
            ],
            'style_targets' => [
                'top'          => [ 'label' => 'Top Bar', 'selector' => '.mgk-parent-messages-empty__top', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'heading'      => mgk_style_text( 'Heading', '.mgk-parent-messages-empty__top h1', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'kicker'       => mgk_style_text( 'Top-right Label', '.mgk-parent-messages-empty__top span', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'shell'        => [ 'label' => 'Empty Shell', 'selector' => '.mgk-parent-messages-empty__shell', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'body'         => [ 'label' => 'Body Box', 'selector' => '.mgk-parent-messages-empty__body', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'illustration' => [ 'label' => 'Illustration Box', 'selector' => '.mgk-parent-messages-empty__illustration', 'features' => [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'title'        => mgk_style_text( 'Title', '.mgk-parent-messages-empty__body strong', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'message'      => mgk_style_text( 'Message', '.mgk-parent-messages-empty__body p', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'button'       => mgk_style_button( 'CTA Button', '.mgk-parent-messages-empty__body a' ),
                'note'         => mgk_style_text( 'Bottom Note', '.mgk-parent-messages-empty footer', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'section'      => [ 'label' => 'Section Box', 'selector' => '.mgk-parent-messages-empty', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
            ],
        ];
    }

    if ( shortcode_exists( 'mgk_parent_messages_escalation' ) ) {
        $sections[] = [
            'tag'      => 'mgk_parent_messages_escalation',
            'title'    => 'MGK Parent · Message Escalation',
            'icon'     => 'eicon-warning',
            'controls' => [
                'hidden'         => [ 'type' => 'switcher', 'label' => 'Hide entire section' ],
                'hide_title'     => [ 'type' => 'switcher', 'label' => 'Hide alert title' ],
                'title'          => [ 'type' => 'text', 'label' => 'Alert title override', 'default' => '' ],
                'hide_message'   => [ 'type' => 'switcher', 'label' => 'Hide alert message' ],
                'message'        => [ 'type' => 'textarea', 'label' => 'Alert message override', 'default' => '' ],
                'hide_disabled'  => [ 'type' => 'switcher', 'label' => 'Hide disabled composer bar' ],
                'disabled_label' => [ 'type' => 'text', 'label' => 'Disabled composer override', 'default' => '' ],
                'hide_example'   => [ 'type' => 'switcher', 'label' => 'Hide masked example' ],
                'masked_example' => [ 'type' => 'textarea', 'label' => 'Masked example override', 'default' => '' ],
                'hide_note'      => [ 'type' => 'switcher', 'label' => 'Hide agency note' ],
                'note'           => [ 'type' => 'textarea', 'label' => 'Agency note override', 'default' => '' ],
            ],
            'style_targets' => [
                'shell'    => [ 'label' => 'Escalation Shell', 'selector' => '.mgk-parent-message-escalation__shell', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'alert'    => [ 'label' => 'Alert Box', 'selector' => '.mgk-parent-message-escalation__alert', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'title'    => mgk_style_text( 'Alert Title', '.mgk-parent-message-escalation__alert strong', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'message'  => mgk_style_text( 'Alert Message', '.mgk-parent-message-escalation__alert p', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'disabled' => mgk_style_text( 'Disabled Composer Bar', '.mgk-parent-message-escalation__disabled', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border', 'shadow' ] ),
                'example'  => mgk_style_text( 'Masked Example Box', '.mgk-parent-message-escalation__example', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border', 'shadow' ] ),
                'note'     => mgk_style_text( 'Agency Note', '.mgk-parent-message-escalation__note', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'section'  => [ 'label' => 'Section Box', 'selector' => '.mgk-parent-message-escalation', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
            ],
        ];
    }

    /* ── S08 Tutor Proposals (DATA page, split shell widgets) ──────────────
       Proposal data, expiry, match reasons, compare state and booking URLs stay
       locked in PHP/JS. Owners get small widgets so they can hide/reorder/style
       each shell section independently.                                      */
    $proposal_widgets = [
        'mgk_proposal_nav' => [
            'MGK Proposal · Navigation',
            'eicon-nav-menu',
            [
                'hidden'         => [ 'type' => 'switcher', 'label' => 'Hide this section' ],
                'utility'        => [ 'type' => 'text', 'label' => 'Utility text', 'default' => 'Logged-out - proposal via magic link - SG/EN' ],
                'logo_label'     => [ 'type' => 'text', 'label' => 'Logo label', 'default' => '[LOGO]' ],
                'browse_label'   => [ 'type' => 'text', 'label' => 'Browse label', 'default' => 'Browse Tutors' ],
                'subjects_label' => [ 'type' => 'text', 'label' => 'Subjects label', 'default' => 'Subjects' ],
                'how_label'      => [ 'type' => 'text', 'label' => 'How label', 'default' => 'How It Works' ],
                'pricing_label'  => [ 'type' => 'text', 'label' => 'Pricing label', 'default' => 'Pricing' ],
                'signin_label'   => [ 'type' => 'text', 'label' => 'Sign in label', 'default' => 'Sign In' ],
            ],
            [
                'utility' => mgk_style_text( 'Utility Bar Text', '.mgk-proposal-utility span', [ 'typography', 'align', 'color', 'background', 'padding' ] ),
                'logo'    => mgk_style_text( 'Logo', '.mgk-proposal-logo', [ 'typography', 'color' ] ),
                'links'   => mgk_style_text( 'Nav Links', '.mgk-proposal-links a', [ 'typography', 'color' ] ),
                'signin'  => mgk_style_button( 'Sign In Button', '.mgk-proposal-signin' ),
                'navbox'  => [ 'label' => 'Nav Box', 'selector' => '.mgk-proposal-mainnav', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
            ],
        ],
        'mgk_proposal_header' => [
            'MGK Proposal · Header + Expiry',
            'eicon-countdown',
            [
                'hidden'            => [ 'type' => 'switcher', 'label' => 'Hide entire section' ],
                'hide_heading'      => [ 'type' => 'switcher', 'label' => 'Hide heading' ],
                'heading'           => [ 'type' => 'text', 'label' => 'Heading', 'default' => 'Your matched tutors' ],
                'hide_summary'      => [ 'type' => 'switcher', 'label' => 'Hide request summary' ],
                'hide_expiry'       => [ 'type' => 'switcher', 'label' => 'Hide expiry box' ],
                'hide_expiry_label' => [ 'type' => 'switcher', 'label' => 'Hide expiry label' ],
                'expiry_label'      => [ 'type' => 'text', 'label' => 'Expiry label', 'default' => 'PROPOSALS EXPIRE IN' ],
                'hide_expiry_note'  => [ 'type' => 'switcher', 'label' => 'Hide expiry note' ],
                'expiry_note'       => [ 'type' => 'text', 'label' => 'Expiry note', 'default' => 'FREE RE-SEND AFTER' ],
            ],
            [
                'titleblock' => [ 'label' => 'Title Block Box', 'selector' => '.mgk-proposal-titleblock', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'heading'    => mgk_style_text( 'Heading', '.mgk-proposal-titleblock h1', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border', 'shadow', 'hover' ] ),
                'summary'    => mgk_style_text( 'Request Summary', '.mgk-proposal-titleblock p', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'timerbox'   => [ 'label' => 'Expiry Box', 'selector' => '.mgk-proposal-expiry', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'timerlabel' => mgk_style_text( 'Expiry Label', '.mgk-proposal-expiry__label', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'timer'      => mgk_style_text( 'Timer Number', '.mgk-proposal-expiry strong', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'timernote'  => mgk_style_text( 'Expiry Note', '.mgk-proposal-expiry__note', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'expired'    => [ 'label' => 'Expired State Box', 'selector' => '.mgk-proposal-expired', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'section'    => [ 'label' => 'Section Box', 'selector' => '.mgk-proposal-header', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
            ],
        ],
        'mgk_proposal_cards' => [
            'MGK Proposal · Cards Grid',
            'eicon-posts-grid',
            [
                'hidden'               => [ 'type' => 'switcher', 'label' => 'Hide entire section' ],
                'hide_verified_badge'  => [ 'type' => 'switcher', 'label' => 'Hide verified badge' ],
                'verified_label'       => [ 'type' => 'text', 'label' => 'Verified badge label', 'default' => 'Verified' ],
                'hide_demo'            => [ 'type' => 'switcher', 'label' => 'Hide demo strip' ],
                'demo_label'           => [ 'type' => 'text', 'label' => 'Demo label', 'default' => 'Demo' ],
                'demo_empty_label'     => [ 'type' => 'text', 'label' => 'No demo label', 'default' => 'Demo coming soon' ],
                'hide_trust'           => [ 'type' => 'switcher', 'label' => 'Hide trust boxes' ],
                'hide_match_reason'    => [ 'type' => 'switcher', 'label' => 'Hide match reason box' ],
                'hide_why_label'       => [ 'type' => 'switcher', 'label' => 'Hide match reason label' ],
                'why_label'            => [ 'type' => 'text', 'label' => 'Match reason label', 'default' => 'Why matched' ],
                'hide_suggested'       => [ 'type' => 'switcher', 'label' => 'Hide suggested price block' ],
                'hide_suggested_label' => [ 'type' => 'switcher', 'label' => 'Hide suggested label' ],
                'suggested_label'      => [ 'type' => 'text', 'label' => 'Suggested label', 'default' => 'Suggested' ],
                'hide_compare'         => [ 'type' => 'switcher', 'label' => 'Hide compare button' ],
                'compare_label'        => [ 'type' => 'text', 'label' => 'Compare button label', 'default' => '+ Compare' ],
                'hide_select'          => [ 'type' => 'switcher', 'label' => 'Hide select button' ],
                'select_label'         => [ 'type' => 'text', 'label' => 'Select button label', 'default' => 'Select' ],
            ],
            [
                'grid'           => [ 'label' => 'Cards Grid', 'selector' => '.mgk-proposal-grid', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'card'           => [ 'label' => 'Card Box', 'selector' => '.mgk-proposal-card', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow', 'hover' ] ],
                'topline'        => [ 'label' => 'Card Top Row', 'selector' => '.mgk-proposal-card__topline', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'avatar'         => [ 'label' => 'Avatar Circle', 'selector' => '.mgk-proposal-avatar img, .mgk-proposal-avatar > span', 'features' => [ 'background', 'padding', 'border', 'shadow' ] ],
                'avatartext'     => mgk_style_text( 'Avatar Placeholder Text', '.mgk-proposal-avatar > span', [ 'typography', 'align', 'color' ] ),
                'badge'          => mgk_style_text( 'Verified Badge', '.mgk-proposal-avatar b', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border', 'shadow' ] ),
                'identitybox'    => [ 'label' => 'Identity Box', 'selector' => '.mgk-proposal-identity', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'name'           => mgk_style_text( 'Tutor Name', '.mgk-proposal-identity h2', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'meta'           => mgk_style_text( 'Tutor Meta', '.mgk-proposal-identity p', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'demo'           => mgk_style_button( 'Demo Strip', '.mgk-proposal-demo' ),
                'trustrow'       => [ 'label' => 'Trust Row', 'selector' => '.mgk-proposal-trust', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'trustbox'       => [ 'label' => 'Trust Box', 'selector' => '.mgk-proposal-trust div', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow', 'hover' ] ],
                'trustvalue'     => mgk_style_text( 'Trust Value', '.mgk-proposal-trust strong', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'trustlabel'     => mgk_style_text( 'Trust Label', '.mgk-proposal-trust span', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'whybox'         => [ 'label' => 'Why Matched Box', 'selector' => '.mgk-proposal-why', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'whylabel'       => mgk_style_text( 'Why Matched Label', '.mgk-proposal-why b', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'whytext'        => mgk_style_text( 'Why Matched Text', '.mgk-proposal-why span', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'actions'        => [ 'label' => 'Actions Row', 'selector' => '.mgk-proposal-actions', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'suggestedbox'   => [ 'label' => 'Suggested Block', 'selector' => '.mgk-proposal-suggested', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'suggestedlabel' => mgk_style_text( 'Suggested Label', '.mgk-proposal-suggested span', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'suggestedprice' => mgk_style_text( 'Suggested Price', '.mgk-proposal-suggested strong', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'buttonrow'      => [ 'label' => 'Button Row', 'selector' => '.mgk-proposal-action-buttons', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'compare'        => mgk_style_button( 'Compare Button', '.mgk-proposal-compare' ),
                'select'         => mgk_style_button( 'Select Button', '.mgk-proposal-select' ),
                'section'        => [ 'label' => 'Section Box', 'selector' => '.mgk-proposal-cards-section', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
            ],
        ],
        'mgk_proposal_rematch' => [
            'MGK Proposal · Re-match Banner',
            'eicon-refresh',
            [
                'hidden'       => [ 'type' => 'switcher', 'label' => 'Hide entire section' ],
                'hide_heading' => [ 'type' => 'switcher', 'label' => 'Hide heading' ],
                'heading'      => [ 'type' => 'text', 'label' => 'Heading', 'default' => 'None quite right?' ],
                'hide_body'    => [ 'type' => 'switcher', 'label' => 'Hide body' ],
                'body'         => [ 'type' => 'textarea', 'label' => 'Body', 'default' => 'GET A FRESH SET OF MATCHES - FREE, NO LIMIT ON FIRST RE-MATCH (BR-11)' ],
                'hide_button'  => [ 'type' => 'switcher', 'label' => 'Hide button' ],
                'button'       => [ 'type' => 'text', 'label' => 'Button label', 'default' => 'Request re-match (free)' ],
            ],
            [
                'inner'   => [ 'label' => 'Inner Layout Box', 'selector' => '.mgk-proposal-rematch__inner', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'heading' => mgk_style_text( 'Heading', '.mgk-proposal-rematch h2', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'body'    => mgk_style_text( 'Body', '.mgk-proposal-rematch p', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'button'  => mgk_style_button( 'Button', '.mgk-proposal-rematch__button' ),
                'section' => [ 'label' => 'Section Box', 'selector' => '.mgk-proposal-rematch', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
            ],
        ],
        'mgk_proposal_compare' => [
            'MGK Proposal · Compare Drawer',
            'eicon-table',
            [
                'hidden'       => [ 'type' => 'switcher', 'label' => 'Hide entire section' ],
                'hide_heading' => [ 'type' => 'switcher', 'label' => 'Hide heading' ],
                'heading'      => [ 'type' => 'text', 'label' => 'Heading', 'default' => 'Compare' ],
                'hide_button'  => [ 'type' => 'switcher', 'label' => 'Hide toggle button' ],
                'button'       => [ 'type' => 'text', 'label' => 'Button label', 'default' => 'View comparison' ],
                'hide_table'   => [ 'type' => 'switcher', 'label' => 'Hide comparison table' ],
            ],
            [
                'head'    => [ 'label' => 'Drawer Header', 'selector' => '.mgk-proposal-compare-head', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'title'   => mgk_style_text( 'Header Text', '.mgk-proposal-compare-head strong', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'button'  => mgk_style_button( 'Toggle Button', '.mgk-proposal-compare-head button' ),
                'body'    => [ 'label' => 'Table Body Box', 'selector' => '.mgk-proposal-compare-body', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'table'   => [ 'label' => 'Table Cells', 'selector' => '.mgk-proposal-compare-table th, .mgk-proposal-compare-table td', 'features' => [ 'typography', 'align', 'color', 'background', 'padding', 'border' ] ],
                'toast'   => mgk_style_text( 'Max Compare Toast', '.mgk-proposal-toast', [ 'typography', 'align', 'color', 'background', 'padding', 'border', 'shadow' ] ),
                'section' => [ 'label' => 'Section Box', 'selector' => '.mgk-proposal-compare-drawer', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
            ],
        ],
        'mgk_proposal_state_intro' => [
            'MGK Proposal State · Intro',
            'eicon-editor-list-ul',
            [
                'hidden'       => [ 'type' => 'switcher', 'label' => 'Hide entire section' ],
                'hide_heading' => [ 'type' => 'switcher', 'label' => 'Hide heading' ],
                'heading'      => [ 'type' => 'text', 'label' => 'Heading', 'default' => 'State slices' ],
                'hide_nav'     => [ 'type' => 'switcher', 'label' => 'Hide state nav' ],
                'nav'          => [ 'type' => 'text', 'label' => 'State nav text', 'default' => 'Expired · Re-match · Skeleton' ],
            ],
            [
                'inner'   => [ 'label' => 'Intro Layout Box', 'selector' => '.mgk-proposal-state-intro .mgk-proposal-state-shell', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'heading' => mgk_style_text( 'Heading', '.mgk-proposal-state-intro h1', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'nav'     => mgk_style_text( 'State Nav Text', '.mgk-proposal-state-intro p', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'section' => [ 'label' => 'Section Box', 'selector' => '.mgk-proposal-state-intro', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
            ],
        ],
        'mgk_proposal_state_expired' => [
            'MGK Proposal State · Expired',
            'eicon-clock-o',
            [
                'hidden'       => [ 'type' => 'switcher', 'label' => 'Hide entire section' ],
                'hide_label'   => [ 'type' => 'switcher', 'label' => 'Hide slice label' ],
                'label'        => [ 'type' => 'text', 'label' => 'Slice label', 'default' => 'Proposed - expired (BR-11)' ],
                'hide_icon'    => [ 'type' => 'switcher', 'label' => 'Hide icon' ],
                'icon'         => [ 'type' => 'text', 'label' => 'Icon text', 'default' => '⌛' ],
                'hide_title'   => [ 'type' => 'switcher', 'label' => 'Hide title' ],
                'title'        => [ 'type' => 'text', 'label' => 'Title', 'default' => 'These proposals expired' ],
                'hide_message' => [ 'type' => 'switcher', 'label' => 'Hide message' ],
                'message'      => [ 'type' => 'textarea', 'label' => 'Message', 'default' => '48H WINDOW CLOSED. TUTOR AVAILABILITY MAY HAVE CHANGED.' ],
                'hide_button'  => [ 'type' => 'switcher', 'label' => 'Hide button' ],
                'button'       => [ 'type' => 'text', 'label' => 'Button label', 'default' => 'Re-send proposals (free)' ],
            ],
            [
                'label'   => mgk_style_text( 'Slice Label', '.mgk-proposal-state--expired .mgk-proposal-state__label', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'body'    => [ 'label' => 'Body Box', 'selector' => '.mgk-proposal-state--expired .mgk-proposal-state__body', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'icon'    => mgk_style_text( 'Icon', '.mgk-proposal-state--expired .mgk-proposal-state__icon', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'title'   => mgk_style_text( 'Title', '.mgk-proposal-state--expired .mgk-proposal-state__title', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'message' => mgk_style_text( 'Message', '.mgk-proposal-state--expired .mgk-proposal-state__message', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'button'  => mgk_style_button( 'Button', '.mgk-proposal-state--expired .mgk-proposal-state__button' ),
                'section' => [ 'label' => 'Section Box', 'selector' => '.mgk-proposal-state--expired', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
            ],
        ],
        'mgk_proposal_state_selected' => [
            'MGK Proposal State · Selected Card',
            'eicon-check-circle',
            [
                'hidden'       => [ 'type' => 'switcher', 'label' => 'Hide entire section' ],
                'hide_label'   => [ 'type' => 'switcher', 'label' => 'Hide slice label' ],
                'label'        => [ 'type' => 'text', 'label' => 'Slice label', 'default' => 'Selected (card highlighted)' ],
                'hide_tutor'   => [ 'type' => 'switcher', 'label' => 'Hide tutor name' ],
                'tutor'        => [ 'type' => 'text', 'label' => 'Tutor name', 'default' => 'Ms Lee' ],
                'hide_status'  => [ 'type' => 'switcher', 'label' => 'Hide selected status' ],
                'status'       => [ 'type' => 'text', 'label' => 'Selected status', 'default' => 'Selected' ],
                'hide_dot'     => [ 'type' => 'switcher', 'label' => 'Hide red dot' ],
                'hide_button'  => [ 'type' => 'switcher', 'label' => 'Hide button' ],
                'button'       => [ 'type' => 'text', 'label' => 'Button label', 'default' => 'Continue to trial' ],
            ],
            [
                'label'   => mgk_style_text( 'Slice Label', '.mgk-proposal-state--selected .mgk-proposal-state__label', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'body'    => [ 'label' => 'Body Box', 'selector' => '.mgk-proposal-state--selected .mgk-proposal-state__body', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'card'    => [ 'label' => 'Selected Card Box', 'selector' => '.mgk-proposal-state-selected-card', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow', 'hover' ] ],
                'toprow'  => [ 'label' => 'Top Row', 'selector' => '.mgk-proposal-state-selected-card__top', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'name'    => mgk_style_text( 'Tutor Name', '.mgk-proposal-state-selected-card__name', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'status'  => mgk_style_text( 'Selected Status', '.mgk-proposal-state-selected-card__status', [ 'typography', 'align', 'color', 'background', 'padding', 'margin' ] ),
                'dot'     => [ 'label' => 'Red Dot', 'selector' => '.mgk-proposal-state-selected-card__dot', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'button'  => mgk_style_button( 'Continue Button', '.mgk-proposal-state-selected-card__button' ),
                'section' => [ 'label' => 'Section Box', 'selector' => '.mgk-proposal-state--selected', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
            ],
        ],
        'mgk_proposal_state_rematch_requested' => [
            'MGK Proposal State · Re-match Requested',
            'eicon-sync',
            [
                'hidden'       => [ 'type' => 'switcher', 'label' => 'Hide entire section' ],
                'hide_label'   => [ 'type' => 'switcher', 'label' => 'Hide slice label' ],
                'label'        => [ 'type' => 'text', 'label' => 'Slice label', 'default' => 'Re-match requested' ],
                'hide_message' => [ 'type' => 'switcher', 'label' => 'Hide message' ],
                'message'      => [ 'type' => 'textarea', 'label' => 'Message', 'default' => 'FINDING NEW MATCHES. YOU WILL GET A FRESH SET WITHIN 6H.' ],
                'hide_timer'   => [ 'type' => 'switcher', 'label' => 'Hide timer' ],
                'timer'        => [ 'type' => 'text', 'label' => 'Timer text', 'default' => '05:58:00' ],
            ],
            [
                'label'   => mgk_style_text( 'Slice Label', '.mgk-proposal-state--rematch-requested .mgk-proposal-state__label', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'body'    => [ 'label' => 'Body Box', 'selector' => '.mgk-proposal-state--rematch-requested .mgk-proposal-state__body', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'message' => mgk_style_text( 'Message', '.mgk-proposal-state--rematch-requested .mgk-proposal-state__message', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'timer'   => mgk_style_text( 'Timer', '.mgk-proposal-state--rematch-requested .mgk-proposal-state__timer', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'section' => [ 'label' => 'Section Box', 'selector' => '.mgk-proposal-state--rematch-requested', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
            ],
        ],
        'mgk_proposal_state_skeleton' => [
            'MGK Proposal State · Loading Skeleton',
            'eicon-sitemap',
            [
                'hidden'       => [ 'type' => 'switcher', 'label' => 'Hide entire section' ],
                'hide_label'   => [ 'type' => 'switcher', 'label' => 'Hide slice label' ],
                'label'        => [ 'type' => 'text', 'label' => 'Slice label', 'default' => 'Loading skeleton' ],
                'hide_avatar'  => [ 'type' => 'switcher', 'label' => 'Hide avatar placeholder' ],
                'hide_lines'   => [ 'type' => 'switcher', 'label' => 'Hide text lines' ],
                'lines'        => [ 'type' => 'number', 'label' => 'Line count', 'default' => 4, 'min' => 1, 'max' => 6 ],
            ],
            [
                'label'   => mgk_style_text( 'Slice Label', '.mgk-proposal-state--skeleton .mgk-proposal-state__label', [ 'typography', 'align', 'color', 'background', 'padding', 'margin', 'border' ] ),
                'body'    => [ 'label' => 'Body Box', 'selector' => '.mgk-proposal-state--skeleton .mgk-proposal-state__body', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'layout'  => [ 'label' => 'Skeleton Layout', 'selector' => '.mgk-proposal-state-skeleton', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
                'avatar'  => [ 'label' => 'Avatar Placeholder', 'selector' => '.mgk-proposal-state-skeleton__avatar', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'lines'   => [ 'label' => 'Line Placeholders', 'selector' => '.mgk-proposal-state-skeleton__lines span', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
                'section' => [ 'label' => 'Section Box', 'selector' => '.mgk-proposal-state--skeleton', 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow' ] ],
            ],
        ],
    ];

    foreach ( $proposal_widgets as $tag => $meta ) {
        if ( shortcode_exists( $tag ) ) {
            $sections[] = [
                'tag'           => $tag,
                'title'         => $meta[0],
                'icon'          => $meta[1],
                'controls'      => $meta[2],
                'style_targets' => $meta[3],
            ];
        }
    }

    /* Legacy/composite S08 widget: useful for one-drop rendering, but seeded
       proposal pages use the split widgets above. */
    if ( shortcode_exists( 'mgk_proposals' ) ) {
        $sections[] = [
            'tag'           => 'mgk_proposals',
            'title'         => 'MGK Proposal · Full Page (legacy)',
            'icon'          => 'eicon-posts-grid',
            'controls'      => [],
            'style_targets' => [
                'heading' => mgk_style_text( 'Header Heading', '.mgk-proposal-titleblock h1' ),
                'cardbox' => [ 'label' => 'Proposal Card Box', 'selector' => '.mgk-proposal-card', 'features' => [ 'background', 'padding', 'border', 'shadow' ] ],
                'select'  => mgk_style_button( 'Select Button', '.mgk-proposal-select' ),
                'section' => [ 'label' => 'Page Box', 'selector' => '.mgk-proposals-page', 'features' => [ 'background', 'padding', 'margin' ] ],
            ],
        ];
    }

    /* ── S10 Slot Picker (DATA page, locked layout) ──────────────────
       The availability grid + checkout as ONE widget. Slot query, state
       (held/booked) and the "Confirm & pay" flow stay LOCKED; owners
       restyle the heading + slot buttons + confirm button (Style tab).      */
    if ( shortcode_exists( 'mgk_slot_picker' ) ) {
        $sections[] = [
            'tag'           => 'mgk_slot_picker',
            'title'         => 'MGK · Slot Picker (booking)',
            'icon'          => 'eicon-calendar',
            'controls'      => [],
            'style_targets' => [
                'heading'    => mgk_style_text( 'Heading', '.mgk-slot-picker__heading' ),
                'hint'       => mgk_style_text( 'Hint Text', '.mgk-slot-picker__hint' ),
                'steps'      => mgk_style_text( 'Step Indicator', '.mgk-booking-steps' ),
                'daylabel'   => mgk_style_text( 'Day Labels', '.mgk-slot-day__label' ),
                'slotbtn'    => mgk_style_button( 'Slot Buttons', '.mgk-slot-btn' ),
                'confirmbtn' => mgk_style_button( '"Confirm & pay" Button', '.mgk-slot-picker .mgk-btn-accent' ),
                'section'    => [ 'label' => 'Section Box', 'selector' => '.mgk-slot-picker', 'features' => [ 'background', 'padding', 'margin' ] ],
            ],
        ];
    }

    /* ── State panels (404 / empty / loading / permission / validation /
       form-error / offline / server-error / session-expired) ──────────────
       Pure CONTENT shells: no data/logic to lock. Owners edit the title +
       message text (Content tab) and restyle the panel / heading / message /
       button (Style tab). Reusable on any page (e.g. a styled 404 page).     */
    $title_msg = function ( $title_label = 'Title', $msg_label = 'Message', $extra = [] ) {
        return array_merge( [
            'title'   => [ 'type' => 'text',     'label' => $title_label ],
            'message' => [ 'type' => 'textarea', 'label' => $msg_label ],
        ], $extra );
    };
    $state_panel_style = function ( $outer, $heading, $with_button = true ) {
        $t = [
            'eyebrow' => mgk_style_text( 'Eyebrow', $outer . ' .mgk-eyebrow' ),
            'heading' => mgk_style_text( 'Heading', $heading ),
            'message' => mgk_style_text( 'Message', $outer . ' p:not(.mgk-eyebrow)' ),
        ];
        if ( $with_button ) {
            $t['button'] = mgk_style_button( 'Button', $outer . ' .mgk-btn' );
        }
        $t['section'] = [ 'label' => 'Panel Box', 'selector' => $outer, 'features' => [ 'background', 'padding', 'margin', 'border', 'shadow', 'align' ] ];
        return $t;
    };
    $state_sections = [
        'mgk_state_404' => [
            'MGK State · 404 / Not Found', 'eicon-error-404',
            $title_msg(), $state_panel_style( '.mgk-not-found-panel', '.mgk-not-found-panel h1' ),
        ],
        'mgk_state_empty' => [
            'MGK State · Empty Results', 'eicon-search-results',
            $title_msg(), $state_panel_style( '.mgk-empty-results', '.mgk-empty-results h2' ),
        ],
        'mgk_state_loading' => [
            'MGK State · Loading Skeleton', 'eicon-loading',
            [ 'count' => [ 'type' => 'number', 'label' => 'Skeleton cards', 'default' => 4, 'min' => 1, 'max' => 12 ] ],
            [ 'section' => [ 'label' => 'Skeleton Box', 'selector' => '.mgk-listing-skeleton', 'features' => [ 'padding', 'margin' ] ] ],
        ],
        'mgk_state_permission' => [
            'MGK State · Permission Denied', 'eicon-lock-user',
            $title_msg(), $state_panel_style( '.mgk-state-permission-denied', '.mgk-state-permission-denied h2' ),
        ],
        'mgk_state_validation' => [
            'MGK State · Validation Message', 'eicon-warning',
            [ 'message' => [ 'type' => 'textarea', 'label' => 'Message' ] ],
            [
                'message' => mgk_style_text( 'Message', '.mgk-form-message' ),
                'section' => [ 'label' => 'Box', 'selector' => '.mgk-form-message', 'features' => [ 'background', 'padding', 'margin', 'border' ] ],
            ],
        ],
        'mgk_state_form_error' => [
            'MGK State · Form Error', 'eicon-warning',
            $title_msg(), $state_panel_style( '.mgk-state-error', '.mgk-state-error h2', false ),
        ],
        'mgk_state_offline' => [
            'MGK State · Offline', 'eicon-time-line',
            $title_msg(), $state_panel_style( '.mgk-state-offline', '.mgk-state-offline h2' ),
        ],
        'mgk_state_server_error' => [
            'MGK State · Server Error', 'eicon-alert',
            $title_msg(), $state_panel_style( '.mgk-state-error', '.mgk-state-error h2', false ),
        ],
        'mgk_state_session_expired' => [
            'MGK State · Session Expired', 'eicon-clock-o',
            $title_msg(), $state_panel_style( '.mgk-state-session-expired', '.mgk-state-session-expired h2' ),
        ],
    ];
    foreach ( $state_sections as $tag => $meta ) {
        if ( shortcode_exists( $tag ) ) {
            $sections[] = [
                'tag'           => $tag,
                'title'         => $meta[0],
                'icon'          => $meta[1],
                'controls'      => $meta[2],
                'style_targets' => $meta[3],
            ];
        }
    }

    /* ────────────────────────────────────────────────────────
       Content-page sections (S04 Subjects, S05 How, S06 Pricing).
       Each entry: tag => [ title, style_targets ].

       Style policy (per "Locked Core + Editable Shell"):
         • CONTENT (marketing copy) → mgk_content_targets(): full text styling.
         • DATA  (query grids, pricing logic, tables) → mgk_data_targets():
           box-level styling only (background / padding / margin / block align),
           so the logic-driven presentation stays consistent.
       Selectors come from the section partials' actual markup.
       ──────────────────────────────────────────────────────── */
    $content_sections = [
        // S04 Subjects — DATA grids get Heading+Sub only (logic locked); copy gets full.
        'mgk_subjects_hero'          => [ 'MGK Subjects · Hero + Search', mgk_content_targets( '.mgk-subject-hero', 'section h1', 'section p', [
            'searchbtn' => mgk_style_button( 'Search Button', '.mgk-subject-hero .mgk-btn-accent' ),
        ] ) ],
        'mgk_subjects_levels'        => [ 'MGK Subjects · By Level',      mgk_data_targets( '.mgk-subject-levels', '.mgk-section-head h2', '.mgk-section-head p' ) ],
        'mgk_subjects_exams'         => [ 'MGK Subjects · By Exam',       mgk_data_targets( '.mgk-subject-exams', '.mgk-section-head h2' ) ],
        'mgk_subjects_combinations'  => [ 'MGK Subjects · Combinations',  mgk_data_targets( '.mgk-subject-combos', '.mgk-section-head h2', '.mgk-section-head p' ) ],
        'mgk_subjects_trending'      => [ 'MGK Subjects · Trending',      mgk_data_targets( '.mgk-subject-trending', '.mgk-section-head h2', '.mgk-section-head p' ) ],
        'mgk_subjects_streams'       => [ 'MGK Subjects · Streams',       mgk_data_targets( '.mgk-subject-streams', '.mgk-section-head h2' ) ],
        'mgk_subjects_international'  => [ 'MGK Subjects · International', mgk_data_targets( '.mgk-subject-international', '.mgk-section-head h2' ) ],
        'mgk_subjects_featured'      => [ 'MGK Subjects · Featured',      mgk_data_targets( '.mgk-subject-featured', 'section h2' ) ],
        'mgk_subjects_cta'           => [ 'MGK Subjects · CTA',           mgk_content_targets( '.mgk-subject-cta', 'section h2', 'section p', [
            'ctabtn' => mgk_style_button( 'CTA Button', '.mgk-subject-cta .mgk-btn-light' ),
        ] ) ],
        // S05 How It Works — marketing copy throughout (full per-element).
        'mgk_how_hero'         => [ 'MGK How · Hero',         mgk_content_targets( '.mgk-how-hero', 'section h1', 'section p', [
            'eyebrow'   => mgk_style_text( 'Eyebrow', '.mgk-how-hero .mgk-eyebrow' ),
            'tabtitle'  => mgk_style_text( 'Tab Title', '.mgk-how-hero .mgk-tab-full' ),
            'panelhead' => mgk_style_text( 'Panel Heading', '.mgk-tab-panel h2' ),
            'panelbody' => mgk_style_text( 'Panel Body', '.mgk-tab-panel p' ),
        ] ) ],
        'mgk_how_process'      => [ 'MGK How · Process',      mgk_content_targets( '.mgk-how-process', '.mgk-how-section-head h2', '.mgk-how-section-head p', [
            'stepnum'   => mgk_style_text( 'Step Number', '.mgk-process-num', [ 'typography', 'color', 'background' ] ),
            'steptitle' => mgk_style_text( 'Step Title', '.mgk-process-step h3' ),
            'steptime'  => mgk_style_text( 'Step Time', '.mgk-process-step strong' ),
            'stepbody'  => mgk_style_text( 'Step Body', '.mgk-process-step p' ),
            'ctabtn'    => mgk_style_button( 'CTA Button', '.mgk-how-process .mgk-btn-accent' ),
        ] ) ],
        'mgk_how_video'        => [ 'MGK How · Video',        mgk_content_targets( '.mgk-how-video', 'section h2', 'section p', [
            'eyebrow'    => mgk_style_text( 'Eyebrow', '.mgk-how-video .mgk-eyebrow' ),
            'check'      => mgk_style_text( 'Checklist Item', '.mgk-how-video .mgk-check-list li' ),
            'primarybtn' => mgk_style_button( 'Primary Button', '.mgk-how-video .mgk-btn-accent' ),
            'secondbtn'  => mgk_style_button( 'Secondary Button', '.mgk-how-video .mgk-btn-outline' ),
        ] ) ],
        'mgk_how_difference'   => [ 'MGK How · Difference',   mgk_content_targets( '.mgk-how-difference', '.mgk-section-head h2', '.mgk-section-head p', [
            'cardtitle' => mgk_style_text( 'Card Title', '.mgk-difference-card h3' ),
            'cardbody'  => mgk_style_text( 'Card Body', '.mgk-difference-card p' ),
            'cardproof' => mgk_style_text( 'Card Proof', '.mgk-difference-card strong' ),
        ] ) ],
        'mgk_how_guarantee'    => [ 'MGK How · Guarantee',    mgk_content_targets( '.mgk-how-guarantee', '.mgk-section-head h2', '.mgk-section-head p', [
            'cardtitle' => mgk_style_text( 'Card Title', '.mgk-guarantee-card h3' ),
            'cardbody'  => mgk_style_text( 'Card Body', '.mgk-guarantee-card p' ),
        ] ) ],
        'mgk_how_pricing'      => [ 'MGK How · Pricing',      mgk_content_targets( '.mgk-how-pricing', '.mgk-section-head h2', '.mgk-section-head p', [
            'paneltitle' => mgk_style_text( 'Panel Title', '.mgk-price-panel h3' ),
            'ctabtn'     => mgk_style_button( 'CTA Button', '.mgk-how-pricing .mgk-btn-outline' ),
        ] ) ],
        'mgk_how_verification' => [ 'MGK How · Verification', mgk_content_targets( '.mgk-how-verification', '.mgk-section-head h2', '.mgk-section-head p', [
            'cardtitle' => mgk_style_text( 'Step Title', '.mgk-verification-card h3' ),
        ] ) ],
        'mgk_how_comparison'   => [ 'MGK How · Comparison',   mgk_content_targets( '.mgk-how-comparison', '.mgk-section-head h2', '.mgk-section-head p', [
            'rowlabel' => mgk_style_text( 'Row Label', '.mgk-compare-row div' ),
        ] ) ],
        'mgk_how_concerns'     => [ 'MGK How · Concerns',     mgk_content_targets( '.mgk-how-concerns', '.mgk-section-head h2', '.mgk-section-head p', [
            'question' => mgk_style_text( 'Concern Question', '.mgk-concern-card h3' ),
            'answer'   => mgk_style_text( 'Concern Answer', '.mgk-concern-card p' ),
        ] ) ],
        'mgk_how_faq'          => [ 'MGK How · FAQ',          mgk_content_targets( '.mgk-how-faq', 'section h2', 'section p', [
            'grouptitle' => mgk_style_text( 'Group Title', '.mgk-how-faq-group h3' ),
            'question'   => mgk_style_text( 'Question', '.mgk-faq-item button span' ),
            'answer'     => mgk_style_text( 'Answer', '.mgk-faq-answer' ),
        ] ) ],
        'mgk_how_cta'          => [ 'MGK How · CTA',          mgk_content_targets( '.mgk-cta-band', '.mgk-cta-band h2', '.mgk-cta-band p', [
            'primarybtn' => mgk_style_button( 'Primary Button', '.mgk-cta-band .mgk-btn-light' ),
            'secondbtn'  => mgk_style_button( 'Secondary Button', '.mgk-cta-band .mgk-btn-accent' ),
            'note'       => mgk_style_text( 'Note', '.mgk-cta-note' ),
        ] ) ],
        // S06 Pricing — hero/faq/packages/cta/included/not-included are copy;
        // calculator/rate-table/comparison/subject-premium/packages-data are logic.
        'mgk_pricing_hero'            => [ 'MGK Pricing · Hero',            mgk_content_targets( '.mgk-pricing-hero', 'section h1', 'section p', [
            'eyebrow'   => mgk_style_text( 'Eyebrow', '.mgk-pricing-hero .mgk-eyebrow' ),
            'statvalue' => mgk_style_text( 'Stat Value', '.mgk-pricing-stat strong', [ 'typography', 'color' ] ),
            'statlabel' => mgk_style_text( 'Stat Label', '.mgk-pricing-stat span', [ 'typography', 'color' ] ),
        ] ) ],
        'mgk_pricing_calculator'      => [ 'MGK Pricing · Calculator',      mgk_data_targets( '.mgk-pricing-calculator-section', '.mgk-section-head h2', '.mgk-section-head p' ) ],
        'mgk_pricing_rate_table'      => [ 'MGK Pricing · Rate Table',      mgk_data_targets( '.mgk-pricing-rate-section', '.mgk-section-head h2', '.mgk-section-head p' ) ],
        'mgk_pricing_subject_premium' => [ 'MGK Pricing · Subject Premium', mgk_data_targets( '.mgk-section', '.mgk-section-head h2', '.mgk-section-head p' ) ],
        'mgk_pricing_packages'        => [ 'MGK Pricing · Packages',        mgk_data_targets( '.mgk-section', '.mgk-section-head h2', '.mgk-section-head p' ) ],
        'mgk_pricing_included'        => [ 'MGK Pricing · Included',        mgk_content_targets( '.mgk-section', '.mgk-section-head h2', '.mgk-section-head p', [
            'cardtitle' => mgk_style_text( 'Item Title', '.mgk-card h3' ),
            'cardbody'  => mgk_style_text( 'Item Body', '.mgk-card p' ),
        ] ) ],
        'mgk_pricing_not_included'    => [ 'MGK Pricing · Not Included',    mgk_content_targets( '.mgk-section', '.mgk-section-head h2', '.mgk-section-head p', [
            'cardtitle' => mgk_style_text( 'Item Title', '.mgk-card h3' ),
            'cardcost'  => mgk_style_text( 'Item Cost', '.mgk-card strong' ),
            'cardbody'  => mgk_style_text( 'Item Body', '.mgk-card p' ),
        ] ) ],
        'mgk_pricing_comparison'      => [ 'MGK Pricing · Comparison',      mgk_data_targets( '.mgk-pricing-comparison-section', '.mgk-section-head h2', '.mgk-section-head p' ) ],
        'mgk_pricing_faq'             => [ 'MGK Pricing · FAQ',             mgk_content_targets( '.mgk-section', '.mgk-section-head h2', null, [
            'question' => mgk_style_text( 'Question', '.mgk-faq-item button span' ),
            'answer'   => mgk_style_text( 'Answer', '.mgk-faq-answer' ),
        ] ) ],
        'mgk_pricing_cta'             => [ 'MGK Pricing · CTA',             mgk_content_targets( '.mgk-cta-band', '.mgk-cta-band h2', '.mgk-cta-band p', [
            'primarybtn' => mgk_style_button( 'Primary Button', '.mgk-cta-band .mgk-btn-light' ),
            'secondbtn'  => mgk_style_button( 'Secondary Button', '.mgk-cta-band .mgk-btn-accent' ),
            'note'       => mgk_style_text( 'Note', '.mgk-cta-note' ),
        ] ) ],
    ];

    foreach ( $content_sections as $tag => $meta ) {
        if ( shortcode_exists( $tag ) ) {
            $sections[] = [
                'tag'           => $tag,
                'title'         => $meta[0],
                'icon'          => 'eicon-section',
                'controls'      => [],
                'style_targets' => $meta[1],
            ];
        }
    }

    return $sections;
}

/**
 * Look up one section config by its tag/widget-name.
 * Used by the widget on rehydration when no config was smuggled in via args.
 */
function mgk_elementor_section_config( $tag ) {
    static $index = null;
    if ( $index === null ) {
        $index = [];
        foreach ( mgk_elementor_sections() as $cfg ) {
            if ( ! empty( $cfg['tag'] ) ) {
                $index[ $cfg['tag'] ] = $cfg;
            }
        }
    }
    return isset( $index[ $tag ] ) ? $index[ $tag ] : null;
}


/**
 * Is the given post built with Elementor (i.e. has a saved Elementor layout)?
 *
 * Replaces the Flatsome build's `ux_builder_is_active()` + "[mgk_ in content"
 * heuristic. When a page is built with Elementor, Elementor hijacks the_content()
 * to render its layout — so templates just need to know whether to defer to that
 * (BUILDER MODE) or fall back to the curated default sections (DEFAULT MODE).
 *
 * Safe when Elementor is not installed/active — returns false.
 *
 * @param int $post_id
 * @return bool
 */
function mgk_is_built_with_elementor( $post_id ) {
    $post_id = (int) $post_id;
    if ( ! $post_id ) {
        return false;
    }

    // A page is only "built" if it has an ACTUAL saved layout. Elementor sets
    // _elementor_edit_mode=builder the moment the editor is opened — even before
    // anything is added — so trusting that flag alone makes a page with an empty
    // layout fall into BUILDER MODE and render the_content() (blank), instead of
    // the full curated DEFAULT MODE. Require non-empty _elementor_data so an empty
    // builder draft still shows the real page. (This also fixes the editor opening
    // on a blank canvas: with no saved widgets, the page stays DEFAULT MODE.)
    $data = get_post_meta( $post_id, '_elementor_data', true );
    $has_layout = is_array( $data )
        ? ! empty( $data )
        : ( is_string( $data ) && $data !== '' && $data !== '[]' && trim( $data ) !== '' );
    if ( ! $has_layout ) {
        return false;
    }

    // Preferred: Elementor's own document API (handles revisions, autosave, etc.).
    if ( did_action( 'elementor/loaded' ) && class_exists( '\Elementor\Plugin' ) ) {
        $documents = \Elementor\Plugin::$instance->documents ?? null;
        if ( $documents ) {
            $doc = $documents->get( $post_id );
            if ( $doc && method_exists( $doc, 'is_built_with_elementor' ) ) {
                return (bool) $doc->is_built_with_elementor();
            }
        }
    }

    // Fallback: the meta flag Elementor sets when a page is edited in the builder.
    return get_post_meta( $post_id, '_elementor_edit_mode', true ) === 'builder';
}


/* ── Register the custom category ────────────────────────── */
add_action( 'elementor/elements/categories_registered', function ( $elements_manager ) {
    $elements_manager->add_category( 'mgk-edu', [
        'title' => __( 'MGK Edu', 'mgk-edu' ),
        'icon'  => 'eicon-flash',
    ] );
} );

/* ── Define the widget class as soon as Elementor is loaded ── */
add_action( 'elementor/loaded', 'mgk_elementor_define_widget_class' );

/* ── Register one widget per section ─────────────────────── */
add_action( 'elementor/widgets/register', function ( $widgets_manager ) {
    // Ensure the class is defined (in case this fires before elementor/loaded did,
    // or that hook order changes between Elementor versions).
    mgk_elementor_define_widget_class();
    if ( ! class_exists( 'MGK_Elementor_Section_Widget' ) ) {
        return;
    }
    foreach ( mgk_elementor_sections() as $cfg ) {
        if ( empty( $cfg['tag'] ) ) {
            continue;
        }
        $widgets_manager->register( new MGK_Elementor_Section_Widget( [], [ 'mgk_config' => $cfg ] ) );
    }
} );
