<?php
/**
 * Template Name: BOF National Park Panel
 * Template Post Type: page
 */
get_header();

$page_id = get_the_ID();
$media_id = (int) get_post_meta( $page_id, '_bof_panel_media_id', true );
$prev_id = (int) get_post_meta( $page_id, '_bof_prev_page_id', true );
$next_id = (int) get_post_meta( $page_id, '_bof_next_page_id', true );
$position = (int) get_post_meta( $page_id, '_bof_panel_global_position', true );
$total = (int) get_post_meta( $page_id, '_bof_panel_global_total', true );
$park_slug = get_post_meta( $page_id, '_bof_panel_park_slug', true );
$park_page = get_post( wp_get_post_parent_id( $page_id ) );
$parks = function_exists( 'bof_np_park_config' ) ? bof_np_park_config() : array();
$image = $media_id ? wp_get_attachment_image_url( $media_id, 'full' ) : '';
?>

<div class="bof-panel-viewer">
    <nav class="bof-panel-topbar" aria-label="Panel navigation">
        <a class="bof-panel-back" href="<?php echo esc_url( $park_page ? get_permalink( $park_page ) : home_url( '/national-parks/' ) ); ?>">← <?php echo esc_html( $park_page ? get_the_title( $park_page ) : 'National Parks' ); ?></a>
        <span class="bof-panel-counter"><?php echo esc_html( $position && $total ? $position . ' / ' . $total : '' ); ?></span>
    </nav>

    <nav class="bof-park-strip bof-panel-park-strip" aria-label="Other National Parks">
        <a class="bof-park-strip-home" href="<?php echo esc_url( home_url( '/national-parks/' ) ); ?>">All Parks</a>
        <?php foreach ( $parks as $slug => $park ) : ?>
            <?php $candidate = get_page_by_path( 'national-parks/' . $slug, OBJECT, 'page' ); ?>
            <?php if ( ! $candidate ) continue; ?>
            <a class="<?php echo $slug === $park_slug ? 'is-current' : ''; ?>" href="<?php echo esc_url( get_permalink( $candidate ) ); ?>"><?php echo esc_html( preg_replace( '/ National Park$/', '', $park['name'] ) ); ?></a>
        <?php endforeach; ?>
    </nav>

    <section class="bof-panel-stage">
        <?php if ( $prev_id ) : ?>
            <a class="bof-panel-arrow bof-panel-arrow-prev" href="<?php echo esc_url( get_permalink( $prev_id ) ); ?>" aria-label="Previous National Park panel"><span aria-hidden="true">‹</span></a>
        <?php endif; ?>

        <div class="bof-panel-artwork">
            <h1><?php the_title(); ?></h1>
            <?php if ( $image ) : ?>
                <a class="bof-panel-image-link" href="<?php echo esc_url( $image ); ?>" target="_blank" rel="noopener" aria-label="Open full-size panel image">
                    <img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>">
                </a>
            <?php else : ?>
                <p>Panel image is unavailable.</p>
            <?php endif; ?>
            <div class="bof-panel-caption"><?php the_content(); ?></div>
        </div>

        <?php if ( $next_id ) : ?>
            <a class="bof-panel-arrow bof-panel-arrow-next" href="<?php echo esc_url( get_permalink( $next_id ) ); ?>" aria-label="Next National Park panel"><span aria-hidden="true">›</span></a>
        <?php endif; ?>
    </section>
</div>

<?php get_footer(); ?>
