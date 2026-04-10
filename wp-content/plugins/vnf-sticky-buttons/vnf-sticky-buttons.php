<?php
/**
 * Plugin Name: VNF Sticky Buttons
 * Plugin URI: https://vietfarmy.com
 * Description: Sticky Call & Zalo buttons for VietFarmy website
 * Version: 1.0.0
 * Author: VietFarmy
 * Author URI: https://vietfarmy.com
 * Text Domain: vnf-sticky-buttons
 */

if (!defined('ABSPATH')) exit;

// ============================================================
// CUSTOMIZER SETTINGS
// ============================================================
add_action('customize_register', 'vnf_sb_customize_register');

function vnf_sb_customize_register($wp_customize) {

    // Section
    $wp_customize->add_section('vnf_sticky_buttons', array(
        'title'    => '🔘 Sticky Buttons',
        'priority' => 999,
        'description' => 'Cấu hình nút Sticky Gọi điện & Zalo',
    ));

    // ── Enable / Disable ──
    $wp_customize->add_setting('vnf_sb_enable', array(
        'type'              => 'theme_mod',
        'transport'         => 'refresh',
        'sanitize_callback' => 'absint',
        'default'           => 1,
    ));
    $wp_customize->add_control('vnf_sb_enable', array(
        'label'    => 'Bật Sticky Buttons',
        'section'  => 'vnf_sticky_buttons',
        'type'     => 'checkbox',
    ));

    // ── Phone Number ──
    $wp_customize->add_setting('vnf_sb_phone', array(
        'type'              => 'theme_mod',
        'transport'         => 'refresh',
        'sanitize_callback' => 'sanitize_text_field',
        'default'           => '0906680182',
    ));
    $wp_customize->add_control('vnf_sb_phone', array(
        'label'    => 'Số điện thoại',
        'section'  => 'vnf_sticky_buttons',
        'type'     => 'text',
    ));

    // ── Call Button Color ──
    $wp_customize->add_setting('vnf_sb_call_color', array(
        'type'              => 'theme_mod',
        'transport'         => 'refresh',
        'sanitize_callback' => 'sanitize_hex_color',
        'default'           => '#2d6a4f',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'vnf_sb_call_color', array(
        'label'    => 'Màu nút Gọi điện',
        'section'  => 'vnf_sticky_buttons',
    )));

    // ── Call Button Size ──
    $wp_customize->add_setting('vnf_sb_call_size', array(
        'type'              => 'theme_mod',
        'transport'         => 'refresh',
        'sanitize_callback' => 'absint',
        'default'           => 56,
    ));
    $wp_customize->add_control('vnf_sb_call_size', array(
        'label'       => 'Kích thước nút Gọi (px)',
        'section'     => 'vnf_sticky_buttons',
        'type'        => 'range',
        'input_attrs' => array('min' => 40, 'max' => 80, 'step' => 2),
    ));

    // ── Zalo Number ──
    $wp_customize->add_setting('vnf_sb_zalo', array(
        'type'              => 'theme_mod',
        'transport'         => 'refresh',
        'sanitize_callback' => 'sanitize_text_field',
        'default'           => '0906680182',
    ));
    $wp_customize->add_control('vnf_sb_zalo', array(
        'label'    => 'Số Zalo',
        'section'  => 'vnf_sticky_buttons',
        'type'     => 'text',
    ));

    // ── Enable Zalo Button ──
    $wp_customize->add_setting('vnf_sb_zalo_enable', array(
        'type'              => 'theme_mod',
        'transport'         => 'refresh',
        'sanitize_callback' => 'absint',
        'default'           => 1,
    ));
    $wp_customize->add_control('vnf_sb_zalo_enable', array(
        'label'    => 'Bật nút Zalo',
        'section'  => 'vnf_sticky_buttons',
        'type'     => 'checkbox',
    ));

    // ── Position ──
    $wp_customize->add_setting('vnf_sb_position', array(
        'type'              => 'theme_mod',
        'transport'         => 'refresh',
        'sanitize_callback' => 'sanitize_text_field',
        'default'           => 'bottom-right',
    ));
    $wp_customize->add_control('vnf_sb_position', array(
        'label'    => 'Vị trí hiển thị',
        'section'  => 'vnf_sticky_buttons',
        'type'     => 'select',
        'choices'  => array(
            'bottom-right' => 'Dưới phải',
            'bottom-left'  => 'Dưới trái',
        ),
    ));

    // ── Bottom Offset ──
    $wp_customize->add_setting('vnf_sb_bottom', array(
        'type'              => 'theme_mod',
        'transport'         => 'refresh',
        'sanitize_callback' => 'absint',
        'default'           => 24,
    ));
    $wp_customize->add_control('vnf_sb_bottom', array(
        'label'       => 'Khoảng cách đáy (px)',
        'section'     => 'vnf_sticky_buttons',
        'type'        => 'number',
        'input_attrs' => array('min' => 0, 'max' => 200),
    ));

    // ── Side Offset ──
    $wp_customize->add_setting('vnf_sb_side', array(
        'type'              => 'theme_mod',
        'transport'         => 'refresh',
        'sanitize_callback' => 'absint',
        'default'           => 24,
    ));
    $wp_customize->add_control('vnf_sb_side', array(
        'label'       => 'Khoảng cách bên (px)',
        'section'     => 'vnf_sticky_buttons',
        'type'        => 'number',
        'input_attrs' => array('min' => 0, 'max' => 200),
    ));

    // ── Enable Pulse Animation ──
    $wp_customize->add_setting('vnf_sb_pulse', array(
        'type'              => 'theme_mod',
        'transport'         => 'refresh',
        'sanitize_callback' => 'absint',
        'default'           => 1,
    ));
    $wp_customize->add_control('vnf_sb_pulse', array(
        'label'    => 'Bật hiệu ứng Pulse',
        'section'  => 'vnf_sticky_buttons',
        'type'     => 'checkbox',
    ));

    // ── Show on Mobile ──
    $wp_customize->add_setting('vnf_sb_mobile', array(
        'type'              => 'theme_mod',
        'transport'         => 'refresh',
        'sanitize_callback' => 'absint',
        'default'           => 1,
    ));
    $wp_customize->add_control('vnf_sb_mobile', array(
        'label'    => 'Hiển thị trên mobile',
        'section'  => 'vnf_sticky_buttons',
        'type'     => 'checkbox',
    ));
}

// ============================================================
// ENQUEUE STYLES
// ============================================================
add_action('wp_enqueue_scripts', 'vnf_sb_enqueue');

function vnf_sb_enqueue() {
    if (!get_theme_mod('vnf_sb_enable', 1)) return;
    wp_enqueue_style('vnf-sticky-buttons', plugin_dir_url(__FILE__) . 'assets/css/sticky-buttons.css', array(), '1.0.0');
}

// ============================================================
// OUTPUT BUTTONS
// ============================================================
add_action('wp_footer', 'vnf_sb_output');

function vnf_sb_output() {
    if (!get_theme_mod('vnf_sb_enable', 1)) return;

    $phone       = get_theme_mod('vnf_sb_phone', '0906680182');
    $call_color  = get_theme_mod('vnf_sb_call_color', '#2d6a4f');
    $call_size   = get_theme_mod('vnf_sb_call_size', 56);
    $zalo        = get_theme_mod('vnf_sb_zalo', '0906680182');
    $zalo_enable = get_theme_mod('vnf_sb_zalo_enable', 1);
    $position    = get_theme_mod('vnf_sb_position', 'bottom-right');
    $bottom      = get_theme_mod('vnf_sb_bottom', 24);
    $side        = get_theme_mod('vnf_sb_side', 24);
    $pulse       = get_theme_mod('vnf_sb_pulse', 1);
    $mobile      = get_theme_mod('vnf_sb_mobile', 1);

    $pos_side    = ($position === 'bottom-left') ? 'left' : 'right';
    $pos_value   = $side . 'px';

    // Darken color for hover
    $call_hover  = vnf_sb_darken_color($call_color, 15);

    $pulse_animation = $pulse ? 'vnf-sb-pulse-animation' : '';
    $mobile_hide     = !$mobile ? '.vnf-sb-group { display: none; }' : '';

    echo '<style>
.vnf-sb-group {
    position: fixed;
    bottom: ' . $bottom . 'px;
    ' . $pos_side . ': ' . $pos_value . ';
    z-index: 99999;
    display: flex;
    flex-direction: column;
    gap: 12px;
    align-items: center;
}
.vnf-sb-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    text-decoration: none;
    transition: transform 0.2s, box-shadow 0.2s;
    box-shadow: 0 4px 16px rgba(0,0,0,0.25);
}
.vnf-sb-btn:hover {
    transform: scale(1.1);
}
.vnf-sb-call {
    width: ' . $call_size . 'px;
    height: ' . $call_size . 'px;
    background: ' . $call_color . ';
    ' . ($pulse ? 'animation: vnf-sb-pulse-ring 2s infinite;' : '') . '
}
.vnf-sb-call:hover {
    background: ' . $call_hover . ';
    animation: none;
    box-shadow: 0 6px 24px ' . vnf_sb_hex2rgba($call_color, 0.5) . ';
}
.vnf-sb-zalo {
    width: ' . ($call_size - 4) . 'px;
    height: ' . ($call_size - 4) . 'px;
}
.vnf-sb-zalo:hover {
    box-shadow: 0 6px 20px rgba(0, 104, 255, 0.5);
}
@keyframes vnf-sb-pulse-ring {
    0% { box-shadow: 0 0 0 0 ' . vnf_sb_hex2rgba($call_color, 0.5) . '; }
    70% { box-shadow: 0 0 0 14px ' . vnf_sb_hex2rgba($call_color, 0) . '; }
    100% { box-shadow: 0 0 0 0 ' . vnf_sb_hex2rgba($call_color, 0) . '; }
}
@media (max-width: 768px) {
    .vnf-sb-group {
        bottom: 16px;
        ' . $pos_side . ': 16px;
        gap: 10px;
    }
    .vnf-sb-call {
        width: 50px;
        height: 50px;
    }
    .vnf-sb-zalo {
        width: 46px;
        height: 46px;
    }
    ' . $mobile_hide . '
}
</style>';

    echo '<div class="vnf-sb-group">';

    // Zalo button
    if ($zalo_enable) {
        echo '<a href="https://zalo.me/' . esc_attr($zalo) . '" target="_blank" rel="noopener" class="vnf-sb-btn vnf-sb-zalo" aria-label="Chat Zalo">
            <svg width="28" height="28" viewBox="0 0 80 80" fill="none">
                <circle cx="40" cy="40" r="40" fill="#0068FF"/>
                <text x="50%" y="54%" dominant-baseline="middle" text-anchor="middle" fill="#fff" font-size="36" font-weight="700" font-family="Arial, sans-serif">Z</text>
            </svg>
        </a>';
    }

    // Call button
    echo '<a href="tel:' . esc_attr($phone) . '" class="vnf-sb-btn vnf-sb-call ' . esc_attr($pulse_animation) . '" aria-label="Gọi điện Hotline">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.8a19.79 19.79 0 01-3.07-8.68A2 2 0 012.18 1h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 8.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/>
        </svg>
    </a>';

    echo '</div>';
}

// ============================================================
// HELPER: Darken hex color
// ============================================================
function vnf_sb_darken_color($hex, $percent) {
    $hex = ltrim($hex, '#');
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));

    $r = max(0, $r - ($r * $percent / 100));
    $g = max(0, $g - ($g * $percent / 100));
    $b = max(0, $b - ($b * $percent / 100));

    return '#' . sprintf('%02x%02x%02x', $r, $g, $b);
}

// ============================================================
// HELPER: Hex to RGBA
// ============================================================
function vnf_sb_hex2rgba($hex, $alpha = 1) {
    $hex = ltrim($hex, '#');
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    return 'rgba(' . $r . ', ' . $g . ', ' . $b . ', ' . $alpha . ')';
}
