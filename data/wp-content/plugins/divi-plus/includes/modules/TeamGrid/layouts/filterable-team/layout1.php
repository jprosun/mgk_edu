<?php
/**
 * The Template for displaying Layout 1
 *
 * @author      Elicus Technologies <hello@elicus.com>
 * @link        https://www.elicus.com/
 * @copyright   2022 Elicus Technologies Private Limited
 * @version     1.9.9
 */

$categories = array_filter( explode( ',', esc_attr( $include_categories ) ) );
if ( empty( $categories ) || '0' == count( $categories ) || '1' < count( $categories ) ) {
	$output .= '<div class="dipl-team-filterable-categories">';
	$output .= sprintf(
		'<ul class="dipl-team-items-categories" data-team_content_padding_top_desktop="%1$s" data-team_content_padding_top_tablet="%2$s" data-team_content_padding_top_phone="%3$s" data-team_content_padding_bottom_desktop="%4$s" data-team_content_padding_bottom_tablet="%5$s" data-team_content_padding_bottom_phone="%6$s" data-popup_elements="%7$s" %8$s>',
		isset( $team_content_padding_top_desktop ) ? $team_content_padding_top_desktop : '',
        isset( $team_content_padding_top_tablet ) ? $team_content_padding_top_tablet : '',
        isset( $team_content_padding_top_phone ) ? $team_content_padding_top_phone : '',
        isset( $team_content_padding_bottom_desktop ) ? $team_content_padding_bottom_desktop : '',
        isset( $team_content_padding_bottom_tablet ) ? $team_content_padding_bottom_tablet : '',
        isset( $team_content_padding_bottom_phone ) ? $team_content_padding_bottom_phone : '',
		esc_attr( $popupelements ),
		$data_atts
	);
	if ( 'on' === $show_all_link ) {
		$output .= '<li data-id="" class="dipl-team-active-category">'. sprintf( esc_html__( '%s', 'divi-plus' ), $all_link_text )  .'</li>';
	}
	$terms   = get_terms( array(
	    'taxonomy'		=> 'dipl-team-member-category',
	    'hide_empty' 	=> true,
	    'include' 		=> $categories
	) );
	foreach ( $terms as $term ) {
	    $output .= '<li data-id="' . $term->term_id . '" data-posts="'. $posts_number .'" data-layout="'. sanitize_file_name( $select_layout ) .'">' . $term->name . '</li>';
	}
	$output .= '</ul>';
	$output .= '</div>';
}