<?php
// One-time local seeding helper. Run with `wp eval-file`; never install in the plugin.
if (!defined('WP_CLI') || !WP_CLI || home_url() !== 'https://multisite.local/ai') {
    throw new RuntimeException('This seed helper is restricted to the local AI subsite.');
}
$fields = ['status'=>'field_6a861cf9bd6c4', 'approved_for_sensitive'=>'field_6a861d6dadeaf', 'cost'=>'field_6a861d9cadeb0', 'tool_url'=>'field_6a861dbbadeb1'];
foreach (json_decode(file_get_contents(__DIR__ . '/local-samples.json'), true, 512, JSON_THROW_ON_ERROR) as $sample) {
    $id = $sample['post_id'];
    if (get_post_type($id) !== 'aicsu_tool' || get_post_status($id) !== 'draft' || !get_post_meta($id, 'aicsu_demo', true) || get_the_title($id) !== $sample['title']) {
        throw new RuntimeException('Sample guard failed for ' . $id);
    }
    foreach ($fields as $name => $key) update_field($key, $sample['meta'][$name], $id);
    update_field('field_6a861dc8adeb2', array_map(fn($label) => ['field_6a861ddeadeb3' => $label], $sample['highlights']), $id);
}
WP_CLI::success('Completed ACF field references and highlights for 12 draft samples.');
