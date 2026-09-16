<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/upload.php';
require __DIR__ . '/includes/auth.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$journal = null;
if ($id) {
    $stmt = db()->prepare("SELECT * FROM journals WHERE id = ?");
    $stmt->execute([$id]);
    $journal = $stmt->fetch();
    if (!$journal) { header('Location: ' . base_url('admin/journals.php')); exit; }
}

// Minimal glue: pre-fill a NEW issue from an accepted manuscript submission.
$fromSubmissionId = (int)($_GET['from_submission'] ?? $_POST['from_submission'] ?? 0);
$fromSubmission = null;
if (!$journal && $fromSubmissionId) {
    $stmt = db()->prepare("SELECT * FROM submissions WHERE id = ?");
    $stmt->execute([$fromSubmissionId]);
    $fromSubmission = $stmt->fetch() ?: null;
}
$prefill = [];
$manuscriptIsPdf = false;
if ($fromSubmission) {
    $prefill = [
        'title'       => $fromSubmission['title'],
        'authors'     => trim($fromSubmission['author_name'] . ($fromSubmission['co_authors'] ? ', ' . $fromSubmission['co_authors'] : '')),
        'description' => $fromSubmission['abstract'],
    ];
    $manuscriptIsPdf = strtolower(pathinfo((string)$fromSubmission['manuscript_file'], PATHINFO_EXTENSION)) === 'pdf'
        && is_file(__DIR__ . '/../' . $fromSubmission['manuscript_file']);
}

$pageTitle = $journal ? 'Edit Journal Issue' : 'Upload Journal Issue';
$activeNav = 'journals';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $volume = (int)($_POST['volume'] ?? 0);
        $issue = (int)($_POST['issue'] ?? 0);
        $pubDate = $_POST['publication_date'] ?? '';
        $editor = trim($_POST['editor'] ?? '');
        $authors = trim($_POST['authors'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $isCurrent = isset($_POST['is_current']) ? 1 : 0;

        if ($title === '') $errors[] = 'Title is required.';
        if ($volume < 1) $errors[] = 'Volume must be at least 1.';
        if ($issue < 1) $errors[] = 'Issue must be at least 1.';
        if (!$pubDate || !strtotime($pubDate)) $errors[] = 'A valid publication date is required.';

        $pdfResult = handle_pdf_upload('pdf_file', 'journals', required: false);
        if (!$pdfResult['ok']) $errors[] = $pdfResult['error'];

        // No new upload + publishing from a submission whose file is a PDF: reuse that file.
        if (!$journal && !$pdfResult['path'] && $fromSubmission && $manuscriptIsPdf) {
            $src = __DIR__ . '/../' . $fromSubmission['manuscript_file'];
            $rel = 'uploads/journals/journals_' . uniqid('', true) . '.pdf';
            if (@copy($src, __DIR__ . '/../' . $rel)) {
                $pdfResult = ['ok' => true, 'path' => $rel, 'size' => (int) filesize($src), 'error' => null];
            } else {
                $errors[] = 'Could not attach the accepted manuscript file. Please upload the PDF manually.';
            }
        }

        // A NEW issue still needs a file one way or another.
        if (!$journal && !$pdfResult['path'] && !$errors) {
            $errors[] = $fromSubmission
                ? 'The accepted manuscript is not a PDF — please upload the final typeset PDF.'
                : 'A PDF file is required.';
        }

        $imgResult = handle_image_upload('cover_image', 'covers');
        if (!$imgResult['ok']) $errors[] = $imgResult['error'];

        if (!$errors) {
            $pubYear = (int) date('Y', strtotime($pubDate));

            if ($isCurrent) {
                db()->exec("UPDATE journals SET is_current = 0");
            }

            if ($journal) {
                $filePath = $pdfResult['path'] ?? $journal['file_path'];
                $fileSize = $pdfResult['path'] ? $pdfResult['size'] : $journal['file_size'];
                $coverPath = $imgResult['path'] ?? $journal['cover_image'];

                if ($pdfResult['path']) delete_uploaded_file($journal['file_path']);
                if ($imgResult['path'] && $journal['cover_image']) delete_uploaded_file($journal['cover_image']);

                $stmt = db()->prepare(
                    "UPDATE journals SET title=?, volume=?, issue=?, pub_year=?, publication_date=?, editor=?, authors=?, description=?, cover_image=?, file_path=?, file_size=?, is_current=? WHERE id=?"
                );
                $stmt->execute([$title, $volume, $issue, $pubYear, $pubDate, $editor, $authors, $description, $coverPath, $filePath, $fileSize, $isCurrent, $journal['id']]);
                flash_set('success', 'Journal issue updated successfully.');
            } else {
                $stmt = db()->prepare(
                    "INSERT INTO journals (title, volume, issue, pub_year, publication_date, editor, authors, description, cover_image, file_path, file_size, is_current) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)"
                );
                $stmt->execute([$title, $volume, $issue, $pubYear, $pubDate, $editor, $authors, $description, $imgResult['path'], $pdfResult['path'], $pdfResult['size'], $isCurrent]);
                $newJournalId = (int) db()->lastInsertId();

                if ($fromSubmission) {
                    db()->prepare("INSERT INTO submission_events (submission_id, event, detail) VALUES (?, 'Published', ?)")
                        ->execute([$fromSubmission['id'], 'Journal issue #' . $newJournalId . ' — Vol ' . $volume . ', Issue ' . $issue]);
                    flash_set('success', 'Journal issue created from manuscript ' . $fromSubmission['reference'] . '.');
                } else {
                    flash_set('success', 'Journal issue uploaded successfully.');
                }
            }
            header('Location: ' . base_url('admin/journals.php'));
            exit;
        }
    }
}

$v = fn($key, $default = '') => e($_POST[$key] ?? ($journal[$key] ?? ($prefill[$key] ?? $default)));

require __DIR__ . '/includes/layout_header.php';
?>

<?php foreach ($errors as $err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endforeach; ?>

<?php if ($fromSubmission): ?>
  <div class="alert alert-success">
    Pre-filled from accepted manuscript <strong><?= e($fromSubmission['reference']) ?></strong>
    (<?= e($fromSubmission['author_name']) ?>).
    Set the volume, issue and publication date, then save.
    <?php if (!$manuscriptIsPdf): ?>
      <br>The accepted file is not a PDF — upload the final typeset PDF below.
    <?php endif; ?>
  </div>
<?php endif; ?>

<div class="form-card" style="max-width:720px;">
  <form method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <?php if ($fromSubmission): ?><input type="hidden" name="from_submission" value="<?= (int)$fromSubmission['id'] ?>"><?php endif; ?>

    <div class="form-group">
      <label for="title">Journal / Issue Title</label>
      <input type="text" id="title" name="title" value="<?= $v('title') ?>" required placeholder="e.g. Journal of Technology Education Practice">
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="volume">Volume Number</label>
        <input type="number" id="volume" name="volume" min="1" value="<?= $v('volume', 1) ?>" required>
      </div>
      <div class="form-group">
        <label for="issue">Issue Number</label>
        <input type="number" id="issue" name="issue" min="1" value="<?= $v('issue', 1) ?>" required>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="publication_date">Publication Date</label>
        <input type="date" id="publication_date" name="publication_date" value="<?= $v('publication_date') ?>" required>
      </div>
      <div class="form-group">
        <label for="editor">Editor (optional)</label>
        <input type="text" id="editor" name="editor" value="<?= $v('editor') ?>">
      </div>
    </div>

    <div class="form-group">
      <label for="authors">Author(s) / Contributor(s)</label>
      <input type="text" id="authors" name="authors" value="<?= $v('authors') ?>"
             placeholder="e.g. Dr. Jane Doe  —  or  —  A. Bello, C. Okafor &amp; M. Ibrahim  —  or  —  Dept. of Technology Education, ABU Zaria">
      <div class="hint">Name the individual person or group of persons this journal belongs to. Journals sharing the exact same credit are grouped together under &ldquo;Browse by Author&rdquo;. Leave blank for an unattributed association issue.</div>
    </div>

    <div class="form-group">
      <label for="description">Description / Abstract</label>
      <textarea id="description" name="description"><?= $v('description') ?></textarea>
    </div>

    <div class="form-group">
      <?php $pdfOptional = $journal || ($fromSubmission && $manuscriptIsPdf); ?>
      <label for="pdf_file">Journal PDF
        <?php if ($journal): ?>(leave blank to keep current file)
        <?php elseif ($fromSubmission && $manuscriptIsPdf): ?>(leave blank to use the accepted manuscript PDF)
        <?php endif; ?>
      </label>
      <input type="file" id="pdf_file" name="pdf_file" accept="application/pdf" <?= $pdfOptional ? '' : 'required' ?>>
      <?php if ($journal): ?>
        <div class="hint">Current file: <?= e(basename($journal['file_path'])) ?> (<?= format_filesize((int)$journal['file_size']) ?>)</div>
      <?php elseif ($fromSubmission && $manuscriptIsPdf): ?>
        <div class="hint">Will attach: <?= e(basename($fromSubmission['manuscript_file'])) ?> (the accepted manuscript). Upload a file here to override it.</div>
      <?php endif; ?>
      <div class="hint">PDF only, maximum 25MB.</div>
    </div>

    <div class="form-group">
      <label for="cover_image">Cover Image (optional)</label>
      <input type="file" id="cover_image" name="cover_image" accept="image/jpeg,image/png,image/webp">
      <?php if ($journal && $journal['cover_image']): ?><div class="hint">Current cover: <?= e(basename($journal['cover_image'])) ?></div><?php endif; ?>
      <div class="hint">JPG, PNG, or WEBP, maximum 5MB.</div>
    </div>

    <div class="form-group checkbox-row">
      <input type="checkbox" id="is_current" name="is_current" <?= ($journal['is_current'] ?? false) ? 'checked' : '' ?>>
      <label for="is_current" style="margin:0;">Mark as the Current Issue</label>
    </div>

    <div style="display:flex;gap:10px;">
      <button type="submit" class="btn btn-navy"><?= $journal ? 'Save Changes' : 'Upload Issue' ?></button>
      <a href="<?= base_url('admin/journals.php') ?>" class="btn btn-outline" style="border-color:var(--border);color:var(--text);">Cancel</a>
    </div>
  </form>
</div>

<?php require __DIR__ . '/includes/layout_footer.php'; ?>
