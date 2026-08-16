<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
    <?php if ( is_front_page() ) : ?>
    <style id="bof-mobile-home-hero-v4">
        .bof-mobile-home-hero { display: none; }
        @media (max-width: 780px) {
            .bof-mobile-home-hero {
                display: block !important;
                width: 100vw !important;
                max-width: none !important;
                margin: 0 calc(50% - 50vw) !important;
                padding: 0 !important;
                background: #efe0bf;
                overflow: hidden;
            }
            .bof-mobile-home-hero img {
                display: block !important;
                width: 100% !important;
                max-width: none !important;
                height: auto !important;
                aspect-ratio: auto !important;
                object-fit: contain !important;
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
    <?php
    $bof_mobile_hero_b64 = '';
    for ( $bof_mobile_hero_part = 1; $bof_mobile_hero_part <= 10; $bof_mobile_hero_part++ ) {
        $bof_mobile_hero_path = get_stylesheet_directory() . '/assets/mobile-hero.b64.' . $bof_mobile_hero_part;
        if ( is_readable( $bof_mobile_hero_path ) ) {
            $bof_mobile_hero_b64 .= trim( file_get_contents( $bof_mobile_hero_path ) );
        }
    }
    ?>
    <div class="bof-mobile-home-hero" aria-label="Beneath Our Feet mobile hero">
        <img
            src="data:image/jpeg;base64,<?php echo esc_attr( $bof_mobile_hero_b64 ); ?>"
            alt="Beneath Our Feet — geology, deep time, and Earth's story"
            width="480"
            height="853"
            decoding="async"
            fetchpriority="high"
        >
    </div>
<?php endif; ?>
