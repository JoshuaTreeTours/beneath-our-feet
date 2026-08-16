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
    .bof-page-content > .bof-mobile-hero {
        display: block !important;
        width: 100vw !important;
        max-width: none !important;
        margin: 0 calc(50% - 50vw) !important;
        padding: 0 !important;
        background: #172018;
        border-bottom: 1px solid #8d754f;
        overflow: hidden;
    }

    .bof-page-content > .bof-mobile-hero img {
        display: block !important;
        width: 100% !important;
        max-width: none !important;
        height: auto !important;
        aspect-ratio: 720 / 332;
        object-fit: cover;
        margin: 0 !important;
        padding: 0 !important;
    }

    /* The first Gutenberg Cover on the homepage is the large desktop hero.
       Hide it unconditionally on phones; the dedicated landscape hero above
       replaces it. This does not depend on an editor-added custom class. */
    .bof-page-content > .wp-block-cover:first-of-type,
    .bof-page-content > .bof-home-hero {
        display: none !important;
    }
}
</style>

<main id="primary" class="site-main bof-page-content">
    <div class="bof-mobile-hero" aria-label="Beneath Our Feet mobile hero">
        <img
            src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/hero-landscape.svg?v=2' ); ?>"
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
