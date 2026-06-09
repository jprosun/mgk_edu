<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$items = $args['how']['verification'] ?? [];
?>
<section class="mgk-section mgk-section-surface mgk-how-verification">
    <div class="mgk-shell">
        <div class="mgk-section-head center">
            <h2>How we verify every tutor</h2>
            <p>4-step process before any tutor can join</p>
        </div>
        <div class="mgk-grid mgk-grid-4 mgk-verification-grid">
            <?php foreach ( $items as $index => $item ) : ?>
                <article class="mgk-card mgk-verification-card">
                    <p class="mgk-eyebrow">Step <?php echo esc_html( (string) ( $index + 1 ) ); ?></p>
                    <h3><?php echo esc_html( $item['title'] ?? '' ); ?></h3>
                    <ul>
                        <?php foreach ( $item['items'] ?? [] as $line ) : ?>
                            <li><?php echo esc_html( $line ); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </article>
            <?php endforeach; ?>
        </div>
        <div class="mgk-acceptance">
            <strong>~ 35% of applicants pass</strong>
            <p>We say no more often than yes. That's why parents trust us.</p>
        </div>
    </div>
</section>
