<?php
/**
 * Static front page template.
 *
 * The full homepage — including the hero — is authored in Gutenberg so the
 * visual editor matches the live page as closely as possible. Theme code is
 * responsible only for the site shell and presentation.
 */
get_header();
?>

<main id="primary" class="site-main bof-page-content">
<?php
while ( have_posts() ) :
    the_post();
    the_content();
endwhile;
?>
</main>

<?php get_footer(); ?>
