<?php
/**
 * Beneath Our Feet theme functions.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once get_stylesheet_directory() . '/inc/panel-importer.php';

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

    if ( is_page( 'national-parks' ) ) {
        wp_enqueue_style(
            'beneath-our-feet-national-parks',
            get_stylesheet_directory_uri() . '/assets/national-parks.css',
            array( 'beneath-our-feet-style' ),
            filemtime( get_stylesheet_directory() . '/assets/national-parks.css' )
        );
    }

    $page_template = is_page() ? get_page_template_slug( get_queried_object_id() ) : '';
    if ( in_array( $page_template, array( 'template-bof-park.php', 'template-bof-panel.php' ), true ) ) {
        wp_enqueue_style(
            'beneath-our-feet-panel-viewer',
            get_stylesheet_directory_uri() . '/assets/panel-viewer.css',
            array( 'beneath-our-feet-style' ),
            filemtime( get_stylesheet_directory() . '/assets/panel-viewer.css' )
        );
    }
}
add_action( 'wp_enqueue_scripts', 'bof_assets' );

/**
 * Seed the National Parks landing page once. The page remains a normal
 * WordPress page afterward, so it can be edited in Gutenberg without Git.
 */
function bof_seed_national_parks_page() {
    $existing = get_page_by_path( 'national-parks', OBJECT, 'page' );
    if ( $existing ) {
        return;
    }

    $content_path = get_stylesheet_directory() . '/content/national-parks.html';
    if ( ! is_readable( $content_path ) ) {
        return;
    }

    $content = file_get_contents( $content_path );
    if ( false === $content || '' === trim( $content ) ) {
        return;
    }

    wp_insert_post(
        array(
            'post_title'   => 'National Parks',
            'post_name'    => 'national-parks',
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'post_content' => $content,
            'post_excerpt' => 'Discover the geology, deep time, wilderness, and raw landscapes preserved in America’s national parks.',
        )
    );
}
add_action( 'init', 'bof_seed_national_parks_page', 20 );
