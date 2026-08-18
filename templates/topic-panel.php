<?php
/** Full-size viewer for non-National-Park topic collections. */
get_header();

/**
 * Build a corrected derivative of the Paleoproterozoic panel so the prominent
 * date line reads "2.5 – 1.0 BILLION YEARS AGO" inside the artwork itself.
 * The original Media Library attachment is left untouched.
 */
function bof_paleoproterozoic_corrected_url( $attachment_id ) {
    $source_file = get_attached_file( (int) $attachment_id );
    if ( ! $source_file || ! is_readable( $source_file ) ) {
        return '';
    }

    $uploads = wp_upload_dir();
    if ( ! empty( $uploads['error'] ) || empty( $uploads['basedir'] ) || empty( $uploads['baseurl'] ) ) {
        return '';
    }

    $corrected_file = trailingslashit( dirname( $source_file ) ) . 'source-092-2-5-to-1-0.webp';
    $needs_build    = ! is_readable( $corrected_file ) || filemtime( $corrected_file ) < filemtime( $source_file );

    if ( $needs_build ) {
        $written = false;

        if ( class_exists( 'Imagick' ) ) {
            try {
                $image  = new Imagick( $source_file );
                $width  = $image->getImageWidth();
                $height = $image->getImageHeight();

                $patch = new ImagickDraw();
                $patch->setFillColor( new ImagickPixel( '#f8ecd8' ) );
                $patch->setStrokeColor( new ImagickPixel( '#f8ecd8' ) );
                $patch->rectangle(
                    (int) round( $width * 0.015 ),
                    (int) round( $height * 0.135 ),
                    (int) round( $width * 0.310 ),
                    (int) round( $height * 0.162 )
                );
                $image->drawImage( $patch );

                $text = new ImagickDraw();
                $text->setFillColor( new ImagickPixel( '#bd5823' ) );
                $fonts = Imagick::queryFonts( 'DejaVu*Condensed*Bold*' );
                if ( ! empty( $fonts ) ) {
                    $text->setFont( $fonts[0] );
                }
                $text->setFontSize( max( 16, $width * 0.0225 ) );
                $text->setFontWeight( 700 );
                $image->annotateImage(
                    $text,
                    (int) round( $width * 0.022 ),
                    (int) round( $height * 0.158 ),
                    0,
                    '2.5 – 1.0 BILLION YEARS AGO'
                );

                $image->setImageFormat( 'webp' );
                $image->setImageCompressionQuality( 92 );
                $written = $image->writeImage( $corrected_file );
                $image->clear();
                $image->destroy();
            } catch ( Exception $e ) {
                $written = false;
            }
        }

        if ( ! $written && function_exists( 'imagecreatefromwebp' ) && function_exists( 'imagettftext' ) && function_exists( 'imagewebp' ) ) {
            $image = @imagecreatefromwebp( $source_file );
            if ( $image ) {
                $width  = imagesx( $image );
                $height = imagesy( $image );
                $bg     = imagecolorallocate( $image, 248, 236, 216 );
                $orange = imagecolorallocate( $image, 189, 88, 35 );

                imagefilledrectangle(
                    $image,
                    (int) round( $width * 0.015 ),
                    (int) round( $height * 0.135 ),
                    (int) round( $width * 0.310 ),
                    (int) round( $height * 0.162 ),
                    $bg
                );

                $font = '/usr/share/fonts/truetype/dejavu/DejaVuSansCondensed-Bold.ttf';
                if ( is_readable( $font ) ) {
                    imagettftext(
                        $image,
                        max( 16, $width * 0.0225 ),
                        0,
                        (int) round( $width * 0.022 ),
                        (int) round( $height * 0.158 ),
                        $orange,
                        $font,
                        '2.5 – 1.0 BILLION YEARS AGO'
                    );
                    $written = @imagewebp( $image, $corrected_file, 92 );
                }
                imagedestroy( $image );
            }
        }

        if ( ! $written ) {
            return '';
        }
    }

    $basedir = wp_normalize_path( $uploads['basedir'] );
    $path    = wp_normalize_path( $corrected_file );
    if ( 0 !== strpos( $path, $basedir ) ) {
        return '';
    }

    $relative = ltrim( substr( $path, strlen( $basedir ) ), '/' );
    return trailingslashit( $uploads['baseurl'] ) . str_replace( '%2F', '/', rawurlencode( $relative ) );
}

$page_id       = get_queried_object_id();
$attachment_id = (int) get_post_meta( $page_id, '_bof_topic_attachment_id', true );
$topic_title   = get_post_meta( $page_id, '_bof_topic_title', true );
$panel_title   = get_post_meta( $page_id, '_bof_topic_panel_title', true );
$image_url     = $attachment_id ? wp_get_attachment_image_url( $attachment_id, 'full' ) : '';

$post_slug = get_post_field( 'post_name', $page_id );

/* Use the same brighter derivative already created for the homepage card on
 * the Burgess Shale panel page itself. No other collection image is changed. */
if ( 'burgess-shale' === $post_slug && function_exists( 'bof_burgess_shale_bright_url' ) ) {
    $bright_url = bof_burgess_shale_bright_url( $attachment_id );
    if ( $bright_url ) {
        $image_url = $bright_url;
    }
}

/* Serve the corrected date artwork on the Paleoproterozoic panel page and on
 * its full-size image link. */
if ( 'the-paleoproterozoic-era' === $post_slug ) {
    $corrected_url = bof_paleoproterozoic_corrected_url( $attachment_id );
    if ( $corrected_url ) {
        $image_url = $corrected_url;
    }
}

list( $prev_id, $next_id ) = bof_topic_panel_neighbors( $page_id );
$parent_id = wp_get_post_parent_id( $page_id );
?>
<section class="bof-panel-viewer bof-topic-viewer" aria-labelledby="bof-panel-title">
    <div class="bof-panel-topbar">
        <a class="bof-panel-back" href="<?php echo esc_url( home_url( '/' ) ); ?>">← Home</a>
        <div class="bof-panel-heading">
            <span><?php echo esc_html( $topic_title ); ?></span>
            <h1 id="bof-panel-title"><?php echo esc_html( $panel_title ); ?></h1>
        </div>
        <a class="bof-panel-all" href="<?php echo esc_url( home_url( '/national-parks/' ) ); ?>">National Parks</a>
    </div>

    <div class="bof-panel-stage">
        <?php if ( $prev_id ) : ?>
            <a class="bof-panel-arrow bof-panel-prev" href="<?php echo esc_url( get_permalink( $prev_id ) ); ?>" aria-label="Previous panel">‹</a>
        <?php endif; ?>

        <?php if ( $image_url ) : ?>
            <a class="bof-panel-image-link" href="<?php echo esc_url( $image_url ); ?>" aria-label="Open full-size panel image">
                <img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $panel_title ); ?>" decoding="async">
            </a>
        <?php endif; ?>

        <?php if ( $next_id ) : ?>
            <a class="bof-panel-arrow bof-panel-next" href="<?php echo esc_url( get_permalink( $next_id ) ); ?>" aria-label="Next panel">›</a>
        <?php endif; ?>
    </div>

    <nav class="bof-park-strip" aria-label="Beneath Our Feet collections">
        <?php foreach ( bof_topic_pages() as $topic_page ) : ?>
            <a<?php echo (int) $topic_page->ID === (int) $parent_id ? ' class="is-current"' : ''; ?> href="<?php echo esc_url( get_permalink( $topic_page->ID ) ); ?>"><?php echo esc_html( get_the_title( $topic_page->ID ) ); ?></a>
        <?php endforeach; ?>
    </nav>

    <div class="bof-panel-mobile-nav">
        <?php if ( $prev_id ) : ?><a href="<?php echo esc_url( get_permalink( $prev_id ) ); ?>">← Previous</a><?php endif; ?>
        <?php if ( $next_id ) : ?><a href="<?php echo esc_url( get_permalink( $next_id ) ); ?>">Next →</a><?php endif; ?>
    </div>
</section>
<?php get_footer(); ?>
