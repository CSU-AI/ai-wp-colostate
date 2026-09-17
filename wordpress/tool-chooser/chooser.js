/* Small, dependency-free enhancement of the server-rendered ACF directory. */
(function () {
  'use strict';
  const sensitive = ['ferpa-student-records', 'hipaa-health', 'export-controlled', 'confidential-research', 'personnel-hr', 'financial', 'sensitive-business'];
  function dataAllowed(tool, groups) {
    const selected = (groups.aicsu_data || []).filter(value => sensitive.includes(value));
    return !selected.length || (tool.approved === true && selected.every(value => (tool.terms.aicsu_data || []).includes(value)));
  }
  function matches(tool, groups, search) {
    return dataAllowed(tool, groups) &&
      Object.entries(groups).every(([tax, values]) => !values.length || values.some(value => (tool.terms[tax] || []).includes(value))) &&
      [tool.name, tool.summary, ...Object.values(tool.names)].join(' ').toLowerCase().includes(search.trim().toLowerCase());
  }
  function score(tool, groups) {
    return Object.entries(groups).reduce((n, [tax, values]) => n + values.filter(value => (tool.terms[tax] || []).includes(value)).length, 0);
  }
  if (typeof module !== 'undefined' && module.exports) { module.exports = { matches, dataAllowed, score }; return; }
  function init(root) {
    if (root.dataset.ready) return;
    root.dataset.ready = 'true';
    const tools = JSON.parse(root.querySelector('.ac-data').textContent);
    const cards = new Map(Array.from(root.querySelectorAll('.ac-card'), card => [Number(card.dataset.id), card]));
    const selected = new Set();
    const form = root.querySelector('.ac-form');
    const search = root.querySelector('input[type="search"]');
    const sort = root.querySelector('.ac-sort');
    const grid = root.querySelector('.ac-grid');
    const dialog = root.querySelector('dialog');
    const make = (tag, text) => { const node = document.createElement(tag); node.textContent = text; return node; };
    root.querySelectorAll('.ac-interactive').forEach(node => { node.hidden = false; });
    if (window.matchMedia('(max-width: 767px)').matches) root.querySelector('.ac-filters').open = false;
    function groups() {
      const result = {};
      new FormData(form).forEach((value, tax) => { (result[tax] ||= []).push(value); });
      return result;
    }
    function refresh() {
      const chosen = groups();
      const visible = tools.filter(tool => matches(tool, chosen, search.value));
      if (sort.value === 'az' || sort.value === 'za') visible.sort((a, b) => a.name.localeCompare(b.name) * (sort.value === 'za' ? -1 : 1));
      if (sort.value === 'match') visible.sort((a, b) => score(b, chosen) - score(a, chosen) || a.name.localeCompare(b.name));
      cards.forEach(card => { card.hidden = true; card.querySelector('.ac-match').hidden = true; });
      const topScore = Math.max(0, ...visible.map(tool => score(tool, chosen)));
      visible.forEach(tool => {
        const card = cards.get(tool.id);
        card.hidden = false;
        card.querySelector('.ac-match').hidden = sort.value !== 'match' || topScore < 2 || score(tool, chosen) !== topScore;
        grid.appendChild(card); // DOM order and screen-reader order match the chosen sort.
      });
      root.querySelector('.ac-count').textContent = `${visible.length} of ${tools.length} tools`;
      root.querySelector('.ac-empty').hidden = visible.length > 0;
      const hasSensitive = (chosen.aicsu_data || []).some(value => sensitive.includes(value));
      root.querySelector('.ac-sensitive').hidden = !hasSensitive;
      const excluded = root.querySelector('.ac-excluded');
      const withheld = tools.filter(tool => !dataAllowed(tool, chosen));
      excluded.hidden = !withheld.length;
      excluded.querySelector('ul').replaceChildren(...withheld.map(tool => make('li', tool.name)));
    }
    function updateSelection() {
      tools.forEach(tool => {
        const button = cards.get(tool.id).querySelector('.ac-compare');
        const on = selected.has(tool.id);
        button.setAttribute('aria-pressed', String(on));
        button.textContent = on ? 'Selected' : 'Compare';
        button.disabled = selected.size === 3 && !on;
        cards.get(tool.id).classList.toggle('ac-is-selected', on);
      });
      root.querySelector('.ac-tray').hidden = selected.size === 0;
      root.querySelector('.ac-selected-count').textContent = `${selected.size} of 3 tools selected`;
      root.querySelector('.ac-open').disabled = selected.size < 2;
    }
    cards.forEach((card, id) => card.querySelector('.ac-compare').addEventListener('click', () => {
      if (selected.has(id)) selected.delete(id); else if (selected.size < 3) selected.add(id);
      updateSelection();
    }));
    root.querySelector('.ac-clear').addEventListener('click', () => {
      selected.clear(); updateSelection();
      root.querySelector('.ac-card:not([hidden]) .ac-compare')?.focus();
    });
    function reset() { search.value = ''; sort.value = 'recommended'; }
    form.addEventListener('reset', () => { reset(); setTimeout(refresh, 0); });
    root.querySelector('.ac-reset').addEventListener('click', () => { form.reset(); search.focus(); });
    form.addEventListener('submit', event => event.preventDefault());
    form.addEventListener('change', refresh);
    search.addEventListener('input', refresh);
    sort.addEventListener('change', refresh);
    root.querySelector('.ac-open').addEventListener('click', () => {
      const picked = tools.filter(tool => selected.has(tool.id));
      const table = make('table', '');
      const caption = make('caption', 'Selected AI tools');
      table.appendChild(caption);
      const head = make('thead', ''); const tr = make('tr', '');
      ['Feature', ...picked.map(tool => tool.name)].forEach(name => { const th = make('th', name); th.scope = 'col'; tr.appendChild(th); });
      head.appendChild(tr); table.appendChild(head);
      const body = make('tbody', '');
      const rows = [['Status', tool => tool.status], ['Cost', tool => tool.cost || 'Not provided'], ['Description', tool => tool.summary], ['Best For', tool => tool.names.aicsu_task], ['Data Classifications', tool => tool.names.aicsu_data], ['Technical Comfort', tool => tool.names.aicsu_complexity], ['Roles', tool => tool.names.aicsu_role]];
      rows.forEach(([label, value]) => {
        const row = make('tr', ''); const th = make('th', label); th.scope = 'row'; row.appendChild(th);
        picked.forEach(tool => row.appendChild(make('td', value(tool) || 'Not specified'))); body.appendChild(row);
      });
      table.appendChild(body); root.querySelector('.ac-table-scroll').replaceChildren(table);
      dialog.showModal(); root.querySelector('.ac-close').focus();
    });
    root.querySelector('.ac-close').addEventListener('click', () => dialog.close());
    dialog.addEventListener('close', () => root.querySelector('.ac-open').focus());
    refresh(); updateSelection();
  }
  const start = () => document.querySelectorAll('.aicsu-chooser').forEach(init);
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start); else start();
  if (window.jQuery) window.jQuery(window).on('elementor/frontend/init', () => {
    window.elementorFrontend.hooks.addAction('frontend/element_ready/shortcode.default', start);
  });
})();
