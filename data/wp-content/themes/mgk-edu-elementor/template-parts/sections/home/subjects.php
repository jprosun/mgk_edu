<?php
/** S01 Subjects grid. @var array $args — heading */
if ( ! defined( 'ABSPATH' ) ) exit;
$heading  = $args['heading'] ?? mgk_site_setting( 'subjects_heading' );
$subjects = mgk_get_subjects();
?>
    <section class="mgk-section mgk-section-surface mgk-home-subjects">
        <div class="mgk-shell">
            <?php mgk_render_section_heading( $heading ); ?>
            <div class="mgk-grid mgk-grid-6 mgk-subjects-mobile">
                <?php foreach ( $subjects as $subject ) : ?>
                    <a class="mgk-card mgk-subject-card" href="<?php echo esc_url( mgk_subject_url( $subject['name'] ) ); ?>">
                        <span class="mgk-subject-icon"><?php echo esc_html( $subject['icon'] ); ?></span>
                        <h3><?php echo esc_html( $subject['name'] ); ?></h3>
                        <p><?php echo esc_html( $subject['count'] ); ?></p>
                    </a>
                <?php endforeach; ?>
                <a class="mgk-card mgk-subject-card" href="<?php echo esc_url( mgk_url( '/subjects/' ) ); ?>">
                    <span class="mgk-subject-icon">+</span>
                    <h3>View All</h3>
                    <p>30+ subjects</p>
                </a>
            </div>
        </div>
    </section>
