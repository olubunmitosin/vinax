/* global jQuery */
/* global snax_quizzes */

(function ($, ctx) {

    'use strict';

    ctx.trivia_quiz_config = $.parseJSON(window.snax_trivia_quiz_config);

    var app = ctx.quiz;

    //--------------
    // Models
    //--------------

    app.Question = Backbone.Model.extend({
        defaults: {
            id:             '',
            order:          '',
            title:          '',
            title_hide:     false,
            media: {
                id:     '',
                image:  ''
            },
            answers:                [],
            answers_tpl:            'text',
            answers_labels_hide:    false
        },
        save: function(attrs, options) {
            if (!options) {
                options = {};
            }

            // Change request to POST.
            options.type = 'POST';

            // Proxy the call to the original save function only if model has been changed.
            Backbone.Model.prototype.save.call(this, attrs, options);

            // Model is up-to-date now.
            this.quizzardSave = false;
        },
        url : function() {
            var url = app.ajax_url + '?action=snax_sync_question';
            var id = this.get('id');

            if (id) {
                url += '&question_id=' + id;
            }

            return url;
        },
        toJSON : function() {
            var attrs = _.clone( this.attributes );

            attrs.quiz_id   = app.id;
            attrs.quiz_type = app.type;
            attrs.security  = $('#quizzard-quiz-nonce').val();

            return attrs;
        }
    });

    app.Answer = Backbone.Model.extend({
        defaults: {
            question_id:    '',
            id:             '',
            order:          '',
            title:          '',
            media:      {
                id:     '',
                image:  ''
            },
            correct:        false
        },
        save: function(attrs, options) {
            if (!options) {
                options = {};
            }

            // Change request to POST.
            options.type = 'POST';

            // Proxy the call to the original save function only if model has been changed.
            Backbone.Model.prototype.save.call(this, attrs, options);

            // Model is up-to-date now.
            this.quizzardSave = false;
        },
        url : function() {
            var url = app.ajax_url + '?action=snax_sync_answer';
            var id = this.get('id');

            if (id) {
                url += '&answer_id=' + id;
            }

            return url;
        },
        toJSON : function() {
            var attrs = _.clone( this.attributes );

            attrs.security  = $('#quizzard-quiz-nonce').val();

            return attrs;
        }
    });

    app.Result = Backbone.Model.extend({
        defaults: {
            id:         '',
            order:      '',
            title:      '',
            range: {
                low:    '',
                high:   ''
            },
            description:'',
            media:      {
                id:     '',
                image:  ''
            }
        },
        save: function(attrs, options) {
            if (!options) {
                options = {};
            }

            // Change request to POST.
            options.type = 'POST';

            // Proxy the call to the original save function only if model has been changed.
            Backbone.Model.prototype.save.call(this, attrs, options);

            // Model is up-to-date now.
            this.quizzardSave = false;
        },
        url : function() {
            var url = app.ajax_url + '?action=snax_sync_result';
            var id = this.get('id');

            if (id) {
                url += '&result_id=' + id;
            }

            return url;
        },
        toJSON : function() {
            var attrs = _.clone( this.attributes );

            attrs.quiz_id   = app.id;
            attrs.quiz_type = app.type;
            attrs.security  = $('#quizzard-quiz-nonce').val();

            return attrs;
        }
    });

    //--------------
    // Collections
    //--------------

    app.QuestionList = Backbone.Collection.extend({
        model:  app.Question,
        comparator: function (model) {
            return model.get('order');
        },
        save: function() {
            _.each(this.models, function(model) {
                // Bulk save, only on modified models.
                if (model.quizzardSave) {
                    model.save();
                }
            });
        }
    });

    app.AnswerList = Backbone.Collection.extend({
        model:  app.Answer,
        comparator: function (model) {
            return model.get('order');
        },
        save: function() {
            _.each(this.models, function(model) {
                // Bulk save, only on modified models.
                if (model.quizzardSave) {
                    model.save();
                }
            });
        }
    });

    app.ResultList = Backbone.Collection.extend({
        model:  app.Result,
        comparator: function (model) {
            return model.get('order');
        },
        save: function() {
            _.each(this.models, function(model) {
                // Bulk save, only on modified models.
                if (model.quizzardSave) {
                    model.save();
                }
            });
        }
    });

    //---------
    // Views
    //---------

    // Single question.

    app.QuestionView = Backbone.View.extend({
        tagName:    'li',
        className:  'quizzard-q-item quizzard-question-collapsed',
        template:   _.template(ctx.getBackboneTemplate('#quizzard-question-tpl')),
        initialize: function () {
            this.collapsed = true;

            // Allow access to view object from DOM.
            this.$el.data('backboneView', this);

            // Single question holds collection of its answers.
            this.collection = new app.AnswerList(this.model.get('answers'));

            this.model.on('destroy',    this.remove, this);
            this.model.on('change:id',  this.newQuestionSaved, this);
        },
        events: {
            'blur  .quizzard-question-title':           'save',
            'change .quizzard-question-title-hide':     'save',
            'change .quizzard-answers-labels-hide':     'save',
            'click .quizzard-question-title-yo':        'openToEdit',
            'click .quizzard-question-delete':          'deleteQuestion',
            'click .quizzard-question-without-media':   'selectMedia',
            'click .quizzard-question-delete-media':    'deleteMedia',
            'change .quizzard-question-answers-tpl':    'changeAnswersTemplate',
            'click a':                                  'handleActions'
        },
        render: function() {
            this.$el.html(this.template(this.model.toJSON()));

            // @todo - refactor
            this.answersView = new app.AnswersView({
                el: this.$('.quizzard-answers'),
                collection: this.collection,
                question: this.model
            });

            return this;
        },
        newQuestionSaved: function(question) {
            // Update question's answers.
            var answers = question.get('answers');

            for (var i = 0; i < answers.length; i++) {
                var answer = this.collection.at(i);

                if (answer) {
                    answer.set('id', answers[i].id);
                    answer.set('question_id', answers[i].question_id);
                }
            }
        },
        openToEdit: function(e) {
            if (e) {
                e.preventDefault();
            }

            this.setCollapsed(false);

            var $title = this.$('.quizzard-question-title');
            var title = $title.val();

            $title.val('').focus().val(title);
        },
        saveAnswers: function() {
            this.answersView.save();
        },
        save: function() {
            var title = this.$('.quizzard-question-title').val();

            // Title changed?
            if (title !== this.model.get('title')) {
                this.model.set('title', title);

                this.modelChanged();
            }

            var titleHide = this.$('.quizzard-question-title-hide').is(':checked');

            // "Title Hide" changed?
            if (titleHide !== this.model.get('title_hide')) {
                this.model.set('title_hide', titleHide);

                this.modelChanged();
            }

            // Media changed?
            var mediaId = this.$('.quizzard-question-media-id').val();
            var media = this.model.get('media');

            var newMediaId = mediaId ? parseInt(mediaId, 10) : '';
            var oldMediaId = media.id ? parseInt(media.id, 10) : '';

            if (oldMediaId !== newMediaId) {
                this.model.set('media', {
                    id:     newMediaId,
                    image:  media.image
                });

                var _this = this;

                // To load new media, we need to reload entire template.
                this.model.save(null, {
                    success: function() {
                        _this.render();
                    }
                });
            }

            // Answers template changed?
            var template = this.$('.quizzard-question-answers-tpl:checked').val();

            if (template !== this.model.get('answers_tpl')) {
                this.model.set('answers_tpl', template);

                this.modelChanged();
            }

            var answersLabelsHide = this.$('.quizzard-answers-labels-hide').is(':checked');

            // "Answers Labels Hide" changed?
            if (answersLabelsHide !== this.model.get('answers_labels_hide')) {
                this.model.set('answers_labels_hide', answersLabelsHide);

                this.modelChanged();
            }
        },
        modelChanged: function() {
            // We use our own flag because the hasChanged() returns true after calling model.save(), even the attributes are the same.
            // Reset is done in model's save() prototype.
            this.model.quizzardSave = true;
        },
        deleteQuestion: function(e) {
            e.preventDefault();
            e.stopPropagation();

            if (!ctx.confirm()) {
                return;
            }

            var _this = this;

            this.model.destroy({
                type: 'POST',
                data: {
                    request_action: 'DELETE'
                },
                processData: true,
                success: function(model, response) {
                    if ('success' === response.status) {
                        _this.remove();
                    }
                }
            });
        },
        'changeAnswersTemplate': function() {
            var template = this.$('.quizzard-question-answers-tpl:checked').val();

            // @todo Refactor this beautiful code :)
            if ('text' === template) {
                this.$('.quizzard-answers').removeClass('quizzard-answers-with-media quizzard-answers-with-media-2 quizzard-answers-with-media-3').addClass('quizzard-answers-without-media');
            } else if ('grid-2' === template) {
                this.$('.quizzard-answers').removeClass('quizzard-answers-without-media quizzard-answers-with-media quizzard-answers-with-media-2 quizzard-answers-with-media-3').addClass('quizzard-answers-with-media quizzard-answers-with-media-2');
            } else if ('grid-3' === template) {
                this.$('.quizzard-answers').removeClass('quizzard-answers-without-media quizzard-answers-with-media quizzard-answers-with-media-2 quizzard-answers-with-media-3').addClass('quizzard-answers-with-media quizzard-answers-with-media-3');
            }

            this.save();
        },
        selectMedia: function(e) {
            // @todo - ugly hack for checkbox inside media selection wrapper. Fix it.
            if ($(e.target).is('input')) {
                return;
            }

            e.preventDefault();
            var _this = this;

            ctx.openMediaLibrary({
                'onSelect': function(obj) {
                    _this.setMedia(obj);
                }
            });
        },
        setMedia: function(mediaObj) {
            this.$('.quizzard-question-media-id').val(mediaObj.id);

            this.save();
        },
        deleteMedia: function (e) {
            e.preventDefault();

            if (!ctx.confirm()) {
                return;
            }

            ctx.mediaDeleted(this.$('.quizzard-question-media-id').val());

            this.$('.quizzard-question-media-id').val('');

            this.save();
        },
        blur: function() {
            this.$('input, textarea').blur();
        },
        handleActions: function(e) {
            var $target = $(e.target);

            // We use events bubbling, to catch event on most outer wrapper.
            // Thanks to this, we can fire "blur" event on all elements, this will update model so we can render view (with new state).
            if ($target.is('.quizzard-question-toggle-state')) {
                e.preventDefault();

                this.blur();
                this.toggleState();
            }
        },
        toggleState: function() {
            this.setCollapsed(!this.collapsed);
        },
        setCollapsed: function(collapsed) {
            this.collapsed = collapsed;

            if (collapsed) {
                this.render();

                this.$el.addClass('quizzard-question-collapsed');
            } else {
                this.$el.removeClass('quizzard-question-collapsed');
            }

            this.$el.trigger('questionStateChanged', [collapsed]);
        },
        isCollapsed: function() {
            return this.collapsed;
        }
    });

    // List of questions.

    app.QuestionsView = Backbone.View.extend({
        el: '#quizzard',
        initialize: function () {
            this.$nextItem  = this.$('.quizzard-next-q-item');
            this.$input     = this.$nextItem.find('.quizzard-question-title');
            this.$addButton = this.$nextItem.find('.quizzard-add');

            this.collection.on('add', this.addOne, this);
            this.collection.on('update', this.updateNewForm, this);

            this.render();
        },
        events: {
            'keyup .quizzard-next-question .quizzard-question-title':       'updateAddButton',
            'keypress .quizzard-next-question .quizzard-question-title':    'createQuestionOnEnter',
            'click .quizzard-next-question .quizzard-add':                  'createQuestionOnClick',
            'click .quizzard-questions-collapse-all':                       'collapseAll',
            'click .quizzard-questions-expand-all':                         'expandAll',
            'questionStateChanged':                                         'stateChanged'
        },
        render: function() {
            this.collection.each(this.renderOne, this);

            this.enableReordering();
            this.updateNewForm();
        },
        updateNewForm: function() {
            if (this.collection.length === 0){
                this.$input.attr('placeholder', this.$input.attr('data-quizzard-placeholder-first'));
            } else {
                this.$input.attr('placeholder', this.$input.attr('data-quizzard-placeholder'));
            }
        },
        stateChanged: function() {
            // Use state change as a trigger to persist collection state.
            this.save();

            var collapsedQuestions = this.$('#quizzard-q-items > li.quizzard-question-collapsed').length;
            var allQuestions = this.collection.length;

            var $collapseAllButton  = this.$('.quizzard-questions-collapse-all');
            var $expandAllButton    = this.$('.quizzard-questions-expand-all');

            // All collapsed?
            if (collapsedQuestions === allQuestions) {
                $collapseAllButton.addClass('button-disabled');
            } else {
                $collapseAllButton.removeClass('button-disabled');
            }

            // All expanded?
            if (0 === collapsedQuestions) {
                $expandAllButton.addClass('button-disabled');
            } else {
                $expandAllButton.removeClass('button-disabled');
            }
        },
        save: function() {
            this.collection.save();

            this.$el.trigger('questionsSaved');
        },
        updateAddButton: function() {
            if (this.$input.val().trim().length > 0) {
                this.$addButton.removeClass('button-disabled');
            } else {
                this.$addButton.addClass('button-disabled');
            }
        },
        createQuestionOnEnter: function(e) {
            // Process only if Enter pressed.
            if ( e.which !== 13 ) { // ENTER_KEY = 13
                return;
            }

            e.preventDefault();

            // Process only if some value set.
            if ( !this.$input.val().trim() ) {
                return;
            }

            this.createQuestion();
            this.resetNewForm();
        },
        createQuestionOnClick: function(e) {
            e.preventDefault();

            if ( !this.$input.val().trim() ) {
                return;
            }

            this.createQuestion();
            this.resetNewForm();
        },
        createQuestion: function() {
            this.collection.create(this.newQuestionAttributes());
        },
        resetNewForm: function() {
            this.$input.val('');
            this.updateAddButton();
        },
        addOne: function(question) {
            this.view = this.renderOne(question);

            this.view.openToEdit();
        },
        renderOne: function(question){
            // Create new view.
            var view = new app.QuestionView({model: question });

            // Add it to DOM.
            this.$nextItem.before(view.render().el);

            this.$el.on('questionsStateChanged', function(e, collapsed) {
                view.setCollapsed(collapsed);
            });

            this.$el.on('questionsSaved', function() {
                view.saveAnswers();
            });

            return view;
        },
        newQuestionAttributes: function() {
            var lastItemOrder = this.collection.length > 0 ? this.collection.at(this.collection.length - 1).get('order') : 0;

            return {
                title: this.$input.val().trim(),
                order: lastItemOrder + 1,
                answers: [
                    {
                        order:          1,
                        title:          ctx.trivia_quiz_config.i18n.yes,
                        correct:        true
                    },
                    {
                        order:          2,
                        title:          ctx.trivia_quiz_config.i18n.no,
                        correct:        false
                    }
                ]
            };
        },
        collapseAll: function(e) {
            e.preventDefault();

            this.setCollapsed(true);
        },
        expandAll: function(e) {
            e.preventDefault();

            this.setCollapsed(false);
        },
        setCollapsed: function(collapsed) {
            this.$el.trigger('questionsStateChanged', [collapsed]);
        },
        enableReordering: function() {
            var _this = this;
            var $list = this.$el.find('#quizzard-q-items');

            if (!$list.sortable('instance')) {
                $list.sortable({
                    items:          '> li:not(.quizzard-next-q-item)',
                    containment:    'parent',
                    placeholder:    'quizzard-q-item quizzard-state-highlight',
                    stop: function () {
                        _this.updateOrder();
                    }
                });
            } else {
                $list.sortable('enable');
            }
        },
        disableReordering: function() {
            var $list = this.$el.find('#quizzard-q-items');

            $list.sortable('disable');
        },
        updateOrder: function() {
            this.$('.quizzard-q-item').not('.quizzard-next-q-item').each(function(index) {
                var question = $(this).data('backboneView').model;
                var newOrder = index + 1;

                if (newOrder !== question.get('order')) {
                    question.set('order', newOrder);
                    question.save();
                }
            });

            this.collection.sort();
        }
    });

    // Single answer.

    app.AnswerView = Backbone.View.extend({
        tagName: 'li',
        className: 'quizzard-item',
        template: _.template(ctx.getBackboneTemplate('#quizzard-answer-tpl')),
        initialize:function () {
            // Allow access to view object from DOM.
            this.$el.data('backboneView', this);

            this.model.on('destroy',    this.remove, this);
            this.model.on('change:question_id',  this.newQuestionSaved, this);
        },
        events: {
            'blur   .quizzard-answer-title':         'save',
            'change .quizzard-answer-correct':       'save',
            'click  .quizzard-answer-delete':        'deleteAnswer',
            'click  .quizzard-answer-without-media': 'selectMedia',
            'click  .quizzard-answer-delete-media':  'deleteMedia'
        },
        render: function(){
            this.$el.html(this.template(this.model.toJSON()));

            return this;
        },
        newQuestionSaved: function() {
            var questionId = this.model.get('question_id');

            this.$('[name=snax_answer_correct_]').each(function() {
                $(this).attr('name', 'snax_answer_correct_' + questionId);
            });
        },
        save: function(e, stopPropagation) {
            var input = this.$('.quizzard-answer-title').val();

            // Title change?
            if (input !== this.model.get('title')) {
                this.model.set('title', input);

                this.modelChanged();
            }

            var correct     = this.$('.quizzard-answer-correct').is(':checked');
            var oldCorrect  = this.model.get('correct') ? true : false;

            // Correct changed?
            if (correct !== oldCorrect) {
                // Update other radios.
                if (!stopPropagation) {
                    $(this.el).siblings().find('.quizzard-answer-correct').trigger('change', [true]);
                }

                this.model.set('correct', correct);

                this.modelChanged();
            }

            // Media changed?
            var mediaId = this.$('.quizzard-answer-media-id').val();
            var media = this.model.get('media');

            var newMediaId = mediaId ? parseInt(mediaId, 10) : '';
            var oldMediaId = media.id ? parseInt(media.id, 10) : '';

            if (oldMediaId !== newMediaId) {
                this.model.set('media', {
                    id:     newMediaId,
                    image:  media.image
                });

                var _this = this;

                // To load new media, we need to reload entire template.
                this.model.save(null, {
                    success: function() {
                        _this.render();
                    }
                });
            }
        },
        modelChanged: function() {
            // We use our own flag because the hasChanged() returns true after calling model.save(), even the attributes are the same.
            // Reset is done in model's save() prototype.
            this.model.quizzardSave = true;
        },
        deleteAnswer: function(e) {
            e.preventDefault();

            if (!ctx.confirm()) {
                return;
            }

            var _this = this;

            this.model.destroy({
                type: 'POST',
                data: {
                    request_action: 'DELETE'
                },
                processData: true,
                success: function(model, response) {
                    if ('success' === response.status) {
                        _this.remove();
                    }
                }
            });
        },
        selectMedia: function(e) {
            e.preventDefault();
            var _this = this;

            ctx.openMediaLibrary({
                'onSelect': function(obj) {
                    _this.setMedia(obj);
                }
            });
        },
        setMedia: function(mediaObj) {
            this.$('.quizzard-answer-media-id').val(mediaObj.id);

            this.save();
        },
        deleteMedia: function (e) {
            e.preventDefault();

            if (!ctx.confirm()) {
                return;
            }

            ctx.mediaDeleted(this.$('.quizzard-answer-media-id').val());

            this.$('.quizzard-answer-media-id').val('');

            this.save();
        }
    });

    // List of answers.

    app.AnswersView = Backbone.View.extend({
        initialize: function (options) {
            this.$nextItem  = this.$('.quizzard-next-item');
            this.$input     = this.$nextItem.find('.quizzard-answer-title');
            this.$addButton = this.$nextItem.find('.quizzard-add');

            this.question = options.question;

            this.collection.on('add', this.addOne, this);
            this.collection.on('change reset add remove', this.updateParentQuestion, this);

            this.render();
        },
        events: {
            'keyup .quizzard-next-answer .quizzard-answer-title':       'updateAddButton',
            'keypress .quizzard-next-answer .quizzard-answer-title':    'createAnswerOnEnter',
            'click .quizzard-next-answer .quizzard-add':                'createAnswerOnClick'
        },
        render: function() {
            this.collection.each(this.addOne, this);

            this.enableReordering();
        },
        save: function() {
            this.collection.save();
        },
        updateAddButton: function() {
            if (this.$input.val().trim().length > 0) {
                this.$addButton.removeClass('button-disabled');
            } else {
                this.$addButton.addClass('button-disabled');
            }
        },
        createAnswerOnEnter: function(e) {
            // Process only if Enter pressed.
            if ( e.which !== 13 ) { // ENTER_KEY = 13
                return;
            }

            e.preventDefault();

            // Process only if some value set.
            if ( !this.$input.val().trim() ) {
                return;
            }

            this.createAnswer();
            this.resetNewForm();
        },
        createAnswerOnClick: function(e) {
            e.preventDefault();

            if ( !this.$input.val().trim() ) {
                return;
            }

            this.createAnswer();
            this.resetNewForm();
        },
        createAnswer: function() {
            this.collection.create(this.newAnswerAttributes());
        },
        resetNewForm: function() {
            this.$input.val('');
            this.updateAddButton();
        },
        addOne: function(answer){
            var view = new app.AnswerView({model: answer});

            this.$nextItem.before(view.render().el);
        },
        newAnswerAttributes: function() {
            var lastItemOrder = this.collection.length > 0 ? this.collection.at(this.collection.length - 1).get('order') : 0;

            return {
                question_id:    this.question.get('id'),
                title:          this.$input.val().trim(),
                order:          lastItemOrder + 1
            };
        },
        updateParentQuestion: function() {
            this.question.set('answers', this.collection.toJSON());
        },
        enableReordering: function() {
            var _this = this;
            var $list = this.$el.find('.quizzard-items');

            if (!$list.sortable('instance')) {
                $list.sortable({
                    items:          '> li:not(.quizzard-next-item)',
                    containment:    'parent',
                    placeholder:    'quizzard-item quizzard-state-highlight',
                    stop: function () {
                        _this.updateOrder();
                    }
                });
            } else {
                $list.sortable('enable');
            }
        },
        updateOrder: function() {
            this.$('.quizzard-item').not('.quizzard-next-item').each(function(index) {
                var answer = $(this).data('backboneView').model;
                var newOrder = index + 1;

                if (newOrder !== answer.get('order')) {
                    answer.set('order', newOrder);
                    answer.save();
                }
            });

            this.collection.sort();
        }
    });

    // Single result.

    app.ResultView = Backbone.View.extend({
        tagName: 'li',
        className: 'quizzard-r-item quizzard-result-collapsed',
        template: _.template(ctx.getBackboneTemplate('#quizzard-result-tpl')),
        initialize: function () {
            this.collapsed = true;

            // Allow access to view object from DOM.
            this.$el.data('backboneView', this);

            this.model.on('destroy', this.remove, this);
        },
        events: {
            'blur  .quizzard-result-title':           'update',
            'click .quizzard-result-title-yo':        'openToEdit',
            'blur  .quizzard-result-range-low':       'update',
            'blur  .quizzard-result-range-high':      'update',
            'blur  .quizzard-result-description':     'update',
            'click .quizzard-result-delete':          'deleteResult',
            'click .quizzard-result-without-media':   'selectMedia',
            'click .quizzard-result-delete-media':    'deleteMedia',
            'click a':                                'handleActions'
        },
        render: function() {
            this.$el.html(this.template(this.model.toJSON()));

            return this;
        },
        openToEdit: function(e) {
            if (e) {
                e.preventDefault();
            }

            this.setCollapsed(false);

            var $title = this.$('.quizzard-result-title');
            var title = $title.val();

            $title.val('').focus().val(title);
        },
        update: function() {
            // Title changed?
            var title = this.$('.quizzard-result-title').val();

            if (title !== this.model.get('title')) {
                this.model.set('title', title);

                this.modelChanged();
            }

            // Ranges changed?
            var range       = this.model.get('range');
            var rangeLow    = this.$('input.quizzard-result-range-low').val();
            var rangeHigh   = this.$('input.quizzard-result-range-high').val();

            if (rangeLow !== range.low) {
                this.model.set('range', {
                    low:    rangeLow,
                    high:   range.high
                });

                this.modelChanged();
            }

            if (rangeHigh !== range.high) {
                this.model.set('range', {
                    low:    range.low,
                    high:   rangeHigh
                });

                this.modelChanged();
            }

            // Description changed?
            var description = this.$('.quizzard-result-description').val();

            if (description !== this.model.get('description')) {
                this.model.set('description', description);

                this.modelChanged();
            }

            // Media changed?
            var media = this.model.get('media');
            var mediaId = this.$('.quizzard-result-media-id').val();

            var newMediaId = mediaId ? parseInt(mediaId, 10) : '';
            var oldMediaId = media.id ? parseInt(media.id, 10) : '';

            if (oldMediaId !== newMediaId) {
                this.model.set('media', {
                    id:     newMediaId,
                    image:  media.image
                });

                var _this = this;

                // To load new media, we need to reload entire template.
                this.model.save(null, {
                    success: function() {
                        _this.render();
                    }
                });
            }
        },
        modelChanged: function() {
            // We use our own flag because the hasChanged() returns true after calling model.save(), even the attributes are the same.
            // Reset is done in model's save() prototype.
            this.model.quizzardSave = true;
        },
        deleteResult: function(e) {
            e.preventDefault();
            e.stopPropagation();

            if (!ctx.confirm()) {
                return;
            }

            var _this = this;

            this.model.destroy({
                type: 'POST',
                data: {
                    request_action: 'DELETE'
                },
                processData: true,
                success: function(model, response) {
                    if ('success' === response.status) {
                        _this.remove();
                    }
                }
            });
        },
        selectMedia: function(e) {
            e.preventDefault();
            var _this = this;

            ctx.openMediaLibrary({
                'onSelect': function(obj) {
                    _this.setMedia(obj);
                }
            });
        },
        setMedia: function(mediaObj) {
            this.$('.quizzard-result-media-id').val(mediaObj.id);

            this.update();
        },
        deleteMedia: function (e) {
            e.preventDefault();

            if (!ctx.confirm()) {
                return;
            }

            ctx.mediaDeleted(this.$('.quizzard-result-media-id').val());

            this.$('.quizzard-result-media-id').val('');

            this.update();
        },
        blur: function() {
            this.$('input, textarea').blur();
        },
        handleActions: function(e) {
            var $target = $(e.target);

            // We use events bubbling, to catch event on most outer wrapper.
            // Thanks to this, we can fire "blur" event on all elements, this will update model so we can render view (with new state).
            if ($target.is('.quizzard-result-toggle-state')) {
                e.preventDefault();

                this.blur();
                this.toggleState();
            }
        },
        toggleState: function() {
            this.setCollapsed(!this.collapsed);
        },
        setCollapsed: function(collapsed) {
            this.collapsed = collapsed;

            if (collapsed) {
                this.render();

                this.$el.addClass('quizzard-result-collapsed');
            } else {
                this.$el.removeClass('quizzard-result-collapsed');
            }

            this.$el.trigger('resultStateChanged', [collapsed]);
        },
        isCollapsed: function() {
            return this.collapsed;
        }
    });

    // List of results.

    app.ResultsView = Backbone.View.extend({
        el: '#quizzard-results',
        initialize: function () {
            this.$nextItem  = this.$('.quizzard-next-r-item');
            this.$input     = this.$nextItem.find('.quizzard-result-title');
            this.$addButton = this.$nextItem.find('.quizzard-add');

            this.collection.on('add', this.addOne, this);
            this.collection.on('update', this.updateNewForm, this);

            this.render();
        },
        events: {
            'keyup .quizzard-next-result .quizzard-result-title':       'updateAddButton',
            'keypress .quizzard-next-result .quizzard-result-title':    'createResultOnEnter',
            'click .quizzard-next-result .quizzard-add':                'createResultOnClick',
            'click .quizzard-results-reset':                            'resetResultsOnClick',
            'resultStateChanged':                                       'save'
        },
        render: function() {
            this.collection.each(this.renderOne, this);

            this.enableReordering();
            this.updateNewForm();
        },
        updateNewForm: function() {
            if (this.collection.length === 0){
                this.$input.attr('placeholder', this.$input.attr('data-quizzard-placeholder-first'));
            } else {
                this.$input.attr('placeholder', this.$input.attr('data-quizzard-placeholder'));
            }
        },
        save: function() {
            this.collection.save();
        },
        updateAddButton: function() {
            if (this.$input.val().trim().length > 0) {
                this.$addButton.removeClass('button-disabled');
            } else {
                this.$addButton.addClass('button-disabled');
            }
        },
        createResultOnEnter: function(e) {
            // Process only if Enter pressed.
            if ( e.which !== 13 ) { // ENTER_KEY = 13
                return;
            }

            e.preventDefault();

            // Process only if some value set.
            if ( !this.$input.val().trim() ) {
                return;
            }

            this.createResult();
            this.resetNewForm();
        },
        createResultOnClick: function(e) {
            e.preventDefault();

            if ( !this.$input.val().trim() ) {
                return;
            }

            this.createResult();
            this.resetNewForm();
        },
        createResult: function() {
            this.collection.create(this.newResultAttributes());
        },
        resetNewForm: function() {
            this.$input.val('');
            this.updateAddButton();
        },
        resetResultsOnClick: function(e) {
            e.preventDefault();

            this.resetResults();
        },
        resetResults: function() {
            // Delete data.
            while(this.collection.length > 0) {
                var model = this.collection.pop();

                model.destroy({
                    data: {
                        action: 'DELETE'
                    },
                    processData: true
                });
            }

            // Check if some questions exist.
            var questionsCount = parseInt(app.questionsView.collection.length, 10);

            if (questionsCount === 0) {
                return;
            }

            // Build auto results.
            var autoResults = [];

            var scores = app.scores;

            var resultsCount = Math.min(questionsCount, 5); // Max 5 ranges.
            var rangeLow = 0;
            var rangeStep = Math.round(100 / resultsCount);

            for (var i = 0; i < resultsCount; i++) {
                var isFirst = 0 === i;
                var isLast  = resultsCount - 1 === i;

                var title;
                var low  = isFirst ? 0   : rangeLow + 1;
                var high = isLast  ? 100 : Math.min(rangeLow + rangeStep, 100);

                if (isLast) {
                    title = scores[5];  // Best.
                } else if (isFirst) {
                    title = scores[1];  // Worst.
                } else {
                    title = scores[ Math.round( high / rangeStep ) ];
                }

                // Add result.
                autoResults.push({
                    'order': i + 1,
                    'title': title,
                    'range': {
                        'low':  low,
                        'high': high
                    }
                });

                rangeLow += rangeStep;
            }

            // Add new results to collection.
            this.collection.add(autoResults);

            this.setCollapsed(true);

            this.save();
        },
        addOne: function(result) {
            var view = this.renderOne(result);

            view.openToEdit();
        },
        renderOne: function(result){
            var view = new app.ResultView({model: result });

            this.$nextItem.before(view.render().el);

            this.$el.on('resultsStateChanged', function(e, collapsed) {
                view.setCollapsed(collapsed);
            });

            return view;
        },
        newResultAttributes: function() {
            var lastItemOrder = this.collection.length > 0 ? this.collection.at(this.collection.length - 1).get('order') : 0;

            return {
                title: this.$input.val().trim(),
                order: lastItemOrder + 1
            };
        },
        setCollapsed: function(collapsed) {
            this.$el.trigger('resultsStateChanged', [collapsed]);
        },
        enableReordering: function() {
            var _this = this;
            var $list = this.$el.find('#quizzard-r-items');

            if (!$list.sortable('instance')) {
                $list.sortable({
                    items:          '> li:not(.quizzard-next-r-item)',
                    containment:    'parent',
                    placeholder:    'quizzard-r-item quizzard-state-highlight',
                    stop: function () {
                        _this.updateOrder();
                    }
                });
            } else {
                $list.sortable('enable');
            }
        },
        disableReordering: function() {
            var $list = this.$el.find('#quizzard-r-items');

            $list.sortable('disable');
        },
        updateOrder: function() {
            this.$('.quizzard-r-item').not('.quizzard-next-r-item').each(function(index) {
                var result   = $(this).data('backboneView').model;
                var newOrder = index + 1;

                if (newOrder !== result.get('order')) {
                    result.set('order', newOrder);
                    result.save();
                }
            });

            this.collection.sort();
        }
    });

    //--------------
    // Initializers
    //--------------

    // Questions view.

    app.questionsView = new app.QuestionsView({
        collection: new app.QuestionList(app.questions)
    });

    // Results view.

    app.resultsView = new app.ResultsView({
        collection: new app.ResultList(app.results)
    });

    // Notify about quiz save action.
    $(document).ready(function() {

        // Persist collections on WP post save.
        $('form#post, form.snax').on('submit', function() {
            app.questionsView.save();
            app.resultsView.save();
        });

        // Tabs.
        var selectTab = function(index) {
            var $tab = $('.quizzard-tab-wrapper > .nav-tab');
            $tab.removeClass('nav-tab-active');         // Reset all.
            $tab.eq(index).addClass('nav-tab-active');  // Activate current.

            var $tabContent = $('.quizzard-tab-content');
            $tabContent.removeClass('quizzard-tab-content-active');         // Rest all.
            $tabContent.eq(index).addClass('quizzard-tab-content-active');  // Activate current.
        };

        $('.quizzard-tab-wrapper > .nav-tab').on('click', function(e) {
            e.preventDefault();

            var tabIndex = $(this).index();
            var postId = $('input#post_ID').val();

            selectTab(tabIndex);

            // Store state.
            ctx.createCookie('snax_quiz_' + postId + '_active_tab', tabIndex);
        });
    });

})(jQuery, snax_quizzes);
