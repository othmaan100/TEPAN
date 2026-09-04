<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Contact';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if ($name === '') $errors[] = 'Please enter your name.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
        if ($message === '') $errors[] = 'Please enter a message.';

        if (!$errors) {
            $stmt = db()->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $subject, $message]);
            flash_set('success', 'Thank you, ' . $name . '. Your message has been received and we will respond soon.');
            header('Location: ' . base_url('contact.php'));
            exit;
        }
    }
}

require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
  <div class="container">
    <h1>Contact TEPAN</h1>
    <p>Questions about membership, journal submissions, or conference proceedings? Reach out below.</p>
  </div>
</section>

<section class="section">
  <div class="container" style="max-width:640px;">

    <?php if ($msg = flash_get('success')): ?>
      <div class="alert alert-success"><?= e($msg) ?></div>
    <?php endif; ?>
    <?php foreach ($errors as $err): ?>
      <div class="alert alert-error"><?= e($err) ?></div>
    <?php endforeach; ?>

    <div class="form-card">
      <form method="post" novalidate>
        <?= csrf_field() ?>
        <div class="form-row">
          <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" value="<?= e($_POST['name'] ?? '') ?>" required>
          </div>
          <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required>
          </div>
        </div>
        <div class="form-group">
          <label for="subject">Subject</label>
          <input type="text" id="subject" name="subject" value="<?= e($_POST['subject'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label for="message">Message</label>
          <textarea id="message" name="message" required><?= e($_POST['message'] ?? '') ?></textarea>
        </div>
        <button type="submit" class="btn btn-navy">Send Message</button>
      </form>
    </div>

    <div style="margin-top:32px;">
      <h3>Other Ways to Reach Us</h3>
      <p>Email: info@tepan.org.ng<br>Location: Abuja, Nigeria</p>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
