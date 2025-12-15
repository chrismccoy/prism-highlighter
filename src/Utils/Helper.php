<?php

declare(strict_types=1);

namespace PrismHighlighter\Utils;

/**
 * Class Helper
 */
class Helper
{
    /**
     * Retrieves the PrismJS language/theme/plugin data from the JSON file or cache.
     */
    public static function getLanguageData(): array
    {
        $data = get_option('prism_highlighter_data', []);

        if (!empty($data) && isset($data['languages'])) {
            return $data;
        }

        $json_file = PRISM_HIGHLIGHTER_PATH . 'assets/prism/components.json';

        // Fallback for different naming conventions
        if (!file_exists($json_file)) {
            $json_file = PRISM_HIGHLIGHTER_PATH . 'assets/prism/components_json.json';
        }

        if (file_exists($json_file)) {
            $content = file_get_contents($json_file);
            $decoded = json_decode($content, true);

            if (is_array($decoded)) {
                // Inject custom internal types
                $decoded['languages']['adddarkplain']  = ['title' => 'Dark Plain'];
                $decoded['languages']['addlightplain'] = ['title' => 'Light Plain'];

                // Cache data
                update_option('prism_highlighter_data', $decoded);
                return $decoded;
            }
        }

        return ['languages' => []];
    }
}
