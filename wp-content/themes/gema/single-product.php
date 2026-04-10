<?php
/**
 * Single Product Template — VietFarmy / Gema
 *
 * @package Gema
 */

get_header(); ?>

    <div class="u-container-sides-spacing">
    <div class="c-layout  o-wrapper  u-container-width">

        <?php get_template_part('template-parts/content', 'header'); ?>

        <div id="primary" class="content-area">

            <main id="main" class="site-main" role="main">

                <?php while ( have_posts() ) : the_post(); ?>

                    <?php get_template_part('template-parts/content', 'single'); ?>

                    <?php the_post_navigation(); ?>

                    <?php if ( comments_open() || get_comments_number() ) : ?>
                        <?php comments_template(); ?>
                    <?php endif; ?>

                <?php endwhile; ?>

            </main><!-- #main -->

            <?php get_sidebar(); ?>

        </div><!-- #primary -->

    </div>
    </div>

<?php get_footer();
