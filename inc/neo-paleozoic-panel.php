<?php
/**
 * Add the missing late Paleozoic / Permian extinction panel to Deep Time.
 * The supplied artwork is stored as six base64 staging parts in the theme,
 * reconstructed once into the WordPress Media Library, then reused normally.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function bof_neo_paleozoic_attachment_id() {
    $filename = 'neo-paleozoic-age-of-coal-forests-and-crisis.webp';

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
    if ( $ids ) {
        return (int) $ids[0];
    }

    $encoded = '';
    for ( $i = 1; $i <= 6; $i++ ) {
        $part = get_stylesheet_directory() . '/assets/panel-staging/neo-paleozoic.part' . str_pad( (string) $i, 2, '0', STR_PAD_LEFT );
        if ( ! is_readable( $part ) ) {
            return 0;
        }
        $piece = file_get_contents( $part );
        if ( false === $piece ) {
            return 0;
        }
        $encoded .= trim( $piece );
    }

    $bytes = base64_decode( $encoded, true );
    if ( false === $bytes || '' === $bytes ) {
        return 0;
    }

    $upload = wp_upload_bits( $filename, null, $bytes );
    if ( ! empty( $upload['error'] ) ) {
        return 0;
    }

    $attachment_id = wp_insert_attachment(
        array(
            'post_mime_type' => 'image/webp',
            'post_title'     => 'The Neo-Paleozoic: Age of Coal Forests and Crisis',
            'post_content'   => '',
            'post_excerpt'   => 'Late Paleozoic geology panel covering the Mississippian, Pennsylvanian, Permian, coal forests, and the end-Permian mass extinction.',
            'post_status'    => 'inherit',
        ),
        $upload['file']
    );

    if ( is_wp_error( $attachment_id ) ) {
        return 0;
    }

    require_once ABSPATH . 'wp-admin/includes/image.php';
    $metadata = wp_generate_attachment_metadata( $attachment_id, $upload['file'] );
    if ( $metadata ) {
        wp_update_attachment_metadata( $attachment_id, $metadata );
    }

    update_post_meta( $attachment_id, '_wp_attachment_image_alt', 'The Neo-Paleozoic: Age of Coal Forests and Crisis — 359 to 252 million years ago' );
    update_post_meta( $attachment_id, '_bof_archive_source_filename', $filename );

    return (int) $attachment_id;
}

function bof_seed_neo_paleozoic_panel() {
    $version = 1;
    if ( (int) get_option( 'bof_neo_paleozoic_panel_version', 0 ) >= $version ) {
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

    $attachment_id = bof_neo_paleozoic_attachment_id();
    if ( ! $attachment_id ) {
        return;
    }

    $panel_title = 'The Neo-Paleozoic: Age of Coal Forests and Crisis';
    $existing = bof_topic_find_child_page( $deep_time->ID, sanitize_title( $panel_title ) );

    // Position 6 is immediately after the Paleozoic overview and before Mesozoic.
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
            if ( $order >= 6 ) {
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
        'title'    => $panel_title,
        'filename' => 'neo-paleozoic-age-of-coal-forests-and-crisis.webp',
    );

    if ( bof_topic_create_panel( $deep_time->ID, $topic, $panel, $attachment_id, 6 ) ) {
        update_option( 'bof_neo_paleozoic_panel_version', $version );
        flush_rewrite_rules( false );
    }
}
add_action( 'init', 'bof_seed_neo_paleozoic_panel', 37 );
