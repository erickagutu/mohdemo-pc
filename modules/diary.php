<?php
/**
 * Project Planner Diary — PP-SDOMS
 * Native PHP 8.1+ & MySQL (No Node.js)
 *
 * CHANGES APPLIED (per request):
 *   2(a) A meeting/diary entry can be attached to MORE THAN ONE project.
 *        The single project dropdown has been replaced by a multi-select
 *        chips control. A new link table `diary_event_projects` stores the
 *        many-to-many relationship (see database/migrations/diary_multi_project.sql).
 *   2(b) Each diary entry now has EDIT and DELETE actions. The calendar and
 *        entry drawer expose Edit and Delete buttons; the modal re-opens in
 *        edit mode and populates all fields including the attached projects.
 *
 * This file is self-contained: it acts as both the page renderer and the
 * JSON API consumed by assets/js/diary.js. All write actions require a CSRF
 * token and validate input server-side.
 */
require_once __DIR__ . '/../config/db.php';
require_auth();

$page_title = 'Project Planner Diary — PP-SDOMS';
$active_tab = 'diary';

/* ============================================================ */
/*  JSON API — consumed by diary.js via fetch()                 */
/* ============================================================ */
$api_action = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest'
    || ($_GET['api'] ?? '') === '1'
    || strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;

if ($api_action) {
    header('Content-Type: application/json; charset=utf-8');
    $respond = static fn($payload, int $code = 200) => http_response_code($code) and print json_encode($payload, JSON_UNESCAPED_UNICODE) and exit;

    if (!$db_connected || !$pdo) { $respond(['ok' => false, 'error' => 'Database unavailable'], 503); }

    // Ensure the link table exists (idempotent). Safe to run on every request.
    $pdo->exec("CREATE TABLE IF NOT EXISTS `diary_event_projects` (
        `diary_event_id` INT NOT NULL,
        `project_id`     INT NOT NULL,
        PRIMARY KEY (`diary_event_id`, `project_id`),
        KEY `idx_dep_project` (`project_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $method = $_SERVER['REQUEST_METHOD'];
    $body = json_decode(file_get_contents('php://input') ?: '[]', true) ?: [];
    if (!$body && $method === 'POST') $body = $_POST; // allow form-encoded fallback

    try {
        if ($method === 'GET') {
            // List all entries with their linked projects.
            $rows = $pdo->query("SELECT * FROM diary_events ORDER BY event_date ASC, event_time ASC")->fetchAll();
            $linkStmt = $pdo->prepare("SELECT p.id, p.programme_title, p.sub_programme
                                        FROM diary_event_projects dep
                                        JOIN projects p ON p.id = dep.project_id
                                        WHERE dep.diary_event_id = ?
                                        ORDER BY p.programme_title, p.sub_programme");
            foreach ($rows as &$r) {
                $linkStmt->execute([$r['id']]);
                $r['projects'] = $linkStmt->fetchAll();
            }
            unset($r);
            $respond(['ok' => true, 'entries' => $rows]);
        }

        if ($method === 'POST') {
            if (!verify_csrf_token($body['csrf_token'] ?? '')) { $respond(['ok' => false, 'error' => 'Invalid session token'], 419); }

            $action = $body['action'] ?? 'save';
            if ($action === 'delete') {
                $id = (int)($body['id'] ?? 0);
                if ($id <= 0) { $respond(['ok' => false, 'error' => 'Missing id'], 422); }
                $pdo->prepare("DELETE FROM diary_event_projects WHERE diary_event_id = ?")->execute([$id]);
                $pdo->prepare("DELETE FROM diary_events WHERE id = ?")->execute([$id]);
                log_audit_trail('DELETE', 'Diary', $id, 'Deleted diary entry #' . $id);
                $respond(['ok' => true]);
            }

            // save (create or update)
            $id    = (int)($body['id'] ?? 0);
            $title = trim((string)($body['title'] ?? ''));
            $date  = trim((string)($body['event_date'] ?? ''));
            if ($title === '' || $date === '') { $respond(['ok' => false, 'error' => 'Title and date are required'], 422); }

            $fields = [
                'title'        => $title,
                'description'  => trim((string)($body['description'] ?? '')) ?: null,
                'event_type'   => trim((string)($body['event_type'] ?? 'Meeting')) ?: 'Meeting',
                'event_date'   => $date,
                'event_time'   => trim((string)($body['event_time'] ?? '')) ?: null,
                'location'     => trim((string)($body['location'] ?? '')) ?: null,
                'host'         => trim((string)($body['host'] ?? '')) ?: null,
                'attendees'    => trim((string)($body['attendees'] ?? '')) ?: null,
                'status'       => trim((string)($body['status'] ?? 'UPCOMING')) ?: 'UPCOMING',
                'priority'     => trim((string)($body['priority'] ?? 'Medium')) ?: 'Medium',
            ];
            // NOTE: project_id column kept for backwards-compat; the real links
            //       now live in diary_event_projects (multi-project).
            $projectIds = array_values(array_filter(array_map('intval', (array)($body['project_ids'] ?? [])),
                                       static fn($v) => $v > 0));
            $fields['project_id'] = $projectIds[0] ?? null;

            if ($id > 0) {
                $set = implode(',', array_map(static fn($k) => "`$k`=?", array_keys($fields)));
                $pdo->prepare("UPDATE diary_events SET $set WHERE id=?")
                    ->execute([...array_values($fields), $id]);
            } else {
                $cols = implode(',', array_map(static fn($k) => "`$k`", array_keys($fields)));
                $ph   = implode(',', array_fill(0, count($fields), '?'));
                $pdo->prepare("INSERT INTO diary_events ($cols) VALUES ($ph)")
                    ->execute(array_values($fields));
                $id = (int)$pdo->lastInsertId();
            }
            // refresh project links
            $pdo->prepare("DELETE FROM diary_event_projects WHERE diary_event_id = ?")->execute([$id]);
            $ins = $pdo->prepare("INSERT IGNORE INTO diary_event_projects (diary_event_id, project_id) VALUES (?,?)");
            foreach ($projectIds as $pid) $ins->execute([$id, $pid]);

            log_audit_trail($id && ($body['id'] ?? 0) ? 'UPDATE' : 'CREATE', 'Diary', $id, 'Saved diary entry "' . $title . '"');
            $respond(['ok' => true, 'id' => $id]);
        }

        $respond(['ok' => false, 'error' => 'Method not allowed'], 405);
    } catch (Throwable $ex) {
        error_log('diary api: ' . $ex->getMessage());
        $respond(['ok' => false, 'error' => 'Server error'], 500);
    }
}

/* ============================================================ */
/*  Page render                                                  */
/* ============================================================ */
$diary_projects = [];
if ($db_connected && $pdo) {
    try {
        // Ensure link table exists on page render too.
        $pdo->exec("CREATE TABLE IF NOT EXISTS `diary_event_projects` (
            `diary_event_id` INT NOT NULL,
            `project_id`     INT NOT NULL,
            PRIMARY KEY (`diary_event_id`, `project_id`),
            KEY `idx_dep_project` (`project_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $diary_projects = $pdo->query("SELECT id, programme_title, sub_programme FROM projects ORDER BY programme_title, sub_programme")->fetchAll();
    } catch (Throwable $e) { $diary_projects = []; }
}

$csrf = get_csrf_token();
require_once __DIR__ . '/../includes/header.php';
?>
<link rel="stylesheet" href="/assets/diary.css?v=20261019-1">
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

<!-- Diary entry modal (create + edit) -->
<div id="newDiaryModal" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="diaryModalTitle">
    <div class="modal-dialog diary-modal-dialog">
        <div class="modal-header diary-modal-header">
            <div>
                <span class="diary-modal-kicker">PROJECT PLANNER DIARY</span>
                <h3 class="modal-title" id="diaryModalTitle">Schedule New Diary Entry</h3>
                <p id="diaryModalSubtitle">Capture the activity, schedule and project details.</p>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeNewDiaryModal()" aria-label="Close">×</button>
        </div>
        <form id="newDiaryForm" class="diary-entry-form">
            <input type="hidden" id="diaryEntryId" value="">
            <input type="hidden" id="diaryCsrf" value="<?= htmlspecialchars($csrf) ?>">
            <div class="form-group"><label for="diaryTitle">Entry Title <span>*</span></label><input type="text" id="diaryTitle" class="form-input" maxlength="255" required placeholder="e.g. KNH project review meeting"></div>
            <div class="diary-form-grid">
                <div class="form-group"><label for="diaryDate">Event Date <span>*</span></label><input type="date" id="diaryDate" class="form-input" required></div>
                <div class="form-group"><label for="diaryTime">Event Time</label><input type="time" id="diaryTime" class="form-input" value="09:00"></div>
                <div class="form-group"><label for="diaryCategory">Category <span>*</span></label><select id="diaryCategory" class="form-select" required><option value="Meeting">Meeting</option><option value="Task">Task</option><option value="Deadline">Deadline</option><option value="Event">Event</option><option value="Field Visit">Field Visit</option><option value="General">General</option></select></div>
                <div class="form-group"><label for="diaryPriority">Priority</label><select id="diaryPriority" class="form-select"><option value="Medium">Medium</option><option value="High">High</option><option value="Critical">Critical</option><option value="Low">Low</option></select></div>
            </div>

            <!-- CHANGE 2(a): multi-project attachment (chips) -->
            <div class="form-group">
                <label for="diaryProjectIds">Link to Projects / Programmes <small>(select one or more)</small></label>
                <div class="diary-proj-pick" id="diaryProjectIds">
                    <?php if (!$diary_projects): ?>
                        <span class="diary-empty-note">No projects available.</span>
                    <?php endif; ?>
                    <?php foreach ($diary_projects as $project): ?>
                        <label class="diary-proj-chip" data-pid="<?= (int)$project['id'] ?>">
                            <input type="checkbox" value="<?= (int)$project['id'] ?>">
                            <span><?= htmlspecialchars(trim(($project['programme_title'] ?? '') . ' — ' . ($project['sub_programme'] ?? ''), ' —')) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="diary-form-grid">
                <div class="form-group"><label for="diaryLocation">Location</label><input type="text" id="diaryLocation" class="form-input" maxlength="255" placeholder="Venue or meeting link"></div>
                <div class="form-group"><label for="diaryHost">Host / Contact</label><input type="text" id="diaryHost" class="form-input" maxlength="150" placeholder="Responsible person"></div>
            </div>
            <div class="form-group"><label for="diaryAttendees">Attendees</label><input type="text" id="diaryAttendees" class="form-input" placeholder="Names or teams attending"></div>
            <div class="form-group"><label for="diaryContent">Agenda &amp; Operational Notes</label><textarea id="diaryContent" class="form-textarea" rows="4" placeholder="Agenda, expected deliverables or other useful details…"></textarea></div>
            <div id="diaryFormError" class="diary-form-error" hidden></div>
            <div class="diary-form-actions">
                <button type="button" class="status-pill" id="diaryDeleteBtn" style="margin-right:auto;display:none;background:#dc2626;color:#fff;border-color:#dc2626;">Delete</button>
                <button type="button" class="status-pill" onclick="closeNewDiaryModal()">Cancel</button>
                <button type="submit" class="btn-primary-green" id="diarySaveBtn">Save Diary Entry</button>
            </div>
        </form>
    </div>
</div>

<script src="/assets/js/diary.js?v=20261019-1"></script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
