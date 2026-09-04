<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/upload.php';
require __DIR__ . '/includes/auth.php';

$pageTitle = 'Manage Journals';
$activeNav = 'journals';

// Handle actions: delete, set-current
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if ($action === 'delete' && $id) {
        $stmt = db()->prepare("SELECT * FROM journals WHERE id = ?");
        $stmt->execute([$id]);
        $j = $stmt->fetch();
        if ($j) {
            delete_uploaded_file($j['file_path']);
            delete_uploaded_file($j['cover_image']);
            db()->prepare("DELETE FROM journals WHERE id = ?")->execute([$id]);
            flash_set('success', 'Journal issue deleted.');
        }
    } elseif ($action === 'set-current' && $id) {
        db()->exec("UPDATE journals SET is_current = 0");
        db()->prepare("UPDATE journals SET is_current = 1 WHERE id = ?")->execute([$id]);
        flash_set('success', 'Current issue updated.');
    }
    header('Location: ' . base_url('admin/journals.php'));
    exit;
}

$journals = db()->query("SELECT * FROM journals ORDER BY volume DESC, issue DESC, publication_date DESC")->fetchAll();

require __DIR__ . '/includes/layout_header.php';
?>

<?php if ($msg = flash_get('success')): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>

<div class="admin-toolbar">
  <h3 style="margin:0;">All Journal Issues (<?= count($journals) ?>)</h3>
  <a href="<?= base_url('admin/journal-form.php') ?>" class="btn btn-navy btn-sm">+ Upload New Issue</a>
</div>

<?php if ($journals): ?>
<div class="table-wrap">
  <table>
    <thead>
      <tr><th>Title</th><th>Volume</th><th>Issue</th><th>Year</th><th>Published</th><th>Downloads</th><th>Status</th><th>Actions</th></tr>
    </thead>
    <tbody>
    <?php foreach ($journals as $j): ?>
      <tr>
        <td><?= e($j['title']) ?></td>
        <td><?= (int)$j['volume'] ?></td>
        <td><?= (int)$j['issue'] ?></td>
        <td><?= (int)$j['pub_year'] ?></td>
        <td><?= format_date($j['publication_date'], 'M j, Y') ?></td>
        <td><?= (int)$j['downloads'] ?></td>
        <td>
          <?php if ($j['is_current']): ?>
            <span class="pill pill-current">Current</span>
          <?php else: ?>
            <span class="pill pill-muted">Archived</span>
          <?php endif; ?>
        </td>
        <td>
          <div style="display:flex;gap:6px;flex-wrap:wrap;">
            <a href="<?= base_url('journal-view.php?id=' . $j['id']) ?>" target="_blank" class="btn btn-sm btn-navy">View</a>
            <a href="<?= base_url('admin/journal-form.php?id=' . $j['id']) ?>" class="btn btn-sm btn-gold">Edit</a>
            <?php if (!$j['is_current']): ?>
              <form method="post" style="display:inline;">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="set-current">
                <input type="hidden" name="id" value="<?= (int)$j['id'] ?>">
                <button type="submit" class="btn btn-sm btn-outline" style="border-color:var(--border);color:var(--navy);">Set Current</button>
              </form>
            <?php endif; ?>
            <form method="post" style="display:inline;" data-confirm="Delete this journal issue permanently? This cannot be undone.">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= (int)$j['id'] ?>">
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
  <div class="empty-state"><div class="icon">📰</div><p>No journal issues uploaded yet.</p><a href="<?= base_url('admin/journal-form.php') ?>" class="btn btn-navy btn-sm">Upload the first issue</a></div>
<?php endif; ?>

<?php require __DIR__ . '/includes/layout_footer.php'; ?>
