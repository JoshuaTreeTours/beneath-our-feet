<?php
/**
 * Topic collections for the floating menu.
 * Reuses images already imported into the WordPress Media Library.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function bof_topic_manifest() {
    static $manifest = null;
    if ( null !== $manifest ) {
        return $manifest;
    }

    $path = get_stylesheet_directory() . '/content/topic-collections.json';
    if ( ! is_readable( $path ) ) {
        $manifest = array( 'version' => 0, 'topics' => array() );
        return $manifest;
    }

    $decoded  = json_decode( file_get_contents( $path ), true );
    $manifest = is_array( $decoded ) ? $decoded : array( 'version' => 0, 'topics' => array() );
    return $manifest;
}

function bof_topic_find_child_page( $parent_id, $slug ) {
    $pages = get_posts(
        array(
            'post_type'      => 'page',
            'post_status'    => array( 'publish', 'draft', 'private' ),
            'post_parent'    => (int) $parent_id,
            'name'           => sanitize_title( $slug ),
            'posts_per_page' => 1,
        )
    );
    return $pages ? $pages[0] : null;
}

function bof_topic_attachment_id( $filename ) {
    $filename = sanitize_file_name( $filename );
    $ids = get_posts(
        array(
            'post_type'      => 'attachment',
            'post_status'    => 'inherit',
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'meta_key'       => '_bof_archive_source_filename',
            'meta_value'     => $filename,
        )
    );
    return $ids ? (int) $ids[0] : 0;
}

function bof_topic_root_page() {
    $root = get_page_by_path( 'collections', OBJECT, 'page' );
    if ( $root ) {
        return $root;
    }

    $root_id = wp_insert_post(
        array(
            'post_type'    => 'page',
            'post_status'  => 'publish',
            'post_title'   => 'Collections',
            'post_name'    => 'collections',
            'post_content' => '',
        ),
        true
    );

    return is_wp_error( $root_id ) ? null : get_post( $root_id );
}

function bof_topic_create_index( $root_id, $topic, $order ) {
    $page = bof_topic_find_child_page( $root_id, $topic['slug'] );
    $postarr = array(
        'post_type'    => 'page',
        'post_status'  => 'publish',
        'post_parent'  => (int) $root_id,
        'post_title'   => sanitize_text_field( $topic['title'] ),
        'post_name'    => sanitize_title( $topic['slug'] ),
        'menu_order'   => (int) $order,
        'post_content' => '',
    );

    if ( $page ) {
        $postarr['ID'] = $page->ID;
        $page_id = wp_update_post( $postarr, true );
    } else {
        $page_id = wp_insert_post( $postarr, true );
    }

    if ( is_wp_error( $page_id ) ) {
        return 0;
    }

    update_post_meta( $page_id, '_bof_topic_index', '1' );
    update_post_meta( $page_id, '_bof_topic_slug', sanitize_title( $topic['slug'] ) );
    update_post_meta( $page_id, '_bof_topic_description', sanitize_text_field( isset( $topic['description'] ) ? $topic['description'] : '' ) );
    return (int) $page_id;
}

function bof_topic_create_panel( $topic_page_id, $topic, $panel, $attachment_id, $order ) {
    $base_slug = sanitize_title( $panel['title'] );
    $page = bof_topic_find_child_page( $topic_page_id, $base_slug );

    $postarr = array(
        'post_type'    => 'page',
        'post_status'  => 'publish',
        'post_parent'  => (int) $topic_page_id,
        'post_title'   => sanitize_text_field( $panel['title'] ),
        'post_name'    => $base_slug,
        'menu_order'   => (int) $order,
        'post_content' => '',
    );

    if ( $page ) {
        $postarr['ID'] = $page->ID;
        $page_id = wp_update_post( $postarr, true );
    } else {
        $page_id = wp_insert_post( $postarr, true );
    }

    if ( is_wp_error( $page_id ) ) {
        return 0;
    }

    update_post_meta( $page_id, '_bof_topic_panel', '1' );
    update_post_meta( $page_id, '_bof_topic_attachment_id', (int) $attachment_id );
    update_post_meta( $page_id, '_bof_topic_panel_order', (int) $order );
    update_post_meta( $page_id, '_bof_topic_slug', sanitize_title( $topic['slug'] ) );
    update_post_meta( $page_id, '_bof_topic_title', sanitize_text_field( $topic['title'] ) );
    update_post_meta( $page_id, '_bof_topic_panel_title', sanitize_text_field( $panel['title'] ) );
    update_post_meta( $page_id, '_bof_topic_source_filename', isset( $panel['filename'] ) ? sanitize_file_name( $panel['filename'] ) : '' );
    return (int) $page_id;
}

function bof_topic_seed_library() {
    $manifest = bof_topic_manifest();
    $version  = isset( $manifest['version'] ) ? (int) $manifest['version'] : 0;
    if ( ! $version || (int) get_option( 'bof_topic_library_version', 0 ) >= $version ) {
        return;
    }

    $root = bof_topic_root_page();
    if ( ! $root ) {
        return;
    }

    $created = 0;
    foreach ( $manifest['topics'] as $topic_order => $topic ) {
        $topic_page_id = bof_topic_create_index( $root->ID, $topic, $topic_order + 1 );
        if ( ! $topic_page_id ) {
            continue;
        }

        foreach ( $topic['panels'] as $panel_order => $panel ) {
            $attachment_id = bof_topic_attachment_id( $panel['filename'] );
            if ( ! $attachment_id ) {
                continue;
            }
            if ( bof_topic_create_panel( $topic_page_id, $topic, $panel, $attachment_id, $panel_order + 1 ) ) {
                $created++;
            }
        }
    }

    if ( $created ) {
        update_option( 'bof_topic_library_version', $version );
        flush_rewrite_rules( false );
    }
}
add_action( 'init', 'bof_topic_seed_library', 35 );

/**
 * Restore the missing Cenozoic panel to Deep Time immediately after Mesozoic.
 * source-044.webp is the imported "Cenozoic Era: Age of Mammals" artwork.
 */
function bof_topic_seed_cenozoic_panel() {
    $version = 2;
    if ( (int) get_option( 'bof_deep_time_cenozoic_version', 0 ) >= $version ) {
        return;
    }

    $root = get_page_by_path( 'collections', OBJECT, 'page' );
    if ( ! $root ) {
        return;
    }

    $deep_time = bof_topic_find_child_page( $root->ID, 'deep-time' );
    if ( ! $deep_time ) {
        return;
    }

    $attachment_id = bof_topic_attachment_id( 'source-044.webp' );
    if ( ! $attachment_id ) {
        return;
    }

    $existing = bof_topic_find_child_page( $deep_time->ID, 'the-cenozoic-era' );
    if ( ! $existing ) {
        $later_pages = get_posts(
            array(
                'post_type'      => 'page',
                'post_status'    => 'publish',
                'post_parent'    => (int) $deep_time->ID,
                'posts_per_page' => -1,
                'meta_key'       => '_bof_topic_panel_order',
                'orderby'        => 'meta_value_num',
                'order'          => 'DESC',
            )
        );

        foreach ( $later_pages as $later_page ) {
            $order = (int) get_post_meta( $later_page->ID, '_bof_topic_panel_order', true );
            if ( $order >= 7 ) {
                update_post_meta( $later_page->ID, '_bof_topic_panel_order', $order + 1 );
                wp_update_post(
                    array(
                        'ID'         => $later_page->ID,
                        'menu_order' => $order + 1,
                    )
                );
            }
        }
    }

    $topic = array(
        'slug'  => 'deep-time',
        'title' => 'Deep Time',
    );
    $panel = array(
        'title'    => 'The Cenozoic Era',
        'filename' => 'source-044.webp',
    );

    if ( bof_topic_create_panel( $deep_time->ID, $topic, $panel, $attachment_id, 7 ) ) {
        update_option( 'bof_deep_time_cenozoic_version', $version );
        flush_rewrite_rules( false );
    }
}
add_action( 'init', 'bof_topic_seed_cenozoic_panel', 36 );

function bof_topic_template_include( $template ) {
    if ( ! is_page() ) {
        return $template;
    }

    $page_id = get_queried_object_id();
    if ( get_post_meta( $page_id, '_bof_topic_panel', true ) ) {
        $candidate = get_stylesheet_directory() . '/templates/topic-panel.php';
        return is_readable( $candidate ) ? $candidate : $template;
    }

    if ( get_post_meta( $page_id, '_bof_topic_index', true ) ) {
        $candidate = get_stylesheet_directory() . '/templates/topic-index.php';
        return is_readable( $candidate ) ? $candidate : $template;
    }

    return $template;
}
add_filter( 'template_include', 'bof_topic_template_include', 100 );

function bof_topic_assets() {
    if ( ! is_page() ) {
        return;
    }
    $page_id = get_queried_object_id();
    if ( ! get_post_meta( $page_id, '_bof_topic_panel', true ) && ! get_post_meta( $page_id, '_bof_topic_index', true ) ) {
        return;
    }

    $path = get_stylesheet_directory() . '/assets/national-park-viewer.css';
    wp_enqueue_style(
        'beneath-our-feet-national-park-viewer',
        get_stylesheet_directory_uri() . '/assets/national-park-viewer.css',
        array( 'beneath-our-feet-style' ),
        is_readable( $path ) ? filemtime( $path ) : null
    );
}
add_action( 'wp_enqueue_scripts', 'bof_topic_assets', 21 );

function bof_topic_panel_neighbors( $current_id ) {
    $parent_id = wp_get_post_parent_id( $current_id );
    $pages = get_posts(
        array(
            'post_type'      => 'page',
            'post_status'    => 'publish',
            'post_parent'    => $parent_id,
            'posts_per_page' => -1,
            'meta_key'       => '_bof_topic_panel_order',
            'orderby'        => 'meta_value_num',
            'order'          => 'ASC',
        )
    );
    $ids = array_map( 'intval', wp_list_pluck( $pages, 'ID' ) );
    $position = array_search( (int) $current_id, $ids, true );
    if ( false === $position || ! $ids ) {
        return array( 0, 0 );
    }
    $count = count( $ids );
    return array(
        $ids[ ( $position - 1 + $count ) % $count ],
        $ids[ ( $position + 1 ) % $count ],
    );
}

function bof_topic_pages() {
    $root = get_page_by_path( 'collections', OBJECT, 'page' );
    if ( ! $root ) {
        return array();
    }

    $manifest = bof_topic_manifest();
    $pages = array();
    foreach ( $manifest['topics'] as $topic ) {
        $page = bof_topic_find_child_page( $root->ID, $topic['slug'] );
        if ( $page ) {
            $pages[] = $page;
        }
    }
    return $pages;
}

/** Temporary media audit endpoint used only to identify the existing Neo-Paleozoic panel. */
function bof_neo_paleozoic_media_audit() {
    if ( empty( $_GET['bof_neo_paleozoic_audit'] ) ) {
        return;
    }

    $attachments = get_posts(
        array(
            'post_type'      => 'attachment',
            'post_status'    => 'inherit',
            'posts_per_page' => -1,
            'orderby'        => 'ID',
            'order'          => 'ASC',
        )
    );

    $rows = array();
    foreach ( $attachments as $attachment ) {
        $filename = get_post_meta( $attachment->ID, '_bof_archive_source_filename', true );
        $url      = wp_get_attachment_image_url( $attachment->ID, 'large' );
        if ( $filename && $url ) {
            $rows[] = array(
                'filename' => $filename,
                'url'      => $url,
            );
        }
    }

    wp_send_json( $rows );
}
add_action( 'init', 'bof_neo_paleozoic_media_audit', 1 );
