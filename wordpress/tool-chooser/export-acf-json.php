<?php
// Local-only schema export: wp --url=https://multisite.local/ai eval-file export-acf-json.php
// Writes the Tool and Agent post types, their taxonomies, and their field groups to acf-json/.
if (!defined('WP_CLI') || !WP_CLI || home_url() !== 'https://multisite.local/ai') {
    throw new RuntimeException('This exporter is restricted to the local AI subsite.');
}

$target = __DIR__ . '/acf-json';
$post_types = ['aicsu_tool', 'aicsu_agent'];
$taxonomies = ['aicsu_role', 'aicsu_data', 'aicsu_task', 'aicsu_complexity', 'aicsu_platform', 'aicsu_agent_tag'];
$items = [];

foreach (acf_get_internal_post_type_posts('acf-post-type') as $post) {
    if (in_array($post['post_type'], $post_types, true)) $items[] = [$post, 'acf-post-type'];
}
foreach (acf_get_internal_post_type_posts('acf-taxonomy') as $post) {
    if (in_array($post['taxonomy'], $taxonomies, true)) $items[] = [$post, 'acf-taxonomy'];
}
foreach ($post_types as $post_type) {
    foreach (acf_get_field_groups(['post_type' => $post_type]) as $group) {
        if (!$group['ID']) continue; // Already loaded from JSON, not the database.
        $group['fields'] = acf_get_fields($group);
        $items[] = [$group, 'acf-field-group'];
    }
}
$expected = count($post_types) + count($taxonomies) + count($post_types);
if (count($items) !== $expected) {
    throw new RuntimeException("Expected {$expected} items (post types, taxonomies, one field group per post type); found " . count($items) . '.');
}

wp_mkdir_p($target);
$written = [];
foreach ($items as [$post, $type]) {
    // Matches ACF_Local_JSON::save_file so ACF reports no pending sync after export.
    $post['modified'] = get_post_modified_time('U', true, $post['ID']);
    unset($post['local_file']); // Absolute Local path; never commit it.
    $json = acf_json_encode(acf_prepare_internal_post_type_for_export($post, $type)) . PHP_EOL;
    file_put_contents($target . '/' . $post['key'] . '.json', $json);
    $written[] = $post['key'] . '.json';
}
foreach (glob($target . '/*.json') as $file) {
    if (!in_array(basename($file), $written, true)) WP_CLI::warning('Stale file not written by this export: ' . basename($file));
}
WP_CLI::success('Wrote ' . count($written) . ' ACF JSON files to ' . $target);
