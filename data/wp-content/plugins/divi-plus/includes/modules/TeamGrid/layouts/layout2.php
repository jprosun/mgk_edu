<?php
/**
 * The Template for displaying Layout 2
 *
 * This template can be overridden by copying it to yourtheme/divi-plus/layouts/team-grid/layout2.php
 *
 * HOWEVER, on occasion divi-plus will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen.
 *
 * @author      Elicus Technologies <hello@elicus.com>
 * @link        https://www.elicus.com/
 * @copyright   2023 Elicus Technologies Private Limited
 * @version     1.9.9
 */

$social_icons = '';
if ( 'on' === $show_social_icon ) {
	if (
		'' !== $website_url ||
		'' !== $facebook_url ||
		'' !== $twitter_url ||
		'' !== $linkedin_url ||
		'' !== $instagram_url ||
		'' !== $youtube_url ||
		'' !== $email ||
		'' !== $phone_number
	) {
		$social_icons = sprintf(
			'<div class="dipl_team_social_wrapper">%1$s%2$s%3$s%4$s%5$s%6$s%7$s%8$s</div>',
			$website_url,
			$facebook_url,
			$twitter_url,
			$linkedin_url,
			$instagram_url,
			$youtube_url,
			$email,
			$phone_number
		);
	}
}

if ( $social_icons ) {
	$visible_content = sprintf(
		'<div class="dipl_team_member_visible_wrapper">%1$s</div>',
		$social_icons
	);
}

if ( $short_description ) {
	$hidden_content = sprintf(
		'<div class="dipl_team_member_hidden_wrapper">%1$s</div>',
		$short_description
	);
}

if ( $social_icons || $short_description ) {
	$overlay_wrapper = sprintf(
		'<div class="dipl_team_overlay_wrapper">%1$s %2$s</div>',
		$social_icons ? $visible_content : '',
		$short_description ? $hidden_content : ''
	);
}

$output .= sprintf(
	'<div id="dipl_team_member_grid_%2$s" class="dipl_team_grid_item dipl_team_member_grid_wrapper%6$s%7$s" data-padding_top_desktop="%9$s" data-padding_top_tablet="%10$s" data-padding_top_phone="%11$s" data-padding_bottom_desktop="%12$s" data-padding_bottom_tablet="%13$s" data-padding_bottom_phone="%14$s" data-link="%15$s" data-link_target="%16$s" %8$s>
		<div class="dipl_team_member_image_wrapper">
			%1$s
			%5$s
		</div>
		<div class="dipl_team_content_wrapper">%3$s %4$s</div>
	</div>', 
	$member_image,
	esc_attr( $post_id ),
	$member_name,
	$designation,
	$social_icons || $short_description ? $overlay_wrapper : '',
	'open_popup' === $onclick_trigger ? ' dipl_team_popup' : '',
	'open_link' === $onclick_trigger ? ' dipl_team_link' : '',
	'open_popup' === $onclick_trigger ? $data : '',
	isset( $team_content_padding_top_desktop ) ? floatval( $team_content_padding_top_desktop ) : '',
	isset( $team_content_padding_top_tablet ) ? floatval( $team_content_padding_top_tablet ) : '',
	isset( $team_content_padding_top_phone ) ? floatval( $team_content_padding_top_phone ) : '',
	isset( $team_content_padding_bottom_desktop ) ? floatval( $team_content_padding_bottom_desktop ) : '',
	isset( $team_content_padding_bottom_tablet ) ? floatval( $team_content_padding_bottom_tablet ) : '',
	isset( $team_content_padding_bottom_phone ) ? floatval( $team_content_padding_bottom_phone ) : '',
	esc_url( get_permalink( $post_id ) ),
	'on' === $link_target ? '_blank' : '_self'
);