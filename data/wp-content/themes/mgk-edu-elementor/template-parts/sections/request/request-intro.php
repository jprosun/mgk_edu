<?php
/**
 * S07 — Request Match · Intro band (split widget).
 *
 * Presentation only. SAFE marketing copy via $args; defaults match the spec.
 * Decorative toggles (hide_progress, hide_trust) and per-line trust text are
 * editable; an empty trust line is simply not rendered. Standalone widget
 * [mgk_request_intro] OR included by request-form.php.
 *
 * @var array $args
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$a = wp_parse_args( (array) ( $args ?? [] ), [
    'intro_pre'     => 'Get',
    'intro_em1'     => '3-5',
    'intro_mid1'    => 'tutor matches',
    'intro_mid2'    => 'in',
    'intro_em2'     => '6 hours',
    'trust_1'       => 'Free',
    'trust_2'       => 'No obligation',
    'trust_3'       => 'No account needed',
    'trust_4'       => 'Hand-picked by our team',
    'hide_progress' => '',
    'hide_trust'    => '',
] );

$hide_progress = $a['hide_progress'] === 'yes';
$hide_trust    = $a['hide_trust'] === 'yes';

// Trust lines: [text, is-check]. An empty line is dropped.
$trust = [
    [ $a['trust_1'], true ],
    [ $a['trust_2'], false ],
    [ $a['trust_3'], true ],
    [ $a['trust_4'], true ],
];
?>
<div class="mgk-rq mgk-rq--intro-only">
    <div class="mgk-rq-intro">
        <h1 class="mgk-rq-headline">
            <?php echo esc_html( $a['intro_pre'] ); ?>
            <em><?php echo esc_html( $a['intro_em1'] ); ?></em>
            <em><?php echo esc_html( $a['intro_mid1'] ); ?></em>
            <?php echo esc_html( $a['intro_mid2'] ); ?>
            <em><?php echo esc_html( $a['intro_em2'] ); ?></em>
        </h1>
        <?php if ( ! $hide_progress ) : ?>
        <div class="mgk-rq-progress" aria-hidden="true"><span></span></div>
        <?php endif; ?>
        <?php if ( ! $hide_trust ) : ?>
        <ul class="mgk-rq-trust" aria-label="Why this is safe">
            <?php foreach ( $trust as $t ) :
                if ( $t[0] === '' || $t[0] === null ) continue; ?>
            <li<?php echo $t[1] ? ' class="is-check"' : ''; ?>><?php echo esc_html( $t[0] ); ?></li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </div>
</div>
