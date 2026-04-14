<?php
/**
 * Template Name: VietFarmy Contact - Full Width
 * Description: Trang liên hệ không có sidebar
 */

get_header(); ?>

<style>
/* Reset and center everything */
.c-layout {
    display: block !important;
    max-width: 1240px !important;
    margin: 0 auto !important;
    padding: 0 20px !important;
}

.content-area-fullwidth {
    width: 100% !important;
    max-width: 100% !important;
    padding: 0 !important;
    float: none !important;
}

.site-main-fullwidth {
    width: 100% !important;
    max-width: 100% !important;
}

/* Ensure content is centered */
.vnf-contact-container {
    max-width: 1200px !important;
    margin-left: auto !important;
    margin-right: auto !important;
    padding-left: 0 !important;
    padding-right: 0 !important;
}

/* Mobile responsive - 1 column */
@media (max-width: 768px) {
    .vnf-contact-wrapper {
        grid-template-columns: 1fr !important;
    }
    .vnf-contact-container {
        padding: 20px 15px !important;
    }
    .vnf-contact-card {
        padding: 20px !important;
    }
    .vnf-form-row {
        grid-template-columns: 1fr !important;
    }
    .vnf-contact-info-item {
        flex-direction: column !important;
        gap: 8px !important;
    }
    .vnf-contact-social {
        flex-direction: column !important;
    }
    .vnf-social-btn {
        justify-content: center !important;
    }
}
</style>

    <div class="content-area-fullwidth">
        <main class="site-main-fullwidth" role="main">
            <?php while ( have_posts() ) : the_post(); ?>
                <?php the_content(); ?>
                <?php if ( comments_open() || get_comments_number() ) :
                    comments_template();
                endif; ?>
            <?php endwhile; ?>
        </main>
    </div>

<?php get_footer();