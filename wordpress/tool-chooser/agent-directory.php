<?php
/**
 * [aicsu_agent_directory]: server-rendered, filterable list of aicsu_agent posts.
 * Uses the chooser's markup and stylesheet (chooser.css); agents.js adds search, filters, and URL prefilters.
 * Prefilter links: ?role=student&platform=copilot&tag=powerpoint,accessibility&q=text (applied in JS, so the page stays cacheable).
 */
defined('ABSPATH') || exit;

add_shortcode('aicsu_agent_directory', function () {
    if (!post_type_exists('aicsu_agent') || !function_exists('get_field')) {
        return '<p>The agent directory is unavailable. Please check the ACF configuration.</p>';
    }
    // param => [taxonomy, legend, show every term (true) or only terms in use (false)]
    $groups = [
        'role' => ['aicsu_role', 'Who Are You?', true],
        'platform' => ['aicsu_platform', 'Platform', false],
        'tag' => ['aicsu_agent_tag', 'Topic', false],
    ];
    $posts = get_posts([
        'post_type' => 'aicsu_agent',
        'post_status' => ['publish'],
        'posts_per_page' => -1,
        'orderby' => ['menu_order' => 'ASC', 'title' => 'ASC'],
        'suppress_filters' => false,
    ]);
    $records = [];
    $used = array_fill_keys(array_keys($groups), []);
    foreach ($posts as $post) {
        $terms = [];
        $names = [];
        foreach ($groups as $param => [$taxonomy]) {
            $items = get_the_terms($post, $taxonomy);
            $items = is_array($items) ? $items : [];
            usort($items, fn($a, $b) => strcasecmp($a->name, $b->name));
            $terms[$param] = wp_list_pluck($items, 'slug');
            $names[$param] = wp_list_pluck($items, 'name');
            foreach ($items as $item) $used[$param][$item->slug] = true;
        }
        // last_reviewed is stored as Ymd; read the raw value so display does not depend on the field's return format.
        // Parse it in the site time zone and format it there, so a date never shifts by a day.
        $raw = (string) get_field('last_reviewed', $post->ID, false);
        $reviewed = $raw !== '' ? date_create($raw, wp_timezone()) : false;
        $records[] = [
            'id' => $post->ID,
            'name' => get_the_title($post),
            'summary' => wp_strip_all_tags($post->post_excerpt),
            'url' => esc_url_raw((string) get_field('agent_url', $post->ID), ['https', 'http']),
            'owner' => trim((string) get_field('owner', $post->ID)),
            'reviewed' => $reviewed ? $reviewed->format('F j, Y') : '',
            'terms' => $terms,
            'names' => $names,
        ];
    }
    $base = plugin_dir_url(__FILE__);
    wp_enqueue_script('aicsu-agents', $base . 'agents.js', [], (string) filemtime(__DIR__ . '/agents.js'), true);
    $uid = wp_unique_id('aicsu-agents-');
    ob_start();
    ?>
    <link rel="stylesheet" href="<?php echo esc_url($base . 'chooser.css?ver=' . filemtime(__DIR__ . '/chooser.css')); ?>">
    <link rel="stylesheet" href="<?php echo esc_url($base . 'agents.css?ver=' . filemtime(__DIR__ . '/agents.css')); ?>">
    <div class="aicsu-chooser aicsu-agents" id="<?php echo esc_attr($uid); ?>">
        <noscript><p>All agents are listed below. Enable JavaScript to search or filter.</p></noscript>
        <div class="ac-layout">
            <details class="ac-filters ac-interactive" open hidden>
                <summary>Filter Agents</summary>
                <form class="ac-form">
                    <p class="ac-hint">Within a group, agents match any selected option. Across groups, agents must match every group you use.</p>
                    <?php foreach ($groups as $param => [$taxonomy, $legend, $every]) : ?>
                        <?php
                        $items = get_terms(['taxonomy' => $taxonomy, 'hide_empty' => false]);
                        $items = is_array($items) ? $items : [];
                        if (!$every) $items = array_values(array_filter($items, fn($item) => isset($used[$param][$item->slug])));
                        usort($items, fn($a, $b) => strcasecmp($a->name, $b->name));
                        if (!$items) continue;
                        ?>
                        <fieldset><legend><?php echo esc_html($legend); ?></legend>
                            <?php foreach ($items as $term) : ?>
                                <label><input type="checkbox" name="<?php echo esc_attr($param); ?>" value="<?php echo esc_attr($term->slug); ?>"> <span><?php echo esc_html($term->name); ?></span></label>
                            <?php endforeach; ?>
                        </fieldset>
                    <?php endforeach; ?>
                    <button type="reset" class="ac-secondary">Reset Filters</button>
                </form>
            </details>
            <section class="ac-results" aria-label="Agent results">
                <div class="ac-toolbar ac-interactive" hidden>
                    <label class="ac-search">Search Agents<input type="search" placeholder="Search by name or keyword" autocomplete="off"></label>
                </div>
                <div class="ac-results-heading"><h2>Explore Your Options</h2><p class="ac-count" role="status" aria-live="polite"><?php echo count($records); ?> agents</p></div>
                <div class="ac-grid">
                <?php foreach ($records as $record) : ?>
                    <?php
                    $search = strtolower(implode(' ', array_merge([$record['name'], $record['summary'], $record['owner']], ...array_values($record['names']))));
                    $chips = array_merge($record['names']['platform'], $record['names']['tag']);
                    ?>
                    <article class="ac-card" data-id="<?php echo (int) $record['id']; ?>"
                        data-role="<?php echo esc_attr(implode(' ', $record['terms']['role'])); ?>"
                        data-platform="<?php echo esc_attr(implode(' ', $record['terms']['platform'])); ?>"
                        data-tag="<?php echo esc_attr(implode(' ', $record['terms']['tag'])); ?>"
                        data-search="<?php echo esc_attr($search); ?>">
                        <p class="ac-eyebrow">AI Agent</p>
                        <h3><?php echo esc_html($record['name']); ?></h3>
                        <p class="ac-summary"><?php echo esc_html($record['summary']); ?></p>
                        <?php if ($record['names']['role']) : ?><p class="ac-cost">For: <?php echo esc_html(implode(', ', $record['names']['role'])); ?></p><?php endif; ?>
                        <?php if ($chips) : ?><ul class="ac-tags"><?php foreach ($chips as $chip) : ?><li><?php echo esc_html($chip); ?></li><?php endforeach; ?></ul><?php endif; ?>
                        <?php if ($record['owner']) : ?><p class="ac-meta">Owner: <?php echo esc_html($record['owner']); ?></p><?php endif; ?>
                        <?php if ($record['reviewed']) : ?><p class="ac-meta">Last reviewed: <?php echo esc_html($record['reviewed']); ?></p><?php endif; ?>
                        <div class="ac-card-actions">
                            <?php if ($record['url']) : ?><a href="<?php echo esc_url($record['url']); ?>" target="_blank" rel="noopener">Open <?php echo esc_html($record['name']); ?></a><?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
                </div>
                <div class="ac-empty" <?php echo $records ? 'hidden' : ''; ?>><h3>No Matching Agents</h3><p>Try removing a filter or changing your search.</p><button type="button" class="ac-reset ac-interactive" hidden>Reset Search and Filters</button></div>
            </section>
        </div>
    </div>
    <?php
    return ob_get_clean();
});
