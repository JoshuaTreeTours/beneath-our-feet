<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
    <?php if ( is_front_page() ) : ?>
    <style id="bof-mobile-home-hero-v3">
        .bof-mobile-home-hero { display: none; }
        @media (max-width: 780px) {
            .bof-mobile-home-hero {
                display: block !important;
                width: 100vw !important;
                max-width: none !important;
                margin: 0 calc(50% - 50vw) !important;
                padding: 0 !important;
                background: #000;
                overflow: hidden;
            }
            .bof-mobile-home-hero img {
                display: block !important;
                width: 100% !important;
                height: auto !important;
                aspect-ratio: 720 / 332;
                object-fit: cover;
                margin: 0 !important;
                padding: 0 !important;
            }
            body.home .site-main > .wp-block-cover:first-of-type,
            body.home .site-main > .bof-home-hero,
            body.home .site-main .entry-content > .wp-block-cover:first-of-type,
            body.home .site-main .bof-page-content > .wp-block-cover:first-of-type,
            body.home .site-main .bof-page-content > .bof-home-hero {
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
<?php if ( is_front_page() ) : ?>
    <div class="bof-mobile-home-hero" aria-label="Beneath Our Feet mobile hero">
        <img
            src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/hero-landscape.svg?v=3' ); ?>"
            alt="Beneath Our Feet — geology, deep time, and Earth's story"
            width="720"
            height="332"
            decoding="async"
            fetchpriority="high"
        >
    </div>
<?php endif; ?>
