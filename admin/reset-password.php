<?php
require_once __DIR__ . '/../includes/functions.php';

$token = $_SERVER['REQUEST_METHOD'] === 'POST'
    ? ($_POST['token'] ?? '')
    : ($_GET['token'] ?? '');

$admin = password_reset_lookup((string)$token);
$error = null;

if (!$admin) {
    $pageTitle = 'Reset Link Invalid';
    ?>
    <!DOCTYPE html>
    <html lang="en"><head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Link Invalid | TEPAN Admin</title>
    <link rel="icon" type="image/jpeg" href="<?= base_url('assets/images/TEPAN_logo.jpeg') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
    </head><body>
    <div class="login-wrap"><div class="login-card">
      <div style="text-align:center;margin-bottom:16px;">
        <img src="<?= base_url('assets/images/TEPAN_logo.jpeg') ?>" alt="TEPAN Logo" class="brand-logo-lg" style="margin:0 auto;display:block;">
      </div>
      <h1>Link Expired or Invalid</h1>
      <div class="alert alert-error">This password reset link is invalid, already used, or has expired. Please request a new one.</div>
      <p style="text-align:center;margin-top:18px;">
        <a href="<?= base_url('admin/forgot-password.php') ?>">Request a new reset link</a>
      </p>
    </div></div>
    </body></html>
    <?php
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $error = 'Your session expired. Please try again.';
    } else {
        $new = $_POST['password'] ?? '';
        $confirm = $_POST['password_confirm'] ?? '';

        if (strlen($new) < 8) {
            $error = 'Password must be at least 8 characters.';
        } elseif ($new !== $confirm) {
            $error = 'The two passwords do not match.';
        } else {
            password_reset_complete((int)$admin['reset_id'], (int)$admin['id'], $new);
            flash_set('success', 'Your password has been reset. Please sign in with your new password.');
            header('Location: ' . base_url('admin/login.php'));
            exit;
        }
    }
}

$pageTitle = 'Choose a New Password';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Choose a New Password | TEPAN Admin</title>
<link rel="icon" type="image/jpeg" href="<?= base_url('assets/images/TEPAN_logo.jpeg') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body>
<div class="login-wrap">
  <div class="login-card">
    <div style="text-align:center;margin-bottom:16px;">
      <img src="<?= base_url('assets/images/TEPAN_logo.jpeg') ?>" alt="TEPAN Logo" class="brand-logo-lg" style="margin:0 auto;display:block;">
    </div>
    <h1>New Password</h1>
    <div class="sub">Setting a new password for <strong><?= e($admin['username']) ?></strong>.</div>

    <?php if ($error): ?>
      <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="token" value="<?= e($token) ?>">
      <div class="form-group">
        <label for="password">New Password</label>
        <input type="password" id="password" name="password" required autofocus minlength="8">
        <div class="hint">At least 8 characters.</div>
      </div>
      <div class="form-group">
        <label for="password_confirm">Confirm New Password</label>
        <input type="password" id="password_confirm" name="password_confirm" required minlength="8">
      </div>
      <button type="submit" class="btn btn-navy" style="width:100%;">Reset Password</button>
    </form>
  </div>
</div>
</body>
</html>
