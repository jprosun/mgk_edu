<?php
/**
 * S04 stream and specialty.
 *
 * @var array $args
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$streams = $args['catalog']['streams'] ?? [];
?>
<section class="mgk-section mgk-subject-streams">
    <div class="mgk-shell">
        <?php mgk_render_section_heading( 'Browse by Stream / Specialty' ); ?>

        <?php if ( empty( $streams ) ) : ?>
            <?php get_template_part( 'template-parts/states/empty-results', null, [
                'title' => 'No streams available',
                'message' => 'Stream data has not been imported yet.',
            ] ); ?>
        <?php else : ?>
            <div class="mgk-grid mgk-grid-4 mgk-stream-grid">
                <?php foreach ( $streams as $stream ) : ?>
                    <?php
                    get_template_part( 'template-parts/components/subject-card', null, [
                        'name' => $stream['name'] ?? '',
                        'subject' => $stream['subject'] ?? $stream['name'] ?? '',
                        'description' => $stream['description'] ?? '',
                        'count' => $stream['count'] ?? '',
                        'icon' => $stream['icon'] ?? '',
                    ] );
                    ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
