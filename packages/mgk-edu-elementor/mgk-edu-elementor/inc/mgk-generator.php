<?php
/**
 * MGK layout generator (WP-CLI, local build-time only) — ELEMENTOR edition.
 *
 * Generator DIRECTION A (see docs/TEMPLATE-BUILD-PLAYBOOK.md §1.4, §3.5):
 *   Runs at the DEVELOPER's machine while building/packaging the template.
 *   Emits seed/seed-layouts.php — a script that writes each CONTENT page's
 *   Elementor layout (the `_elementor_data` JSON tree) exactly ONCE on theme
 *   activation, guarded by a per-page _mgk_layout_seeded flag so it NEVER
 *   overwrites a page the owner has edited. Never re-generated against a
 *   running user site.
 *
 * WHAT CHANGED VS THE FLATSOME BUILD
 *   Flatsome stored the layout as a shortcode string in post_content. Elementor
 *   stores it as a JSON element tree in the `_elementor_data` post meta, plus
 *   `_elementor_edit_mode = 'builder'`. So this generator emits that JSON: one
 *   MGK section widget per block, wrapped section→column→widget, in canonical
 *   order. Each widget carries EMPTY settings, so on the front end it falls back
 *   to mgk_site_setting() — producing output identical to the page template's
 *   DEFAULT MODE. The seed therefore makes the page editable in Elementor without
 *   changing a single pixel of the out-of-the-box render.
 *
 * The HTML markup itself still lives in PHP (inc/mgk-sections.php /
 * inc/mgk-content-sections.php), rendered by the widgets in inc/mgk-elementor.php.
 * This generator only assembles which sections, in which order, seed each page.
 *
 * Usage (inside container):
 *   wp mgk gen-layouts --allow-root
 *
 * Output: seed/seed-layouts.php (overwrites; it is a generated artifact).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Canonical CONTENT-page layouts: page slug => ordered list of MGK widget tags.
 * This is the single declaration of "what the default page looks like".
 * Editing this + re-running the generator changes NEW installs only.
 *
 * NOTE: the home hero uses the composite `mgk_hero` widget (not the Flatsome
 * nested mgk_hero_layout/copy/eyebrow micro-elements) — in Elementor, internal
 * composition is done with native containers, and `mgk_hero` is the widget we
 * register for the hero.
 */
function mgk_content_layouts() {
    return [
        // S01 Home
        'home' => [
            'mgk_hero',
            'mgk_trust_stats', 'mgk_live_feed', 'mgk_steps', 'mgk_subjects',
            'mgk_featured_tutors', 'mgk_why', 'mgk_spotlight', 'mgk_results', 'mgk_reviews',
            'mgk_faq', 'mgk_pricing_teaser', 'mgk_press', 'mgk_final_cta', 'mgk_newsletter',
        ],
        // S04 Subjects
        'subjects' => [
            'mgk_subjects_hero', 'mgk_subjects_levels', 'mgk_subjects_exams',
            'mgk_subjects_combinations', 'mgk_subjects_trending', 'mgk_subjects_streams',
            'mgk_subjects_international', 'mgk_subjects_featured', 'mgk_subjects_cta',
        ],
        // S05 How It Works
        'how-it-works' => [
            'mgk_how_hero', 'mgk_how_process', 'mgk_how_video', 'mgk_how_difference',
            'mgk_how_guarantee', 'mgk_how_pricing', 'mgk_how_verification',
            'mgk_how_comparison', 'mgk_how_concerns', 'mgk_how_faq', 'mgk_how_cta',
        ],
        // S06 Pricing
        'pricing' => [
            'mgk_pricing_hero', 'mgk_pricing_calculator', 'mgk_pricing_rate_table',
            'mgk_pricing_subject_premium', 'mgk_pricing_packages', 'mgk_pricing_included',
            'mgk_pricing_not_included', 'mgk_pricing_comparison', 'mgk_pricing_faq',
            'mgk_pricing_cta',
        ],

        // ── DATA pages: each is ONE locked widget (layout/query/logic locked,
        //    only display labels + Style editable). See inc/mgk-elementor.php. ──
        // S02 Teacher Listing — split into 6 editable sub-sections (results grid locked).
        'tutors'          => [
            'mgk_listing_search', 'mgk_listing_toolbar', 'mgk_listing_filters',
            'mgk_listing_results', 'mgk_listing_promo',
        ],
        // S07 Request a Tutor (lead form)
        'request-tutor'   => [ 'mgk_request_form' ],
        // S19 Tutor Apply — mobile-first wizard shell.
        'become-a-tutor'  => [ 'mgk_tutor_apply' ],
        // S20 Tutor Verification — demo video + status timeline shell.
        'tutor/verification' => [ 'mgk_tutor_verification' ],
        // S21 Tutor Dashboard — 12 locked data widgets inside one editable shell.
        'tutor/dashboard' => [ 'mgk_tutor_dashboard' ],
        // S22 Lesson Log — mobile-first lesson capture shell.
        'tutor/lesson-log' => [ 'mgk_tutor_lesson_log' ],
        // S23 Tutor Earnings — payout, commission, ledger and invoice shell.
        'tutor/earnings' => [ 'mgk_tutor_earnings' ],
        // S24 Tutor Schedule/Profile — availability and public profile shell.
        'tutor/schedule' => [ 'mgk_tutor_schedule_profile' ],
        // Parent dashboard — empty logged-in state before any child is linked.
        'parent'          => [ 'mgk_parent_empty_dashboard' ],
        // Parent dashboard — normal state once at least one child is linked.
        'parent/dashboard' => [
            'mgk_parent_dash_welcome', 'mgk_parent_dash_renewal', 'mgk_parent_dash_kpis',
            'mgk_parent_dash_progress_logs', 'mgk_parent_dash_upcoming',
            'mgk_parent_dash_action_cards', 'mgk_parent_dash_quick_links',
            'mgk_parent_dash_footer',
        ],
        // Parent message thread — app shell with locked thread/message data.
        'parent/messages' => [ 'mgk_parent_messages_page' ],
        'parent/messages/empty' => [ 'mgk_parent_messages_empty' ],
        'parent/messages/report' => [ 'mgk_parent_messages_escalation' ],
        'parent/review' => [ 'mgk_parent_review' ],
        'parent/referrals' => [ 'mgk_parent_referral' ],
        'parent/account' => [ 'mgk_parent_account' ],
        'parent/notifications' => [ 'mgk_notification_center' ],
        'parent/trial' => [
            'mgk_parent_package_context', 'mgk_parent_package_options',
            'mgk_parent_package_pause',
        ],
        'parent/trial/switch' => [ 'mgk_parent_package_switch' ],
        'parent/trial/end' => [ 'mgk_parent_package_end' ],
        'parent/trial/lapsed' => [ 'mgk_parent_package_lapsed' ],
        // S08 Tutor Proposals (matches) — split into shell widgets.
        'tutor-proposals' => [
            'mgk_proposal_header', 'mgk_proposal_cards', 'mgk_proposal_rematch',
            'mgk_proposal_compare',
        ],
        // Alias for /parent/proposals/ when seeded as a child page.
        'parent/proposals' => [
            'mgk_proposal_header', 'mgk_proposal_cards', 'mgk_proposal_rematch',
            'mgk_proposal_compare',
        ],
        // S08 State Slices — editable QA/design states for proposal lifecycle UI.
        'proposal-states' => [
            'mgk_proposal_state_intro', 'mgk_proposal_state_expired',
            'mgk_proposal_state_selected', 'mgk_proposal_state_rematch_requested',
            'mgk_proposal_state_skeleton',
        ],
        // S10 Slot Picker (booking)
        'book-slot'       => [ 'mgk_slot_picker' ],
        // NOTE: S03 Teacher Profile is a CPT single (mg_teacher), not a page slug,
        // so it is NOT seeded here. To make a tutor editable in Elementor, open the
        // tutor in the editor and drop the "MGK · Teacher Profile" widget — the page
        // template (single-mg_teacher.php) renders the full profile by default.
    ];
}

/**
 * Deterministic 7-char hex id for an Elementor element.
 *
 * Elementor only requires element ids to be unique within the document and
 * shaped like its own (7 lowercase hex chars). We derive them from a stable
 * seed (page slug + role + index) via md5 so regeneration is reproducible and
 * collision-free — no randomness, which keeps the generated seed file diff-clean.
 *
 * @param string $seed
 * @return string 7 hex chars
 */
function mgk_el_id( $seed ) {
    return substr( md5( 'mgk-elementor:' . $seed ), 0, 7 );
}

/**
 * Build the Elementor `_elementor_data` element tree for one page.
 *
 * Layout per block: section > column(100%) > widget. Empty widget settings so
 * the front-end render falls back to mgk_site_setting() (identical to DEFAULT
 * MODE). Returns a PHP array (encode with wp_json_encode at write time).
 *
 * @param string $slug
 * @param array  $widget_tags Ordered MGK widget tags.
 * @return array
 */
function mgk_build_elementor_data( $slug, array $widget_tags ) {
    $data = [];

    // Guard: only seed tags that are actually registered as MGK Elementor widgets.
    // A layout entry pointing at an unregistered tag would make Elementor render a
    // broken/empty widget (or drop it), so we skip it here and warn — never seed a
    // tag the editor can't open. (mgk_elementor_section_config() is defined in
    // inc/mgk-elementor.php; if that file isn't loaded we keep all tags.)
    if ( function_exists( 'mgk_elementor_section_config' ) ) {
        $widget_tags = array_values( array_filter( $widget_tags, function ( $tag ) use ( $slug ) {
            if ( mgk_elementor_section_config( $tag ) ) {
                return true;
            }
            if ( defined( 'WP_CLI' ) && WP_CLI && class_exists( 'WP_CLI' ) ) {
                WP_CLI::warning( "Skipping unregistered widget tag '{$tag}' in layout '{$slug}' — not a registered MGK Elementor widget." );
            }
            return false;
        } ) );
    }

    foreach ( array_values( $widget_tags ) as $i => $tag ) {
        $widget = [
            'id'         => mgk_el_id( "$slug:widget:$i:$tag" ),
            'elType'     => 'widget',
            'settings'   => (object) [], // empty → shortcode falls back to site settings
            'elements'   => [],
            'widgetType' => $tag,
        ];

        $column = [
            'id'       => mgk_el_id( "$slug:column:$i" ),
            'elType'   => 'column',
            'settings' => [ '_column_size' => 100, '_inline_size' => null ],
            'elements' => [ $widget ],
        ];

        $section = [
            'id'       => mgk_el_id( "$slug:section:$i" ),
            'elType'   => 'section',
            'settings' => (object) [],
            'elements' => [ $column ],
        ];

        $data[] = $section;
    }

    return $data;
}

/**
 * Build one Elementor widget node.
 */
function mgk_el_widget( $slug, $tag, $key ) {
    return [
        'id'         => mgk_el_id( "$slug:widget:$key:$tag" ),
        'elType'     => 'widget',
        'settings'   => (object) [],
        'elements'   => [],
        'widgetType' => $tag,
    ];
}

/**
 * Build a full-width (100%) section wrapping a single widget.
 */
function mgk_el_full_section( $slug, $tag, $key ) {
    return [
        'id'       => mgk_el_id( "$slug:section:$key" ),
        'elType'   => 'section',
        'settings' => (object) [],
        'elements' => [
            [
                'id'       => mgk_el_id( "$slug:column:$key" ),
                'elType'   => 'column',
                'settings' => [ '_column_size' => 100, '_inline_size' => null ],
                'elements' => [ mgk_el_widget( $slug, $tag, $key ) ],
            ],
        ],
    ];
}

/**
 * Teacher-listing layout that PRESERVES the desktop 2-column design while keeping
 * each part as its own editable widget:
 *   [ search ]      (full-width section)
 *   [ toolbar ]     (full-width section)
 *   [ filters | results ]   ONE section, two columns (sidebar 22% | results 78%)
 *   [ promo ]       (full-width section)
 *   [ related ]     (full-width section)
 * The 2-col section gives filters + results a shared grid parent so the sidebar
 * sits beside the results (the .mgk-listing-layout look) instead of stacking.
 */
function mgk_build_listing_layout( $slug ) {
    $data = [];

    if ( ! function_exists( 'mgk_elementor_section_config' ) || mgk_elementor_section_config( 'mgk_listing_search' ) ) {
        $data[] = mgk_el_full_section( $slug, 'mgk_listing_search', 0 );
    }
    if ( ! function_exists( 'mgk_elementor_section_config' ) || mgk_elementor_section_config( 'mgk_listing_toolbar' ) ) {
        $data[] = mgk_el_full_section( $slug, 'mgk_listing_toolbar', 1 );
    }

    // 2-column row: filters (sidebar) | results + promo.
    // Related searches render inside Results so they sit below the tutor list.
    // Promo sits INSIDE the results column, right below the grid, so
    // they appear directly under the tutor list (not full-width under the sidebar).
    $results_col_widgets = [ mgk_el_widget( $slug, 'mgk_listing_results', 3 ) ];
    if ( ! function_exists( 'mgk_elementor_section_config' ) || mgk_elementor_section_config( 'mgk_listing_promo' ) ) {
        $results_col_widgets[] = mgk_el_widget( $slug, 'mgk_listing_promo', 4 );
    }

    $data[] = [
        'id'       => mgk_el_id( "$slug:section:row" ),
        'elType'   => 'section',
        'settings' => [ 'structure' => '20', 'gap' => 'default' ],
        'elements' => [
            [
                'id'       => mgk_el_id( "$slug:col:filters" ),
                'elType'   => 'column',
                'settings' => [ '_column_size' => 25, '_inline_size' => 24, 'content_position' => 'top' ],
                'elements' => [ mgk_el_widget( $slug, 'mgk_listing_filters', 2 ) ],
            ],
            [
                'id'       => mgk_el_id( "$slug:col:results" ),
                'elType'   => 'column',
                'settings' => [ '_column_size' => 75, '_inline_size' => 76, 'content_position' => 'top' ],
                'elements' => $results_col_widgets,
            ],
        ],
    ];

    return $data;
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {

    /**
     * `wp mgk <command>`
     */
    class MGK_CLI_Command {

        /**
         * Generate seed/seed-layouts.php from the canonical layouts (Elementor JSON).
         *
         * ## EXAMPLES
         *     wp mgk gen-layouts
         *
         * @subcommand gen-layouts
         * @when after_wp_load
         */
        public function gen_layouts( $args, $assoc_args ) {
            $version = defined( 'MGK_LAYOUT_VERSION' ) ? MGK_LAYOUT_VERSION : '1.0';
            $layouts = mgk_content_layouts();

            $out  = "<?php\n";
            $out .= "/**\n";
            $out .= " * seed/seed-layouts.php — GENERATED by `wp mgk gen-layouts`. Do not edit by hand.\n";
            $out .= " *\n";
            $out .= " * Writes each CONTENT page's Elementor layout (`_elementor_data` JSON) ONCE,\n";
            $out .= " * guarded by _mgk_layout_seeded so an owner's edits are never overwritten.\n";
            $out .= " * Each widget carries empty settings, so the front-end render is identical to\n";
            $out .= " * the page template's DEFAULT MODE — the seed only makes the page editable in\n";
            $out .= " * Elementor. Runs via the auto-seed framework (listed in manifest.json seed_files).\n";
            $out .= " */\n\n";
            $out .= "if ( ! defined( 'ABSPATH' ) ) exit;\n\n";
            $out .= "if ( ! function_exists( 'mgk_seed_layout' ) ) :\n";
            $out .= "/**\n";
            $out .= " * Seed one page's Elementor layout, once.\n";
            $out .= " *\n";
            $out .= " * @param string \$slug         Page slug (path).\n";
            $out .= " * @param string \$elementor_json wp_json_encode'd `_elementor_data` tree.\n";
            $out .= " * @param string \$version       Layout version, stored in _mgk_layout_seeded.\n";
            $out .= " */\n";
            $out .= "function mgk_seed_layout( \$slug, \$elementor_json, \$version ) {\n";
            $out .= "    \$page = get_page_by_path( \$slug );\n";
            $out .= "    if ( ! \$page ) return;                                                   // shell created by seed-<slug>.php\n";
            $out .= "    if ( get_post_meta( \$page->ID, '_mgk_layout_seeded', true ) ) return;     // already seeded — never overwrite\n";
            $out .= "    if ( get_post_meta( \$page->ID, '_elementor_edit_mode', true ) === 'builder' ) {\n";
            $out .= "        // Owner already built this page in Elementor before our seed ran — respect it.\n";
            $out .= "        update_post_meta( \$page->ID, '_mgk_layout_seeded', \$version );\n";
            $out .= "        return;\n";
            $out .= "    }\n";
            $out .= "    // Elementor stores the layout in meta; post_content is left empty (Elementor\n";
            $out .= "    // regenerates rendered HTML into it on save/first render).\n";
            $out .= "    update_post_meta( \$page->ID, '_elementor_data', wp_slash( \$elementor_json ) );\n";
            $out .= "    update_post_meta( \$page->ID, '_elementor_edit_mode', 'builder' );\n";
            $out .= "    if ( defined( 'ELEMENTOR_VERSION' ) ) {\n";
            $out .= "        update_post_meta( \$page->ID, '_elementor_version', ELEMENTOR_VERSION );\n";
            $out .= "    }\n";
            $out .= "    update_post_meta( \$page->ID, '_mgk_layout_seeded', \$version );\n";
            $out .= "}\n";
            $out .= "endif;\n\n";
            $out .= "\$mgk_layout_version = '" . $version . "';\n\n";

            foreach ( $layouts as $slug => $widget_tags ) {
                // The tutor-listing page uses a custom layout (2-col filters|results)
                // so the split sub-widgets keep the desktop 2-column design.
                $tree = ( $slug === 'tutors' )
                    ? mgk_build_listing_layout( $slug )
                    : mgk_build_elementor_data( $slug, $widget_tags );
                $json = wp_json_encode( $tree );
                // var_export the JSON string as a PHP single-quoted literal (safe, diff-clean).
                $php_literal = var_export( $json, true );
                $out .= "mgk_seed_layout( " . var_export( $slug, true ) . ", " . $php_literal . ", \$mgk_layout_version );\n";
            }

            $target = get_stylesheet_directory() . '/seed/seed-layouts.php';
            $written = file_put_contents( $target, $out );

            if ( $written === false ) {
                WP_CLI::error( "Could not write {$target}" );
            }
            WP_CLI::success( sprintf( 'Generated %s (%d pages, version %s, %d bytes)', $target, count( $layouts ), $version, $written ) );
        }
    }

    WP_CLI::add_command( 'mgk', 'MGK_CLI_Command' );
}
