<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once __DIR__ . '/functions.php';
$pageTitle = $pageTitle ?? SITE_SHORT;
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
function nav_active(string $file): string
{
    global $currentPath;
    return basename($currentPath) === $file ? ' active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle) ?> | <?= e(SITE_SHORT) ?></title>
<meta name="description" content="Technology Education Practitioners Association of Nigeria (TEPAN) — conference journals, publication archive, and conference proceedings.">
<link rel="icon" type="image/jpeg" href="<?= base_url('assets/images/TEPAN_logo.jpeg') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body>
<a href="#main-content" class="skip-link">Skip to content</a>

<header class="site-header">
  <div class="topbar">
    <div class="container topbar-inner">
      <span>Advancing Technology Education Across Nigeria</span>
      <span class="topbar-links">
        <a href="<?= base_url('contact.php') ?>">Contact</a>
        <a href="<?= base_url('admin/login.php') ?>">Admin Login</a>
      </span>
    </div>
  </div>
  <div class="container navbar">
    <a href="<?= base_url('index.php') ?>" class="brand">
      <img src="<?= base_url('assets/images/TEPAN_logo.jpeg') ?>" alt="TEPAN Logo" class="brand-logo">
      <span class="brand-text">
        <strong>TEPAN</strong>
        <small>Technology Education Practitioners Association of Nigeria</small>
      </span>
    </a>
    <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation" aria-expanded="false">
      <span></span><span></span><span></span>
    </button>
    <nav class="main-nav" id="mainNav">
      <a href="<?= base_url('index.php') ?>" class="<?= nav_active('index.php') ?>">Home</a>
      <div class="nav-dropdown">
        <a href="<?= base_url('journals.php') ?>" class="<?= nav_active('journals.php') ?>">Journals ▾</a>
        <div class="dropdown-menu">
          <a href="<?= base_url('journals.php?view=current') ?>">Current Issue</a>
          <a href="<?= base_url('journals.php?view=volumes') ?>">Browse by Volume</a>
          <a href="<?= base_url('journals.php?view=years') ?>">Browse by Year</a>
        </div>
      </div>
      <a href="<?= base_url('proceedings.php') ?>" class="<?= nav_active('proceedings.php') ?>">Conference Proceedings</a>
      <a href="<?= base_url('about.php') ?>" class="<?= nav_active('about.php') ?>">About</a>
      <a href="<?= base_url('contact.php') ?>" class="<?= nav_active('contact.php') ?>">Contact</a>
    </nav>
  </div>
</header>
<main id="main-content">
