<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$atts      = $args['atts'] ?? [];
$batch     = $args['batch'] ?? [];
$proposals = $args['proposals'] ?? [];
$expired   = ! empty( $args['expired'] );
$expiry    = (int) ( $args['expiry'] ?? time() );
$lead_data = $batch['lead_data'] ?? [];
?>
<section class="mgk-proposal-header" data-event="proposal_page_view" data-mgk-event="proposal_page_view"
         data-proposal-count="<?php echo esc_attr( (string) count( $proposals ) ); ?>"
         data-level="<?php echo esc_attr( $lead_data['level'] ?? '' ); ?>"
         data-subject="<?php echo esc_attr( $lead_data['subject'] ?? '' ); ?>"
         data-budget-range="<?php echo esc_attr( $lead_data['budget'] ?? '' ); ?>"
         data-has-expired="<?php echo esc_attr( $expired ? '1' : '0' ); ?>">
    <div class="mgk-proposal-shell mgk-proposal-header__inner">
        <div class="mgk-proposal-titleblock">
            <?php if ( ! mgk_proposal_bool( $atts['hide_heading'] ?? '' ) ) : ?>
                <h1><?php echo esc_html( $atts['heading'] ?? 'Your matched tutors' ); ?></h1>
            <?php endif; ?>
            <?php if ( ! mgk_proposal_bool( $atts['hide_summary'] ?? '' ) ) : ?>
                <p><?php echo esc_html( $args['summary'] ?? '' ); ?></p>
            <?php endif; ?>
        </div>
        <?php if ( ! mgk_proposal_bool( $atts['hide_expiry'] ?? '' ) ) : ?>
            <aside class="mgk-proposal-expiry" data-event="proposal_timer_view" data-mgk-event="proposal_timer_view" data-expiry="<?php echo esc_attr( (string) $expiry ); ?>">
                <?php if ( ! mgk_proposal_bool( $atts['hide_expiry_label'] ?? '' ) ) : ?>
                    <span class="mgk-proposal-expiry__label"><?php echo esc_html( $atts['expiry_label'] ?? 'PROPOSALS EXPIRE IN' ); ?></span>
                <?php endif; ?>
                <strong class="js-mgk-proposal-countdown"><?php echo $expired ? esc_html__( 'Expired', 'mgk-edu' ) : '48:00:00'; ?></strong>
                <?php if ( ! mgk_proposal_bool( $atts['hide_expiry_note'] ?? '' ) ) : ?>
                    <span class="mgk-proposal-expiry__note"><?php echo esc_html( $atts['expiry_note'] ?? 'FREE RE-SEND AFTER' ); ?></span>
                <?php endif; ?>
            </aside>
        <?php endif; ?>
    </div>
    <?php if ( $expired ) : ?>
        <?php get_template_part( 'template-parts/states/expired-proposals', null, [ 'button' => 'Request fresh matches' ] ); ?>
    <?php endif; ?>
</section>
