<?php
/**
 * Static front page template.
 *
 * The homepage remains authored in Gutenberg for desktop. A dedicated,
 * lightweight rectangular hero is rendered by the theme on phones so the
 * large Gutenberg cover does not have to load on small screens.
 */
get_header();
?>

<style id="bof-mobile-hero-styles">
.bof-mobile-hero {
    display: none;
}

@media (max-width: 780px) {
    .bof-mobile-hero {
        display: block;
        width: 100%;
        margin: 0;
        padding: 0;
        background: #172018;
        border-bottom: 1px solid #8d754f;
    }

    .bof-mobile-hero img {
        display: block;
        width: 100%;
        height: auto;
        margin: 0;
    }

    /* Hide the desktop Gutenberg cover when the dedicated phone hero is used. */
    .bof-page-content > .bof-home-hero,
    .bof-page-content > .bof-mobile-hero + .wp-block-cover {
        display: none !important;
    }
}
</style>

<main id="primary" class="site-main bof-page-content">
    <div class="bof-mobile-hero" aria-label="Beneath Our Feet mobile hero">
        <img
            src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/hero-landscape.svg' ); ?>"
            alt="Beneath Our Feet — geology, deep time, and Earth's story"
            width="720"
            height="332"
            decoding="async"
            fetchpriority="high"
        >
    </div>

<?php
while ( have_posts() ) :
    the_post();
    the_content();
endwhile;
?>
</main>

<?php get_footer(); ?>
