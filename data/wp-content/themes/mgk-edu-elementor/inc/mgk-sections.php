<?php
/**
 * MGK home (S01) sections as shortcodes.
 *
 * ARCHITECTURE — "shortcode wraps partial" (same as inc/mgk-content-sections.php):
 *   Each shortcode is a THIN WRAPPER that renders a template-part via
 *   get_template_part(). The HTML markup lives in ONE place
 *   (template-parts/sections/home/*.php), never duplicated. front-page.php and the
 *   UX Builder element both render through the same partial → markup never diverges.
 *
 *   Function names (mgk_section_*) and shortcode tags (mgk_hero, mgk_steps, ...)
 *   are unchanged so front-page.php and inc/mgk-ux-builder.php keep working as-is.
 *
 *   Per-instance atts (eyebrow, heading, ...) override; when omitted, the partial
 *   falls back to mgk_site_setting(), so calling a shortcode with no atts
 *   reproduces the original home output exactly.
 *
 * See: inc/mgk-ux-builder.php (element registration), docs/TEMPLATE-BUILD-PLAYBOOK.md
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function mgk_shortcode_att( $atts, $key, $setting_key = '', $fallback = '' ) {
    if ( is_array( $atts ) && array_key_exists( $key, $atts ) && $atts[ $key ] !== '' ) {
        return $atts[ $key ];
    }

    if ( $setting_key !== '' ) {
        return mgk_site_setting( $setting_key );
    }

    return $fallback;
}

function mgk_topbar_default_layout_content() {
    return '[mgk_topbar_phone]
[mgk_topbar_email]
[mgk_topbar_links]
[mgk_topbar_tutor_link]
[mgk_topbar_agency_link]
[mgk_topbar_region]
[/mgk_topbar_links]';
}

function mgk_site_topbar_shortcode() {
    return implode( "\n", [
        '[mgk_topbar_layout bg_color="#171717" text_color="#ffffff" font_size="12" min_height="28" justify="space-between" gap="16"]',
        '[mgk_topbar_phone]',
        '[mgk_topbar_email]',
        '[mgk_topbar_links gap="14"]',
        '[mgk_topbar_tutor_link]',
        '[mgk_topbar_agency_link]',
        '[mgk_topbar_region]',
        '[/mgk_topbar_links]',
        '[/mgk_topbar_layout]',
    ] );
}

function mgk_section_topbar_layout( $atts = [], $content = null ) {
    $atts = shortcode_atts( [
        'bg_color'   => '#171717',
        'text_color' => '#ffffff',
        'font_size'  => '12',
        'min_height' => '28',
        'justify'    => 'space-between',
        'gap'        => '16',
        'padding'    => '',
    ], $atts );

    $content = trim( (string) $content );
    if ( $content === '' ) {
        $content = mgk_topbar_default_layout_content();
    }

    $allowed_justify = [ 'flex-start', 'center', 'flex-end', 'space-between', 'space-around' ];
    $justify = in_array( $atts['justify'], $allowed_justify, true ) ? $atts['justify'] : 'space-between';
    $bg_color = sanitize_hex_color( $atts['bg_color'] ) ?: '#171717';
    $text_color = sanitize_hex_color( $atts['text_color'] ) ?: '#ffffff';

    $bar_styles = [
        '--mgk-topbar-bg:' . $bg_color,
        '--mgk-topbar-color:' . $text_color,
        '--mgk-topbar-font-size:' . max( 8, min( 20, (float) $atts['font_size'] ) ) . 'px',
        '--mgk-topbar-min-height:' . max( 16, min( 64, (float) $atts['min_height'] ) ) . 'px',
        '--mgk-topbar-justify:' . $justify,
        '--mgk-topbar-gap:' . max( 0, min( 48, (float) $atts['gap'] ) ) . 'px',
    ];

    if ( $atts['padding'] !== '' ) {
        $bar_styles[] = '--mgk-topbar-padding:' . esc_attr( $atts['padding'] );
    }

    ob_start();
    ?>
    <div class="mgk-utility" style="<?php echo esc_attr( implode( ';', $bar_styles ) ); ?>">
        <div class="mgk-shell mgk-utility-inner">
            <?php echo do_shortcode( $content ); ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'mgk_topbar_layout', 'mgk_section_topbar_layout' );

function mgk_section_topbar_phone( $atts = [] ) {
    $text = mgk_shortcode_att( $atts, 'text', 'phone' );
    return '<span class="mgk-utility-phone">' . esc_html( $text ) . '</span>';
}
add_shortcode( 'mgk_topbar_phone', 'mgk_section_topbar_phone' );

function mgk_section_topbar_email( $atts = [] ) {
    $text = mgk_shortcode_att( $atts, 'text', 'email' );
    return '<span class="mgk-utility-email">' . esc_html( $text ) . '</span>';
}
add_shortcode( 'mgk_topbar_email', 'mgk_section_topbar_email' );

function mgk_section_topbar_links( $atts = [], $content = null ) {
    $atts = shortcode_atts( [
        'gap' => '14',
    ], $atts );

    $content = trim( (string) $content );
    if ( $content === '' ) {
        $content = '[mgk_topbar_tutor_link][mgk_topbar_agency_link][mgk_topbar_region]';
    }

    $gap = max( 0, min( 48, (float) $atts['gap'] ) );
    return '<span class="mgk-utility-links" style="' . esc_attr( '--mgk-topbar-link-gap:' . $gap . 'px' ) . '">' . do_shortcode( $content ) . '</span>';
}
add_shortcode( 'mgk_topbar_links', 'mgk_section_topbar_links' );

function mgk_section_topbar_tutor_link( $atts = [] ) {
    $label = mgk_shortcode_att( $atts, 'label', 'utility_tutor_label' );
    $url   = mgk_shortcode_att( $atts, 'url', '', mgk_cta_url( 'tutor' ) );
    return '<a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a>';
}
add_shortcode( 'mgk_topbar_tutor_link', 'mgk_section_topbar_tutor_link' );

function mgk_section_topbar_agency_link( $atts = [] ) {
    $label = mgk_shortcode_att( $atts, 'label', 'utility_agency_label' );
    $url   = mgk_shortcode_att( $atts, 'url', '', mgk_cta_url( 'agency' ) );
    return '<a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a>';
}
add_shortcode( 'mgk_topbar_agency_link', 'mgk_section_topbar_agency_link' );

function mgk_section_topbar_region( $atts = [] ) {
    $text = mgk_shortcode_att( $atts, 'text', 'region_label' );
    return '<span class="mgk-utility-region">' . esc_html( $text ) . '</span>';
}
add_shortcode( 'mgk_topbar_region', 'mgk_section_topbar_region' );

/**
 * Render a home section partial to string, passing atts as $args.
 * Uses mgk_render_part() from inc/mgk-content-sections.php.
 *
 * @param string $name Partial basename under template-parts/sections/home/.
 * @param array  $atts Shortcode atts forwarded to the partial.
 * @return string
 */
function mgk_home_part( $name, $atts ) {
    return mgk_render_part( 'template-parts/sections/home/' . $name, is_array( $atts ) ? $atts : [] );
}

function mgk_hero_default_layout_content() {
    return '[mgk_hero_copy]
[mgk_hero_eyebrow]
[mgk_hero_title]
[mgk_hero_lines]
[mgk_hero_search]
[mgk_hero_proof]
[/mgk_hero_copy]
[mgk_hero_media]';
}

function mgk_section_hero_layout( $atts = [], $content = null ) {
    $content = trim( (string) $content );
    if ( $content === '' ) {
        $content = mgk_hero_default_layout_content();
    }

    ob_start();
    ?>
    <section class="mgk-hero">
        <div class="mgk-shell mgk-hero-grid">
            <?php echo do_shortcode( $content ); ?>
        </div>
    </section>
    <?php
    return ob_get_clean();
}
add_shortcode( 'mgk_hero_layout', 'mgk_section_hero_layout' );

function mgk_section_hero_copy( $atts = [], $content = null ) {
    $content = trim( (string) $content );
    if ( $content === '' ) {
        $content = '[mgk_hero_eyebrow]
[mgk_hero_title]
[mgk_hero_lines]
[mgk_hero_search]
[mgk_hero_proof]';
    }

    return '<div>' . do_shortcode( $content ) . '</div>';
}
add_shortcode( 'mgk_hero_copy', 'mgk_section_hero_copy' );

function mgk_section_hero_eyebrow( $atts = [] ) {
    $text = mgk_shortcode_att( $atts, 'text', 'hero_eyebrow' );
    return '<p class="mgk-eyebrow">' . esc_html( $text ) . '</p>';
}
add_shortcode( 'mgk_hero_eyebrow', 'mgk_section_hero_eyebrow' );

function mgk_section_hero_title( $atts = [] ) {
    $before    = mgk_shortcode_att( $atts, 'before', 'hero_title_before' );
    $highlight = mgk_shortcode_att( $atts, 'highlight', 'hero_title_highlight' );
    $after     = mgk_shortcode_att( $atts, 'after', 'hero_title_after' );

    ob_start();
    ?>
    <h1>
        <?php echo esc_html( $before ); ?>
        <span class="mgk-accent-text"><?php echo esc_html( $highlight ); ?></span>
        <?php echo esc_html( $after ); ?>
    </h1>
    <?php
    return ob_get_clean();
}
add_shortcode( 'mgk_hero_title', 'mgk_section_hero_title' );

function mgk_section_hero_lines() {
    return '<div class="mgk-line"></div><div class="mgk-line short"></div>';
}
add_shortcode( 'mgk_hero_lines', 'mgk_section_hero_lines' );

function mgk_section_hero_search( $atts = [] ) {
    $search_button      = mgk_shortcode_att( $atts, 'button', 'hero_search_button' );
    $subject_label      = mgk_shortcode_att( $atts, 'subject_label', '', 'Subject' );
    $subject_placeholder = mgk_shortcode_att( $atts, 'subject_placeholder', '', 'English, Math...' );
    $level_label        = mgk_shortcode_att( $atts, 'level_label', '', 'Level' );
    $level_placeholder  = mgk_shortcode_att( $atts, 'level_placeholder', '', 'P1-JC2' );
    $area_label         = mgk_shortcode_att( $atts, 'area_label', '', 'Area / Online' );
    $area_placeholder   = mgk_shortcode_att( $atts, 'area_placeholder', '', 'Or online' );
    $budget_label       = mgk_shortcode_att( $atts, 'budget_label', '', 'Budget' );
    $budget_placeholder = mgk_shortcode_att( $atts, 'budget_placeholder', '', '$30-$150/hr' );

    ob_start();
    ?>
    <form class="mgk-search-panel" action="<?php echo esc_url( mgk_get_tutor_listing_url() ); ?>" method="get" data-mgk-search-form data-mgk-event="hero_search_submitted">
        <div class="mgk-search-grid">
            <label><span><?php echo esc_html( $subject_label ); ?></span><select name="subject"><option value=""><?php echo esc_html( $subject_placeholder ); ?></option><option>Math</option><option>English</option><option>Chinese</option><option>Science</option></select></label>
            <label><span><?php echo esc_html( $level_label ); ?></span><select name="level"><option value=""><?php echo esc_html( $level_placeholder ); ?></option><option>Primary</option><option>Secondary</option><option>JC / IB</option></select></label>
            <label><span><?php echo esc_html( $area_label ); ?></span><select name="area"><option value=""><?php echo esc_html( $area_placeholder ); ?></option><option>Central SG</option><option>Online</option><option>East</option><option>West</option></select></label>
            <label><span><?php echo esc_html( $budget_label ); ?></span><select name="budget"><option value=""><?php echo esc_html( $budget_placeholder ); ?></option><option>$30-$50/hr</option><option>$50-$80/hr</option><option>$80-$150/hr</option></select></label>
        </div>
        <button class="mgk-btn mgk-btn-accent" type="submit"><?php echo esc_html( $search_button ); ?></button>
        <p class="mgk-form-message" data-mgk-form-message></p>
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode( 'mgk_hero_search', 'mgk_section_hero_search' );

function mgk_section_hero_proof( $atts = [] ) {
    $text = mgk_shortcode_att( $atts, 'text', 'hero_proof' );
    return '<p class="mgk-hero-proof">' . esc_html( $text ) . '</p>';
}
add_shortcode( 'mgk_hero_proof', 'mgk_section_hero_proof' );

function mgk_section_hero_media( $atts = [] ) {
    $image_id = ( is_array( $atts ) && array_key_exists( 'image_id', $atts ) && $atts['image_id'] !== '' )
        ? (int) $atts['image_id']
        : (int) mgk_site_setting( 'hero_media_image_id' );
    $label  = mgk_shortcode_att( $atts, 'label', 'hero_media_label' );
    $button = mgk_shortcode_att( $atts, 'button', 'hero_media_button' );
    $style  = '';

    if ( $image_id ) {
        $url = wp_get_attachment_image_url( $image_id, 'large' );
        if ( $url ) {
            $style = ' style="background-image:url(' . esc_url( $url ) . ')"';
        }
    }

    ob_start();
    ?>
    <div class="mgk-placeholder mgk-hero-media"<?php echo $style; ?>>
        <?php if ( ! $image_id && $label !== '' ) : ?>
            <?php echo esc_html( $label ); ?>
        <?php endif; ?>
        <?php if ( $button !== '' ) : ?>
            <span class="mgk-video-chip"><?php echo esc_html( $button ); ?></span>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'mgk_hero_media', 'mgk_section_hero_media' );

function mgk_section_hero( $atts = [] )            { return mgk_home_part( 'hero', $atts ); }
add_shortcode( 'mgk_hero', 'mgk_section_hero' );

function mgk_section_trust_stats( $atts = [] )     { return mgk_home_part( 'trust-stats', $atts ); }
add_shortcode( 'mgk_trust_stats', 'mgk_section_trust_stats' );

function mgk_section_live_feed( $atts = [] )       { return mgk_home_part( 'live-feed', $atts ); }
add_shortcode( 'mgk_live_feed', 'mgk_section_live_feed' );

function mgk_section_steps( $atts = [] )           { return mgk_home_part( 'steps', $atts ); }
add_shortcode( 'mgk_steps', 'mgk_section_steps' );

function mgk_section_subjects( $atts = [] )        { return mgk_home_part( 'subjects', $atts ); }
add_shortcode( 'mgk_subjects', 'mgk_section_subjects' );

function mgk_section_featured_tutors( $atts = [] ) { return mgk_home_part( 'featured-tutors', $atts ); }
add_shortcode( 'mgk_featured_tutors', 'mgk_section_featured_tutors' );

function mgk_section_why( $atts = [] )             { return mgk_home_part( 'why', $atts ); }
add_shortcode( 'mgk_why', 'mgk_section_why' );

function mgk_section_spotlight( $atts = [] )       { return mgk_home_part( 'spotlight', $atts ); }
add_shortcode( 'mgk_spotlight', 'mgk_section_spotlight' );

function mgk_section_results( $atts = [] )         { return mgk_home_part( 'results', $atts ); }
add_shortcode( 'mgk_results', 'mgk_section_results' );

function mgk_section_reviews( $atts = [] )         { return mgk_home_part( 'reviews', $atts ); }
add_shortcode( 'mgk_reviews', 'mgk_section_reviews' );

function mgk_section_faq( $atts = [] )             { return mgk_home_part( 'faq', $atts ); }
add_shortcode( 'mgk_faq', 'mgk_section_faq' );

function mgk_section_pricing_teaser( $atts = [] )  { return mgk_home_part( 'pricing-teaser', $atts ); }
add_shortcode( 'mgk_pricing_teaser', 'mgk_section_pricing_teaser' );

function mgk_section_press( $atts = [] )           { return mgk_home_part( 'press', $atts ); }
add_shortcode( 'mgk_press', 'mgk_section_press' );

function mgk_section_final_cta( $atts = [] )       { return mgk_home_part( 'final-cta', $atts ); }
add_shortcode( 'mgk_final_cta', 'mgk_section_final_cta' );

function mgk_section_newsletter( $atts = [] )      { return mgk_home_part( 'newsletter', $atts ); }
add_shortcode( 'mgk_newsletter', 'mgk_section_newsletter' );
