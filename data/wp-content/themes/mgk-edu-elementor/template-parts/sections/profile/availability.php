<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$tutor = $args['tutor'] ?? [];
$tutor_slug = $tutor['slug'] ?? sanitize_title( $tutor['name'] ?? '' );
?>
<section id="availability" class="mgk-section mgk-section-surface">
    <div class="mgk-shell">
        <div class="mgk-profile-section-row">
            <?php mgk_render_section_heading( 'Available this week', '', $tutor['open_slots'] . ' slots open · Click to book' ); ?>
            <div class="mgk-week-switcher"><button>Last week</button><strong>This week</strong><button>Next week</button></div>
        </div>
        <div class="mgk-availability-grid">
            <?php foreach ( $tutor['availability'] as $day => $slots ) : ?>
                <div class="mgk-day">
                    <b><?php echo esc_html( $day ); ?></b>
                    <?php if ( $slots ) : foreach ( $slots as $slot ) : ?>
                        <a href="<?php echo esc_url( mgk_get_trial_url( [ 'tutor' => $tutor_slug, 'slot' => $slot ] ) ); ?>"><?php echo esc_html( $slot ); ?></a>
                    <?php endforeach; else : ?>
                        <span>Full</span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <a class="mgk-check" href="<?php echo esc_url( mgk_get_trial_url( [ 'tutor' => $tutor_slug, 'custom_time' => 1 ] ) ); ?>">Request custom time →</a>
    </div>
</section>
