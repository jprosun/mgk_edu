<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$filters = $args['filters'] ?? mgk_listing_filters();
$l_subject = $args['label_subject'] ?? 'Subject';
$l_level   = $args['label_level']   ?? 'Level';
$l_area    = $args['label_area']    ?? 'Area / Online';
$l_budget  = $args['label_budget']  ?? 'Budget';
$l_update  = $args['update_label']  ?? 'Update Search';
?>
<section class="mgk-listing-search">
    <div class="mgk-shell">
        <form action="<?php echo esc_url( mgk_get_tutor_listing_url() ); ?>" method="get" class="mgk-listing-search-form" data-mgk-event="listing_search_updated">
            <label><span><?php echo esc_html( $l_subject ); ?></span><select name="subject"><option value="">Any subject</option><?php foreach ( [ 'Math', 'English', 'Chinese', 'Science' ] as $option ) : ?><option <?php selected( $filters['subject'], $option ); ?>><?php echo esc_html( $option ); ?></option><?php endforeach; ?></select></label>
            <label><span><?php echo esc_html( $l_level ); ?></span><select name="level"><option value="">Any level</option><?php foreach ( [ 'P5', 'P6', 'Primary', 'Secondary' ] as $option ) : ?><option <?php selected( $filters['level'], $option ); ?>><?php echo esc_html( $option ); ?></option><?php endforeach; ?></select></label>
            <label><span><?php echo esc_html( $l_area ); ?></span><select name="area"><option value="">Any area</option><?php foreach ( [ 'Central SG', 'Online', 'East', 'West' ] as $option ) : ?><option <?php selected( $filters['area'], $option ); ?>><?php echo esc_html( $option ); ?></option><?php endforeach; ?></select></label>
            <label><span><?php echo esc_html( $l_budget ); ?></span><select name="budget"><option value="">Any budget</option><?php foreach ( [ '$30-$80/hr', '$50-$100/hr', '$80-$150/hr' ] as $option ) : ?><option <?php selected( $filters['budget'], $option ); ?>><?php echo esc_html( $option ); ?></option><?php endforeach; ?></select></label>
            <button class="mgk-btn mgk-btn-accent" type="submit"><?php echo esc_html( $l_update ); ?></button>
        </form>
    </div>
</section>
<div class="mgk-shell mgk-breadcrumb">Home / Student / Teachers / <strong><?php echo esc_html( trim( ( $filters['level'] ?: 'All' ) . ' ' . ( $filters['subject'] ?: 'Tutors' ) . ' in ' . ( $filters['area'] ?: 'Singapore' ) ) ); ?></strong></div>
