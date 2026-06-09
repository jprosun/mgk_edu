<?php
/**
 * S01 Hero. @var array $args — eyebrow, title_before, title_highlight, title_after, search_button, proof
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$eyebrow         = $args['eyebrow']         ?? mgk_site_setting( 'hero_eyebrow' );
$title_before    = $args['title_before']    ?? mgk_site_setting( 'hero_title_before' );
$title_highlight = $args['title_highlight'] ?? mgk_site_setting( 'hero_title_highlight' );
$title_after     = $args['title_after']     ?? mgk_site_setting( 'hero_title_after' );
$proof           = $args['proof']           ?? mgk_site_setting( 'hero_proof' );
echo mgk_section_hero_layout( [], mgk_section_hero_copy( [], 
    mgk_section_hero_eyebrow( [ 'text' => $eyebrow ] ) .
    mgk_section_hero_title( [
        'before'    => $title_before,
        'highlight' => $title_highlight,
        'after'     => $title_after,
    ] ) .
    mgk_section_hero_lines() .
    mgk_section_hero_search( [ 'button' => $args['search_button'] ?? mgk_site_setting( 'hero_search_button' ) ] ) .
    mgk_section_hero_proof( [ 'text' => $proof ] )
) . mgk_section_hero_media() );
