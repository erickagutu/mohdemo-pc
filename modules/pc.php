<?php
require_once __DIR__ . '/../includes/layout.php';
$pdo = db();

// ---- Save KPI (add / edit) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'kpi') {
    $id        = (int)($_POST['id'] ?? 0);
    $project_id= (int)($_POST['project_id'] ?? 0);
    $kpi       = trim($_POST['kpi_name'] ?? '');
    $sub       = trim($_POST['sub_kpi_name'] ?? '') ?: null;
    $weight    = (float)($_POST['weight'] ?? 0);
    $unit      = trim($_POST['unit'] ?? '') ?: null;
    $q = function($q, $k) { return (float)($_POST[$q . '_' . $k] ?? 0); };

    if ($project_id <= 0 || $kpi === '') {
        $error = 'Project and KPI name are required.';
    } else {
        try {
            if ($id > 0) {
                $stmt = $pdo->prepare("UPDATE kpis SET project_id=?,kpi_name=?,sub_kpi_name=?,weight=?,unit=?,
                  q1_target=?,q1_achieved=?,q2_target=?,q2_achieved=?,q3_target=?,q3_achieved=?,q4_target=?,q4_achieved=?
                  WHERE id=?");
                $stmt->execute([$project_id,$kpi,$sub,$weight,$unit,
                  $q('q1','target'),$q('q1','achieved'),
                  $q('q2','target'),$q('q2','achieved'),
                  $q('q3','target'),$q('q3','achieved'),
                  $q('q4','target'),$q('q4','achieved'),
                  $id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO kpis
                  (project_id,kpi_name,sub_kpi_name,weight,unit,
                   q1_target,q1_achieved,q2_target,q2_achieved,q3_target,q3_achieved,q4_target,q4_achieved)
                  VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
                $stmt->execute([$project_id,$kpi,$sub,$weight,$unit,
                  $q('q1','target'),$q('q1','achieved'),
                  $q('q2','target'),$q('q2','achieved'),
                  $q('q3','target'),$q('q3','achieved'),
                  $q('q4','target'),$q('q4','achieved')]);
            }
            header("Location: pc.php?project_id={$project_id}&ok=1"); exit;
        } catch (PDOException $ex) {
            $error = 'Could not save KPI.';
        }
    }
}

// ---- Delete KPI ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'kpi_delete') {
    $id = (int)($_POST['id'] ?? 0);
    $pid= (int)($_POST['project_id'] ?? 0);
    $pdo->prepare("DELETE FROM kpis WHERE id=?")->execute([$id]);
    header("Location: pc.php?project_id={$pid}&ok=2"); exit;
}

$projects = $pdo->query("SELECT id, code, name FROM projects ORDER BY code")->fetchAll();
$pid = isset($_GET['project_id']) ? (int)$_GET['project_id'] : (int)($_POST['project_id'] ?? 0);
if ($pid <= 0 && $projects) { $pid = (int)$projects[0]['id']; }

// project filter select
$proj = null;
if ($pid > 0) {
    $s = $pdo->prepare("SELECT * FROM projects WHERE id=?");
    $s->execute([$pid]);
    $proj = $s->fetch();
}

// KPIs grouped for this project
$kpis = [];
if ($proj) {
    $s = $pdo->prepare("SELECT * FROM kpis WHERE project_id=? ORDER BY id");
    $s->execute([$pid]);
    foreach ($s->fetchAll() as $r) {
        $kpis[$r['kpi_name']][] = $r;
    }
}

$editingKpi = null;
if (isset($_GET['edit_kpi'])) {
    $s = $pdo->prepare("SELECT * FROM kpis WHERE id=?");
    $s->execute([(int)$_GET['edit_kpi']]);
    $editingKpi = $s->fetch() ?: null;
}

// achievement level helper
function level(float $ach, float $tgt): string {
    if ($tgt <= 0) return 'none';
    $r = $ach / $tgt;
    if ($r >= 0.95) return 'full';
    if ($r >= 0.50) return 'partial';
    return 'none';
}
function levelLabel(string $l): string {
    return ['none'=>'Not started','partial'=>'Partially achieved','full'=>'Achieved'][$l] ?? $l;
}
function levelBadge(string $l): string {
    $map = ['none'=>'badge-error','partial'=>'badge-warning','full'=>'badge-success'];
    return '<span class="badge ' . ($map[$l] ?? 'badge-neutral') . '">' . levelLabel($l) . '</span>';
}

$ok = $_GET['ok'] ?? '';
$messages = [1=>'KPI saved.', 2=>'KPI deleted.'];

header_html('Performance Contract', 'pc');
?>
<div class="page-title">
  <div>
    <h1>Performance Contract</h1>
    <p>KPIs and quarterly achievement per project, with a Gantt view of progression.</p>
  </div>
  <form method="get" action="pc.php" style="display:flex;gap:8px;align-items:center">
    <select name="project_id" onchange="this.form.submit()" style="width:auto">
      <?php foreach ($projects as $p): ?>
        <option value="<?= (int)$p['id'] ?>" <?= $pid===(int)$p['id']?'selected':'' ?>><?= e($p['code']) ?> — <?= e($p['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </form>
</div>

<?php if (isset($messages[(int)$ok])): ?>
  <div class="alert alert-ok"><?= e($messages[(int)$ok]) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
  <div class="alert alert-err"><?= e($error) ?></div>
<?php endif; ?>

<?php if (!$proj): ?>
  <div class="card"><div class="card-body" style="color:var(--muted)">No projects available. Add one first in <a href="projects.php">Projects</a>.</div></div>
<?php else: ?>

<!-- ============ KPI TABLE (TOP) ============ -->
<div class="card">
  <div class="card-head">
    <div>
      <h2>Performance Contract — KPIs and Sub-KPIs</h2>
      <div class="sub"><?= e($proj['code']) ?> · <?= e($proj['name']) ?> · <?= e(fmt_date($proj['start_date'])) ?> → <?= e(fmt_date($proj['end_date'])) ?></div>
    </div>
    <button class="btn btn-primary" onclick="openKpiModal()">+ Add KPI</button>
  </div>
  <div class="table-wrap">
    <table class="data">
      <thead>
        <tr>
          <th>KPI / Sub-KPI</th><th class="right">Weight</th><th>Unit</th>
          <th class="center">Q1<br><small>Tgt / Ach</small></th>
          <th class="center">Q2<br><small>Tgt / Ach</small></th>
          <th class="center">Q3<br><small>Tgt / Ach</small></th>
          <th class="center">Q4<br><small>Tgt / Ach</small></th>
          <th class="center">Overall</th>
          <th class="right">Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php if (!$kpis): ?>
        <tr><td colspan="9" class="center" style="color:var(--muted)">No KPIs yet. Click “Add KPI” to create one.</td></tr>
      <?php endif; ?>
      <?php
      $qLabels = ['q1','q2','q3','q4'];
      foreach ($kpis as $kname => $rows):
        $first = true;
        foreach ($rows as $row):
          $totalT = 0; $totalA = 0;
          foreach ($qLabels as $q) { $totalT += (float)$row[$q.'_target']; $totalA += (float)$row[$q.'_achieved']; }
          $overall = $totalT > 0 ? round(($totalA/$totalT)*100,1) : 0;
          $ovLevel = level($totalA, $totalT);
      ?>
        <tr class="<?= $first ? 'group-row' : '' ?>">
          <td>
            <?php if ($first): ?><strong><?= e($kname) ?></strong><?php if ($row['sub_kpi_name']): ?><br><span style="font-weight:400;color:var(--muted)">└ <?= e($row['sub_kpi_name']) ?></span><?php endif; ?>
            <?php else: ?><span style="color:var(--muted)">└ <?= e($row['sub_kpi_name'] ?: '—') ?></span><?php endif; ?>
          </td>
          <td class="right"><?= fmt_num($row['weight']) ?></td>
          <td><?= e($row['unit'] ?? '—') ?></td>
          <?php foreach ($qLabels as $q):
            $t = (float)$row[$q.'_target']; $a = (float)$row[$q.'_achieved'];
            $lvl = level($a,$t); $pct = $t>0?min(100,round($a/$t*100)):0;
          ?>
          <td class="center">
            <div style="font-size:12px;color:var(--neutral-700)"><?= fmt_num($t) ?> / <?= fmt_num($a) ?></div>
            <div class="ach-bar <?= $pct>=100?'over':'' ?>" style="margin:5px auto 0"><span style="width:<?= $pct ?>%"></span></div>
            <div style="margin-top:4px"><?= levelBadge($lvl) ?></div>
          </td>
          <?php endforeach; ?>
          <td class="center">
            <div style="font-weight:600;color:var(--primary-700)"><?= fmt_num($overall) ?>%</div>
            <?= levelBadge($ovLevel) ?>
          </td>
          <td class="right">
            <a class="btn btn-sm" href="pc.php?project_id=<?= $pid ?>&edit_kpi=<?= (int)$row['id'] ?>">Edit</a>
            <form method="post" action="pc.php" style="display:inline" onsubmit="return confirm('Delete this KPI line?');">
              <input type="hidden" name="form" value="kpi_delete">
              <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
              <input type="hidden" name="project_id" value="<?= $pid ?>">
              <button class="btn btn-sm btn-danger" type="submit">Delete</button>
            </form>
          </td>
        </tr>
      <?php $first = false; endforeach; endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- ============ TOGGLE BUTTON (Gantt chart) ============ -->
<div class="gantt-toggle">
  <button type="button" id="ganttToggle" class="btn btn-primary" aria-expanded="false" aria-controls="ganttSection">
    <span id="ganttToggleText">VIEW GANTT CHART</span>
  </button>
</div>

<!-- ============ GANTT CHART (BOTTOM, hidden by default) ============ -->
<div class="gantt-section" id="ganttSection" aria-hidden="true">
  <div class="card">
    <div class="card-head">
      <div>
        <h2>Performance Contract Gantt Chart — Quarterly Progression &amp; Achievement Levels</h2>
        <div class="sub">Stripped bands mark each quarter; dots show achievement per quarter.</div>
      </div>
    </div>
    <div class="card-body">
      <?php if (!$kpis): ?>
        <p style="color:var(--muted)">No KPIs to display.</p>
      <?php else: ?>
      <div class="gantt-wrap">
        <div class="gantt">
          <div class="gh">KPI / Sub-KPI</div>
          <div class="gh q">Q1</div><div class="gh q">Q2</div><div class="gh q">Q3</div><div class="gh q">Q4</div>

          <?php foreach ($kpis as $kname => $rows):
            $first = true;
            foreach ($rows as $row):
          ?>
            <div class="glabel">
              <?php if ($first): ?><strong><?= e($kname) ?></strong><?php else: ?><span style="color:var(--muted)">└</span> <?php endif; ?>
              <?= e($row['sub_kpi_name'] ?: '—') ?>
              <small>Unit: <?= e($row['unit'] ?? '—') ?></small>
            </div>
            <?php foreach ($qLabels as $q):
              $t = (float)$row[$q.'_target']; $a = (float)$row[$q.'_achieved'];
              $lvl = level($a,$t);
            ?>
            <div class="gcell striped">
              <span class="gdot <?= $lvl ?>" title="<?= e(levelLabel($lvl)) ?>"></span>
              <span class="gval"><?= fmt_num($a) ?>/<?= fmt_num($t) ?></span>
            </div>
            <?php endforeach; ?>
          <?php $first = false; endforeach; endforeach; ?>
        </div>
      </div>
      <div class="gantt-legend">
        <span><span class="gdot none"></span> Not started (&lt;50%)</span>
        <span><span class="gdot partial"></span> Partially achieved (50–94%)</span>
        <span><span class="gdot full"></span> Achieved (≥95%)</span>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
(function(){
  var btn = document.getElementById('ganttToggle');
  var sec = document.getElementById('ganttSection');
  var txt = document.getElementById('ganttToggleText');
  btn.addEventListener('click', function(){
    var open = sec.classList.toggle('is-open');
    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    sec.setAttribute('aria-hidden', open ? 'false' : 'true');
    txt.textContent = open ? 'HIDE GANTT CHART' : 'VIEW GANTT CHART';
  });
})();
</script>

<!-- ============ KPI MODAL (add / edit) ============ -->
<div class="modal-overlay" id="kpiModal" role="dialog" aria-modal="true" aria-labelledby="kpiModalTitle">
  <div class="modal">
    <div class="modal-head">
      <h3 id="kpiModalTitle"><?= $editingKpi ? 'Edit KPI' : 'Add KPI' ?></h3>
      <button type="button" class="close-x" onclick="closeKpiModal()" aria-label="Close">&times;</button>
    </div>
    <form method="post" action="pc.php">
      <input type="hidden" name="form" value="kpi">
      <input type="hidden" name="id" value="<?= $editingKpi ? (int)$editingKpi['id'] : 0 ?>">
      <input type="hidden" name="project_id" value="<?= $pid ?>">
      <div class="modal-body">
        <div class="form-grid">
          <div class="full"><label>KPI name *</label><input name="kpi_name" value="<?= e($editingKpi['kpi_name'] ?? '') ?>" required></div>
          <div class="full"><label>Sub-KPI name</label><input name="sub_kpi_name" value="<?= e($editingKpi['sub_kpi_name'] ?? '') ?>" placeholder="Optional"></div>
          <div><label>Weight (%)</label><input type="number" step="0.01" name="weight" value="<?= e($editingKpi['weight'] ?? '0') ?>"></div>
          <div><label>Unit</label><input name="unit" value="<?= e($editingKpi['unit'] ?? '') ?>" placeholder="e.g. km, no, m"></div>
        </div>
        <h4 style="margin:18px 0 8px;color:var(--primary-700)">Quarterly targets &amp; achievements</h4>
        <div class="form-grid">
          <?php foreach ($qLabels as $q): $qt=$q.'_target'; $qa=$q.'_achieved'; ?>
          <div>
            <label><?= strtoupper($q) ?> target</label>
            <input type="number" step="0.01" name="<?= $q ?>_target" value="<?= e($editingKpi[$qt] ?? '0') ?>">
          </div>
          <div>
            <label><?= strtoupper($q) ?> achieved</label>
            <input type="number" step="0.01" name="<?= $q ?>_achieved" value="<?= e($editingKpi[$qa] ?? '0') ?>">
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn" onclick="closeKpiModal()">Cancel</button>
        <button type="submit" class="btn btn-primary"><?= $editingKpi ? 'Save changes' : 'Add KPI' ?></button>
      </div>
    </form>
  </div>
</div>

<script>
function openKpiModal(){ var m=document.getElementById('kpiModal'); m.classList.add('is-open'); }
function closeKpiModal(){ var m=document.getElementById('kpiModal'); m.classList.remove('is-open');
  // return focus to the trigger
}
(function(){
  var m=document.getElementById('kpiModal');
  m.addEventListener('click',function(e){ if(e.target===m) closeKpiModal(); });
  document.addEventListener('keydown',function(e){ if(e.key==='Escape') closeKpiModal(); });
  <?php if ($editingKpi): ?> openKpiModal(); <?php endif; ?>
})();
</script>
<?php endif; ?>
<?php footer_html(); ?>
