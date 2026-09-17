<?php
// Read-only integration check: wp --url=https://multisite.local/ai eval-file <this file>.
if (!defined('WP_CLI') || !WP_CLI || home_url() !== 'https://multisite.local/ai') throw new RuntimeException('Local AI site only.');
$check = function ($ok, $message) { if (!$ok) throw new RuntimeException($message); };
$check(shortcode_exists('aicsu_tool_chooser'), 'Chooser plugin is not active.');
foreach (['aicsu_role', 'aicsu_data', 'aicsu_task', 'aicsu_complexity'] as $taxonomy) {
    $check(taxonomy_exists($taxonomy) && in_array('aicsu_tool', get_taxonomy($taxonomy)->object_type, true), "{$taxonomy} is not attached.");
}
foreach (['aicsu_output', 'aicsu_source', 'aicsu_collab', 'aicsu_frequency'] as $taxonomy) {
    $check(!taxonomy_exists($taxonomy), "{$taxonomy} should be removed.");
}
$group = acf_get_field_group('group_6a861cf88c190');
$check($group['location'][0][0]['value'] === 'aicsu_tool' && $group['show_in_rest'], 'Field group configuration is wrong.');
$check(count(get_field('highlights', 40)) === 3, 'Repeater values are missing.');
wp_set_current_user(0);
$GLOBALS['wp_query'] = new WP_Query();
$html = do_shortcode('[aicsu_tool_chooser]');
$check(substr_count($html, '<article class="ac-card"') === 12, 'Published page rendering must show all 12 tool records.');
$check(strpos($html, 'Who Are You?') < strpos($html, 'What Data Is Involved?') && strpos($html, 'What Data Is Involved?') < strpos($html, 'What Do You Want to Do?') && strpos($html, 'What Do You Want to Do?') < strpos($html, 'Technical Comfort'), 'Filter order is wrong.');
$check(!str_contains($html, 'More Filters'), 'Removed filters are still rendered.');
$check(str_contains($html, '<noscript>'), 'No-JavaScript guidance is missing.');
$GLOBALS['wp_query']->is_preview = true;
$GLOBALS['wp_query']->queried_object = get_post(52);
$GLOBALS['wp_query']->queried_object_id = 52;
$html = do_shortcode('[aicsu_tool_chooser]');
$check(substr_count($html, '<article class="ac-card"') === 12, 'Draft preview rendering must show all 12 tool records.');
wp_set_current_user(get_post_field('post_author', 52));
$html = do_shortcode('[aicsu_tool_chooser]');
$check(substr_count($html, '<article class="ac-card"') === 12, 'Authorized preview must show 12 samples.');
$check(str_contains($html, 'Enterprise Chat'), 'ACF highlights were not rendered.');
$check(str_contains($html, 'https://copilot.microsoft.com/'), 'Updated ACF URL was not rendered.');
$check(in_array(get_post_status(52), ['draft', 'publish'], true), 'Chooser page has an unexpected status.');
WP_CLI::success('ACF configuration, live fields, published/draft rendering, and server-rendered fallback passed.');
