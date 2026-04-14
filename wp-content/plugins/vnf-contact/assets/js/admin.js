/**
 * VietFarmy Contact - Admin JS
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        // Tab switching
        $('.vnf-contact-admin .nav-tab').on('click', function(e) {
            e.preventDefault();
            var target = $(this).attr('href');

            $('.nav-tab').removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active');

            $('.vnf-contact-tab').hide();
            $(target).show();
        });

        // Upload button
        $('.vnf-contact-upload-btn').on('click', function() {
            var $input = $(this).prev('input');
            var $btn = $(this);

            var frame = wp.media({
                title: 'Chọn Ảnh',
                button: { text: 'Chọn' },
                multiple: false
            });

            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                $input.val(attachment.url);

                // Show preview
                var $preview = $('<img>').attr('src', attachment.url).css({
                    'max-width': '200px',
                    'margin-top': '10px',
                    'border-radius': '8px'
                });
                $input.nextAll('img').remove();
                $input.after($preview);
            });

            frame.open();
        });
    });

})(jQuery);