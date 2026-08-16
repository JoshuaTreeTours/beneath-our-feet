<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
    <?php if ( is_front_page() ) : ?>
    <style id="bof-responsive-home-heroes">
        /* Gutenberg controls which image is used. Theme CSS controls which
           hero is visible at each viewport size. */
        .bof-mobile-hero {
            display: none !important;
        }

        @media (max-width: 780px) {
            .bof-desktop-hero {
                display: none !important;
            }

            /* Make the Gutenberg mobile Cover block the actual mobile hero,
               even if legacy bof-home-hero styles are also present. */
            .bof-page-content > .bof-mobile-hero,
            .bof-mobile-hero,
            .bof-home-hero.bof-mobile-hero {
                display: block !important;
                position: relative !important;
                width: 100vw !important;
                max-width: none !important;
                min-height: 0 !important;
                height: auto !important;
                aspect-ratio: 1023 / 1537 !important;
                margin: 0 calc(50% - 50vw) !important;
                padding: 0 !important;
                overflow: hidden !important;
                background: #efe2c8 !important;
            }

            /* WordPress Cover markup can vary slightly by version. Target
               both the Cover background class and any image in the block. */
            .bof-mobile-hero > .wp-block-cover__image-background,
            .bof-mobile-hero .wp-block-cover__image-background,
            .bof-mobile-hero > img,
            .bof-mobile-hero img {
                display: block !important;
                position: absolute !important;
                inset: 0 !important;
                width: 100% !important;
                height: 100% !important;
                max-width: none !important;
                min-width: 100% !important;
                min-height: 100% !important;
                margin: 0 !important;
                object-fit: contain !important;
                object-position: center center !important;
                opacity: 1 !important;
                visibility: visible !important;
            }

            /* Legacy mobile hero rules in style.css hide bof-home-hero media.
               Explicitly restore the Gutenberg image when this is the mobile
               hero, while suppressing only overlay/text layers. */
            .bof-home-hero.bof-mobile-hero > .wp-block-cover__image-background,
            .bof-home-hero.bof-mobile-hero .wp-block-cover__image-background {
                display: block !important;
            }

            .bof-mobile-hero .wp-block-cover__background,
            .bof-mobile-hero .wp-block-cover__inner-container {
                display: none !important;
            }
        }
    </style>
    <?php endif; ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<?php if ( ! is_front_page() ) : ?>
<header class="site-header">
    <div class="site-branding">
        <p class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a></p>
        <?php $description = get_bloginfo( 'description', 'display' ); ?>
        <?php if ( $description ) : ?>
            <p class="site-description"><?php echo esc_html( $description ); ?></p>
        <?php endif; ?>
    </div>
    <nav class="site-nav" aria-label="<?php esc_attr_e( 'Primary navigation', 'beneath-our-feet' ); ?>">
        <?php
        wp_nav_menu(
            array(
                'theme_location' => 'primary',
                'container'      => false,
                'fallback_cb'    => false,
            )
        );
        ?>
    </nav>
</header>
<?php endif; ?>
<main class="site-main">
