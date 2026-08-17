<?php
/**
 * One-time reconciliation of imported National Park images to their pages.
 *
 * The full media archive is already in WordPress. This pass makes sure every
 * National Park entry in the curated manifest has its imported attachment,
 * park route, and full-size panel page wired together without another upload.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function bof_np_reconcile_find_attachment( $filename ) {
    $filename = sanitize_file_name( $filename );

    $ids = get_posts(
        array(
            'post_type'      => 'attachment',
            'post_status'    => 'inherit',
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'meta_query'     => array(
                'relation' => 'OR',
                array(
                    'key'   => '_bof_np_source_filename',
                    'value' => $filename,
                ),
                array(
                    'key'   => '_bof_archive_source_filename',
                    'value' => $filename,
                ),
            ),
        )
    );

    return $ids ? (int) $ids[0] : 0;
}

function bof_np_reconcile_imported_panels() {
    $version = 1;
    if ( (int) get_option( 'bof_np_reconcile_version', 0 ) >= $version ) {
        return;
    }

    if ( ! function_exists( 'bof_np_manifest' ) || ! function_exists( 'bof_np_get_or_create_park_page' ) || ! function_exists( 'bof_np_get_or_create_panel_page' ) ) {
        return;
    }

    $root = get_page_by_path( 'national-parks', OBJECT, 'page' );
    if ( ! $root ) {
        return;
    }

    $manifest = bof_np_manifest();
    $panels   = isset( $manifest['panels'] ) && is_array( $manifest['panels'] ) ? $manifest['panels'] : array();
    if ( ! $panels ) {
        return;
    }

    $matched = 0;
    $missing = array();
    $parks   = array();

    foreach ( $panels as $panel ) {
        if ( empty( $panel['filename'] ) || empty( $panel['park_slug'] ) || empty( $panel['panel_slug'] ) ) {
            continue;
        }

        $attachment_id = bof_np_reconcile_find_attachment( $panel['filename'] );
        if ( ! $attachment_id ) {
            $missing[] = $panel['filename'];
            continue;
        }

        /* Keep attachment metadata consistent even if the earlier import was
           interrupted between adding Media and creating the page. */
        update_post_meta( $attachment_id, '_bof_np_source_filename', sanitize_file_name( $panel['filename'] ) );
        update_post_meta( $attachment_id, '_bof_np_park_slug', sanitize_title( $panel['park_slug'] ) );
        update_post_meta( $attachment_id, '_bof_np_panel_order', (int) $panel['order'] );
        update_post_meta( $attachment_id, '_wp_attachment_image_alt', $panel['park_title'] . ' geology panel — ' . $panel['panel_title'] );

        $park_page_id = bof_np_get_or_create_park_page( $root->ID, $panel['park_slug'], $panel['park_title'] );
        if ( is_wp_error( $park_page_id ) ) {
            continue;
        }

        $panel_page_id = bof_np_get_or_create_panel_page( $park_page_id, $panel, $attachment_id );
        if ( is_wp_error( $panel_page_id ) ) {
            continue;
        }

        $parks[ $panel['park_slug'] ] = (int) $park_page_id;
        $matched++;
    }

    update_option(
        'bof_np_reconcile_report',
        array(
            'matched' => $matched,
            'parks'   => count( $parks ),
            'missing' => $missing,
            'time'    => time(),
        ),
        false
    );

    /* The prepared archive was already imported successfully. Mark this pass
       complete so it remains a cheap one-time repair rather than a query on
       every request. */
    update_option( 'bof_np_reconcile_version', $version, false );
    flush_rewrite_rules( false );
}
add_action( 'init', 'bof_np_reconcile_imported_panels', 35 );
