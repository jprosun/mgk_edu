<?php
/**
 * S02 Tutor Listing — single-source renderer, split into editable SUB-SECTIONS.
 *
 * The listing is assembled from 6 parts, each with its own render function +
 * [mgk_listing_*] shortcode + Elementor widget, so owners can drag / reorder /
 * hide / style each block independently in the builder:
 *
 *   1. search   — [mgk_listing_search]    Search bar (subject/level/area/budget + Update)
 *   2. toolbar  — [mgk_listing_toolbar]   "N tutors found" + Sort + Grid/List + filter button
 *   3. filters  — [mgk_listing_filters]   Filter sidebar (Apply / Clear + groups) + mobile drawer
 *   4. results  — [mgk_listing_results]   Results GRID + pagination + compare drawer   ← LOCKED (data)
 *   5. promo    — [mgk_listing_promo]     Ad / promo banner
 *   6. related  — [mgk_listing_related]   Related searches
 *
 * State stays consistent because EVERY part derives from the SAME source — the
 * URL query ($_GET, via mgk_listing_filters()). The query itself runs ONCE per
 * request (memoized in mgk_listing_query()), so splitting the chrome into separate
 * widgets cannot make the filter/pagination state drift. The results GRID + query
 * + pagination logic stay locked in PHP; only display labels are att-editable and
 * only chrome is restyled in Elementor.
 *
 * [mgk_tutor_listing] (the original composite) is KEPT so existing seeds / pages
 * that used the single widget still render identically.
 *
 * @package mgk-edu-elementor
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Run the listing query ONCE per request and memoize. Every part-renderer reads
 * from this so they always agree on filters / counts / page / slice.
 *
 * @return array {filters, tutors, total_count, per_page, total_pages,
 *                current_page, offset, page_tutors, showing_from, showing_to,
 *                pagination_url (callable), pagination_items}
 */
function mgk_listing_query() {
    static $cache = null;
    if ( $cache !== null ) {
        return $cache;
    }

    $filters      = mgk_listing_filters();
    $tutors       = mgk_filter_tutors( $filters );
    $total_count  = count( $tutors );
    $per_page     = 6;
    $total_pages  = max( 1, (int) ceil( $total_count / $per_page ) );
    $current_page = min( max( 1, (int) ( $filters['page'] ?? 1 ) ), $total_pages );
    $offset       = ( $current_page - 1 ) * $per_page;
    $page_tutors  = array_slice( $tutors, $offset, $per_page );
    $showing_from = $total_count ? $offset + 1 : 0;
    $showing_to   = $total_count ? min( $offset + count( $page_tutors ), $total_count ) : 0;

    $pagination_args = mgk_current_query_args();
    unset( $pagination_args['page'], $pagination_args['tutor_page'] );
    $pagination_url = function ( $page ) use ( $pagination_args ) {
        $args = $pagination_args;
        if ( (int) $page > 1 ) {
            $args['tutor_page'] = (int) $page;
        }
        return add_query_arg( $args, mgk_get_tutor_listing_url() );
    };

    $pagination_items = [];
    if ( $total_pages <= 7 ) {
        $pagination_items = range( 1, $total_pages );
    } else {
        $pagination_items = [ 1 ];
        if ( $current_page > 4 ) {
            $pagination_items[] = '...';
        }
        for ( $page = max( 2, $current_page - 1 ); $page <= min( $total_pages - 1, $current_page + 1 ); $page++ ) {
            $pagination_items[] = $page;
        }
        if ( $current_page < $total_pages - 3 ) {
            $pagination_items[] = '...';
        }
        $pagination_items[] = $total_pages;
    }

    return $cache = compact(
        'filters', 'tutors', 'total_count', 'per_page', 'total_pages',
        'current_page', 'offset', 'page_tutors', 'showing_from', 'showing_to',
        'pagination_url', 'pagination_items'
    );
}

/** Small label resolver: $atts[$key] if non-empty, else $default. */
function mgk_listing_label( array $atts, $key, $default ) {
    return ( isset( $atts[ $key ] ) && $atts[ $key ] !== '' ) ? $atts[ $key ] : $default;
}

function mgk_render_listing_active_chips( array $filters, $filter_label = 'Filter' ) {
    $chips = mgk_active_filter_chips( $filters );
    ob_start();
    ?>
    <div class="mgk-active-chips">
        <?php if ( $chips ) : ?>
            <span class="mgk-chip-title">Active filters</span>
            <?php foreach ( $chips as $chip ) : ?>
                <a class="mgk-filter-chip" href="<?php echo esc_url( mgk_filter_remove_url( $chip['key'], $chip['value'] ) ); ?>" aria-label="<?php echo esc_attr( 'Remove ' . $chip['label'] . ' filter: ' . $chip['value'] ); ?>">
                    <strong><?php echo esc_html( $chip['value'] ); ?></strong>
                    <i class="mgk-chip-remove" aria-hidden="true"></i>
                </a>
            <?php endforeach; ?>
            <a class="mgk-clear-all" href="<?php echo esc_url( mgk_get_tutor_listing_url() ); ?>">Clear all</a>
        <?php else : ?>
            <span class="mgk-chip-empty">No active filters. Use the sidebar to narrow results.</span>
        <?php endif; ?>
    </div>
    <?php
    return (string) ob_get_clean();
}

function mgk_listing_related_links( array $filters, array $tutors = [] ) {
    $first = function ( $key, $fallback = '' ) use ( $filters ) {
        $values = mgk_filter_values( $filters, $key );
        return $values ? $values[0] : $fallback;
    };

    $subjects = [];
    $levels   = [];
    $areas    = [];
    foreach ( $tutors ?: mgk_get_tutors_from_db() as $tutor ) {
        $subjects = array_merge( $subjects, (array) ( $tutor['subjects'] ?? [] ) );
        $levels   = array_merge( $levels, (array) ( $tutor['levels'] ?? [] ) );
        $areas    = array_merge( $areas, (array) ( $tutor['locations'] ?? [] ) );
    }

    $subjects = array_values( array_unique( array_filter( array_map( 'sanitize_text_field', $subjects ) ) ) );
    $levels   = array_values( array_unique( array_filter( array_map( 'sanitize_text_field', $levels ) ) ) );
    $areas    = array_values( array_unique( array_filter( array_map( 'sanitize_text_field', $areas ) ) ) );

    $subject = $first( 'subject', 'Math' );
    $level   = $first( 'level', 'P5' );
    $area    = $first( 'area', 'Central SG' );

    $alt_subjects = array_values( array_filter( $subjects, function ( $item ) use ( $subject ) {
        return strtolower( (string) $item ) !== strtolower( (string) $subject );
    } ) );
    $preferred_alt_subjects = array_values( array_filter( [ 'English', 'Science', 'Chinese' ], function ( $item ) use ( $alt_subjects ) {
        return in_array( strtolower( $item ), array_map( 'strtolower', $alt_subjects ), true );
    } ) );
    $alt_subjects = array_values( array_unique( array_merge( $preferred_alt_subjects, $alt_subjects ) ) );
    $alt_levels = array_values( array_filter( $levels, function ( $item ) use ( $level ) {
        return strtolower( (string) $item ) !== strtolower( (string) $level );
    } ) );

    $links = [];
    $seen  = [];
    $add = function ( array $params, $label ) use ( &$links, &$seen ) {
        $params = mgk_normalize_listing_params( $params );
        ksort( $params );
        $key = md5( wp_json_encode( $params ) );
        if ( isset( $seen[ $key ] ) || ! mgk_filter_tutors( $params ) ) {
            return;
        }
        $seen[ $key ] = true;
        $links[] = [
            'label' => $label,
            'url'   => mgk_get_tutor_listing_url( $params ),
        ];
    };

    $add( [ 'subject' => $subject, 'level' => $level, 'area' => $area ], sprintf( '%s %s tutors in %s', $level, $subject, $area ) );

    foreach ( array_slice( $alt_subjects, 0, 2 ) as $alt_subject ) {
        $add( [ 'subject' => $alt_subject, 'level' => $level, 'area' => $area ], sprintf( '%s %s tutors in %s', $level, $alt_subject, $area ) );
    }

    $next_level = preg_match( '/^P([1-5])$/i', (string) $level, $m ) ? 'P' . ( (int) $m[1] + 1 ) : ( $alt_levels[0] ?? $level );
    $add( [ 'subject' => $subject, 'level' => $next_level, 'tier' => 'Ex-MOE' ], sprintf( '%s %s PSLE specialists', $next_level, $subject ) );
    $add( [ 'subject' => $subject, 'level' => $level, 'area' => 'Online' ], sprintf( 'Online %s tutors %s', $subject, $level ) );

    if ( count( $links ) < 4 ) {
        foreach ( $subjects as $fallback_subject ) {
            foreach ( $levels ?: [ $level ] as $fallback_level ) {
                $add( [ 'subject' => $fallback_subject, 'level' => $fallback_level ], sprintf( '%s %s tutors', $fallback_level, $fallback_subject ) );
                if ( count( $links ) >= 4 ) {
                    break 2;
                }
            }
        }
    }

    return array_slice( $links, 0, 4 );
}

/* ============================================================
   PART RENDERERS — each returns a string, each reads mgk_listing_query().
   ============================================================ */

/** 1. Search bar. */
function mgk_render_listing_search( array $atts = [] ) {
    $q = mgk_listing_query();
    $labels = [
        'label_subject' => mgk_listing_label( $atts, 'label_subject', 'Subject' ),
        'label_level'   => mgk_listing_label( $atts, 'label_level', 'Level' ),
        'label_area'    => mgk_listing_label( $atts, 'label_area', 'Area / Online' ),
        'label_budget'  => mgk_listing_label( $atts, 'label_budget', 'Budget' ),
        'update_label'  => mgk_listing_label( $atts, 'update_label', 'Update Search' ),
    ];
    return mgk_render_part( 'template-parts/sections/listing/search-summary', array_merge( [ 'filters' => $q['filters'] ], $labels ) );
}

/** 2. Result toolbar (count + sort + view toggle + filter button). */
function mgk_render_listing_toolbar( array $atts = [] ) {
    $q = mgk_listing_query();
    $labels = [
        'found_suffix' => mgk_listing_label( $atts, 'found_suffix', 'tutors found' ),
        'filter_label' => mgk_listing_label( $atts, 'filter_label', 'Filter' ),
        'sort_label'   => mgk_listing_label( $atts, 'sort_label', 'Sort' ),
    ];
    return mgk_render_part( 'template-parts/sections/listing/result-toolbar', array_merge( [ 'filters' => $q['filters'], 'count' => $q['total_count'] ], $labels ) );
}

/** 3. Filter sidebar + mobile drawer. */
function mgk_render_listing_filters( array $atts = [] ) {
    $q = mgk_listing_query();
    $labels = [
        'filters_heading' => mgk_listing_label( $atts, 'filters_heading', 'Filters' ),
        'apply_label'     => mgk_listing_label( $atts, 'apply_label', 'Apply' ),
        'apply_all_label' => mgk_listing_label( $atts, 'apply_all_label', 'Apply Filters' ),
        'clear_label'     => mgk_listing_label( $atts, 'clear_label', 'Clear All Filters' ),
    ];
    ob_start();
    ?>
    <aside class="mgk-listing-sidebar">
        <?php get_template_part( 'template-parts/components/filter-sidebar', null, array_merge( [ 'filters' => $q['filters'] ], $labels ) ); ?>
    </aside>

    <div class="mgk-filter-drawer" data-mgk-filter-drawer hidden>
        <div class="mgk-filter-backdrop" data-mgk-filter-close></div>
        <div class="mgk-filter-sheet" role="dialog" aria-modal="true" aria-label="Filters">
            <div class="mgk-filter-sheet-head">
                <h2><?php echo esc_html( $labels['filters_heading'] ); ?></h2>
                <button type="button" data-mgk-filter-close aria-label="Close filters">x</button>
            </div>
            <?php get_template_part( 'template-parts/components/filter-sidebar', null, array_merge( [ 'filters' => $q['filters'] ], $labels ) ); ?>
        </div>
    </div>
    <?php
    return (string) ob_get_clean();
}

/** 4. Results grid + pagination + compare drawer. LOCKED (data/logic). */
function mgk_render_listing_results( array $atts = [] ) {
    $q = mgk_listing_query();
    $page_tutors    = $q['page_tutors'];
    $total_count    = $q['total_count'];
    $current_page   = $q['current_page'];
    $total_pages    = $q['total_pages'];
    $per_page       = $q['per_page'];
    $showing_from   = $q['showing_from'];
    $showing_to     = $q['showing_to'];
    $pagination_url = $q['pagination_url'];
    $pagination_items = $q['pagination_items'];
    $filter_label = mgk_listing_label( $atts, 'filter_label', 'Filter' );

    ob_start();
    ?>
    <div class="mgk-listing-results">
        <?php get_template_part( 'template-parts/states/loading-skeleton', null, [ 'count' => 4 ] ); ?>
        <?php echo mgk_render_listing_active_chips( $q['filters'], $filter_label ); ?>

        <?php if ( $page_tutors ) : ?>
            <?php get_template_part( 'template-parts/sections/listing/results-grid', null, [ 'tutors' => $page_tutors ] ); ?>

            <div class="mgk-pagination">
                <span>Showing <?php echo esc_html( (string) $showing_from ); ?>-<?php echo esc_html( (string) $showing_to ); ?> of <?php echo esc_html( (string) $total_count ); ?> tutors</span>
                <?php if ( $total_pages > 1 ) : ?>
                    <nav aria-label="Tutor result pages">
                        <?php if ( $current_page > 1 ) : ?>
                            <a href="<?php echo esc_url( $pagination_url( $current_page - 1 ) ); ?>" aria-label="Previous page">&lt;</a>
                        <?php else : ?>
                            <span class="is-disabled" aria-disabled="true">&lt;</span>
                        <?php endif; ?>

                        <?php foreach ( $pagination_items as $item ) : ?>
                            <?php if ( $item === '...' ) : ?>
                                <span class="is-gap" aria-hidden="true">...</span>
                            <?php elseif ( (int) $item === $current_page ) : ?>
                                <span class="is-current" aria-current="page"><?php echo esc_html( (string) $item ); ?></span>
                            <?php else : ?>
                                <a href="<?php echo esc_url( $pagination_url( $item ) ); ?>"><?php echo esc_html( (string) $item ); ?></a>
                            <?php endif; ?>
                        <?php endforeach; ?>

                        <?php if ( $current_page < $total_pages ) : ?>
                            <a href="<?php echo esc_url( $pagination_url( $current_page + 1 ) ); ?>" aria-label="Next page">&gt;</a>
                        <?php else : ?>
                            <span class="is-disabled" aria-disabled="true">&gt;</span>
                        <?php endif; ?>
                    </nav>
                <?php endif; ?>
            </div>

            <?php if ( $current_page < $total_pages ) : ?>
                <div class="mgk-mobile-load">
                    <a class="mgk-btn mgk-btn-outline" href="<?php echo esc_url( $pagination_url( $current_page + 1 ) ); ?>">Next <?php echo esc_html( (string) min( $per_page, $total_count - $showing_to ) ); ?> tutors</a>
                </div>
            <?php endif; ?>
        <?php else : ?>
            <?php get_template_part( 'template-parts/states/empty-results', null, [
                'title'   => 'No tutors found for this search',
                'message' => 'Try a wider budget, online lessons, or a broader level range.',
            ] ); ?>
        <?php endif; ?>

        <?php echo mgk_render_listing_related( $atts ); ?>
    </div>

    <?php if ( $q['tutors'] ) : ?>
        <?php get_template_part( 'template-parts/sections/listing/compare-drawer' ); ?>
    <?php endif; ?>
    <?php
    return (string) ob_get_clean();
}

/** 5. Promo / ad banner. */
function mgk_render_listing_promo( array $atts = [] ) {
    $ad_title  = mgk_listing_label( $atts, 'ad_title',  'Try our PSLE Crash Course' );
    $ad_body   = mgk_listing_label( $atts, 'ad_body',   '10-week intensive · 92% A* rate · Limited slots' );
    $ad_button = mgk_listing_label( $atts, 'ad_button', 'Learn More' );
    $ad_url    = mgk_listing_label( $atts, 'ad_url',    '/psle-crash-course/' );
    ob_start();
    ?>
    <section class="mgk-listing-ad">
        <div>
            <h2><?php echo esc_html( $ad_title ); ?></h2>
            <p><?php echo esc_html( $ad_body ); ?></p>
        </div>
        <a class="mgk-btn mgk-btn-accent" href="<?php echo esc_url( mgk_url( $ad_url ) ); ?>"><?php echo esc_html( $ad_button ); ?></a>
    </section>
    <?php
    return (string) ob_get_clean();
}

/** 6. Related searches. */
function mgk_render_listing_related( array $atts = [] ) {
    static $rendered = false;
    if ( $rendered ) {
        return '';
    }
    $rendered = true;
    $q = mgk_listing_query();
    return mgk_render_part( 'template-parts/sections/listing/related-searches', [
        'related_heading' => mgk_listing_label( $atts, 'related_heading', 'Related searches' ),
        'links'           => mgk_listing_related_links( $q['filters'], $q['tutors'] ),
    ] );
}

/* ============================================================
   COMPOSITE renderer — assembles the 6 parts in the original layout.
   Kept so page-tutors.php DEFAULT MODE and [mgk_tutor_listing] are unchanged.
   ============================================================ */

/**
 * Render the full tutor-listing page body and RETURN it as a string.
 * Byte-compatible with the pre-split output (same DOM, same labels).
 *
 * @param array $atts Display-label overrides (all optional, see the part renderers).
 * @return string
 */
function mgk_render_tutor_listing( array $atts = [] ) {
    ob_start();
    echo mgk_render_listing_search( $atts );
    ?>
    <section class="mgk-section mgk-listing-page">
        <div class="mgk-shell">
            <?php echo mgk_render_listing_toolbar( $atts ); ?>

            <div class="mgk-listing-layout">
                <?php echo mgk_render_listing_filters( $atts ); ?>

                <div class="mgk-listing-results-col">
                    <?php
                    echo mgk_render_listing_results( $atts );
                    echo mgk_render_listing_promo( $atts );
                    echo mgk_render_listing_related( $atts );
                    ?>
                </div>
            </div>
        </div>
    </section>
    <?php
    return (string) ob_get_clean();
}

/* ============================================================
   SHORTCODES — composite + 6 sub-sections.
   ============================================================ */

function mgk_shortcode_tutor_listing( $atts ) {
    return mgk_render_tutor_listing( is_array( $atts ) ? $atts : [] );
}
add_shortcode( 'mgk_tutor_listing', 'mgk_shortcode_tutor_listing' );

/** Sub-section shortcodes. Each wraps its part renderer (atts forwarded verbatim). */
add_shortcode( 'mgk_listing_search',  function ( $a ) { return mgk_render_listing_search( is_array( $a ) ? $a : [] ); } );
add_shortcode( 'mgk_listing_toolbar', function ( $a ) { return mgk_render_listing_toolbar( is_array( $a ) ? $a : [] ); } );
add_shortcode( 'mgk_listing_filters', function ( $a ) { return mgk_render_listing_filters( is_array( $a ) ? $a : [] ); } );
add_shortcode( 'mgk_listing_results', function ( $a ) { return mgk_render_listing_results( is_array( $a ) ? $a : [] ); } );
add_shortcode( 'mgk_listing_promo',   function ( $a ) { return mgk_render_listing_promo( is_array( $a ) ? $a : [] ); } );
add_shortcode( 'mgk_listing_related', function ( $a ) { return mgk_render_listing_related( is_array( $a ) ? $a : [] ); } );
