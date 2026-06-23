// ============================================================
// IVOR PAINE MEMORIAL HOSPITAL — Main JavaScript
// ============================================================

document.addEventListener('DOMContentLoaded', function () {

    // ----------------------------------------------------------
    // 1. Sidebar submenu toggle
    // ----------------------------------------------------------
    var toggles = document.querySelectorAll('.submenu-toggle');

    toggles.forEach(function (toggle) {
        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            var parentLi = this.parentElement;
            var submenu  = parentLi.querySelector('.submenu');
            if (!submenu) return;

            document.querySelectorAll('.submenu.open').forEach(function (openMenu) {
                if (openMenu !== submenu) {
                    openMenu.classList.remove('open');
                    var otherToggle = openMenu.previousElementSibling;
                    if (otherToggle) otherToggle.classList.remove('open');
                }
            });

            submenu.classList.toggle('open');
            this.classList.toggle('open');
        });
    });

    // ----------------------------------------------------------
    // 2. Auto-open + highlight active sidebar link
    // ----------------------------------------------------------
    var currentFile = window.location.pathname.split('/').pop();

    document.querySelectorAll('.submenu li a').forEach(function (link) {
        var href = link.getAttribute('href');
        if (!href) return;
        var hrefFile = href.split('/').pop();
        if (hrefFile && hrefFile === currentFile) {
            var submenu = link.closest('.submenu');
            if (submenu) {
                submenu.classList.add('open');
                var parentToggle = submenu.previousElementSibling;
                if (parentToggle) parentToggle.classList.add('open');
            }
            link.classList.add('active-link');
        }
    });

    // ----------------------------------------------------------
    // 3. Sidebar collapse toggle
    // ----------------------------------------------------------
    var toggleBtn = document.getElementById('sidebarToggle');
    var sidebar   = document.getElementById('sidebar');

    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', function () {
            sidebar.style.left = (sidebar.style.left === '-240px') ? '0' : '-240px';
        });
    }

    // ----------------------------------------------------------
    // 4. Auto-dismiss success alerts after 5 s
    // ----------------------------------------------------------
    document.querySelectorAll('.alert-success').forEach(function (alert) {
        setTimeout(function () {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity    = '0';
            setTimeout(function () { alert.style.display = 'none'; }, 550);
        }, 5000);
    });

    // ----------------------------------------------------------
    // 5. Table search — view pages & report pages
    //    Works on every .table-wrap inside a .table-container
    // ----------------------------------------------------------
    attachTableSearch();

    // ----------------------------------------------------------
    // 6. Record page table search — manual record pages
    //    Works on every .record-table-wrap
    // ----------------------------------------------------------
    attachRecordTableSearch();

    // ----------------------------------------------------------
    // 7. Initialize Select2 for all dropdowns (Manual search)
    // ----------------------------------------------------------
    if (typeof jQuery !== 'undefined' && $.fn.select2) {
        $('select').select2({
            width: '100%',
            placeholder: 'Select an option'
        });
    }

});

// ============================================================
// Generic table search + column filter
// Attaches to every .table-wrap inside every .table-container
// ============================================================
function attachTableSearch() {
    var filterKeywords = ['position', 'status', 'ward', 'specialty', 'grade', 'occupancy'];

    document.querySelectorAll('.table-container').forEach(function (container) {

        // Handle EVERY .table-wrap in this container (e.g. report 10 has two)
        container.querySelectorAll('.table-wrap').forEach(function (tableWrap) {

            var table = tableWrap.querySelector('table');
            if (!table) return;

            var dataRows = Array.from(table.querySelectorAll('tbody tr:not(.group-header-row)'));
            if (dataRows.length === 0) return;

            var headers = Array.from(table.querySelectorAll('thead th')).map(function (th) {
                return th.textContent.trim().toLowerCase();
            });

            // Find columns suitable for dropdown filter
            var filterCols = [];
            headers.forEach(function (header, idx) {
                filterKeywords.forEach(function (kw) {
                    if (header.indexOf(kw) !== -1) {
                        filterCols.push({ label: header, idx: idx });
                    }
                });
            });

            // Build bar
            var bar = document.createElement('div');
            bar.className = 'table-search-bar';

            var searchInput = document.createElement('input');
            searchInput.type        = 'text';
            searchInput.className   = 'table-search-input';
            searchInput.placeholder = 'Search table…';
            bar.appendChild(searchInput);

            var selects = [];
            filterCols.forEach(function (fc) {
                var vals = new Set();
                dataRows.forEach(function (row) {
                    var cell = row.cells[fc.idx];
                    if (!cell) return;
                    var text = cell.textContent.trim().replace(/\s+/g, ' ');
                    if (text && text !== '—' && text !== '-' && text.length < 50) vals.add(text);
                });
                if (vals.size < 2) return;

                var labelCap = fc.label.charAt(0).toUpperCase() + fc.label.slice(1);
                var sel = document.createElement('select');
                sel.className      = 'table-filter-select';
                sel.dataset.colIdx = fc.idx;
                sel.title          = 'Filter by ' + labelCap;

                var allOpt = document.createElement('option');
                allOpt.value = '';
                allOpt.textContent = 'All ' + labelCap + 's';
                sel.appendChild(allOpt);

                Array.from(vals).sort().forEach(function (v) {
                    var opt = document.createElement('option');
                    opt.value = v;
                    opt.textContent = v;
                    sel.appendChild(opt);
                });

                bar.appendChild(sel);
                selects.push(sel);
            });

            var countBadge = document.createElement('span');
            countBadge.className   = 'search-count-badge';
            countBadge.textContent = dataRows.length + ' row' + (dataRows.length !== 1 ? 's' : '');
            bar.appendChild(countBadge);

            // Insert bar immediately before this table-wrap
            tableWrap.parentNode.insertBefore(bar, tableWrap);

            function filterRows() {
                var term = searchInput.value.toLowerCase().trim();
                var activeFilters = {};
                selects.forEach(function (sel) {
                    if (sel.value) activeFilters[parseInt(sel.dataset.colIdx, 10)] = sel.value;
                });

                var visible = 0;
                Array.from(table.querySelectorAll('tbody tr')).forEach(function (row) {
                    if (row.classList.contains('group-header-row')) {
                        row.style.display = '';
                        return;
                    }
                    var matchText    = !term || row.textContent.toLowerCase().indexOf(term) !== -1;
                    var matchFilters = true;
                    Object.keys(activeFilters).forEach(function (ci) {
                        var cell = row.cells[parseInt(ci, 10)];
                        if (cell && cell.textContent.indexOf(activeFilters[ci]) === -1) matchFilters = false;
                    });
                    var show = matchText && matchFilters;
                    row.style.display = show ? '' : 'none';
                    if (show) visible++;
                });

                countBadge.textContent = visible + ' row' + (visible !== 1 ? 's' : '');
            }

            searchInput.addEventListener('input', filterRows);
            selects.forEach(function (sel) { sel.addEventListener('change', filterRows); });
        });
    });
}

// ============================================================
// Record page table search
// Attaches to every .record-table-wrap (manual record pages)
// ============================================================
function attachRecordTableSearch() {
    document.querySelectorAll('.record-table-wrap').forEach(function (wrap) {
        var table = wrap.querySelector('table');
        if (!table) return;

        var rows = Array.from(table.querySelectorAll('tbody tr'));
        if (rows.length < 2) return;   // skip trivially small tables

        var bar = document.createElement('div');
        bar.className = 'record-search-bar';

        var input = document.createElement('input');
        input.type        = 'text';
        input.className   = 'table-search-input record-search-input';
        input.placeholder = 'Search…';
        bar.appendChild(input);

        var countBadge = document.createElement('span');
        countBadge.className   = 'search-count-badge';
        countBadge.textContent = rows.length + ' row' + (rows.length !== 1 ? 's' : '');
        bar.appendChild(countBadge);

        wrap.parentNode.insertBefore(bar, wrap);

        input.addEventListener('input', function () {
            var term    = this.value.toLowerCase().trim();
            var visible = 0;
            rows.forEach(function (row) {
                var show = !term || row.textContent.toLowerCase().indexOf(term) !== -1;
                row.style.display = show ? '' : 'none';
                if (show) visible++;
            });
            countBadge.textContent = visible + ' row' + (visible !== 1 ? 's' : '');
        });
    });
}

// ============================================================
// Form validation helpers (called via onsubmit="...")
// ============================================================

function validateLoginForm() {
    var u = document.getElementById('username');
    var p = document.getElementById('password');
    if (!u || u.value.trim() === '') { alert('Please enter your username.'); if (u) u.focus(); return false; }
    if (!p || p.value === '')         { alert('Please enter your password.'); if (p) p.focus(); return false; }
    return true;
}

function validateRequired(formId) {
    var form = document.getElementById(formId);
    if (!form) return true;
    var fields = form.querySelectorAll('[required]');
    for (var i = 0; i < fields.length; i++) {
        if (fields[i].value.trim() === '') {
            alert('Please fill in all required fields.');
            fields[i].focus();
            return false;
        }
    }
    return true;
}

function validateDateRange(startId, endId) {
    var startEl = document.getElementById(startId);
    var endEl   = document.getElementById(endId);
    if (!startEl || !endEl || endEl.value === '') return true;
    var start = new Date(startEl.value);
    var end   = new Date(endEl.value);
    if (isNaN(start) || isNaN(end)) return true;
    if (end < start) { alert('End date cannot be before start date.'); endEl.focus(); return false; }
    return true;
}

function validateDOBAdmit(dobId, admitId) {
    var dob   = document.getElementById(dobId);
    var admit = document.getElementById(admitId);
    if (!dob || !admit || dob.value === '' || admit.value === '') return true;
    if (new Date(dob.value) >= new Date(admit.value)) {
        alert('Date of Birth must be before Date Admitted.');
        dob.focus();
        return false;
    }
    return true;
}
