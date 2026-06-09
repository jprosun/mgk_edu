<?php
/**
 * S04 hero and quick search.
 *
 * @var array $args
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$catalog = $args['catalog'] ?? [];
$page = $args['page'] ?? [];
$popular = $catalog['popular'] ?? [];
?>
<section class="mgk-section mgk-subject-hero">
    <div class="mgk-shell">
        <div class="mgk-section-head center">
            <p class="mgk-eyebrow"><?php echo esc_html( $page['eyebrow'] ?? '30+ subjects - all levels' ); ?></p>
            <h1><?php echo esc_html( $page['title'] ?? 'Find tutors by subject' ); ?></h1>
            <p><?php echo esc_html( $page['body'] ?? 'Search quickly or browse by level, major exam, stream, and international curriculum.' ); ?></p>
        </div>

        <form class="mgk-subject-search" method="get" action="<?php echo esc_url( mgk_cta_url( 'browse' ) ); ?>">
            <label for="mgk-subject-search-input">Search subject</label>
            <input id="mgk-subject-search-input" name="subject" type="search" placeholder="<?php echo esc_attr( $page['search_placeholder'] ?? 'Search subject e.g. PSLE Math, H2 Chem...' ); ?>" />
            <button class="mgk-btn mgk-btn-accent" type="submit"><?php echo esc_html( $page['search_button'] ?? 'Search' ); ?></button>
        </form>

        <?php if ( $popular ) : ?>
            <div class="mgk-subject-popular" aria-label="Popular subjects">
                <span>Popular:</span>
                <?php foreach ( $popular as $subject ) : ?>
                    <a href="<?php echo esc_url( mgk_subject_url( $subject ) ); ?>"><?php echo esc_html( $subject ); ?></a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
