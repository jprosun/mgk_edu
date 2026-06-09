<?php
/**
 * Reusable subject card.
 *
 * @var array $args
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$name = $args['name'] ?? '';
$count = $args['count'] ?? '';
$badge = $args['badge'] ?? '';
$level = $args['level'] ?? '';
$description = $args['description'] ?? '';
$icon = $args['icon'] ?? '';
$query = $args['query'] ?? [];
$accent = ! empty( $args['accent'] );

if ( ! $name ) {
    return;
}

if ( $level && empty( $query['level'] ) ) {
    $query['level'] = $level;
}

$url = $args['url'] ?? mgk_subject_url( $args['subject'] ?? $name, $query );
?>
<a class="mgk-subject-tile<?php echo $accent ? ' is-accent' : ''; ?>" href="<?php echo esc_url( $url ); ?>">
    <?php if ( $icon ) : ?>
        <span class="mgk-subject-tile-icon"><?php echo esc_html( $icon ); ?></span>
    <?php endif; ?>
    <span class="mgk-subject-tile-name"><?php echo esc_html( $name ); ?></span>
    <?php if ( $description ) : ?>
        <span class="mgk-subject-tile-desc"><?php echo esc_html( $description ); ?></span>
    <?php endif; ?>
    <?php if ( $count || $badge ) : ?>
        <span class="mgk-subject-tile-meta">
            <?php if ( $count ) : ?><span><?php echo esc_html( $count ); ?></span><?php endif; ?>
            <?php if ( $badge ) : ?><b><?php echo esc_html( $badge ); ?></b><?php endif; ?>
        </span>
    <?php endif; ?>
</a>
