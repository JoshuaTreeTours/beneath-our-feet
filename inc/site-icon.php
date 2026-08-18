<?php
/**
 * Beneath Our Feet site icon installer.
 *
 * Installs the branded 512px PNG into the WordPress Media Library and sets it
 * as the Site Icon. Runs once per icon version.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function bof_install_site_icon() {
    $version = 1;

    if ( (int) get_option( 'bof_site_icon_version', 0 ) >= $version ) {
        return;
    }

    $asset_path = get_stylesheet_directory() . '/assets/bof-site-icon.png';
    if ( ! is_readable( $asset_path ) ) {
        return;
    }

    $existing = get_posts(
        array(
            'post_type'      => 'attachment',
            'post_status'    => 'inherit',
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'meta_key'       => '_bof_site_icon_asset',
            'meta_value'     => (string) $version,
        )
    );

    if ( $existing ) {
        $attachment_id = (int) $existing[0];
    } else {
        $contents = file_get_contents( $asset_path );
        if ( false === $contents ) {
            return;
        }

        $upload = wp_upload_bits( 'beneath-our-feet-site-icon.png', null, $contents );
        if ( ! empty( $upload['error'] ) ) {
            return;
        }

        $attachment_id = wp_insert_attachment(
            array(
                'post_mime_type' => 'image/png',
                'post_title'     => 'Beneath Our Feet Site Icon',
                'post_content'   => '',
                'post_status'    => 'inherit',
            ),
            $upload['file']
        );

        if ( ! $attachment_id || is_wp_error( $attachment_id ) ) {
            return;
        }

        require_once ABSPATH . 'wp-admin/includes/image.php';
        $metadata = wp_generate_attachment_metadata( $attachment_id, $upload['file'] );
        if ( $metadata ) {
            wp_update_attachment_metadata( $attachment_id, $metadata );
        }

        update_post_meta( $attachment_id, '_wp_attachment_image_alt', 'Beneath Our Feet geology site icon' );
        update_post_meta( $attachment_id, '_bof_site_icon_asset', (string) $version );
    }

    update_option( 'site_icon', $attachment_id );
    update_option( 'bof_site_icon_version', $version );
}
add_action( 'init', 'bof_install_site_icon', 45 );

/**
 * Set the WordPress Site Title to the public-facing brand once.
 * This changes the displayed site name only; it does not change the domain or URLs.
 */
function bof_set_site_title_branding() {
    $version = 1;

    if ( (int) get_option( 'bof_site_title_version', 0 ) >= $version ) {
        return;
    }

    update_option( 'blogname', 'Beneath Our Feet' );
    update_option( 'bof_site_title_version', $version );
}
add_action( 'init', 'bof_set_site_title_branding', 5 );

/**
 * Keep the Pages admin browser tab clean and branded.
 * This changes only the browser tab title on the Pages list screen.
 */
function bof_force_pages_admin_tab_title() {
    $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

    if ( ! $screen || 'edit' !== $screen->base || 'page' !== $screen->post_type ) {
        return;
    }

    echo "<script>document.title='Beneath Our Feet';</script>\n";
}
add_action( 'admin_head-edit.php', 'bof_force_pages_admin_tab_title', 999 );
