<?php
/** Topic collection route: skip thumbnails and open the first panel. */
$page_id = get_queried_object_id();
$panels = get_posts(
    array(
        'post_type'      => 'page',
        'post_status'    => 'publish',
        'post_parent'    => $page_id,
        'posts_per_page' => 1,
        'meta_key'       => '_bof_topic_panel_order',
        'orderby'        => 'meta_value_num',
        'order'          => 'ASC',
    )
);

if ( $panels ) {
    wp_safe_redirect( get_permalink( $panels[0]->ID ) );
    exit;
}

get_header();
?>
<section class="bof-park-index">
    <header class="bof-park-index-hero">
        <p class="bof-park-kicker">Beneath Our Feet • Collection</p>
        <h1><?php echo esc_html( get_the_title( $page_id ) ); ?></h1>
        <p>No panel has been attached to this collection yet.</p>
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>">← Home</a>
    </header>
</section>
<?php get_footer(); ?>
