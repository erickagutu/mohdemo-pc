<?php
/**
 * PP-SDOMS site header — matches the production assets/style.css layout.
 *
 * Set $page_title and $active_tab before including this file.
 * If you already have your own includes/header.php on the server, keep yours.
 */
require_once __DIR__ . '/../config/db.php';
@session_start();

$nav = [
    'dashboard' => ['Dashboard',          '/index.php'],
    'pc'        => ['Performance Contract', '/modules/pc.php'],
    'diary'     => ['Project Diary',       '/modules/diary.php'],
];
$active_tab = $active_tab ?? '';
$page_title = $page_title ?? 'PP-SDOMS';
$user = get_current_user_data();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($page_title) ?></title>
<link rel="stylesheet" href="/assets/style.css">
</head>
<body>

<!-- Top Ministerial Header -->
<header class="top-header">
  <a href="/" class="brand-container">
    <div class="brand-text">
      <h1>State Department for Medical Services</h1>
      <div class="sub-title">Performance Planning &amp; Strategic Delivery Online Management System (PP-SDOMS)</div>
    </div>
  </a>
  <div class="header-user-actions">
    <div class="user-capsule">
      <div class="user-info">
        <span class="user-name"><?= htmlspecialchars($user['full_name'] ?? 'User') ?></span>
        <span class="user-role-badge"><?= htmlspecialchars($user['role'] ?? 'Staff') ?></span>
      </div>
    </div>
    <a href="/logout.php" class="btn-logout">⏻ Logout</a>
  </div>
</header>

<!-- Main Navbar -->
<nav class="main-nav">
  <ul class="nav-links">
    <?php foreach ($nav as $key => [$label, $href]): ?>
      <li class="nav-item">
        <a href="<?= $href ?>" class="nav-link<?= $active_tab === $key ? ' active' : '' ?>"><?= htmlspecialchars($label) ?></a>
      </li>
    <?php endforeach; ?>
  </ul>
</nav>

<!-- Core Values Banner -->
<div class="core-values-banner">"Quality Healthcare for All Kenyans — Innovation · Integrity · Service Excellence"</div>

<main class="app-container">
