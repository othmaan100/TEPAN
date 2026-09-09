<?php
require_once __DIR__ . '/includes/functions.php';

$token = trim((string)($_GET['token'] ?? ($_POST['token'] ?? '')));
$review = null;
$submission = null;

if ($token !== '' && ctype_xdigit($token)) {
    $hash = hash('sha256', $token);
    $stmt = db()->prepare(
        "SELECT r.*, s.reference, s.title, s.abstract, s.keywords, s.manuscript_file, s.supplementary_file, s.status AS sub_status
         FROM submission_reviews r JOIN submissions s ON s.id = r.submission_id
         WHERE r.token_hash = ? LIMIT 1"
    );
    $stmt->execute([$hash]);
    $review = $stmt->fetch();
}

/* ---- Serve the manuscript file only to the holder of a valid review token ---- */
if ($review && isset($_GET['download'])) {
    $which = $_GET['download'] === 'supp' ? 'supplementary_file' : 'manuscript_file';
    $rel = $review[$which] ?? null;
    $full = $rel ? __DIR__ . '/' . $rel : null;
    if (!$full || !is_file($full)) { http_response_code(404); die('File not available.'); }
    $ext = strtolower(pathinfo($rel, PATHINFO_EXTENSION)) ?: 'pdf';
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $review['reference'] . '-' . ($which === 'supp' ? 'supplementary' : 'manuscript') . '.' . $ext . '"');
    header('Content-Length: ' . filesize($full));
    readfile($full);
    exit;
}

$pageTitle = 'Peer Review';
$errors = [];
$flash = null;

if ($review && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'Your session expired. Please try again.';
    } elseif (($_POST['action'] ?? '') === 'decline') {
        db()->prepare("UPDATE submission_reviews SET status = 'declined' WHERE id = ?")->execute([$review['id']]);
        db()->prepare("INSERT INTO submission_events (submission_id, event, detail) VALUES (?, 'Review declined', ?)")
            ->execute([$review['submission_id'], $review['reviewer_name']]);
        send_mail(CONTACT_EMAIL, 'Review declined: ' . $review['reference'],
            $review['reviewer_name'] . ' declined to review ' . $review['reference'] . '.');
        $review['status'] = 'declined';
    } elseif ($review['status'] === 'invited') {
        $rec = $_POST['recommendation'] ?? '';
        $toAuthor = trim($_POST['comments_to_author'] ?? '');
        $toEditor = trim($_POST['comments_to_editor'] ?? '');
        $validRec = ['accept', 'minor_revisions', 'major_revisions', 'reject'];
        if (!in_array($rec, $validRec, true)) $errors[] = 'Please choose a recommendation.';
        if ($toAuthor === '') $errors[] = 'Please provide comments for the author.';

        if (!$errors) {
            db()->prepare(
                "UPDATE submission_reviews
                 SET status = 'completed', recommendation = ?, comments_to_author = ?, comments_to_editor = ?, completed_at = NOW()
                 WHERE id = ?"
            )->execute([$rec, $toAuthor, $toEditor ?: null, $review['id']]);
            db()->prepare("INSERT INTO submission_events (submission_id, event, detail) VALUES (?, 'Review completed', ?)")
                ->execute([$review['submission_id'], $review['reviewer_name'] . ' — ' . str_replace('_', ' ', $rec)]);
            send_mail(CONTACT_EMAIL, 'Review completed: ' . $review['reference'],
                $review['reviewer_name'] . ' recommends: ' . str_replace('_', ' ', $rec) . ' for ' . $review['reference'] . '.');
            $review['status'] = 'completed';
            $flash = 'Your review has been submitted. Thank you for supporting the journal.';
        }
    }
}

require __DIR__ . '/includes/header.php';
?>
<section class="section">
  <div class="container" style="max-width:720px;">

    <?php if (!$review): ?>
      <div class="empty-state"><div class="icon">⚠️</div><p>This review link is invalid or has been withdrawn.</p></div>

    <?php elseif ($review['status'] === 'completed'): ?>
      <div class="alert alert-success"><?= $flash ? e($flash) : 'You have already submitted this review. Thank you.' ?></div>

    <?php elseif ($review['status'] === 'declined'): ?>
      <div class="alert" style="background:#eef3f9;border:1px solid var(--border);color:var(--text);">
        You have declined this review. If this was a mistake, contact <a href="mailto:<?= e(CONTACT_EMAIL) ?>"><?= e(CONTACT_EMAIL) ?></a>.
      </div>

    <?php else: ?>
      <div class="pub-meta">Manuscript <?= e($review['reference']) ?></div>
      <h1 style="font-size:1.4rem;"><?= e($review['title']) ?></h1>
      <p style="white-space:pre-wrap;color:var(--text-muted);"><?= e($review['abstract']) ?></p>
      <?php if ($review['keywords']): ?><p><strong>Keywords:</strong> <?= e($review['keywords']) ?></p><?php endif; ?>

      <div class="pub-actions" style="margin:16px 0;">
        <a class="btn btn-gold btn-sm" href="<?= base_url('review.php?token=' . e($token) . '&download=ms') ?>">Download manuscript</a>
        <?php if ($review['supplementary_file']): ?>
          <a class="btn btn-navy btn-sm" href="<?= base_url('review.php?token=' . e($token) . '&download=supp') ?>">Download supplementary file</a>
        <?php endif; ?>
      </div>

      <?php foreach ($errors as $err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endforeach; ?>

      <div class="form-card" style="max-width:100%;">
        <h3 style="margin-top:0;">Your Review</h3>
        <form method="post">
          <?= csrf_field() ?>
          <input type="hidden" name="token" value="<?= e($token) ?>">
          <div class="form-group">
            <label for="recommendation">Recommendation</label>
            <select id="recommendation" name="recommendation" required>
              <option value="">— choose —</option>
              <option value="accept">Accept</option>
              <option value="minor_revisions">Accept with minor revisions</option>
              <option value="major_revisions">Major revisions required</option>
              <option value="reject">Reject</option>
            </select>
          </div>
          <div class="form-group">
            <label for="comments_to_author">Comments to the Author</label>
            <textarea id="comments_to_author" name="comments_to_author" required style="min-height:150px;"><?= e($_POST['comments_to_author'] ?? '') ?></textarea>
            <div class="hint">These are shared with the author (anonymously).</div>
          </div>
          <div class="form-group">
            <label for="comments_to_editor">Confidential Comments to the Editor</label>
            <textarea id="comments_to_editor" name="comments_to_editor"><?= e($_POST['comments_to_editor'] ?? '') ?></textarea>
            <div class="hint">Seen only by the editor.</div>
          </div>
          <button type="submit" class="btn btn-navy">Submit Review</button>
        </form>
        <form method="post" style="margin-top:12px;" data-confirm="Decline to review this manuscript?">
          <?= csrf_field() ?>
          <input type="hidden" name="token" value="<?= e($token) ?>">
          <input type="hidden" name="action" value="decline">
          <button type="submit" class="btn btn-sm btn-danger">Decline to review</button>
        </form>
      </div>
    <?php endif; ?>

  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
