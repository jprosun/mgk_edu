<?php
/* The no-lead empty-state gate lives centrally in mgk_render_proposal_part()
 * (inc/mgk-proposals.php), so this wrapper only runs when a real lead exists or
 * we're previewing in the editor. */
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="mgk-proposals-page">
    <?php
    echo do_shortcode( '[mgk_proposal_header]' );
    echo do_shortcode( '[mgk_proposal_cards]' );
    echo do_shortcode( '[mgk_proposal_rematch]' );
    echo do_shortcode( '[mgk_proposal_compare]' );
    ?>
</div>
