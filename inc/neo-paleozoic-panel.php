<?php
/**
 * Add the missing late Paleozoic / Permian extinction panel to Deep Time.
 * Uses the exact WordPress Media Library attachment supplied by the site owner.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function bof_seed_neo_paleozoic_panel() {
    $version = 5;

    $root = get_page_by_path( 'collections', OBJECT, 'page' );
    if ( ! $root ) {
        return;
    }

    $deep_time = bof_topic_find_child_page( $root->ID, 'deep-time' );
    if ( ! $deep_time ) {
        return;
    }

    // Exact WordPress Media Library item provided by the user.
    $attachment_id = 176;
    if ( 'attachment' !== get_post_type( $attachment_id ) ) {
        return;
    }

    $panel_title = 'The Neo-Paleozoic: Age of Coal Forests and Crisis';
    $existing = bof_topic_find_child_page( $deep_time->ID, sanitize_title( $panel_title ) );

    // Keep this panel immediately before Mesozoic. Only shift later panels if
    // the Neo-Paleozoic page has not yet been created.
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
        'filename' => 'media-library-176',
    );

    if ( bof_topic_create_panel( $deep_time->ID, $topic, $panel, $attachment_id, 6 ) ) {
        update_option( 'bof_neo_paleozoic_panel_version', $version );
        flush_rewrite_rules( false );
    }
}
add_action( 'init', 'bof_seed_neo_paleozoic_panel', 37 );
