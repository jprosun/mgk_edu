<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$tutor = $args['tutor'] ?? [];
$name = $tutor['display_name'] ?? $tutor['name'] ?? 'Ms. Lee Yi Ling';
$short_name = $tutor['short_name'] ?? 'Ms Lee';
$slug = $tutor['slug'] ?? sanitize_title( $name );
$demo_url = $tutor['demo_video_url'] ?? '';
$duration = $tutor['demo_duration'] ?? '2:15';
$chapters = $tutor['lesson_chapters'] ?? [
    'Fractions (3m)',
    'PSLE Word (4m)',
    'Geometry (2m)',
];
$subject_label = ! empty( $tutor['subjects'] ) && is_array( $tutor['subjects'] ) ? implode( ' · ', array_slice( $tutor['subjects'], 0, 2 ) ) : 'P5 Math';
?>
<section class="mgk-section mgk-teacher-demo">
    <div class="mgk-shell">
        <div class="mgk-teacher-demo-head">
            <div>
                <h2>Watch <?php echo esc_html( $short_name ); ?> teach</h2>
                <p>2-min sample lesson · see teaching style before booking</p>
            </div>
            <span>Sec 5 demo MOT 5</span>
        </div>

        <div class="mgk-teacher-demo-grid">
            <button class="mgk-teacher-demo-video" type="button" data-event="demo_video_play" data-teacher="<?php echo esc_attr( $slug ); ?>"<?php echo $demo_url ? ' data-demo-video-url="' . esc_url( $demo_url ) . '"' : ' data-demo-video-missing="true"'; ?>>
                <span>Play Demo<br>(<?php echo esc_html( $duration ); ?>)</span>
                <small class="mgk-demo-topic"><?php echo esc_html( $subject_label ); ?> topic</small>
                <small class="mgk-demo-views">1,234 views</small>
            </button>

            <aside class="mgk-teacher-demo-aside">
                <h3>Why parents love this demo:</h3>
                <ul>
                    <li>Clear step-by-step explanation</li>
                    <li>Engaging without being silly</li>
                    <li>Real teaching style</li>
                    <li>Sample standard question</li>
                </ul>
                <div class="mgk-teacher-demo-more">
                    <b>More videos</b>
                    <?php foreach ( $chapters as $chapter ) : ?>
                        <span>&rsaquo; <?php echo esc_html( $chapter ); ?></span>
                    <?php endforeach; ?>
                </div>
            </aside>
        </div>
    </div>
</section>
