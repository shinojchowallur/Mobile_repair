(function () {
  var $ = function (s, c) { return (c || document).querySelector(s); };
  var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };

  /* theme + mobile menu */
  var root = document.documentElement;
  var themeBtn = $('#themeBtn');
  function setIcon() { themeBtn.innerHTML = '<i class="fa-solid ' + (root.dataset.theme === 'dark' ? 'fa-sun' : 'fa-moon') + '"></i>'; }
  setIcon();
  themeBtn.onclick = function () {
    root.dataset.theme = root.dataset.theme === 'dark' ? 'light' : 'dark';
    try { localStorage.setItem('theme', root.dataset.theme); } catch (e) {}
    setIcon();
  };
  $('#burger').onclick = function () {
    var open = $('#menu').classList.toggle('open');
    this.setAttribute('aria-expanded', open);
  };

  /* toast auto-hide */
  var toast = $('.toast');
  if (toast) setTimeout(function () { toast.classList.add('out'); setTimeout(function () { toast.remove(); }, 450); }, 3500);

  /* ---------- registration form ---------- */
  var form = $('#repairForm');
  if (form) {
    // "existing OR new": using one side disables the other
    $$('.row[data-group]').forEach(function (row) {
      var sel = $('select', row), news = $$('input', row);
      function sync() {
        var typed = news.some(function (i) { return i.value.trim(); });
        sel.disabled = typed;
        news.forEach(function (i) { i.disabled = !!sel.value; });
      }
      sel.addEventListener('change', sync);
      news.forEach(function (i) { i.addEventListener('input', sync); });
      sync();
    });
    $('#resetBtn').addEventListener('click', function () {
      setTimeout(function () {
        $$('select,input', form).forEach(function (el) { el.disabled = false; el.classList.remove('invalid'); });
      }, 0);
    });
    form.addEventListener('submit', function (e) {
      var bad = false;
      $$('[required]', form).forEach(function (el) {
        var empty = !el.value.trim();
        el.classList.toggle('invalid', empty);
        bad = bad || empty;
      });
      if (bad) { e.preventDefault(); $('.invalid', form).focus(); return; }
      var b = $('button[type=submit]', form);
      b.classList.add('loading'); b.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';
    });
    $$('[required]', form).forEach(function (el) { el.addEventListener('input', function () { el.classList.remove('invalid'); }); });
  }

  /* ---------- report table ---------- */
  var tbl = $('#tbl');
  if (!tbl) return;
  var body = $('tbody', tbl), all = $$('tr', body), PAGE = 5, page = 1, sortDir = {}, view = all.slice();

  // animated counters
  $$('[data-count]').forEach(function (el) {
    var end = +el.dataset.count, n = 0, step = Math.max(1, Math.ceil(end / 20));
    var t = setInterval(function () { n = Math.min(end, n + step); el.textContent = n; if (n >= end) clearInterval(t); }, 30);
  });

  function filter() {
    var q = $('#search').value.toLowerCase().trim(), st = $('#statusFilter').value;
    view = all.filter(function (r) {
      var hay = (r.dataset.customer + ' ' + r.dataset.device + ' ' + r.dataset.tech + ' ' + r.dataset.problem).toLowerCase();
      return (!q || hay.indexOf(q) > -1) && (!st || r.dataset.status === st);
    });
    page = 1; render();
  }
  function render() {
    var pages = Math.max(1, Math.ceil(view.length / PAGE));
    page = Math.min(page, pages);
    all.forEach(function (r) { r.hidden = true; });
    view.slice((page - 1) * PAGE, page * PAGE).forEach(function (r) { r.hidden = false; body.appendChild(r); });
    var from = view.length ? (page - 1) * PAGE + 1 : 0, to = Math.min(page * PAGE, view.length);
    $('#info').textContent = 'Showing ' + from + ' - ' + to + ' of ' + view.length + ' records';
    $('#empty').hidden = view.length > 0;
    var h = '<button ' + (page === 1 ? 'disabled' : '') + ' data-p="' + (page - 1) + '" aria-label="Previous"><i class="fa-solid fa-chevron-left"></i></button>';
    for (var i = 1; i <= pages; i++) h += '<button class="' + (i === page ? 'on' : '') + '" data-p="' + i + '">' + i + '</button>';
    h += '<button ' + (page === pages ? 'disabled' : '') + ' data-p="' + (page + 1) + '" aria-label="Next"><i class="fa-solid fa-chevron-right"></i></button>';
    $('#pages').innerHTML = h;
  }
  $('#pages').addEventListener('click', function (e) {
    var b = e.target.closest('button'); if (!b || b.disabled) return;
    page = +b.dataset.p; render();
  });
  $('#search').addEventListener('input', filter);
  $('#statusFilter').addEventListener('change', filter);

  // sorting
  $$('th[data-sort]', tbl).forEach(function (th) {
    th.addEventListener('click', function () {
      var c = +th.dataset.sort, d = sortDir[c] = -(sortDir[c] || -1);
      view.sort(function (a, b) {
        return d * a.children[c].textContent.trim().localeCompare(b.children[c].textContent.trim(), undefined, { numeric: true });
      });
      page = 1; render();
    });
  });

  // view modal
  var modal = $('#modal');
  function closeModal() { modal.hidden = true; }
  body.addEventListener('click', function (e) {
    var b = e.target.closest('.view-btn'); if (!b) return;
    var d = b.closest('tr').dataset;
    var f = [['Customer', d.customer], ['Phone', d.phone], ['Address', d.address], ['Device', d.device],
             ['Problem', d.problem], ['Technician', d.tech + (d.skill ? ' (' + d.skill + ')' : '')],
             ['Received', d.date], ['Status', d.status]];
    var dl = $('#mBody'); dl.textContent = '';
    f.forEach(function (x) {
      var dt = document.createElement('dt'), dd = document.createElement('dd');
      dt.textContent = x[0]; dd.textContent = x[1] || '-'; dl.appendChild(dt); dl.appendChild(dd);
    });
    modal.hidden = false; $('#mClose').focus();
  });
  $('#mClose').onclick = closeModal;
  modal.addEventListener('click', function (e) { if (e.target === modal) closeModal(); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeModal(); });

  // delete confirm
  body.addEventListener('submit', function (e) {
    if (!confirm('Delete this repair record? This cannot be undone.')) e.preventDefault();
  });

  // export: show every row, print (Save as PDF), then restore paging
  $('#exportBtn').onclick = function () {
    all.forEach(function (r) { r.hidden = !view.includes(r); });
    window.print();
    render();
  };

  render();
})();
