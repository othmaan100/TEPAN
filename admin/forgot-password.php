<?php
require_once __DIR__ . '/../includes/functions.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: ' . base_url('admin/account.php'));
    exit;
}

$error = null;
$done = false;
$devLink = null;   // shown only when PASSWORD_RESET_DEV_SHOW_LINK is on

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $error = 'Your session expired. Please try again.';
    } else {
        $email = trim($_POST['email'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } else {
            $stmt = db()->prepare("SELECT * FROM admins WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $admin = $stmt->fetch();

            if ($admin) {
                $raw = password_reset_create((int)$admin['id']);
                $link = absolute_base_url() . '/admin/reset-password.php?token=' . $raw;
                $sent = password_reset_send_email($admin['email'], $admin['full_name'], $link);
                if (!$sent && PASSWORD_RESET_DEV_SHOW_LINK) {
                    $devLink = $link;
                }
            }
            // Always report the same outcome — never reveal whether an account exists.
            $done = true;
        }
    }
}

$pageTitle = 'Forgot Password';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Forgot Password | TEPAN Admin</title>
<link rel="icon" type="image/jpeg" href="<?= base_url('assets/images/TEPAN_logo.jpeg') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body>
<div class="login-wrap">
  <div class="login-card">
    <div style="text-align:center;margin-bottom:16px;">
      <img src="<?= base_url('assets/images/TEPAN_logo.jpeg') ?>" alt="TEPAN Logo" class="brand-logo-lg" style="margin:0 auto;display:block;">
    </div>
    <h1>Reset Admin Password</h1>

    <?php if ($done): ?>
      <div class="sub" style="margin-bottom:20px;">
        If that email matches an admin account, a password reset link has been sent. The link expires in
        <?= (int)PASSWORD_RESET_TTL_MINUTES ?> minutes.
      </div>

      <?php if ($devLink): ?>
        <div class="alert alert-error" style="text-align:left;">
          <strong>Local mode:</strong> no mail server is available, so the link is shown here.
          Open it to continue:
          <p style="word-break:break-all;margin:10px 0 0;">
            <a href="<?= e($devLink) ?>"><?= e($devLink) ?></a>
          </p>
        </div>
      <?php endif; ?>

      <p style="text-align:center;margin-top:18px;">
        <a href="<?= base_url('admin/login.php') ?>">&larr; Back to sign in</a>
      </p>
    <?php else: ?>
      <div class="sub">Enter the email address on your admin account and we will send a reset link.</div>

      <?php if ($error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
      <?php endif; ?>

      <form method="post">
        <?= csrf_field() ?>
        <div class="form-group">
          <label for="email">Admin Email Address</label>
          <input type="email" id="email" name="email" required autofocus value="<?= e($_POST['email'] ?? '') ?>">
        </div>
        <button type="submit" class="btn btn-navy" style="width:100%;">Send Reset Link</button>
      </form>
      <p style="text-align:center;margin-top:18px;">
        <a href="<?= base_url('admin/login.php') ?>">&larr; Back to sign in</a>
      </p>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
