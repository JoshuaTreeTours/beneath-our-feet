<?php
/**
 * National Parks landing page.
 */
get_header();
?>

<div id="primary" class="bof-national-parks-page">
<?php
while ( have_posts() ) :
    the_post();
    the_content();
endwhile;
?>
</div>

<?php get_footer(); ?>
