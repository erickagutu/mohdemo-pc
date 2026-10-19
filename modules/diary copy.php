<?php
/** Project Planner Diary integrated with the PP-SDOMS diary_events/projects schema. */
require_once __DIR__ . '/../config/db.php';
require_auth();

$page_title = 'Project Planner Diary — PP-SDOMS';
$active_tab = 'diary';
$diary_projects = [];
if ($db_connected && $pdo) {
    try {
        $diary_projects = $pdo->query("SELECT id, programme_title, sub_programme FROM projects ORDER BY programme_title, sub_programme")->fetchAll();
    } catch (Throwable $e) { $diary_projects = []; }
}
require_once __DIR__ . '/../includes/header.php';
?>
<div class="app-container diary-module">
    <div class="section-header diary-section-header">
        <div class="section-title-wrap">
            <span class="section-title-icon" aria-hidden="true">♧</span>
            <div><h2 class="section-title">Project Planner Diary</h2><div class="diary-page-subtitle">Plan meetings, tasks, deadlines and project activities.</div></div>
        </div>
        <button type="button" id="addDiaryEntryBtn" class="btn-primary-green">＋ <span>New Diary Entry</span></button>
    </div>

    <section class="calendar-card" aria-label="Diary calendar">
        <div class="calendar-header diary-calendar-header">
            <div class="diary-month-navigation">
                <button type="button" id="prevMonthBtn" class="status-pill">← Prev</button>
                <h3 id="calendarMonthDisplay" aria-live="polite"></h3>
                <button type="button" id="nextMonthBtn" class="status-pill">Next →</button>
            </div>
        </div>
        <div id="calendarGrid" class="calendar-grid"></div>
    </section>

    <nav class="diary-category-filters" aria-label="Filter diary entries by category">
        <button type="button" class="calendar-event-pill event-cat-meeting" data-filter-category="Meeting" aria-pressed="false">Meeting</button>
        <button type="button" class="calendar-event-pill event-cat-task" data-filter-category="Task" aria-pressed="false">Task</button>
        <button type="button" class="calendar-event-pill event-cat-deadline" data-filter-category="Deadline" aria-pressed="false">Deadline</button>
        <button type="button" class="calendar-event-pill event-cat-event" data-filter-category="Event" aria-pressed="false">Event</button>
        <button type="button" class="calendar-event-pill event-cat-general" data-filter-category="General" aria-pressed="false">General</button>
    </nav>

    <section id="linkedProjectDrawer" class="linked-project-drawer diary-details-panel" aria-live="polite">
        <div class="diary-details-heading">
            <h3><span aria-hidden="true">▣</span> Event Details &amp; Linked Project Specifications</h3>
            <button type="button" id="clearDiarySelection" class="modal-close-btn" aria-label="Clear selection">×</button>
        </div>
        <div id="drawerProjectContent" class="diary-details-content"><p class="diary-empty-note">Select a category on the calendar to view its entries.</p></div>
    </section>
</div>

<div id="newDiaryModal" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="diaryModalTitle">
    <div class="modal-dialog diary-modal-dialog">
        <div class="modal-header diary-modal-header">
            <div><span class="diary-modal-kicker">PROJECT PLANNER DIARY</span><h3 class="modal-title" id="diaryModalTitle">Schedule New Diary Entry</h3><p>Capture the activity, schedule and project details.</p></div>
            <button type="button" class="modal-close-btn" onclick="closeNewDiaryModal()" aria-label="Close">×</button>
        </div>
        <form id="newDiaryForm" class="diary-entry-form">
            <div class="form-group"><label for="diaryTitle">Entry Title <span>*</span></label><input type="text" id="diaryTitle" class="form-input" maxlength="255" required placeholder="e.g. KNH project review meeting"></div>
            <div class="diary-form-grid">
                <div class="form-group"><label for="diaryDate">Event Date <span>*</span></label><input type="date" id="diaryDate" class="form-input" min="<?= (new DateTimeImmutable('today', new DateTimeZone('Africa/Nairobi')))->format('Y-m-d') ?>" required></div>
                <div class="form-group"><label for="diaryTime">Event Time</label><input type="time" id="diaryTime" class="form-input" value="09:00"></div>
                <div class="form-group"><label for="diaryCategory">Category <span>*</span></label><select id="diaryCategory" class="form-select" required><option value="Meeting">Meeting</option><option value="Task">Task</option><option value="Deadline">Deadline</option><option value="Event">Event</option><option value="General">General</option></select></div>
                <div class="form-group"><label for="diaryPriority">Priority</label><select id="diaryPriority" class="form-select"><option value="Medium">Medium</option><option value="High">High</option><option value="Critical">Critical</option><option value="Low">Low</option></select></div>
            </div>
            <div class="form-group"><label for="diaryProjectId">Link to Project / Programme <small>(Optional)</small></label><select id="diaryProjectId" class="form-select"><option value="">None / Standalone Entry</option><?php foreach ($diary_projects as $project): ?><option value="<?= (int)$project['id'] ?>"><?= htmlspecialchars(trim(($project['programme_title'] ?? '') . ' — ' . ($project['sub_programme'] ?? ''), ' —')) ?></option><?php endforeach; ?></select></div>
            <div class="diary-form-grid">
                <div class="form-group"><label for="diaryLocation">Location</label><input type="text" id="diaryLocation" class="form-input" maxlength="255" placeholder="Venue or meeting link"></div>
                <div class="form-group"><label for="diaryHost">Host / Contact</label><input type="text" id="diaryHost" class="form-input" maxlength="150" placeholder="Responsible person"></div>
            </div>
            <div class="form-group"><label for="diaryAttendees">Attendees</label><input type="text" id="diaryAttendees" class="form-input" placeholder="Names or teams attending"></div>
            <div class="form-group"><label for="diaryContent">Agenda &amp; Operational Notes</label><textarea id="diaryContent" class="form-textarea" rows="4" placeholder="Agenda, expected deliverables or other useful details…"></textarea></div>
            <div id="diaryFormError" class="diary-form-error" hidden></div>
            <div class="diary-form-actions"><button type="button" class="status-pill" onclick="closeNewDiaryModal()">Cancel</button><button type="submit" class="btn-primary-green">Save Diary Entry</button></div>
        </form>
    </div>
</div>
<script src="/assets/js/diary.js?v=20261018-1"></script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
