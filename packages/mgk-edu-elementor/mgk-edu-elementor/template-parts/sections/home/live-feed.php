<?php
/** S01 Live feed. @var array $args */
if ( ! defined( 'ABSPATH' ) ) exit;
?>
    <section class="mgk-live-feed">
        <div class="mgk-shell mgk-live-track">
            <span class="mgk-live-label">LIVE</span>
            <?php foreach ( [ mgk_site_setting( 'live_1' ), mgk_site_setting( 'live_2' ), mgk_site_setting( 'live_3' ) ] as $item ) : ?>
                <?php if ( $item ) : ?><span><?php echo esc_html( $item ); ?></span><?php endif; ?>
            <?php endforeach; ?>
        </div>
    </section>
