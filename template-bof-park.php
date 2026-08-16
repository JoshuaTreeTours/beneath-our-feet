<?php
/**
 * Template Name: BOF National Park Collection
 * Template Post Type: page
 */
get_header();

$park_slug = get_post_meta( get_the_ID(), '_bof_park_slug', true );
$park_region = get_post_meta( get_the_ID(), '_bof_park_region', true );
$parks = function_exists( 'bof_np_park_config' ) ? bof_np_park_config() : array();
$children = get_pages(
    array(
        'parent' => get_the_ID(),
        'sort_column' => 'menu_order,post_title',
        'sort_order' => 'ASC',
    )
);
?>

<div class="bof-park-page">
    <section class="bof-park-intro">
        <p class="bof-park-kicker"><?php echo esc_html( $park_region ? $park_region : 'National Park Geology' ); ?></p>
        <h1><?php the_title(); ?></h1>
        <div class="bof-park-description"><?php the_content(); ?></div>
        <p class="bof-park-count"><?php echo esc_html( sprintf( _n( '%d illustrated geology panel', '%d illustrated geology panels', count( $children ), 'beneath-our-feet' ), count( $children ) ) ); ?></p>
    </section>

    <nav class="bof-park-strip" aria-label="National Parks">
        <a class="bof-park-strip-home" href="<?php echo esc_url( home_url( '/national-parks/' ) ); ?>">All Parks</a>
        <?php foreach ( $parks as $slug => $park ) : ?>
            <?php $park_page = get_page_by_path( 'national-parks/' . $slug, OBJECT, 'page' ); ?>
            <?php if ( ! $park_page ) continue; ?>
            <a class="<?php echo $slug === $park_slug ? 'is-current' : ''; ?>" href="<?php echo esc_url( get_permalink( $park_page ) ); ?>"><?php echo esc_html( preg_replace( '/ National Park$/', '', $park['name'] ) ); ?></a>
        <?php endforeach; ?>
    </nav>

    <section class="bof-park-panels" aria-label="<?php echo esc_attr( get_the_title() . ' geology panels' ); ?>">
        <?php foreach ( $children as $panel ) : ?>
            <?php
            $media_id = (int) get_post_meta( $panel->ID, '_bof_panel_media_id', true );
            $image = $media_id ? wp_get_attachment_image_url( $media_id, 'large' ) : '';
            ?>
            <a class="bof-park-panel-card" href="<?php echo esc_url( get_permalink( $panel ) ); ?>">
                <?php if ( $image ) : ?>
                    <img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( get_the_title( $panel ) ); ?>" loading="lazy">
                <?php endif; ?>
                <span><?php echo esc_html( get_the_title( $panel ) ); ?></span>
            </a>
        <?php endforeach; ?>
    </section>
</div>

<?php get_footer(); ?>
