<?php
/**
 * Admin page for VietFarmy Gallery
 */

if (!defined('ABSPATH')) exit;

// ================================================================
// MENU
// ================================================================
add_action('admin_menu', 'vnf_gl_admin_menu_page');

function vnf_gl_admin_menu_page() {
    add_menu_page(
        'VietFarmy Gallery',
        'Gallery',
        'vnf_gl_manage',
        'vnf-gallery',
        'vnf_gl_admin_page',
        'dashicons-images-alt',
        30
    );
}

function vnf_gl_admin_page() {
    if (!current_user_can('vnf_gl_manage')) {
        wp_die(__('Bạn không có quyền truy cập trang này.'));
    }

    $galleries = vnf_gl_get_galleries();
    $current_id = isset($_GET['gallery_id']) ? (int) $_GET['gallery_id'] : ($galleries ? $galleries[0]->id : 0);

    // Handle create
    if (isset($_POST['vnf_gl_create_nonce']) && wp_verify_nonce($_POST['vnf_gl_create_nonce'], 'vnf_gl_create')) {
        $name = sanitize_text_field($_POST['gallery_name'] ?? '');
        $slug = sanitize_title($_POST['gallery_slug'] ?? $name);
        global $wpdb;

        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}vnf_galleries WHERE slug = %s", $slug
        ));
        if ($exists) $slug .= '-' . time();

        $wpdb->insert($wpdb->prefix . 'vnf_galleries', array(
            'name'     => $name ?: 'Gallery mới',
            'slug'     => $slug,
            'layout'   => 'grid',
            'columns'  => 4,
            'settings' => json_encode(array(
                'spacing'       => 8,
                'border_radius' => 4,
                'show_title'    => 0,
                'show_desc'     => 0,
                'lightbox'      => 1,
                'lazyload'      => 1,
            )),
        ));
        $new_id = $wpdb->insert_id;
        wp_redirect(admin_url('admin.php?page=vnf-gallery&gallery_id=' . $new_id));
        exit;
    }

    $images   = vnf_gl_get_images($current_id);
    $settings = vnf_gl_get_settings($current_id);

    $defaults = array(
        'spacing'       => 8,
        'border_radius' => 4,
        'show_title'    => 0,
        'show_desc'     => 0,
        'lightbox'      => 1,
        'lazyload'      => 1,
    );
    $settings = wp_parse_args($settings, $defaults);

    $current_gallery = null;
    foreach ($galleries as $g) {
        if ((int) $g->id === $current_id) {
            $current_gallery = $g;
            break;
        }
    }
    ?>
    <div class="vnf-gl-admin wrap">
        <h1>VietFarmy Gallery
            <a href="#" class="page-title-action" id="vnf-gl-new-btn">+ Tạo gallery mới</a>
        </h1>

        <!-- New gallery dialog -->
        <div id="vnf-gl-new-dialog" class="vnf-gl-dialog" style="display:none;">
            <div class="vnf-gl-dialog-inner">
                <h2>Tạo Gallery Mới</h2>
                <form method="post">
                    <?php wp_nonce_field('vnf_gl_create', 'vnf_gl_create_nonce'); ?>
                    <table class="form-table">
                        <tr>
                            <th>Tên gallery</th>
                            <td><input type="text" name="gallery_name" id="vnf-gl-name" placeholder="VD: Sản phẩm" style="width:100%;"></td>
                        </tr>
                        <tr>
                            <th>Slug (URL)</th>
                            <td>
                                <code id="vnf-gl-slug-preview"></code>
                                <input type="hidden" name="gallery_slug" id="vnf-gl-slug-input">
                            </td>
                        </tr>
                    </table>
                    <p class="submit">
                        <input type="submit" class="button-primary" value="Tạo gallery">
                        <a href="#" class="button vnf-gl-dialog-cancel">Hủy</a>
                    </p>
                </form>
            </div>
        </div>
        <div class="vnf-gl-dialog-overlay" id="vnf-gl-dialog-overlay" style="display:none;"></div>

        <div class="vnf-gl-layout">
            <!-- Sidebar: gallery list -->
            <div class="vnf-gl-sidebar">
                <h2>Galleries</h2>
                <ul class="vnf-gl-list">
                    <?php foreach ($galleries as $g) : ?>
                        <li>
                            <a href="<?php echo admin_url('admin.php?page=vnf-gallery&gallery_id=' . $g->id); ?>"
                               class="<?php echo ((int)$g->id === $current_id) ? 'active' : ''; ?>">
                                <span class="dashicons dashicons-images-alt"></span>
                                <?php echo esc_html($g->name); ?>
                            </a>
                            <?php if ($g->slug !== 'default') : ?>
                                <a href="#" class="vnf-gl-delete-gl button-link"
                                   data-id="<?php echo $g->id; ?>"
                                   title="Xóa gallery">
                                    <span class="dashicons dashicons-trash"></span>
                                </a>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <?php if ($current_gallery) : ?>
                <hr>
                <h2>Cài Đặt Hiển Thị</h2>
                <form id="vnf-gl-settings-form">
                    <table class="form-table">
                        <tr>
                            <th>Bố cục</th>
                            <td>
                                <select name="layout">
                                    <option value="grid" <?php selected($current_gallery->layout, 'grid'); ?>>Lưới (Grid)</option>
                                    <option value="masonry" <?php selected($current_gallery->layout, 'masonry'); ?>>Xếp chồng (Masonry)</option>
                                    <option value="slider" <?php selected($current_gallery->layout, 'slider'); ?>>Trình chiếu (Slider)</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th>Số cột</th>
                            <td>
                                <select name="columns">
                                    <?php for ($i = 2; $i <= 6; $i++) : ?>
                                        <option value="<?php echo $i; ?>" <?php selected($current_gallery->columns, $i); ?>><?php echo $i; ?> cột</option>
                                    <?php endfor; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th>Khoảng cách</th>
                            <td>
                                <input type="number" name="spacing" value="<?php echo (int) $settings['spacing']; ?>" min="0" max="32"> px
                            </td>
                        </tr>
                        <tr>
                            <th>Bo góc</th>
                            <td>
                                <input type="number" name="border_radius" value="<?php echo (int) $settings['border_radius']; ?>" min="0" max="50"> px
                            </td>
                        </tr>
                        <tr>
                            <th>Hiệu ứng Lightbox</th>
                            <td>
                                <label><input type="checkbox" name="lightbox" value="1" <?php checked($settings['lightbox'], 1); ?>> Bật Lightbox</label>
                            </td>
                        </tr>
                        <tr>
                            <th>Tải ảnh lazy</th>
                            <td>
                                <label><input type="checkbox" name="lazyload" value="1" <?php checked($settings['lazyload'], 1); ?>> Lazy load</label>
                            </td>
                        </tr>
                        <tr>
                            <th>Hiện tiêu đề</th>
                            <td>
                                <label><input type="checkbox" name="show_title" value="1" <?php checked($settings['show_title'], 1); ?>> Hiện</label>
                            </td>
                        </tr>
                        <tr>
                            <th>Hiện mô tả</th>
                            <td>
                                <label><input type="checkbox" name="show_desc" value="1" <?php checked($settings['show_desc'], 1); ?>> Hiện</label>
                            </td>
                        </tr>
                    </table>
                    <input type="hidden" name="gallery_id" value="<?php echo $current_id; ?>">
                    <input type="hidden" name="action" value="vnf_gl_save_settings">
                    <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('vnf_gl_nonce'); ?>">
                    <p class="submit">
                        <input type="submit" class="button-primary" value="Lưu cài đặt">
                        <span class="vnf-gl-settings-msg"></span>
                    </p>
                </form>

                <hr>
                <p>
                    <strong>Shortcode:</strong><br>
                    <code id="vnf-gl-shortcode-display">[vnf_gallery id="<?php echo $current_id; ?>"]</code>
                    <button class="button button-small" id="vnf-gl-copy-shortcode" data-copy="[vnf_gallery id=&quot;<?php echo $current_id; ?>&quot;]">Sao chép</button>
                </p>
                <p>
                    Hoặc dùng PHP:<br>
                    <code style="font-size:11px;">&lt;?php echo do_shortcode('[vnf_gallery id="<?php echo $current_id; ?>"]'); ?&gt;</code>
                </p>
                <p>
                    Theo slug:<br>
                    <code style="font-size:11px;">[vnf_gallery slug="<?php echo esc_attr($current_gallery->slug); ?>"]</code>
                </p>
                <?php endif; ?>
            </div>

            <!-- Main: image management -->
            <div class="vnf-gl-main">
                <h2>
                    <?php echo $current_gallery ? esc_html($current_gallery->name) : 'Quản lý Ảnh'; ?>
                    <span class="vnf-gl-image-count" style="font-size:14px;color:#666;font-weight:normal;">
                        (<?php echo count($images); ?> ảnh)
                    </span>
                </h2>

                <!-- Add image form -->
                <div class="vnf-gl-add-form" id="vnf-gl-add-form">
                    <h3>Thêm Ảnh Mới</h3>
                    <div class="vnf-gl-form-grid">
                        <!-- Image source -->
                        <div class="vnf-gl-field-group">
                            <label>Hình ảnh</label>
                            <div class="vnf-gl-img-source-tabs">
                                <button type="button" class="vnf-gl-tab-btn active" data-tab="url">Từ URL</button>
                                <button type="button" class="vnf-gl-tab-btn" data-tab="file">Từ tệp</button>
                            </div>

                            <div class="vnf-gl-tab-content active" data-tab="url">
                                <input type="url" id="vnf-gl-url-input" placeholder="https://example.com/image.jpg" style="width:100%;">
                                <div class="vnf-gl-url-preview" id="vnf-gl-url-preview"></div>
                            </div>

                            <div class="vnf-gl-tab-content" data-tab="file" style="display:none;">
                                <input type="hidden" id="vnf-gl-file-id" value="">
                                <input type="button" class="vnf-gl-upload-btn button" value="Chọn tệp hình ảnh">
                                <div class="vnf-gl-file-preview" id="vnf-gl-file-preview"></div>
                            </div>
                        </div>

                        <!-- Thumbnail URL (optional) -->
                        <div class="vnf-gl-field-group">
                            <label for="vnf-gl-thumb">URL thumbnail (tùy chọn)</label>
                            <input type="url" id="vnf-gl-thumb" placeholder="https://... thumbnail.jpg" style="width:100%;">
                        </div>

                        <!-- Other fields -->
                        <div class="vnf-gl-field-group">
                            <label for="vnf-gl-title">Tiêu đề</label>
                            <input type="text" id="vnf-gl-title" placeholder="VD: Cà phê Robusta" style="width:100%;">
                        </div>

                        <div class="vnf-gl-field-group">
                            <label for="vnf-gl-desc">Mô tả</label>
                            <textarea id="vnf-gl-desc" rows="2" placeholder="Mô tả ngắn…" style="width:100%;"></textarea>
                        </div>

                        <div class="vnf-gl-field-group">
                            <label for="vnf-gl-alt">Alt text (SEO)</label>
                            <input type="text" id="vnf-gl-alt" placeholder="Mô tả cho SEO" style="width:100%;">
                        </div>

                        <div class="vnf-gl-field-group">
                            <label for="vnf-gl-link">Link khi click</label>
                            <input type="url" id="vnf-gl-link" placeholder="https://..." style="width:100%;">
                        </div>
                    </div>

                    <p class="submit">
                        <input type="hidden" id="vnf-gl-gallery-id" value="<?php echo $current_id; ?>">
                        <input type="hidden" id="vnf-gl-edit-id" value="">
                        <button type="button" class="button-primary" id="vnf-gl-save-image">Thêm Ảnh</button>
                        <button type="button" class="button" id="vnf-gl-cancel-edit" style="display:none;">Hủy sửa</button>
                        <span class="vnf-gl-msg"></span>
                    </p>
                </div>

                <hr>

                <!-- Images list (sortable) -->
                <h3>Danh Sách Ảnh</h3>
                <div id="vnf-gl-images-list">
                    <?php if (empty($images)) : ?>
                        <p class="vnf-gl-no-images"><?php esc_html_e('Chưa có ảnh nào. Thêm ảnh đầu tiên ở trên.', 'vnf-gallery'); ?></p>
                    <?php else : ?>
                        <ul class="vnf-gl-sortable" id="vnf-gl-sortable">
                            <?php foreach ($images as $idx => $image) :
                                $img = vnf_gl_render_image($image);
                            ?>
                                <li class="vnf-gl-image-item" data-id="<?php echo $image->id; ?>" data-order="<?php echo $idx; ?>">
                                    <span class="vnf-gl-drag-handle" title="Kéo để sắp xếp">&#9776;</span>
                                    <div class="vnf-gl-image-thumb">
                                        <?php if ($img) : ?>
                                            <img src="<?php echo esc_url($img); ?>" alt="">
                                        <?php else : ?>
                                            <div class="vnf-gl-no-img">Không có ảnh</div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="vnf-gl-image-info">
                                        <strong><?php echo esc_html($image->title); ?></strong>
                                        <?php if ($image->description) : ?>
                                            <p><?php echo esc_html($image->description); ?></p>
                                        <?php endif; ?>
                                        <?php if ($image->link_url) : ?>
                                            <span class="vnf-gl-link-badge">
                                                <span class="dashicons dashicons-admin-links"></span>
                                                <?php echo esc_url($image->link_url); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="vnf-gl-image-actions">
                                        <button type="button" class="vnf-gl-edit-btn button-small button"
                                                title="Sửa"
                                                data-id="<?php echo $image->id; ?>">
                                            <span class="dashicons dashicons-edit"></span>
                                        </button>
                                        <button type="button" class="vnf-gl-delete-btn button-small button"
                                                title="Xóa"
                                                data-id="<?php echo $image->id; ?>">
                                            <span class="dashicons dashicons-trash"></span>
                                        </button>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php
}