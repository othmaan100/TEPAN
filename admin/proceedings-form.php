<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/upload.php';
require __DIR__ . '/includes/auth.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$proceeding = null;
if ($id) {
    $stmt = db()->prepare("SELECT * FROM proceedings WHERE id = ?");
    $stmt->execute([$id]);
    $proceeding = $stmt->fetch();
    if (!$proceeding) { header('Location: ' . base_url('admin/proceedings.php')); exit; }
}

$pageTitle = $proceeding ? 'Edit Conference Proceedings' : 'Upload Conference Proceedings';
$activeNav = 'proceedings';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $confName = trim($_POST['conference_name'] ?? '');
        $confYear = (int)($_POST['conference_year'] ?? 0);
        $location = trim($_POST['location'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($title === '') $errors[] = 'Title is required.';
        if ($confName === '') $errors[] = 'Conference name is required.';
        if ($confYear < 1900) $errors[] = 'A valid conference year is required.';

        $pdfResult = handle_pdf_upload('pdf_file', 'proceedings', required: !$proceeding);
        if (!$pdfResult['ok']) $errors[] = $pdfResult['error'];

        $imgResult = handle_image_upload('cover_image', 'covers');
        if (!$imgResult['ok']) $errors[] = $imgResult['error'];

        if (!$errors) {
            if ($proceeding) {
                $filePath = $pdfResult['path'] ?? $proceeding['file_path'];
                $fileSize = $pdfResult['path'] ? $pdfResult['size'] : $proceeding['file_size'];
                $coverPath = $imgResult['path'] ?? $proceeding['cover_image'];

                if ($pdfResult['path']) delete_uploaded_file($proceeding['file_path']);
                if ($imgResult['path'] && $proceeding['cover_image']) delete_uploaded_file($proceeding['cover_image']);

                $stmt = db()->prepare(
                    "UPDATE proceedings SET title=?, conference_name=?, conference_year=?, location=?, description=?, cover_image=?, file_path=?, file_size=? WHERE id=?"
                );
                $stmt->execute([$title, $confName, $confYear, $location, $description, $coverPath, $filePath, $fileSize, $proceeding['id']]);
                flash_set('success', 'Proceedings entry updated successfully.');
            } else {
                $stmt = db()->prepare(
                    "INSERT INTO proceedings (title, conference_name, conference_year, location, description, cover_image, file_path, file_size) VALUES (?,?,?,?,?,?,?,?)"
                );
                $stmt->execute([$title, $confName, $confYear, $location, $description, $imgResult['path'], $pdfResult['path'], $pdfResult['size']]);
                flash_set('success', 'Conference proceedings uploaded successfully.');
            }
            header('Location: ' . base_url('admin/proceedings.php'));
            exit;
        }
    }
}

$v = fn($key, $default = '') => e($_POST[$key] ?? ($proceeding[$key] ?? $default));

require __DIR__ . '/includes/layout_header.php';
?>

<?php foreach ($errors as $err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endforeach; ?>

<div class="form-card" style="max-width:720px;">
  <form method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div class="form-group">
      <label for="title">Proceedings Title</label>
      <input type="text" id="title" name="title" value="<?= $v('title') ?>" required placeholder="e.g. Proceedings of the 12th TEPAN Annual Conference">
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="conference_name">Conference Name</label>
        <input type="text" id="conference_name" name="conference_name" value="<?= $v('conference_name') ?>" required placeholder="e.g. TEPAN 12th Annual National Conference">
      </div>
      <div class="form-group">
        <label for="conference_year">Conference Year</label>
        <input type="number" id="conference_year" name="conference_year" min="1900" max="2100" value="<?= $v('conference_year', date('Y')) ?>" required>
      </div>
    </div>

    <div class="form-group">
      <label for="location">Location (optional)</label>
      <input type="text" id="location" name="location" value="<?= $v('location') ?>" placeholder="e.g. Abuja, Nigeria">
    </div>

    <div class="form-group">
      <label for="description">Description</label>
      <textarea id="description" name="description"><?= $v('description') ?></textarea>
    </div>

    <div class="form-group">
      <label for="pdf_file">Proceedings PDF <?= $proceeding ? '(leave blank to keep current file)' : '' ?></label>
      <input type="file" id="pdf_file" name="pdf_file" accept="application/pdf" <?= $proceeding ? '' : 'required' ?>>
      <?php if ($proceeding): ?><div class="hint">Current file: <?= e(basename($proceeding['file_path'])) ?> (<?= format_filesize((int)$proceeding['file_size']) ?>)</div><?php endif; ?>
      <div class="hint">PDF only, maximum 25MB.</div>
    </div>

    <div class="form-group">
      <label for="cover_image">Cover Image (optional)</label>
      <input type="file" id="cover_image" name="cover_image" accept="image/jpeg,image/png,image/webp">
      <?php if ($proceeding && $proceeding['cover_image']): ?><div class="hint">Current cover: <?= e(basename($proceeding['cover_image'])) ?></div><?php endif; ?>
      <div class="hint">JPG, PNG, or WEBP, maximum 5MB.</div>
    </div>

    <div style="display:flex;gap:10px;">
      <button type="submit" class="btn btn-navy"><?= $proceeding ? 'Save Changes' : 'Upload Proceedings' ?></button>
      <a href="<?= base_url('admin/proceedings.php') ?>" class="btn btn-outline" style="border-color:var(--border);color:var(--text);">Cancel</a>
    </div>
  </form>
</div>

<?php require __DIR__ . '/includes/layout_footer.php'; ?>
