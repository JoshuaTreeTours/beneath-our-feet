<?php
/** National Park panel viewer. */
get_header();

$page_id       = get_queried_object_id();
$attachment_id = (int) get_post_meta( $page_id, '_bof_np_attachment_id', true );
$park_title    = get_post_meta( $page_id, '_bof_np_park_title', true );
$panel_title   = get_post_meta( $page_id, '_bof_np_panel_title', true );
$image_url     = $attachment_id ? wp_get_attachment_image_url( $attachment_id, 'full' ) : '';
list( $prev_id, $next_id ) = bof_np_panel_neighbors( $page_id );
$parent_id = wp_get_post_parent_id( $page_id );
?>
<section class="bof-panel-viewer" aria-labelledby="bof-panel-title">
    <div class="bof-panel-topbar">
        <a class="bof-panel-back" href="<?php echo esc_url( home_url( '/national-parks/' ) ); ?>">← All parks</a>
        <div class="bof-panel-heading">
            <span><?php echo esc_html( $park_title ); ?></span>
            <h1 id="bof-panel-title"><?php echo esc_html( $panel_title ); ?></h1>
        </div>
    </div>

    <div class="bof-panel-stage">
        <?php if ( $prev_id ) : ?>
            <a class="bof-panel-arrow bof-panel-prev" href="<?php echo esc_url( get_permalink( $prev_id ) ); ?>" aria-label="Previous National Park panel">‹</a>
        <?php endif; ?>

        <?php if ( $image_url ) : ?>
            <a class="bof-panel-image-link" href="<?php echo esc_url( $image_url ); ?>" aria-label="Open original full-size panel image">
                <img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $park_title . ' geology panel — ' . $panel_title ); ?>" decoding="async">
            </a>
        <?php endif; ?>

        <?php if ( $next_id ) : ?>
            <a class="bof-panel-arrow bof-panel-next" href="<?php echo esc_url( get_permalink( $next_id ) ); ?>" aria-label="Next National Park panel">›</a>
        <?php endif; ?>
    </div>

    <nav class="bof-park-strip" aria-label="National Park pages">
        <?php foreach ( bof_np_park_pages() as $park_page ) : ?>
            <?php
            $first_panel = get_posts(
                array(
                    'post_type'      => 'page',
                    'post_status'    => 'publish',
                    'post_parent'    => $park_page->ID,
                    'posts_per_page' => 1,
                    'meta_key'       => '_bof_np_panel_order',
                    'orderby'        => 'meta_value_num',
                    'order'          => 'ASC',
                )
            );
            $park_url = $first_panel ? get_permalink( $first_panel[0]->ID ) : get_permalink( $park_page->ID );
            ?>
            <a<?php echo (int) $park_page->ID === (int) $parent_id ? ' class="is-current"' : ''; ?> href="<?php echo esc_url( $park_url ); ?>"><?php echo esc_html( get_the_title( $park_page->ID ) ); ?></a>
        <?php endforeach; ?>
    </nav>
</section>
<?php get_footer(); ?>
