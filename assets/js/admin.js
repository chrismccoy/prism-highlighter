/**
 * Admin Settings JavaScript
 */
(function($) {
    'use strict';

    var PrismAdmin = {

        /**
         * Initializes the script by caching DOM elements, binding event listeners,
         * and setting up initial UI states.
         */
        init: function() {
            // Cache frequently accessed DOM elements.
            this.$activeContainer = $('#prism-active-languages');
            this.$langList        = $('#prism-all-languages-list');
            this.$cssToggle       = $('#prism-add-css-toggle');
            this.$cssContainer    = $('#prism-css-container');
            this.$cssTextarea     = $('#prism-css-textarea');
            this.$exampleCode     = $('#prism-css-example');

            // If the main language list container is not found, exit gracefully.
            if (this.$langList.length === 0) {
                return;
            }

            this.bindEvents();
            this.setupTabOverride();
            this.toggleCssContainer();
        },

        /**
         * Binds event listeners to various UI elements on the settings page.
         * Uses event delegation for dynamically added elements.
         */
        bindEvents: function() {
            var self = this;

            // Toggle the visibility of the "Add Language" list.
            $(document).on('click', '#prism-add-lang-btn', function(e) {
                e.preventDefault();
                self.$langList.fadeToggle('fast');
            });

            // Handle "Remove All" languages action.
            $(document).on('click', '#prism-remove-all-btn', function(e) {
                e.preventDefault();
                if (confirm('Are you sure you want to remove all languages? This will disable code highlighting for all languages except Core.')) {
                    self.$activeContainer.find('.prism-remove-lang').trigger('click');
                }
            });

            // Handle checking/unchecking items in the hidden language list.
            this.$langList.on('click', 'input.prism-lang-checkbox', function(e) {
                self.handleCheckboxClick($(this));
            });

            // Handle removing an active language chip.
            this.$activeContainer.on('click', '.prism-remove-lang', function(e) {
                e.preventDefault();
                var lang = $(this).data('lang');

                // Uncheck the corresponding checkbox in the list.
                var $checkbox = self.$langList.find('input[value="' + lang + '"]');
                if ($checkbox.length) {
                    $checkbox.prop('checked', false);
                }
                self.removeChip(lang);
            });

            // Toggle custom CSS editor visibility.
            this.$cssToggle.on('change', function() {
                self.toggleCssContainer();
            });

            // Show CSS example code.
            $(document).on('click', '#prism-css-example-btn', function(e) {
                e.preventDefault();
                self.$exampleCode.fadeToggle('fast');
            });
        },

        /**
         * Logic when a language checkbox is toggled.
         * Adds/removes chips and checks dependencies.
         */
        handleCheckboxClick: function($checkbox) {
            var lang  = $checkbox.val();
            var label = $.trim($checkbox.parent().text());

            if ($checkbox.is(':checked')) {
                this.addChip(lang, label);
                this.checkDependencies(lang);
            } else {
                this.removeChip(lang);
            }
        },

        /**
         * Creates and appends a new language "chip" to the UI.
         */
        addChip: function(lang, label) {
            // Prevent duplicates.
            if ($('#prism-chip-' + lang).length > 0) {
                return;
            }

            var html = '<div class="prism-lang-chip" id="prism-chip-' + lang + '">' +
                       '<input type="hidden" name="prism_highlighter_options[lang-used][]" value="' + lang + '">' +
                       label +
                       '<a href="#" class="prism-remove-lang" data-lang="' + lang + '">' + 
                       '<span class="dashicons dashicons-dismiss"></span></a>' +
                       '</div>';

            $(html).appendTo(this.$activeContainer).hide().fadeIn('fast');
        },

        /**
         * Removes a language chip from the UI.
         */
        removeChip: function(lang) {
            $('#prism-chip-' + lang).fadeOut('fast', function() {
                $(this).remove();
            });
        },

        /**
         * Recursively enables required dependencies for a selected language.
         */
        checkDependencies: function(lang) {
            var data = window.prism_data;

            if (typeof data === 'undefined' || !data.languages || !data.languages[lang]) {
                return;
            }

            var component = data.languages[lang];

            if (component.require) {
                var required = component.require;

                if (typeof required === 'string') {
                    required = [required];
                }

                var self = this;
                $.each(required, function(index, reqLang) {
                    var $reqCheckbox = self.$langList.find('input[value="' + reqLang + '"]');

                    if ($reqCheckbox.length && !$reqCheckbox.is(':checked')) {
                        $reqCheckbox.prop('checked', true);
                        $reqCheckbox.parent().fadeOut(100).fadeIn(100);
                        self.handleCheckboxClick($reqCheckbox);
                    }
                });
            }
        },

        /**
         * Initializes TabOverride for the custom CSS textarea.
         */
        setupTabOverride: function() {
            if (this.$cssTextarea.length && typeof tabOverride !== 'undefined') {
                tabOverride.set(this.$cssTextarea[0]);
            }
        },

        /**
         * Toggles the display of the custom CSS container.
         */
        toggleCssContainer: function() {
            if (this.$cssToggle.is(':checked')) {
                this.$cssContainer.fadeIn('fast');
            } else {
                this.$cssContainer.hide();
            }
        }
    };

    // Initialize on DOM Ready.
    $(document).ready(function() {
        PrismAdmin.init();
    });

})(jQuery);
