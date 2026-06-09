<?php
/**
 * schemas/_common.php — Brand Settings + Contact
 * ===============================================
 * Mọi category đều include schema này.
 *
 * Pattern: Field "brand_name" trên ACF form auto-sync vào wp_options.blogname
 *          → 1 source of truth cho tên brand. Logo, hero, browser tab tự update.
 */

if ( ! function_exists( 'factory_home_id' ) ) {
    /** Page ID gắn ACF fields. Mỗi workspace có thể override. */
    function factory_home_id() {
        return (int) get_option( 'factory_home_id', 2721 );
    }
}

/** Brand name = Site Title (auto-synced from ACF brand_name field) */
function factory_brand_name() {
    return get_bloginfo( 'name' );
}

/** Brand tagline = Site Tagline (auto-synced from ACF brand_tagline field) */
function factory_brand_tagline() {
    return get_bloginfo( 'description' );
}

/* Register Brand Settings field group */
add_action( 'acf/init', function () {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) return;

    acf_add_local_field_group([
        'key'        => 'group_factory_brand',
        'title'      => '🏷 Brand Settings',
        'position'   => 'side',
        'menu_order' => 0,
        'location'   => [[[ 'param' => 'page', 'operator' => '==', 'value' => (string) factory_home_id() ]]],
        'fields'     => [
            ['key'=>'fld_brand_name',    'name'=>'brand_name',    'label'=>'Brand name',    'type'=>'text',  'instructions'=>'Auto-sync → Site Title. Logo + Hero + Browser tab tự update.'],
            ['key'=>'fld_brand_tagline', 'name'=>'brand_tagline', 'label'=>'Tagline',       'type'=>'text',  'instructions'=>'Auto-sync → Site Tagline.'],
            ['key'=>'fld_brand_address', 'name'=>'brand_address', 'label'=>'Address',       'type'=>'textarea', 'rows'=>2],
            ['key'=>'fld_brand_phone',   'name'=>'brand_phone',   'label'=>'Phone',         'type'=>'text'],
            ['key'=>'fld_brand_email',   'name'=>'brand_email',   'label'=>'Email',         'type'=>'email'],
            ['key'=>'fld_brand_map_url', 'name'=>'brand_map_url', 'label'=>'Google Maps URL (optional)', 'type'=>'text',
             'instructions'=>'Để TRỐNG = auto-generate từ Address bên trên. Hoặc paste link Google Maps pinned riêng (vd. https://maps.app.goo.gl/xxx).'],
        ],
    ]);
});

/** Auto-sync brand_name + brand_tagline ACF → wp_options. */
add_action( 'acf/save_post', function ( $post_id ) {
    if ( (int) $post_id !== factory_home_id() ) return;

    $name = get_field( 'brand_name', $post_id );
    if ( $name && $name !== get_option( 'blogname' ) ) {
        update_option( 'blogname', $name );
    }
    $tagline = get_field( 'brand_tagline', $post_id );
    if ( $tagline !== null && $tagline !== get_option( 'blogdescription' ) ) {
        update_option( 'blogdescription', $tagline );
    }
}, 20 );

/** Reverse-sync: load brand_name/tagline từ blogname nếu field trống. */
add_filter( 'acf/load_value/name=brand_name', function ( $value ) {
    return $value ?: get_bloginfo( 'name' );
});
add_filter( 'acf/load_value/name=brand_tagline', function ( $value ) {
    return $value ?: get_bloginfo( 'description' );
});

/* Brand shortcodes — dùng được trong UX Block, page content, header HTML */
add_shortcode( 'brand_name',    function () { return esc_html( factory_brand_name() ); });
add_shortcode( 'brand_tagline', function () { return esc_html( factory_brand_tagline() ); });
add_shortcode( 'brand_address', function () { return nl2br( esc_html( get_field('brand_address', factory_home_id()) ) ); });
add_shortcode( 'brand_phone',   function () { return esc_html( get_field('brand_phone',   factory_home_id()) ); });
add_shortcode( 'brand_email',   function () { return esc_html( get_field('brand_email',   factory_home_id()) ); });

/** Helper: lấy map URL — custom field hoặc auto-derive từ address */
function factory_brand_map_url() {
    $custom = get_field( 'brand_map_url', factory_home_id() );
    if ( $custom ) return $custom;

    $address = get_field( 'brand_address', factory_home_id() );
    if ( ! $address ) return '';
    // Replace newlines với commas + URL-encode
    // Chỉ replace LINE BREAKS (không phải single space) thành ', '
    $q = rawurlencode( trim( preg_replace( '/\r?\n+/', ', ', $address ) ) );
    return "https://www.google.com/maps/search/?api=1&query={$q}";
}

/** [brand_map_link text="View on map"] — clickable link mở Google Maps tab mới */
add_shortcode( 'brand_map_link', function ( $atts ) {
    $atts = shortcode_atts([
        'text'  => 'View on map',
        'class' => '',
    ], $atts);
    $url = factory_brand_map_url();
    if ( ! $url ) return '';
    return sprintf(
        '<a href="%s" target="_blank" rel="noopener" class="%s">%s</a>',
        esc_url( $url ),
        esc_attr( $atts['class'] ),
        esc_html( $atts['text'] )
    );
});

/** [brand_map_embed height="200"] — iframe nhúng Google Maps (không cần API key) */
add_shortcode( 'brand_map_embed', function ( $atts ) {
    $atts = shortcode_atts([
        'height' => '200',
        'width'  => '100%',
    ], $atts);
    $address = get_field( 'brand_address', factory_home_id() );
    if ( ! $address ) return '';
    // Chỉ replace LINE BREAKS (không phải single space) thành ', '
    $q = rawurlencode( trim( preg_replace( '/\r?\n+/', ', ', $address ) ) );
    return sprintf(
        '<iframe src="https://maps.google.com/maps?q=%s&output=embed" width="%s" height="%s" frameborder="0" style="border:0;display:block;" loading="lazy" allowfullscreen></iframe>',
        $q,
        esc_attr( $atts['width'] ),
        esc_attr( $atts['height'] )
    );
});
