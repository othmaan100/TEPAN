<?php
require_once __DIR__ . '/../includes/functions.php';
require __DIR__ . '/includes/auth.php';

$pageTitle = 'Policy & Content Pages';
$activeNav = 'pages';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
    $id = (int)($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';
    if ($action === 'toggle' && $id) {
        db()->prepare("UPDATE pages SET is_published = 1 - is_published WHERE id = ?")->execute([$id]);
        flash_set('success', 'Page visibility updated.');
    } elseif ($action === 'delete' && $id) {
        db()->prepare("DELETE FROM pages WHERE id = ?")->execute([$id]);
        flash_set('success', 'Page deleted.');
    }
    header('Location: ' . base_url('admin/pages.php'));
    exit;
}

$pages = db()->query("SELECT * FROM pages ORDER BY nav_group, sort_order, title")->fetchAll();

require __DIR__ . '/includes/layout_header.php';
?>
<?php if ($m = flash_get('success')): ?><div class="alert alert-success"><?= e($m) ?></div><?php endif; ?>

<div class="admin-toolbar">
  <h3 style="margin:0;">Pages (<?= count($pages) ?>)</h3>
  <a href="<?= base_url('admin/page-form.php') ?>" class="btn btn-navy btn-sm">+ New Page</a>
</div>

<div class="table-wrap">
  <table>
    <thead><tr><th>Title</th><th>Slug</th><th>Menu group</th><th>Status</th><th>Updated</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach ($pages as $p): ?>
      <tr>
        <td><?= e($p['title']) ?></td>
        <td><code><?= e($p['slug']) ?></code></td>
        <td><?= e($p['nav_group'] ?: '—') ?></td>
        <td><?= $p['is_published'] ? '<span class="pill pill-current">Published</span>' : '<span class="pill pill-muted">Hidden</span>' ?></td>
        <td><?= format_date($p['updated_at'], 'M j, Y') ?></td>
        <td>
          <div style="display:flex;gap:6px;flex-wrap:wrap;">
            <a class="btn btn-sm btn-navy" href="<?= base_url('page.php?slug=' . e($p['slug'])) ?>" target="_blank">View</a>
            <a class="btn btn-sm btn-gold" href="<?= base_url('admin/page-form.php?id=' . $p['id']) ?>">Edit</a>
            <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= $p['id'] ?>"><button class="btn btn-sm btn-outline" style="border-color:var(--border);color:var(--navy);"><?= $p['is_published'] ? 'Hide' : 'Publish' ?></button></form>
            <form method="post" data-confirm="Delete this page?"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $p['id'] ?>"><button class="btn btn-sm btn-danger">Delete</button></form>
          </div>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/includes/layout_footer.php'; ?>
