/* global document */
/* global jQuery */
/* global uploader */
/* global snax */
/* global alert */
/* global confirm */
/* global plupload */
/* global snaxDemoItemsConfig */
/* global fabric */
/* global snax_quizzes */
/* global snax_polls */
/* global snax_front_submission_config */

(function ($, ctx) {

    'use strict';

    /** CONFIG *******************************************/

    // Register new component.
    ctx.uploadLinks = {};

    // Component namespace shortcut.
    var c = ctx.uploadLinks;

    // CSS selectors
    var selectors = {
        'post':                 '.snax-form-frontend',
        'postTitle':            '#snax-post-title',
        'postUrl':              '#snax-post-url',
        'postDescription':      '#snax-post-description',
        'parentFormat':         '.snax-form-frontend input[name=snax-post-format]',
        'form':                 '.snax-tab-content-link',
        'formNav':              '.snax-tabs-nav',
        'linkUrlsField':       '.snax-link-url-multi',
        'linkUrlField':        '.snax-link-url',
        'removeLinkUrlLink':   '.snax-remove-link',
        'submitField':          '.snax-add-link-item',
        'link':                '.snax-card, .snax-link',
        'linksWrapper':        '.snax-cards',
        'linkDelete':          '.snax-link-action-delete',
        'loadDemoLinkButton':  '.snax-demo-format-link > a',
        // Links processing.
        'feedbackWrapper':      '.snax-feedback',
        'closeButton':          '.snax-close-button',
        'linkProcessing':      '.snax-xofy-x',
        'linksAll':            '.snax-xofy-y',
        'linksProgressBar':    '.snax-progress-bar',
        'linkState':           '.snax-state',
        'linksStates':         '.snax-states',
        'statesWrapper':        '.snax-details',
        'featuredImage':        '#snax-featured-image',
        'deleteFeaturedImage':  '#snax-featured-image .snax-media-action-delete-featured',
        'processingText':       '.snax-text-processing',
        'exampleWrapper':       '.snax-link-example',
        'exampleLink':          'a.snax-link-example-url',
        'exampleButton':        'input.snax-link-example-url'
    };

    // CSS classes
    var classes = {
        'linkUrlField':        'snax-link-url',
        'removeLinkUrlLink':   'snax-remove-link',
        'fieldValidationError': 'snax-error',
        'formHidden':           'snax-tab-content-hidden',
        'formVisible':          'snax-tab-content-visible',
        'formNavHidden':        'snax-tabs-nav-hidden',
        'formNavVisible':       'snax-tabs-nav-visible',
        'postWithoutMedia':     'snax-form-frontend-without-media',
        'postWithMedia':        'snax-form-frontend-with-media',
        'postWithRemovedMedia': 'snax-form-frontend-with-removed-media',
        // Links processing.
        'linkState':            'snax-state',
        'linkStateProcessing':  'snax-state-processing',
        'linkStateSuccess':     'snax-state-success',
        'linkStateError':       'snax-state-error',
        'linkProcessed':        'snax-details-expanded',
        'keepDemoData':          'snax-keep-demo-data'
    };

    // i18n.
    var i18n = {
        'confirm':          ctx.config.i18n.are_you_sure,
        'processing_text':  ctx.config.i18n.link_processing_text
    };

    c.selectors = selectors;
    c.classes   = classes;
    c.i18n      = i18n;

    var $forms,
        parentFormat,
        $linksAll,
        $linkProcessing,
        $linksProgressBar,
        $linksStates,
        linksAll,
        linkProcessing,
        linksUploaded,
        linksFailed,
        linkErrors,
        linkStates;                    // States of processed files. Format: [ { name: 1.jpg, state: 1 }, ... ].
                                        // States: 1 (success),  -1 (error), file not in array (not processed yet).

    /** INIT *******************************************/

    c.init = function () {
        $forms = $(selectors.form);

        if (!$forms.length) {
            return;
        }

        parentFormat = $(selectors.parentFormat).val();

        if (parentFormat.length === 0) {
            snax.log('Snax Front Submission Error: Parent format not defined!');
            return;
        }

        if (snax.currentUserId === 0) {
            snax.log('Snax: Login required');
            return;
        }

        $forms.each(function() {
            var $form = $(this);

            c.attachEventHandlers($form);
        });
    };

    /** EVENTS *****************************************/

    c.attachEventHandlers = function($form) {

        // Url pasted.

        $form.find(selectors.linkUrlField).on('paste drop', function() {
            var $link = $(this);

            // Delay to make sure that we can read from the field.
            setTimeout(function () {
                if($link.val().length > 0) {
                    $form.find(selectors.submitField).trigger('click');
                }
            }, 200);
        });

        // Form submitted.

        $form.find(selectors.submitField).on('click', function(e) {
            e.preventDefault();

            // Collect link codes.
            var $url = $form.find(selectors.linkUrlField);

            var url = $.trim($url.val());

            c.initFeedback(1, url);

            c.fetchUrlData(url);
        });

        // Feedback closed.

        $(selectors.feedbackWrapper).on('click', selectors.closeButton, function(e) {
            e.preventDefault();

            $(selectors.form).removeClass(classes.formVisible).addClass(classes.formHidden);
            $(selectors.formNav).removeClass(classes.formNavVisible).addClass(classes.formNavHidden);

            linksFailed = 0;
            c.uploadFinished();
        });

        // Example link.

        $form.find(selectors.exampleLink).on('click', function(e) {
            e.preventDefault();

            var url = $(this).attr('href');

            $form.find(selectors.linkUrlField).val(url);
            $form.find(selectors.submitField).trigger('click');
        });

        // Example button.

        $form.find(selectors.exampleButton).on('click', function(e) {
            e.preventDefault();

            // Forward to link.
            $(this).parents(selectors.exampleWrapper).find(selectors.exampleLink).trigger('click');
        });
    };

    /** API *********************************************/

    c.fetchUrlData = function(url) {
        var xhr = $.ajax({
            'type': 'POST',
            'url': snax.config.ajax_url,
            'dataType': 'json',
            'data': {
                'action':           'snax_fetch_og_data',
                'snax_url':         url,
                'snax_author_id':   snax.currentUserId,
                'snax_skip_image':  $(selectors.featuredImage).length === 0 ? 'standard' : 'none'
            }
        });

        xhr.done(function (res) {
            // Fill url.
            $(selectors.postUrl).val(url);

            // Even if not all data was correctly fetched, some can be valid.
            c.fillData(res.args);

            if (res.status === 'success') {
                $(selectors.form).removeClass(classes.formVisible).addClass(classes.formHidden);
                $(selectors.formNav).removeClass(classes.formNavVisible).addClass(classes.formNavHidden);
                c.linkProcessed(1);
            } else {
                c.linkProcessed(-1, res.message);
            }

            c.uploadFinished();
        });
    };

    c.fillData = function(data) {
        // Set title.
        $(selectors.postTitle).val(data.title);

        // Set description.
        var $description = $(selectors.postDescription);

        if ($description.is('.froala-editor-simple')) {
            $description.froalaEditor('html.set', data.description);
        } else {
            $description.val(data.description);
        }

        // Set featured image.
        if ( data.image_id ) {
            $('.snax-tab-content-featured-image .snax-media-upload-form').trigger('snaxFileUploaded', [ data.image_id ]);
        } else {
            ctx.skipConfirmation = true;
            $(selectors.deleteFeaturedImage).trigger('click');
        }
    };

    c.linkProcessed = function(status, errorMsg) {
        linkStates[linkProcessing - 1] = status;

        if (status === -1) {
            linksFailed++;
            linkErrors[linkProcessing - 1] = errorMsg;
        }

        // Update feedback.
        linkProcessing++;
        linksUploaded++;

        c.updateFeedback();
    };

    c.initFeedback = function(all, url) {
        // Init.
        linkProcessing = 1;
        linksUploaded  = 0;
        linksAll       = all;
        linkStates     = [];
        linkErrors     = [];
        linksFailed    = 0;

        $linkProcessing    = $(selectors.linkProcessing);
        $linksAll          = $(selectors.linksAll);
        $linksProgressBar  = $(selectors.linksProgressBar);
        $linksStates       = $(selectors.linksStates);

        $linkProcessing.text(linkProcessing);
        $linksAll.text(linksAll);
        $linksProgressBar.css('width', 0);

        // Reset states.
        var i;
        $linksStates.empty();

        for(i = 0; i < linksAll; i++) {
            $linksStates.append('<li class="'+ classes.linkState +'"></li>');
        }

        $(selectors.statesWrapper).removeClass(classes.linkProcessed);

        $(selectors.feedbackWrapper).find(selectors.processingText).text(i18n.processing_text);

        snax.displayFeedback('processing-files');
    };

    c.updateFeedback = function() {
        var currentIndex = linksUploaded - 1;
        var currentState = typeof linkStates[currentIndex] !== 'undefined' ? linkStates[currentIndex] : 0;

        var $linkState = $(selectors.linksStates).find(selectors.linkState).eq(currentIndex);

        $linkState.addClass(classes.linkStateProcessing);

        if (currentState !== 0) {
            $linkState.
                removeClass(classes.linkStateProcessing).
                addClass(currentState === 1 ? classes.linkStateSuccess : classes.linkStateError);

            if (currentState === -1) {
                var errorMessage = linkErrors[currentIndex];

                $linkState.html(errorMessage);
            }

            var progress = linksUploaded / linksAll * 100;

            $linkProcessing.text(linksUploaded);
            $linksProgressBar.css('width', progress + '%');
        }
    };

    c.uploadFinished = function() {
        var finished = linksUploaded === linksAll;

        if (finished) {
            if (linksFailed > 0) {
                $(selectors.statesWrapper).addClass(classes.linkProcessed);
            } else {
                setTimeout(function() {
                    ctx.form.clearDemoDataOnMediaUploaded();

                    var $post = $(selectors.post);

                    // Switch from "Init" form to "Full" form.
                    if ($post.hasClass(classes.postWithoutMedia)) {
                        $('body').trigger('snaxFullFormLoaded', [$post]);
                    }

                    $post.removeClass(classes.postWithoutMedia + ' ' + classes.postWithRemovedMedia).addClass(classes.postWithMedia);

                    if (!$post.is('.snax-form-frontend-edit-mode')) {
                        var $postTitle = $(selectors.post).find(selectors.postTitle);

                        // If title has no "snax-focused" class yet,
                        // we can be sure that the form is loaded for the first time.
                        // So we can perform some initial actions.
                        if (!$postTitle.is('.snax-focused') && $postTitle.val().length === 0) {
                            // Focus on title.
                            $postTitle.addClass('snax-focused').focus();
                        }
                    }

                    snax.hideFeedback();
                }, 750);
            }
        }

        return finished;
    };

})(jQuery, snax.frontendSubmission);

(function ($, ctx) {

    'use strict';

    // Init components.
    $(document).ready(function() {
        ctx.uploadLinks.init();
    });

})(jQuery, snax.frontendSubmission);
