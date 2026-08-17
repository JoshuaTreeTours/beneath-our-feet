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
    <style id="bof-floating-menu-styles">
        .bof-menu-toggle {
            position: fixed;
            top: 18px;
            right: 18px;
            z-index: 10002;
            width: 54px;
            height: 54px;
            display: grid;
            place-items: center;
            padding: 0;
            border: 1px solid rgba(239,226,200,.72);
            border-radius: 50%;
            background: rgba(23,32,24,.94);
            box-shadow: 0 8px 26px rgba(0,0,0,.24);
            cursor: pointer;
            -webkit-backdrop-filter: blur(8px);
            backdrop-filter: blur(8px);
        }

        .bof-menu-toggle:hover,
        .bof-menu-toggle:focus-visible {
            background: #27382a;
            outline: 2px solid #d7b56e;
            outline-offset: 3px;
        }

        .bof-menu-icon,
        .bof-menu-icon::before,
        .bof-menu-icon::after {
            display: block;
            width: 24px;
            height: 2px;
            border-radius: 2px;
            background: #f6edda;
            transition: transform .22s ease, opacity .22s ease;
        }

        .bof-menu-icon {
            position: relative;
        }

        .bof-menu-icon::before,
        .bof-menu-icon::after {
            content: "";
            position: absolute;
            left: 0;
        }

        .bof-menu-icon::before { top: -7px; }
        .bof-menu-icon::after { top: 7px; }

        .bof-menu-toggle[aria-expanded="true"] .bof-menu-icon {
            background: transparent;
        }

        .bof-menu-toggle[aria-expanded="true"] .bof-menu-icon::before {
            top: 0;
            transform: rotate(45deg);
        }

        .bof-menu-toggle[aria-expanded="true"] .bof-menu-icon::after {
            top: 0;
            transform: rotate(-45deg);
        }

        .bof-menu-backdrop {
            position: fixed;
            inset: 0;
            z-index: 10000;
            background: rgba(12,15,12,.52);
            opacity: 0;
            visibility: hidden;
            transition: opacity .22s ease, visibility .22s ease;
        }

        .bof-floating-menu {
            position: fixed;
            top: 0;
            right: 0;
            z-index: 10001;
            width: min(430px, 92vw);
            height: 100dvh;
            overflow-y: auto;
            padding: 32px 32px 48px;
            color: #f6edda;
            background: #172018;
            border-left: 1px solid rgba(203,183,141,.45);
            box-shadow: -14px 0 42px rgba(0,0,0,.28);
            transform: translateX(102%);
            transition: transform .26s ease;
        }

        .bof-menu-open .bof-menu-backdrop {
            opacity: 1;
            visibility: visible;
        }

        .bof-menu-open .bof-floating-menu {
            transform: translateX(0);
        }

        .bof-menu-open {
            overflow: hidden;
        }

        .bof-menu-brand {
            margin: 0 64px 1.8rem 0;
            font-size: 1.45rem;
            line-height: 1.05;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .bof-menu-brand a {
            color: #f6edda;
            text-decoration: none;
        }

        .bof-menu-home {
            display: inline-block;
            margin-bottom: 1.7rem;
            color: #dfbd73;
            font-size: .83rem;
            font-weight: 700;
            letter-spacing: .14em;
            text-decoration: none;
            text-transform: uppercase;
        }

        .bof-menu-section {
            padding: 1.15rem 0 1.25rem;
            border-top: 1px solid rgba(203,183,141,.28);
        }

        .bof-menu-section h2 {
            margin: 0 0 .7rem;
            color: #c99b47;
            font-size: .75rem;
            line-height: 1.2;
            letter-spacing: .16em;
            text-transform: uppercase;
        }

        .bof-menu-section ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .bof-menu-section li + li {
            margin-top: .28rem;
        }

        .bof-menu-section a {
            display: block;
            padding: .28rem 0;
            color: #f6edda;
            font-size: 1.05rem;
            line-height: 1.35;
            text-decoration: none;
        }

        .bof-menu-section a:hover,
        .bof-menu-section a:focus-visible {
            color: #dfbd73;
            text-decoration: underline;
            text-underline-offset: 4px;
        }

        @media (max-width: 780px) {
            .bof-menu-toggle {
                top: 12px;
                right: 12px;
                width: 48px;
                height: 48px;
            }

            .bof-floating-menu {
                width: min(390px, 94vw);
                padding: 24px 24px 40px;
            }

            .bof-menu-brand {
                margin-right: 58px;
                font-size: 1.25rem;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .bof-floating-menu,
            .bof-menu-backdrop,
            .bof-menu-icon,
            .bof-menu-icon::before,
            .bof-menu-icon::after {
                transition: none;
            }
        }
    </style>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php
$bof_search_url = static function ( $term ) {
    return esc_url( add_query_arg( 's', $term, home_url( '/' ) ) );
};
?>

<button class="bof-menu-toggle" type="button" aria-expanded="false" aria-controls="bof-floating-menu" aria-label="Open site menu">
    <span class="bof-menu-icon" aria-hidden="true"></span>
</button>
<div class="bof-menu-backdrop" aria-hidden="true"></div>
<nav id="bof-floating-menu" class="bof-floating-menu" aria-label="Beneath Our Feet exploration menu">
    <p class="bof-menu-brand"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Beneath Our Feet</a></p>
    <a class="bof-menu-home" href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>

    <section class="bof-menu-section" aria-labelledby="bof-menu-places">
        <h2 id="bof-menu-places">Explore Places</h2>
        <ul>
            <li><a href="<?php echo $bof_search_url( 'Places' ); ?>">Places</a></li>
            <li><a href="<?php echo esc_url( home_url( '/national-parks/' ) ); ?>">National Parks</a></li>
            <li><a href="<?php echo $bof_search_url( 'Santa Barbara' ); ?>">Santa Barbara Geology</a></li>
            <li><a href="<?php echo $bof_search_url( 'Colorado Plateau' ); ?>">Colorado Plateau</a></li>
            <li><a href="<?php echo $bof_search_url( 'California geology' ); ?>">California Geology</a></li>
        </ul>
    </section>

    <section class="bof-menu-section" aria-labelledby="bof-menu-time">
        <h2 id="bof-menu-time">Earth Through Time</h2>
        <ul>
            <li><a href="<?php echo $bof_search_url( 'Deep Time' ); ?>">Deep Time</a></li>
            <li><a href="<?php echo $bof_search_url( 'Plate Tectonics' ); ?>">Plate Tectonics</a></li>
            <li><a href="<?php echo $bof_search_url( 'Supercontinents' ); ?>">Supercontinents</a></li>
            <li><a href="<?php echo $bof_search_url( 'Mountain Building' ); ?>">Mountain Building &amp; Orogenies</a></li>
            <li><a href="<?php echo $bof_search_url( 'Geologic Time' ); ?>">Geologic Time Scale</a></li>
        </ul>
    </section>

    <section class="bof-menu-section" aria-labelledby="bof-menu-earth">
        <h2 id="bof-menu-earth">Earth Materials &amp; Processes</h2>
        <ul>
            <li><a href="<?php echo $bof_search_url( 'Rocks Minerals' ); ?>">Rocks &amp; Minerals</a></li>
            <li><a href="<?php echo $bof_search_url( 'Fossils' ); ?>">Fossils &amp; Life</a></li>
            <li><a href="<?php echo $bof_search_url( 'Faults Earthquakes' ); ?>">Faults &amp; Earthquakes</a></li>
            <li><a href="<?php echo $bof_search_url( 'Volcanoes Magma' ); ?>">Volcanoes &amp; Magma</a></li>
            <li><a href="<?php echo $bof_search_url( 'Erosion Landscapes' ); ?>">Erosion &amp; Landscapes</a></li>
            <li><a href="<?php echo $bof_search_url( 'Oceans Coasts Climate' ); ?>">Oceans, Coasts &amp; Climate</a></li>
        </ul>
    </section>

    <section class="bof-menu-section" aria-labelledby="bof-menu-collections">
        <h2 id="bof-menu-collections">Collections</h2>
        <ul>
            <li><a href="<?php echo $bof_search_url( 'How We Know' ); ?>">How We Know</a></li>
            <li><a href="<?php echo $bof_search_url( 'Maps Field Guides' ); ?>">Maps &amp; Field Guides</a></li>
            <li><a href="<?php echo $bof_search_url( 'Moon' ); ?>">Beneath Our Feet on the Moon</a></li>
            <li><a href="<?php echo esc_url( home_url( '/source-material/' ) ); ?>">Source Material</a></li>
            <li><a href="<?php echo $bof_search_url( 'About Beneath Our Feet' ); ?>">About the Project</a></li>
        </ul>
    </section>
</nav>

<script id="bof-floating-menu-script">
(function () {
    var body = document.body;
    var toggle = document.querySelector('.bof-menu-toggle');
    var menu = document.getElementById('bof-floating-menu');
    var backdrop = document.querySelector('.bof-menu-backdrop');

    if (!toggle || !menu || !backdrop) return;

    function setOpen(open) {
        body.classList.toggle('bof-menu-open', open);
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        toggle.setAttribute('aria-label', open ? 'Close site menu' : 'Open site menu');
    }

    toggle.addEventListener('click', function () {
        setOpen(!body.classList.contains('bof-menu-open'));
    });

    backdrop.addEventListener('click', function () {
        setOpen(false);
    });

    menu.addEventListener('click', function (event) {
        if (event.target.closest('a')) setOpen(false);
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && body.classList.contains('bof-menu-open')) {
            setOpen(false);
            toggle.focus();
        }
    });
})();
</script>

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