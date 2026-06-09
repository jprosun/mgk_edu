<?php
/**
 * S07 — Request Match · STATE 2 (post-submit confirmation).
 *
 * Presentation only. The countdown, SLA due time, lead state, masking and
 * proposal-count logic are LOCKED in mgk-forms.php. $args carries SAFE
 * marketing copy from the Elementor widget shell. PII is always masked.
 *
 * @var array $args
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$token = isset( $_GET['mgk_lead'] ) ? sanitize_text_field( wp_unslash( $_GET['mgk_lead'] ) ) : '';
$view  = function_exists( 'mgk_request_confirm_view' )
    ? mgk_request_confirm_view( $token )
    : [ 'found' => false, 'sla_due_at' => time() + 6 * 3600, 'email_mask' => '', 'phone_mask' => '+65 9XXX XXXX', 'subject' => '', 'level' => '' ];

$a = wp_parse_args( (array) $args, [
    'heading'      => 'Request received!',
    'subheading'   => 'WE’RE REVIEWING YOUR REQUEST.',
    // Email-only + manual review: no SMS, no fixed countdown (timing depends on
    // the agency accepting the request).
    'reassure'     => 'Once we’ve hand-picked your tutors, we’ll email you a link to view them and book a trial.',
    'btn_browse'   => 'Browse tutors meanwhile →',
    'btn_how'      => 'How matching works',
] );

// Browse link preserves subject + level when known.
$browse_args = [];
if ( ! empty( $view['subject'] ) ) $browse_args['subject'] = $view['subject'];
if ( ! empty( $view['level'] ) )   $browse_args['level']   = $view['level'];
$browse_url = add_query_arg( $browse_args, home_url( '/student/teachers/' ) );

// Email-only: show the masked email we'll send to (no phone/SMS).
$email_mask = $view['email_mask'] !== '' ? $view['email_mask'] : '';
?>
<div class="mgk-rq-confirm" data-mgk-confirm
     data-event="request_confirm_view" data-screen="request_confirm">

    <div class="mgk-rq-success" aria-hidden="true">
        <span class="mgk-rq-success-check">&#10003;</span>
    </div>

    <h1 class="mgk-rq-confirm-head"><?php echo esc_html( $a['heading'] ); ?></h1>
    <p class="mgk-rq-confirm-sub"><?php echo esc_html( $a['subheading'] ); ?></p>

    <div class="mgk-rq-reassure">
        <p class="mgk-rq-reassure-main"><?php echo esc_html( $a['reassure'] ); ?></p>
        <?php if ( $email_mask ) : ?>
            <p class="mgk-rq-reassure-sub">WE’LL EMAIL: <?php echo esc_html( $email_mask ); ?></p>
        <?php endif; ?>
    </div>

    <div class="mgk-rq-actions">
        <a class="mgk-rq-outbtn" href="<?php echo esc_url( $browse_url ); ?>"
           data-event="cta_click" data-screen="request_confirm"><?php echo esc_html( $a['btn_browse'] ); ?></a>
        <a class="mgk-rq-outbtn" href="<?php echo esc_url( home_url( '/how-it-works/' ) ); ?>"
           data-event="cta_click" data-screen="request_confirm"><?php echo esc_html( $a['btn_how'] ); ?></a>
    </div>
</div>
