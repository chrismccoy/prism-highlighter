<?php

declare(strict_types=1);

namespace PrismHighlighter\Frontend;

use PrismHighlighter\Admin\Settings;
use PrismHighlighter\Assets\AssetLoader;

/**
 * Class ContentProcessor
 *
 * Filters post content to process <pre> tags and apply Prism classes.
 */
class ContentProcessor
{
    /**
     * Settings Instance of the settings handler.
     */
    private Settings $settings;

    /**
     * Regex pattern to find custom pre tags.
     */
    private string $regex = "/(<pre\s[^>]*class\s*=\s*[\"\'][^>]*lang\s*:[^>]*>)(.*)(<\s*\/pre\s*>)/isU";

    /**
     * Flag to determine if scripts should be enqueued.
     */
    private bool $hasMatches = false;

    /**
     * ContentProcessor constructor.
     */
    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
        add_filter('the_content', [$this, 'filterContent'], 10);
        add_filter('comment_text', [$this, 'filterContent'], 10);
        add_action('wp_footer', [$this, 'maybeEnqueueAssets']);
    }

    /**
     * Scans content for Prism-compatible tags.
     */
    public function filterContent(string $content): string
    {
        if (preg_match($this->regex, $content)) {
            $this->hasMatches = true;
            return preg_replace_callback($this->regex, [$this, 'processMatch'], $content);
        }
        return $content;
    }

    /**
     * Callback for preg_replace to format HTML for Prism.
     */
    private function processMatch(array $matches): string
    {
        $pre_tag_open = $matches[1];
        $code_content = $matches[2];
        $options      = $this->settings->getOptions();

        $pretag_class = ['prism-highlighter-container'];
        $pretag_data  = [];
        $codetag_class = '';
        $language      = '';

        // Add Global Class
        if (!empty($options['class'])) {
            $pretag_class[] = $options['class'];
        }

        // Add Gutter (Line Numbers)
        if (!empty($options['gutter'])) {
            $pretag_class['line-number'] = 'line-numbers';
            if (($options['start-number'] ?? 1) != 1) {
                $pretag_data[] = 'data-start="' . esc_attr((string)$options['start-number']) . '"';
            }
        }

        // Parse attributes from the raw match
        if (preg_match('/class\s*=\s*[\"\']([^\"\']*)[\"\']/i', $pre_tag_open, $attr_class_match)) {
            $raw_class_attr = $attr_class_match[1];
            $normalized_class = preg_replace('/(\s*:\s*)/', ':', trim($raw_class_attr));
            $parts = explode(' ', $normalized_class);

            foreach ($parts as $part) {
                if (strpos($part, ':') === false) {
                    continue;
                }

                [$key, $val] = explode(':', $part, 2);
                $val = trim($val);

                switch ($key) {
                    case 'lang':
                        $language = $val;
                        if (strpos($val, 'add') !== false) {
                            $pretag_class[] = 'prism-highlighter-' . $val;
                            $codetag_class = 'prism-highlighter-' . $val;
                            unset($pretag_class['line-number']);
                        } else {
                            $codetag_class = 'language-' . $val;
                            $pretag_class[] = 'language-' . $val;
                        }
                        break;
                    case 'mark':
                        $pretag_data[] = 'data-line="' . esc_attr($val) . '"';
                        unset($pretag_class['line-number']);
                        break;
                    case 'class':
                        $pretag_class[] = esc_attr($val);
                        break;
                    case 'gutter':
                        if ($val === 'true') {
                            $pretag_class['line-number'] = 'line-numbers';
                        } elseif ($val === 'false') {
                            unset($pretag_class['line-number']);
                        }
                        break;
                    case 'start':
                        if ($val != 1) {
                            $pretag_data[] = 'data-start="' . esc_attr($val) . '"';
                        }
                        break;
                }
            }
        }

        $final_class_str = implode(' ', array_unique($pretag_class));
        $final_data_str  = implode(' ', $pretag_data);

        return sprintf(
            '<pre class="%s" %s><code rel="%s" class="%s">%s</code></pre>',
            esc_attr($final_class_str),
            $final_data_str,
            esc_attr($language),
            esc_attr($codetag_class),
            $code_content
        );
    }

    /**
     * Conditionally enqueues assets in the footer if matches were found.
     */
    public function maybeEnqueueAssets(): void
    {
        // Check global post object for matches even if filter wasn't triggered yet (rare edge case)
        global $post;
        if (!$this->hasMatches && is_a($post, 'WP_Post') && preg_match($this->regex, $post->post_content)) {
            $this->hasMatches = true;
        }

        if (!$this->hasMatches) {
            return;
        }

        $options = $this->settings->getOptions();
        $token   = $options['token'];
        $base_url = AssetLoader::getBuildUrl();

        wp_enqueue_style(
            'prism-base',
            PRISM_HIGHLIGHTER_URL . 'assets/css/frontend.css',
            [],
            PRISM_HIGHLIGHTER_VERSION
        );

        wp_enqueue_style(
            'prism-theme',
            $base_url . '/prism-' . $token . '.css',
            ['prism-base'],
            $token
        );

        wp_enqueue_script(
            'prism-js',
            $base_url . '/prism-' . $token . '.js',
            [],
            $token,
            true
        );
    }
}
