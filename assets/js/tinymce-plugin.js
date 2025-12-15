/**
 * TinyMCE Plugin JavaScript
 */
(function($) {
    'use strict';

    if (typeof tinymce === 'undefined') {
        return;
    }

    /**
     * Registers the TinyMCE plugin.
     */
    tinymce.create('tinymce.plugins.Prism_Highlighter', {
        /**
         * Initializes the plugin.
         */
        init: function(editor, url) {

            /**
             * Helper to toggle focus class on `<pre>` tags.
             */
            function handleFocus(node) {
                $(editor.getBody()).find('pre').removeClass('prism-highlighter-focused');

                if (node.nodeName === 'PRE') {
                    $(node).addClass('prism-highlighter-focused');
                }
            }

            /**
             * Command to open the insertion modal.
             */
            editor.addCommand('prism_command', function() {
                var node = editor.selection.getNode();
                var content = '';

                // If editing an existing block, get its text content
                if (node.nodeName === 'PRE') {
                    content = node.textContent;
                    handleFocus(node);
                } else {
                    // Otherwise get selected text
                    content = editor.selection.getContent({format: 'text'});
                }

                if (window.PrismModal) {
                    window.PrismModal.openModal(content);
                } else {
                    console.error('Prism Highlighter: PrismModal object not found.');
                }
            });

            /**
             * Bind events for visual feedback (highlighting the block when clicked).
             */
            editor.on('NodeChange', function(e) {
                handleFocus(e.element);
            });

            editor.on('Click', function(e) {
                handleFocus(e.target);
            });
        },

        /**
         * Returns plugin metadata.
         */
        getInfo: function() {
            return {
                longname: 'Prism Syntax',
                author: 'Chris McCoy',
                version: '1.0.0',
                infourl: 'https://prismjs.com/'
            };
        }
    });

    // Add plugin to TinyMCE manager.
    tinymce.PluginManager.add('prism_tinymce_btn', tinymce.plugins.Prism_Highlighter);

})(jQuery);
