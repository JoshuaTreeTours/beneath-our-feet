<?php
/**
 * Static front page template.
 *
 * The homepage remains authored in Gutenberg. Only the dedicated mobile hero
 * is optimized here: its existing WordPress upload is resized once to a
 * 640x960 WebP and the rendered mobile-hero image is pointed at that cached
 * derivative. Desktop hero markup and every other homepage element are left
 * untouched.
 */
get_header();

/**
 * Replace only the image inside the Gutenberg mobile-hero Cover block with a
 * cached WebP derivative of the existing Media Library upload.
 *
 * If the server cannot create WebP, the original markup is returned unchanged.
 */
function bof_optimize_mobile_hero_markup( $content ) {
    return preg_replace_callback(
        '/(<div[^>]*class=(?:"|\')[^"\']*\bbof-mobile-hero\b[^"\']*(?:"|\')[^>]*>.*?<img\b)([^>]*)(>)/is',
        function ( $matches ) {
            $attrs = $matches[2];

            if ( ! preg_match( '/\bsrc=("|\')(.*?)\1/i', $attrs, $src_match ) ) {
                return $matches[0];
            }

            $source_url = html_entity_decode( $src_match[2], ENT_QUOTES, 'UTF-8' );
            $url_path   = (string) wp_parse_url( $source_url, PHP_URL_PATH );
            $marker     = '/wp-content/uploads/';
            $position   = strpos( $url_path, $marker );

            if ( false === $position ) {
                return $matches[0];
            }

            $relative_path = ltrim( substr( $url_path, $position + strlen( $marker ) ), '/' );
            if ( ! $relative_path ) {
                return $matches[0];
            }

            $uploads     = wp_upload_dir();
            $source_path = trailingslashit( $uploads['basedir'] ) . $relative_path;
            if ( ! is_readable( $source_path ) ) {
                return $matches[0];
            }

            $pathinfo    = pathinfo( $relative_path );
            $target_name = sanitize_file_name( $pathinfo['filename'] . '-mobile-640x960.webp' );
            $target_rel  = ( ! empty( $pathinfo['dirname'] ) && '.' !== $pathinfo['dirname'] ? trailingslashit( $pathinfo['dirname'] ) : '' ) . $target_name;
            $target_path = trailingslashit( $uploads['basedir'] ) . $target_rel;

            if ( ! is_readable( $target_path ) || filemtime( $target_path ) < filemtime( $source_path ) ) {
                $editor = wp_get_image_editor( $source_path );
                if ( is_wp_error( $editor ) ) {
                    return $matches[0];
                }

                $editor->set_quality( 70 );
                $resized = $editor->resize( 640, 960, false );
                if ( is_wp_error( $resized ) ) {
                    return $matches[0];
                }

                $saved = $editor->save( $target_path, 'image/webp' );
                if ( is_wp_error( $saved ) || ! is_readable( $target_path ) ) {
                    return $matches[0];
                }
            }

            $target_url = trailingslashit( $uploads['baseurl'] ) . str_replace( '%2F', '/', rawurlencode( $target_rel ) );
            $target_url = str_replace( '%2F', '/', $target_url );

            $attrs = preg_replace( '/\bsrc=("|\').*?\1/i', 'src="' . esc_url( $target_url ) . '"', $attrs, 1 );
            $attrs = preg_replace( '/\s+srcset=("|\').*?\1/i', '', $attrs );
            $attrs = preg_replace( '/\s+sizes=("|\').*?\1/i', '', $attrs );
            $attrs = preg_replace( '/\s+width=("|\').*?\1/i', '', $attrs );
            $attrs = preg_replace( '/\s+height=("|\').*?\1/i', '', $attrs );

            if ( ! preg_match( '/\bfetchpriority=/i', $attrs ) ) {
                $attrs .= ' fetchpriority="high"';
            }
            $attrs .= ' width="640" height="960"';

            return $matches[1] . $attrs . $matches[3];
        },
        $content,
        1
    );
}
?>

<div id="primary" class="bof-page-content">
<?php
while ( have_posts() ) :
    the_post();

    $bof_home_content = apply_filters( 'the_content', get_the_content() );
    $bof_home_content = bof_optimize_mobile_hero_markup( $bof_home_content );

    echo $bof_home_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- filtered Gutenberg content.
endwhile;
?>
</div>

<?php get_footer(); ?>
