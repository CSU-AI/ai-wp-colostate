<?php
/**
 * [aicsu_home_hero]: the home page hero. Headline, intro, two buttons, and the Quick Match panel side by side.
 *
 * Plugin-owned on purpose: the CSU theme overrides Elementor text and button colors on dark backgrounds,
 * so a hero built from Elementor widgets needs page CSS with !important. Drop this shortcode into a full-width
 * container with no styling and it supplies its own background.
 *
 * Attributes: eyebrow, heading (wrap words in *asterisks* to highlight them), lede,
 *   primary_label, primary_url, secondary_label, secondary_url (a button is omitted if its label or URL is empty),
 *   and the Quick Match settings: chooser_url, limit, qm_heading, qm_intro, qm_note, next_faculty, next_staff, next_researcher, next_student.
 */
defined('ABSPATH') || exit;

add_shortcode('aicsu_home_hero', function ($atts) {
    $atts = shortcode_atts([
        'eyebrow' => 'Colorado State University',
        'heading' => 'Find the Right AI *Tool for Your Work*',
        'lede' => '',
        'primary_label' => '', 'primary_url' => '',
        'secondary_label' => '', 'secondary_url' => '',
        'chooser_url' => '', 'limit' => '4', 'qm_heading' => 'Quick Match', 'qm_intro' => 'Two choices. No sign-in.', 'qm_note' => 'Level 4 restricted data: do not use AI tools with it.',
        'next_faculty' => '', 'next_staff' => '', 'next_researcher' => '', 'next_student' => '',
    ], $atts, 'aicsu_home_hero');

    // *highlight* -> its own highlighted line in the serif face (like the colostate.edu lockup); everything else is escaped.
    $heading = preg_replace('/\*(.+?)\*/', '<br><span class="ah-hl">$1</span>', esc_html($atts['heading']));
    $buttons = [];
    foreach ([['primary', 'ah-primary'], ['secondary', 'ah-secondary']] as [$key, $class]) {
        $url = esc_url($atts[$key . '_url'], ['https', 'http']);
        if ($atts[$key . '_label'] !== '' && $url) $buttons[] = sprintf('<a class="ah-btn %s" href="%s">%s</a>', $class, $url, esc_html($atts[$key . '_label']));
    }
    $panel = aicsu_quick_match_render([
        'chooser_url' => $atts['chooser_url'], 'limit' => $atts['limit'], 'heading' => $atts['qm_heading'], 'intro' => $atts['qm_intro'], 'note' => $atts['qm_note'],
        'next_faculty' => $atts['next_faculty'], 'next_staff' => $atts['next_staff'], 'next_researcher' => $atts['next_researcher'], 'next_student' => $atts['next_student'],
    ]);
    $base = plugin_dir_url(__FILE__);
    ob_start();
    ?>
    <link rel="stylesheet" href="<?php echo esc_url($base . 'home-hero.css?ver=' . filemtime(__DIR__ . '/home-hero.css')); ?>">
    <div class="aicsu-home-hero">
        <div class="ah-inner">
            <div class="ah-copy">
                <?php if ($atts['eyebrow'] !== '') : ?><p class="ah-eyebrow"><?php echo esc_html($atts['eyebrow']); ?></p><?php endif; ?>
                <h1 class="ah-heading"><?php echo wp_kses($heading, ['span' => ['class' => true], 'br' => []]); ?></h1>
                <?php if ($atts['lede'] !== '') : ?><p class="ah-lede"><?php echo esc_html($atts['lede']); ?></p><?php endif; ?>
                <?php if ($buttons) : ?><p class="ah-actions"><?php echo implode(' ', $buttons); // already escaped above ?></p><?php endif; ?>
            </div>
            <div class="ah-panel"><?php echo $panel; // built and escaped by aicsu_quick_match_render() ?></div>
        </div>
    </div>
    <?php
    return ob_get_clean();
});
