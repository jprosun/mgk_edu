<?php
/**
 * MGK WhatsApp — Meta WhatsApp Cloud API integration.
 *
 * Sends business-initiated template messages via Meta's Graph API. Credentials
 * live in the `mgk_whatsapp` option (set under MGK Site Settings → WhatsApp),
 * never hardcoded.
 *
 * DEMO-SAFE: if credentials are missing (or sending is toggled off), nothing is
 * sent — the attempt is recorded to the `mgk_wa_log` option so you can see what
 * WOULD have been sent. The rest of the request-match flow (lead creation, SLA,
 * confirmation screen) runs exactly the same with or without WhatsApp configured.
 *
 * Meta requires business-initiated messages to use a pre-approved TEMPLATE — you
 * cannot send free-form text outside a 24h customer window. So we send by
 * template name + ordered body parameters.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

const MGK_WA_OPTION   = 'mgk_whatsapp';   // credentials + template config
const MGK_WA_LOG      = 'mgk_wa_log';     // ring buffer of recent send attempts
const MGK_WA_LOG_MAX  = 50;
const MGK_WA_GRAPH_VER = 'v21.0';

/** Read the WhatsApp config (merged with safe defaults). */
function mgk_wa_config() {
    $defaults = [
        'enabled'             => 0,
        'phone_number_id'     => '',
        'access_token'        => '',
        'admin_notify_number' => '',          // E.164, e.g. +6591234567
        'tpl_parent_confirm'  => '',          // approved template name (S07 submit)
        'tpl_parent_lang'     => 'en',
        'tpl_admin_lead'      => '',
        'tpl_admin_lang'      => 'en',
        'tpl_proposals_ready' => '',          // S08 "your matches are ready"
        'tpl_proposals_lang'  => 'en',
        'tpl_expired_agency'  => '',          // admin alert: proposals expired (48h)
    ];
    $cfg = get_option( MGK_WA_OPTION, [] );
    return is_array( $cfg ) ? array_merge( $defaults, $cfg ) : $defaults;
}

/** True only when we have everything needed to actually call the API. */
function mgk_wa_is_live() {
    $cfg = mgk_wa_config();
    return ! empty( $cfg['enabled'] )
        && $cfg['phone_number_id'] !== ''
        && $cfg['access_token'] !== '';
}

/** Append an entry to the demo/audit log (most-recent-first, capped). */
function mgk_wa_log( $entry ) {
    $log = get_option( MGK_WA_LOG, [] );
    if ( ! is_array( $log ) ) $log = [];
    array_unshift( $log, $entry );
    if ( count( $log ) > MGK_WA_LOG_MAX ) {
        $log = array_slice( $log, 0, MGK_WA_LOG_MAX );
    }
    update_option( MGK_WA_LOG, $log, false );
}

/**
 * Send a WhatsApp TEMPLATE message.
 *
 * @param string $to_e164    Recipient in E.164 (+65...). Falsy → skipped.
 * @param string $template   Approved template name.
 * @param array  $body_params Ordered string values for the template body {{1}},{{2}}…
 * @param string $lang        Template language code (default 'en').
 * @return array{ok:bool,mode:string,detail:string}
 */
function mgk_wa_send( $to_e164, $template, array $body_params = [], $lang = 'en' ) {
    $to_e164  = trim( (string) $to_e164 );
    $template = trim( (string) $template );

    $base = [
        'to'       => $to_e164,
        'template' => $template,
        'params'   => $body_params,
        'time'     => current_time( 'mysql', true ),
    ];

    if ( $to_e164 === '' ) {
        mgk_wa_log( $base + [ 'mode' => 'skipped', 'detail' => 'no recipient number' ] );
        return [ 'ok' => false, 'mode' => 'skipped', 'detail' => 'no recipient number' ];
    }

    // DEMO mode — not configured or disabled: log what would have been sent.
    if ( ! mgk_wa_is_live() || $template === '' ) {
        $why = $template === '' ? 'no template name configured' : 'WhatsApp not configured/enabled';
        mgk_wa_log( $base + [ 'mode' => 'demo', 'detail' => $why ] );
        return [ 'ok' => true, 'mode' => 'demo', 'detail' => $why ];
    }

    $cfg = mgk_wa_config();

    // Build the template components: body parameters as text.
    $components = [];
    if ( $body_params ) {
        $components[] = [
            'type'       => 'body',
            'parameters' => array_map( function ( $v ) {
                return [ 'type' => 'text', 'text' => (string) $v ];
            }, array_values( $body_params ) ),
        ];
    }

    $payload = [
        'messaging_product' => 'whatsapp',
        'to'                => preg_replace( '/[^\d]/', '', $to_e164 ), // Graph wants digits only
        'type'              => 'template',
        'template'          => [
            'name'     => $template,
            'language' => [ 'code' => $lang ?: 'en' ],
        ],
    ];
    if ( $components ) {
        $payload['template']['components'] = $components;
    }

    $url = sprintf(
        'https://graph.facebook.com/%s/%s/messages',
        MGK_WA_GRAPH_VER,
        rawurlencode( $cfg['phone_number_id'] )
    );

    $resp = wp_remote_post( $url, [
        'timeout' => 15,
        'headers' => [
            'Authorization' => 'Bearer ' . $cfg['access_token'],
            'Content-Type'  => 'application/json',
        ],
        'body'    => wp_json_encode( $payload ),
    ] );

    if ( is_wp_error( $resp ) ) {
        $detail = $resp->get_error_message();
        mgk_wa_log( $base + [ 'mode' => 'error', 'detail' => $detail ] );
        return [ 'ok' => false, 'mode' => 'error', 'detail' => $detail ];
    }

    $code = (int) wp_remote_retrieve_response_code( $resp );
    $body = wp_remote_retrieve_body( $resp );

    if ( $code >= 200 && $code < 300 ) {
        mgk_wa_log( $base + [ 'mode' => 'sent', 'detail' => 'HTTP ' . $code ] );
        return [ 'ok' => true, 'mode' => 'sent', 'detail' => 'HTTP ' . $code ];
    }

    // API rejected (bad token, template not approved, etc.) — log full body for debugging.
    mgk_wa_log( $base + [ 'mode' => 'error', 'detail' => 'HTTP ' . $code . ': ' . mb_substr( (string) $body, 0, 300 ) ] );
    return [ 'ok' => false, 'mode' => 'error', 'detail' => 'HTTP ' . $code ];
}

/* ── Hook into the Request Match (S07) flow ──────────────────────────────────
 * mgk_request_match_created fires in inc/mgk-forms.php right after the lead is
 * created. We only READ the lead here and fire messages — we never change the
 * lead/SLA logic, so the funnel behaves identically whether or not WA is set up.
 */
// NOTE: auto-send on submit is DISABLED by design. A new lead must be reviewed
// (Accept/Reject) by an admin before any message goes out — see the Accept
// action in inc/mgk-forms.php (mgk_lead_accept). To re-enable instant parent
// confirmation on submit, uncomment the next line.
// add_action( 'mgk_request_match_created', 'mgk_wa_on_request_match', 10, 2 );

function mgk_wa_on_request_match( $lead_id, $payload ) {
    $lead_id = (int) $lead_id;

    // Human-readable subject/level for the message body.
    $subject = '';
    $level   = '';
    if ( $lead_id && function_exists( 'get_post_meta' ) ) {
        $subject = (string) get_post_meta( $lead_id, 'mgk_lead_subject', true );
        $level   = (string) get_post_meta( $lead_id, 'mgk_lead_child_level', true );
    }
    // Fall back to the raw payload when meta isn't populated (e.g. demo lead id 0).
    $subject = $subject ?: (string) ( $payload['subject'] ?? '' );
    $level   = $level   ?: (string) ( $payload['level'] ?? '' );

    $parent_phone = (string) ( $payload['phone_e164'] ?? $payload['phone'] ?? '' );
    $track_url    = home_url( '/request-match/' );

    $cfg = mgk_wa_config();

    /* 1) Parent confirmation — subject, level, SLA window, tracking link. */
    mgk_wa_send(
        $parent_phone,
        $cfg['tpl_parent_confirm'],
        [
            $subject ?: 'your subject',
            $level   ?: 'your level',
            '6',                     // SLA hours
            $track_url,
        ],
        $cfg['tpl_parent_lang']
    );

    /* 2) Admin / agency new-lead alert. */
    if ( ! empty( $cfg['admin_notify_number'] ) ) {
        mgk_wa_send(
            $cfg['admin_notify_number'],
            $cfg['tpl_admin_lead'],
            [
                $subject ?: '—',
                $level   ?: '—',
                (string) ( $lead_id ?: 'demo' ),
            ],
            $cfg['tpl_admin_lang']
        );
    }
}

/**
 * Send the "your matches are ready" WhatsApp when the agency sends proposals.
 * Called from mgk_send_proposals() (inc/mgk-proposals.php). The link is the S08
 * magic link carrying the lead's token, so the parent lands straight on their
 * personalised proposals with no login.
 *
 * @return array send result from mgk_wa_send()
 */
function mgk_wa_send_proposals_ready( $lead_id, $magic_link, $subject = '', $level = '', $count = 0 ) {
    $lead_id = (int) $lead_id;
    $phone   = $lead_id ? (string) get_post_meta( $lead_id, 'mgk_lead_parent_phone', true ) : '';
    $cfg     = mgk_wa_config();

    return mgk_wa_send(
        $phone,
        $cfg['tpl_proposals_ready'],
        [
            $subject ?: 'your subject',
            $level   ?: 'your level',
            (string) ( $count ?: 0 ),
            (string) $magic_link,
        ],
        $cfg['tpl_proposals_lang']
    );
}

/* ── Admin: WhatsApp settings page (submenu under MGK Site) ───────────────── */

add_action( 'admin_menu', function () {
    add_submenu_page(
        'mgk-site-settings',
        'WhatsApp (Meta Cloud API)',
        'WhatsApp',
        'manage_options',
        'mgk-whatsapp',
        'mgk_wa_settings_page'
    );
}, 20 );

function mgk_wa_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) return;
    $cfg = mgk_wa_config();
    $log = get_option( MGK_WA_LOG, [] );
    $live = mgk_wa_is_live();

    $fields = [
        'enabled'             => [ 'Enable WhatsApp sending', 'checkbox', 'Off = demo mode: messages are logged below but not sent.' ],
        'phone_number_id'     => [ 'Phone Number ID', 'text', 'From Meta → WhatsApp → API Setup.' ],
        'access_token'        => [ 'Access Token', 'password', 'Permanent token. Stored in the database — use a system user token, not your personal one.' ],
        'admin_notify_number' => [ 'Admin notify number (E.164)', 'text', 'e.g. +6591234567 — receives a new-lead alert. Leave empty to skip.' ],
        'tpl_parent_confirm'  => [ 'Parent confirm template name', 'text', 'Approved template. Body vars in order: {{1}} subject, {{2}} level, {{3}} SLA hours, {{4}} tracking link.' ],
        'tpl_parent_lang'     => [ 'Parent template language', 'text', 'e.g. en, en_US, vi.' ],
        'tpl_admin_lead'      => [ 'Admin lead template name', 'text', 'Body vars: {{1}} subject, {{2}} level, {{3}} lead id.' ],
        'tpl_admin_lang'      => [ 'Admin template language', 'text', '' ],
        'tpl_proposals_ready' => [ 'Proposals-ready template name', 'text', 'Sent when the agency clicks "Send Proposals". Body vars: {{1}} subject, {{2}} level, {{3}} tutor count, {{4}} S08 magic link.' ],
        'tpl_proposals_lang'  => [ 'Proposals-ready language', 'text', '' ],
        'tpl_expired_agency'  => [ 'Expired-alert template (to admin)', 'text', 'Sent to the admin number when a lead\'s proposals lapse (48h). Body vars: {{1}} subject, {{2}} level, {{3}} lead id.' ],
    ];
    ?>
    <div class="wrap">
        <h1>WhatsApp (Meta Cloud API)</h1>
        <p>
            Status:
            <?php if ( $live ) : ?>
                <strong style="color:#1a7f37;">LIVE</strong> — messages are sent via Meta.
            <?php else : ?>
                <strong style="color:#b35900;">DEMO</strong> — not configured/enabled. The Request Match flow still works; outgoing messages are only logged below.
            <?php endif; ?>
        </p>
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <input type="hidden" name="action" value="mgk_save_whatsapp">
            <?php wp_nonce_field( 'mgk_save_whatsapp' ); ?>
            <table class="form-table" role="presentation"><tbody>
            <?php foreach ( $fields as $key => $f ) :
                [ $label, $type, $help ] = $f;
                $id = 'mgk_wa_' . $key;
                $val = $cfg[ $key ] ?? '';
            ?>
                <tr>
                    <th scope="row"><label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label></th>
                    <td>
                        <?php if ( $type === 'checkbox' ) : ?>
                            <label>
                                <input type="hidden" name="mgk_wa[<?php echo esc_attr( $key ); ?>]" value="0">
                                <input type="checkbox" id="<?php echo esc_attr( $id ); ?>" name="mgk_wa[<?php echo esc_attr( $key ); ?>]" value="1" <?php checked( $val, '1' ); ?>> Enabled
                            </label>
                        <?php elseif ( $type === 'password' ) : ?>
                            <input type="password" class="large-text" id="<?php echo esc_attr( $id ); ?>" name="mgk_wa[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $val ); ?>" autocomplete="off">
                        <?php else : ?>
                            <input type="text" class="regular-text" id="<?php echo esc_attr( $id ); ?>" name="mgk_wa[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $val ); ?>">
                        <?php endif; ?>
                        <?php if ( $help ) : ?><p class="description"><?php echo esc_html( $help ); ?></p><?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody></table>
            <?php submit_button( 'Save WhatsApp settings' ); ?>
        </form>

        <h2>Recent send log (last <?php echo (int) MGK_WA_LOG_MAX; ?>)</h2>
        <?php if ( empty( $log ) ) : ?>
            <p>No messages yet. Submit the Request Match form (S07) to see entries here.</p>
        <?php else : ?>
            <table class="widefat striped"><thead><tr>
                <th>Time (UTC)</th><th>Mode</th><th>To</th><th>Template</th><th>Params</th><th>Detail</th>
            </tr></thead><tbody>
            <?php foreach ( $log as $row ) : ?>
                <tr>
                    <td><?php echo esc_html( $row['time'] ?? '' ); ?></td>
                    <td><?php echo esc_html( strtoupper( $row['mode'] ?? '' ) ); ?></td>
                    <td><?php echo esc_html( $row['to'] ?? '' ); ?></td>
                    <td><?php echo esc_html( $row['template'] ?? '' ); ?></td>
                    <td><?php echo esc_html( implode( ' | ', array_map( 'strval', (array) ( $row['params'] ?? [] ) ) ) ); ?></td>
                    <td><?php echo esc_html( $row['detail'] ?? '' ); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody></table>
        <?php endif; ?>
    </div>
    <?php
}

add_action( 'admin_post_mgk_save_whatsapp', function () {
    if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Permission denied.' );
    check_admin_referer( 'mgk_save_whatsapp' );

    $in  = isset( $_POST['mgk_wa'] ) ? (array) wp_unslash( $_POST['mgk_wa'] ) : [];
    $cfg = [
        'enabled'             => ! empty( $in['enabled'] ) ? 1 : 0,
        'phone_number_id'     => sanitize_text_field( $in['phone_number_id'] ?? '' ),
        'access_token'        => trim( sanitize_text_field( $in['access_token'] ?? '' ) ),
        'admin_notify_number' => sanitize_text_field( $in['admin_notify_number'] ?? '' ),
        'tpl_parent_confirm'  => sanitize_text_field( $in['tpl_parent_confirm'] ?? '' ),
        'tpl_parent_lang'     => sanitize_text_field( $in['tpl_parent_lang'] ?? 'en' ),
        'tpl_admin_lead'      => sanitize_text_field( $in['tpl_admin_lead'] ?? '' ),
        'tpl_admin_lang'      => sanitize_text_field( $in['tpl_admin_lang'] ?? 'en' ),
        'tpl_proposals_ready' => sanitize_text_field( $in['tpl_proposals_ready'] ?? '' ),
        'tpl_proposals_lang'  => sanitize_text_field( $in['tpl_proposals_lang'] ?? 'en' ),
        'tpl_expired_agency'  => sanitize_text_field( $in['tpl_expired_agency'] ?? '' ),
    ];
    update_option( MGK_WA_OPTION, $cfg, false );

    wp_safe_redirect( add_query_arg( 'updated', '1', admin_url( 'admin.php?page=mgk-whatsapp' ) ) );
    exit;
} );
