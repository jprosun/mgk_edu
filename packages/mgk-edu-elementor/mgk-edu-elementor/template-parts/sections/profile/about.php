<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$tutor = $args['tutor'] ?? [];
?>
<section class="mgk-section">
    <div class="mgk-shell mgk-profile-two-col">
        <div>
            <?php mgk_render_section_heading( 'About ' . $tutor['short_name'] ); ?>
            <?php foreach ( $tutor['about'] as $paragraph ) : ?><p><?php echo esc_html( $paragraph ); ?></p><?php endforeach; ?>
            <h3>Teaching philosophy</h3>
            <p><?php echo esc_html( $tutor['philosophy'] ); ?></p>
        </div>
        <aside class="mgk-card">
            <h2>Specializations</h2>
            <?php foreach ( $tutor['specializations'] as $item ) : ?>
                <div class="mgk-special-row"><span><?php echo esc_html( $item[0] ); ?></span><strong><?php echo esc_html( $item[1] ); ?></strong></div>
            <?php endforeach; ?>
        </aside>
    </div>
</section>
