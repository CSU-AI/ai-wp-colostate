<?php
// Local-only: rebuilds the test directory page layout (page 84) and places agent-filter.html in its HTML widget.
// wp --url=https://multisite.local/ai --user=cdedward eval-file wordpress/agent-directory/build-directory-page.php
// Production: build the same layout in the editor; a super admin pastes agent-filter.html by hand
// (the MCP account lacks unfiltered_html). Set AICSU_PAGE_ID to target another Local page.
if (!defined('WP_CLI') || !WP_CLI || home_url() !== 'https://multisite.local/ai') {
    throw new RuntimeException('This script is restricted to the local AI subsite.');
}
$page_id = (int) (getenv('AICSU_PAGE_ID') ?: 84);
$loop_item = get_posts(['post_type' => 'elementor_library', 'title' => 'AI Agent Card', 'post_status' => 'publish', 'numberposts' => 1]);
if (!$loop_item) throw new RuntimeException('Run build-loop-item.php first.');
$html = file_get_contents(dirname(__DIR__) . '/tool-chooser/agent-filter.html');

$size = fn(string $unit, $n) => ['unit' => $unit, 'size' => $n];
$gap = fn(int $n) => ['unit' => 'px', 'size' => $n, 'column' => (string) $n, 'row' => (string) $n, 'isLinked' => true];
$container = fn(string $id, array $settings, array $children) => ['id' => $id, 'elType' => 'container', 'isInner' => false, 'settings' => ['content_width' => 'full'] + $settings, 'elements' => $children];
$widget = fn(string $id, string $type, array $settings) => ['id' => $id, 'elType' => 'widget', 'widgetType' => $type, 'settings' => $settings, 'elements' => []];

$elements = [
    $container('d1a0001', ['content_width' => 'boxed', 'flex_direction' => 'column', 'flex_gap' => $gap(24)], [
        $widget('d1a0002', 'heading', ['title' => 'AI Agents', 'header_size' => 'h1']),
        $widget('d1a0003', 'text-editor', ['editor' => '<p>Browse agents built for CSU. Filter by who the agent is for, the platform it runs on, and what it helps with.</p>']),
        $container('d1a0004', ['css_classes' => 'aicsu-agent-layout', 'flex_direction' => 'row', 'flex_direction_tablet' => 'row', 'flex_direction_mobile' => 'column', 'flex_gap' => $gap(32), 'flex_align_items' => 'flex-start'], [
            $container('d1a0005', ['css_classes' => 'aicsu-agent-sidebar', 'width' => $size('px', 270), 'width_tablet' => $size('px', 230), 'width_mobile' => $size('%', 100), '_flex_size' => 'none'], [
                $widget('d1a0006', 'html', ['html' => $html, '_css_classes' => 'aicsu-agent-filter-widget']),
            ]),
            $container('d1a0007', ['css_classes' => 'aicsu-agent-main', '_flex_size' => 'grow', 'width_mobile' => $size('%', 100), 'flex_direction' => 'column', 'flex_gap' => $gap(16)], [
                $widget('d1a0008', 'heading', ['title' => 'Explore Your Options', 'header_size' => 'h2']),
                $container('d1a0009', ['css_classes' => 'aicsu-agent-results', 'flex_direction' => 'column'], [
                    $widget('d1a0010', 'loop-grid', [
                        '_skin' => 'post', 'template_id' => $loop_item[0]->ID,
                        'columns' => 2, 'columns_tablet' => 1, 'columns_mobile' => 1,
                        'posts_per_page' => -1, 'post_query_post_type' => 'aicsu_agent',
                        'post_query_orderby' => 'title', 'post_query_order' => 'asc',
                    ]),
                ]),
            ]),
        ]),
    ]),
];
$doc = \Elementor\Plugin::$instance->documents->get($page_id);
$doc->save(['elements' => $elements]);
WP_CLI::success("Directory page {$page_id} rebuilt");
