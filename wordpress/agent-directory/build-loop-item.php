<?php
// Local-only: creates or rebuilds the "AI Agent Card" Elementor Pro Loop Item.
// wp --url=https://multisite.local/ai eval-file wordpress/agent-directory/build-loop-item.php
// Production gets the card by exporting this template from Local and importing it, or by the production MCP.
if (!defined('WP_CLI') || !WP_CLI || home_url() !== 'https://multisite.local/ai') {
    throw new RuntimeException('This script is restricted to the local AI subsite.');
}

function aicsu_tag(string $name, array $settings = []): string {
    return sprintf('[elementor-tag id="%s" name="%s" settings="%s"]', substr(md5($name . json_encode($settings)), 0, 7), $name, urlencode(wp_json_encode($settings ?: new stdClass())));
}

function aicsu_card_elements(): array {
    // Card styling (green top rule, chips, type sizes) lives in wordpress/tool-chooser/agent-filter.html and keys off these classes.
    $widget = fn(string $id, string $type, string $class, array $settings) => ['id' => $id, 'elType' => 'widget', 'widgetType' => $type, 'settings' => $settings + ['_css_classes' => $class], 'elements' => []];
    $terms = fn(string $taxonomy, string $label) => ['_id' => substr(md5($taxonomy), 0, 7), 'type' => 'terms', 'taxonomy' => $taxonomy, 'text_prefix' => $label, 'show_icon' => 'none', 'link' => ''];
    $px = fn(int $n) => ['unit' => 'px', 'top' => (string) $n, 'right' => (string) $n, 'bottom' => (string) $n, 'left' => (string) $n, 'isLinked' => true];
    return [[
        'id' => 'a1c5d00', 'elType' => 'container', 'isInner' => false,
        'settings' => [
            'css_classes' => 'aicsu-agent-card',
            'content_width' => 'full', 'flex_direction' => 'column', 'flex_gap' => ['unit' => 'px', 'size' => 12, 'column' => '12', 'row' => '12', 'isLinked' => true],
            'padding' => $px(24), 'padding_mobile' => $px(20),
        ],
        'elements' => [
            $widget('a1c5d07', 'text-editor', 'ac-eyebrow', ['editor' => 'AI Agent']),
            $widget('a1c5d01', 'heading', 'ac-title', ['header_size' => 'h3', 'title' => 'Agent name', '__dynamic__' => ['title' => aicsu_tag('post-title')]]),
            $widget('a1c5d02', 'text-editor', 'ac-summary', ['editor' => 'Agent description', '__dynamic__' => ['editor' => aicsu_tag('post-excerpt')]]),
            $widget('a1c5d03', 'post-info', 'ac-audience', ['icon_list' => [$terms('aicsu_role', 'For: ')], 'view' => 'stacked']),
            $widget('a1c5d08', 'post-info', 'ac-chips', ['icon_list' => [$terms('aicsu_platform', ''), $terms('aicsu_agent_tag', '')], 'view' => 'stacked']),
            $widget('a1c5d04', 'text-editor', 'ac-meta', ['editor' => 'Owner', '__dynamic__' => ['editor' => aicsu_tag('acf-text', ['key' => 'field_6ae4e654663c0:owner', 'before' => 'Owner: '])]]),
            $widget('a1c5d05', 'text-editor', 'ac-meta', ['editor' => 'Last reviewed', '__dynamic__' => ['editor' => aicsu_tag('acf-text', ['key' => 'field_6ae4e654663ca:last_reviewed', 'before' => 'Last reviewed: '])]]),
            $widget('a1c5d06', 'button', 'ac-action', ['text' => 'Open agent', 'link' => ['url' => '', 'is_external' => 'on'], '__dynamic__' => ['text' => aicsu_tag('post-title', ['before' => 'Open ']), 'link' => aicsu_tag('acf-url', ['key' => 'field_6ae4e654663b7:agent_url'])]]),
        ],
    ]];
}

$existing = get_posts(['post_type' => 'elementor_library', 'title' => 'AI Agent Card', 'post_status' => 'any', 'numberposts' => 1]);
$document = $existing
    ? \Elementor\Plugin::$instance->documents->get($existing[0]->ID)
    : \Elementor\Plugin::$instance->documents->create('loop-item', ['post_title' => 'AI Agent Card', 'post_status' => 'publish']);
$document->save(['elements' => aicsu_card_elements(), 'settings' => ['post_status' => 'publish']]);
WP_CLI::success('Loop Item ID ' . $document->get_main_id());
