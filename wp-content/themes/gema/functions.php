<?php
/**
 * Gema functions and definitions.
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Gema
 */

// Include Featured Image from URL
require_once get_template_directory() . '/assets/php/featured-image-from-url.php';

// ============================================================
// SẢN PHẨM TỪ URL — Dùng chung key: _vietfarmy_product_image_url
// ============================================================

// 1. BỎ ảnh mặc định của WooCommerce (cả shop lẫn single)
// -----------------------------------------------
remove_action('woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10);

// 2. Thêm Meta Box nhập URL vào trang Edit Product
// -----------------------------------------------
add_action('add_meta_boxes', 'vietfarmy_add_url_meta_box');

function vietfarmy_add_url_meta_box() {
    add_meta_box(
        'vietfarmy_url_image',
        'Ảnh sản phẩm từ URL',
        'vietfarmy_url_meta_box_callback',
        'product',
        'side',
        'low'
    );
}

function vietfarmy_url_meta_box_callback($post) {
    wp_nonce_field('vietfarmy_url_save', 'vietfarmy_url_nonce');
    $url = get_post_meta($post->ID, '_vietfarmy_product_image_url', true);
    ?>
    <style>
        .vietfarmy-url-box { background: #fff; padding: 4px; }
        .vietfarmy-url-box .url-input-row { display: flex; gap: 6px; align-items: center; }
        .vietfarmy-url-box input[type="url"] { flex: 1; padding: 8px 10px; border: 1px solid #ccc; border-radius: 4px; font-size: 13px; }
        .vietfarmy-url-box input[type="url"]:focus { border-color: #2271b1; box-shadow: 0 0 0 1px #2271b1; outline: none; }
        .vietfarmy-url-box .btn-save-url {
            background: #2271b1; color: #fff; border: none; padding: 8px 16px;
            border-radius: 4px; cursor: pointer; font-size: 13px; white-space: nowrap;
        }
        .vietfarmy-url-box .btn-save-url:hover { background: #135e96; }
        .vietfarmy-url-box .preview-box {
            margin-top: 10px; background: #f0f0f1; padding: 8px; border-radius: 4px; text-align: center;
        }
        .vietfarmy-url-box .preview-box img { max-width: 100%; border-radius: 4px; border: 1px solid #ddd; }
        .vietfarmy-url-box .hint { font-size: 12px; color: #646970; margin-top: 8px; }
    </style>
    <div class="vietfarmy-url-box">
        <div class="url-input-row">
            <input type="url"
                   id="vietfarmy_product_image_url"
                   name="vietfarmy_product_image_url"
                   value="<?php echo esc_url($url); ?>"
                   placeholder="https://example.com/image.jpg">
            <button type="button" class="btn-save-url" id="vietfarmy_save_url_btn">Lưu URL</button>
        </div>

        <?php if ($url) : ?>
            <div class="preview-box">
                <img src="<?php echo esc_url($url); ?>" alt="Preview">
            </div>
        <?php endif; ?>

        <p class="hint">📎 Dán link ảnh → nhấn <strong>Lưu URL</strong> hoặc dùng nút <strong>Xuất bản/Cập nhật</strong> của WordPress.</p>
    </div>

    <script>
    (function(){
        var btn = document.getElementById('vietfarmy_save_url_btn');
        if (!btn) return;
        btn.addEventListener('click', function(){
            var input = document.getElementById('vietfarmy_product_image_url');
            if (!input || !input.value) return;
            // submit form
            var form = btn.closest('form');
            if (form) {
                // ensure the input is enabled before submit
                input.disabled = false;
                form.submit();
            }
        });
    })();
    </script>
    <?php
}

// 3. Lưu URL khi save product
// -----------------------------------------------
add_action('save_post', 'vietfarmy_save_url_meta');

function vietfarmy_save_url_meta($post_id) {
    // Chỉ chạy cho product post type
    if (get_post_type($post_id) !== 'product') return;

    if (!isset($_POST['vietfarmy_url_nonce']) || !wp_verify_nonce($_POST['vietfarmy_url_nonce'], 'vietfarmy_url_save')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    if (isset($_POST['vietfarmy_product_image_url'])) {
        update_post_meta($post_id, '_vietfarmy_product_image_url', esc_url_raw($_POST['vietfarmy_product_image_url']));
    }
}

// ============================================================
// FEATURED IMAGE COLUMN TRONG ADMIN PRODUCT LIST
// ============================================================

// 4a. Thêm cột "Hình ảnh" vào danh sách sản phẩm
// -----------------------------------------------
add_filter('manage_edit-product_columns', 'vietfarmy_add_image_column');

function vietfarmy_add_image_column($columns) {
    $new_columns = array();
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        // Chèn cột "Hình ảnh" ngay sau cột "Tên"
        if ($key === 'name') {
            $new_columns['vietfarmy_product_image'] = 'Hình ảnh';
        }
    }
    return $new_columns;
}

// 4b. Hiển thị thumbnail trong cột — ưu tiên: URL > Featured Image > Placeholder
// -----------------------------------------------
add_action('manage_product_posts_custom_column', 'vietfarmy_render_image_column', 10, 2);

function vietfarmy_render_image_column($column, $post_id) {
    if ($column !== 'vietfarmy_product_image') return;

    $product = wc_get_product($post_id);
    if (!$product) return;

    // 1. Ưu tiên: Ảnh từ URL
    $url_image = get_post_meta($post_id, '_vietfarmy_product_image_url', true);

    // 2. Fallback: Featured Image của sản phẩm
    $featured_id = $product->get_image_id();

    if ($url_image) {
        $img_src = esc_url($url_image);
    } elseif ($featured_id) {
        $img_src = wp_get_attachment_image_url($featured_id, array(60, 60));
    } else {
        $img_src = wc_placeholder_img_src(array(60, 60));
    }

    echo '<img src="' . $img_src . '" alt="" style="width:60px;height:60px;object-fit:cover;border-radius:4px;border:1px solid #ddd;">';
}

// 4c. CSS cho cột trong admin
// -----------------------------------------------
add_action('admin_head', 'vietfarmy_admin_image_column_css');

function vietfarmy_admin_image_column_css() {
    $screen = get_current_screen();
    if (!$screen || $screen->id !== 'edit-product') return;
    echo '<style>
        .column-vietfarmy_product_image { width: 80px; }
        .column-vietfarmy_product_image img { width:60px !important; height:60px !important; }
    </style>';
}

// Fix layout trang Posts (admin list) — ngăn text bị co nhỏ
// -----------------------------------------------
add_action('admin_head', 'vnf_admin_posts_layout_fix');

function vnf_admin_posts_layout_fix() {
    $screen = get_current_screen();
    if (!$screen || $screen->id !== 'edit-post') return;
    echo '<style>
        /* Ngăn các cột bị co nhỏ do SEO plugin column */
        .wp-list-table th.column-author,
        .wp-list-table th.column-categories,
        .wp-list-table th.column-tags,
        .wp-list-table th.column-statistics,
        .wp-list-table th.column-comments {
            white-space: nowrap;
            width: auto;
            min-width: 80px;
        }
        .wp-list-table td.column-author .author-name {
            white-space: nowrap;
        }
        /* SEO Details column — giới hạn chiều rộng */
        .wp-list-table th.column-seo_details,
        .wp-list-table td.column-seo_details {
            max-width: 200px;
            overflow: hidden;
        }
        /* Ngăn post title bị co */
        .wp-list-table .row-title {
            white-space: normal !important;
            max-width: 300px;
            display: inline-block;
        }
    </style>';
}

// 4. Hiển thị ảnh từ URL — Trang danh sách (shop/archive)
// Chạy ở priority 9, thấp hơn default 10, nên in TRƯỚC ảnh mặc định
// -----------------------------------------------
add_action('woocommerce_before_shop_loop_item_title', 'vietfarmy_show_url_image_shop', 9);

function vietfarmy_show_url_image_shop() {
    global $product;
    if (!$product) return;

    $url = get_post_meta($product->get_id(), '_vietfarmy_product_image_url', true);
    if ($url) {
        echo '<div class="vietfarmy-product-image">' .
             '<img src="' . esc_url($url) . '" alt="' . esc_attr($product->get_name()) . '">' .
             '</div>';
    }
}

// 5. CSS cho trang shop/archive
// -----------------------------------------------
add_action('wp_head', 'vietfarmy_url_image_css');

function vietfarmy_url_image_css() {
    if (is_shop() || is_product_category() || is_front_page()) {
        echo '<style>
            .vietfarmy-product-image img {
                width: 100%;
                height: auto;
                display: block;
            }
            .vietfarmy-product-image {
                margin-bottom: 12px;
            }
        </style>';
    }
}

// ============================================================
// ALBUM ẢNH SẢN PHẨM TỪ URL
// ============================================================

// 7. Meta Box Album URL
// -----------------------------------------------
add_action('add_meta_boxes', 'vietfarmy_add_gallery_meta_box');

function vietfarmy_add_gallery_meta_box() {
    add_meta_box(
        'vietfarmy_gallery_urls',
        'Album ảnh sản phẩm từ URL',
        'vietfarmy_gallery_meta_box_callback',
        'product',
        'side',
        'low'
    );
}

function vietfarmy_gallery_meta_box_callback($post) {
    wp_nonce_field('vietfarmy_gallery_save', 'vietfarmy_gallery_nonce');
    $gallery_urls = get_post_meta($post->ID, '_vietfarmy_product_gallery_urls', true);
    if (!is_array($gallery_urls)) $gallery_urls = array();
    ?>
    <style>
        .vngallery-box { background: #fff; padding: 4px; }
        .vngallery-box .vngallery-input-row { display: flex; gap: 6px; align-items: center; margin-bottom: 6px; }
        .vngallery-box input[type="url"] { flex: 1; padding: 7px 9px; border: 1px solid #ccc; border-radius: 4px; font-size: 12px; }
        .vngallery-box input[type="url"]:focus { border-color: #2271b1; outline: none; }
        .vngallery-box .btn-add-url {
            background: #2271b1; color: #fff; border: none; padding: 7px 14px;
            border-radius: 4px; cursor: pointer; font-size: 12px; white-space: nowrap;
        }
        .vngallery-box .btn-add-url:hover { background: #135e96; }
        .vngallery-list { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
        .vngallery-item { position: relative; width: 60px; height: 60px; border-radius: 4px; overflow: hidden; border: 1px solid #ddd; background: #f0f0f1; }
        .vngallery-item img { width: 100%; height: 100%; object-fit: cover; }
        .vngallery-item .btn-remove-url {
            position: absolute; top: 0; right: 0; background: rgba(220,53,69,.85);
            color: #fff; border: none; width: 18px; height: 18px;
            border-radius: 0 0 0 4px; cursor: pointer; font-size: 10px;
            line-height: 18px; text-align: center; padding: 0;
        }
        .vngallery-item .btn-remove-url:hover { background: #dc3545; }
        .vngallery-empty { font-size: 12px; color: #999; padding: 8px 0; }
        .vngallery-hint { font-size: 11px; color: #646970; margin-top: 6px; line-height: 1.4; }
        .vngallery-hint strong { color: #444; }
    </style>

    <div class="vngallery-box">
        <div class="vngallery-input-row">
            <input type="url" id="vngallery_url_input" placeholder="https://example.com/image.jpg">
            <button type="button" class="btn-add-url" id="vngallery_add_btn">+ Thêm</button>
        </div>

        <div class="vngallery-list" id="vngallery_list">
            <?php foreach ($gallery_urls as $index => $img_url) : ?>
                <div class="vngallery-item" data-index="<?php echo $index; ?>">
                    <img src="<?php echo esc_url($img_url); ?>" alt="Thumbnail">
                    <button type="button" class="btn-remove-url" title="Xóa">×</button>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (empty($gallery_urls)) : ?>
            <div class="vngallery-empty" id="vngallery_empty">Chưa có ảnh nào.</div>
        <?php endif; ?>

        <p class="vngallery-hint">📎 Dán URL ảnh → nhấn <strong>+ Thêm</strong> → nhấn <strong>Cập nhật</strong> sản phẩm để lưu.</p>
    </div>

    <!-- Hidden input lưu danh sách URL -->
    <input type="hidden" id="vngallery_urls_json" name="vietfarmy_product_gallery_urls_json" value='<?php echo esc_attr(json_encode($gallery_urls)); ?>'>

    <script>
    (function(){
        var input = document.getElementById('vngallery_url_input');
        var list = document.getElementById('vngallery_list');
        var hiddenInput = document.getElementById('vngallery_urls_json');
        var emptyMsg = document.getElementById('vngallery_empty');

        function getUrls() {
            try { return JSON.parse(hiddenInput.value || '[]'); }
            catch(e) { return []; }
        }

        function saveUrls(urls) {
            hiddenInput.value = JSON.stringify(urls);
            renderList(urls);
        }

        function renderList(urls) {
            if (!list) return;
            list.innerHTML = '';
            if (emptyMsg) emptyMsg.style.display = urls.length ? 'none' : 'block';
            urls.forEach(function(url, i) {
                var item = document.createElement('div');
                item.className = 'vngallery-item';
                item.setAttribute('data-index', i);
                item.innerHTML = '<img src="' + url + '" alt="Thumb"><button type="button" class="btn-remove-url" title="Xóa">×</button>';
                list.appendChild(item);
            });
        }

        // Thêm ảnh
        document.getElementById('vngallery_add_btn').addEventListener('click', function() {
            var val = input.value.trim();
            if (!val) return;
            var urls = getUrls();
            urls.push(val);
            saveUrls(urls);
            input.value = '';
            input.focus();
        });

        // Enter key
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') { e.preventDefault(); document.getElementById('vngallery_add_btn').click(); }
        });

        // Xóa ảnh (delegation)
        list.addEventListener('click', function(e) {
            var btn = e.target.closest('.btn-remove-url');
            if (!btn) return;
            var item = btn.closest('.vngallery-item');
            var idx = parseInt(item.getAttribute('data-index'), 10);
            var urls = getUrls();
            urls.splice(idx, 1);
            saveUrls(urls);
        });

        // Lưu JSON vào hidden input trước submit
        document.querySelector('form#post').addEventListener('submit', function() {
            var urls = getUrls();
            hiddenInput.value = JSON.stringify(urls);
        });
    })();
    </script>
    <?php
}

// 8. Lưu Album URL khi save product
// -----------------------------------------------
add_action('save_post', 'vietfarmy_save_gallery_meta');

function vietfarmy_save_gallery_meta($post_id) {
    if (get_post_type($post_id) !== 'product') return;
    if (!isset($_POST['vietfarmy_gallery_nonce']) || !wp_verify_nonce($_POST['vietfarmy_gallery_nonce'], 'vietfarmy_gallery_save')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    if (isset($_POST['vietfarmy_product_gallery_urls_json'])) {
        $json = stripslashes($_POST['vietfarmy_product_gallery_urls_json']);
        $urls = json_decode($json, true);
        if (is_array($urls)) {
            $urls = array_map('esc_url_raw', $urls);
            update_post_meta($post_id, '_vietfarmy_product_gallery_urls', $urls);
        }
    }
}

// 9. Hiển thị Album URL + Featured Image thành slider trên trang chi tiết sản phẩm
// Bỏ hoàn toàn gallery mặc định của WooCommerce
// -----------------------------------------------
remove_action('woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20);
add_action('woocommerce_before_single_product_summary', 'vietfarmy_product_gallery_slider', 20);

function vietfarmy_product_gallery_slider() {
    global $product;

    $product_id = $product->get_id();

    // 1. Ảnh chính: ưu tiên URL → featured image → placeholder
    $main_url = get_post_meta($product_id, '_vietfarmy_product_image_url', true);
    $featured_id = $product->get_image_id();
    if ($main_url) {
        $main_img = esc_url($main_url);
    } elseif ($featured_id) {
        $main_img = wp_get_attachment_image_url($featured_id, 'large');
    } else {
        $main_img = wc_placeholder_img_src('large');
    }

    // 2. Album URL
    $gallery_urls = get_post_meta($product_id, '_vietfarmy_product_gallery_urls', true);
    if (!is_array($gallery_urls)) $gallery_urls = array();

    // 3. Gallery images từ WC (uploaded)
    $wc_gallery_ids = $product->get_gallery_image_ids();
    if (!is_array($wc_gallery_ids)) $wc_gallery_ids = array();

    // Tổng hợp tất cả ảnh: main + gallery URLs + WC gallery
    $all_images = array();
    $all_images[$main_img] = true; // tránh trùng

    foreach ($gallery_urls as $gurl) {
        if ($gurl) $all_images[esc_url($gurl)] = true;
    }
    foreach ($wc_gallery_ids as $gid) {
        $src = wp_get_attachment_image_url($gid, 'large');
        if ($src) $all_images[$src] = true;
    }

    // Loại bỏ main_img khỏi thumbnails
    unset($all_images[$main_img]);
    $thumbnails = array_keys($all_images);
    ?>
    <style>
    .vngallery-slider { margin-bottom: 20px; }
    .vngallery-main-wrap {
        position: relative; overflow: hidden; border-radius: 8px;
        background: #f8f8f8; cursor: zoom-in;
    }
    .vngallery-main-wrap img { width: 100%; height: auto; display: block; transform-origin: center center; transition: transform .3s ease; }
    .vngallery-main-wrap.zoomed { overflow: hidden; cursor: grab; }
    .vngallery-main-wrap.zoomed img { cursor: grab; }
    .vngallery-main-wrap.zoomed:active img { cursor: grabbing; }

    /* Hover zoom lens */
    .vngallery-lens {
        display: none; position: absolute; border: 2px solid #2271b1;
        border-radius: 50%; box-shadow: 0 4px 12px rgba(0,0,0,.25);
        pointer-events: none; z-index: 10;
    }
    .vngallery-main-wrap:hover .vngallery-lens { display: block; }

    /* Zoom hint badge */
    .vngallery-hint-badge {
        position: absolute; bottom: 10px; right: 10px;
        background: rgba(0,0,0,.55); color: #fff; font-size: 11px;
        padding: 4px 10px; border-radius: 20px; pointer-events: none;
        opacity: .8; letter-spacing: .3px;
    }

    /* Thumbnails */
    .vngallery-thumbs { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; }
    .vngallery-thumb {
        width: 72px; height: 72px; border-radius: 6px; overflow: hidden;
        border: 2px solid transparent; cursor: pointer; opacity: 0.7;
        transition: border-color .2s, opacity .2s; object-fit: cover;
    }
    .vngallery-thumb:hover { opacity: 1; }
    .vngallery-thumb.active { border-color: #2271b1; opacity: 1; }

    /* Lightbox */
    .vngallery-lightbox { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,.92); z-index: 99999; align-items: center; justify-content: center; }
    .vngallery-lightbox.open { display: flex; }
    .vngallery-lightbox .lb-img-wrap { position: relative; display: flex; align-items: center; justify-content: center; max-width: 92vw; max-height: 90vh; }
    .vngallery-lightbox .lb-img {
        max-width: 92vw; max-height: 90vh; border-radius: 6px;
        object-fit: contain; cursor: zoom-in;
    }
    .vngallery-lightbox .lb-img.zoomed { cursor: zoom-out; }
    .vngallery-lightbox .lb-controls { position: absolute; top: 0; left: 0; right: 0; display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; }
    .vngallery-lightbox .lb-counter { color: rgba(255,255,255,.7); font-size: 13px; }
    .vngallery-lightbox .lb-close {
        position: absolute; top: 12px; right: 16px; color: #fff; font-size: 24px;
        cursor: pointer; line-height: 1; background: rgba(255,255,255,.12); border: none;
        width: 36px; height: 36px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
    }
    .vngallery-lightbox .lb-close:hover { background: rgba(255,255,255,.25); }
    .vngallery-lightbox .lb-prev,
    .vngallery-lightbox .lb-next {
        position: absolute; top: 50%; transform: translateY(-50%);
        color: #fff; font-size: 22px; cursor: pointer; background: rgba(255,255,255,.12);
        border: none; width: 44px; height: 44px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
    }
    .vngallery-lightbox .lb-prev { left: 16px; }
    .vngallery-lightbox .lb-next { right: 16px; }
    .vngallery-lightbox .lb-prev:hover,
    .vngallery-lightbox .lb-next:hover { background: rgba(255,255,255,.25); }

    /* Zoom icons */
    .vngallery-lightbox .lb-zoom-hint { position: absolute; bottom: 16px; right: 16px; background: rgba(0,0,0,.5); color: rgba(255,255,255,.7); font-size: 11px; padding: 4px 10px; border-radius: 20px; pointer-events: none; }
    </style>

    <div class="vngallery-slider" id="vngallery_slider">
        <div class="vngallery-main-wrap" id="vngallery_main_wrap">
            <img src="<?php echo $main_img; ?>" alt="<?php echo esc_attr($product->get_name()); ?>" id="vngallery_main_img" data-index="0">
            <div class="vngallery-lens" id="vngallery_lens"></div>
            <div class="vngallery-hint-badge">🔍 Click để phóng to</div>
        </div>

        <?php if (!empty($thumbnails)) : ?>
        <div class="vngallery-thumbs" id="vngallery_thumbs">
            <?php foreach ($thumbnails as $i => $thumb) : ?>
                <img src="<?php echo $thumb; ?>" class="vngallery-thumb" data-src="<?php echo $thumb; ?>" data-index="<?php echo $i + 1; ?>">
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Lightbox overlay -->
    <div class="vngallery-lightbox" id="vngallery_lightbox">
        <div class="lb-controls">
            <span class="lb-counter" id="lb_counter"></span>
            <button class="lb-close" aria-label="Đóng">&#10005;</button>
        </div>
        <button class="lb-prev" aria-label="Trước">&#8249;</button>
        <div class="lb-img-wrap">
            <img src="" class="lb-img" data-idx="0" alt="" id="lb_img">
            <span class="lb-zoom-hint">Click ảnh để zoom</span>
        </div>
        <button class="lb-next" aria-label="Tiếp">&#8250;</button>
    </div>

    <script>
    (function(){
        var allImages = [<?php
            $all_for_js = array($main_img);
            foreach ($thumbnails as $t) $all_for_js[] = $t;
            echo "'" . implode("','", array_map('esc_url', $all_for_js)) . "'";
        ?>];
        var currentIndex = 0;
        var isLightboxZoomed = false;

        // ── Set main image ──────────────────────────────
        function setImage(src, idx) {
            currentIndex = idx;
            var img = document.getElementById('vngallery_main_img');
            if (img) { img.src = src; img.dataset.index = idx; img.style.transform = 'scale(1) translate(0,0)'; }
            document.querySelectorAll('.vngallery-thumb').forEach(function(t) {
                t.classList.toggle('active', parseInt(t.dataset.index) === idx);
            });
        }

        // ── Click thumbnail ──────────────────────────────
        document.querySelectorAll('.vngallery-thumb').forEach(function(t) {
            t.addEventListener('click', function() {
                setImage(this.dataset.src, parseInt(this.dataset.index));
            });
        });

        // ── Hover zoom (lens) ────────────────────────────
        var mainWrap = document.getElementById('vngallery_main_wrap');
        var mainImg  = document.getElementById('vngallery_main_img');
        var lens     = document.getElementById('vngallery_lens');

        if (mainWrap && mainImg && lens) {
            var lensSize = 120, zoomFactor = 2.5;

            mainWrap.addEventListener('mousemove', function(e) {
                var rect = mainWrap.getBoundingClientRect();
                var x = e.clientX - rect.left;
                var y = e.clientY - rect.top;

                lens.style.width = lensSize + 'px';
                lens.style.height = lensSize + 'px';
                lens.style.left = (x - lensSize / 2) + 'px';
                lens.style.top  = (y - lensSize / 2) + 'px';

                var bgX = ((x / rect.width) * 100).toFixed(2);
                var bgY = ((y / rect.height) * 100).toFixed(2);
                lens.style.backgroundImage = "url('" + allImages[currentIndex] + "')";
                lens.style.backgroundSize  = (rect.width * zoomFactor) + 'px ' + (rect.height * zoomFactor) + 'px';
                lens.style.backgroundPosition = (x * zoomFactor - lensSize/2) + 'px ' + (y * zoomFactor - lensSize/2) + 'px';
            });

            mainWrap.addEventListener('mouseleave', function() {
                lens.style.backgroundImage = '';
            });
        }

        // ── Click → zoom / pan on main image ─────────────
        if (mainWrap && mainImg) {
            var isZoomed = false, panX = 0, panY = 0;
            var zoomScale = 2;

            mainWrap.addEventListener('click', function(e) {
                if (e.target.closest('.vngallery-thumbs')) return;

                if (!isZoomed) {
                    isZoomed = true;
                    mainWrap.classList.add('zoomed');
                    mainImg.style.transition = 'none';
                    panX = 0; panY = 0;
                    mainImg.style.transform = 'scale(' + zoomScale + ') translate(' + panX + 'px,' + panY + 'px)';
                } else {
                    isZoomed = false;
                    mainWrap.classList.remove('zoomed');
                    panX = 0; panY = 0;
                    mainImg.style.transition = 'transform .3s ease';
                    mainImg.style.transform = 'scale(1) translate(0,0)';
                }
            });

            var isDragging = false, lastX = 0, lastY = 0;
            mainImg.addEventListener('mousedown', function(e) {
                if (!isZoomed) return;
                isDragging = true; lastX = e.clientX; lastY = e.clientY;
                e.preventDefault();
            });
            document.addEventListener('mousemove', function(e) {
                if (!isDragging || !isZoomed) return;
                var dx = e.clientX - lastX;
                var dy = e.clientY - lastY;
                panX += dx / zoomScale;
                panY += dy / zoomScale;
                // Clamp
                var maxPan = mainImg.offsetWidth * (zoomScale - 1) / 2;
                panX = Math.max(-maxPan, Math.min(maxPan, panX));
                panY = Math.max(-maxPan, Math.min(maxPan, panY));
                lastX = e.clientX; lastY = e.clientY;
                mainImg.style.transition = 'none';
                mainImg.style.transform = 'scale(' + zoomScale + ') translate(' + panX + 'px,' + panY + 'px)';
            });
            document.addEventListener('mouseup', function() { isDragging = false; });
        }

        // ── Lightbox ────────────────────────────────────
        var lb = document.getElementById('vngallery_lightbox');
        var lbImg = document.getElementById('lb_img');
        var lbCounter = document.getElementById('lb_counter');
        var lbZoomed = false, lbPanX = 0, lbPanY = 0;
        var lbZoomScale = 2.5;

        function updateLBCounter() {
            if (lbCounter) lbCounter.textContent = (currentIndex + 1) + ' / ' + allImages.length;
        }

        function openLightbox(idx) {
            if (!lb) return;
            currentIndex = idx;
            lbZoomed = false; lbPanX = 0; lbPanY = 0;
            lbImg.style.transform = 'scale(1) translate(0,0)';
            lbImg.classList.remove('zoomed');
            lbImg.src = allImages[idx];
            lbImg.dataset.idx = idx;
            updateLBCounter();
            lb.classList.add('open');
            document.documentElement.style.overflow = 'hidden';
        }

        function closeLightbox() {
            if (!lb) return;
            lb.classList.remove('open');
            document.documentElement.style.overflow = '';
        }

        function lbSetImage(idx) {
            lbZoomed = false; lbPanX = 0; lbPanY = 0;
            lbImg.style.transform = 'scale(1) translate(0,0)';
            lbImg.classList.remove('zoomed');
            currentIndex = idx;
            lbImg.src = allImages[idx];
            lbImg.dataset.idx = idx;
            updateLBCounter();
        }

        if (lb) {
            lb.querySelector('.lb-close').addEventListener('click', closeLightbox);
            lb.querySelector('.lb-prev').addEventListener('click', function() {
                lbSetImage((parseInt(lbImg.dataset.idx) - 1 + allImages.length) % allImages.length);
            });
            lb.querySelector('.lb-next').addEventListener('click', function() {
                lbSetImage((parseInt(lbImg.dataset.idx) + 1) % allImages.length);
            });
            lb.addEventListener('click', function(e) { if (e.target === lb || e.target.classList.contains('lb-img-wrap')) closeLightbox(); });

            // ESC to close
            document.addEventListener('keydown', function(e) { if (e.key === 'Escape' && lb.classList.contains('open')) closeLightbox(); });
            document.addEventListener('keydown', function(e) {
                if (!lb.classList.contains('open')) return;
                if (e.key === 'ArrowLeft')  lbSetImage((parseInt(lbImg.dataset.idx) - 1 + allImages.length) % allImages.length);
                if (e.key === 'ArrowRight') lbSetImage((parseInt(lbImg.dataset.idx) + 1) % allImages.length);
            });

            // Lightbox zoom on img click
            lbImg.addEventListener('click', function(e) {
                e.stopPropagation();
                if (!lbZoomed) {
                    lbZoomed = true;
                    lbPanX = 0; lbPanY = 0;
                    lbImg.classList.add('zoomed');
                    lbImg.style.transform = 'scale(' + lbZoomScale + ') translate(0,0)';
                } else {
                    lbZoomed = false; lbPanX = 0; lbPanY = 0;
                    lbImg.classList.remove('zoomed');
                    lbImg.style.transition = 'transform .3s ease';
                    lbImg.style.transform = 'scale(1) translate(0,0)';
                }
            });

            // Pan in lightbox zoomed
            var lbDrag = false, lbLastX = 0, lbLastY = 0;
            lbImg.addEventListener('mousedown', function(e) {
                if (!lbZoomed) return;
                lbDrag = true; lbLastX = e.clientX; lbLastY = e.clientY; e.preventDefault();
            });
            document.addEventListener('mousemove', function(e) {
                if (!lbDrag || !lbZoomed) return;
                var dx = e.clientX - lbLastX;
                var dy = e.clientY - lbLastY;
                lbPanX += dx / lbZoomScale; lbPanY += dy / lbZoomScale;
                var maxPan = lbImg.offsetWidth * (lbZoomScale - 1) / 2;
                lbPanX = Math.max(-maxPan, Math.min(maxPan, lbPanX));
                lbPanY = Math.max(-maxPan, Math.min(maxPan, lbPanY));
                lbLastX = e.clientX; lbLastY = e.clientY;
                lbImg.style.transition = 'none';
                lbImg.style.transform = 'scale(' + lbZoomScale + ') translate(' + lbPanX + 'px,' + lbPanY + 'px)';
            });
            document.addEventListener('mouseup', function() { lbDrag = false; });
        }

        // Open lightbox from main image
        if (mainWrap) {
            mainWrap.addEventListener('dblclick', function() { openLightbox(currentIndex); });
        }
    })();
    </script>
    <?php
}

// 10. CSS cho album gallery
// -----------------------------------------------
add_action('wp_head', 'vietfarmy_gallery_css');

function vietfarmy_gallery_css() {
    if (is_product()) {
        echo '<style>
            .vngallery-slider { margin-bottom: 20px; }
        </style>';
    }
}

// ============================================================
// FEATURED IMAGE TỪ URL — CHO POST (BÀI VIẾT)
// ============================================================

// 1. Thêm Meta Box nhập URL ảnh đại diện cho post
add_action('add_meta_boxes', 'vnf_post_add_image_meta_box');

function vnf_post_add_image_meta_box() {
    add_meta_box(
        'vnf_post_image_url',
        'Ảnh đại diện từ URL',
        'vnf_post_image_meta_box_callback',
        'post',
        'side',
        'low'
    );
}

function vnf_post_image_meta_box_callback($post) {
    wp_nonce_field('vnf_post_image_save', 'vnf_post_image_nonce');
    $url = get_post_meta($post->ID, '_vnf_post_image_url', true);
    ?>
    <style>
        .vnf-post-url-box .url-input-row { display: flex; gap: 6px; align-items: center; margin-bottom: 8px; }
        .vnf-post-url-box input[type="url"] { flex: 1; padding: 8px 10px; border: 1px solid #ccc; border-radius: 4px; font-size: 13px; }
        .vnf-post-url-box input[type="url"]:focus { border-color: #2271b1; outline: none; }
        .vnf-post-url-box .preview-box { margin-top: 8px; background: #f0f0f1; padding: 8px; border-radius: 4px; text-align: center; }
        .vnf-post-url-box .preview-box img { max-width: 100%; border-radius: 4px; border: 1px solid #ddd; max-height: 150px; object-fit: cover; }
        .vnf-post-url-box .hint { font-size: 12px; color: #646970; margin-top: 8px; line-height: 1.5; }
    </style>
    <div class="vnf-post-url-box">
        <div class="url-input-row">
            <input type="url" id="vnf_post_image_url" name="vnf_post_image_url" value="<?php echo esc_url($url); ?>" placeholder="https://example.com/image.jpg">
        </div>
        <?php if ($url) : ?>
            <div class="preview-box">
                <img src="<?php echo esc_url($url); ?>" alt="Preview">
            </div>
        <?php endif; ?>
        <p class="hint">📎 Dán link ảnh từ web bất kỳ. Ảnh sẽ hiển thị thay cho ảnh đại diện mặc định. Không cần upload lên host.</p>
    </div>
    <?php
}

// 2. Lưu URL khi save post
add_action('save_post', 'vnf_post_save_image_meta');

function vnf_post_save_image_meta($post_id) {
    if (get_post_type($post_id) !== 'post') return;
    if (!isset($_POST['vnf_post_image_nonce']) || !wp_verify_nonce($_POST['vnf_post_image_nonce'], 'vnf_post_image_save')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;
    if (isset($_POST['vnf_post_image_url'])) {
        update_post_meta($post_id, '_vnf_post_image_url', esc_url_raw($_POST['vnf_post_image_url']));
    }
}

// ============================================================
// FEATURED IMAGE TỪ URL — CHO PAGE
// ============================================================
add_action('add_meta_boxes', 'vnf_page_add_image_meta_box');

function vnf_page_add_image_meta_box() {
    add_meta_box(
        'vnf_page_image_url',
        'Ảnh đại diện từ URL',
        'vnf_page_image_meta_box_callback',
        'page',
        'side',
        'low'
    );
}

function vnf_page_image_meta_box_callback($post) {
    wp_nonce_field('vnf_page_image_save', 'vnf_page_image_nonce');
    $url = get_post_meta($post->ID, '_vnf_post_image_url', true);
    ?>
    <style>
        .vnf-post-url-box .url-input-row { display: flex; gap: 6px; align-items: center; margin-bottom: 8px; }
        .vnf-post-url-box input[type="url"] { flex: 1; padding: 8px 10px; border: 1px solid #ccc; border-radius: 4px; font-size: 13px; }
        .vnf-post-url-box .preview-box { margin-top: 8px; background: #f0f0f1; padding: 8px; border-radius: 4px; text-align: center; }
        .vnf-post-url-box .preview-box img { max-width: 100%; border-radius: 4px; border: 1px solid #ddd; max-height: 150px; object-fit: cover; }
        .vnf-post-url-box .hint { font-size: 12px; color: #646970; margin-top: 8px; line-height: 1.5; }
    </style>
    <div class="vnf-post-url-box">
        <div class="url-input-row">
            <input type="url" id="vnf_page_image_url" name="vnf_post_image_url" value="<?php echo esc_url($url); ?>" placeholder="https://example.com/image.jpg">
        </div>
        <?php if ($url) : ?>
            <div class="preview-box">
                <img src="<?php echo esc_url($url); ?>" alt="Preview">
            </div>
        <?php endif; ?>
        <p class="hint">📎 Dán link ảnh từ web bất kỳ.</p>
    </div>
    <?php
}

add_action('save_post', 'vnf_page_save_image_meta');

function vnf_page_save_image_meta($post_id) {
    if (get_post_type($post_id) !== 'page') return;
    if (!isset($_POST['vnf_page_image_nonce']) || !wp_verify_nonce($_POST['vnf_page_image_nonce'], 'vnf_page_image_save')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;
    if (isset($_POST['vnf_post_image_url'])) {
        update_post_meta($post_id, '_vnf_post_image_url', esc_url_raw($_POST['vnf_post_image_url']));
    }
}

if ( ! function_exists( 'gema_setup' ) ) :/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */

	function gema_setup() {
		/*
		 * Make theme available for translation.
		 * Translations can be filed in the /languages/ directory.
		 * If you're building a theme based on Gema, use a find and replace
		 * to change 'gema' to the name of your theme in all the template files.
		 */
		load_theme_textdomain( 'gema', get_template_directory() . '/languages' );

		// Add default posts and comments RSS feed links to head.
		add_theme_support( 'automatic-feed-links' );

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */
		add_theme_support( 'title-tag' );

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link http://codex.wordpress.org/Function_Reference/add_theme_support#Post_Thumbnails
		 */
		add_theme_support( 'post-thumbnails' );

		//used as featured image for posts on home page and archive pages
		add_image_size( 'gema-super-small', 10, 10, false );
		add_image_size( 'gema-archive-landscape', 432, 9999, false );
		add_image_size( 'gema-archive-portrait', 396, 9999, false );

		//used for the single post featured image
		add_image_size( 'gema-single-landscape', 1120, 9999, false );
		add_image_size( 'gema-single-portrait', 660, 9999, false );

		// This theme uses wp_nav_menu() in one location.
		register_nav_menus( array(
			'primary' => esc_html__( 'Primary Menu', 'gema' ),
			'footer'  => esc_html__( 'Footer Menu', 'gema' ),
		) );
		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support( 'html5', array(
			'search-form',
			'comment-form',
			'gallery',
			'caption',
		) );

		/*
		 * Enable support for custom logo.
		 *
		 *  @since Gema 1.0
		 */
		add_theme_support( 'custom-logo', array(
			'height'      => 220,
			'width'       => 710,
			'flex-height' => true,
			'flex-width'  => true,
			'header-text' => array(
				'site-title',
				'site-description-text',
			)
		) );

		if ( ! function_exists( 'the_custom_logo' ) ) {
			//in case we are on a WP version older than 4.5, try to use Jetpack's Site Logo feature
			add_theme_support( 'site-logo', array(
				'size'        => 'gema-site-logo',
				'header-text' => array(
					'site-title',
					'site-description-text',
				)
			) );
		}

		add_image_size( 'gema-site-logo', 710, 220, false );

		/*
		 * Enable support for Post Formats.
		 * See https://developer.wordpress.org/themes/functionality/post-formats/
		 */
		add_theme_support( 'post-formats', array( 'quote', 'gallery', 'video', 'audio', 'image', 'link' ) );

		/*
		 * Add editor styles and fonts
		 */
		add_editor_style( array( 'editor-style.css' ) );
		add_editor_style( array( gema_google_fonts_url() ) );

		/*
		 * Enable support for Visible Edit Shortcuts in the Customizer Preview
		 *
		 * @link https://make.wordpress.org/core/2016/11/10/visible-edit-shortcuts-in-the-customizer-preview/
		 */
		add_theme_support( 'customize-selective-refresh-widgets' );

		/**
		 * Enable support for the Style Manager Customizer section (via Customify).
		 */
		add_theme_support( 'customizer_style_manager' );
		add_theme_support( 'style_manager_font_palettes' );

		/**
		 * Add support for wide and full aligned blocks
		 */
		add_theme_support( 'align-wide' );
	}
endif; // gema_setup

add_action('after_setup_theme', 'gema_setup');

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function gema_content_width() {
	$GLOBALS['content_width'] = apply_filters('gema_content_width', 720);
}

add_action('after_setup_theme', 'gema_content_width', 0);

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function gema_widgets_init() {
	register_sidebar(array(
		'name' => esc_html__('Sidebar', 'gema'),
		'id' => 'sidebar-1',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => '</aside>',
		'before_title' => '<h2 class="widget__title">',
		'after_title' => '</h2>',
	));
}

add_action('widgets_init', 'gema_widgets_init');

/**
 * Enqueue scripts and styles.
 */
function gema_scripts() {
	/* The main theme stylesheet */
	if( !is_rtl() ) wp_enqueue_style( 'gema-style', get_stylesheet_uri() );

	/* Default Self-hosted Fonts */
	wp_enqueue_style( 'gema-fonts-butler', gema_butler_font_url() );

	wp_enqueue_style( 'gema-google-fonts', gema_google_fonts_url() );

	wp_enqueue_script('bricklayer', get_template_directory_uri() . '/js/bricklayer.js', array(), '20170421', true);
	wp_enqueue_script('gema-modernizr', get_template_directory_uri() . '/js/modernizr-custom.js', array(), '20160322', true);
	wp_enqueue_script('gema-skip-link-focus-fix', get_template_directory_uri() . '/js/skip-link-focus-fix.js', array(), '20160126', true);

	/* Enqueue the main theme script file */
	wp_enqueue_script( 'gema-scripts', get_template_directory_uri() . '/assets/js/main.js', array( 'jquery', 'bricklayer', 'imagesloaded' ), '1.1.5.1', true );

	if (is_singular() && comments_open() && get_option('thread_comments')) {
		wp_enqueue_script('comment-reply');
	}
}

add_action( 'wp_enqueue_scripts', 'gema_scripts' );

function gema_gutenberg_styles() {
	wp_enqueue_style( 'gema-gutenberg', get_theme_file_uri( '/editor.css' ), false );

	$padding = 60;
	$sidebar = 400;
	$width = pixelgrade_option( 'main_content_content_width' ) - $sidebar - $padding;

	$style = '
	    .edit-post-visual-editor[class][class] .block-editor-block-list__layout .wp-block:not([data-align="wide"]):not([data-align="full"]),
        .edit-post-visual-editor[class][class] .editor-post-title__block {
            max-width: ' . $width . 'px;
        }';
	wp_add_inline_style( 'gema-gutenberg', $style );

}

add_action( 'enqueue_block_editor_assets', 'gema_gutenberg_styles' );


/**
 * Add custom image sizes attribute to enhance responsive image functionality
 * for content images
 *
 * @since Gema 1.0.0
 *
 * @param string $sizes A source size value for use in a 'sizes' attribute.
 * @param array  $size  Image size. Accepts an array of width and height
 *                      values in pixels (in that order).
 * @return string A source size value for use in a content image 'sizes' attribute.
 */
function gema_content_image_sizes_attr( $sizes, $size ) {
	$sizes = '(max-width: 600px) 91vw, (max-width: 900px) 600px, (max-width: 1060px) 50vw, (max-width: 1200px) 520px, (max-width: 1400px) 43vw, 600px';
	return $sizes;
}
add_filter( 'wp_calculate_image_sizes', 'gema_content_image_sizes_attr', 10 , 2 );

/**
 * Add custom image sizes attribute to enhance responsive image functionality
 * for post thumbnails
 *
 * @since Gema 1.0.0
 *
 * @param array $attr Attributes for the image markup.
 * @param int   $attachment Image attachment ID.
 * @param array $size Registered image size or flat array of height and width dimensions.
 * @return array
 */
function gema_post_thumbnail_sizes_attr( $attr, $attachment, $size ) {
	switch ($size) {
		case 'gema-single-landscape':
		case 'gema-single-portrait':
			$attr['sizes'] = '(max-width: 900px) 100vw, (max-width: 1260px) 920px, 1060px';
			break;
		case 'gema-portrait':
			$attr['sizes'] = '(max-width: 470px) 100vw, 432px';
			break;
		case 'gema-landscape':
			$attr['sizes'] = '(max-width: 470px) 100vw, 396px';
			break;
		default:
			break;
	}
	return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'gema_post_thumbnail_sizes_attr', 10 , 3 );

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Load Recommended/Required plugins notification
 */
require get_template_directory() . '/inc/required-plugins/required-plugins.php';

/**
 * Custom functions that act independently of the theme templates.
 */
require get_template_directory() . '/inc/extras.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
require get_template_directory() . '/inc/jetpack.php';

/**
 * Load the Hybrid Media Grabber class
 */
require get_template_directory() . '/inc/hybrid-media-grabber.php';

/* Automagical updates */
function gema_wupdates_check_ML4Gm( $transient ) {
	// First get the theme directory name (the theme slug - unique)
	$slug = basename( get_template_directory() );

	// Nothing to do here if the checked transient entry is empty or if we have already checked
	if ( empty( $transient->checked ) || empty( $transient->checked[ $slug ] ) || ! empty( $transient->response[ $slug ] ) ) {
		return $transient;
	}

	// Let's start gathering data about the theme
	// Then WordPress version
	include( ABSPATH . WPINC . '/version.php' );
	$http_args = array (
		'body' => array(
			'slug' => $slug,
			'url' => home_url( '/' ), //the site's home URL
			'version' => 0,
			'locale' => get_locale(),
			'phpv' => phpversion(),
			'child_theme' => is_child_theme(),
			'data' => null, //no optional data is sent by default
		),
		'user-agent' => 'WordPress/' . $wp_version . '; ' . home_url( '/' )
	);

	// If the theme has been checked for updates before, get the checked version
	if ( isset( $transient->checked[ $slug ] ) && $transient->checked[ $slug ] ) {
		$http_args['body']['version'] = $transient->checked[ $slug ];
	}

	// Use this filter to add optional data to send
	// Make sure you return an associative array - do not encode it in any way
	$optional_data = apply_filters( 'wupdates_call_data_request', $http_args['body']['data'], $slug, $http_args['body']['version'] );

	// Encrypting optional data with private key, just to keep your data a little safer
	// You should not edit the code bellow
	$optional_data = json_encode( $optional_data );
	$w=array();$re="";$s=array();$sa=md5('598f55c897d6388b8b4679bf26c2f0d8fab53aad');
	$l=strlen($sa);$d=$optional_data;$ii=-1;
	while(++$ii<256){$w[$ii]=ord(substr($sa,(($ii%$l)+1),1));$s[$ii]=$ii;} $ii=-1;$j=0;
	while(++$ii<256){$j=($j+$w[$ii]+$s[$ii])%255;$t=$s[$j];$s[$ii]=$s[$j];$s[$j]=$t;}
	$l=strlen($d);$ii=-1;$j=0;$k=0;
	while(++$ii<$l){$j=($j+1)%256;$k=($k+$s[$j])%255;$t=$w[$j];$s[$j]=$s[$k];$s[$k]=$t;
		$x=$s[(($s[$j]+$s[$k])%255)];$re.=chr(ord($d[$ii])^$x);}
	$optional_data=bin2hex($re);

	// Save the encrypted optional data so it can be sent to the updates server
	$http_args['body']['data'] = $optional_data;

	// Check for an available update
	$url = $http_url = set_url_scheme( 'https://wupdates.com/wp-json/wup/v1/themes/check_version/ML4Gm', 'http' );
	if ( $ssl = wp_http_supports( array( 'ssl' ) ) ) {
		$url = set_url_scheme( $url, 'https' );
	}

	$raw_response = wp_remote_post( $url, $http_args );
	if ( $ssl && is_wp_error( $raw_response ) ) {
		$raw_response = wp_remote_post( $http_url, $http_args );
	}
	// We stop in case we haven't received a proper response
	if ( is_wp_error( $raw_response ) || 200 != wp_remote_retrieve_response_code( $raw_response ) ) {
		return $transient;
	}

	$response = (array) json_decode($raw_response['body']);
	if ( ! empty( $response ) ) {
		// You can use this action to show notifications or take other action
		do_action( 'wupdates_before_response', $response, $transient );
		if ( isset( $response['allow_update'] ) && $response['allow_update'] && isset( $response['transient'] ) ) {
			$transient->response[ $slug ] = (array) $response['transient'];
		}
		do_action( 'wupdates_after_response', $response, $transient );
	}

	return $transient;
}
add_filter( 'pre_set_site_transient_update_themes', 'gema_wupdates_check_ML4Gm' );

function gema_wupdates_add_id_ML4Gm( $ids = array() ) {
	// First get the theme directory name (unique)
	$slug = basename( get_template_directory() );

	// Now add the predefined details about this product
	// Do not tamper with these please!!!
	$ids[ $slug ] = array( 'name' => 'Gema', 'slug' => 'gema', 'id' => 'ML4Gm', 'type' => 'theme', 'digest' => '1397d3087860bfd8d462ebbce25090cb', );

	return $ids;
}
add_filter( 'wupdates_gather_ids', 'gema_wupdates_add_id_ML4Gm', 10, 1 );

// ============================================================
// OPEN GRAPH META TAGS — Facebook / Messenger / Zalo Share
// ============================================================
add_action('wp_head', 'vnf_open_graph_meta', 1);

// ============================================================
// CSS FIX — POST DETAIL LAYOUT (Featured Image canh giữa)
// ============================================================
add_action('wp_head', 'vnf_post_detail_css', 1);

function vnf_post_detail_css() {
    if (!is_singular('post')) return;
    echo '<style>
    /* Fix layout ảnh featured — canh giữa */
    .post-type-post.singular .entry-featured {
        float: none !important;
        max-width: 720px !important;
        width: 100% !important;
        position: static !important;
        margin: 0 auto 24px auto !important;
        padding: 0 !important;
        box-sizing: border-box !important;
    }
    .post-type-post.singular .entry-featured img,
    .post-type-post.singular .entry-featured .vnf-single-img {
        width: 100% !important;
        max-width: 100% !important;
        display: block !important;
        height: auto !important;
    }
    /* Bỏ absolutely-positioned từ theme Gema */
    .post-type-post.singular.has-featured-image .post__content {
        clear: both !important;
        padding-top: 0 !important;
        margin-top: 0 !important;
    }
    .post-type-post.singular.has-featured-image .entry-header {
        position: static !important;
        padding-top: 0 !important;
    }
    /* Ẩn khoảng trắng khi không có ảnh */
    .post-type-post.singular.no-featured-image .entry-featured {
        display: none !important;
    }
    </style>';
}

function vnf_open_graph_meta() {
    if (!is_singular() && !is_front_page()) return;

    global $post;
    $post_id = 0;

    if (is_singular() && !empty($post)) {
        $post_id = $post->ID;
    } elseif (is_front_page()) {
        $blog_page = get_option('page_for_posts');
        $post_id = $blog_page ? $blog_page : 0;
    }

    // --- Homepage không có blog page ---
    if (!$post_id) {
        if (!is_front_page()) return;

        $logo = get_theme_mod('vnf_header_logo', '');
        echo "\n";
        echo '<meta property="og:type" content="website" />' . "\n";
        echo '<meta property="og:title" content="' . esc_attr(get_bloginfo('name')) . '" />' . "\n";
        echo '<meta property="og:url" content="' . esc_url(home_url('/')) . '" />' . "\n";
        echo '<meta property="og:site_name" content="' . esc_attr(get_bloginfo('name')) . '" />' . "\n";
        echo '<meta property="og:description" content="' . esc_attr(wp_strip_all_tags(get_bloginfo('description'))) . '" />' . "\n";
        if ($logo) {
            echo '<meta property="og:image" content="' . esc_url($logo) . '" />' . "\n";
        }
        echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
        return;
    }

    // --- TITLE & URL ---
    $title = get_the_title($post_id) . ' — ' . get_bloginfo('name');
    $url   = get_permalink($post_id);

    // --- IMAGE: 4 bước ưu tiên ---
    $image = '';

    // 1. Custom URL meta (_vnf_post_image_url)
    $custom_url = get_post_meta($post_id, '_vnf_post_image_url', true);
    if (!empty(trim($custom_url))) {
        $image = trim($custom_url);
    }

    // 2. Featured Image upload lên WordPress
    if (empty($image) && has_post_thumbnail($post_id)) {
        $thumb_url = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'large');
        if (!empty($thumb_url[0])) {
            $image = $thumb_url[0];
        }
    }

    // 3. First image trong content — QUAN TRỌNG NHẤT cho site này
    if (empty($image)) {
        $content = get_post_field('post_content', $post_id);
        if (!empty($content)) {
            preg_match_all('/<img[^>]+src=["\']([^"\']+)["\']/i', $content, $matches);
            foreach ((array) $matches[1] as $img_src) {
                $img_lower = strtolower($img_src);
                // Bỏ qua ảnh nhỏ, logo, icon, tracking
                if (
                    strpos($img_lower, 'logo') === false &&
                    strpos($img_lower, 'icon') === false &&
                    strpos($img_lower, 'pixel') === false &&
                    strpos($img_lower, 'tracking') === false &&
                    strpos($img_lower, '.gif') === false &&
                    strlen($img_src) > 20
                ) {
                    // Loại bỏ query string resize/cdn
                    $clean = preg_replace('/[?&](w=|h=|resize=|ssl=).*$/', '', $img_src);
                    $image = $clean;
                    break;
                }
            }
        }
    }

    // 4. Fallback: logo
    if (empty($image)) {
        $logo_url = get_theme_mod('vnf_header_logo', '');
        if (!empty($logo_url)) $image = $logo_url;
    }

    // --- DESCRIPTION ---
    $desc = get_the_excerpt($post_id);
    if (empty(trim($desc))) {
        $raw = get_post_field('post_content', $post_id);
        $raw = wp_strip_all_tags($raw);
        $raw = preg_replace('/\s+/', ' ', $raw);
        $desc = mb_substr($raw, 0, 160);
    }
    $desc = wp_strip_all_tags($desc);

    // --- HTTPS cho og:image ---
    $image_secure = str_replace('http://', 'https://', $image);

    echo "\n";
    echo '<meta property="og:type" content="article" />' . "\n";
    echo '<meta property="og:title" content="' . esc_attr($title) . '" />' . "\n";
    echo '<meta property="og:url" content="' . esc_url($url) . '" />' . "\n";
    echo '<meta property="og:site_name" content="' . esc_attr(get_bloginfo('name')) . '" />' . "\n";
    if ($desc) {
        echo '<meta property="og:description" content="' . esc_attr($desc) . '" />' . "\n";
    }
    if ($image) {
        echo '<meta property="og:image" content="' . esc_url($image) . '" />' . "\n";
        echo '<meta property="og:image:secure_url" content="' . esc_url($image_secure) . '" />' . "\n";
        echo '<meta property="og:image:width" content="1200" />' . "\n";
        echo '<meta property="og:image:height" content="630" />' . "\n";
    }
    echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
    if ($image) {
        echo '<meta name="twitter:image" content="' . esc_url($image) . '" />' . "\n";
    }
    echo '<meta name="twitter:title" content="' . esc_attr($title) . '" />' . "\n";
    if ($desc) {
        echo '<meta name="twitter:description" content="' . esc_attr($desc) . '" />' . "\n";
    }
    echo "\n";
}

/**
* Various plugins integrations.
*/
require get_template_directory() . '/inc/integrations.php';

// Load VietFarmy modules
require get_template_directory() . '/inc/vnf/process-timeline.php';

// ============================================================
// CUSTOMIZER — Header Zone (VietFarmy)
// ============================================================
add_action('customize_register', 'vnf_customize_register');

function vnf_customize_register($wp_customize) {

    $wp_customize->add_section('vnf_header', array(
        'title'    => 'Header Zone (VietFarmy)',
        'priority' => 30,
    ));

    // Logo
    $wp_customize->add_setting('vnf_header_logo', array(
        'type'              => 'theme_mod',
        'transport'         => 'refresh',
        'sanitize_callback' => 'esc_url_raw',
    ));
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'vnf_header_logo', array(
        'label'    => 'Logo',
        'section'  => 'vnf_header',
    )));

    // Banner
    $wp_customize->add_setting('vnf_header_banner', array(
        'type'              => 'theme_mod',
        'transport'         => 'refresh',
        'sanitize_callback' => 'esc_url_raw',
    ));
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'vnf_header_banner', array(
        'label'    => 'Banner',
        'section'  => 'vnf_header',
    )));

    // Show banner
    $wp_customize->add_setting('vnf_header_show_banner', array(
        'type'              => 'theme_mod',
        'transport'         => 'refresh',
        'sanitize_callback' => 'absint',
    ));
    $wp_customize->add_control('vnf_header_show_banner', array(
        'label'    => 'Hiển thị Banner',
        'section'  => 'vnf_header',
        'type'     => 'checkbox',
    ));

    // Banner link
    $wp_customize->add_setting('vnf_header_banner_link', array(
        'type'              => 'theme_mod',
        'transport'         => 'refresh',
        'sanitize_callback' => 'esc_url_raw',
    ));
    $wp_customize->add_control('vnf_header_banner_link', array(
        'label'    => 'Liên kết Banner (URL)',
        'section'  => 'vnf_header',
        'type'     => 'text',
    ));

    // ── Menu Items ──
    $wp_customize->add_setting('vnf_header_menu_items', array(
        'type'              => 'theme_mod',
        'transport'         => 'refresh',
        'sanitize_callback' => function($val) {
            // Chỉ sanitize từng dòng, giữ nguyên \n và |
            $lines = preg_split('/\r?\n/', $val);
            $clean = array();
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                $parts = explode('|', $line, 2);
                $label = isset($parts[0]) ? sanitize_text_field(trim($parts[0])) : '';
                $url = isset($parts[1]) ? esc_url_raw(trim($parts[1])) : '#';
                if (!empty($label)) $clean[] = $label . ' | ' . $url;
            }
            return implode("\n", $clean);
        },
        'default'           => '',
    ));
    $wp_customize->add_control('vnf_header_menu_items', array(
        'label'       => 'Menu Items',
        'description' => 'Mỗi dòng: <strong>Tên | URL</strong><br>Ví dụ:<br>Trang chủ | /<br>Sản phẩm | /products',
        'section'     => 'vnf_header',
        'type'        => 'textarea',
    ));

    // ── Social Media ──
    $wp_customize->add_section('vnf_social', array(
        'title'    => 'Mạng xã hội (VietFarmy)',
        'priority' => 35,
    ));

    $social_fields = array(
        'vnf_social_facebook'  => 'Facebook URL',
        'vnf_social_instagram' => 'Instagram URL',
        'vnf_social_zalo'      => 'Zalo URL',
        'vnf_social_youtube'   => 'YouTube URL',
    );

    foreach ($social_fields as $key => $label) {
        $wp_customize->add_setting($key, array(
            'type'              => 'theme_mod',
            'transport'         => 'refresh',
            'sanitize_callback' => 'esc_url_raw',
        ));
        $wp_customize->add_control($key, array(
            'label'    => $label,
            'section'  => 'vnf_social',
            'type'     => 'url',
        ));
    }

    // ── Sản phẩm nổi bật ──
    $wp_customize->add_section('vnf_featured', array(
        'title'    => 'Sản phẩm nổi bật (VietFarmy)',
        'priority' => 30,
    ));

    $fp1 = 'vnf_featured_product_1'; $fp2 = 'vnf_featured_product_2'; $fp3 = 'vnf_featured_product_3';
    foreach (array($fp1 => 'Sản phẩm #1', $fp2 => 'Sản phẩm #2', $fp3 => 'Sản phẩm #3') as $key => $label) {
        $wp_customize->add_setting($key, array('type' => 'theme_mod', 'transport' => 'refresh', 'sanitize_callback' => 'absint'));
        $wp_customize->add_control($key, array('label' => "$label (ID sản phẩm)", 'section' => 'vnf_featured', 'type' => 'number'));
    }

    // ── Bài viết nổi bật ──
    $wp_customize->add_section('vnf_featured_posts', array('title' => 'Bài viết nổi bật (VietFarmy)', 'priority' => 31));
    foreach (array('vnf_featured_post_1' => 'Bài viết #1', 'vnf_featured_post_2' => 'Bài viết #2', 'vnf_featured_post_3' => 'Bài viết #3') as $key => $label) {
        $wp_customize->add_setting($key, array('type' => 'theme_mod', 'transport' => 'refresh', 'sanitize_callback' => 'absint'));
        $wp_customize->add_control($key, array('label' => "$label (ID bài viết)", 'section' => 'vnf_featured_posts', 'type' => 'number'));
    }
}
