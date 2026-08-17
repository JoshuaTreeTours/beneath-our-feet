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

    $html  = '<section class="bof-material-card-section" aria-label="Explore geology topics">';
    $html .= '<div class="bof-material-card-heading">';
    $html .= '<p class="bof-material-card-eyebrow">Explore the Science</p>';
    $html .= '<h2 class="bof-material-card-title-heading">How Earth Works</h2>';
    $html .= '</div>';
    $html .= '<div class="bof-material-card-grid">';

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

function bof_material_card_styles() {
    if ( ! is_front_page() ) {
        return;
    }

    $css = '
    .bof-material-card-section{width:min(1180px,90vw);margin:clamp(4rem,8vw,7rem) auto clamp(5rem,9vw,8rem)}
    .bof-material-card-heading{margin:0 0 1.8rem}
    .bof-material-card-eyebrow{margin:0 0 .55rem;color:var(--bof-gold);font-size:.8rem;font-weight:700;letter-spacing:.16em;text-transform:uppercase}
    .bof-material-card-title-heading{margin:0;color:var(--bof-ink);font-size:clamp(2.4rem,5vw,4.5rem);line-height:.98;font-weight:500}
    .bof-material-card-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px}
    .bof-material-card{position:relative;display:flex;align-items:flex-end;min-height:300px;overflow:hidden;border:1px solid var(--bof-border);background:#233126 center center/cover no-repeat;color:#fff8e9;text-decoration:none;isolation:isolate;transition:transform .18s ease,box-shadow .18s ease}
    .bof-material-card:hover,.bof-material-card:focus-visible{transform:translateY(-3px);box-shadow:0 12px 28px rgba(0,0,0,.18);outline:2px solid #d7b56e;outline-offset:3px}
    .bof-material-card-shade{position:absolute;inset:0;z-index:-1;background:linear-gradient(180deg,rgba(11,16,12,.04) 30%,rgba(11,16,12,.82) 100%)}
    .bof-material-card-title{display:block;width:100%;padding:1.25rem 1.35rem 1.4rem;font-size:clamp(1.35rem,2.2vw,2rem);line-height:1.05;font-weight:600;text-shadow:0 2px 10px rgba(0,0,0,.55)}
    @media(max-width:900px){.bof-material-card-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media(max-width:600px){.bof-material-card-section{width:92vw;margin-top:3rem;margin-bottom:4rem}.bof-material-card-grid{grid-template-columns:1fr;gap:14px}.bof-material-card{min-height:230px}.bof-material-card-heading{margin-bottom:1.35rem}}
    ';

    wp_add_inline_style( 'beneath-our-feet-style', $css );
}
add_action( 'wp_enqueue_scripts', 'bof_material_card_styles', 30 );
