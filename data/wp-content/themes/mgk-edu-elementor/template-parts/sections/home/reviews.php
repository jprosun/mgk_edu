<?php
/** S01 Reviews. @var array $args — heading, body */
if ( ! defined( 'ABSPATH' ) ) exit;
$heading = $args['heading'] ?? mgk_site_setting( 'reviews_heading' );
$body    = $args['body']    ?? mgk_site_setting( 'reviews_body' );
$reviews = mgk_get_reviews();
?>
    <section class="mgk-section mgk-home-reviews">
        <div class="mgk-shell">
            <?php mgk_render_section_heading( $heading, '', $body ); ?>
            <div class="mgk-grid mgk-grid-3">
                <?php foreach ( $reviews as $review ) : ?>
                    <article class="mgk-card">
                        <div class="mgk-review-head">
                            <div class="mgk-placeholder mgk-review-avatar"></div>
                            <div><h3><?php echo esc_html( $review['name'] ); ?></h3><p>***** · <?php echo esc_html( $review['meta'] ); ?></p></div>
                        </div>
                        <p><?php echo esc_html( $review['copy'] ); ?></p>
                        <p class="mgk-check">Verified parent · <?php echo esc_html( $review['subject'] ); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
            <a class="mgk-mobile-reviews-link" href="<?php echo esc_url( mgk_url( '/reviews/' ) ); ?>">View All Reviews →</a>
        </div>
    </section>
