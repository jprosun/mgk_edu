<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$tutor = $args['tutor'] ?? [];
$tutor_slug = $tutor['slug'] ?? sanitize_title( $tutor['name'] ?? '' );
?>
<a class="mgk-profile-sticky" href="<?php echo esc_url( mgk_get_trial_url( [ 'tutor' => $tutor_slug ] ) ); ?>" data-mgk-mobile-sticky data-mgk-event="trial_cta_clicked">
    <span><b><?php echo esc_html( $tutor['rate'] ); ?> · Trial <?php echo esc_html( $tutor['trial'] ); ?></b><small>Response <?php echo esc_html( $tutor['response'] ); ?></small></span>
    <strong>Book Trial →</strong>
</a>
