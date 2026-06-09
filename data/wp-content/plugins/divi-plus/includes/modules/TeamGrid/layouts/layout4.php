<?php
/**
 * The Template for displaying Layout 4
 *
 * This template can be overridden by copying it to yourtheme/divi-plus/layouts/team-grid/layout4.php
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

if ( '' !== $member_image ) {
	$team_member_image = sprintf(
		'<div class="dipl_team_member_image_wrapper">%1$s</div>',
		$member_image
	);
}

$output .= sprintf(
	'<div id="dipl_team_member_grid_%2$s" class="dipl_team_grid_item dipl_team_member_grid_wrapper%7$s%8$s" data-link="%10$s" data-link_target="%11$s" %9$s>
		%1$s
		<div class="dipl_team_content_wrapper">%3$s %4$s %5$s %6$s</div>
	</div>', 
	'' !== $member_image ? $team_member_image : '',
	esc_attr( $post_id ),
	$member_name,
	$designation,
	$short_description,
	$social_icons,
	'open_popup' === $onclick_trigger ? ' dipl_team_popup' : '',
	'open_link' === $onclick_trigger ? ' dipl_team_link' : '',
	'open_popup' === $onclick_trigger ? $data : '',
	esc_url( get_permalink( $post_id ) ),
	'on' === $link_target ? '_blank' : '_self'
);