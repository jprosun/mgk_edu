<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$tutor = $args['tutor'] ?? [];
?>
<section class="mgk-section mgk-section-surface">
    <div class="mgk-shell">
        <?php mgk_render_section_heading( 'Verified Qualifications', '', 'All credentials checked by Margick verification team' ); ?>
        <div class="mgk-grid mgk-grid-2">
            <?php foreach ( $tutor['qualifications'] as $qualification ) : ?>
                <article class="mgk-card mgk-qualification">
                    <div><h3><?php echo esc_html( $qualification[0] ); ?></h3><strong>Verified</strong></div>
                    <p><?php echo esc_html( $qualification[1] ); ?></p>
                    <small><?php echo esc_html( $qualification[2] ); ?></small>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
