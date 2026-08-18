<?php
/** Full-size viewer for non-National-Park topic collections. */
get_header();

$page_id       = get_queried_object_id();
$attachment_id = (int) get_post_meta( $page_id, '_bof_topic_attachment_id', true );
$topic_title   = get_post_meta( $page_id, '_bof_topic_title', true );
$panel_title   = get_post_meta( $page_id, '_bof_topic_panel_title', true );
$image_url     = $attachment_id ? wp_get_attachment_image_url( $attachment_id, 'full' ) : '';

/* Use the same brighter derivative already created for the homepage card on
 * the Burgess Shale panel page itself. No other collection image is changed. */
if ( 'burgess-shale' === get_post_field( 'post_name', $page_id ) && function_exists( 'bof_burgess_shale_bright_url' ) ) {
    $bright_url = bof_burgess_shale_bright_url( $attachment_id );
    if ( $bright_url ) {
        $image_url = $bright_url;
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
