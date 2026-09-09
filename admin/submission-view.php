<?php
require_once __DIR__ . '/../includes/functions.php';
require __DIR__ . '/includes/auth.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare("SELECT * FROM submissions WHERE id = ?");
$stmt->execute([$id]);
$s = $stmt->fetch();
if (!$s) { header('Location: ' . base_url('admin/submissions.php')); exit; }

$pageTitle = 'Submission ' . $s['reference'];
$activeNav = 'submissions';
$errors = [];

/* ---- Serve manuscript / supplementary file to the logged-in editor ---- */
if (isset($_GET['file'])) {
    $col = $_GET['file'] === 'supp' ? 'supplementary_file' : 'manuscript_file';
    $rel = $s[$col] ?? null;
    $full = $rel ? __DIR__ . '/../' . $rel : null;
    if (!$full || !is_file($full)) { http_response_code(404); die('File not available.'); }
    $ext = strtolower(pathinfo($rel, PATHINFO_EXTENSION)) ?: 'pdf';
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $s['reference'] . '-' . ($col === 'supplementary_file' ? 'supplementary' : 'manuscript') . '.' . $ext . '"');
    header('Content-Length: ' . filesize($full));
    readfile($full);
    exit;
}

$recLabels = ['accept' => 'Accept', 'minor_revisions' => 'Minor revisions', 'major_revisions' => 'Major revisions', 'reject' => 'Reject'];
$statusLabels = [
    'submitted' => 'Submitted', 'under_review' => 'Under review', 'revisions_requested' => 'Revisions requested',
    'accepted' => 'Accepted', 'rejected' => 'Rejected', 'withdrawn' => 'Withdrawn',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'notes') {
            db()->prepare("UPDATE submissions SET editor_notes = ? WHERE id = ?")
                ->execute([trim($_POST['editor_notes'] ?? ''), $s['id']]);
            flash_set('success', 'Notes saved.');

        } elseif ($action === 'status') {
            $new = $_POST['status'] ?? '';
            if (isset($statusLabels[$new])) {
                db()->prepare("UPDATE submissions SET status = ? WHERE id = ?")->execute([$new, $s['id']]);
                db()->prepare("INSERT INTO submission_events (submission_id, event, detail) VALUES (?, 'Status changed', ?)")
                    ->execute([$s['id'], $statusLabels[$new]]);
                flash_set('success', 'Status updated.');
            }

        } elseif ($action === 'add_reviewer') {
            $rn = trim($_POST['reviewer_name'] ?? '');
            $re = trim($_POST['reviewer_email'] ?? '');
            $due = trim($_POST['due_at'] ?? '') ?: null;
            if ($rn === '' || !filter_var($re, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Reviewer name and a valid email are required.';
            } else {
                [$raw, $hash] = token_pair();
                db()->prepare("INSERT INTO submission_reviews (submission_id, reviewer_name, reviewer_email, token_hash, due_at) VALUES (?,?,?,?,?)")
                    ->execute([$s['id'], $rn, $re, $hash, $due]);
                if ($s['status'] === 'submitted') {
                    db()->prepare("UPDATE submissions SET status = 'under_review' WHERE id = ?")->execute([$s['id']]);
                }
                db()->prepare("INSERT INTO submission_events (submission_id, event, detail) VALUES (?, 'Reviewer invited', ?)")
                    ->execute([$s['id'], $rn]);
                $link = abs_url('review.php?token=' . $raw);
                send_mail($re, 'Invitation to review a manuscript (' . $s['reference'] . ')',
                    "Dear $rn,\n\nYou are invited to review a manuscript for the " . SITE_NAME . " journal.\n\n"
                    . "Open this link to see the manuscript and submit your review:\n$link\n\n"
                    . ($due ? "Requested by: $due\n\n" : "")
                    . "Thank you for supporting peer review.\n");
                flash_set('success', 'Reviewer added. Review link: ' . $link);
            }

        } elseif ($action === 'remove_reviewer') {
            db()->prepare("DELETE FROM submission_reviews WHERE id = ? AND submission_id = ?")
                ->execute([(int)($_POST['review_id'] ?? 0), $s['id']]);
            flash_set('success', 'Reviewer removed.');

        } elseif ($action === 'decision') {
            $dec = $_POST['decision'] ?? '';
            $letter = trim($_POST['decision_letter'] ?? '');
            $map = ['accept' => 'accepted', 'minor_revisions' => 'revisions_requested', 'major_revisions' => 'revisions_requested', 'reject' => 'rejected'];
            if (!isset($map[$dec])) {
                $errors[] = 'Choose a decision.';
            } elseif ($letter === '') {
                $errors[] = 'Write a decision letter to the author.';
            } else {
                db()->prepare("UPDATE submissions SET decision = ?, decision_letter = ?, status = ?, decided_at = NOW() WHERE id = ?")
                    ->execute([$dec, $letter, $map[$dec], $s['id']]);
                db()->prepare("INSERT INTO submission_events (submission_id, event, detail) VALUES (?, 'Editor decision', ?)")
                    ->execute([$s['id'], $recLabels[$dec]]);
                send_mail($s['author_email'], 'Decision on your manuscript (' . $s['reference'] . ')',
                    "Dear " . $s['author_name'] . ",\n\nRe: \"" . $s['title'] . "\" (" . $s['reference'] . ")\n\n"
                    . "Decision: " . $recLabels[$dec] . "\n\n" . $letter . "\n\n"
                    . "You can also view this on the submission status page: " . abs_url('submission-status.php') . "\n");
                flash_set('success', 'Decision recorded and emailed to the author.');
            }
        }

        if (!$errors) { header('Location: ' . base_url('admin/submission-view.php?id=' . $s['id'])); exit; }
    }
}

$reviews = db()->prepare("SELECT * FROM submission_reviews WHERE submission_id = ? ORDER BY invited_at");
$reviews->execute([$s['id']]);
$reviews = $reviews->fetchAll();

$events = db()->prepare("SELECT * FROM submission_events WHERE submission_id = ? ORDER BY created_at DESC");
$events->execute([$s['id']]);
$events = $events->fetchAll();

require __DIR__ . '/includes/layout_header.php';
?>
<?php if ($m = flash_get('success')): ?><div class="alert alert-success" style="white-space:pre-wrap;word-break:break-all;"><?= e($m) ?></div><?php endif; ?>
<?php foreach ($errors as $err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endforeach; ?>

<div class="breadcrumb"><a href="<?= base_url('admin/submissions.php') ?>">Submissions</a> <span class="sep">/</span> <?= e($s['reference']) ?></div>

<div style="display:grid;grid-template-columns:1fr 320px;gap:24px;align-items:start;">
  <div>
    <div class="admin-card">
      <div class="pub-meta"><?= e($s['reference']) ?> &middot; <?= format_date($s['created_at']) ?></div>
      <h2 style="margin:.2em 0;"><?= e($s['title']) ?></h2>
      <p><strong><?= e($s['author_name']) ?></strong> &lt;<?= e($s['author_email']) ?>&gt;
         <?= $s['affiliation'] ? '<br>' . e($s['affiliation']) : '' ?>
         <?= $s['co_authors'] ? '<br>Co-authors: ' . e($s['co_authors']) : '' ?></p>
      <p style="white-space:pre-wrap;"><?= e($s['abstract']) ?></p>
      <?php if ($s['keywords']): ?><p><strong>Keywords:</strong> <?= e($s['keywords']) ?></p><?php endif; ?>
      <?php if ($s['cover_letter']): ?><details><summary style="cursor:pointer;font-weight:600;">Cover letter</summary><p style="white-space:pre-wrap;"><?= e($s['cover_letter']) ?></p></details><?php endif; ?>
      <div class="pub-actions" style="margin-top:12px;">
        <a class="btn btn-gold btn-sm" href="<?= base_url('admin/submission-view.php?id=' . $s['id'] . '&file=ms') ?>">Download manuscript</a>
        <?php if ($s['supplementary_file']): ?><a class="btn btn-navy btn-sm" href="<?= base_url('admin/submission-view.php?id=' . $s['id'] . '&file=supp') ?>">Supplementary file</a><?php endif; ?>
      </div>
    </div>

    <div class="admin-card" style="margin-top:18px;">
      <h3 style="margin-top:0;">Reviewers</h3>
      <?php if ($reviews): ?>
        <div class="table-wrap" style="border:none;">
          <table>
            <thead><tr><th>Reviewer</th><th>Status</th><th>Recommendation</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($reviews as $r): ?>
              <tr>
                <td><?= e($r['reviewer_name']) ?><br><span style="color:var(--text-muted);font-size:.8rem;"><?= e($r['reviewer_email']) ?></span></td>
                <td><?= ucfirst($r['status']) ?><?= $r['due_at'] ? '<br><span style="color:var(--text-muted);font-size:.78rem;">due ' . e($r['due_at']) . '</span>' : '' ?></td>
                <td><?= $r['recommendation'] ? e($recLabels[$r['recommendation']] ?? $r['recommendation']) : '—' ?></td>
                <td>
                  <form method="post" data-confirm="Remove this reviewer?" style="display:inline;">
                    <?= csrf_field() ?><input type="hidden" name="action" value="remove_reviewer"><input type="hidden" name="review_id" value="<?= $r['id'] ?>">
                    <button class="btn btn-sm btn-danger">Remove</button>
                  </form>
                </td>
              </tr>
              <?php if ($r['comments_to_author'] || $r['comments_to_editor']): ?>
                <tr><td colspan="4" style="background:var(--bg);">
                  <?php if ($r['comments_to_author']): ?><p style="margin:.4em 0;"><strong>To author:</strong><br><span style="white-space:pre-wrap;"><?= e($r['comments_to_author']) ?></span></p><?php endif; ?>
                  <?php if ($r['comments_to_editor']): ?><p style="margin:.4em 0;"><strong>Confidential to editor:</strong><br><span style="white-space:pre-wrap;"><?= e($r['comments_to_editor']) ?></span></p><?php endif; ?>
                </td></tr>
              <?php endif; ?>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <p style="color:var(--text-muted);">No reviewers invited yet.</p>
      <?php endif; ?>

      <form method="post" style="margin-top:14px;">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add_reviewer">
        <div class="form-row">
          <div class="form-group"><label>Reviewer name</label><input type="text" name="reviewer_name" required></div>
          <div class="form-group"><label>Reviewer email</label><input type="email" name="reviewer_email" required></div>
        </div>
        <div class="form-group"><label>Response due (optional)</label><input type="date" name="due_at"></div>
        <button class="btn btn-navy btn-sm">Invite Reviewer</button>
        <div class="hint">A single-use review link is generated. If email is unavailable, copy the link shown after saving and send it yourself.</div>
      </form>
    </div>

    <div class="admin-card" style="margin-top:18px;">
      <h3 style="margin-top:0;">Editor Decision</h3>
      <?php if ($s['decision']): ?>
        <p><strong>Recorded:</strong> <?= e($recLabels[$s['decision']] ?? $s['decision']) ?> on <?= format_date($s['decided_at']) ?></p>
        <p style="white-space:pre-wrap;background:var(--bg);padding:12px;border-radius:8px;"><?= e($s['decision_letter']) ?></p>
      <?php endif; ?>
      <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="decision">
        <div class="form-group">
          <label for="decision">Decision</label>
          <select id="decision" name="decision" required>
            <option value="">— choose —</option>
            <?php foreach ($recLabels as $k => $lbl): ?><option value="<?= $k ?>"><?= e($lbl) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label for="decision_letter">Decision letter to the author</label>
          <textarea id="decision_letter" name="decision_letter" required style="min-height:160px;"><?= e($s['decision_letter'] ?? '') ?></textarea>
        </div>
        <button class="btn btn-navy">Record &amp; Email Decision</button>
      </form>
    </div>
  </div>

  <div>
    <div class="admin-card">
      <h3 style="margin-top:0;">Status</h3>
      <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="status">
        <div class="form-group">
          <select name="status" onchange="this.form.submit()">
            <?php foreach ($statusLabels as $k => $lbl): ?>
              <option value="<?= $k ?>" <?= $s['status'] === $k ? 'selected' : '' ?>><?= e($lbl) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </form>
    </div>

    <div class="admin-card" style="margin-top:18px;">
      <h3 style="margin-top:0;">Internal Notes</h3>
      <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="notes">
        <div class="form-group">
          <textarea name="editor_notes" style="min-height:120px;"><?= e($s['editor_notes'] ?? '') ?></textarea>
        </div>
        <button class="btn btn-sm btn-navy">Save Notes</button>
      </form>
    </div>

    <div class="admin-card" style="margin-top:18px;">
      <h3 style="margin-top:0;">History</h3>
      <ul style="list-style:none;padding:0;margin:0;font-size:.84rem;">
        <?php foreach ($events as $ev): ?>
          <li style="padding:6px 0;border-bottom:1px solid var(--border);">
            <strong><?= e($ev['event']) ?></strong><?= $ev['detail'] ? ' — ' . e($ev['detail']) : '' ?>
            <br><span style="color:var(--text-muted);"><?= format_date($ev['created_at'], 'M j, Y g:ia') ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/layout_footer.php'; ?>
