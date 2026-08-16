<?php
/** Individual National Park collection page. */
get_header();

$page_id    = get_queried_object_id();
$park_title = get_the_title( $page_id );
$park_slug  = get_post_meta( $page_id, '_bof_np_park_slug', true );
$panels     = get_posts(
    array(
        'post_type'      => 'page',
        'post_status'    => 'publish',
        'post_parent'    => $page_id,
        'posts_per_page' => -1,
        'meta_key'       => '_bof_np_panel_order',
        'orderby'        => 'meta_value_num',
        'order'          => 'ASC',
    )
);
?>
<section class="bof-park-index" aria-labelledby="bof-park-title">
    <header class="bof-park-index-hero">
        <p class="bof-park-kicker">Beneath Our Feet • National Park Series</p>
        <h1 id="bof-park-title"><?php echo esc_html( $park_title ); ?></h1>
        <p>Enter the landscape, then look beneath it. Each panel follows the rocks, structures, water, climate, and deep-time events that made this protected wilderness what it is today.</p>
        <a href="<?php echo esc_url( home_url( '/national-parks/' ) ); ?>">← Back to all National Parks</a>
    </header>

    <div class="bof-park-panel-grid">
        <?php foreach ( $panels as $panel_page ) : ?>
            <?php
            $attachment_id = (int) get_post_meta( $panel_page->ID, '_bof_np_attachment_id', true );
            $thumb = $attachment_id ? wp_get_attachment_image_url( $attachment_id, 'large' ) : '';
            ?>
            <a class="bof-park-panel-card" href="<?php echo esc_url( get_permalink( $panel_page->ID ) ); ?>">
                <?php if ( $thumb ) : ?>
                    <span class="bof-park-panel-thumb"><img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( get_the_title( $panel_page->ID ) ); ?>" loading="lazy"></span>
                <?php endif; ?>
                <span class="bof-park-panel-card-copy">
                    <strong><?php echo esc_html( get_the_title( $panel_page->ID ) ); ?></strong>
                    <span>Open geology panel →</span>
                </span>
            </a>
        <?php endforeach; ?>
    </div>

    <nav class="bof-park-strip bof-park-strip-index" aria-label="Other National Parks">
        <?php foreach ( bof_np_park_pages() as $park_page ) : ?>
            <a<?php echo (int) $park_page->ID === (int) $page_id ? ' class="is-current"' : ''; ?> href="<?php echo esc_url( get_permalink( $park_page->ID ) ); ?>"><?php echo esc_html( get_the_title( $park_page->ID ) ); ?></a>
        <?php endforeach; ?>
    </nav>
</section>
<?php get_footer(); ?>
