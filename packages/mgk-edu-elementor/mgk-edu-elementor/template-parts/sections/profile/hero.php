<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$tutor = $args['tutor'] ?? [];
$name = $tutor['display_name'] ?? $tutor['name'] ?? 'Ms. Lee Yi Ling';
$short_name = $tutor['short_name'] ?? 'Ms Lee';
$slug = $tutor['slug'] ?? sanitize_title( $name );
$tier = strtoupper( str_replace( [ ' Teacher', ' teacher' ], '', $tutor['tier'] ?? 'EX-MOE' ) );
$experience = $tutor['experience_years'] ?? $tutor['experience'] ?? '8 years';
$experience = preg_match( '/\d+/', (string) $experience, $matches ) ? $matches[0] . 'Y' : '8Y';
$credential = strtoupper( str_replace( [ '-trained', ' trained' ], '', $tutor['credential_badge'] ?? $tutor['training'] ?? 'NIE' ) );
$area = strtoupper( str_replace( ' SG', '', $tutor['location_area'] ?? $tutor['location'] ?? 'Central' ) );
$online = ! empty( $tutor['online_available'] ) || ! empty( $tutor['online'] ) ? 'ONLINE' : 'ONLINE';
$rate = $tutor['hourly_rate_sgd'] ?? $tutor['rate'] ?? '$65/hr';
$trial = $tutor['trial_price_sgd'] ?? $tutor['trial'] ?? '$40';
$rating = $tutor['avg_rating'] ?? $tutor['rating'] ?? '4.9';
$reviews = $tutor['total_reviews'] ?? $tutor['reviews'] ?? '87';
$response = $tutor['response_time'] ?? $tutor['response'] ?? '4h';
$active = $tutor['last_active'] ?? '2d';
$students = $tutor['active_students'] ?? '12';
$rate = $rate ?: '$65/hr';
$trial = $trial ?: '$40';
$response = $response ?: '4h';
$active = $active ?: '2d';
$students = $students ?: '12';
$member_since = $tutor['member_since'] ?? '2022';
$subjects = ! empty( $tutor['subjects'] ) && is_array( $tutor['subjects'] ) ? $tutor['subjects'] : [ 'Math' ];
$levels = ! empty( $tutor['levels'] ) && is_array( $tutor['levels'] ) ? $tutor['levels'] : [ 'P5' ];
$languages = $tutor['languages'] ?? 'English';
$breadcrumb_topic = strtoupper( trim( ( $levels[0] ?? 'P5' ) . ' ' . ( $subjects[0] ?? 'Math' ) . ' TUTORS' ) );
?>
<section class="mgk-profile-hero mgk-teacher-hero">
    <div class="mgk-shell">
        <div class="mgk-teacher-toolbar">
            <nav class="mgk-teacher-crumb" aria-label="Tutor breadcrumb">
                <a href="<?php echo esc_url( mgk_cta_url( 'browse' ) ); ?>">&lt; Back</a>
                <span>&middot;</span>
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
                <span>&rsaquo;</span>
                <a href="<?php echo esc_url( mgk_cta_url( 'browse' ) ); ?>"><?php echo esc_html( $breadcrumb_topic ); ?></a>
                <span>&rsaquo;</span>
                <strong><?php echo esc_html( $name ); ?></strong>
            </nav>
            <div class="mgk-teacher-actions" aria-label="Tutor actions">
                <button type="button">Save</button>
                <button type="button">Share</button>
                <button type="button">Report</button>
            </div>
        </div>

        <div class="mgk-teacher-hero-layout">
            <div class="mgk-teacher-avatar-card">
                <div class="mgk-teacher-avatar<?php echo empty( $tutor['photo'] ) ? ' mgk-placeholder' : ''; ?>"
                     aria-label="<?php echo esc_attr( $name ); ?>"
                     <?php if ( ! empty( $tutor['photo'] ) ) : ?>style="background-image:url('<?php echo esc_url( $tutor['photo'] ); ?>')"<?php endif; ?>>
                    <?php if ( empty( $tutor['photo'] ) ) : ?><span>Avatar</span><?php endif; ?>
                </div>
                <strong class="mgk-teacher-verified">Verified Tutor</strong>
            </div>

            <div class="mgk-teacher-main">
                <div class="mgk-teacher-summary">
                    <h1><?php echo esc_html( $name ); ?></h1>
                    <div class="mgk-teacher-meta">
                        <span><?php echo esc_html( $tier ); ?> Teacher</span>
                        <span><?php echo esc_html( $experience ); ?></span>
                        <span><?php echo esc_html( $credential ); ?>-trained</span>
                    </div>
                    <div class="mgk-teacher-meta muted">
                        <span><?php echo esc_html( $area ); ?> SG</span>
                        <span>Available <?php echo esc_html( $online ); ?></span>
                        <span><?php echo esc_html( $languages ); ?></span>
                        <span>Member since <?php echo esc_html( $member_since ); ?></span>
                    </div>
                </div>

                <div class="mgk-teacher-stats" aria-label="Tutor trust stats">
                    <div><strong><?php echo esc_html( $rating ); ?></strong><span><?php echo esc_html( $reviews ); ?> reviews</span></div>
                    <div><strong>✓</strong><span>MOE+NIE verified</span></div>
                    <div><strong><?php echo esc_html( $response ); ?></strong><span>avg response</span></div>
                    <div><strong><?php echo esc_html( $active ); ?></strong><span>last active</span></div>
                    <div><strong><?php echo esc_html( $students ); ?></strong><span>active students</span></div>
                </div>

                <div class="mgk-teacher-badges" aria-label="Tutor badges">
                    <span>Top 1% tutor 2025</span>
                    <span>Tutor of the Month</span>
                    <span><?php echo esc_html( $students ); ?>+ students taught</span>
                </div>
            </div>

            <div class="mgk-teacher-booking">
                <?php get_template_part( 'template-parts/sections/profile/booking-widget', null, [ 'tutor' => $tutor ] ); ?>
            </div>
        </div>
    </div>
</section>
