/**
 * VietFarmy Contact - Form Handler
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        initContactForm();
    });

    function initContactForm() {
        var $form = $('#vnf-contact-form');
        if (!$form.length) return;

        $form.on('submit', function(e) {
            e.preventDefault();

            var $btn = $('#vc-submit');
            var $msg = $('#vc-message-box');
            var $name = $('#vc-name').val().trim();
            var $phone = $('#vc-phone').val().trim();

            // Validate
            if (!$name || !$phone) {
                showMessage($msg, 'Vui lòng nhập họ tên và số điện thoại.', 'error');
                return;
            }

            // Disable button
            $btn.prop('disabled', true).find('.vnf-btn-text').hide();
            $btn.find('.vnf-btn-loading').show();

            // Submit
            $.ajax({
                url: vnfContact.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'vnf_contact_submit',
                    nonce: vnfContact.nonce,
                    name: $name,
                    phone: $phone,
                    subject: $('#vc-subject').val(),
                    message: $('#vc-message').val()
                },
                success: function(res) {
                    if (res.success) {
                        showMessage($msg, res.data.message, 'success');
                        $form[0].reset();

                        // Track conversion (for Facebook Pixel / GA)
                        if (typeof gtag !== 'undefined') {
                            gtag('event', 'generate_lead', {
                                event_category: 'Contact',
                                event_label: 'Contact Form'
                            });
                        }
                        if (typeof fbq !== 'undefined') {
                            fbq('track', 'Lead');
                        }
                    } else {
                        showMessage($msg, res.data.message || 'Có lỗi xảy ra.', 'error');
                    }
                },
                error: function() {
                    showMessage($msg, 'Không thể gửi tin nhắn. Vui lòng thử lại.', 'error');
                },
                complete: function() {
                    $btn.prop('disabled', false).find('.vnf-btn-text').show();
                    $btn.find('.vnf-btn-loading').hide();
                }
            });
        });
    }

    function showMessage($el, text, type) {
        $el.removeClass('success error').addClass(type).text(text);
    }

})(jQuery);