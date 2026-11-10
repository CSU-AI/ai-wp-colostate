<?php
/**
 * Shared tool data and card markup, used by [aicsu_tool_chooser] and [aicsu_quick_match].
 */
defined('ABSPATH') || exit;

/** Taxonomy => label for the tool filter groups. */
function aicsu_tool_groups(): array {
    return [
        'aicsu_role' => 'Who Are You?',
        'aicsu_data' => 'What Data Is Involved?',
        'aicsu_task' => 'What Do You Want to Do?',
        'aicsu_complexity' => 'Technical Comfort',
    ];
}

/** Data classification term slugs, lowest to highest. */
function aicsu_tool_levels(): array {
    return ['level-1-public', 'level-2-internal', 'level-3-confidential', 'level-4-restricted'];
}

/** One record per published tool, in directory order. */
function aicsu_tool_records(): array {
    $groups = aicsu_tool_groups();
    $levels = aicsu_tool_levels();
    $posts = get_posts([
        'post_type' => 'aicsu_tool',
        'post_status' => ['publish'],
        // ponytail: a small directory is rendered once; add pagination for hundreds of tools.
        'posts_per_page' => -1,
        'orderby' => ['menu_order' => 'ASC', 'title' => 'ASC'],
        'suppress_filters' => false,
    ]);
    $statuses = ['coming_soon' => 'Coming soon', 'pilot' => 'CSU evaluation pilot', 'public_only' => 'Public information only', 'unsupported' => 'Not reviewed or supported by CSU', 'informational' => 'Not provided by CSU'];
    $records = [];
    foreach ($posts as $post) {
        $terms = [];
        $names = [];
        foreach ($groups as $taxonomy => $label) {
            $items = get_the_terms($post, $taxonomy);
            $items = is_array($items) ? $items : [];
            if ($taxonomy === 'aicsu_data') $items = array_values(array_filter($items, fn($item) => in_array($item->slug, $levels, true)));
            $terms[$taxonomy] = wp_list_pluck($items, 'slug');
            $names[$taxonomy] = implode(', ', wp_list_pluck($items, 'name'));
        }
        $demo = (bool) get_post_meta($post->ID, 'aicsu_demo', true);
        $status = (string) get_field('status', $post->ID);
        $ceiling = max([0, ...array_map(fn($slug) => array_search($slug, $levels, true), $terms['aicsu_data'])]) + 1;
        $status_label = $status === 'approved' ? "Approved for CSU use • up to Level {$ceiling}" : ($statuses[$status] ?? 'Approval not reviewed');
        $records[] = [
            'id' => $post->ID, 'slug' => $post->post_name, 'name' => get_the_title($post),
            'summary' => wp_strip_all_tags($post->post_excerpt),
            'cost' => (string) get_field('cost', $post->ID),
            'status' => ($demo ? 'Example: ' : '') . $status_label,
            'approved' => $status === 'approved' && (bool) get_field('approved_for_sensitive', $post->ID),
            'terms' => $terms, 'names' => $names,
            'url' => esc_url_raw((string) get_field('tool_url', $post->ID), ['https', 'http']),
            'highlights' => get_field('highlights', $post->ID) ?: [], 'demo' => $demo,
        ];
    }
    return $records;
}

/** Echo one tool card for the chooser grid. */
function aicsu_render_tool_card(array $record): void {
    ?>
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
    <?php
}
