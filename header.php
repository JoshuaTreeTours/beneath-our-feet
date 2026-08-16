<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
    <?php if ( is_front_page() ) : ?>
    <style id="bof-responsive-home-heroes">
        /* The mobile hero is a normal Gutenberg Cover block so it can be
           replaced directly from the WordPress Media Library. */
        .bof-mobile-hero {
            display: none !important;
        }

        @media (max-width: 780px) {
            .bof-desktop-hero {
                display: none !important;
            }

            .bof-page-content > .bof-mobile-hero,
            .bof-mobile-hero {
                display: flex !important;
                width: 100vw !important;
                max-width: none !important;
                min-height: 0 !important;
                height: auto !important;
                aspect-ratio: 1023 / 1537;
                margin: 0 calc(50% - 50vw) !important;
                padding: 0 !important;
                overflow: hidden;
                background: #efe2c8;
            }

            .bof-mobile-hero .wp-block-cover__image-background {
                display: block !important;
                width: 100% !important;
                height: 100% !important;
                max-width: none !important;
                object-fit: contain !important;
                object-position: center center !important;
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
<header class="site-header">
    <div class="site-branding">
        <?php if ( is_front_page() && is_home() ) : ?>
            <h1 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a></h1>
        <?php else : ?>
            <p class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a></p>
        <?php endif; ?>
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
<main class="site-main">
