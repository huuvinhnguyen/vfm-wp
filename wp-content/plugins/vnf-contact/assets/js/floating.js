/**
 * VietFarmy Contact - Floating Buttons
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        initFloatingButtons();
    });

    function initFloatingButtons() {
        var phone = vnfContactFloat.phone || '';
        var zaloId = vnfContactFloat.zaloId || '';
        var show = vnfContactFloat.show;

        if (!show) return;

        // Create floating buttons container
        var $container = $('<div class="vnf-floating-buttons"></div>');

        // Zalo button
        if (zaloId) {
            var zaloUrl = 'https://zalo.me/' + zaloId;
            var $zalo = $('<a>', {
                href: zaloUrl,
                target: '_blank',
                rel: 'noopener',
                class: 'vnf-floating-btn vnf-floating-zalo',
                title: 'Nhắn Zalo'
            }).html('<svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="12"/><path fill="#0068FF" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 15c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3zm2.5-6.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm-5 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm10 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/></svg>');

            $container.append($zalo);
        }

        // Call button
        if (phone) {
            var cleanPhone = phone.replace(/\D/g, '');
            var $call = $('<a>', {
                href: 'tel:' + cleanPhone,
                class: 'vnf-floating-btn vnf-floating-call',
                title: 'Gọi ngay'
            }).html('<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>');

            $container.append($call);
        }

        // Append to body
        $('body').append($container);

        // Hide on scroll up, show on scroll down
        var lastScroll = 0;
        $(window).on('scroll', function() {
            var currentScroll = $(window).scrollTop();
            if (currentScroll > 200) {
                if (currentScroll > lastScroll) {
                    $container.css('transform', 'translateX(120%)');
                } else {
                    $container.css('transform', 'translateX(0)');
                }
            } else {
                $container.css('transform', 'translateX(0)');
            }
            lastScroll = currentScroll;
        });
    }

})(jQuery);