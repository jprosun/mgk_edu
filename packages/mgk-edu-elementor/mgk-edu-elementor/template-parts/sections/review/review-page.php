<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$atts = $args['atts'] ?? [];
$ctx = $args['context'] ?? [];
$teacher = (array) ( $ctx['teacher'] ?? [] );
$lesson = (array) ( $ctx['lesson'] ?? [] );
$state = (string) ( $ctx['state'] ?? 'post-trial' );
$is_eligible = ! empty( $ctx['is_eligible'] );

if ( ! $is_eligible && $state !== 'submitted' ) {
    $state = 'not-eligible';
}

$token = function ( $text ) use ( $teacher, $lesson ) {
    return strtr( (string) $text, [
        '{teacher}' => (string) ( $teacher['name'] ?? 'your tutor' ),
        '{child}'   => (string) ( $lesson['child_name'] ?? 'your child' ),
        '{date}'    => (string) ( $lesson['trial_completed'] ?? '' ),
        '{subject}' => (string) ( $lesson['subject'] ?? '' ),
        '{package}' => (string) ( $lesson['package_meta'] ?? '' ),
    ] );
};

$show_post_trial = $state === 'post-trial' && ! mgk_parent_bool( $atts['hide_post_trial'] ?? '' );
$show_post_package = $state === 'post-package' && ! mgk_parent_bool( $atts['hide_post_package'] ?? '' );
$show_submitted = $state === 'submitted' && ! mgk_parent_bool( $atts['hide_submitted'] ?? '' );
$show_not_eligible = $state === 'not-eligible' && ! mgk_parent_bool( $atts['hide_not_eligible'] ?? '' );
$section_label = function ( $key ) use ( $atts ) {
    if ( mgk_parent_bool( $atts[ 'hide_' . $key ] ?? '' ) ) {
        return;
    }
    $label = $atts[ 'sec_' . $key ] ?? '';
    if ( $label !== '' ) {
        echo '<span class="mgk-parent-review-sec">' . esc_html( $label ) . '</span>';
    }
};
$stars = function ( $name = 'mgk_review_rating', $value = 4 ) {
    $value = max( 1, min( 5, (int) $value ) );
    echo '<input type="hidden" class="mgk-parent-review-rating-input" name="' . esc_attr( $name ) . '" value="' . esc_attr( (string) $value ) . '">';
    echo '<div class="mgk-parent-review-stars" role="radiogroup" aria-label="Rating">';
    for ( $i = 1; $i <= 5; $i++ ) {
        $active = $i <= $value;
        echo '<button type="button" class="' . ( $active ? 'is-active' : '' ) . '" data-value="' . esc_attr( (string) $i ) . '" aria-label="' . esc_attr( (string) $i ) . ' stars">&#9733;</button>';
    }
    echo '</div>';
};
$form_action = admin_url( 'admin-post.php' );
$prompt_title = $state === 'post-package' && ! empty( $atts['package_heading'] )
    ? $atts['package_heading']
    : ( $atts['prompt_title'] ?? '' );
$prompt_meta = $state === 'post-package' && ! empty( $atts['package_subline'] )
    ? $atts['package_subline']
    : ( $atts['prompt_meta'] ?? '' );
?>
<section class="mgk-parent-review mgk-parent-review--<?php echo esc_attr( $state ); ?>">
    <div class="mgk-parent-review__shell">
        <?php if ( ! mgk_parent_bool( $atts['hide_prompt'] ?? '' ) ) : ?>
            <header class="mgk-parent-review-prompt">
                <?php $section_label( 'prompt' ); ?>
                <div class="mgk-parent-review-prompt__row">
                    <?php if ( ! mgk_parent_bool( $atts['hide_avatar'] ?? '' ) ) : ?>
                        <div class="mgk-parent-review-avatar" aria-hidden="true"></div>
                    <?php endif; ?>
                    <div>
                        <h1><?php echo esc_html( $token( $prompt_title ) ); ?></h1>
                        <p><?php echo esc_html( $token( $prompt_meta ) ); ?></p>
                    </div>
                </div>
            </header>
        <?php endif; ?>

        <?php if ( $show_post_trial ) : ?>
            <form class="mgk-parent-review-form mgk-parent-review-post-trial" method="post" action="<?php echo esc_url( $form_action ); ?>">
                <?php $section_label( 'post_trial' ); ?>
                <?php wp_nonce_field( 'mgk_parent_review_submit', 'mgk_parent_review_nonce' ); ?>
                <input type="hidden" name="action" value="mgk_parent_review_submit">
                <input type="hidden" name="mgk_review_teacher_id" value="<?php echo esc_attr( (string) ( $teacher['id'] ?? 0 ) ); ?>">
                <input type="hidden" name="mgk_review_type" value="post_trial">
                <label class="mgk-parent-review-label"><?php echo esc_html( $atts['rating_label'] ?? '' ); ?></label>
                <?php $stars( 'mgk_review_rating', 4 ); ?>
                <p class="mgk-parent-review-hint"><?php echo esc_html( $atts['rating_hint'] ?? '' ); ?></p>
                <label class="mgk-parent-review-label mgk-parent-review-comment-label">
                    <?php echo esc_html( $atts['comment_label'] ?? '' ); ?>
                    <span><?php echo esc_html( $atts['comment_optional'] ?? '' ); ?></span>
                </label>
                <textarea name="mgk_review_comment" placeholder="<?php echo esc_attr( $atts['comment_placeholder'] ?? '' ); ?>"></textarea>
                <div class="mgk-parent-review-actions">
                    <button type="submit" class="mgk-parent-review-submit"><?php echo esc_html( $atts['submit_label'] ?? '' ); ?></button>
                    <a class="mgk-parent-review-skip" href="<?php echo esc_url( $teacher['profile_url'] ?? '#' ); ?>"><?php echo esc_html( $atts['skip_label'] ?? '' ); ?></a>
                </div>
            </form>
        <?php endif; ?>

        <?php if ( $show_post_package ) : ?>
            <form class="mgk-parent-review-form mgk-parent-review-post-package" method="post" action="<?php echo esc_url( $form_action ); ?>">
                <?php $section_label( 'post_package' ); ?>
                <?php wp_nonce_field( 'mgk_parent_review_submit', 'mgk_parent_review_nonce' ); ?>
                <input type="hidden" name="action" value="mgk_parent_review_submit">
                <input type="hidden" name="mgk_review_teacher_id" value="<?php echo esc_attr( (string) ( $teacher['id'] ?? 0 ) ); ?>">
                <input type="hidden" name="mgk_review_type" value="post_package">
                <input type="hidden" name="mgk_review_rating" value="4">
                <div class="mgk-parent-review-dimensions">
                    <?php
                    $dimensions = [
                        'dimension_1' => 'mgk_review_teaching',
                        'dimension_2' => 'mgk_review_patience',
                        'dimension_3' => 'mgk_review_punctuality',
                        'dimension_4' => 'mgk_review_communication',
                    ];
                    foreach ( $dimensions as $dimension => $field_name ) :
                    ?>
                        <div class="mgk-parent-review-dimension">
                            <span><?php echo esc_html( $atts[ $dimension ] ?? '' ); ?></span>
                            <?php $stars( $field_name, 4 ); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <label class="mgk-parent-review-label mgk-parent-review-package-review-label">
                    <?php echo esc_html( $atts['package_review_label'] ?? '' ); ?>
                    <span><?php echo esc_html( $atts['package_review_optional'] ?? '' ); ?></span>
                </label>
                <textarea name="mgk_review_package_comment" placeholder="<?php echo esc_attr( $atts['package_comment_placeholder'] ?? ( $atts['comment_placeholder'] ?? '' ) ); ?>"></textarea>
                <div class="mgk-parent-review-photo-block">
                    <label class="mgk-parent-review-label mgk-parent-review-photo-label">
                        <?php echo esc_html( $atts['photo_heading'] ?? '' ); ?>
                        <span><?php echo esc_html( $atts['photo_optional'] ?? '' ); ?></span>
                    </label>
                    <button type="button" class="mgk-parent-review-photo">
                        <span aria-hidden="true"><?php echo esc_html( $atts['photo_label'] ?? '' ); ?></span>
                        <em><?php echo esc_html( $atts['photo_note'] ?? '' ); ?></em>
                    </button>
                </div>
                <button type="submit" class="mgk-parent-review-submit mgk-parent-review-submit--full"><?php echo esc_html( $atts['package_submit_label'] ?? ( $atts['submit_label'] ?? '' ) ); ?></button>
            </form>
        <?php endif; ?>

        <?php if ( ! mgk_parent_bool( $atts['hide_moderation'] ?? '' ) && ! $show_submitted && ! $show_not_eligible ) : ?>
            <aside class="mgk-parent-review-moderation">
                <?php $section_label( 'moderation' ); ?>
                <p><?php echo esc_html( $token( $atts['moderation_notice'] ?? '' ) ); ?></p>
            </aside>
        <?php endif; ?>

        <?php if ( $show_submitted ) : ?>
            <div class="mgk-parent-review-state mgk-parent-review-submitted">
                <?php $section_label( 'submitted' ); ?>
                <strong><?php echo esc_html( $atts['submitted_title'] ?? '' ); ?></strong>
                <p><?php echo esc_html( $atts['submitted_body'] ?? '' ); ?></p>
                <a href="<?php echo esc_url( $teacher['profile_url'] ?? '#' ); ?>"><?php echo esc_html( $atts['submitted_button'] ?? '' ); ?></a>
            </div>
        <?php endif; ?>

        <?php if ( $show_not_eligible ) : ?>
            <div class="mgk-parent-review-state mgk-parent-review-not-eligible">
                <?php $section_label( 'not_eligible' ); ?>
                <strong><?php echo esc_html( $atts['not_eligible_title'] ?? '' ); ?></strong>
                <p><?php echo esc_html( $atts['not_eligible_body'] ?? '' ); ?></p>
                <a href="<?php echo esc_url( mgk_url( $atts['dashboard_url'] ?? '/parent/dashboard/' ) ); ?>"><?php echo esc_html( $atts['not_eligible_button'] ?? '' ); ?></a>
            </div>
        <?php endif; ?>

        <?php if ( ! empty( $atts['bottom_note'] ) ) : ?>
            <footer class="mgk-parent-review-bottom-note"><?php echo esc_html( $atts['bottom_note'] ); ?></footer>
        <?php endif; ?>
    </div>
</section>
<script>
document.querySelectorAll('.mgk-parent-review-stars').forEach(function (group) {
    if (group.dataset.mgkReviewBound === '1') return;
    group.dataset.mgkReviewBound = '1';
    group.addEventListener('click', function (event) {
        var button = event.target.closest('button[data-value]');
        if (!button) return;
        var value = parseInt(button.getAttribute('data-value'), 10);
        var input = group.previousElementSibling;
        if (input && input.classList.contains('mgk-parent-review-rating-input')) {
            input.value = String(value);
        }
        group.querySelectorAll('button[data-value]').forEach(function (star) {
            star.classList.toggle('is-active', parseInt(star.getAttribute('data-value'), 10) <= value);
        });
    });
});
</script>
