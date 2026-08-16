<?php
get_header();
?>

<?php if ( have_posts() ) : ?>
    <?php while ( have_posts() ) : the_post(); ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class( 'entry' ); ?>>
            <header class="entry-header">
                <?php if ( is_singular() ) : ?>
                    <h1 class="entry-title"><?php the_title(); ?></h1>
                <?php else : ?>
                    <h2 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                <?php endif; ?>
            </header>
            <div class="entry-content">
                <?php
                if ( is_singular() ) {
                    the_content();
                } else {
                    the_excerpt();
                }
                ?>
            </div>
        </article>
    <?php endwhile; ?>

    <?php the_posts_navigation(); ?>
<?php else : ?>
    <article class="entry">
        <h1 class="entry-title"><?php esc_html_e( 'Beneath Our Feet', 'beneath-our-feet' ); ?></h1>
        <p><?php esc_html_e( 'Stories written in stone, time, and the landscapes we stand on.', 'beneath-our-feet' ); ?></p>
    </article>
<?php endif; ?>

<?php
get_footer();
