<?php
get_header();
?>

<?php if ( is_front_page() || is_home() ) : ?>
    <section class="bof-hero" aria-label="Beneath Our Feet">
        <div class="bof-hero-shade"></div>
        <div class="bof-hero-inner">
            <p class="bof-kicker">GEOLOGY • DEEP TIME • PLACE</p>
            <h1 class="screen-reader-text">Beneath Our Feet</h1>
            <p class="bof-hero-copy">Discover Earth’s story through geology, deep time, and museum-quality illustrations.</p>
            <a class="bof-button" href="#explore">Explore the story</a>
        </div>
    </section>

    <section class="bof-intro">
        <p class="bof-eyebrow">Stories written in stone</p>
        <h2>The landscape is the evidence.</h2>
        <p>Beneath Our Feet begins with the places we know, then looks downward and backward in time: rocks, fossils, faults, volcanoes, ancient seas, uplift, erosion, and the forces that shaped the ground beneath us.</p>
    </section>

    <section id="explore" class="bof-grid" aria-label="Explore Beneath Our Feet">
        <article class="bof-card">
            <span class="bof-card-number">01</span>
            <h3>Places</h3>
            <p>Start with a landscape — from national parks to the coast — and uncover the geology that makes it distinctive.</p>
            <a href="#">Explore places →</a>
        </article>
        <article class="bof-card">
            <span class="bof-card-number">02</span>
            <h3>Deep Time</h3>
            <p>Move through millions and billions of years and see how continents, oceans, climates, and life changed.</p>
            <a href="#">Enter deep time →</a>
        </article>
        <article class="bof-card">
            <span class="bof-card-number">03</span>
            <h3>How We Know</h3>
            <p>Follow the evidence geologists use: stratigraphy, fossils, radiometric dating, structures, minerals, and landscapes.</p>
            <a href="#">See the evidence →</a>
        </article>
    </section>

    <section class="bof-feature">
        <div>
            <p class="bof-eyebrow">A visual field guide to Earth</p>
            <h2>One planet. Billions of years. Countless stories.</h2>
        </div>
        <p>Built to make complex geology visual, memorable, and worth exploring — whether you are standing at a canyon rim, walking a beach, or simply wondering what lies beneath your feet.</p>
    </section>
<?php elseif ( have_posts() ) : ?>
    <?php while ( have_posts() ) : the_post(); ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class( 'entry' ); ?>>
            <header class="entry-header">
                <?php if ( is_singular() ) : ?>
                    <h1 class="entry-title"><?php the_title(); ?></h1>
                <?php else : ?>
                    <h2 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                <?php endif; ?>
            </header>
            <div class="entry-content">
                <?php is_singular() ? the_content() : the_excerpt(); ?>
            </div>
        </article>
    <?php endwhile; ?>
    <?php the_posts_navigation(); ?>
<?php endif; ?>

<?php
get_footer();
