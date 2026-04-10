<?php
if (!defined('ABSPATH')) exit;
add_shortcode('vnf_slideshow', 'vnf_sw_shortcode');

function vnf_sw_shortcode($atts) {
    $atts = shortcode_atts(array('id' => '1', 'slug' => ''), $atts, 'vnf_slideshow');

    $slideshow_id = 0;
    if (!empty($atts['slug'])) {
        $slideshow_id = vnf_sw_get_slideshow_id_by_slug($atts['slug']);
    } elseif (!empty($atts['id']) && is_numeric($atts['id'])) {
        $slideshow_id = (int) $atts['id'];
    }
    if (!$slideshow_id) return '<div class="vnf-sw-error">Slideshow khong ton tai.</div>';

    $slides = vnf_sw_get_slides($slideshow_id);
    $settings = vnf_sw_get_settings($slideshow_id);
    if (empty($slides)) return '';

    $defaults = array('autoplay'=>1,'speed'=>4000,'height'=>480,'transition'=>'fade','nav'=>1,'dots'=>1,'caption'=>1);
    $settings = wp_parse_args($settings, $defaults);

    $js_slides = array();
    foreach ($slides as $slide) {
        $img = vnf_sw_render_slide_image($slide);
        if (!$img) continue;
        $js_slides[] = array(
            'src'   => $img,
            'title' => $slide->title,
            'desc'  => $slide->description,
            'alt'   => $slide->alt_text ?: $slide->title,
            'link'  => $slide->link_url,
            'newtab'=> $slide->link_target ? '_blank' : '_self',
        );
    }
    if (empty($js_slides)) return '';

    $autoplay  = (int) $settings['autoplay'];
    $speed     = (int) $settings['speed'];
    $height    = (int) $settings['height'];
    $show_nav  = (int) $settings['nav'];
    $show_dots = (int) $settings['dots'];
    $show_cap  = (int) $settings['caption'];
    $is_fade   = ($settings['transition'] === 'fade');
    $total     = count($js_slides);
    $slide_id  = 'vsw-' . substr(md5(uniqid()), 0, 8);

    ob_start();
    ?>
<style>
.vsw-wrap-<?php echo $slide_id; ?> {
    position: relative;
    overflow: hidden;
    background: #111;
    max-height: <?php echo $height; ?>px;
    line-height: 0;
}
.vsw-wrap-<?php echo $slide_id; ?> .vsw-inner {
    display: flex;
    transition: transform .55s ease;
}
.vsw-wrap-<?php echo $slide_id; ?> .vsw-item {
    flex: 0 0 100%;
    width: 100%;
    position: relative;
}
.vsw-wrap-<?php echo $slide_id; ?> .vsw-item img {
    width: 100%;
    max-height: <?php echo $height; ?>px;
    object-fit: cover;
    display: block;
}
.vsw-wrap-<?php echo $slide_id; ?> .vsw-item a img {
    cursor: pointer;
}
.vsw-wrap-<?php echo $slide_id; ?> .vsw-dots {
    position: absolute;
    bottom: 16px; left: 50%;
    transform: translateX(-50%);
    display: flex; gap: 8px; z-index: 10;
}
.vsw-wrap-<?php echo $slide_id; ?> .vsw-dot {
    width: 10px; height: 10px;
    border-radius: 50%;
    background: rgba(255,255,255,.4);
    border: none; cursor: pointer; padding: 0;
    transition: background .3s, transform .3s;
}
.vsw-wrap-<?php echo $slide_id; ?> .vsw-dot.vsw-active {
    background: #fff; transform: scale(1.3);
}
.vsw-wrap-<?php echo $slide_id; ?> .vsw-nav {
    position: absolute; top: 0; bottom: 0; left: 0; right: 0;
    pointer-events: none; z-index: 10;
}
.vsw-wrap-<?php echo $slide_id; ?> .vsw-btn {
    position: absolute; top: 50%; transform: translateY(-50%);
    width: 48px; height: 48px; border-radius: 50%;
    background: rgba(0,0,0,.35); border: none;
    cursor: pointer; pointer-events: all;
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 20px; font-weight: bold;
    opacity: 0; transition: opacity .3s;
}
.vsw-wrap-<?php echo $slide_id; ?> .vsw-btn-prev { left: 16px; }
.vsw-wrap-<?php echo $slide_id; ?> .vsw-btn-next { right: 16px; }
.vsw-wrap-<?php echo $slide_id; ?>:hover .vsw-btn { opacity: 1; }
.vsw-wrap-<?php echo $slide_id; ?> .vsw-caption {
    position: absolute; bottom: 0; left: 0; right: 0;
    padding: 60px 24px 20px;
    background: linear-gradient(transparent, rgba(0,0,0,.7));
    color: #fff; z-index: 5; font-size: 18px; font-weight: 600;
}
</style>

<div class="vsw-wrap-<?php echo $slide_id; ?>"
     id="<?php echo $slide_id; ?>"
     data-total="<?php echo $total; ?>"
     data-cur="0"
     data-autoplay="<?php echo $autoplay; ?>"
     data-speed="<?php echo $speed; ?>">

    <div class="vsw-inner">
        <?php foreach ($js_slides as $i => $s): ?>
        <div class="vsw-item" data-i="<?php echo $i; ?>">
            <?php if (!empty($s['link'])): ?>
            <a href="<?php echo esc_url($s['link']); ?>"<?php echo $s['newtab'] === '_blank' ? ' target="_blank" rel="noopener"' : ''; ?>>
                <img src="<?php echo esc_url($s['src']); ?>" alt="<?php echo esc_attr($s['alt']); ?>">
            </a>
            <?php else: ?>
            <img src="<?php echo esc_url($s['src']); ?>" alt="<?php echo esc_attr($s['alt']); ?>">
            <?php endif; ?>
            <?php if ($show_cap && (!empty($s['title']) || !empty($s['desc']))): ?>
            <div class="vsw-caption">
                <?php if (!empty($s['title'])) echo '<div>' . esc_html($s['title']) . '</div>'; ?>
                <?php if (!empty($s['desc']))  echo '<div style="font-size:14px;font-weight:400;opacity:.85;margin-top:4px;">' . esc_html($s['desc']) . '</div>'; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if ($show_dots): ?>
    <div class="vsw-dots">
        <?php for ($d = 0; $d < $total; $d++): ?>
        <button class="vsw-dot<?php echo $d === 0 ? ' vsw-active' : ''; ?>" data-i="<?php echo $d; ?>"></button>
        <?php endfor; ?>
    </div>
    <?php endif; ?>

    <?php if ($show_nav): ?>
    <div class="vsw-nav">
        <button class="vsw-btn vsw-btn-prev" data-dir="-1">&lt;</button>
        <button class="vsw-btn vsw-btn-next" data-dir="1">&gt;</button>
    </div>
    <?php endif; ?>

</div>

<script>
(function() {
    var wrap = document.getElementById('<?php echo $slide_id; ?>');
    if (!wrap) return;
    var total = parseInt(wrap.dataset.total) || 0;
    var cur = 0;
    var timer = null;
    var speed = parseInt(wrap.dataset.speed) || 4000;
    var autoplay = parseInt(wrap.dataset.autoplay) === 1;

    function goTo(idx) {
        idx = ((idx % total) + total) % total;
        cur = idx;
        var inner = wrap.querySelector('.vsw-inner');
        if (inner) inner.style.transform = 'translateX(-' + (idx * 100) + '%)';
        var dots = wrap.querySelectorAll('.vsw-dot');
        for (var j = 0; j < dots.length; j++) {
            dots[j].classList.toggle('vsw-active', j === idx);
        }
        wrap.dataset.cur = idx;
        if (autoplay) {
            clearInterval(timer);
            timer = setInterval(function() { goTo(cur + 1); }, speed);
        }
    }

    // Nav buttons
    var btns = wrap.querySelectorAll('.vsw-btn[data-dir]');
    for (var b = 0; b < btns.length; b++) {
        btns[b].onclick = function() {
            goTo(cur + parseInt(this.dataset.dir));
        };
    }

    // Dots
    var dots = wrap.querySelectorAll('.vsw-dot');
    for (var d = 0; d < dots.length; d++) {
        dots[d].onclick = function() {
            goTo(parseInt(this.dataset.i));
        };
    }

    // Touch swipe
    var tx0 = 0;
    wrap.ontouchstart = function(e) { tx0 = e.touches[0].clientX; };
    wrap.ontouchend = function(e) {
        var dx = e.changedTouches[0].clientX - tx0;
        if (Math.abs(dx) > 40) goTo(dx < 0 ? cur + 1 : cur - 1);
    };

    // Keyboard
    wrap.setAttribute('tabindex', '0');
    wrap.onkeydown = function(e) {
        if (e.key === 'ArrowLeft')  goTo(cur - 1);
        if (e.key === 'ArrowRight') goTo(cur + 1);
    };

    // Mouse pause
    wrap.onmouseenter = function() { if (autoplay) clearInterval(timer); };
    wrap.onmouseleave = function() { if (autoplay) timer = setInterval(function() { goTo(cur + 1); }, speed); };

    // Start autoplay
    if (autoplay) timer = setInterval(function() { goTo(cur + 1); }, speed);
})();
</script>
    <?php
    return ob_get_clean();
}

function vnf_sw_get_slideshow_id_by_slug($slug) {
    global $wpdb;
    $row = $wpdb->get_row($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}vnf_slideshows WHERE slug = %s",
        sanitize_title($slug)
    ));
    return $row ? (int) $row->id : 0;
}