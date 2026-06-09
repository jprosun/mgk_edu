<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$tutor = $args['tutor'] ?? [];
$subjects = ! empty( $tutor['subjects'] ) && is_array( $tutor['subjects'] ) ? implode( ', ', $tutor['subjects'] ) : 'Math, Science';
$locations = ! empty( $tutor['locations'] ) && is_array( $tutor['locations'] ) ? implode( ' · ', $tutor['locations'] ) : 'Student home · Online';
$duration = $tutor['duration'] ?? '1.5h or 2h · 1-2x/week';
$languages = $tutor['languages'] ?? 'English';
?>
<section class="mgk-profile-quick">
    <div class="mgk-shell mgk-profile-quick-grid">
        <div><b>Subjects</b><span><?php echo esc_html( $subjects ); ?></span></div>
        <div><b>Location</b><span><?php echo esc_html( $locations ); ?></span></div>
        <div><b>Duration</b><span><?php echo esc_html( $duration ); ?></span></div>
        <div><b>Languages</b><span><?php echo esc_html( $languages ); ?></span></div>
    </div>
</section>
