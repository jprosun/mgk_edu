<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$rows = $args['how']['comparison'] ?? [];
?>
<section class="mgk-section mgk-how-comparison">
    <div class="mgk-shell">
        <div class="mgk-section-head center">
            <h2>Margick vs Traditional Agency</h2>
            <p>See why parents are switching</p>
        </div>
        <div class="mgk-compare-table" role="table" aria-label="Margick compared with traditional agencies">
            <div class="mgk-compare-row mgk-compare-head" role="row">
                <div role="columnheader">Feature</div>
                <div role="columnheader">Margick</div>
                <div role="columnheader">Traditional Agency</div>
            </div>
            <?php foreach ( $rows as $row ) : ?>
                <div class="mgk-compare-row" role="row">
                    <div role="cell"><?php echo esc_html( $row['feature'] ?? '' ); ?></div>
                    <div role="cell"><?php echo esc_html( $row['mgk'] ?? '' ); ?></div>
                    <div role="cell"><?php echo esc_html( $row['agency'] ?? '' ); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
