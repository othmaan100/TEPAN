<?php
require_once __DIR__ . '/../includes/functions.php';
require __DIR__ . '/includes/auth.php';

$pageTitle = 'Contact Messages';
$activeNav = 'messages';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    if ($action === 'delete' && $id) {
        db()->prepare("DELETE FROM contact_messages WHERE id = ?")->execute([$id]);
        flash_set('success', 'Message deleted.');
    } elseif ($action === 'mark-read' && $id) {
        db()->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?")->execute([$id]);
    }
    header('Location: ' . base_url('admin/messages.php'));
    exit;
}

$messages = db()->query("SELECT * FROM contact_messages ORDER BY created_at DESC")->fetchAll();

require __DIR__ . '/includes/layout_header.php';
?>

<?php if ($msg = flash_get('success')): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>

<h3>Messages from the Contact Form (<?= count($messages) ?>)</h3>

<?php if ($messages): ?>
  <div style="display:flex;flex-direction:column;gap:14px;">
    <?php foreach ($messages as $m): ?>
      <div class="admin-card" style="<?= $m['is_read'] ? '' : 'border-left:4px solid var(--gold);' ?>">
        <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:10px;">
          <div>
            <strong><?= e($m['name']) ?></strong> &lt;<?= e($m['email']) ?>&gt;
            <?php if (!$m['is_read']): ?><span class="pill pill-current" style="margin-left:8px;">New</span><?php endif; ?>
            <div style="color:var(--text-muted);font-size:.82rem;"><?= format_date($m['created_at'], 'M j, Y g:i A') ?></div>
          </div>
          <div style="display:flex;gap:6px;">
            <?php if (!$m['is_read']): ?>
              <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="mark-read"><input type="hidden" name="id" value="<?= (int)$m['id'] ?>"><button class="btn btn-sm btn-navy">Mark Read</button></form>
            <?php endif; ?>
            <form method="post" data-confirm="Delete this message?"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$m['id'] ?>"><button class="btn btn-sm btn-danger">Delete</button></form>
          </div>
        </div>
        <?php if ($m['subject']): ?><p style="margin:10px 0 4px;"><strong>Subject:</strong> <?= e($m['subject']) ?></p><?php endif; ?>
        <p style="margin:8px 0 0;white-space:pre-wrap;"><?= e($m['message']) ?></p>
      </div>
    <?php endforeach; ?>
  </div>
<?php else: ?>
  <div class="empty-state"><div class="icon">✉️</div><p>No messages yet.</p></div>
<?php endif; ?>

<?php require __DIR__ . '/includes/layout_footer.php'; ?>
