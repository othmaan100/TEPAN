<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Unsubscribe';

$token = trim((string)($_GET['token'] ?? ''));
$ok = false;

if ($token !== '' && ctype_xdigit($token)) {
    $hash = hash('sha256', $token);
    $stmt = db()->prepare("SELECT id FROM subscribers WHERE token_hash = ? LIMIT 1");
    $stmt->execute([$hash]);
    $row = $stmt->fetch();
    if ($row) {
        db()->prepare("UPDATE subscribers SET unsubscribed = 1 WHERE id = ?")->execute([$row['id']]);
        $ok = true;
    }
}

require __DIR__ . '/includes/header.php';
?>
<section class="section">
  <div class="container" style="max-width:600px;">
    <div class="form-card" style="text-align:center;">
      <div class="icon" style="font-size:2.4rem;"><?= $ok ? '👋' : '⚠️' ?></div>
      <h1 style="font-size:1.4rem;"><?= $ok ? 'You have been unsubscribed' : 'Link not recognised' ?></h1>
      <p><?= $ok ? 'You will no longer receive emails from us. You can re-subscribe any time.' : 'This unsubscribe link is invalid.' ?></p>
      <a class="btn btn-navy btn-sm" href="<?= base_url('index.php') ?>">Home</a>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
