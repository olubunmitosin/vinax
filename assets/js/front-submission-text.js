/* global document */
/* global jQuery */
/* global snax */
/* global confirm */

(function ($, ctx) {

    'use strict';

    // Register new component
    ctx.textFormat = {};

    // Component namespace shortcut
    var c = ctx.textFormat;

    /** CONFIG *******************************************/

    // CSS selectors
    var selectors = {
        'form':         '.snax-form-frontend-format-text',
        'editor':       '.snax-form-frontend-format-text #snax-post-description',
        'uploadNonce':  '.snax-form-frontend-format-text #snax-media-form-nonce'
    };

    // CSS classes
    var classes = {
        'validationError':  'snax-validation-error',
        'debugInfo':        'snax-debug-info'
    };

    c.selectors = selectors;
    c.classes   = classes;

    /** VARS *******************************************/

    var $editor;

    /** INIT *******************************************/

    c.init = function () {
        c.initEditor();
        c.bindEvents();
    };

    c.initEditor = function() {
        $editor = $(selectors.editor);

        var config = {
            'key':          'CMFIZJNKLDXIREJI==',
            'language':     c.getEditorConfig('language'),
            'heightMin':    360,
            // Toolbar buttons on large devices (≥ 1200px).
            'toolbarButtons': [
                'bold', 'paragraphFormat', 'formatOL', 'formatUL', 'quote',
                '|',
                'insertLink', 'insertImage', 'insertVideo',
                '|',
                'undo', 'redo'
            ],
            // On medium devices (≥ 992px).
            toolbarButtonsMD: [
                'bold', 'paragraphFormat', 'formatOL', 'formatUL', 'quote',
                '|',
                'insertLink', 'insertImage', 'insertVideo',
                '|',
                'undo', 'redo'
            ],
            // On small devices (≥ 768px).
            toolbarButtonsSM: [
                'bold', 'paragraphFormat', 'formatOL', 'formatUL', 'quote',
                '|',
                'insertLink', 'insertImage', 'insertVideo',
                '|',
                'undo', 'redo'
            ],
            // On extra small devices (< 768px).
            toolbarButtonsXS: [
                'bold', 'paragraphFormat', 'formatOL', 'formatUL', 'quote',
                '|',
                'insertLink', 'insertImage', 'insertVideo',
                '|',
                'undo', 'redo'
            ],
            'quickInsertButtons': ['image', 'ol', 'ul'],
            'paragraphFormat': {
                'N':    'Normal',
                'H2':   'Heading 2',
                'H3':   'Heading 3'
            },
            'paragraphFormatSelection': true,
            'charCounterMax': c.getEditorMaxCharacters(),

            // Image.
            // -----
            'imageInsertButtons':   ['imageUpload', 'imageManager'],
            'imageResize':          false,
            'imageEditButtons':     ['snaxImageSource', 'imageAlt', 'imageRemove'],

            // Images upload.
            // -------------
            'imageUploadURL':       c.getEditorConfig('async_upload_url'),
            'imageUploadParam':     'async-upload', // Name of the parameter that contains the image file information in the upload request.
            'imageUploadParams': {
                '_wpnonce':         $(selectors.uploadNonce).val(),     // Security.
                'type':             'snax_froala_image',                // Type of async upload, to override response.
                'short':            0,                                  // Don't use short form response. We need to return JSON object.
                'snax_media_upload_action':     'new_post_upload',      // To check user upload capabilites.
                'snax_media_upload_format':     'text'                  // For filtering already uploaded images.
            },
            'imageMaxSize':         c.getEditorConfig('image_max_size'),
            'imageAllowedTypes':    c.getEditorConfig('image_allowed_types'),
            'imageUploadMethod':    'POST',

            // Image manager.
            'imageManagerLoadURL':      snax.config.ajax_url,
            'imageManagerLoadMethod':   'GET',
            'imageManagerLoadParams':   {
                'action':               'snax_load_user_uploaded_images',
                'security':             $('input[name=snax-delete-media-nonce]').val(),
                'snax_author_id':       snax.currentUserId,
                'snax_format':          'text'
            },
            'imageManagerDeleteURL':    snax.config.ajax_url,
            'imageManagerDeleteMethod': 'POST',
            'imageManagerDeleteParams': {
                'action':               'snax_delete_media',
                'security':             $('input[name=snax-delete-media-nonce]').val(),
                'snax_author_id':       snax.currentUserId
            },

            // Embed upload.
            'videoEditButtons':     ['videoRemove'],
            'videoResize':          false
        };

        // Override Froala's config using this filter function.
        if (typeof ctx.froalaEditorConfig === 'function') {
            config = ctx.froalaEditorConfig(config);
        }

        if (snax.inDebugMode()) {
            snax.log(config);
        }

        // Init.
        $editor.froalaEditor(config);

        var currentContent = $editor.text();

        if (currentContent.length > 0) {
            $editor.froalaEditor('html.set', currentContent);
        }
    };

    c.getEditorMaxCharacters = function() {
        var maxCharacters = parseInt($editor.attr('maxlength'), 10);

        return maxCharacters > 0 ? maxCharacters : -1;
    };

    c.getEditorConfig = function(id) {
        var config = ctx.config.froala;

        if (typeof config[id] !== 'undefined') {
            return config[id];
        }

        return null;
    };

    /** EVENTS *****************************************/

    c.bindEvents = function() {
        // Image.
        c.handleImageUploadSuccess();
        c.handleImageRemoval();
        c.handleImageUploadErrors();

        // Embed.
        c.handleEmbedRemoval();
        c.handleEmbedUploadErrors();

        c.handleFormSubmit();
    };

    c.handleImageUploadSuccess = function() {
        $editor.on('froalaEditor.image.loaded', function (e, editor, $img) {
            // Image loaded from Froala Media Manager strips '-' from (data-snax-id) attribute.
            // As a workaround we use the '_' instead (data-snax_id).
            // For further processing we need to normalize it to "data-snax-id".
            var id = parseInt($img.attr('data-snax_id'));

            if (id > 0) {
                $img.attr('data-snax-id', id);
                $img.removeAttr('data-snax_id');
            }

            snax.updateMediaMetadata({
                'mediaId':      parseInt($img.attr('data-snax-id'), 10),
                'parentFormat': 'text'
            });
        });
    };

    c.handleImageRemoval = function() {
        $editor.on('froalaEditor.image.beforeRemove', function (e, editor) {
            if (!confirm(editor.language.translate('Are you sure? Image will be deleted.'))) {
                return false;
            }
        });

        $editor.on('froalaEditor.image.removed', function (e, editor, $img) {
            // Media is removed from editor so we can remove it from server too.
            snax.deleteMedia({
                'mediaId':  parseInt($img.attr('data-snax-id'), 10),
                'authorId': snax.currentUserId
            });
        });
    };

    c.handleImageUploadErrors = function() {
        $editor.on('froalaEditor.image.error', function (e, editor, error) {
            var errorFeedback  = '';

            switch (error.code) {
                // Feedback for regular users.
                case '1': // Image cannot be loaded from the passed link.
                case '5': // File it too large.
                case '6': // Image file type is invalid.
                    errorFeedback  = error.message;
                    break;

                // Debug info for admins.
                default:
                    if (snax.inDebugMode()) {
                        errorFeedback  = error.message;
                    }
            }

            if (errorFeedback) {
                var $popup = editor.popups.get('image.insert');

                // Reset state.
                $popup.removeClass(classes.debugInfo);

                if (snax.inDebugMode()) {
                    $popup.addClass(classes.debugInfo);
                }

                $popup.find('.fr-message').text(errorFeedback);
            }
        });
    };

    c.handleEmbedRemoval = function() {
        $editor.on('froalaEditor.video.beforeRemove', function (e, editor) {
            if (!confirm(editor.language.translate('Are you sure? Embed will be deleted.'))) {
                return false;
            }
        });
    };

    c.handleEmbedUploadErrors = function() {
        $editor.on('froalaEditor.video.linkError froalaEditor.video.codeError', function (e, editor, link, errorMessage) {
            var $popup   = editor.popups.get('video.insert');
            var $message = $popup.find('.fr-message');

            // Create if not exists.
            if ($message.length === 0) {
                // @todo - inline styles
                $message = $('<h3 style="font-size: 16px; margin: 10px 10px 0 10px; font-weight: 400;" class="fr-message"></h3>');
                $popup.find('.fr-buttons').after($message);
            }

            $message.text(errorMessage);
        });
    };

    c.handleFormSubmit = function() {
        $(selectors.form).submit(function() {
            var postContent = $editor.val();

            // Replace paragraphs with double line breaks.
            postContent = c.removep(postContent);

            $editor.val(postContent);
        });
    };

    // Replace paragraphs with double line breaks.
    c.removep = function( html ) {
        var blocklist = 'blockquote|ul|ol|li|dl|dt|dd|table|thead|tbody|tfoot|tr|th|td|h[1-6]|fieldset',
            blocklist1 = blocklist + '|div|p',
            blocklist2 = blocklist + '|pre',
            preserve_linebreaks = false,
            preserve_br = false,
            preserve = [];

        if ( ! html ) {
            return '';
        }

        // Preserve script and style tags.
        if ( html.indexOf( '<script' ) !== -1 || html.indexOf( '<style' ) !== -1 ) {
            html = html.replace( /<(script|style)[^>]*>[\s\S]*?<\/\1>/g, function( match ) {
                preserve.push( match );
                return '<wp-preserve>';
            } );
        }

        // Protect pre tags.
        if ( html.indexOf( '<pre' ) !== -1 ) {
            preserve_linebreaks = true;
            html = html.replace( /<pre[^>]*>[\s\S]+?<\/pre>/g, function( a ) {
                a = a.replace( /<br ?\/?>(\r\n|\n)?/g, '<wp-line-break>' );
                a = a.replace( /<\/?p( [^>]*)?>(\r\n|\n)?/g, '<wp-line-break>' );
                return a.replace( /\r?\n/g, '<wp-line-break>' );
            });
        }

        // keep <br> tags inside captions and remove line breaks
        if ( html.indexOf( '[caption' ) !== -1 ) {
            preserve_br = true;
            html = html.replace( /\[caption[\s\S]+?\[\/caption\]/g, function( a ) {
                return a.replace( /<br([^>]*)>/g, '<wp-temp-br$1>' ).replace( /[\r\n\t]+/, '' );
            });
        }

        // Pretty it up for the source editor
        html = html.replace( new RegExp( '\\s*</(' + blocklist1 + ')>\\s*', 'g' ), '</$1>\n' );
        html = html.replace( new RegExp( '\\s*<((?:' + blocklist1 + ')(?: [^>]*)?)>', 'g' ), '\n<$1>' );

        // Mark </p> if it has any attributes.
        html = html.replace( /(<p [^>]+>.*?)<\/p>/g, '$1</p#>' );

        // Separate <div> containing <p>
        html = html.replace( /<div( [^>]*)?>\s*<p>/gi, '<div$1>\n\n' );

        // Remove <p> and <br />
        html = html.replace( /\s*<p>/gi, '' );
        html = html.replace( /\s*<\/p>\s*/gi, '\n\n' );
        html = html.replace( /\n[\s\u00a0]+\n/g, '\n\n' );
        html = html.replace( /\s*<br ?\/?>\s*/gi, '\n' );

        // Fix some block element newline issues
        html = html.replace( /\s*<div/g, '\n<div' );
        html = html.replace( /<\/div>\s*/g, '</div>\n' );
        html = html.replace( /\s*\[caption([^\[]+)\[\/caption\]\s*/gi, '\n\n[caption$1[/caption]\n\n' );
        html = html.replace( /caption\]\n\n+\[caption/g, 'caption]\n\n[caption' );

        html = html.replace( new RegExp('\\s*<((?:' + blocklist2 + ')(?: [^>]*)?)\\s*>', 'g' ), '\n<$1>' );
        html = html.replace( new RegExp('\\s*</(' + blocklist2 + ')>\\s*', 'g' ), '</$1>\n' );
        html = html.replace( /<((li|dt|dd)[^>]*)>/g, ' \t<$1>' );

        if ( html.indexOf( '<option' ) !== -1 ) {
            html = html.replace( /\s*<option/g, '\n<option' );
            html = html.replace( /\s*<\/select>/g, '\n</select>' );
        }

        if ( html.indexOf( '<hr' ) !== -1 ) {
            html = html.replace( /\s*<hr( [^>]*)?>\s*/g, '\n\n<hr$1>\n\n' );
        }

        if ( html.indexOf( '<object' ) !== -1 ) {
            html = html.replace( /<object[\s\S]+?<\/object>/g, function( a ) {
                return a.replace( /[\r\n]+/g, '' );
            });
        }

        // Unmark special paragraph closing tags
        html = html.replace( /<\/p#>/g, '</p>\n' );
        html = html.replace( /\s*(<p [^>]+>[\s\S]*?<\/p>)/g, '\n$1' );

        // Trim whitespace
        html = html.replace( /^\s+/, '' );
        html = html.replace( /[\s\u00a0]+$/, '' );

        // put back the line breaks in pre|script
        if ( preserve_linebreaks ) {
            html = html.replace( /<wp-line-break>/g, '\n' );
        }

        // and the <br> tags in captions
        if ( preserve_br ) {
            html = html.replace( /<wp-temp-br([^>]*)>/g, '<br$1>' );
        }

        // Put back preserved tags.
        if ( preserve.length ) {
            html = html.replace( /<wp-preserve>/g, function() {
                return preserve.shift();
            } );
        }

        return html;
    };

    // Run.
    $(document).ready(function() {
        c.init();
    });

})(jQuery, snax.frontendSubmission);


/*****************************
 *
 * Froala Image Source Button
 *
 ****************************/
(function ($) {

    'use strict';

    var editor;
    var $current_image;
    var $image_resizer;
    var $overlay;

    // Define an icon.
    $.FroalaEditor.DefineIcon('snaxImageSource', { NAME: 'edit' });

    // Define a button.
    $.FroalaEditor.RegisterCommand('snaxImageSource', {
        undo:   false,
        focus:  false,
        title:  'Source',
        callback: function () {
            editor = this;
            $current_image = this.image.get();

            showPopup();
        }
    });

    // Register save command.
    $.FE.RegisterCommand('imageSetSource', {
        undo: true,
        focus: false,
        title: 'Update',
        refreshAfterCallback: false,
        callback: function () {
            setSource();
        }
    });

    // Define Source template.
    $.extend($.FE.POPUP_TEMPLATES, {
        'image.source': '[_BUTTONS_][_SOURCE_LAYER_]'
    });

    // Define popup toolbar buttons.
    $.extend($.FE.DEFAULTS, {
        imageSourceButtons: ['imageBack', '|']
    });

    function showPopup () {
        var $popup = editor.popups.get('image.source');
        if (!$popup) {
            $popup = _initPopup();
        }

        $popup.find('input').val($current_image.attr('data-snax-source') || '').trigger('change');

        hideProgressBar();
        editor.popups.refresh('image.source');
        editor.popups.setContainer('image.source', $(editor.opts.scrollableContainer));
        var left = $current_image.offset().left + $current_image.width() / 2;
        var top = $current_image.offset().top + $current_image.height();

        editor.popups.show('image.source', left, top, $current_image.outerHeight());
    }

    /**
     * Init the image source popup.
     */
    function _initPopup () {
        var buttons = '<div class="fr-buttons">' + editor.button.buildList(editor.opts.imageSourceButtons) + '</div>';

        var popup_layer =
            '<div class="fr-image-source-layer fr-layer fr-active" id="fr-image-source-layer-' + editor.id + '">' +
                '<div class="fr-input-line">' +
                    '<input type="text" placeholder="' + editor.language.translate('Image Source') + '" tabIndex="1">' +
                '</div>' +
                '<div class="fr-action-buttons">' +
                    '<button type="button" class="fr-command fr-submit" data-cmd="imageSetSource" tabIndex="2">' + editor.language.translate('Update') + '</button>' +
                '</div>' +
            '</div>';

        var template = {
            'buttons': buttons,
            'source_layer': popup_layer
        };

        // Set the template in the popup.
        var $popup = editor.popups.create('image.source', template);

        if (editor.$wp) {
            editor.events.$on(editor.$wp, 'scroll.image-source', function () {
                if ($current_image && editor.popups.isVisible('image.source')) {
                    showPopup();
                }
            });
        }

        return $popup;
    }

    /**
     * Hide progress bar.
     */
    function hideProgressBar (dismiss) {
        var $popup = editor.popups.get('image.insert');

        if ($popup) {
            $popup.find('.fr-layer.fr-pactive').addClass('fr-active').removeClass('fr-pactive');
            $popup.find('.fr-image-progress-bar-layer').removeClass('fr-active');
            $popup.find('.fr-buttons').show();

            // Dismiss error message.
            if (dismiss || editor.$el.find('img.fr-error').length) {
                editor.events.focus();
                editor.$el.find('img.fr-error').remove();
                editor.undo.saveStep();
                editor.undo.run();
                editor.undo.dropRedo();
            }
        }
    }

    function setSource (source) {
        if ($current_image) {
            var $popup = editor.popups.get('image.source');
            $current_image.attr('data-snax-source', source || $popup.find('input').val() || '');
            $popup.find('input:focus').blur();
            _editImg($current_image);
        }
    }

    function _editImg ($img) {
        _edit.call($img.get(0));
    }

    /**
     * Start edit.
     */
    var touchScroll;

    function _edit (e) {
        if ($current_image.parents('[contenteditable="false"]:not(.fr-element):not(body)').length) {
            return true;
        }

        if (e && e.type === 'touchend' && touchScroll) {
            return true;
        }

        if (e && editor.edit.isDisabled()) {
            e.stopPropagation();
            e.preventDefault();
            return false;
        }

        // Hide resizer for other instances.
        for (var i = 0; i < $.FE.INSTANCES.length; i++) {
            if ($.FE.INSTANCES[i] !== editor) {
                $.FE.INSTANCES[i].events.trigger('image.hideResizer');
            }
        }

        editor.toolbar.disable();

        if (e) {
            e.stopPropagation();
            e.preventDefault();
        }

        // Hide keyboard.
        if (editor.helpers.isMobile()) {
            editor.events.disableBlur();
            editor.$el.blur();
            editor.events.enableBlur();
        }

        if (editor.opts.iframe) {
            editor.size.syncIframe();
        }

        _selectImage();
        _repositionResizer();
        _showEditPopup();

        editor.selection.clear();
        editor.button.bulkRefresh();

        editor.events.trigger('video.hideResizer');
    }

    /**
     * Place selection around current image.
     */
    function _selectImage () {
        if ($current_image) {
            editor.selection.clear();
            var range = editor.doc.createRange();
            range.selectNode($current_image.get(0));
            var selection = editor.selection.get();
            selection.addRange(range);
        }
    }

    /**
     * Reposition resizer.
     */
    function _repositionResizer () {
        if (!$image_resizer) {
            _initImageResizer();
        }

        var $container = editor.$wp || $(editor.opts.scrollableContainer);

        $container.append($image_resizer);
        $image_resizer.data('instance', editor);

        var wrap_correction_top = $container.scrollTop() - (($container.css('position') !== 'static' ? $container.offset().top : 0));
        var wrap_correction_left = $container.scrollLeft() - (($container.css('position') !== 'static' ? $container.offset().left : 0));

        wrap_correction_left -= editor.helpers.getPX($container.css('border-left-width'));
        wrap_correction_top -= editor.helpers.getPX($container.css('border-top-width'));

        $image_resizer
            .css('top', (editor.opts.iframe ? $current_image.offset().top : $current_image.offset().top + wrap_correction_top) - 1)
            .css('left', (editor.opts.iframe ? $current_image.offset().left : $current_image.offset().left + wrap_correction_left) - 1)
            .css('width', $current_image.get(0).getBoundingClientRect().width)
            .css('height', $current_image.get(0).getBoundingClientRect().height)
            .addClass('fr-active');
    }

    /**
     * Create resize handler.
     */
    function _getHandler (pos) {
        return '<div class="fr-handler fr-h' + pos + '"></div>';
    }

    /**
     * Exit edit.
     */
    function _exitEdit (force_exit) {
        if ($current_image && (_canExit() || force_exit === true)) {
            editor.toolbar.enable();

            $image_resizer.removeClass('fr-active');

            editor.popups.hide('image.edit');

            $current_image = null;

            _unmarkExit();
        }
    }

    var img_exit_flag = false;

    function _unmarkExit () {
        img_exit_flag = false;
    }

    function _canExit () {
        return img_exit_flag;
    }

    /**
     * Init image resizer.
     */
    function _initImageResizer () {
        var doc;

        // No shared image resizer.
        if (!editor.shared.$image_resizer) {
            // Create shared image resizer.
            editor.shared.$image_resizer = $('<div class="fr-image-resizer"></div>');
            $image_resizer = editor.shared.$image_resizer;

            // Bind mousedown event shared.
            editor.events.$on($image_resizer, 'mousedown', function (e) {
                e.stopPropagation();
            }, true);

            // Image resize is enabled.
            if (editor.opts.imageResize) {
                $image_resizer.append(_getHandler('nw') + _getHandler('ne') + _getHandler('sw') + _getHandler('se'));

                // Add image resizer overlay and set it.
                editor.shared.$img_overlay = $('<div class="fr-image-overlay"></div>');
                $overlay = editor.shared.$img_overlay;
                doc = $image_resizer.get(0).ownerDocument;
                $(doc).find('body').append($overlay);
            }
        } else {
            $image_resizer = editor.shared.$image_resizer;
            $overlay = editor.shared.$img_overlay;

            editor.events.on('destroy', function () {
                $image_resizer.removeClass('fr-active').appendTo($('body'));
            }, true);
        }

        // Shared destroy.
        editor.events.on('shared.destroy', function () {
            $image_resizer.html('').removeData().remove();
            $image_resizer = null;

            if (editor.opts.imageResize) {
                $overlay.remove();
                $overlay = null;
            }
        }, true);

        // Window resize. Exit from edit.
        if (!editor.helpers.isMobile()) {
            editor.events.$on($(editor.o_win), 'resize', function () {
                if ($current_image && !$current_image.hasClass('fr-uploading')) {
                    _exitEdit(true);
                }
                else if ($current_image) {
                    _repositionResizer();
                }
            });
        }
    }

    /**
     * Show the image edit popup.
     */
    function _showEditPopup () {
        var $popup = editor.popups.get('image.edit');
        if (!$popup) {
            $popup = _initEditPopup();
        }

        editor.popups.setContainer('image.edit', $(editor.opts.scrollableContainer));
        editor.popups.refresh('image.edit');
        var left = $current_image.offset().left + $current_image.outerWidth() / 2;
        var top = $current_image.offset().top + $current_image.outerHeight();

        editor.popups.show('image.edit', left, top, $current_image.outerHeight());
    }

    /**
     * Init the image edit popup.
     */

    function _initEditPopup (delayed) {
        if (delayed) {
            if (editor.$wp) {
                editor.events.$on(editor.$wp, 'scroll', function () {
                    if ($current_image && editor.popups.isVisible('image.edit')) {
                        _showEditPopup();
                    }
                });
            }

            return true;
        }

        // Image buttons.
        var image_buttons = '';
        if (editor.opts.imageEditButtons.length > 0) {
            image_buttons += '<div class="fr-buttons">';
            image_buttons += editor.button.buildList(editor.opts.imageEditButtons);
            image_buttons += '</div>';
        }

        var template = {
            buttons: image_buttons
        };

        var $popup = editor.popups.create('image.edit', template);

        return $popup;
    }

})(jQuery);
