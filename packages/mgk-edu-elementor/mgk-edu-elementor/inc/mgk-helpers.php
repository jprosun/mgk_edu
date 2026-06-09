<?php
/**
 * MGK helper functions.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function mgk_url( $path = '/' ) {
    return home_url( '/' . ltrim( $path, '/' ) );
}

function mgk_cta_url( $target = 'find-tutor', $args = [] ) {
    if ( $target === 'signin' ) {
        $url = wp_login_url( mgk_url( '/parent/' ) );
        return $args ? add_query_arg( array_map( 'sanitize_text_field', $args ), $url ) : $url;
    }

    $routes = [
        // Match-intent CTAs ("Find a Tutor", "Request Custom Match", hero/final/
        // sticky/nav) enter the matchmaking funnel at S07. Browse-intent CTAs
        // ("Browse Tutors", filters, breadcrumbs) go to the S02 listing.
        'find-tutor' => '/request-match/',
        'browse'     => '/student/teachers/',
        'trial'      => '/parent/trial/',
        'request'    => '/request-match/',
        'subjects'   => '/subjects/',
        'how'        => '/how-it-works/',
        'pricing'    => '/pricing/',
        'tutor'      => '/become-a-tutor/',
        'agency'     => '/for-agencies/',
        'faq'        => '/faq/',
        'dashboard'  => '/parent/dashboard/',
    ];

    $url = mgk_url( $routes[ $target ] ?? '/' );
    return $args ? add_query_arg( array_map( 'sanitize_text_field', $args ), $url ) : $url;
}

/**
 * Header account button: depends on login state.
 * Logged out → Sign In (to login, returns to /parent/).
 * Logged in  → My Account (straight to the parent dashboard, S13).
 * Returns [ 'label' => string, 'url' => string ].
 */
function mgk_site_account_link() {
    if ( is_user_logged_in() ) {
        return [
            'label' => mgk_site_setting( 'header_account_label' ) ?: 'My Account',
            'url'   => mgk_cta_url( 'dashboard' ),
        ];
    }
    return [
        'label' => mgk_site_setting( 'header_signin_label' ) ?: 'Sign In',
        'url'   => mgk_cta_url( 'signin' ),
    ];
}

function mgk_normalize_listing_params( $params = [] ) {
    $normalized = [];

    foreach ( (array) $params as $key => $value ) {
        if ( $value === '' || $value === null ) {
            continue;
        }

        $key = sanitize_key( $key );
        if ( $key === 'location' ) {
            $key = 'area';
        }

        if ( is_array( $value ) ) {
            $clean = array_filter( array_map( 'sanitize_text_field', $value ) );
            if ( $clean ) {
                $normalized[ $key ] = implode( ',', $clean );
            }
            continue;
        }

        $normalized[ $key ] = sanitize_text_field( (string) $value );
    }

    return $normalized;
}

function mgk_get_tutor_listing_url( $params = [] ) {
    return mgk_cta_url( 'browse', mgk_normalize_listing_params( $params ) );
}

function mgk_get_trial_url( $params = [] ) {
    return mgk_cta_url( 'trial', $params );
}

function mgk_subject_url( $subject, $args = [] ) {
    $query = array_merge( [ 'subject' => $subject ], $args );
    return mgk_get_tutor_listing_url( $query );
}

function mgk_teacher_profile_url( $tutor ) {
    $slug = is_array( $tutor ) ? ( $tutor['slug'] ?? sanitize_title( $tutor['name'] ?? '' ) ) : sanitize_title( (string) $tutor );
    return mgk_url( '/teacher/' . $slug . '/' );
}

function mgk_button( $args ) {
    $label = $args['label'] ?? 'Continue';
    $url = $args['url'] ?? '#';
    $variant = $args['variant'] ?? 'accent';
    $event = $args['event'] ?? '';
    $classes = trim( 'mgk-btn mgk-btn-' . sanitize_html_class( $variant ) . ' ' . ( $args['class'] ?? '' ) );

    return sprintf(
        '<a class="%1$s" href="%2$s"%3$s>%4$s</a>',
        esc_attr( $classes ),
        esc_url( $url ),
        $event ? ' data-mgk-event="' . esc_attr( $event ) . '"' : '',
        esc_html( $label )
    );
}

function mgk_demo( $key, $default = [] ) {
    $data = function_exists( 'mgk_demo_data' ) ? mgk_demo_data() : [];
    return $data[ $key ] ?? $default;
}

function mgk_attrs( $attrs ) {
    $html = '';
    foreach ( $attrs as $key => $value ) {
        if ( $value === null || $value === false ) {
            continue;
        }
        $html .= ' ' . esc_attr( $key ) . '="' . esc_attr( (string) $value ) . '"';
    }
    return $html;
}

function mgk_render_section_heading( $title, $eyebrow = '', $body = '' ) {
    ?>
    <div class="mgk-section-head">
        <?php if ( $eyebrow ) : ?><p class="mgk-eyebrow"><?php echo esc_html( $eyebrow ); ?></p><?php endif; ?>
        <h2><?php echo esc_html( $title ); ?></h2>
        <?php if ( $body ) : ?><p><?php echo esc_html( $body ); ?></p><?php endif; ?>
    </div>
    <?php
}

function mgk_get_query_filter( $key, $default = '' ) {
    $value = isset( $_GET[ $key ] ) ? wp_unslash( $_GET[ $key ] ) : $default;
    if ( is_array( $value ) ) {
        $value = implode( ',', array_filter( array_map( 'sanitize_text_field', $value ) ) );
    }
    return sanitize_text_field( (string) $value );
}

function mgk_get_query_values( $key ) {
    if ( ! isset( $_GET[ $key ] ) ) {
        return [];
    }

    $raw = wp_unslash( $_GET[ $key ] );
    $values = is_array( $raw ) ? $raw : explode( ',', (string) $raw );
    return array_values( array_filter( array_map( 'sanitize_text_field', $values ), function ( $value ) {
        return $value !== '';
    } ) );
}

function mgk_listing_filters() {
    $area = mgk_get_query_filter( 'area', mgk_get_query_filter( 'location', '' ) );

    return [
        'subject'  => mgk_get_query_filter( 'subject', '' ),
        'level'    => mgk_get_query_filter( 'level', '' ),
        'area'     => $area,
        'location' => $area,
        'budget'   => mgk_get_query_filter( 'budget', '' ),
        'budget_min' => mgk_get_query_filter( 'budget_min', '' ),
        'budget_max' => mgk_get_query_filter( 'budget_max', '' ),
        'tier'     => mgk_get_query_filter( 'tier', '' ),
        'rating'   => mgk_get_query_filter( 'rating', '' ),
        'availability' => mgk_get_query_filter( 'availability', '' ),
        'online'   => mgk_get_query_filter( 'online', '' ),
        'other'    => mgk_get_query_filter( 'other', '' ),
        'sort'     => mgk_get_query_filter( 'sort', 'best-match' ),
        'page'     => max( 1, (int) mgk_get_query_filter( 'tutor_page', mgk_get_query_filter( 'page', '1' ) ) ),
    ];
}

function mgk_filter_values( $filters, $key ) {
    $value = $filters[ $key ] ?? '';
    if ( is_array( $value ) ) {
        $values = array_map( 'sanitize_text_field', $value );
    } else {
        $values = array_map( 'trim', explode( ',', (string) $value ) );
    }

    return array_values( array_filter( $values, function ( $item ) {
        return trim( (string) $item ) !== '';
    } ) );
}

function mgk_value_matches_any( $needle_values, $haystack_values ) {
    $needles = array_map( 'strtolower', array_filter( (array) $needle_values, function ( $value ) {
        return $value !== '' && strtolower( (string) $value ) !== 'any';
    } ) );

    if ( ! $needles ) {
        return true;
    }

    $haystack = array_map( 'strtolower', (array) $haystack_values );
    foreach ( $needles as $needle ) {
        foreach ( $haystack as $item ) {
            if ( $needle === $item || strpos( $item, $needle ) !== false || strpos( $needle, $item ) !== false ) {
                return true;
            }
        }
    }

    return false;
}

function mgk_expand_level_filters( $levels ) {
    $expanded = [];
    foreach ( (array) $levels as $level ) {
        $expanded[] = $level;
        $lower = strtolower( (string) $level );
        if ( strpos( $lower, 'sec' ) === 0 ) {
            $expanded[] = 'Secondary';
        }
        if ( strpos( $lower, 'jc' ) === 0 ) {
            $expanded[] = 'JC';
        }
        if ( preg_match_all( '/p([1-6])/', $lower, $matches ) ) {
            foreach ( $matches[1] as $primary_level ) {
                $expanded[] = 'P' . $primary_level;
            }
            $expanded[] = 'Primary';
        }
    }
    return array_values( array_unique( array_filter( $expanded ) ) );
}

function mgk_budget_range_from_filter( $filters ) {
    $min = isset( $filters['budget_min'] ) && $filters['budget_min'] !== '' ? (int) $filters['budget_min'] : null;
    $max = isset( $filters['budget_max'] ) && $filters['budget_max'] !== '' ? (int) $filters['budget_max'] : null;

    if ( ( $min === null || $max === null ) && ! empty( $filters['budget'] ) && preg_match( '/(\d+)\D+(\d+)/', (string) $filters['budget'], $matches ) ) {
        $min = $min ?? (int) $matches[1];
        $max = $max ?? (int) $matches[2];
    }

    return [ $min, $max ];
}

function mgk_budget_label( $filters ) {
    if ( ! empty( $filters['budget'] ) ) {
        return $filters['budget'];
    }

    [ $min, $max ] = mgk_budget_range_from_filter( $filters );
    if ( $min !== null || $max !== null ) {
        return '$' . ( $min ?? '0' ) . '-$' . ( $max ?? 'any' ) . '/hr';
    }

    return 'Any budget';
}

function mgk_sort_tutors( $tutors, $sort ) {
    $sort = sanitize_key( $sort ?: 'best-match' );

    usort( $tutors, function ( $a, $b ) use ( $sort ) {
        if ( $sort === 'rating' ) {
            return (float) $b['rating'] <=> (float) $a['rating'];
        }
        if ( $sort === 'reviews' ) {
            return (int) $b['reviews'] <=> (int) $a['reviews'];
        }
        if ( $sort === 'price-low' ) {
            return (int) $a['rate_num'] <=> (int) $b['rate_num'];
        }
        if ( $sort === 'price-high' ) {
            return (int) $b['rate_num'] <=> (int) $a['rate_num'];
        }
        if ( $sort === 'fastest' ) {
            return (int) $a['response'] <=> (int) $b['response'];
        }
        return 0;
    } );

    return $tutors;
}

function mgk_filter_tutors( $filters ) {
    $tutors = mgk_get_tutors_from_db();

    $subjects = mgk_filter_values( $filters, 'subject' );
    $levels = mgk_expand_level_filters( mgk_filter_values( $filters, 'level' ) );
    $areas = mgk_filter_values( $filters, 'area' );
    $tiers = mgk_filter_values( $filters, 'tier' );
    $rating = mgk_get_rating_threshold( $filters['rating'] ?? '' );
    [ $budget_min, $budget_max ] = mgk_budget_range_from_filter( $filters );

    $filtered = array_values( array_filter( $tutors, function ( $tutor ) use ( $subjects, $levels, $areas, $tiers, $rating, $budget_min, $budget_max ) {
        if ( ! mgk_value_matches_any( $subjects, $tutor['subjects'] ?? [] ) ) {
            return false;
        }
        if ( ! mgk_value_matches_any( $levels, $tutor['levels'] ?? [] ) ) {
            return false;
        }
        if ( ! mgk_value_matches_any( $areas, $tutor['locations'] ?? [] ) ) {
            return false;
        }
        if ( ! mgk_value_matches_any( $tiers, [ $tutor['tier'] ?? '' ] ) ) {
            return false;
        }
        if ( $rating && (float) ( $tutor['rating'] ?? 0 ) < $rating ) {
            return false;
        }
        if ( $budget_min !== null && (int) $tutor['rate_num'] < $budget_min ) {
            return false;
        }
        if ( $budget_max !== null && (int) $tutor['rate_num'] > $budget_max ) {
            return false;
        }
        return true;
    } ) );

    return mgk_sort_tutors( $filtered, $filters['sort'] ?? 'best-match' );
}

function mgk_get_rating_threshold( $rating ) {
    if ( ! $rating ) {
        return 0;
    }
    if ( preg_match( '/(\d+(?:\.\d+)?)/', (string) $rating, $matches ) ) {
        return (float) $matches[1];
    }
    return 0;
}

function mgk_active_filter_chips( $filters ) {
    $chips = [];
    $labels = [
        'subject'  => 'Subject',
        'level'    => 'Level',
        'area'     => 'Area',
        'budget'   => 'Budget',
        'tier'     => 'Tutor tier',
        'rating'   => 'Rating',
        'availability' => 'Availability',
        'online'   => 'Online',
        'other'    => 'Other',
    ];

    foreach ( $labels as $key => $label ) {
        if ( ! empty( $filters[ $key ] ) ) {
            foreach ( mgk_filter_values( $filters, $key ) as $value ) {
                if ( trim( (string) $value ) === '' ) {
                    continue;
                }
                $chips[] = [
                    'key'   => $key,
                    'label' => $label,
                    'value' => $value,
                ];
            }
        }
    }

    if ( empty( $filters['budget'] ) && ( ! empty( $filters['budget_min'] ) || ! empty( $filters['budget_max'] ) ) ) {
        $chips[] = [
            'key'   => 'budget_range',
            'label' => 'Budget',
            'value' => '$' . ( $filters['budget_min'] ?: '0' ) . '-$' . ( $filters['budget_max'] ?: 'any' ) . '/hr',
        ];
    }

    return $chips;
}

function mgk_current_query_args() {
    $params = [];
    foreach ( $_GET as $key => $value ) {
        $key = sanitize_key( $key );
        if ( is_array( $value ) ) {
            $clean = array_filter( array_map( 'sanitize_text_field', wp_unslash( $value ) ) );
            if ( $clean ) {
                $params[ $key ] = implode( ',', $clean );
            }
            continue;
        }
        if ( is_scalar( $value ) ) {
            $params[ $key ] = sanitize_text_field( wp_unslash( (string) $value ) );
        }
    }
    return $params;
}

function mgk_filter_remove_url( $remove_key, $remove_value = null ) {
    $params = mgk_current_query_args();
    unset( $params['tutor_page'], $params['page'] );
    if ( $remove_key === 'budget_range' ) {
        unset( $params['budget_min'], $params['budget_max'], $params['budget'] );
    } else {
        if ( $remove_value !== null && isset( $params[ $remove_key ] ) ) {
            $remove_value = strtolower( sanitize_text_field( (string) $remove_value ) );
            $next = array_values( array_filter( explode( ',', (string) $params[ $remove_key ] ), function ( $value ) use ( $remove_value ) {
                return strtolower( trim( $value ) ) !== $remove_value;
            } ) );

            if ( $next ) {
                $params[ $remove_key ] = implode( ',', $next );
            } else {
                unset( $params[ $remove_key ] );
            }
        } else {
            unset( $params[ $remove_key ] );
        }
        if ( $remove_key === 'area' && empty( $params['area'] ) ) {
            unset( $params['location'] );
        }
        if ( $remove_key === 'budget' ) {
            unset( $params['budget_min'], $params['budget_max'] );
        }
    }
    return add_query_arg( $params, mgk_get_tutor_listing_url() );
}

function mgk_filter_toggle_url( $key, $value, $filters = [] ) {
    $key = sanitize_key( $key );
    if ( $key === 'location' ) {
        $key = 'area';
    }
    $value = sanitize_text_field( (string) $value );
    $params = mgk_current_query_args();
    unset( $params['location'], $params['tutor_page'], $params['page'] );
    if ( $key === 'budget' ) {
        unset( $params['budget_min'], $params['budget_max'] );
    }

    $active = mgk_filter_values( $filters, $key );
    $active_lower = array_map( 'strtolower', $active );
    $value_lower = strtolower( $value );

    if ( in_array( $value_lower, $active_lower, true ) ) {
        $next = array_values( array_filter( $active, function ( $item ) use ( $value_lower ) {
            return strtolower( $item ) !== $value_lower;
        } ) );
    } else {
        $next = array_merge( $active, [ $value ] );
    }

    if ( $next ) {
        $params[ $key ] = implode( ',', $next );
    } else {
        unset( $params[ $key ] );
    }

    return add_query_arg( $params, mgk_get_tutor_listing_url() );
}

function mgk_filter_key_for_group( $group ) {
    $map = [
        'Level'      => 'level',
        'Subject'    => 'subject',
        'Budget'     => 'budget',
        'Location'   => 'area',
        'Tutor Tier' => 'tier',
        'Rating'     => 'rating',
        'Other'      => 'other',
    ];
    return $map[ $group ] ?? '';
}

function mgk_filter_value_from_item( $item ) {
    return trim( preg_replace( '/\s*\(.+\)$/', '', (string) $item ) );
}

function mgk_is_filter_active( $filters, $key, $value ) {
    return in_array( strtolower( (string) $value ), array_map( 'strtolower', mgk_filter_values( $filters, $key ) ), true );
}

function mgk_get_tutors( $filters = [] ) {
    return mgk_filter_tutors( array_merge( mgk_listing_filters(), (array) $filters ) );
}

function mgk_get_featured_tutors( $limit = 8 ) {
    return array_slice( mgk_get_tutors_from_db(), 0, max( 1, (int) $limit ) );
}

function mgk_get_tutor( $id_or_slug = '' ) {
    return mgk_profile_tutor( sanitize_title( (string) $id_or_slug ) );
}

function mgk_get_subjects() {
    $catalog = mgk_get_subject_catalog();
    $subjects = $catalog['subject_overview'] ?? [];

    return $subjects ? array_slice( $subjects, 0, 11 ) : mgk_demo( 'subjects', [] );
}

function mgk_subject_level_groups() {
    return [
        'Preschool' => [
            'title'   => 'Preschool (Ages 3-6)',
            'level'   => 'Preschool',
            'aliases' => [ 'preschool', 'nursery', 'k1', 'k2', 'kindergarten', 'ages 3-6', 'p1 prep' ],
        ],
        'Primary' => [
            'title'   => 'Primary (P1-P6) · PSLE',
            'level'   => 'Primary',
            'aliases' => [ 'primary', 'p1', 'p2', 'p3', 'p4', 'p5', 'p6', 'psle' ],
        ],
        'Secondary' => [
            'title'   => 'Secondary (Sec 1-4) · N/O-Level',
            'level'   => 'Secondary',
            'aliases' => [ 'secondary', 'sec', 'sec 1', 'sec 2', 'sec 3', 'sec 4', 'o-level', 'n-level', 'olevel', 'nlevel', 'a-math', 'e-math' ],
        ],
        'JC' => [
            'title'   => 'JC (JC1-JC2) · A-Level',
            'level'   => 'JC',
            'aliases' => [ 'jc', 'jc1', 'jc2', 'a-level', 'alevel', 'h1', 'h2', 'gp', 'pw', 'ki' ],
        ],
        'IB' => [
            'title'   => 'IB / IGCSE / International',
            'level'   => 'IB',
            'aliases' => [ 'ib', 'igcse', 'international', 'dp', 'tok', 'ee', 'sat', 'act', 'ielts', 'toefl', 'cbse', 'ap' ],
        ],
    ];
}

function mgk_split_teacher_values( $value ) {
    if ( is_array( $value ) ) {
        $items = [];
        foreach ( $value as $entry ) {
            $items = array_merge( $items, mgk_split_teacher_values( $entry ) );
        }
        return array_values( array_unique( $items ) );
    }

    $value = maybe_unserialize( $value );
    if ( is_array( $value ) ) {
        return mgk_split_teacher_values( $value );
    }

    $value = trim( wp_strip_all_tags( (string) $value ) );
    if ( $value === '' ) {
        return [];
    }

    if ( in_array( $value[0], [ '[', '{' ], true ) ) {
        $decoded = json_decode( $value, true );
        if ( json_last_error() === JSON_ERROR_NONE ) {
            return mgk_split_teacher_values( $decoded );
        }
    }

    $parts = preg_split( '/[,;\|\n\r]+/', $value );
    $parts = array_map( function ( $item ) {
        return trim( preg_replace( '/\s+/', ' ', (string) $item ) );
    }, $parts );

    return array_values( array_unique( array_filter( $parts ) ) );
}

function mgk_teacher_meta_values( $post_id, $keys ) {
    $values = [];
    foreach ( $keys as $key ) {
        $meta = get_post_meta( $post_id, $key, false );
        foreach ( $meta as $item ) {
            $values = array_merge( $values, mgk_split_teacher_values( $item ) );
        }
    }

    return array_values( array_unique( array_filter( $values ) ) );
}

function mgk_teacher_term_values( $post_id, $taxonomy ) {
    if ( ! taxonomy_exists( $taxonomy ) ) {
        return [];
    }

    $terms = wp_get_post_terms( $post_id, $taxonomy, [ 'fields' => 'names' ] );
    if ( is_wp_error( $terms ) ) {
        return [];
    }

    return array_values( array_unique( array_filter( array_map( 'sanitize_text_field', (array) $terms ) ) ) );
}

function mgk_match_level_group( $value ) {
    $value = strtolower( (string) $value );
    foreach ( mgk_subject_level_groups() as $group => $config ) {
        foreach ( $config['aliases'] as $alias ) {
            if ( $value === $alias || strpos( $value, $alias ) !== false ) {
                return $group;
            }
        }
    }

    return '';
}

function mgk_infer_level_groups_from_subjects( $subjects ) {
    $groups = [];
    foreach ( (array) $subjects as $subject ) {
        $group = mgk_match_level_group( $subject );
        if ( $group ) {
            $groups[] = $group;
            continue;
        }

        $lower = strtolower( (string) $subject );
        if ( preg_match( '/\bphonics|reading|numeracy\b/', $lower ) ) {
            $groups[] = 'Preschool';
        } elseif ( preg_match( '/\bpsle|p[1-6]|higher mt|tamil|malay|hindi\b/', $lower ) ) {
            $groups[] = 'Primary';
        } elseif ( preg_match( '/\ba-math|e-math|chemistry|physics|biology|history|geography\b/', $lower ) ) {
            $groups[] = 'Secondary';
        } elseif ( preg_match( '/\bh[12]|gp|econs|pw|ki\b/', $lower ) ) {
            $groups[] = 'JC';
        } elseif ( preg_match( '/\bib|igcse|tok|ee\b/', $lower ) ) {
            $groups[] = 'IB';
        }
    }

    return array_values( array_unique( $groups ) );
}

function mgk_subject_display_name( $subject ) {
    $subject = trim( preg_replace( '/\s+/', ' ', (string) $subject ) );
    $subject = preg_replace( '/^(?:p[1-6](?:\s*-\s*p[1-6])?|primary|psle|sec(?:ondary)?\s*[1-4]?(?:\s*-\s*[1-4])?|o-?level|n-?level)\s+/i', '', $subject );
    $subject = trim( preg_replace( '/\s+/', ' ', (string) $subject ) );

    $map = [
        'h2 chem' => 'H2 Chem',
        'h2 math' => 'H2 Math',
        'h2 physics' => 'H2 Physics',
        'h2 bio' => 'H2 Bio',
        'h2 biology' => 'H2 Bio',
        'h2 econs' => 'H2 Econs',
        'h2 economics' => 'H2 Econs',
        'ib math aa' => 'IB Math AA',
        'ib math ai' => 'IB Math AI',
        'ib english' => 'IB English',
        'ib eng l&l' => 'IB Eng L&L',
        'ib chem hl' => 'IB Chem HL',
        'ib physics' => 'IB Physics',
        'a-math' => 'A-Math',
        'e-math' => 'E-Math',
        'emath' => 'E-Math',
        'amath' => 'A-Math',
        'mt' => 'Mother Tongue',
        'higher mt' => 'Higher MT',
    ];
    $key = strtolower( $subject );

    return $map[ $key ] ?? $subject;
}

function mgk_subject_sort_weight( $subject ) {
    $order = [
        'English', 'Math', 'Chinese', 'Science', 'Higher Chinese', 'Higher MT', 'PSLE Math', 'PSLE Science',
        'E-Math', 'A-Math', 'Chemistry', 'Physics', 'Biology', 'History', 'Geography',
        'H2 Math', 'H2 Chem', 'H2 Physics', 'H2 Bio', 'H2 Econs', 'GP',
        'IB Math AA', 'IB Math AI', 'IB English', 'IB Physics', 'IGCSE Math', 'IGCSE Eng',
    ];
    $index = array_search( $subject, $order, true );

    return $index === false ? 999 : $index;
}

function mgk_teacher_subject_group_map( $subjects, $levels ) {
    $raw_subjects = mgk_split_teacher_values( $subjects );
    $base_groups = array_values( array_unique( array_filter( array_map( 'mgk_match_level_group', mgk_split_teacher_values( $levels ) ) ) ) );
    $map = [];

    foreach ( $raw_subjects as $raw_subject ) {
        $display_subject = mgk_subject_display_name( $raw_subject );
        if ( ! $display_subject ) {
            continue;
        }

        $explicit_group = mgk_match_level_group( $raw_subject );
        $subject_groups = $explicit_group ? [ $explicit_group ] : $base_groups;

        if ( ! $subject_groups ) {
            $subject_groups = mgk_infer_level_groups_from_subjects( [ $raw_subject ] );
        }

        if ( ! $subject_groups ) {
            continue;
        }

        $map[ $display_subject ] = array_values( array_unique( array_merge( $map[ $display_subject ] ?? [], $subject_groups ) ) );
    }

    return $map;
}

function mgk_db_teacher_subject_rows() {
    $posts = get_posts( [
        'post_type'      => 'mg_teacher',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'no_found_rows'  => true,
    ] );

    $rows = [];
    foreach ( $posts as $post_id ) {
        $slug = get_post_field( 'post_name', $post_id );
        $title = get_the_title( $post_id );
        $subjects = mgk_teacher_term_values( $post_id, 'mgk_subject' );
        $levels = mgk_teacher_term_values( $post_id, 'mgk_level' );

        if ( ! $subjects ) {
            $subjects = mgk_teacher_meta_values( $post_id, [
                'subjects', 'subject', 'mgk_subjects', 'teaching_subjects', 'tutor_subjects', 'teacher_subjects', 'subject_names', 'subjects_taught',
            ] );
        }

        if ( ! $levels ) {
            $levels = mgk_teacher_meta_values( $post_id, [
                'levels', 'level', 'mgk_levels', 'teaching_levels', 'tutor_levels', 'teacher_levels', 'education_levels',
            ] );
        }

        if ( ! $subjects || ! $levels ) {
            $profile = mgk_profile_tutor( $slug );
            if ( $profile ) {
                $subjects = $subjects ?: ( $profile['subjects'] ?? [] );
                $levels = $levels ?: ( $profile['levels'] ?? [] );
            }
        }

        if ( ! $subjects || ! $levels ) {
            foreach ( mgk_demo( 'listing_tutors', [] ) as $demo_tutor ) {
                if ( sanitize_title( $demo_tutor['name'] ?? '' ) === $slug || strcasecmp( $demo_tutor['name'] ?? '', $title ) === 0 ) {
                    $subjects = $subjects ?: ( $demo_tutor['subjects'] ?? [] );
                    $levels = $levels ?: ( $demo_tutor['levels'] ?? [] );
                    break;
                }
            }
        }

        $subject_groups = mgk_teacher_subject_group_map( $subjects, $levels );
        $subjects = array_keys( $subject_groups );
        $groups = $subject_groups ? array_values( array_unique( array_merge( ...array_values( $subject_groups ) ) ) ) : [];

        if ( $subjects && $groups ) {
            $rows[] = [
                'id'       => (int) $post_id,
                'slug'     => $slug,
                'subjects' => $subjects,
                'groups'   => $groups,
                'subject_groups' => $subject_groups,
            ];
        }
    }

    if ( ! $rows && ! $posts ) {
        foreach ( mgk_demo( 'listing_tutors', [] ) as $index => $demo_tutor ) {
            $subject_groups = mgk_teacher_subject_group_map( $demo_tutor['subjects'] ?? [], $demo_tutor['levels'] ?? [] );
            $subjects = array_keys( $subject_groups );
            $groups = $subject_groups ? array_values( array_unique( array_merge( ...array_values( $subject_groups ) ) ) ) : [];
            $rows[] = [
                'id'       => -1 * ( $index + 1 ),
                'slug'     => sanitize_title( $demo_tutor['name'] ?? 'demo-tutor-' . $index ),
                'subjects' => $subjects,
                'groups'   => $groups,
                'subject_groups' => $subject_groups,
            ];
        }
    }

    return $rows;
}

function mgk_build_subject_cards_from_counts( $subject_teachers ) {
    $cards = [];
    foreach ( $subject_teachers as $subject => $teacher_ids ) {
        $count = count( $teacher_ids );
        if ( $count < 1 ) {
            continue;
        }
        $cards[] = [
            'name'  => $subject,
            'count' => (string) $count,
            'query' => [ 'subject' => $subject ],
        ];
    }

    usort( $cards, function ( $a, $b ) {
        $weight = mgk_subject_sort_weight( $a['name'] ) <=> mgk_subject_sort_weight( $b['name'] );
        if ( $weight !== 0 ) {
            return $weight;
        }
        return strcasecmp( $a['name'], $b['name'] );
    } );

    return $cards;
}

function mgk_get_subject_catalog() {
    $rows = mgk_db_teacher_subject_rows();
    $groups = mgk_subject_level_groups();
    $group_subjects = [];
    $group_teacher_ids = [];
    $all_subjects = [];

    foreach ( array_keys( $groups ) as $group ) {
        $group_subjects[ $group ] = [];
        $group_teacher_ids[ $group ] = [];
    }

    foreach ( $rows as $row ) {
        $subject_groups = $row['subject_groups'] ?? [];

        if ( ! $subject_groups ) {
            foreach ( $row['subjects'] ?? [] as $subject ) {
                $subject_groups[ $subject ] = $row['groups'] ?? [];
            }
        }

        foreach ( $subject_groups as $subject => $subject_group_list ) {
            foreach ( $subject_group_list as $group ) {
                if ( ! isset( $group_subjects[ $group ] ) ) {
                    continue;
                }
                $group_teacher_ids[ $group ][ $row['id'] ] = true;
                $group_subjects[ $group ][ $subject ][ $row['id'] ] = true;
                $all_subjects[ $subject ][ $row['id'] ] = true;
            }
        }
    }

    $levels = [];
    foreach ( $groups as $group => $config ) {
        $levels[] = [
            'title'    => $config['title'],
            'meta'     => count( $group_teacher_ids[ $group ] ) . ' tutors',
            'level'    => $config['level'],
            'subjects' => mgk_build_subject_cards_from_counts( $group_subjects[ $group ] ),
        ];
    }

    $exam_defs = [
        [ 'name' => 'PSLE', 'description' => 'Primary School Leaving Exam', 'subjects' => 'English · Math · Science · MT', 'group' => 'Primary', 'query' => [ 'level' => 'Primary' ] ],
        [ 'name' => 'O-Level', 'description' => 'GCE Ordinary Level', 'subjects' => 'A-Math, E-Math, Sci, Lang', 'group' => 'Secondary', 'query' => [ 'level' => 'Secondary' ] ],
        [ 'name' => 'A-Level', 'description' => 'GCE Advanced Level', 'subjects' => 'H2 subjects, GP, PW', 'group' => 'JC', 'query' => [ 'level' => 'JC' ] ],
        [ 'name' => 'IB', 'description' => 'International Baccalaureate', 'subjects' => 'DP subjects, TOK, EE', 'group' => 'IB', 'query' => [ 'level' => 'IB' ] ],
    ];
    $exams = array_map( function ( $exam ) use ( $group_teacher_ids ) {
        $count = count( $group_teacher_ids[ $exam['group'] ] ?? [] );
        unset( $exam['group'] );
        $exam['count'] = $count . ' tutors';
        return $exam;
    }, $exam_defs );

    $catalog = mgk_demo( 'subject_catalog', [] );
    $overview = mgk_build_subject_cards_from_counts( $all_subjects );
    foreach ( $overview as &$subject ) {
        $subject['icon'] = strtoupper( substr( preg_replace( '/[^A-Za-z0-9]/', '', $subject['name'] ), 0, 2 ) );
        $subject['count'] .= ' tutors';
    }
    unset( $subject );

    $catalog['levels'] = $levels;
    $catalog['exams'] = $exams;
    $catalog['subject_overview'] = $overview;
    $catalog['popular'] = array_slice( array_column( $overview, 'name' ), 0, 5 );

    return $catalog;
}

function mgk_get_reviews() {
    return mgk_demo( 'reviews', [] );
}

function mgk_get_teacher_reviews( $teacher_id, $limit = 20 ) {
    $teacher_id = (int) $teacher_id;
    if ( ! $teacher_id || ! post_type_exists( 'mg_review' ) ) {
        return [];
    }

    $posts = get_posts( [
        'post_type'      => 'mg_review',
        'post_status'    => 'publish',
        'posts_per_page' => $limit,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'meta_query'     => [
            [
                'key'     => 'mgk_review_teacher_id',
                'value'   => $teacher_id,
                'compare' => '=',
            ],
        ],
    ] );

    return array_map( function ( $post ) {
        $id = $post->ID;
        $rating = (float) get_post_meta( $id, 'mgk_review_rating', true );

        return [
            'id'            => $id,
            'name'          => get_post_meta( $id, 'mgk_review_parent_name', true ) ?: get_the_title( $id ),
            'rating'        => $rating > 0 ? $rating : 5.0,
            'meta'          => get_post_meta( $id, 'mgk_review_meta', true ) ?: 'Verified completed lesson',
            'copy'          => wp_strip_all_tags( $post->post_content ),
            'verified'      => (bool) get_post_meta( $id, 'mgk_review_verified', true ),
            'teaching'      => (float) get_post_meta( $id, 'mgk_review_teaching', true ),
            'patience'      => (float) get_post_meta( $id, 'mgk_review_patience', true ),
            'punctuality'   => (float) get_post_meta( $id, 'mgk_review_punctuality', true ),
            'communication' => (float) get_post_meta( $id, 'mgk_review_communication', true ),
            'tags'          => array_filter( array_map( 'trim', explode( ',', (string) get_post_meta( $id, 'mgk_review_tags', true ) ) ) ),
            'photos'        => (int) get_post_meta( $id, 'mgk_review_photo_count', true ),
        ];
    }, $posts );
}

function mgk_summarize_teacher_reviews( array $reviews ) {
    $count = count( $reviews );
    $breakdown = [ '5' => 0, '4' => 0, '3' => 0, '2' => 0, '1' => 0 ];
    $scores = [
        'teaching'      => [],
        'patience'      => [],
        'punctuality'   => [],
        'communication' => [],
    ];

    foreach ( $reviews as $review ) {
        $rating = max( 1, min( 5, (float) ( $review['rating'] ?? 0 ) ) );
        $bucket = (string) max( 1, min( 5, (int) floor( $rating ) ) );
        $breakdown[ $bucket ]++;

        foreach ( $scores as $key => $values ) {
            $value = (float) ( $review[ $key ] ?? 0 );
            if ( $value > 0 ) {
                $scores[ $key ][] = $value;
            }
        }
    }

    $average = $count ? array_sum( array_column( $reviews, 'rating' ) ) / $count : 0;
    $avg_score = function ( $values ) {
        return $values ? round( array_sum( $values ) / count( $values ), 1 ) : 0;
    };

    return [
        'count'         => $count,
        'rating'        => $count ? round( $average, 1 ) : 0,
        'breakdown'     => $breakdown,
        'teaching'      => $avg_score( $scores['teaching'] ),
        'patience'      => $avg_score( $scores['patience'] ),
        'punctuality'   => $avg_score( $scores['punctuality'] ),
        'communication' => $avg_score( $scores['communication'] ),
    ];
}

function mgk_get_faqs( $group = 'faqs' ) {
    return mgk_demo( $group, [] );
}

function mgk_get_pricing_config() {
    return mgk_demo( 'pricing', [] );
}

function mgk_validate_sg_phone( $phone ) {
    return (bool) preg_match( '/^(?:\+65)?[689]\d{7}$/', preg_replace( '/\s+/', '', (string) $phone ) );
}

function mgk_validate_lead_payload( $payload ) {
    $errors = [];
    foreach ( [ 'parent_name', 'phone', 'level', 'subject' ] as $required ) {
        if ( empty( $payload[ $required ] ) ) {
            $errors[ $required ] = 'This field is required.';
        }
    }
    if ( ! empty( $payload['phone'] ) && ! mgk_validate_sg_phone( $payload['phone'] ) ) {
        $errors['phone'] = 'Use a valid Singapore mobile number.';
    }
    return $errors;
}

function mgk_create_lead( $payload ) {
    $errors = mgk_validate_lead_payload( $payload );
    if ( $errors ) {
        return new WP_Error( 'mgk_invalid_lead', 'Lead validation failed.', $errors );
    }

    if ( function_exists( 'mgk_booking_create_lead' ) ) {
        return mgk_booking_create_lead( $payload );
    }

    return [
        'id'     => 0,
        'token'  => wp_generate_password( 20, false, false ),
        'status' => 'captured',
    ];
}

function mgk_get_available_slots( $tutor_slug = '' ) {
    $tutor = mgk_profile_tutor( $tutor_slug );
    return $tutor['availability'] ?? [];
}

function mgk_hold_slot( $slot_id, $lead_id = 0 ) {
    if ( function_exists( 'mgk_booking_hold_slot' ) ) {
        return mgk_booking_hold_slot( $slot_id, (int) $lead_id );
    }
    return [ 'slot_id' => sanitize_text_field( (string) $slot_id ), 'lead_id' => (int) $lead_id, 'status' => 'held', 'expires_in' => 600 ];
}

function mgk_release_slot( $slot_id, $lead_id = 0 ) {
    if ( function_exists( 'mgk_booking_release_slot' ) ) {
        return mgk_booking_release_slot( $slot_id, (int) $lead_id );
    }
    return [ 'slot_id' => sanitize_text_field( (string) $slot_id ), 'status' => 'released' ];
}

function mgk_track_event( $event, $payload = [] ) {
    do_action( 'mgk_track_event', sanitize_key( $event ), (array) $payload );
}

function mgk_get_similar_tutors( $post_id, $limit = 3 ) {
    $subjects = wp_get_post_terms( $post_id, 'mgk_subject', [ 'fields' => 'ids' ] );
    $levels   = wp_get_post_terms( $post_id, 'mgk_level',   [ 'fields' => 'ids' ] );

    $subjects = is_array( $subjects ) ? $subjects : [];
    $levels   = is_array( $levels )   ? $levels   : [];

    $found = [];

    // First pass: same subject
    if ( $subjects ) {
        $found = get_posts( [
            'post_type'      => 'mg_teacher',
            'post_status'    => 'publish',
            'posts_per_page' => $limit + 2,
            'post__not_in'   => [ $post_id ],
            'orderby'        => 'rand',
            'tax_query'      => [ [ 'taxonomy' => 'mgk_subject', 'field' => 'term_id', 'terms' => $subjects, 'operator' => 'IN' ] ],
        ] );
    }

    // Second pass: same level (fill up if needed)
    if ( count( $found ) < $limit && $levels ) {
        $exclude = array_merge( [ $post_id ], wp_list_pluck( $found, 'ID' ) );
        $more = get_posts( [
            'post_type'      => 'mg_teacher',
            'post_status'    => 'publish',
            'posts_per_page' => $limit,
            'post__not_in'   => $exclude,
            'orderby'        => 'rand',
            'tax_query'      => [ [ 'taxonomy' => 'mgk_level', 'field' => 'term_id', 'terms' => $levels, 'operator' => 'IN' ] ],
        ] );
        $found = array_merge( $found, $more );
    }

    if ( count( $found ) < $limit ) {
        $exclude = array_merge( [ $post_id ], wp_list_pluck( $found, 'ID' ) );
        $more = get_posts( [
            'post_type'      => 'mg_teacher',
            'post_status'    => 'publish',
            'posts_per_page' => $limit - count( $found ),
            'post__not_in'   => $exclude,
            'orderby'        => 'rand',
        ] );
        $found = array_merge( $found, $more );
    }

    $result = [];
    foreach ( array_slice( $found, 0, $limit ) as $p ) {
        $rate_num  = (int) get_post_meta( $p->ID, 'mgk_rate_num', true );
        $rating = get_post_meta( $p->ID, 'mgk_rating', true ) ?: '0';
        if ( function_exists( 'mgk_get_teacher_reviews' ) && function_exists( 'mgk_summarize_teacher_reviews' ) ) {
            $review_summary = mgk_summarize_teacher_reviews( mgk_get_teacher_reviews( $p->ID ) );
            if ( ! empty( $review_summary['count'] ) ) {
                $rating = (string) $review_summary['rating'];
            }
        }
        $result[] = [
            'name'   => $p->post_title,
            'slug'   => $p->post_name,
            'rating' => $rating,
            'rate'   => $rate_num ? '$' . $rate_num . '/hr' : '',
            'photo'  => (string) ( get_the_post_thumbnail_url( $p->ID, 'medium' ) ?: '' ),
        ];
    }

    return $result;
}

function mgk_profile_tutor( $slug = '' ) {
    if ( ! $slug ) {
        $slug = get_post_field( 'post_name', get_queried_object_id() );
    }

    // Find the real WP post
    $posts = get_posts( [
        'post_type'   => 'mg_teacher',
        'post_status' => 'publish',
        'name'        => $slug,
        'numberposts' => 1,
    ] );

    if ( $posts ) {
        $post = $posts[0];
        $id   = $post->ID;

        // Helper: read ACF field or fall back to post_meta
        $f = function( $key ) use ( $id ) {
            return function_exists( 'get_field' ) ? get_field( $key, $id ) : get_post_meta( $id, $key, true );
        };

        $rate_num = (int) $f( 'mgk_rate_num' );
        $subjects = wp_get_post_terms( $id, 'mgk_subject', [ 'fields' => 'names' ] );
        $levels   = wp_get_post_terms( $id, 'mgk_level',   [ 'fields' => 'names' ] );
        $locations_raw = $f( 'mgk_locations' );
        $locations = is_array( $locations_raw ) ? $locations_raw : ( $locations_raw ? [ $locations_raw ] : [] );

        // Repeater fields — ACF returns array of rows; fall back to empty array
        $about_rows   = $f( 'mgk_about_paragraphs' ) ?: [];
        $about        = array_map( fn( $r ) => $r['content'] ?? '', $about_rows );

        $spec_rows    = $f( 'mgk_specializations' ) ?: [];
        $specs        = array_map( fn( $r ) => [ $r['specialty'] ?? '', $r['level'] ?? '' ], $spec_rows );

        $qual_rows    = $f( 'mgk_qualifications' ) ?: [];
        $quals        = array_map( fn( $r ) => [ $r['title'] ?? '', $r['description'] ?? '', $r['cert_id'] ?? '' ], $qual_rows );

        $track_rows   = $f( 'mgk_track_stats' ) ?: [];
        $track        = array_map( fn( $r ) => [ $r['value'] ?? '', $r['label'] ?? '' ], $track_rows );

        $pkg_rows     = $f( 'mgk_packages' ) ?: [];
        $packages     = array_map( fn( $r ) => [ $r['name'] ?? '', $r['price'] ?? '', $r['description'] ?? '', ! empty( $r['featured'] ) ], $pkg_rows );

        $faq_rows     = $f( 'mgk_faqs' ) ?: [];
        $faqs         = array_map( fn( $r ) => [ 'q' => $r['q'] ?? '', 'a' => $r['a'] ?? '' ], $faq_rows );

        $short_name   = $f( 'mgk_short_name' ) ?: preg_replace( '/^(Mr\.|Ms\.|Mrs\.|Dr\.)\s+/', '', $post->post_title );
        $db_reviews   = mgk_get_teacher_reviews( $id );
        $review_summary = mgk_summarize_teacher_reviews( $db_reviews );
        $rating_value = $review_summary['count'] ? (string) $review_summary['rating'] : ( (string) $f( 'mgk_rating' ) ?: '0' );
        $reviews_value = $review_summary['count'] ? (string) $review_summary['count'] : ( (string) $f( 'mgk_reviews' ) ?: '0' );

        // Availability — build day-keyed array with current week dates
        $avail_rows  = $f( 'mgk_availability' ) ?: [];
        $day_order   = [ 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun' ];
        $week_start  = strtotime( 'monday this week' );
        $week_dates  = [];
        foreach ( $day_order as $i => $d ) {
            $week_dates[ $d ] = (int) date( 'j', $week_start + $i * 86400 );
        }
        $availability     = [];
        $open_slots_count = 0;
        foreach ( $avail_rows as $row ) {
            $day   = $row['day']   ?? '';
            $raw   = $row['slots'] ?? '';
            $slots = $raw ? array_map( 'trim', explode( ',', $raw ) ) : [];
            $label = $day . ( isset( $week_dates[ $day ] ) ? ' ' . $week_dates[ $day ] : '' );
            $availability[ $label ] = $slots;
            $open_slots_count += count( $slots );
        }

        return [
            // Listing fields (also used in profile header)
            'id'           => $id,
            'name'         => $post->post_title,
            'slug'         => $post->post_name,
            'short_name'   => $short_name,
            'tier'         => (string) $f( 'mgk_tier' ),
            'experience'   => (string) $f( 'mgk_experience' ),
            'rating'       => $rating_value,
            'reviews'      => $reviews_value,
            'response'     => (string) $f( 'mgk_response' ),
            'rate'         => $rate_num ? '$' . $rate_num . '/hr' : '',
            'trial'        => (string) $f( 'mgk_trial_price' ),
            'subjects'     => is_array( $subjects ) ? $subjects : [],
            'levels'       => is_array( $levels )   ? $levels   : [],
            'locations'    => $locations,
            'bio'          => $post->post_excerpt ?: wp_strip_all_tags( $post->post_content ),
            // Phase 1: profile header
            'credential_badge'  => (string) $f( 'mgk_credential_badge' ),
            'languages'         => (string) $f( 'mgk_languages' ),
            'duration'          => (string) $f( 'mgk_duration' ),
            'active_students'   => (string) $f( 'mgk_active_students' ),
            'last_active'       => (string) $f( 'mgk_last_active' ) ?: '1d',
            'member_since'      => get_the_date( 'Y', $id ),
            'demo_video_url'    => (string) $f( 'mgk_demo_video_url' ),
            'philosophy'        => (string) $f( 'mgk_philosophy' ),
            // Phase 2: rich content
            'about'             => $about,
            'specializations'   => $specs,
            'qualifications'    => $quals,
            'track'             => $track,
            'packages'          => $packages,
            'faqs'              => $faqs,
            // Phase 3a: availability
            'availability'      => $availability,
            'open_slots'        => (string) $open_slots_count,
            // Phase 3b: similar tutors (auto-computed)
            'similar'           => mgk_get_similar_tutors( $id ),
            // Photo — WordPress featured image
            'photo'             => (string) ( get_the_post_thumbnail_url( $id, 'large' ) ?: '' ),
        ];
    }

    // Fallback to demo profile data
    $profiles = mgk_demo( 'profile_tutors', [] );
    if ( isset( $profiles[ $slug ] ) ) {
        return $profiles[ $slug ];
    }

    return null;
}
