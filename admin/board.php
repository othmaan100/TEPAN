<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/upload.php';
require __DIR__ . '/includes/auth.php';

$pageTitle = 'Editorial Board';
$activeNav = 'board';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
    $id = (int)($_POST['id'] ?? 0);
    if (($_POST['action'] ?? '') === 'delete' && $id) {
        $stmt = db()->prepare("SELECT photo FROM board_members WHERE id = ?");
        $stmt->execute([$id]);
        if ($row = $stmt->fetch()) {
            delete_uploaded_file($row['photo']);
            db()->prepare("DELETE FROM board_members WHERE id = ?")->execute([$id]);
            flash_set('success', 'Board member removed.');
        }
    }
    header('Location: ' . base_url('admin/board.php'));
    exit;
}

$members = db()->query("SELECT * FROM board_members ORDER BY sort_order, name")->fetchAll();
$roleLabels = ['editor_in_chief' => 'Editor-in-Chief', 'associate_editor' => 'Associate Editor', 'board_member' => 'Board Member'];

require __DIR__ . '/includes/layout_header.php';
?>
<?php if ($m = flash_get('success')): ?><div class="alert alert-success"><?= e($m) ?></div><?php endif; ?>

<div class="admin-toolbar">
  <h3 style="margin:0;">Editorial Board (<?= count($members) ?>)</h3>
  <a href="<?= base_url('admin/board-form.php') ?>" class="btn btn-navy btn-sm">+ Add Member</a>
</div>

<?php if ($members): ?>
<div class="table-wrap">
  <table>
    <thead><tr><th>Name</th><th>Role</th><th>Affiliation</th><th>Order</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach ($members as $m): ?>
      <tr>
        <td><?= e($m['name']) ?></td>
        <td><?= e($roleLabels[$m['role']] ?? $m['role']) ?></td>
        <td><?= e($m['affiliation'] ?: '—') ?><?= $m['country'] ? ', ' . e($m['country']) : '' ?></td>
        <td><?= (int)$m['sort_order'] ?></td>
        <td>
          <div style="display:flex;gap:6px;">
            <a class="btn btn-sm btn-gold" href="<?= base_url('admin/board-form.php?id=' . $m['id']) ?>">Edit</a>
            <form method="post" data-confirm="Remove this board member?"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $m['id'] ?>"><button class="btn btn-sm btn-danger">Delete</button></form>
          </div>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php else: ?>
  <div class="empty-state"><div class="icon">👥</div><p>No board members yet.</p><a class="btn btn-navy btn-sm" href="<?= base_url('admin/board-form.php') ?>">Add the first</a></div>
<?php endif; ?>

<?php require __DIR__ . '/includes/layout_footer.php'; ?>
