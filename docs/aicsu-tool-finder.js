/* ==========================================================================
   AI @ CSU Fort Collins - Tool Finder enhancement
   ai.colostate.edu
   --------------------------------------------------------------------------
   Progressive enhancement layered on top of Search & Filter Pro results.

   Search & Filter Pro does the filtering (OR within a taxonomy, AND across
   taxonomies). This file adds only the three things it cannot do:

     1. Match scoring        - ranks and flags the strongest matches
     2. Compare tray         - multi-select plus a side-by-side table
     3. Exclusion notice     - shows tools withheld for the chosen data
                               classification, instead of silently hiding them

   It reads the full tool set once from the WordPress REST API and keys each
   rendered result to a post by its post-<ID> body class, so it does not care
   which template rendered the card.

   If this file fails to load, the finder still filters correctly. Nothing
   here is required for the page to work.

   No dependencies. No build step. Served from GitHub Pages alongside
   aicsu-design-system.css.
   ========================================================================== */

(function () {
  'use strict';

  var CONFIG = {
    postType: 'aicsu_tool',
    restRoot: '/wp-json/wp/v2/',

    /* Wrapper you put around the Search & Filter results area. */
    resultsSelector: '.aicsu-finder-results',

    /* Search & Filter Pro form wrapper. */
    formSelector: '.searchandfilter',

    /* Data-classification terms that trigger the exclusion notice.
       Keep in sync with the aicsu_data taxonomy. */
    sensitiveDataSlugs: [
      'ferpa-student-records',
      'hipaa-health',
      'export-controlled',
      'confidential-research',
      'personnel-hr',
      'financial',
      'sensitive-business'
    ],

    /* Below this many checked filters, ranking is noise. */
    minChecksForScoring: 2,

    storageKey: 'aicsu-finder-compare'
  };

  var STATUS_LABELS = {
    approved: 'Approved for sensitive CSU data',
    pilot: 'CSU evaluation pilot',
    public_only: 'Public, non-sensitive data only'
  };

  var tools = null;        /* id -> tool record */
  var termIndex = {};      /* taxonomy -> { byId: {}, bySlug: {} } */
  var selected = loadSelection();
  var bar = null;
  var dialog = null;

  /* ---------------------------------------------------------------- utils */

  function decode(html) {
    var el = document.createElement('textarea');
    el.innerHTML = html || '';
    return el.value;
  }

  function el(tag, className, text) {
    var node = document.createElement(tag);
    if (className) { node.className = className; }
    if (text != null) { node.textContent = text; }
    return node;
  }

  function loadSelection() {
    try {
      var raw = window.sessionStorage.getItem(CONFIG.storageKey);
      return raw ? JSON.parse(raw) : [];
    } catch (e) { return []; }
  }

  function saveSelection() {
    try {
      window.sessionStorage.setItem(CONFIG.storageKey, JSON.stringify(selected));
    } catch (e) { /* private browsing, non-fatal */ }
  }

  function postIdFrom(node) {
    if (!node.className || typeof node.className !== 'string') { return null; }
    var m = node.className.match(/(?:^|\s)(?:post-|e-loop-item-)(\d+)(?:\s|$)/);
    return m ? parseInt(m[1], 10) : null;
  }

  /* Outermost descendants that carry a post ID. Stops descending once found,
     so a nested .post-12 inside .post-12 is not counted twice. */
  function findCards(root) {
    var out = [];
    (function walk(node) {
      for (var i = 0; i < node.children.length; i++) {
        var child = node.children[i];
        if (postIdFrom(child) !== null) { out.push(child); }
        else { walk(child); }
      }
    })(root);
    return out;
  }

  /* -------------------------------------------------------------- rest api */

  function loadTools() {
    var url = CONFIG.restRoot + CONFIG.postType + '?per_page=100&_embed=wp%3Aterm';
    return fetch(url, { credentials: 'same-origin' })
      .then(function (r) {
        if (!r.ok) { throw new Error('REST ' + r.status); }
        return r.json();
      })
      .then(function (posts) {
        var map = {};
        posts.forEach(function (post) {
          var keys = [];
          var terms = {};
          var groups = (post._embedded && post._embedded['wp:term']) || [];
          groups.forEach(function (group) {
            group.forEach(function (term) {
              var tax = term.taxonomy;
              if (!terms[tax]) { terms[tax] = []; }
              terms[tax].push({ id: term.id, slug: term.slug, name: term.name });
              keys.push(tax + ':' + term.slug);

              if (!termIndex[tax]) { termIndex[tax] = { byId: {}, bySlug: {} }; }
              termIndex[tax].byId[term.id] = term.slug;
              termIndex[tax].bySlug[term.slug] = term.id;
            });
          });

          map[post.id] = {
            id: post.id,
            name: decode(post.title && post.title.rendered),
            summary: decode(post.excerpt && post.excerpt.rendered).replace(/\s+/g, ' ').trim(),
            link: post.link,
            acf: post.acf || {},
            terms: terms,
            keys: keys
          };
        });
        return map;
      });
  }

  /* -------------------------------------------------------------- filters */

  /* Reads the Search & Filter form and returns normalised "taxonomy:slug"
     keys. S&F emits either term slugs or term IDs depending on its URL
     settings, so both are accepted. */
  function checkedKeys() {
    var form = document.querySelector(CONFIG.formSelector);
    if (!form) { return []; }

    var keys = [];
    var inputs = form.querySelectorAll('input[type="checkbox"]:checked, input[type="radio"]:checked, select');

    Array.prototype.forEach.call(inputs, function (input) {
      var match = (input.name || '').match(/_sft_([a-z0-9_]+)/i);
      if (!match) { return; }
      var tax = match[1];

      var values = [];
      if (input.tagName === 'SELECT') {
        Array.prototype.forEach.call(input.selectedOptions, function (o) {
          if (o.value) { values.push(o.value); }
        });
      } else if (input.value) {
        values.push(input.value);
      }

      values.forEach(function (value) {
        var slug = value;
        if (/^\d+$/.test(value) && termIndex[tax] && termIndex[tax].byId[value]) {
          slug = termIndex[tax].byId[value];
        }
        keys.push(tax + ':' + slug);
      });
    });

    return keys;
  }

  function sensitiveSelected(keys) {
    return keys.some(function (key) {
      var parts = key.split(':');
      return parts[0] === 'aicsu_data' &&
             CONFIG.sensitiveDataSlugs.indexOf(parts[1]) !== -1;
    });
  }

  /* -------------------------------------------------------------- scoring */

  function applyScoring(cards, keys) {
    var scores = [];

    cards.forEach(function (card) {
      var tool = tools[postIdFrom(card)];
      var score = 0;
      if (tool) {
        score = keys.filter(function (k) { return tool.keys.indexOf(k) !== -1; }).length;
      }
      card.setAttribute('data-aicsu-score', String(score));
      /* Only affects layout if the results container is flex or grid. */
      card.style.order = String(-score);
      scores.push(score);
    });

    var best = scores.length ? Math.max.apply(null, scores) : 0;
    var rank = keys.length >= CONFIG.minChecksForScoring && best >= 2;

    cards.forEach(function (card) {
      var flag = card.querySelector('.aicsu-finder-flag');
      var isBest = rank && parseInt(card.getAttribute('data-aicsu-score'), 10) === best;

      card.classList.toggle('aicsu-is-best', isBest);
      if (isBest && !flag) {
        card.insertBefore(el('span', 'aicsu-finder-flag', 'Best match'), card.firstChild);
      } else if (!isBest && flag) {
        flag.remove();
      }
    });
  }

  /* ------------------------------------------------------------ exclusions */

  /* Tools withheld because of the chosen data classification are shown as
     dimmed stubs with the reason. A tool that disappears reads as a bug;
     a tool that says why it is unavailable is the point of the page. */
  function applyExclusions(results, cards, keys) {
    var existing = results.querySelector('.aicsu-finder-excluded');
    if (existing) { existing.remove(); }

    if (!sensitiveSelected(keys)) { return; }

    var shown = cards.map(postIdFrom);
    var withheld = Object.keys(tools).map(function (id) { return tools[id]; })
      .filter(function (tool) {
        return shown.indexOf(tool.id) === -1 && !tool.acf.approved_for_sensitive;
      });

    if (!withheld.length) { return; }

    var box = el('div', 'aicsu-finder-excluded');
    box.appendChild(el('h3', null,
      withheld.length + (withheld.length === 1 ? ' tool is' : ' tools are') +
      ' not permitted for this data classification'));
    box.appendChild(el('p', null,
      'These tools have not been approved for the type of data you selected. ' +
      'Do not enter that data into them, including in a summarised or ' +
      'anonymised form.'));

    var list = el('ul', 'aicsu-finder-excluded-list');
    withheld.forEach(function (tool) {
      var item = el('li');
      item.appendChild(el('strong', null, tool.name));
      item.appendChild(el('span', null, ' — ' +
        (STATUS_LABELS[tool.acf.status] || 'Not approved for sensitive data')));
      list.appendChild(item);
    });
    box.appendChild(list);
    results.appendChild(box);
  }

  /* ------------------------------------------------------------------ meta */

  /* The classic-theme archive template renders a title and excerpt only, so
     the status badge and cost are injected here from the REST payload.

     The status must also be stated in the post excerpt, in words. This
     injection is an upgrade, not the only place a user can learn that a tool
     is not approved for sensitive data. Never let safety information exist
     solely in JavaScript. */
  function decorateMeta(cards) {
    cards.forEach(function (card) {
      var tool = tools[postIdFrom(card)];
      if (!tool || card.querySelector('.aicsu-finder-badge')) { return; }

      var status = tool.acf.status;
      if (status && STATUS_LABELS[status]) {
        var badge = el('span',
          'aicsu-finder-badge aicsu-finder-badge--' + status.replace(/_/g, '-'),
          STATUS_LABELS[status]);
        card.insertBefore(badge, card.firstChild);
      }

      if (tool.acf.cost) {
        card.appendChild(el('p', 'aicsu-finder-cost', tool.acf.cost));
      }
    });
  }

  /* --------------------------------------------------------------- compare */

  function decorateForCompare(cards) {
    cards.forEach(function (card) {
      var id = postIdFrom(card);
      var tool = tools[id];
      if (!tool) { return; }

      card.classList.add('aicsu-finder-card');

      var btn = card.querySelector('.aicsu-finder-compare-toggle');
      if (!btn) {
        btn = el('button', 'aicsu-finder-compare-toggle');
        btn.type = 'button';
        btn.appendChild(el('span', 'aicsu-finder-compare-label', 'Compare'));
        btn.addEventListener('click', function (event) {
          event.preventDefault();
          event.stopPropagation();
          toggle(id);
        });
        card.appendChild(btn);
      }

      var on = selected.indexOf(id) !== -1;
      btn.setAttribute('aria-pressed', on ? 'true' : 'false');
      btn.setAttribute('aria-label',
        (on ? 'Remove ' : 'Add ') + tool.name + (on ? ' from' : ' to') + ' comparison');
      card.classList.toggle('aicsu-is-selected', on);
    });
  }

  function toggle(id) {
    var at = selected.indexOf(id);
    if (at === -1) { selected.push(id); } else { selected.splice(at, 1); }
    saveSelection();
    refresh();
  }

  function buildBar() {
    if (bar) { return bar; }

    bar = el('div', 'aicsu-finder-bar');
    bar.hidden = true;

    var count = el('p', 'aicsu-finder-bar-count');
    count.id = 'aicsu-finder-count';
    count.setAttribute('aria-live', 'polite');
    bar.appendChild(count);

    var actions = el('div', 'aicsu-finder-bar-actions');

    var clear = el('button', 'aicsu-btn--secondary', 'Clear');
    clear.type = 'button';
    clear.addEventListener('click', function () {
      selected = [];
      saveSelection();
      refresh();
    });

    var open = el('button', 'aicsu-btn--primary', 'Compare Selected Tools');
    open.type = 'button';
    open.addEventListener('click', openDialog);

    actions.appendChild(clear);
    actions.appendChild(open);
    bar.appendChild(actions);
    document.body.appendChild(bar);
    return bar;
  }

  function updateBar() {
    var node = buildBar();
    var n = selected.length;
    node.hidden = n === 0;
    node.querySelector('.aicsu-finder-bar-count').textContent =
      n + (n === 1 ? ' tool selected' : ' tools selected');
  }

  function termNames(tool, tax) {
    var list = tool.terms[tax] || [];
    return list.map(function (t) { return t.name; }).join(', ') || '—';
  }

  function openDialog() {
    if (!selected.length) { return; }

    if (!dialog) {
      dialog = el('dialog', 'aicsu-finder-dialog');
      dialog.setAttribute('aria-label', 'Tool comparison');
      document.body.appendChild(dialog);
    }
    dialog.textContent = '';

    var head = el('div', 'aicsu-finder-dialog-head');
    head.appendChild(el('h2', null, 'Comparing ' + selected.length + ' tools'));
    var close = el('button', 'aicsu-finder-dialog-close', 'Close');
    close.type = 'button';
    close.addEventListener('click', function () { dialog.close(); });
    head.appendChild(close);
    dialog.appendChild(head);

    var rows = [
      ['Status',            function (t) { return STATUS_LABELS[t.acf.status] || '—'; }],
      ['Sensitive CSU data', function (t) { return t.acf.approved_for_sensitive ? 'Approved' : 'Not approved'; }],
      ['Cost',              function (t) { return t.acf.cost || '—'; }],
      ['What it does',      function (t) { return t.summary || '—'; }],
      ['Best for',          function (t) { return termNames(t, 'aicsu_task'); }],
      ['Data allowed',      function (t) { return termNames(t, 'aicsu_data'); }],
      ['Produces',          function (t) { return termNames(t, 'aicsu_output'); }],
      ['Skill level',       function (t) { return termNames(t, 'aicsu_complexity'); }],
      ['Who can use it',    function (t) { return termNames(t, 'aicsu_role'); }]
    ];

    var picked = selected.map(function (id) { return tools[id]; })
                         .filter(Boolean);

    var scroll = el('div', 'aicsu-finder-dialog-scroll');
    var table = el('table', 'aicsu-table aicsu-finder-table');

    var thead = el('thead');
    var hr = el('tr');
    hr.appendChild(el('th', null, ''));
    picked.forEach(function (tool) {
      var th = el('th');
      th.scope = 'col';
      var link = el('a', null, tool.name);
      link.href = tool.acf.tool_url || tool.link;
      th.appendChild(link);
      hr.appendChild(th);
    });
    thead.appendChild(hr);
    table.appendChild(thead);

    var tbody = el('tbody');
    rows.forEach(function (row) {
      var tr = el('tr');
      var th = el('th', null, row[0]);
      th.scope = 'row';
      tr.appendChild(th);
      picked.forEach(function (tool) {
        tr.appendChild(el('td', null, row[1](tool)));
      });
      tbody.appendChild(tr);
    });
    table.appendChild(tbody);
    scroll.appendChild(table);
    dialog.appendChild(scroll);

    if (typeof dialog.showModal === 'function') { dialog.showModal(); }
    else { dialog.setAttribute('open', ''); }
    close.focus();
  }

  /* ----------------------------------------------------------------- cycle */

  function refresh() {
    var results = document.querySelector(CONFIG.resultsSelector);
    if (!results || !tools) { return; }

    var cards = findCards(results).filter(function (card) {
      return tools[postIdFrom(card)];
    });
    var keys = checkedKeys();

    decorateMeta(cards);
    applyScoring(cards, keys);
    applyExclusions(results, cards, keys);
    decorateForCompare(cards);
    updateBar();
  }

  function watch() {
    var results = document.querySelector(CONFIG.resultsSelector);
    if (!results) { return; }

    var pending = null;
    new MutationObserver(function () {
      window.clearTimeout(pending);
      pending = window.setTimeout(refresh, 60);
    }).observe(results, { childList: true, subtree: true });

    /* S&F Pro fires this as a jQuery event, which native listeners miss. */
    if (window.jQuery) {
      window.jQuery(document).on('sf:ajaxfinish', function () {
        window.setTimeout(refresh, 0);
      });
    }

    var form = document.querySelector(CONFIG.formSelector);
    if (form) {
      form.addEventListener('change', function () {
        window.setTimeout(refresh, 0);
      });
    }
  }

  function init() {
    if (!document.querySelector(CONFIG.resultsSelector)) { return; }

    loadTools().then(function (map) {
      tools = map;
      refresh();
      watch();
      document.documentElement.classList.add('aicsu-finder-ready');
    }).catch(function (error) {
      /* Filtering still works without any of this. */
      if (window.console) {
        window.console.warn('[aicsu] tool finder enhancement disabled:', error.message);
      }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
