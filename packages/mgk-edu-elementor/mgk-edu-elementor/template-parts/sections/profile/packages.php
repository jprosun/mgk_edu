<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$tutor = $args['tutor'] ?? [];
$tutor_slug = ! empty( $tutor['slug'] ) ? sanitize_title( $tutor['slug'] ) : sanitize_title( $tutor['name'] ?? '' );
?>
<section class="mgk-section">
    <div class="mgk-shell">
        <?php mgk_render_section_heading( 'Lesson Packages' ); ?>
        <div class="mgk-grid mgk-grid-3">
            <?php foreach ( $tutor['packages'] as $package ) :
                $plan = function_exists( 'mgk_engine_detect_tier' ) ? mgk_engine_detect_tier( (string) $package[0] ) : '';
                $choose_url = ( in_array( $plan, [ 'package_8', 'package_16' ], true ) && function_exists( 'mgk_get_buy_package_url_for' ) )
                    ? mgk_get_buy_package_url_for( $tutor_slug, $plan )
                    : mgk_get_trial_url( [ 'package' => $package[0], 'tutor' => $tutor_slug ] );
                ?>
                <article class="mgk-card mgk-package-card<?php echo ! empty( $package[3] ) ? ' is-featured' : ''; ?>">
                    <?php if ( ! empty( $package[3] ) ) : ?><p class="mgk-eyebrow">Recommended</p><?php endif; ?>
                    <h3><?php echo esc_html( $package[0] ); ?></h3>
                    <strong><?php echo esc_html( $package[1] ); ?></strong>
                    <p><?php echo esc_html( $package[2] ); ?></p>
                    <a class="mgk-btn mgk-btn-accent" href="<?php echo esc_url( $choose_url ); ?>">Choose</a>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
