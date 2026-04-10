<?php
/**
 * Front Page Template — VietFarmy
 *
 * @package Gema
 */

get_header();

// SLIDESHOW — banner trang chủ
echo do_shortcode('[vnf_slideshow id="1"]');

// Lấy ID sản phẩm nổi bật (3 sản phẩm)
$product_ids = array();
for ($i = 1; $i <= 3; $i++) {
    $id = get_theme_mod("vnf_featured_product_$i", '');
    if (!empty($id)) $product_ids[] = (int) $id;
}

// Lấy ID bài viết nổi bật (3 bài viết)
$post_ids = array();
for ($i = 1; $i <= 3; $i++) {
    $id = get_theme_mod("vnf_featured_post_$i", '');
    if (!empty($id)) $post_ids[] = (int) $id;
}
?>

<!-- QUY TRÌNH FARM ĐẾN LY -->
<?php echo do_shortcode('[vnf_process]'); ?>

<!-- SẢN PHẨM NỔI BẬT -->
<section class="vnf-section">
    <div class="vnf-inner">
        <div class="vnf-section-header">
            <h2 class="vnf-section-title">Sản phẩm nổi bật</h2>
            <p class="vnf-section-subtitle">Những tinh túy nhất từ nông trại Gia Lai</p>
        </div>

        <?php
        if (!empty($product_ids)) {
            $products = new WP_Query(array(
                'post_type'      => 'product',
                'post__in'       => $product_ids,
                'posts_per_page' => count($product_ids),
                'orderby'        => 'post__in',
                'post_status'    => 'publish',
            ));
        } else {
            $products = new WP_Query(array(
                'post_type'      => 'product',
                'posts_per_page' => 3,
                'orderby'        => 'date',
                'order'          => 'DESC',
                'post_status'    => 'publish',
            ));
        }
        ?>

        <?php if ($products->have_posts()) : ?>
            <div class="vnf-product-grid">
                <?php while ($products->have_posts()) : $products->the_post(); global $product; ?>
                    <div class="vnf-card">
                        <a href="<?php the_permalink(); ?>" class="vnf-card-thumb">
                            <?php
                            $img_url = get_post_meta(get_the_ID(), '_vietfarmy_product_image_url', true);
                            if (!empty($img_url)) {
                                echo '<img src="' . esc_url($img_url) . '" alt="' . esc_attr(get_the_title()) . '">';
                            } elseif (has_post_thumbnail()) {
                                the_post_thumbnail('woocommerce_thumbnail', array('alt' => get_the_title()));
                            } else {
                                echo '<img src="' . wc_placeholder_img_src('woocommerce_thumbnail') . '" alt="Product">';
                            }
                            ?>
                            <?php if ($product->is_on_sale()) : ?>
                                <span class="vnf-badge vnf-badge-sale">Sale</span>
                            <?php endif; ?>
                        </a>
                        <div class="vnf-card-info">
                            <h3 class="vnf-card-name"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                            <div class="vnf-card-price"><?php echo $product->get_price_html(); ?></div>
                            <a href="<?php the_permalink(); ?>" class="vnf-btn">Xem chi tiết</a>
                        </div>
                    </div>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- BÀI VIẾT NỔI BẬT -->
<section class="vnf-section vnf-section-blog">
    <div class="vnf-inner">
        <div class="vnf-section-header">
            <h2 class="vnf-section-title">Bài viết nổi bật</h2>
            <p class="vnf-section-subtitle">Câu chuyện từ nông trại & hành trình cà phê</p>
        </div>

        <?php
        if (!empty($post_ids)) {
            $posts = new WP_Query(array(
                'post_type'      => 'post',
                'post__in'       => $post_ids,
                'posts_per_page' => count($post_ids),
                'orderby'        => 'post__in',
                'post_status'    => 'publish',
            ));
        } else {
            $posts = new WP_Query(array(
                'post_type'      => 'post',
                'posts_per_page' => 3,
                'orderby'        => 'date',
                'order'          => 'DESC',
                'post_status'    => 'publish',
            ));
        }
        ?>

        <?php if ($posts->have_posts()) : ?>
            <div class="vnf-post-grid">
                <?php while ($posts->have_posts()) : $posts->the_post(); ?>
                    <div class="vnf-card">
                        <a href="<?php the_permalink(); ?>" class="vnf-card-thumb">
                            <?php
                            $img_url = get_post_meta(get_the_ID(), '_vnf_post_image_url', true);
                            if (!empty($img_url)) {
                                echo '<img src="' . esc_url($img_url) . '" alt="' . esc_attr(get_the_title()) . '">';
                            } elseif (has_post_thumbnail()) {
                                the_post_thumbnail('medium_large', array('alt' => get_the_title()));
                            } else {
                                echo '<img src="https://via.placeholder.com/400x250?text=Coffee" alt="Post">';
                            }
                            ?>
                        </a>
                        <div class="vnf-card-info">
                            <h3 class="vnf-card-name"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                            <div class="vnf-card-date"><?php echo get_the_date('d/m/Y'); ?></div>
                            <a href="<?php the_permalink(); ?>" class="vnf-btn">Đọc tiếp</a>
                        </div>
                    </div>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<style>
/* ===== SHARED ===== */
.vnf-section { padding: 48px 0; background: #fff; }
.vnf-section-blog { background: #f8f8f5; }
.vnf-inner { max-width: 1200px; margin: 0 auto; padding: 0 24px; }
.vnf-section-header { text-align: center; margin-bottom: 36px; }
.vnf-section-title { font-size: 28px; font-weight: 700; color: #2d6a4f; margin: 0 0 8px; }
.vnf-section-subtitle { font-size: 15px; color: #888; margin: 0; }

/* Grid */
.vnf-product-grid,
.vnf-post-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }

/* Card */
.vnf-card { background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.06); transition: transform 0.2s, box-shadow 0.2s; }
.vnf-card:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,0,0,0.12); }
.vnf-card-thumb { display: block; position: relative; overflow: hidden; aspect-ratio: 4/3; background: #f0f0f0; }
.vnf-card-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform 0.3s; }
.vnf-card:hover .vnf-card-thumb img { transform: scale(1.05); }
.vnf-badge { position: absolute; top: 10px; left: 10px; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; color: #fff; z-index: 1; }
.vnf-badge-sale { background: #e74c3c; }
.vnf-card-info { padding: 20px; }
.vnf-card-name { font-size: 16px; font-weight: 600; color: #222; margin: 0 0 10px; line-height: 1.4; }
.vnf-card-name a { color: inherit; text-decoration: none; }
.vnf-card-name a:hover { color: #2d6a4f; }
.vnf-card-price { font-size: 17px; font-weight: 700; color: #c0392b; margin-bottom: 14px; }
.vnf-card-price del { color: #999; font-size: 13px; font-weight: 400; margin-right: 6px; }
.vnf-card-date { font-size: 13px; color: #999; margin-bottom: 14px; }
.vnf-btn { display: block; text-align: center; padding: 10px 16px; background: #2d6a4f; color: #fff; border-radius: 8px; font-size: 14px; font-weight: 600; text-decoration: none; transition: background 0.2s; }
.vnf-btn:hover { background: #1e4d34; color: #fff; }

/* Responsive */
@media (max-width: 768px) {
    .vnf-product-grid, .vnf-post-grid { grid-template-columns: repeat(2, 1fr); gap: 14px; }
    .vnf-card-info { padding: 14px; }
    .vnf-card-name { font-size: 14px; }
    .vnf-section-title { font-size: 22px; }
}
@media (max-width: 480px) {
    .vnf-product-grid, .vnf-post-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
    .vnf-section { padding: 32px 0; }
}
</style>

<?php get_footer(); ?>
