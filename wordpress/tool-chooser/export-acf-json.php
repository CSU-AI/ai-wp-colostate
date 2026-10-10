<?php
// Local-only schema export: wp --url=https://multisite.local/ai eval-file export-acf-json.php
// Writes the Tool post type, chooser taxonomies, and Tool Details field group to acf-json/.
if (!defined('WP_CLI') || !WP_CLI || home_url() !== 'https://multisite.local/ai') {
    throw new RuntimeException('This exporter is restricted to the local AI subsite.');
}

$target = __DIR__ . '/acf-json';
$taxonomies = ['aicsu_role', 'aicsu_data', 'aicsu_task', 'aicsu_complexity'];
$items = [];

foreach (acf_get_internal_post_type_posts('acf-post-type') as $post) {
    if ($post['post_type'] === 'aicsu_tool') $items[] = [$post, 'acf-post-type'];
}
foreach (acf_get_internal_post_type_posts('acf-taxonomy') as $post) {
    if (in_array($post['taxonomy'], $taxonomies, true)) $items[] = [$post, 'acf-taxonomy'];
}
foreach (acf_get_field_groups(['post_type' => 'aicsu_tool']) as $group) {
    if (!$group['ID']) continue; // Already loaded from JSON, not the database.
    $group['fields'] = acf_get_fields($group);
    $items[] = [$group, 'acf-field-group'];
}
if (count($items) !== 2 + count($taxonomies)) {
    throw new RuntimeException('Expected 1 post type, ' . count($taxonomies) . ' taxonomies, and 1 field group; found ' . count($items) . '.');
}

wp_mkdir_p($target);
$written = [];
foreach ($items as [$post, $type]) {
    // Matches ACF_Local_JSON::save_file so ACF reports no pending sync after export.
    $post['modified'] = get_post_modified_time('U', true, $post['ID']);
    $json = acf_json_encode(acf_prepare_internal_post_type_for_export($post, $type)) . PHP_EOL;
    file_put_contents($target . '/' . $post['key'] . '.json', $json);
    $written[] = $post['key'] . '.json';
}
foreach (glob($target . '/*.json') as $file) {
    if (!in_array(basename($file), $written, true)) WP_CLI::warning('Stale file not written by this export: ' . basename($file));
}
WP_CLI::success('Wrote ' . count($written) . ' ACF JSON files to ' . $target);
