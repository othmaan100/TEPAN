<?php
require_once __DIR__ . '/../includes/functions.php';
require __DIR__ . '/includes/auth.php';

$pageTitle = 'Announcements';
$activeNav = 'announcements';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
    $id = (int)($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($action === 'delete' && $id) {
        db()->prepare("DELETE FROM announcements WHERE id = ?")->execute([$id]);
        flash_set('success', 'Announcement deleted.');
    } elseif ($action === 'toggle' && $id) {
        db()->prepare("UPDATE announcements SET is_published = 1 - is_published WHERE id = ?")->execute([$id]);
        flash_set('success', 'Visibility updated.');
    } elseif ($action === 'notify' && $id) {
        $stmt = db()->prepare("SELECT * FROM announcements WHERE id = ?");
        $stmt->execute([$id]);
        $a = $stmt->fetch();
        if ($a) {
            $recipients = confirmed_subscriber_emails();
            $link = abs_url('announcement.php?slug=' . $a['slug']);
            $sent = 0;
            foreach ($recipients as $sub) {
                $body = ($sub['name'] ? 'Hello ' . $sub['name'] . ",\n\n" : "Hello,\n\n")
                    . $a['title'] . "\n\n"
                    . trim(strip_tags((string)$a['body'])) . "\n\n"
                    . "Read online: " . $link . "\n";
                if (send_mail($sub['email'], $a['title'], $body)) $sent++;
            }
            flash_set('success', 'Notification attempted for ' . count($recipients) . ' subscriber(s); ' . $sent . ' accepted by the mail server.');
        }
    }
    header('Location: ' . base_url('admin/announcements.php'));
    exit;
}

$items = db()->query("SELECT * FROM announcements ORDER BY COALESCE(published_at, created_at) DESC")->fetchAll();
$subCount = (int) db()->query("SELECT COUNT(*) FROM subscribers WHERE confirmed = 1 AND unsubscribed = 0")->fetchColumn();

require __DIR__ . '/includes/layout_header.php';
?>
<?php if ($m = flash_get('success')): ?><div class="alert alert-success"><?= e($m) ?></div><?php endif; ?>

<div class="admin-toolbar">
  <h3 style="margin:0;">Announcements (<?= count($items) ?>) &middot; <?= $subCount ?> confirmed subscriber(s)</h3>
  <a href="<?= base_url('admin/announcement-form.php') ?>" class="btn btn-navy btn-sm">+ New Announcement</a>
</div>

<?php if ($items): ?>
<div class="table-wrap">
  <table>
    <thead><tr><th>Title</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach ($items as $a): ?>
      <tr>
        <td><?= e($a['title']) ?></td>
        <td><?= format_date($a['published_at'] ?: $a['created_at'], 'M j, Y') ?></td>
        <td><?= $a['is_published'] ? '<span class="pill pill-current">Published</span>' : '<span class="pill pill-muted">Draft</span>' ?></td>
        <td>
          <div style="display:flex;gap:6px;flex-wrap:wrap;">
            <a class="btn btn-sm btn-navy" href="<?= base_url('announcement.php?slug=' . e($a['slug'])) ?>" target="_blank">View</a>
            <a class="btn btn-sm btn-gold" href="<?= base_url('admin/announcement-form.php?id=' . $a['id']) ?>">Edit</a>
            <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= $a['id'] ?>"><button class="btn btn-sm btn-outline" style="border-color:var(--border);color:var(--navy);"><?= $a['is_published'] ? 'Unpublish' : 'Publish' ?></button></form>
            <?php if ($a['is_published']): ?>
              <form method="post" data-confirm="Email this announcement to all &nbsp;<?= $subCount ?>&nbsp; confirmed subscribers now?"><?= csrf_field() ?><input type="hidden" name="action" value="notify"><input type="hidden" name="id" value="<?= $a['id'] ?>"><button class="btn btn-sm btn-navy">Email subscribers</button></form>
            <?php endif; ?>
            <form method="post" data-confirm="Delete this announcement?"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $a['id'] ?>"><button class="btn btn-sm btn-danger">Delete</button></form>
          </div>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php else: ?>
  <div class="empty-state"><div class="icon">📢</div><p>No announcements yet.</p></div>
<?php endif; ?>

<?php require __DIR__ . '/includes/layout_footer.php'; ?>
