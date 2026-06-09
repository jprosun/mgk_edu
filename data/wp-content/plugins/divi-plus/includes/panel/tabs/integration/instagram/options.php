<?php
add_settings_section(
	'el-settings-integration-instagram-section',
	'', 
	'', 
	self::$menu_slug
);

add_settings_field(
	'el-dipl-instagram-access-token',
	esc_html__( 'Instagram Access Token', 'divi-plus' ),
	array( $this, 'el_textfield_render' ),
	esc_html( self::$menu_slug ),
	'el-settings-integration-instagram-section',
	array(
		'field_id'     => 'dipl-instagram-access-token',
		'setting'      => esc_html( self::$option ),
		'default'      => '',
		'id'           => 'el-dipl-instagram-access-token',
		'data-type'    => 'elicus-option',
		'info'		   => et_get_safe_localization(
			sprintf(
				'%1$s <a href="%2$s" title="%3$s" target="_blank">%4$s</a> %5$s <a href="%6$s" title="%7$s" target="_blank">%8$s</a> %9$s',
				esc_html__( 'Here you can enter the instagram access token for the instagram modules. You can use a single access token for all instagram modules and it can be saved in the plugin', 'divi-plus' ),
				esc_url( admin_url( '/options-general.php?page=divi-plus-options&menu=integration&submenu=instagram' ) ),
				esc_html__( 'Divi Plus Settings', 'divi-plus' ),
				esc_html__( 'settings', 'divi-plus' ),
				esc_html__( 'page. Or if you want to use different access token for each instagram module then you can enter here. Click', 'divi-plus' ),
				esc_url( 'https://diviextended.com/documentation/divi-plus/how-to-get-instagram-feed-access-token/' ),
				esc_html__( 'Facebook APP', 'divi-plus' ),
				esc_html__( 'here', 'divi-plus' ),
				esc_html__( 'to know about how to create one.', 'divi-plus' )
			)
		),
	)
);
