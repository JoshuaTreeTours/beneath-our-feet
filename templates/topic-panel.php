<?php
/** Full-size viewer for non-National-Park topic collections. */
get_header();

/**
 * Ensure the approved Paleoproterozoic replacement panel is copied from the
 * deployed theme into the WordPress uploads directory at the exact public URL
 * requested by the site owner.
 */
function bof_paleoproterozoic_replacement_url() {
    $theme_file = get_stylesheet_directory() . '/assets/source-092-2-5-to-1-0.webp';
    if ( ! is_readable( $theme_file ) || filesize( $theme_file ) < 10000 ) {
        return '';
    }

    $uploads = wp_upload_dir();
    if ( ! empty( $uploads['error'] ) || empty( $uploads['basedir'] ) || empty( $uploads['baseurl'] ) ) {
        return '';
    }

    $target_dir  = trailingslashit( $uploads['basedir'] ) . '2026/08';
    $target_file = trailingslashit( $target_dir ) . 'source-092-2-5-to-1-0.webp';

    if ( ! wp_mkdir_p( $target_dir ) ) {
        return '';
    }

    $needs_copy = ! is_readable( $target_file ) || filesize( $target_file ) !== filesize( $theme_file ) || filemtime( $target_file ) < filemtime( $theme_file );
    if ( $needs_copy && ! @copy( $theme_file, $target_file ) ) {
        return '';
    }

    return trailingslashit( $uploads['baseurl'] ) . '2026/08/source-092-2-5-to-1-0.webp';
}

$page_id       = get_queried_object_id();
$attachment_id = (int) get_post_meta( $page_id, '_bof_topic_attachment_id', true );
$topic_title   = get_post_meta( $page_id, '_bof_topic_title', true );
$panel_title   = get_post_meta( $page_id, '_bof_topic_panel_title', true );
$image_url     = $attachment_id ? wp_get_attachment_image_url( $attachment_id, 'full' ) : '';
$post_slug     = get_post_field( 'post_name', $page_id );

if ( 'burgess-shale' === $post_slug && function_exists( 'bof_burgess_shale_bright_url' ) ) {
    $bright_url = bof_burgess_shale_bright_url( $attachment_id );
    if ( $bright_url ) {
        $image_url = $bright_url;
    }
}

if ( 'the-paleoproterozoic-era' === $post_slug ) {
    $replacement_url = bof_paleoproterozoic_replacement_url();
    if ( $replacement_url ) {
        $image_url = $replacement_url;
    }
}

// Deep Time → Earth's Clock: use the owner's corrected replacement artwork.
if ( 409 === (int) $page_id && 'earths-clock' === $post_slug ) {
    $image_url = 'https://beneath-our-feet.com/wp-content/uploads/2026/08/source-133-1.webp-1.webp';
}

// Deep Time → Geologic Time Scale: use the owner's newly uploaded corrected panel.
if ( 410 === (int) $page_id && 'geologic-time-scale' === $post_slug ) {
    $image_url = 'https://beneath-our-feet.com/wp-content/uploads/2026/08/source-053-1.webp';
}

// Deep Time → Earth's Calendar: use the newly uploaded corrected panel.
if ( 'earths-calendar' === $post_slug ) {
    $image_url = 'https://beneath-our-feet.com/wp-content/uploads/2026/08/file_00000000b15081fd8e2d1de6b89a8baa.png';
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
</section>
<?php get_footer(); ?>
