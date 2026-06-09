<?php
/**
 * Editable commercial site settings for MGK templates.
 *
 * This file intentionally uses native WordPress theme mods instead of page
 * PHP edits, so a site owner can change brand, copy, images, colors, and CTAs
 * from WP Admin or the Customizer.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function mgk_site_setting_defaults() {
    return [
        'logo_image_id'        => '',
        'logo_text'            => '[LOGO]',
        'phone'                => '+65 1234 5678',
        'email'                => 'help@example.sg',
        'region_label'         => 'SG / EN',
        'utility_tutor_label'  => 'For Tutors',
        'utility_agency_label' => 'For Agency',
        'header_signin_label'  => 'Sign In',
        'header_primary_label' => 'Find Tutor',
        'accent_color'         => '#D63B1F',
        'dark_color'           => '#1A1A1A',
        'muted_color'          => '#707070',
        'show_live_feed'       => '1',
        'show_steps'           => '1',
        'show_subjects'        => '1',
        'show_tutors'          => '1',
        'show_why'             => '1',
        'show_spotlight'       => '1',
        'show_results'         => '1',
        'show_reviews'         => '1',
        'show_faq'             => '1',
        'show_pricing'         => '1',
        'show_press'           => '1',
        'show_final_cta'       => '1',
        'show_newsletter'      => '1',

        'hero_eyebrow'         => "Singapore's #1 Home Tuition Platform",
        'hero_title_before'    => 'Find your perfect tutor in',
        'hero_title_highlight' => '6 hours',
        'hero_title_after'     => '',
        'hero_search_button'   => 'Search 50,000+ Tutors ->',
        'hero_proof'           => '***** 4.8/5 - 12,400+ successful matches - MOE-registered partner agencies',
        'hero_media_image_id'  => '',
        'hero_media_label'     => 'Hero video / illustration',
        'hero_media_button'    => 'Watch how it works (1:30)',

        'stat_1_value' => '50,000+',
        'stat_1_label' => 'Verified Tutors',
        'stat_2_value' => '6 hours',
        'stat_2_label' => 'Avg Match Time',
        'stat_3_value' => '12,400+',
        'stat_3_label' => 'Match Success',
        'stat_4_value' => '*4.8/5',
        'stat_4_label' => 'Parent Rating',

        'live_1' => 'Mrs Tan just matched with Ms Lee (P5 Math) - 2 min ago',
        'live_2' => 'Mr Lim booked Mr Wong (Sec 3 Chem) - 8 min ago',
        'live_3' => 'New 5-star review for Ms Goh - 15 min ago',

        'steps_heading' => 'Find a tutor in 4 simple steps',
        'steps_body'    => 'A fast path from search to a trial lesson with the right tutor.',
        'step_1_title'  => 'Tell us your need',
        'step_1_body'   => 'Level, subject, schedule, budget',
        'step_2_title'  => 'Get matched in 6h',
        'step_2_body'   => 'AI picks 3-5 best fits, you decide',
        'step_3_title'  => 'Trial lesson 40% off',
        'step_3_body'   => 'Try before committing to package',
        'step_4_title'  => 'Continue with package',
        'step_4_body'   => '8 or 16 lessons, save up to 10%',

        'subjects_heading' => 'Browse by Subject',
        'tutors_heading'   => 'Top Tutors This Week',
        'tutors_body'      => 'Verified - Active - Available now',
        'tutor_filters'    => 'All, PSLE Specialist, Ex-MOE, JC Subject, Online, Female, Bilingual',

        'why_heading' => 'Why parents choose us',
        'why_body'    => 'Designed for speed, trust, and transparent decisions.',
        'why_1_title' => 'Every tutor verified',
        'why_1_body'  => 'NRIC, degree, background, teaching proof, and parent reviews.',
        'why_2_title' => 'Match in 6 hours',
        'why_2_body'  => 'Industry-fast shortlist without waiting days for a coordinator.',
        'why_3_title' => 'Track progress weekly',
        'why_3_body'  => 'Lesson logs and weekly digest emails show real improvement.',
        'why_4_title' => 'Transparent pricing',
        'why_4_body'  => 'No hidden fees. Cancel anytime. Clear hourly ranges.',
        'why_5_title' => 'Free replacement',
        'why_5_body'  => 'Not happy after trial? We match a new tutor for free.',
        'why_6_title' => 'In-app messaging',
        'why_6_body'  => 'Direct chat with tutor for homework help between lessons.',

        'spotlight_image_id' => '',
        'spotlight_label'    => 'Profile photo + video',
        'spotlight_eyebrow'  => 'Tutor of the Month',
        'spotlight_name'     => 'Ms. Lee Yi Ling',
        'spotlight_meta'     => 'Verified Ex-MOE - 12 years - NIE certified',
        'spotlight_stat_1'   => '200+|Students taught',
        'spotlight_stat_2'   => '87%|PSLE A/A*',
        'spotlight_stat_3'   => '*5.0|Rating',
        'spotlight_profile_label' => 'View Profile',
        'spotlight_trial_label'   => 'Book Trial $40',

        'results_heading' => 'Real results from real parents',
        'reviews_heading' => '12,400+ Parent Reviews',
        'reviews_body'    => '* 4.8 average from verified parents',
        'faq_heading'     => 'Frequently asked questions',

        'pricing_heading' => 'Transparent pricing',
        'pricing_body'    => 'Clear hourly ranges by level and tutor tier before you book.',
        'pricing_lines'   => "Primary: \$30-\$90/hr\nSecondary: \$35-\$95/hr\nJC / IB: \$50-\$150/hr\nSave up to 10% with package",
        'pricing_cta'     => 'See full pricing ->',
        'calculator_title' => 'Try our pricing calculator',
        'calculator_rows'  => "Level: P5\nSubject: Math\nFrequency: 1x/week",
        'calculator_result'=> '$220-$440/month',
        'calculator_note'  => 'Range based on tutor tier',

        'press_label' => 'As featured in',
        'press_names' => 'ST, CNA, Today, SCMP, Yahoo, Mothership',

        'final_cta_heading'   => 'Ready to find your tutor?',
        'final_cta_body'      => 'No upfront fee - Match in 6 hours - Cancel anytime',
        'final_cta_primary'   => 'Find Tutor Now ->',
        'final_cta_secondary' => 'Browse Tutors',
        'mobile_sticky_label' => 'Find a Tutor in 6h ->',

        'newsletter_heading' => 'Free Singapore Parent Guide',
        'newsletter_body'    => 'PSLE prep checklist - MOE updates - Tutor finding tips',
        'newsletter_button'  => 'Get Free Guide',

        'footer_intro'       => "Singapore's leading 1-1 tuition platform.",
        'footer_registration'=> 'MOE Reg #12345',
        'footer_copyright'   => 'Margick K-12 Tutoring',
        'footer_regions'     => 'SG / MY / ID / HK',

        // ── Payments (booking engine) ──
        // Method on/off are intent flags; the booking engine ALSO gates on
        // whether each method is actually configured (see mgk_payment_config()).
        'pay_paynow_enabled'   => '1',
        'paynow_uen'           => '',
        'paynow_payee'         => '',
        'pay_stripe_enabled'   => '0',
        'stripe_publishable'   => '',
        'stripe_secret'        => '',
        'stripe_webhook_secret'=> '',
    ];
}

function mgk_site_setting( $key, $default = null ) {
    $defaults = mgk_site_setting_defaults();
    $fallback = $default !== null ? $default : ( $defaults[ $key ] ?? '' );
    $value = get_theme_mod( 'mgk_' . $key, $fallback );
    return $value === '' && $fallback !== '' ? $fallback : $value;
}

function mgk_site_lines( $key ) {
    $raw = (string) mgk_site_setting( $key );
    $lines = preg_split( '/\r\n|\r|\n/', $raw );
    return array_values( array_filter( array_map( 'trim', $lines ), function ( $line ) {
        return $line !== '';
    } ) );
}

function mgk_site_csv( $key ) {
    $items = explode( ',', (string) mgk_site_setting( $key ) );
    return array_values( array_filter( array_map( 'trim', $items ), function ( $item ) {
        return $item !== '';
    } ) );
}

function mgk_site_enabled( $key ) {
    return mgk_site_setting( $key, '1' ) === '1';
}

/**
 * Resolve the effective logo attachment ID.
 *
 * Priority: the WP Customizer custom logo (Site Identity) wins so the owner can
 * use the native WP flow; if unset we fall back to the MGK Site Settings
 * `logo_image_id` for backward compatibility. Returns 0 when neither is set
 * (caller then renders the text label).
 */
function mgk_resolve_logo_id() {
    $custom = (int) get_theme_mod( 'custom_logo' );
    if ( $custom ) {
        return $custom;
    }
    return (int) mgk_site_setting( 'logo_image_id' );
}

/**
 * Keep the Customizer custom_logo and the MGK Site Settings logo_image_id in sync,
 * so changing the logo in either place updates the other. Fires whenever either
 * source is saved (see hooks at the bottom of this file). Guarded against recursion.
 */
function mgk_sync_logo_sources( $source = 'customizer' ) {
    static $running = false;
    if ( $running ) {
        return;
    }
    $running = true;

    if ( $source === 'customizer' ) {
        // Customizer changed → push into MGK Site Settings.
        $custom = (int) get_theme_mod( 'custom_logo' );
        $settings = get_option( 'mgk_site_settings', [] );
        if ( ! is_array( $settings ) ) {
            $settings = [];
        }
        if ( (int) ( $settings['logo_image_id'] ?? 0 ) !== $custom ) {
            $settings['logo_image_id'] = $custom ? (string) $custom : '';
            update_option( 'mgk_site_settings', $settings );
        }
    } else {
        // MGK Site Settings changed → push into the Customizer custom logo.
        $logo_id = (int) mgk_site_setting( 'logo_image_id' );
        if ( (int) get_theme_mod( 'custom_logo' ) !== $logo_id ) {
            if ( $logo_id ) {
                set_theme_mod( 'custom_logo', $logo_id );
            } else {
                remove_theme_mod( 'custom_logo' );
            }
        }
    }

    $running = false;
}
add_action( 'customize_save_after', function () { mgk_sync_logo_sources( 'customizer' ); } );
add_action( 'update_option_mgk_site_settings', function () { mgk_sync_logo_sources( 'settings' ); } );

function mgk_site_logo_html( $class = 'mgk-logo' ) {
    $logo_id = mgk_resolve_logo_id();
    $label = (string) mgk_site_setting( 'logo_text' );
    $inner = esc_html( $label );

    if ( $logo_id ) {
        $image = wp_get_attachment_image( $logo_id, 'medium', false, [ 'class' => $class . '__image' ] );
        if ( $image ) {
            $inner = $image . '<span class="screen-reader-text">' . esc_html( get_bloginfo( 'name' ) ) . '</span>';
        }
    }

    return sprintf(
        '<a class="%1$s" href="%2$s" aria-label="%3$s">%4$s</a>',
        esc_attr( $class ),
        esc_url( home_url( '/' ) ),
        esc_attr( get_bloginfo( 'name' ) ),
        $inner
    );
}

function mgk_site_image_style( $key ) {
    $image_id = (int) mgk_site_setting( $key );
    if ( ! $image_id ) {
        return '';
    }
    $url = wp_get_attachment_image_url( $image_id, 'large' );
    return $url ? ' style="background-image:url(' . esc_url( $url ) . ')"' : '';
}

function mgk_site_nav_fallback() {
    // Only pages that actually exist. Shown when no menu is assigned to the
    // mgk_primary location; the owner can override via Appearance > Menus.
    $links = [
        [ 'Browse Tutors', mgk_cta_url( 'browse' ) ],
        [ 'Subjects', mgk_url( '/subjects/' ) ],
        [ 'How It Works', mgk_url( '/how-it-works/' ) ],
        [ 'Pricing', mgk_cta_url( 'pricing' ) ],
    ];
    foreach ( $links as $link ) {
        echo '<a href="' . esc_url( $link[1] ) . '">' . esc_html( $link[0] ) . '</a>';
    }
}

function mgk_site_render_menu( $location, $class, $fallback = 'mgk_site_nav_fallback' ) {
    echo '<div class="' . esc_attr( $class ) . '">';
    if ( has_nav_menu( $location ) ) {
        $locations = get_nav_menu_locations();
        $menu = isset( $locations[ $location ] ) ? wp_get_nav_menu_object( $locations[ $location ] ) : null;
        $items = $menu ? wp_get_nav_menu_items( $menu->term_id ) : [];
        foreach ( (array) $items as $item ) {
            if ( (int) $item->menu_item_parent !== 0 ) {
                continue;
            }
            echo '<a href="' . esc_url( $item->url ) . '">' . esc_html( $item->title ) . '</a>';
        }
    } elseif ( is_callable( $fallback ) ) {
        call_user_func( $fallback );
    }
    echo '</div>';
}

function mgk_site_home_steps() {
    $steps = [];
    for ( $i = 1; $i <= 4; $i++ ) {
        $steps[] = [
            mgk_site_setting( "step_{$i}_title" ),
            mgk_site_setting( "step_{$i}_body" ),
        ];
    }
    return $steps;
}

function mgk_site_home_stats() {
    $stats = [];
    for ( $i = 1; $i <= 4; $i++ ) {
        $stats[] = [
            'value' => mgk_site_setting( "stat_{$i}_value" ),
            'label' => mgk_site_setting( "stat_{$i}_label" ),
        ];
    }
    return $stats;
}

function mgk_site_home_why_items() {
    $items = [];
    for ( $i = 1; $i <= 6; $i++ ) {
        $items[] = [
            mgk_site_setting( "why_{$i}_title" ),
            mgk_site_setting( "why_{$i}_body" ),
        ];
    }
    return $items;
}

function mgk_site_split_stat( $value ) {
    $parts = array_map( 'trim', explode( '|', (string) $value, 2 ) );
    return [ $parts[0] ?? '', $parts[1] ?? '' ];
}

add_action( 'wp_head', function () {
    $accent = sanitize_hex_color( mgk_site_setting( 'accent_color' ) );
    $dark = sanitize_hex_color( mgk_site_setting( 'dark_color' ) );
    $muted = sanitize_hex_color( mgk_site_setting( 'muted_color' ) );
    ?>
    <style id="mgk-site-settings-css">
        :root {
            <?php if ( $accent ) : ?>--mgk-accent: <?php echo esc_html( $accent ); ?>;<?php endif; ?>
            <?php if ( $dark ) : ?>--mgk-dark: <?php echo esc_html( $dark ); ?>; --mgk-text: <?php echo esc_html( $dark ); ?>;<?php endif; ?>
            <?php if ( $muted ) : ?>--mgk-muted: <?php echo esc_html( $muted ); ?>;<?php endif; ?>
        }
    </style>
    <?php
}, 20 );

function mgk_site_settings_groups() {
    return [
        'Brand and Header' => [
            'logo_image_id' => [ 'Logo image', 'image' ],
            'logo_text' => [ 'Logo text fallback', 'text' ],
            'phone' => [ 'Phone', 'text' ],
            'email' => [ 'Email', 'text' ],
            'region_label' => [ 'Region/language label', 'text' ],
            'utility_tutor_label' => [ 'Tutor link label', 'text' ],
            'utility_agency_label' => [ 'Agency link label', 'text' ],
            'header_signin_label' => [ 'Sign in button', 'text' ],
            'header_primary_label' => [ 'Primary header CTA', 'text' ],
            'accent_color' => [ 'Accent color', 'color' ],
            'dark_color' => [ 'Text/dark color', 'color' ],
            'muted_color' => [ 'Muted text color', 'color' ],
        ],
        'Homepage Layout' => [
            'show_live_feed' => [ 'Show live feed', 'checkbox' ],
            'show_steps' => [ 'Show steps section', 'checkbox' ],
            'show_subjects' => [ 'Show subjects section', 'checkbox' ],
            'show_tutors' => [ 'Show tutors section', 'checkbox' ],
            'show_why' => [ 'Show why section', 'checkbox' ],
            'show_spotlight' => [ 'Show spotlight section', 'checkbox' ],
            'show_results' => [ 'Show results section', 'checkbox' ],
            'show_reviews' => [ 'Show reviews section', 'checkbox' ],
            'show_faq' => [ 'Show FAQ section', 'checkbox' ],
            'show_pricing' => [ 'Show pricing section', 'checkbox' ],
            'show_press' => [ 'Show press section', 'checkbox' ],
            'show_final_cta' => [ 'Show final CTA section', 'checkbox' ],
            'show_newsletter' => [ 'Show newsletter section', 'checkbox' ],
        ],
        'Homepage Hero' => [
            'hero_eyebrow' => [ 'Eyebrow', 'text' ],
            'hero_title_before' => [ 'Title before highlight', 'text' ],
            'hero_title_highlight' => [ 'Title highlight', 'text' ],
            'hero_title_after' => [ 'Title after highlight', 'text' ],
            'hero_search_button' => [ 'Search button text', 'text' ],
            'hero_proof' => [ 'Hero proof line', 'text' ],
            'hero_media_image_id' => [ 'Hero image/video thumbnail', 'image' ],
            'hero_media_label' => [ 'Hero media fallback label', 'text' ],
            'hero_media_button' => [ 'Hero media button', 'text' ],
        ],
        'Homepage Sections' => [
            'steps_heading' => [ 'Steps heading', 'text' ],
            'steps_body' => [ 'Steps body', 'text' ],
            'subjects_heading' => [ 'Subjects heading', 'text' ],
            'tutors_heading' => [ 'Tutors heading', 'text' ],
            'tutors_body' => [ 'Tutors body', 'text' ],
            'tutor_filters' => [ 'Tutor filters, comma-separated', 'textarea' ],
            'why_heading' => [ 'Why heading', 'text' ],
            'why_body' => [ 'Why body', 'text' ],
            'results_heading' => [ 'Results heading', 'text' ],
            'reviews_heading' => [ 'Reviews heading', 'text' ],
            'reviews_body' => [ 'Reviews body', 'text' ],
            'faq_heading' => [ 'FAQ heading', 'text' ],
            'press_label' => [ 'Press label', 'text' ],
            'press_names' => [ 'Press names, comma-separated', 'textarea' ],
        ],
        'Steps and Trust Cards' => [
            'stat_1_value' => [ 'Stat 1 value', 'text' ],
            'stat_1_label' => [ 'Stat 1 label', 'text' ],
            'stat_2_value' => [ 'Stat 2 value', 'text' ],
            'stat_2_label' => [ 'Stat 2 label', 'text' ],
            'stat_3_value' => [ 'Stat 3 value', 'text' ],
            'stat_3_label' => [ 'Stat 3 label', 'text' ],
            'stat_4_value' => [ 'Stat 4 value', 'text' ],
            'stat_4_label' => [ 'Stat 4 label', 'text' ],
            'live_1' => [ 'Live feed item 1', 'text' ],
            'live_2' => [ 'Live feed item 2', 'text' ],
            'live_3' => [ 'Live feed item 3', 'text' ],
            'step_1_title' => [ 'Step 1 title', 'text' ],
            'step_1_body' => [ 'Step 1 body', 'text' ],
            'step_2_title' => [ 'Step 2 title', 'text' ],
            'step_2_body' => [ 'Step 2 body', 'text' ],
            'step_3_title' => [ 'Step 3 title', 'text' ],
            'step_3_body' => [ 'Step 3 body', 'text' ],
            'step_4_title' => [ 'Step 4 title', 'text' ],
            'step_4_body' => [ 'Step 4 body', 'text' ],
        ],
        'Why Cards' => [
            'why_1_title' => [ 'Why card 1 title', 'text' ],
            'why_1_body' => [ 'Why card 1 body', 'textarea' ],
            'why_2_title' => [ 'Why card 2 title', 'text' ],
            'why_2_body' => [ 'Why card 2 body', 'textarea' ],
            'why_3_title' => [ 'Why card 3 title', 'text' ],
            'why_3_body' => [ 'Why card 3 body', 'textarea' ],
            'why_4_title' => [ 'Why card 4 title', 'text' ],
            'why_4_body' => [ 'Why card 4 body', 'textarea' ],
            'why_5_title' => [ 'Why card 5 title', 'text' ],
            'why_5_body' => [ 'Why card 5 body', 'textarea' ],
            'why_6_title' => [ 'Why card 6 title', 'text' ],
            'why_6_body' => [ 'Why card 6 body', 'textarea' ],
        ],
        'Spotlight and Pricing' => [
            'spotlight_image_id' => [ 'Spotlight image/video', 'image' ],
            'spotlight_label' => [ 'Spotlight fallback label', 'text' ],
            'spotlight_eyebrow' => [ 'Spotlight eyebrow', 'text' ],
            'spotlight_name' => [ 'Spotlight name', 'text' ],
            'spotlight_meta' => [ 'Spotlight meta', 'text' ],
            'spotlight_stat_1' => [ 'Spotlight stat 1, value|label', 'text' ],
            'spotlight_stat_2' => [ 'Spotlight stat 2, value|label', 'text' ],
            'spotlight_stat_3' => [ 'Spotlight stat 3, value|label', 'text' ],
            'spotlight_profile_label' => [ 'Profile CTA label', 'text' ],
            'spotlight_trial_label' => [ 'Trial CTA label', 'text' ],
            'pricing_heading' => [ 'Pricing heading', 'text' ],
            'pricing_body' => [ 'Pricing body', 'text' ],
            'pricing_lines' => [ 'Pricing bullets, one per line', 'textarea' ],
            'pricing_cta' => [ 'Pricing CTA label', 'text' ],
            'calculator_title' => [ 'Calculator title', 'text' ],
            'calculator_rows' => [ 'Calculator rows, one per line', 'textarea' ],
            'calculator_result' => [ 'Calculator result', 'text' ],
            'calculator_note' => [ 'Calculator note', 'text' ],
        ],
        'Payments (Booking)' => [
            'pay_paynow_enabled'    => [ 'Enable PayNow QR', 'checkbox' ],
            'paynow_uen'            => [ 'PayNow UEN (company)', 'text' ],
            'paynow_payee'          => [ 'PayNow payee/merchant name', 'text' ],
            'pay_stripe_enabled'    => [ 'Enable Card (Stripe)', 'checkbox' ],
            'stripe_publishable'    => [ 'Stripe publishable key (pk_…)', 'text' ],
            'stripe_secret'         => [ 'Stripe secret key (sk_…)', 'secret' ],
            'stripe_webhook_secret' => [ 'Stripe webhook secret (whsec_…)', 'secret' ],
        ],
        'Final CTA, Newsletter, Footer' => [
            'final_cta_heading' => [ 'Final CTA heading', 'text' ],
            'final_cta_body' => [ 'Final CTA body', 'text' ],
            'final_cta_primary' => [ 'Final CTA primary button', 'text' ],
            'final_cta_secondary' => [ 'Final CTA secondary button', 'text' ],
            'mobile_sticky_label' => [ 'Mobile sticky CTA label', 'text' ],
            'newsletter_heading' => [ 'Newsletter heading', 'text' ],
            'newsletter_body' => [ 'Newsletter body', 'text' ],
            'newsletter_button' => [ 'Newsletter button', 'text' ],
            'footer_intro' => [ 'Footer intro', 'textarea' ],
            'footer_registration' => [ 'Footer registration line', 'text' ],
            'footer_copyright' => [ 'Footer copyright name', 'text' ],
            'footer_regions' => [ 'Footer regions', 'text' ],
        ],
    ];
}

add_action( 'admin_menu', function () {
    add_menu_page(
        'MGK Site Settings',
        'MGK Site',
        'manage_options',
        'mgk-site-settings',
        'mgk_site_settings_page',
        'dashicons-admin-customizer',
        31
    );
} );

add_action( 'admin_enqueue_scripts', function ( $hook ) {
    if ( $hook !== 'toplevel_page_mgk-site-settings' ) {
        return;
    }
    wp_enqueue_media();
    wp_add_inline_script( 'jquery-core', "
        jQuery(function($){
            $('.mgk-media-button').on('click', function(e){
                e.preventDefault();
                var field = $('#' + $(this).data('target'));
                var preview = $('#' + $(this).data('preview'));
                var frame = wp.media({ title: 'Choose image', button: { text: 'Use image' }, multiple: false });
                frame.on('select', function(){
                    var attachment = frame.state().get('selection').first().toJSON();
                    field.val(attachment.id);
                    preview.html('<img src=\"' + attachment.sizes.thumbnail.url + '\" alt=\"\" style=\"max-width:120px;height:auto;display:block;\">');
                });
                frame.open();
            });
        });
    " );
} );

function mgk_site_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) return;
    ?>
    <div class="wrap">
        <h1>MGK Site Settings</h1>
        <p>Use this page to edit the commercial-facing website without touching PHP templates.</p>
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <input type="hidden" name="action" value="mgk_save_site_settings">
            <?php wp_nonce_field( 'mgk_save_site_settings' ); ?>
            <?php foreach ( mgk_site_settings_groups() as $group => $fields ) : ?>
                <h2><?php echo esc_html( $group ); ?></h2>
                <table class="form-table" role="presentation">
                    <tbody>
                    <?php foreach ( $fields as $key => $field ) : ?>
                        <?php
                        [ $label, $type ] = $field;
                        $input_id = 'mgk_' . esc_attr( $key );
                        $value = mgk_site_setting( $key );
                        ?>
                        <tr>
                            <th scope="row"><label for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_html( $label ); ?></label></th>
                            <td>
                                <?php if ( $type === 'textarea' ) : ?>
                                    <textarea class="large-text" rows="4" id="<?php echo esc_attr( $input_id ); ?>" name="mgk_settings[<?php echo esc_attr( $key ); ?>]"><?php echo esc_textarea( $value ); ?></textarea>
                                <?php elseif ( $type === 'checkbox' ) : ?>
                                    <label>
                                        <input type="hidden" name="mgk_settings[<?php echo esc_attr( $key ); ?>]" value="0">
                                        <input type="checkbox" id="<?php echo esc_attr( $input_id ); ?>" name="mgk_settings[<?php echo esc_attr( $key ); ?>]" value="1" <?php checked( $value, '1' ); ?>>
                                        Enabled
                                    </label>
                                <?php elseif ( $type === 'color' ) : ?>
                                    <input type="color" id="<?php echo esc_attr( $input_id ); ?>" name="mgk_settings[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $value ); ?>">
                                <?php elseif ( $type === 'secret' ) : ?>
                                    <?php $has = $value !== ''; ?>
                                    <input type="password" class="regular-text" id="<?php echo esc_attr( $input_id ); ?>" name="mgk_settings[<?php echo esc_attr( $key ); ?>]" value="" autocomplete="new-password" placeholder="<?php echo $has ? '•••••••• (saved — leave blank to keep)' : 'Not set'; ?>">
                                    <?php if ( $has ) : ?><p class="description">A key is saved. Leave blank to keep it; type a new value to replace.</p><?php endif; ?>
                                <?php elseif ( $type === 'image' ) : ?>
                                    <?php $preview_id = $input_id . '_preview'; ?>
                                    <input type="number" id="<?php echo esc_attr( $input_id ); ?>" name="mgk_settings[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $value ); ?>" min="0">
                                    <button class="button mgk-media-button" data-target="<?php echo esc_attr( $input_id ); ?>" data-preview="<?php echo esc_attr( $preview_id ); ?>">Choose image</button>
                                    <div id="<?php echo esc_attr( $preview_id ); ?>" style="margin-top:8px;">
                                        <?php if ( (int) $value ) echo wp_get_attachment_image( (int) $value, 'thumbnail' ); ?>
                                    </div>
                                <?php else : ?>
                                    <input type="text" class="regular-text" id="<?php echo esc_attr( $input_id ); ?>" name="mgk_settings[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $value ); ?>">
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endforeach; ?>
            <?php submit_button( 'Save MGK Site Settings' ); ?>
        </form>
    </div>
    <?php
}

add_action( 'admin_post_mgk_save_site_settings', function () {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Permission denied.' );
    }
    check_admin_referer( 'mgk_save_site_settings' );

    $posted = isset( $_POST['mgk_settings'] ) && is_array( $_POST['mgk_settings'] )
        ? wp_unslash( $_POST['mgk_settings'] )
        : [];

    foreach ( mgk_site_setting_defaults() as $key => $default ) {
        if ( ! array_key_exists( $key, $posted ) ) {
            continue;
        }
        $raw = $posted[ $key ];
        // Secret keys: blank submit = keep the saved value (don't wipe by accident).
        if ( in_array( $key, [ 'stripe_secret', 'stripe_webhook_secret' ], true ) ) {
            $raw = trim( (string) $raw );
            if ( $raw === '' ) {
                continue; // leave the stored value untouched
            }
            $value = sanitize_text_field( $raw );
            set_theme_mod( 'mgk_' . $key, $value );
            continue;
        }
        if ( $key === 'paynow_uen' ) {
            $value = strtoupper( sanitize_text_field( $raw ) );
            set_theme_mod( 'mgk_' . $key, $value );
            continue;
        }
        if ( substr( $key, -9 ) === '_image_id' || $key === 'logo_image_id' ) {
            $value = (string) max( 0, (int) $raw );
        } elseif ( strpos( $key, 'show_' ) === 0 || strpos( $key, 'pay_' ) === 0 ) {
            $value = $raw === '1' ? '1' : '0';
        } elseif ( strpos( $key, 'color' ) !== false ) {
            $value = sanitize_hex_color( $raw ) ?: $default;
        } elseif ( strpos( $key, 'lines' ) !== false || strpos( $key, 'rows' ) !== false || strpos( $key, 'body' ) !== false || strpos( $key, 'intro' ) !== false || strpos( $key, 'filters' ) !== false || strpos( $key, 'names' ) !== false ) {
            $value = sanitize_textarea_field( $raw );
        } else {
            $value = sanitize_text_field( $raw );
        }
        set_theme_mod( 'mgk_' . $key, $value );
    }

    wp_safe_redirect( add_query_arg( 'updated', 'true', admin_url( 'admin.php?page=mgk-site-settings' ) ) );
    exit;
} );

add_action( 'customize_register', function ( $wp_customize ) {
    $wp_customize->add_section( 'mgk_site_settings', [
        'title'    => 'MGK Site Settings',
        'priority' => 30,
    ] );

    $controls = [
        'logo_image_id' => [ 'Logo image', 'image' ],
        'logo_text' => [ 'Logo text fallback', 'text' ],
        'phone' => [ 'Phone', 'text' ],
        'email' => [ 'Email', 'text' ],
        'accent_color' => [ 'Accent color', 'color' ],
        'hero_eyebrow' => [ 'Hero eyebrow', 'text' ],
        'hero_title_before' => [ 'Hero title before highlight', 'text' ],
        'hero_title_highlight' => [ 'Hero highlight', 'text' ],
        'hero_title_after' => [ 'Hero title after highlight', 'text' ],
        'hero_media_image_id' => [ 'Hero image', 'image' ],
        'footer_intro' => [ 'Footer intro', 'textarea' ],
    ];

    foreach ( $controls as $key => $control ) {
        [ $label, $type ] = $control;
        $setting = 'mgk_' . $key;
        $wp_customize->add_setting( $setting, [
            'default' => mgk_site_setting_defaults()[ $key ] ?? '',
            'sanitize_callback' => $type === 'image' ? 'absint' : ( $type === 'color' ? 'sanitize_hex_color' : 'sanitize_text_field' ),
        ] );

        if ( $type === 'image' ) {
            $wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, $setting, [
                'label' => $label,
                'section' => 'mgk_site_settings',
                'mime_type' => 'image',
            ] ) );
        } elseif ( $type === 'color' ) {
            $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $setting, [
                'label' => $label,
                'section' => 'mgk_site_settings',
            ] ) );
        } else {
            $wp_customize->add_control( $setting, [
                'label' => $label,
                'section' => 'mgk_site_settings',
                'type' => $type,
            ] );
        }
    }
} );

function mgk_page_field( $key, $default = '', $post_id = null ) {
    $post_id = $post_id ?: get_queried_object_id();
    if ( ! $post_id ) {
        return $default;
    }

    $value = get_post_meta( $post_id, '_mgk_' . $key, true );
    return $value !== '' ? $value : $default;
}

function mgk_page_enabled( $key, $default = '1', $post_id = null ) {
    return mgk_page_field( $key, $default, $post_id ) === '1';
}

function mgk_page_content_fields() {
    return [
        'hero_eyebrow' => [ 'Hero eyebrow', 'text' ],
        'hero_title' => [ 'Hero title', 'text' ],
        'hero_body' => [ 'Hero body', 'textarea' ],
        'hero_search_placeholder' => [ 'Search placeholder', 'text' ],
        'hero_search_button' => [ 'Search button', 'text' ],
        'cta_title' => [ 'Final CTA title', 'text' ],
        'cta_body' => [ 'Final CTA body', 'textarea' ],
        'cta_primary_label' => [ 'Primary CTA label', 'text' ],
        'cta_secondary_label' => [ 'Secondary CTA label', 'text' ],
        'cta_note' => [ 'CTA note', 'text' ],
        'mobile_sticky_label' => [ 'Mobile sticky CTA label', 'text' ],
    ];
}

function mgk_page_layout_fields() {
    return [
        'show_hero' => 'Hero',
        'show_process' => 'How: process',
        'show_video' => 'How: video',
        'show_difference' => 'How: difference',
        'show_guarantee' => 'How: guarantee',
        'show_pricing' => 'Pricing section',
        'show_verification' => 'How: verification',
        'show_comparison' => 'Comparison',
        'show_concerns' => 'How: concerns',
        'show_faq' => 'FAQ',
        'show_calculator' => 'Pricing: calculator',
        'show_rate_table' => 'Pricing: rate table',
        'show_subject_premium' => 'Pricing: subject premiums',
        'show_packages' => 'Pricing: packages',
        'show_included' => 'Pricing: included',
        'show_not_included' => 'Pricing: not included',
        'show_level_groups' => 'Subjects: level groups',
        'show_exam_groups' => 'Subjects: exam groups',
        'show_combinations' => 'Subjects: combinations',
        'show_trending' => 'Subjects: trending',
        'show_streams' => 'Subjects: streams',
        'show_international' => 'Subjects: international',
        'show_featured' => 'Subjects: featured',
        'show_cta' => 'Final CTA',
    ];
}

add_action( 'add_meta_boxes', function () {
    add_meta_box(
        'mgk_page_content',
        'MGK Page Content',
        'mgk_page_content_meta_box',
        'page',
        'normal',
        'high'
    );

    add_meta_box(
        'mgk_page_layout',
        'MGK Page Layout',
        'mgk_page_layout_meta_box',
        'page',
        'side',
        'default'
    );
} );

function mgk_page_content_meta_box( $post ) {
    wp_nonce_field( 'mgk_save_page_meta', 'mgk_page_meta_nonce' );
    echo '<p>Editable copy for MGK page templates. Leave a field blank to use the template default.</p>';
    echo '<table class="form-table" role="presentation"><tbody>';

    foreach ( mgk_page_content_fields() as $key => $field ) {
        [ $label, $type ] = $field;
        $value = get_post_meta( $post->ID, '_mgk_' . $key, true );
        $input_id = 'mgk_page_' . $key;
        echo '<tr><th scope="row"><label for="' . esc_attr( $input_id ) . '">' . esc_html( $label ) . '</label></th><td>';
        if ( $type === 'textarea' ) {
            echo '<textarea class="large-text" rows="3" id="' . esc_attr( $input_id ) . '" name="mgk_page[' . esc_attr( $key ) . ']">' . esc_textarea( $value ) . '</textarea>';
        } else {
            echo '<input class="large-text" type="text" id="' . esc_attr( $input_id ) . '" name="mgk_page[' . esc_attr( $key ) . ']" value="' . esc_attr( $value ) . '">';
        }
        echo '</td></tr>';
    }

    echo '</tbody></table>';
}

function mgk_page_layout_meta_box( $post ) {
    echo '<p>Control which template sections are visible on this page.</p>';

    foreach ( mgk_page_layout_fields() as $key => $label ) {
        $value = get_post_meta( $post->ID, '_mgk_' . $key, true );
        $checked = $value === '' ? true : $value === '1';
        echo '<p><label>';
        echo '<input type="hidden" name="mgk_page[' . esc_attr( $key ) . ']" value="0">';
        echo '<input type="checkbox" name="mgk_page[' . esc_attr( $key ) . ']" value="1" ' . checked( $checked, true, false ) . '> ';
        echo esc_html( $label );
        echo '</label></p>';
    }
}

add_action( 'save_post_page', function ( $post_id ) {
    if ( ! isset( $_POST['mgk_page_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mgk_page_meta_nonce'] ) ), 'mgk_save_page_meta' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! current_user_can( 'edit_page', $post_id ) ) {
        return;
    }

    $posted = isset( $_POST['mgk_page'] ) && is_array( $_POST['mgk_page'] )
        ? wp_unslash( $_POST['mgk_page'] )
        : [];

    foreach ( mgk_page_content_fields() as $key => $field ) {
        $raw = $posted[ $key ] ?? '';
        $value = $field[1] === 'textarea' ? sanitize_textarea_field( $raw ) : sanitize_text_field( $raw );
        if ( $value === '' ) {
            delete_post_meta( $post_id, '_mgk_' . $key );
        } else {
            update_post_meta( $post_id, '_mgk_' . $key, $value );
        }
    }

    foreach ( mgk_page_layout_fields() as $key => $label ) {
        update_post_meta( $post_id, '_mgk_' . $key, ( $posted[ $key ] ?? '0' ) === '1' ? '1' : '0' );
    }
} );
