<?php
/**
 * Template Name: VietFarmy Contact - Full Width
 * Description: Trang liên hệ không có sidebar
 */

get_header(); ?>

    <div class="u-container-sides-spacing">
    <div class="c-layout  o-wrapper  u-container-width">

        <?php get_template_part('template-parts/content', 'header'); ?>

        <div id="primary" class="content-area content-area-fullwidth">

            <main id="main" class="site-main-fullwidth" role="main">

                <?php while ( have_posts() ) : the_post(); ?>

                    <?php the_content(); ?>

                    <?php if ( comments_open() || get_comments_number() ) :
                        comments_template();
                    endif; ?>

                <?php endwhile; ?>

            </main>

        </div>

    </div>
    </div>

<style>
.content-area-fullwidth {
    max-width: 100% !important;
}
.content-area-fullwidth #main {
    max-width: 100% !important;
}
</style>

<?php get_footer();
