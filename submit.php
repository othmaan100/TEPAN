<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/upload.php';

$pageTitle = 'Submit a Manuscript';
$errors = [];
$done = false;
$reference = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $abstract = trim($_POST['abstract'] ?? '');
        $keywords = trim($_POST['keywords'] ?? '');
        $authorName = trim($_POST['author_name'] ?? '');
        $authorEmail = trim($_POST['author_email'] ?? '');
        $coAuthors = trim($_POST['co_authors'] ?? '');
        $affiliation = trim($_POST['affiliation'] ?? '');
        $coverLetter = trim($_POST['cover_letter'] ?? '');
        $confirmOriginal = isset($_POST['confirm_original']);

        if ($title === '') $errors[] = 'Manuscript title is required.';
        if (mb_strlen($abstract) < 40) $errors[] = 'Please provide an abstract (at least 40 characters).';
        if ($authorName === '') $errors[] = 'Corresponding author name is required.';
        if (!filter_var($authorEmail, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid corresponding author email is required.';
        if (!$confirmOriginal) $errors[] = 'You must confirm the work is original and not under review elsewhere.';

        $ms = handle_document_upload('manuscript_file', 'manuscripts', ['pdf', 'doc', 'docx', 'rtf', 'odt'], true, 20);
        if (!$ms['ok']) $errors[] = $ms['error'];

        $supp = handle_document_upload('supplementary_file', 'supplementary', ['pdf', 'doc', 'docx', 'zip', 'rtf', 'odt'], false, 20);
        if (!$supp['ok']) $errors[] = $supp['error'];

        if (!$errors) {
            $reference = next_reference('submissions', 'reference', 'TEPAN');
            db()->prepare(
                "INSERT INTO submissions
                 (reference, title, abstract, keywords, author_name, author_email, co_authors, affiliation,
                  cover_letter, manuscript_file, manuscript_size, supplementary_file, status)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?, 'submitted')"
            )->execute([
                $reference, $title, $abstract, $keywords ?: null, $authorName, $authorEmail,
                $coAuthors ?: null, $affiliation ?: null, $coverLetter ?: null,
                $ms['path'], $ms['size'], $supp['path'],
            ]);
            $sid = (int) db()->lastInsertId();
            db()->prepare("INSERT INTO submission_events (submission_id, event, detail) VALUES (?, 'Submitted', ?)")
                ->execute([$sid, 'Received from ' . $authorEmail]);

            send_mail(
                $authorEmail,
                'Manuscript received (' . $reference . ')',
                "Dear $authorName,\n\nYour manuscript \"$title\" has been received.\n"
                . "Reference: $reference\n\n"
                . "Track progress here: " . abs_url('submission-status.php') . "\n"
                . "(You will need your reference number and this email address.)\n"
            );
            send_mail(CONTACT_EMAIL, 'New manuscript: ' . $reference,
                "\"$title\" by $authorName <$authorEmail>. Review it in the admin portal.");

            $done = true;
        }
    }
}

require __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
  <div class="container">
    <h1>Submit a Manuscript</h1>
    <p>Original research for the peer-reviewed TEPAN journal. Please read the
      <a href="<?= base_url('page.php?slug=author-guidelines') ?>" style="color:#e4c987;">Author Guidelines</a> first.</p>
  </div>
</section>

<section class="section">
  <div class="container" style="max-width:720px;">
    <?php if ($done): ?>
      <div class="alert alert-success">
        Thank you. Your manuscript has been submitted and assigned reference
        <strong><?= e($reference) ?></strong>. Keep this number — you will need it (with your email) to
        <a href="<?= base_url('submission-status.php') ?>">check the status</a> of your submission.
      </div>
    <?php else: ?>
      <?php foreach ($errors as $err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endforeach; ?>

      <div class="alert" style="background:#eef3f9;border:1px solid var(--border);color:var(--text);">
        Peer review is <strong>double-anonymous</strong>. Upload a manuscript file with author names,
        affiliations and identifying references removed. Enter author details in the form below only.
      </div>

      <div class="form-card" style="max-width:100%;">
        <form method="post" enctype="multipart/form-data">
          <?= csrf_field() ?>

          <div class="form-group">
            <label for="title">Manuscript Title</label>
            <input type="text" id="title" name="title" required value="<?= e($_POST['title'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label for="abstract">Abstract</label>
            <textarea id="abstract" name="abstract" required style="min-height:140px;"><?= e($_POST['abstract'] ?? '') ?></textarea>
          </div>
          <div class="form-group">
            <label for="keywords">Keywords</label>
            <input type="text" id="keywords" name="keywords" placeholder="comma, separated, keywords" value="<?= e($_POST['keywords'] ?? '') ?>">
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="author_name">Corresponding Author</label>
              <input type="text" id="author_name" name="author_name" required value="<?= e($_POST['author_name'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label for="author_email">Author Email</label>
              <input type="email" id="author_email" name="author_email" required value="<?= e($_POST['author_email'] ?? '') ?>">
            </div>
          </div>
          <div class="form-group">
            <label for="co_authors">Co-authors (if any)</label>
            <input type="text" id="co_authors" name="co_authors" placeholder="Full names, comma separated" value="<?= e($_POST['co_authors'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label for="affiliation">Affiliation(s)</label>
            <input type="text" id="affiliation" name="affiliation" value="<?= e($_POST['affiliation'] ?? '') ?>">
          </div>

          <div class="form-group">
            <label for="manuscript_file">Manuscript File (anonymised)</label>
            <input type="file" id="manuscript_file" name="manuscript_file" required accept=".pdf,.doc,.docx,.rtf,.odt">
            <div class="hint">PDF or Word, max 20MB.</div>
          </div>
          <div class="form-group">
            <label for="supplementary_file">Supplementary File (optional)</label>
            <input type="file" id="supplementary_file" name="supplementary_file" accept=".pdf,.doc,.docx,.zip,.rtf,.odt">
            <div class="hint">Data, instruments or appendices. PDF / Word / ZIP, max 20MB.</div>
          </div>
          <div class="form-group">
            <label for="cover_letter">Cover Letter (optional)</label>
            <textarea id="cover_letter" name="cover_letter"><?= e($_POST['cover_letter'] ?? '') ?></textarea>
          </div>

          <div class="form-group checkbox-row">
            <input type="checkbox" id="confirm_original" name="confirm_original" <?= isset($_POST['confirm_original']) ? 'checked' : '' ?>>
            <label for="confirm_original" style="margin:0;">This work is original, is not published elsewhere, and is not under review by another journal.</label>
          </div>

          <button type="submit" class="btn btn-navy">Submit Manuscript</button>
        </form>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
