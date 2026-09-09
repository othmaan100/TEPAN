<?php
require_once __DIR__ . '/includes/functions.php';

$edition = null;
$slug = isset($_GET['slug']) ? preg_replace('/[^a-z0-9\-]/', '', strtolower($_GET['slug'])) : '';
if ($slug !== '') {
    $stmt = db()->prepare("SELECT * FROM conference_editions WHERE slug = ? AND is_published = 1 LIMIT 1");
    $stmt->execute([$slug]);
    $edition = $stmt->fetch();
}

if (!$edition || !$edition['registration_open']) {
    http_response_code(404);
    $pageTitle = 'Registration Unavailable';
    require __DIR__ . '/includes/header.php';
    echo '<section class="section container"><div class="empty-state"><div class="icon">🚫</div>'
        . '<p>Registration for this conference is not open.</p><a class="btn btn-navy btn-sm" href="' . base_url('conferences.php') . '">All conferences</a></div></section>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = 'Register — ' . $edition['name'];
$errors = [];
$done = false;
$reference = null;
$typeDefault = ($_GET['type'] ?? '') === 'presenter' ? 'presenter' : 'attendee';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $affiliation = trim($_POST['affiliation'] ?? '');
        $type = ($_POST['attendance_type'] ?? '') === 'presenter' ? 'presenter' : 'attendee';
        $paperTitle = trim($_POST['paper_title'] ?? '');
        $abstract = trim($_POST['abstract'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        if ($name === '') $errors[] = 'Please enter your full name.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
        if ($type === 'presenter' && $paperTitle === '') $errors[] = 'Presenters must provide a paper title.';
        if ($type === 'presenter' && $abstract === '') $errors[] = 'Presenters must provide an abstract.';

        if (!$errors) {
            $reference = next_reference('conference_registrations', 'reference', 'TEPAN-CONF');
            db()->prepare(
                "INSERT INTO conference_registrations
                 (edition_id, reference, name, email, phone, affiliation, attendance_type, paper_title, abstract, notes)
                 VALUES (?,?,?,?,?,?,?,?,?,?)"
            )->execute([
                $edition['id'], $reference, $name, $email, $phone ?: null, $affiliation ?: null,
                $type, ($type === 'presenter' ? $paperTitle : null), ($type === 'presenter' ? $abstract : null), $notes ?: null,
            ]);

            send_mail(
                $email,
                'Conference registration received (' . $reference . ')',
                "Hello $name,\n\nYour registration for " . $edition['name'] . " has been received.\n"
                . "Reference: $reference\nType: " . ucfirst($type) . "\n\n"
                . "Payment is arranged separately — see the registration details on the conference page.\n"
            );
            send_mail(CONTACT_EMAIL, 'New conference registration: ' . $reference,
                "$name <$email> registered for " . $edition['name'] . " as $type.");

            $done = true;
        }
    }
}

require __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
  <div class="container">
    <h1>Register</h1>
    <p><?= e($edition['name']) ?><?= $edition['city'] ? ' &middot; ' . e($edition['city']) : '' ?></p>
  </div>
</section>

<section class="section">
  <div class="container" style="max-width:660px;">
    <?php if ($done): ?>
      <div class="alert alert-success">
        Thank you, your registration has been received. Your reference is <strong><?= e($reference) ?></strong> &mdash;
        keep it for correspondence. A confirmation email has been sent (where mail is configured).
      </div>
      <a class="btn btn-navy btn-sm" href="<?= base_url('conference.php?slug=' . e($edition['slug'])) ?>">Back to conference</a>
    <?php else: ?>
      <?php foreach ($errors as $err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endforeach; ?>
      <div class="form-card" style="max-width:100%;">
        <form method="post">
          <?= csrf_field() ?>
          <div class="form-row">
            <div class="form-group">
              <label for="name">Full Name</label>
              <input type="text" id="name" name="name" required value="<?= e($_POST['name'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label for="email">Email</label>
              <input type="email" id="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>">
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label for="phone">Phone</label>
              <input type="text" id="phone" name="phone" value="<?= e($_POST['phone'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label for="affiliation">Affiliation</label>
              <input type="text" id="affiliation" name="affiliation" value="<?= e($_POST['affiliation'] ?? '') ?>">
            </div>
          </div>
          <div class="form-group">
            <label for="attendance_type">I am registering as</label>
            <select id="attendance_type" name="attendance_type" onchange="document.getElementById('presenterFields').hidden = this.value !== 'presenter';">
              <?php $sel = $_POST['attendance_type'] ?? $typeDefault; ?>
              <option value="attendee" <?= $sel === 'attendee' ? 'selected' : '' ?>>Attendee</option>
              <option value="presenter" <?= $sel === 'presenter' ? 'selected' : '' ?>>Presenter (submitting an abstract)</option>
            </select>
          </div>
          <div id="presenterFields" <?= ($_POST['attendance_type'] ?? $typeDefault) === 'presenter' ? '' : 'hidden' ?>>
            <div class="form-group">
              <label for="paper_title">Paper Title</label>
              <input type="text" id="paper_title" name="paper_title" value="<?= e($_POST['paper_title'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label for="abstract">Abstract</label>
              <textarea id="abstract" name="abstract"><?= e($_POST['abstract'] ?? '') ?></textarea>
            </div>
          </div>
          <div class="form-group">
            <label for="notes">Notes (dietary needs, accessibility, etc.)</label>
            <textarea id="notes" name="notes"><?= e($_POST['notes'] ?? '') ?></textarea>
          </div>
          <button type="submit" class="btn btn-navy">Submit Registration</button>
        </form>
      </div>
      <?php if ($edition['registration_info']): ?>
        <div class="prose" style="margin-top:24px;">
          <h3>Fees &amp; payment</h3>
          <?= $edition['registration_info'] ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
