<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$atts      = $args['atts'] ?? [];
$batch     = $args['batch'] ?? [];
$proposals = $args['proposals'] ?? [];
$expired   = ! empty( $args['expired'] );
$lead      = $batch['lead_token'] ?? 'demo-proposal';

$hide_demo      = mgk_proposal_bool( $atts['hide_demo'] ?? '' );
$hide_trust     = mgk_proposal_bool( $atts['hide_trust'] ?? '' );
$hide_reason    = mgk_proposal_bool( $atts['hide_match_reason'] ?? '' );
$hide_suggested = mgk_proposal_bool( $atts['hide_suggested'] ?? '' );
$hide_badge     = mgk_proposal_bool( $atts['hide_verified_badge'] ?? '' );
$hide_compare   = mgk_proposal_bool( $atts['hide_compare'] ?? '' );
$hide_select    = mgk_proposal_bool( $atts['hide_select'] ?? '' );
$hide_why_label = mgk_proposal_bool( $atts['hide_why_label'] ?? '' );
$hide_suggested_label = mgk_proposal_bool( $atts['hide_suggested_label'] ?? '' );
?>
<section class="mgk-proposal-cards-section">
    <div class="mgk-proposal-shell">
        <?php if ( empty( $proposals ) ) : ?>
            <?php get_template_part( 'template-parts/states/empty-results', null, [
                'title'   => 'No proposals ready yet',
                'message' => "We're still hand-picking your tutors. You'll receive them within 6 hours.",
                'button'  => 'Browse tutors meanwhile',
                'url'     => home_url( '/student/teachers/' ),
            ] ); ?>
        <?php else : ?>
            <div class="mgk-proposal-grid" data-mgk-proposal-grid>
                <?php foreach ( $proposals as $index => $proposal ) :
                    $select_url = add_query_arg( array_filter( [
                        'lead'  => $lead,
                        'tutor' => $proposal['slug'],
                    ] ), home_url( '/parent/trial/' ) );
                    $compare_json = wp_json_encode( [
                        'id'         => $proposal['id'],
                        'slug'       => $proposal['slug'],
                        'name'       => $proposal['short_name'],
                        'rating'     => $proposal['rating'] . ' (' . $proposal['reviews'] . ')',
                        'rateTrial'  => $proposal['rate'] . ' / $' . $proposal['trial_price'],
                        'experience' => trim( $proposal['tier'] . ' ' . $proposal['experience'] ),
                    ] );
                    ?>
                    <article class="mgk-proposal-card js-mgk-proposal-card<?php echo $index < 2 ? ' is-compare-selected' : ''; ?>"
                             data-event="proposal_card_view"
                             data-mgk-event="proposal_card_view"
                             data-position="<?php echo esc_attr( (string) ( $index + 1 ) ); ?>"
                             data-tutor="<?php echo esc_attr( $proposal['slug'] ); ?>"
                             data-tutor-id="<?php echo esc_attr( (string) $proposal['id'] ); ?>"
                             data-tutor-tier="<?php echo esc_attr( $proposal['tier'] ); ?>"
                             data-trial-price="<?php echo esc_attr( (string) $proposal['trial_price'] ); ?>"
                             data-default-compare="<?php echo $index < 2 ? '1' : '0'; ?>"
                             data-compare="<?php echo esc_attr( $compare_json ); ?>">
                        <div class="mgk-proposal-card__topline">
                            <div class="mgk-proposal-avatar">
                                <?php if ( ! empty( $proposal['photo'] ) ) : ?>
                                    <img src="<?php echo esc_url( $proposal['photo'] ); ?>" alt="<?php echo esc_attr( $proposal['name'] ); ?>" loading="lazy">
                                <?php else : ?>
                                    <span>Avatar</span>
                                <?php endif; ?>
                                <?php if ( ! $hide_badge ) : ?>
                                    <b><?php echo esc_html( $atts['verified_label'] ?? 'Verified' ); ?></b>
                                <?php endif; ?>
                            </div>
                            <div class="mgk-proposal-identity">
                                <h2><?php echo esc_html( $proposal['name'] ); ?></h2>
                                <p><?php echo esc_html( $proposal['meta'] ); ?></p>
                                <?php if ( ! $hide_demo ) : ?>
                                    <button type="button" class="mgk-proposal-demo" data-event="proposal_demo_play" data-mgk-event="proposal_demo_play" data-tutor="<?php echo esc_attr( $proposal['slug'] ); ?>">
                                        <?php if ( $proposal['demo_duration'] ) : ?>
                                            <span aria-hidden="true">&#9658;</span> <?php echo esc_html( $atts['demo_label'] ?? 'Demo' ); ?> (<?php echo esc_html( $proposal['demo_duration'] ); ?>)
                                        <?php else : ?>
                                            <?php echo esc_html( $atts['demo_empty_label'] ?? 'Demo coming soon' ); ?>
                                        <?php endif; ?>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ( ! $hide_trust ) : ?>
                            <div class="mgk-proposal-trust">
                                <div><strong>&#9733;<?php echo esc_html( $proposal['rating'] ); ?></strong><span><?php echo esc_html( $proposal['reviews'] ); ?> rev</span></div>
                                <div><strong>&#10003;</strong><span><?php echo esc_html( $proposal['verified_label'] ); ?></span></div>
                                <div><strong><?php echo esc_html( $proposal['response'] ); ?></strong><span>Response</span></div>
                                <div><strong><?php echo esc_html( $proposal['active'] ); ?></strong><span>Active</span></div>
                                <div><strong><?php echo esc_html( $proposal['students'] ); ?></strong><span>Students</span></div>
                            </div>
                        <?php endif; ?>

                        <?php if ( ! $hide_reason ) : ?>
                            <div class="mgk-proposal-why">
                                <?php if ( ! $hide_why_label ) : ?>
                                    <b><?php echo esc_html( $atts['why_label'] ?? 'Why matched' ); ?></b>
                                <?php endif; ?>
                                <span><?php echo esc_html( $proposal['why'] ); ?></span>
                            </div>
                        <?php endif; ?>

                        <div class="mgk-proposal-actions">
                            <?php if ( ! $hide_suggested ) : ?>
                                <div class="mgk-proposal-suggested">
                                    <?php if ( ! $hide_suggested_label ) : ?>
                                        <span><?php echo esc_html( $atts['suggested_label'] ?? 'Suggested' ); ?></span>
                                    <?php endif; ?>
                                    <strong><?php echo esc_html( $proposal['trial_label'] ); ?> &rarr;</strong>
                                </div>
                            <?php endif; ?>
                            <div class="mgk-proposal-action-buttons">
                                <?php if ( ! $hide_compare ) : ?>
                                    <button type="button" class="mgk-proposal-compare js-mgk-proposal-compare" data-event="proposal_compare_toggle" data-mgk-event="proposal_compare_toggle" data-tutor="<?php echo esc_attr( $proposal['slug'] ); ?>">
                                        <?php echo esc_html( $atts['compare_label'] ?? '+ Compare' ); ?>
                                    </button>
                                <?php endif; ?>
                                <?php if ( ! $hide_select ) : ?>
                                    <?php if ( $expired ) : ?>
                                        <button type="button" class="mgk-proposal-select" disabled><?php echo esc_html( $atts['select_label'] ?? 'Select' ); ?> &rarr;</button>
                                    <?php else : ?>
                                        <a class="mgk-proposal-select" href="<?php echo esc_url( $select_url ); ?>" data-event="proposal_select" data-mgk-event="proposal_select" data-tutor="<?php echo esc_attr( $proposal['slug'] ); ?>" data-lead="<?php echo esc_attr( $lead ); ?>">
                                            <?php echo esc_html( $atts['select_label'] ?? 'Select' ); ?> &rarr;
                                        </a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
