window.currentSortField = window.currentSortField || '';
window.currentSortDir = window.currentSortDir || 'asc';
window.mSelected = window.mSelected || new Set();

window.initTable = function(sortField, sortDir) {
    window.currentSortField = sortField;
    window.currentSortDir = sortDir;
};

window.buildUrl = function() {
    var params = new URLSearchParams(window.location.search);
    var qEl = document.getElementById('searchInput');
    var q = qEl ? (qEl.value || '').trim() : '';
    var fieldEl = document.getElementById('filterField');
    var field = fieldEl ? fieldEl.value : '';
    var perPageEl = document.getElementById('perPage');
    var perPage = perPageEl ? perPageEl.value : '25';

    var prevField = params.get('_field');
    if (prevField && prevField !== field) {
        params.delete('filters[' + prevField + '][$contains]');
        params.delete('filters[' + prevField + '][$eq]');
        for (var kk of Array.from(params.keys())) {
            if (kk.indexOf('filters[' + prevField + '][') === 0) params.delete(kk);
        }
        params.delete('filter_op[' + prevField + ']');
    }
    if (q) {
        for (var kk2 of Array.from(params.keys())) {
            if (kk2.indexOf('filters[' + field + '][') === 0) params.delete(kk2);
        }
        params.set('filters[' + field + '][$contains]', q);
        params.set('_field', field);
        params.set('_q', q);
    } else {
        if (field) {
            for (var kk3 of Array.from(params.keys())) {
                if (kk3.indexOf('filters[' + field + '][') === 0) params.delete(kk3);
            }
            params.delete('filter_op[' + field + ']');
        }
        if (!qEl || !q) {
            params.delete('_q');
            params.delete('_field');
        }
        if (prevField && prevField !== field) {
            for (var kk4 of Array.from(params.keys())) {
                if (kk4.indexOf('filters[' + prevField + '][') === 0) params.delete(kk4);
            }
            params.delete('filter_op[' + prevField + ']');
        }
    }

    document.querySelectorAll('[data-field]').forEach(function(input) {
        var fieldName = input.dataset.field;
        var opEl = document.querySelector('[data-op="' + fieldName + '"]');
        var operator = opEl ? opEl.value : '$eq';
        var value = input.tagName === 'SELECT' ? input.value : (input.value || '').trim();
        for (var k of Array.from(params.keys())) {
            if (k.indexOf('filters[' + fieldName + '][') === 0) params.delete(k);
        }
        params.delete('filter_op[' + fieldName + ']');
        if (value) {
            params.set('filters[' + fieldName + '][' + operator + ']', value);
            params.set('filter_op[' + fieldName + ']', operator);
        }
    });

    if (window.currentSortField) params.set('sort[0]', window.currentSortField + ':' + window.currentSortDir);
    else params.delete('sort[0]');
    params.set('per_page', perPage);

    var path = window.location.pathname;
    if (path.indexOf('/table') === -1) {
        var moduleEl = document.querySelector('input.module');
        var module = moduleEl ? moduleEl.value : '';
        if (module) path = '/' + module.replace(/-/g, '/') + '/table';
        else path = window.location.pathname;
    }
    window.location.href = path + '?' + params.toString();
};

window.doSort = function(col) {
    if (window.currentSortField === col) {
        window.currentSortDir = window.currentSortDir === 'asc' ? 'desc' : 'asc';
    } else {
        window.currentSortField = col;
        window.currentSortDir = 'asc';
    }
    window.buildUrl();
};

window.updateFilterOp = function(fieldName) {};

window.applyAdvanced = function() {
    var el = document.getElementById('advFilter');
    if (el) el.classList.add('hidden');
    window.buildUrl();
};

window.resetAdvanced = function() {
    document.querySelectorAll('[data-field]').forEach(function(input) { input.value = ''; });
    document.querySelectorAll('[data-op]').forEach(function(select) { select.value = '$eq'; });
    window.applyAdvanced();
};

window.toggleAll = function(el) {
    document.querySelectorAll('tbody input[type="checkbox"]').forEach(function(c) { c.checked = el.checked; });
};

window.mToggle = function(el) {
    var id = el.dataset.id;
    var icon = el.querySelector('[data-check]');
    if (window.mSelected.has(id)) {
        window.mSelected.delete(id);
        el.style.backgroundColor = '';
        if (icon) icon.className = 'icon-[tabler--circle] size-5 text-base-content/20 shrink-0';
    } else {
        window.mSelected.add(id);
        el.style.backgroundColor = 'rgba(0,0,0,0.03)';
        if (icon) icon.className = 'icon-[tabler--circle-check-filled] size-5 text-primary shrink-0';
    }
    window.updateMSel();
};

window.mToggleAll = function() {
    var items = document.querySelectorAll('#mBody > div[data-id]');
    if (window.mSelected.size) {
        window.mSelected.clear();
        items.forEach(function(el) {
            el.style.backgroundColor = '';
            var ic = el.querySelector('[data-check]');
            if (ic) ic.className = 'icon-[tabler--circle] size-5 text-base-content/20 shrink-0';
        });
    } else {
        items.forEach(function(el) {
            window.mSelected.add(el.dataset.id);
            el.style.backgroundColor = 'rgba(0,0,0,0.03)';
            var ic = el.querySelector('[data-check]');
            if (ic) ic.className = 'icon-[tabler--circle-check-filled] size-5 text-primary shrink-0';
        });
    }
    window.updateMSel();
};

window.updateMSel = function() {
    var countEl = document.getElementById('mSelCount');
    var toggleEl = document.getElementById('mToggleAll');
    if (countEl) countEl.textContent = window.mSelected.size ? window.mSelected.size + ' selected' : '';
    if (toggleEl) toggleEl.textContent = window.mSelected.size ? 'Unselect' : 'Select All';
};

window.deleteSelected = function() {
    var desktopIds = Array.from(document.querySelectorAll('tbody input[type="checkbox"]:checked')).map(function(c) { return c.value; });
    var ids = desktopIds.length ? desktopIds : Array.from(window.mSelected);
    if (!ids.length) return alert('No items selected');
    if (!confirm('Delete ' + ids.length + ' item(s)?')) return;
    var form = document.createElement('form');
    // Turunkan endpoint dari URL halaman tabel: /admin/{prefix}/{module}/table -> .../delete
    // (nama route tidak sama dengan path karena prefix /admin)
    var base = location.pathname.replace(/\/table\/?$/, '');
    form.method = 'POST'; form.action = base + '/delete';
    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    if (csrfMeta) form.innerHTML += '<input type="hidden" name="_token" value="' + csrfMeta.content + '">';
    ids.forEach(function(id) { form.innerHTML += '<input type="hidden" name="ids[]" value="' + id + '">'; });
    document.body.appendChild(form); form.submit();
};
