/* global document */
/* global jQuery */
/* global snax_admin */

window.snax_admin = {};

(function ($, ns) {

    $(document).ready(function () {

        ns.metaboxes();
        ns.settings();
        ns.memeImporter();

    });

})(jQuery, snax_admin);


/*************
 *
 * Metaboxes
 *
 *************/
(function ($, ns) {

    /** CSS *****************/

    var selectors = {
        'toggle': '#snax-metabox-options .snax-forms-toogle',
        'formsWrapper': '#snax-metabox-options-forms'
    };

    var classes = {
        'formsVisible': 'snax-forms-visibility-standard',
        'formsHidden': 'snax-forms-visibility-none'
    };

    /** end of CSS **********/

    ns.metaboxes = function () {

        $(selectors.toggle).on('change', function () {
            $(selectors.formsWrapper).toggleClass(classes.formsVisible + ' ' + classes.formsHidden);
        });

        $('#snax-open-list').on('change', function () {
            updateOpenListOptions();
        });

        $('#snax-ranked-list').on('change', function () {
            updateRankedListOptions();
        });

        $('a.snax-set-current-date').on('click', function(e) {
            e.preventDefault();

            var $input = $(this).prev('input');

            var formattedDate = new Date();

            var day     = pad(formattedDate.getDate(), 2);
            var month   = pad(formattedDate.getMonth() + 1, 2);
            var year    = formattedDate.getFullYear();
            var hours   = pad(formattedDate.getHours(), 2);
            var minutes = pad(formattedDate.getMinutes(), 2);
            var seconds = pad(formattedDate.getSeconds(), 2);

            $input.val(year + '-' + month + '-' + day + ' ' + hours + ':' + minutes + ':' + seconds);
        });
    };

    function updateOpenListOptions() {
        var show = $('#snax-open-list').is(':checked');
        var $box = $('#snax-open-list-options');

        if (show) {
            $box.show();
        } else {
            $box.hide();
        }
    }

    function updateRankedListOptions() {
        var show = $('#snax-ranked-list').is(':checked');
        var $box = $('#snax-ranked-list-options');

        if (show) {
            $box.show();
        } else {
            $box.hide();
        }
    }

    function pad(n, width, z) {
        z = z || '0';
        n = n + '';
        return n.length >= width ? n : new Array(width - n.length + 1).join(z) + n;
    }

})(jQuery, snax_admin);

/*************
 *
 * Settings
 *
 *************/
(function ($, ns) {

    ns.settings = function () {
        if ($.fn.sortable) {
            $('#snax-settings-active-formats').sortable({
                'update': function() {
                    var formats = [];

                    $(this).find('input[type=checkbox]').each(function() {
                        formats.push($(this).val());
                    });

                    $('#snax_formats_order').val(formats.join(','));
                }
            });
        }

        // Hide related elements.
        $('.snax-hide-rel-settings').each(function() {
            var $checkbox    = $(this);
            var relSelector  = $checkbox.attr('data-snax-rel-settings');

            var $relSettings = [];
            $(relSelector).each(function() {
                $relSettings.push($(this).parents('tr'));
            });

            var update = function() {
                if (!$checkbox.is(':checked')) {
                    $.each($relSettings, function() { $(this).hide(); });
                } else {
                    $.each($relSettings, function() { $(this).show(); });
                }
            };

            $checkbox.on('change', function() {
                update();
            });

            update();
        });
    };

})(jQuery, snax_admin);

/*************
 *
 * Meme importer
 *
 *************/
(function ($, ns) {

    var locked = false;

    ns.memeImporter = function () {
        $('.snax-import-meme-templates-button').click(function(e) {
            e.preventDefault();
            var $that   = $(this);
            var $status = $('.snax-import-meme-templates-status');
            if ( locked ) {
                return;
            }
            locked =true;

            $status.html('Starting import...');

            var xhr = $.ajax({
                'type': 'GET',
                'url': $that.attr('href'),
                'dataType': 'json',
            });

            xhr.done(function (data) {
                importMemes(data);
            });
            xhr.fail(function() {
                $status.html('Could not download memes data');
                locked = false;
            });


            var importMemes = function(data) {
                var memes = data.data.memes;
                var count = memes.length;
                var importedCount = 0;
                var errorCount = 0;
                var skipCount = 0;

                var nextMeme = function(){
                    if (memes.length > 0){
                        var meme = memes.pop();
                        importMeme(meme.name, meme.url, meme.id);
                    } else {
                        locked = false;
                    }
                };

                var updateStatus = function() {
                    var text = '';
                    if (memes.length < 1) {
                        text = 'Done! ';
                    } else {
                        text = 'Working... ';
                    }
                    text+= 'Imported ' + importedCount + ' out of ' + count + ' memes with ' +  errorCount + ' errors and ' + skipCount + ' duplicates skipped';
                    $status.html(text);
                };

                var importMeme = function(name,imageUrl,importId) {
                    var nonce   = $('#snax_meme_import_nonce').val();
                    var config = $.parseJSON(window.snax_admin_config);
                    var xhr = $.ajax({
                        'type': 'POST',
                        'url': config.ajax_url,
                        'dataType': 'json',
                        'data': {
                            'action':           'snax_import_meme',
                            'security':         nonce,
                            'snax_image_url':   imageUrl,
                            'snax_meme_name':   name,
                            'snax_import_id':   importId
                        }
                    });
                    xhr.done(function (data) {
                        if (data.status === 'success') {
                            if (data.message === 'imported') {
                                importedCount+=1;
                            }
                            if (data.message === 'skip') {
                                skipCount+=1;
                            }
                        } else {
                            errorCount+=1;
                        }
                        updateStatus();
                        nextMeme();
                    });
                    xhr.fail(function() {
                        errorCount+=1;
                        updateStatus();
                        nextMeme();
                    });
                };

                var init = function() {
                    if ( ! memes || count < 1) {
                        $status.html('Could not download memes data');
                        locked = false;
                        return;
                    }
                    nextMeme();
                };

                init();

            };

        });
    };

})(jQuery, snax_admin);
