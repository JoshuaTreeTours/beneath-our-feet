<?php
/**
 * One-time replacement for the Earth’s Clock panel (attachment 220).
 * Keeps the existing attachment ID, filename and public URL intact.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function bof_refresh_earth_clock_panel_once() {
    $done_key = 'bof_earth_clock_refresh_20260829_done';
    if ( get_option( $done_key ) ) {
        return;
    }

    $attachment_id = 220;
    $file = get_attached_file( $attachment_id );
    if ( ! $file || 'source-133.webp' !== basename( $file ) ) {
        error_log( 'BOF Earth Clock refresh aborted: attachment 220 is not source-133.webp.' );
        return;
    }

    $theme_dir = get_stylesheet_directory();
    $parts = array(
        $theme_dir . '/assets/earth-clock-refresh.part1.b64',
        $theme_dir . '/assets/earth-clock-refresh.part2.b64',
        $theme_dir . '/assets/earth-clock-refresh.part3.b64',
        $theme_dir . '/assets/earth-clock-refresh.part4.b64',
    );

    $encoded = '';
    foreach ( $parts as $part ) {
        if ( ! is_readable( $part ) ) {
            error_log( 'BOF Earth Clock refresh aborted: missing staged image part.' );
            return;
        }
        $chunk = file_get_contents( $part );
        if ( false === $chunk ) {
            error_log( 'BOF Earth Clock refresh aborted: could not read staged image part.' );
            return;
        }
        $encoded .= trim( $chunk );
    }

    $bytes = base64_decode( $encoded, true );
    unset( $encoded );
    if ( false === $bytes ) {
        error_log( 'BOF Earth Clock refresh aborted: invalid staged base64.' );
        return;
    }

    $expected_hash = '776d91cc0ea430259b79f24aff62f86411fa4503dd2ea4ff4161225e63453ab0';
    if ( ! hash_equals( $expected_hash, hash( 'sha256', $bytes ) ) ) {
        error_log( 'BOF Earth Clock refresh aborted: staged image hash mismatch.' );
        return;
    }

    $image_info = getimagesizefromstring( $bytes );
    if ( ! $image_info || 1536 !== (int) $image_info[0] || 1024 !== (int) $image_info[1] || 'image/webp' !== $image_info['mime'] ) {
        error_log( 'BOF Earth Clock refresh aborted: staged image validation failed.' );
        return;
    }

    $dir = dirname( $file );
    if ( ! is_dir( $dir ) || ! is_writable( $dir ) ) {
        error_log( 'BOF Earth Clock refresh aborted: uploads directory is not writable.' );
        return;
    }

    $tmp = $file . '.earth-clock-new';
    if ( strlen( $bytes ) !== file_put_contents( $tmp, $bytes, LOCK_EX ) ) {
        @unlink( $tmp );
        error_log( 'BOF Earth Clock refresh aborted: could not write temporary image.' );
        return;
    }
    unset( $bytes );

    if ( ! hash_equals( $expected_hash, hash_file( 'sha256', $tmp ) ) ) {
        @unlink( $tmp );
        error_log( 'BOF Earth Clock refresh aborted: temporary image hash mismatch.' );
        return;
    }

    $old_metadata = wp_get_attachment_metadata( $attachment_id );
    $old_sizes = array();
    if ( is_array( $old_metadata ) && ! empty( $old_metadata['sizes'] ) && is_array( $old_metadata['sizes'] ) ) {
        foreach ( $old_metadata['sizes'] as $size ) {
            if ( ! empty( $size['file'] ) ) {
                $old_sizes[] = $dir . '/' . basename( $size['file'] );
            }
        }
    }

    // Rename the validated file over the original so the public filename/URL never changes.
    if ( ! @rename( $tmp, $file ) ) {
        if ( ! @copy( $tmp, $file ) ) {
            @unlink( $tmp );
            error_log( 'BOF Earth Clock refresh aborted: could not replace original image.' );
            return;
        }
        @unlink( $tmp );
    }

    foreach ( array_unique( $old_sizes ) as $old_size ) {
        if ( $old_size !== $file && is_file( $old_size ) ) {
            @unlink( $old_size );
        }
    }

    require_once ABSPATH . 'wp-admin/includes/image.php';
    $metadata = wp_generate_attachment_metadata( $attachment_id, $file );
    if ( is_array( $metadata ) && ! empty( $metadata ) ) {
        wp_update_attachment_metadata( $attachment_id, $metadata );
    } else {
        error_log( 'BOF Earth Clock refresh warning: original replaced, but metadata regeneration returned empty.' );
    }

    clean_post_cache( $attachment_id );
    update_option(
        $done_key,
        array(
            'attachment_id' => $attachment_id,
            'filename'      => basename( $file ),
            'sha256'        => $expected_hash,
            'updated_at'    => gmdate( 'c' ),
        ),
        false
    );
}
add_action( 'init', 'bof_refresh_earth_clock_panel_once', 5 );
