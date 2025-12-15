/**
 * Gutenberg Block Registration
 *
 * Registers the 'Prism Code' block type for the WordPress block editor.
 * Handles the editor interface (Edit) and the frontend markup generation (Save).
 *
 * @package PrismHighlighter
 */
(function (wp, $) {
    'use strict';

    // Extract WordPress dependencies
    var el = wp.element.createElement;
    var registerBlockType = wp.blocks.registerBlockType;
    var TextareaControl = wp.components.TextareaControl;
    var SelectControl = wp.components.SelectControl;
    var InspectorControls = wp.blockEditor.InspectorControls || wp.editor.InspectorControls;
    var PanelBody = wp.components.PanelBody;
    var TextControl = wp.components.TextControl;

    /**
     * SVG Icon definition for the block.
     */
    var prismIcon = el(
        'svg',
        { width: 20, height: 20, viewBox: '0 0 20 20' },
        el('path', { d: 'M10 2l9 16H1L10 2zm1 12h-2v2h2v-2zm0-9h-2v7h2V5z' })
    );

    /**
     * Helper function to encode HTML entities within a string.
     */
    function encodeEntities(str) {
        return $('<textarea>').text(str).html();
    }

    /**
     * Register the block.
     */
    registerBlockType('prism-highlighter/code', {
        title: 'Prism Code',
        icon: prismIcon,
        category: 'common',
        keywords: ['code', 'syntax', 'highlight', 'prism', 'dev', 'programming'],

        /**
         * Block Attributes.
         */
        attributes: {
            language: {
                type: 'string',
                default: (typeof prism_vars !== 'undefined') ? prism_vars.default_lang : 'php',
            },
            content: {
                type: 'string',
                source: 'text',
                selector: 'code',
            },
            highlightLines: {
                type: 'string',
                default: '',
            },
            startLine: {
                type: 'string',
                default: '',
            },
        },

        /**
         * The Edit component.
         * Renders the block interface in the editor.
         */
        edit: function (props) {
            var attributes = props.attributes;
            var setAttributes = props.setAttributes;

            // Load languages from localized script data or fallback
            var langOptions = [];
            if (typeof prism_block_langs !== 'undefined' && Array.isArray(prism_block_langs)) {
                langOptions = prism_block_langs;
            } else {
                console.warn('Prism Highlighter Block: `prism_block_langs` not available. Using fallback defaults.');
                langOptions = [
                    { label: 'PHP', value: 'php' },
                    { label: 'JavaScript', value: 'javascript' },
                    { label: 'CSS', value: 'css' },
                    { label: 'Markup', value: 'markup' }
                ];
            }

            // Ensure current language is valid
            var currentLangIsValid = langOptions.some(function(option) {
                return option.value === attributes.language;
            });

            if (!currentLangIsValid && langOptions.length > 0) {
                setAttributes({ language: langOptions[0].value });
            } else if (langOptions.length === 0) {
                setAttributes({ language: 'php' });
            }

            /**
             * Updates the code content attribute.
             */
            function updateContent(newContent) {
                setAttributes({ content: newContent });
            }

            return [
                // Inspector Controls (Sidebar Settings)
                el(
                    InspectorControls,
                    { key: 'inspector' },
                    el(
                        PanelBody,
                        { title: 'Prism Settings', initialOpen: true },
                        el(SelectControl, {
                            label: 'Language',
                            value: attributes.language,
                            options: langOptions,
                            onChange: function (val) {
                                setAttributes({ language: val });
                            },
                            __next40pxDefaultSize: true,
                            __nextHasNoMarginBottom: true
                        }),
                        // Add a spacer div or style here if the removal of margin makes inputs touch
                        el('div', { style: { height: '10px' } }),
                        el(TextControl, {
                            label: 'Highlight Lines (e.g., 1-5, 8)',
                            value: attributes.highlightLines,
                            onChange: function (val) {
                                setAttributes({ highlightLines: val });
                            },
                            __next40pxDefaultSize: true,
                            __nextHasNoMarginBottom: true
                        }),
                        el('div', { style: { height: '10px' } }),
                        el(TextControl, {
                            label: 'Start Line Number',
                            value: attributes.startLine,
                            onChange: function (val) {
                                setAttributes({ startLine: val });
                            },
                            __next40pxDefaultSize: true,
                            __nextHasNoMarginBottom: true
                        })
                    )
                ),
                // Main Block Editor Area
                el('div', { className: 'prism-highlighter-block-wrapper' },
                    el(TextareaControl, {
                        label: 'Code Snippet',
                        value: attributes.content,
                        onChange: updateContent,
                        rows: 10,
                        className: 'prism-highlighter-block-textarea',
                        // Fix deprecation warning for bottom margin
                        __nextHasNoMarginBottom: true
                    })
                )
            ];
        },

        /**
         * The Save component.
         * Defines the markup to be saved to the database.
         */
        save: function (props) {
            var atts = props.attributes;
            var classArray = ['lang:' + atts.language];

            if (atts.highlightLines) {
                classArray.push('mark:' + atts.highlightLines);
            }

            if (atts.startLine) {
                classArray.push('gutter:true');
                classArray.push('start:' + atts.startLine);
            }

            var finalClass = classArray.join(' ');

            return el(
                'pre',
                { className: finalClass },
                el('code', {}, encodeEntities(atts.content))
            );
        },
    });

    /**
     * DOM Ready: Initialize optional third-party tab support.
     */
    $(document).ready(function() {
        $(document).on('focus', '.prism-highlighter-block-textarea textarea', function() {
            if (typeof tabOverride !== 'undefined') {
                tabOverride.set(this);
            }
        });
    });

})(window.wp, jQuery);
