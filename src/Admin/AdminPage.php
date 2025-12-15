<?php

declare(strict_types=1);

namespace PrismHighlighter\Admin;

use PrismHighlighter\Utils\Helper;

/**
 * Class AdminPage
 *
 * Renders the main settings page in the WordPress admin.
 */
class AdminPage
{
    /**
     * Settings
     */
    private Settings $settings;

    /**
     * AdminPage constructor.
     */
    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
        add_action('admin_menu', [$this, 'addPluginPage']);
    }

    /**
     * Adds the settings page to the Settings menu.
     */
    public function addPluginPage(): void
    {
        add_options_page(
            'Prism Highlighter Options',
            'Prism Highlighter',
            'manage_options',
            'prism-highlighter',
            [$this, 'render']
        );
    }

    /**
     * Renders the HTML for the settings page.
     */
    public function render(): void
    {
        $options      = $this->settings->getOptions();
        $languageData = Helper::getLanguageData();

        // Load the view template
        require_once PRISM_HIGHLIGHTER_PATH . 'templates/admin-settings.php';
    }
}
