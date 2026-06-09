<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$tutor = $args['tutor'] ?? [];
?>
<section class="mgk-section">
    <div class="mgk-shell">
        <?php mgk_render_section_heading( 'Similar Tutors' ); ?>
        <div class="mgk-grid mgk-grid-3">
            <?php foreach ( $tutor['similar'] as $similar ) : ?>
                <a class="mgk-card mgk-similar-card" href="<?php echo esc_url( home_url( '/teacher/' . sanitize_title( $similar['slug'] ?? $similar['name'] ) . '/' ) ); ?>">
                    <div class="mgk-avatar<?php echo empty( $similar['photo'] ) ? ' mgk-placeholder' : ''; ?>"
                         <?php if ( ! empty( $similar['photo'] ) ) : ?>style="background-image:url('<?php echo esc_url( $similar['photo'] ); ?>')"<?php endif; ?>></div>
                    <h3><?php echo esc_html( $similar['name'] ); ?></h3>
                    <p class="mgk-check">*<?php echo esc_html( $similar['rating'] ); ?> · <?php echo esc_html( $similar['rate'] ); ?></p>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
