<?php
/**
 * Operational site settings for MGK templates.
 *
 * Page copy, page layout, and marketing content are edited in Elementor. This
 * settings screen is intentionally limited to site identity, brand tokens, and
 * booking/payment configuration.
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
        'stripe_connect_client_id' => '',
        'stripe_connect_account_id'=> '',
        'stripe_connect_livemode'  => '0',
        'stripe_connect_connected_at' => '',
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
        'Site Identity' => [
            'logo_image_id' => [ 'Logo image', 'image' ],
            'logo_text' => [ 'Logo text fallback', 'text' ],
            'phone' => [ 'Phone', 'text' ],
            'email' => [ 'Email', 'text' ],
        ],
        'Brand Tokens' => [
            'accent_color' => [ 'Accent color', 'color' ],
            'dark_color' => [ 'Text/dark color', 'color' ],
            'muted_color' => [ 'Muted text color', 'color' ],
        ],
        'Payments (Booking)' => [
            'pay_paynow_enabled'    => [ 'Enable PayNow QR', 'checkbox' ],
            'paynow_uen'            => [ 'PayNow UEN (company)', 'text' ],
            'paynow_payee'          => [ 'PayNow payee/merchant name', 'text' ],
            'pay_stripe_enabled'    => [ 'Enable Card (Stripe)', 'checkbox' ],
        ],
        'Stripe Platform (Dev)' => [
            'stripe_connect_client_id' => [ 'Stripe Connect client ID (ca_…)', 'text' ],
            'stripe_publishable'    => [ 'Platform publishable key (pk_…)', 'text' ],
            'stripe_secret'         => [ 'Platform secret key (sk_…)', 'secret' ],
            'stripe_webhook_secret' => [ 'Stripe webhook secret (whsec_…)', 'secret' ],
            'stripe_connect_account_id' => [ 'Connected account ID (dev override)', 'text' ],
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

/**
 * Agency-facing "how to take real payments" guide, shown above the Payments
 * fields. The money flow is 3-tier (see project-payment-tier-model): the PARENT
 * pays → the AGENCY's own Stripe/bank receives it → Margick never touches it.
 * So each agency pastes THEIR OWN Stripe keys here. The panel also surfaces the
 * live MOCK-vs-LIVE status and the exact webhook URL to register in Stripe.
 */
function mgk_payments_setup_panel_html() {
    $cfg         = function_exists( 'mgk_payment_config' ) ? mgk_payment_config() : [];
    $stripe_live = ! empty( $cfg['stripe_live'] );    // a secret key is set
    $stripe_on   = ! empty( $cfg['stripe_active'] );   // "Enable Card" ticked
    $paynow_on   = ! empty( $cfg['paynow_active'] );   // enabled + valid UEN
    $secret      = function_exists( 'mgk_stripe_secret_key' ) ? mgk_stripe_secret_key() : '';
    $wh_set      = function_exists( 'mgk_stripe_webhook_secret' ) ? mgk_stripe_webhook_secret() !== '' : false;
    $is_test_key = strpos( $secret, 'sk_test_' ) === 0;
    $webhook_url = rest_url( 'mgk/v1/stripe/webhook' );
    $dash_link   = $is_test_key ? 'https://dashboard.stripe.com/test/apikeys' : 'https://dashboard.stripe.com/apikeys';

    // Overall money state (direct-key model — agency uses their OWN Stripe account).
    if ( $stripe_on && $stripe_live && $wh_set ) {
        $label = $is_test_key ? '● Card payments LIVE (test keys)' : '● Card payments LIVE';
        $state = [ '#067a4b', '#e6f6ee', $label, 'Parents pay by card on Stripe Checkout. Money lands in your own Stripe balance and pays out to your bank. Bookings confirm automatically.' ];
    } elseif ( $stripe_on && $stripe_live ) {
        $state = [ '#b26a00', '#fff4e0', '● Keys set — finishing webhook', 'Your Stripe key is saved. The confirmation webhook auto-configures on a public domain; on localhost it stays in test/manual mode.' ];
    } elseif ( $stripe_on ) {
        $state = [ '#b26a00', '#fff4e0', '● Card mock mode — paste your Stripe keys', 'Card flow runs as a local mock until you paste your two Stripe keys below.' ];
    } else {
        $state = [ '#555', '#eee', '○ Card payments are OFF', 'Tick “Enable Card (Stripe)” below, then paste your two Stripe keys.' ];
    }

    // One-time flash from the auto webhook setup after a key save.
    $flash = get_transient( 'mgk_stripe_webhook_flash' );
    if ( $flash ) delete_transient( 'mgk_stripe_webhook_flash' );
    $flash_colors = [ 'success' => [ '#067a4b', '#e6f6ee' ], 'warning' => [ '#b26a00', '#fff4e0' ], 'info' => [ '#1d6fb8', '#e7f1fb' ] ];

    ob_start();
    ?>
    <div style="border:1px solid #c3c4c7;border-left:4px solid <?php echo esc_attr( $state[0] ); ?>;background:#fff;padding:14px 18px;margin:6px 0 18px;max-width:880px;border-radius:4px;">
        <div style="display:inline-block;background:<?php echo esc_attr( $state[1] ); ?>;color:<?php echo esc_attr( $state[0] ); ?>;font-weight:700;padding:3px 10px;border-radius:12px;font-size:12px;"><?php echo esc_html( $state[2] ); ?></div>
        <span style="margin-left:10px;font-size:12px;color:#555;">
            PayNow: <strong style="color:<?php echo $paynow_on ? '#067a4b' : '#888'; ?>;"><?php echo $paynow_on ? 'active' : 'off'; ?></strong>
        </span>
        <p style="margin:8px 0 14px;color:#333;"><?php echo wp_kses_post( $state[3] ); ?></p>

        <?php if ( $flash && isset( $flash_colors[ $flash['type'] ] ) ) :
            [ $fc, $fb ] = $flash_colors[ $flash['type'] ]; ?>
            <p style="margin:0 0 14px;padding:8px 12px;border-radius:4px;background:<?php echo esc_attr( $fb ); ?>;color:<?php echo esc_attr( $fc ); ?>;font-size:13px;"><?php echo esc_html( $flash['msg'] ); ?></p>
        <?php endif; ?>

        <p style="margin:0 0 8px;font-weight:600;">Accept cards in 2 steps</p>
        <ol style="margin:0 0 14px 18px;color:#333;font-size:13px;line-height:1.7;">
            <li><strong>Create your Stripe account</strong> and add your company bank for payouts at <a href="https://dashboard.stripe.com/register" target="_blank" rel="noopener">dashboard.stripe.com</a>. Stripe handles the card details and your business verification — it never touches this website.</li>
            <li><strong>Copy your two keys</strong> (<code>pk_…</code> Publishable + <code>sk_…</code> Secret) from <a href="<?php echo esc_url( $dash_link ); ?>" target="_blank" rel="noopener">Stripe → Developers → API keys</a>, paste them in the fields below, tick <em>Enable Card</em>, and Save.</li>
        </ol>
        <p style="margin:0 0 14px;color:#067a4b;font-size:13px;">✓ That’s it — the payment confirmation webhook is created automatically when you save your secret key. No webhook screen, no extra tools to install.</p>

        <details style="margin:0 0 12px;">
            <summary style="cursor:pointer;font-size:12px;color:#666;">Advanced / manual webhook URL</summary>
            <p style="margin:8px 0 6px;color:#666;font-size:12px;">Only if auto-setup can’t run (e.g. a staging URL Stripe can’t reach). Add this URL under Stripe → Developers → Webhooks, subscribe to the four <code>checkout.session</code>/<code>payment_intent</code> events, then paste the <code>whsec_…</code> into the field below.</p>
            <div style="display:flex;gap:6px;align-items:center;">
                <input type="text" readonly value="<?php echo esc_attr( $webhook_url ); ?>" onclick="this.select()" style="flex:1;max-width:560px;font-family:monospace;font-size:12px;padding:5px 8px;background:#f6f7f7;border:1px solid #c3c4c7;border-radius:3px;">
                <button type="button" class="button button-small" onclick="navigator.clipboard&amp;&amp;navigator.clipboard.writeText('<?php echo esc_js( $webhook_url ); ?>');this.textContent='Copied';">Copy</button>
            </div>
            <p style="margin:8px 0 0;font-size:12px;color:<?php echo $wh_set ? '#067a4b' : '#888'; ?>;">Webhook signing secret: <strong><?php echo $wh_set ? 'configured ✓' : 'not set yet'; ?></strong></p>
        </details>

        <p style="margin:0 0 6px;font-weight:600;">🇸🇬 PayNow — simplest, no Stripe needed</p>
        <p style="margin:0;color:#444;font-size:13px;">Prefer bank transfer only? Just enter your company <strong>UEN</strong> + payee name below and tick <em>Enable PayNow QR</em> — no Stripe account at all. Parents scan the QR and transfer straight into your company account. Note: PayNow has <strong>no automatic confirmation</strong> — mark those bookings paid under <em>Bookings</em> once the transfer lands.</p>
    </div>
    <?php
    return ob_get_clean();
}

function mgk_site_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) return;
    $stripe_notice = isset( $_GET['stripe_connect'] ) ? sanitize_key( wp_unslash( $_GET['stripe_connect'] ) ) : '';
    $stripe_notices = [
        'connected'      => [ 'success', 'Stripe account connected.' ],
        'disconnected'   => [ 'success', 'Stripe account disconnected.' ],
        'missing_config' => [ 'error', 'Add the Stripe Connect client ID and platform secret key first.' ],
        'bad_state'      => [ 'error', 'Stripe connect session expired. Please try again.' ],
        'denied'         => [ 'error', 'Stripe connection was cancelled.' ],
        'missing_code'   => [ 'error', 'Stripe did not return an authorization code.' ],
        'token_error'    => [ 'error', 'Stripe OAuth token exchange failed.' ],
        'bad_account'    => [ 'error', 'Stripe returned an invalid connected account.' ],
    ];
    ?>
    <div class="wrap">
        <h1>MGK Site Settings</h1>
        <p>Configure site identity, brand tokens, and payment settings. Edit page content and layout in Elementor.</p>
        <?php if ( $stripe_notice && isset( $stripe_notices[ $stripe_notice ] ) ) : ?>
            <div class="notice notice-<?php echo esc_attr( $stripe_notices[ $stripe_notice ][0] ); ?>"><p><?php echo esc_html( $stripe_notices[ $stripe_notice ][1] ); ?></p></div>
        <?php endif; ?>
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <input type="hidden" name="action" value="mgk_save_site_settings">
            <?php wp_nonce_field( 'mgk_save_site_settings' ); ?>
            <?php foreach ( mgk_site_settings_groups() as $group => $fields ) : ?>
                <h2><?php echo esc_html( $group ); ?></h2>
                <?php if ( $group === 'Payments (Booking)' ) { echo mgk_payments_setup_panel_html(); } ?>
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
            // A freshly pasted/rotated secret key triggers automatic webhook setup.
            if ( $key === 'stripe_secret' ) {
                $mgk_stripe_key_changed = true;
            }
            continue;
        }
        if ( $key === 'paynow_uen' ) {
            $value = strtoupper( sanitize_text_field( $raw ) );
            set_theme_mod( 'mgk_' . $key, $value );
            continue;
        }
        if ( $key === 'stripe_connect_account_id' ) {
            $value = preg_match( '/^acct_[A-Za-z0-9_]+$/', trim( (string) $raw ) ) ? trim( (string) $raw ) : '';
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

    // When a Stripe secret key was just pasted/rotated, auto-provision the webhook
    // endpoint so the agency never touches Stripe's Developers → Webhooks screen.
    if ( ! empty( $mgk_stripe_key_changed ) && function_exists( 'mgk_stripe_ensure_webhook_endpoint' ) ) {
        $wh = mgk_stripe_ensure_webhook_endpoint();
        if ( is_wp_error( $wh ) ) {
            set_transient( 'mgk_stripe_webhook_flash', [
                'type' => $wh->get_error_code() === 'mgk_local_url' ? 'info' : 'warning',
                'msg'  => $wh->get_error_message(),
            ], 60 );
        } else {
            set_transient( 'mgk_stripe_webhook_flash', [
                'type' => 'success',
                'msg'  => 'Stripe webhook configured automatically — card payments will confirm bookings on their own. Nothing else to set up.',
            ], 60 );
        }
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
        'dark_color' => [ 'Text/dark color', 'color' ],
        'muted_color' => [ 'Muted text color', 'color' ],
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

// Marketing page copy and layout are intentionally edited in Elementor now.
// mgk_page_field() and mgk_page_enabled() remain for legacy template fallbacks.
