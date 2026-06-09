<?php
/**
 * The Template for displaying gallery for the woo product
 *
 * This template can be overridden by copying it to yourtheme/divi-plus/layouts/woo-product-gallery/layout-1.php
 *
 * HOWEVER, on occasion divi-plus will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen.
 *
 * @author      Elicus Technologies <hello@elicus.com>
 * @link        https://www.elicus.com/
 * @copyright   2024 Elicus Technologies Private Limited
 * @version     1.11.0
 */

// add the attachment image
$image = sprintf(
	'<div class="dipl_woo_product_gallery-image">%1$s %2$s</div>',
	et_core_intentionally_unescaped( $attachmentImg, 'html' ),
	et_core_intentionally_unescaped( $overlay_output, 'html' )
);

$title = '';
if ( 'on' == $show_title ) {
	$titleCnt	= trim( wptexturize( get_the_title( $attachment_id ) ) );
	$title		= !empty( $titleCnt ) ? sprintf(
		'<div class="dipl_woo_product_gallery-title"><%1$s class="et_pb_title">%2$s</%1$s></div>',
		et_core_intentionally_unescaped( $header_level, 'html' ),
		trim( wptexturize( get_the_title( $attachment_id ) ) )
	) : '';
}

$caption = '';
if ( 'on' == $show_caption ) {
	$captionCnt	= get_the_excerpt( $attachment_id );
	$caption	= !empty( $captionCnt ) ? sprintf(
		'<div class="dipl_woo_product_gallery-caption">%1$s</div>',
		$captionCnt
	) : '';
}

$contentWrapper = '';
if ( !empty($title) || !empty($caption) ) {
	$contentWrapper = sprintf(
		'<div class="dipl_woo_product_gallery_title_caption_wrapper">%1$s %2$s</div>',
		et_core_intentionally_unescaped( $title, 'html' ),
		et_core_intentionally_unescaped( $caption, 'html' ),
	);
}

if ( $enable_lightbox && 'on' == $enable_lightbox ) {
	$output .= sprintf(
		'<div class="dipl_woo_product_gallery-item dipl-column-item">
			<a href="%3$s" class="dipl_woo_product_gallery-lightbox">%1$s %2$s</a>
		</div>',
		et_core_intentionally_unescaped( $image, 'html' ),
		et_core_intentionally_unescaped( $contentWrapper, 'html' ),
		wp_get_attachment_image_url( $attachment_id, 'full' )
	);
} else {
	$output .= sprintf(
		'<div class="dipl_woo_product_gallery-item dipl-column-item">%1$s %2$s</div>',
		et_core_intentionally_unescaped( $image, 'html' ),
		et_core_intentionally_unescaped( $contentWrapper, 'html' ),
	);
}