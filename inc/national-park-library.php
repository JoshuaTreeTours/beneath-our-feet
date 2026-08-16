<?php
/**
 * National Parks media importer, park indexes, and panel viewer helpers.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function bof_np_manifest() {
    static $manifest = null;
    if ( null !== $manifest ) {
        return $manifest;
    }

    $path = get_stylesheet_directory() . '/content/national-parks-manifest.json';
    if ( ! is_readable( $path ) ) {
        $manifest = array( 'panels' => array() );
        return $manifest;
    }

    $decoded = json_decode( file_get_contents( $path ), true );
    $manifest = is_array( $decoded ) ? $decoded : array( 'panels' => array() );
    return $manifest;
}

function bof_np_find_child_page( $parent_id, $slug ) {
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

function bof_np_get_or_create_park_page( $root_id, $park_slug, $park_title ) {
    $page = bof_np_find_child_page( $root_id, $park_slug );
    if ( $page ) {
        update_post_meta( $page->ID, '_bof_np_park_index', '1' );
        update_post_meta( $page->ID, '_bof_np_park_slug', $park_slug );
        return $page->ID;
    }

    $page_id = wp_insert_post(
        array(
            'post_type'    => 'page',
            'post_status'  => 'publish',
            'post_parent'  => (int) $root_id,
            'post_title'   => $park_title,
            'post_name'    => $park_slug,
            'post_content' => '',
        ),
        true
    );

    if ( is_wp_error( $page_id ) ) {
        return $page_id;
    }

    update_post_meta( $page_id, '_bof_np_park_index', '1' );
    update_post_meta( $page_id, '_bof_np_park_slug', $park_slug );
    return $page_id;
}

function bof_np_get_or_create_panel_page( $park_page_id, $panel, $attachment_id ) {
    $page = bof_np_find_child_page( $park_page_id, $panel['panel_slug'] );

    $postarr = array(
        'post_type'   => 'page',
        'post_status' => 'publish',
        'post_parent' => (int) $park_page_id,
        'post_title'  => $panel['panel_title'],
        'post_name'   => $panel['panel_slug'],
        'menu_order'  => (int) $panel['order'],
    );

    if ( $page ) {
        $postarr['ID'] = $page->ID;
        $page_id = wp_update_post( $postarr, true );
    } else {
        $postarr['post_content'] = '';
        $page_id = wp_insert_post( $postarr, true );
    }

    if ( is_wp_error( $page_id ) ) {
        return $page_id;
    }

    update_post_meta( $page_id, '_bof_np_panel', '1' );
    update_post_meta( $page_id, '_bof_np_attachment_id', (int) $attachment_id );
    update_post_meta( $page_id, '_bof_np_panel_order', (int) $panel['order'] );
    update_post_meta( $page_id, '_bof_np_park_slug', sanitize_title( $panel['park_slug'] ) );
    update_post_meta( $page_id, '_bof_np_park_title', sanitize_text_field( $panel['park_title'] ) );
    update_post_meta( $page_id, '_bof_np_panel_title', sanitize_text_field( $panel['panel_title'] ) );
    return $page_id;
}

function bof_np_existing_attachment( $filename ) {
    $ids = get_posts(
        array(
            'post_type'      => 'attachment',
            'post_status'    => 'inherit',
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'meta_key'       => '_bof_np_source_filename',
            'meta_value'     => $filename,
        )
    );
    return $ids ? (int) $ids[0] : 0;
}

function bof_np_import_attachment( $zip, $panel ) {
    $filename = sanitize_file_name( $panel['filename'] );
    $existing = bof_np_existing_attachment( $filename );
    if ( $existing ) {
        return $existing;
    }

    $bytes = $zip->getFromName( $panel['filename'] );
    if ( false === $bytes ) {
        return new WP_Error( 'bof_np_missing_file', 'Missing image in ZIP: ' . $panel['filename'] );
    }

    $upload = wp_upload_bits( $filename, null, $bytes );
    if ( ! empty( $upload['error'] ) ) {
        return new WP_Error( 'bof_np_upload_error', $upload['error'] );
    }

    $filetype = wp_check_filetype( $upload['file'] );
    $attachment_id = wp_insert_attachment(
        array(
            'post_mime_type' => $filetype['type'] ? $filetype['type'] : 'image/webp',
            'post_title'     => $panel['park_title'] . ' — ' . $panel['panel_title'],
            'post_content'   => '',
            'post_excerpt'   => $panel['panel_title'] . ' geology panel from Beneath Our Feet.',
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

    update_post_meta( $attachment_id, '_wp_attachment_image_alt', $panel['park_title'] . ' geology panel — ' . $panel['panel_title'] );
    update_post_meta( $attachment_id, '_bof_np_source_filename', $filename );
    update_post_meta( $attachment_id, '_bof_np_park_slug', sanitize_title( $panel['park_slug'] ) );
    update_post_meta( $attachment_id, '_bof_np_panel_order', (int) $panel['order'] );
    return (int) $attachment_id;
}

function bof_np_run_import( $tmp_zip ) {
    if ( ! class_exists( 'ZipArchive' ) ) {
        return new WP_Error( 'bof_np_no_zip', 'ZipArchive is not available on this server.' );
    }

    $zip = new ZipArchive();
    if ( true !== $zip->open( $tmp_zip ) ) {
        return new WP_Error( 'bof_np_bad_zip', 'The uploaded ZIP could not be opened.' );
    }

    $manifest = bof_np_manifest();
    $panels = isset( $manifest['panels'] ) && is_array( $manifest['panels'] ) ? $manifest['panels'] : array();
    if ( ! $panels ) {
        $zip->close();
        return new WP_Error( 'bof_np_empty_manifest', 'The National Parks manifest is empty.' );
    }

    $root = get_page_by_path( 'national-parks', OBJECT, 'page' );
    if ( ! $root ) {
        $zip->close();
        return new WP_Error( 'bof_np_no_root', 'The National Parks landing page was not found.' );
    }

    $result = array( 'media' => 0, 'pages' => 0, 'parks' => array(), 'errors' => array() );

    foreach ( $panels as $panel ) {
        $attachment_id = bof_np_import_attachment( $zip, $panel );
        if ( is_wp_error( $attachment_id ) ) {
            $result['errors'][] = $attachment_id->get_error_message();
            continue;
        }
        $result['media']++;

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

function bof_np_admin_menu() {
    add_management_page(
        'Beneath Our Feet National Parks',
        'BOF National Parks',
        'manage_options',
        'bof-national-parks-import',
        'bof_np_admin_page'
    );
}
add_action( 'admin_menu', 'bof_np_admin_menu' );

function bof_np_admin_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $result = null;
    if ( isset( $_POST['bof_np_import_submit'] ) ) {
        check_admin_referer( 'bof_np_import' );
        if ( empty( $_FILES['bof_np_zip']['tmp_name'] ) || UPLOAD_ERR_OK !== (int) $_FILES['bof_np_zip']['error'] ) {
            $result = new WP_Error( 'bof_np_upload', 'Choose the Beneath Our Feet National Parks import ZIP and try again.' );
        } else {
            $result = bof_np_run_import( $_FILES['bof_np_zip']['tmp_name'] );
        }
    }
    ?>
    <div class="wrap">
        <h1>Beneath Our Feet — National Parks Import</h1>
        <p>Upload the prepared National Parks ZIP. The importer adds every panel to Media, creates each park page, creates a page for every panel, and wires the viewer navigation automatically.</p>
        <?php if ( is_wp_error( $result ) ) : ?>
            <div class="notice notice-error"><p><?php echo esc_html( $result->get_error_message() ); ?></p></div>
        <?php elseif ( is_array( $result ) ) : ?>
            <div class="notice notice-success"><p><?php echo esc_html( sprintf( 'Import complete: %d media items, %d panel pages, %d park pages.', $result['media'], $result['pages'], count( $result['parks'] ) ) ); ?></p></div>
            <?php if ( ! empty( $result['errors'] ) ) : ?>
                <div class="notice notice-warning"><p><?php echo esc_html( implode( ' | ', $result['errors'] ) ); ?></p></div>
            <?php endif; ?>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data">
            <?php wp_nonce_field( 'bof_np_import' ); ?>
            <table class="form-table"><tr><th scope="row">National Parks ZIP</th><td><input type="file" name="bof_np_zip" accept=".zip,application/zip" required></td></tr></table>
            <p class="submit"><button type="submit" class="button button-primary" name="bof_np_import_submit" value="1">Import National Park panels</button></p>
        </form>
    </div>
    <?php
}

function bof_np_template_include( $template ) {
    if ( ! is_page() ) {
        return $template;
    }

    $page_id = get_queried_object_id();
    if ( get_post_meta( $page_id, '_bof_np_panel', true ) ) {
        $candidate = get_stylesheet_directory() . '/templates/national-park-panel.php';
        return is_readable( $candidate ) ? $candidate : $template;
    }

    if ( get_post_meta( $page_id, '_bof_np_park_index', true ) ) {
        $candidate = get_stylesheet_directory() . '/templates/national-park-index.php';
        return is_readable( $candidate ) ? $candidate : $template;
    }

    return $template;
}
add_filter( 'template_include', 'bof_np_template_include', 99 );

function bof_np_library_assets() {
    if ( ! is_page() ) {
        return;
    }
    $page_id = get_queried_object_id();
    if ( ! get_post_meta( $page_id, '_bof_np_panel', true ) && ! get_post_meta( $page_id, '_bof_np_park_index', true ) ) {
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
add_action( 'wp_enqueue_scripts', 'bof_np_library_assets', 20 );

function bof_np_panel_pages() {
    return get_posts(
        array(
            'post_type'      => 'page',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'meta_key'       => '_bof_np_panel_order',
            'orderby'        => 'meta_value_num',
            'order'          => 'ASC',
            'meta_query'     => array(
                array(
                    'key'   => '_bof_np_panel',
                    'value' => '1',
                ),
            ),
        )
    );
}

function bof_np_panel_neighbors( $current_id ) {
    $pages = bof_np_panel_pages();
    $ids = wp_list_pluck( $pages, 'ID' );
    $position = array_search( (int) $current_id, array_map( 'intval', $ids ), true );
    if ( false === $position || ! $ids ) {
        return array( 0, 0 );
    }

    $count = count( $ids );
    $prev = $ids[ ( $position - 1 + $count ) % $count ];
    $next = $ids[ ( $position + 1 ) % $count ];
    return array( (int) $prev, (int) $next );
}

function bof_np_park_pages() {
    $root = get_page_by_path( 'national-parks', OBJECT, 'page' );
    if ( ! $root ) {
        return array();
    }

    $manifest = bof_np_manifest();
    $seen = array();
    $pages = array();
    foreach ( $manifest['panels'] as $panel ) {
        if ( isset( $seen[ $panel['park_slug'] ] ) ) {
            continue;
        }
        $seen[ $panel['park_slug'] ] = true;
        $page = bof_np_find_child_page( $root->ID, $panel['park_slug'] );
        if ( $page ) {
            $pages[] = $page;
        }
    }
    return $pages;
}
