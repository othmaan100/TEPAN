<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Subscription Confirmed';

$token = trim((string)($_GET['token'] ?? ''));
$ok = false;

if ($token !== '' && ctype_xdigit($token)) {
    $hash = hash('sha256', $token);
    $stmt = db()->prepare("SELECT id FROM subscribers WHERE token_hash = ? LIMIT 1");
    $stmt->execute([$hash]);
    $row = $stmt->fetch();
    if ($row) {
        db()->prepare("UPDATE subscribers SET confirmed = 1, confirmed_at = NOW(), unsubscribed = 0 WHERE id = ?")
            ->execute([$row['id']]);
        $ok = true;
    }
}

require __DIR__ . '/includes/header.php';
?>
<section class="section">
  <div class="container" style="max-width:600px;">
    <div class="form-card" style="text-align:center;">
      <?php if ($ok): ?>
        <div class="icon" style="font-size:2.4rem;">✅</div>
        <h1 style="font-size:1.4rem;">You're subscribed</h1>
        <p>You'll now get an email when a new journal issue or announcement is published.</p>
      <?php else: ?>
        <div class="icon" style="font-size:2.4rem;">⚠️</div>
        <h1 style="font-size:1.4rem;">Link not recognised</h1>
        <p>This confirmation link is invalid or has already been used.</p>
      <?php endif; ?>
      <a class="btn btn-navy btn-sm" href="<?= base_url('announcements.php') ?>">Announcements</a>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
