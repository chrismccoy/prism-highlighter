<?php

declare(strict_types=1);

namespace PrismHighlighter\Admin;

/**
 * Class Settings
 *
 * Handles option registration, retrieval, and sanitization.
 */
class Settings
{
    /**
     * Registers the admin initialization hook.
     */
    public function __construct()
    {
        add_action('admin_init', [$this, 'registerSettings']);
    }

    /**
     * Register WordPress settings fields.
     */
    public function registerSettings(): void
    {
        register_setting(
            'prism_highlighter_option_group',
            PRISM_HIGHLIGHTER_OPTION_KEY,
            [$this, 'sanitizeOptions']
        );
    }

    /**
     * Retrieve current plugin options with fallback to defaults.
     */
    public function getOptions(): array
    {
        $options = get_option(PRISM_HIGHLIGHTER_OPTION_KEY, []);
        if (empty($options) || empty($options['lang-used'])) {
            return $this->getDefaultOptions();
        }
        return $options;
    }

    /**
     * Defines the default configuration for the plugin.
     */
    public function getDefaultOptions(): array
    {
        return [
            'lang-used'        => ['core', 'clike', 'markup', 'markup-templating', 'php', 'css', 'javascript', 'sql'],
            'default-lang'     => 'php',
            'max-height'       => 480,
            'add-css'          => 0,
            'add-css-value'    => '',
            'theme'            => 'default',
            'gutter'           => 1,
            'auto-links'       => 1,
            'show-lang'        => 0,
            'show-hidden-char' => 0,
            'class'            => '',
            'start-number'     => 1,
            'token'            => (string)time(),
        ];
    }

    /**
     * Sanitize and validate options submitted via the settings page.
     */
    public function sanitizeOptions(array $input): array
    {
        // Handle "Restore Defaults" action
        if (isset($_POST['prism-reset-defaults'])) {
            add_settings_error(
                'prism_highlighter_option_group',
                'defaults',
                'Settings restored to defaults.',
                'updated'
            );
            return $this->getDefaultOptions();
        }

        $input['token']        = (string)time();
        $input['default-lang'] = sanitize_text_field($input['default-lang'] ?? 'php');
        $input['theme']        = sanitize_text_field($input['theme'] ?? 'default');
        $input['class']        = sanitize_text_field($input['class'] ?? '');
        $input['start-number'] = absint($input['start-number'] ?? 1);
        $input['max-height']   = absint($input['max-height'] ?? 480);
        $input['add-css']      = isset($input['add-css']) ? 1 : 0;

        // Strip tags from custom CSS to allow basic CSS syntax but prevent malicious scripts
        $input['add-css-value'] = wp_strip_all_tags($input['add-css-value'] ?? '');

        // Sanitize Checkboxes
        $checkboxes = ['gutter', 'auto-links', 'show-lang', 'show-hidden-char'];
        foreach ($checkboxes as $cb) {
            $input[$cb] = isset($input[$cb]) ? 1 : 0;
        }

        // Sanitize Languages array
        $input['lang-used'] = isset($input['lang-used']) && is_array($input['lang-used'])
            ? array_map('sanitize_text_field', $input['lang-used'])
            : ['core'];

        // Ensure dependencies are always present
        $forced = ['core', 'clike', 'markup', 'markup-templating'];
        foreach ($forced as $dep) {
            if (!in_array($dep, $input['lang-used'], true)) {
                $input['lang-used'][] = $dep;
            }
        }

        return $input;
    }
}
