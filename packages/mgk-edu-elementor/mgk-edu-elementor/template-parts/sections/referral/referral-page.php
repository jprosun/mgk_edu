<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$atts = $args['atts'] ?? [];
$ctx = $args['context'] ?? [];
$state = (string) ( $ctx['state'] ?? 'default' );
$code = (string) ( $ctx['referral_code'] ?? 'TAN-EMMA-2026' );
$link = (string) ( $ctx['referral_link'] ?? '#' );
$invitees = (array) ( $ctx['invitees'] ?? [] );
$tracking = (array) ( $ctx['tracking'] ?? [] );
$pending = (array) ( $ctx['pending'] ?? [] );
$earned = (array) ( $ctx['earned'] ?? [] );

$token = function ( $text ) use ( $ctx, $code, $invitees ) {
    return strtr( (string) $text, [
        '{code}'   => $code,
        '{count}'  => (string) count( $invitees ),
        '{child}'  => (string) ( $ctx['child_name'] ?? 'Emma' ),
        '{parent}' => (string) ( $ctx['parent_name'] ?? 'Mrs Tan' ),
    ] );
};

$section_label = function ( $key ) use ( $atts ) {
    if ( function_exists( 'mgk_parent_bool' ) && mgk_parent_bool( $atts[ 'hide_' . $key ] ?? '' ) ) {
        return;
    }
    $label = $atts[ 'sec_' . $key ] ?? '';
    if ( $label !== '' ) {
        echo '<span class="mgk-parent-referral-sec">' . esc_html( $label ) . '</span>';
    }
};

$share_text = rawurlencode( $token( $atts['preview_text'] ?? '' ) );
$share_link = rawurlencode( $link );
$whatsapp_url = 'https://wa.me/?text=' . $share_text . '%20' . $share_link;
$email_url = 'mailto:?subject=' . rawurlencode( 'Referral code ' . $code ) . '&body=' . $share_text . '%0A' . $share_link;
?>
<section class="mgk-parent-referral mgk-parent-referral--<?php echo esc_attr( $state ); ?>">
    <?php if ( $state === 'invitee-pending' && ! mgk_parent_bool( $atts['hide_pending'] ?? '' ) ) : ?>
        <div class="mgk-parent-referral-state-card mgk-parent-referral-pending">
            <header class="mgk-parent-referral-state-head">
                <strong><?php echo esc_html( $atts['pending_title'] ?? '' ); ?></strong>
                <span><?php echo esc_html( $atts['pending_kicker'] ?? '' ); ?></span>
            </header>
            <div class="mgk-parent-referral-state-shell">
                <h2><?php echo esc_html( ( $pending['name'] ?? 'Invitee' ) . ' — in progress' ); ?></h2>
                <div class="mgk-parent-referral-steps">
                    <?php foreach ( (array) ( $pending['steps'] ?? [] ) as $step ) : ?>
                        <p class="<?php echo ! empty( $step['done'] ) ? 'is-done' : ''; ?>">
                            <span></span><?php echo esc_html( $step['label'] ?? '' ); ?>
                        </p>
                    <?php endforeach; ?>
                </div>
                <div class="mgk-parent-referral-callout"><?php echo esc_html( $atts['pending_body'] ?? '' ); ?></div>
                <footer><?php echo esc_html( $atts['pending_note'] ?? '' ); ?></footer>
            </div>
        </div>
    <?php elseif ( $state === 'reward-earned' && ! mgk_parent_bool( $atts['hide_earned'] ?? '' ) ) : ?>
        <div class="mgk-parent-referral-state-card mgk-parent-referral-earned">
            <header class="mgk-parent-referral-state-head">
                <strong><?php echo esc_html( $atts['earned_title'] ?? '' ); ?></strong>
                <span><?php echo esc_html( $atts['earned_kicker'] ?? '' ); ?></span>
            </header>
            <div class="mgk-parent-referral-state-shell">
                <div class="mgk-parent-referral-earned-icon" aria-hidden="true">*</div>
                <h2><?php echo esc_html( $atts['earned_heading'] ?? '' ); ?></h2>
                <p><?php echo esc_html( $earned['message'] ?? '' ); ?></p>
                <div class="mgk-parent-referral-credit"><?php echo esc_html( $atts['earned_credit'] ?? '' ); ?></div>
                <a class="mgk-parent-referral-primary" href="<?php echo esc_url( mgk_url( '/parent/trial/' ) ); ?>"><?php echo esc_html( $atts['earned_primary'] ?? '' ); ?></a>
                <a class="mgk-parent-referral-secondary" href="<?php echo esc_url( mgk_get_parent_referral_url() ); ?>"><?php echo esc_html( $atts['earned_secondary'] ?? '' ); ?></a>
                <footer><?php echo esc_html( $atts['earned_note'] ?? '' ); ?></footer>
            </div>
        </div>
    <?php else : ?>
        <div class="mgk-parent-referral__shell">
            <?php if ( $state === 'empty' ) : ?>
                <header class="mgk-parent-referral-empty-head">
                    <strong><?php echo esc_html( $atts['empty_title'] ?? '' ); ?></strong>
                    <span><?php echo esc_html( $atts['empty_kicker'] ?? '' ); ?></span>
                </header>
            <?php endif; ?>

            <?php if ( $state !== 'empty' && ! mgk_parent_bool( $atts['hide_hero'] ?? '' ) ) : ?>
                <header class="mgk-parent-referral-hero">
                    <?php $section_label( 'hero' ); ?>
                    <h1><?php echo esc_html( $token( $atts['hero_title'] ?? '' ) ); ?></h1>
                    <p><?php echo esc_html( $token( $atts['hero_body'] ?? '' ) ); ?></p>
                    <div><?php echo esc_html( $token( $atts['reward_line'] ?? '' ) ); ?></div>
                </header>
            <?php endif; ?>

            <?php if ( $state === 'empty' && ! mgk_parent_bool( $atts['hide_empty'] ?? '' ) ) : ?>
                <div class="mgk-parent-referral-empty">
                    <div class="mgk-parent-referral-empty-art" aria-hidden="true">*</div>
                    <h2><?php echo esc_html( $atts['empty_heading'] ?? '' ); ?></h2>
                    <p><?php echo esc_html( $atts['empty_body'] ?? '' ); ?></p>
                    <strong><?php echo esc_html( $code ); ?></strong>
                </div>
            <?php endif; ?>

            <?php if ( ! mgk_parent_bool( $atts['hide_code_share'] ?? '' ) ) : ?>
                <div class="mgk-parent-referral-share">
                    <?php $section_label( 'code_share' ); ?>
                    <div class="mgk-parent-referral-code">
                        <span><?php echo esc_html( $atts['code_label'] ?? '' ); ?></span>
                        <strong><?php echo esc_html( $code ); ?></strong>
                        <em><?php echo esc_html( $link ); ?></em>
                    </div>
                    <div class="mgk-parent-referral-actions">
                        <h2><?php echo esc_html( $atts['share_heading'] ?? '' ); ?></h2>
                        <div>
                            <a class="mgk-parent-referral-whatsapp" href="<?php echo esc_url( $whatsapp_url ); ?>">o <?php echo esc_html( $atts['whatsapp_label'] ?? '' ); ?></a>
                            <button type="button" data-copy="<?php echo esc_attr( $link ); ?>"># <?php echo esc_html( $atts['copy_label'] ?? '' ); ?></button>
                            <a href="<?php echo esc_url( $email_url ); ?>">x <?php echo esc_html( $atts['email_label'] ?? '' ); ?></a>
                        </div>
                        <p><span><?php echo esc_html( $atts['preview_label'] ?? '' ); ?></span> <?php echo esc_html( $token( $atts['preview_text'] ?? '' ) ); ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ( $state !== 'empty' && ! mgk_parent_bool( $atts['hide_invitees'] ?? '' ) ) : ?>
                <div class="mgk-parent-referral-invitees">
                    <?php $section_label( 'invitees' ); ?>
                    <header>
                        <h2><?php echo esc_html( $token( $atts['invitees_heading'] ?? '' ) ); ?></h2>
                        <span><?php echo esc_html( $atts['invitees_note'] ?? '' ); ?></span>
                    </header>
                    <div class="mgk-parent-referral-invitee-list">
                        <?php foreach ( $invitees as $invitee ) : ?>
                            <article class="mgk-parent-referral-invitee is-<?php echo esc_attr( sanitize_key( $invitee['status'] ?? 'pending' ) ); ?>">
                                <div class="mgk-parent-referral-avatar" aria-hidden="true"></div>
                                <strong><?php echo esc_html( $invitee['name'] ?? '' ); ?></strong>
                                <span><?php echo esc_html( $invitee['label'] ?? '' ); ?></span>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ( $state !== 'empty' && ! mgk_parent_bool( $atts['hide_tracking'] ?? '' ) ) : ?>
                <div class="mgk-parent-referral-tracking">
                    <?php $section_label( 'tracking' ); ?>
                    <div class="mgk-parent-referral-track-grid">
                        <?php foreach ( $tracking as $item ) : ?>
                            <article class="<?php echo ! empty( $item['active'] ) ? 'is-active' : ''; ?>">
                                <strong><?php echo esc_html( $item['value'] ?? '' ); ?></strong>
                                <span><?php echo esc_html( $item['label'] ?? '' ); ?></span>
                            </article>
                        <?php endforeach; ?>
                    </div>
                    <p><?php echo esc_html( $atts['tracking_note'] ?? '' ); ?></p>
                </div>
            <?php elseif ( $state === 'empty' && ! empty( $atts['empty_note'] ) ) : ?>
                <footer class="mgk-parent-referral-empty-note"><?php echo esc_html( $atts['empty_note'] ); ?></footer>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</section>
<script>
document.querySelectorAll('.mgk-parent-referral [data-copy]').forEach(function (button) {
    if (button.dataset.mgkReferralBound === '1') return;
    button.dataset.mgkReferralBound = '1';
    button.addEventListener('click', function () {
        var value = button.getAttribute('data-copy') || '';
        if (navigator.clipboard && value) {
            navigator.clipboard.writeText(value);
        }
    });
});
</script>
