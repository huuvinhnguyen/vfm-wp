<?php
/**
 * The template for displaying the footer.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Gema
 */

// Logo footer
$logo_url = get_theme_mod('vnf_footer_logo', get_theme_mod('vnf_header_logo', ''));

// Social links từ Customizer
$facebook_url  = get_theme_mod('vnf_social_facebook', '#');
$instagram_url = get_theme_mod('vnf_social_instagram', '#');
$zalo_url      = get_theme_mod('vnf_social_zalo', '#');
$youtube_url   = get_theme_mod('vnf_social_youtube', '#');
?>

<!-- VIETFARMY FOOTER -->
<div class="vnf-footer-wrapper">
    <div class="vnf-footer-container">

        <!-- Cột 1: Giới thiệu -->
        <div class="vnf-footer-col vnf-footer-about">
            <?php if ($logo_url) : ?>
                <a href="<?php echo esc_url(home_url('/')); ?>" class="vnf-footer-logo">
                    <img src="<?php echo esc_url($logo_url); ?>" alt="<?php bloginfo('name'); ?>">
                </a>
            <?php else : ?>
                <a href="<?php echo esc_url(home_url('/')); ?>" class="vnf-footer-logo-text">
                    VietFarmy
                </a>
            <?php endif; ?>

            <p class="vnf-footer-slogan">VietFarmy — Tinh túy cà phê từ nông trại Gia Lai.</p>
            <p class="vnf-footer-desc">
                Chuyên cung cấp cà phê rang xay nguyên chất, nông sản sạch theo tiêu chuẩn bền vững.
            </p>

            <!-- Social icons -->
            <div class="vnf-footer-social">
                <?php if ($facebook_url && $facebook_url !== '#') : ?>
                    <a href="<?php echo esc_url($facebook_url); ?>" target="_blank" rel="noopener" class="vnf-social-link" aria-label="Facebook">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="#3b5998"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </a>
                <?php endif; ?>
                <?php if ($instagram_url && $instagram_url !== '#') : ?>
                    <a href="<?php echo esc_url($instagram_url); ?>" target="_blank" rel="noopener" class="vnf-social-link" aria-label="Instagram">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="#e1306c"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                    </a>
                <?php endif; ?>
                <?php if ($zalo_url && $zalo_url !== '#') : ?>
                    <a href="<?php echo esc_url($zalo_url); ?>" target="_blank" rel="noopener" class="vnf-social-link vnf-social-zalo" aria-label="Zalo">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="#0068ff"><circle cx="12" cy="12" r="12" fill="#0068ff"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="#fff" font-size="10" font-weight="700">Z</text></svg>
                    </a>
                <?php endif; ?>
                <?php if ($youtube_url && $youtube_url !== '#') : ?>
                    <a href="<?php echo esc_url($youtube_url); ?>" target="_blank" rel="noopener" class="vnf-social-link" aria-label="YouTube">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="#ff0000"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Cột 2: Liên hệ -->
        <div class="vnf-footer-col vnf-footer-contact">
            <h4 class="vnf-footer-heading">Liên hệ</h4>

            <div class="vnf-footer-contact-list">

                <div class="vnf-contact-item">
                    <span class="vnf-contact-icon">📍</span>
                    <div class="vnf-contact-text">
                        <strong>Địa chỉ:</strong><br>
                        Tổ 4 Lý Chính Thắng, Pleiku, Gia Lai
                    </div>
                </div>

                <div class="vnf-contact-item">
                    <span class="vnf-contact-icon">📞</span>
                    <div class="vnf-contact-text">
                        <strong>Hotline/Zalo:</strong><br>
                        <a href="tel:0906680182" class="vnf-contact-link">0906 680 182</a>
                    </div>
                </div>

                <div class="vnf-contact-item">
                    <span class="vnf-contact-icon">✉️</span>
                    <div class="vnf-contact-text">
                        <strong>Email:</strong><br>
                        <a href="mailto:info@vietfarmy.vn" class="vnf-contact-link">info@vietfarmy.vn</a>
                    </div>
                </div>

                <div class="vnf-contact-item">
                    <span class="vnf-contact-icon">🕐</span>
                    <div class="vnf-contact-text">
                        <strong>Giờ làm việc:</strong><br>
                        08:00 - 17:00 (Thứ 2 - Thứ 6)
                    </div>
                </div>

            </div>
        </div>

    </div>

    <!-- Copyright -->
    <div class="vnf-footer-bottom">
        <div class="vnf-footer-bottom-inner">
            <span>&copy; <?php echo date('Y'); ?> VietFarmy. Tất cả quyền được bảo lưu.</span>
        </div>
    </div>
</div>

<style>
/* VIETFARMY FOOTER */
.vnf-footer-wrapper {
    background: #1a3a1a;
    color: #e8e0d5;
    margin-top: 0;
}
.vnf-footer-container {
    display: flex;
    flex-wrap: wrap;
    max-width: 1200px;
    margin: 0 auto;
    padding: 48px 24px 32px;
    gap: 40px;
}
.vnf-footer-col {
    flex: 1;
    min-width: 260px;
}

/* Cột 1: Logo & Giới thiệu */
.vnf-footer-logo img {
    height: 48px;
    width: auto;
    display: block;
    margin-bottom: 12px;
}
.vnf-footer-logo-text {
    font-size: 24px;
    font-weight: 700;
    color: #fff;
    text-decoration: none;
    display: block;
    margin-bottom: 12px;
}
.vnf-footer-slogan {
    font-size: 15px;
    font-weight: 600;
    color: #c5a96e;
    margin: 0 0 10px;
}
.vnf-footer-desc {
    font-size: 14px;
    line-height: 1.6;
    color: #b0a090;
    margin: 0 0 20px;
}

/* Social icons */
.vnf-footer-social {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}
.vnf-social-link {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: rgba(255,255,255,0.1);
    transition: background 0.2s, transform 0.2s;
    text-decoration: none;
}
.vnf-social-link:hover {
    background: rgba(255,255,255,0.2);
    transform: translateY(-2px);
}

/* Cột 2: Liên hệ */
.vnf-footer-heading {
    font-size: 16px;
    font-weight: 700;
    color: #c5a96e;
    margin: 0 0 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid rgba(197,169,110,0.3);
    text-transform: uppercase;
    letter-spacing: 1px;
}
.vnf-footer-contact-list {
    display: flex;
    flex-direction: column;
    gap: 14px;
}
.vnf-contact-item {
    display: flex;
    gap: 12px;
    align-items: flex-start;
}
.vnf-contact-icon {
    font-size: 18px;
    flex-shrink: 0;
    line-height: 1.4;
}
.vnf-contact-text {
    font-size: 14px;
    line-height: 1.6;
    color: #d0c8bc;
}
.vnf-contact-text strong {
    color: #e8e0d5;
    font-weight: 600;
}
.vnf-contact-link {
    color: #c5a96e;
    text-decoration: none;
    transition: color 0.2s;
}
.vnf-contact-link:hover {
    color: #fff;
}

/* Copyright */
.vnf-footer-bottom {
    background: #122212;
    padding: 16px 24px;
}
.vnf-footer-bottom-inner {
    max-width: 1200px;
    margin: 0 auto;
    text-align: center;
    font-size: 13px;
    color: #7a7060;
}

/* Responsive mobile */
@media (max-width: 768px) {
    .vnf-footer-container {
        flex-direction: column;
        padding: 32px 16px 24px;
        gap: 32px;
    }
    .vnf-footer-col {
        min-width: 100%;
    }
    .vnf-footer-social {
        justify-content: flex-start;
    }
}
</style>

<?php
// Đóng các thẻ từ header.php
?>
	</div><!-- #content -->
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
