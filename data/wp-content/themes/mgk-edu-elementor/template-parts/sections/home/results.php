<?php
/** S01 Results / success stories. @var array $args — heading */
if ( ! defined( 'ABSPATH' ) ) exit;
$heading = $args['heading'] ?? mgk_site_setting( 'results_heading' );
?>
    <section class="mgk-section mgk-section-surface mgk-home-results">
        <div class="mgk-shell">
            <?php mgk_render_section_heading( $heading ); ?>
            <div class="mgk-grid mgk-grid-3">
                <?php foreach ( [ [ 'From C to A* in 6 months', 'Mrs Lim, P5 mother · PSLE Math' ], [ 'O-Level A1 against all odds', 'Mr Tan, Sec 4 father · Chemistry' ], [ 'Confidence boost for shy student', 'Ms Wong, parent of P3 · English' ] ] as $story ) : ?>
                    <article class="mgk-card mgk-story-card">
                        <div class="mgk-placeholder">Before / after grade chart</div>
                        <h3><?php echo esc_html( '"' . $story[0] . '"' ); ?></h3>
                        <div class="mgk-line"></div>
                        <div class="mgk-line short"></div>
                        <p class="mgk-muted">- <?php echo esc_html( $story[1] ); ?></p>
                        <a class="mgk-check" href="<?php echo esc_url( mgk_url( '/success-stories/' ) ); ?>">Read story →</a>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
