<?php
/**
 * MGK Edu Elementor — Factory Base
 * ================================
 * Hello Elementor child. Sections are authored as PHP shortcodes (single source
 * of truth in template-parts/) and registered as native Elementor widgets in
 * inc/mgk-elementor.php, so owners drag/drop/edit them in the Elementor editor.
 *
 * Master copy. Copy vào mỗi workspace WP install qua generator/new-template.ps1.
 *
 * Logic:
 *   1. factory_category() resolves category from (in order):
 *        a. wp_option `factory_category` (set after first seed)
 *        b. seed/manifest.json (shipped with theme)
 *        c. hardcoded default 'fashion'
 *   2. Always load schemas/_common.php (Brand Settings)
 *   3. Load schemas/{category}.php + {category}-overrides.php
 *   4. Auto-seed hook reads seed/manifest.json and runs declared seed files
 *      on first request after activation. Idempotent via {slug}_seeded option.
 *
 * Per-workspace customizations (theme switcher, custom code) override below.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/** Cached manifest reader. Returns null if no manifest. */
function factory_manifest() {
    static $cache = false;
    if ( $cache !== false ) return $cache;
    $path = __DIR__ . '/seed/manifest.json';
    if ( ! file_exists( $path ) ) return $cache = null;
    $data = json_decode( file_get_contents( $path ), true );
    return $cache = ( is_array( $data ) ? $data : null );
}

/** Factory category. Reads option, then manifest, then default. */
function factory_category() {
    static $cached = null;
    if ( $cached !== null ) return $cached;

    $opt = get_option( 'factory_category' );
    if ( $opt ) return $cached = $opt;

    $manifest = factory_manifest();
    if ( ! empty( $manifest['category'] ) ) return $cached = $manifest['category'];

    return $cached = 'fashion';
}

/** Path tới folder schemas. Tìm 3 chỗ. */
function factory_schemas_path() {
    // 1. Workspace local copy (preferred for portability)
    $local = dirname( __DIR__, 2 ) . '/schemas';
    if ( is_dir( $local ) ) return $local;

    // 2. Factory shared (Windows dev path — DEV ONLY, K8s pod sẽ KHÔNG tìm thấy path này)
    $shared = 'D:/margick/Margick_template/template-factory/schemas';
    if ( is_dir( $shared ) ) return $shared;

    // 3. Inside child theme (production: bundled in zip)
    $inside = __DIR__ . '/schemas';
    if ( is_dir( $inside ) ) return $inside;

    return null;
}

/** Load schema files: _common.php + {category}.php + {category}-overrides.php */
function factory_load_schemas() {
    $path = factory_schemas_path();
    if ( ! $path ) return;

    $files = [
        $path . '/_common.php',
        $path . '/' . factory_category() . '.php',
        $path . '/' . factory_category() . '-overrides.php',
    ];
    foreach ( $files as $f ) {
        if ( file_exists( $f ) ) require_once $f;
    }
}
factory_load_schemas();

require_once __DIR__ . '/inc/mgk-demo-data.php';
require_once __DIR__ . '/inc/mgk-db-tutors.php';
require_once __DIR__ . '/inc/mgk-helpers.php';
require_once __DIR__ . '/inc/mgk-site-settings.php';
require_once __DIR__ . '/inc/mgk-acf-fields.php';
require_once __DIR__ . '/inc/mgk-setup.php';
require_once __DIR__ . '/inc/mgk-assets.php';
require_once __DIR__ . '/inc/mgk-commerce.php';
require_once __DIR__ . '/inc/mgk-states.php';
require_once __DIR__ . '/inc/mgk-cpts.php';
require_once __DIR__ . '/inc/mgk-rest.php';
require_once __DIR__ . '/inc/mgk-booking.php';
// ── Pricing / Discount engine (single source of truth: display === charge) ──
require_once __DIR__ . '/inc/pricing/mgk-discounts.php';
require_once __DIR__ . '/inc/pricing/mgk-voucher-cpt.php';
require_once __DIR__ . '/inc/pricing/mgk-discounts-admin.php';
// ── Booking Engine (Phase 0.5) — real custom-table booking engine ──
require_once __DIR__ . '/inc/booking/booking-schema.php';
require_once __DIR__ . '/inc/booking/booking-events.php';
require_once __DIR__ . '/inc/booking/booking-view.php';
require_once __DIR__ . '/inc/booking/booking-availability.php';
require_once __DIR__ . '/inc/booking/booking-locks.php';
require_once __DIR__ . '/inc/booking/booking-cron.php';
require_once __DIR__ . '/inc/booking/booking-paynow.php';
require_once __DIR__ . '/inc/booking/booking-payment-stripe.php';
require_once __DIR__ . '/inc/booking/booking-rest.php';
require_once __DIR__ . '/inc/booking/booking-manage.php';
require_once __DIR__ . '/inc/pricing/mgk-packages.php';
require_once __DIR__ . '/inc/booking/booking-mirror.php';
require_once __DIR__ . '/inc/booking/booking-admin.php';
require_once __DIR__ . '/inc/booking/booking-admin-bookings.php';
require_once __DIR__ . '/inc/mgk-request-fields.php';
require_once __DIR__ . '/inc/mgk-forms.php';
require_once __DIR__ . '/inc/mgk-proposals.php';
require_once __DIR__ . '/inc/mgk-learning.php';
require_once __DIR__ . '/inc/mgk-parent-dashboard.php';
require_once __DIR__ . '/inc/mgk-messaging-model.php';
require_once __DIR__ . '/inc/mgk-messaging.php';
require_once __DIR__ . '/inc/mgk-realtime.php';
require_once __DIR__ . '/inc/mgk-parent-review.php';
require_once __DIR__ . '/inc/mgk-parent-referral.php';
require_once __DIR__ . '/inc/mgk-parent-account.php';
require_once __DIR__ . '/inc/mgk-notification-center.php';
require_once __DIR__ . '/inc/mgk-whatsapp.php';
// ── Parent identity & passwordless auth (S11 account claim, FR-BOOK-07/BR-22) ──
require_once __DIR__ . '/inc/auth/mgk-parent-identity.php';
require_once __DIR__ . '/inc/auth/mgk-passwordless.php';
require_once __DIR__ . '/inc/auth/mgk-login.php';
require_once __DIR__ . '/inc/mgk-tutor-apply.php';
require_once __DIR__ . '/inc/mgk-tutor-verification.php';
require_once __DIR__ . '/inc/mgk-tutor-dashboard.php';
require_once __DIR__ . '/inc/mgk-tutor-lesson-log.php';
require_once __DIR__ . '/inc/mgk-tutor-earnings.php';
require_once __DIR__ . '/inc/mgk-tutor-schedule-profile.php';
require_once __DIR__ . '/inc/mgk-select-tutor.php';
require_once __DIR__ . '/inc/mgk-slots.php';
require_once __DIR__ . '/inc/mgk-pay.php';
require_once __DIR__ . '/inc/mgk-confirmation.php';
require_once __DIR__ . '/inc/mgk-sections.php';
require_once __DIR__ . '/inc/mgk-content-sections.php';
require_once __DIR__ . '/inc/mgk-listing-render.php';
require_once __DIR__ . '/inc/mgk-profile-render.php';
require_once __DIR__ . '/inc/mgk-states-render.php';
require_once __DIR__ . '/inc/mgk-elementor.php';
require_once __DIR__ . '/inc/mgk-generator.php';

/**
 * Bust child stylesheet cache during local/template iteration.
 */
add_filter( 'style_loader_src', function ( $src, $handle ) {
    if ( $handle !== 'hello-elementor-child-style' ) {
        return $src;
    }

    $style_path = get_stylesheet_directory() . '/style.css';
    if ( ! file_exists( $style_path ) ) {
        return $src;
    }

    return add_query_arg( 'v', filemtime( $style_path ), remove_query_arg( 'ver', $src ) );
}, 10, 2 );

/**
 * Enrichment screen classes for layout rules that need to escape the parent
 * theme's default page wrapper.
 */
add_filter( 'body_class', function ( $classes ) {
    if ( factory_category() !== 'edu' ) {
        return $classes;
    }

    $classes[] = 'mgk-edu';

    if ( is_front_page() || is_home() ) {
        $classes[] = 'mgk-page-s01';
    } elseif ( is_page( [ 'tutors', 'teachers' ] ) ) {
        $classes[] = 'mgk-page-s02';
    } elseif ( is_singular( 'mg_teacher' ) ) {
        $classes[] = 'mgk-page-s03';
    } elseif ( is_page( 'request-tutor' ) ) {
        $classes[] = 'mgk-page-s07';
    } elseif ( is_page( 'become-a-tutor' ) ) {
        $classes[] = 'mgk-page-tutor-apply';
    } elseif ( is_page( 'verification' ) ) {
        $classes[] = 'mgk-page-tutor-verification';
    } elseif ( is_page() && ( $lesson_log = get_page_by_path( 'tutor/lesson-log' ) ) && (int) $lesson_log->ID === (int) get_queried_object_id() ) {
        $classes[] = 'mgk-page-tutor-lesson-log';
    } elseif ( is_page() && ( $earnings = get_page_by_path( 'tutor/earnings' ) ) && (int) $earnings->ID === (int) get_queried_object_id() ) {
        $classes[] = 'mgk-page-tutor-earnings';
    } elseif ( is_page() && ( $schedule = get_page_by_path( 'tutor/schedule' ) ) && (int) $schedule->ID === (int) get_queried_object_id() ) {
        $classes[] = 'mgk-page-tutor-schedule';
    } elseif ( is_page() && ( $tutor_dash = get_page_by_path( 'tutor/dashboard' ) ) && (int) $tutor_dash->ID === (int) get_queried_object_id() ) {
        $classes[] = 'mgk-page-tutor-dashboard';
    } elseif ( is_page( [ 'tutor-proposals', 'proposals', 'proposal-states' ] ) ) {
        $classes[] = 'mgk-page-s08';
    } elseif ( is_page( [ 'parent', 'dashboard' ] ) ) {
        $classes[] = 'mgk-page-parent-dashboard';
    } elseif ( is_page( [ 'messages', 'report', 'empty' ] ) ) {
        $classes[] = 'mgk-page-parent-messages';
    } elseif ( is_page( 'review' ) ) {
        $classes[] = 'mgk-page-parent-review';
    } elseif ( is_page( 'referrals' ) ) {
        $classes[] = 'mgk-page-parent-referral';
    } elseif ( is_page( 'account' ) ) {
        $classes[] = 'mgk-page-parent-account';
    } elseif ( is_page() && ( $notifications = get_page_by_path( 'parent/notifications' ) ) && (int) $notifications->ID === (int) get_queried_object_id() ) {
        $classes[] = 'mgk-page-notifications';
    } elseif ( is_page( 'trial' ) || ( is_page() && ( $trial_page = get_page_by_path( 'parent/trial' ) ) && in_array( (int) $trial_page->ID, get_post_ancestors( get_queried_object_id() ), true ) ) ) {
        $classes[] = 'mgk-page-s09';
        $classes[] = 'mgk-page-parent-package';
    } elseif ( is_page( 'book-slot' ) ) {
        $classes[] = 'mgk-page-s10';
    } elseif ( is_page( [ 'trial-pay', 'pay' ] ) ) {
        $classes[] = 'mgk-page-s11';
    } elseif ( is_page( [ 'trial-confirmed', 'confirmed' ] ) ) {
        $classes[] = 'mgk-page-s12';
    }

    return $classes;
} );

/**
 * Auto-seed framework.
 *
 * Reads seed/manifest.json, runs declared seed files in order on first request
 * after theme activation. Designed for margick K8s host: theme gets uploaded
 * via margick API, activated, and on first HTTP hit this hook populates the
 * empty DB with template content.
 *
 * manifest.json schema:
 *   {
 *     "slug": "enrichment-bloom",        // unique per template, used in seeded-flag option
 *     "category": "enrichment",          // matches schemas/{category}.php
 *     "seed_files": [                    // relative to seed/ folder, runs in order
 *       "seed-enrichment.php",
 *       "add-enrichment-teachers.php",
 *       "..."
 *     ],
 *     "permalink_structure": "/%postname%/"   // optional, only set if site has no permalink
 *   }
 *
 * Three layers of idempotency guard:
 *   1. Static `$running` — prevents reentry within same request.
 *   2. Option `{slug}_seeded` = 'running' set BEFORE scripts run — concurrent sub-requests bail.
 *   3. Option `{slug}_seeded` = 'yes' set AFTER scripts — future requests skip.
 *
 * Runs at init:99 to ensure CPTs (init:10) and ACF field groups (acf/init) are registered.
 */
add_action( 'init', function () {
    static $running = false;
    if ( $running ) return;

    $manifest = factory_manifest();
    if ( ! $manifest || empty( $manifest['slug'] ) || empty( $manifest['seed_files'] ) ) return;

    $slug      = sanitize_key( $manifest['slug'] );
    $flag_key  = $slug . '_seeded';
    $state     = get_option( $flag_key, '' );
    if ( $state === 'yes' || $state === 'running' ) return;

    if ( ! post_type_exists( 'mg_teacher' ) ) return;
    if ( ! function_exists( 'update_field' ) ) return;

    $running = true;
    update_option( $flag_key, 'running' );

    // Ensure category matches manifest (in case admin clicked something)
    if ( ! empty( $manifest['category'] ) && get_option( 'factory_category' ) !== $manifest['category'] ) {
        update_option( 'factory_category', $manifest['category'] );
    }

    // Permalink fix — force manifest's structure on first seed only (after seed, flag
    // prevents re-run, so user's later changes are preserved).
    if ( ! empty( $manifest['permalink_structure'] ) && get_option( 'permalink_structure' ) !== $manifest['permalink_structure'] ) {
        update_option( 'permalink_structure', $manifest['permalink_structure'] );
    }

    // WP_CLI polyfill: some seed scripts use WP_CLI::log/success when run via CLI.
    // In web context the class doesn't exist → fatal. Polyfill maps to echo.
    if ( ! class_exists( 'WP_CLI' ) ) {
        class WP_CLI {
            public static function log( $msg )     { echo "  [log] $msg\n"; }
            public static function success( $msg ) { echo "  [success] $msg\n"; }
            public static function warning( $msg ) { echo "  [warning] $msg\n"; }
            public static function error( $msg, $exit = true ) {
                echo "  [error] $msg\n";
                if ( $exit ) throw new RuntimeException( (string) $msg );
            }
            public static function line( $msg = '' ) { echo "  $msg\n"; }
        }
    }

    ob_start();
    try {
        foreach ( $manifest['seed_files'] as $rel ) {
            $path = __DIR__ . '/seed/' . $rel;
            if ( file_exists( $path ) ) {
                echo "\n--- Running: seed/$rel ---\n";
                require_once $path;
            } else {
                echo "\n--- Missing: seed/$rel (skipped) ---\n";
            }
        }
        // Final permalink flush so newly-created pages are reachable
        flush_rewrite_rules();
    } catch ( Throwable $e ) {
        update_option( $slug . '_seed_error', $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine() );
    }
    $log = ob_get_clean();

    update_option( $slug . '_seed_log', substr( (string) $log, 0, 12000 ) );
    update_option( $flag_key, 'yes' );
    $running = false;
}, 99 );


/* ============================================================
   Per-workspace overrides
   ============================================================
   Kéo content riêng cho workspace này vào dưới đây.
   Generator KHÔNG overwrite khi sync child-theme-base.
   --- BEGIN WORKSPACE OVERRIDES --- */



/* --- END WORKSPACE OVERRIDES --- */
