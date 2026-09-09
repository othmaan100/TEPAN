<?php
require_once __DIR__ . '/../includes/functions.php';
require __DIR__ . '/includes/auth.php';

$pageTitle = 'Manuscript Submissions';
$activeNav = 'submissions';

$statuses = [
    'submitted' => 'Submitted',
    'under_review' => 'Under review',
    'revisions_requested' => 'Revisions requested',
    'accepted' => 'Accepted',
    'rejected' => 'Rejected',
    'withdrawn' => 'Withdrawn',
];
$filter = $_GET['status'] ?? '';
if (!isset($statuses[$filter])) $filter = '';

if ($filter) {
    $stmt = db()->prepare("SELECT * FROM submissions WHERE status = ? ORDER BY created_at DESC");
    $stmt->execute([$filter]);
    $rows = $stmt->fetchAll();
} else {
    $rows = db()->query("SELECT * FROM submissions ORDER BY created_at DESC")->fetchAll();
}

$counts = [];
foreach (db()->query("SELECT status, COUNT(*) c FROM submissions GROUP BY status") as $r) {
    $counts[$r['status']] = (int)$r['c'];
}

require __DIR__ . '/includes/layout_header.php';
?>
<?php if ($m = flash_get('success')): ?><div class="alert alert-success"><?= e($m) ?></div><?php endif; ?>

<div class="tabs" style="margin-bottom:20px;">
  <a href="<?= base_url('admin/submissions.php') ?>" class="<?= $filter === '' ? 'active' : '' ?>">All (<?= array_sum($counts) ?>)</a>
  <?php foreach ($statuses as $k => $lbl): ?>
    <a href="<?= base_url('admin/submissions.php?status=' . $k) ?>" class="<?= $filter === $k ? 'active' : '' ?>"><?= e($lbl) ?> (<?= $counts[$k] ?? 0 ?>)</a>
  <?php endforeach; ?>
</div>

<?php if ($rows): ?>
<div class="table-wrap">
  <table>
    <thead><tr><th>Ref</th><th>Title</th><th>Author</th><th>Submitted</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $s): ?>
      <tr>
        <td><?= e($s['reference']) ?></td>
        <td><?= e(mb_strimwidth($s['title'], 0, 60, '…')) ?></td>
        <td><?= e($s['author_name']) ?><br><span style="color:var(--text-muted);font-size:.8rem;"><?= e($s['author_email']) ?></span></td>
        <td><?= format_date($s['created_at'], 'M j, Y') ?></td>
        <td><span class="pill <?= in_array($s['status'], ['accepted'], true) ? 'pill-current' : 'pill-muted' ?>"><?= e($statuses[$s['status']] ?? $s['status']) ?></span></td>
        <td><a class="btn btn-sm btn-gold" href="<?= base_url('admin/submission-view.php?id=' . $s['id']) ?>">Open</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php else: ?>
  <div class="empty-state"><div class="icon">📝</div><p>No submissions<?= $filter ? ' with this status' : ' yet' ?>.</p></div>
<?php endif; ?>

<?php require __DIR__ . '/includes/layout_footer.php'; ?>
