<?php
/**
 * S12 — Tutor contact card (revealed after a paid booking — NFR-10).
 *
 * Unlock state + the actual phone/email + the message route all come from the
 * locked view ($args['contact'], $args['urls']). Elementor cannot change the
 * unlock logic; only the status / CTA / note copy is SAFE.
 *
 * @var array $args
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$a    = (array) ( $args ?? [] );
$c    = (array) ( $a['contact'] ?? [] );
$urls = (array) ( $a['urls'] ?? [] );

$unlocked = ! empty( $c['unlocked'] );
$name  = (string) ( $c['name'] ?? 'Ms Lee Yi Ling' );
$short = (string) ( $c['short'] ?? $name );

$unlocked_label = $a['unlocked_label'] ?? '🔓 CONTACT NOW UNLOCKED';
$locked_label   = '🔒 CONTACT UNLOCKS AFTER BOOKING';
$cta_label      = $a['cta_label'] ?? ( '💬 Message ' . $short );
$masked_note    = $a['masked_note'] ?? 'CONTACT WAS MASKED BEFORE BOOKING (NFR-10).';

$msg_url = (string) ( $urls['message'] ?? home_url( '/messages/' ) );
$avatar  = (string) ( $c['avatar'] ?? '' );
?>
<section class="mgk-cf-card mgk-cf-contact<?php echo $unlocked ? ' is-unlocked' : ' is-locked'; ?>" data-event="confirm_contact_view">
    <p class="mgk-cf-contact-status"><?php echo esc_html( $unlocked ? $unlocked_label : $locked_label ); ?></p>

    <div class="mgk-cf-contact-row">
        <span class="mgk-cf-contact-avatar"<?php echo $avatar ? ' style="background-image:url(' . esc_url( $avatar ) . ')"' : ''; ?> aria-hidden="true"><?php echo $avatar ? '' : 'Av'; ?></span>
        <div class="mgk-cf-contact-info">
            <p class="mgk-cf-contact-name"><?php echo esc_html( $name ); ?></p>
            <p class="mgk-cf-contact-line">📱 <?php echo esc_html( $c['phone'] ?? '' ); ?></p>
            <p class="mgk-cf-contact-line">✉ <?php echo esc_html( $c['email'] ?? '' ); ?></p>
        </div>
    </div>

    <a class="mgk-cf-contact-cta<?php echo $unlocked ? '' : ' is-disabled'; ?>"
       href="<?php echo esc_url( $unlocked ? $msg_url : '#' ); ?>"
       data-event="message_tutor_click"<?php echo $unlocked ? '' : ' aria-disabled="true" tabindex="-1"'; ?>>
        <?php echo esc_html( $cta_label ); ?>
    </a>

    <p class="mgk-cf-contact-note"><?php echo esc_html( $masked_note ); ?></p>
</section>
