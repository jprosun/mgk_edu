<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$atts = $args['atts'] ?? [];
$ctx = $args['context'] ?? [];
$compose = $ctx['compose'] ?? [];
$photo = $compose['photo'] ?? [];
$lesson_refs = $compose['lesson_refs'] ?? [];
?>
<div class="mgk-parent-message-compose-modal" data-mgk-message-compose-modal hidden>
    <div class="mgk-parent-message-compose-modal__backdrop" data-mgk-message-compose-close></div>
    <section class="mgk-parent-message-compose" role="dialog" aria-modal="true" aria-label="<?php echo esc_attr( $atts['compose_heading'] ?? 'PHOTO + LESSON-REF COMPOSE' ); ?>">
        <header class="mgk-parent-message-compose__top">
            <h2><?php echo esc_html( $atts['compose_heading'] ?? 'PHOTO + LESSON-REF COMPOSE' ); ?></h2>
            <span><?php echo esc_html( $atts['compose_kicker'] ?? 'MESSAGE TYPES' ); ?></span>
            <button type="button" aria-label="Close" data-mgk-message-compose-close>×</button>
        </header>
        <div class="mgk-parent-message-compose__shell">
            <div class="mgk-parent-message-compose__photo">
                <h3><?php echo esc_html( $atts['photo_heading'] ?? 'Attaching a photo' ); ?></h3>
                <div class="mgk-parent-message-compose__photo-box">
                    <div class="mgk-parent-message-compose__preview">
                        <span><?php echo esc_html( $atts['preview_label'] ?? 'preview ·' ); ?></span>
                        <strong><?php echo esc_html( $photo['name'] ?? 'IMG_2231.jpg' ); ?></strong>
                    </div>
                    <div class="mgk-parent-message-compose__photo-meta">
                        <button type="button"><?php echo esc_html( $atts['remove_label'] ?? '× Remove' ); ?></button>
                        <span><?php echo esc_html( $photo['status'] ?? 'AUTO-SCANNED · PDPA: STORED IN-PLATFORM ONLY' ); ?></span>
                    </div>
                </div>
            </div>
            <div class="mgk-parent-message-compose__lesson">
                <h3><?php echo esc_html( $atts['lesson_heading'] ?? 'Sharing a lesson reference' ); ?></h3>
                <div class="mgk-parent-message-compose__lesson-box">
                    <strong><?php echo esc_html( $atts['pick_label'] ?? '📎 PICK A LESSON TO LINK' ); ?></strong>
                    <?php foreach ( $lesson_refs as $ref ) : ?>
                        <button type="button" data-lesson-ref="<?php echo esc_attr( $ref['id'] ?? '' ); ?>"><?php echo esc_html( $ref['label'] ?? '' ); ?></button>
                    <?php endforeach; ?>
                </div>
            </div>
            <footer class="mgk-parent-message-compose__note">
                <?php echo esc_html( $atts['compose_note'] ?? 'THREE MESSAGE TYPES: TEXT, PHOTO, LESSON-REFERENCE. READ RECEIPTS (✓ SENT / ✓✓ READ) ON EVERY MESSAGE.' ); ?>
            </footer>
        </div>
    </section>
</div>
