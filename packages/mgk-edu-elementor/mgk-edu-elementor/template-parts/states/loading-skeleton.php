<?php
/**
 * Listing loading skeleton.
 *
 * @var array $args
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$count = isset( $args['count'] ) ? (int) $args['count'] : 4;
?>
<div class="mgk-listing-skeleton" aria-hidden="true">
    <?php for ( $i = 0; $i < $count; $i++ ) : ?>
        <div class="mgk-card mgk-skeleton-card">
            <div class="mgk-skeleton-avatar"></div>
            <div class="mgk-skeleton-lines">
                <span></span><span></span><span></span>
            </div>
        </div>
    <?php endfor; ?>
</div>
