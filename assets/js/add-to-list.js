/* global document */
/* global jQuery */
/* global uploader */
/* global snax */
/* global alert */
/* global confirm */

// Post namespace.
snax.post = {};

(function ($, ctx) {

    'use strict';

    ctx.config = $.parseJSON(window.snax_add_to_list_config);

    // Init components.
    $(document).ready(function() {
        ctx.tabs.init();
        ctx.uploadMedia.init();
        ctx.uploadEmbed.init();
        ctx.uploadText.init();
    });

})(jQuery, snax.post);


/*******************
 *
 * Component: Tabs
 *
 ******************/

(function ($, ctx) {

    'use strict';

    /** CONFIG *******************************************/

    // Register new component.
    ctx.tabs = {};

    // Component namespace shortcut.
    var c = ctx.tabs;

    // CSS selectors.
    var selectors = {
        'tabsNav':              '.snax-tabs-nav',
        'tabsNavItem':          '.snax-tabs-nav-item',
        'tabsNavItemCurrent':   '.snax-tabs-nav-item-current',
        'tabContent':           '.snax-tab-content',
        'tabContentCurrent':    '.snax-tab-content-current',
        'focusableFields':      'input,textarea'

    };

    // CSS classes.
    var classes = {
        'tabsNavItemCurrent':   'snax-tabs-nav-item-current',
        'tabContentCurrent':    'snax-tab-content-current'
    };

    // Allow accessing
    c.selectors   = selectors;
    c.classes     = classes;

    /** INIT *******************************************/

    c.init = function () {
        c.attachEventHandlers();
    };

    /** EVENTS *****************************************/

    c.attachEventHandlers = function() {

        /* Switch tab */

        $(selectors.tabsNavItem).on('click', function(e) {
            e.preventDefault();

            var $tab = $(this);

            // Remove current selection.
            $(selectors.tabsNavItemCurrent).removeClass(classes.tabsNavItemCurrent);
            $(selectors.tabContentCurrent).removeClass(classes.tabContentCurrent);

            // Select current nav item.
            $tab.addClass(classes.tabsNavItemCurrent);

            // Select current content (with the same index as selected nav item).
            var navItemIndex = $(selectors.tabsNavItem).index($tab);

            var $tabContent = $(selectors.tabContent).eq(navItemIndex);

            $tabContent.addClass(classes.tabContentCurrent);

            // Focus first field.
            $tabContent.find(selectors.focusableFields).filter(':visible:first').focus();
        });

        /* Mimic label behaviour */
        $('label[for=snax-item-has-source], label[for=snax-item-has-ref-link]').on('click', function() {
            var inputName = $(this).attr('for');

            $(this).prev('input[name='+ inputName +']').trigger('click');
        });

    };

})(jQuery, snax.post);


/*************************
 *
 * Component: Upload Media
 *
 ************************/

(function ($, ctx) {

    'use strict';

    /** CONFIG *******************************************/

    // Register new component.
    ctx.uploadMedia = {};

    // Component namespace shortcut.
    var c = ctx.uploadMedia;

    // CSS selectors.
    var selectors = {
        'wrapper':              '.snax-tab-content',
        'form':                 'form#snax-new-item-image,form#snax-new-item-audio,form#snax-new-item-video',
        'mediaForm':            '.snax-media-upload-form',
        'titleField':           'input[name=snax-item-title]',
        'hasSourceField':       'input[name=snax-item-has-source]',
        'sourceField':          'input[name=snax-item-source]',
        'hasRefLinkField':      'input[name=snax-item-has-ref-link]',
        'refLinkField':         'input[name=snax-item-ref-link]',
        'descriptionField':     'textarea[name=snax-item-description]',
        'legalField':           'input[name=snax-item-legal]',
        'legalWrapper':         '.snax-new-item-row-legal',
        'mediaWrapper':         '.snax-media',
        'mediaUpload':          '.snax-upload',
        'mediaPreview':         '.snax-upload-preview',
        'mediaPreviewInner':    '.snax-upload-preview-inner',
        'clearPreviewLink':     '.snax-upload-preview-delete',
        'mediaIdField':         '.snax-uploaded-media-id',
        'actionButtons':        '.snax-plupload-browse-button,.snax-load-form-button',
        'dropArea':             '.snax-drag-drop-area',
        'newItemWrapper':       '.snax-new-item-wrapper',
        'mediaCustomForm':      '.snax-media-upload-form.snax-custom-form'
    };

    // CSS classes.
    var classes = {
        'wrapperFocus':         'snax-tab-content-focus',
        'wrapperBlur':          'snax-tab-content-blur',
        'formPriorMedia':       'snax-form-prior-media',
        'formWithMedia':        'snax-form-with-media',
        'formWithoutMedia':     'snax-form-without-media',
        'fieldValidationError': 'snax-validation-error',
        'newItemProcessing':    'snax-new-item-wrapper-processing',
        'withoutFeedback':      'snax-without-feedback'
    };

    var i18n = {
        'confirm':              'Are you sure?',
        'multiDropForbidden':   'You can drop only one file here. Last file will be used.'
    };

    // Allow accessing
    c.selectors = selectors;
    c.classes   = classes;
    c.i18n      = i18n;

    // Vars.
    var $forms,
        newItemData,
        skipUploadComplete;

    /** INIT *******************************************/

    c.init = function () {
        $forms = $(selectors.form);

        if (!$forms.length) {
            snax.log('Snax Post Error: item forms not found!');
            return;
        }

        snaxPlupload.hideFeedback();

        if (typeof snax.newItemData === 'undefined') {
            snax.log('Snax Post Error: New item base data is not defined!');
            return;
        }

        newItemData = snax.newItemData;

        $forms.each(function() {
            var $form = $(this);

            if (snax.currentUserId === 0) {
                c.attachLoginEvents($form);
                return;
            }

            var $mediaForm  = $form.find(selectors.mediaForm);

            if ($mediaForm.length === 0) {
                snax.log('Snax Post Error: media form missing!');
                return;
            }

            var uploader = $mediaForm.data('snaxUploader');

            if (typeof uploader === 'undefined') {
                snax.log('Snax Post Error: uploader instance not defined!');
                return;
            }

            c.loadPreview($form);
            c.attachEventHandlers($form, $mediaForm, uploader);
        });
    };

    /** EVENTS *****************************************/

    c.attachEventHandlers = function($form, $mediaForm, uploader) {

        uploader.bind('FilesAdded', function (up) {
            // Block multiple files dropping.
            if ( ! up.getOption('multi_selection') && up.files.length > 1) {
                alert(i18n.multiDropForbidden);

                while (up.files.length > 1) {
                    up.removeFile(up.files[0]);
                }
            }
        });

        /** Upload image */

        uploader.bind('FileUploaded', function (up, file, response) {
            // if async-upload returned an error message, we need to catch it here
            c.uploadError($form);

            var uploadedMediaId = parseInt(response.response, 10);

            if (!isNaN(uploadedMediaId)) {
                skipUploadComplete = true;

                c.showUploadedMedia(uploadedMediaId, $form);
                snaxPlupload.initQueue(up);
            }
        });

        uploader.bind('Error', function () {
            c.uploadError($form);
        });

        uploader.bind('UploadComplete', function (up) {
            if (!skipUploadComplete) {
                c.uploadComplete($form);
            }
        });

        /** Submit new item */

        $form.submit(function (e) {
            if ($form.find(selectors.mediaCustomForm).length > 0) {
                return;
            }

            // Collect form data.
            var $title              = $form.find(selectors.titleField);
            var $mediaRow           = $form.find(selectors.mediaWrapper);
            var $description        = $form.find(selectors.descriptionField);
            var $legal              = $form.find(selectors.legalField);
            var $uploadedMediaId    = $form.find(selectors.mediaIdField);

            var formValid = true;

            // Validate uploaded image.
            if ($uploadedMediaId.val() === '') {
                $mediaRow.addClass(classes.fieldValidationError);

                formValid = false;
            } else {
                $mediaRow.removeClass(classes.fieldValidationError);
            }

            // Validate legal, if required.
            var legalAccepted = false;

            if ($legal.length > 0) { // If there is no legal field, skip front validation.
                var $legalWrapper = $form.find(selectors.legalWrapper);
                legalAccepted = $legal.is(':checked');

                if (!legalAccepted) {
                    $legalWrapper.addClass(classes.fieldValidationError);

                    formValid = false;
                } else {
                    $legalWrapper.removeClass(classes.fieldValidationError);
                }
            }

            if (formValid) {
                // Get source.
                var source = '';

                if ($form.find(selectors.hasSourceField).is(':checked')) {
                    var $source = $form.find(selectors.sourceField);
                    source = $.trim($source.val());
                }

                // Get referral link.
                var refLink = '';

                if ($form.find(selectors.hasRefLinkField).is(':checked')) {
                    var $refLink = $form.find(selectors.refLinkField);
                    refLink = $.trim($refLink.val());
                }

                var type = $mediaForm.parents('.snax-upload').find('input[name=snax-media-item-type]').val();

                var imageData = {
                    'title':        $.trim($title.val()),
                    'source':       source,
                    'refLink':      refLink,
                    'description':  $.trim($description.val()),
                    'mediaId':      parseInt($uploadedMediaId.val(), 10),
                    'postId':       newItemData.postId,
                    'authorId':     newItemData.authorId,
                    'origin':       'contribution',
                    'legal':        legalAccepted,
                    'type':         type
                };

                if (typeof ctx.newItemImageDataFilter === 'function') {
                    imageData = ctx.newItemImageDataFilter(imageData, $form);
                }

                // All data correct, submit new item.
                var item = snax.MediaItem(imageData);

                item.save(function(res) {
                    if (res.status === 'success') {
                        location.href = res.args.redirect_url;
                    } else {
                        alert(res.message);
                    }
                });
            }

            // Stop default form submission. It's done via ajax.
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            return false;
        });

        /* Clear Preview */

        $form.find(selectors.clearPreviewLink).on('click', function(e) {
            e.preventDefault();

            if (!confirm(i18n.confirm)) {
                return;
            }

            var $preview = $form.find(selectors.mediaPreview);
            var mediaId = parseInt($preview.attr('data-snax-media-id'), 10);

            $form.removeClass(classes.formWithMedia);

            c.clearPreview($form);

            snax.deleteMedia({
                'mediaId':  mediaId,
                'authorId': snax.currentUserId
            });
        });

        $mediaForm.bind('snaxUploadInProgress', function () {
            c.uploadProgress($form);
        });

        $mediaForm.on('snaxUploadCompleted', function() {
            // Empty previous errors. Check for new if any.
            c.uploadError($form);

            c.uploadComplete($form);
        });

        $mediaForm.on('snaxFileFromUrlUploaded', function(e, mediaId) {
            c.showUploadedMedia(mediaId, $form);
        });
    };

    c.attachLoginEvents = function($form) {
        // Drop files on form.
        $form.find(selectors.dropArea).on('drop', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            snax.loginRequired();
        });

        // Use "Select Files" or "Get by URL" buttons.
        $form.find(selectors.actionButtons).on('click', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            snax.loginRequired();
        });
    };

    c.loadPreview = function($form) {
        var $preview = $form.find(selectors.mediaPreview);
        var mediaId = parseInt($preview.attr('data-snax-media-id'), 10);

        if (mediaId > 0) {
            c.showUploadedMedia(mediaId, $form);
        }
    };

    c.showUploadedMedia = function (id, $form) {
        var $preview = $form.find(selectors.mediaPreview);
        var $previewInner = $form.find(selectors.mediaPreviewInner);
        var postId = snax.newItemData.postId;

        c.clearPreview($form);

        var type = $form.find('input[name=snax-media-item-type]').val();

        snax.getMediaHtmlTag({ 'mediaId': id, 'postId': postId, 'type': type }, function(res) {
            if (res.status === 'success') {
                $form.removeClass(classes.formWithoutMedia).removeClass(classes.formPriorMedia).addClass(classes.formWithMedia);

                $preview.attr('data-snax-media-id', id);
                $previewInner.append(res.args.html);

                c.uploadComplete($form);
            }
        });

        $form.find(selectors.mediaIdField).val(id);

        $form.parents(selectors.wrapper).
            removeClass(classes.wrapperBlur).
            addClass(classes.wrapperFocus);
    };

    c.clearPreview = function($form) {
        $form.removeClass(classes.formWithMedia).addClass(classes.formWithoutMedia).addClass(classes.formPriorMedia);

        var $previewInner = $form.find(selectors.mediaPreviewInner);

        $previewInner.empty();

        $form.find(selectors.mediaIdField).val('');
    };

    c.uploadError = function($form) {

        var errors = snaxPlupload.getErrors();
        var fileErrors = snaxPlupload.getFileErrors();
        var fileStates = snaxPlupload.getFileStates();
        var $previewInner = $form.find(selectors.mediaPreviewInner);

        for (var i in fileErrors) {
            if (fileErrors.hasOwnProperty(i)) {
                $previewInner.append('<p>' + fileErrors[i] +  '</p>');
            }
        }

        var error = errors.pop();

        if (error) {
            $previewInner.append('<p>' + error.message +  '</p>');
        }

        var fakeFileId = 1;
        var fileState = typeof fileStates[fakeFileId] !== 'undefined' ? fileStates[fakeFileId] : 0;

        if (-1 === fileState) {
            var messages = snaxPlupload.getFileStateMessages();

            $previewInner.append('<p>' + messages[fakeFileId] +  '</p>');
        }

        snaxPlupload.initQueue();
    };

    c.uploadProgress = function($form) {
        $form.parents(selectors.newItemWrapper).addClass(classes.newItemProcessing);
    };

    c.uploadComplete = function($form) {
        $form.parents(selectors.newItemWrapper).removeClass(classes.newItemProcessing);
        $form.find(selectors.titleField).focus();

        // Apply MEJS player.
        if ( typeof window.wp.mediaelement !== 'undefined' ) {
            $( window.wp.mediaelement.initialize );
        }
    };

})(jQuery, snax.post);


/*************************
 *
 * Component: Upload Embed
 *
 ************************/

(function ($, ctx) {

    'use strict';

    /** CONFIG *******************************************/

    // Register new component.
    ctx.uploadEmbed = {};

    // Component namespace shortcut.
    var c = ctx.uploadEmbed;

    // CSS selectors.
    var selectors = {
        'wrapper':              '.snax-tab-content',
        'form':                 'form#snax-new-item-embed,form#snax-new-item-audio,form#snax-new-item-video',
        'titleField':           'input[name=snax-item-title]',
        'hasSourceField':       'input[name=snax-item-has-source]',
        'sourceField':          'input[name=snax-item-source]',
        'hasRefLinkField':      'input[name=snax-item-has-ref-link]',
        'refLinkField':         'input[name=snax-item-ref-link]',
        'descriptionField':     'textarea[name=snax-item-description]',
        'legalField':           'input[name=snax-item-legal]',
        'legalWrapper':         '.snax-new-item-row-legal',
        'embedCodeField':       'textarea[name=snax-item-embed-code], textarea.snax-embed-url',
        'embedCodeWrapper':     '.snax-new-item-row-embed-code',
        'wrongEmbedCodeTip':    '.snax-validation-tip',
        'mediaWrapper':         '.snax-media',
        'mediaPreviewInner':    '.snax-upload-preview-inner',
        'clearPreviewLink':     '.snax-upload-preview-delete',
        'newItemWrapper':       '.snax-new-item-wrapper',
        'actionButtons':        '.snax-plupload-browse-button,.snax-load-form-button',
        'dropArea':             '.snax-drag-drop-area'
    };

    // CSS classes.
    var classes = {
        'wrapperFocus':         'snax-tab-content-focus',
        'wrapperBlur':          'snax-tab-content-blur',
        'formPriorMedia':       'snax-form-prior-media',
        'formWithMedia':        'snax-form-with-media',
        'formWithoutMedia':     'snax-form-without-media',
        'fieldValidationError': 'snax-validation-error',
        'mediaUploaded':        'snax-media-uploaded',
        'newItemProcessing':    'snax-new-item-wrapper-processing'
    };


    var i18n = {
        'confirm':      'Are you sure?'
    };

    // Allow accessing
    c.selectors   = selectors;
    c.classes     = classes;
    c.i18n        = i18n;

    // Vars.
    var $forms, newItemData;

    /** INIT *******************************************/

    c.init = function () {
        $forms = $(selectors.form);

        if (!$forms.length) {
            snax.log('Snax Post Error: item forms not found!');
            return;
        }

        if (typeof snax.newItemData === 'undefined') {
            snax.log('Snax Post Error: New item base data is not defined!');
            return;
        }

        newItemData = snax.newItemData;

        $forms.each(function() {
            var $form = $(this);

            if (snax.currentUserId === 0) {
                c.attachLoginEvents($form);
                return;
            }

            c.attachEventHandlers($form);
        });
    };


    /** EVENTS *****************************************/

    c.attachEventHandlers = function($form) {

        /* Enter urls */

        $form.find(selectors.embedCodeField).on('keyup', function() {
            var $textarea = $(this);

            $form.parents(selectors.newItemWrapper).addClass(classes.newItemProcessing);

            var embedUrl = $.trim($textarea.val());

            snax.getEmbedPreview(embedUrl, function(res) {
                var $previewInner   = $form.find(selectors.mediaPreviewInner);
                var $embedWrapper   = $form.find(selectors.embedCodeWrapper);
                var $embedTitle     = $form.find(selectors.titleField);
                var $errorFeedback  = $embedWrapper.find(selectors.wrongEmbedCodeTip);

                c.clearPreview($form);

                if (res.status === 'success') {
                    $form.
                        removeClass(classes.formWithoutMedia).
                        removeClass(classes.formPriorMedia).
                        addClass(classes.formWithMedia);

                    $embedWrapper.removeClass('snax-validation-error');

                    $errorFeedback.text('');
                    $($embedTitle).val(res.args.embed_title);

                    var $embed = $(res.args.html);

                    $('body').trigger( 'snaxBeforeNewContentReady', [ $embed ] );

                    // Show feedback.
                    $previewInner.append($embed);

                    // Show all other fields.
                    $form.parents(selectors.wrapper).
                        removeClass(classes.wrapperBlur).
                        addClass(classes.wrapperFocus);
                } else {
                    $embedWrapper.addClass('snax-validation-error');
                    $errorFeedback.text(res.message);
                }

                $form.parents(selectors.newItemWrapper).removeClass(classes.newItemProcessing);
                $form.find(selectors.titleField).focus();
            });
        });

        /* Submit new item */

        $form.submit(function (e) {
            // Collect form data.
            var $title          = $form.find(selectors.titleField);
            var $embedCode      = $form.find(selectors.embedCodeField);
            var $mediaWrapper   = $form.find(selectors.mediaWrapper);
            var $description    = $form.find(selectors.descriptionField);
            var $legal          = $form.find(selectors.legalField);

            var formValid = true;

            // Validate embed code.
            if ($.trim($embedCode.val()) === '') {
                $mediaWrapper.addClass(classes.fieldValidationError);

                formValid = false;
            } else {
                $mediaWrapper.removeClass(classes.fieldValidationError);
            }

            // Validate legal, if required.
            var legalAccepted = false;

            if ($legal.length > 0) { // If there is no legal field, skip front validation.
                var $legalWrapper = $form.find(selectors.legalWrapper);

                legalAccepted = $legal.is(':checked');

                if (!legalAccepted) {
                    $legalWrapper.addClass(classes.fieldValidationError);

                    formValid = false;
                } else {
                    $legalWrapper.removeClass(classes.fieldValidationError);
                }
            }

            if (formValid) {
                // Get source.
                var source = '';

                if ($form.find(selectors.hasSourceField).is(':checked')) {
                    var $source = $form.find(selectors.sourceField);
                    source = $.trim($source.val());
                }

                // Get referral link.
                var refLink = '';

                if ($form.find(selectors.hasRefLinkField).is(':checked')) {
                    var $refLink = $form.find(selectors.refLinkField);
                    refLink = $.trim($refLink.val());
                }

                var embedData = {
                    'title':        $.trim($title.val()),
                    'source':       source,
                    'refLink':      refLink,
                    'description':  $.trim($description.val()),
                    'embedCode':    $.trim($embedCode.val()),
                    'postId':       newItemData.postId,
                    'authorId':     newItemData.authorId,
                    'origin':       'contribution',
                    'legal':        legalAccepted
                };

                if (typeof ctx.newItemEmbedDataFilter === 'function') {
                    embedData = ctx.newItemEmbedDataFilter(embedData, $form);
                }

                // All data correct, submit new item.
                var item = snax.EmbedItem(embedData);

                item.save(function(res) {
                    if (res.status === 'success') {
                        location.href = res.args.redirect_url;
                    } else {
                        alert(res.message);
                    }
                });
            }

            // Stop default form submission. It's done via ajax.
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            return false;
        });

        /* Clear Preview */

        $form.find(selectors.clearPreviewLink).on('click', function(e) {
            e.preventDefault();

            if (!confirm(i18n.confirm)) {
                return;
            }

            $form.removeClass(classes.formWithMedia).addClass(classes.formWithoutMedia).addClass(classes.formPriorMedia);

            $form.find(selectors.embedCodeField).val('');

            c.clearPreview($form);
        });
    };

    c.attachLoginEvents = function($form) {
        $(document).on('paste drop', selectors.embedCodeField, function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            snax.loginRequired();
        });

        $(selectors.embedCodeField).on('click', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            snax.loginRequired();
        });

        // Drop files on form.
        $form.find(selectors.dropArea).on('drop', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            snax.loginRequired();
        });

        // Use "Select files" "Embed ... code" buttons.
        $form.find(selectors.actionButtons).on('click', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            snax.loginRequired();
        });
    };

    c.clearPreview = function($form) {
        var $previewInner = $form.find(selectors.mediaPreviewInner);

        $previewInner.empty();

        $form.find(selectors.mediaWrapper).removeClass(classes.fieldValidationError);
    };

})(jQuery, snax.post);


/*************************
 *
 * Component: Upload Text
 *
 ************************/

(function ($, ctx) {

    'use strict';

    /** CONFIG *******************************************/

        // Register new component.
    ctx.uploadText = {};

    // Component namespace shortcut.
    var c = ctx.uploadText;

    // CSS selectors.
    var selectors = {
        'wrapper':              '.snax-tab-content',
        'form':                 'form#snax-new-item-text',
        'titleField':           'input[name=snax-item-title]',
        'hasRefLinkField':      'input[name=snax-item-has-ref-link]',
        'refLinkField':         'input[name=snax-item-ref-link]',
        'descriptionField':     'textarea[name=snax-item-description]',
        'legalField':           'input[name=snax-item-legal]',
        'titleWrapper':         '.snax-new-item-row-title',
        'legalWrapper':         '.snax-new-item-row-legal',
        'newItemWrapper':       '.snax-new-item-wrapper'
    };

    // CSS classes.
    var classes = {
        'wrapperFocus':         'snax-tab-content-focus',
        'wrapperBlur':          'snax-tab-content-blur',
        'fieldValidationError': 'snax-validation-error',
        'newItemProcessing':    'snax-new-item-wrapper-processing'
    };

    var i18n = {};

    // Allow accessing
    c.selectors   = selectors;
    c.classes     = classes;
    c.i18n        = i18n;

    // Vars.
    var $form, newItemData;

    /** INIT *******************************************/

    c.init = function () {
        $form = $(selectors.form);

        if (!$form.length) {
            snax.log('Snax Post Error: Text form not found!');
            return;
        }

        if (snax.currentUserId === 0) {
            c.attachLoginEvents();
            return;
        }

        if (typeof snax.newItemData === 'undefined') {
            snax.log('Snax Post Error: New item base data is not defined!');
            return;
        }

        newItemData = snax.newItemData;

        c.attachEventHandlers();
    };


    /** EVENTS *****************************************/

    c.attachEventHandlers = function() {

        /* Submit new item */

        $(selectors.form).submit(function (e) {
            // Collect form data.
            var $title          = $form.find(selectors.titleField);
            var $titleWrapper   = $form.find(selectors.titleWrapper);
            var $description    = $form.find(selectors.descriptionField);
            var $legal          = $form.find(selectors.legalField);

            var formValid = true;

            // Validate title.
            var title = $.trim($title.val());

            if (title.length > 0) {
                $titleWrapper.removeClass(classes.fieldValidationError);
            } else {
                $titleWrapper.addClass(classes.fieldValidationError);

                formValid = false;
            }

            // Validate legal, if required.
            var legalAccepted = false;

            if ($legal.length > 0) { // If there is no legal field, skip front validation.
                var $legalWrapper = $form.find(selectors.legalWrapper);

                legalAccepted = $legal.is(':checked');

                if (!legalAccepted) {
                    $legalWrapper.addClass(classes.fieldValidationError);

                    formValid = false;
                } else {
                    $legalWrapper.removeClass(classes.fieldValidationError);
                }
            }

            if (formValid) {
                // Get referral link.
                var refLink = '';

                if ($form.find(selectors.hasRefLinkField).is(':checked')) {
                    var $refLink = $form.find(selectors.refLinkField);
                    refLink = $.trim($refLink.val());
                }

                var itemData = {
                    'title':        $.trim($title.val()),
                    'refLink':      refLink,
                    'description':  $.trim($description.val()),
                    'postId':       newItemData.postId,
                    'authorId':     newItemData.authorId,
                    'origin':       'contribution',
                    'legal':        legalAccepted
                };

                if (typeof ctx.newItemTextDataFilter === 'function') {
                    itemData = ctx.newItemTextDataFilter(itemData, $form);
                }

                // All data correct, submit new item.
                var item = snax.TextItem(itemData);

                item.save(function(res) {
                    if (res.status === 'success') {
                        location.href = res.args.redirect_url;
                    } else {
                        alert(res.message);
                    }
                });
            }

            // Stop default form submission. It's done via ajax.
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            return false;
        });
    };

    c.attachLoginEvents = function() {
        $('input,textarea', selectors.form).on('click keydown', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            snax.loginRequired();
        });
    };

})(jQuery, snax.post);
