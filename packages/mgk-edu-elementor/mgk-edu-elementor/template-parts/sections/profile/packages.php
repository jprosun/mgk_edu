<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$tutor = $args['tutor'] ?? [];
?>
<section class="mgk-section">
    <div class="mgk-shell">
        <?php mgk_render_section_heading( 'Lesson Packages' ); ?>
        <div class="mgk-grid mgk-grid-3">
            <?php foreach ( $tutor['packages'] as $package ) : ?>
                <article class="mgk-card mgk-package-card<?php echo ! empty( $package[3] ) ? ' is-featured' : ''; ?>">
                    <?php if ( ! empty( $package[3] ) ) : ?><p class="mgk-eyebrow">Recommended</p><?php endif; ?>
                    <h3><?php echo esc_html( $package[0] ); ?></h3>
                    <strong><?php echo esc_html( $package[1] ); ?></strong>
                    <p><?php echo esc_html( $package[2] ); ?></p>
                    <a class="mgk-btn mgk-btn-accent" href="<?php echo esc_url( mgk_get_trial_url( [ 'package' => $package[0], 'tutor' => sanitize_title( $tutor['name'] ) ] ) ); ?>">Choose</a>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
