<?php

declare(strict_types=1);

namespace PrismHighlighter\Assets;

use PrismHighlighter\Admin\Settings;
use PrismHighlighter\Utils\Helper;

/**
 * Class AssetLoader
 *
 * Manages enqueueing of scripts and styles for Admin and Frontend.
 */
class AssetLoader
{
    /**
     * Settings
     */
    private Settings $settings;

    /**
     * AssetLoader constructor.
     */
    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdmin']);
    }

    /**
     * Enqueues Admin-specific scripts and styles.
     */
    public function enqueueAdmin(string $hook): void
    {
        $is_settings = ($hook === 'settings_page_prism-highlighter');
        $is_editor   = ($hook === 'post.php' || $hook === 'post-new.php');

        if (!$is_settings && !$is_editor) {
            return;
        }

        wp_enqueue_style(
            'prism-admin',
            PRISM_HIGHLIGHTER_URL . 'assets/css/admin.css',
            [],
            PRISM_HIGHLIGHTER_VERSION
        );

        wp_enqueue_script(
            'prism-taboverride',
            PRISM_HIGHLIGHTER_URL . 'assets/js/taboverride.js',
            [],
            '4.0.3',
            true
        );

        if ($is_settings) {
            wp_enqueue_script(
                'prism-admin-js',
                PRISM_HIGHLIGHTER_URL . 'assets/js/admin.js',
                ['jquery', 'prism-taboverride'],
                PRISM_HIGHLIGHTER_VERSION,
                true
            );

            wp_localize_script('prism-admin-js', 'prism_data', Helper::getLanguageData());
        }

        if ($is_editor) {
            wp_enqueue_style(
                'prism-modal',
                PRISM_HIGHLIGHTER_URL . 'assets/css/modal.css',
                ['dashicons'],
                PRISM_HIGHLIGHTER_VERSION
            );

            wp_enqueue_script(
                'prism-modal-js',
                PRISM_HIGHLIGHTER_URL . 'assets/js/modal.js',
                ['jquery', 'prism-taboverride'],
                PRISM_HIGHLIGHTER_VERSION,
                true
            );

            $opts = $this->settings->getOptions();
            wp_localize_script('prism-modal-js', 'prism_vars', [
                'default_lang' => $opts['default-lang'] ?? 'php'
            ]);
        }
    }

    /**
     * Helper to get the build directory URL.
     */
    public static function getBuildUrl(): string
    {
        $upload_dir = wp_upload_dir();
        return $upload_dir['baseurl'] . '/' . PRISM_HIGHLIGHTER_BUILD_DIR;
    }
}
