<?php
/**
 * Reusable CTA band.
 *
 * @var array $args
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$title = $args['title'] ?? 'Ready to find your tutor?';
$body = $args['body'] ?? 'Match in 6 hours. No upfront fees.';
$primary = $args['primary'] ?? [ 'label' => 'Find Tutor Now →', 'url' => mgk_cta_url( 'find-tutor' ) ];
$secondary = $args['secondary'] ?? [ 'label' => 'Browse All Tutors', 'url' => mgk_cta_url( 'browse' ) ];
$note = $args['note'] ?? '';
?>
<section class="mgk-section mgk-section-accent mgk-cta-band">
    <div class="mgk-shell">
        <h2><?php echo esc_html( $title ); ?></h2>
        <p><?php echo esc_html( $body ); ?></p>
        <div class="mgk-cta-actions">
            <a class="mgk-btn mgk-btn-light" href="<?php echo esc_url( $primary['url'] ?? mgk_cta_url( 'find-tutor' ) ); ?>" data-event="cta_click" data-cta="final_find_tutor_mobile"><?php echo esc_html( $primary['label'] ?? 'Find Tutor Now →' ); ?></a>
            <?php if ( $secondary ) : ?>
                <a class="mgk-btn mgk-btn-accent" href="<?php echo esc_url( $secondary['url'] ?? mgk_cta_url( 'browse' ) ); ?>"><?php echo esc_html( $secondary['label'] ?? 'Browse All Tutors' ); ?></a>
            <?php endif; ?>
        </div>
        <?php if ( $note ) : ?><p class="mgk-cta-note"><?php echo esc_html( $note ); ?></p><?php endif; ?>
    </div>
</section>
