<?php
/**
 * AJAX handlers for VietFarmy Gallery
 */

if (!defined('ABSPATH')) exit;

add_action('wp_ajax_vnf_gl_save_image', 'vnf_gl_ajax_save_image');
add_action('wp_ajax_vnf_gl_delete_image', 'vnf_gl_ajax_delete_image');
add_action('wp_ajax_vnf_gl_reorder_images', 'vnf_gl_ajax_reorder_images');
add_action('wp_ajax_vnf_gl_save_settings', 'vnf_gl_ajax_save_settings');
add_action('wp_ajax_vnf_gl_delete_gallery', 'vnf_gl_ajax_delete_gallery');
add_action('wp_ajax_vnf_gl_get_images', 'vnf_gl_ajax_get_images');

function vnf_gl_require_nonce() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'vnf_gl_nonce')) {
        wp_send_json_error(array('message' => 'Unauthorized'));
    }
    if (!current_user_can('vnf_gl_manage')) {
        wp_send_json_error(array('message' => 'No permission'));
    }
}

function vnf_gl_ajax_save_image() {
    vnf_gl_require_nonce();

    global $wpdb;
    $table  = $wpdb->prefix . 'vnf_gallery_images';
    $gid    = (int) ($_POST['gallery_id'] ?? 0);
    $edit_id = (int) ($_POST['edit_id'] ?? 0);

    if (!$gid) {
        wp_send_json_error(array('message' => 'Thiếu gallery ID.'));
    }

    $data = array(
        'gallery_id'  => $gid,
        'title'       => sanitize_text_field($_POST['title'] ?? ''),
        'description' => sanitize_textarea_field($_POST['description'] ?? ''),
        'image_url'   => esc_url_raw($_POST['image_url'] ?? ''),
        'image_id'    => (int) ($_POST['image_id'] ?? 0),
        'thumb_url'   => esc_url_raw($_POST['thumb_url'] ?? ''),
        'alt_text'    => sanitize_text_field($_POST['alt_text'] ?? ''),
        'link_url'    => esc_url_raw($_POST['link_url'] ?? ''),
        'status'      => 1,
    );

    if ($edit_id) {
        $data['updated_at'] = current_time('mysql');
        $wpdb->update($table, $data, array('id' => $edit_id));
        $result_id = $edit_id;
    } else {
        // Get max order
        $max_order = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT MAX(order_num) FROM $table WHERE gallery_id = %d", $gid
        ));
        $data['order_num'] = $max_order + 1;
        $wpdb->insert($table, $data);
        $result_id = $wpdb->insert_id;
    }

    if ($result_id) {
        $image = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $result_id));
        $image->thumb = vnf_gl_render_image($image);
        wp_send_json_success(array('image' => $image));
    } else {
        wp_send_json_error(array('message' => 'Lưu thất bại.'));
    }
}

function vnf_gl_ajax_delete_image() {
    vnf_gl_require_nonce();

    global $wpdb;
    $id = (int) ($_POST['id'] ?? 0);
    if (!$id) {
        wp_send_json_error(array('message' => 'Thiếu image ID.'));
    }

    $wpdb->delete($wpdb->prefix . 'vnf_gallery_images', array('id' => $id));
    wp_send_json_success();
}

function vnf_gl_ajax_reorder_images() {
    vnf_gl_require_nonce();

    global $wpdb;
    $table = $wpdb->prefix . 'vnf_gallery_images';
    $order = $_POST['order'] ?? array();

    if (is_array($order)) {
        foreach ($order as $i => $id) {
            $wpdb->update($table, array('order_num' => (int) $i), array('id' => (int) $id));
        }
    }

    wp_send_json_success();
}

function vnf_gl_ajax_save_settings() {
    vnf_gl_require_nonce();

    global $wpdb;
    $gid = (int) ($_POST['gallery_id'] ?? 0);
    if (!$gid) {
        wp_send_json_error(array('message' => 'Thiếu gallery ID.'));
    }

    $data = array(
        'layout'      => sanitize_key($_POST['layout'] ?? 'grid'),
        'columns'     => max(2, min(6, (int) ($_POST['columns'] ?? 4))),
        'settings'    => json_encode(array(
            'spacing'       => max(0, (int) ($_POST['spacing'] ?? 8)),
            'border_radius' => max(0, (int) ($_POST['border_radius'] ?? 4)),
            'show_title'    => !empty($_POST['show_title']) ? 1 : 0,
            'show_desc'     => !empty($_POST['show_desc']) ? 1 : 0,
            'lightbox'      => !empty($_POST['lightbox']) ? 1 : 0,
            'lazyload'      => !empty($_POST['lazyload']) ? 1 : 0,
        )),
        'updated_at'  => current_time('mysql'),
    );

    $wpdb->update($wpdb->prefix . 'vnf_galleries', $data, array('id' => $gid));

    wp_send_json_success(array('settings' => json_decode($data['settings'], true)));
}

function vnf_gl_ajax_delete_gallery() {
    vnf_gl_require_nonce();

    global $wpdb;
    $gid = (int) ($_POST['id'] ?? 0);
    if (!$gid || $gid <= 0) {
        wp_send_json_error(array('message' => 'Invalid ID.'));
    }

    // Don't delete default gallery
    $row = $wpdb->get_row($wpdb->prepare(
        "SELECT slug FROM {$wpdb->prefix}vnf_galleries WHERE id = %d", $gid
    ));
    if ($row && $row->slug === 'default') {
        wp_send_json_error(array('message' => 'Không thể xóa gallery mặc định.'));
    }

    $wpdb->delete($wpdb->prefix . 'vnf_gallery_images', array('gallery_id' => $gid));
    $wpdb->delete($wpdb->prefix . 'vnf_galleries', array('id' => $gid));

    wp_send_json_success();
}

function vnf_gl_ajax_get_images() {
    vnf_gl_require_nonce();

    $gid = (int) ($_POST['gallery_id'] ?? 0);
    if (!$gid) {
        wp_send_json_error(array('message' => 'Thiếu gallery ID.'));
    }

    $images = vnf_gl_get_images($gid);
    $html = vnf_gl_render_images_list_html($images);
    wp_send_json_success(array('html' => $html, 'images' => $images));
}

function vnf_gl_render_images_list_html($images) {
    if (empty($images)) {
        return '<p class="vnf-gl-no-images">' . __('Chưa có ảnh nào. Thêm ảnh đầu tiên ở trên.', 'vnf-gallery') . '</p>';
    }

    $html = '<ul class="vnf-gl-sortable" id="vnf-gl-sortable">';
    foreach ($images as $idx => $image) :
        $img = vnf_gl_render_image($image);
        $html .= '<li class="vnf-gl-image-item" data-id="' . $image->id . '" data-order="' . $idx . '">';
        $html .= '<span class="vnf-gl-drag-handle" title="Kéo để sắp xếp">&#9776;</span>';
        $html .= '<div class="vnf-gl-image-thumb">';
        if ($img) {
            $html .= '<img src="' . esc_url($img) . '" alt="">';
        } else {
            $html .= '<div class="vnf-gl-no-img">Không có ảnh</div>';
        }
        $html .= '</div>';
        $html .= '<div class="vnf-gl-image-info">';
        $html .= '<strong>' . esc_html($image->title) . '</strong>';
        if ($image->description) {
            $html .= '<p>' . esc_html($image->description) . '</p>';
        }
        if ($image->link_url) {
            $html .= '<span class="vnf-gl-link-badge"><span class="dashicons dashicons-admin-links"></span>' . esc_url($image->link_url) . '</span>';
        }
        $html .= '</div>';
        $html .= '<div class="vnf-gl-image-actions">';
        $html .= '<button type="button" class="vnf-gl-edit-btn button-small button" title="Sửa" data-id="' . $image->id . '"><span class="dashicons dashicons-edit"></span></button>';
        $html .= '<button type="button" class="vnf-gl-delete-btn button-small button" title="Xóa" data-id="' . $image->id . '"><span class="dashicons dashicons-trash"></span></button>';
        $html .= '</div>';
        $html .= '</li>';
    endforeach;
    $html .= '</ul>';

    return $html;
}