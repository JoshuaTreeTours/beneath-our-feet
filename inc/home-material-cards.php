<?php
/**
 * Replace the old dark homepage series insert with six visual cards
 * linking to the curated Earth-material/process collections.
 * The Gutenberg database content is left untouched.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function bof_home_card_image_url( $filename ) {
    static $cache = array();

    if ( isset( $cache[ $filename ] ) ) {
        return $cache[ $filename ];
    }

    $attachments = get_posts(
        array(
            'post_type'      => 'attachment',
            'post_status'    => 'inherit',
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'meta_query'     => array(
                array(
                    'key'     => '_wp_attached_file',
                    'value'   => $filename,
                    'compare' => 'LIKE',
                ),
            ),
        )
    );

    if ( ! empty( $attachments ) ) {
        $url = wp_get_attachment_image_url( (int) $attachments[0], 'large' );
        if ( $url ) {
            $cache[ $filename ] = $url;
            return $url;
        }
    }

    $fallback = wp_get_attachment_image_url( 18, 'large' );
    $cache[ $filename ] = $fallback ? $fallback : '';
    return $cache[ $filename ];
}

function bof_replace_home_series_with_material_cards( $block_content, $block ) {
    if ( ! is_front_page() || empty( $block['blockName'] ) || 'core/group' !== $block['blockName'] ) {
        return $block_content;
    }

    $class_name = isset( $block['attrs']['className'] ) ? (string) $block['attrs']['className'] : '';
    if ( false === strpos( $class_name, 'bof-home-series' ) ) {
        return $block_content;
    }

    $cards = array(
        array( 'title' => 'Rocks & Minerals',         'slug' => 'rocks-minerals',         'image' => 'source-159.webp' ),
        array( 'title' => 'Fossils & Life',           'slug' => 'fossils-life',           'image' => 'source-034.webp' ),
        array( 'title' => 'Faults & Earthquakes',     'slug' => 'faults-earthquakes',     'image' => 'source-045.webp' ),
        array( 'title' => 'Volcanoes & Magma',        'slug' => 'volcanoes-magma',        'image' => 'source-098.webp' ),
        array( 'title' => 'Erosion & Landscapes',     'slug' => 'erosion-landscapes',     'image' => 'source-057.webp' ),
        array( 'title' => 'Oceans, Coasts & Climate', 'slug' => 'oceans-coasts-climate', 'image' => 'source-036.webp' ),
    );

    $html = '<section class="bof-material-card-section" aria-label="Explore geology topics"><div class="bof-material-card-grid">';

    foreach ( $cards as $card ) {
        $url       = home_url( '/collections/' . $card['slug'] . '/' );
        $image_url = bof_home_card_image_url( $card['image'] );
        $style     = $image_url ? ' style="background-image:url(' . esc_url( $image_url ) . ')"' : '';

        $html .= '<a class="bof-material-card" href="' . esc_url( $url ) . '"' . $style . '>';
        $html .= '<span class="bof-material-card-shade" aria-hidden="true"></span>';
        $html .= '<span class="bof-material-card-title">' . esc_html( $card['title'] ) . '</span>';
        $html .= '</a>';
    }

    $html .= '</div></section>';
    return $html;
}
add_filter( 'render_block', 'bof_replace_home_series_with_material_cards', 40, 2 );
