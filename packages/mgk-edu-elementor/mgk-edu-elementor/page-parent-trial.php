<?php
/**
 * Trial/request route for Batch 1 CTAs (DATA page).
 *
 * BUILDER MODE — if built in Elementor, render the_content() (Elementor needs it).
 * DEFAULT MODE — otherwise render the trial request form + tutor context.
 * See docs/TEMPLATE-BUILD-PLAYBOOK-ELEMENTOR.md §3.3 — form/logic stay in PHP.
 */

get_header();

// S09 Trial Booking — Select Tutor takes PRIORITY over Elementor content.
// When the parent arrives from proposals with a booking context (?lead/?tutor),
// this is the booking flow (step 1/3), so render the S09 select-tutor shortcode
// even if the page itself was built in Elementor (its builder content is the
// S15 renewal view, shown only on no-context visits). This is also where the
// lead transitions PROPOSED → ACCEPTED (inside mgk_get_select_tutor_view()).
$tutor_slug = mgk_get_query_filter( 'tutor', '' );
$lead_token = mgk_get_query_filter( 'lead', '' );
if ( function_exists( 'mgk_select_tutor_part' ) && ( $tutor_slug !== '' || $lead_token !== '' ) ) {
    echo do_shortcode( '[mgk_select_tutor]' );
    get_footer();
    return;
}

// No booking context → render the Elementor-built page content (e.g. S15 renewal).
if ( mgk_is_built_with_elementor( get_queried_object_id() ) ) :

    while ( have_posts() ) {
        the_post();
        the_content();
    }

    get_footer();
    return;

endif;

$tutor = $tutor_slug ? mgk_profile_tutor( sanitize_title( $tutor_slug ) ) : null;
$reviews = $tutor ? mgk_get_teacher_reviews( $tutor['id'] ?? 0, 3 ) : [];
$review_summary = mgk_summarize_teacher_reviews( $reviews );
?>

<section class="mgk-section mgk-trial-request">
    <div class="mgk-shell mgk-trial-grid">
        <form class="mgk-card mgk-booking-form" action="<?php echo esc_url( mgk_get_trial_url( $tutor ? [ 'tutor' => $tutor['slug'] ] : [] ) ); ?>" method="post" data-mgk-validate data-mgk-event="trial_request_submitted" novalidate>
            <input type="hidden" name="mgk_action" value="trial_checkout">
            <input type="hidden" name="tutor" value="<?php echo esc_attr( $tutor['slug'] ?? '' ); ?>">
            <?php wp_nonce_field( 'mgk_trial_checkout', 'mgk_trial_nonce' ); ?>
            <label><span>Parent name</span><input type="text" name="parent_name" required placeholder="Mrs Tan"></label>
            <label><span>Mobile number</span><input type="tel" name="phone" required placeholder="+65 9123 4567"></label>
            <label><span>Child level</span><select name="level" required><option value="">Choose level</option><option>Primary</option><option>Secondary</option><option>JC / IB</option></select></label>
            <label><span>Subject</span><select name="subject" required><option value="">Choose subject</option><option>Math</option><option>English</option><option>Chinese</option><option>Science</option></select></label>
            <label><span>Preferred schedule</span><input type="text" name="schedule" placeholder="Weekday evenings, Sat morning"></label>
            <label><span>Budget</span><select name="budget"><option>$30-$80/hr</option><option>$50-$100/hr</option><option>$80-$150/hr</option></select></label>
            <label class="mgk-consent"><input type="checkbox" name="consent" value="1" required data-mgk-trial-consent><span>I agree to be contacted about this trial request.</span></label>
            <?php get_template_part( 'template-parts/states/validation-message', null, [ 'message' => 'Please complete the required fields before submitting.' ] ); ?>
            <button class="mgk-btn mgk-btn-accent" type="submit" data-mgk-consent-submit disabled>Continue to Payment</button>
        </form>

        <aside class="mgk-card mgk-trial-side">
            <h2>What happens next</h2>
            <ol class="mgk-ordered">
                <li>We confirm tutor availability and student fit.</li>
                <li>We create a WooCommerce order with this tutor and request details.</li>
                <li>After checkout, your trial request is ready for confirmation.</li>
            </ol>
            <?php if ( $tutor ) : ?>
                <div class="mgk-mini-stats">
                    <div class="mgk-mini-stat"><strong><?php echo esc_html( $tutor['rating'] ); ?></strong><span>Rating</span></div>
                    <div class="mgk-mini-stat"><strong><?php echo esc_html( $tutor['trial'] ); ?></strong><span>Trial</span></div>
                    <div class="mgk-mini-stat"><strong><?php echo esc_html( $tutor['response'] ); ?></strong><span>Response</span></div>
                </div>
                <div class="mgk-trial-reviews">
                    <h3>Verified reviews<?php echo $review_summary['count'] ? ' (' . esc_html( (string) $review_summary['count'] ) . ')' : ''; ?></h3>
                    <?php if ( $reviews ) : ?>
                        <?php foreach ( $reviews as $review ) : ?>
                            <article>
                                <strong><?php echo esc_html( $review['name'] ); ?></strong>
                                <span><?php echo esc_html( (string) $review['rating'] ); ?> / 5</span>
                                <p><?php echo esc_html( $review['copy'] ); ?></p>
                            </article>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <p>No verified reviews have been linked to this tutor yet.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <a class="mgk-btn mgk-btn-outline" href="<?php echo esc_url( mgk_get_tutor_listing_url() ); ?>">Browse more tutors</a>
        </aside>
    </div>
</section>

<?php
get_footer();
