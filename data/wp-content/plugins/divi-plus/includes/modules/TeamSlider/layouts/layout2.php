<?php
/**
 * The Template for displaying Layout 2
 *
 * This template can be overridden by copying it to yourtheme/divi-plus/layouts/team-slider/layout2.php
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

if ( '' !== $skill_bar ) {
	$skill_bar = sprintf(
		'<div class="dipl_skill_bar_wrapper">%1$s</div>',
		$skill_bar
	);
}

$output .= sprintf(
	'<div id="dipl_team_member_%7$s" class="dipl_team_member_card%8$s" data-link="%9$s" data-link_target="%10$s">
		<div class="dipl_team_image_wrapper">%1$s</div>
		<div class="dipl_team_content_wrapper">%2$s%3$s%4$s%5$s%6$s</div>
	</div>',
	$member_image,
	$member_name,
	$designation,
	$short_description,
	$skill_bar,
	$social_icons,
	esc_attr( $post_id ),
	'on' === $enable_member_link ? ' dipl_team_link' : '',
	esc_url( get_permalink( $post_id ) ),
	'on' === $link_target ? '_blank' : '_self'
);