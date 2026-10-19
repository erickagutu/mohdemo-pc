/* Project Planner Diary — calendar, CRUD, multi-project support.
 *
 * Talks to modules/diary.php?api=1 which returns JSON.
 * Supports: list, create, edit, delete, and multiple projects per entry.
 */
(function () {
    'use strict';

    var CSRF_TOKEN = (document.getElementById('diaryCsrf') || {}).value || '';
    var API_URL = '/modules/diary.php?api=1';

    var allEntries = [];
    var activeCategory = null;
    var currentDate = new Date();
    var editingId = null;

    var CATEGORY_COLORS = {
        'Meeting': '#2563eb',
        'Task': '#7c3aed',
        'Deadline': '#dc2626',
        'Event': '#059669',
        'Field Visit': '#ea580c',
        'General': '#64748b'
    };

    /* -------------------- API helpers -------------------- */
    function api(method, body) {
        var opts = { method: method, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json' } };
        if (body) opts.body = JSON.stringify(Object.assign({ csrf_token: CSRF_TOKEN }, body));
        return fetch(API_URL, opts).then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d }; }); });
    }

    function loadEntries() {
        return api('GET').then(function (res) {
            allEntries = (res.ok && res.data.entries) ? res.data.entries : [];
            renderCalendar();
        });
    }

    /* -------------------- Calendar -------------------- */
    function fmtYMD(d) { return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0'); }
    function escapeHtml(s) { var d = document.createElement('div'); d.textContent = s == null ? '' : String(s); return d.innerHTML; }

    function renderCalendar() {
        var grid = document.getElementById('calendarGrid');
        var display = document.getElementById('calendarMonthDisplay');
        var months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        var y = currentDate.getFullYear(), m = currentDate.getMonth();
        display.textContent = months[m] + ' ' + y;

        var first = new Date(y, m, 1);
        var startDow = first.getDay();
        var daysInMonth = new Date(y, m + 1, 0).getDate();

        // Build entries-by-date map
        var byDate = {};
        allEntries.forEach(function (e) {
            var key = (e.event_date || '').slice(0, 10);
            if (!byDate[key]) byDate[key] = [];
            byDate[key].push(e);
        });

        var html = '';
        ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'].forEach(function (d) { html += '<div class="cal-dow">' + d + '</div>'; });
        for (var i = 0; i < startDow; i++) html += '<div class="cal-day empty"></div>';
        for (var day = 1; day <= daysInMonth; day++) {
            var ds = y + '-' + String(m + 1).padStart(2, '0') + '-' + String(day).padStart(2, '0');
            var dayEntries = byDate[ds] || [];
            if (activeCategory) dayEntries = dayEntries.filter(function (e) { return e.event_type === activeCategory; });
            var pills = '';
            dayEntries.slice(0, 4).forEach(function (e) {
                var c = CATEGORY_COLORS[e.event_type] || '#64748b';
                pills += '<button type="button" class="cal-pill" style="border-color:' + c + ';color:' + c + '" data-eid="' + e.id + '" title="' + escapeHtml(e.title) + '">' + escapeHtml(e.title) + '</button>';
            });
            if (dayEntries.length > 4) pills += '<span class="cal-more">+' + (dayEntries.length - 4) + ' more</span>';
            html += '<div class="cal-day' + (ds === fmtYMD(new Date()) ? ' today' : '') + '"><span class="cal-date">' + day + '</span><div class="cal-pills">' + pills + '</div></div>';
        }
        grid.innerHTML = html;

        grid.querySelectorAll('.cal-pill').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var eid = parseInt(btn.getAttribute('data-eid'), 10);
                var entry = allEntries.find(function (e) { return parseInt(e.id, 10) === eid; });
                if (entry) showInDrawer(entry);
            });
        });
    }

    /* -------------------- Drawer -------------------- */
    function showInDrawer(entry) {
        var content = document.getElementById('drawerProjectContent');
        var projects = entry.projects || [];
        var projHtml = '';
        if (projects.length) {
            projHtml = '<div class="diary-linked-projects"><h4>Linked Projects (' + projects.length + ')</h4><ul>';
            projects.forEach(function (p) {
                projHtml += '<li><span class="proj-pill">' + escapeHtml((p.programme_title || '') + ' — ' + (p.sub_programme || '')) + '</span></li>';
            });
            projHtml += '</ul></div>';
        } else {
            projHtml = '<p class="diary-empty-note">No projects linked to this entry.</p>';
        }
        var meta = '';
        if (entry.event_time) meta += '<span>🕒 ' + escapeHtml(entry.event_time) + '</span>';
        if (entry.location) meta += '<span>📍 ' + escapeHtml(entry.location) + '</span>';
        if (entry.host) meta += '<span>👤 ' + escapeHtml(entry.host) + '</span>';
        if (entry.priority) meta += '<span>⚡ ' + escapeHtml(entry.priority) + '</span>';
        if (entry.status) meta += '<span>📊 ' + escapeHtml(entry.status) + '</span>';

        content.innerHTML =
            '<div class="diary-entry-detail">' +
                '<div class="diary-entry-detail-head">' +
                    '<div><h4>' + escapeHtml(entry.title) + '</h4>' +
                    '<div class="diary-entry-meta"><span class="entry-cat-badge" style="background:' + (CATEGORY_COLORS[entry.event_type] || '#64748b') + '">' + escapeHtml(entry.event_type) + '</span> <span>📅 ' + escapeHtml(entry.event_date) + '</span>' + meta + '</div></div>' +
                    '<div class="diary-entry-actions">' +
                        '<button type="button" class="btn-edit" data-act="edit">✎ Edit</button> ' +
                        '<button type="button" class="btn-delete" data-act="delete">🗑 Delete</button>' +
                    '</div>' +
                '</div>' +
                (entry.attendees ? '<p><strong>Attendees:</strong> ' + escapeHtml(entry.attendees) + '</p>' : '') +
                (entry.description ? '<p>' + escapeHtml(entry.description).replace(/\n/g, '<br>') + '</p>' : '') +
                projHtml +
            '</div>';

        content.querySelector('[data-act="edit"]').addEventListener('click', function () { openModal(entry); });
        content.querySelector('[data-act="delete"]').addEventListener('click', function () { deleteEntry(entry); });
        document.getElementById('linkedProjectDrawer').classList.add('is-open');
    }

    /* -------------------- Modal (create + edit) -------------------- */
    function openModal(entry) {
        editingId = entry ? parseInt(entry.id, 10) : null;
        document.getElementById('diaryEntryId').value = editingId || '';
        document.getElementById('diaryTitle').value = entry ? (entry.title || '') : '';
        document.getElementById('diaryDate').value = entry ? (entry.event_date || '') : '';
        document.getElementById('diaryTime').value = entry ? (entry.event_time || '09:00') : '09:00';
        document.getElementById('diaryCategory').value = entry ? (entry.event_type || 'Meeting') : 'Meeting';
        document.getElementById('diaryPriority').value = entry ? (entry.priority || 'Medium') : 'Medium';
        document.getElementById('diaryLocation').value = entry ? (entry.location || '') : '';
        document.getElementById('diaryHost').value = entry ? (entry.host || '') : '';
        document.getElementById('diaryAttendees').value = entry ? (entry.attendees || '') : '';
        document.getElementById('diaryContent').value = entry ? (entry.description || '') : '';

        // multi-project chips
        var linked = {};
        if (entry && entry.projects) entry.projects.forEach(function (p) { linked[parseInt(p.id, 10)] = true; });
        document.querySelectorAll('#diaryProjectIds .diary-proj-chip').forEach(function (lbl) {
            var pid = parseInt(lbl.getAttribute('data-pid'), 10);
            var cb = lbl.querySelector('input[type=checkbox]');
            var on = !!linked[pid];
            cb.checked = on;
            lbl.classList.toggle('checked', on);
        });

        var titleEl = document.getElementById('diaryModalTitle');
        var subEl = document.getElementById('diaryModalSubtitle');
        var delBtn = document.getElementById('diaryDeleteBtn');
        if (entry) {
            titleEl.textContent = 'Edit Diary Entry';
            subEl.textContent = 'Update the activity, schedule and project details.';
            delBtn.style.display = 'inline-block';
        } else {
            titleEl.textContent = 'Schedule New Diary Entry';
            subEl.textContent = 'Capture the activity, schedule and project details.';
            delBtn.style.display = 'none';
        }

        var err = document.getElementById('diaryFormError'); err.hidden = true; err.textContent = '';
        document.getElementById('newDiaryModal').classList.add('is-open');
        document.getElementById('diaryTitle').focus();
    }

    function closeModal() { document.getElementById('newDiaryModal').classList.remove('is-open'); editingId = null; }

    function collectForm() {
        var pids = [];
        document.querySelectorAll('#diaryProjectIds input[type=checkbox]:checked').forEach(function (cb) { pids.push(parseInt(cb.value, 10)); });
        return {
            id: editingId || 0,
            action: 'save',
            title: document.getElementById('diaryTitle').value.trim(),
            event_date: document.getElementById('diaryDate').value,
            event_time: document.getElementById('diaryTime').value || null,
            event_type: document.getElementById('diaryCategory').value,
            priority: document.getElementById('diaryPriority').value,
            location: document.getElementById('diaryLocation').value.trim(),
            host: document.getElementById('diaryHost').value.trim(),
            attendees: document.getElementById('diaryAttendees').value.trim(),
            description: document.getElementById('diaryContent').value.trim(),
            status: 'UPCOMING',
            project_ids: pids
        };
    }

    function saveEntry(ev) {
        ev.preventDefault();
        var data = collectForm();
        var err = document.getElementById('diaryFormError');
        if (!data.title || !data.event_date) { err.textContent = 'Title and date are required.'; err.hidden = false; return; }
        var btn = document.getElementById('diarySaveBtn'); btn.disabled = true; btn.textContent = 'Saving…';
        api('POST', data).then(function (res) {
            btn.disabled = false; btn.textContent = 'Save Diary Entry';
            if (res.ok && res.data.ok) { closeModal(); return loadEntries(); }
            err.textContent = (res.data && res.data.error) || 'Could not save the entry.'; err.hidden = false;
        }).catch(function () { btn.disabled = false; btn.textContent = 'Save Diary Entry'; err.textContent = 'Network error.'; err.hidden = false; });
    }

    function deleteEntry(entry) {
        if (!confirm('Delete "' + entry.title + '"? This cannot be undone.')) return;
        api('POST', { action: 'delete', id: parseInt(entry.id, 10) }).then(function (res) {
            if (res.ok && res.data.ok) {
                document.getElementById('linkedProjectDrawer').classList.remove('is-open');
                loadEntries();
            } else { alert((res.data && res.data.error) || 'Could not delete.'); }
        });
    }

    /* -------------------- Wire up -------------------- */
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('addDiaryEntryBtn').addEventListener('click', function () { openModal(null); });
        document.getElementById('newDiaryForm').addEventListener('submit', saveEntry);
        document.getElementById('diaryDeleteBtn').addEventListener('click', function () {
            if (!editingId) return;
            var entry = allEntries.find(function (e) { return parseInt(e.id, 10) === editingId; });
            if (entry) deleteEntry(entry);
        });
        // chip toggle
        document.querySelectorAll('#diaryProjectIds .diary-proj-chip').forEach(function (lbl) {
            var cb = lbl.querySelector('input[type=checkbox]');
            cb.addEventListener('change', function () { lbl.classList.toggle('checked', cb.checked); });
        });

        document.getElementById('prevMonthBtn').addEventListener('click', function () { currentDate.setMonth(currentDate.getMonth() - 1); renderCalendar(); });
        document.getElementById('nextMonthBtn').addEventListener('click', function () { currentDate.setMonth(currentDate.getMonth() + 1); renderCalendar(); });
        document.getElementById('clearDiarySelection').addEventListener('click', function () { document.getElementById('linkedProjectDrawer').classList.remove('is-open'); });

        document.querySelectorAll('.calendar-event-pill').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var cat = btn.getAttribute('data-filter-category');
                if (activeCategory === cat) { activeCategory = null; btn.setAttribute('aria-pressed', 'false'); }
                else {
                    activeCategory = cat;
                    document.querySelectorAll('.calendar-event-pill').forEach(function (b) { b.setAttribute('aria-pressed', 'false'); });
                    btn.setAttribute('aria-pressed', 'true');
                }
                renderCalendar();
            });
        });

        window.closeNewDiaryModal = closeModal;
        document.getElementById('newDiaryModal').addEventListener('click', function (e) { if (e.target === this) closeModal(); });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeModal(); });

        loadEntries();
    });
})();
