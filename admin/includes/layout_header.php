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

      <div class="nav-section">Publishing</div>
      <a href="<?= base_url('admin/journals.php') ?>" class="<?= $activeNav === 'journals' ? 'active' : '' ?>">📰 Journals</a>
      <a href="<?= base_url('admin/submissions.php') ?>" class="<?= $activeNav === 'submissions' ? 'active' : '' ?>">📥 Submissions</a>
      <a href="<?= base_url('admin/board.php') ?>" class="<?= $activeNav === 'board' ? 'active' : '' ?>">👥 Editorial Board</a>
      <a href="<?= base_url('admin/pages.php') ?>" class="<?= $activeNav === 'pages' ? 'active' : '' ?>">📄 Policy Pages</a>
      <a href="<?= base_url('admin/resources.php') ?>" class="<?= $activeNav === 'resources' ? 'active' : '' ?>">📎 Author Resources</a>

      <div class="nav-section">Conferences</div>
      <a href="<?= base_url('admin/conferences.php') ?>" class="<?= $activeNav === 'conferences' ? 'active' : '' ?>">🎓 Editions &amp; CFP</a>
      <a href="<?= base_url('admin/proceedings.php') ?>" class="<?= $activeNav === 'proceedings' ? 'active' : '' ?>">🗂️ Proceedings</a>

      <div class="nav-section">Outreach</div>
      <a href="<?= base_url('admin/announcements.php') ?>" class="<?= $activeNav === 'announcements' ? 'active' : '' ?>">📢 Announcements</a>
      <a href="<?= base_url('admin/subscribers.php') ?>" class="<?= $activeNav === 'subscribers' ? 'active' : '' ?>">✉️ Subscribers</a>
      <a href="<?= base_url('admin/messages.php') ?>" class="<?= $activeNav === 'messages' ? 'active' : '' ?>">💬 Messages</a>

      <div class="nav-section">Account</div>
      <a href="<?= base_url('admin/account.php') ?>" class="<?= $activeNav === 'account' ? 'active' : '' ?>">👤 My Account</a>
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
