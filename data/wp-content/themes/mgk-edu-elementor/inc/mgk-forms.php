<?php
/**
 * MGK Forms — Request Match (S07) LOCKED DATA CORE.
 * ================================================
 * This file is the single source of truth for the Request Match flow's
 * data + logic. Per the MGK architecture rule (ONBOARDING §1.5,
 * PLAYBOOK §3.5): Elementor controls presentation, NOT data; wp-admin
 * controls data; PHP controls logic. NOTHING in this file is exposed as an
 * editable Elementor control:
 *   - field source enums (levels / subjects / days / time / budget bounds)
 *   - required-field logic
 *   - SG phone validation + E.164 normalisation
 *   - PDPA-required logic
 *   - lead creation + lead state + SLA timer
 *   - PII masking
 *   - nonce / submit endpoint
 *
 * The Elementor widgets (inc/mgk-elementor.php) are thin shells that only
 * render the partials and expose safe marketing copy + Style-tab targets.
 *
 * The HTML lives in template-parts/sections/request/*.php. The presentation
 * partials READ from these helpers; they never define data inline.
 *
 * Phase scope: no real OTP, SMS, email, WhatsApp or PayNow calls. The match
 * SLA is a 6-hour promise stored as lead meta `mgk_lead_sla_due_at`.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ── Tunable constants (logic, not Elementor-editable) ───── */

if ( ! defined( 'MGK_MATCH_SLA_HOURS' ) ) define( 'MGK_MATCH_SLA_HOURS', 6 );
if ( ! defined( 'MGK_BUDGET_MIN' ) )      define( 'MGK_BUDGET_MIN', 25 );
if ( ! defined( 'MGK_BUDGET_MAX' ) )      define( 'MGK_BUDGET_MAX', 200 );
if ( ! defined( 'MGK_NOTE_MAXLEN' ) )     define( 'MGK_NOTE_MAXLEN', 500 );

/**
 * Locked enum + range config for the Request Match form.
 * Filterable server-side ONLY (mgk_request_enums) — never via Elementor.
 *
 * @return array{
 *   levels:array<int,array{value:string,label:string}>,
 *   subjects:array<int,array{value:string,label:string}>,
 *   days:array<int,array{value:string,label:string}>,
 *   times:array<int,string>,
 *   budget:array{min:int,max:int,default_min:int,default_max:int,step:int},
 *   note_max:int,
 *   sla_hours:int
 * }
 */
/**
 * Read a taxonomy's terms into [ ['value'=>slug,'label'=>name], … ].
 * Returns a static fallback (built from $fallback_labels) when the taxonomy is
 * missing or has no terms, so the form still works on a bare install.
 *
 * DATA CORE helper — the term list IS the wp-admin-managed option set.
 *
 * @param string   $taxonomy
 * @param string[] $fallback_labels
 * @return array<int,array{value:string,label:string}>
 */
function mgk_request_terms_options( $taxonomy, array $fallback_labels = [] ) {
    $out = [];

    if ( taxonomy_exists( $taxonomy ) ) {
        $terms = get_terms( [
            'taxonomy'   => $taxonomy,
            'hide_empty' => false,
            'orderby'    => 'term_order',  // honour admin drag-order if set
            'order'      => 'ASC',
        ] );
        // term_order isn't a core orderby; if it yields a WP_Error, retry by name.
        if ( is_wp_error( $terms ) ) {
            $terms = get_terms( [ 'taxonomy' => $taxonomy, 'hide_empty' => false ] );
        }
        if ( ! is_wp_error( $terms ) && $terms ) {
            foreach ( $terms as $t ) {
                $out[] = [ 'value' => $t->slug, 'label' => $t->name ];
            }
        }
    }

    if ( ! $out ) {
        foreach ( $fallback_labels as $label ) {
            $out[] = [ 'value' => sanitize_title( $label ), 'label' => $label ];
        }
    }

    return $out;
}

function mgk_request_enums() {
    static $cache = null;
    if ( $cache !== null ) return $cache;

    // DATA CORE: Child Level + Subject options come from the wp-admin taxonomies
    // (mgk_level, mgk_subject). Admins manage them under Teachers → Levels/Subjects;
    // the dropdowns mirror that. NOT editable from Elementor. Fallback to a static
    // list only if the taxonomy is empty / unavailable (e.g. bare install).
    $levels = mgk_request_terms_options( 'mgk_level',
        [ 'P1', 'P2', 'P3', 'P4', 'P5', 'P6', 'Sec 1', 'Sec 2', 'Sec 3', 'Sec 4', 'Sec 5', 'JC1', 'JC2', 'IB', 'IGCSE' ] );

    $subjects = mgk_request_terms_options( 'mgk_subject',
        [ 'English', 'Math', 'A-Math', 'E-Math', 'Chinese', 'Science', 'Physics', 'Chemistry', 'Biology', 'General Paper' ] );
    // The subject dropdown shows the full term name; keep a short uppercase label
    // for the compact ENUM helper line.
    foreach ( $subjects as &$s ) {
        $s['full']  = $s['label'];
        $s['label'] = strtoupper( $s['label'] );
    }
    unset( $s );

    $days = [];
    foreach ( [ 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun' ] as $d ) {
        $days[] = [ 'value' => strtolower( $d ), 'label' => $d ];
    }

    // 30-min increments 8:00 AM → 10:00 PM, stored as 24h "HH:MM" values.
    $times = [];
    for ( $h = 8; $h <= 22; $h++ ) {
        foreach ( [ 0, 30 ] as $m ) {
            if ( $h === 22 && $m === 30 ) continue;
            $times[] = sprintf( '%02d:%02d', $h, $m );
        }
    }

    $cache = [
        'levels'   => $levels,
        'subjects' => $subjects,
        'days'     => $days,
        'times'    => $times,
        'budget'   => [
            'min'         => MGK_BUDGET_MIN,
            'max'         => MGK_BUDGET_MAX,
            'default_min' => 40,
            'default_max' => 90,
            'step'        => 5,
        ],
        'note_max'  => MGK_NOTE_MAXLEN,
        'sla_hours' => MGK_MATCH_SLA_HOURS,
    ];

    /** @internal Server-side only. NOT an Elementor control. */
    return $cache = apply_filters( 'mgk_request_enums', $cache );
}

/** Format a 24h "HH:MM" value into a 12h display label, e.g. "16:00" → "4:00 PM". */
function mgk_request_time_label( $hhmm ) {
    if ( ! preg_match( '/^(\d{1,2}):(\d{2})$/', (string) $hhmm, $m ) ) return (string) $hhmm;
    $h = (int) $m[1];
    $min = $m[2];
    $ampm = $h >= 12 ? 'PM' : 'AM';
    $h12 = $h % 12; if ( $h12 === 0 ) $h12 = 12;
    return sprintf( '%d:%s %s', $h12, $min, $ampm );
}

/** Is this a valid enum value for the given key (levels|subjects|days)? */
function mgk_request_enum_has( $key, $value ) {
    $enums = mgk_request_enums();
    if ( empty( $enums[ $key ] ) ) return false;
    foreach ( $enums[ $key ] as $opt ) {
        if ( (string) $opt['value'] === (string) $value ) return true;
    }
    return false;
}

/* ── SG phone → E.164 (logic, locked) ────────────────────── */

/**
 * Supported phone countries for the request form. Each: dial code + a national
 * validation rule. WhatsApp Cloud API can message any of these (business-
 * initiated templates are billed per Meta's country pricing — none are free).
 */
function mgk_phone_countries() {
    return [
        'SG' => [ 'label' => 'Singapore', 'flag' => '🇸🇬', 'dial' => '65' ],
        'VN' => [ 'label' => 'Vietnam',   'flag' => '🇻🇳', 'dial' => '84' ],
    ];
}

/**
 * Normalise a mobile number to E.164 for the given country, or '' if invalid.
 *   SG (+65): 8 digits, starts 6/8/9. Optional 65 / +65 prefix.
 *   VN (+84): mobile, 9 national digits after a leading 0 (03/05/07/08/09).
 *             Accepts 0912345678, 912345678, 84912345678, +84912345678.
 * Unknown country falls back to SG (keeps prior behaviour).
 */
function mgk_normalize_phone( $raw, $country = 'SG' ) {
    $country = strtoupper( (string) $country );
    $digits  = preg_replace( '/[^\d]/', '', (string) $raw );

    if ( $country === 'VN' ) {
        // Strip a leading 84 country code, then a leading national 0.
        if ( strlen( $digits ) >= 11 && strpos( $digits, '84' ) === 0 ) {
            $digits = substr( $digits, 2 );
        }
        $digits = ltrim( $digits, '0' );
        // VN mobile: 9 digits, first digit 3/5/7/8/9 (after dropping the 0).
        if ( ! preg_match( '/^[35789]\d{8}$/', $digits ) ) return '';
        return '+84' . $digits;
    }

    // Default: Singapore.
    if ( strlen( $digits ) === 10 && strpos( $digits, '65' ) === 0 ) {
        $digits = substr( $digits, 2 );
    }
    if ( ! preg_match( '/^[689]\d{7}$/', $digits ) ) return '';
    return '+65' . $digits;
}

/**
 * Backwards-compatible SG wrapper (existing callers). Still SG-only.
 */
function mgk_normalize_sg_phone( $raw ) {
    return mgk_normalize_phone( $raw, 'SG' );
}

/* ── PII masking (logic, locked) ─────────────────────────── */

/** Mask an email: "marygtan@gmail.com" → "m par***@gmail.com"-style. */
function mgk_mask_email( $email ) {
    $email = (string) $email;
    if ( ! $email || strpos( $email, '@' ) === false ) return '';
    list( $user, $domain ) = explode( '@', $email, 2 );
    $user = (string) $user;
    if ( $user === '' ) {
        $masked = '***';
    } elseif ( strlen( $user ) <= 3 ) {
        $masked = substr( $user, 0, 1 ) . '***';
    } else {
        $masked = substr( $user, 0, 3 ) . '***';
    }
    return $masked . '@' . $domain;
}

/** Mask an E.164 phone, keeping country code + first national digit:
 *  "+6591234567" → "+65 9XXX XXXX" · "+84912345678" → "+84 9XXXX XXXX". */
function mgk_mask_phone( $e164 ) {
    $e164 = (string) $e164;
    if ( preg_match( '/^\+84(\d)\d{8}$/', $e164, $m ) ) {
        return '+84 ' . $m[1] . 'XXXX XXXX';
    }
    if ( preg_match( '/^\+65([689])\d{7}$/', $e164, $m ) ) {
        return '+65 ' . $m[1] . 'XXX XXXX';
    }
    return '+65 9XXX XXXX';
}

/* ── SLA timer (logic, locked) ───────────────────────────── */

/** SLA due timestamp = created_at + MGK_MATCH_SLA_HOURS hours. */
function mgk_request_sla_due_at( $created_ts = 0 ) {
    $created_ts = $created_ts ?: current_time( 'timestamp', true );
    return (int) $created_ts + ( MGK_MATCH_SLA_HOURS * HOUR_IN_SECONDS );
}

/* ── Server validation (logic, locked) ───────────────────── */

/**
 * Validate the raw Request Match payload.
 * Returns an array of field => message (empty array = valid).
 */
function mgk_validate_request_payload( array $p ) {
    $errors = [];
    $enums  = mgk_request_enums();

    // Child's name — required, ≤120 chars (matches the child entity). Trimmed.
    $child_name = isset( $p['child_name'] ) ? trim( (string) $p['child_name'] ) : '';
    if ( $child_name === '' ) {
        $errors['child_name'] = 'Please enter your child’s name.';
    } elseif ( mb_strlen( $child_name ) > 120 ) {
        $errors['child_name'] = 'Please keep the name under 120 characters.';
    }

    if ( empty( $p['child_level'] ) || ! mgk_request_enum_has( 'levels', $p['child_level'] ) ) {
        $errors['child_level'] = 'Please select your child’s level.';
    }
    if ( empty( $p['subject'] ) || ! mgk_request_enum_has( 'subjects', $p['subject'] ) ) {
        $errors['subject'] = 'Please select a subject.';
    }

    $days = isset( $p['preferred_days'] ) ? (array) $p['preferred_days'] : [];
    $days = array_values( array_filter( array_map( 'strval', $days ), function ( $d ) {
        return mgk_request_enum_has( 'days', $d );
    } ) );
    if ( ! $days ) {
        $errors['preferred_days'] = 'Pick at least one preferred day.';
    }

    $from = isset( $p['time_from'] ) ? (string) $p['time_from'] : '';
    $to   = isset( $p['time_to'] )   ? (string) $p['time_to']   : '';
    if ( ! preg_match( '/^\d{1,2}:\d{2}$/', $from ) || ! preg_match( '/^\d{1,2}:\d{2}$/', $to ) ) {
        $errors['time_to'] = 'Choose a valid time range.';
    } elseif ( mgk_time_to_minutes( $from ) >= mgk_time_to_minutes( $to ) ) {
        $errors['time_to'] = 'Start time must be before end time.';
    }

    // Budget is OPTIONAL but, if present, must sit inside SGD 25–200.
    $has_budget = isset( $p['budget_min'] ) && $p['budget_min'] !== '' && isset( $p['budget_max'] ) && $p['budget_max'] !== '';
    if ( $has_budget ) {
        $bmin = (int) $p['budget_min'];
        $bmax = (int) $p['budget_max'];
        if ( $bmin < MGK_BUDGET_MIN || $bmax > MGK_BUDGET_MAX || $bmin > $bmax ) {
            $errors['budget'] = 'Budget must be between $' . MGK_BUDGET_MIN . ' and $' . MGK_BUDGET_MAX . '/hr.';
        }
    }

    $note = isset( $p['note'] ) ? (string) $p['note'] : '';
    if ( mb_strlen( $note ) > MGK_NOTE_MAXLEN ) {
        $errors['note'] = 'Please keep your note under ' . MGK_NOTE_MAXLEN . ' characters.';
    }

    // Email is the REQUIRED contact channel (matches replace phone on S07).
    $email = isset( $p['email'] ) ? trim( (string) $p['email'] ) : '';
    if ( $email === '' || ! is_email( $email ) ) {
        $errors['email'] = 'Enter a valid email address.';
    }

    // Phone is now OPTIONAL. If provided, it must be a valid SG/VN mobile.
    if ( ! empty( $p['phone'] ) ) {
        $country = isset( $p['phone_country'] ) ? strtoupper( (string) $p['phone_country'] ) : 'SG';
        if ( ! isset( mgk_phone_countries()[ $country ] ) ) {
            $country = 'SG';
        }
        if ( ! mgk_normalize_phone( $p['phone'], $country ) ) {
            $errors['phone'] = ( $country === 'VN' )
                ? 'Enter a valid Vietnam mobile (e.g. 0912 345 678).'
                : 'Enter a valid SG mobile (8 digits, starts with 6/8/9).';
        }
    }

    if ( empty( $p['pdpa_consent'] ) ) {
        $errors['pdpa_consent'] = 'Please agree to the PDPA Notice to continue.';
    }

    return $errors;
}

/** Minutes since midnight for an "HH:MM" string. */
function mgk_time_to_minutes( $hhmm ) {
    if ( ! preg_match( '/^(\d{1,2}):(\d{2})$/', (string) $hhmm, $m ) ) return -1;
    return ( (int) $m[1] ) * 60 + (int) $m[2];
}

/* ── Build the locked lead payload from a raw request ────── */

/**
 * Normalise + shape the validated request into the payload that the lead
 * core (mgk_booking_create_lead) stores. Does NOT validate — caller must
 * run mgk_validate_request_payload() first.
 */
function mgk_request_build_payload( array $p ) {
    $enums = mgk_request_enums();

    $days = array_values( array_filter(
        array_map( 'strval', (array) ( $p['preferred_days'] ?? [] ) ),
        function ( $d ) { return mgk_request_enum_has( 'days', $d ); }
    ) );

    $has_budget = isset( $p['budget_min'] ) && $p['budget_min'] !== '' && isset( $p['budget_max'] ) && $p['budget_max'] !== '';

    $country = isset( $p['phone_country'] ) ? strtoupper( (string) $p['phone_country'] ) : 'SG';
    if ( ! isset( mgk_phone_countries()[ $country ] ) ) {
        $country = 'SG';
    }
    $phone_e164 = mgk_normalize_phone( $p['phone'] ?? '', $country );

    $payload = [
        'child_name'     => sanitize_text_field( mb_substr( trim( (string) ( $p['child_name'] ?? '' ) ), 0, 120 ) ),
        'level'          => sanitize_key( $p['child_level'] ?? '' ),
        'subject'        => sanitize_key( $p['subject'] ?? '' ),
        'preferred_days' => implode( ',', $days ),
        'time_from'      => sanitize_text_field( $p['time_from'] ?? '' ),
        'time_to'        => sanitize_text_field( $p['time_to'] ?? '' ),
        'budget_min'     => $has_budget ? (int) $p['budget_min'] : '',
        'budget_max'     => $has_budget ? (int) $p['budget_max'] : '',
        'note'           => sanitize_textarea_field( mb_substr( (string) ( $p['note'] ?? '' ), 0, MGK_NOTE_MAXLEN ) ),
        'phone_country'  => $country,
        'phone'          => $phone_e164,
        'phone_e164'     => $phone_e164,
        'email'          => isset( $p['email'] ) ? sanitize_email( $p['email'] ) : '',
        'pdpa_consent'   => ! empty( $p['pdpa_consent'] ) ? 'yes' : 'no',
        'source_utm'     => sanitize_text_field( $p['source_utm'] ?? '' ),
        // parent_name is required by the core lead validator; this flow doesn't
        // collect a name (zero-friction), so seed a safe placeholder.
        'parent_name'    => sanitize_text_field( $p['parent_name'] ?? 'Parent' ),
    ];

    return $payload;
}

/**
 * Create a Request Match lead. Wraps the locked lead core, then stamps the
 * SLA + matching state. Returns ['id','token','status','sla_due_at'] or WP_Error.
 *
 * Falls back to a safe demo lead (id 0) when the lead CPT / core is absent so
 * the confirmation state can still render in a bare environment.
 */
function mgk_request_create_lead( array $raw ) {
    $payload = mgk_request_build_payload( $raw );

    $created_ts = current_time( 'timestamp', true );
    $sla_due    = mgk_request_sla_due_at( $created_ts );

    if ( function_exists( 'mgk_booking_create_lead' ) && post_type_exists( 'mg_lead' ) ) {
        $lead = mgk_booking_create_lead( $payload );
        if ( is_wp_error( $lead ) ) return $lead;

        $lead_id = (int) ( $lead['id'] ?? 0 );
        if ( $lead_id ) {
            update_post_meta( $lead_id, 'mgk_lead_sla_due_at', $sla_due );
            update_post_meta( $lead_id, 'mgk_lead_source',     'request_match' );

            // ── Option 3: a signed-in parent is ADDING a child ──
            // Link the lead to their account and create the real mg_child now
            // (don't wait for payment) so the dashboard shows the new child
            // immediately. Email is theirs (locked on the form), no re-ask.
            if ( function_exists( 'mgk_is_parent_user' ) && mgk_is_parent_user() ) {
                $uid = get_current_user_id();
                update_post_meta( $lead_id, 'mgk_lead_parent_user_id', $uid );
                if ( function_exists( 'mgk_child_find_or_create' ) ) {
                    $child_id = mgk_child_find_or_create( $uid, $payload['child_name'] ?? '', $payload['level'] ?? '' );
                    if ( $child_id ) {
                        update_post_meta( $lead_id, 'mgk_lead_child_id', (int) $child_id );
                    }
                }
            }

            // Lead awaits admin review — NOT auto-advanced. captured → pending_review.
            // No parent message is sent until an admin clicks Accept (mgk_lead_accept).
            if ( function_exists( 'mgk_lead_transition' ) && function_exists( 'mgk_lead_can_transition' ) ) {
                $current = get_post_meta( $lead_id, 'mgk_lead_state', true ) ?: MGK_LEAD_CAPTURED;
                if ( mgk_lead_can_transition( $current, MGK_LEAD_PENDING_REVIEW ) ) {
                    mgk_lead_transition( $lead_id, MGK_LEAD_PENDING_REVIEW );
                }
            }
            // Fires for logging/integrations, but the WhatsApp/email auto-send is
            // intentionally NOT hooked here — review gates all outbound messages.
            do_action( 'mgk_request_match_created', $lead_id, $payload );
        }

        return [
            'id'         => $lead_id,
            'token'      => (string) ( $lead['token'] ?? '' ),
            'status'     => 'pending_review',
            'sla_due_at' => $sla_due,
        ];
    }

    // Safe demo fallback — no DB record, no external calls.
    return [
        'id'         => 0,
        'token'      => wp_generate_password( 20, false, false ),
        'status'     => 'matching',
        'sla_due_at' => $sla_due,
    ];
}

/* ── Confirmation lookup by token (logic, locked) ────────── */

/**
 * Resolve a lead by its public token into the safe, masked view-model the
 * confirmation state needs. Never returns raw phone/email or lead ID.
 *
 * @return array{found:bool,sla_due_at:int,email_mask:string,phone_mask:string,subject:string,level:string}
 */
function mgk_request_confirm_view( $token ) {
    $default = [
        'found'      => false,
        'sla_due_at' => mgk_request_sla_due_at(),
        'email_mask' => '',
        'phone_mask' => '+65 9XXX XXXX',
        'subject'    => '',
        'level'      => '',
    ];

    $token = sanitize_text_field( (string) $token );
    if ( ! $token || ! post_type_exists( 'mg_lead' ) ) {
        return $default;
    }

    $ids = get_posts( [
        'post_type'      => 'mg_lead',
        'post_status'    => [ 'publish' ],
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'no_found_rows'  => true,
        'meta_query'     => [
            [ 'key' => 'mgk_lead_token', 'value' => $token, 'compare' => '=' ],
        ],
    ] );
    if ( empty( $ids ) ) return $default;

    $id  = (int) $ids[0];
    $sla = (int) get_post_meta( $id, 'mgk_lead_sla_due_at', true );

    return [
        'found'      => true,
        'sla_due_at' => $sla ?: mgk_request_sla_due_at(),
        'email_mask' => mgk_mask_email( (string) get_post_meta( $id, 'mgk_lead_email', true ) ),
        'phone_mask' => mgk_mask_phone( (string) get_post_meta( $id, 'mgk_lead_phone_e164', true ) ),
        'subject'    => (string) get_post_meta( $id, 'mgk_lead_subject', true ),
        'level'      => (string) get_post_meta( $id, 'mgk_lead_level', true ),
    ];
}

/* ── POST handler: request_match submit (no-JS / fallback) ─ */

/**
 * Server-side submit handler. Works without JS (progressive enhancement) and
 * is also the endpoint the JS posts to. On success it redirects back to the
 * same route with ?mgk_lead=<token> so the page swaps to the confirmation
 * state. The nonce, validation and lead logic are LOCKED here — never exposed
 * to Elementor.
 */
add_action( 'template_redirect', function () {
    if ( ( $_SERVER['REQUEST_METHOD'] ?? '' ) !== 'POST' ) return;

    $action = isset( $_POST['mgk_action'] ) ? sanitize_key( wp_unslash( $_POST['mgk_action'] ) ) : '';
    if ( $action !== 'request_match' ) return;

    $ok_nonce = isset( $_POST['mgk_request_nonce'] ) && wp_verify_nonce(
        sanitize_text_field( wp_unslash( $_POST['mgk_request_nonce'] ) ), 'mgk_request_match'
    );

    // Build raw payload from POST (arrays for multi-select days).
    $raw = [
        'child_name'     => isset( $_POST['child_name'] ) ? sanitize_text_field( wp_unslash( $_POST['child_name'] ) ) : '',
        'child_level'    => isset( $_POST['child_level'] ) ? sanitize_key( wp_unslash( $_POST['child_level'] ) ) : '',
        'subject'        => isset( $_POST['subject'] ) ? sanitize_key( wp_unslash( $_POST['subject'] ) ) : '',
        'preferred_days' => isset( $_POST['preferred_days'] ) ? (array) wp_unslash( $_POST['preferred_days'] ) : [],
        'time_from'      => isset( $_POST['time_from'] ) ? sanitize_text_field( wp_unslash( $_POST['time_from'] ) ) : '',
        'time_to'        => isset( $_POST['time_to'] ) ? sanitize_text_field( wp_unslash( $_POST['time_to'] ) ) : '',
        'budget_min'     => isset( $_POST['budget_min'] ) ? sanitize_text_field( wp_unslash( $_POST['budget_min'] ) ) : '',
        'budget_max'     => isset( $_POST['budget_max'] ) ? sanitize_text_field( wp_unslash( $_POST['budget_max'] ) ) : '',
        'note'           => isset( $_POST['note'] ) ? sanitize_textarea_field( wp_unslash( $_POST['note'] ) ) : '',
        'phone'          => isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '',
        'phone_country'  => isset( $_POST['phone_country'] ) ? sanitize_text_field( wp_unslash( $_POST['phone_country'] ) ) : 'SG',
        'email'          => isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '',
        'pdpa_consent'   => ! empty( $_POST['pdpa_consent'] ),
        'source_utm'     => isset( $_POST['source_utm'] ) ? sanitize_text_field( wp_unslash( $_POST['source_utm'] ) ) : '',
    ];

    $redirect_base = wp_get_referer() ?: home_url( '/request-match/' );

    if ( ! $ok_nonce ) {
        wp_safe_redirect( add_query_arg( 'mgk_err', 'expired', $redirect_base ) );
        exit;
    }

    $errors = mgk_validate_request_payload( $raw );
    if ( $errors ) {
        // Re-show the form with a generic error flag; JS handles inline errors,
        // the no-JS path just gets the summary banner.
        wp_safe_redirect( add_query_arg( 'mgk_err', 'invalid', $redirect_base ) );
        exit;
    }

    $result = mgk_request_create_lead( $raw );
    if ( is_wp_error( $result ) ) {
        wp_safe_redirect( add_query_arg( 'mgk_err', 'server', $redirect_base ) );
        exit;
    }

    if ( function_exists( 'mgk_track_event' ) ) {
        mgk_track_event( 'request_form_success', [
            'level'                => $raw['child_level'],
            'subject'              => $raw['subject'],
            'has_budget'           => ( $raw['budget_min'] !== '' ) ? 1 : 0,
            'selected_days_count'  => count( (array) $raw['preferred_days'] ),
            'source_utm'           => $raw['source_utm'],
        ] );
    }

    $target = home_url( '/request-match/' );
    wp_safe_redirect( add_query_arg( 'mgk_lead', rawurlencode( (string) $result['token'] ), $target ) );
    exit;
} );

/* ── Shortcodes (thin shells → partials) ─────────────────── */

/**
 * Render a request-match partial with SAFE marketing copy passed through.
 * The widget shell (inc/mgk-elementor.php) injects $atts; here we just locate
 * the partial and pass copy as $args. No data/logic comes from $atts.
 */
function mgk_request_render_part( $part, $atts = [] ) {
    $file = get_theme_file_path( 'template-parts/sections/request/' . $part . '.php' );
    if ( ! file_exists( $file ) ) return '';
    $args = is_array( $atts ) ? $atts : [];
    ob_start();
    include $file;
    return ob_get_clean();
}

/**
 * [mgk_request_match] — renders State 1 (form) OR State 2 (confirmation),
 * switching on the presence of a valid ?mgk_lead token. Same route, two states.
 */
add_shortcode( 'mgk_request_match', function ( $atts ) {
    $atts = shortcode_atts( [
        // State 1 safe copy
        'intro_pre' => '', 'intro_em1' => '', 'intro_mid1' => '', 'intro_mid2' => '', 'intro_em2' => '',
        'trust_1' => '', 'trust_2' => '', 'trust_3' => '', 'trust_4' => '',
        'form_heading' => '', 'form_note' => '', 'submit_label' => '', 'submit_note' => '', 'pdpa_url' => '',
        // State 2 safe copy
        'heading' => '', 'subheading' => '', 'timer_label' => '', 'timer_foot' => '',
        'reassure' => '', 'btn_browse' => '', 'btn_how' => '',
    ], $atts, 'mgk_request_match' );

    // Drop empty atts so the partial defaults apply.
    $atts = array_filter( $atts, function ( $v ) { return $v !== '' && $v !== null; } );

    $has_token = ! empty( $_GET['mgk_lead'] );
    return mgk_request_render_part( $has_token ? 'request-confirm' : 'request-form', $atts );
} );

/** [mgk_request_intro] — Intro band only (split widget). */
add_shortcode( 'mgk_request_intro', function ( $atts ) {
    $atts = shortcode_atts( [
        'intro_pre' => '', 'intro_em1' => '', 'intro_mid1' => '', 'intro_mid2' => '', 'intro_em2' => '',
        'trust_1' => '', 'trust_2' => '', 'trust_3' => '', 'trust_4' => '',
        'hide_progress' => '', 'hide_trust' => '',
    ], $atts, 'mgk_request_intro' );
    // Drop empties so partial defaults apply. Visibility is controlled by the
    // hide_progress / hide_trust switchers (not by clearing copy).
    $atts = array_filter( $atts, function ( $v ) { return $v !== '' && $v !== null; } );
    return mgk_request_render_part( 'request-intro', $atts );
} );

/** [mgk_request_fields] — the complete form (heading + single <form>), split widget. */
add_shortcode( 'mgk_request_fields', function ( $atts ) {
    $atts = shortcode_atts( [
        'form_heading' => '', 'form_note' => '', 'submit_label' => '', 'submit_note' => '', 'pdpa_url' => '',
    ], $atts, 'mgk_request_fields' );
    $atts = array_filter( $atts, function ( $v ) { return $v !== '' && $v !== null; } );
    return mgk_request_render_part( 'request-fields', $atts );
} );

/* ── Per-field split shortcodes (each a standalone Elementor widget) ──────
   Render via mgk_request_field_html(). No <form> tag — the Submit widget's JS
   gathers every .mgk-rq input in the same Elementor Section and POSTs to the
   locked REST endpoint. Data/options/validation stay locked server-side. */

if ( function_exists( 'mgk_request_field_html' ) ) {
    $mgk_field_shortcodes = [
        'mgk_request_field_child_name' => 'child_name',
        'mgk_request_field_level'    => 'level',
        'mgk_request_field_subject'  => 'subject',
        'mgk_request_field_schedule' => 'schedule',
        'mgk_request_field_budget'   => 'budget',
        'mgk_request_field_note'     => 'note',
        // The contact field is now EMAIL (was phone). The legacy
        // mgk_request_field_phone tag is kept as an alias → email so existing
        // Elementor pages that placed the phone widget render the email field
        // without re-seeding the layout.
        'mgk_request_field_email'    => 'email',
        'mgk_request_field_phone'    => 'email',
        'mgk_request_field_pdpa'     => 'pdpa',
    ];
    foreach ( $mgk_field_shortcodes as $tag => $type ) {
        add_shortcode( $tag, function ( $atts ) use ( $type ) {
            $atts = shortcode_atts( [
                'hide' => '', 'hide_helper' => '', 'hide_multi' => '',
                'label' => '', 'helper' => '', 'placeholder' => '',
                'multi_note' => '', 'link_label' => '', 'pdpa_url' => '',
            ], (array) $atts, 'mgk_request_field_' . $type );
            return mgk_request_field_html( $type, $atts );
        } );
    }
}

/**
 * [mgk_request_submit] — the submit button (and JS submit anchor). Carries the
 * nonce + confirm/REST URLs so the Submit widget's JS can POST the gathered
 * fields. data-mgk-request marks the submit scope root for the JS collector.
 */
add_shortcode( 'mgk_request_submit', function ( $atts ) {
    $a = shortcode_atts( [
        'submit_label' => 'Get My Matches →',
        'submit_note'  => '',
    ], (array) $atts, 'mgk_request_submit' );

    $sla = function_exists( 'mgk_request_enums' ) ? ( mgk_request_enums()['sla_hours'] ?? 6 ) : 6;
    $note = $a['submit_note'] !== '' ? $a['submit_note']
        : 'NO ACCOUNT CREATED · YOU’LL HEAR BACK WITHIN ' . $sla . ' HOURS';

    ob_start();
    ?>
    <div class="mgk-rq mgk-rq--submit-only" data-mgk-request data-submit-scope
         data-rest-url="<?php echo esc_attr( rest_url( 'mgk/v1/request-match' ) ); ?>"
         data-nonce="<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ); ?>"
         data-confirm-url="<?php echo esc_attr( home_url( '/request-match/' ) ); ?>">
        <input type="hidden" name="source_utm" id="rq_source_utm" value="">
        <button type="button" class="mgk-rq-submit" id="js-rq-submit"
                data-event="cta_click" data-screen="request_match">
            <span class="mgk-rq-submit-label"><?php echo esc_html( $a['submit_label'] ); ?></span>
            <span class="mgk-rq-submit-loading" hidden>Submitting…</span>
        </button>
        <p class="mgk-rq-subnote"><?php echo esc_html( $note ); ?></p>
    </div>
    <?php
    return ob_get_clean();
} );

/** [mgk_request_confirm] — force the confirmation state (preview / standalone). */
add_shortcode( 'mgk_request_confirm', function ( $atts ) {
    $atts = shortcode_atts( [
        'heading' => '', 'subheading' => '', 'timer_label' => '', 'timer_foot' => '',
        'reassure' => '', 'btn_browse' => '', 'btn_how' => '',
    ], $atts, 'mgk_request_confirm' );
    $atts = array_filter( $atts, function ( $v ) { return $v !== '' && $v !== null; } );
    return mgk_request_render_part( 'request-confirm', $atts );
} );

/* ── Elementor layout (split widgets) for the request-match page ─────────
   Each section is its OWN Elementor widget so owners edit/reorder/style them
   one by one. The data/options/validation stay locked in PHP (the widgets are
   thin shells). Used by the auto-create hook and by mgk_request_sync_layout().*/

/**
 * Build the Elementor _elementor_data JSON (one container → intro + 7 fields +
 * submit). Stable element IDs so re-syncing doesn't churn the document.
 */
function mgk_request_elementor_data() {
    $widgets = [];
    $n = 0;
    $add = function ( $widget_type ) use ( &$widgets, &$n ) {
        $n++;
        $widgets[] = [
            'id'         => substr( md5( 'mgkrq-' . $widget_type . '-' . $n ), 0, 8 ),
            'elType'     => 'widget',
            'settings'   => new stdClass(),
            'elements'   => [],
            'widgetType' => $widget_type,
        ];
    };
    foreach ( [
        'mgk_request_intro',
        'mgk_request_field_level',
        'mgk_request_field_subject',
        'mgk_request_field_schedule',
        'mgk_request_field_budget',
        'mgk_request_field_note',
        'mgk_request_field_phone',
        'mgk_request_field_pdpa',
        'mgk_request_submit',
    ] as $wt ) {
        $add( $wt );
    }

    $data = [ [
        'id'       => substr( md5( 'mgkrq-container' ), 0, 8 ),
        'elType'   => 'container',
        'settings' => new stdClass(),
        'elements' => $widgets,
        'isInner'  => false,
    ] ];

    return wp_json_encode( $data );
}

/**
 * Mark a page as an Elementor-built request-match page using the split-widget
 * layout. update_post_meta() unslashes once, so the DB ends up with clean JSON
 * (Elementor stores _elementor_data slashed).
 */
function mgk_request_apply_elementor_layout( $page_id ) {
    $page_id = (int) $page_id;
    if ( ! $page_id ) return;
    update_post_meta( $page_id, '_wp_page_template', 'page-blank.php' );
    update_post_meta( $page_id, '_elementor_edit_mode', 'builder' );
    update_post_meta( $page_id, '_elementor_data', wp_slash( mgk_request_elementor_data() ) );
    delete_post_meta( $page_id, '_elementor_element_cache' );
    if ( class_exists( '\Elementor\Plugin' ) ) {
        \Elementor\Plugin::$instance->files_manager->clear_cache();
    }
}

/* ── Auto-create /request-match/ page ────────────────────── */

add_action( 'init', function () {
    if ( get_option( 'mgk_request_match_page_created' ) ) return;

    $existing = get_page_by_path( 'request-match' );
    $pid = $existing ? (int) $existing->ID : 0;

    if ( ! $pid ) {
        $pid = wp_insert_post( [
            'post_type'    => 'page',
            'post_title'   => 'Request a Match',
            'post_name'    => 'request-match',
            // Fallback content for non-Elementor renderers; Elementor builder mode
            // (set below) renders from _elementor_data instead.
            'post_content' => '[mgk_request_match]',
            'post_status'  => 'publish',
        ] );
    }

    // Build the page from split widgets so each section is independently
    // editable in Elementor (full-width blank canvas, no page title).
    if ( $pid && ! is_wp_error( $pid ) ) {
        mgk_request_apply_elementor_layout( $pid );
    }

    update_option( 'mgk_request_match_page_created', 1 );
}, 99 );

/* ── REST: POST /mgk/v1/request-match (JS submit path) ───── */

add_action( 'rest_api_init', function () {
    register_rest_route( 'mgk/v1', '/request-match', [
        'methods'             => WP_REST_Server::CREATABLE,
        'callback'            => 'mgk_rest_request_match',
        'permission_callback' => '__return_true',
    ] );
} );

/**
 * REST handler for the Request Match form. Validation + lead logic are LOCKED
 * here (same core as the no-JS POST path). Returns the public token + masked
 * SLA only — never the lead ID or raw PII.
 */
function mgk_rest_request_match( WP_REST_Request $req ) {
    $body = $req->get_json_params() ?: (array) $req->get_body_params();

    $raw = [
        'child_name'     => sanitize_text_field( (string) ( $body['child_name'] ?? '' ) ),
        'child_level'    => sanitize_key( $body['child_level'] ?? '' ),
        'subject'        => sanitize_key( $body['subject'] ?? '' ),
        'preferred_days' => (array) ( $body['preferred_days'] ?? [] ),
        'time_from'      => sanitize_text_field( $body['time_from'] ?? '' ),
        'time_to'        => sanitize_text_field( $body['time_to'] ?? '' ),
        'budget_min'     => isset( $body['budget_min'] ) ? sanitize_text_field( (string) $body['budget_min'] ) : '',
        'budget_max'     => isset( $body['budget_max'] ) ? sanitize_text_field( (string) $body['budget_max'] ) : '',
        'note'           => sanitize_textarea_field( $body['note'] ?? '' ),
        'phone'          => sanitize_text_field( $body['phone'] ?? '' ),
        'phone_country'  => sanitize_text_field( $body['phone_country'] ?? 'SG' ),
        'email'          => sanitize_email( $body['email'] ?? '' ),
        'pdpa_consent'   => ! empty( $body['pdpa_consent'] ) && $body['pdpa_consent'] !== 'no',
        'source_utm'     => sanitize_text_field( $body['source_utm'] ?? '' ),
    ];

    $errors = mgk_validate_request_payload( $raw );
    if ( $errors ) {
        return new WP_REST_Response( [
            'code'    => 'mgk_invalid_request',
            'message' => 'Some fields need attention.',
            'errors'  => $errors,
        ], 422 );
    }

    $result = mgk_request_create_lead( $raw );
    if ( is_wp_error( $result ) ) {
        return new WP_REST_Response( [
            'code'    => $result->get_error_code(),
            'message' => 'Something went wrong. Please try again or contact us.',
        ], 500 );
    }

    if ( function_exists( 'mgk_track_event' ) ) {
        mgk_track_event( 'request_form_success', [
            'level'   => $raw['child_level'],
            'subject' => $raw['subject'],
        ] );
    }

    return rest_ensure_response( [
        'token'      => (string) $result['token'],
        'status'     => (string) $result['status'],
        'sla_due_at' => (int) $result['sla_due_at'],
    ] );
}

/**
 * S07 confirmation gate.
 *
 * The /request-match/ page is built in Elementor (split form widgets = State 1
 * only). On its own, Elementor would render the form even when the URL carries
 * a valid ?mgk_lead token after submit, so the parent never sees the "Request
 * received" confirmation (State 2). This filter swaps the page content for the
 * confirmation partial when, and only when, a real lead token is present — the
 * form is left untouched for normal (no-token) visits.
 */
add_filter( 'the_content', function ( $content ) {
    if ( is_admin() ) {
        return $content;
    }

    $token = isset( $_GET['mgk_lead'] ) ? sanitize_text_field( wp_unslash( $_GET['mgk_lead'] ) ) : '';
    if ( $token === '' ) {
        return $content; // no token → leave the form as-is
    }

    // Only act on the request-match page's main query.
    $post = get_post();
    if ( ! $post || $post->post_name !== 'request-match' || ! is_main_query() ) {
        return $content;
    }

    // Only swap when the token resolves to a real lead (else show the form).
    $view = function_exists( 'mgk_request_confirm_view' ) ? mgk_request_confirm_view( $token ) : [ 'found' => false ];
    if ( empty( $view['found'] ) ) {
        return $content;
    }

    // Priority 20 → runs AFTER Elementor's apply_builder_in_content (prio 9),
    // so the confirmation replaces the Elementor form layout for the token hit.
    return do_shortcode( '[mgk_request_confirm]' );
}, 20 );

/* ── Admin: Accept / Reject a Request Match lead (S07 review gate) ──────────
 * A new lead from S07 sits at PENDING_REVIEW. No message is sent to the parent
 * until an admin clicks Accept here. Accept → email the parent a confirmation +
 * follow-up link, move the lead to QUALIFIED (it then enters matching). Reject →
 * close the lead, send nothing. There is no time limit on the review.
 */

add_action( 'add_meta_boxes', function () {
    add_meta_box(
        'mgk_lead_review',
        'Review Request (S07 → Accept / Reject)',
        'mgk_lead_review_metabox',
        'mg_lead',
        'side',
        'high'
    );
} );

function mgk_lead_review_metabox( $post ) {
    $lead_id = (int) $post->ID;
    $state   = get_post_meta( $lead_id, 'mgk_lead_state', true ) ?: '—';
    $email   = (string) get_post_meta( $lead_id, 'mgk_lead_email', true );
    $phone   = (string) get_post_meta( $lead_id, 'mgk_lead_phone_e164', true );
    $decided = (string) get_post_meta( $lead_id, 'mgk_lead_review_decided', true );

    echo '<p><strong>State:</strong> ' . esc_html( $state ) . '</p>';
    echo '<p><strong>Parent email:</strong> ' . ( $email ? esc_html( $email ) : '<em>none</em>' ) . '</p>';
    if ( $phone ) {
        echo '<p><strong>Phone:</strong> ' . esc_html( $phone ) . '</p>';
    }

    if ( $decided ) {
        echo '<p><em>Reviewed: ' . esc_html( $decided ) . '</em></p>';
    }

    if ( $state === MGK_LEAD_PENDING_REVIEW ) {
        $accept = wp_nonce_url( admin_url( 'admin-post.php?action=mgk_lead_review&decision=accept&lead=' . $lead_id ), 'mgk_lead_review_' . $lead_id );
        $reject = wp_nonce_url( admin_url( 'admin-post.php?action=mgk_lead_review&decision=reject&lead=' . $lead_id ), 'mgk_lead_review_' . $lead_id );
        echo '<p style="display:flex;gap:8px;">';
        echo '<a href="' . esc_url( $accept ) . '" class="button button-primary" style="flex:1;text-align:center;">Accept</a>';
        echo '<a href="' . esc_url( $reject ) . '" class="button" style="flex:1;text-align:center;" onclick="return confirm(\'Reject this lead? No message will be sent.\');">Reject</a>';
        echo '</p>';
        echo '<p class="description">Accept emails the parent a confirmation + follow-up link and starts matching. Nothing is sent until you Accept.</p>';
    } elseif ( $state === MGK_LEAD_CLOSED_LOST ) {
        echo '<p style="color:#b32d2e;"><strong>Rejected.</strong></p>';
    } else {
        echo '<p style="color:#1a7f37;"><strong>Accepted</strong> — lead is in the pipeline.</p>';
    }
}

/** Email the parent their accepted request + the S08 proposals link. */
function mgk_lead_send_accept_email( $lead_id ) {
    $lead_id = (int) $lead_id;
    $to = sanitize_email( (string) get_post_meta( $lead_id, 'mgk_lead_email', true ) );
    if ( ! $to ) {
        return false;
    }
    // Magic link straight into S08 (proposals) — no login. The token resolves
    // the lead; if no proposals are attached yet, S08 shows its empty state.
    $token = (string) get_post_meta( $lead_id, 'mgk_lead_token', true );
    $link  = $token
        ? add_query_arg( 'token', rawurlencode( $token ), home_url( '/tutor-proposals/' ) )
        : home_url( '/tutor-proposals/' );
    $site  = get_bloginfo( 'name' );

    $body = sprintf(
        "Hi,\n\nGood news — we've reviewed your request and picked tutors for you.\n\n" .
        "View your matched tutors here:\n%s\n\n" .
        "No account or password needed — just tap the link above to see your tutors and book a trial.\n\n— %s",
        $link, $site
    );

    return (bool) wp_mail( $to, sprintf( '%s — your matched tutors are ready', $site ), $body );
}

add_action( 'admin_post_mgk_lead_review', function () {
    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_die( 'Permission denied.' );
    }
    $lead_id  = isset( $_GET['lead'] ) ? (int) $_GET['lead'] : 0;
    $decision = isset( $_GET['decision'] ) ? sanitize_key( $_GET['decision'] ) : '';
    check_admin_referer( 'mgk_lead_review_' . $lead_id );

    $lead = $lead_id ? get_post( $lead_id ) : null;
    if ( ! $lead || $lead->post_type !== 'mg_lead' ) {
        wp_die( 'Lead not found.' );
    }

    $state = get_post_meta( $lead_id, 'mgk_lead_state', true );
    $msg   = '';

    if ( $state !== MGK_LEAD_PENDING_REVIEW ) {
        $msg = 'already-decided';
    } elseif ( $decision === 'accept' ) {
        if ( function_exists( 'mgk_lead_transition' ) ) {
            mgk_lead_transition( $lead_id, MGK_LEAD_QUALIFIED );
        }
        update_post_meta( $lead_id, 'mgk_lead_review_decided', 'accepted ' . current_time( 'mysql' ) );
        $emailed = mgk_lead_send_accept_email( $lead_id );
        do_action( 'mgk_lead_accepted', $lead_id );
        $msg = $emailed ? 'accepted-emailed' : 'accepted-noemail';
    } elseif ( $decision === 'reject' ) {
        if ( function_exists( 'mgk_lead_transition' ) ) {
            mgk_lead_transition( $lead_id, MGK_LEAD_CLOSED_LOST );
        }
        update_post_meta( $lead_id, 'mgk_lead_review_decided', 'rejected ' . current_time( 'mysql' ) );
        do_action( 'mgk_lead_rejected', $lead_id );
        $msg = 'rejected';
    }

    wp_safe_redirect( add_query_arg( 'mgk_review', rawurlencode( $msg ), get_edit_post_link( $lead_id, 'raw' ) ) );
    exit;
} );

add_action( 'admin_notices', function () {
    if ( ! isset( $_GET['mgk_review'] ) || ! function_exists( 'get_current_screen' ) ) {
        return;
    }
    $screen = get_current_screen();
    if ( ! $screen || $screen->id !== 'mg_lead' ) {
        return;
    }
    $map = [
        'accepted-emailed' => [ 'success', 'Lead accepted — confirmation emailed to the parent. Matching can begin.' ],
        'accepted-noemail' => [ 'warning', 'Lead accepted, but no email was sent (parent email missing or mail failed).' ],
        'rejected'         => [ 'success', 'Lead rejected and closed. No message was sent.' ],
        'already-decided'  => [ 'warning', 'This lead was already reviewed.' ],
    ];
    $code = sanitize_key( wp_unslash( $_GET['mgk_review'] ) );
    if ( isset( $map[ $code ] ) ) {
        printf( '<div class="notice notice-%s is-dismissible"><p>%s</p></div>', esc_attr( $map[ $code ][0] ), esc_html( $map[ $code ][1] ) );
    }
} );
