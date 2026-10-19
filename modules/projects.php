<?php
require_once __DIR__ . '/../includes/layout.php';
$pdo = db();

// --- CRUD for projects ---
$editing = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $name   = trim($_POST['name']   ?? '');
    $code   = trim($_POST['code']   ?? '');
    $desc   = trim($_POST['description'] ?? '');
    $start  = $_POST['start_date'] ?: null;
    $end    = $_POST['end_date']   ?: null;
    $id     = (int)($_POST['id'] ?? 0);

    if ($action === 'save') {
        if ($name === '' || $code === '') {
            $error = 'Project name and code are required.';
        } else {
            try {
                if ($id > 0) {
                    $stmt = $pdo->prepare("UPDATE projects SET name=?,code=?,description=?,start_date=?,end_date=? WHERE id=?");
                    $stmt->execute([$name,$code,$desc,$start,$end,$id]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO projects (name,code,description,start_date,end_date) VALUES (?,?,?,?,?)");
                    $stmt->execute([$name,$code,$desc,$start,$end]);
                }
                header('Location: projects.php?ok=1'); exit;
            } catch (PDOException $ex) {
                $error = 'Could not save project (code may already exist).';
            }
        }
        $editing = ['id'=>$id,'name'=>$name,'code'=>$code,'description'=>$desc,'start_date'=>$start,'end_date'=>$end];
    } elseif ($action === 'delete' && $id > 0) {
        $pdo->prepare("DELETE FROM projects WHERE id=?")->execute([$id]);
        header('Location: projects.php?ok=2'); exit;
    }
}

if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id=?");
    $stmt->execute([(int)$_GET['edit']]);
    $editing = $stmt->fetch() ?: null;
}

$projects = $pdo->query("SELECT * FROM projects ORDER BY code, name")->fetchAll();
$ok = $_GET['ok'] ?? '';
$messages = [1=>'Project saved.', 2=>'Project deleted.'];

header_html('Projects', 'projects');
?>
<div class="page-title">
  <div><h1>Projects</h1><p>Maintain the projects used by Performance Contracts and the Diary.</p></div>
  <?php if (!$editing): ?><button class="btn btn-primary" onclick="document.getElementById('addBtn').click()">+ Add project</button><?php endif; ?>
</div>

<?php if (isset($messages[(int)$ok])): ?>
  <div class="alert alert-ok"><?= e($messages[(int)$ok]) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
  <div class="alert alert-err"><?= e($error) ?></div>
<?php endif; ?>

<?php if ($editing): ?>
<div class="card">
  <div class="card-head"><h2><?= $editing['id'] ? 'Edit project' : 'New project' ?></h2></div>
  <div class="card-body">
    <form method="post" action="projects.php">
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" value="<?= (int)($editing['id'] ?? 0) ?>">
      <div class="form-grid">
        <div><label>Project name *</label><input name="name" value="<?= e($editing['name'] ?? '') ?>" required></div>
        <div><label>Project code *</label><input name="code" value="<?= e($editing['code'] ?? '') ?>" required></div>
        <div><label>Start date</label><input type="date" name="start_date" value="<?= e($editing['start_date'] ?? '') ?>"></div>
        <div><label>End date</label><input type="date" name="end_date" value="<?= e($editing['end_date'] ?? '') ?>"></div>
        <div class="full"><label>Description</label><textarea name="description"><?= e($editing['description'] ?? '') ?></textarea></div>
      </div>
      <div class="form-actions">
        <a class="btn" href="projects.php">Cancel</a>
        <button class="btn btn-primary" type="submit">Save project</button>
      </div>
    </form>
  </div>
</div>
<?php else: ?>
<a id="addBtn" href="projects.php?edit=0" style="display:none"></a>
<?php endif; ?>

<div class="card">
  <div class="card-head"><h2>All projects (<?= count($projects) ?>)</h2></div>
  <div class="table-wrap">
    <table class="data">
      <thead><tr><th>Code</th><th>Name</th><th>Period</th><th>Description</th><th class="right">Actions</th></tr></thead>
      <tbody>
      <?php if (!$projects): ?>
        <tr><td colspan="5" class="center" style="color:var(--muted)">No projects yet.</td></tr>
      <?php endif; ?>
      <?php foreach ($projects as $p): ?>
        <tr>
          <td><span class="pill"><?= e($p['code']) ?></span></td>
          <td><?= e($p['name']) ?></td>
          <td><?= e(fmt_date($p['start_date'])) ?> &rarr; <?= e(fmt_date($p['end_date'])) ?></td>
          <td style="color:var(--muted)"><?= e(mb_strimwidth($p['description'] ?? '',0,80,'…')) ?></td>
          <td class="right">
            <a class="btn btn-sm" href="projects.php?edit=<?= (int)$p['id'] ?>">Edit</a>
            <form method="post" action="projects.php" style="display:inline" onsubmit="return confirm('Delete this project? Its KPIs and meeting links will also be removed.');">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
              <button class="btn btn-sm btn-danger" type="submit">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php footer_html(); ?>
