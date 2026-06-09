<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$tutor = $args['tutor'] ?? [];
$reviews = mgk_get_teacher_reviews( $tutor['id'] ?? 0 );
$summary = mgk_summarize_teacher_reviews( $reviews );
$rating = $summary['rating'];
$review_count = $summary['count'];
$breakdown = $summary['breakdown'];
$initial_visible_reviews = 2;
$visible_reviews = array_slice( $reviews, 0, $initial_visible_reviews );
$visible_summary = mgk_summarize_teacher_reviews( $visible_reviews );
$visible_breakdown = $visible_summary['breakdown'];
$max_count = max( 1, max( array_map( 'intval', $visible_breakdown ) ) );
?>
<section id="reviews" class="mgk-section mgk-section-surface mgk-profile-reviews" data-mgk-reviews>
    <div class="mgk-shell">
        <div class="mgk-review-headline">
            <div>
                <h2>Reviews (<?php echo esc_html( (string) $review_count ); ?>)</h2>
                <p>From verified parents who completed lessons</p>
            </div>
            <?php if ( $review_count > $initial_visible_reviews ) : ?><a href="#reviews" data-mgk-review-see-all>View All Reviews &rarr;</a><?php endif; ?>
        </div>
        <?php if ( ! $review_count ) : ?>
            <div class="mgk-empty-panel">
                <h3>No verified reviews yet</h3>
                <p>Reviews added in WP Admin under MGK Reviews will appear here once linked to this tutor.</p>
            </div>
        <?php else : ?>
        <div class="mgk-review-layout">
            <aside class="mgk-card mgk-review-score" data-mgk-review-summary>
                <strong data-mgk-review-score><?php echo esc_html( (string) $visible_summary['rating'] ); ?></strong>
                <span>*****</span>
                <small><span data-mgk-review-count><?php echo esc_html( (string) $visible_summary['count'] ); ?></span> verified</small>
                <?php foreach ( $visible_breakdown as $stars => $count ) : ?>
                    <?php $percent = max( 4, round( ( (int) $count / $max_count ) * 100 ) ); ?>
                    <div class="mgk-rating-row" data-mgk-rating-row="<?php echo esc_attr( (string) $stars ); ?>">
                        <b><?php echo esc_html( $stars ); ?>*</b>
                        <span data-mgk-rating-bar style="--rating-count:<?php echo esc_attr( (string) $percent ); ?>"></span>
                        <em data-mgk-rating-count><?php echo esc_html( (string) $count ); ?></em>
                    </div>
                <?php endforeach; ?>
                <dl class="mgk-review-breakout">
                    <div><dt>Teaching</dt><dd data-mgk-breakout="teaching"><?php echo esc_html( (string) $visible_summary['teaching'] ); ?></dd></div>
                    <div><dt>Patience</dt><dd data-mgk-breakout="patience"><?php echo esc_html( (string) $visible_summary['patience'] ); ?></dd></div>
                    <div><dt>Punctuality</dt><dd data-mgk-breakout="punctuality"><?php echo esc_html( (string) $visible_summary['punctuality'] ); ?></dd></div>
                    <div><dt>Communication</dt><dd data-mgk-breakout="communication"><?php echo esc_html( (string) $visible_summary['communication'] ); ?></dd></div>
                </dl>
            </aside>
            <div class="mgk-review-list">
                <div class="mgk-review-tabs" aria-label="Review filters">
                    <button type="button" class="is-active" data-mgk-review-filter="all">All (<?php echo esc_html( (string) $review_count ); ?>)</button>
                    <button type="button" data-mgk-review-filter="5">5 star (<?php echo esc_html( (string) ( $breakdown['5'] ?? 0 ) ); ?>)</button>
                    <button type="button" data-mgk-review-filter="photos">With photos (<?php echo esc_html( (string) count( array_filter( $reviews, fn( $r ) => ! empty( $r['photos'] ) ) ) ); ?>)</button>
                </div>
                <?php foreach ( $reviews as $index => $review ) : ?>
                    <?php $is_extra = $index >= $initial_visible_reviews; ?>
                    <article class="mgk-card mgk-review-card<?php echo $is_extra ? ' is-hidden' : ''; ?>"
                        tabindex="0"
                        role="button"
                        data-mgk-review-card
                        <?php echo $is_extra ? 'data-mgk-review-extra="true"' : ''; ?>
                        data-rating="<?php echo esc_attr( (string) $review['rating'] ); ?>"
                        data-photos="<?php echo esc_attr( (string) (int) $review['photos'] ); ?>"
                        data-teaching="<?php echo esc_attr( (string) $review['teaching'] ); ?>"
                        data-patience="<?php echo esc_attr( (string) $review['patience'] ); ?>"
                        data-punctuality="<?php echo esc_attr( (string) $review['punctuality'] ); ?>"
                        data-communication="<?php echo esc_attr( (string) $review['communication'] ); ?>">
                        <div class="mgk-review-card-top">
                            <h3><?php echo esc_html( $review['name'] ); ?></h3>
                            <span>*****</span>
                        </div>
                        <p class="mgk-check"><?php echo esc_html( $review['meta'] ); ?></p>
                        <p><?php echo esc_html( $review['copy'] ); ?></p>
                        <div class="mgk-review-lines" aria-hidden="true"><span></span><span></span></div>
                        <?php if ( ! empty( $review['photos'] ) ) : ?>
                            <div class="mgk-review-photos" aria-label="Review photos">
                                <?php for ( $i = 0; $i < (int) $review['photos']; $i++ ) : ?>
                                    <span></span>
                                <?php endfor; ?>
                            </div>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
                <?php if ( $review_count > $initial_visible_reviews ) : ?>
                    <a id="reviews-all" class="mgk-review-more" href="#reviews" data-mgk-review-see-all>See all <?php echo esc_html( (string) $review_count ); ?> reviews &rarr;</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>
