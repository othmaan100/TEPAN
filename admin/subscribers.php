<?php
require_once __DIR__ . '/../includes/functions.php';
require __DIR__ . '/includes/auth.php';

// CSV export
if (($_GET['export'] ?? '') === 'csv') {
    $rows = db()->query("SELECT email, name, confirmed, unsubscribed, created_at, confirmed_at FROM subscribers ORDER BY created_at DESC")->fetchAll();
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="tepan-subscribers-' . date('Ymd') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['email', 'name', 'confirmed', 'unsubscribed', 'created_at', 'confirmed_at']);
    foreach ($rows as $r) fputcsv($out, $r);
    fclose($out);
    exit;
}

$pageTitle = 'Subscribers';
$activeNav = 'announcements';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
    $id = (int)($_POST['id'] ?? 0);
    if (($_POST['action'] ?? '') === 'delete' && $id) {
        db()->prepare("DELETE FROM subscribers WHERE id = ?")->execute([$id]);
        flash_set('success', 'Subscriber removed.');
    }
    header('Location: ' . base_url('admin/subscribers.php'));
    exit;
}

$subs = db()->query("SELECT * FROM subscribers ORDER BY created_at DESC")->fetchAll();
$confirmed = 0; $pending = 0; $gone = 0;
foreach ($subs as $s) {
    if ($s['unsubscribed']) $gone++;
    elseif ($s['confirmed']) $confirmed++;
    else $pending++;
}

require __DIR__ . '/includes/layout_header.php';
?>
<?php if ($m = flash_get('success')): ?><div class="alert alert-success"><?= e($m) ?></div><?php endif; ?>

<div class="admin-stats">
  <div class="admin-stat"><strong><?= $confirmed ?></strong><span>Confirmed</span></div>
  <div class="admin-stat"><strong><?= $pending ?></strong><span>Pending confirmation</span></div>
  <div class="admin-stat"><strong><?= $gone ?></strong><span>Unsubscribed</span></div>
</div>

<div class="admin-toolbar">
  <h3 style="margin:0;">Mailing List (<?= count($subs) ?>)</h3>
  <a class="btn btn-navy btn-sm" href="<?= base_url('admin/subscribers.php?export=csv') ?>">Export CSV</a>
</div>

<?php if ($subs): ?>
<div class="table-wrap">
  <table>
    <thead><tr><th>Email</th><th>Name</th><th>State</th><th>Joined</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($subs as $s): ?>
      <tr>
        <td><?= e($s['email']) ?></td>
        <td><?= e($s['name'] ?: '—') ?></td>
        <td>
          <?php if ($s['unsubscribed']): ?><span class="pill pill-muted">Unsubscribed</span>
          <?php elseif ($s['confirmed']): ?><span class="pill pill-current">Confirmed</span>
          <?php else: ?><span class="pill pill-muted">Pending</span><?php endif; ?>
        </td>
        <td><?= format_date($s['created_at'], 'M j, Y') ?></td>
        <td>
          <form method="post" data-confirm="Remove this subscriber?"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $s['id'] ?>"><button class="btn btn-sm btn-danger">Delete</button></form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php else: ?>
  <div class="empty-state"><div class="icon">✉️</div><p>No subscribers yet.</p></div>
<?php endif; ?>

<?php require __DIR__ . '/includes/layout_footer.php'; ?>
