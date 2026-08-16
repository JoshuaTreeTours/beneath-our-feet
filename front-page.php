<?php
/**
 * Static front page template.
 *
 * The homepage remains authored in Gutenberg. The shared header renders the
 * dedicated mobile hero before the page content, so this template only needs
 * to output the Gutenberg content itself.
 */
get_header();
?>

<div id="primary" class="bof-page-content">
<?php
while ( have_posts() ) :
    the_post();
    the_content();
endwhile;
?>
</div>

<?php get_footer(); ?>
