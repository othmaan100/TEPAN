<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/upload.php';
require __DIR__ . '/includes/auth.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$c = null;
if ($id) {
    $stmt = db()->prepare("SELECT * FROM conference_editions WHERE id = ?");
    $stmt->execute([$id]);
    $c = $stmt->fetch();
    if (!$c) { header('Location: ' . base_url('admin/conferences.php')); exit; }
}

$pageTitle = $c ? 'Edit Conference Edition' : 'New Conference Edition';
$activeNav = 'conferences';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $f = fn($k) => trim($_POST[$k] ?? '');
        $name = $f('name');
        $year = (int)($_POST['year'] ?? 0);
        $data = [
            'name' => $name,
            'edition_label' => $f('edition_label') ?: null,
            'theme' => $f('theme') ?: null,
            'year' => $year,
            'start_date' => $f('start_date') ?: null,
            'end_date' => $f('end_date') ?: null,
            'venue' => $f('venue') ?: null,
            'city' => $f('city') ?: null,
            'description' => (string)($_POST['description'] ?? '') ?: null,
            'cfp_body' => (string)($_POST['cfp_body'] ?? '') ?: null,
            'cfp_deadline' => $f('cfp_deadline') ?: null,
            'cfp_open' => isset($_POST['cfp_open']) ? 1 : 0,
            'registration_open' => isset($_POST['registration_open']) ? 1 : 0,
            'registration_info' => (string)($_POST['registration_info'] ?? '') ?: null,
            'is_published' => isset($_POST['is_published']) ? 1 : 0,
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
        ];

        if ($name === '') $errors[] = 'Name is required.';
        if ($year < 1990 || $year > 2100) $errors[] = 'Enter a valid year.';

        $img = handle_image_upload('banner_image', 'conferences');
        if (!$img['ok']) $errors[] = $img['error'];

        if (!$errors) {
            if ($c) {
                $data['banner_image'] = $img['path'] ?? $c['banner_image'];
                if ($img['path'] && $c['banner_image']) delete_uploaded_file($c['banner_image']);
                $data['slug'] = $c['slug'];
                $cols = implode('=?, ', array_keys($data)) . '=?';
                db()->prepare("UPDATE conference_editions SET $cols WHERE id = ?")
                    ->execute([...array_values($data), $c['id']]);
                flash_set('success', 'Conference edition updated.');
            } else {
                $data['banner_image'] = $img['path'];
                $data['slug'] = unique_slug('conference_editions', $name . '-' . $year);
                $cols = implode(', ', array_keys($data));
                $ph = implode(', ', array_fill(0, count($data), '?'));
                db()->prepare("INSERT INTO conference_editions ($cols) VALUES ($ph)")
                    ->execute(array_values($data));
                flash_set('success', 'Conference edition created.');
            }
            header('Location: ' . base_url('admin/conferences.php'));
            exit;
        }
    }
}

$v = fn($k, $d = '') => e($_POST[$k] ?? ($c[$k] ?? $d));
$chk = fn($k, $def = 0) => (($_POST ? isset($_POST[$k]) : ($c[$k] ?? $def)) ? 'checked' : '');

require __DIR__ . '/includes/layout_header.php';
?>
<?php foreach ($errors as $err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endforeach; ?>

<div class="form-card" style="max-width:820px;">
  <form method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="form-group">
      <label for="name">Conference Name</label>
      <input type="text" id="name" name="name" required value="<?= $v('name') ?>" placeholder="12th TEPAN Annual National Conference">
    </div>
    <div class="form-row">
      <div class="form-group">
        <label for="edition_label">Edition Label</label>
        <input type="text" id="edition_label" name="edition_label" value="<?= $v('edition_label') ?>" placeholder="12th Edition">
      </div>
      <div class="form-group">
        <label for="year">Year</label>
        <input type="number" id="year" name="year" required value="<?= $v('year', date('Y')) ?>">
      </div>
    </div>
    <div class="form-group">
      <label for="theme">Theme</label>
      <input type="text" id="theme" name="theme" value="<?= $v('theme') ?>">
    </div>
    <div class="form-row">
      <div class="form-group">
        <label for="start_date">Start date</label>
        <input type="date" id="start_date" name="start_date" value="<?= $v('start_date') ?>">
      </div>
      <div class="form-group">
        <label for="end_date">End date</label>
        <input type="date" id="end_date" name="end_date" value="<?= $v('end_date') ?>">
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label for="venue">Venue</label>
        <input type="text" id="venue" name="venue" value="<?= $v('venue') ?>">
      </div>
      <div class="form-group">
        <label for="city">City</label>
        <input type="text" id="city" name="city" value="<?= $v('city') ?>">
      </div>
    </div>
    <div class="form-group">
      <label for="description">Description (HTML allowed)</label>
      <textarea id="description" name="description" style="min-height:120px;"><?= e($_POST['description'] ?? ($c['description'] ?? '')) ?></textarea>
    </div>
    <div class="form-group">
      <label for="banner_image">Banner image (optional)</label>
      <input type="file" id="banner_image" name="banner_image" accept="image/jpeg,image/png,image/webp">
      <?php if ($c && $c['banner_image']): ?><div class="hint">Current: <?= e(basename($c['banner_image'])) ?></div><?php endif; ?>
    </div>

    <hr style="border:none;border-top:1px solid var(--border);margin:22px 0;">
    <h3 style="margin:0 0 12px;">Call for Papers</h3>
    <div class="form-group checkbox-row">
      <input type="checkbox" id="cfp_open" name="cfp_open" <?= $chk('cfp_open') ?>>
      <label for="cfp_open" style="margin:0;">Call for Papers is open</label>
    </div>
    <div class="form-group">
      <label for="cfp_deadline">Abstract deadline</label>
      <input type="date" id="cfp_deadline" name="cfp_deadline" value="<?= $v('cfp_deadline') ?>">
    </div>
    <div class="form-group">
      <label for="cfp_body">Call for Papers text (HTML allowed)</label>
      <textarea id="cfp_body" name="cfp_body" style="min-height:120px;"><?= e($_POST['cfp_body'] ?? ($c['cfp_body'] ?? '')) ?></textarea>
    </div>

    <hr style="border:none;border-top:1px solid var(--border);margin:22px 0;">
    <h3 style="margin:0 0 12px;">Registration</h3>
    <div class="form-group checkbox-row">
      <input type="checkbox" id="registration_open" name="registration_open" <?= $chk('registration_open') ?>>
      <label for="registration_open" style="margin:0;">Registration is open</label>
    </div>
    <div class="form-group">
      <label for="registration_info">Fees &amp; payment details (HTML allowed)</label>
      <textarea id="registration_info" name="registration_info" style="min-height:120px;" placeholder="Fee categories, bank account for transfer, etc."><?= e($_POST['registration_info'] ?? ($c['registration_info'] ?? '')) ?></textarea>
    </div>

    <hr style="border:none;border-top:1px solid var(--border);margin:22px 0;">
    <div class="form-row">
      <div class="form-group">
        <label for="sort_order">Sort order</label>
        <input type="number" id="sort_order" name="sort_order" value="<?= $v('sort_order', '0') ?>">
      </div>
      <div class="form-group checkbox-row" style="align-items:flex-end;">
        <input type="checkbox" id="is_published" name="is_published" <?= $c ? ($c['is_published'] ? 'checked' : '') : 'checked' ?>>
        <label for="is_published" style="margin:0;">Published</label>
      </div>
    </div>

    <div style="display:flex;gap:10px;">
      <button type="submit" class="btn btn-navy"><?= $c ? 'Save Changes' : 'Create Edition' ?></button>
      <a href="<?= base_url('admin/conferences.php') ?>" class="btn btn-outline" style="border-color:var(--border);color:var(--text);">Cancel</a>
    </div>
  </form>
</div>

<?php require __DIR__ . '/includes/layout_footer.php'; ?>
