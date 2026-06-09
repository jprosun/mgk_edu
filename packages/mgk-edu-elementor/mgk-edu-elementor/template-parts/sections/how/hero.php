<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$tabs = $args['how']['tabs'] ?? [];
$page = $args['page'] ?? [];
$tab_sublabels = [
    'parents'  => 'FIND A TUTOR',
    'tutors'   => 'BECOME A TUTOR',
    'agencies' => 'RUN YOUR OWN PLATFORM',
];
$tab_short_labels = [
    'parents'  => 'Parents',
    'tutors'   => 'Tutors',
    'agencies' => 'Agency',
];
?>
<section class="mgk-section mgk-how-hero">
    <div class="mgk-shell">
        <div class="mgk-section-head center">
            <p class="mgk-eyebrow"><?php echo esc_html( $page['eyebrow'] ?? 'Simple - transparent - guaranteed' ); ?></p>
            <h1><?php echo esc_html( $page['title'] ?? 'How Margick Works' ); ?></h1>
            <?php if ( ! empty( $page['body'] ) ) : ?><p><?php echo esc_html( $page['body'] ); ?></p><?php endif; ?>
            <span class="mgk-how-hero-rule" aria-hidden="true"></span>
        </div>

        <?php if ( $tabs ) : ?>
            <div class="mgk-how-tabs" data-mgk-tabs>
                <div class="mgk-tab-list" role="tablist" aria-label="How Margick works for different audiences">
                    <?php foreach ( $tabs as $index => $tab ) : ?>
                        <?php $panel_id = 'mgk-how-tab-' . sanitize_key( $tab['id'] ?? $index ); ?>
                        <button class="<?php echo $index === 0 ? 'is-active' : ''; ?>" type="button" role="tab" data-mgk-tab data-event="how_tab_click" aria-selected="<?php echo $index === 0 ? 'true' : 'false'; ?>" tabindex="<?php echo $index === 0 ? '0' : '-1'; ?>" aria-controls="<?php echo esc_attr( $panel_id ); ?>">
                            <strong><span class="mgk-tab-full"><?php echo esc_html( $tab['title'] ?? '' ); ?></span><span class="mgk-tab-short"><?php echo esc_html( $tab_short_labels[ $tab['id'] ?? '' ] ?? ( $tab['title'] ?? '' ) ); ?></span></strong>
                            <span><?php echo esc_html( $tab_sublabels[ $tab['id'] ?? '' ] ?? strtoupper( $tab['kicker'] ?? '' ) ); ?></span>
                        </button>
                    <?php endforeach; ?>
                </div>
                <?php foreach ( $tabs as $index => $tab ) : ?>
                    <?php $panel_id = 'mgk-how-tab-' . sanitize_key( $tab['id'] ?? $index ); ?>
                    <div id="<?php echo esc_attr( $panel_id ); ?>" class="mgk-tab-panel" role="tabpanel" data-mgk-tab-panel<?php echo $index === 0 ? '' : ' hidden'; ?>>
                        <h2><?php echo esc_html( $tab['heading'] ?? '' ); ?></h2>
                        <p><?php echo esc_html( $tab['body'] ?? '' ); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
