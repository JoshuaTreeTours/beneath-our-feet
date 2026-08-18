<?php
/**
 * Replace the old dark homepage series insert with six visual cards
 * linking to the curated Earth-material/process collections.
 * The Gutenberg database content is left untouched.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Create a brighter homepage-only derivative of the original Burgess Shale
 * panel. This preserves the source artwork and is used only by the home card.
 */
function bof_burgess_shale_bright_url( $attachment_id ) {
    $source_file = get_attached_file( (int) $attachment_id );
    if ( ! $source_file || ! is_readable( $source_file ) ) {
        return '';
    }

    $uploads = wp_upload_dir();
    if ( ! empty( $uploads['error'] ) || empty( $uploads['basedir'] ) || empty( $uploads['baseurl'] ) ) {
        return '';
    }

    $bright_file = trailingslashit( dirname( $source_file ) ) . 'source-034-home-bright.webp';
    $needs_build = ! is_readable( $bright_file ) || filemtime( $bright_file ) < filemtime( $source_file );

    if ( $needs_build ) {
        $written = false;

        if ( class_exists( 'Imagick' ) ) {
            try {
                $image = new Imagick( $source_file );
                $image->setImageColorspace( Imagick::COLORSPACE_SRGB );
                $image->modulateImage( 122, 100, 100 );
                $image->setImageFormat( 'webp' );
                $image->setImageCompressionQuality( 90 );
                $written = $image->writeImage( $bright_file );
                $image->clear();
                $image->destroy();
            } catch ( Exception $e ) {
                $written = false;
            }
        }

        if ( ! $written && function_exists( 'imagecreatefromwebp' ) && function_exists( 'imagewebp' ) ) {
            $image = @imagecreatefromwebp( $source_file );
            if ( $image ) {
                @imagefilter( $image, IMG_FILTER_BRIGHTNESS, 28 );
                @imagefilter( $image, IMG_FILTER_CONTRAST, -4 );
                $written = @imagewebp( $image, $bright_file, 90 );
                imagedestroy( $image );
            }
        }

        if ( ! $written ) {
            return '';
        }
    }

    return bof_upload_file_url( $bright_file, $uploads );
}

/** Convert an uploads filepath to its public uploads URL. */
function bof_upload_file_url( $file, $uploads = null ) {
    if ( null === $uploads ) {
        $uploads = wp_upload_dir();
    }
    if ( ! empty( $uploads['error'] ) || empty( $uploads['basedir'] ) || empty( $uploads['baseurl'] ) ) {
        return '';
    }

    $basedir = wp_normalize_path( $uploads['basedir'] );
    $path    = wp_normalize_path( $file );
    if ( 0 !== strpos( $path, $basedir ) ) {
        return '';
    }

    $relative = ltrim( substr( $path, strlen( $basedir ) ), '/' );
    return trailingslashit( $uploads['baseurl'] ) . str_replace( '%2F', '/', rawurlencode( $relative ) );
}

/**
 * Make a small homepage-only WebP copy of a source file.
 * No Media Library originals or collection-page images are modified.
 */
function bof_home_card_resized_url( $source_file, $name ) {
    if ( ! $source_file || ! is_readable( $source_file ) ) {
        return '';
    }

    $uploads = wp_upload_dir();
    if ( ! empty( $uploads['error'] ) ) {
        return '';
    }

    $target = trailingslashit( dirname( $source_file ) ) . sanitize_file_name( $name ) . '-card-640.webp';
    $needs_build = ! is_readable( $target ) || filemtime( $target ) < filemtime( $source_file );

    if ( $needs_build ) {
        $editor = wp_get_image_editor( $source_file );
        if ( is_wp_error( $editor ) ) {
            return '';
        }

        $size = $editor->get_size();
        if ( ! empty( $size['width'] ) && (int) $size['width'] > 640 ) {
            $result = $editor->resize( 640, null, false );
            if ( is_wp_error( $result ) ) {
                return '';
            }
        }

        $editor->set_quality( 76 );
        $saved = $editor->save( $target, 'image/webp' );
        if ( is_wp_error( $saved ) || ! is_readable( $target ) ) {
            return '';
        }
    }

    return bof_upload_file_url( $target, $uploads );
}

/**
 * Create a single smaller homepage-only copy of Panel 123.
 * This is intentionally isolated from the six cards so it can be reverted
 * independently in one commit if it does not improve LCP.
 */
function bof_home_intro_panel_url() {
    static $url = null;

    if ( null !== $url ) {
        return $url;
    }

    $url = '';
    $attachments = get_posts(
        array(
            'post_type'      => 'attachment',
            'post_status'    => 'inherit',
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'meta_query'     => array(
                array(
                    'key'     => '_wp_attached_file',
                    'value'   => 'source-123.webp',
                    'compare' => 'LIKE',
                ),
            ),
        )
    );

    if ( empty( $attachments ) ) {
        return $url;
    }

    $source_file = get_attached_file( (int) $attachments[0] );
    if ( ! $source_file || ! is_readable( $source_file ) ) {
        return $url;
    }

    $uploads = wp_upload_dir();
    if ( ! empty( $uploads['error'] ) ) {
        return $url;
    }

    $target = trailingslashit( dirname( $source_file ) ) . 'source-123-home-600.webp';
    $needs_build = ! is_readable( $target ) || filemtime( $target ) < filemtime( $source_file );

    if ( $needs_build ) {
        $editor = wp_get_image_editor( $source_file );
        if ( is_wp_error( $editor ) ) {
            return $url;
        }

        $result = $editor->resize( 600, null, false );
        if ( is_wp_error( $result ) ) {
            return $url;
        }

        $editor->set_quality( 74 );
        $saved = $editor->save( $target, 'image/webp' );
        if ( is_wp_error( $saved ) || ! is_readable( $target ) ) {
            return $url;
        }
    }

    $url = bof_upload_file_url( $target, $uploads );
    return $url;
}

/** Serve the 600px Panel 123 derivative only on the homepage introduction. */
function bof_optimize_home_intro_panel( $block_content, $block ) {
    if ( ! is_front_page() || empty( $block['blockName'] ) || 'core/image' !== $block['blockName'] ) {
        return $block_content;
    }

    if ( false === strpos( $block_content, 'source-123' ) && false === strpos( $block_content, 'wp-image-214' ) ) {
        return $block_content;
    }

    $image_url = bof_home_intro_panel_url();
    if ( ! $image_url || ! class_exists( 'WP_HTML_Tag_Processor' ) ) {
        return $block_content;
    }

    $processor = new WP_HTML_Tag_Processor( $block_content );
    if ( ! $processor->next_tag( 'img' ) ) {
        return $block_content;
    }

    $processor->set_attribute( 'src', $image_url );
    $processor->set_attribute( 'width', '600' );
    $processor->set_attribute( 'height', '900' );
    $processor->set_attribute( 'loading', 'lazy' );
    $processor->set_attribute( 'decoding', 'async' );
    $processor->remove_attribute( 'srcset' );
    $processor->remove_attribute( 'sizes' );

    return $processor->get_updated_html();
}
add_filter( 'render_block', 'bof_optimize_home_intro_panel', 35, 2 );

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
        $attachment_id = (int) $attachments[0];
        $source_file   = get_attached_file( $attachment_id );

        if ( 'source-034.webp' === $filename ) {
            $bright_url  = bof_burgess_shale_bright_url( $attachment_id );
            $bright_file = trailingslashit( dirname( $source_file ) ) . 'source-034-home-bright.webp';
            if ( $bright_url && is_readable( $bright_file ) ) {
                $url = bof_home_card_resized_url( $bright_file, 'source-034-home-bright' );
                if ( $url ) {
                    $cache[ $filename ] = $url;
                    return $url;
                }
            }
        } elseif ( $source_file ) {
            $url = bof_home_card_resized_url( $source_file, pathinfo( $filename, PATHINFO_FILENAME ) );
            if ( $url ) {
                $cache[ $filename ] = $url;
                return $url;
            }
        }

        $url = wp_get_attachment_image_url( $attachment_id, 'large' );
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

    $lens_icon = '<svg viewBox="0 0 64 64" aria-hidden="true" focusable="false"><circle cx="27" cy="27" r="15" fill="none" stroke="currentColor" stroke-width="3"/><path d="M38 38 53 53" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"/><path d="M18 27h18M21 22h12M21 32h12" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>';

    $html  = '<section class="bof-material-card-section" aria-label="Explore geology topics">';
    $html .= '<div class="bof-material-card-heading">';
    $html .= '<p class="bof-material-card-eyebrow">Explore the Science</p>';
    $html .= '<div class="bof-material-card-title-row"><h2 class="bof-material-card-title-heading">How Earth Works</h2><span class="bof-material-card-heading-icon" aria-hidden="true">' . $lens_icon . '</span></div>';
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
    .bof-material-card-title-row{display:flex;align-items:center;justify-content:space-between;gap:1rem}
    .bof-material-card-title-heading{margin:0;color:var(--bof-ink);font-size:clamp(2.4rem,5vw,4.5rem);line-height:.98;font-weight:500}
    .bof-material-card-heading-icon{flex:0 0 auto;width:3.4rem;height:3.4rem;color:#a8782f;opacity:.38;margin-right:.25rem}
    .bof-material-card-heading-icon svg{display:block;width:100%;height:100%}
    .bof-material-card-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px}
    .bof-material-card{position:relative;display:flex;align-items:flex-end;min-height:300px;overflow:hidden;border:1px solid var(--bof-border);background:#233126 center center/cover no-repeat;color:#fff8e9;text-decoration:none;isolation:isolate;transition:transform .18s ease,box-shadow .18s ease}
    .bof-material-card:hover,.bof-material-card:focus-visible{transform:translateY(-3px);box-shadow:0 12px 28px rgba(0,0,0,.18);outline:2px solid #d7b56e;outline-offset:3px}
    .bof-material-card-shade{position:absolute;inset:0;z-index:-1;background:linear-gradient(180deg,rgba(11,16,12,.04) 30%,rgba(11,16,12,.82) 100%)}
    .bof-material-card-title{display:block;width:100%;padding:1.25rem 1.35rem 1.4rem;font-size:clamp(1.35rem,2.2vw,2rem);line-height:1.05;font-weight:600;text-shadow:0 2px 10px rgba(0,0,0,.55)}
    @media(max-width:900px){.bof-material-card-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media(max-width:600px){.bof-material-card-section{width:92vw;margin-top:3rem;margin-bottom:4rem}.bof-material-card-grid{grid-template-columns:1fr;gap:14px}.bof-material-card{min-height:230px}.bof-material-card-heading{margin-bottom:1.35rem}.bof-material-card-title-row{align-items:flex-start}.bof-material-card-heading-icon{width:3rem;height:3rem;opacity:.34;margin-top:.15rem}}
    ';

    wp_add_inline_style( 'beneath-our-feet-style', $css );
}
add_action( 'wp_enqueue_scripts', 'bof_material_card_styles', 30 );
