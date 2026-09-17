<?php
/**
 * Plugin Name: AI @ CSU Tool Chooser
 * Description: ACF-backed tool chooser for an Elementor Shortcode widget: [aicsu_tool_chooser].
 * Version: 0.1.0
 */
defined('ABSPATH') || exit;

add_shortcode('aicsu_tool_chooser', function () {
    if (!post_type_exists('aicsu_tool') || !function_exists('get_field')) {
        return '<p>The tool directory is unavailable. Please check the ACF configuration.</p>';
    }
    $groups = [
        'aicsu_role' => 'Who Are You?',
        'aicsu_data' => 'What Data Is Involved?',
        'aicsu_task' => 'What Do You Want to Do?',
        'aicsu_complexity' => 'Technical Comfort',
    ];
    $posts = get_posts([
        'post_type' => 'aicsu_tool',
        'post_status' => ['publish'],
        // ponytail: a small directory is rendered once; add pagination for hundreds of tools.
        'posts_per_page' => -1,
        'orderby' => ['menu_order' => 'ASC', 'title' => 'ASC'],
        'suppress_filters' => false,
    ]);
    $statuses = ['approved' => 'Approved for CSU use', 'coming_soon' => 'Coming soon', 'pilot' => 'CSU evaluation pilot', 'public_only' => 'Public, non-sensitive data only', 'unsupported' => 'Not reviewed or supported by CSU', 'informational' => 'Not provided by CSU'];
    $records = [];
    foreach ($posts as $post) {
        $terms = [];
        $names = [];
        foreach ($groups as $taxonomy => $label) {
            $items = get_the_terms($post, $taxonomy);
            $items = is_array($items) ? $items : [];
            $terms[$taxonomy] = wp_list_pluck($items, 'slug');
            $names[$taxonomy] = implode(', ', wp_list_pluck($items, 'name'));
        }
        $demo = (bool) get_post_meta($post->ID, 'aicsu_demo', true);
        $status = (string) get_field('status', $post->ID);
        $records[] = [
            'id' => $post->ID, 'name' => get_the_title($post),
            'summary' => wp_strip_all_tags($post->post_excerpt),
            'cost' => (string) get_field('cost', $post->ID),
            'status' => ($demo ? 'Example: ' : '') . ($statuses[$status] ?? 'Approval not reviewed'),
            'approved' => $status === 'approved' && (bool) get_field('approved_for_sensitive', $post->ID),
            'terms' => $terms, 'names' => $names,
            'url' => esc_url_raw((string) get_field('tool_url', $post->ID), ['https', 'http']),
            'highlights' => get_field('highlights', $post->ID) ?: [], 'demo' => $demo,
        ];
    }
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
                    <p class="ac-hint">Choose any options within a group. Tools must match every selected group.</p>
                    <?php foreach ($groups as $taxonomy => $label) : ?>
                        <fieldset><legend><?php echo esc_html($label); ?></legend>
                            <?php $items = get_terms(['taxonomy' => $taxonomy, 'hide_empty' => false]); ?>
                            <?php foreach (is_array($items) ? $items : [] as $term) : ?>
                                <label><input type="checkbox" name="<?php echo esc_attr($taxonomy); ?>" value="<?php echo esc_attr($term->slug); ?>"> <span><?php echo esc_html($term->name); ?></span></label>
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
                <p class="ac-sensitive" hidden>Data filters require a matching classification for every sensitive data type selected, plus an approved status. Example records do not establish CSU approval.</p>
                <div class="ac-grid">
                <?php foreach ($records as $record) : ?>
                    <article class="ac-card" data-id="<?php echo (int) $record['id']; ?>">
                        <p class="ac-eyebrow"><?php echo $record['demo'] ? 'Sample Tool' : 'AI Tool'; ?><span class="ac-match" hidden>Best Match</span></p>
                        <h3><?php echo esc_html($record['name']); ?></h3>
                        <p class="ac-badge"><?php echo esc_html($record['status']); ?></p>
                        <p class="ac-summary"><?php echo esc_html($record['summary']); ?></p>
                        <p class="ac-cost"><?php echo esc_html($record['cost'] ?: 'Cost not provided'); ?></p>
                        <?php if ($record['highlights']) : ?><ul class="ac-tags"><?php foreach ($record['highlights'] as $highlight) : ?><li><?php echo esc_html($highlight['label'] ?? ''); ?></li><?php endforeach; ?></ul><?php endif; ?>
                        <div class="ac-card-actions">
                            <?php if ($record['url']) : ?><a href="<?php echo esc_url($record['url']); ?>">Visit <?php echo esc_html($record['name']); ?></a><?php endif; ?>
                            <button type="button" class="ac-compare ac-interactive ac-secondary" aria-pressed="false" aria-label="<?php echo esc_attr('Compare ' . $record['name']); ?>" hidden>Compare</button>
                        </div>
                    </article>
                <?php endforeach; ?>
                </div>
                <div class="ac-empty" <?php echo $records ? 'hidden' : ''; ?>><h3>No Matching Tools</h3><p>Try removing a filter or changing your search.</p><button type="button" class="ac-reset ac-interactive" hidden>Reset Search and Filters</button></div>
                <details class="ac-excluded" hidden><summary>Tools Excluded by Data Requirements</summary><p>These tools do not satisfy all selected data classifications and approval checks.</p><ul></ul></details>
            </section>
        </div>
        <dialog class="ac-dialog" aria-label="Tool comparison"><div class="ac-dialog-head"><h2>Compare Tools</h2><button type="button" class="ac-close ac-secondary">Close</button></div><p>Sample descriptions, pricing, and approvals are for testing only.</p><p class="ac-hint">On a small screen, scroll the table sideways to see every tool.</p><div class="ac-table-scroll" tabindex="0" role="region" aria-label="Comparison table"></div></dialog>
        <script type="application/json" class="ac-data"><?php echo wp_json_encode($records, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?></script>
    </div>
    <?php
    return ob_get_clean();
});
