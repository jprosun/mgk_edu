<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$atts = $args['atts'] ?? [];
$ctx = $args['context'] ?? [];

$hidden = function ( $key ) use ( $atts ) {
    return function_exists( 'mgk_parent_bool' ) && mgk_parent_bool( $atts[ 'hide_' . $key ] ?? '' );
};

$section_label = function ( $label ) {
    if ( $label !== '' ) {
        echo '<span class="mgk-tutor-earnings-sec">' . esc_html( $label ) . '</span>';
    }
};
?>
<section class="mgk-tutor-earnings" data-mgk-tutor-earnings>
    <div class="mgk-tutor-earnings__shell">
        <?php if ( ! $hidden( 'topbar' ) ) : ?>
            <header class="mgk-tutor-earnings-topbar">
                <strong><?php echo esc_html( $atts['brand_label'] ?? '' ); ?></strong>
                <nav>
                    <a href="<?php echo esc_url( function_exists( 'mgk_get_tutor_dashboard_url' ) ? mgk_get_tutor_dashboard_url() : '#' ); ?>"><?php echo esc_html( $atts['nav_dashboard'] ?? '' ); ?></a>
                    <a class="is-active" href="<?php echo esc_url( mgk_get_tutor_earnings_url() ); ?>"><?php echo esc_html( $atts['nav_earnings'] ?? '' ); ?></a>
                    <a href="<?php echo esc_url( function_exists( 'mgk_get_tutor_schedule_url' ) ? mgk_get_tutor_schedule_url() : '#' ); ?>"><?php echo esc_html( $atts['nav_schedule'] ?? '' ); ?></a>
                    <a href="<?php echo esc_url( function_exists( 'mgk_get_tutor_schedule_url' ) ? mgk_get_tutor_schedule_url() . '#profile' : '#profile' ); ?>"><?php echo esc_html( $atts['nav_profile'] ?? '' ); ?></a>
                </nav>
            </header>
        <?php endif; ?>

        <?php if ( ! $hidden( 'summary' ) ) : ?>
            <section class="mgk-tutor-earnings-summary">
                <?php $section_label( $atts['sec_summary'] ?? '' ); ?>
                <header>
                    <h1><?php echo esc_html( ( $atts['page_title'] ?? '' ) . ' · ' . ( $atts['month_label'] ?? '' ) ); ?></h1>
                    <div class="mgk-tutor-earnings-months">
                        <a href="#"><?php echo esc_html( $atts['prev_month_label'] ?? '' ); ?></a>
                        <a class="is-active" href="#"><?php echo esc_html( $atts['current_month_label'] ?? '' ); ?></a>
                        <a href="#"><?php echo esc_html( $atts['next_month_label'] ?? '' ); ?></a>
                    </div>
                </header>
                <div class="mgk-tutor-earnings-kpis">
                    <?php foreach ( (array) ( $ctx['summary'] ?? [] ) as $item ) : ?>
                        <article class="<?php echo ! empty( $item['hot'] ) ? 'is-hot' : ( ! empty( $item['dark'] ) ? 'is-dark' : '' ); ?>">
                            <span><?php echo esc_html( $item['label'] ?? '' ); ?></span>
                            <strong class="<?php echo ! empty( $item['hot_value'] ) ? 'is-hot-value' : ''; ?>"><?php echo esc_html( $item['value'] ?? '' ); ?></strong>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <div class="mgk-tutor-earnings-split">
            <?php if ( ! $hidden( 'commission' ) ) : ?>
                <section class="mgk-tutor-earnings-panel mgk-tutor-earnings-commission">
                    <?php $section_label( $atts['sec_commission'] ?? '' ); ?>
                    <header>
                        <h2><?php echo esc_html( $atts['commission_title'] ?? '' ); ?></h2>
                        <div>
                            <button type="button" class="is-active"><?php echo esc_html( $atts['model_a_label'] ?? '' ); ?></button>
                            <button type="button"><?php echo esc_html( $atts['model_b_label'] ?? '' ); ?></button>
                        </div>
                    </header>
                    <article class="mgk-tutor-earnings-model-a">
                        <span><?php echo esc_html( $atts['model_a_title'] ?? '' ); ?></span>
                        <h3><?php echo esc_html( $atts['model_a_body'] ?? '' ); ?></h3>
                        <div class="mgk-tutor-earnings-breakdown">
                            <?php foreach ( (array) ( $ctx['model_a'] ?? [] ) as $row ) : ?>
                                <p class="<?php echo ! empty( $row['strong'] ) ? 'is-strong' : ''; ?>">
                                    <span><?php echo esc_html( $row['label'] ?? '' ); ?></span>
                                    <b class="<?php echo ! empty( $row['hot'] ) ? 'is-hot' : ''; ?>"><?php echo esc_html( $row['value'] ?? '' ); ?></b>
                                </p>
                            <?php endforeach; ?>
                        </div>
                        <small><?php echo esc_html( $atts['model_a_note'] ?? '' ); ?></small>
                    </article>
                    <article class="mgk-tutor-earnings-model-b">
                        <span><?php echo esc_html( $atts['model_b_title'] ?? '' ); ?></span>
                        <h3><?php echo esc_html( $atts['model_b_body'] ?? '' ); ?></h3>
                        <div class="mgk-tutor-earnings-slider">
                            <em>60%</em><i><b></b></i><em>80%</em><strong>70%</strong>
                        </div>
                        <small><?php echo esc_html( $atts['model_b_note'] ?? '' ); ?></small>
                    </article>
                </section>
            <?php endif; ?>

            <?php if ( ! $hidden( 'payouts' ) ) : ?>
                <section class="mgk-tutor-earnings-panel mgk-tutor-earnings-payouts">
                    <?php $section_label( $atts['sec_payouts'] ?? '' ); ?>
                    <h2><?php echo esc_html( $atts['payout_title'] ?? '' ); ?></h2>
                    <div class="mgk-tutor-earnings-pending">
                        <span><?php echo esc_html( $atts['pending_label'] ?? '' ); ?></span>
                        <strong><?php echo esc_html( $atts['pending_body'] ?? '' ); ?></strong>
                    </div>
                    <div class="mgk-tutor-earnings-table mgk-tutor-earnings-payout-table">
                        <b>DATE</b><b>AMOUNT</b><b>STATUS</b>
                        <?php foreach ( (array) ( $ctx['payouts'] ?? [] ) as $row ) : ?>
                            <span><?php echo esc_html( $row['date'] ?? '' ); ?></span>
                            <span><?php echo esc_html( $row['amount'] ?? '' ); ?></span>
                            <span class="is-hot"><?php echo esc_html( $row['status'] ?? '' ); ?></span>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
        </div>

        <?php if ( ! $hidden( 'ledger' ) ) : ?>
            <section class="mgk-tutor-earnings-panel mgk-tutor-earnings-ledger">
                <?php $section_label( $atts['sec_ledger'] ?? '' ); ?>
                <h2><?php echo esc_html( $atts['ledger_title'] ?? '' ); ?></h2>
                <div class="mgk-tutor-earnings-table mgk-tutor-earnings-ledger-table">
                    <b>LESSON</b><b>DATE</b><b>RATE</b><b>STATUS</b><b>NET</b>
                    <?php foreach ( (array) ( $ctx['ledger'] ?? [] ) as $row ) : ?>
                        <span><?php echo esc_html( $row['lesson'] ?? '' ); ?></span>
                        <span><?php echo esc_html( $row['date'] ?? '' ); ?></span>
                        <span><?php echo esc_html( $row['rate'] ?? '' ); ?></span>
                        <span class="is-hot"><?php echo esc_html( $row['status'] ?? '' ); ?></span>
                        <span><?php echo esc_html( $row['net'] ?? '' ); ?></span>
                    <?php endforeach; ?>
                </div>
                <p><?php echo esc_html( $atts['ledger_note'] ?? '' ); ?></p>
            </section>
        <?php endif; ?>

        <?php if ( ! $hidden( 'invoices' ) ) : ?>
            <section class="mgk-tutor-earnings-panel mgk-tutor-earnings-invoices">
                <?php $section_label( $atts['sec_invoices'] ?? '' ); ?>
                <header>
                    <h2><?php echo esc_html( $atts['invoice_title'] ?? '' ); ?></h2>
                    <a href="#"><?php echo esc_html( $atts['download_all_label'] ?? '' ); ?></a>
                </header>
                <div>
                    <?php foreach ( (array) ( $ctx['invoices'] ?? [] ) as $invoice ) : ?>
                        <article>
                            <span>▣ <?php echo esc_html( $invoice['label'] ?? '' ); ?></span>
                            <b><?php echo esc_html( $invoice['status'] ?? '' ); ?></b>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if ( ! $hidden( 'empty' ) ) : ?>
            <section class="mgk-tutor-earnings-empty">
                <strong><?php echo esc_html( $atts['empty_title'] ?? '' ); ?></strong>
                <p><?php echo esc_html( $atts['empty_body'] ?? '' ); ?></p>
            </section>
        <?php endif; ?>
    </div>
</section>
