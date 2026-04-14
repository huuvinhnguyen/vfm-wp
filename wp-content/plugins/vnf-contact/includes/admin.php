<?php
/**
 * Admin settings for VietFarmy Contact
 */

if (!defined('ABSPATH')) exit;

// ================================================================
// MENU
// ================================================================
add_action('admin_menu', 'vnf_contact_admin_menu');

function vnf_contact_admin_menu() {
    add_menu_page(
        'VietFarmy Contact',
        'Liên hệ',
        'vnf_contact_manage',
        'vnf-contact',
        'vnf_contact_settings_page',
        'dashicons-email-alt',
        30
    );
}

// ================================================================
// SAVE SETTINGS
// ================================================================
add_action('admin_init', 'vnf_contact_save_settings');

function vnf_contact_save_settings() {
    if (!isset($_POST['vnf_contact_nonce']) || !wp_verify_nonce($_POST['vnf_contact_nonce'], 'vnf_contact_save')) {
        return;
    }

    if (!current_user_can('vnf_contact_manage')) {
        return;
    }

    $settings = array(
        'welcome_msg'    => sanitize_text_field($_POST['welcome_msg'] ?? ''),
        'address'        => sanitize_text_field($_POST['address'] ?? ''),
        'phone'          => sanitize_text_field($_POST['phone'] ?? ''),
        'phone_display'  => sanitize_text_field($_POST['phone_display'] ?? ''),
        'zalo_id'        => sanitize_text_field($_POST['zalo_id'] ?? ''),
        'email'          => sanitize_email($_POST['email'] ?? ''),
        'working_time'   => sanitize_text_field($_POST['working_time'] ?? ''),
        'facebook_url'   => esc_url_raw($_POST['facebook_url'] ?? ''),
        'facebook_name'  => sanitize_text_field($_POST['facebook_name'] ?? ''),
        'map_embed'      => $_POST['map_embed'] ?? '',
        'image_trust'    => sanitize_text_field($_POST['image_trust'] ?? ''),
        'welcome_note'   => sanitize_textarea_field($_POST['welcome_note'] ?? ''),
        'show_floating'  => !empty($_POST['show_floating']) ? 1 : 0,
        'show_qr_zalo'   => !empty($_POST['show_qr_zalo']) ? 1 : 0,
        'qr_zalo_image'  => esc_url_raw($_POST['qr_zalo_image'] ?? ''),
    );

    update_option('vnf_contact_settings', $settings);
    add_settings_error('vnf_contact', 'saved', 'Đã lưu cài đặt thành công!', 'updated');
}

// ================================================================
// SETTINGS PAGE
// ================================================================
function vnf_contact_settings_page() {
    if (!current_user_can('vnf_contact_manage')) {
        wp_die(__('Bạn không có quyền truy cập trang này.'));
    }

    $settings = get_option('vnf_contact_settings', array());
    $defaults = array(
        'welcome_msg'    => 'Kết nối với VietFarmy – Mang hương vị Gia Lai về ngôi nhà của bạn.',
        'address'        => '',
        'phone'          => '',
        'phone_display'  => '',
        'zalo_id'        => '',
        'email'          => '',
        'working_time'   => 'Thứ 2 - Chủ nhật | 08:00 - 18:00',
        'facebook_url'   => '',
        'facebook_name'  => '',
        'map_embed'      => '',
        'image_trust'    => '',
        'welcome_note'   => '',
        'show_floating'  => 1,
        'show_qr_zalo'   => 0,
        'qr_zalo_image'  => '',
    );
    $settings = wp_parse_args($settings, $defaults);
    ?>
    <div class="vnf-contact-admin wrap">
        <h1>
            <span class="dashicons dashicons-email-alt" style="font-size:24px;margin-right:8px;"></span>
            VietFarmy Contact - Cài Đặt Liên Hệ
        </h1>

        <?php settings_errors(); ?>

        <form method="post" action="">
            <?php wp_nonce_field('vnf_contact_save', 'vnf_contact_nonce'); ?>

            <!-- Tab Navigation -->
            <h2 class="nav-tab-wrapper">
                <a href="#tab-info" class="nav-tab nav-tab-active">Thông Tin</a>
                <a href="#tab-form" class="nav-tab">Form Liên Hệ</a>
                <a href="#tab-map" class="nav-tab">Bản Đồ</a>
                <a href="#tab-social" class="nav-tab">Mạng Xã Hội</a>
                <a href="#tab-floating" class="nav-tab">Nút Nổi</a>
            </h2>

            <div id="tab-info" class="vnf-contact-tab active">
                <h2>Thông Tin Cốt Lõi</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">Thông điệp chào đón</th>
                        <td>
                            <input type="text" name="welcome_msg" value="<?php echo esc_attr($settings['welcome_msg']); ?>" class="regular-text" style="width:100%;">
                            <p class="description">VD: "Kết nối với VietFarmy – Mang hương vị Gia Lai về ngôi nhà của bạn."</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Địa chỉ xưởng rang</th>
                        <td>
                            <input type="text" name="address" value="<?php echo esc_attr($settings['address']); ?>" class="regular-text" style="width:100%;">
                            <p class="description">VD: VietFarmy Roastery: 123 Đường Nguyễn Trãi, P. Yên Đỗ, TP. Pleiku, Gia Lai</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Ghi chú thăm xưởng</th>
                        <td>
                            <input type="text" name="welcome_note" value="<?php echo esc_attr($settings['welcome_note']); ?>" class="regular-text" style="width:100%;">
                            <p class="description">VD: "Chào đón bạn ghé thăm và thưởng thức cà phê trực tiếp tại xưởng rang."</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Số điện thoại</th>
                        <td>
                            <input type="text" name="phone" value="<?php echo esc_attr($settings['phone']); ?>" class="regular-text" placeholder="0901234567">
                            <p class="description">Số điện thoại để gọi (không có dấu cách)</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Hiển thị số điện thoại</th>
                        <td>
                            <input type="text" name="phone_display" value="<?php echo esc_attr($settings['phone_display']); ?>" class="regular-text" placeholder="0901 234 567">
                            <p class="description">Số hiển thị trên trang (có định dạng)</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Zalo ID</th>
                        <td>
                            <input type="text" name="zalo_id" value="<?php echo esc_attr($settings['zalo_id']); ?>" class="regular-text" placeholder="0901234567">
                            <p class="description">Số điện thoại Zalo để tạo link nhắn tin</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Email</th>
                        <td>
                            <input type="email" name="email" value="<?php echo esc_attr($settings['email']); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Giờ làm việc</th>
                        <td>
                            <input type="text" name="working_time" value="<?php echo esc_attr($settings['working_time']); ?>" class="regular-text">
                            <p class="description">VD: Thứ 2 - Chủ nhật | 08:00 - 18:00</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Ảnh tăng tin cậy</th>
                        <td>
                            <input type="text" name="image_trust" value="<?php echo esc_attr($settings['image_trust']); ?>" class="regular-text" style="width:80%;">
                            <button type="button" class="button vnf-contact-upload-btn">Chọn ảnh</button>
                            <p class="description">Ảnh chụp bạn bên bao cà phê hoặc máy rang</p>
                            <?php if ($settings['image_trust']) : ?>
                                <img src="<?php echo esc_url($settings['image_trust']); ?>" style="max-width:200px;margin-top:10px;border-radius:8px;">
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>

            <div id="tab-form" class="vnf-contact-tab" style="display:none;">
                <h2>Cài Đặt Form Liên Hệ</h2>
                <p>Form liên hệ sẽ được hiển thị trong trang liên hệ. Khách hàng có thể gửi tin nhắn trực tiếp.</p>
                <p><strong>Shortcode:</strong> <code>[vnf_contact]</code></p>
                <table class="form-table">
                    <tr>
                        <th scope="row">Hiện form liên hệ</th>
                        <td>
                            <label>
                                <input type="checkbox" name="show_form" value="1" checked disabled>
                                Form liên hệ luôn được bật khi sử dụng shortcode
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Gửi email thông báo</th>
                        <td>
                            <label>
                                <input type="checkbox" name="notify_email" value="1" checked>
                                Gửi email khi có tin nhắn mới
                            </label>
                        </td>
                    </tr>
                </table>
            </div>

            <div id="tab-map" class="vnf-contact-tab" style="display:none;">
                <h2>Cài Đặt Bản Đồ</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">Google Maps Embed</th>
                        <td>
                            <textarea name="map_embed" rows="8" class="large-text code" style="width:100%;"><?php echo esc_textarea($settings['map_embed']); ?></textarea>
                            <p class="description">
                                1. Vào <a href="https://www.google.com/maps" target="_blank">Google Maps</a> và tìm địa chỉ<br>
                                2. Nhấn "Chia sẻ" → "Nhúng bản đồ"<br>
                                3. Copy đoạn code iframe và dán vào đây
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Xem trước</th>
                        <td>
                            <div class="vnf-contact-map-preview">
                                <?php if ($settings['map_embed']) : ?>
                                    <?php echo $settings['map_embed']; ?>
                                <?php else : ?>
                                    <p style="color:#999;">Chưa có bản đồ. Vui lòng nhập code embed.</p>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

            <div id="tab-social" class="vnf-contact-tab" style="display:none;">
                <h2>Kết Nối Mạng Xã Hội</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">Facebook Fanpage</th>
                        <td>
                            <input type="url" name="facebook_url" value="<?php echo esc_attr($settings['facebook_url']); ?>" class="regular-text" style="width:100%;" placeholder="https://www.facebook.com/...">
                            <p class="description">Link fanpage VietFarmy Roastery</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Tên Fanpage</th>
                        <td>
                            <input type="text" name="facebook_name" value="<?php echo esc_attr($settings['facebook_name']); ?>" class="regular-text" style="width:100%;">
                            <p class="description">VD: VietFarmy Roastery - Cà Phê Rang Mộc</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Hiện QR Zalo</th>
                        <td>
                            <label>
                                <input type="checkbox" name="show_qr_zalo" value="1" <?php checked($settings['show_qr_zalo'], 1); ?>>
                                Hiển thị mã QR Zalo trên trang liên hệ
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Ảnh QR Zalo</th>
                        <td>
                            <input type="text" name="qr_zalo_image" value="<?php echo esc_attr($settings['qr_zalo_image']); ?>" class="regular-text" style="width:80%;">
                            <button type="button" class="button vnf-contact-upload-btn">Chọn ảnh</button>
                            <p class="description">Tải lên ảnh QR Zalo Official Account</p>
                            <?php if ($settings['qr_zalo_image']) : ?>
                                <img src="<?php echo esc_url($settings['qr_zalo_image']); ?>" style="max-width:150px;margin-top:10px;border-radius:8px;">
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>

            <div id="tab-floating" class="vnf-contact-tab" style="display:none;">
                <h2>Nút Gọi/Zalo Nổi</h2>
                <p>Hiển thị nút gọi điện và nhắn Zalo nhanh trên góc màn hình (chỉ hiện trên mobile).</p>
                <table class="form-table">
                    <tr>
                        <th scope="row">Bật nút nổi</th>
                        <td>
                            <label>
                                <input type="checkbox" name="show_floating" value="1" <?php checked($settings['show_floating'], 1); ?>>
                                Hiển thị nút Zalo/Call ở góc màn hình
                            </label>
                        </td>
                    </tr>
                </table>

                <h3>Hướng Dẫn</h3>
                <ol style="color:#666;">
                    <li>Vào <strong>Trang Quản lý Zalo OA</strong> → <strong>Cài đặt</strong> → <strong>Lấy link</strong> để lấy link nhắn tin.</li>
                    <li>Sao chép <strong>Zalo ID</strong> (số điện thoại) vào ô bên trên.</li>
                    <li>Nút Zalo sẽ tự động mở ứng dụng Zalo với tin nhắn được soạn sẵn.</li>
                </ol>
            </div>

            <p class="submit">
                <input type="submit" class="button-primary" value="Lưu Cài Đặt">
            </p>
        </form>

        <hr style="margin:30px 0;">
        <h2>Hướng Dẫn Sử Dụng</h2>
        <h3>1. Tạo trang Liên hệ</h3>
        <ol>
            <li>Vào <strong>Pages</strong> → <strong>Add New</strong></li>
            <li>Đặt title: <strong>Liên hệ</strong></li>
            <li>Thêm shortcode: <code>[vnf_contact]</code></li>
            <li>Publish</li>
        </ol>

        <h3>2. Cài đặt SEO cho trang</h3>
        <ul>
            <li>Title: <code>Liên hệ - VietFarmy | Cà Phê Rang Mộc Gia Lai</code></li>
            <li>Meta description: <code>Kết nối với VietFarmy. Cà phê rang mộc Gia Lai nguyên chất. Giao hàng toàn quốc. Hotline: 0901 234 567</code></li>
        </ul>
    </div>

    <style>
    .vnf-contact-admin .nav-tab-wrapper { margin: 20px 0; }
    .vnf-contact-tab { background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 4px; }
    .vnf-contact-map-preview { max-width: 600px; border: 1px solid #ddd; padding: 10px; background: #f8f9fa; }
    .vnf-contact-map-preview iframe { max-width: 100%; }
    </style>

    <script>
    jQuery(document).ready(function($) {
        // Tab switching
        $('.vnf-contact-admin .nav-tab').on('click', function(e) {
            e.preventDefault();
            var target = $(this).attr('href');

            $('.nav-tab').removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active');

            $('.vnf-contact-tab').hide();
            $(target).show();
        });

        // Upload button
        $('.vnf-contact-upload-btn').on('click', function() {
            var $input = $(this).prev('input');
            var $btn = $(this);

            var frame = wp.media({
                title: 'Chọn Ảnh',
                button: { text: 'Chọn' },
                multiple: false
            });

            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                $input.val(attachment.url);

                // Show preview
                var $preview = $('<img>').attr('src', attachment.url).css({
                    'max-width': '200px',
                    'margin-top': '10px',
                    'border-radius': '8px'
                });
                $input.nextAll('img').remove();
                $input.after($preview);
            });

            frame.open();
        });
    });
    </script>
    <?php
}