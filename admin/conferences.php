<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/upload.php';
require __DIR__ . '/includes/auth.php';

$pageTitle = 'Conference Editions';
$activeNav = 'conferences';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
    $id = (int)($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';
    if ($action === 'delete' && $id) {
        $stmt = db()->prepare("SELECT banner_image FROM conference_editions WHERE id = ?");
        $stmt->execute([$id]);
        if ($row = $stmt->fetch()) {
            delete_uploaded_file($row['banner_image']);
            db()->prepare("DELETE FROM conference_editions WHERE id = ?")->execute([$id]);
            flash_set('success', 'Conference edition deleted.');
        }
    } elseif ($action === 'toggle' && $id) {
        db()->prepare("UPDATE conference_editions SET is_published = 1 - is_published WHERE id = ?")->execute([$id]);
        flash_set('success', 'Visibility updated.');
    }
    header('Location: ' . base_url('admin/conferences.php'));
    exit;
}

$editions = db()->query(
    "SELECT c.*, (SELECT COUNT(*) FROM conference_registrations r WHERE r.edition_id = c.id) AS reg_count
     FROM conference_editions c ORDER BY c.year DESC, c.sort_order"
)->fetchAll();

require __DIR__ . '/includes/layout_header.php';
?>
<?php if ($m = flash_get('success')): ?><div class="alert alert-success"><?= e($m) ?></div><?php endif; ?>

<div class="admin-toolbar">
  <h3 style="margin:0;">Conference Editions (<?= count($editions) ?>)</h3>
  <a href="<?= base_url('admin/conference-form.php') ?>" class="btn btn-navy btn-sm">+ New Edition</a>
</div>

<?php if ($editions): ?>
<div class="table-wrap">
  <table>
    <thead><tr><th>Name</th><th>Year</th><th>CFP</th><th>Registration</th><th>Regs</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach ($editions as $c): ?>
      <tr>
        <td><?= e($c['name']) ?></td>
        <td><?= (int)$c['year'] ?></td>
        <td><?= $c['cfp_open'] ? '<span class="pill pill-current">Open</span>' : '<span class="pill pill-muted">Closed</span>' ?></td>
        <td><?= $c['registration_open'] ? '<span class="pill pill-current">Open</span>' : '<span class="pill pill-muted">Closed</span>' ?></td>
        <td><a href="<?= base_url('admin/registrations.php?edition=' . $c['id']) ?>"><?= (int)$c['reg_count'] ?></a></td>
        <td><?= $c['is_published'] ? 'Published' : 'Hidden' ?></td>
        <td>
          <div style="display:flex;gap:6px;flex-wrap:wrap;">
            <a class="btn btn-sm btn-navy" href="<?= base_url('conference.php?slug=' . e($c['slug'])) ?>" target="_blank">View</a>
            <a class="btn btn-sm btn-gold" href="<?= base_url('admin/conference-form.php?id=' . $c['id']) ?>">Edit</a>
            <a class="btn btn-sm btn-outline" style="border-color:var(--border);color:var(--navy);" href="<?= base_url('admin/registrations.php?edition=' . $c['id']) ?>">Registrations</a>
            <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= $c['id'] ?>"><button class="btn btn-sm btn-outline" style="border-color:var(--border);color:var(--navy);"><?= $c['is_published'] ? 'Hide' : 'Publish' ?></button></form>
            <form method="post" data-confirm="Delete this edition and its registrations?"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $c['id'] ?>"><button class="btn btn-sm btn-danger">Delete</button></form>
          </div>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php else: ?>
  <div class="empty-state"><div class="icon">🎓</div><p>No conference editions yet.</p><a class="btn btn-navy btn-sm" href="<?= base_url('admin/conference-form.php') ?>">Create the first</a></div>
<?php endif; ?>

<?php require __DIR__ . '/includes/layout_footer.php'; ?>
