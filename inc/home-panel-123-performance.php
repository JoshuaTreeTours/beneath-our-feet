<?php
/**
 * Homepage-only performance correction for attachment 214 (Panel 123).
 *
 * PageSpeed showed this below-the-fold Gutenberg image loading the 768x1152
 * derivative during the initial mobile request. Keep the block and artwork
 * unchanged, but deliver the existing 683x1024 derivative and make the image
 * explicitly lazy/low priority so it does not compete with the mobile hero.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function bof_optimize_home_panel_123( $block_content, $block ) {
    if ( ! is_front_page() || empty( $block['blockName'] ) || 'core/image' !== $block['blockName'] ) {
        return $block_content;
    }

    $attachment_id = isset( $block['attrs']['id'] ) ? (int) $block['attrs']['id'] : 0;
    if ( 214 !== $attachment_id ) {
        return $block_content;
    }

    $large = wp_get_attachment_image_src( $attachment_id, 'large' );
    if ( ! $large || empty( $large[0] ) ) {
        return $block_content;
    }

    if ( class_exists( 'WP_HTML_Tag_Processor' ) ) {
        $processor = new WP_HTML_Tag_Processor( $block_content );
        if ( $processor->next_tag( 'img' ) ) {
            $processor->set_attribute( 'src', esc_url( $large[0] ) );
            $processor->set_attribute( 'width', (string) (int) $large[1] );
            $processor->set_attribute( 'height', (string) (int) $large[2] );
            $processor->set_attribute( 'loading', 'lazy' );
            $processor->set_attribute( 'fetchpriority', 'low' );
            $processor->set_attribute( 'decoding', 'async' );
            $processor->remove_attribute( 'srcset' );
            $processor->remove_attribute( 'sizes' );
            return $processor->get_updated_html();
        }
    }

    return $block_content;
}
add_filter( 'render_block', 'bof_optimize_home_panel_123', 45, 2 );
