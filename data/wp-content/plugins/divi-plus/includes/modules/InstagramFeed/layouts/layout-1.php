<?php
/**
 * The Template for displaying Layout 1
 *
 * This template can be overridden by copying it to yourtheme/divi-plus/layouts/instagram-feed/layout-1.php
 *
 * HOWEVER, on occasion divi-plus will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen.
 *
 * @author      Elicus Technologies <hello@elicus.com>
 * @link        https://www.elicus.com/
 * @copyright   2024 Elicus Technologies Private Limited
 * @version     1.10.0
 */

$permalink	= isset( $data['permalink'] ) ? $data['permalink'] : '';
$username	= isset( $data['username'] ) ? $data['username'] : '';

// If enable caption and available
$caption = '';
if ( 'on' == $show_caption && !empty($data['caption']) ) {
	$caption = sprintf( '<p class="dipl_instagram_item-caption">%1$s</p>', $data['caption'] );
}

// Instagram image
if ( ( 'IMAGE' === $media_type || 'CAROUSEL_ALBUM' === $media_type ) ) {

	// Render element for image will not work in ajax, at backend
	if ( !empty($multi_view) ) {
		$instaImg = $multi_view->render_element( array(
			'tag'		=> 'img',
			'attrs'	=> array(
				'src'	=> esc_url( $media_url ),
				'alt'	=> '',
				'class'	=> 'dipl_instagram_item-image',
			),
		) );
	} else {
		$instaImg = sprintf(
			'<img src="%1$s" alt="" class="dipl_instagram_item-image">',
			esc_url( $media_url )
		);
	}

	// Check if link enable
	if ( 'on' == $link_post ) {
		$instaImg = sprintf(
			'<a href="%1$s" target="_blank">%2$s</a>',
			esc_url( $permalink ),
			et_core_intentionally_unescaped( $instaImg, 'html' )
		);
	}

	$instaFeed .= sprintf(
		'<div class="dipl-column-item dipl_instagram_feed_item instagram-item-image">
			<div class="dipl_instagram_feed_item-inner">%1$s %2$s</div>
		</div>',
		et_core_intentionally_unescaped( $instaImg, 'html' ),
		et_core_intentionally_unescaped( $caption, 'html' )
	);
}

// Instagram video
if ( 'VIDEO' === $media_type ) {

	// Get video
	$instaImg = sprintf( 
		'<video class="dipl_instagram_item-video" controls poster="%1$s">
			<source src="%2$s" type="video/mp4">
		</video>',
		esc_url( $media_url ), esc_url( $data['media_url'] )
	);

	$instaFeed .= sprintf(
		'<div class="dipl-column-item dipl_instagram_feed_item instagram-item-video">
			<div class="dipl_instagram_feed_item-inner">%1$s %2$s</div>
		</div>',
		et_core_intentionally_unescaped( $instaImg, 'html' ),
		et_core_intentionally_unescaped( $caption, 'html' )
	);
}
