<?php
/**
 * Frontend display for VietFarmy Gallery
 */

if (!defined('ABSPATH')) exit;

add_shortcode('vnf_gallery', 'vnf_gl_shortcode');

function vnf_gl_shortcode($atts) {
    $atts = shortcode_atts(array(
        'id'      => '0',
        'slug'    => '',
        'ids'     => '',       // Danh sách ID ảnh: 1,2,3,4
        'layout'  => 'grid',
        'columns' => '4',
        'size'    => 'medium', // thumbnail, medium, large, full
    ), $atts, 'vnf_gallery');

    $images = array();
    $settings = array();
    $gallery_uid = 'vgl-' . substr(md5(uniqid()), 0, 8);

    // Ưu tiên: ids="1,2,3" > id="1" > slug="abc"
    if (!empty($atts['ids'])) {
        // Lấy ảnh theo danh sách ID
        $image_ids = array_filter(array_map('intval', explode(',', $atts['ids'])));
        if (!empty($image_ids)) {
            $images = vnf_gl_get_images_by_ids($image_ids);
        }
    } elseif (!empty($atts['id']) && is_numeric($atts['id'])) {
        $gallery_id = (int) $atts['id'];
        $images = vnf_gl_get_images($gallery_id);
        $gallery_data = vnf_gl_get_gallery_data($gallery_id);
        $settings = vnf_gl_get_settings($gallery_id);
        if ($gallery_data) {
            $atts['layout'] = $gallery_data->layout;
            $atts['columns'] = $gallery_data->columns;
        }
    } elseif (!empty($atts['slug'])) {
        $gallery_id = vnf_gl_get_gallery_id_by_slug($atts['slug']);
        if ($gallery_id) {
            $images = vnf_gl_get_images($gallery_id);
            $gallery_data = vnf_gl_get_gallery_data($gallery_id);
            $settings = vnf_gl_get_settings($gallery_id);
            if ($gallery_data) {
                $atts['layout'] = $gallery_data->layout;
                $atts['columns'] = $gallery_data->columns;
            }
        }
    }

    if (empty($images)) return '';

    $defaults = array(
        'spacing'       => 8,
        'border_radius' => 4,
        'show_title'    => 0,
        'show_desc'    => 0,
        'lightbox'      => 1,
        'lazyload'      => 1,
    );
    $settings = wp_parse_args($settings, $defaults);

    $layout  = sanitize_key($atts['layout']);
    $columns = max(1, min(6, (int) $atts['columns']));
    $size    = sanitize_key($atts['size']);
    $spacing = (int) $settings['spacing'];
    $radius  = (int) $settings['border_radius'];

    ob_start();
    ?>
    <style>
    .vnf-gl-wrap-<?php echo $gallery_uid; ?> {
        --vgl-spacing: <?php echo $spacing; ?>px;
        --vgl-radius: <?php echo $radius; ?>px;
    }
    .vnf-gl-wrap-<?php echo $gallery_uid; ?>.vgl-layout-grid {
        display: grid;
        grid-template-columns: repeat(<?php echo $columns; ?>, 1fr);
        gap: var(--vgl-spacing);
    }
    .vnf-gl-wrap-<?php echo $gallery_uid; ?>.vgl-layout-masonry {
        column-count: <?php echo $columns; ?>;
        column-gap: var(--vgl-spacing);
    }
    .vnf-gl-wrap-<?php echo $gallery_uid; ?>.vgl-layout-masonry .vgl-item {
        break-inside: avoid;
        margin-bottom: var(--vgl-spacing);
    }
    .vnf-gl-wrap-<?php echo $gallery_uid; ?> .vgl-item {
        position: relative;
        border-radius: var(--vgl-radius);
        overflow: hidden;
        background: #f0f0f0;
    }
    .vnf-gl-wrap-<?php echo $gallery_uid; ?> .vgl-item img {
        width: 100%;
        height: auto;
        display: block;
        border-radius: var(--vgl-radius);
        transition: transform .4s ease, filter .4s ease;
    }
    .vnf-gl-wrap-<?php echo $gallery_uid; ?> .vgl-item:hover img {
        transform: scale(1.05);
        filter: brightness(1.05);
    }
    .vnf-gl-wrap-<?php echo $gallery_uid; ?> .vgl-item-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(transparent 50%, rgba(0,0,0,.6));
        opacity: 0;
        transition: opacity .3s ease;
        display: flex;
        align-items: flex-end;
        padding: var(--vgl-spacing);
    }
    .vnf-gl-wrap-<?php echo $gallery_uid; ?> .vgl-item:hover .vgl-item-overlay {
        opacity: 1;
    }
    .vnf-gl-wrap-<?php echo $gallery_uid; ?> .vgl-item-title {
        color: #fff;
        font-size: 14px;
        font-weight: 600;
        text-shadow: 0 1px 3px rgba(0,0,0,.5);
    }
    .vnf-gl-wrap-<?php echo $gallery_uid; ?> .vgl-item-desc {
        color: rgba(255,255,255,.85);
        font-size: 12px;
        margin-top: 2px;
    }
    .vnf-gl-wrap-<?php echo $gallery_uid; ?>.vgl-layout-slider {
        display: flex;
        overflow-x: auto;
        gap: var(--vgl-spacing);
        scroll-snap-type: x mandatory;
        padding-bottom: 10px;
    }
    .vnf-gl-wrap-<?php echo $gallery_uid; ?>.vgl-layout-slider .vgl-item {
        flex: 0 0 calc((100% - var(--vgl-spacing) * <?php echo max(0, $columns - 1); ?>) / <?php echo $columns; ?>);
        scroll-snap-align: start;
    }
    .vnf-gl-wrap-<?php echo $gallery_uid; ?> a[data-lightbox] {
        display: block;
    }
    </style>

    <?php
    // Build gallery HTML
    $items_html = '';
    foreach ($images as $image) {
        $thumb = vnf_gl_get_image_by_size($image, $size);
        $full = vnf_gl_render_full_image($image);
        $alt = esc_attr($image->alt_text ?: $image->title);
        $title = esc_html($image->title);
        $desc = esc_html($image->description);

        if ($settings['lightbox']) {
            $items_html .= '<div class="vgl-item">';
            $items_html .= '<a href="' . $full . '" data-lightbox="vgl-' . $gallery_uid . '" data-title="' . $title . '">';
            if ($settings['lazyload']) {
                $items_html .= '<img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-src="' . esc_url($thumb) . '" alt="' . $alt . '" class="lazyload">';
            } else {
                $items_html .= '<img src="' . esc_url($thumb) . '" alt="' . $alt . '">';
            }
            $items_html .= '</a>';
        } elseif (!empty($image->link_url)) {
            $items_html .= '<div class="vgl-item">';
            $items_html .= '<a href="' . esc_url($image->link_url) . '">';
            if ($settings['lazyload']) {
                $items_html .= '<img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-src="' . esc_url($thumb) . '" alt="' . $alt . '" class="lazyload">';
            } else {
                $items_html .= '<img src="' . esc_url($thumb) . '" alt="' . $alt . '">';
            }
            $items_html .= '</a>';
        } else {
            $items_html .= '<div class="vgl-item">';
            if ($settings['lazyload']) {
                $items_html .= '<img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-src="' . esc_url($thumb) . '" alt="' . $alt . '" class="lazyload">';
            } else {
                $items_html .= '<img src="' . esc_url($thumb) . '" alt="' . $alt . '">';
            }
        }

        // Overlay with title/desc
        if (($settings['show_title'] && $title) || ($settings['show_desc'] && $desc)) {
            $items_html .= '<div class="vgl-item-overlay">';
            $items_html .= '<div>';
            if ($settings['show_title'] && $title) {
                $items_html .= '<div class="vgl-item-title">' . $title . '</div>';
            }
            if ($settings['show_desc'] && $desc) {
                $items_html .= '<div class="vgl-item-desc">' . $desc . '</div>';
            }
            $items_html .= '</div>';
            $items_html .= '</div>';
        }

        // Close tags
        if ($settings['lightbox'] || !empty($image->link_url)) {
            $items_html .= '</a>';
        }
        $items_html .= '</div>';
    }
    ?>

    <div class="vnf-gl-wrap-<?php echo $gallery_uid; ?> vgl-layout-<?php echo esc_attr($layout); ?>"
         id="<?php echo $gallery_uid; ?>">
        <?php echo $items_html; ?>
    </div>

    <?php
    // Lazy load init script
    if ($settings['lazyload']) :
    ?>
    <script>
    (function() {
        var wrap = document.getElementById('<?php echo $gallery_uid; ?>');
        if (!wrap) return;
        var imgs = wrap.querySelectorAll('img.lazyload');
        if ('IntersectionObserver' in window) {
            var obs = new IntersectionObserver(function(entries) {
                entries.forEach(function(e) {
                    if (e.isIntersecting) {
                        var el = e.target;
                        if (el.dataset.src) {
                            el.src = el.dataset.src;
                            el.classList.remove('lazyload');
                            obs.unobserve(el);
                        }
                    }
                });
            });
            imgs.forEach(function(img) { obs.observe(img); });
        } else {
            imgs.forEach(function(img) {
                if (img.dataset.src) {
                    img.src = img.dataset.src;
                    img.classList.remove('lazyload');
                }
            });
        }
    })();
    </script>
    <?php endif; ?>

    <?php
    return ob_get_clean();
}

/**
 * Lấy ảnh theo danh sách ID
 */
function vnf_gl_get_images_by_ids($ids = array()) {
    if (empty($ids)) return array();

    global $wpdb;
    $ids_str = implode(',', array_map('intval', $ids));
    return $wpdb->get_results(
        "SELECT * FROM {$wpdb->prefix}vnf_gallery_images WHERE id IN ($ids_str) AND status = 1 ORDER BY FIELD(id, $ids_str)"
    );
}

/**
 * Lấy ảnh theo kích thước
 */
function vnf_gl_get_image_by_size($image, $size = 'medium') {
    // Nếu có image_id từ WordPress Media Library
    if (!empty($image->image_id)) {
        $url = wp_get_attachment_image_url($image->image_id, $size);
        if ($url) return $url;
    }

    // Fallback: dùng thumb_url hoặc image_url
    if (!empty($image->thumb_url)) {
        return esc_url($image->thumb_url);
    }
    if (!empty($image->image_url)) {
        return esc_url($image->image_url);
    }

    return '';
}

function vnf_gl_get_gallery_id_by_slug($slug) {
    global $wpdb;
    $row = $wpdb->get_row($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}vnf_galleries WHERE slug = %s",
        sanitize_title($slug)
    ));
    return $row ? (int) $row->id : 0;
}

function vnf_gl_get_gallery_data($gallery_id) {
    global $wpdb;
    return $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}vnf_galleries WHERE id = %d",
        $gallery_id
    ));
}

// Register Gutenberg block
add_action('init', 'vnf_gl_register_block');

function vnf_gl_register_block() {
    if (!function_exists('register_block_type')) return;

    register_block_type('vnf-gallery/gallery', array(
        'render_callback' => 'vnf_gl_block_render',
    ));
}

function vnf_gl_block_render($atts) {
    $atts = shortcode_atts(array(
        'id'   => '',
        'slug' => '',
        'ids'  => '',
    ), $atts, 'vnf_gallery');

    return vnf_gl_shortcode($atts);
}