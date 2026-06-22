<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$atts = $args['atts'] ?? [];
$ctx = $args['context'] ?? [];
$tutor = (array) ( $ctx['tutor'] ?? [] );
$job = (array) ( $ctx['job'] ?? [] );

$section_label = function ( $label ) {
    if ( $label !== '' ) {
        echo '<span class="mgk-tutor-dash-sec">' . esc_html( $label ) . '</span>';
    }
};

$hidden = function ( $key ) use ( $atts ) {
    return function_exists( 'mgk_parent_bool' ) && mgk_parent_bool( $atts[ 'hide_' . $key ] ?? '' );
};
?>
<section class="mgk-tutor-dash" data-mgk-tutor-dashboard>
    <div class="mgk-tutor-dash__shell">
        <?php
        $mgk_log_notice = isset( $_GET['mgk_log'] ) ? sanitize_key( wp_unslash( $_GET['mgk_log'] ) ) : '';
        if ( $mgk_log_notice ) :
            $notices = [
                'ok'       => [ 'ok',   'Lesson log submitted — the parent can now see it. ✓' ],
                'dup'      => [ 'warn', 'That lesson was already logged.' ],
                'denied'   => [ 'err',  'You can only log your own lessons.' ],
                'badnonce' => [ 'err',  'Your session expired — please try again.' ],
                'fail'     => [ 'err',  'Could not save the lesson log. Please try again.' ],
                'notlesson' => [ 'err',  'That booking is a package purchase, not a lesson to log.' ],
                'notended'  => [ 'warn', 'That lesson has not ended yet — log it after the scheduled end time.' ],
                'notconfirmed' => [ 'err', 'Only confirmed paid lessons can be logged.' ],
                'nochild'   => [ 'err',  'That booking is missing a student record. Ask agency ops to attach the child first.' ],
            ];
            $n = $notices[ $mgk_log_notice ] ?? null;
            if ( $n ) :
                $bg = [ 'ok' => '#eaf6ec;border:1px solid #b5e0bd;color:#1a7f37', 'warn' => '#fff5e6;border:1px solid #f5d8a8;color:#8a5a00', 'err' => '#fdecec;border:1px solid #f3b9b6;color:#b32d2e' ][ $n[0] ];
                echo '<div style="margin:0 0 16px;padding:12px 16px;border-radius:9px;font-size:14px;background:' . esc_attr( $bg ) . '">' . esc_html( $n[1] ) . '</div>';
            endif;
        endif;
        ?>
        <?php if ( ! $hidden( 'mobilebar' ) ) : ?>
            <header class="mgk-tutor-dash-mobilebar">
                <strong><?php echo esc_html( $atts['mobile_greeting'] ?? '' ); ?></strong>
                <span><?php echo esc_html( $atts['mobile_tools'] ?? '' ); ?></span>
            </header>
        <?php endif; ?>

        <?php if ( ! $hidden( 'welcome' ) ) : ?>
            <header class="mgk-tutor-dash-welcome">
                <?php $section_label( $atts['sec_welcome'] ?? '' ); ?>
                <div>
                    <h1><?php echo esc_html( ( $atts['welcome_prefix'] ?? '' ) . ' ' . ( $tutor['name'] ?? '' ) ); ?></h1>
                    <p><?php echo esc_html( $tutor['meta'] ?? '' ); ?></p>
                </div>
                <nav>
                    <a href="<?php echo esc_url( function_exists( 'mgk_get_tutor_lesson_log_url' ) ? mgk_get_tutor_lesson_log_url() : '#' ); ?>"><?php echo esc_html( $atts['log_lesson_label'] ?? '' ); ?></a>
                    <a class="is-primary" href="<?php echo esc_url( function_exists( 'mgk_get_tutor_schedule_url' ) ? mgk_get_tutor_schedule_url() : '#' ); ?>"><?php echo esc_html( $atts['schedule_label'] ?? '' ); ?></a>
                </nav>
            </header>
        <?php endif; ?>

        <?php if ( ! $hidden( 'job' ) ) : ?>
            <div class="mgk-tutor-dash-mobile-job-head">
                <strong><?php echo esc_html( $atts['mobile_job_title'] ?? '' ); ?></strong>
                <?php $section_label( $atts['sec_job'] ?? '' ); ?>
            </div>
        <?php endif; ?>

        <div class="mgk-tutor-dash-grid">
            <?php if ( ! $hidden( 'job' ) ) : ?>
                <article class="mgk-tutor-dash-card mgk-tutor-dash-job">
                    <?php $section_label( $atts['sec_job'] ?? '' ); ?>
                    <b><?php echo esc_html( $job['badge'] ?? '' ); ?></b>
                    <h2><?php echo esc_html( $job['subject'] ?? '' ); ?></h2>
                    <strong><?php echo esc_html( $job['sla'] ?? '' ); ?></strong>
                    <p><?php echo esc_html( $job['body'] ?? '' ); ?></p>
                    <div>
                        <a class="is-primary" href="#"><?php echo esc_html( $atts['accept_label'] ?? '' ); ?></a>
                        <a href="#"><?php echo esc_html( $atts['decline_label'] ?? '' ); ?></a>
                    </div>
                    <em><?php echo esc_html( $atts['decline_note'] ?? '' ); ?></em>
                    <small><?php echo esc_html( $job['note'] ?? '' ); ?></small>
                </article>
            <?php endif; ?>

            <?php if ( ! $hidden( 'today' ) ) : ?>
                <article class="mgk-tutor-dash-card mgk-tutor-dash-today">
                    <?php $section_label( $atts['sec_today'] ?? '' ); ?>
                    <?php foreach ( (array) ( $ctx['today'] ?? [] ) as $lesson ) : ?>
                        <div>
                            <strong><?php echo esc_html( $lesson['time'] ?? '' ); ?></strong>
                            <?php if ( ! empty( $lesson['action'] ) ) : ?><a href="<?php echo esc_url( $lesson['url'] ?? '#' ); ?>"><?php echo esc_html( $lesson['action'] ); ?></a><?php endif; ?>
                            <span><?php echo esc_html( $lesson['meta'] ?? '' ); ?></span>
                        </div>
                    <?php endforeach; ?>
                </article>
            <?php endif; ?>

            <?php if ( ! $hidden( 'week' ) ) : ?>
                <article class="mgk-tutor-dash-card mgk-tutor-dash-week">
                    <?php $section_label( $atts['sec_week'] ?? '' ); ?>
                    <div>
                        <?php foreach ( (array) ( $ctx['week'] ?? [] ) as $day ) : ?>
                            <span class="<?php echo ! empty( $day['hot'] ) ? 'is-hot' : ''; ?>"><b><?php echo esc_html( $day['day'] ?? '' ); ?></b><?php echo esc_html( $day['count'] ?? '' ); ?></span>
                        <?php endforeach; ?>
                    </div>
                    <p><?php echo esc_html( $atts['week_note'] ?? '' ); ?></p>
                </article>
            <?php endif; ?>

            <?php if ( ! $hidden( 'logs' ) ) : ?>
                <article class="mgk-tutor-dash-card mgk-tutor-dash-logs">
                    <?php $section_label( $atts['sec_logs'] ?? '' ); ?>
                    <?php foreach ( (array) ( $ctx['logs'] ?? [] ) as $log ) : ?>
                        <?php if ( ! empty( $log['url'] ) ) : ?>
                            <p class="<?php echo ! empty( $log['hot'] ) ? 'is-hot' : ''; ?>"><a href="<?php echo esc_url( $log['url'] ); ?>"><?php echo esc_html( $log['title'] ?? '' ); ?></a></p>
                        <?php else : ?>
                            <p class="<?php echo ! empty( $log['hot'] ) ? 'is-hot' : ''; ?>"><?php echo esc_html( $log['title'] ?? '' ); ?></p>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <?php if ( ! empty( $ctx['is_real'] ) && empty( $ctx['logs'] ) ) : ?>
                        <p>No pending logs — you’re all caught up. ✓</p>
                    <?php endif; ?>
                </article>
            <?php endif; ?>

            <?php if ( ! $hidden( 'earnings' ) ) : $earnings = (array) ( $ctx['earnings'] ?? [] ); ?>
                <article class="mgk-tutor-dash-card mgk-tutor-dash-earnings">
                    <?php $section_label( $atts['sec_earnings'] ?? '' ); ?>
                    <a href="<?php echo esc_url( $earnings['url'] ?? '#' ); ?>"><strong><?php echo esc_html( $earnings['amount'] ?? '' ); ?></strong></a>
                    <div>mini bar chart</div>
                    <p><?php echo esc_html( $earnings['delta'] ?? '' ); ?></p>
                </article>
            <?php endif; ?>

            <?php if ( ! $hidden( 'payout' ) ) : $payout = (array) ( $ctx['payout'] ?? [] ); ?>
                <article class="mgk-tutor-dash-card mgk-tutor-dash-payout">
                    <?php $section_label( $atts['sec_payout'] ?? '' ); ?>
                    <div>
                        <span><?php echo esc_html( $payout['label'] ?? '' ); ?></span>
                        <a href="<?php echo esc_url( $payout['url'] ?? '#' ); ?>"><strong><?php echo esc_html( $payout['amount'] ?? '' ); ?></strong></a>
                        <em><?php echo esc_html( $payout['meta'] ?? '' ); ?></em>
                    </div>
                    <p><?php echo esc_html( $payout['status'] ?? '' ); ?></p>
                </article>
            <?php endif; ?>

            <?php if ( ! $hidden( 'leaderboard' ) ) : ?>
                <article class="mgk-tutor-dash-card mgk-tutor-dash-leaderboard">
                    <?php $section_label( $atts['sec_leaderboard'] ?? '' ); ?>
                    <?php foreach ( (array) ( $ctx['leaderboard'] ?? [] ) as $row ) : ?>
                        <p class="<?php echo ! empty( $row['you'] ) ? 'is-you' : ''; ?>"><span><?php echo esc_html( $row['name'] ?? '' ); ?></span><b><?php echo esc_html( $row['rating'] ?? '' ); ?></b></p>
                    <?php endforeach; ?>
                    <small><?php echo esc_html( $atts['leaderboard_note'] ?? '' ); ?></small>
                </article>
            <?php endif; ?>

            <?php if ( ! $hidden( 'ratings' ) ) : $ratings = (array) ( $ctx['ratings'] ?? [] ); ?>
                <article class="mgk-tutor-dash-card mgk-tutor-dash-ratings">
                    <?php $section_label( $atts['sec_ratings'] ?? '' ); ?>
                    <strong><?php echo esc_html( $ratings['score'] ?? '' ); ?></strong>
                    <p><?php echo esc_html( $ratings['meta'] ?? '' ); ?></p>
                    <blockquote><?php echo esc_html( $ratings['quote'] ?? '' ); ?></blockquote>
                </article>
            <?php endif; ?>

            <?php if ( ! $hidden( 'messages' ) ) : ?>
                <article class="mgk-tutor-dash-card mgk-tutor-dash-messages">
                    <?php $section_label( $atts['sec_messages'] ?? '' ); ?>
                    <?php foreach ( (array) ( $ctx['messages'] ?? [] ) as $thread ) : ?>
                        <p><span><?php echo esc_html( $thread['title'] ?? '' ); ?></span><b><?php echo esc_html( $thread['meta'] ?? '' ); ?></b></p>
                    <?php endforeach; ?>
                </article>
            <?php endif; ?>

            <?php if ( ! $hidden( 'profile' ) ) : $profile = (array) ( $ctx['profile'] ?? [] ); ?>
                <article class="mgk-tutor-dash-card mgk-tutor-dash-profile">
                    <?php $section_label( $atts['sec_profile'] ?? '' ); ?>
                    <div><span style="width: <?php echo esc_attr( $profile['percent'] ?? '85%' ); ?>"></span></div>
                    <p><?php echo esc_html( $profile['note'] ?? '' ); ?></p>
                </article>
            <?php endif; ?>

            <?php if ( ! $hidden( 'quick' ) ) : ?>
                <article class="mgk-tutor-dash-card mgk-tutor-dash-quick">
                    <?php $section_label( $atts['sec_quick'] ?? '' ); ?>
                    <?php foreach ( (array) ( $ctx['quick'] ?? [] ) as $action ) : ?>
                        <a class="<?php echo ! empty( $action['label'] ) ? 'is-primary' : ''; ?>" href="<?php echo esc_url( $action['url'] ?? '#' ); ?>"><?php echo esc_html( $action['label'] ?? '' ); ?></a>
                    <?php endforeach; ?>
                </article>
            <?php endif; ?>
        </div>

        <?php if ( ! $hidden( 'mobile_nav' ) ) : ?>
            <nav class="mgk-tutor-dash-mobile-nav">
                <a href="#"><?php echo esc_html( $atts['mobile_nav_home'] ?? '' ); ?></a>
                <a href="<?php echo esc_url( function_exists( 'mgk_get_tutor_schedule_url' ) ? mgk_get_tutor_schedule_url() : '#' ); ?>"><?php echo esc_html( $atts['mobile_nav_schedule'] ?? '' ); ?></a>
                <a href="<?php echo esc_url( function_exists( 'mgk_get_tutor_lesson_log_url' ) ? mgk_get_tutor_lesson_log_url() : '#' ); ?>"><?php echo esc_html( $atts['mobile_nav_log'] ?? '' ); ?></a>
                <a href="<?php echo esc_url( function_exists( 'mgk_get_tutor_earnings_url' ) ? mgk_get_tutor_earnings_url() : '#' ); ?>"><?php echo esc_html( $atts['mobile_nav_earn'] ?? '' ); ?></a>
                <a href="#"><?php echo esc_html( $atts['mobile_nav_more'] ?? '' ); ?></a>
            </nav>
        <?php endif; ?>
    </div>
</section>
