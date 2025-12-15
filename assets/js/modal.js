/**
 * Modal Dialog JavaScript
 */
(function ($) {
    'use strict';

    var PrismModal = {

        /**
         * Initializes the modal script.
         */
        init: function () {
            this.cacheDOM();
            this.bindEvents();
            this.bindMediaButton();
            this.setupTabOverride();
        },

        /**
         * Caches references to essential DOM elements within the modal.
         */
        cacheDOM: function () {
            this.$overlay          = $('#prism-editor-overlay');
            this.$wrap             = $('#prism-editor-wrap');
            this.$code             = $('#prism-editor-code');
            this.$submit           = $('#prism-submit');
            this.$cancel           = $('#prism-cancel, .prism-editor-closebtn');
            this.$langSelect       = $('#prism-language');
            this.$lineNumbers      = $('#prism-show-lines');
            this.$startLine        = $('#prism-start-line');
            this.$highlightLines   = $('#prism-highlight-lines');
            this.$className        = $('#prism-class-name');
            this.$optionsToggle    = $('#prism-toggle-options');
            this.$optionsContainer = $('#prism-options-container');
        },

        /**
         * Binds event listeners to the modal's UI elements.
         */
        bindEvents: function () {
            var self = this;

            this.$cancel.on('click', function() { self.closeModal(); });
            this.$submit.on('click', function() { self.insertCode(); });
            this.$optionsToggle.on('click', function() { self.toggleOptions(); });
            this.$overlay.on('click', function() { self.closeModal(); });
        },

        /**
         * Binds the click event for the "Prism Code" button located next to "Add Media".
         * Determines active editor context (Visual vs Text).
         */
        bindMediaButton: function() {
            var self = this;

            $(document).on('click', '#prism-media-button', function(e) {
                e.preventDefault();
                var content = '';

                // Identify the active editor ID using WordPress Global
                var activeEditorId = (typeof wpActiveEditor !== 'undefined') ? wpActiveEditor : 'content';

                // Try to get the TinyMCE instance
                var editor = (typeof tinymce !== 'undefined') ? tinymce.get(activeEditorId) : null;

                // Are we in Visual Mode (TinyMCE)?
                if (editor && !editor.isHidden()) {
                    // Execute the command registered in tinymce-plugin.js
                    editor.execCommand('prism_command');
                    return;
                }

                // Are we in Text Mode (Raw HTML)?
                var $textarea = $('#' + activeEditorId);

                if ($textarea.length) {
                    var start = $textarea[0].selectionStart;
                    var end   = $textarea[0].selectionEnd;
                    content = $textarea.val().substring(start, end);
                }

                self.openModal(content);
            });
        },

        /**
         * Configures the TabOverride library for the code input textarea.
         */
        setupTabOverride: function () {
            if (this.$code.length && typeof tabOverride !== 'undefined') {
                tabOverride.set(this.$code[0]);
            }
        },

        /**
         * Opens the modal dialog.
         */
        openModal: function (content) {
            content = content || '';
            this.$code.val(content);
            this.$overlay.show();
            this.$wrap.show();
            this.$code.focus();
        },

        /**
         * Closes the modal dialog.
         */
        closeModal: function () {
            this.$overlay.hide();
            this.$wrap.hide();
            this.resetForm();
        },

        /**
         * Toggles the visibility of the advanced options container.
         */
        toggleOptions: function () {
            this.$optionsContainer.slideToggle('fast');
            this.$optionsToggle.find('span.dashicons').toggleClass('dashicons-arrow-down-alt2 dashicons-arrow-up-alt2');
        },

        /**
         * Resets all form fields.
         */
        resetForm: function () {
            this.$code.val('');
            this.$lineNumbers.prop('checked', false);
            this.$startLine.val('');
            this.$highlightLines.val('');
            this.$className.val('');

            if (typeof prism_vars !== 'undefined' && prism_vars.default_lang) {
                this.$langSelect.val(prism_vars.default_lang);
            }
        },

        /**
         * Constructs the PrismJS-compatible HTML and inserts it into the editor.
         */
        insertCode: function () {
            var language = this.$langSelect.val();
            var code     = this.$code.val();

            if (!code) {
                this.closeModal();
                return;
            }

            var classes = ['lang:' + language];

            if (this.$lineNumbers.is(':checked')) {
                classes.push('gutter:true');
                var start = this.$startLine.val().trim();
                if (start && start !== '1') {
                    classes.push('start:' + start);
                }
            }

            var highlight = this.$highlightLines.val().trim();
            if (highlight) {
                classes.push('mark:' + highlight);
            }

            var customClass = this.$className.val().trim();
            if (customClass) {
                classes.push('class:' + customClass);
            }

            var encodedCode = $('<div/>').text(code).html();
            var classString = classes.join(' ');
            var html = '<pre class="' + classString + '">' + encodedCode + '</pre>';

            // Determine Target Editor
            var activeEditorId = (typeof wpActiveEditor !== 'undefined') ? wpActiveEditor : 'content';
            var editor = (typeof tinymce !== 'undefined') ? tinymce.get(activeEditorId) : null;

            // Try TinyMCE Insertion
            if (editor && !editor.isHidden()) {
                editor.insertContent(html);
            }
            // Try QTags (Text/HTML Editor) Insertion (if function exists)
            else if (typeof QTags !== 'undefined' && typeof QTags.insertContent === 'function') {
                QTags.insertContent(html);
            }
            // Fallback: Direct Textarea Insertion
            else {
                 var $textarea = $('#' + activeEditorId);
                 if ($textarea.length) {
                     var currentVal = $textarea.val();
                     var start = $textarea[0].selectionStart;
                     var end = $textarea[0].selectionEnd;

                     $textarea.val(
                         currentVal.substring(0, start) +
                         html +
                         currentVal.substring(end)
                     );
                 }
            }

            this.closeModal();
        },
    };

    window.PrismModal = PrismModal;

    $(document).ready(function () {
        PrismModal.init();
    });

})(jQuery);
