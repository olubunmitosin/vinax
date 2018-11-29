/* global jQuery */
/* global confirm */
/* global snax_polls */

if ( typeof window.snax_polls === 'undefined' ) {
    window.snax_polls = {};
}

(function ($, ctx) {

    'use strict';

    ctx.openMediaLibrary = function(callbacks) {
        var frame = wp.media({
            'title':    'Select an image',
            'multiple': false,
            'library':  {
                'type': 'image'
            },
            'button': {
                'text': 'Insert'
            }
        });

        frame.on('select',function() {
            var objSelected = frame.state().get('selection').first().toJSON();

            callbacks.onSelect(objSelected);
        });

        frame.open();
    };

    ctx.mediaDeleted = function() {};

    ctx.getBackboneTemplate = function (selector) {
        var template = $(selector).html();

        template = template.replace('/*<![CDATA[*/', '');
        template = template.replace('/*]]>*/', '');

        return template;
    };

    ctx.confirm = function(message) {
        message = message || 'Are you sure?';

        return confirm(message);
    };

    ctx.createCookie = function (name, value, days) {
        var expires;

        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = '; expires=' + date.toUTCString();
        }
        else {
            expires = '';
        }

        document.cookie = name.concat('=', value, expires, '; path=/');
    };

    ctx.readCookie = function (name) {
        var nameEQ = name + '=';
        var ca = document.cookie.split(';');

        for(var i = 0; i < ca.length; i += 1) {
            var c = ca[i];
            while (c.charAt(0) === ' ') {
                c = c.substring(1,c.length);
            }

            if (c.indexOf(nameEQ) === 0) {
                return c.substring(nameEQ.length,c.length);
            }
        }

        return null;

    };

    ctx.deleteCookie = function (name) {
        ctx.createCookie(name, '', -1);
    };

})(jQuery, snax_polls);

