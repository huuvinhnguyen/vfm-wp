<?php
/**
 * Plugin Name: VietFarmy Gallery
 * Description: Plugin quản lý thư viện ảnh với hỗ trợ URL, tệp upload, nhiều gallery, sắp xếp ảnh, lightbox.
 * Version: 1.0.0
 * Author: VietFarmy
 * Text Domain: vnf-gallery
 */

if (!defined('ABSPATH')) exit;

define('VNF_GALLERY_URL', plugin_dir_url(__FILE__));
define('VNF_GALLERY_DIR', plugin_dir_path(__FILE__));

// ================================================================
// TABLE INSTALL / UNINSTALL
// ================================================================
register_activation_hook(__FILE__, 'vnf_gl_install');
register_deactivation_hook(__FILE__, 'vnf_gl_uninstall');

function vnf_gl_install() {
    global $wpdb;
    $table = $wpdb->prefix . 'vnf_galleries';
    $table2 = $wpdb->prefix . 'vnf_gallery_images';

    $sql = "CREATE TABLE IF NOT EXISTS $table (
        id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        name        VARCHAR(255) NOT NULL DEFAULT '',
        slug        VARCHAR(128) NOT NULL DEFAULT 'default',
        layout      VARCHAR(32) NOT NULL DEFAULT 'grid',
        columns     TINYINT UNSIGNED NOT NULL DEFAULT 4,
        settings    TEXT NOT NULL,
        created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uk_slug (slug)
    ) {$wpdb->get_charset_collate()};";

    $sql2 = "CREATE TABLE IF NOT EXISTS $table2 (
        id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        gallery_id  BIGINT UNSIGNED NOT NULL DEFAULT 0,
        title       VARCHAR(255) NOT NULL DEFAULT '',
        description TEXT NOT NULL,
        image_url   VARCHAR(1000) NOT NULL DEFAULT '',
        image_id    BIGINT UNSIGNED NOT NULL DEFAULT 0,
        alt_text    VARCHAR(255) NOT NULL DEFAULT '',
        thumb_url   VARCHAR(1000) NOT NULL DEFAULT '',
        link_url    VARCHAR(1000) NOT NULL DEFAULT '',
        order_num   INT NOT NULL DEFAULT 0,
        status      TINYINT(1) NOT NULL DEFAULT 1,
        created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        KEY idx_gallery (gallery_id, order_num),
        KEY idx_status (status)
    ) {$wpdb->get_charset_collate()};";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    dbDelta($sql2);

    // Seed default gallery
    $row = $wpdb->get_row("SELECT id FROM $table WHERE slug = 'default'");
    if (!$row) {
        $wpdb->insert($table, array(
            'name'     => 'Gallery Mặc Định',
            'slug'     => 'default',
            'layout'   => 'grid',
            'columns'  => 4,
            'settings' => json_encode(array(
                'spacing'     => 8,
                'border_radius' => 4,
                'show_title'  => 0,
                'show_desc'   => 0,
                'lightbox'    => 1,
                'lazyload'    => 1,
            )),
        ));
    }

    // Set capability
    $role = get_role('administrator');
    if ($role && !$role->has_cap('vnf_gl_manage')) {
        $role->add_cap('vnf_gl_manage');
    }
}

function vnf_gl_uninstall() {
    // Optionally clean up tables on uninstall
}

// ================================================================
// INCLUDES
// ================================================================
require_once VNF_GALLERY_DIR . 'includes/admin.php';
require_once VNF_GALLERY_DIR . 'includes/frontend.php';
require_once VNF_GALLERY_DIR . 'includes/ajax.php';

// ================================================================
// ENQUEUE ASSETS
// ================================================================
add_action('wp_enqueue_scripts', 'vnf_gl_enqueue_frontend');
add_action('admin_enqueue_scripts', 'vnf_gl_enqueue_admin');

function vnf_gl_enqueue_admin($hook) {
    if (strpos($hook, 'vnf-gallery') === false) return;

    wp_enqueue_style('vnf-gl-admin', VNF_GALLERY_URL . 'assets/css/admin.css', array(), '1.0.0');
    wp_enqueue_media();
    wp_enqueue_script('vnf-gl-admin', VNF_GALLERY_URL . 'assets/js/admin.js', array('jquery'), '1.0.0', true);

    $galleries = vnf_gl_get_galleries();
    $current_id = isset($_GET['gallery_id']) ? (int) $_GET['gallery_id'] : ($galleries ? $galleries[0]->id : 0);
    $images = $current_id ? vnf_gl_get_images($current_id) : array();
    $settings = $current_id ? vnf_gl_get_settings($current_id) : array();

    wp_localize_script('vnf-gl-admin', 'vnf_gl_admin', array(
        'ajax_url'    => admin_url('admin-ajax.php'),
        'nonce'       => wp_create_nonce('vnf_gl_nonce'),
        'images'      => $images,
        'settings'    => $settings,
        'gallery_id'  => $current_id,
        'strings'     => array(
            'confirm_delete'  => __('Bạn có chắc muốn xóa ảnh này?', 'vnf-gallery'),
            'confirm_delete2' => __('Xóa gallery này sẽ xóa toàn bộ ảnh bên trong. Tiếp tục?', 'vnf-gallery'),
            'saving'          => __('Đang lưu…', 'vnf-gallery'),
            'saved'           => __('Đã lưu!', 'vnf-gallery'),
            'no_images'       => __('Chưa có ảnh nào. Thêm ảnh đầu tiên.', 'vnf-gallery'),
            'error'           => __('Đã xảy ra lỗi. Vui lòng thử lại.', 'vnf-gallery'),
            'select_image'    => __('Chọn hình ảnh', 'vnf-gallery'),
            'upload_image'    => __('Tải lên ảnh mới', 'vnf-gallery'),
        ),
    ));
}

function vnf_gl_enqueue_frontend() {
    // Only enqueue if a gallery is present on the page
    global $post;
    if (!$post) return;

    $has_gallery = has_shortcode($post->post_content, 'vnf_gallery');
    if (!$has_gallery) {
        // Also check in widgets or other content
        $has_gallery = is_singular() && has_shortcode($post->post_content, 'vnf_gallery');
    }

    if ($has_gallery || apply_filters('vnf_gl_force_enqueue', false)) {
        wp_enqueue_style('vnf-gl-lightbox', VNF_GALLERY_URL . 'assets/css/lightbox.min.css', array(), '1.0.0');
        wp_enqueue_script('vnf-gl-lightbox', VNF_GALLERY_URL . 'assets/js/lightbox.min.js', array('jquery'), '1.0.0', true);
        wp_enqueue_style('vnf-gl-frontend', VNF_GALLERY_URL . 'assets/css/frontend.css', array(), '1.0.0');
    }
}

// ================================================================
// HELPERS
// ================================================================
function vnf_gl_get_galleries() {
    global $wpdb;
    return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}vnf_galleries ORDER BY id ASC");
}

function vnf_gl_get_images($gallery_id) {
    global $wpdb;
    return $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}vnf_gallery_images WHERE gallery_id = %d AND status = 1 ORDER BY order_num ASC",
        $gallery_id
    ));
}

function vnf_gl_get_settings($gallery_id) {
    global $wpdb;
    $row = $wpdb->get_row($wpdb->prepare(
        "SELECT settings FROM {$wpdb->prefix}vnf_galleries WHERE id = %d",
        $gallery_id
    ));
    if (!$row) return array();
    $decoded = json_decode($row->settings, true);
    return is_array($decoded) ? $decoded : array();
}

function vnf_gl_render_image($image) {
    if (!empty($image->thumb_url)) {
        return esc_url($image->thumb_url);
    } elseif (!empty($image->image_url)) {
        return esc_url($image->image_url);
    } elseif (!empty($image->image_id)) {
        return wp_get_attachment_image_url($image->image_id, 'thumbnail');
    }
    return '';
}

function vnf_gl_render_full_image($image) {
    if (!empty($image->image_url)) {
        return esc_url($image->image_url);
    } elseif (!empty($image->image_id)) {
        return wp_get_attachment_image_url($image->image_id, 'full');
    }
    return '';
}