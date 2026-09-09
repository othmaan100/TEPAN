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

        $pdfResult = handle_pdf_upload('pdf_file', 'journals', required: !$journal);
        if (!$pdfResult['ok']) $errors[] = $pdfResult['error'];

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
                flash_set('success', 'Journal issue uploaded successfully.');
            }
            header('Location: ' . base_url('admin/journals.php'));
            exit;
        }
    }
}

$v = fn($key, $default = '') => e($_POST[$key] ?? ($journal[$key] ?? $default));

require __DIR__ . '/includes/layout_header.php';
?>

<?php foreach ($errors as $err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endforeach; ?>

<div class="form-card" style="max-width:720px;">
  <form method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>

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
      <label for="pdf_file">Journal PDF <?= $journal ? '(leave blank to keep current file)' : '' ?></label>
      <input type="file" id="pdf_file" name="pdf_file" accept="application/pdf" <?= $journal ? '' : 'required' ?>>
      <?php if ($journal): ?><div class="hint">Current file: <?= e(basename($journal['file_path'])) ?> (<?= format_filesize((int)$journal['file_size']) ?>)</div><?php endif; ?>
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
