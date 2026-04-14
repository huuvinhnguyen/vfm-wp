<?php
/**
 * Frontend display for VietFarmy Contact
 */

if (!defined('ABSPATH')) exit;

// AJAX handlers
add_action('wp_ajax_vnf_contact_submit', 'vnf_contact_ajax_submit');
add_action('wp_ajax_nopriv_vnf_contact_submit', 'vnf_contact_ajax_submit');

function vnf_contact_ajax_submit() {
    check_ajax_referer('vnf_contact_nonce', 'nonce');

    $name    = sanitize_text_field($_POST['name'] ?? '');
    $phone   = sanitize_text_field($_POST['phone'] ?? '');
    $subject = sanitize_text_field($_POST['subject'] ?? '');
    $message = sanitize_textarea_field($_POST['message'] ?? '');

    // Validate
    if (empty($name) || empty($phone)) {
        wp_send_json_error(array('message' => 'Vui lòng nhập họ tên và số điện thoại.'));
    }

    if (!preg_match('/^[0-9\-\+\s]{9,15}$/', $phone)) {
        wp_send_json_error(array('message' => 'Số điện thoại không hợp lệ.'));
    }

    // Save to database
    global $wpdb;
    $table = $wpdb->prefix . 'vnf_contact_messages';

    $result = $wpdb->insert($table, array(
        'name'      => $name,
        'phone'     => $phone,
        'subject'   => $subject,
        'message'   => $message,
        'ip'        => $_SERVER['REMOTE_ADDR'] ?? '',
        'created_at'=> current_time('mysql'),
    ));

    if ($result) {
        // Send email notification
        $settings = get_option('vnf_contact_settings', array());
        $to = $settings['email'] ?? get_option('admin_email');
        $subject_email = "[VietFarmy] Tin nhắn mới từ {$name}";
        $body = "Họ và tên: {$name}\nSĐT: {$phone}\nQuan tâm: {$subject}\n\nNội dung:\n{$message}";
        wp_mail($to, $subject_email, $body);

        wp_send_json_success(array('message' => 'Cảm ơn bạn! Chúng tôi sẽ liên hệ trong thời gian sớm nhất.'));
    } else {
        wp_send_json_error(array('message' => 'Có lỗi xảy ra. Vui lòng thử lại.'));
    }
}

// ================================================================
// SHORTCODE
// ================================================================
function vnf_contact_shortcode($atts) {
    $settings = get_option('vnf_contact_settings', array());
    $defaults = array(
        'welcome_msg'    => 'Kết nối với VietFarmy – Mang hương vị Gia Lai về ngôi nhà của bạn.',
        'address'        => '',
        'phone'          => '',
        'phone_display'  => '',
        'zalo_id'        => '',
        'email'          => '',
        'working_time'   => '',
        'facebook_url'   => '',
        'facebook_name'  => '',
        'map_embed'      => '',
        'image_trust'    => '',
        'welcome_note'   => '',
        'show_qr_zalo'   => 0,
        'qr_zalo_image'  => '',
    );
    $settings = wp_parse_args($settings, $defaults);

    // Load CSS
    wp_enqueue_style('vnf-contact', VNF_CONTACT_URL . 'assets/css/contact.css', array(), '1.0.0');
    wp_enqueue_script('vnf-contact', VNF_CONTACT_URL . 'assets/js/contact.js', array('jquery'), '1.0.0', true);

    wp_localize_script('vnf-contact', 'vnfContact', array(
        'ajaxUrl'  => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('vnf_contact_nonce'),
        'settings' => $settings,
    ));

    ob_start();

    // Welcome message
    $welcome = $settings['welcome_msg'] ?: $defaults['welcome_msg'];
    ?>

    <div class="vnf-contact-container" style="max-width:1200px;margin:0 auto;padding:60px 20px 40px;font-family:inherit;box-sizing:border-box;">
        <div class="vnf-contact-welcome" style="text-align:center;margin-bottom:48px;">
            <h2 style="font-size:1.75rem;color:#2d5016;font-weight:600;line-height:1.4;margin:0;"><?php echo esc_html($welcome); ?></h2>
        </div>

        <div class="vnf-contact-wrapper" style="display:grid;grid-template-columns:1fr 1fr;gap:32px;margin-bottom:48px;">
            <!-- Left Column: Form -->
            <div class="vnf-contact-col vnf-contact-col-left">
                <div class="vnf-contact-card" style="background:#fff;border-radius:12px;padding:32px;box-shadow:0 2px 16px rgba(0,0,0,.08);">
                    <h3 style="margin:0 0 24px;font-size:1.25rem;color:#2d5016;">Gửi tin nhắn</h3>
                    <form id="vnf-contact-form" class="vnf-contact-form" style="display:flex;flex-direction:column;gap:20px;">
                        <div class="vnf-form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                            <div class="vnf-form-group">
                                <label for="vc-name" style="font-weight:600;color:#333;font-size:14px;display:block;margin-bottom:6px;">Họ và tên <span style="color:#e53935;">*</span></label>
                                <input type="text" id="vc-name" name="name" required placeholder="Nhập họ và tên của bạn" style="width:100%;padding:12px 16px;border:2px solid #e8e8e8;border-radius:8px;font-size:15px;box-sizing:border-box;">
                            </div>
                            <div class="vnf-form-group">
                                <label for="vc-phone" style="font-weight:600;color:#333;font-size:14px;display:block;margin-bottom:6px;">Số điện thoại/Zalo <span style="color:#e53935;">*</span></label>
                                <input type="tel" id="vc-phone" name="phone" required placeholder="09xx xxx xxx" style="width:100%;padding:12px 16px;border:2px solid #e8e8e8;border-radius:8px;font-size:15px;box-sizing:border-box;">
                            </div>
                        </div>

                        <div class="vnf-form-group">
                            <label for="vc-subject" style="font-weight:600;color:#333;font-size:14px;display:block;margin-bottom:6px;">Bạn quan tâm đến</label>
                            <select id="vc-subject" name="subject" style="width:100%;padding:12px 16px;border:2px solid #e8e8e8;border-radius:8px;font-size:15px;box-sizing:border-box;">
                                <option value="">-- Chọn --</option>
                                <option value="mua-le">Mua lẻ</option>
                                <option value="nhap-si">Nhập sỉ cho quán</option>
                                <option value="lam-qua-tang">Làm quà tặng</option>
                                <option value="tu-van-san-pham">Tư vấn sản phẩm</option>
                                <option value="khac">Khác</option>
                            </select>
                        </div>

                        <div class="vnf-form-group">
                            <label for="vc-message" style="font-weight:600;color:#333;font-size:14px;display:block;margin-bottom:6px;">Lời nhắn</label>
                            <textarea id="vc-message" name="message" rows="4" placeholder="Viết lời nhắn của bạn..." style="width:100%;padding:12px 16px;border:2px solid #e8e8e8;border-radius:8px;font-size:15px;resize:vertical;box-sizing:border-box;"></textarea>
                        </div>

                        <button type="submit" class="vnf-btn vnf-btn-primary" id="vc-submit" style="padding:14px 32px;border:none;border-radius:8px;font-size:16px;font-weight:600;cursor:pointer;background:linear-gradient(135deg, #4CAF50 0%, #2E7D32 100%);color:#fff;">
                            <span class="vnf-btn-text">Gửi Tin Nhắn</span>
                            <span class="vnf-btn-loading" style="display:none;">Đang gửi...</span>
                        </button>

                        <div class="vnf-form-message" id="vc-message-box"></div>
                    </form>
                </div>
            </div>

            <!-- Right Column: Info -->
            <div class="vnf-contact-col vnf-contact-col-right">
                <div class="vnf-contact-card" style="background:#fff;border-radius:12px;padding:32px;box-shadow:0 2px 16px rgba(0,0,0,.08);">
                    <!-- Trust Image -->
                    <?php if (!empty($settings['image_trust'])) : ?>
                        <div class="vnf-contact-trust-img" style="margin-bottom:24px;border-radius:8px;overflow:hidden;">
                            <img src="<?php echo esc_url($settings['image_trust']); ?>" alt="VietFarmy Team" style="width:100%;height:180px;object-fit:cover;">
                        </div>
                    <?php endif; ?>

                    <div class="vnf-contact-info-list" style="display:flex;flex-direction:column;gap:20px;">
                        <!-- Address -->
                        <?php if (!empty($settings['address'])) : ?>
                            <div class="vnf-contact-info-item" style="display:flex;gap:16px;align-items:flex-start;">
                                <div class="vnf-info-icon" style="width:40px;height:40px;border-radius:50%;background:#E8F5E9;color:#4CAF50;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                </div>
                                <div class="vnf-info-content">
                                    <strong style="display:block;color:#333;margin-bottom:4px;">Địa chỉ</strong>
                                    <p style="margin:0;color:#555;font-size:15px;"><?php echo esc_html($settings['address']); ?></p>
                                    <?php if (!empty($settings['welcome_note'])) : ?>
                                        <small class="vnf-welcome-note" style="display:block;margin-top:4px;color:#4CAF50;font-style:italic;"><?php echo esc_html($settings['welcome_note']); ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Phone -->
                        <?php if (!empty($settings['phone_display'])) : ?>
                            <div class="vnf-contact-info-item" style="display:flex;gap:16px;align-items:flex-start;">
                                <div class="vnf-info-icon" style="width:40px;height:40px;border-radius:50%;background:#E8F5E9;color:#4CAF50;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                                </div>
                                <div class="vnf-info-content">
                                    <strong style="display:block;color:#333;margin-bottom:4px;">Hotline / Zalo</strong>
                                    <p style="margin:0;">
                                        <a href="tel:<?php echo preg_replace('/\D/', '', $settings['phone']); ?>" class="vnf-phone-link" style="color:#4CAF50;font-weight:600;font-size:18px;text-decoration:none;">
                                            <?php echo esc_html($settings['phone_display']); ?>
                                        </a>
                                    </p>
                                    <small style="color:#888;font-size:12px;">Hỗ trợ 24/7</small>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Email -->
                        <?php if (!empty($settings['email'])) : ?>
                            <div class="vnf-contact-info-item" style="display:flex;gap:16px;align-items:flex-start;">
                                <div class="vnf-info-icon" style="width:40px;height:40px;border-radius:50%;background:#E8F5E9;color:#4CAF50;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                                </div>
                                <div class="vnf-info-content">
                                    <strong style="display:block;color:#333;margin-bottom:4px;">Email</strong>
                                    <p style="margin:0;color:#555;font-size:15px;"><a href="mailto:<?php echo esc_attr($settings['email']); ?>" style="color:#555;text-decoration:none;"><?php echo esc_html($settings['email']); ?></a></p>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Working Time -->
                        <?php if (!empty($settings['working_time'])) : ?>
                            <div class="vnf-contact-info-item" style="display:flex;gap:16px;align-items:flex-start;">
                                <div class="vnf-info-icon" style="width:40px;height:40px;border-radius:50%;background:#E8F5E9;color:#4CAF50;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                </div>
                                <div class="vnf-info-content">
                                    <strong style="display:block;color:#333;margin-bottom:4px;">Giờ làm việc</strong>
                                    <p style="margin:0;color:#555;font-size:15px;"><?php echo esc_html($settings['working_time']); ?></p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Social Links -->
                    <div class="vnf-contact-social" style="display:flex;gap:12px;margin-top:24px;flex-wrap:wrap;">
                        <?php if (!empty($settings['facebook_url'])) : ?>
                            <a href="<?php echo esc_url($settings['facebook_url']); ?>" target="_blank" rel="noopener" class="vnf-social-btn vnf-social-facebook" style="display:inline-flex;align-items:center;gap:8px;padding:10px 16px;border-radius:8px;font-size:14px;font-weight:600;text-decoration:none;background:#1877F2;color:#fff;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>
                                <?php echo esc_html($settings['facebook_name'] ?: 'Facebook'); ?>
                            </a>
                        <?php endif; ?>

                        <?php if (!empty($settings['zalo_id'])) : ?>
                            <a href="https://zalo.me/<?php echo esc_attr($settings['zalo_id']); ?>" target="_blank" rel="noopener" class="vnf-social-btn vnf-social-zalo" style="display:inline-flex;align-items:center;gap:8px;padding:10px 16px;border-radius:8px;font-size:14px;font-weight:600;text-decoration:none;background:#0068FF;color:#fff;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="11" fill="#0068FF"/><path d="M12 6C8.13 6 5 9.13 5 13c0 2.38 1.19 4.47 3 5.74V22l4.11-2.26c.78.21 1.61.26 2.39.26 3.87 0 7-3.13 7-7s-3.13-7-7-7z" fill="#fff"/></svg>
                                Nhắn Zalo
                            </a>
                        <?php endif; ?>
                    </div>

                    <!-- QR Zalo -->
                    <?php if (!empty($settings['show_qr_zalo']) && !empty($settings['qr_zalo_image'])) : ?>
                        <div class="vnf-contact-qr" style="margin-top:24px;padding-top:24px;border-top:1px solid #eee;text-align:center;">
                            <h4 style="margin:0 0 12px;color:#333;">Quét QR để thêm Zalo</h4>
                            <img src="<?php echo esc_url($settings['qr_zalo_image']); ?>" alt="Zalo QR Code" style="max-width:140px;border-radius:8px;">
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Map Section -->
        <?php if (!empty($settings['map_embed'])) : ?>
            <div class="vnf-contact-map" style="background:#fff;border-radius:12px;padding:32px;box-shadow:0 2px 16px rgba(0,0,0,.08);margin-top:32px;">
                <h3 style="margin:0 0 20px;color:#2d5016;">Tìm chúng tôi trên bản đồ</h3>
                <div class="vnf-contact-map-embed" style="border-radius:8px;overflow:hidden;width:100%;">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d31091.55568277558!2d108.0057!3d13.9385!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x316c21ce86f444a3%3A0x75f0f45f6027cf2c!2sPleiku%2C%20Gia%20Lai!5e0!3m2!1sen!2s!4v1600000000000!5m2!1sen!2s"
                        width="100%"
                        height="400"
                        style="border:0;"
                        allowfullscreen=""
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php
    return ob_get_clean();
}