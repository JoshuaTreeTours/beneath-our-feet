<?php
/**
 * Static front page template.
 *
 * The homepage remains authored in Gutenberg. The dedicated mobile hero stays
 * inside its Gutenberg Cover block; this template only repairs the old blocked
 * PHP image URL at render time by substituting the approved JPEG data already
 * stored with the theme.
 */
get_header();

$bof_mobile_hero_b64 = '';
for ( $bof_mobile_hero_part = 1; $bof_mobile_hero_part <= 10; $bof_mobile_hero_part++ ) {
    $bof_mobile_hero_path = get_stylesheet_directory() . '/assets/mobile-hero.b64.' . $bof_mobile_hero_part;
    if ( is_readable( $bof_mobile_hero_path ) ) {
        $bof_mobile_hero_b64 .= trim( file_get_contents( $bof_mobile_hero_path ) );
    }
}
?>

<div id="primary" class="bof-page-content">
<?php
while ( have_posts() ) :
    the_post();

    $bof_home_content = apply_filters( 'the_content', get_the_content() );

    if ( $bof_mobile_hero_b64 ) {
        $bof_mobile_hero_data = 'data:image/jpeg;base64,' . $bof_mobile_hero_b64;
        $bof_mobile_hero_url  = get_stylesheet_directory_uri() . '/mobile-hero.php?v=2';
        $bof_home_content     = str_replace(
            array(
                esc_url( $bof_mobile_hero_url ),
                $bof_mobile_hero_url,
            ),
            esc_attr( $bof_mobile_hero_data ),
            $bof_home_content
        );
    }

    echo $bof_home_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- filtered Gutenberg content.
endwhile;
?>
</div>

<?php get_footer(); ?>
