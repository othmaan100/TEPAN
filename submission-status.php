<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Submission Status';

$labels = [
    'submitted' => 'Submitted — awaiting editor screening',
    'under_review' => 'Under peer review',
    'revisions_requested' => 'Revisions requested',
    'accepted' => 'Accepted for publication',
    'rejected' => 'Not accepted',
    'withdrawn' => 'Withdrawn',
];

$submission = null;
$searched = false;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $searched = true;
    if (!csrf_verify()) {
        $error = 'Your session expired. Please try again.';
    } else {
        $ref = trim($_POST['reference'] ?? '');
        $email = trim($_POST['email'] ?? '');
        if ($ref === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Enter both your reference number and the email you submitted with.';
        } else {
            $stmt = db()->prepare("SELECT * FROM submissions WHERE reference = ? AND author_email = ? LIMIT 1");
            $stmt->execute([$ref, $email]);
            $submission = $stmt->fetch();
            if (!$submission) $error = 'No submission matches that reference and email.';
        }
    }
}

require __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
  <div class="container">
    <h1>Check Submission Status</h1>
    <p>Enter your reference number and the email address you submitted with.</p>
  </div>
</section>

<section class="section">
  <div class="container" style="max-width:640px;">
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

    <?php if ($submission): ?>
      <div class="form-card" style="max-width:100%;">
        <div class="pub-meta"><?= e($submission['reference']) ?></div>
        <h2 style="margin:.2em 0;font-size:1.2rem;"><?= e($submission['title']) ?></h2>
        <p><strong>Status:</strong> <?= e($labels[$submission['status']] ?? $submission['status']) ?></p>
        <p style="color:var(--text-muted);font-size:.86rem;">Submitted <?= format_date($submission['created_at']) ?>.</p>

        <?php if ($submission['decision_letter'] && in_array($submission['status'], ['revisions_requested','accepted','rejected'], true)): ?>
          <div class="alert" style="background:#eef3f9;border:1px solid var(--border);color:var(--text);white-space:pre-wrap;">
<?= e($submission['decision_letter']) ?>
          </div>
        <?php endif; ?>

        <?php if ($submission['status'] === 'revisions_requested'): ?>
          <p>Please email your revised manuscript and a point-by-point response to
            <a href="mailto:<?= e(CONTACT_EMAIL) ?>"><?= e(CONTACT_EMAIL) ?></a>, quoting your reference number.</p>
        <?php endif; ?>
      </div>
      <p style="margin-top:16px;"><a href="<?= base_url('submission-status.php') ?>">Check another submission</a></p>
    <?php else: ?>
      <div class="form-card" style="max-width:100%;">
        <form method="post">
          <?= csrf_field() ?>
          <div class="form-group">
            <label for="reference">Reference Number</label>
            <input type="text" id="reference" name="reference" required placeholder="TEPAN-2026-0001" value="<?= e($_POST['reference'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label for="email">Author Email</label>
            <input type="email" id="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>">
          </div>
          <button type="submit" class="btn btn-navy">Check Status</button>
        </form>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
