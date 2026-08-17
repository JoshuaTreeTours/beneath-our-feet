<?php
/**
 * Beneath Our Feet theme functions.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once get_stylesheet_directory() . '/inc/national-park-library.php';
require_once get_stylesheet_directory() . '/inc/all-media-import.php';
require_once get_stylesheet_directory() . '/inc/national-park-reconcile.php';
require_once get_stylesheet_directory() . '/inc/topic-library.php';
require_once get_stylesheet_directory() . '/inc/geological-settings.php';
require_once get_stylesheet_directory() . '/inc/neo-paleozoic-panel.php';
require_once get_stylesheet_directory() . '/inc/menu-topic-redirects.php';
require_once get_stylesheet_directory() . '/inc/site-icon.php';
require_once get_stylesheet_directory() . '/inc/home-material-cards.php';
require_once get_stylesheet_directory() . '/inc/home-panel-123-performance.php';

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
