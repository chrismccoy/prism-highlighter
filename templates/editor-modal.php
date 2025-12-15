<?php
/**
 * Template: TinyMCE Editor Modal
 *
 * Renders the "Insert Code" modal dialog.
 */

defined('ABSPATH') || exit;

$active_langs        = $options['lang-used'] ?? ['php'];
$hidden_dependencies = ['clike', 'markup', 'markup-templating'];
$lang_options_html   = '';

// Generate options list for the dropdown
foreach ($active_langs as $lang) {
    if (
        $lang === 'core' ||
        in_array($lang, $hidden_dependencies, true) ||
        !isset($languageData['languages'][$lang])
    ) {
        continue;
    }

    $label = is_array($languageData['languages'][$lang])
        ? $languageData['languages'][$lang]['title']
        : $languageData['languages'][$lang];

    $lang_options_html .= sprintf(
        '<option value="%s">%s</option>',
        esc_attr($lang),
        esc_html($label)
    );
}

?>
<div class="prism-overlay" id="prism-editor-overlay" style="display:none;"></div>

<div class="prism-editor-wrap" id="prism-editor-wrap" style="display:none;">
    <div class="prism-editor-title">
        <?php esc_html_e('Prism Syntax Highlighter', 'prism-highlighter'); ?>
        <button type="button" class="prism-editor-closebtn"></button>
    </div>

    <div class="prism-editor-body">
        <div class="prism-inline-options">
            <label>
                <?php esc_html_e('Language:', 'prism-highlighter'); ?>
                <select id="prism-language">
                    <?php echo $lang_options_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                </select>
            </label>

            <label>
                <?php esc_html_e('Highlight Lines:', 'prism-highlighter'); ?>
                <input type="text" id="prism-highlight-lines" placeholder="e.g. 1-3, 5" class="small-text">
            </label>
        </div>

        <textarea id="prism-editor-code" class="prism-editor-code" placeholder="<?php esc_attr_e('Paste code here...', 'prism-highlighter'); ?>"></textarea>

        <div id="prism-toggle-options">
            <span class="dashicons dashicons-arrow-down-alt2"></span>
            <?php esc_html_e('More Options', 'prism-highlighter'); ?>
        </div>

        <div id="prism-options-container">
            <table>
                <tr>
                    <td><?php esc_html_e('Override Line Numbers:', 'prism-highlighter'); ?></td>
                    <td><label><input type="checkbox" id="prism-show-lines"> <?php esc_html_e('Show', 'prism-highlighter'); ?></label></td>
                </tr>
                <tr>
                    <td><?php esc_html_e('Start Number:', 'prism-highlighter'); ?></td>
                    <td><input type="text" id="prism-start-line" placeholder="1" class="small-text"></td>
                </tr>
                <tr>
                    <td><?php esc_html_e('Add Class:', 'prism-highlighter'); ?></td>
                    <td><input type="text" id="prism-class-name"></td>
                </tr>
            </table>
        </div>
    </div>

    <div class="prism-editor-submitbox">
        <button type="button" class="button" id="prism-cancel">
            <?php esc_html_e('Cancel', 'prism-highlighter'); ?>
        </button>
        <button type="button" class="button button-primary" id="prism-submit">
            <?php esc_html_e('Insert Code', 'prism-highlighter'); ?>
        </button>
    </div>
</div>
