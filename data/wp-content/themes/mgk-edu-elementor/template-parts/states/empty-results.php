<?php
/**
 * Empty listing state.
 *
 * @var array $args
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$title = $args['title'] ?? 'No tutors match these filters';
$message = $args['message'] ?? 'Try broadening the subject, location, budget, or tutor tier.';
?>
<div class="mgk-state-panel mgk-empty-results">
    <p class="mgk-eyebrow">No results</p>
    <h2><?php echo esc_html( $title ); ?></h2>
    <p><?php echo esc_html( $message ); ?></p>
    <a class="mgk-btn mgk-btn-accent" href="<?php echo esc_url( mgk_cta_url( 'browse' ) ); ?>">Clear filters</a>
</div>
