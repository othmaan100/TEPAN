<?php
require_once __DIR__ . '/../includes/functions.php';
require __DIR__ . '/includes/auth.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$page = null;
if ($id) {
    $stmt = db()->prepare("SELECT * FROM pages WHERE id = ?");
    $stmt->execute([$id]);
    $page = $stmt->fetch();
    if (!$page) { header('Location: ' . base_url('admin/pages.php')); exit; }
}

$pageTitle = $page ? 'Edit Page' : 'New Page';
$activeNav = 'pages';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $body = (string)($_POST['body'] ?? '');
        $navGroup = trim($_POST['nav_group'] ?? '');
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        $isPublished = isset($_POST['is_published']) ? 1 : 0;

        if ($navGroup !== '' && !in_array($navGroup, ['authors', 'policies', 'about'], true)) {
            $navGroup = '';
        }
        if ($title === '') $errors[] = 'Title is required.';

        if (!$errors) {
            if ($page) {
                // Keep the existing slug unless the admin typed a different one.
                $typedSlug = trim($_POST['slug'] ?? '');
                $slug = ($typedSlug !== '' && $typedSlug !== $page['slug'])
                    ? unique_slug('pages', $typedSlug, $page['id'])
                    : $page['slug'];
                db()->prepare(
                    "UPDATE pages SET title=?, slug=?, body=?, nav_group=?, sort_order=?, is_published=? WHERE id=?"
                )->execute([$title, $slug, $body, $navGroup ?: null, $sortOrder, $isPublished, $page['id']]);
                flash_set('success', 'Page updated.');
            } else {
                $slug = unique_slug('pages', trim($_POST['slug'] ?? '') ?: $title);
                db()->prepare(
                    "INSERT INTO pages (title, slug, body, nav_group, sort_order, is_published) VALUES (?,?,?,?,?,?)"
                )->execute([$title, $slug, $body, $navGroup ?: null, $sortOrder, $isPublished]);
                flash_set('success', 'Page created.');
            }
            header('Location: ' . base_url('admin/pages.php'));
            exit;
        }
    }
}

$val = fn($k, $d = '') => e($_POST[$k] ?? ($page[$k] ?? $d));

require __DIR__ . '/includes/layout_header.php';
?>
<?php foreach ($errors as $err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endforeach; ?>

<div class="form-card" style="max-width:820px;">
  <form method="post">
    <?= csrf_field() ?>
    <div class="form-group">
      <label for="title">Title</label>
      <input type="text" id="title" name="title" required value="<?= $val('title') ?>">
    </div>
    <div class="form-row">
      <div class="form-group">
        <label for="slug">Slug <?= $page ? '' : '(optional)' ?></label>
        <input type="text" id="slug" name="slug" value="<?= $val('slug') ?>" placeholder="auto from title">
        <div class="hint">URL: page.php?slug=<em>slug</em></div>
      </div>
      <div class="form-group">
        <label for="nav_group">Menu group</label>
        <select id="nav_group" name="nav_group">
          <?php $g = $_POST['nav_group'] ?? ($page['nav_group'] ?? ''); ?>
          <option value="" <?= $g === '' ? 'selected' : '' ?>>— none —</option>
          <option value="authors" <?= $g === 'authors' ? 'selected' : '' ?>>For Authors</option>
          <option value="policies" <?= $g === 'policies' ? 'selected' : '' ?>>Policies</option>
          <option value="about" <?= $g === 'about' ? 'selected' : '' ?>>About</option>
        </select>
      </div>
    </div>
    <div class="form-group">
      <label for="body">Body (HTML allowed)</label>
      <textarea id="body" name="body" style="min-height:340px;font-family:ui-monospace,Consolas,monospace;font-size:.85rem;"><?= e($_POST['body'] ?? ($page['body'] ?? '')) ?></textarea>
      <div class="hint">Use &lt;h3&gt;, &lt;p&gt;, &lt;ul&gt;&lt;li&gt;, &lt;a href&gt;. Content is shown as-is.</div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label for="sort_order">Sort order</label>
        <input type="number" id="sort_order" name="sort_order" value="<?= $val('sort_order', '0') ?>">
      </div>
      <div class="form-group checkbox-row" style="align-items:flex-end;">
        <input type="checkbox" id="is_published" name="is_published" <?= ($page['is_published'] ?? 1) ? 'checked' : '' ?>>
        <label for="is_published" style="margin:0;">Published</label>
      </div>
    </div>
    <div style="display:flex;gap:10px;">
      <button type="submit" class="btn btn-navy"><?= $page ? 'Save Changes' : 'Create Page' ?></button>
      <a href="<?= base_url('admin/pages.php') ?>" class="btn btn-outline" style="border-color:var(--border);color:var(--text);">Cancel</a>
    </div>
  </form>
</div>

<?php require __DIR__ . '/includes/layout_footer.php'; ?>
