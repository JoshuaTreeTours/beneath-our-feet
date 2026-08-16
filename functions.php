<?php
/**
 * Beneath Our Feet theme functions.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function bof_setup() {
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'responsive-embeds' );
    add_theme_support( 'align-wide' );
    add_theme_support( 'editor-styles' );
    add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );

    register_nav_menus(
        array(
            'primary' => __( 'Primary Menu', 'beneath-our-feet' ),
        )
    );
}
add_action( 'after_setup_theme', 'bof_setup' );

function bof_assets() {
    wp_enqueue_style(
        'beneath-our-feet-style',
        get_stylesheet_uri(),
        array(),
        wp_get_theme()->get( 'Version' )
    );
}
add_action( 'wp_enqueue_scripts', 'bof_assets' );
