<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$tutor = $args['tutor'] ?? [];
$name = $tutor['display_name'] ?? $tutor['name'] ?? 'Tutor';
$slug = $tutor['slug'] ?? sanitize_title( $name );
$rate = ! empty( $tutor['rate'] ) ? $tutor['rate'] : '$65/hr';
$trial = ! empty( $tutor['trial'] ) ? $tutor['trial'] : '$40';
$response = ! empty( $tutor['response'] ) ? $tutor['response'] : '4h';
$open_slots = ! empty( $tutor['open_slots'] ) ? $tutor['open_slots'] : '12';
?>
<aside class="mgk-booking-card">
    <p>Starting from</p>
    <strong><?php echo esc_html( $rate ); ?></strong>
    <span>SGD / hour</span>
    <div class="mgk-trial-box">
        <b>Trial: <?php echo esc_html( $trial ); ?></b>
        <span>First lesson 40% off</span>
    </div>
    <form data-mgk-event="trial_request_started" class="mgk-booking-form" action="<?php echo esc_url( mgk_get_trial_url() ); ?>" method="get">
        <input type="hidden" name="tutor" value="<?php echo esc_attr( $slug ); ?>">
        <button class="mgk-btn mgk-btn-accent" type="submit">Book Trial Lesson &rarr;</button>
    </form>
    <a class="mgk-btn mgk-btn-outline" href="<?php echo esc_url( mgk_get_trial_url( [ 'tutor' => $slug, 'message' => 1 ] ) ); ?>">Message Tutor</a>
    <a class="mgk-btn mgk-btn-outline" href="#availability">View Full Schedule</a>
    <small>Response in <?php echo esc_html( $response ); ?> · <?php echo esc_html( $open_slots ); ?> slots open</small>
</aside>
