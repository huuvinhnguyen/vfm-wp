<?php
/**
 * VietFarmy Process Timeline — Shortcode + Customizer
 * [vnf_process]
 */

// ── SHORTCODE ──────────────────────────────────────────────
add_shortcode('vnf_process', 'vnf_process_shortcode');

function vnf_process_shortcode($atts) {
    $steps = array();
    for ($i = 1; $i <= 4; $i++) {
        $steps[] = array(
            'title'       => get_theme_mod("vnf_process_title_$i", ''),
            'image'       => get_theme_mod("vnf_process_image_$i", ''),
            'video'       => get_theme_mod("vnf_process_video_$i", ''),
            'description' => get_theme_mod("vnf_process_desc_$i", ''),
        );
    }

    $defaults = array(
        1 => array('title' => 'Chăm sóc', 'desc' => 'Cây cà phê được chăm sóc tỉ mỉ, tưới nước, bón phân hữu cơ và kiểm soát sâu bệnh theo tiêu chuẩn bền vững 4C.'),
        2 => array('title' => 'Hái chín', 'desc' => 'Người nông dân thu hoạch từng quả chín đỏ bằng tay, đảm bảo độ chín hoàn hảo nhất cho từng hạt.'),
        3 => array('title' => 'Rang xay', 'desc' => 'Hạt cà phê được rang ở nhiệt độ kiểm soát, giữ trọn hương vị đặc trưng của vùng Gia Lai.'),
        4 => array('title' => 'Đóng gói', 'desc' => 'Sản phẩm đóng gói kín đáo, dán tem xác minh nguồn gốc, sẵn sàng giao đến tay khách hàng.'),
    );

    ob_start();
    ?>
    <section class="vnf-process-section">
        <div class="vnf-process-inner">
            <div class="vnf-section-header">
                <h2 class="vnf-section-title">Quy trình từ Farm đến Ly</h2>
                <p class="vnf-section-subtitle">Từ nông trại đến tách cà phê hoàn hảo</p>
            </div>
            <div class="vnf-timeline">
                <?php for ($i = 0; $i < 4; $i++):
                    $step = $steps[$i];
                    $def  = $defaults[$i + 1];
                    $title = !empty($step['title']) ? $step['title'] : $def['title'];
                    $image = !empty($step['image']) ? $step['image'] : '';
                    $video = !empty($step['video']) ? $step['video'] : '';
                    $desc  = !empty($step['description']) ? $step['description'] : $def['desc'];
                ?>
                    <div class="vnf-timeline-step">
                        <div class="vnf-step-number"><?php echo $i + 1; ?></div>
                        <div class="vnf-step-media">
                            <?php if (!empty($video)): ?>
                                <video src="<?php echo esc_url($video); ?>" controls playsinline poster="<?php echo esc_url($image); ?>"></video>
                            <?php elseif (!empty($image)): ?>
                                <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($title); ?>">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/400x300?text=<?php echo urlencode($title); ?>" alt="<?php echo esc_attr($title); ?>">
                            <?php endif; ?>
                        </div>
                        <div class="vnf-step-content">
                            <h3 class="vnf-step-title"><?php echo esc_html($title); ?></h3>
                            <p class="vnf-step-desc"><?php echo esc_html($desc); ?></p>
                        </div>
                    </div>
                <?php endfor; ?>
                <div class="vnf-timeline-line"></div>
            </div>
        </div>
    </section>
    <style>
    .vnf-process-section { background: #1a3a1a; padding: 56px 0; }
    .vnf-process-inner { max-width: 1200px; margin: 0 auto; padding: 0 24px; }
    .vnf-process-section .vnf-section-header { text-align: center; margin-bottom: 48px; }
    .vnf-process-section .vnf-section-title { font-size: 28px; font-weight: 700; color: #c5a96e; margin: 0 0 8px; }
    .vnf-process-section .vnf-section-subtitle { font-size: 15px; color: #8a9a7a; margin: 0; }
    .vnf-timeline { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0; position: relative; }
    .vnf-timeline-line { position: absolute; top: 80px; left: 10%; right: 10%; height: 3px; background: linear-gradient(to right, #c5a96e, #8a6a3e); z-index: 0; }
    .vnf-timeline-step { display: flex; flex-direction: column; align-items: center; text-align: center; position: relative; z-index: 1; padding: 0 12px; }
    .vnf-step-number { width: 40px; height: 40px; border-radius: 50%; background: #c5a96e; color: #1a3a1a; font-size: 16px; font-weight: 700; display: flex; align-items: center; justify-content: center; margin-bottom: 16px; flex-shrink: 0; }
    .vnf-step-media { width: 100%; aspect-ratio: 4/3; border-radius: 12px; overflow: hidden; background: rgba(255,255,255,0.08); margin-bottom: 16px; }
    .vnf-step-media img, .vnf-step-media video { width: 100%; height: 100%; object-fit: cover; display: block; }
    .vnf-step-content { padding: 0 4px; }
    .vnf-step-title { font-size: 16px; font-weight: 700; color: #e8e0d5; margin: 0 0 8px; }
    .vnf-step-desc { font-size: 13px; color: #b0a090; line-height: 1.6; margin: 0; }
    @media (max-width: 768px) {
        .vnf-timeline { grid-template-columns: 1fr; gap: 32px; }
        .vnf-timeline-line { display: none; }
        .vnf-timeline-step { flex-direction: row; text-align: left; gap: 16px; }
        .vnf-step-media { width: 120px; flex-shrink: 0; aspect-ratio: 1/1; }
        .vnf-step-number { margin-bottom: 0; }
    }
    @media (max-width: 480px) {
        .vnf-process-section { padding: 40px 0; }
        .vnf-step-media { width: 90px; }
        .vnf-step-title { font-size: 14px; }
        .vnf-step-desc { font-size: 12px; }
    }
    </style>
    <?php
    return ob_get_clean();
}

// ── CUSTOMIZER ────────────────────────────────────────────
add_action('customize_register', 'vnf_process_customizer');

function vnf_process_customizer($wp_customize) {
    $wp_customize->add_section('vnf_process', array(
        'title'    => 'Quy trình Farm đến Ly (VietFarmy)',
        'priority' => 32,
    ));

    $process_fields = array(
        1 => 'Chăm sóc',
        2 => 'Hái chín',
        3 => 'Rang xay',
        4 => 'Đóng gói',
    );

    foreach ($process_fields as $num => $label) {
        // Tiêu đề
        $wp_customize->add_setting("vnf_process_title_$num", array(
            'type' => 'theme_mod', 'transport' => 'refresh',
        ));
        $wp_customize->add_control("vnf_process_title_$num", array(
            'label' => "$label — Tiêu đề",
            'section' => 'vnf_process', 'type' => 'text',
        ));

        // URL Hình ảnh
        $wp_customize->add_setting("vnf_process_image_$num", array(
            'type' => 'theme_mod', 'transport' => 'refresh',
            'sanitize_callback' => 'esc_url_raw',
        ));
        $wp_customize->add_control("vnf_process_image_$num", array(
            'label' => "$label — URL Hình ảnh",
            'section' => 'vnf_process', 'type' => 'url',
        ));

        // URL Video
        $wp_customize->add_setting("vnf_process_video_$num", array(
            'type' => 'theme_mod', 'transport' => 'refresh',
            'sanitize_callback' => 'esc_url_raw',
        ));
        $wp_customize->add_control("vnf_process_video_$num", array(
            'label' => "$label — Video URL (mp4)",
            'section' => 'vnf_process', 'type' => 'url',
        ));

        // Mô tả
        $wp_customize->add_setting("vnf_process_desc_$num", array(
            'type' => 'theme_mod', 'transport' => 'refresh',
        ));
        $wp_customize->add_control("vnf_process_desc_$num", array(
            'label' => "$label — Mô tả",
            'section' => 'vnf_process', 'type' => 'textarea',
        ));
    }
}
