<?php
/**
 * [aicsu_quick_match]: a compact panel with two questions (role, most sensitive data) and a short list of the tools that fit.
 *
 * Sized to sit in a home page hero. The list shows tool names and cost only; "See all matching tools" opens the full
 * tool chooser (the Help Me Choose page) pre-filtered with the same answers via ?role=&data=.
 * Level 4 (restricted) is deliberately not offered: no tool is approved for it, and the site's position is that
 * restricted data stays out of AI tools. The note under the data question says so.
 *
 * Attributes:
 *   chooser_url   page with the full chooser (found automatically if omitted)
 *   heading, intro, note   panel text
 *   limit         tools listed before "See all" (default 4)
 *   next_faculty, next_staff, next_researcher, next_student   "Link text|URL" shown as the role's next step
 * Each listed tool links to the chooser showing just that tool (?item=<slug>), carrying the answers so Show all tools restores the filtered list.
 * Answers are read from and written to ?role=<slug>&data=<level slug>, so a result can be shared.
 */
defined('ABSPATH') || exit;

function aicsu_quick_match_render($atts) {
    if (!post_type_exists('aicsu_tool') || !function_exists('get_field')) {
        return '<p>Quick Match is unavailable. Please check the ACF configuration.</p>';
    }
    $atts = shortcode_atts([
        'chooser_url' => '',
        'heading' => 'Quick Match',
        'intro' => 'Two choices. No sign-in.',
        'note' => 'Level 4 restricted data: do not use AI tools with it.',
        'limit' => '4',
        'next_faculty' => '', 'next_staff' => '', 'next_researcher' => '', 'next_student' => '',
    ], $atts, 'aicsu_quick_match');

    $records = aicsu_tool_records();
    $levels = array_slice(aicsu_tool_levels(), 0, 3); // Level 4 is intentionally excluded.
    $level_terms = [];
    foreach ($levels as $i => $slug) {
        $term = get_term_by('slug', $slug, 'aicsu_data');
        // "level-2-internal" -> "Internal": a short block label; the full term name is the tooltip.
        if ($term) $level_terms[$slug] = ['short' => ucfirst(explode('-', $slug)[2] ?? $term->name), 'level' => 'Level ' . ($i + 1), 'full' => $term->name];
    }
    $roles = get_terms(['taxonomy' => 'aicsu_role', 'hide_empty' => false]);
    $roles = is_array($roles) ? $roles : [];
    $order = ['faculty', 'staff', 'researcher', 'student'];
    usort($roles, function ($a, $b) use ($order) {
        $ia = array_search($a->slug, $order, true); $ib = array_search($b->slug, $order, true);
        return ($ia === false ? 99 : $ia) <=> ($ib === false ? 99 : $ib) ?: strcasecmp($a->name, $b->name);
    });

    $next = [];
    foreach ($roles as $term) {
        [$label, $url] = array_pad(array_map('trim', explode('|', (string) ($atts['next_' . $term->slug] ?? ''), 2)), 2, '');
        if ($label !== '' && $url !== '') $next[$term->slug] = ['label' => $label, 'url' => esc_url_raw($url, ['https', 'http']), 'role' => $term->name];
    }

    $chooser = $atts['chooser_url'];
    if ($chooser === '') {
        $found = get_posts([
            'post_type' => 'page', 'post_status' => 'publish', 'posts_per_page' => 1, 'orderby' => 'ID', 'order' => 'ASC',
            'meta_key' => '_elementor_data', 'meta_value' => '[aicsu_tool_chooser]', 'meta_compare' => 'LIKE',
        ]);
        $chooser = $found ? get_permalink($found[0]) : '';
    }
    $data = array_map(fn($r) => [
        'id' => $r['id'], 'approved' => $r['approved'],
        'terms' => ['aicsu_role' => $r['terms']['aicsu_role'], 'aicsu_data' => $r['terms']['aicsu_data']],
    ], $records);

    $base = plugin_dir_url(__FILE__);
    wp_enqueue_script('aicsu-quick-match', $base . 'quick-match.js', [], (string) filemtime(__DIR__ . '/quick-match.js'), true);
    $uid = wp_unique_id('aicsu-qm-');
    ob_start();
    ?>
    <link rel="stylesheet" href="<?php echo esc_url($base . 'quick-match.css?ver=' . filemtime(__DIR__ . '/quick-match.css')); ?>">
    <div class="aicsu-quickmatch" id="<?php echo esc_attr($uid); ?>" data-chooser="<?php echo esc_url($chooser); ?>" data-limit="<?php echo (int) $atts['limit']; ?>"
        data-next="<?php echo esc_attr(wp_json_encode($next)); ?>">
        <h2 class="qm-title"><?php echo esc_html($atts['heading']); ?></h2>
        <?php if ($atts['intro'] !== '') : ?><p class="qm-intro"><?php echo esc_html($atts['intro']); ?></p><?php endif; ?>
        <noscript><p class="qm-intro">All approved tools are listed below. Enable JavaScript to narrow them by role and data.</p></noscript>
        <form class="qm-form" hidden>
            <fieldset class="qm-roles"><legend>1. I am a</legend>
                <div class="qm-blocks">
                <?php foreach ($roles as $term) : ?>
                    <label class="qm-block"><input type="radio" name="role" value="<?php echo esc_attr($term->slug); ?>"><span><span class="qm-name"><?php echo esc_html($term->name); ?></span></span></label>
                <?php endforeach; ?>
                </div>
            </fieldset>
            <fieldset class="qm-levels"><legend>2. My data is</legend>
                <div class="qm-blocks">
                <?php foreach ($level_terms as $slug => $level) : ?>
                    <label class="qm-block" title="<?php echo esc_attr($level['full']); ?>"><input type="radio" name="data" value="<?php echo esc_attr($slug); ?>"><span><span class="qm-name"><?php echo esc_html($level['short']); ?></span><span class="qm-sub"><?php echo esc_html($level['level']); ?></span></span></label>
                <?php endforeach; ?>
                </div>
                <?php if ($atts['note'] !== '') : ?><p class="qm-note"><?php echo esc_html($atts['note']); ?></p><?php endif; ?>
            </fieldset>
        </form>
        <div class="qm-results">
            <p class="qm-prompt" hidden>Choose a role and a data level to see the tools that fit.</p>
            <p class="qm-heading" hidden>Tools that fit</p>
            <p class="qm-status" role="status" aria-live="polite"></p>
            <ul class="qm-list">
            <?php foreach ($records as $record) : ?>
                <li data-id="<?php echo (int) $record['id']; ?>"><span class="qm-tool"><?php if ($chooser) : ?><a class="qm-tool-link" href="<?php echo esc_url(add_query_arg('item', $record['slug'], $chooser)); ?>"><?php echo esc_html($record['name']); ?></a><?php else : echo esc_html($record['name']); endif; ?></span><span class="qm-cost"><?php echo esc_html($record['cost'] ?: 'Cost not provided'); ?></span></li>
            <?php endforeach; ?>
            </ul>
            <p class="qm-empty" hidden>No approved tool fits that role and data level. Try a lower data level, or ask for help choosing.</p>
        </div>
        <p class="qm-next" hidden></p>
        <?php if ($chooser) : ?>
            <p class="qm-links">
                <a class="qm-see-all" href="<?php echo esc_url($chooser); ?>" hidden>See all matching tools</a>
                <a class="qm-help" href="<?php echo esc_url($chooser); ?>">Not sure? Help me choose</a>
            </p>
        <?php endif; ?>
        <script type="application/json" class="qm-data"><?php echo wp_json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?></script>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('aicsu_quick_match', 'aicsu_quick_match_render');
