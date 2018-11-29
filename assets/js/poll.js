/* global jQuery */
/* global document */
/* global snax_polls */
/* global snax_poll_config */
/* global JSON */
/* global console */

if ( typeof window.snax_polls === 'undefined' ) {
    window.snax_polls = {};
}

/**
 * Helpers.
 */
(function($, ctx) {

    ctx.shuffleArray = function(array) {
        var currentIndex = array.length;
        var randomIndex;
        var tempValue;

        while (currentIndex > 0) {
            randomIndex = Math.floor(Math.random() * currentIndex);
            currentIndex -= 1;

            // And swap it with the current element.
            tempValue = array[currentIndex];
            array[currentIndex] = array[randomIndex];
            array[randomIndex] = tempValue;
        }

        return array;
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

/**
 * Handle poll actions.
 */
(function ($, ctx) {

    'use strict';

    // Poll object.
    var poll;

    // Poll DOM element.
    var $poll;

    // CSS classes.
    var QUESTION_ANSWERED       = 'snax-poll-question-answered';
    var QUESTION_UNANSWERED     = 'snax-poll-question-unanswered';
    var QUESTION_REVEAL_ANSWERS = 'snax-poll-question-reveal-answers';
    var QUESTION_HIDDEN         = 'snax-poll-question-hidden';
    var ANSWER_CHECKED          = 'snax-poll-answer-checked';
    var ANSWER_RIGHT            = 'snax-poll-answer-right';
    var ANSWER_WRONG            = 'snax-poll-answer-wrong';
    var PAGINATION_LOCKED       = 'g1-arrow-disabled';

    var pollResults;

    var bindEvents = function() {
        pollStartedAction();
        questionAnsweredAction();
        nextPageAction();

        poll.on('questionAnswered', function(questionId, answerId, results) {
            pollResults = results;

            var $question = $poll.find('.snax-poll-question-' + questionId);
            var $answer   = $poll.find('.snax-poll-answer-' + answerId);

            // Update states.
            $question.removeClass(QUESTION_UNANSWERED).addClass(QUESTION_ANSWERED);
            $answer.addClass(ANSWER_CHECKED);

            // Reveal answers.
            if ('immediately' === poll.revealCorrectWrongAnswers()) {
                revealCorrectWrongAnswers($question);
            }

            // Enable pagination.
            if (!$question.is('.snax-poll-question-hidden')) {
                $poll.find('.snax-poll-pagination .g1-arrow-disabled').removeClass('g1-arrow-disabled');
            }
        });

        poll.on('started', function () {
            $poll.find('.snax-poll-with-start-trigger').removeClass('snax-poll-with-start-trigger');
        });

        poll.on('ended', function (locked, results) {
            if (locked) {
                $('.snax-poll-actions-hidden').removeClass('snax-poll-actions-hidden');
                $('.snax-poll-results-hidden').removeClass('snax-poll-results-hidden');
            } else {
                showResults();
            }
        });

        poll.on('unlocked', function() {
            showResults();
        });
    };

    var pollStartedAction = function() {
        $poll.find('.snax-poll-button-start-poll').on('click', function(e) {
            e.preventDefault();

            poll.start();
        });
    };

    var questionAnsweredAction = function() {
        $poll.find('.snax-poll-question').on('click', function(e) {
            e.preventDefault();

            var $question = $(this);

            var $answer = $(e.target).parents('.snax-poll-answer');

            // Proceed only if user clicked in answer.
            if (!$answer.is('.snax-poll-answer')) {
                return;
            }

            var answerId    = parseInt($answer.attr('data-quizzard-answer-id'), 10);
            var questionId  = parseInt($question.attr('data-quizzard-question-id'), 10);

            poll.addAnswer(questionId, answerId, 1);
        });
    };

    var nextPageAction = function() {
        $poll.find('.snax-poll-pagination-next').on('click', function(e) {
            if ($(this).hasClass(PAGINATION_LOCKED)) {
                e.preventDefault();
            }

        });
    };

    var showResults = function() {
        if ('poll-end' === poll.revealCorrectWrongAnswers()) {
            $poll.find('.snax-poll-question').each(function() {
                revealCorrectWrongAnswers($(this));
            });
        }

        if (poll.oneQuestionPerPage()) {
            var $questions = $poll.find('.snax-poll-questions-wrapper').clone();

            $poll.find('.snax-poll-questions-wrapper').addClass('snax-poll-one-per-page-finished');

            // Show all answered answers.
            $questions.find('.' + QUESTION_ANSWERED).removeClass(QUESTION_HIDDEN);

            // Append and show correct answers.
            $poll.find('.snax-poll-check-answers').
                append($questions).
                removeClass('snax-poll-check-answers-hidden');
        }
    };

    var revealCorrectWrongAnswers = function($question) {
        if ( $question.hasClass(QUESTION_REVEAL_ANSWERS) ){
            return;
        }
        var questionId      = parseInt($question.attr('data-quizzard-question-id'), 10);

        // Show all users' answers (%).
        var tweenFont = function( t, b, c, d ) {
            return c * Math.sin(t/d * (Math.PI/2)) + b;
        };
        var i18n = $.parseJSON(snax_poll_config).i18n;
        var answers = pollResults.questions[questionId];
        var shareHTML = pollResults.shareHTML;

        for (var answerId in answers.answers) {
            var amount      = answers.answers[answerId];
            var percentage  = Math.round( amount / answers.total * 100 );
            var percentageSize = percentage + 50;
            var percentageClass = 'snax-poll-answer-percentage-higher';
            if (percentage < 50 ) {
                percentageClass = 'snax-poll-answer-percentage-lower';
            }
            var fontSize = tweenFont(percentage, 16, 40, 100);
            var amountText = amount;
            if (amountText > 1000 && amountText < 10000) {
                amountText = parseInt(amountText,10) / 1000;
                amountText = + amountText.toFixed(2);
                amountText += i18n.k;
            }
            if (amountText > 10000) {
                amountText = parseInt(amountText,10) / 1000;
                amountText = + amountText.toFixed(1);
                amountText += i18n.k;
            }
            $('.snax-poll-question-' + questionId + ' .snax-poll-anticipation').remove();
            // classic polls.
            $poll.find('.poll-classic .snax-poll-answer-' + answerId).prepend('<div class="snax-poll-answer-results"><div class="snax-poll-answer-results-percentage">' + percentage + '%</div><div class="snax-poll-answer-results-amount">' + amountText + ' ' + i18n.votes +'</div></div>');
            $poll.find('.poll-classic .snax-poll-answer-' + answerId + ' .snax-poll-answer-label').prepend('<div class="snax-poll-answer-percentage"><div style="width: '+ percentage +'%;"></div></div>');
            $poll.find('.poll-classic .snax-poll-answer-' + answerId).parent('.snax-poll-answers-item').attr('data-snax-percentage',percentage);

            // versus polls.
            $poll.find('.poll-versus .snax-poll-answer-' + answerId + ' .snax-poll-answer-media').append('<div class="snax-poll-answer-percentage ' + percentageClass + ' " style = "font-size:'+  fontSize +'px"><div style="height: '+ percentageSize +'%;width: '+ percentageSize +'%;">' + percentage + '%</div></div>');

            var binarySize = tweenFont(percentage, 20, 100, 100);
            // binary polls.
            var binaryResultClass = 'snax-poll-binary-result-' + questionId;
            if ( $poll.find('.' + binaryResultClass).length === 0 ){
                $poll.find('.poll-binary .snax-poll-question-' + questionId + ' .snax-poll-question-media').append('<div class="snax-poll-binary-result ' + binaryResultClass + '"></div>');
            }
            $poll.find('.' + binaryResultClass).append('<div class="snax-poll-answer-percentage ' + percentageClass + ' " style = "font-size:'+  fontSize +'px;"><div style="height:' + binarySize + 'px;width:' + binarySize + 'px;">' + percentage + '%</div></div>');
        }

        // add zero vote results in classic poll.
        $poll.find('.poll-classic .snax-poll-question-' + questionId + ' .snax-poll-answer:not(:has(.snax-poll-answer-results))').each(function(){
            $(this).prepend('<div class="snax-poll-answer-results"><div class="snax-poll-answer-results-percentage">' + '0' + '%</div></div>');
            $(this).find('.snax-poll-answer-label').prepend('<div class="snax-poll-answer-percentage"><div style="width: '+ '0' +'%;"></div></div>');
            $(this).parent('.snax-poll-answers-item').attr('data-snax-percentage',0);
        });

        // add zero votes to versus.
        $poll.find('.poll-versus .snax-poll-question-' + questionId + ' .snax-poll-answer-media:not(:has(.snax-poll-answer-percentage))').each(function(){
            $(this).append('<div class="snax-poll-answer-percentage snax-poll-answer-percentage-lower" style = "font-size:16px"><div style="height: 50%;width: 50%;">0%</div></div>');
        });

        // add zero votes to binary.
        var $binaryPercent = $poll.find('.poll-binary .snax-poll-question-' + questionId + ' .snax-poll-question-media .snax-poll-binary-result .snax-poll-answer-percentage');
        if ($binaryPercent.length < 2 ){
            var $binaryResult = $poll.find('.poll-binary .snax-poll-question-' + questionId + ' .snax-poll-question-media .snax-poll-binary-result');
            if ($poll.find('.poll-binary .snax-poll-question-' + questionId + ' .snax-poll-answers-item:first-child .snax-poll-answer').hasClass('snax-poll-answer-checked')){
                $binaryResult.append('<div class="snax-poll-answer-percentage snax-poll-answer-percentage-lower " style="font-size:20px"><div style="height:40px;width:40px;">0%</div></div>');
            } else {
                $binaryResult.prepend('<div class="snax-poll-answer-percentage snax-poll-answer-percentage-lower " style="font-size:20px"><div style="height:40px;width:40px;">0%</div></div>');
            }
        }

        // sort classic.
        var $li = $('.poll-classic .snax-poll-question-' + questionId + ' .snax-poll-answers-item');
        var $ul = $('.poll-classic .snax-poll-question-' + questionId + ' .snax-poll-answers-items');
        $li.sort(function (a, b) {
            var contentA =parseInt( $(a).attr('data-snax-percentage'));
            var contentB =parseInt( $(b).attr('data-snax-percentage'));
            return (contentA > contentB) ? -1 : (contentA < contentB) ? 1 : 0;
        });
        $li.detach().appendTo($ul);

        // add shares.
        $('.snax-poll-question-' + questionId + ' .snax-poll-answers .snax-poll-answers-items').append(shareHTML);

        // scroll to the top of the question.
        if($.parseJSON(snax_poll_config).reveal_correct_wrong_answers !== 'poll-end'){
            if ($('.poll-classic .snax-poll-question-' + questionId).length > 0 ){
                $('.poll-classic .snax-poll-question-' + questionId).get(0).scrollIntoView();
            }
        }

        $question.addClass(QUESTION_REVEAL_ANSWERS);
    };

    ctx.initPoll = function () {
        $poll = $('.snax_poll');

        $poll.addClass('snax-share-object');

        if ($poll.length === 0) {
            return;
        }

        // Create poll object.
        poll = new ctx.Poll($.parseJSON(snax_poll_config));

        // Store reference.
        $poll.data('quizzardShareObject', poll);

        var questionIds = poll.getActiveQuestions();    // It can be just a subset of all questions (shuffle: on and questions per poll: < all questions).
        var questions = [];                             // Array of DOM objects representing questions.

        $.each(questionIds, function(index, id) {
            var $question = $poll.find('.snax-poll-question-' + id);
            questions.push($question.parent());

            if (poll.shuffleAnswers()) {
                // Get all question's answers.
                var answers = $question.find('.snax-poll-answers-item');

                // Shuffle them.
                ctx.shuffleArray(answers);

                // Reorder answers in DOM.
                $question.find('.snax-poll-answers-items').append(answers);
            }
        });

        // Reorder questions in DOM.
        $poll.find('.snax-poll-questions-items').html(questions);

        // Show question(s).
        if (poll.oneQuestionPerPage()) {
            questions[poll.getPage() - 1].find('.snax-poll-question').removeClass(QUESTION_HIDDEN);
        } else {
            $.each(questions, function(index, $question) {
                $question.find('.snax-poll-question').removeClass(QUESTION_HIDDEN);
            });
        }

        bindEvents();

        poll.initAnswers();

        if ($.parseJSON(snax_poll_config).one_vote_per_user === 1) {
            var poll_id = $.parseJSON(snax_poll_config).poll_id;
            $.each(questionIds, function(index, id) {
                var cookie = ctx.readCookie('snax_poll_vote_' + poll_id + '-' + id);
                if (cookie > 0){
                    poll.addAnswer(id, cookie, 0);
                }
            });
        }
    };

    // Init.
    $(document).ready(function() {
        ctx.initPoll();
    });

})(jQuery, snax_polls);

/**
 * Define Poll class.
 */
(function($, ctx) {

    'use strict';

    ctx.Poll = function(options) {
        var obj = {};
        var defaults = {
            debug: false
        };

        var currentPage;
        var activeQuestions;
        var answeredQuestions;
        var correctAnswers;
        var answers;
        var events;
        var locked;
        var correct_answers = {};
        var QUESTION_ANSWERED       = 'snax-poll-question-answered';
        var QUESTION_UNANSWERED     = 'snax-poll-question-unanswered';
        var ANSWER_CHECKED          = 'snax-poll-answer-checked';

        // Constructor.
        var init = function () {
            options = $.extend(defaults, options);

            for (var i = 0; i < options.questions_answers_arr.length; i++) {
                var item = options.questions_answers_arr[i];

                correct_answers[item.question_id] = item.answer;
            }

            log(options);

            currentPage = options.page;
            locked      = options.share_to_unlock;

            // Register default callbacks.
            events = {
                'started':          function() {},
                'ended':            function() {},
                'unlocked':         function() {},
                'questionAnswered': function() {}
            };

            return obj;
        };

        obj.initAnswers = function() {
            correctAnswers      = 0;    // Number of correct answers.
            answers             = {};   // Answer list (question id => answer id).
            answeredQuestions   = 0;    // Number of question that were already answered.

            var storedAnswers = readFromLocalStorage('answers');

            if (null !== storedAnswers) {
                for (var questionId in storedAnswers) {
                    var answerId = storedAnswers[questionId];

                    obj.addAnswer(questionId, answerId, 1);
                }

                log('State updated.');
                log('Answered questions: ' + answeredQuestions);
                log('Correct answers: ' + correctAnswers);
            }

            // Hide "Let's Play" button.
            if (obj.oneQuestionPerPage() && 1 === currentPage) {
                var questionsOnPage   = obj.getActiveQuestions();
                var currentQuestionId = questionsOnPage[0];

                if (wasQuestionAnswered(currentQuestionId)) {
                    obj.start();
                }
            }


        };

        obj.getActiveQuestions = function() {
            // When we shuffle questions, we need to keep the same state over all pages.
            var storeLocally = obj.shuffleQuestions() && obj.oneQuestionPerPage();

            if (storeLocally) {
                activeQuestions = readFromLocalStorage('active_questions');
            }

            if (!activeQuestions) {
                log('Build final poll question list');
                activeQuestions = [];

                // All questions, in original order.
                for ( var i = 0; i < options.questions_answers_arr.length; i++ ) {
                    var item = options.questions_answers_arr[i];

                    activeQuestions.push(item.question_id);
                }

                log('Active questions');
                log(activeQuestions);

                if (obj.shuffleQuestions()) {
                    ctx.shuffleArray(activeQuestions);

                    log('Shuffled questions');
                    log(activeQuestions);

                    if (-1 !== options.questions_per_poll) {
                        limitQuestions();

                        log('Limited questions');
                        log(activeQuestions);
                    }
                }

                if (storeLocally) {
                    addToLocalStorage('active_questions', activeQuestions);
                }
            }

            return activeQuestions;
        };

        obj.addAnswer = function(questionId, answerId, points) {
            // Proceed only if question is not answered yet.
            if (wasQuestionAnswered(questionId)) {
                return;
            }

            answeredQuestions++;

            answers[questionId] = answerId;

            if (obj.isCorrectAnswer(questionId, answerId)) {
                correctAnswers++;
            }

            log('Question ' + questionId + ' answered (answer ' + answerId + ').');

            if (obj.oneQuestionPerPage()) {
                addToLocalStorage('answers', answers);
            }
            var $question = $('.snax-poll-question-' + questionId);
            var $answer   = $('.snax-poll-answer-' + answerId);

            // Update states.
            $question.removeClass(QUESTION_UNANSWERED).addClass(QUESTION_ANSWERED);
            $answer.addClass(ANSWER_CHECKED);
            saveAnswer(questionId, answerId, function(results) {
                events.questionAnswered(questionId, answerId, results);

                if (answeredQuestions === activeQuestions.length) {
                    log('Poll ended.');

                    events.ended(obj.isLocked());

                    resetLocalStorage();
                }
            }, points);
        };

        obj.start = function() {
            events.started();
        };

        obj.unlock = function(verificationOk) {
            if (!obj.isLocked()) {
                return;
            }

            if (inDebugMode()) {
                verificationOk = true;
            }

            if (!verificationOk) {
                return;
            }

            log('Unlock poll.');

            locked = false;


            events.unlocked();
        };

        obj.getAnswer = function(questionId) {
            return answers[questionId];
        };

        obj.getCorrectAnswer = function(questionId) {
            return correct_answers[questionId];
        };

        obj.revealCorrectWrongAnswers = function() {
            return options.reveal_correct_wrong_answers;
        };

        obj.shuffleQuestions = function() {
            return options.shuffle_questions;
        };

        obj.shuffleAnswers = function() {
            return options.shuffle_answers;
        };

        obj.oneQuestionPerPage = function() {
            return options.one_question_per_page;
        };

        obj.isCorrectAnswer = function(questionId, answerId) {
            return answerId === correct_answers[questionId];
        };

        obj.on = function(eventName, callback) {
            events[eventName] = callback;
        };

        obj.getScore = function(type) {
            var correct = correctAnswers;
            var all     = activeQuestions.length;
            var score   = '';

            switch(type) {
                case 'percentage':
                    score = Math.round(correct / all * 100);
                    break;
            }

            return score;
        };

        obj.isLocked = function() {
            return locked;
        };

        var wasQuestionAnswered = function(questionId) {
            return typeof answers[questionId] !== 'undefined';
        };

        obj.getPage = function() {
            return currentPage;
        };

        var limitQuestions = function() {
            var questionsLimit = Math.min(options.questions_per_poll, options.all_questions);

            if (questionsLimit !== activeQuestions.length) {
                activeQuestions.splice(questionsLimit, activeQuestions.length - questionsLimit);
            }
        };

        var saveAnswer = function(questionId, answerId, callback, points) {
            log('Save answer.');

            var xhr = $.ajax({
                'type': 'POST',
                'url': options.ajax_url,
                'dataType': 'json',
                'data': {
                    'action':       'snax_save_poll_answer',
                    'poll_id':      options.poll_id,
                    'author_id':    options.author_id,
                    'question_id':  questionId,
                    'answer_id':    answerId,
                    'summary':      options.share_description,
                    'add_points':   points
                }
            });

            xhr.done(function (res) {
                if (res.status === 'success') {
                    if ($.parseJSON(snax_poll_config).one_vote_per_user === 1) {
                        var cookie_name = 'snax_poll_vote_' + options.poll_id + '-' + questionId;
                        ctx.createCookie( cookie_name, answerId, 365);
                    }
                    callback(res.args.results);
                }
            });
        };

        var readFromLocalStorage = function(id) {
            log('Reading "'+ id +'" from local storage');

            // Build final var id.
            id = 'snax_poll_'+ options.poll_id + '_' + id;

            var value = ctx.readCookie(id);

            if (value !== null) {
                value = $.parseJSON(value);
            }

            log('Value: ');
            log(value);

            return value;
        };

        var addToLocalStorage = function(id, value, days) {
            log('Adding "'+ id +'" to local storage');
            log(value);

            // Build final var id.
            id = 'snax_poll_'+ options.poll_id + '_' + id;

            days = days || 1;

            ctx.createCookie(id, JSON.stringify(value), days);
        };

        var resetLocalStorage = function() {
            ctx.deleteCookie('snax_poll_' + options.poll_id + '_active_questions');
            ctx.deleteCookie('snax_poll_' + options.poll_id + '_answers');
        };

        var log = function(data) {
            if (inDebugMode() && typeof console !== 'undefined') {
                console.log(data);
            }
        };

        var inDebugMode = function() {
            return options.debug;
        };

        return init();
    };

})(jQuery, snax_polls);
