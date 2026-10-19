<?php
/**
 * Minimal site header. The original PP-SDOMS deployment ships its own
 * includes/header.php with full navigation, auth and styling; this file is a
 * lightweight stand-in so the modules render when deployed standalone.
 *
 * If you already have includes/header.php on your server, keep yours — the
 * modules only require the helpers defined in config/db.php and the
 * $page_title / $active_tab variables set before this file is included.
 */
require_once __DIR__ . '/../config/db.php';
@session_start();
$nav = [
    'pc'    => ['Performance Contract', '/modules/pc.php'],
    'diary' => ['Diary',                '/modules/diary.php'],
];
$active_tab = $active_tab ?? '';
$page_title = $page_title ?? 'PP-SDOMS';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($page_title) ?></title>
<link rel="stylesheet" href="/assets/styles.css">
</head>
<body>
<header class="app-header">
  <div class="app-bar">
    <a href="/" class="brand"><span class="brand-mark">PP</span><span class="brand-name">PP-SDOMS</span></a>
    <nav class="app-nav">
      <?php foreach ($nav as $key => [$label, $href]): ?>
        <a href="<?= $href ?>" class="<?= $active_tab === $key ? 'is-active' : '' ?>"><?= htmlspecialchars($label) ?></a>
      <?php endforeach; ?>
    </nav>
  </div>
</header>
<main class="app-main">
