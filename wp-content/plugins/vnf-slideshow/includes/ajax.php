<?php
/**
 * AJAX handlers for VietFarmy Slideshow
 */

if (!defined('ABSPATH')) exit;

add_action('wp_ajax_vnf_sw_save_slide', 'vnf_sw_ajax_save_slide');
add_action('wp_ajax_vnf_sw_delete_slide', 'vnf_sw_ajax_delete_slide');
add_action('wp_ajax_vnf_sw_reorder_slides', 'vnf_sw_ajax_reorder_slides');
add_action('wp_ajax_vnf_sw_save_settings', 'vnf_sw_ajax_save_settings');
add_action('wp_ajax_vnf_sw_delete_slideshow', 'vnf_sw_ajax_delete_slideshow');
add_action('wp_ajax_vnf_sw_get_slides', 'vnf_sw_ajax_get_slides');

function vnf_sw_require_nonce() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'vnf_sw_nonce')) {
        wp_send_json_error(array('message' => 'Unauthorized'));
    }
    if (!current_user_can('vnf_sw_manage')) {
        wp_send_json_error(array('message' => 'No permission'));
    }
}

function vnf_sw_ajax_save_slide() {
    vnf_sw_require_nonce();

    global $wpdb;
    $table  = $wpdb->prefix . 'vnf_slides';
    $sid    = (int) ($_POST['slideshow_id'] ?? 0);
    $edit_id = (int) ($_POST['edit_id'] ?? 0);

    if (!$sid) {
        wp_send_json_error(array('message' => 'Thiếu slideshow ID.'));
    }

    $data = array(
        'slideshow_id' => $sid,
        'title'        => sanitize_text_field($_POST['title'] ?? ''),
        'description'  => sanitize_textarea_field($_POST['description'] ?? ''),
        'image_url'    => esc_url_raw($_POST['image_url'] ?? ''),
        'image_id'     => (int) ($_POST['image_id'] ?? 0),
        'alt_text'     => sanitize_text_field($_POST['alt_text'] ?? ''),
        'link_url'     => esc_url_raw($_POST['link_url'] ?? ''),
        'link_target'  => !empty($_POST['link_target']) ? 1 : 0,
        'status'       => 1,
    );

    if ($edit_id) {
        $data['updated_at'] = current_time('mysql');
        $wpdb->update($table, $data, array('id' => $edit_id));
        $result_id = $edit_id;
    } else {
        // Get max order
        $max_order = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT MAX(order_num) FROM $table WHERE slideshow_id = %d", $sid
        ));
        $data['order_num'] = $max_order + 1;
        $wpdb->insert($table, $data);
        $result_id = $wpdb->insert_id;
    }

    if ($result_id) {
        $slide = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $result_id));
        $slide->image = vnf_sw_render_slide_image($slide);
        wp_send_json_success(array('slide' => $slide));
    } else {
        wp_send_json_error(array('message' => 'Lưu thất bại.'));
    }
}

function vnf_sw_ajax_delete_slide() {
    vnf_sw_require_nonce();

    global $wpdb;
    $id = (int) ($_POST['id'] ?? 0);
    if (!$id) {
        wp_send_json_error(array('message' => 'Thiếu slide ID.'));
    }

    $wpdb->delete($wpdb->prefix . 'vnf_slides', array('id' => $id));
    wp_send_json_success();
}

function vnf_sw_ajax_reorder_slides() {
    vnf_sw_require_nonce();

    global $wpdb;
    $table = $wpdb->prefix . 'vnf_slides';
    $order = $_POST['order'] ?? array();

    if (is_array($order)) {
        foreach ($order as $i => $id) {
            $wpdb->update($table, array('order_num' => (int) $i), array('id' => (int) $id));
        }
    }

    wp_send_json_success();
}

function vnf_sw_ajax_save_settings() {
    vnf_sw_require_nonce();

    global $wpdb;
    $sid = (int) ($_POST['slideshow_id'] ?? 0);
    if (!$sid) {
        wp_send_json_error(array('message' => 'Thiếu slideshow ID.'));
    }

    $data = array(
        'autoplay'   => !empty($_POST['autoplay']) ? 1 : 0,
        'speed'      => max(500, (int) ($_POST['speed'] ?? 4000)),
        'height'     => max(200, (int) ($_POST['height'] ?? 480)),
        'transition' => sanitize_key($_POST['transition'] ?? 'fade'),
        'nav'        => !empty($_POST['nav']) ? 1 : 0,
        'dots'       => !empty($_POST['dots']) ? 1 : 0,
        'caption'    => !empty($_POST['caption']) ? 1 : 0,
    );

    $wpdb->update(
        $wpdb->prefix . 'vnf_slideshows',
        array('settings' => json_encode($data), 'updated_at' => current_time('mysql')),
        array('id' => $sid)
    );

    wp_send_json_success(array('settings' => $data));
}

function vnf_sw_ajax_delete_slideshow() {
    vnf_sw_require_nonce();

    global $wpdb;
    $sid = (int) ($_POST['id'] ?? 0);
    if (!$sid || $sid <= 0) {
        wp_send_json_error(array('message' => 'Invalid ID.'));
    }

    // Don't delete default slideshow
    $row = $wpdb->get_row($wpdb->prepare(
        "SELECT slug FROM {$wpdb->prefix}vnf_slideshows WHERE id = %d", $sid
    ));
    if ($row && $row->slug === 'default') {
        wp_send_json_error(array('message' => 'Không thể xóa slideshow mặc định.'));
    }

    $wpdb->delete($wpdb->prefix . 'vnf_slides', array('slideshow_id' => $sid));
    $wpdb->delete($wpdb->prefix . 'vnf_slideshows', array('id' => $sid));

    wp_send_json_success();
}

function vnf_sw_ajax_get_slides() {
    vnf_sw_require_nonce();

    $sid = (int) ($_POST['slideshow_id'] ?? 0);
    if (!$sid) {
        wp_send_json_error(array('message' => 'Thiếu slideshow ID.'));
    }

    $slides = vnf_sw_get_slides($sid);
    $html = vnf_sw_render_slides_list_html($slides);
    wp_send_json_success(array('html' => $html, 'slides' => $slides));
}

function vnf_sw_render_slides_list_html($slides) {
    if (empty($slides)) {
        return '<p class="vnf-sw-no-slides">' . __('Chưa có slide nào. Thêm slide đầu tiên ở trên.', 'vnf-slideshow') . '</p>';
    }

    $html = '<ul class="vnf-sw-sortable" id="vnf-sw-sortable">';
    foreach ($slides as $idx => $slide) :
        $img = vnf_sw_render_slide_image($slide);
        $html .= '<li class="vnf-sw-slide-item" data-id="' . $slide->id . '" data-order="' . $idx . '">';
        $html .= '<span class="vnf-sw-drag-handle" title="Kéo để sắp xếp">&#9776;</span>';
        $html .= '<div class="vnf-sw-slide-thumb">';
        if ($img) {
            $html .= '<img src="' . esc_url($img) . '" alt="">';
        } else {
            $html .= '<div class="vnf-sw-no-img">Không có ảnh</div>';
        }
        $html .= '</div>';
        $html .= '<div class="vnf-sw-slide-info">';
        $html .= '<strong>' . esc_html($slide->title) . '</strong>';
        if ($slide->description) {
            $html .= '<p>' . esc_html($slide->description) . '</p>';
        }
        if ($slide->link_url) {
            $html .= '<span class="vnf-sw-link-badge"><span class="dashicons dashicons-admin-links"></span>' . esc_url($slide->link_url) . '</span>';
        }
        $html .= '</div>';
        $html .= '<div class="vnf-sw-slide-actions">';
        $html .= '<button type="button" class="vnf-sw-edit-btn button-small button" title="Sửa" data-id="' . $slide->id . '"><span class="dashicons dashicons-edit"></span></button>';
        $html .= '<button type="button" class="vnf-sw-delete-btn button-small button" title="Xóa" data-id="' . $slide->id . '"><span class="dashicons dashicons-trash"></span></button>';
        $html .= '</div>';
        $html .= '</li>';
    endforeach;
    $html .= '</ul>';

    return $html;
}