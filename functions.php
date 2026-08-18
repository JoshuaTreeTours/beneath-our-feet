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
require_once get_stylesheet_directory() . '/inc/home-category-icons.php';
require_once get_stylesheet_directory() . '/inc/structured-data.php';
require_once get_stylesheet_directory() . '/inc/structured-data-socials.php';
require_once get_stylesheet_directory() . '/inc/media-seo-metadata.php';

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

/**
 * Give the homepage a concise search description without changing visible content.
 */
function bof_home_meta_description() {
    if ( ! is_front_page() ) {
        return;
    }

    $description = 'Explore Earth’s geology through visual stories of rocks, fossils, tectonics, landscapes and deep time. Discover the evidence beneath our feet.';
    echo "\n<meta name=\"description\" content=\"" . esc_attr( $description ) . "\">\n";
}
add_action( 'wp_head', 'bof_home_meta_description', 5 );

function bof_assets() {
    $theme_css = get_stylesheet_directory() . '/style.css';
    wp_enqueue_style(
        'beneath-our-feet-style',
        get_stylesheet_uri(),
        array(),
        is_readable( $theme_css ) ? filemtime( $theme_css ) : wp_get_theme()->get( 'Version' )
    );

    /* The desktop hero image already contains the Beneath Our Feet title and
       subtitle. Hide only Gutenberg's duplicated overlay copy on desktop. */
    if ( is_front_page() ) {
        wp_add_inline_style(
            'beneath-our-feet-style',
            '@media (min-width:781px){.bof-home-hero .wp-block-cover__inner-container{display:none!important;}}'
        );
    }

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
 * approved WordPress Media Library image (attachment ID 18). Serve WordPress's
 * 768px medium-large derivative on the front end instead of the 1023px master
 * so mobile LCP is lighter while the original Media Library file remains intact.
 */
function bof_render_mobile_hero_from_media( $block_content, $block ) {
    if ( ! is_front_page() || empty( $block['blockName'] ) || 'core/cover' !== $block['blockName'] ) {
        return $block_content;
    }

    $class_name = isset( $block['attrs']['className'] ) ? (string) $block['attrs']['className'] : '';
    if ( false === strpos( $class_name, 'bof-mobile-hero' ) ) {
        return $block_content;
    }

    $mobile_hero_url = wp_get_attachment_image_url( 18, 'medium_large' );
    if ( ! $mobile_hero_url ) {
        $mobile_hero_url = wp_get_attachment_image_url( 18, 'full' );
    }
    if ( ! $mobile_hero_url ) {
        return $block_content;
    }

    $escaped_url = esc_url( $mobile_hero_url );

    // Replace the rendered Cover image source without changing Gutenberg data.
    $block_content = preg_replace(
        '/(<img\b[^>]*\bsrc=["\'])[^"\']*(["\'][^>]*>)/i',
        '$1' . $escaped_url . '$2',
        $block_content,
        1
    );

    // Prevent an existing srcset from causing the browser to choose the full-size master.
    $block_content = preg_replace( '/\s+srcset=("[^"]*"|\'[^\']*\')/i', '', $block_content, 1 );
    $block_content = preg_replace( '/\s+sizes=("[^"]*"|\'[^\']*\')/i', '', $block_content, 1 );

    return $block_content;
}
add_filter( 'render_block', 'bof_render_mobile_hero_from_media', 20, 2 );

/**
 * Link the three homepage category headings without changing the Gutenberg
 * homepage structure or surrounding card content.
 */
function bof_link_home_category_headings( $block_content, $block ) {
    if ( ! is_front_page() || empty( $block['blockName'] ) || 'core/heading' !== $block['blockName'] ) {
        return $block_content;
    }

    $links = array(
        'Places'      => home_url( '/collections/places/' ),
        'Deep Time'   => home_url( '/collections/deep-time/' ),
        'How We Know' => home_url( '/collections/how-we-know/' ),
    );

    foreach ( $links as $title => $url ) {
        if ( preg_match( '/<h3\b([^>]*)>\s*' . preg_quote( $title, '/' ) . '\s*<\/h3>/i', $block_content ) ) {
            return preg_replace(
                '/(<h3\b[^>]*>)\s*' . preg_quote( $title, '/') . '\s*(<\/h3>)/i',
                '$1<a href="' . esc_url( $url ) . '">' . esc_html( $title ) . '</a>$2',
                $block_content,
                1
            );
        }
    }

    return $block_content;
}
add_filter( 'render_block', 'bof_link_home_category_headings', 30, 2 );

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

    $version  = 9;
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
