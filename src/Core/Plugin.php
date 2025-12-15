<?php

declare(strict_types=1);

namespace PrismHighlighter\Core;

use PrismHighlighter\Admin\AdminPage;
use PrismHighlighter\Admin\Settings;
use PrismHighlighter\Assets\AssetBuilder;
use PrismHighlighter\Assets\AssetLoader;
use PrismHighlighter\Blocks\GutenbergBlock;
use PrismHighlighter\Editor\TinyMCE;
use PrismHighlighter\Frontend\ContentProcessor;

/**
 * Class Plugin
 *
 * The main container class that runs the plugin modules using the Singleton pattern.
 *
 * @package PrismHighlighter\Core
 */
class Plugin
{
    /**
     * Singleton instance.
     */
    private static ?Plugin $instance = null;

    /**
     * Array of initialized service objects.
     */
    private array $services = [];

    /**
     * Retrieves the singleton instance of the Plugin class.
     */
    public static function getInstance(): Plugin
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initializes all plugin services and components.
     */
    public function run(): void
    {
        // Core Logic
        $this->services['settings'] = new Settings();
        $this->services['builder']  = new AssetBuilder($this->services['settings']);

        // Admin Area Modules
        if (is_admin()) {
            new AdminPage($this->services['settings']);
            new TinyMCE($this->services['settings']);
        }

        // Frontend & Global Assets
        new AssetLoader($this->services['settings']);
        new ContentProcessor($this->services['settings']);
        new GutenbergBlock($this->services['settings']);
    }

    /**
     * Plugin Activation
     *
     * Creates default options and builds initial asset files.
     */
    public static function activate(): void
    {
        $settings = new Settings();

        if (!get_option(PRISM_HIGHLIGHTER_OPTION_KEY)) {
            update_option(PRISM_HIGHLIGHTER_OPTION_KEY, $settings->getDefaultOptions());
        }

        // Trigger an initial build of the JS/CSS assets
        $builder = new AssetBuilder($settings);
        $builder->buildFiles();
    }
}
