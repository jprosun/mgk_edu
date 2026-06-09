<?php
/**
 * S09 — Chosen tutor summary card.
 *
 * DATA (name, tier/exp/credential, rating, reviews, verification) comes from the
 * locked tutor context ($args['tutor']) — NOT editable in Elementor. Subjects /
 * location lines + the heading + change/back link labels are SAFE copy.
 *
 * @var array $args
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$a       = (array) ( $args ?? [] );
$tutor   = (array) ( $a['tutor'] ?? [] );
$context = (array) ( $a['context'] ?? [] );

$name   = (string) ( $tutor['name'] ?? 'Your tutor' );
$tier   = trim( (string) ( $tutor['tier'] ?? '' ) );
$exp    = trim( (string) ( $tutor['experience'] ?? '' ) );
$cred   = trim( (string) ( $tutor['credential'] ?? '' ) );
$meta   = strtoupper( implode( ' · ', array_filter( [ $tier, $exp, $cred ] ) ) );
$rating = (string) ( $tutor['rating'] ?? '' );
$reviews= (string) ( $tutor['reviews'] ?? '' );
$photo  = (string) ( $tutor['photo'] ?? '' );
$badge  = function_exists( 'mgk_get_tutor_verification_badge' ) ? mgk_get_tutor_verification_badge( $tutor ) : '';

$back_url = function_exists( 'mgk_get_back_to_proposals_url' )
    ? mgk_get_back_to_proposals_url( $context['lead_token'] ?? '' )
    : home_url( '/tutor-proposals/' );

// SAFE copy (Elementor-editable shell).
$heading      = $a['heading']      ?? 'You’re booking a trial with:';
$subjects     = $a['subjects']     ?? '★' . ( $rating !== '' ? $rating : '4.9' ) . ' (' . ( $reviews !== '' ? $reviews : '87' ) . ') · P5-P6 MATH, PSLE SCI · ENGLISH, MANDARIN';
$location     = $a['location']     ?? '📍 STUDENT HOME (CENTRAL) · ONLINE AVAILABLE';
$change_label = $a['change_label'] ?? '← CHANGE TUTOR';
$back_label   = $a['back_label']   ?? '/ BACK TO PROPOSALS';
$avatar_label = $a['avatar_label'] ?? 'Avatar';
?>
<div class="mgk-bk-chosen">
    <?php if ( ( $a['hide_heading'] ?? '' ) !== 'yes' ) : ?>
    <h1 class="mgk-bk-chosen-heading"><?php echo esc_html( $heading ); ?></h1>
    <?php endif; ?>

    <article class="mgk-bk-tutor-card" data-event="booking_select_tutor_view" data-tutor="<?php echo esc_attr( $tutor['slug'] ?? '' ); ?>">
        <div class="mgk-bk-tutor-avatar">
            <?php if ( $photo ) : ?>
                <img src="<?php echo esc_url( $photo ); ?>" alt="<?php echo esc_attr( $name ); ?>">
            <?php else : ?>
                <span class="mgk-bk-avatar-ph"><?php echo esc_html( $avatar_label ); ?></span>
            <?php endif; ?>
            <?php if ( $badge ) : ?>
            <span class="mgk-bk-verified"><?php echo esc_html( $badge ); ?></span>
            <?php endif; ?>
        </div>
        <div class="mgk-bk-tutor-body">
            <h2 class="mgk-bk-tutor-name"><?php echo esc_html( $name ); ?></h2>
            <?php if ( $meta ) : ?><p class="mgk-bk-tutor-meta"><?php echo esc_html( $meta ); ?></p><?php endif; ?>
            <p class="mgk-bk-tutor-detail"><?php echo esc_html( $subjects ); ?></p>
            <p class="mgk-bk-tutor-detail"><?php echo esc_html( $location ); ?></p>
            <p class="mgk-bk-tutor-links">
                <a href="<?php echo esc_url( $back_url ); ?>" class="mgk-bk-link" data-event="booking_change_tutor_click"><?php echo esc_html( $change_label ); ?></a>
                <a href="<?php echo esc_url( $back_url ); ?>" class="mgk-bk-link" data-event="booking_back_to_proposals_click"><?php echo esc_html( $back_label ); ?></a>
            </p>
        </div>
    </article>
</div>
