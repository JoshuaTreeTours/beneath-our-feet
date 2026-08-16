<?php
/**
 * Editable static front page template.
 *
 * The featured image controls the hero image. The page excerpt controls the
 * hero sentence. The Gutenberg page body controls everything below the hero.
 */
get_header();

while ( have_posts() ) :
    the_post();

    $hero_image = get_the_post_thumbnail_url( get_the_ID(), 'full' );
    if ( ! $hero_image ) {
        $hero_image = 'https://wordpress-1476123-6617325.cloudwaysapps.com/wp-content/uploads/2026/08/763018716_122104309185419437_8310539191905664210_n.jpg';
    }

    $hero_copy = has_excerpt()
        ? get_the_excerpt()
        : 'Discover Earth’s story through geology, deep time, and museum-quality illustrations.';
    ?>

    <section class="bof-hero" aria-label="<?php echo esc_attr( get_the_title() ); ?>" style="background-image:url('<?php echo esc_url( $hero_image ); ?>');">
        <div class="bof-hero-shade"></div>
        <div class="bof-hero-inner">
            <p class="bof-kicker">GEOLOGY • DEEP TIME • PLACE</p>
            <h1 class="screen-reader-text"><?php the_title(); ?></h1>
            <p class="bof-hero-copy"><?php echo esc_html( $hero_copy ); ?></p>
            <a class="bof-button" href="#page-content">Explore the story</a>
        </div>
    </section>

    <div id="page-content" class="bof-page-content">
        <?php if ( trim( get_the_content() ) ) : ?>
            <?php the_content(); ?>
        <?php else : ?>
            <section class="bof-intro">
                <p class="bof-eyebrow">Stories written in stone</p>
                <h2>The landscape is the evidence.</h2>
                <p>Beneath Our Feet begins with the places we know, then looks downward and backward in time: rocks, fossils, faults, volcanoes, ancient seas, uplift, erosion, and the forces that shaped the ground beneath us.</p>
            </section>

            <section class="bof-grid" aria-label="Explore Beneath Our Feet">
                <article class="bof-card">
                    <span class="bof-card-number">01</span>
                    <h3>Places</h3>
                    <p>Start with a landscape — from national parks to the coast — and uncover the geology that makes it distinctive.</p>
                </article>
                <article class="bof-card">
                    <span class="bof-card-number">02</span>
                    <h3>Deep Time</h3>
                    <p>Move through millions and billions of years and see how continents, oceans, climates, and life changed.</p>
                </article>
                <article class="bof-card">
                    <span class="bof-card-number">03</span>
                    <h3>How We Know</h3>
                    <p>Follow the evidence geologists use: stratigraphy, fossils, radiometric dating, structures, minerals, and landscapes.</p>
                </article>
            </section>

            <section class="bof-feature">
                <div>
                    <p class="bof-eyebrow">A visual field guide to Earth</p>
                    <h2>One planet. Billions of years. Countless stories.</h2>
                </div>
                <p>Built to make complex geology visual, memorable, and worth exploring — whether you are standing at a canyon rim, walking a beach, or simply wondering what lies beneath your feet.</p>
            </section>
        <?php endif; ?>
    </div>

<?php endwhile; ?>

<?php get_footer(); ?>
