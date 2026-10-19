<?php
require_once __DIR__ . '/../includes/layout.php';
$pdo = db();

// ---------------------------------------------------------------------------
// Handle POST actions: save (create/update) + delete
// ---------------------------------------------------------------------------
$editing = null;
$editProjects = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    $title    = trim($_POST['title'] ?? '');
    $date     = $_POST['meeting_date'] ?? '';
    $startT   = $_POST['start_time'] ?: null;
    $endT     = $_POST['end_time']   ?: null;
    $location = trim($_POST['location'] ?? '');
    $attend   = trim($_POST['attendees'] ?? '');
    $agenda   = trim($_POST['agenda'] ?? '');
    $minutes  = trim($_POST['minutes'] ?? '');
    $projIds  = array_filter(array_map('intval', (array)($_POST['project_ids'] ?? [])));
    $id       = (int)($_POST['id'] ?? 0);

    if ($action === 'save') {
        if ($title === '' || $date === '') {
            $error = 'Title and date are required.';
            $editing = compact('id','title','meeting_date','start_time','end_time','location','attendees','agenda','minutes') + ['_p'=>$projIds];
            $editProjects = $projIds;
        } else {
            try {
                $pdo->beginTransaction();
                if ($id > 0) {
                    $stmt = $pdo->prepare("UPDATE meetings SET title=?,meeting_date=?,start_time=?,end_time=?,location=?,attendees=?,agenda=?,minutes=? WHERE id=?");
                    $stmt->execute([$title,$date,$startT,$endT,$location,$attend,$agenda,$minutes,$id]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO meetings (title,meeting_date,start_time,end_time,location,attendees,agenda,minutes) VALUES (?,?,?,?,?,?,?,?)");
                    $stmt->execute([$title,$date,$startT,$endT,$location,$attend,$agenda,$minutes]);
                    $id = (int)$pdo->lastInsertId();
                }
                // refresh project attachments (many-to-many)
                $pdo->prepare("DELETE FROM meeting_projects WHERE meeting_id=?")->execute([$id]);
                $ins = $pdo->prepare("INSERT INTO meeting_projects (meeting_id,project_id) VALUES (?,?)");
                foreach ($projIds as $pid) { $ins->execute([$id,$pid]); }
                $pdo->commit();
                header('Location: diary.php?ok=1'); exit;
            } catch (Throwable $ex) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                $error = 'Could not save the meeting.';
                $editing = compact('id','title','meeting_date','start_time','end_time','location','attendees','agenda','minutes') + ['_p'=>$projIds];
                $editProjects = $projIds;
            }
        }
    } elseif ($action === 'delete' && $id > 0) {
        // FK ON CASCADE removes meeting_projects rows automatically
        $pdo->prepare("DELETE FROM meetings WHERE id=?")->execute([$id]);
        header('Location: diary.php?ok=2'); exit;
    }
}

// Load for editing
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM meetings WHERE id=?");
    $stmt->execute([(int)$_GET['edit']]);
    $editing = $stmt->fetch() ?: null;
    if ($editing) {
        $s = $pdo->prepare("SELECT project_id FROM meeting_projects WHERE meeting_id=?");
        $s->execute([(int)$editing['id']]);
        $editProjects = array_column($s->fetchAll(), 'project_id');
    }
}

// List meetings with attached projects (many-to-many)
$meetings = $pdo->query("
  SELECT m.*, GROUP_CONCAT(p.code ORDER BY p.code SEPARATOR '||') AS proj_codes,
            GROUP_CONCAT(p.name ORDER BY p.code SEPARATOR '||')  AS proj_names
  FROM meetings m
  LEFT JOIN meeting_projects mp ON mp.meeting_id = m.id
  LEFT JOIN projects p           ON p.id = mp.project_id
  GROUP BY m.id
  ORDER BY m.meeting_date DESC, m.start_time DESC
")->fetchAll();

$projects = $pdo->query("SELECT id, code, name FROM projects ORDER BY code")->fetchAll();

$ok = $_GET['ok'] ?? '';
$messages = [1=>'Meeting saved.', 2=>'Meeting deleted.'];

header_html('Diary', 'diary');
?>
<div class="page-title">
  <div><h1>Diary</h1><p>Record meetings and attach one or more projects to each entry.</p></div>
  <button class="btn btn-primary" onclick="openMeetingModal()">+ New meeting</button>
</div>

<?php if (isset($messages[(int)$ok])): ?>
  <div class="alert alert-ok"><?= e($messages[(int)$ok]) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
  <div class="alert alert-err"><?= e($error) ?></div>
<?php endif; ?>

<?php if (!$meetings): ?>
  <div class="card"><div class="card-body" style="color:var(--muted)">No meetings yet. Click “New meeting” to add one.</div></div>
<?php endif; ?>

<?php foreach ($meetings as $m):
  $codes = $m['proj_codes'] ? explode('||', $m['proj_codes']) : [];
  $names = $m['proj_names'] ? explode('||', $m['proj_names']) : [];
?>
<div class="meeting-row">
  <div class="top">
    <div>
      <h3><?= e($m['title']) ?></h3>
      <div class="meta">
        <span>📅 <?= e(fmt_date($m['meeting_date'])) ?></span>
        <?php if ($m['start_time']): ?><span>🕒 <?= e(date('H:i', strtotime($m['start_time']))) ?><?= $m['end_time'] ? ' – ' . e(date('H:i', strtotime($m['end_time']))) : '' ?></span><?php endif; ?>
        <?php if ($m['location']): ?><span>📍 <?= e($m['location']) ?></span><?php endif; ?>
      </div>
      <div style="margin-top:10px">
        <?php if ($codes): foreach ($codes as $i => $c): ?>
          <span class="pill"><?= e($c) ?> · <?= e($names[$i] ?? '') ?></span>
        <?php endforeach; else: ?>
          <span class="badge badge-neutral">No project attached</span>
        <?php endif; ?>
      </div>
      <?php if ($m['attendees']): ?><div class="body"><strong>Attendees:</strong> <?= e($m['attendees']) ?></div><?php endif; ?>
      <?php if ($m['agenda']):   ?><div class="body"><strong>Agenda:</strong> <?= e($m['agenda']) ?></div><?php endif; ?>
      <?php if ($m['minutes']):  ?><div class="body"><strong>Minutes:</strong> <?= e($m['minutes']) ?></div><?php endif; ?>
    </div>
    <div class="actions">
      <a class="btn btn-sm" href="diary.php?edit=<?= (int)$m['id'] ?>">Edit</a>
      <form method="post" action="diary.php" style="display:inline" onsubmit="return confirm('Delete this meeting?');">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
        <button class="btn btn-sm btn-danger" type="submit">Delete</button>
      </form>
    </div>
  </div>
</div>
<?php endforeach; ?>

<!-- ============ Meeting modal (create / edit) ============ -->
<div class="modal-overlay" id="meetModal" role="dialog" aria-modal="true" aria-labelledby="meetTitle">
  <div class="modal">
    <div class="modal-head">
      <h3 id="meetTitle"><?= $editing ? 'Edit meeting' : 'New meeting' ?></h3>
      <button type="button" class="close-x" onclick="closeMeetingModal()" aria-label="Close">&times;</button>
    </div>
    <form method="post" action="diary.php">
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" value="<?= (int)($editing['id'] ?? 0) ?>">
      <div class="modal-body">
        <div class="form-grid">
          <div class="full"><label>Title *</label><input name="title" value="<?= e($editing['title'] ?? '') ?>" required></div>
          <div><label>Date *</label><input type="date" name="meeting_date" value="<?= e($editing['meeting_date'] ?? '') ?>" required></div>
          <div><label>Location</label><input name="location" value="<?= e($editing['location'] ?? '') ?>"></div>
          <div><label>Start time</label><input type="time" name="start_time" value="<?= e($editing['start_time'] ?? '') ?>"></div>
          <div><label>End time</label><input type="time" name="end_time" value="<?= e($editing['end_time'] ?? '') ?>"></div>
          <div class="full"><label>Attendees</label><input name="attendees" value="<?= e($editing['attendees'] ?? '') ?>" placeholder="Comma-separated names"></div>
          <div class="full"><label>Agenda</label><textarea name="agenda"><?= e($editing['agenda'] ?? '') ?></textarea></div>
          <div class="full"><label>Minutes / Notes</label><textarea name="minutes"><?= e($editing['minutes'] ?? '') ?></textarea></div>
          <div class="full">
            <label>Attached projects <span style="font-weight:400;color:var(--muted)">(select one or more)</span></label>
            <div class="proj-pick" id="projPick">
              <?php if (!$projects): ?>
                <span style="color:var(--muted)">No projects yet. <a href="projects.php">Add one</a> first.</span>
              <?php endif; ?>
              <?php foreach ($projects as $p):
                $checked = in_array((int)$p['id'], $editProjects, true);
              ?>
                <label data-pid="<?= (int)$p['id'] ?>" <?= $checked ? 'class="checked"' : '' ?>>
                  <input type="checkbox" name="project_ids[]" value="<?= (int)$p['id'] ?>" <?= $checked ? 'checked' : '' ?> onchange="syncChip(this)">
                  <?= e($p['code']) ?> <small>· <?= e($p['name']) ?></small>
                </label>
              <?php endforeach; ?>
            </div>
            <div class="hint">A meeting can be linked to multiple projects.</div>
          </div>
        </div>
      </div>
      <div class="modal-foot">
        <a class="btn" href="diary.php">Cancel</a>
        <button type="submit" class="btn btn-primary"><?= $editing ? 'Save changes' : 'Create meeting' ?></button>
      </div>
    </form>
  </div>
</div>

<script>
function openMeetingModal(){ document.getElementById('meetModal').classList.add('is-open'); }
function closeMeetingModal(){ document.getElementById('meetModal').classList.remove('is-open'); }
function syncChip(cb){
  var lbl = cb.closest('label');
  lbl.classList.toggle('checked', cb.checked);
}
(function(){
  var m=document.getElementById('meetModal');
  m.addEventListener('click',function(e){ if(e.target===m) closeMeetingModal(); });
  document.addEventListener('keydown',function(e){ if(e.key==='Escape') closeMeetingModal(); });
  <?php if ($editing): ?> openMeetingModal(); <?php endif; ?>
})();
</script>
<?php footer_html(); ?>
