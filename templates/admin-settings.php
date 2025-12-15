<?php
/**
 * Template: Admin Settings Page
 *
 * This file renders the HTML for the plugin settings page.
 */

use PrismHighlighter\Utils\Helper;

defined('ABSPATH') || exit;

$option_key = PRISM_HIGHLIGHTER_OPTION_KEY;
$languages  = $languageData['languages'] ?? [];
$themes     = $languageData['themes'] ?? [
    'default'  => 'Default',
    'dark'     => 'Dark',
    'coy'      => 'Coy',
    'okaidia'  => 'Okaidia',
    'twilight' => 'Twilight',
];

$selected_langs      = $options['lang-used'] ?? [];
$hidden_dependencies = ['clike', 'markup', 'markup-templating'];

?>
<div class="wrap prism-wrap">
    <h1><?php esc_html_e('Prism Highlighter Settings', 'prism-highlighter'); ?></h1>

    <div class="prism-form-container">
        <form method="post" action="options.php">
            <?php settings_fields('prism_highlighter_option_group'); ?>

            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e('Choose Languages', 'prism-highlighter'); ?></th>
                    <td>
                        <div id="prism-active-languages" class="prism-clearfix">
                            <?php foreach ($selected_langs as $lang) : ?>
                                <?php
                                if (in_array($lang, $hidden_dependencies, true)) {
                                    continue;
                                }

                                $label = ($lang === 'core')
                                    ? 'Core'
                                    : (is_array($languages[$lang] ?? null) ? $languages[$lang]['title'] : $lang);
                                ?>

                                <?php if ($lang === 'core') : ?>
                                    <div class="prism-lang-chip disabled">
                                        <input type="hidden" name="<?php echo esc_attr($option_key); ?>[lang-used][]" value="core">
                                        <?php echo esc_html($label); ?>
                                    </div>
                                <?php else : ?>
                                    <div class="prism-lang-chip" id="prism-chip-<?php echo esc_attr($lang); ?>">
                                        <input type="hidden" name="<?php echo esc_attr($option_key); ?>[lang-used][]" value="<?php echo esc_attr($lang); ?>">
                                        <?php echo esc_html($label); ?>
                                        <a href="#" class="prism-remove-lang" data-lang="<?php echo esc_attr($lang); ?>">
                                            <span class="dashicons dashicons-dismiss"></span>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>

                        <div class="prism-lang-controls">
                            <button type="button" class="button" id="prism-add-lang-btn">
                                <?php esc_html_e('Add Language', 'prism-highlighter'); ?>
                            </button>
                            <button type="button" class="button" id="prism-remove-all-btn">
                                <?php esc_html_e('Remove All', 'prism-highlighter'); ?>
                            </button>
                        </div>

                        <div id="prism-all-languages-list" style="display:none;">
                            <label class="prism-lang-label">
                                <input type="checkbox" disabled checked> Core
                            </label>
                            <hr>
                            <?php foreach ($languages as $key => $data) : ?>
                                <?php
                                if ($key === 'meta' || $key === 'core' || in_array($key, $hidden_dependencies, true)) {
                                    continue;
                                }
                                $title     = is_array($data) ? $data['title'] : $data;
                                $isChecked = in_array($key, $selected_langs, true) ? 'checked' : '';
                                ?>
                                <label class="prism-lang-label">
                                    <input type="checkbox" class="prism-lang-checkbox" value="<?php echo esc_attr($key); ?>" <?php echo esc_attr($isChecked); ?>>
                                    <?php echo esc_html($title); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php esc_html_e('Default Language', 'prism-highlighter'); ?></th>
                    <td>
                        <select name="<?php echo esc_attr($option_key); ?>[default-lang]">
                            <?php foreach ($selected_langs as $slug) : ?>
                                <?php
                                if ($slug === 'core' || in_array($slug, $hidden_dependencies, true)) {
                                    continue;
                                }
                                $label = (isset($languages[$slug]) && is_array($languages[$slug]))
                                    ? $languages[$slug]['title']
                                    : $slug;
                                ?>
                                <option value="<?php echo esc_attr($slug); ?>" <?php selected($options['default-lang'] ?? 'php', $slug); ?>>
                                    <?php echo esc_html($label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description"><?php esc_html_e("Default language in the code editor's drop down menu.", 'prism-highlighter'); ?></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php esc_html_e('Theme', 'prism-highlighter'); ?></th>
                    <td>
                        <select name="<?php echo esc_attr($option_key); ?>[theme]">
                            <?php foreach ($themes as $slug => $val) : ?>
                                <?php
                                if ($slug === 'meta') {
                                    continue;
                                }
                                $label = is_array($val) ? $val['title'] : $val;
                                ?>
                                <option value="<?php echo esc_attr($slug); ?>" <?php selected($options['theme'] ?? 'default', $slug); ?>>
                                    <?php echo esc_html($label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php esc_html_e('Options', 'prism-highlighter'); ?></th>
                    <td>
                        <fieldset>
                            <label>
                                <input type="checkbox" name="<?php echo esc_attr($option_key); ?>[gutter]" value="1" <?php checked(1, $options['gutter'] ?? 0); ?> />
                                <?php esc_html_e('Show line numbers', 'prism-highlighter'); ?>
                            </label><br>

                            <label>
                                <input type="checkbox" name="<?php echo esc_attr($option_key); ?>[auto-links]" value="1" <?php checked(1, $options['auto-links'] ?? 0); ?> />
                                <?php esc_html_e('Make all URL links clickable', 'prism-highlighter'); ?>
                            </label><br>

                            <label>
                                <input type="checkbox" name="<?php echo esc_attr($option_key); ?>[show-lang]" value="1" <?php checked(1, $options['show-lang'] ?? 0); ?> />
                                <?php esc_html_e('Show language title', 'prism-highlighter'); ?>
                            </label><br>

                            <label>
                                <input type="checkbox" name="<?php echo esc_attr($option_key); ?>[show-hidden-char]" value="1" <?php checked(1, $options['show-hidden-char'] ?? 0); ?> />
                                <?php esc_html_e('Show hidden characters', 'prism-highlighter'); ?>
                            </label>
                        </fieldset>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php esc_html_e('Starting Line Number', 'prism-highlighter'); ?></th>
                    <td>
                        <input type="number" class="small-text" name="<?php echo esc_attr($option_key); ?>[start-number]" value="<?php echo esc_attr((string)($options['start-number'] ?? 1)); ?>" />
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php esc_html_e('Max Height (px)', 'prism-highlighter'); ?></th>
                    <td>
                        <input type="number" class="small-text" name="<?php echo esc_attr($option_key); ?>[max-height]" value="<?php echo esc_attr((string)($options['max-height'] ?? 480)); ?>" />
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php esc_html_e('Additional CSS', 'prism-highlighter'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" id="prism-add-css-toggle" name="<?php echo esc_attr($option_key); ?>[add-css]" value="1" <?php checked(1, $options['add-css'] ?? 0); ?> />
                            <?php esc_html_e('Enable Custom CSS', 'prism-highlighter'); ?>
                        </label>

                        <div id="prism-css-container" style="<?php echo !empty($options['add-css']) ? 'display:block' : 'display:none'; ?>; margin-top: 10px;">
                            <textarea class="prism-css-editor" id="prism-css-textarea" name="<?php echo esc_attr($option_key); ?>[add-css-value]"><?php echo esc_textarea($options['add-css-value'] ?? ''); ?></textarea>
                            <br>
                            <a href="#" id="prism-css-example-btn" class="button button-secondary" style="margin-top:5px;">
                                <?php esc_html_e('Show Example', 'prism-highlighter'); ?>
                            </a>
                            <pre id="prism-css-example" class="prism-css-example" style="display:none; margin-top:10px;">pre.prism-highlighter-container { font-size: 15px; }</pre>
                        </div>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php esc_html_e('Global Class', 'prism-highlighter'); ?></th>
                    <td>
                        <input type="text" class="regular-text" name="<?php echo esc_attr($option_key); ?>[class]" value="<?php echo esc_attr($options['class'] ?? ''); ?>" />
                    </td>
                </tr>
            </table>

            <?php
            submit_button('Save Changes', 'primary', 'submit', false);
            echo ' ';
            submit_button(
                'Restore to Defaults',
                'secondary',
                'prism-reset-defaults',
                false,
                ['onclick' => "return confirm('" . esc_js(__('Are you sure you want to reset all settings to their default values?', 'prism-highlighter')) . "');"]
            );
            ?>
        </form>
    </div>
</div>
