<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$atts = $args['atts'] ?? [];
$ctx = $args['context'] ?? [];
$mode = (string) ( $ctx['mode'] ?? 'demo' );

/* ── Gated: not signed in as a tutor ─────────────────────────────────────── */
if ( $mode === 'gated' ) : ?>
<section class="mgk-tutor-earnings mgk-tutor-earnings--gated" style="max-width:520px;margin:0 auto;padding:clamp(28px,6vw,64px) 20px;text-align:center">
    <h1 style="font-size:clamp(1.4rem,3.5vw,2rem);margin:0 0 .4em"><?php echo esc_html( $atts['gated_title'] ?? 'Sign in to view earnings' ); ?></h1>
    <p style="color:#555;margin:0 0 1.5em"><?php echo esc_html( $atts['gated_body'] ?? '' ); ?></p>
    <a style="display:inline-block;background:#111;color:#fff;text-decoration:none;border-radius:8px;padding:13px 24px;font-weight:600" href="<?php echo esc_url( $ctx['login_url'] ?? mgk_url( '/tutor/login/' ) ); ?>"><?php echo esc_html( $atts['gated_cta'] ?? 'Tutor sign in →' ); ?></a>
</section>
<?php return; endif;

/* ── Live: real read-only earnings for the signed-in tutor ───────────────── */
if ( $mode === 'real' ) :
    $summary = (array) ( $ctx['summary'] ?? [] );
    $ledger  = (array) ( $ctx['ledger'] ?? [] );
    $pct     = (float) ( $ctx['commission_pct'] ?? 0 );
?>
<section class="mgk-tutor-earnings mgk-tutor-earnings--live" data-mgk-tutor-earnings>
    <style>
        .mgk-tutor-earnings--live{--mgk-line:#e6e6ea}
        .mgk-tutor-earnings--live .mgk-tutor-earnings__shell{max-width:920px;margin:0 auto;padding:clamp(18px,3vw,32px) clamp(14px,3vw,24px)}
        .mgk-tutor-earnings--live .mgk-el-topbar{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;border-bottom:1px solid var(--mgk-line);padding-bottom:14px;margin-bottom:22px}
        .mgk-tutor-earnings--live .mgk-el-topbar nav a{margin-left:14px;text-decoration:none;color:#555;font-size:.92rem}
        .mgk-tutor-earnings--live .mgk-el-topbar nav a.is-active{color:#111;font-weight:700}
        .mgk-tutor-earnings--live .mgk-el-head{display:flex;justify-content:space-between;align-items:baseline;flex-wrap:wrap;gap:10px;margin-bottom:18px}
        .mgk-tutor-earnings--live .mgk-el-head h1{font-size:clamp(1.4rem,3vw,2rem);margin:0}
        .mgk-tutor-earnings--live .mgk-el-months a{text-decoration:none;color:#555;border:1px solid var(--mgk-line);border-radius:7px;padding:6px 11px;margin-left:6px;font-size:.85rem}
        .mgk-tutor-earnings--live .mgk-el-months strong{font-weight:700;margin:0 4px}
        .mgk-tutor-earnings--live .mgk-el-kpis{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:18px}
        .mgk-tutor-earnings--live .mgk-el-kpis article{border:1px solid var(--mgk-line);border-radius:10px;padding:14px}
        .mgk-tutor-earnings--live .mgk-el-kpis span{display:block;font-size:.72rem;letter-spacing:.05em;text-transform:uppercase;color:#888;margin-bottom:6px}
        .mgk-tutor-earnings--live .mgk-el-kpis strong{font-size:1.5rem}
        .mgk-tutor-earnings--live .mgk-el-kpis .is-net strong{color:#1a7f37}
        .mgk-tutor-earnings--live .mgk-el-note{background:#f7f7f8;border-radius:9px;padding:12px 14px;color:#555;font-size:.88rem;margin-bottom:18px}
        .mgk-tutor-earnings--live .mgk-el-pending{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:6px;border:1px dashed var(--mgk-line);border-radius:10px;padding:14px;margin-bottom:22px}
        .mgk-tutor-earnings--live .mgk-el-pending b{font-size:1.2rem}
        .mgk-tutor-earnings--live .mgk-el-pending em{color:#888;font-style:normal;font-size:.82rem;flex-basis:100%}
        .mgk-tutor-earnings--live table{width:100%;border-collapse:collapse;font-size:.92rem}
        .mgk-tutor-earnings--live th{text-align:left;font-size:.72rem;letter-spacing:.05em;text-transform:uppercase;color:#888;border-bottom:1px solid var(--mgk-line);padding:8px 6px}
        .mgk-tutor-earnings--live td{padding:11px 6px;border-bottom:1px solid #f0f0f2}
        .mgk-tutor-earnings--live td.num{text-align:right;font-variant-numeric:tabular-nums}
        .mgk-tutor-earnings--live .is-noshow{color:#b32d2e}
        .mgk-tutor-earnings--live .mgk-el-empty{text-align:center;color:#777;padding:34px 10px}
        @media(max-width:680px){.mgk-tutor-earnings--live .mgk-el-kpis{grid-template-columns:repeat(2,1fr)}}
    </style>
    <div class="mgk-tutor-earnings__shell">
        <?php if ( ! ( function_exists( 'mgk_parent_bool' ) && mgk_parent_bool( $atts['hide_topbar'] ?? '' ) ) ) : ?>
            <header class="mgk-el-topbar">
                <strong><?php echo esc_html( $atts['brand_label'] ?? '' ); ?></strong>
                <nav>
                    <a href="<?php echo esc_url( $ctx['dashboard_url'] ?? '#' ); ?>"><?php echo esc_html( $atts['nav_dashboard'] ?? 'Dashboard' ); ?></a>
                    <a class="is-active" href="<?php echo esc_url( mgk_get_tutor_earnings_url() ); ?>"><?php echo esc_html( $atts['nav_earnings'] ?? 'Earnings' ); ?></a>
                    <a href="<?php echo esc_url( $ctx['schedule_url'] ?? '#' ); ?>"><?php echo esc_html( $atts['nav_schedule'] ?? 'Schedule' ); ?></a>
                </nav>
            </header>
        <?php endif; ?>

        <div class="mgk-el-head">
            <h1><?php echo esc_html( ( $atts['page_title'] ?? 'Earnings' ) . ' · ' . ( $ctx['month_label'] ?? '' ) ); ?></h1>
            <div class="mgk-el-months">
                <a href="<?php echo esc_url( $ctx['prev_url'] ?? '#' ); ?>"><?php echo esc_html( $ctx['prev_label'] ?? '' ); ?></a>
                <strong><?php echo esc_html( $ctx['month_label'] ?? '' ); ?></strong>
                <a href="<?php echo esc_url( $ctx['next_url'] ?? '#' ); ?>"><?php echo esc_html( $ctx['next_label'] ?? '' ); ?></a>
            </div>
        </div>

        <div class="mgk-el-kpis">
            <article><span><?php echo esc_html( $atts['live_gross_label'] ?? 'Gross earned' ); ?></span><strong><?php echo esc_html( $summary['gross'] ?? '' ); ?></strong></article>
            <article><span><?php echo esc_html( $atts['live_commission_label'] ?? 'Commission' ); ?><?php echo $pct > 0 ? ' (' . esc_html( rtrim( rtrim( number_format( $pct, 1 ), '0' ), '.' ) ) . '%)' : ''; ?></span><strong><?php echo esc_html( $summary['commission'] ?? '' ); ?></strong></article>
            <article class="is-net"><span><?php echo esc_html( $atts['live_net_label'] ?? 'Net payable' ); ?></span><strong><?php echo esc_html( $summary['net'] ?? '' ); ?></strong></article>
            <article><span><?php echo esc_html( $atts['live_lessons_label'] ?? 'Lessons' ); ?></span><strong><?php echo esc_html( (string) ( $summary['lessons'] ?? 0 ) ); ?></strong></article>
        </div>

        <div class="mgk-el-note"><?php echo esc_html( $ctx['commission_note'] ?? '' ); ?></div>

        <div class="mgk-el-pending">
            <span><?php echo esc_html( $atts['live_pending_label'] ?? 'Pending payout' ); ?></span>
            <b><?php echo esc_html( $ctx['pending_net'] ?? '' ); ?></b>
            <em><?php echo esc_html( $atts['live_pending_note'] ?? '' ); ?></em>
        </div>

        <h2 style="font-size:1.05rem;margin:0 0 10px"><?php echo esc_html( $atts['live_ledger_title'] ?? 'Lessons this month' ); ?></h2>
        <?php if ( ! empty( $ctx['has_any'] ) && ! empty( $ledger ) ) : ?>
            <table>
                <thead><tr><th>Lesson</th><th>Date</th><th>Rate</th><th>Status</th><th class="num">Net</th></tr></thead>
                <tbody>
                    <?php foreach ( $ledger as $row ) : ?>
                        <tr>
                            <td><?php echo esc_html( $row['lesson'] ?? '' ); ?></td>
                            <td><?php echo esc_html( $row['date'] ?? '' ); ?></td>
                            <td><?php echo esc_html( $row['rate'] ?? '' ); ?></td>
                            <td class="<?php echo empty( $row['billable'] ) ? 'is-noshow' : ''; ?>"><?php echo esc_html( $row['status'] ?? '' ); ?></td>
                            <td class="num"><?php echo esc_html( $row['net'] ?? '' ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif ( ! empty( $ctx['has_any'] ) ) : ?>
            <div class="mgk-el-empty"><?php echo esc_html( $atts['live_empty_month'] ?? 'No logged lessons in this month yet.' ); ?></div>
        <?php else : ?>
            <div class="mgk-el-empty">
                <strong style="display:block;font-size:1.1rem;margin-bottom:6px"><?php echo esc_html( $atts['live_empty_title'] ?? 'No earnings yet' ); ?></strong>
                <p style="margin:0 0 14px"><?php echo esc_html( $atts['live_empty_body'] ?? '' ); ?></p>
                <a style="color:#111;font-weight:600" href="<?php echo esc_url( $ctx['schedule_url'] ?? '#' ); ?>"><?php echo esc_html( $atts['live_set_availability'] ?? 'Set your availability →' ); ?></a>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php return; endif;

/* ── Demo (Elementor editor) ─────────────────────────────────────────────── */
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
