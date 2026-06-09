<?php
/**
 * S09 — "What's in the trial lesson" box. Mostly CONTENT/SHELL — safe copy.
 *
 * @var array $args
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$a = wp_parse_args( (array) ( $args ?? [] ), [
    'heading'  => 'What’s in the trial lesson',
    'bullet_1' => '1.5h one-to-one level diagnostic + sample teaching',
    'bullet_2' => 'Written feedback on strengths & gaps',
    'bullet_3' => 'No commitment — continue with a package only if happy',
] );

$bullets = array_filter( [ $a['bullet_1'], $a['bullet_2'], $a['bullet_3'] ], function ( $v ) { return $v !== '' && $v !== null; } );
?>
<div class="mgk-bk-included">
    <h3 class="mgk-bk-included-heading"><?php echo esc_html( $a['heading'] ); ?></h3>
    <ul class="mgk-bk-included-list">
        <?php foreach ( $bullets as $b ) : ?>
        <li><?php echo esc_html( $b ); ?></li>
        <?php endforeach; ?>
    </ul>
</div>
