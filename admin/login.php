<?php
require_once __DIR__ . '/../includes/functions.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: ' . base_url('admin/index.php'));
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $error = 'Your session expired. Please try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        $stmt = db()->prepare("SELECT * FROM admins WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['full_name'];
            $_SESSION['admin_username'] = $admin['username'];
            header('Location: ' . base_url('admin/index.php'));
            exit;
        }
        $error = 'Invalid username or password.';
    }
}

$pageTitle = 'Admin Login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login | TEPAN</title>
<link rel="icon" type="image/jpeg" href="<?= base_url('assets/images/TEPAN_logo.jpeg') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body>
<div class="login-wrap">
  <div class="login-card">
    <div style="text-align:center;margin-bottom:16px;">
      <img src="<?= base_url('assets/images/TEPAN_logo.jpeg') ?>" alt="TEPAN Logo" class="brand-logo-lg" style="margin:0 auto;display:block;">
    </div>
    <h1>TEPAN Admin</h1>
    <div class="sub">Sign in to manage journals and proceedings</div>

    <?php if ($error): ?>
      <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post">
      <?= csrf_field() ?>
      <div class="form-group">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required autofocus>
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
      </div>
      <button type="submit" class="btn btn-navy" style="width:100%;">Sign In</button>
    </form>
    <p style="text-align:center;margin-top:18px;"><a href="<?= base_url('index.php') ?>">&larr; Back to site</a></p>
  </div>
</div>
</body>
</html>
