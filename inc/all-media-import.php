<?php
/**
 * Bulk importer for the complete Beneath Our Feet image archive.
 * National Park images also create their park and panel pages automatically.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function bof_archive_existing_attachment( $filename ) {
    $ids = get_posts(
        array(
            'post_type'      => 'attachment',
            'post_status'    => 'inherit',
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'meta_key'       => '_bof_archive_source_filename',
            'meta_value'     => sanitize_file_name( $filename ),
        )
    );
    return $ids ? (int) $ids[0] : 0;
}

function bof_archive_import_attachment( $zip, $entry_name, $panel = null ) {
    $filename = sanitize_file_name( basename( $entry_name ) );
    $existing = bof_archive_existing_attachment( $filename );
    if ( $existing ) {
        return $existing;
    }

    $bytes = $zip->getFromName( $entry_name );
    if ( false === $bytes ) {
        return new WP_Error( 'bof_archive_missing_file', 'Missing image in ZIP: ' . $entry_name );
    }

    $upload = wp_upload_bits( $filename, null, $bytes );
    if ( ! empty( $upload['error'] ) ) {
        return new WP_Error( 'bof_archive_upload_error', $upload['error'] );
    }

    $filetype = wp_check_filetype( $upload['file'] );
    if ( $panel ) {
        $title   = $panel['park_title'] . ' — ' . $panel['panel_title'];
        $caption = $panel['panel_title'] . ' geology panel from Beneath Our Feet.';
        $alt     = $panel['park_title'] . ' geology panel — ' . $panel['panel_title'];
    } else {
        $number  = preg_match( '/source-(\d+)/', $filename, $matches ) ? $matches[1] : '';
        $title   = $number ? 'Beneath Our Feet Panel ' . $number : ucwords( str_replace( array( '-', '_' ), ' ', pathinfo( $filename, PATHINFO_FILENAME ) ) );
        $caption = 'Beneath Our Feet geology panel.';
        $alt     = $title;
    }

    $attachment_id = wp_insert_attachment(
        array(
            'post_mime_type' => $filetype['type'] ? $filetype['type'] : 'image/webp',
            'post_title'     => $title,
            'post_content'   => '',
            'post_excerpt'   => $caption,
            'post_status'    => 'inherit',
        ),
        $upload['file']
    );

    if ( is_wp_error( $attachment_id ) ) {
        return $attachment_id;
    }

    require_once ABSPATH . 'wp-admin/includes/image.php';
    $metadata = wp_generate_attachment_metadata( $attachment_id, $upload['file'] );
    if ( $metadata ) {
        wp_update_attachment_metadata( $attachment_id, $metadata );
    }

    update_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt );
    update_post_meta( $attachment_id, '_bof_archive_source_filename', $filename );

    if ( $panel ) {
        update_post_meta( $attachment_id, '_bof_np_source_filename', $filename );
        update_post_meta( $attachment_id, '_bof_np_park_slug', sanitize_title( $panel['park_slug'] ) );
        update_post_meta( $attachment_id, '_bof_np_panel_order', (int) $panel['order'] );
    }

    return (int) $attachment_id;
}

function bof_archive_run_import( $tmp_zip ) {
    if ( function_exists( 'set_time_limit' ) ) {
        @set_time_limit( 0 );
    }
    ignore_user_abort( true );

    if ( ! class_exists( 'ZipArchive' ) ) {
        return new WP_Error( 'bof_archive_no_zip', 'ZipArchive is not available on this server.' );
    }

    $zip = new ZipArchive();
    if ( true !== $zip->open( $tmp_zip ) ) {
        return new WP_Error( 'bof_archive_bad_zip', 'The uploaded ZIP could not be opened.' );
    }

    $manifest = bof_np_manifest();
    $panel_by_filename = array();
    foreach ( $manifest['panels'] as $panel ) {
        $panel_by_filename[ $panel['filename'] ] = $panel;
    }

    $root = get_page_by_path( 'national-parks', OBJECT, 'page' );
    if ( ! $root ) {
        $zip->close();
        return new WP_Error( 'bof_archive_no_root', 'The National Parks landing page was not found.' );
    }

    $result = array(
        'media'  => 0,
        'pages'  => 0,
        'parks'  => array(),
        'errors' => array(),
    );

    for ( $i = 0; $i < $zip->numFiles; $i++ ) {
        $stat = $zip->statIndex( $i );
        if ( empty( $stat['name'] ) || substr( $stat['name'], -1 ) === '/' ) {
            continue;
        }

        $entry_name = $stat['name'];
        $extension  = strtolower( pathinfo( $entry_name, PATHINFO_EXTENSION ) );
        if ( ! in_array( $extension, array( 'webp', 'jpg', 'jpeg', 'png' ), true ) ) {
            continue;
        }

        $basename = basename( $entry_name );
        $panel    = isset( $panel_by_filename[ $basename ] ) ? $panel_by_filename[ $basename ] : null;
        $attachment_id = bof_archive_import_attachment( $zip, $entry_name, $panel );

        if ( is_wp_error( $attachment_id ) ) {
            $result['errors'][] = $attachment_id->get_error_message();
            continue;
        }
        $result['media']++;

        if ( ! $panel ) {
            continue;
        }

        $park_page_id = bof_np_get_or_create_park_page( $root->ID, $panel['park_slug'], $panel['park_title'] );
        if ( is_wp_error( $park_page_id ) ) {
            $result['errors'][] = $park_page_id->get_error_message();
            continue;
        }
        $result['parks'][ $panel['park_slug'] ] = $park_page_id;

        $panel_page_id = bof_np_get_or_create_panel_page( $park_page_id, $panel, $attachment_id );
        if ( is_wp_error( $panel_page_id ) ) {
            $result['errors'][] = $panel_page_id->get_error_message();
            continue;
        }
        $result['pages']++;
    }

    $zip->close();
    flush_rewrite_rules( false );
    return $result;
}

function bof_archive_admin_menu() {
    add_management_page(
        'Beneath Our Feet Media Archive',
        'BOF Media Archive',
        'manage_options',
        'bof-media-archive-import',
        'bof_archive_admin_page'
    );
}
add_action( 'admin_menu', 'bof_archive_admin_menu' );

function bof_archive_admin_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $result = null;
    if ( isset( $_POST['bof_archive_import_submit'] ) ) {
        check_admin_referer( 'bof_archive_import' );
        if ( empty( $_FILES['bof_archive_zip']['tmp_name'] ) || UPLOAD_ERR_OK !== (int) $_FILES['bof_archive_zip']['error'] ) {
            $result = new WP_Error( 'bof_archive_upload', 'Choose the prepared Beneath Our Feet media ZIP and try again.' );
        } else {
            $result = bof_archive_run_import( $_FILES['bof_archive_zip']['tmp_name'] );
        }
    }
    ?>
    <div class="wrap">
        <h1>Beneath Our Feet — Media Archive Import</h1>
        <p>This one import adds every image in the prepared archive to the WordPress Media Library. The National Park panels are recognized automatically, organized into park pages, and given individual viewer pages with previous/next navigation.</p>
        <p><strong>Safe to run again:</strong> already imported files are skipped by source filename, so an interrupted import can simply be repeated.</p>
        <?php if ( is_wp_error( $result ) ) : ?>
            <div class="notice notice-error"><p><?php echo esc_html( $result->get_error_message() ); ?></p></div>
        <?php elseif ( is_array( $result ) ) : ?>
            <div class="notice notice-success"><p><?php echo esc_html( sprintf( 'Import complete: %d media files processed, %d National Park panel pages, %d park pages.', $result['media'], $result['pages'], count( $result['parks'] ) ) ); ?></p></div>
            <?php if ( ! empty( $result['errors'] ) ) : ?>
                <div class="notice notice-warning"><p><?php echo esc_html( implode( ' | ', array_slice( $result['errors'], 0, 10 ) ) ); ?></p></div>
            <?php endif; ?>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data">
            <?php wp_nonce_field( 'bof_archive_import' ); ?>
            <table class="form-table"><tr><th scope="row">Prepared media ZIP</th><td><input type="file" name="bof_archive_zip" accept=".zip,application/zip" required></td></tr></table>
            <p class="submit"><button type="submit" class="button button-primary" name="bof_archive_import_submit" value="1">Import all Beneath Our Feet images</button></p>
        </form>
    </div>
    <?php
}
