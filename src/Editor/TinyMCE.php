<?php

declare(strict_types=1);

namespace PrismHighlighter\Editor;

use PrismHighlighter\Admin\Settings;
use PrismHighlighter\Utils\Helper;

/**
 * Class TinyMCE
 *
 * Integrates Prism Syntax Highlighter with the WordPress Classic Editor.
 * Handles the "Add Media" area button, the modal rendering, and editor styles.
 *
 * @package PrismHighlighter\Editor
 */
class TinyMCE
{
    /**
     * Settings Instance of the settings handler.
     */
    private Settings $settings;

    /**
     * TinyMCE constructor.
     */
    public function __construct(Settings $settings)
    {
        $this->settings = $settings;

        add_action('admin_init', [$this, 'init']);
        add_action('admin_footer', [$this, 'renderModal']);

        // Register the button next to "Add Media"
        add_action('media_buttons', [$this, 'addMediaButton']);
    }

    /**
     * Initializes editor hooks if the user has permission and rich editing is enabled.
     */
    public function init(): void
    {
        // Only add hooks if user has permissions to edit.
        if (!current_user_can('edit_posts') && !current_user_can('edit_pages')) {
            return;
        }

        // Only load if Rich Editing is enabled for the user.
        if (get_user_option('rich_editing') !== 'true') {
            return;
        }

        add_filter('mce_external_plugins', [$this, 'registerExternalPlugin']);
        add_filter('mce_css', [$this, 'addEditorStyles']);
    }

    /**
     * Renders a button next to the "Add Media" button in the Classic Editor.
     */
    public function addMediaButton(): void
    {
        $title = esc_attr__('Insert Code Snippet', 'prism-highlighter');
        $label = esc_html__('Prism Code', 'prism-highlighter');

        // The ID 'prism-media-button' is targeted by assets/js/modal.js
        echo <<<HTML
        <button type="button" id="prism-media-button" class="button" title="{$title}">
            <span class="dashicons dashicons-editor-code" style="vertical-align: text-top; margin-right: 2px;"></span>
            {$label}
        </button>
HTML;
    }

    /**
     * Adds the plugin's custom JavaScript file to the TinyMCE plugin list.
     * This script handles the 'prism_command' used by the media button.
     */
    public function registerExternalPlugin(array $plugins): array
    {
        $plugins['prism_tinymce_btn'] = PRISM_HIGHLIGHTER_URL . 'assets/js/tinymce-plugin.js';
        return $plugins;
    }

    /**
     * Adds editor-specific CSS to the TinyMCE iframe to style code blocks visually.
     */
    public function addEditorStyles(string $mce_css): string
    {
        if (!empty($mce_css)) {
            $mce_css .= ',';
        }
        $mce_css .= PRISM_HIGHLIGHTER_URL . 'assets/css/editor.css';
        return $mce_css;
    }

    /**
     * Renders the HTML markup for the "Insert Code" modal dialog in the admin footer.
     */
    public function renderModal(): void
    {
        $screen = get_current_screen();

        // Only render on post/page edit screens.
        if (!$screen || ($screen->base !== 'post' && $screen->id !== 'edit-comments')) {
            return;
        }

        $options      = $this->settings->getOptions();
        $languageData = Helper::getLanguageData();

        // Load the view template.
        require_once PRISM_HIGHLIGHTER_PATH . 'templates/editor-modal.php';
    }
}
