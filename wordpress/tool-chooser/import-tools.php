<?php
// Local-only sync: wp --url=https://multisite.local/ai eval-file import-tools.php
if (!defined('WP_CLI') || !WP_CLI || home_url() !== 'https://multisite.local/ai') {
    throw new RuntimeException('This importer is restricted to the local AI subsite.');
}

try {
$directory = dirname(__DIR__, 2) . '/content/planned/tools/directory';
$taxonomies = ['aicsu_role', 'aicsu_data', 'aicsu_task', 'aicsu_complexity'];
$sensitive = ['ferpa-student-records', 'hipaa-health', 'export-controlled', 'confidential-research', 'personnel-hr', 'financial', 'sensitive-business'];
$field_keys = ['status' => 'field_6a861cf9bd6c4', 'approved_for_sensitive' => 'field_6a861d6dadeaf', 'cost' => 'field_6a861d9cadeb0', 'tool_url' => 'field_6a861dbbadeb1'];
$dry_run = getenv('AICSU_TOOL_IMPORT_DRY_RUN') === '1';

foreach (glob($directory . '/*.md') as $file) {
    if (basename($file) === 'README.md') continue;
    $markdown = file_get_contents($file);
    if (!preg_match('/\A---\R(\{.*\})\R---\R?(.*)\z/s', $markdown, $parts)) {
        throw new RuntimeException(basename($file) . ': expected JSON front matter between --- lines.');
    }
    $tool = json_decode($parts[1], true, 512, JSON_THROW_ON_ERROR);
    $id = filter_var($tool['local_post_id'] ?? null, FILTER_VALIDATE_INT);
    if (!$id || get_post_type($id) !== 'aicsu_tool') {
        throw new RuntimeException(basename($file) . ': local_post_id is not an existing Tool.');
    }
    if (!in_array($tool['status'] ?? '', ['approved', 'pilot', 'public_only'], true)) {
        throw new RuntimeException(basename($file) . ': invalid approval status.');
    }
    $terms = $tool['taxonomies'] ?? [];
    if (array_diff(array_keys($terms), $taxonomies)) {
        throw new RuntimeException(basename($file) . ': unknown taxonomy.');
    }
    $sensitive_terms = array_intersect($terms['aicsu_data'] ?? [], $sensitive);
    if ($sensitive_terms && (($tool['status'] ?? '') !== 'approved' || empty($tool['approved_for_sensitive']))) {
        throw new RuntimeException(basename($file) . ': sensitive data terms require approved status and approved_for_sensitive=true.');
    }
    foreach ($taxonomies as $taxonomy) {
        $slugs = array_values($terms[$taxonomy] ?? []);
        foreach ($slugs as $slug) {
            if (!term_exists($slug, $taxonomy)) throw new RuntimeException(basename($file) . ": missing {$taxonomy} term {$slug}.");
        }
        if ($dry_run) continue;
        $result = wp_set_object_terms($id, $slugs, $taxonomy, false);
        if (is_wp_error($result)) throw new RuntimeException(basename($file) . ': ' . $result->get_error_message());
    }
    if ($dry_run) {
        WP_CLI::log('Validated ' . basename($file));
        continue;
    }
    $result = wp_update_post([
        'ID' => $id,
        'post_title' => (string) $tool['title'],
        'post_name' => (string) $tool['slug'],
        'post_excerpt' => trim($parts[2]),
        'menu_order' => (int) ($tool['menu_order'] ?? 0),
    ], true);
    if (is_wp_error($result)) throw new RuntimeException(basename($file) . ': ' . $result->get_error_message());
    foreach ($field_keys as $name => $key) update_field($key, $tool[$name] ?? '', $id);
    update_field('field_6a861dc8adeb2', array_map(fn($label) => ['field_6a861ddeadeb3' => (string) $label], $tool['highlights'] ?? []), $id);
    empty($tool['demo']) ? delete_post_meta($id, 'aicsu_demo') : update_post_meta($id, 'aicsu_demo', '1');
    WP_CLI::log('Updated ' . get_the_title($id) . " ({$id})");
}

WP_CLI::success($dry_run ? 'Validated tool Markdown without changes.' : 'Synced tool Markdown to local WordPress. Publication states were preserved.');
} catch (Throwable $error) {
    WP_CLI::error($error->getMessage());
}
