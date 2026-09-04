<?php
// Expects $pageTitle and $activeNav to be set before include.
$admin = current_admin();
$activeNav = $activeNav ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle ?? 'Admin') ?> | TEPAN Admin</title>
<link rel="icon" type="image/jpeg" href="<?= base_url('assets/images/TEPAN_logo.jpeg') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body>
<div class="admin-shell">
  <aside class="admin-sidebar" id="adminSidebar">
    <div class="brand-mini">
      <img src="<?= base_url('assets/images/TEPAN_logo.jpeg') ?>" alt="TEPAN Logo" class="brand-logo" style="width:36px;height:36px;">
      <span>TEPAN Admin</span>
    </div>
    <nav>
      <a href="<?= base_url('admin/index.php') ?>" class="<?= $activeNav === 'dashboard' ? 'active' : '' ?>">📊 Dashboard</a>
      <a href="<?= base_url('admin/journals.php') ?>" class="<?= $activeNav === 'journals' ? 'active' : '' ?>">📰 Journals</a>
      <a href="<?= base_url('admin/proceedings.php') ?>" class="<?= $activeNav === 'proceedings' ? 'active' : '' ?>">🗂️ Proceedings</a>
      <a href="<?= base_url('admin/messages.php') ?>" class="<?= $activeNav === 'messages' ? 'active' : '' ?>">✉️ Messages</a>
      <a href="<?= base_url('index.php') ?>" target="_blank">🔗 View Site</a>
      <a href="<?= base_url('admin/logout.php') ?>">🚪 Logout</a>
    </nav>
  </aside>
  <div class="admin-main">
    <div class="admin-topbar">
      <button class="nav-toggle" id="sidebarToggle" aria-label="Toggle menu"><span></span><span></span><span></span></button>
      <h2 style="margin:0;font-size:1.15rem;"><?= e($pageTitle ?? 'Admin') ?></h2>
      <div>Signed in as <strong><?= e($admin['name']) ?></strong></div>
    </div>
    <div class="admin-content">
