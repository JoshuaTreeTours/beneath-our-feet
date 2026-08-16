<?php
/**
 * Bulk importer for the complete Beneath Our Feet image archive.
 * National Park images also create their park and panel pages automatically.
 *
 * The browser uploads the ZIP in small chunks and WordPress processes images
 * in short AJAX batches. This avoids normal PHP upload-size and request-time
 * limits while keeping the import safe to rerun after an interruption.
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

function bof_archive_temp_dir() {
    $uploads = wp_upload_dir();
    $dir = trailingslashit( $uploads['basedir'] ) . 'bof-archive-import-temp';
    if ( ! is_dir( $dir ) ) {
        wp_mkdir_p( $dir );
    }
    return $dir;
}

function bof_archive_state_key( $import_id ) {
    return 'bof_archive_state_' . sanitize_key( $import_id );
}

function bof_archive_ajax_authorize() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => 'Administrator access is required.' ), 403 );
    }
    check_ajax_referer( 'bof_archive_import_ajax', 'nonce' );
}

function bof_archive_ajax_chunk() {
    bof_archive_ajax_authorize();

    $import_id = isset( $_POST['import_id'] ) ? sanitize_key( wp_unslash( $_POST['import_id'] ) ) : '';
    $index     = isset( $_POST['chunk_index'] ) ? absint( $_POST['chunk_index'] ) : 0;
    $total     = isset( $_POST['chunk_total'] ) ? absint( $_POST['chunk_total'] ) : 0;

    if ( ! $import_id || ! $total || empty( $_FILES['chunk']['tmp_name'] ) ) {
        wp_send_json_error( array( 'message' => 'The upload chunk was invalid.' ), 400 );
    }

    $dir       = bof_archive_temp_dir();
    $part_path = trailingslashit( $dir ) . $import_id . '.zip.part';
    $zip_path  = trailingslashit( $dir ) . $import_id . '.zip';

    if ( 0 === $index ) {
        @unlink( $part_path );
        @unlink( $zip_path );
    }

    $input  = fopen( $_FILES['chunk']['tmp_name'], 'rb' );
    $output = fopen( $part_path, 'ab' );
    if ( ! $input || ! $output ) {
        if ( $input ) fclose( $input );
        if ( $output ) fclose( $output );
        wp_send_json_error( array( 'message' => 'WordPress could not write the ZIP upload.' ), 500 );
    }

    stream_copy_to_stream( $input, $output );
    fclose( $input );
    fclose( $output );

    if ( $index + 1 === $total && ! @rename( $part_path, $zip_path ) ) {
        wp_send_json_error( array( 'message' => 'WordPress could not finalize the ZIP upload.' ), 500 );
    }

    wp_send_json_success( array( 'received' => $index + 1, 'total' => $total ) );
}
add_action( 'wp_ajax_bof_archive_import_chunk', 'bof_archive_ajax_chunk' );

function bof_archive_ajax_start() {
    bof_archive_ajax_authorize();

    if ( ! class_exists( 'ZipArchive' ) ) {
        wp_send_json_error( array( 'message' => 'ZipArchive is not available on this server.' ), 500 );
    }

    $import_id = isset( $_POST['import_id'] ) ? sanitize_key( wp_unslash( $_POST['import_id'] ) ) : '';
    $zip_path  = trailingslashit( bof_archive_temp_dir() ) . $import_id . '.zip';
    if ( ! $import_id || ! is_readable( $zip_path ) ) {
        wp_send_json_error( array( 'message' => 'The uploaded ZIP could not be found.' ), 404 );
    }

    $zip = new ZipArchive();
    if ( true !== $zip->open( $zip_path ) ) {
        wp_send_json_error( array( 'message' => 'The uploaded ZIP could not be opened.' ), 400 );
    }

    $entries = array();
    for ( $i = 0; $i < $zip->numFiles; $i++ ) {
        $stat = $zip->statIndex( $i );
        if ( empty( $stat['name'] ) || '/' === substr( $stat['name'], -1 ) ) {
            continue;
        }
        $extension = strtolower( pathinfo( $stat['name'], PATHINFO_EXTENSION ) );
        if ( in_array( $extension, array( 'webp', 'jpg', 'jpeg', 'png' ), true ) ) {
            $entries[] = $stat['name'];
        }
    }
    $zip->close();

    if ( ! $entries ) {
        @unlink( $zip_path );
        wp_send_json_error( array( 'message' => 'No images were found in the ZIP.' ), 400 );
    }

    update_option(
        bof_archive_state_key( $import_id ),
        array(
            'zip_path' => $zip_path,
            'entries'  => $entries,
            'cursor'   => 0,
            'media'    => 0,
            'pages'    => 0,
            'parks'    => array(),
            'errors'   => array(),
        ),
        false
    );

    wp_send_json_success( array( 'total' => count( $entries ) ) );
}
add_action( 'wp_ajax_bof_archive_import_start', 'bof_archive_ajax_start' );

function bof_archive_ajax_process() {
    bof_archive_ajax_authorize();

    $import_id = isset( $_POST['import_id'] ) ? sanitize_key( wp_unslash( $_POST['import_id'] ) ) : '';
    $state_key = bof_archive_state_key( $import_id );
    $state     = get_option( $state_key );

    if ( ! $import_id || empty( $state['zip_path'] ) || empty( $state['entries'] ) ) {
        wp_send_json_error( array( 'message' => 'The import session could not be found. Select the ZIP and start again.' ), 404 );
    }

    $root = get_page_by_path( 'national-parks', OBJECT, 'page' );
    if ( ! $root ) {
        wp_send_json_error( array( 'message' => 'The National Parks landing page could not be found.' ), 500 );
    }

    $zip = new ZipArchive();
    if ( true !== $zip->open( $state['zip_path'] ) ) {
        wp_send_json_error( array( 'message' => 'The import ZIP could no longer be opened.' ), 500 );
    }

    $manifest          = bof_np_manifest();
    $panel_by_filename = array();
    foreach ( $manifest['panels'] as $panel ) {
        $panel_by_filename[ $panel['filename'] ] = $panel;
    }

    $batch_size = 8;
    $total      = count( $state['entries'] );
    $start      = (int) $state['cursor'];
    $end        = min( $total, $start + $batch_size );
    $message    = '';

    for ( $i = $start; $i < $end; $i++ ) {
        $entry_name = $state['entries'][ $i ];
        $basename   = basename( $entry_name );
        $panel      = isset( $panel_by_filename[ $basename ] ) ? $panel_by_filename[ $basename ] : null;
        $attachment_id = bof_archive_import_attachment( $zip, $entry_name, $panel );

        if ( is_wp_error( $attachment_id ) ) {
            $state['errors'][] = $attachment_id->get_error_message();
            $state['cursor'] = $i + 1;
            continue;
        }

        $state['media']++;

        if ( $panel ) {
            $park_page_id = bof_np_get_or_create_park_page( $root->ID, $panel['park_slug'], $panel['park_title'] );
            if ( is_wp_error( $park_page_id ) ) {
                $state['errors'][] = $park_page_id->get_error_message();
            } else {
                $state['parks'][ $panel['park_slug'] ] = (int) $park_page_id;
                $panel_page_id = bof_np_get_or_create_panel_page( $park_page_id, $panel, $attachment_id );
                if ( is_wp_error( $panel_page_id ) ) {
                    $state['errors'][] = $panel_page_id->get_error_message();
                } else {
                    $state['pages']++;
                }
            }
        }

        $state['cursor'] = $i + 1;
    }

    $zip->close();
    $complete = $state['cursor'] >= $total;

    if ( $complete ) {
        flush_rewrite_rules( false );
        @unlink( $state['zip_path'] );
        delete_option( $state_key );
        $message = sprintf(
            'Finished: %d images in Media Library, %d National Park panel pages, %d park collections.',
            (int) $state['media'],
            (int) $state['pages'],
            count( $state['parks'] )
        );
    } else {
        update_option( $state_key, $state, false );
    }

    wp_send_json_success(
        array(
            'processed' => (int) $state['cursor'],
            'total'     => $total,
            'complete'  => $complete,
            'media'     => (int) $state['media'],
            'pages'     => (int) $state['pages'],
            'parks'     => count( $state['parks'] ),
            'errors'    => count( $state['errors'] ),
            'message'   => $message,
        )
    );
}
add_action( 'wp_ajax_bof_archive_import_process', 'bof_archive_ajax_process' );

function bof_archive_admin_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $nonce = wp_create_nonce( 'bof_archive_import_ajax' );
    ?>
    <div class="wrap">
        <h1>Beneath Our Feet — Media Archive Import</h1>
        <p>Select the prepared <strong>Beneath Our Feet All Images WordPress Import</strong> ZIP. WordPress will add every image to Media Library, recognize the National Park panels, create the park collections and individual panel pages, and wire the previous/next viewer navigation automatically.</p>
        <p><strong>Keep this tab open until it reaches 100%.</strong> The importer is safe to repeat: images already imported from this archive are recognized and reused.</p>

        <p>
            <input id="bof-archive-file" type="file" accept=".zip,application/zip">
            <button id="bof-archive-start" class="button button-primary" disabled>Import all Beneath Our Feet images</button>
        </p>

        <div style="max-width:780px;height:24px;background:#e3e3e3;border-radius:4px;overflow:hidden">
            <div id="bof-archive-bar" style="width:0;height:100%;background:#38533b;transition:width .15s ease"></div>
        </div>
        <p id="bof-archive-status" style="font-size:15px"></p>
        <pre id="bof-archive-log" style="max-width:780px;white-space:pre-wrap;background:#fff;border:1px solid #ccd0d4;padding:12px;max-height:240px;overflow:auto"></pre>
    </div>

    <script>
    (function () {
        const input = document.getElementById('bof-archive-file');
        const button = document.getElementById('bof-archive-start');
        const bar = document.getElementById('bof-archive-bar');
        const status = document.getElementById('bof-archive-status');
        const log = document.getElementById('bof-archive-log');
        const ajaxUrl = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;
        const nonce = <?php echo wp_json_encode( $nonce ); ?>;
        const chunkSize = 5 * 1024 * 1024;

        function write(text) {
            log.textContent += text + "\n";
            log.scrollTop = log.scrollHeight;
        }

        async function request(fields) {
            const form = new FormData();
            Object.keys(fields).forEach(function (key) {
                if (fields[key] instanceof Blob) {
                    form.append(key, fields[key], 'archive.part');
                } else {
                    form.append(key, fields[key]);
                }
            });
            const response = await fetch(ajaxUrl, { method: 'POST', body: form, credentials: 'same-origin' });
            const json = await response.json();
            if (!json.success) {
                throw new Error(json.data && json.data.message ? json.data.message : 'WordPress import request failed.');
            }
            return json.data;
        }

        input.addEventListener('change', function () {
            button.disabled = !input.files.length;
        });

        button.addEventListener('click', async function () {
            const file = input.files[0];
            if (!file) return;

            button.disabled = true;
            input.disabled = true;
            bar.style.width = '0%';
            log.textContent = '';
            const importId = Date.now().toString(36) + '-' + Math.random().toString(36).slice(2, 9);
            const chunks = Math.ceil(file.size / chunkSize);

            try {
                write('Uploading ' + file.name + ' in ' + chunks + ' small chunks…');
                for (let i = 0; i < chunks; i++) {
                    const begin = i * chunkSize;
                    const end = Math.min(file.size, begin + chunkSize);
                    await request({
                        action: 'bof_archive_import_chunk',
                        nonce: nonce,
                        import_id: importId,
                        chunk_index: i,
                        chunk_total: chunks,
                        chunk: file.slice(begin, end)
                    });
                    const pct = Math.round(((i + 1) / chunks) * 30);
                    bar.style.width = pct + '%';
                    status.textContent = 'Uploading package: ' + (i + 1) + ' / ' + chunks;
                }

                const started = await request({ action: 'bof_archive_import_start', nonce: nonce, import_id: importId });
                write('Package received. ' + started.total + ' images found.');

                let complete = false;
                while (!complete) {
                    const result = await request({ action: 'bof_archive_import_process', nonce: nonce, import_id: importId });
                    const pct = 30 + Math.round((result.processed / result.total) * 70);
                    bar.style.width = Math.min(100, pct) + '%';
                    status.textContent = 'Media Library: ' + result.processed + ' / ' + result.total + ' images — ' + result.pages + ' National Park pages';
                    complete = !!result.complete;
                    if (result.message) write(result.message);
                }

                bar.style.width = '100%';
                status.innerHTML = '<strong>Complete.</strong> All images are in Media Library and the National Park collections are published.';
                write('The National Park viewer pages, park strip, and previous/next arrows are ready.');
            } catch (error) {
                status.innerHTML = '<strong>Import stopped:</strong> ' + error.message;
                write('ERROR: ' + error.message);
                button.disabled = false;
                input.disabled = false;
            }
        });
    })();
    </script>
    <?php
}
