<?php
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Newsletter';
$message = null;
$devLink = null;
$isError = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $isError = true;
        $message = 'Your session expired. Please try again.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $name = trim($_POST['name'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $isError = true;
            $message = 'Please enter a valid email address.';
        } else {
            [$raw, $hash] = token_pair();
            $stmt = db()->prepare("SELECT id, confirmed, unsubscribed FROM subscribers WHERE email = ?");
            $stmt->execute([$email]);
            $existing = $stmt->fetch();

            if ($existing && $existing['confirmed'] && !$existing['unsubscribed']) {
                $message = 'You are already subscribed — thank you!';
            } else {
                if ($existing) {
                    db()->prepare("UPDATE subscribers SET name = ?, token_hash = ?, unsubscribed = 0, confirmed = 0 WHERE id = ?")
                        ->execute([$name ?: null, $hash, $existing['id']]);
                } else {
                    db()->prepare("INSERT INTO subscribers (email, name, token_hash) VALUES (?, ?, ?)")
                        ->execute([$email, $name ?: null, $hash]);
                }
                $link = abs_url('subscribe-confirm.php?token=' . $raw);
                $sent = send_mail(
                    $email,
                    'Confirm your subscription',
                    "Please confirm you want to receive updates from " . SITE_NAME . ".\n\n"
                    . "Confirm: " . $link . "\n\nIf this was not you, ignore this email."
                );
                $message = 'Almost done — check your inbox for a confirmation link to activate your subscription.';
                if (!$sent && MAIL_DEV_SHOW_LINKS) $devLink = $link;
            }
        }
    }
} else {
    header('Location: ' . base_url('announcements.php'));
    exit;
}

require __DIR__ . '/includes/header.php';
?>
<section class="section">
  <div class="container" style="max-width:640px;">
    <div class="form-card">
      <h1 style="font-size:1.4rem;margin-top:0;">Newsletter</h1>
      <div class="alert <?= $isError ? 'alert-error' : 'alert-success' ?>"><?= e($message) ?></div>
      <?php if ($devLink): ?>
        <div class="alert alert-error" style="word-break:break-all;">
          <strong>Local mode:</strong> no mail server, so confirm here:
          <p style="margin:8px 0 0;"><a href="<?= e($devLink) ?>"><?= e($devLink) ?></a></p>
        </div>
      <?php endif; ?>
      <a class="btn btn-navy btn-sm" href="<?= base_url('announcements.php') ?>">Back to announcements</a>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
