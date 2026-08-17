<?php
/**
 * Individual National Park collection route.
 *
 * Collection thumbnail pages are intentionally skipped. Visiting a park goes
 * straight to its first full-size geology panel; the viewer then handles
 * previous/next navigation and switching between parks.
 */

$page_id = get_queried_object_id();
$panels  = get_posts(
    array(
        'post_type'      => 'page',
        'post_status'    => 'publish',
        'post_parent'    => $page_id,
        'posts_per_page' => 1,
        'meta_key'       => '_bof_np_panel_order',
        'orderby'        => 'meta_value_num',
        'order'          => 'ASC',
    )
);

if ( $panels ) {
    wp_safe_redirect( get_permalink( $panels[0]->ID ) );
    exit;
}

/* Fallback only if a park somehow has no panel attached. */
get_header();
?>
<section class="bof-park-index">
    <header class="bof-park-index-hero">
        <p class="bof-park-kicker">Beneath Our Feet • National Park Series</p>
        <h1><?php echo esc_html( get_the_title( $page_id ) ); ?></h1>
        <p>No geology panel has been attached to this park yet.</p>
        <a href="<?php echo esc_url( home_url( '/national-parks/' ) ); ?>">← Back to all National Parks</a>
    </header>
</section>
<?php get_footer(); ?>
