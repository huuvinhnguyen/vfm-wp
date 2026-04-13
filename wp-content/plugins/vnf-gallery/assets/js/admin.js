/**
 * VietFarmy Gallery - Admin JavaScript
 */
(function($) {
    'use strict';

    var VNF = window.vnf_gl_admin || {};

    $(document).ready(function() {
        initTabs();
        initNewGallery();
        initURLPreview();
        initFileUpload();
        initSaveImage();
        initEditImage();
        initDeleteImage();
        initSortable();
        initSettings();
        initDeleteGallery();
        initCopyShortcode();
    });

    // Tabs: URL / File
    function initTabs() {
        $('.vnf-gl-admin').on('click', '.vnf-gl-tab-btn', function() {
            var $btn = $(this);
            var tab = $btn.data('tab');
            var $group = $btn.closest('.vnf-gl-field-group');

            $group.find('.vnf-gl-tab-btn').removeClass('active');
            $group.find('.vnf-gl-tab-content').hide();

            $btn.addClass('active');
            $group.find('.vnf-gl-tab-content[data-tab="' + tab + '"]').show();
        });
    }

    // New gallery dialog
    function initNewGallery() {
        var $dialog = $('#vnf-gl-new-dialog');
        var $overlay = $('#vnf-gl-dialog-overlay');

        $('#vnf-gl-new-btn').on('click', function(e) {
            e.preventDefault();
            $dialog.show();
            $overlay.show();
        });

        $('.vnf-gl-dialog-cancel, #vnf-gl-dialog-overlay').on('click', function(e) {
            if (e.target === this) {
                $dialog.hide();
                $overlay.hide();
            }
        });

        // Slug preview
        $('#vnf-gl-name').on('input', function() {
            var slug = $(this).val()
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
            $('#vnf-gl-slug-preview').text(slug);
            $('#vnf-gl-slug-input').val(slug);
        });
    }

    // URL preview
    function initURLPreview() {
        $('#vnf-gl-url-input').on('input', function() {
            var url = $(this).val();
            var $preview = $('#vnf-gl-url-preview');
            if (url && isImageURL(url)) {
                $preview.html('<img src="' + url + '" alt="Preview">');
            } else {
                $preview.html('');
            }
        });
    }

    // File upload via WordPress media library
    function initFileUpload() {
        var frame;
        var $preview = $('#vnf-gl-file-preview');
        var $fileId = $('#vnf-gl-file-id');

        $('.vnf-gl-upload-btn').on('click', function(e) {
            e.preventDefault();

            if (frame) {
                frame.open();
                return;
            }

            frame = wp.media({
                title: VNF.strings.select_image,
                button: { text: VNF.strings.upload_image },
                multiple: false
            });

            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                $fileId.val(attachment.id);
                $preview.html('<img src="' + attachment.url + '" alt="Preview">');
            });

            frame.open();
        });
    }

    // Save image
    function initSaveImage() {
        $('#vnf-gl-save-image').on('click', function() {
            var $btn = $(this);
            var $msg = $('.vnf-gl-msg');

            // Get image source
            var imageUrl = '';
            var imageId = 0;

            var activeTab = $('.vnf-gl-img-source-tabs .active').data('tab');
            if (activeTab === 'url') {
                imageUrl = $('#vnf-gl-url-input').val();
            } else {
                imageId = parseInt($('#vnf-gl-file-id').val()) || 0;
                // Get URL from attachment
                if (imageId && VNF.images) {
                    var attachment = VNF.images.find(function(a) { return a.image_id === imageId; });
                    // We'll send image_id and get URL from WordPress
                }
            }

            var thumbUrl = $('#vnf-gl-thumb').val();

            var data = {
                action: 'vnf_gl_save_image',
                nonce: VNF.nonce,
                gallery_id: $('#vnf-gl-gallery-id').val(),
                edit_id: $('#vnf-gl-edit-id').val(),
                image_url: imageUrl,
                image_id: imageId,
                thumb_url: thumbUrl,
                title: $('#vnf-gl-title').val(),
                description: $('#vnf-gl-desc').val(),
                alt_text: $('#vnf-gl-alt').val(),
                link_url: $('#vnf-gl-link').val()
            };

            if (!data.image_url && !data.image_id) {
                $msg.text('Vui lòng chọn hình ảnh.').css('color', '#dc3232');
                return;
            }

            $btn.prop('disabled', true).text('Đang lưu…');
            $msg.text('');

            $.post(VNF.ajax_url, data, function(res) {
                if (res.success) {
                    refreshImagesList();
                    resetForm();
                    $msg.text('Đã lưu!').css('color', 'green');
                    setTimeout(function() { $msg.text(''); }, 2000);
                } else {
                    $msg.text(res.data.message || 'Lỗi').css('color', '#dc3232');
                }
            }).always(function() {
                $btn.prop('disabled', false).text($('#vnf-gl-edit-id').val() ? 'Cập nhật' : 'Thêm Ảnh');
            });
        });
    }

    // Edit image
    function initEditImage() {
        $('.vnf-gl-admin').on('click', '.vnf-gl-edit-btn', function() {
            var id = $(this).data('id');
            var image = VNF.images.find(function(i) { return i.id == id; });
            if (!image) return;

            $('#vnf-gl-edit-id').val(image.id);
            $('#vnf-gl-title').val(image.title);
            $('#vnf-gl-desc').val(image.description);
            $('#vnf-gl-alt').val(image.alt_text);
            $('#vnf-gl-link').val(image.link_url);
            $('#vnf-gl-thumb').val(image.thumb_url || '');

            if (image.image_url) {
                $('#vnf-gl-url-input').val(image.image_url);
                $('#vnf-gl-url-preview').html('<img src="' + image.image_url + '" alt="Preview">');
                $('.vnf-gl-tab-btn[data-tab="url"]').click();
            } else if (image.image_id) {
                $('#vnf-gl-file-id').val(image.image_id);
                var $preview = $('#vnf-gl-file-preview');
                if (image.thumb) {
                    $preview.html('<img src="' + image.thumb + '" alt="Preview">');
                } else {
                    $preview.html('<img src="' + imageUrl + '" alt="Preview">');
                }
                $('.vnf-gl-tab-btn[data-tab="file"]').click();
            }

            $('#vnf-gl-save-image').text('Cập nhật');
            $('#vnf-gl-cancel-edit').show();
            $('html, body').animate({ scrollTop: $('#vnf-gl-add-form').offset().top - 20 }, 300);
        });

        $('#vnf-gl-cancel-edit').on('click', function() {
            resetForm();
        });
    }

    // Delete image
    function initDeleteImage() {
        $('.vnf-gl-admin').on('click', '.vnf-gl-delete-btn', function() {
            if (!confirm(VNF.strings.confirm_delete)) return;

            var $btn = $(this);
            var id = $btn.data('id');

            $.post(VNF.ajax_url, {
                action: 'vnf_gl_delete_image',
                nonce: VNF.nonce,
                id: id
            }, function(res) {
                if (res.success) {
                    refreshImagesList();
                }
            });
        });
    }

    // Sortable
    function initSortable() {
        var $sortable = $('#vnf-gl-sortable');
        if (!$sortable.length) return;

        $sortable.sortable({
            placeholder: 'ui-sortable-placeholder',
            update: function(event, ui) {
                var order = [];
                $sortable.find('.vnf-gl-image-item').each(function() {
                    order.push($(this).data('id'));
                });

                $.post(VNF.ajax_url, {
                    action: 'vnf_gl_reorder_images',
                    nonce: VNF.nonce,
                    order: order
                }, function(res) {
                    if (res.success) {
                        VNF.images = res.data.images || VNF.images;
                    }
                });
            }
        });
    }

    // Save settings
    function initSettings() {
        $('#vnf-gl-settings-form').on('submit', function(e) {
            e.preventDefault();

            var $form = $(this);
            var $msg = $('.vnf-gl-settings-msg');

            $.post(VNF.ajax_url, $form.serialize(), function(res) {
                if (res.success) {
                    $msg.text('Đã lưu!').css('color', 'green');
                    setTimeout(function() { $msg.text(''); }, 2000);
                } else {
                    $msg.text('Lỗi').css('color', '#dc3232');
                }
            });
        });
    }

    // Delete gallery
    function initDeleteGallery() {
        $('.vnf-gl-admin').on('click', '.vnf-gl-delete-gl', function(e) {
            e.preventDefault();
            if (!confirm(VNF.strings.confirm_delete2)) return;

            var id = $(this).data('id');

            $.post(VNF.ajax_url, {
                action: 'vnf_gl_delete_gallery',
                nonce: VNF.nonce,
                id: id
            }, function(res) {
                if (res.success) {
                    window.location.href = admin_url + 'admin.php?page=vnf-gallery';
                }
            });
        });
    }

    // Copy shortcode
    function initCopyShortcode() {
        $('#vnf-gl-copy-shortcode').on('click', function() {
            var text = $(this).data('copy');
            navigator.clipboard.writeText(text).then(function() {
                alert('Đã sao chép: ' + text);
            });
        });
    }

    // Helper: refresh images list
    function refreshImagesList() {
        $.post(VNF.ajax_url, {
            action: 'vnf_gl_get_images',
            nonce: VNF.nonce,
            gallery_id: VNF.gallery_id
        }, function(res) {
            if (res.success) {
                $('#vnf-gl-images-list').html(res.data.html);
                VNF.images = res.data.images || [];
                updateImageCount(res.data.images ? res.data.images.length : 0);
            }
        });
    }

    // Helper: reset form
    function resetForm() {
        $('#vnf-gl-edit-id').val('');
        $('#vnf-gl-url-input').val('');
        $('#vnf-gl-file-id').val('');
        $('#vnf-gl-thumb').val('');
        $('#vnf-gl-title').val('');
        $('#vnf-gl-desc').val('');
        $('#vnf-gl-alt').val('');
        $('#vnf-gl-link').val('');
        $('#vnf-gl-url-preview').html('');
        $('#vnf-gl-file-preview').html('');
        $('#vnf-gl-save-image').text('Thêm Ảnh');
        $('#vnf-gl-cancel-edit').hide();
        $('.vnf-gl-tab-btn[data-tab="url"]').click();
    }

    // Helper: update image count
    function updateImageCount(count) {
        $('.vnf-gl-image-count').text('(' + count + ' ảnh)');
    }

    // Helper: check if URL is image
    function isImageURL(url) {
        return /\.(jpg|jpeg|png|gif|webp|svg)(\?.*)?$/i.test(url);
    }

})(jQuery);