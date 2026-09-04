<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/upload.php';
require __DIR__ . '/includes/auth.php';

$pageTitle = 'Manage Conference Proceedings';
$activeNav = 'proceedings';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if ($action === 'delete' && $id) {
        $stmt = db()->prepare("SELECT * FROM proceedings WHERE id = ?");
        $stmt->execute([$id]);
        $p = $stmt->fetch();
        if ($p) {
            delete_uploaded_file($p['file_path']);
            delete_uploaded_file($p['cover_image']);
            db()->prepare("DELETE FROM proceedings WHERE id = ?")->execute([$id]);
            flash_set('success', 'Proceedings entry deleted.');
        }
    }
    header('Location: ' . base_url('admin/proceedings.php'));
    exit;
}

$proceedings = db()->query("SELECT * FROM proceedings ORDER BY conference_year DESC, created_at DESC")->fetchAll();

require __DIR__ . '/includes/layout_header.php';
?>

<?php if ($msg = flash_get('success')): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>

<div class="admin-toolbar">
  <h3 style="margin:0;">All Conference Proceedings (<?= count($proceedings) ?>)</h3>
  <a href="<?= base_url('admin/proceedings-form.php') ?>" class="btn btn-navy btn-sm">+ Upload Proceedings</a>
</div>

<?php if ($proceedings): ?>
<div class="table-wrap">
  <table>
    <thead>
      <tr><th>Title</th><th>Conference</th><th>Year</th><th>Location</th><th>Downloads</th><th>Actions</th></tr>
    </thead>
    <tbody>
    <?php foreach ($proceedings as $p): ?>
      <tr>
        <td><?= e($p['title']) ?></td>
        <td><?= e($p['conference_name']) ?></td>
        <td><?= (int)$p['conference_year'] ?></td>
        <td><?= e($p['location'] ?? '—') ?></td>
        <td><?= (int)$p['downloads'] ?></td>
        <td>
          <div style="display:flex;gap:6px;flex-wrap:wrap;">
            <a href="<?= base_url('download.php?type=proceeding&id=' . $p['id']) ?>" target="_blank" class="btn btn-sm btn-navy">Download</a>
            <a href="<?= base_url('admin/proceedings-form.php?id=' . $p['id']) ?>" class="btn btn-sm btn-gold">Edit</a>
            <form method="post" style="display:inline;" data-confirm="Delete this proceedings entry permanently? This cannot be undone.">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
              <button type="submit" class="btn btn-sm btn-danger">Delete</button>
            </form>
          </div>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php else: ?>
  <div class="empty-state"><div class="icon">🗂️</div><p>No conference proceedings uploaded yet.</p><a href="<?= base_url('admin/proceedings-form.php') ?>" class="btn btn-navy btn-sm">Upload the first proceedings</a></div>
<?php endif; ?>

<?php require __DIR__ . '/includes/layout_footer.php'; ?>
