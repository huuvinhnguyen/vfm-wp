<?php
/**
 * The header for our theme.
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Gema
 */

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="profile" href="http://gmpg.org/xfn/11">
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">

<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

<div class="mobile-header-wrapper">

	<div class="mobile-logo"></div>

	<button class="overlay-toggle  menu-toggle  menu-open" aria-controls="primary-menu" aria-expanded="false">
		<?php get_template_part( 'assets/images/menu-bars-svg' ); ?>
        <span class="screen-reader-text"><?php esc_html_e( 'Primary Menu', 'gema' ); ?></span>
	</button>
	<?php if ( ! is_single() && ( ! pixelgrade_option( 'search_button', true ) ) ) { ?>
        <div class="search-toggle js-search"> <span class="screen-reader-text"><?php esc_html_e( 'Open Search', 'gema' ); ?></span></div>
    <?php } ?>
    <?php if( is_single() ) { ?>
        <button class="overlay-toggle  sidebar-toggle  sidebar-open"<?php if( ! is_single() ) echo ' disabled="disabled"'; ?> aria-expanded="false">
            <?php get_template_part( 'assets/images/sidebar-icon-svg' ); ?>
            <span class="screen-reader-text"><?php esc_html_e( 'Open Sidebar', 'gema' ); ?></span>
        </button>
    <?php } ?>
</div>

<style>
/* Reset — bỏ khoảng trắng phía trên */
#page { margin: 0 !important; padding: 0 !important; }
#content { margin: 0 !important; padding: 0 !important; }
.mobile-header-wrapper { display: none !important; height: 0 !important; min-height: 0 !important; margin: 0 !important; padding: 0 !important; overflow: hidden !important; }
</style>

<div id="page" class="site">
	<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'gema' ); ?></a>

	<div id="content" class="site-content">

<?php
// ── Header Zone (Logo / Menu / Banner) — VietFarmy ──
$logo_url = get_theme_mod('vnf_header_logo', '');
$banner_url = get_theme_mod('vnf_header_banner', '');
$show_banner = get_theme_mod('vnf_header_show_banner', false);
$banner_link = get_theme_mod('vnf_header_banner_link', home_url('/'));

// Menu items: mỗi dòng "Tên | URL"
$menu_raw = get_theme_mod('vnf_header_menu_items', '');

function vnf_render_menu_items($menu_raw) {
    if (empty(trim($menu_raw))) return;
    $current_url = isset($_SERVER['REQUEST_URI']) ? rtrim($_SERVER['REQUEST_URI'], '/') : '';
    $menu_raw = str_replace("\r", '', $menu_raw);
    $lines = preg_split('/\n/', trim($menu_raw));
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        $parts = explode('|', $line, 2);
        $label = isset($parts[0]) ? trim($parts[0]) : '';
        $url = isset($parts[1]) ? trim($parts[1]) : '#';
        if (empty($label)) continue;
        $url_normalized = rtrim(wp_http_validate_url($url) ? $url : home_url($url), '/');
        $is_active = ($current_url === $url_normalized || $current_url === parse_url($url_normalized, PHP_URL_PATH));
        $active_class = $is_active ? ' class="current-menu-item"' : '';
        echo '<li><a href="' . esc_url($url) . '"' . $active_class . '>' . esc_html($label) . '</a></li>';
    }
}
?>
<style>
/* Reset — loại bỏ style theme Gema can thiệp menu */
.vnf-nav, .vnf-drawer-nav {
    list-style: none !important;
    margin: 0 !important;
    padding: 0 !important;
}
.vnf-nav li, .vnf-drawer-nav li {
    list-style: none !important;
}
.vnf-nav a, .vnf-drawer-nav a {
    text-decoration: none !important;
    color: inherit !important;
}

/* ── Desktop ── */
.vnf-header {
    background: #fff;
    border-bottom: 2px solid #2d6a4f;
    position: sticky;
    top: 0;
    z-index: 100000;
}
.vnf-header-bar {
    display: flex; align-items: center; justify-content: space-between;
    padding: 0 24px; max-width: 1200px; margin: 0 auto; gap: 16px; height: 64px;
}
.vnf-logo img { height: 44px; width: auto; display: block; }
.vnf-logo-text { font-size: 18px; font-weight: 700; color: #2d6a4f; text-decoration: none; }
.vnf-nav {
    flex: 1; display: flex; align-items: center;
    justify-content: center; gap: 4px;
}
.vnf-nav ul {
    display: flex; align-items: center; justify-content: center;
    list-style: none; margin: 0; padding: 0; gap: 0;
}
.vnf-nav li { flex-shrink: 0; }
.vnf-nav a {
    display: flex; align-items: center; justify-content: center;
    padding: 8px 20px; color: #333;
    font-size: 14px; border-radius: 8px; transition: all .2s; font-weight: 500;
    white-space: nowrap;
}
.vnf-nav a:hover, .vnf-nav a.current-menu-item {
    background: #2d6a4f; color: #fff;
}
.vnf-banner img { width: 100%; height: auto; max-height: 380px; object-fit: cover; display: block; }
.vnf-banner a { display: block; }

/* Khoảng cách giữa banner và nội dung */
.vnf-banner-gap { height: 24px; background: #fff; }

/* ── Mobile drawer — mặc định ẩn trên mọi màn hình ── */
.vnf-overlay { display: none; }
.vnf-drawer { display: none; }
.vnf-hamburger { display: none; }
.vnf-overlay.open { display: block; opacity: 1; }
.vnf-drawer.open { display: block; }

/* ── Mobile (<= 768px) ── */
@media (max-width: 768px) {
    /* Reset triệt để — bỏ khoảng trắng */
    .vnf-header { position: relative; margin: 0 !important; padding: 0 !important; z-index: 10000; }
    .vnf-header-bar { height: 52px; padding: 0 12px !important; gap: 6px; margin: 0 !important; border-radius: 0 !important; }
    .vnf-header-bar > * { margin: 0 !important; }

    /* Logo trái */
    .vnf-logo { flex-shrink: 0; margin: 0 !important; }
    .vnf-logo img { height: 34px; }
    .vnf-logo-text { font-size: 15px; }

    /* Ẩn menu ngang */
    .vnf-nav { display: none; }

    /* Hamburger — đẩy sang phải */
    .vnf-hamburger {
        display: flex; flex-direction: column; justify-content: center;
        align-items: center; width: 38px; height: 38px; cursor: pointer;
        background: none; border: none; padding: 6px; border-radius: 6px;
        transition: background .2s; margin-left: auto; flex-shrink: 0;
    }
    .vnf-hamburger:hover { background: #f0f0f0; }
    .vnf-hamburger span {
        display: block; width: 22px; height: 2px; background: #333;
        margin: 4px 0; border-radius: 2px; transition: all .3s;
    }

    /* Overlay backdrop */
    .vnf-overlay {
        display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,.5); z-index: 10000;
        opacity: 0; visibility: hidden; transition: opacity .3s, visibility .3s;
    }
    .vnf-overlay.open {
        display: block; opacity: 1; visibility: visible;
    }

    /* Drawer menu */
    .vnf-drawer {
        position: fixed; top: 52px; right: 0; bottom: 0; width: 280px;
        background: #fff; z-index: 10001; overflow-y: auto;
        transform: translateX(100%); transition: transform .3s cubic-bezier(.4,0,.2,1);
        box-shadow: -4px 0 20px rgba(0,0,0,.15);
    }
    .vnf-drawer.open {
        display: block;
        transform: translateX(0);
    }
    .vnf-drawer-header {
        display: flex; justify-content: space-between; align-items: center;
        padding: 16px; border-bottom: 1px solid #eee;
        position: sticky; top: 0; z-index: 1; background: #fff;
    }
    .vnf-drawer-header strong { font-size: 15px; color: #2d6a4f; }
    .vnf-drawer-close {
        background: none; border: none; font-size: 22px; cursor: pointer;
        width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center;
        justify-content: center; color: #666;
    }
    .vnf-drawer-close:hover { background: #f0f0f0; }
    .vnf-drawer-nav li a {
        display: block; padding: 14px 20px; color: #333; text-decoration: none;
        font-size: 15px; border-bottom: 1px solid #f0f0f0; transition: all .2s;
    }
    .vnf-drawer-nav li a:hover { background: #f8f8f8; color: #2d6a4f; }
    .vnf-drawer-nav li:last-child a { border-bottom: none; }

    /* Banner full width */
    .vnf-banner { margin: 0 !important; padding: 0 !important; }
    .vnf-banner img { width: 100%; max-height: none !important; border-radius: 0 !important; }

    /* Khoảng cách giữa banner và nội dung */
    .vnf-banner-gap { height: 24px; }
}
</style>

<div class="vnf-header">
    <div class="vnf-header-bar">
        <!-- Logo -->
        <div class="vnf-logo">
            <?php if ($logo_url) : ?>
                <a href="<?php echo esc_url(home_url('/')); ?>"><img src="<?php echo esc_url($logo_url); ?>" alt="<?php bloginfo('name'); ?>"></a>
            <?php else : ?>
                <a href="<?php echo esc_url(home_url('/')); ?>" class="vnf-logo-text"><?php bloginfo('name'); ?></a>
            <?php endif; ?>
        </div>

        <!-- Menu Desktop -->
        <nav class="vnf-nav">
            <ul>
                <?php vnf_render_menu_items($menu_raw); ?>
            </ul>
        </nav>

        <!-- Hamburger (mobile) -->
        <button class="vnf-hamburger" aria-label="Mở menu" id="vnf_hamburger">
            <span></span><span></span><span></span>
        </button>
    </div>

    <!-- Banner -->
    <?php if ($show_banner && $banner_url) : ?>
        <div class="vnf-banner">
            <?php if ($banner_link !== home_url('/')) : ?>
                <a href="<?php echo esc_url($banner_link); ?>"><img src="<?php echo esc_url($banner_url); ?>" alt="Banner"></a>
            <?php else : ?>
                <img src="<?php echo esc_url($banner_url); ?>" alt="Banner">
            <?php endif; ?>
        </div>
    <?php endif; ?>

</div>

<!-- Khoảng cách giữa banner và nội dung -->
<div class="vnf-banner-gap"></div>

<div id="page" class="site">
	<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'gema' ); ?></a>

	<div id="content" class="site-content">


<!-- Mobile Drawer -->
<div class="vnf-overlay" id="vnf_overlay"></div>
<div class="vnf-drawer" id="vnf_drawer">
    <div class="vnf-drawer-header">
        <strong>📋 Menu</strong>
        <button class="vnf-drawer-close" id="vnf_close">&#10005;</button>
    </div>
    <nav class="vnf-drawer-nav">
        <ul>
            <?php vnf_render_menu_items($menu_raw); ?>
        </ul>
    </nav>
</div>

<script>
(function(){
    var btn = document.getElementById('vnf_hamburger');
    var overlay = document.getElementById('vnf_overlay');
    var drawer = document.getElementById('vnf_drawer');
    var closeBtn = document.getElementById('vnf_close');
    function open() {
        drawer.classList.add('open');
        requestAnimationFrame(function() {
            overlay.classList.add('open');
        });
        document.body.style.overflow = 'hidden';
    }
    function close() {
        overlay.classList.remove('open');
        requestAnimationFrame(function() {
            drawer.classList.remove('open');
        });
        document.body.style.overflow = '';
    }
    if (btn) btn.addEventListener('click', open);
    if (closeBtn) closeBtn.addEventListener('click', close);
    if (overlay) overlay.addEventListener('click', close);
    document.addEventListener('keydown', function(e){ if (e.key === 'Escape') close(); });
})();
</script>
