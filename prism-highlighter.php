<?php
/**
 * Plugin Name: Prism Highlighter
 * Description: Integrates Prism Syntax Highlighter
 * Version: 1.0.0
 * Author: Chris McCoy
 * Text Domain: prism-highlighter
 * Domain Path: /languages
 * Requires PHP: 7.4
 *
 * @package PrismHighlighter
 */

declare(strict_types=1);

namespace PrismHighlighter;

use PrismHighlighter\Core\Plugin;

// Prevent direct access to the file.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Define Plugin Version.
 */
define('PRISM_HIGHLIGHTER_VERSION', '1.0.0');

/**
 * Define Plugin Root Path.
 */
define('PRISM_HIGHLIGHTER_PATH', plugin_dir_path(__FILE__));

/**
 * Define Plugin Root URL.
 */
define('PRISM_HIGHLIGHTER_URL', plugin_dir_url(__FILE__));

/**
 * Define Option Key for Database.
 */
define('PRISM_HIGHLIGHTER_OPTION_KEY', 'prism_highlighter_options');

/**
 * Define Build Directory Name.
 */
define('PRISM_HIGHLIGHTER_BUILD_DIR', 'prism-highlighter-build');

// Utilities
require_once PRISM_HIGHLIGHTER_PATH . 'src/Utils/Helper.php';

// Settings & Admin
require_once PRISM_HIGHLIGHTER_PATH . 'src/Admin/Settings.php';
require_once PRISM_HIGHLIGHTER_PATH . 'src/Admin/AdminPage.php';

// Assets Management
require_once PRISM_HIGHLIGHTER_PATH . 'src/Assets/AssetBuilder.php';
require_once PRISM_HIGHLIGHTER_PATH . 'src/Assets/AssetLoader.php';

// Editor Integrations
require_once PRISM_HIGHLIGHTER_PATH . 'src/Blocks/GutenbergBlock.php';
require_once PRISM_HIGHLIGHTER_PATH . 'src/Editor/TinyMCE.php';

// Frontend
require_once PRISM_HIGHLIGHTER_PATH . 'src/Frontend/ContentProcessor.php';

// Core Orchestrator
require_once PRISM_HIGHLIGHTER_PATH . 'src/Core/Plugin.php';

/**
 * Initialize the plugin instance.
 */
function prism_highlighter_init(): void
{
    Plugin::getInstance()->run();
}

add_action('plugins_loaded', 'PrismHighlighter\\prism_highlighter_init');

// Register the activation hook
register_activation_hook(__FILE__, [Plugin::class, 'activate']);
