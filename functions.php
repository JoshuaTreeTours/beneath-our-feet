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
require_once get_stylesheet_directory() . '/inc/menu-topic-redirects.php';
require_once get_stylesheet_directory() . '/inc/site-icon.php';

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
        $np_css = get_stylesheet_directory() . '/assets/national-parks.css';
        wp_enqueue_style(
            'beneath-our-feet-national-parks',
            get_stylesheet_directory_uri() . '/assets/national-parks.css',
            array( 'beneath-our-feet-style' ),
            is_readable( $np_css ) ? filemtime( $np_css ) : null
        );
    }
}
add_action( 'wp_enqueue_scripts', 'bof_assets' );

/**
 * Keep the Gutenberg mobile hero block intact, but always render it with the
 * approved WordPress Media Library image (attachment ID 18). This avoids the
 * blocked theme-level PHP image endpoint that Cloudways returns as 403.
 */
function bof_render_mobile_hero_from_media( $block_content, $block ) {
    if ( ! is_front_page() || empty( $block['blockName'] ) || 'core/cover' !== $block['blockName'] ) {
        return $block_content;
    }

    $class_name = isset( $block['attrs']['className'] ) ? (string) $block['attrs']['className'] : '';
    if ( false === strpos( $class_name, 'bof-mobile-hero' ) ) {
        return $block_content;
    }

    $mobile_hero_url = wp_get_attachment_image_url( 18, 'full' );
    if ( ! $mobile_hero_url ) {
        return $block_content;
    }

    $escaped_url = esc_url( $mobile_hero_url );

    // Replace the rendered Cover image src without changing the Gutenberg block itself.
    $block_content = preg_replace(
        '/(<img\b[^>]*\bsrc=["\'])[^"\']*(["\'][^>]*>)/i',
        '$1' . $escaped_url . '$2',
        $block_content,
        1
    );

    return $block_content;
}
add_filter( 'render_block', 'bof_render_mobile_hero_from_media', 20, 2 );

/**
 * Seed the National Parks landing page, and apply the current curated landing
 * content once when its theme version changes. After that it remains a normal
 * Gutenberg page that can be edited in WordPress.
 */
function bof_seed_national_parks_page() {
    $content_path = get_stylesheet_directory() . '/content/national-parks.html';
    if ( ! is_readable( $content_path ) ) {
        return;
    }

    $content = file_get_contents( $content_path );
    if ( false === $content || '' === trim( $content ) ) {
        return;
    }

    $version  = 2;
    $existing = get_page_by_path( 'national-parks', OBJECT, 'page' );

    if ( ! $existing ) {
        $page_id = wp_insert_post(
            array(
                'post_title'   => 'National Parks',
                'post_name'    => 'national-parks',
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'post_content' => $content,
                'post_excerpt' => 'Discover the geology, deep time, wilderness, and raw landscapes preserved in America’s national parks.',
            )
        );
        if ( $page_id && ! is_wp_error( $page_id ) ) {
            update_option( 'bof_np_landing_version', $version );
        }
        return;
    }

    if ( (int) get_option( 'bof_np_landing_version', 1 ) < $version ) {
        wp_update_post(
            array(
                'ID'           => $existing->ID,
                'post_content' => $content,
                'post_excerpt' => 'Discover the geology, deep time, wilderness, and raw landscapes preserved in America’s national parks.',
            )
        );
        update_option( 'bof_np_landing_version', $version );
    }
}
add_action( 'init', 'bof_seed_national_parks_page', 20 );

/**
 * Seed and version the Source Material page from the theme. After this update,
 * it remains a normal Gutenberg page and can be edited manually in WordPress.
 */
function bof_seed_source_material_page() {
    $content_path = get_stylesheet_directory() . '/content/source-material.html';
    if ( ! is_readable( $content_path ) ) {
        return;
    }

    $content = file_get_contents( $content_path );
    if ( false === $content || '' === trim( $content ) ) {
        return;
    }

    $version  = 2;
    $existing = get_page_by_path( 'source-material', OBJECT, 'page' );

    if ( ! $existing ) {
        $page_id = wp_insert_post(
            array(
                'post_title'   => 'Source Material',
                'post_name'    => 'source-material',
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'post_content' => $content,
                'post_excerpt' => 'Selected geology and oceanography references used for further reading and exploration.',
            )
        );
        if ( $page_id && ! is_wp_error( $page_id ) ) {
            update_option( 'bof_source_material_version', $version );
        }
        return;
    }

    if ( (int) get_option( 'bof_source_material_version', 1 ) < $version ) {
        wp_update_post(
            array(
                'ID'           => $existing->ID,
                'post_content' => $content,
                'post_excerpt' => 'Selected geology and oceanography references used for further reading and exploration.',
            )
        );
        update_option( 'bof_source_material_version', $version );
    }
}
add_action( 'init', 'bof_seed_source_material_page', 21 );
