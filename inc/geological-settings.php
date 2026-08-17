<?php
/**
 * Geological Settings collection.
 *
 * Builds a curated nine-panel collection from images already uploaded to the
 * WordPress media directory. The supplied URLs are resolved back to Media
 * Library attachment IDs so the existing topic viewer can render them without
 * duplicating the artwork.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Resolve one supplied uploads URL to an attachment ID.
 *
 * The normal attachment_url_to_postid() lookup is tried first. If the image
 * exists on disk but WordPress has no attachment row for it, create that row
 * against the existing file rather than downloading or duplicating the image.
 */
function bof_geological_settings_attachment_id( $url, $title ) {
    $url = esc_url_raw( $url );
    if ( ! $url ) {
        return 0;
    }

    $attachment_id = attachment_url_to_postid( $url );
    if ( $attachment_id ) {
        return (int) $attachment_id;
    }

    $url_path = (string) wp_parse_url( $url, PHP_URL_PATH );
    $marker   = '/wp-content/uploads/';
    $position = strpos( $url_path, $marker );
    if ( false === $position ) {
        return 0;
    }

    $relative_path = ltrim( substr( $url_path, $position + strlen( $marker ) ), '/' );
    if ( ! $relative_path ) {
        return 0;
    }

    $existing = get_posts(
        array(
            'post_type'      => 'attachment',
            'post_status'    => 'inherit',
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'meta_key'       => '_wp_attached_file',
            'meta_value'     => $relative_path,
        )
    );
    if ( $existing ) {
        return (int) $existing[0];
    }

    $uploads   = wp_upload_dir();
    $file_path = trailingslashit( $uploads['basedir'] ) . $relative_path;
    if ( ! is_readable( $file_path ) ) {
        return 0;
    }

    $filetype = wp_check_filetype( basename( $file_path ), null );
    $attachment_id = wp_insert_attachment(
        array(
            'guid'           => $url,
            'post_mime_type' => $filetype['type'] ? $filetype['type'] : 'image/png',
            'post_title'     => sanitize_text_field( $title ),
            'post_content'   => '',
            'post_status'    => 'inherit',
        ),
        $file_path,
        0,
        true
    );

    if ( is_wp_error( $attachment_id ) || ! $attachment_id ) {
        return 0;
    }

    update_attached_file( $attachment_id, $file_path );

    require_once ABSPATH . 'wp-admin/includes/image.php';
    $metadata = wp_generate_attachment_metadata( $attachment_id, $file_path );
    if ( is_array( $metadata ) ) {
        wp_update_attachment_metadata( $attachment_id, $metadata );
    }

    return (int) $attachment_id;
}

/** Seed the collection and preserve the requested panel order. */
function bof_seed_geological_settings_collection() {
    $version = 1;
    if ( (int) get_option( 'bof_geological_settings_version', 0 ) >= $version ) {
        return;
    }

    if ( ! function_exists( 'bof_topic_root_page' ) || ! function_exists( 'bof_topic_create_index' ) || ! function_exists( 'bof_topic_create_panel' ) ) {
        return;
    }

    $root = bof_topic_root_page();
    if ( ! $root ) {
        return;
    }

    $topic = array(
        'slug'        => 'geological-settings',
        'title'       => 'Geological Settings',
        'description' => 'Environments and landscapes recorded in the rock record',
    );

    $topic_page_id = bof_topic_create_index( $root->ID, $topic, 99 );
    if ( ! $topic_page_id ) {
        return;
    }

    $panels = array(
        array(
            'title' => 'Geological Settings',
            'url'   => 'https://beneath-our-feet.com/wp-content/uploads/2026/08/file_00000000b09081fd8c2127755982a0a1.png',
        ),
        array(
            'title' => 'Volcanoes',
            'url'   => 'https://beneath-our-feet.com/wp-content/uploads/2026/08/file_00000000645c81fdb3cf4455e8e7ce74.png',
        ),
        array(
            'title' => 'Deserts',
            'url'   => 'https://beneath-our-feet.com/wp-content/uploads/2026/08/file_00000000f14c81fda5273f42777c067d.png',
        ),
        array(
            'title' => 'The Ocean Floor',
            'url'   => 'https://beneath-our-feet.com/wp-content/uploads/2026/08/file_0000000059ac81fd965593b83b1eed53.png',
        ),
        array(
            'title' => 'Caves & Karst',
            'url'   => 'https://beneath-our-feet.com/wp-content/uploads/2026/08/file_000000000a3081fdaaaaa1b84790c253.png',
        ),
        array(
            'title' => 'Glaciers',
            'url'   => 'https://beneath-our-feet.com/wp-content/uploads/2026/08/file_00000000fc0481fd92627727e8354570.png',
        ),
        array(
            'title' => 'Mountains',
            'url'   => 'https://beneath-our-feet.com/wp-content/uploads/2026/08/file_00000000958481fdbaf93e260b15583c.png',
        ),
        array(
            'title' => 'Rivers',
            'url'   => 'https://beneath-our-feet.com/wp-content/uploads/2026/08/file_00000000024481fd9c1e36af6af8234e.png',
        ),
        array(
            'title' => 'Beaches',
            'url'   => 'https://beneath-our-feet.com/wp-content/uploads/2026/08/file_00000000fb3c81fdbe20f7bc830aab2a.png',
        ),
    );

    $created = 0;
    foreach ( $panels as $index => $item ) {
        $attachment_id = bof_geological_settings_attachment_id( $item['url'], $item['title'] );
        if ( ! $attachment_id ) {
            continue;
        }

        $panel = array(
            'title'    => $item['title'],
            'filename' => sanitize_file_name( basename( (string) wp_parse_url( $item['url'], PHP_URL_PATH ) ) ),
        );

        if ( bof_topic_create_panel( $topic_page_id, $topic, $panel, $attachment_id, $index + 1 ) ) {
            $created++;
        }
    }

    if ( count( $panels ) === $created ) {
        update_option( 'bof_geological_settings_version', $version );
        flush_rewrite_rules( false );
    }
}
add_action( 'init', 'bof_seed_geological_settings_collection', 38 );
