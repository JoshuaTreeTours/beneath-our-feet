<?php
/**
 * One-time Media Library metadata enrichment.
 * Updates attachment titles, alt text, and descriptions without changing
 * image files, URLs, captions, page markup, or layout.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function bof_media_seo_panel_context( $attachment_id ) {
    $np_pages = get_posts(
        array(
            'post_type'      => 'page',
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'meta_key'       => '_bof_np_attachment_id',
            'meta_value'     => (int) $attachment_id,
        )
    );

    if ( $np_pages ) {
        $page_id     = (int) $np_pages[0];
        $park_title  = trim( (string) get_post_meta( $page_id, '_bof_np_park_title', true ) );
        $panel_title = trim( (string) get_post_meta( $page_id, '_bof_np_panel_title', true ) );
        if ( $panel_title ) {
            return array(
                'title'       => $park_title ? $park_title . ' Geology — ' . $panel_title : $panel_title . ' — Beneath Our Feet',
                'alt'         => $park_title ? $panel_title . ' geology illustration for ' . $park_title : $panel_title . ' geology illustration',
                'description' => $park_title
                    ? 'Illustrated geology panel exploring ' . $panel_title . ' at ' . $park_title . '. Part of Beneath Our Feet, a visual guide to geology, landscapes, fossils, deep time, and Earth history.'
                    : 'Illustrated geology panel exploring ' . $panel_title . '. Part of Beneath Our Feet, a visual guide to geology, landscapes, fossils, deep time, and Earth history.',
            );
        }
    }

    $topic_pages = get_posts(
        array(
            'post_type'      => 'page',
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'meta_key'       => '_bof_topic_attachment_id',
            'meta_value'     => (int) $attachment_id,
        )
    );

    if ( $topic_pages ) {
        $page_id     = (int) $topic_pages[0];
        $topic_title = trim( (string) get_post_meta( $page_id, '_bof_topic_title', true ) );
        $panel_title = trim( (string) get_post_meta( $page_id, '_bof_topic_panel_title', true ) );
        if ( $panel_title ) {
            return array(
                'title'       => $topic_title ? $panel_title . ' — ' . $topic_title . ' | Beneath Our Feet' : $panel_title . ' | Beneath Our Feet',
                'alt'         => $topic_title ? $panel_title . ' geology illustration in the ' . $topic_title . ' collection' : $panel_title . ' geology illustration',
                'description' => $topic_title
                    ? 'Illustrated geology panel about ' . $panel_title . ' in the ' . $topic_title . ' collection. Part of Beneath Our Feet, a visual guide to geology, landscapes, fossils, deep time, and Earth history.'
                    : 'Illustrated geology panel about ' . $panel_title . '. Part of Beneath Our Feet, a visual guide to geology, landscapes, fossils, deep time, and Earth history.',
            );
        }
    }

    return array();
}

function bof_media_seo_readable_name( $attachment_id ) {
    $attachment = get_post( $attachment_id );
    if ( ! $attachment ) {
        return '';
    }

    $title = trim( wp_strip_all_tags( $attachment->post_title ) );
    if ( $title && ! preg_match( '/^(source[-_ ]?\d+|Beneath Our Feet Panel \d+)$/i', $title ) ) {
        return $title;
    }

    $file = get_attached_file( $attachment_id );
    $base = $file ? pathinfo( basename( $file ), PATHINFO_FILENAME ) : '';
    $base = preg_replace( '/[-_]+/', ' ', $base );
    $base = preg_replace( '/\b\d{3,}\b/', '', $base );
    $base = trim( preg_replace( '/\s+/', ' ', $base ) );
    return $base ? ucwords( $base ) : $title;
}

function bof_media_seo_backfill() {
    $version = 1;
    if ( (int) get_option( 'bof_media_seo_metadata_version', 0 ) >= $version ) {
        return;
    }

    $attachments = get_posts(
        array(
            'post_type'      => 'attachment',
            'post_status'    => 'inherit',
            'post_mime_type' => 'image',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'orderby'        => 'ID',
            'order'          => 'ASC',
        )
    );

    foreach ( $attachments as $attachment_id ) {
        $attachment_id = (int) $attachment_id;
        $attachment    = get_post( $attachment_id );
        if ( ! $attachment ) {
            continue;
        }

        $context = bof_media_seo_panel_context( $attachment_id );
        if ( $context ) {
            wp_update_post(
                array(
                    'ID'           => $attachment_id,
                    'post_title'   => sanitize_text_field( $context['title'] ),
                    'post_content' => sanitize_textarea_field( $context['description'] ),
                )
            );
            update_post_meta( $attachment_id, '_wp_attachment_image_alt', sanitize_text_field( $context['alt'] ) );
            continue;
        }

        $readable = bof_media_seo_readable_name( $attachment_id );
        $updates  = array( 'ID' => $attachment_id );

        if ( $readable && preg_match( '/^(source[-_ ]?\d+|Beneath Our Feet Panel \d+)$/i', trim( (string) $attachment->post_title ) ) ) {
            $updates['post_title'] = sanitize_text_field( $readable . ' | Beneath Our Feet' );
        }

        if ( '' === trim( (string) $attachment->post_content ) && $readable ) {
            $updates['post_content'] = sanitize_textarea_field( 'Beneath Our Feet geology and Earth-history illustration: ' . $readable . '.' );
        }

        if ( count( $updates ) > 1 ) {
            wp_update_post( $updates );
        }

        $alt = trim( (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) );
        if ( '' === $alt && $readable ) {
            update_post_meta( $attachment_id, '_wp_attachment_image_alt', sanitize_text_field( $readable ) );
        }
    }

    update_option( 'bof_media_seo_metadata_version', $version, false );
}
add_action( 'init', 'bof_media_seo_backfill', 80 );
