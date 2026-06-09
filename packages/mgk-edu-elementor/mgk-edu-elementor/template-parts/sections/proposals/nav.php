<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$atts = $args['atts'] ?? [];
?>
<header class="mgk-proposal-nav" data-event="proposal_nav_view">
    <div class="mgk-proposal-utility">
        <div class="mgk-proposal-shell">
            <span><?php echo esc_html( $atts['utility'] ?? '' ); ?></span>
        </div>
    </div>
    <nav class="mgk-proposal-mainnav" aria-label="Proposal navigation">
        <div class="mgk-proposal-shell mgk-proposal-mainnav__inner">
            <a class="mgk-proposal-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" data-event="nav_click" data-mgk-event="nav_click">
                <?php echo esc_html( $atts['logo_label'] ?? '[LOGO]' ); ?>
            </a>
            <div class="mgk-proposal-links">
                <a href="<?php echo esc_url( home_url( '/student/teachers/' ) ); ?>" data-event="nav_click" data-mgk-event="nav_click"><?php echo esc_html( $atts['browse_label'] ?? 'Browse Tutors' ); ?></a>
                <a href="<?php echo esc_url( home_url( '/subjects/' ) ); ?>" data-event="nav_click" data-mgk-event="nav_click"><?php echo esc_html( $atts['subjects_label'] ?? 'Subjects' ); ?></a>
                <a href="<?php echo esc_url( home_url( '/how-it-works/' ) ); ?>" data-event="nav_click" data-mgk-event="nav_click"><?php echo esc_html( $atts['how_label'] ?? 'How It Works' ); ?></a>
                <a href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>" data-event="nav_click" data-mgk-event="nav_click"><?php echo esc_html( $atts['pricing_label'] ?? 'Pricing' ); ?></a>
            </div>
            <a class="mgk-proposal-signin" href="<?php echo esc_url( home_url( '/login/' ) ); ?>" data-event="nav_click" data-mgk-event="nav_click">
                <?php echo esc_html( $atts['signin_label'] ?? 'Sign In' ); ?>
            </a>
        </div>
    </nav>
</header>
