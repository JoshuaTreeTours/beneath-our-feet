<?php
/**
 * Restore the original Panel 123 artwork in the homepage visual-field-guide
 * feature while keeping the existing Gutenberg text and lightweight image
 * delivery. Attachment 214 is the original Panel 123 Media Library item.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function bof_restore_home_panel_123_feature( $block_content, $block ) {
    if ( ! is_front_page() || empty( $block['blockName'] ) || 'core/media-text' !== $block['blockName'] ) {
        return $block_content;
    }

    $class_name = isset( $block['attrs']['className'] ) ? (string) $block['attrs']['className'] : '';
    if ( false === strpos( $class_name, 'bof-home-media' ) ) {
        return $block_content;
    }

    $image = wp_get_attachment_image_src( 214, 'large' );
    if ( ! $image || empty( $image[0] ) ) {
        return $block_content;
    }

    if ( ! class_exists( 'WP_HTML_Tag_Processor' ) ) {
        return $block_content;
    }

    $processor = new WP_HTML_Tag_Processor( $block_content );
    if ( ! $processor->next_tag( 'img' ) ) {
        return $block_content;
    }

    $processor->set_attribute( 'src', esc_url( $image[0] ) );
    $processor->set_attribute( 'alt', 'Beneath Our Feet illustrated geology panel' );
    $processor->set_attribute( 'width', (string) (int) $image[1] );
    $processor->set_attribute( 'height', (string) (int) $image[2] );
    $processor->set_attribute( 'loading', 'lazy' );
    $processor->set_attribute( 'fetchpriority', 'low' );
    $processor->set_attribute( 'decoding', 'async' );
    $processor->remove_attribute( 'srcset' );
    $processor->remove_attribute( 'sizes' );

    return $processor->get_updated_html();
}
add_filter( 'render_block', 'bof_restore_home_panel_123_feature', 50, 2 );
