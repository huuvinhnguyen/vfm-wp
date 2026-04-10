<?php
/**
 * Admin page for VietFarmy Slideshow
 */

if (!defined('ABSPATH')) exit;

// ================================================================
// MENU
// ================================================================
add_action('admin_menu', 'vnf_sw_admin_menu_page');

function vnf_sw_admin_menu_page() {
    add_menu_page(
        'VietFarmy Slideshow',
        'Slideshow',
        'vnf_sw_manage',
        'vnf-slideshow',
        'vnf_sw_admin_page',
        'dashicons-images-alt2',
        30
    );
}

function vnf_sw_admin_page() {
    if (!current_user_can('vnf_sw_manage')) {
        wp_die(__('Bạn không có quyền truy cập trang này.'));
    }

    $slideshows = vnf_sw_get_slideshows();
    $current_id = isset($_GET['slideshow_id']) ? (int) $_GET['slideshow_id'] : ($slideshows ? $slideshows[0]->id : 0);

    // Handle create
    if (isset($_POST['vnf_sw_create_nonce']) && wp_verify_nonce($_POST['vnf_sw_create_nonce'], 'vnf_sw_create')) {
        $name = sanitize_text_field($_POST['slideshow_name'] ?? '');
        $slug = sanitize_title($_POST['slideshow_slug'] ?? $name);
        global $wpdb;

        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}vnf_slideshows WHERE slug = %s", $slug
        ));
        if ($exists) $slug .= '-' . time();

        $wpdb->insert($wpdb->prefix . 'vnf_slideshows', array(
            'name'     => $name ?: 'Slideshow mới',
            'slug'     => $slug,
            'settings' => json_encode(array(
                'autoplay'   => 1,
                'speed'      => 4000,
                'height'     => 480,
                'transition' => 'fade',
                'nav'        => 1,
                'dots'       => 1,
                'caption'    => 1,
            )),
        ));
        $new_id = $wpdb->insert_id;
        wp_redirect(admin_url('admin.php?page=vnf-slideshow&slideshow_id=' . $new_id));
        exit;
    }

    $slides   = vnf_sw_get_slides($current_id);
    $settings = vnf_sw_get_settings($current_id);

    $defaults = array(
        'autoplay'   => 1,
        'speed'      => 4000,
        'height'     => 480,
        'transition' => 'fade',
        'nav'        => 1,
        'dots'       => 1,
        'caption'    => 1,
    );
    $settings = wp_parse_args($settings, $defaults);

    $current_slideshow = null;
    foreach ($slideshows as $s) {
        if ((int) $s->id === $current_id) {
            $current_slideshow = $s;
            break;
        }
    }
    ?>
    <div class="vnf-sw-admin wrap">
        <h1>VietFarmy Slideshow
            <a href="#" class="page-title-action" id="vnf-sw-new-btn">+ Tạo slideshow mới</a>
        </h1>

        <!-- New slideshow dialog -->
        <div id="vnf-sw-new-dialog" class="vnf-sw-dialog" style="display:none;">
            <div class="vnf-sw-dialog-inner">
                <h2>Tạo Slideshow Mới</h2>
                <form method="post">
                    <?php wp_nonce_field('vnf_sw_create', 'vnf_sw_create_nonce'); ?>
                    <table class="form-table">
                        <tr>
                            <th>Tên slideshow</th>
                            <td><input type="text" name="slideshow_name" id="vnf-sw-name" placeholder="VD: Banner Trang Chủ" style="width:100%;"></td>
                        </tr>
                        <tr>
                            <th>Slug (URL)</th>
                            <td>
                                <code id="vnf-sw-slug-preview"></code>
                                <input type="hidden" name="slideshow_slug" id="vnf-sw-slug-input">
                            </td>
                        </tr>
                    </table>
                    <p class="submit">
                        <input type="submit" class="button-primary" value="Tạo slideshow">
                        <a href="#" class="button vnf-sw-dialog-cancel">Hủy</a>
                    </p>
                </form>
            </div>
        </div>
        <div class="vnf-sw-dialog-overlay" id="vnf-sw-dialog-overlay" style="display:none;"></div>

        <div class="vnf-sw-layout">
            <!-- Sidebar: slideshow list -->
            <div class="vnf-sw-sidebar">
                <h2>Slideshows</h2>
                <ul class="vnf-sw-list">
                    <?php foreach ($slideshows as $s) : ?>
                        <li>
                            <a href="<?php echo admin_url('admin.php?page=vnf-slideshow&slideshow_id=' . $s->id); ?>"
                               class="<?php echo ((int)$s->id === $current_id) ? 'active' : ''; ?>">
                                <span class="dashicons dashicons-images-alt2"></span>
                                <?php echo esc_html($s->name); ?>
                            </a>
                            <?php if ($s->slug !== 'default') : ?>
                                <a href="#" class="vnf-sw-delete-sw button-link"
                                   data-id="<?php echo $s->id; ?>"
                                   title="Xóa slideshow">
                                    <span class="dashicons dashicons-trash"></span>
                                </a>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <?php if ($current_slideshow) : ?>
                <hr>
                <h2>Cài Đặt</h2>
                <form id="vnf-sw-settings-form">
                    <table class="form-table">
                        <tr>
                            <th>Tự động chạy</th>
                            <td>
                                <label><input type="checkbox" name="autoplay" value="1" <?php checked($settings['autoplay'], 1); ?>> Có</label>
                            </td>
                        </tr>
                        <tr>
                            <th>Thời gian mỗi slide</th>
                            <td>
                                <input type="number" name="speed" value="<?php echo (int) $settings['speed']; ?>" min="500" max="30000" step="100"> ms
                            </td>
                        </tr>
                        <tr>
                            <th>Chiều cao</th>
                            <td>
                                <input type="number" name="height" value="<?php echo (int) $settings['height']; ?>" min="200" max="1080"> px
                            </td>
                        </tr>
                        <tr>
                            <th>Hiệu ứng chuyển slide</th>
                            <td>
                                <select name="transition">
                                    <option value="fade" <?php selected($settings['transition'], 'fade'); ?>>Fade</option>
                                    <option value="slide" <?php selected($settings['transition'], 'slide'); ?>>Slide</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th>Thanh điều hướng</th>
                            <td>
                                <label><input type="checkbox" name="nav" value="1" <?php checked($settings['nav'], 1); ?>> Hiện</label>
                            </td>
                        </tr>
                        <tr>
                            <th>Chấm tròn chỉ số</th>
                            <td>
                                <label><input type="checkbox" name="dots" value="1" <?php checked($settings['dots'], 1); ?>> Hiện</label>
                            </td>
                        </tr>
                        <tr>
                            <th>Chú thích ảnh</th>
                            <td>
                                <label><input type="checkbox" name="caption" value="1" <?php checked($settings['caption'], 1); ?>> Hiện</label>
                            </td>
                        </tr>
                    </table>
                    <input type="hidden" name="slideshow_id" value="<?php echo $current_id; ?>">
                    <input type="hidden" name="action" value="vnf_sw_save_settings">
                    <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('vnf_sw_nonce'); ?>">
                    <p class="submit">
                        <input type="submit" class="button-primary" value="Lưu cài đặt">
                        <span class="vnf-sw-settings-msg"></span>
                    </p>
                </form>

                <hr>
                <p>
                    <strong>Shortcode:</strong><br>
                    <code id="vnf-sw-shortcode-display">[vnf_slideshow id="<?php echo $current_id; ?>"]</code>
                    <button class="button button-small" id="vnf-sw-copy-shortcode" data-copy="[vnf_slideshow id=&quot;<?php echo $current_id; ?>&quot;]">Sao chép</button>
                </p>
                <p>
                    Hoặc dùng PHP:<br>
                    <code style="font-size:11px;">&lt;?php echo do_shortcode('[vnf_slideshow id="<?php echo $current_id; ?>"]'); ?&gt;</code>
                </p>
                <?php endif; ?>
            </div>

            <!-- Main: slide management -->
            <div class="vnf-sw-main">
                <h2>
                    <?php echo $current_slideshow ? esc_html($current_slideshow->name) : 'Quản lý Slides'; ?>
                    <span class="vnf-sw-slide-count" style="font-size:14px;color:#666;font-weight:normal;">
                        (<?php echo count($slides); ?> slide)
                    </span>
                </h2>

                <!-- Add slide form -->
                <div class="vnf-sw-add-form" id="vnf-sw-add-form">
                    <h3>Thêm Slide Mới</h3>
                    <div class="vnf-sw-form-grid">
                        <!-- Image source -->
                        <div class="vnf-sw-field-group">
                            <label>Hình ảnh</label>
                            <div class="vnf-sw-img-source-tabs">
                                <button type="button" class="vnf-sw-tab-btn active" data-tab="url">Từ URL</button>
                                <button type="button" class="vnf-sw-tab-btn" data-tab="file">Từ tệp</button>
                            </div>

                            <div class="vnf-sw-tab-content active" data-tab="url">
                                <input type="url" id="vnf-sw-url-input" placeholder="https://example.com/image.jpg" style="width:100%;">
                                <div class="vnf-sw-url-preview" id="vnf-sw-url-preview"></div>
                            </div>

                            <div class="vnf-sw-tab-content" data-tab="file" style="display:none;">
                                <input type="hidden" id="vnf-sw-file-id" value="">
                                <input type="button" class="vnf-sw-upload-btn button" value="Chọn tệp hình ảnh">
                                <div class="vnf-sw-file-preview" id="vnf-sw-file-preview"></div>
                            </div>
                        </div>

                        <!-- Other fields -->
                        <div class="vnf-sw-field-group">
                            <label for="vnf-sw-title">Tiêu đề / Caption</label>
                            <input type="text" id="vnf-sw-title" placeholder="VD: Cà phê Robusta Gia Lai" style="width:100%;">
                        </div>

                        <div class="vnf-sw-field-group">
                            <label for="vnf-sw-desc">Mô tả</label>
                            <textarea id="vnf-sw-desc" rows="2" placeholder="Mô tả ngắn…" style="width:100%;"></textarea>
                        </div>

                        <div class="vnf-sw-field-group">
                            <label for="vnf-sw-alt">Alt text</label>
                            <input type="text" id="vnf-sw-alt" placeholder="Mô tả cho SEO" style="width:100%;">
                        </div>

                        <div class="vnf-sw-field-group">
                            <label for="vnf-sw-link">Link khi click</label>
                            <input type="url" id="vnf-sw-link" placeholder="https://..." style="width:100%;">
                        </div>

                        <div class="vnf-sw-field-group">
                            <label>
                                <input type="checkbox" id="vnf-sw-link-newtab"> Mở link trong tab mới
                            </label>
                        </div>
                    </div>

                    <p class="submit">
                        <input type="hidden" id="vnf-sw-slideshow-id" value="<?php echo $current_id; ?>">
                        <input type="hidden" id="vnf-sw-edit-id" value="">
                        <button type="button" class="button-primary" id="vnf-sw-save-slide">Thêm Slide</button>
                        <button type="button" class="button" id="vnf-sw-cancel-edit" style="display:none;">Hủy sửa</button>
                        <span class="vnf-sw-msg"></span>
                    </p>
                </div>

                <hr>

                <!-- Slides list (sortable) -->
                <h3>Danh Sách Slides</h3>
                <div id="vnf-sw-slides-list">
                    <?php if (empty($slides)) : ?>
                        <p class="vnf-sw-no-slides"><?php esc_html_e('Chưa có slide nào. Thêm slide đầu tiên ở trên.', 'vnf-slideshow'); ?></p>
                    <?php else : ?>
                        <ul class="vnf-sw-sortable" id="vnf-sw-sortable">
                            <?php foreach ($slides as $idx => $slide) :
                                $img = vnf_sw_render_slide_image($slide);
                            ?>
                                <li class="vnf-sw-slide-item" data-id="<?php echo $slide->id; ?>" data-order="<?php echo $idx; ?>">
                                    <span class="vnf-sw-drag-handle" title="Kéo để sắp xếp">&#9776;</span>
                                    <div class="vnf-sw-slide-thumb">
                                        <?php if ($img) : ?>
                                            <img src="<?php echo esc_url($img); ?>" alt="">
                                        <?php else : ?>
                                            <div class="vnf-sw-no-img">Không có ảnh</div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="vnf-sw-slide-info">
                                        <strong><?php echo esc_html($slide->title); ?></strong>
                                        <?php if ($slide->description) : ?>
                                            <p><?php echo esc_html($slide->description); ?></p>
                                        <?php endif; ?>
                                        <?php if ($slide->link_url) : ?>
                                            <span class="vnf-sw-link-badge">
                                                <span class="dashicons dashicons-admin-links"></span>
                                                <?php echo esc_url($slide->link_url); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="vnf-sw-slide-actions">
                                        <button type="button" class="vnf-sw-edit-btn button-small button"
                                                title="Sửa"
                                                data-id="<?php echo $slide->id; ?>">
                                            <span class="dashicons dashicons-edit"></span>
                                        </button>
                                        <button type="button" class="vnf-sw-delete-btn button-small button"
                                                title="Xóa"
                                                data-id="<?php echo $slide->id; ?>">
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
