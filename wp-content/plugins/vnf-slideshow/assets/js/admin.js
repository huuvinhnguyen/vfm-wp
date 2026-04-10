/**
 * VietFarmy Slideshow - Admin JavaScript
 */
(function($) {
    'use strict';

    var admin = window.vnf_sw_admin || {};

    function getSlideshowId() {
        return parseInt($('#vnf-sw-slideshow-id').val() || admin.slideshow_id || 0);
    }

    // ================================================================
    // NEW SLIDESHOW DIALOG
    // ================================================================
    $(document).on('click', '#vnf-sw-new-btn', function(e) {
        e.preventDefault();
        $('#vnf-sw-new-dialog').show();
        $('#vnf-sw-dialog-overlay').show();
        $('#vnf-sw-name').focus();
    });

    $(document).on('click', '.vnf-sw-dialog-cancel, #vnf-sw-dialog-overlay', function(e) {
        e.preventDefault();
        $('#vnf-sw-new-dialog').hide();
        $('#vnf-sw-dialog-overlay').hide();
    });

    // Auto-generate slug from name
    $(document).on('input', '#vnf-sw-name', function() {
        var slug = $(this).val().toLowerCase()
            .replace(/[^a-z0-9\u00e0-\u00fa]+/g, '-')
            .replace(/^-+|-+$/g, '-')
            .replace(/-/g, '-');
        $('#vnf-sw-slug-preview').text(slug || 'ten-slideshow');
        $('#vnf-sw-slug-input').val(slug || 'ten-slideshow');
    });

    // ================================================================
    // IMAGE SOURCE TABS
    // ================================================================
    $(document).on('click', '.vnf-sw-tab-btn', function() {
        var tab = $(this).data('tab');
        $(this).siblings().removeClass('active').end().addClass('active');
        $(this).closest('.vnf-sw-field-group').find('.vnf-sw-tab-content').hide().filter('[data-tab="' + tab + '"]').show();
    });

    // ================================================================
    // URL INPUT — LIVE PREVIEW
    // ================================================================
    $(document).on('input', '#vnf-sw-url-input', function() {
        var url = $(this).val().trim();
        var $preview = $('#vnf-sw-url-preview');
        if (url && /^https?:\/\/.+\.(jpg|jpeg|png|gif|webp|svg)/i.test(url)) {
            $preview.html('<img src="" alt="preview">');
            $preview.find('img').attr('src', url).on('error', function() {
                $(this).parent().html('<span style="color:red;font-size:12px">Không tải được ảnh</span>');
            });
        } else {
            $preview.empty();
        }
    });

    // ================================================================
    // FILE UPLOAD (WP MEDIA LIBRARY)
    // ================================================================
    var fileFrame;
    $(document).on('click', '.vnf-sw-upload-btn', function(e) {
        e.preventDefault();
        if (fileFrame) { fileFrame.open(); return; }
        fileFrame = wp.media({
            title: 'Chọn hình ảnh',
            multiple: false,
            library: { type: 'image' }
        });
        fileFrame.on('select', function() {
            var att = fileFrame.state().get('selection').first().toJSON();
            $('#vnf-sw-file-id').val(att.id);
            var thumbUrl = att.sizes && att.sizes.thumbnail ? att.sizes.thumbnail.url : att.url;
            $('#vnf-sw-file-preview').html('<img src="' + thumbUrl + '" alt="preview">');
        });
        fileFrame.open();
    });

    // ================================================================
    // SAVE SLIDE
    // ================================================================
    $(document).on('click', '#vnf-sw-save-slide', function() {
        var $msg = $('.vnf-sw-msg');
        $msg.text(admin.strings.saving).css('color', '#666');

        var imageUrl = $('#vnf-sw-url-input').val().trim();
        var imageId  = parseInt($('#vnf-sw-file-id').val()) || 0;
        var editId   = parseInt($('#vnf-sw-edit-id').val()) || 0;

        if (!imageUrl && !imageId) {
            $msg.text('Vui lòng chọn hình ảnh.').css('color', 'red');
            return;
        }

        var data = {
            action: 'vnf_sw_save_slide',
            nonce: admin.nonce,
            slideshow_id: getSlideshowId(),
            edit_id: editId,
            title: $('#vnf-sw-title').val(),
            description: $('#vnf-sw-desc').val(),
            image_url: imageUrl,
            image_id: imageId,
            alt_text: $('#vnf-sw-alt').val(),
            link_url: $('#vnf-sw-link').val(),
            link_target: $('#vnf-sw-link-newtab').is(':checked') ? 1 : 0
        };

        $.post(admin.ajax_url, data, function(resp) {
            if (resp.success) {
                $msg.text(admin.strings.saved).css('color', 'green');
                clearForm();
                reloadSlidesList();
            } else {
                $msg.text(resp.data.message || admin.strings.error).css('color', 'red');
            }
        }).fail(function() {
            $msg.text(admin.strings.error).css('color', 'red');
        });
    });

    function clearForm() {
        $('#vnf-sw-url-input').val('');
        $('#vnf-sw-file-id').val('');
        $('#vnf-sw-title').val('');
        $('#vnf-sw-desc').val('');
        $('#vnf-sw-alt').val('');
        $('#vnf-sw-link').val('');
        $('#vnf-sw-link-newtab').prop('checked', false);
        $('#vnf-sw-url-preview').empty();
        $('#vnf-sw-file-preview').empty();
        $('#vnf-sw-edit-id').val('');
        $('#vnf-sw-save-slide').text('Thêm Slide');
        $('#vnf-sw-cancel-edit').hide();
        // Reset to URL tab
        $('.vnf-sw-tab-btn').removeClass('active').filter('[data-tab="url"]').addClass('active');
        $('.vnf-sw-tab-content').hide().filter('[data-tab="url"]').show();
    }

    // ================================================================
    // EDIT SLIDE
    // ================================================================
    $(document).on('click', '.vnf-sw-edit-btn', function() {
        var id = parseInt($(this).data('id'));
        var slide = admin.slides.find(function(s) { return parseInt(s.id) === id; });
        if (!slide) return;

        $('#vnf-sw-edit-id').val(slide.id);
        if (slide.image_url) {
            $('.vnf-sw-tab-btn').removeClass('active').filter('[data-tab="url"]').addClass('active');
            $('.vnf-sw-tab-content').hide().filter('[data-tab="url"]').show();
            $('#vnf-sw-url-input').val(slide.image_url);
        } else if (slide.image_id) {
            $('.vnf-sw-tab-btn').removeClass('active').filter('[data-tab="file"]').addClass('active');
            $('.vnf-sw-tab-content').hide().filter('[data-tab="file"]').show();
            $('#vnf-sw-file-id').val(slide.image_id);
            // Show thumbnail from admin data
            var thumb = slide.image_url || '';
            if (slide.image_id && admin.slides) {
                // We'll show what we have
                $('#vnf-sw-file-preview').html('<span style="font-size:12px;color:#666;">Đã chọn tệp (ID: ' + slide.image_id + ')</span>');
            }
        }
        $('#vnf-sw-title').val(slide.title);
        $('#vnf-sw-desc').val(slide.description);
        $('#vnf-sw-alt').val(slide.alt_text);
        $('#vnf-sw-link').val(slide.link_url);
        $('#vnf-sw-link-newtab').prop('checked', !!parseInt(slide.link_target));

        $('#vnf-sw-save-slide').text('Cập nhật Slide');
        $('#vnf-sw-cancel-edit').show();
        $('html, body').animate({ scrollTop: $('#vnf-sw-add-form').offset().top - 32 }, 400);
    });

    $(document).on('click', '#vnf-sw-cancel-edit', clearForm);

    // ================================================================
    // DELETE SLIDE
    // ================================================================
    $(document).on('click', '.vnf-sw-delete-btn', function() {
        var id = parseInt($(this).data('id'));
        if (!confirm(admin.strings.confirm_delete)) return;

        $.post(admin.ajax_url, {
            action: 'vnf_sw_delete_slide',
            nonce: admin.nonce,
            id: id
        }, function(resp) {
            if (resp.success) {
                reloadSlidesList();
            }
        });
    });

    // ================================================================
    // DELETE SLIDESHOW
    // ================================================================
    $(document).on('click', '.vnf-sw-delete-sw', function(e) {
        e.preventDefault();
        var id = parseInt($(this).data('id'));
        if (!confirm(admin.strings.confirm_delete2)) return;

        $.post(admin.ajax_url, {
            action: 'vnf_sw_delete_slideshow',
            nonce: admin.nonce,
            id: id
        }, function(resp) {
            if (resp.success) {
                window.location.href = admin_url('admin.php?page=vnf-slideshow');
            }
        });
    });

    // ================================================================
    // SAVE SETTINGS
    // ================================================================
    $(document).on('submit', '#vnf-sw-settings-form', function(e) {
        e.preventDefault();
        var $msg = $('.vnf-sw-settings-msg');
        $msg.text(admin.strings.saving).css('color', '#666');

        var data = $(this).serialize();

        $.post(admin.ajax_url, data, function(resp) {
            if (resp.success) {
                $msg.text(admin.strings.saved).css('color', 'green');
            } else {
                $msg.text(resp.data.message || admin.strings.error).css('color', 'red');
            }
        });
    });

    // ================================================================
    // COPY SHORTCODE
    // ================================================================
    $(document).on('click', '#vnf-sw-copy-shortcode', function() {
        var text = $(this).data('copy') || '';
        navigator.clipboard.writeText(text).then(function() {
            $('#vnf-sw-copy-shortcode').text('Đã sao chép!').addClass('button-primary');
            setTimeout(function() {
                $('#vnf-sw-copy-shortcode').text('Sao chép').removeClass('button-primary');
            }, 2000);
        });
    });

    // ================================================================
    // REORDER SLIDES (Sortable)
    // ================================================================
    function reloadSlidesList() {
        var sid = getSlideshowId();
        var $list = $('#vnf-sw-slides-list');
        $.post(admin.ajax_url, {
            action: 'vnf_sw_get_slides',
            nonce: admin.nonce,
            slideshow_id: sid
        }, function(resp) {
            if (resp.success && resp.data.html) {
                $list.html(resp.data.html);
                // Update admin.slides
                if (resp.data.slides) admin.slides = resp.data.slides;
                updateSlideCount($list.find('.vnf-sw-slide-item').length);
            }
        });
    }

    function updateSlideCount(n) {
        $('.vnf-sw-slide-count').text('(' + n + ' slide' + (n !== 1 ? 's' : '') + ')');
    }

    $('#vnf-sw-sortable').sortable({
        handle: '.vnf-sw-drag-handle',
        update: function() {
            var order = [];
            $('#vnf-sw-sortable .vnf-sw-slide-item').each(function() {
                order.push(parseInt($(this).data('id')));
            });
            $.post(admin.ajax_url, {
                action: 'vnf_sw_reorder_slides',
                nonce: admin.nonce,
                order: order
            });
        }
    });

})(jQuery);
