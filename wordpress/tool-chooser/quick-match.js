/* Quick Match: role + most sensitive data level -> the tools that fit. Dependency-free. */
(function () {
  'use strict';
  const LEVELS = ['level-1-public', 'level-2-internal', 'level-3-confidential']; // Level 4 is not offered.
  // Same rule as the full chooser for a single selected level: the tool is tagged for that level,
  // and Level 3 also needs the tool's sensitive-data approval.
  function eligible(tool, role, level) {
    const terms = tool.terms || {};
    return LEVELS.includes(level) && (terms.aicsu_role || []).includes(role) &&
      (terms.aicsu_data || []).includes(level) &&
      (LEVELS.indexOf(level) < 2 || tool.approved === true);
  }
  if (typeof module !== 'undefined' && module.exports) { module.exports = { eligible, LEVELS }; return; }

  function init(root) {
    if (root.dataset.ready) return;
    root.dataset.ready = 'true';
    const tools = JSON.parse(root.querySelector('.qm-data').textContent);
    const next = JSON.parse(root.dataset.next || '{}');
    const limit = parseInt(root.dataset.limit, 10) || 4;
    const rows = new Map(Array.from(root.querySelectorAll('.qm-list li'), row => [Number(row.dataset.id), row]));
    const form = root.querySelector('.qm-form');
    const prompt = root.querySelector('.qm-prompt');
    const heading = root.querySelector('.qm-heading');
    const status = root.querySelector('.qm-status');
    const empty = root.querySelector('.qm-empty');
    const nextStep = root.querySelector('.qm-next');
    const seeAll = root.querySelector('.qm-see-all');
    const toolLinks = Array.from(root.querySelectorAll('.qm-tool-link'));
    form.hidden = false;

    const value = name => form.querySelector(`input[name="${name}"]:checked`)?.value || '';
    function refresh(updateUrl) {
      const role = value('role');
      const data = value('data');
      const ready = !!(role && data);
      const fit = ready ? tools.filter(tool => eligible(tool, role, data)) : [];
      const shown = new Set(fit.slice(0, limit).map(tool => tool.id));
      rows.forEach((row, id) => { row.hidden = !shown.has(id); });
      prompt.hidden = ready;
      heading.hidden = !ready || !fit.length;
      empty.hidden = !ready || fit.length > 0;
      status.textContent = ready ? `${fit.length} ${fit.length === 1 ? 'tool fits' : 'tools fit'} your answers` : '';
      // Next step for the chosen role.
      nextStep.replaceChildren();
      nextStep.hidden = !next[role];
      if (next[role]) {
        const link = document.createElement('a');
        link.href = next[role].url;
        link.textContent = next[role].label;
        nextStep.append(`Next step for ${next[role].role.toLowerCase()}: `, link);
      }
      if (seeAll && root.dataset.chooser) {
        const url = new URL(root.dataset.chooser, window.location.href);
        url.searchParams.set('role', role);
        url.searchParams.set('data', data);
        seeAll.href = url.toString();
        seeAll.hidden = !ready || !fit.length;
        seeAll.textContent = fit.length > limit ? `See all ${fit.length} matching tools` : `Compare ${fit.length === 1 ? 'this tool' : 'these tools'} in the chooser`;
      }
      // Each tool opens the chooser on that tool, carrying the answers so "Show all tools" returns to the same filtered list.
      toolLinks.forEach(link => {
        const url = new URL(link.href, window.location.href);
        if (role) url.searchParams.set('role', role); else url.searchParams.delete('role');
        if (data) url.searchParams.set('data', data); else url.searchParams.delete('data');
        link.href = url.toString();
      });
      if (updateUrl) {
        const params = new URLSearchParams(window.location.search);
        [['role', role], ['data', data]].forEach(([key, v]) => { if (v) params.set(key, v); else params.delete(key); });
        const query = params.toString();
        window.history.replaceState(null, '', window.location.pathname + (query ? '?' + query : '') + window.location.hash);
      }
    }
    // Restore answers from a shared link; unknown values are ignored.
    const incoming = new URLSearchParams(window.location.search);
    ['role', 'data'].forEach(name => {
      const wanted = (incoming.get(name) || '').trim().toLowerCase();
      form.querySelectorAll(`input[name="${name}"]`).forEach(radio => { radio.checked = radio.value === wanted; });
    });
    form.addEventListener('change', () => refresh(true));
    form.addEventListener('submit', event => event.preventDefault());
    refresh(false);
  }

  const start = () => document.querySelectorAll('.aicsu-quickmatch').forEach(init);
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start); else start();
  if (window.jQuery) window.jQuery(window).on('elementor/frontend/init', () => {
    window.elementorFrontend.hooks.addAction('frontend/element_ready/shortcode.default', start);
  });
})();
