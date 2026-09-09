<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/upload.php';
require __DIR__ . '/includes/auth.php';

$pageTitle = 'Author Resources';
$activeNav = 'resources';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $action = $_POST['action'] ?? 'add';
        $id = (int)($_POST['id'] ?? 0);

        if ($action === 'delete' && $id) {
            $stmt = db()->prepare("SELECT file_path FROM journal_resources WHERE id = ?");
            $stmt->execute([$id]);
            if ($row = $stmt->fetch()) {
                delete_uploaded_file($row['file_path']);
                db()->prepare("DELETE FROM journal_resources WHERE id = ?")->execute([$id]);
                flash_set('success', 'Resource deleted.');
            }
            header('Location: ' . base_url('admin/resources.php'));
            exit;
        }

        $label = trim($_POST['label'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        if ($label === '') $errors[] = 'A label is required.';

        $up = handle_document_upload('file', 'resources', ['pdf', 'doc', 'docx', 'zip', 'rtf', 'odt'], true, 15);
        if (!$up['ok']) $errors[] = $up['error'];

        if (!$errors) {
            db()->prepare("INSERT INTO journal_resources (label, description, file_path, file_size, sort_order) VALUES (?,?,?,?,?)")
                ->execute([$label, $description ?: null, $up['path'], $up['size'], $sortOrder]);
            flash_set('success', 'Resource added.');
            header('Location: ' . base_url('admin/resources.php'));
            exit;
        }
    }
}

$resources = db()->query("SELECT * FROM journal_resources ORDER BY sort_order, label")->fetchAll();

require __DIR__ . '/includes/layout_header.php';
?>
<?php if ($m = flash_get('success')): ?><div class="alert alert-success"><?= e($m) ?></div><?php endif; ?>
<?php foreach ($errors as $err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endforeach; ?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;align-items:start;">
  <div>
    <h3 style="margin-top:0;">Templates &amp; Forms (<?= count($resources) ?>)</h3>
    <?php if ($resources): ?>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Label</th><th>Type</th><th>Size</th><th>DL</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($resources as $r): ?>
            <tr>
              <td><?= e($r['label']) ?></td>
              <td><?= strtoupper(pathinfo($r['file_path'], PATHINFO_EXTENSION)) ?></td>
              <td><?= format_filesize((int)$r['file_size']) ?></td>
              <td><?= (int)$r['downloads'] ?></td>
              <td>
                <div style="display:flex;gap:6px;">
                  <a class="btn btn-sm btn-navy" href="<?= base_url('download.php?type=resource&id=' . $r['id']) ?>" target="_blank">Get</a>
                  <form method="post" data-confirm="Delete this resource?"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $r['id'] ?>"><button class="btn btn-sm btn-danger">Del</button></form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="empty-state"><div class="icon">📄</div><p>No resources uploaded yet.</p></div>
    <?php endif; ?>
  </div>

  <div class="form-card" style="max-width:100%;">
    <h3 style="margin-top:0;">Add Resource</h3>
    <form method="post" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <div class="form-group">
        <label for="label">Label</label>
        <input type="text" id="label" name="label" required placeholder="Manuscript Template (DOCX)">
      </div>
      <div class="form-group">
        <label for="description">Description (optional)</label>
        <input type="text" id="description" name="description">
      </div>
      <div class="form-group">
        <label for="file">File</label>
        <input type="file" id="file" name="file" required accept=".pdf,.doc,.docx,.zip,.rtf,.odt">
        <div class="hint">PDF / Word / ZIP, max 15MB.</div>
      </div>
      <div class="form-group">
        <label for="sort_order">Sort order</label>
        <input type="number" id="sort_order" name="sort_order" value="0">
      </div>
      <button type="submit" class="btn btn-navy">Add Resource</button>
    </form>
  </div>
</div>

<?php require __DIR__ . '/includes/layout_footer.php'; ?>
