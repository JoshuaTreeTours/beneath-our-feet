<?php
/**
 * Temporary, single-purpose runner for the Earth’s Clock image refresh.
 * This file is removed immediately after the replacement is verified.
 */

$expected_key = 'bof-earth-clock-20260829-776d91cc';
if ( ! isset( $_GET['key'] ) || ! hash_equals( $expected_key, (string) $_GET['key'] ) ) {
    http_response_code( 403 );
    exit( 'Forbidden' );
}

$wp_load = dirname( __DIR__, 3 ) . '/wp-load.php';
if ( ! is_readable( $wp_load ) ) {
    http_response_code( 500 );
    exit( 'WordPress bootstrap not found.' );
}
require_once $wp_load;

require_once __DIR__ . '/inc/earth-clock-refresh.php';

if ( ! function_exists( 'bof_refresh_earth_clock_panel_once' ) ) {
    http_response_code( 500 );
    exit( 'Refresh function unavailable.' );
}

bof_refresh_earth_clock_panel_once();

$attachment_id = 220;
$file = get_attached_file( $attachment_id );
$expected_hash = '776d91cc0ea430259b79f24aff62f86411fa4503dd2ea4ff4161225e63453ab0';
$actual_hash = $file && is_readable( $file ) ? hash_file( 'sha256', $file ) : '';
$metadata = wp_get_attachment_metadata( $attachment_id );

header( 'Content-Type: application/json; charset=utf-8' );
header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0' );

echo wp_json_encode(
    array(
        'ok'            => hash_equals( $expected_hash, $actual_hash ),
        'attachment_id' => $attachment_id,
        'filename'      => $file ? basename( $file ) : null,
        'sha256'        => $actual_hash,
        'width'         => is_array( $metadata ) && isset( $metadata['width'] ) ? (int) $metadata['width'] : null,
        'height'        => is_array( $metadata ) && isset( $metadata['height'] ) ? (int) $metadata['height'] : null,
        'sizes'         => is_array( $metadata ) && isset( $metadata['sizes'] ) ? array_keys( $metadata['sizes'] ) : array(),
        'source_url'    => wp_get_attachment_url( $attachment_id ),
    )
);
