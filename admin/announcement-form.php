<?php
require_once __DIR__ . '/../includes/functions.php';
require __DIR__ . '/includes/auth.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$item = null;
if ($id) {
    $stmt = db()->prepare("SELECT * FROM announcements WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    if (!$item) { header('Location: ' . base_url('admin/announcements.php')); exit; }
}

$pageTitle = $item ? 'Edit Announcement' : 'New Announcement';
$activeNav = 'announcements';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $body = (string)($_POST['body'] ?? '');
        $isPublished = isset($_POST['is_published']) ? 1 : 0;
        $publishedAt = trim($_POST['published_at'] ?? '');
        $publishedAt = $publishedAt !== '' ? date('Y-m-d H:i:s', strtotime($publishedAt)) : null;

        if ($title === '') $errors[] = 'Title is required.';

        if (!$errors) {
            if ($item) {
                $slug = $item['slug'];
                db()->prepare("UPDATE announcements SET title=?, body=?, is_published=?, published_at=? WHERE id=?")
                    ->execute([$title, $body, $isPublished, $publishedAt, $item['id']]);
                flash_set('success', 'Announcement updated.');
            } else {
                $slug = unique_slug('announcements', $title);
                if (!$publishedAt && $isPublished) $publishedAt = date('Y-m-d H:i:s');
                db()->prepare("INSERT INTO announcements (slug, title, body, is_published, published_at) VALUES (?,?,?,?,?)")
                    ->execute([$slug, $title, $body, $isPublished, $publishedAt]);
                flash_set('success', 'Announcement created.');
            }
            header('Location: ' . base_url('admin/announcements.php'));
            exit;
        }
    }
}

$val = fn($k, $d = '') => e($_POST[$k] ?? ($item[$k] ?? $d));

require __DIR__ . '/includes/layout_header.php';
?>
<?php foreach ($errors as $err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endforeach; ?>

<div class="form-card" style="max-width:820px;">
  <form method="post">
    <?= csrf_field() ?>
    <div class="form-group">
      <label for="title">Title</label>
      <input type="text" id="title" name="title" required value="<?= $val('title') ?>">
    </div>
    <div class="form-group">
      <label for="body">Body (HTML allowed)</label>
      <textarea id="body" name="body" style="min-height:260px;font-family:ui-monospace,Consolas,monospace;font-size:.85rem;"><?= e($_POST['body'] ?? ($item['body'] ?? '')) ?></textarea>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label for="published_at">Publish date/time (optional)</label>
        <input type="datetime-local" id="published_at" name="published_at"
               value="<?= $item && $item['published_at'] ? date('Y-m-d\TH:i', strtotime($item['published_at'])) : '' ?>">
        <div class="hint">Leave blank to use now when published.</div>
      </div>
      <div class="form-group checkbox-row" style="align-items:flex-end;">
        <input type="checkbox" id="is_published" name="is_published" <?= ($item['is_published'] ?? 1) ? 'checked' : '' ?>>
        <label for="is_published" style="margin:0;">Published</label>
      </div>
    </div>
    <div style="display:flex;gap:10px;">
      <button type="submit" class="btn btn-navy"><?= $item ? 'Save Changes' : 'Create' ?></button>
      <a href="<?= base_url('admin/announcements.php') ?>" class="btn btn-outline" style="border-color:var(--border);color:var(--text);">Cancel</a>
    </div>
  </form>
  <?php if ($item): ?>
    <p class="hint" style="margin-top:14px;">After saving, use <strong>Email subscribers</strong> on the list page to send this out.</p>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/layout_footer.php'; ?>
