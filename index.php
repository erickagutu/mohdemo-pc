<?php
require_once __DIR__ . '/includes/layout.php';

$pdo = db();
$projCount   = (int)$pdo->query('SELECT COUNT(*) FROM projects')->fetchColumn();
$kpiCount    = (int)$pdo->query('SELECT COUNT(*) FROM kpis')->fetchColumn();
$meetingCount= (int)$pdo->query('SELECT COUNT(*) FROM meetings')->fetchColumn();

// overall achievement across all KPI quarters
$ach = $pdo->query("
  SELECT
    COALESCE(SUM(q1_achieved+q2_achieved+q3_achieved+q4_achieved),0) AS achieved,
    NULLIF(SUM(q1_target+q2_target+q3_target+q4_target),0)           AS target
  FROM kpis
")->fetch();
$overall = ($ach['target'] !== null) ? round(($ach['achieved'] / $ach['target']) * 100, 1) : 0;

header_html('Dashboard', 'index');
?>
<div class="page-title">
  <div>
    <h1>Performance Monitoring Dashboard</h1>
    <p>Track performance contracts, KPIs and stakeholder meetings.</p>
  </div>
  <a class="btn btn-primary" href="modules/pc.php">Open Performance Contract</a>
</div>

<div class="stats">
  <div class="stat"><div class="label">Projects</div><div class="value"><?= $projCount ?></div><div class="delta">Active in the portfolio</div></div>
  <div class="stat"><div class="label">KPIs tracked</div><div class="value"><?= $kpiCount ?></div><div class="delta">Across all projects</div></div>
  <div class="stat"><div class="label">Meetings logged</div><div class="value"><?= $meetingCount ?></div><div class="delta">In the diary</div></div>
  <div class="stat"><div class="label">Overall achievement</div><div class="value"><?= e(fmt_num($overall)) ?>%</div><div class="delta">Cumulative across quarters</div></div>
</div>

<div class="card">
  <div class="card-head"><h2>Quick links</h2></div>
  <div class="card-body">
    <p><a href="modules/pc.php">Performance Contract</a> — KPI table and quarterly Gantt chart per project.</p>
    <p><a href="modules/diary.php">Diary</a> — record meetings and attach one or more projects to each.</p>
    <p><a href="modules/projects.php">Projects</a> — manage the projects referenced by contracts and meetings.</p>
  </div>
</div>
<?php footer_html(); ?>
