<?php
/**
 * Plugin Name: AI @ CSU Tool Chooser
 * Description: ACF-backed tool chooser and agent directory for Elementor Shortcode widgets: [aicsu_tool_chooser] and [aicsu_agent_directory], and the home page [aicsu_quick_match] filter and [aicsu_home_hero].
 * Version: 0.4.0
 */
defined('ABSPATH') || exit;

require_once __DIR__ . '/tool-records.php';
require_once __DIR__ . '/agent-directory.php';
require_once __DIR__ . '/quick-match.php';
require_once __DIR__ . '/home-hero.php';

// Tool and agent post types, taxonomies, and fields ship as ACF Local JSON. Regenerate with export-acf-json.php.
add_filter('acf/settings/load_json', function ($paths) {
    $paths[] = __DIR__ . '/acf-json';
    return $paths;
});
// ACF's default save path is the shared network theme. Never write this plugin's schema there.
add_filter('acf/json/save_paths', function ($paths, $post) {
    return is_file(__DIR__ . '/acf-json/' . ($post['key'] ?? '') . '.json') ? [__DIR__ . '/acf-json'] : $paths;
}, 10, 2);

add_shortcode('aicsu_tool_chooser', function () {
    if (!post_type_exists('aicsu_tool') || !function_exists('get_field')) {
        return '<p>The tool directory is unavailable. Please check the ACF configuration.</p>';
    }
    $groups = aicsu_tool_groups();
    $levels = aicsu_tool_levels();
    $records = aicsu_tool_records();
    $base = plugin_dir_url(__FILE__);
    wp_enqueue_script('aicsu-chooser', $base . 'chooser.js', [], (string) filemtime(__DIR__ . '/chooser.js'), true);
    $uid = wp_unique_id('aicsu-chooser-');
    ob_start();
    ?>
    <link rel="stylesheet" href="<?php echo esc_url($base . 'chooser.css?ver=' . filemtime(__DIR__ . '/chooser.css')); ?>">
    <div class="aicsu-chooser" id="<?php echo esc_attr($uid); ?>">
        <noscript><p>All tools are listed below. Enable JavaScript to search, filter, sort, or compare.</p></noscript>
        <div class="ac-layout">
            <details class="ac-filters ac-interactive" open hidden>
                <summary>Filter Tools</summary>
                <form class="ac-form">
                    <p class="ac-hint">For data, the highest level selected applies. In other groups, tools match any selected option within the group.</p>
                    <?php foreach ($groups as $taxonomy => $label) : ?>
                        <fieldset><legend><?php echo esc_html($label); ?></legend>
                            <?php $items = get_terms(['taxonomy' => $taxonomy, 'hide_empty' => false]); ?>
                            <?php if ($taxonomy === 'aicsu_data' && is_array($items)) {
                                $items = array_values(array_filter($items, fn($item) => in_array($item->slug, $levels, true)));
                                usort($items, fn($a, $b) => array_search($a->slug, $levels, true) <=> array_search($b->slug, $levels, true));
                            } ?>
                            <?php foreach (is_array($items) ? $items : [] as $term) : ?>
                                <label><input type="checkbox" name="<?php echo esc_attr($taxonomy); ?>" value="<?php echo esc_attr($term->slug); ?>"> <span><?php echo esc_html($taxonomy === 'aicsu_data' ? 'Level ' . (array_search($term->slug, $levels, true) + 1) . ': ' . $term->name : $term->name); ?></span></label>
                            <?php endforeach; ?>
                        </fieldset>
                    <?php endforeach; ?>
                    <button type="reset" class="ac-secondary">Reset Filters</button>
                </form>
            </details>
            <section class="ac-results" aria-label="Tool results">
                <div class="ac-tray" hidden><p class="ac-selected-count" role="status" aria-live="polite"></p><div><button type="button" class="ac-clear ac-secondary">Clear Selection</button><button type="button" class="ac-open">Compare Tools</button></div></div>
                <div class="ac-toolbar ac-interactive" hidden>
                    <label class="ac-search">Search Tools<input type="search" placeholder="Search by name or keyword" autocomplete="off"></label>
                    <label>Sort By<select class="ac-sort"><option value="recommended">Directory Order</option><option value="match">Best Match</option><option value="az">Name: A to Z</option><option value="za">Name: Z to A</option></select></label>
                </div>
                <div class="ac-results-heading"><h2>Explore Your Options</h2><p class="ac-count" role="status" aria-live="polite"><?php echo count($records); ?> tools</p></div>
                <p class="ac-interactive ac-hint" hidden>Select up to three tools to compare side by side.</p>
                <p class="ac-hint">Tool approval does not grant access to CSU data. For Level 3, follow applicable data-steward and access requirements.</p>
                <p class="ac-sensitive" hidden>Level 3 and 4 results require matching tool approval.</p>
                <div class="ac-grid">
                <?php foreach ($records as $record) : ?>
                    <?php aicsu_render_tool_card($record); ?>
                <?php endforeach; ?>
                </div>
                <div class="ac-empty" <?php echo $records ? 'hidden' : ''; ?>><h3>No Matching Tools</h3><p>Try removing a filter or changing your search.</p><button type="button" class="ac-reset ac-interactive" hidden>Reset Search and Filters</button></div>
                <details class="ac-excluded" hidden><summary>Tools Excluded by Data Requirements</summary><p>These tools do not satisfy all selected data classifications and approval checks.</p><ul></ul></details>
            </section>
        </div>
        <dialog class="ac-dialog" aria-label="Tool comparison"><div class="ac-dialog-head"><h2>Compare Tools</h2><button type="button" class="ac-close ac-secondary">Close</button></div><p class="ac-hint">On a small screen, scroll the table sideways to see every tool.</p><div class="ac-table-scroll" tabindex="0" role="region" aria-label="Comparison table"></div></dialog>
        <script type="application/json" class="ac-data"><?php echo wp_json_encode($records, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?></script>
    </div>
    <?php
    return ob_get_clean();
});
