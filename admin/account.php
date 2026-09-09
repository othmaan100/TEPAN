<?php
require_once __DIR__ . '/../includes/functions.php';
require __DIR__ . '/includes/auth.php';

$pageTitle = 'My Account';
$activeNav = 'account';

$me = current_admin();
$stmt = db()->prepare("SELECT * FROM admins WHERE id = ?");
$stmt->execute([$me['id']]);
// NB: not $admin — layout_header.php reassigns $admin = current_admin().
$acct = $stmt->fetch();
if (!$acct) { header('Location: ' . base_url('admin/logout.php')); exit; }

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $form = $_POST['form'] ?? '';

        if ($form === 'profile') {
            $fullName = trim($_POST['full_name'] ?? '');
            $email = trim($_POST['email'] ?? '');

            if ($fullName === '') $errors[] = 'Full name cannot be empty.';
            if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address, or leave it blank.';

            if (!$errors) {
                db()->prepare("UPDATE admins SET full_name = ?, email = ? WHERE id = ?")
                    ->execute([$fullName, ($email !== '' ? $email : null), $acct['id']]);
                $_SESSION['admin_name'] = $fullName;
                flash_set('success', 'Profile updated.');
                header('Location: ' . base_url('admin/account.php'));
                exit;
            }
        } elseif ($form === 'password') {
            $current = $_POST['current_password'] ?? '';
            $new = $_POST['new_password'] ?? '';
            $confirm = $_POST['new_password_confirm'] ?? '';

            if (!password_verify($current, $acct['password'])) {
                $errors[] = 'Your current password is incorrect.';
            } elseif (strlen($new) < 8) {
                $errors[] = 'New password must be at least 8 characters.';
            } elseif ($new !== $confirm) {
                $errors[] = 'The new passwords do not match.';
            } elseif ($new === $current) {
                $errors[] = 'The new password must be different from the current one.';
            }

            if (!$errors) {
                db()->prepare("UPDATE admins SET password = ? WHERE id = ?")
                    ->execute([password_hash($new, PASSWORD_DEFAULT), $acct['id']]);
                db()->prepare("DELETE FROM password_resets WHERE admin_id = ?")->execute([$acct['id']]);
                flash_set('success', 'Password changed successfully.');
                header('Location: ' . base_url('admin/account.php'));
                exit;
            }
        }
    }
}

require __DIR__ . '/includes/layout_header.php';
?>

<?php if ($msg = flash_get('success')): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>
<?php foreach ($errors as $err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endforeach; ?>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:24px;max-width:840px;">

  <div class="form-card" style="max-width:100%;">
    <h3 style="margin-top:0;">Profile</h3>
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="form" value="profile">
      <div class="form-group">
        <label for="username">Username</label>
        <input type="text" id="username" value="<?= e($acct['username']) ?>" disabled>
        <div class="hint">The username is fixed and cannot be changed here.</div>
      </div>
      <div class="form-group">
        <label for="full_name">Full Name</label>
        <input type="text" id="full_name" name="full_name" required value="<?= e($_POST['full_name'] ?? $acct['full_name']) ?>">
      </div>
      <div class="form-group">
        <label for="email">Recovery Email</label>
        <input type="email" id="email" name="email" value="<?= e($_POST['email'] ?? ($acct['email'] ?? '')) ?>">
        <div class="hint">Used for the &ldquo;forgot password&rdquo; link. Leave blank to disable email recovery.</div>
      </div>
      <button type="submit" class="btn btn-navy">Save Profile</button>
    </form>
  </div>

  <div class="form-card" style="max-width:100%;">
    <h3 style="margin-top:0;">Change Password</h3>
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="form" value="password">
      <div class="form-group">
        <label for="current_password">Current Password</label>
        <input type="password" id="current_password" name="current_password" required autocomplete="current-password">
      </div>
      <div class="form-group">
        <label for="new_password">New Password</label>
        <input type="password" id="new_password" name="new_password" required minlength="8" autocomplete="new-password">
        <div class="hint">At least 8 characters.</div>
      </div>
      <div class="form-group">
        <label for="new_password_confirm">Confirm New Password</label>
        <input type="password" id="new_password_confirm" name="new_password_confirm" required minlength="8" autocomplete="new-password">
      </div>
      <button type="submit" class="btn btn-navy">Update Password</button>
    </form>
  </div>

</div>

<?php require __DIR__ . '/includes/layout_footer.php'; ?>
