/* Project Planner Diary — calendar, CRUD, multi-project support.
 * Uses the production assets/style.css calendar classes.
 * Talks to modules/diary.php?api=1 (JSON).
 */
(function () {
    'use strict';

    var CSRF_TOKEN = (document.getElementById('diaryCsrf') || {}).value || '';
    var API_URL = '/modules/diary.php?api=1';

    var allEntries = [];
    var activeCategory = null;
    var currentDate = new Date();
    var editingId = null;

    /* The production stylesheet uses event-cat-* for category colours. */
    function categoryClass(type) {
        switch ((type || '').toLowerCase()) {
            case 'meeting':  return 'event-cat-meeting';
            case 'task':     return 'event-cat-task';
            case 'deadline': return 'event-cat-deadline';
            case 'event':    return 'event-cat-event';
            default:         return 'event-cat-general';
        }
    }

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

    /* -------------------- Utilities -------------------- */
    function fmtYMD(d) { return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0'); }
    function escapeHtml(s) { var d = document.createElement('div'); d.textContent = s == null ? '' : String(s); return d.innerHTML; }
    function categoryLabel(type) {
        switch ((type || '').toLowerCase()) {
            case 'meeting': return 'Meeting';
            case 'task': return 'Task';
            case 'deadline': return 'Deadline';
            case 'event': return 'Event';
            case 'field visit': return 'Field Visit';
            default: return 'General';
        }
    }

    /* -------------------- Calendar -------------------- */
    function renderCalendar() {
        var grid = document.getElementById('calendarGrid');
        var display = document.getElementById('calendarMonthDisplay');
        var months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        var y = currentDate.getFullYear(), m = currentDate.getMonth();
        display.textContent = months[m] + ' ' + y;

        var first = new Date(y, m, 1);
        var startDow = first.getDay();
        var daysInMonth = new Date(y, m + 1, 0).getDate();
        var prevMonthDays = new Date(y, m, 0).getDate();

        var byDate = {};
        allEntries.forEach(function (e) {
            var key = (e.event_date || '').slice(0, 10);
            if (!byDate[key]) byDate[key] = [];
            byDate[key].push(e);
        });

        var html = '';
        ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'].forEach(function (d) {
            html += '<div class="calendar-day-header">' + d + '</div>';
        });

        // Leading days from previous month
        for (var i = startDow - 1; i >= 0; i--) {
            html += '<div class="calendar-day-cell other-month"><span class="day-number">' + (prevMonthDays - i) + '</span></div>';
        }

        for (var day = 1; day <= daysInMonth; day++) {
            var ds = y + '-' + String(m + 1).padStart(2, '0') + '-' + String(day).padStart(2, '0');
            var dayEntries = byDate[ds] || [];
            if (activeCategory) dayEntries = dayEntries.filter(function (e) { return e.event_type === activeCategory; });
            var hasEvent = dayEntries.length > 0;
            var isToday = ds === fmtYMD(new Date());

            var pills = '';
            dayEntries.slice(0, 4).forEach(function (e) {
                pills += '<button type="button" class="calendar-event-pill ' + categoryClass(e.event_type) + '" data-eid="' + e.id + '" title="' + escapeHtml(e.title) + '">' + escapeHtml(e.title) + '</button>';
            });
            if (dayEntries.length > 4) {
                pills += '<span class="calendar-event-pill ' + categoryClass(dayEntries[0].event_type) + '" style="opacity:.7;cursor:default;">+' + (dayEntries.length - 4) + ' more</span>';
            }

            html += '<div class="calendar-day-cell' + (hasEvent ? ' has-event' : '') + (isToday ? ' selected' : '') + '" data-date="' + ds + '">' +
                        '<span class="day-number">' + day + '</span>' +
                        '<div class="events-container">' + pills + '</div>' +
                    '</div>';
        }

        // Trailing days from next month
        var totalCells = startDow + daysInMonth;
        var trailing = (7 - (totalCells % 7)) % 7;
        for (var t = 1; t <= trailing; t++) {
            html += '<div class="calendar-day-cell other-month"><span class="day-number">' + t + '</span></div>';
        }

        grid.innerHTML = html;

        // Wire event pills
        grid.querySelectorAll('.calendar-event-pill[data-eid]').forEach(function (btn) {
            btn.addEventListener('click', function (ev) {
                ev.stopPropagation();
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
            projHtml = '<div class="diary-linked-projects-list">';
            projects.forEach(function (p) {
                var label = trimDash((p.programme_title || '') + ' — ' + (p.sub_programme || ''));
                projHtml += '<span class="proj-pill">' + escapeHtml(label) + '</span>';
            });
            projHtml += '</div>';
        } else {
            projHtml = '<p class="diary-unlinked-note">No projects linked to this entry.</p>';
        }

        var meta = '';
        if (entry.event_time) meta += '<span>🕒 ' + escapeHtml(entry.event_time) + '</span>';
        if (entry.location) meta += '<span>📍 ' + escapeHtml(entry.location) + '</span>';
        if (entry.host) meta += '<span>👤 ' + escapeHtml(entry.host) + '</span>';
        if (entry.priority) meta += '<span>⚡ ' + escapeHtml(entry.priority) + '</span>';
        if (entry.status) meta += '<span>📊 ' + escapeHtml(entry.status) + '</span>';

        content.innerHTML =
            '<div class="diary-entry-detail">' +
                '<div class="diary-entry-topline">' +
                    '<span class="calendar-event-pill ' + categoryClass(entry.event_type) + '">' + escapeHtml(categoryLabel(entry.event_type)) + '</span>' +
                    '<span>📅 ' + escapeHtml(entry.event_date) + '</span>' + meta +
                '</div>' +
                '<h4>' + escapeHtml(entry.title) + '</h4>' +
                (entry.description ? '<p>' + escapeHtml(entry.description).replace(/\n/g, '<br>') + '</p>' : '') +
                (entry.attendees ? '<div class="diary-entry-meta"><span><strong>Attendees:</strong> ' + escapeHtml(entry.attendees) + '</span></div>' : '') +
                '<div class="diary-entry-actions" style="margin-top:10px;">' +
                    '<button type="button" class="btn-edit" data-act="edit">✎ Edit</button>' +
                    '<button type="button" class="btn-delete" data-act="delete">🗑 Delete</button>' +
                '</div>' +
                projHtml +
            '</div>';

        content.querySelector('[data-act="edit"]').addEventListener('click', function () { openModal(entry); });
        content.querySelector('[data-act="delete"]').addEventListener('click', function () { deleteEntry(entry); });
        document.getElementById('linkedProjectDrawer').classList.add('active');
    }

    function trimDash(s) { return s.replace(/^[\s—]+|[\s—]+$/g, ''); }

    /* -------------------- Modal -------------------- */
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
            delBtn.style.display = 'inline-flex';
        } else {
            titleEl.textContent = 'Schedule New Diary Entry';
            subEl.textContent = 'Capture the activity, schedule and project details.';
            delBtn.style.display = 'none';
        }

        var err = document.getElementById('diaryFormError'); err.hidden = true; err.textContent = '';
        var modal = document.getElementById('newDiaryModal');
        modal.classList.add('active');
        document.getElementById('diaryTitle').focus();
    }

    function closeModal() {
        document.getElementById('newDiaryModal').classList.remove('active');
        editingId = null;
    }

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
                document.getElementById('linkedProjectDrawer').classList.remove('active');
                loadEntries();
            } else { alert((res.data && res.data.error) || 'Could not delete.'); }
        });
    }

    /* -------------------- Wire up -------------------- */
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('addDiaryEntryBtn').addEventListener('click', function () { openModal(null); });
        document.getElementById('newDiaryForm').addEventListener('submit', saveEntry);
        document.getElementById('diaryCancelBtn').addEventListener('click', closeModal);
        document.getElementById('diaryModalClose').addEventListener('click', closeModal);
        document.getElementById('newDiaryModal').addEventListener('click', function (e) { if (e.target === this) closeModal(); });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeModal(); });

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
        document.getElementById('clearDiarySelection').addEventListener('click', function () {
            document.getElementById('linkedProjectDrawer').classList.remove('active');
        });

        document.querySelectorAll('.diary-category-filters .calendar-event-pill').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var cat = btn.getAttribute('data-filter-category');
                if (activeCategory === cat) {
                    activeCategory = null;
                    btn.classList.remove('active');
                    btn.setAttribute('aria-pressed', 'false');
                } else {
                    activeCategory = cat;
                    document.querySelectorAll('.diary-category-filters .calendar-event-pill').forEach(function (b) {
                        b.classList.remove('active'); b.setAttribute('aria-pressed', 'false');
                    });
                    btn.classList.add('active');
                    btn.setAttribute('aria-pressed', 'true');
                }
                renderCalendar();
            });
        });

        loadEntries();
    });
})();
