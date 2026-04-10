<?php
/**
 * Plugin Name: VietFarmy Slideshow
 * Description: Plugin tạo slideshow ảnh với hỗ trợ URL, tệp upload, nhiều slideshow, kéo thả sắp xếp.
 * Version: 2.0.0
 * Author: VietFarmy
 * Text Domain: vnf-slideshow
 */

if (!defined('ABSPATH')) exit;

define('VNF_PLUGIN_URL', plugin_dir_url(__FILE__));
define('VNF_PLUGIN_DIR', plugin_dir_path(__FILE__));

// ================================================================
// TABLE INSTALL / UNINSTALL
// ================================================================
register_activation_hook(__FILE__, 'vnf_sw_install');
register_deactivation_hook(__FILE__, 'vnf_sw_uninstall');

function vnf_sw_install() {
    global $wpdb;
    $table = $wpdb->prefix . 'vnf_slideshows';
    $table2 = $wpdb->prefix . 'vnf_slides';

    $sql = "CREATE TABLE IF NOT EXISTS $table (
        id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        name       VARCHAR(255)    NOT NULL DEFAULT '',
        slug       VARCHAR(128)    NOT NULL DEFAULT 'default',
        settings   TEXT            NOT NULL,
        created_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uk_slug (slug)
    ) {$wpdb->get_charset_collate()};";

    $sql2 = "CREATE TABLE IF NOT EXISTS $table2 (
        id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        slideshow_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
        title        VARCHAR(255)    NOT NULL DEFAULT '',
        description  TEXT            NOT NULL,
        image_url    VARCHAR(1000)   NOT NULL DEFAULT '',
        image_id     BIGINT UNSIGNED NOT NULL DEFAULT 0,
        alt_text     VARCHAR(255)    NOT NULL DEFAULT '',
        link_url     VARCHAR(1000)   NOT NULL DEFAULT '',
        link_target  TINYINT(1)      NOT NULL DEFAULT 0,
        order_num    INT             NOT NULL DEFAULT 0,
        status       TINYINT(1)      NOT NULL DEFAULT 1,
        created_at   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        KEY idx_slideshow (slideshow_id, order_num),
        KEY idx_status (status)
    ) {$wpdb->get_charset_collate()};";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    dbDelta($sql2);

    // Seed default slideshow
    $row = $wpdb->get_row("SELECT id FROM $table WHERE slug = 'default'");
    if (!$row) {
        $wpdb->insert($table, array(
            'name'     => 'Slideshow Mặc Định',
            'slug'     => 'default',
            'settings' => json_encode(array(
                'autoplay'    => 1,
                'speed'       => 4000,
                'height'      => 480,
                'transition'  => 'fade',
                'nav'         => 1,
                'dots'        => 1,
                'caption'     => 1,
            )),
        ));
    }

    // Set capability
    $role = get_role('administrator');
    if ($role && !$role->has_cap('vnf_sw_manage')) {
        $role->add_cap('vnf_sw_manage');
    }
}

function vnf_sw_uninstall() {
    // Clean up only if explicitly requested
    // global $wpdb;
    // $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}vnf_slides");
    // $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}vnf_slideshows");
}

// ================================================================
// INCLUDES
// ================================================================
require_once VNF_PLUGIN_DIR . 'includes/admin.php';
require_once VNF_PLUGIN_DIR . 'includes/frontend.php';
require_once VNF_PLUGIN_DIR . 'includes/ajax.php';

// ================================================================
// ENQUEUE ASSETS
// ================================================================
add_action('wp_enqueue_scripts', 'vnf_sw_enqueue_frontend');
add_action('admin_enqueue_scripts', 'vnf_sw_enqueue_admin');

function vnf_sw_enqueue_admin($hook) {
    if (strpos($hook, 'vnf-slideshow') === false) return;

    wp_enqueue_style('vnf-sw-admin', VNF_PLUGIN_URL . 'assets/css/admin.css', array(), '2.0.0');
    wp_enqueue_media();
    wp_enqueue_script('vnf-sw-admin', VNF_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), '2.0.0', true);

    $slideshows = vnf_sw_get_slideshows();
    $current_id = isset($_GET['slideshow_id']) ? (int) $_GET['slideshow_id'] : ($slideshows ? $slideshows[0]->id : 0);
    $slides = $current_id ? vnf_sw_get_slides($current_id) : array();
    $settings = $current_id ? vnf_sw_get_settings($current_id) : array();

    wp_localize_script('vnf-sw-admin', 'vnf_sw_admin', array(
        'ajax_url'   => admin_url('admin-ajax.php'),
        'nonce'      => wp_create_nonce('vnf_sw_nonce'),
        'slides'     => $slides,
        'settings'   => $settings,
        'slideshow_id' => $current_id,
        'strings'    => array(
            'confirm_delete'  => __('Bạn có chắc muốn xóa slide này?', 'vnf-slideshow'),
            'confirm_delete2' => __('Xóa slideshow này sẽ xóa toàn bộ slide bên trong. Tiếp tục?', 'vnf-slideshow'),
            'saving'          => __('Đang lưu…', 'vnf-slideshow'),
            'saved'           => __('Đã lưu!', 'vnf-slideshow'),
            'no_slides'       => __('Chưa có slide nào. Thêm slide đầu tiên.', 'vnf-slideshow'),
            'error'           => __('Đã xảy ra lỗi. Vui lòng thử lại.', 'vnf-slideshow'),
        ),
    ));
}

function vnf_sw_enqueue_frontend() {
    // CSS and JS are inline in shortcode output - no external files needed
}

// ================================================================
// HELPERS
// ================================================================
function vnf_sw_get_slideshows() {
    global $wpdb;
    return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}vnf_slideshows ORDER BY id ASC");
}

function vnf_sw_get_slides($slideshow_id) {
    global $wpdb;
    return $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}vnf_slides WHERE slideshow_id = %d AND status = 1 ORDER BY order_num ASC",
        $slideshow_id
    ));
}

function vnf_sw_get_settings($slideshow_id) {
    global $wpdb;
    $row = $wpdb->get_row($wpdb->prepare(
        "SELECT settings FROM {$wpdb->prefix}vnf_slideshows WHERE id = %d",
        $slideshow_id
    ));
    if (!$row) return array();
    $decoded = json_decode($row->settings, true);
    return is_array($decoded) ? $decoded : array();
}

function vnf_sw_render_slide_image($slide) {
    if (!empty($slide->image_url)) {
        return esc_url($slide->image_url);
    } elseif (!empty($slide->image_id)) {
        return wp_get_attachment_image_url($slide->image_id, 'full');
    }
    return '';
}
