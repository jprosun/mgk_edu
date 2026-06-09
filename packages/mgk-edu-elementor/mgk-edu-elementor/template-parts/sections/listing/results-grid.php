<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$tutors = $args['tutors'] ?? [];
?>
<div class="mgk-results-grid" data-mgk-results>
    <?php foreach ( $tutors as $index => $tutor ) : ?>
        <?php $profile_url = mgk_teacher_profile_url( $tutor ); ?>
        <article class="mgk-card mgk-result-card" data-mgk-result-card>
            <div class="mgk-result-top">
                <a class="mgk-result-avatar<?php echo empty( $tutor['photo'] ) ? ' mgk-placeholder' : ''; ?>"
                   href="<?php echo esc_url( $profile_url ); ?>"
                   aria-label="<?php echo esc_attr( 'View profile for ' . $tutor['name'] ); ?>"
                   data-mgk-event="tutor_profile_viewed"
                   <?php if ( ! empty( $tutor['photo'] ) ) : ?>style="background-image:url('<?php echo esc_url( $tutor['photo'] ); ?>')"<?php endif; ?>></a>
                <div class="mgk-result-main">
                    <h2><a href="<?php echo esc_url( $profile_url ); ?>" data-mgk-event="tutor_profile_viewed"><?php echo esc_html( $tutor['name'] ); ?></a></h2>
                    <p class="mgk-check">Verified · <?php echo esc_html( $tutor['tier'] . ' ' . $tutor['experience'] ); ?></p>
                    <p>*<?php echo esc_html( $tutor['rating'] ); ?> (<?php echo esc_html( $tutor['reviews'] ); ?> reviews) · Responds in <?php echo esc_html( $tutor['response'] ); ?></p>
                </div>
                <strong class="mgk-result-rate"><?php echo esc_html( $tutor['rate'] ); ?></strong>
            </div>
            <div class="mgk-result-tags">
                <?php foreach ( $tutor['tags'] as $tag ) : ?><span><?php echo esc_html( $tag ); ?></span><?php endforeach; ?>
            </div>
            <p class="mgk-result-bio"><?php echo esc_html( $tutor['bio'] ); ?></p>
            <div class="mgk-result-actions">
                <a class="mgk-btn mgk-btn-outline" href="<?php echo esc_url( $profile_url ); ?>" data-mgk-event="tutor_profile_viewed">View Profile</a>
                <a class="mgk-btn mgk-btn-accent" href="<?php echo esc_url( mgk_get_trial_url( [ 'tutor' => $tutor['slug'] ?? sanitize_title( $tutor['name'] ) ] ) ); ?>" data-mgk-event="trial_cta_clicked">Book Trial <?php echo esc_html( $tutor['trial'] ); ?></a>
            </div>
            <label class="mgk-compare-check"><input type="checkbox" data-mgk-compare value="<?php echo esc_attr( $tutor['name'] ); ?>"> Add to Compare (max 3)</label>
        </article>
    <?php endforeach; ?>
</div>
