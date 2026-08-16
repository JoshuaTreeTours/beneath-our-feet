<?php
/**
 * Beneath Our Feet panel media importer.
 *
 * Imports the prepared ZIP package into the WordPress Media Library in small
 * AJAX batches, then builds National Park collection pages and panel viewer
 * pages with previous/next navigation.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function bof_np_park_config() {
    return array(
        'joshua-tree' => array(
            'name' => 'Joshua Tree National Park',
            'region' => 'Mojave & Colorado Deserts',
            'description' => 'Ancient crystalline rocks, great granitic masses, active faults, and relentless desert weathering make Joshua Tree a superb place to see deep time exposed in the open air.',
        ),
        'grand-canyon' => array(
            'name' => 'Grand Canyon National Park',
            'region' => 'Colorado Plateau',
            'description' => 'A mile-deep incision through layered sedimentary rocks and ancient crystalline basement reveals an extraordinary record approaching two billion years of Earth history.',
        ),
        'yellowstone' => array(
            'name' => 'Yellowstone National Park',
            'region' => 'Northern Rockies',
            'description' => 'A continental hotspot, giant calderas, hydrothermal systems, geysers, hot springs, and young volcanic rocks reveal a landscape that is still geologically restless.',
        ),
        'zion' => array(
            'name' => 'Zion National Park',
            'region' => 'Colorado Plateau',
            'description' => 'Towering Navajo Sandstone records immense Jurassic sand seas later uplifted and carved into one of the great canyon landscapes of the American Southwest.',
        ),
        'bryce-canyon' => array(
            'name' => 'Bryce Canyon National Park',
            'region' => 'Colorado Plateau',
            'description' => 'Frost, rain, gravity, and erosion sculpt the colorful Claron Formation into amphitheaters filled with hoodoos and fins.',
        ),
        'arches' => array(
            'name' => 'Arches National Park',
            'region' => 'Colorado Plateau',
            'description' => 'Salt tectonics, fractures, sandstone fins, weathering, and erosion combine to create the greatest concentration of natural stone arches on Earth.',
        ),
        'canyonlands' => array(
            'name' => 'Canyonlands National Park',
            'region' => 'Colorado Plateau',
            'description' => 'The Colorado and Green Rivers dissect a high desert plateau into mesas, buttes, needles, cliffs, and a vast network of canyons.',
        ),
        'capitol-reef' => array(
            'name' => 'Capitol Reef National Park',
            'region' => 'Colorado Plateau',
            'description' => 'The Waterpocket Fold bends a long sequence of sedimentary rock into a spectacular monocline, exposing chapters of Mesozoic environments across the desert.',
        ),
        'death-valley' => array(
            'name' => 'Death Valley National Park',
            'region' => 'Basin & Range',
            'description' => 'Crustal extension, fault-block mountains, salt flats, alluvial fans, ancient lake beds, and some of North America’s most dramatic relief meet in one extreme landscape.',
        ),
        'yosemite' => array(
            'name' => 'Yosemite National Park',
            'region' => 'Sierra Nevada',
            'description' => 'Granite crystallized deep beneath an ancient volcanic arc, then uplift and repeated glaciation carved domes, cliffs, valleys, and the iconic face of Half Dome.',
        ),
        'petrified-forest' => array(
            'name' => 'Petrified Forest National Park',
            'region' => 'Colorado Plateau',
            'description' => 'Triassic river deposits, colorful badlands, volcanic ash, fossils, and immense logs turned to stone preserve a vivid record of a very different world.',
        ),
        'great-smoky-mountains' => array(
            'name' => 'Great Smoky Mountains National Park',
            'region' => 'Southern Appalachians',
            'description' => 'Ancient sedimentary and metamorphic rocks folded and faulted during Appalachian mountain building form a deeply weathered, forested landscape of extraordinary antiquity.',
        ),
        'everglades' => array(
            'name' => 'Everglades National Park',
            'region' => 'South Florida',
            'description' => 'A young limestone platform, subtle elevation, freshwater flow, sea-level change, and living ecosystems together build one of Earth’s most distinctive low-relief landscapes.',
        ),
        'grand-teton' => array(
            'name' => 'Grand Teton National Park',
            'region' => 'Northern Rockies',
            'description' => 'A major normal fault lifted the Teton Range beside Jackson Hole, while glaciers sharpened the young mountain front into peaks, cirques, and U-shaped valleys.',
        ),
        'glacier' => array(
            'name' => 'Glacier National Park',
            'region' => 'Northern Rockies',
            'description' => 'Ancient Belt Supergroup rocks, enormous thrust faults, and repeated glaciation combine to reveal both deep crustal history and the power of moving ice.',
        ),
        'olympic' => array(
            'name' => 'Olympic National Park',
            'region' => 'Pacific Northwest',
            'description' => 'Oceanic sediments and basalt scraped from a subducting plate were uplifted into a rugged coastal mountain range and then carved by rivers and glaciers.',
        ),
        'sequoia' => array(
            'name' => 'Sequoia National Park',
            'region' => 'Sierra Nevada',
            'description' => 'Sierra Nevada granites, roof pendants, uplift, rivers, and ice-created valleys surround the giant sequoia groves with a landscape assembled over immense spans of time.',
        ),
        'white-sands' => array(
            'name' => 'White Sands National Park',
            'region' => 'Tularosa Basin',
            'description' => 'Gypsum dissolved from surrounding mountains, concentrated in a closed basin, and carried by wind forms a brilliant dune field unlike almost any other on Earth.',
        ),
        'carlsbad-caverns' => array(
            'name' => 'Carlsbad Caverns National Park',
            'region' => 'Guadalupe Mountains',
            'description' => 'A Permian reef complex became the foundation for immense caves later enlarged by sulfuric-acid dissolution deep underground.',
        ),
        'hawaii-volcanoes' => array(
            'name' => 'Hawaiʻi Volcanoes National Park',
            'region' => 'Hawaiian Hotspot',
            'description' => 'Shield volcanoes built over a mantle hotspot reveal basaltic volcanism at planetary scale, with lava flows continually creating new land.',
        ),
        'lassen-volcanic' => array(
            'name' => 'Lassen Volcanic National Park',
            'region' => 'Cascade Range',
            'description' => 'A remarkable concentration of volcanic forms — plug domes, shield volcanoes, cinder cones, lava flows, and hydrothermal features — records an active volcanic arc.',
        ),
        'theodore-roosevelt' => array(
            'name' => 'Theodore Roosevelt National Park',
            'region' => 'Northern Great Plains',
            'description' => 'River incision and rapid erosion expose colorful Paleogene sediments, lignite beds, concretions, and badlands carved from the edge of the Great Plains.',
        ),
        'badlands' => array(
            'name' => 'Badlands National Park',
            'region' => 'Great Plains',
            'description' => 'Oligocene sediments, ancient soils, volcanic ash, and a rich fossil record are being stripped into steep pinnacles and gullies by rapid erosion.',
        ),
        'crater-lake' => array(
            'name' => 'Crater Lake National Park',
            'region' => 'Cascade Range',
            'description' => 'The collapse of Mount Mazama after a catastrophic eruption created a deep caldera later filled by rain and snow to form the lake seen today.',
        ),
    );
}

function bof_panel_import_admin_menu() {
    add_management_page(
        'BOF Panel Import',
        'BOF Panel Import',
        'manage_options',
        'bof-panel-import',
        'bof_panel_import_admin_page'
    );
}
add_action( 'admin_menu', 'bof_panel_import_admin_menu' );

function bof_panel_import_admin_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    $nonce = wp_create_nonce( 'bof-panel-import' );
    ?>
    <div class="wrap">
        <h1>Beneath Our Feet — Panel Import</h1>
        <p>Choose the prepared <strong>Beneath Our Feet WordPress Media ZIP</strong>. The importer uploads it in chunks, adds every image to Media Library, and creates the National Park collection and panel pages automatically.</p>
        <p><strong>Leave this browser tab open until the progress bar reaches 100%.</strong></p>
        <input id="bof-import-file" type="file" accept=".zip,application/zip">
        <button id="bof-import-start" class="button button-primary" disabled>Import panels</button>
        <div style="max-width:760px;margin-top:20px;background:#e5e5e5;height:24px;border-radius:4px;overflow:hidden">
            <div id="bof-import-bar" style="width:0;height:100%;background:#38533b;transition:width .15s ease"></div>
        </div>
        <p id="bof-import-status" style="font-size:15px"></p>
        <pre id="bof-import-log" style="max-width:760px;white-space:pre-wrap;background:#fff;border:1px solid #ccd0d4;padding:12px;max-height:280px;overflow:auto"></pre>
    </div>
    <script>
    (function(){
        const fileInput = document.getElementById('bof-import-file');
        const startButton = document.getElementById('bof-import-start');
        const bar = document.getElementById('bof-import-bar');
        const status = document.getElementById('bof-import-status');
        const log = document.getElementById('bof-import-log');
        const ajaxUrl = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;
        const nonce = <?php echo wp_json_encode( $nonce ); ?>;
        const chunkSize = 5 * 1024 * 1024;

        function write(message) {
            log.textContent += message + "\n";
            log.scrollTop = log.scrollHeight;
        }

        async function post(fields) {
            const form = new FormData();
            Object.keys(fields).forEach(key => form.append(key, fields[key]));
            const response = await fetch(ajaxUrl, { method: 'POST', body: form, credentials: 'same-origin' });
            const data = await response.json();
            if (!data.success) {
                throw new Error(data.data && data.data.message ? data.data.message : 'Import request failed.');
            }
            return data.data;
        }

        fileInput.addEventListener('change', function(){
            startButton.disabled = !fileInput.files.length;
        });

        startButton.addEventListener('click', async function(){
            const file = fileInput.files[0];
            if (!file) return;
            startButton.disabled = true;
            fileInput.disabled = true;
            log.textContent = '';
            const importId = Date.now().toString(36) + '-' + Math.random().toString(36).slice(2,10);
            const chunks = Math.ceil(file.size / chunkSize);

            try {
                write('Uploading prepared media package…');
                for (let i = 0; i < chunks; i++) {
                    const start = i * chunkSize;
                    const end = Math.min(file.size, start + chunkSize);
                    const blob = file.slice(start, end);
                    await post({
                        action: 'bof_import_chunk',
                        nonce,
                        import_id: importId,
                        chunk_index: i,
                        chunk_total: chunks,
                        chunk: blob,
                    });
                    const pct = Math.round(((i + 1) / chunks) * 35);
                    bar.style.width = pct + '%';
                    status.textContent = 'Uploading ZIP: ' + (i + 1) + ' of ' + chunks + ' chunks';
                }

                write('ZIP uploaded. Reading manifest…');
                const started = await post({ action: 'bof_import_start', nonce, import_id: importId });
                write('Found ' + started.total + ' images. Beginning Media Library import…');

                let done = false;
                while (!done) {
                    const result = await post({ action: 'bof_import_process', nonce, import_id: importId });
                    const pct = 35 + Math.round((result.processed / result.total) * 65);
                    bar.style.width = Math.min(100, pct) + '%';
                    status.textContent = 'Media Library: ' + result.processed + ' of ' + result.total + ' images';
                    if (result.message) write(result.message);
                    done = !!result.complete;
                }

                bar.style.width = '100%';
                status.innerHTML = '<strong>Complete.</strong> All images are in Media Library and the National Park pages are published.';
                write('Finished. National Park panel pages and previous/next navigation are live.');
            } catch (error) {
                status.innerHTML = '<strong>Import stopped:</strong> ' + error.message;
                write('ERROR: ' + error.message);
                startButton.disabled = false;
                fileInput.disabled = false;
            }
        });
    })();
    </script>
    <?php
}

function bof_import_authorized() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => 'Administrator access is required.' ), 403 );
    }
    check_ajax_referer( 'bof-panel-import', 'nonce' );
}

function bof_import_temp_dir() {
    $uploads = wp_upload_dir();
    $dir = trailingslashit( $uploads['basedir'] ) . 'bof-import-temp';
    if ( ! is_dir( $dir ) ) {
        wp_mkdir_p( $dir );
    }
    return $dir;
}

function bof_import_state_key( $import_id ) {
    return 'bof_import_state_' . sanitize_key( $import_id );
}

function bof_ajax_import_chunk() {
    bof_import_authorized();

    $import_id = isset( $_POST['import_id'] ) ? sanitize_key( wp_unslash( $_POST['import_id'] ) ) : '';
    $index = isset( $_POST['chunk_index'] ) ? absint( $_POST['chunk_index'] ) : 0;
    $total = isset( $_POST['chunk_total'] ) ? absint( $_POST['chunk_total'] ) : 0;

    if ( ! $import_id || ! $total || empty( $_FILES['chunk']['tmp_name'] ) ) {
        wp_send_json_error( array( 'message' => 'Invalid upload chunk.' ), 400 );
    }

    $dir = bof_import_temp_dir();
    $part_path = trailingslashit( $dir ) . $import_id . '.zip.part';
    $zip_path = trailingslashit( $dir ) . $import_id . '.zip';

    if ( 0 === $index ) {
        @unlink( $part_path );
        @unlink( $zip_path );
    }

    $in = fopen( $_FILES['chunk']['tmp_name'], 'rb' );
    $out = fopen( $part_path, 'ab' );
    if ( ! $in || ! $out ) {
        if ( $in ) fclose( $in );
        if ( $out ) fclose( $out );
        wp_send_json_error( array( 'message' => 'Could not write the uploaded ZIP chunk.' ), 500 );
    }
    stream_copy_to_stream( $in, $out );
    fclose( $in );
    fclose( $out );

    if ( $index + 1 === $total ) {
        if ( ! @rename( $part_path, $zip_path ) ) {
            wp_send_json_error( array( 'message' => 'Could not finalize the uploaded ZIP.' ), 500 );
        }
    }

    wp_send_json_success( array( 'received' => $index + 1, 'total' => $total ) );
}
add_action( 'wp_ajax_bof_import_chunk', 'bof_ajax_import_chunk' );

function bof_ajax_import_start() {
    bof_import_authorized();

    if ( ! class_exists( 'ZipArchive' ) ) {
        wp_send_json_error( array( 'message' => 'ZipArchive is not available on this server.' ), 500 );
    }

    $import_id = isset( $_POST['import_id'] ) ? sanitize_key( wp_unslash( $_POST['import_id'] ) ) : '';
    $zip_path = trailingslashit( bof_import_temp_dir() ) . $import_id . '.zip';
    if ( ! $import_id || ! is_readable( $zip_path ) ) {
        wp_send_json_error( array( 'message' => 'Uploaded ZIP could not be found.' ), 404 );
    }

    $zip = new ZipArchive();
    if ( true !== $zip->open( $zip_path ) ) {
        wp_send_json_error( array( 'message' => 'The uploaded ZIP could not be opened.' ), 400 );
    }
    $manifest_json = $zip->getFromName( 'manifest.json' );
    $zip->close();
    if ( false === $manifest_json ) {
        wp_send_json_error( array( 'message' => 'manifest.json was not found in the prepared media package.' ), 400 );
    }

    $manifest = json_decode( $manifest_json, true );
    if ( empty( $manifest['items'] ) || ! is_array( $manifest['items'] ) ) {
        wp_send_json_error( array( 'message' => 'The media manifest is invalid.' ), 400 );
    }

    $state = array(
        'zip_path' => $zip_path,
        'items' => array_values( $manifest['items'] ),
        'cursor' => 0,
        'media' => array(),
        'errors' => array(),
    );
    update_option( bof_import_state_key( $import_id ), $state, false );

    wp_send_json_success( array( 'total' => count( $state['items'] ) ) );
}
add_action( 'wp_ajax_bof_import_start', 'bof_ajax_import_start' );

function bof_find_imported_attachment( $import_key ) {
    $ids = get_posts(
        array(
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'meta_key' => '_bof_import_key',
            'meta_value' => $import_key,
            'no_found_rows' => true,
        )
    );
    return $ids ? (int) $ids[0] : 0;
}

function bof_import_media_item( ZipArchive $zip, $item ) {
    if ( empty( $item['file'] ) ) {
        return new WP_Error( 'bof_missing_file', 'Manifest item is missing a file path.' );
    }

    $entry = ltrim( (string) $item['file'], '/' );
    $import_key = sha1( $entry );
    $existing = bof_find_imported_attachment( $import_key );
    if ( $existing ) {
        return $existing;
    }

    $stream = $zip->getStream( $entry );
    if ( ! $stream ) {
        return new WP_Error( 'bof_zip_entry', 'Could not read ' . $entry . ' from the ZIP.' );
    }

    $filename = sanitize_file_name( basename( $entry ) );
    $tmp = wp_tempnam( $filename );
    $out = fopen( $tmp, 'wb' );
    if ( ! $out ) {
        fclose( $stream );
        return new WP_Error( 'bof_temp_file', 'Could not create a temporary image file.' );
    }
    stream_copy_to_stream( $stream, $out );
    fclose( $stream );
    fclose( $out );

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $title = ! empty( $item['title'] ) ? sanitize_text_field( $item['title'] ) : pathinfo( $filename, PATHINFO_FILENAME );
    $file_array = array(
        'name' => $filename,
        'tmp_name' => $tmp,
        'type' => 'image/webp',
        'error' => 0,
        'size' => filesize( $tmp ),
    );

    $attachment_id = media_handle_sideload( $file_array, 0, $title );
    if ( is_wp_error( $attachment_id ) ) {
        @unlink( $tmp );
        return $attachment_id;
    }

    update_post_meta( $attachment_id, '_bof_import_key', $import_key );
    if ( ! empty( $item['source_name'] ) ) {
        update_post_meta( $attachment_id, '_bof_source_name', sanitize_text_field( $item['source_name'] ) );
    }
    update_post_meta( $attachment_id, '_wp_attachment_image_alt', $title );

    return (int) $attachment_id;
}

function bof_upsert_child_page( $title, $slug, $parent_id, $content = '' ) {
    $path = trim( get_page_uri( $parent_id ), '/' ) . '/' . $slug;
    $page = get_page_by_path( $path, OBJECT, 'page' );
    $data = array(
        'post_title' => $title,
        'post_name' => $slug,
        'post_parent' => $parent_id,
        'post_type' => 'page',
        'post_status' => 'publish',
        'post_content' => $content,
    );

    if ( $page ) {
        $data['ID'] = $page->ID;
        $id = wp_update_post( $data, true );
    } else {
        $id = wp_insert_post( $data, true );
    }
    return $id;
}

function bof_finalize_national_park_pages( $state ) {
    $landing = get_page_by_path( 'national-parks', OBJECT, 'page' );
    if ( ! $landing ) {
        return new WP_Error( 'bof_no_landing', 'The National Parks landing page could not be found.' );
    }

    $parks = bof_np_park_config();
    $by_park = array();
    foreach ( $state['items'] as $item ) {
        if ( empty( $item['is_national_park_panel'] ) || empty( $item['national_park'] ) || empty( $item['file'] ) ) {
            continue;
        }
        $park_slug = sanitize_key( $item['national_park'] );
        if ( ! isset( $parks[ $park_slug ] ) ) {
            continue;
        }
        $media_id = isset( $state['media'][ $item['file'] ] ) ? (int) $state['media'][ $item['file'] ] : 0;
        if ( ! $media_id ) {
            continue;
        }
        $item['media_id'] = $media_id;
        $by_park[ $park_slug ][] = $item;
    }

    $ordered_panel_pages = array();
    $park_page_ids = array();

    foreach ( $parks as $park_slug => $park ) {
        if ( empty( $by_park[ $park_slug ] ) ) {
            continue;
        }

        $park_content = '<p>' . esc_html( $park['description'] ) . '</p>';
        $park_page_id = bof_upsert_child_page( $park['name'], $park_slug, $landing->ID, $park_content );
        if ( is_wp_error( $park_page_id ) ) {
            return $park_page_id;
        }
        update_post_meta( $park_page_id, '_wp_page_template', 'template-bof-park.php' );
        update_post_meta( $park_page_id, '_bof_park_slug', $park_slug );
        update_post_meta( $park_page_id, '_bof_park_region', $park['region'] );
        $park_page_ids[ $park_slug ] = (int) $park_page_id;

        foreach ( $by_park[ $park_slug ] as $position => $item ) {
            $title = sanitize_text_field( $item['title'] );
            $slug = sanitize_title( $title );
            if ( '' === $slug ) {
                $slug = 'panel-' . ( $position + 1 );
            }
            $panel_content = '<p>Explore this Beneath Our Feet geology panel for ' . esc_html( $title ) . ' in ' . esc_html( $park['name'] ) . '.</p>';
            $panel_page_id = bof_upsert_child_page( $title, $slug, $park_page_id, $panel_content );
            if ( is_wp_error( $panel_page_id ) ) {
                return $panel_page_id;
            }

            update_post_meta( $panel_page_id, '_wp_page_template', 'template-bof-panel.php' );
            update_post_meta( $panel_page_id, '_bof_panel_media_id', (int) $item['media_id'] );
            update_post_meta( $panel_page_id, '_bof_panel_park_slug', $park_slug );
            update_post_meta( $panel_page_id, '_bof_panel_position', (int) $position );
            wp_update_post( array( 'ID' => $panel_page_id, 'menu_order' => (int) $position ) );
            $ordered_panel_pages[] = (int) $panel_page_id;
        }
    }

    $total_pages = count( $ordered_panel_pages );
    foreach ( $ordered_panel_pages as $i => $page_id ) {
        $prev = $i > 0 ? $ordered_panel_pages[ $i - 1 ] : 0;
        $next = $i + 1 < $total_pages ? $ordered_panel_pages[ $i + 1 ] : 0;
        update_post_meta( $page_id, '_bof_prev_page_id', $prev );
        update_post_meta( $page_id, '_bof_next_page_id', $next );
        update_post_meta( $page_id, '_bof_panel_global_position', $i + 1 );
        update_post_meta( $page_id, '_bof_panel_global_total', $total_pages );
    }

    $seed_path = get_stylesheet_directory() . '/content/national-parks.html';
    if ( is_readable( $seed_path ) ) {
        $seed = file_get_contents( $seed_path );
        if ( false !== $seed && '' !== trim( $seed ) ) {
            wp_update_post(
                array(
                    'ID' => $landing->ID,
                    'post_content' => $seed,
                )
            );
        }
    }

    return array(
        'park_pages' => count( $park_page_ids ),
        'panel_pages' => $total_pages,
    );
}

function bof_ajax_import_process() {
    bof_import_authorized();

    $import_id = isset( $_POST['import_id'] ) ? sanitize_key( wp_unslash( $_POST['import_id'] ) ) : '';
    $state_key = bof_import_state_key( $import_id );
    $state = get_option( $state_key );
    if ( ! $import_id || empty( $state['zip_path'] ) || empty( $state['items'] ) ) {
        wp_send_json_error( array( 'message' => 'Import state could not be found.' ), 404 );
    }

    $zip = new ZipArchive();
    if ( true !== $zip->open( $state['zip_path'] ) ) {
        wp_send_json_error( array( 'message' => 'The import ZIP can no longer be opened.' ), 500 );
    }

    $batch_size = 3;
    $total = count( $state['items'] );
    $start = (int) $state['cursor'];
    $end = min( $total, $start + $batch_size );
    $messages = array();

    for ( $i = $start; $i < $end; $i++ ) {
        $item = $state['items'][ $i ];
        $media_id = bof_import_media_item( $zip, $item );
        if ( is_wp_error( $media_id ) ) {
            $state['errors'][] = array(
                'file' => isset( $item['file'] ) ? $item['file'] : '',
                'message' => $media_id->get_error_message(),
            );
            $messages[] = 'Skipped ' . ( isset( $item['file'] ) ? basename( $item['file'] ) : 'unknown file' ) . ': ' . $media_id->get_error_message();
        } else {
            $state['media'][ $item['file'] ] = (int) $media_id;
        }
        $state['cursor'] = $i + 1;
    }
    $zip->close();

    $complete = $state['cursor'] >= $total;
    if ( $complete ) {
        $finalized = bof_finalize_national_park_pages( $state );
        if ( is_wp_error( $finalized ) ) {
            update_option( $state_key, $state, false );
            wp_send_json_error( array( 'message' => $finalized->get_error_message() ), 500 );
        }
        @unlink( $state['zip_path'] );
        delete_option( $state_key );
        $messages[] = sprintf(
            'Created %d National Park collection pages and %d individual panel pages.',
            $finalized['park_pages'],
            $finalized['panel_pages']
        );
    } else {
        update_option( $state_key, $state, false );
    }

    wp_send_json_success(
        array(
            'processed' => (int) $state['cursor'],
            'total' => $total,
            'complete' => $complete,
            'message' => implode( "\n", $messages ),
        )
    );
}
add_action( 'wp_ajax_bof_import_process', 'bof_ajax_import_process' );
