<?php
// Read-only local export: wp --url=https://multisite.local/ai eval-file export-tools.php
if (!defined('WP_CLI') || !WP_CLI || home_url() !== 'https://multisite.local/ai') {
    throw new RuntimeException('This exporter is restricted to the local AI subsite.');
}

$taxonomies = ['aicsu_role', 'aicsu_data', 'aicsu_task', 'aicsu_complexity'];
$tools = [];
foreach (get_posts(['post_type' => 'aicsu_tool', 'post_status' => 'any', 'posts_per_page' => -1, 'orderby' => ['menu_order' => 'ASC', 'title' => 'ASC']]) as $post) {
    $terms = [];
    foreach ($taxonomies as $taxonomy) {
        $items = get_the_terms($post, $taxonomy);
        $terms[$taxonomy] = is_array($items) ? wp_list_pluck($items, 'slug') : [];
    }
    $tools[] = [
        'title' => get_the_title($post),
        'slug' => $post->post_name,
        'local_post_id' => $post->ID,
        'production_post_id' => null,
        'wordpress_status' => $post->post_status,
        'menu_order' => (int) $post->menu_order,
        'status' => (string) get_field('status', $post->ID),
        'approved_for_sensitive' => (bool) get_field('approved_for_sensitive', $post->ID),
        'cost' => (string) get_field('cost', $post->ID),
        'tool_url' => (string) get_field('tool_url', $post->ID),
        'highlights' => array_values(array_filter(array_map(fn($row) => (string) ($row['label'] ?? ''), get_field('highlights', $post->ID) ?: []))),
        'taxonomies' => $terms,
        'demo' => (bool) get_post_meta($post->ID, 'aicsu_demo', true),
        'summary' => wp_strip_all_tags($post->post_excerpt),
    ];
}

echo wp_json_encode($tools, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
