<?php

declare(strict_types=1);

namespace PrismHighlighter\Assets;

use PrismHighlighter\Admin\Settings;

/**
 * Class AssetBuilder
 *
 * Responsible for concatenating and saving custom JS and CSS builds based on selected options.
 */
class AssetBuilder
{
    /**
     * Settings Instance of the settings handler.
     */
    private Settings $settings;

    /**
     * AssetBuilder constructor.
     */
    public function __construct(Settings $settings)
    {
        $this->settings = $settings;

        // Rebuild files when options are updated.
        add_action('update_option_' . PRISM_HIGHLIGHTER_OPTION_KEY, [$this, 'buildFiles'], 10, 0);
    }

    /**
     * Run the build process for both JS and CSS files.
     */
    public function buildFiles(): void
    {
        global $wp_filesystem;

        if (empty($wp_filesystem)) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            WP_Filesystem();
        }

        $upload_dir = wp_upload_dir();
        $build_path = $upload_dir['basedir'] . DIRECTORY_SEPARATOR . PRISM_HIGHLIGHTER_BUILD_DIR;

        // Ensure build directory exists
        if (!$wp_filesystem->is_dir($build_path)) {
            $wp_filesystem->mkdir($build_path);
        }

        $options = $this->settings->getOptions();
        $token   = $options['token'] ?? (string)time();

        $this->buildJavascript($build_path, $token, $options);
        $this->buildCss($build_path, $token, $options);
    }

    /**
     * Concatenates core, languages, and plugins into a single JS file.
     */
    private function buildJavascript(string $path, string $token, array $options): void
    {
        global $wp_filesystem;

        $scripts = '';
        $base_path = PRISM_HIGHLIGHTER_PATH . 'assets/prism/';
        $selected_langs = $options['lang-used'] ?? [];

        // Add Core
        $scripts .= $this->getFileContent($base_path . 'components/prism-core.min.js');

        // Add Essential Dependencies (clike, markup, etc)
        $dependencies = ['clike', 'markup', 'markup-templating'];
        foreach ($dependencies as $dep) {
            if (in_array($dep, $selected_langs, true)) {
                $scripts .= $this->getFileContent($base_path . 'components/prism-' . $dep . '.min.js');
            }
        }

        // Add Selected Languages (excluding special/dependencies)
        foreach ($selected_langs as $lang) {
            if (
                $lang === 'core' ||
                in_array($lang, $dependencies, true) ||
                strpos($lang, 'add') === 0
            ) {
                continue;
            }
            $scripts .= $this->getFileContent($base_path . 'components/prism-' . $lang . '.min.js');
        }

        // Add Plugins
        $plugins = [
            'line-numbers'    => $options['gutter'] ?? false,
            'show-invisibles' => $options['show-hidden-char'] ?? false,
            'show-language'   => $options['show-lang'] ?? false,
            'autolinker'      => $options['auto-links'] ?? false,
        ];

        foreach ($plugins as $plugin => $enabled) {
            if ($enabled) {
                $scripts .= $this->getFileContent($base_path . 'plugins/' . $plugin . '/prism-' . $plugin . '.min.js');
            }
        }

        // Write file
        $this->cleanDirectory($path, 'js');
        $wp_filesystem->put_contents($path . '/prism-' . $token . '.js', $scripts);
    }

    /**
     * Concatenates themes and plugin styles into a single CSS file.
     */
    private function buildCss(string $path, string $token, array $options): void
    {
        global $wp_filesystem;

        $css = '';
        $base_path = PRISM_HIGHLIGHTER_PATH . 'assets/prism/';

        // Theme CSS
        $theme = ($options['theme'] === 'default') ? 'prism' : $options['theme'];
        $css .= $this->getFileContent($base_path . 'themes/' . $theme . '.css');

        // Plugin CSS
        $plugins = [
            'line-numbers'    => $options['gutter'] ?? false,
            'show-invisibles' => $options['show-hidden-char'] ?? false,
            'show-language'   => $options['show-lang'] ?? false,
        ];

        foreach ($plugins as $plugin => $enabled) {
            if ($enabled) {
                $css .= $this->getFileContent($base_path . 'plugins/' . $plugin . '/prism-' . $plugin . '.css');
            }
        }

        // Custom CSS Overrides
        if (!empty($options['max-height'])) {
            $css .= "\npre.prism-highlighter-container { max-height: " . (int)$options['max-height'] . "px; }";
        }

        if (!empty($options['add-css']) && !empty($options['add-css-value'])) {
            $css .= "\n" . strip_tags($options['add-css-value']);
        }

        // Write file
        $this->cleanDirectory($path, 'css');
        $wp_filesystem->put_contents($path . '/prism-' . $token . '.css', $css);
    }

    /**
     * Reads the content of a local file safely.
     */
    private function getFileContent(string $file): string
    {
        return file_exists($file) ? file_get_contents($file) . ";\n" : '';
    }

    /**
     * Deletes old build files with the specified extension.
     */
    private function cleanDirectory(string $path, string $ext): void
    {
        $files = glob($path . '/*.' . $ext);
        if ($files) {
            array_map('unlink', $files);
        }
    }
}
