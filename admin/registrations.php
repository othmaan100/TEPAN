<?php
require_once __DIR__ . '/../includes/functions.php';
require __DIR__ . '/includes/auth.php';

$editionId = (int)($_GET['edition'] ?? 0);
$stmt = db()->prepare("SELECT * FROM conference_editions WHERE id = ?");
$stmt->execute([$editionId]);
$edition = $stmt->fetch();
if (!$edition) { header('Location: ' . base_url('admin/conferences.php')); exit; }

// CSV export
if (($_GET['export'] ?? '') === 'csv') {
    $rows = db()->prepare("SELECT reference,name,email,phone,affiliation,attendance_type,paper_title,payment_status,created_at FROM conference_registrations WHERE edition_id = ? ORDER BY created_at");
    $rows->execute([$editionId]);
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="registrations-' . $edition['slug'] . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['reference', 'name', 'email', 'phone', 'affiliation', 'type', 'paper_title', 'payment', 'created_at']);
    foreach ($rows->fetchAll() as $r) fputcsv($out, $r);
    fclose($out);
    exit;
}

$pageTitle = 'Registrations — ' . $edition['name'];
$activeNav = 'conferences';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
    $id = (int)($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';
    if ($action === 'payment' && $id) {
        $status = in_array($_POST['payment_status'] ?? '', ['unpaid', 'paid', 'waived'], true) ? $_POST['payment_status'] : 'unpaid';
        db()->prepare("UPDATE conference_registrations SET payment_status = ? WHERE id = ? AND edition_id = ?")
            ->execute([$status, $id, $editionId]);
        flash_set('success', 'Payment status updated.');
    } elseif ($action === 'delete' && $id) {
        db()->prepare("DELETE FROM conference_registrations WHERE id = ? AND edition_id = ?")->execute([$id, $editionId]);
        flash_set('success', 'Registration deleted.');
    }
    header('Location: ' . base_url('admin/registrations.php?edition=' . $editionId));
    exit;
}

$regs = db()->prepare("SELECT * FROM conference_registrations WHERE edition_id = ? ORDER BY created_at DESC");
$regs->execute([$editionId]);
$regs = $regs->fetchAll();

require __DIR__ . '/includes/layout_header.php';
?>
<?php if ($m = flash_get('success')): ?><div class="alert alert-success"><?= e($m) ?></div><?php endif; ?>

<div class="breadcrumb"><a href="<?= base_url('admin/conferences.php') ?>">Conference Editions</a> <span class="sep">/</span> <?= e($edition['name']) ?></div>

<div class="admin-toolbar">
  <h3 style="margin:0;">Registrations (<?= count($regs) ?>)</h3>
  <?php if ($regs): ?><a class="btn btn-navy btn-sm" href="<?= base_url('admin/registrations.php?edition=' . $editionId . '&export=csv') ?>">Export CSV</a><?php endif; ?>
</div>

<?php if ($regs): ?>
<div class="table-wrap">
  <table>
    <thead><tr><th>Ref</th><th>Name</th><th>Email</th><th>Type</th><th>Paper</th><th>Payment</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach ($regs as $r): ?>
      <tr>
        <td><?= e($r['reference']) ?></td>
        <td><?= e($r['name']) ?><?php if ($r['affiliation']): ?><br><span style="color:var(--text-muted);font-size:.8rem;"><?= e($r['affiliation']) ?></span><?php endif; ?></td>
        <td><?= e($r['email']) ?><?php if ($r['phone']): ?><br><span style="color:var(--text-muted);font-size:.8rem;"><?= e($r['phone']) ?></span><?php endif; ?></td>
        <td><?= ucfirst($r['attendance_type']) ?></td>
        <td><?= $r['paper_title'] ? e(mb_strimwidth($r['paper_title'], 0, 40, '…')) : '—' ?></td>
        <td>
          <form method="post" style="display:flex;gap:4px;">
            <?= csrf_field() ?><input type="hidden" name="action" value="payment"><input type="hidden" name="id" value="<?= $r['id'] ?>">
            <select name="payment_status" onchange="this.form.submit()">
              <?php foreach (['unpaid', 'paid', 'waived'] as $ps): ?>
                <option value="<?= $ps ?>" <?= $r['payment_status'] === $ps ? 'selected' : '' ?>><?= ucfirst($ps) ?></option>
              <?php endforeach; ?>
            </select>
          </form>
        </td>
        <td>
          <div style="display:flex;gap:6px;">
            <?php if ($r['abstract']): ?>
              <details><summary class="btn btn-sm btn-outline" style="border-color:var(--border);color:var(--navy);cursor:pointer;">Abstract</summary>
                <div style="position:absolute;background:#fff;border:1px solid var(--border);padding:12px;max-width:340px;box-shadow:var(--shadow-lg);z-index:20;white-space:pre-wrap;font-size:.82rem;"><?= e($r['abstract']) ?></div>
              </details>
            <?php endif; ?>
            <form method="post" data-confirm="Delete this registration?"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $r['id'] ?>"><button class="btn btn-sm btn-danger">Del</button></form>
          </div>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php else: ?>
  <div class="empty-state"><div class="icon">📝</div><p>No registrations yet.</p></div>
<?php endif; ?>

<?php require __DIR__ . '/includes/layout_footer.php'; ?>
