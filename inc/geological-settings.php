<?php
/**
 * Geological Settings collection.
 *
 * Builds a curated nine-panel collection from images already uploaded to the
 * WordPress media directory. WebP derivatives are generated beside the source
 * artwork and used by this collection for substantially faster delivery.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Resolve a supplied uploads URL to its local uploads path.
 */
function bof_geological_settings_upload_path( $url ) {
    $url_path = (string) wp_parse_url( esc_url_raw( $url ), PHP_URL_PATH );
    $marker   = '/wp-content/uploads/';
    $position = strpos( $url_path, $marker );
    if ( false === $position ) {
        return array();
    }

    $relative_path = ltrim( substr( $url_path, $position + strlen( $marker ) ), '/' );
    if ( ! $relative_path ) {
        return array();
    }

    $uploads = wp_upload_dir();
    return array(
        'relative' => $relative_path,
        'absolute' => trailingslashit( $uploads['basedir'] ) . $relative_path,
        'baseurl'  => trailingslashit( $uploads['baseurl'] ),
    );
}

/**
 * Return an attachment ID for a WebP derivative of one Geological Settings
 * source image, creating the derivative and Media Library row only when needed.
 */
function bof_geological_settings_webp_attachment_id( $url, $title ) {
    $source = bof_geological_settings_upload_path( $url );
    if ( empty( $source['absolute'] ) || ! is_readable( $source['absolute'] ) ) {
        return 0;
    }

    $source_relative = $source['relative'];
    $source_info     = pathinfo( $source_relative );
    $directory       = ! empty( $source_info['dirname'] ) && '.' !== $source_info['dirname'] ? trailingslashit( $source_info['dirname'] ) : '';
    $webp_relative   = $directory . $source_info['filename'] . '.webp';
    $uploads         = wp_upload_dir();
    $webp_path       = trailingslashit( $uploads['basedir'] ) . $webp_relative;
    $webp_url        = trailingslashit( $uploads['baseurl'] ) . $webp_relative;

    $existing = get_posts(
        array(
            'post_type'      => 'attachment',
            'post_status'    => 'inherit',
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'meta_key'       => '_wp_attached_file',
            'meta_value'     => $webp_relative,
        )
    );
    if ( $existing && is_readable( $webp_path ) ) {
        return (int) $existing[0];
    }

    if ( ! is_readable( $webp_path ) ) {
        $editor = wp_get_image_editor( $source['absolute'] );
        if ( is_wp_error( $editor ) ) {
            return 0;
        }

        $editor->set_quality( 82 );
        $saved = $editor->save( $webp_path, 'image/webp' );
        if ( is_wp_error( $saved ) || ! is_readable( $webp_path ) ) {
            return 0;
        }
    }

    if ( $existing ) {
        return (int) $existing[0];
    }

    $attachment_id = wp_insert_attachment(
        array(
            'guid'           => esc_url_raw( $webp_url ),
            'post_mime_type' => 'image/webp',
            'post_title'     => sanitize_text_field( $title ),
            'post_content'   => '',
            'post_status'    => 'inherit',
        ),
        $webp_path,
        0,
        true
    );

    if ( is_wp_error( $attachment_id ) || ! $attachment_id ) {
        return 0;
    }

    update_attached_file( $attachment_id, $webp_path );
    update_post_meta( $attachment_id, '_bof_geological_settings_source', $source_relative );

    require_once ABSPATH . 'wp-admin/includes/image.php';
    $metadata = wp_generate_attachment_metadata( $attachment_id, $webp_path );
    if ( is_array( $metadata ) ) {
        wp_update_attachment_metadata( $attachment_id, $metadata );
    }

    return (int) $attachment_id;
}

/** Seed or refresh the collection while preserving the requested panel order. */
function bof_seed_geological_settings_collection() {
    $version = 2;
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
        $attachment_id = bof_geological_settings_webp_attachment_id( $item['url'], $item['title'] );
        if ( ! $attachment_id ) {
            continue;
        }

        $panel = array(
            'title'    => $item['title'],
            'filename' => sanitize_file_name( pathinfo( (string) wp_parse_url( $item['url'], PHP_URL_PATH ), PATHINFO_FILENAME ) . '.webp' ),
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
