<?php
/**
 * Plugin Name: VietFarmy Contact
 * Description: Trang Liên hệ chuyên nghiệp với form, bản đồ, Zalo/Call button
 * Version: 1.0.0
 * Author: VietFarmy
 * Text Domain: vnf-contact
 */

if (!defined('ABSPATH')) exit;

define('VNF_CONTACT_URL', plugin_dir_url(__FILE__));
define('VNF_CONTACT_DIR', plugin_dir_path(__FILE__));

// ================================================================
// ACTIVATION / SETTINGS
// ================================================================
register_activation_hook(__FILE__, 'vnf_contact_activate');

function vnf_contact_activate() {
    // Default settings
    $defaults = array(
        'welcome_msg'    => 'Kết nối với VietFarmy – Mang hương vị Gia Lai về ngôi nhà của bạn.',
        'address'        => 'VietFarmy Roastery: 123 Đường Nguyễn Trãi, P. Yên Đỗ, TP. Pleiku, Gia Lai',
        'phone'          => '0901 234 567',
        'phone_display'  => '0901 234 567',
        'zalo_id'        => '0901234567',
        'email'          => 'vinhnguyen@vietfarmy.vn',
        'working_time'    => 'Thứ 2 - Chủ nhật | 08:00 - 18:00',
        'facebook_url'   => 'https://www.facebook.com/vietfarmy',
        'facebook_name'  => 'VietFarmy Roastery - Cà Phê Rang Mộc',
        'map_embed'       => '',
        'image_trust'    => '',
        'welcome_note'   => 'Chào đón bạn ghé thăm và thưởng thức cà phê trực tiếp tại xưởng rang.',
        'show_floating'  => 1,
        'show_qr_zalo'   => 0,
    );

    if (!get_option('vnf_contact_settings')) {
        add_option('vnf_contact_settings', $defaults);
    }

    // Create messages table
    global $wpdb;
    $table = $wpdb->prefix . 'vnf_contact_messages';

    $sql = "CREATE TABLE IF NOT EXISTS $table (
        id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        name        VARCHAR(255) NOT NULL,
        phone       VARCHAR(32) NOT NULL,
        subject     VARCHAR(255) NOT NULL DEFAULT '',
        message     TEXT NOT NULL,
        ip          VARCHAR(45) NOT NULL DEFAULT '',
        status      TINYINT(1) NOT NULL DEFAULT 0,
        created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        KEY idx_status (status),
        KEY idx_created (created_at)
    ) {$wpdb->get_charset_collate()};";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // Add capability
    $role = get_role('administrator');
    if ($role && !$role->has_cap('vnf_contact_manage')) {
        $role->add_cap('vnf_contact_manage');
    }
}

// ================================================================
// INCLUDES
// ================================================================
require_once VNF_CONTACT_DIR . 'includes/admin.php';
require_once VNF_CONTACT_DIR . 'includes/frontend.php';

// ================================================================
// HIDE SIDEBAR ON CONTACT PAGE
// ================================================================
add_filter('gema_show_sidebar', 'vnf_contact_hide_sidebar_filter', 10, 2);

function vnf_contact_hide_sidebar_filter($show, $post_id) {
    if ($post_id) {
        $post = get_post($post_id);
        if ($post && has_shortcode($post->post_content, 'vnf_contact')) {
            return false;
        }
    }
    return $show;
}

// Alternative: use theme's content width filter
add_filter('gema_main_content_width', function($width) {
    if (is_singular('page')) {
        global $post;
        if ($post && has_shortcode($post->post_content, 'vnf_contact')) {
            return 100; // Full width
        }
    }
    return $width;
});

// Load full width template for contact page
add_filter('template_include', function($template) {
    if (is_singular('page')) {
        global $post;
        if ($post && has_shortcode($post->post_content, 'vnf_contact')) {
            $custom_template = VNF_CONTACT_DIR . 'templates/fullwidth-contact.php';
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }
    }
    return $template;
});

// ================================================================
// ENQUEUE ASSETS
// ================================================================
add_action('wp_enqueue_scripts', 'vnf_contact_enqueue');
add_action('admin_enqueue_scripts', 'vnf_contact_admin_enqueue');

function vnf_contact_enqueue() {
    $settings = get_option('vnf_contact_settings', array());

    // Load CSS/JS on contact page or when shortcode is present
    global $post;
    $has_shortcode = false;

    if (is_singular('page') && $post) {
        $has_shortcode = has_shortcode($post->post_content, 'vnf_contact');
    }

    if ($has_shortcode || is_page('lien-he') || is_page('contact')) {
        wp_enqueue_style('vnf-contact', VNF_CONTACT_URL . 'assets/css/contact.css', array(), '1.0.0');
        wp_enqueue_script('vnf-contact', VNF_CONTACT_URL . 'assets/js/contact.js', array('jquery'), '1.0.0', true);

        wp_localize_script('vnf-contact', 'vnfContact', array(
            'ajaxUrl'  => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('vnf_contact_nonce'),
            'settings' => $settings,
        ));
    }
}

function vnf_contact_admin_enqueue($hook) {
    if (strpos($hook, 'vnf-contact') === false) return;

    wp_enqueue_style('vnf-contact-admin', VNF_CONTACT_URL . 'assets/css/admin.css', array(), '1.0.0');
    wp_enqueue_media();
    wp_enqueue_script('vnf-contact-admin', VNF_CONTACT_URL . 'assets/js/admin.js', array('jquery'), '1.0.0', true);
}

// ================================================================
// SHORTCODES
// ================================================================
add_shortcode('vnf_contact', 'vnf_contact_shortcode');

// Helper to get settings
function vnf_contact_get_settings() {
    return get_option('vnf_contact_settings', array());
}