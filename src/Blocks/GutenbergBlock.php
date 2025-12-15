<?php

declare(strict_types=1);

namespace PrismHighlighter\Blocks;

use PrismHighlighter\Admin\Settings;
use PrismHighlighter\Utils\Helper;

/**
 * Class GutenbergBlock
 *
 * Registers and enqueues assets for the Gutenberg Block editor.
 */
class GutenbergBlock
{
    /**
     * Settings
     */
    private Settings $settings;

    /**
     * GutenbergBlock constructor.
     */
    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
        add_action('enqueue_block_editor_assets', [$this, 'enqueueBlockAssets']);
    }

    /**
     * Enqueues the block JavaScript and passes localized data.
     */
    public function enqueueBlockAssets(): void
    {
        wp_enqueue_script(
            'prism-taboverride',
            PRISM_HIGHLIGHTER_URL . 'assets/js/taboverride.js',
            [],
            '4.0.3',
            true
        );

        wp_enqueue_script(
            'prism-highlighter-block',
            PRISM_HIGHLIGHTER_URL . 'assets/js/block.js',
            ['wp-blocks', 'wp-editor', 'wp-element', 'wp-components', 'jquery', 'prism-taboverride'],
            PRISM_HIGHLIGHTER_VERSION,
            true
        );

        $options = $this->settings->getOptions();

        wp_localize_script('prism-highlighter-block', 'prism_vars', [
            'default_lang' => $options['default-lang'] ?? 'php'
        ]);

        $filtered_block_langs_options = [];
        $all_components_data = Helper::getLanguageData();
        $all_languages_list  = $all_components_data['languages'] ?? [];
        $active_settings_langs = $options['lang-used'] ?? [];
        $hidden_dependencies   = ['core', 'clike', 'markup', 'markup-templating'];

        if (!empty($all_languages_list) && !empty($active_settings_langs)) {
            foreach ($active_settings_langs as $lang_slug) {
                if (in_array($lang_slug, $hidden_dependencies, true) || !isset($all_languages_list[$lang_slug])) {
                    continue;
                }

                $label = is_array($all_languages_list[$lang_slug])
                    ? $all_languages_list[$lang_slug]['title']
                    : $all_languages_list[$lang_slug];

                $filtered_block_langs_options[] = ['label' => $label, 'value' => $lang_slug];
            }
        }
        wp_localize_script('prism-highlighter-block', 'prism_block_langs', $filtered_block_langs_options);
    }
}
