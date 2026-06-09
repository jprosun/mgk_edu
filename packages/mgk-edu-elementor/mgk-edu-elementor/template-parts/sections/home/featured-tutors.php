<?php
/** S01 Featured tutors. @var array $args — heading, body, limit */
if ( ! defined( 'ABSPATH' ) ) exit;
$heading = $args['heading'] ?? mgk_site_setting( 'tutors_heading' );
$body    = $args['body']    ?? mgk_site_setting( 'tutors_body' );
$limit   = isset( $args['limit'] ) ? (int) $args['limit'] : 8;
$tutors  = mgk_get_featured_tutors( max( 1, $limit ) );
?>
    <section class="mgk-section mgk-home-tutors">
        <div class="mgk-shell">
            <div class="mgk-section-head">
                <h2><?php echo esc_html( $heading ); ?></h2>
                <p><?php echo esc_html( $body ); ?></p>
            </div>
            <div class="mgk-filter-pills" aria-label="Tutor filters">
                <?php foreach ( mgk_site_csv( 'tutor_filters' ) as $index => $pill ) : ?>
                    <span class="mgk-pill<?php echo $index === 0 ? ' active' : ''; ?>"><?php echo esc_html( $pill ); ?></span>
                <?php endforeach; ?>
            </div>
            <div class="mgk-grid mgk-grid-4">
                <?php foreach ( $tutors as $tutor ) : ?>
                    <article class="mgk-card mgk-tutor-card">
                        <div class="mgk-tutor-mobile-row">
                            <div class="mgk-avatar<?php echo empty( $tutor['photo'] ) ? ' mgk-placeholder' : ''; ?>"
                                 <?php if ( ! empty( $tutor['photo'] ) ) : ?>style="background-image:url('<?php echo esc_url( $tutor['photo'] ); ?>')"<?php endif; ?>></div>
                            <div>
                                <h3><?php echo esc_html( $tutor['name'] ); ?></h3>
                                <p class="mgk-check">Verified <?php echo esc_html( $tutor['tier'] ); ?></p>
                                <p>*<?php echo esc_html( $tutor['rating'] ); ?> (<?php echo esc_html( $tutor['reviews'] ); ?>)</p>
                            </div>
                            <strong class="mgk-rate"><?php echo esc_html( $tutor['rate'] ); ?></strong>
                        </div>
                        <div class="mgk-tutor-meta">
                            <span><?php echo esc_html( implode( ', ', array_slice( (array) ( $tutor['subjects'] ?? [] ), 0, 2 ) ) ); ?></span>
                            <a class="mgk-btn mgk-btn-accent" href="<?php echo esc_url( mgk_get_trial_url( [ 'tutor' => sanitize_title( $tutor['name'] ) ] ) ); ?>" data-mgk-event="trial_cta_clicked">Book Trial</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <a class="mgk-tablet-view-all-tutors" href="<?php echo esc_url( mgk_get_tutor_listing_url() ); ?>">View All Tutors →</a>
        </div>
    </section>
