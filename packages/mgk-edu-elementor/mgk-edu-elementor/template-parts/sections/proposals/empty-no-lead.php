<?php
/**
 * S08 empty state — shown when the page is opened without a valid lead+token.
 * Proposals depend on a request (S07), so there is nothing to show here until
 * the parent has submitted one. We never expose demo tutors on a no-token hit.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="mgk-proposals-empty mgk-section">
    <div class="mgk-shell" style="max-width:560px;text-align:center;">
        <div class="mgk-proposals-empty__icon" aria-hidden="true">🔍</div>
        <h1>No matches to show yet</h1>
        <p>Proposals appear here after you send a request. Tell us what you're
           looking for and we'll hand-pick 3–5 tutors within 6 hours.</p>
        <p>
            <a class="mgk-btn mgk-btn-accent" href="<?php echo esc_url( mgk_cta_url( 'request' ) ); ?>">Request a Match →</a>
            <a class="mgk-btn mgk-btn-outline" href="<?php echo esc_url( mgk_cta_url( 'browse' ) ); ?>">Browse tutors</a>
        </p>
    </div>
</div>
