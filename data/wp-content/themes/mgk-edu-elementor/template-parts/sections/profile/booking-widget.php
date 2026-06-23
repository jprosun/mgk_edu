<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$tutor = $args['tutor'] ?? [];
$name = $tutor['display_name'] ?? $tutor['name'] ?? 'Tutor';
$slug = $tutor['slug'] ?? sanitize_title( $name );
$rate = ! empty( $tutor['rate'] ) ? $tutor['rate'] : '$65/hr';
$response = ! empty( $tutor['response'] ) ? $tutor['response'] : '4h';
$open_slots = ! empty( $tutor['open_slots'] ) ? $tutor['open_slots'] : '12';

// Trial price + discount % come from the SAME engine/rules as the package cards,
// so the sidebar can never diverge from the "Trial lesson" package. Falls back to
// the stored ACF trial price only if the engine quote is unavailable.
$rules     = function_exists( 'mgk_discount_rules' ) ? mgk_discount_rules() : [];
$trial_pct = (int) ( $rules['trial_pct'] ?? 0 );
$trial     = ! empty( $tutor['trial'] ) ? $tutor['trial'] : '$40';
if ( ! empty( $tutor['id'] ) && function_exists( 'mgk_quote_trial_for_tutor' ) ) {
    $tq = mgk_quote_trial_for_tutor( (int) $tutor['id'] );
    if ( ! empty( $tq['total_str'] ) ) {
        $trial = $tq['total_str'];
    }
}
?>
<aside class="mgk-booking-card">
    <p>Starting from</p>
    <strong><?php echo esc_html( $rate ); ?></strong>
    <span>SGD / hour</span>
    <div class="mgk-trial-box">
        <b>Trial: <?php echo esc_html( $trial ); ?></b>
        <?php if ( $trial_pct > 0 ) : ?><span>First lesson <?php echo (int) $trial_pct; ?>% off</span><?php endif; ?>
    </div>
    <form data-mgk-event="trial_request_started" class="mgk-booking-form" action="<?php echo esc_url( mgk_get_trial_url() ); ?>" method="get">
        <input type="hidden" name="tutor" value="<?php echo esc_attr( $slug ); ?>">
        <button class="mgk-btn mgk-btn-accent" type="submit">Book Trial Lesson &rarr;</button>
    </form>
    <a class="mgk-btn mgk-btn-outline" href="<?php echo esc_url( mgk_get_trial_url( [ 'tutor' => $slug, 'message' => 1 ] ) ); ?>">Message Tutor</a>
    <a class="mgk-btn mgk-btn-outline" href="#availability">View Full Schedule</a>
    <small>Response in <?php echo esc_html( $response ); ?> · <?php echo esc_html( $open_slots ); ?> slots open</small>
</aside>
