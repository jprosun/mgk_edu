<?php
/**
 * S04 featured subject deep dive.
 *
 * @var array $args
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$featured = $args['catalog']['featured'] ?? [];
if ( empty( $featured ) ) {
    return;
}
?>
<section class="mgk-section mgk-subject-featured">
    <div class="mgk-shell">
        <div class="mgk-featured-subject">
            <div>
                <p class="mgk-eyebrow">Featured subject</p>
                <h2><?php echo esc_html( $featured['title'] ?? '' ); ?></h2>
                <p><?php echo esc_html( $featured['copy'] ?? '' ); ?></p>

                <?php if ( ! empty( $featured['checks'] ) ) : ?>
                    <h3>What to look for in a tutor</h3>
                    <ul>
                        <?php foreach ( $featured['checks'] as $check ) : ?>
                            <li><?php echo esc_html( $check ); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <a class="mgk-btn mgk-btn-accent" href="<?php echo esc_url( mgk_subject_url( $featured['subject'] ?? 'Math', $featured['query'] ?? [] ) ); ?>">
                    Browse 145 PSLE Math Tutors
                </a>
            </div>

            <aside class="mgk-featured-tutors">
                <h3>Top PSLE Math tutors</h3>
                <?php foreach ( $featured['tutors'] ?? [] as $tutor ) : ?>
                    <div class="mgk-featured-tutor">
                        <span class="mgk-mini-avatar"></span>
                        <div>
                            <strong><?php echo esc_html( $tutor['name'] ?? '' ); ?></strong>
                            <p><?php echo esc_html( $tutor['meta'] ?? '' ); ?></p>
                        </div>
                        <b><?php echo esc_html( $tutor['rate'] ?? '' ); ?></b>
                    </div>
                <?php endforeach; ?>
            </aside>
        </div>
    </div>
</section>
