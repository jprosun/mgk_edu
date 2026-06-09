<?php
/**
 * The Template for displaying Layout 1
 *
 * This template can be overridden by copying it to yourtheme/divi-woocommerce-extended/layouts/woo-products-accordion/layout1.php
 *
 * HOWEVER, on occasion divi-woocommerce-extended will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen.
 *
 * @author      Elicus Technologies <hello@elicus.com>
 * @link        https://www.elicus.com/
 * @copyright   2024 Elicus Technologies Private Limited
 * @version     1.11.0
 */

global $product, $post;

// Product object
$product = wc_get_product( get_the_ID() );

// on sale label
$on_sale = '';
if ( 'on' === $show_sale_badge && $product->is_on_sale() ) {
	$sale_badge = self::get_product_sale_badge( $post, $product, $sale_badge_text, $sale_label_text );
	$on_sale = apply_filters( 'woocommerce_sale_flash', $sale_badge, $post, $product );
}

// Get categories list
$categories = '';
if ( 'on' === $show_categories || in_array( 'categories', $inactive_state, true ) ) {
	$categories = sprintf(
		'<div class="dipl-woo-product__categories">%1$s</div>',
		wc_get_product_category_list( get_the_ID() )
	);
}

// Get the product title
$title = '';
if ( 'on' === $show_title || in_array( 'title', $inactive_state, true ) ) {
	$title = sprintf( 
		'<%3$s class="dipl-woo-product__title">
			<a href="%1$s">%2$s</a>
		</%3$s>',
		get_the_permalink(),
		get_the_title(),
		esc_html( $title_level )
	);
}

$short_desc_html = '';
$short_desc      = $product->get_short_description();
if ( ( 'on' === $show_description || in_array( 'description', $inactive_state, true ) ) && ! empty( $short_desc ) ) {

	// Limit the description
	if ( ! empty( $description_length ) ) {
		$short_desc = self::dipl_trim_content( $short_desc, $description_length );
	}

	$short_desc_html = sprintf( 
		'<div class="dipl-woo-product__description">%1$s</div>', 
		et_core_intentionally_unescaped( $short_desc, 'html' )
	);
}

$out_of_stock = '';
if ( 'on' === $show_outofstock_label ) {
	$out_of_stock = self::get_product_outofstock_badge( $product, $out_of_stock_label );
}

// Get star ratting
$star_ratting = '';
if ( 'on' === $show_rating && wc_review_ratings_enabled() ) {
	$star_ratting = sprintf( 
		'<div class="dipl-woo-product__rating">%1$s</div>',
		wc_get_rating_html( $product->get_average_rating() ) // WordPress.XSS.EscapeOutput.OutputNotEscaped.
	);
}

// Get price
$price_html = '';
$price      = $product->get_price_html();
if ( 'on' === $show_price && ! empty( $price ) ) {
	$price_html = sprintf( 
		'<div class="dipl-woo-product__price"><span class="price">%1$s</span></div>',
		et_core_intentionally_unescaped( $price, 'html' )
	);
}

$cart_btn = '';
if ( 'on' === $show_add_to_cart ) {
	$cart_btn = apply_filters(
		'woocommerce_loop_add_to_cart_link',
		sprintf(
			'<div class="dipl-woo-product__add_to_cart add_to_cart_inline">
				<a href="%s" rel="nofollow" data-product_id="%s" data-product_sku="%s" class="button %s product_type_%s">%s</a>
			</div>',
			esc_url( $product->add_to_cart_url() ),
			esc_attr( $product->get_id() ),
			esc_attr( $product->get_sku() ),
			$product->is_purchasable() ? 'add_to_cart_button' : '',
			esc_attr( $product->get_type() ),
			esc_html( $product->add_to_cart_text() )
		),
		$product
	);
}

$items .= sprintf(
	'<div class="%8$s" %9$s>
		<div class="dipl-products-accordion-item-inner">
			%10$s
			<div class="dipl-products-accordion-item-content %11$s">
				%1$s %2$s %3$s %4$s %5$s %6$s %7$s
			</div>
		</div>
	</div>',
	et_core_intentionally_unescaped( $categories, 'html' ),
	et_core_intentionally_unescaped( $title, 'html' ),
	et_core_intentionally_unescaped( $out_of_stock, 'html' ),
	et_core_intentionally_unescaped( $short_desc_html, 'html' ),
	et_core_intentionally_unescaped( $star_ratting, 'html' ),
	et_core_intentionally_unescaped( $price_html, 'html' ),
	et_core_intentionally_unescaped( $cart_btn, 'html' ),
	esc_attr( implode( ' ', wc_get_product_class( 'dipl-products-accordion-item', get_the_ID() ) ) ),
	( $accordion_background ) ? $accordion_background : '',
	et_core_intentionally_unescaped( $on_sale, 'html' ),
	! empty( $animation ) ? esc_attr( 'et_pb_animation_' . $animation ) : ''
);