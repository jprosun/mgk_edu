<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$atts = $args['atts'] ?? [];
$ctx = $args['context'] ?? [];

$hidden = function ( $key ) use ( $atts ) {
    return function_exists( 'mgk_parent_bool' ) && mgk_parent_bool( $atts[ 'hide_' . $key ] ?? '' );
};

$section_label = function ( $label ) {
    if ( $label !== '' ) {
        echo '<span class="mgk-notif-sec">' . esc_html( $label ) . '</span>';
    }
};
?>
<section class="mgk-notif" data-mgk-notification-center>
    <div class="mgk-notif__shell">
        <?php if ( ! $hidden( 'header' ) ) : ?>
            <header class="mgk-notif-head">
                <?php $section_label( $atts['sec_header'] ?? '' ); ?>
                <h1><?php echo esc_html( $atts['title'] ?? '' ); ?></h1>
                <p><?php echo esc_html( $atts['subtitle'] ?? '' ); ?></p>
                <nav>
                    <button type="button"><?php echo esc_html( $atts['profile_label'] ?? '' ); ?></button>
                    <span><?php echo esc_html( $atts['master_label'] ?? '' ); ?></span>
                    <button type="button" class="is-active"><?php echo esc_html( $atts['all_on_label'] ?? '' ); ?></button>
                    <button type="button"><?php echo esc_html( $atts['essential_label'] ?? '' ); ?></button>
                </nav>
            </header>
        <?php endif; ?>

        <?php if ( ! $hidden( 'matrix' ) ) : ?>
            <section class="mgk-notif-matrix">
                <?php $section_label( $atts['sec_matrix'] ?? '' ); ?>
                <div class="mgk-notif-table">
                    <b><?php echo esc_html( $atts['event_col'] ?? '' ); ?></b>
                    <b><?php echo esc_html( $atts['push_col'] ?? '' ); ?></b>
                    <b><?php echo esc_html( $atts['email_col'] ?? '' ); ?></b>
                    <b><?php echo esc_html( $atts['sms_col'] ?? '' ); ?></b>
                    <b><?php echo esc_html( $atts['whatsapp_col'] ?? '' ); ?></b>
                    <?php foreach ( (array) ( $ctx['events'] ?? [] ) as $row ) : ?>
                        <span><?php echo esc_html( $row['event'] ?? '' ); ?></span>
                        <?php foreach ( [ 'push', 'email', 'sms', 'whatsapp' ] as $channel ) : $value = (string) ( $row[ $channel ] ?? '' ); ?>
                            <i class="<?php echo in_array( $value, [ '○', 'off' ], true ) ? 'is-off' : ''; ?>"><?php echo esc_html( $value ); ?></i>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </div>
                <p><?php echo esc_html( $atts['matrix_note'] ?? '' ); ?></p>
            </section>
        <?php endif; ?>

        <?php if ( ! $hidden( 'quiet' ) ) : ?>
            <section class="mgk-notif-quiet">
                <?php $section_label( $atts['sec_quiet'] ?? '' ); ?>
                <div>
                    <h2><?php echo esc_html( $atts['quiet_title'] ?? '' ); ?></h2>
                    <p><?php echo esc_html( $atts['quiet_body'] ?? '' ); ?></p>
                </div>
                <nav>
                    <button type="button" class="is-active"><?php echo esc_html( $atts['quiet_toggle'] ?? '' ); ?></button>
                    <button type="button"><?php echo esc_html( $atts['quiet_from'] ?? '' ); ?></button>
                    <span>TO</span>
                    <button type="button"><?php echo esc_html( $atts['quiet_to'] ?? '' ); ?></button>
                    <button type="button"><?php echo esc_html( $atts['quiet_tz'] ?? '' ); ?></button>
                </nav>
                <strong><?php echo esc_html( $atts['quiet_alert'] ?? '' ); ?></strong>
            </section>
        <?php endif; ?>

        <?php if ( ! $hidden( 'preview' ) ) : ?>
            <section class="mgk-notif-preview">
                <?php $section_label( $atts['sec_preview'] ?? '' ); ?>
                <h2><?php echo esc_html( $atts['preview_title'] ?? '' ); ?></h2>
                <div>
                    <?php foreach ( (array) ( $ctx['previews'] ?? [] ) as $preview ) : ?>
                        <article>
                            <span><?php echo esc_html( $preview['kicker'] ?? '' ); ?></span>
                            <h3><?php echo esc_html( $preview['title'] ?? '' ); ?></h3>
                            <i></i>
                            <nav>
                                <button type="button" class="is-active"><?php echo esc_html( $preview['button'] ?? '' ); ?></button>
                                <button type="button"></button>
                            </nav>
                        </article>
                    <?php endforeach; ?>
                </div>
                <p><?php echo esc_html( $atts['preview_note'] ?? '' ); ?></p>
            </section>
        <?php endif; ?>

        <?php if ( ! $hidden( 'pdpa' ) ) : ?>
            <footer class="mgk-notif-pdpa">
                <?php $section_label( $atts['sec_pdpa'] ?? '' ); ?>
                <p><?php echo esc_html( $atts['pdpa_body'] ?? '' ); ?></p>
                <a href="<?php echo esc_url( function_exists( 'mgk_get_parent_account_url' ) ? mgk_get_parent_account_url() : mgk_url( '/parent/account/' ) ); ?>"><?php echo esc_html( $atts['pdpa_link'] ?? '' ); ?></a>
            </footer>
        <?php endif; ?>
    </div>
</section>
