<?php
/**
 * National Parks landing page.
 */
get_header();
?>

<main id="primary" class="bof-national-parks-page">
<?php
while ( have_posts() ) :
    the_post();
    the_content();
endwhile;
?>
</main>

<?php get_footer(); ?>
