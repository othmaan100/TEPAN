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
  <div class="container">

    <?php if ($msg = flash_get('success')): ?>
      <div class="alert alert-success"><?= e($msg) ?></div>
    <?php endif; ?>
    <?php foreach ($errors as $err): ?>
      <div class="alert alert-error"><?= e($err) ?></div>
    <?php endforeach; ?>

    <div class="contact-grid">
      <div>
        <div class="form-card" style="max-width:100%;">
          <h2 style="font-size:1.35rem; margin-top:0; margin-bottom:8px;">Send Us a Message</h2>
          <p style="color:var(--text-muted); font-size:0.92rem; margin-bottom:20px;">
            Fill out the form below and our editorial team will get back to you promptly.
          </p>
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
      </div>

      <div>
        <div class="contact-info-card">
          <h2 style="font-size:1.35rem; margin-top:0; margin-bottom:8px;">Contact Information</h2>
          <p style="color:var(--text-muted); font-size:0.92rem; margin-bottom:24px;">
            Reach out directly for editorial queries, journal submissions, conference proceedings, or general association inquiries.
          </p>

          <div class="contact-card-list">
            <div class="contact-card-item">
              <div class="contact-icon-wrapper" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
              </div>
              <div>
                <span class="contact-card-title">Email</span>
                <a href="mailto:editoratepan@gmail.com" class="contact-card-link">editoratepan@gmail.com</a>
              </div>
            </div>

            <div class="contact-card-item">
              <div class="contact-icon-wrapper" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
              </div>
              <div>
                <span class="contact-card-title">Phone Numbers</span>
                <div class="contact-phone-group">
                  <a href="tel:07036120387" class="contact-card-link">07036120387</a>
                  <a href="tel:08036899034" class="contact-card-link">08036899034</a>
                  <a href="tel:08036290334" class="contact-card-link">08036290334</a>
                </div>
              </div>
            </div>

            <div class="contact-card-item">
              <div class="contact-icon-wrapper" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
              </div>
              <div>
                <span class="contact-card-title">Location</span>
                <span class="contact-card-text">Abuja, Nigeria</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
