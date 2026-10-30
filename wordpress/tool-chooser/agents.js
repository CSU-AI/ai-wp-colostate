/* Small, dependency-free enhancement of the server-rendered agent directory. */
(function () {
  'use strict';
  const PARAMS = ['role', 'platform', 'tag'];
  // An agent matches when, in every group with a selection, it carries at least one selected term, and the search text is found.
  function matches(agent, groups, search) {
    return PARAMS.every(param => !(groups[param] || []).length || groups[param].some(value => (agent.terms[param] || []).includes(value))) &&
      agent.text.includes(String(search || '').trim().toLowerCase());
  }
  if (typeof module !== 'undefined' && module.exports) { module.exports = { matches }; return; }
  function init(root) {
    if (root.dataset.ready) return;
    root.dataset.ready = 'true';
    const form = root.querySelector('.ac-form');
    const search = root.querySelector('input[type="search"]');
    const grid = root.querySelector('.ac-grid');
    const agents = Array.from(root.querySelectorAll('.ac-card'), card => ({
      card,
      text: card.dataset.search || '',
      terms: Object.fromEntries(PARAMS.map(param => [param, (card.dataset[param] || '').split(' ').filter(Boolean)]))
    }));
    root.querySelectorAll('.ac-interactive').forEach(node => { node.hidden = false; });
    if (window.matchMedia('(max-width: 767px)').matches) root.querySelector('.ac-filters').open = false;
    function groups() {
      const result = {};
      new FormData(form).forEach((value, name) => { (result[name] ||= []).push(value); });
      return result;
    }
    function refresh(updateUrl) {
      const chosen = groups();
      let shown = 0;
      agents.forEach(agent => {
        const ok = matches(agent, chosen, search.value);
        agent.card.hidden = !ok;
        if (ok) shown++;
      });
      root.querySelector('.ac-count').textContent = `${shown} of ${agents.length} agents`;
      root.querySelector('.ac-empty').hidden = shown > 0;
      if (updateUrl) {
        const params = new URLSearchParams();
        PARAMS.forEach(param => { if ((chosen[param] || []).length) params.set(param, chosen[param].join(',')); });
        if (search.value.trim()) params.set('q', search.value.trim());
        const query = params.toString();
        window.history.replaceState(null, '', window.location.pathname + (query ? '?' + query : '') + window.location.hash);
      }
    }
    // Prefilter links: unknown slugs are ignored.
    const incoming = new URLSearchParams(window.location.search);
    PARAMS.forEach(param => {
      const wanted = (incoming.get(param) || '').split(',').map(value => value.trim().toLowerCase()).filter(Boolean);
      form.querySelectorAll(`input[name="${param}"]`).forEach(box => { box.checked = wanted.includes(box.value); });
    });
    search.value = incoming.get('q') || '';
    form.addEventListener('reset', () => { search.value = ''; setTimeout(() => refresh(true), 0); });
    root.querySelector('.ac-reset').addEventListener('click', () => { form.reset(); search.focus(); });
    form.addEventListener('submit', event => event.preventDefault());
    form.addEventListener('change', () => refresh(true));
    search.addEventListener('input', () => refresh(true));
    refresh(false);
  }
  const start = () => document.querySelectorAll('.aicsu-agents').forEach(init);
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start); else start();
  if (window.jQuery) window.jQuery(window).on('elementor/frontend/init', () => {
    window.elementorFrontend.hooks.addAction('frontend/element_ready/shortcode.default', start);
  });
})();
