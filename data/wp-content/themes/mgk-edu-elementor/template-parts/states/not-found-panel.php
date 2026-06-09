<?php
/**
 * Generic not-found panel.
 *
 * @var array $args
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$title = $args['title'] ?? 'Profile not found';
$message = $args['message'] ?? 'This tutor profile is not available. Browse active tutors instead.';
?>
<div class="mgk-state-panel mgk-not-found-panel">
    <p class="mgk-eyebrow">Not found</p>
    <h1><?php echo esc_html( $title ); ?></h1>
    <p><?php echo esc_html( $message ); ?></p>
    <a class="mgk-btn mgk-btn-accent" href="<?php echo esc_url( mgk_cta_url( 'browse' ) ); ?>">Browse tutors</a>
</div>
