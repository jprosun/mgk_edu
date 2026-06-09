<?php
/**
 * S17 parent referral shell.
 *
 * Referral records are DATA CORE: invitees, reward unlock state and credit
 * application belong in wp-admin later. Elementor edits only shell copy,
 * preview state, visibility and presentation.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function mgk_get_parent_referral_url( $state = '' ) {
    $params = [];
    if ( $state !== '' ) {
        $params['state'] = sanitize_key( (string) $state );
    }

    return add_query_arg( $params, mgk_url( '/parent/referrals/' ) );
}

function mgk_parent_referral_state() {
    $state = sanitize_key( mgk_get_query_filter( 'state', 'default' ) );
    return in_array( $state, [ 'default', 'invitee-pending', 'reward-earned', 'empty' ], true ) ? $state : 'default';
}

function mgk_parent_referral_context() {
    $context = [
        'state'          => mgk_parent_referral_state(),
        'parent_name'    => 'Mrs Tan',
        'child_name'     => 'Emma',
        'referral_code'  => 'TAN-EMMA-2026',
        'referral_link'  => mgk_url( '/?ref=tan-emma-2026' ),
        'reward_amount'  => '$XX',
        'credit_balance' => '$XX',
        'invitees'       => [
            [
                'name'   => 'Mrs Lim',
                'status' => 'paid',
                'label'  => '/ PAID · reward earned',
            ],
            [
                'name'   => 'Mr Kumar',
                'status' => 'pending',
                'label'  => 'Signed up · trial booked',
            ],
            [
                'name'   => 'Mrs Goh',
                'status' => 'invited',
                'label'  => 'Invited · not joined yet',
            ],
        ],
        'tracking'       => [
            [ 'value' => '$XX', 'label' => 'EARNED (1 PAID)', 'active' => true ],
            [ 'value' => '$XX', 'label' => 'PENDING (IN PROGRESS)', 'active' => false ],
            [ 'value' => '$XX', 'label' => 'CREDIT BALANCE', 'active' => false ],
        ],
        'pending'        => [
            'name' => 'Mr Kumar',
            'steps' => [
                [ 'label' => 'Invited', 'done' => true ],
                [ 'label' => 'Signed up ✓', 'done' => true ],
                [ 'label' => 'Trial booked ✓', 'done' => true ],
                [ 'label' => 'First paid package — reward unlocks here', 'done' => false ],
            ],
        ],
        'earned'         => [
            'invitee' => 'Mrs Lim',
            'message' => 'MRS LIM BOUGHT HER FIRST PACKAGE. YOU BOTH RECEIVED $XX CREDIT (BR-24).',
        ],
    ];

    return apply_filters( 'mgk_parent_referral_context', $context );
}

function mgk_render_parent_referral_part( $part, $atts = [] ) {
    if ( function_exists( 'mgk_parent_bool' ) && mgk_parent_bool( $atts['hidden'] ?? '' ) ) {
        return '';
    }

    $context = mgk_parent_referral_context();
    $preview_state = sanitize_key( (string) ( $atts['preview_state'] ?? '' ) );
    if ( in_array( $preview_state, [ 'default', 'invitee-pending', 'reward-earned', 'empty' ], true ) ) {
        $context['state'] = $preview_state;
    }

    return mgk_render_part( 'template-parts/sections/referral/' . $part, [
        'atts'    => $atts,
        'context' => $context,
    ] );
}

add_shortcode( 'mgk_parent_referral', function ( $atts ) {
    $atts = function_exists( 'mgk_parent_shortcode_atts' )
        ? mgk_parent_shortcode_atts( [
            'preview_state'           => '',
            'hide_hero'               => '',
            'sec_hero'                => 'SEC 1 Hero',
            'hero_title'              => 'Give a friend, get rewarded',
            'hero_body'               => "WHEN A FRIEND JOINS AND COMPLETES THEIR FIRST PAID PACKAGE, YOU BOTH GET A REWARD (BR-24).",
            'reward_line'             => 'REWARD AMOUNT: $XX CREDIT · PER-TENANT CONFIG · PM TO CONFIRM',
            'hide_code_share'         => '',
            'sec_code_share'          => 'SEC 2 Code + Share',
            'code_label'              => 'YOUR REFERRAL CODE',
            'share_heading'           => 'Share via',
            'whatsapp_label'          => 'WhatsApp',
            'copy_label'              => 'Copy link',
            'email_label'             => 'Email',
            'preview_label'           => 'PREVIEW:',
            'preview_text'            => '"Emma loves her tutor on [agency]! Get $XX off your first package with my code {code}."',
            'hide_invitees'           => '',
            'sec_invitees'            => 'SEC 3 Invitee List',
            'invitees_heading'        => 'Your invites ({count})',
            'invitees_note'           => 'STATUS UPDATES AUTOMATICALLY',
            'hide_tracking'           => '',
            'sec_tracking'            => 'SEC 4 Reward Tracking',
            'tracking_note'           => 'CREDIT AUTO-APPLIES TO NEXT PACKAGE · OR PER-TENANT PAYOUT RULE · PM TO CONFIRM',
            'hide_pending'            => '',
            'pending_title'           => 'INVITEE PENDING',
            'pending_kicker'          => 'STATUS DETAIL',
            'pending_body'            => 'REWARD RELEASES TO BOTH PARTIES ONLY AFTER FIRST PAID PACKAGE (BR-24). NO REWARD ON TRIAL ALONE.',
            'pending_note'            => 'PER-INVITEE FUNNEL. PDPA: INVITEE IDENTITY SHOWN TO REFERRER ONLY AFTER THEY CONSENT / JOIN.',
            'hide_earned'             => '',
            'earned_title'            => 'REWARD EARNED',
            'earned_kicker'           => 'SUCCESS',
            'earned_heading'          => 'You earned a reward!',
            'earned_credit'           => '+ $XX added to your credit balance',
            'earned_primary'          => 'Apply to next package →',
            'earned_secondary'        => 'Invite more friends',
            'earned_note'             => 'REWARD AMOUNT + PAYOUT VS CREDIT PER-TENANT CONFIG · PM TO CONFIRM.',
            'hide_empty'              => '',
            'empty_title'             => 'EMPTY · NO INVITES',
            'empty_kicker'            => 'FIRST-USE',
            'empty_heading'           => 'No invites yet',
            'empty_body'              => 'SHARE YOUR CODE TO START EARNING. YOUR INVITES & REWARDS WILL APPEAR HERE.',
            'empty_note'              => 'EMPTY HIDES LIST + TRACKING, KEEPS CODE + SHARE FRONT-AND-CENTRE.',
        ], $atts )
        : shortcode_atts( [], $atts );

    return mgk_render_parent_referral_part( 'referral-page', $atts );
} );
