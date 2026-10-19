<?php
/**
 * Shared layout helpers: header() and footer().
 */
require_once __DIR__ . '/../config/db.php';

function header_html(string $title, string $active = ''): void
{
    $nav = [
        'index'    => ['Home', 'index.php'],
        'pc'       => ['Performance Contract', 'modules/pc.php'],
        'diary'    => ['Diary', 'modules/diary.php'],
        'projects' => ['Projects', 'modules/projects.php'],
    ];
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title) ?> &middot; Performance Monitor</title>
<link rel="stylesheet" href="assets/styles.css">
</head>
<body>
<header class="app-header">
  <div class="app-bar">
    <a href="index.php" class="brand">
      <span class="brand-mark">PM</span>
      <span class="brand-name">Performance Monitor</span>
    </a>
    <nav class="app-nav">
      <?php foreach ($nav as $key => [$label, $href]): ?>
        <a href="<?= $href ?>" class="<?= $active === $key ? 'is-active' : '' ?>"><?= e($label) ?></a>
      <?php endforeach; ?>
    </nav>
  </div>
</header>
<main class="app-main">
    <?php
}

function footer_html(): void
{
    ?>
</main>
<footer class="app-footer">
  <p>Performance Monitoring &copy; <?= date('Y') ?></p>
</footer>
</body>
</html>
    <?php
}
