<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/upload.php';
require __DIR__ . '/includes/auth.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$member = null;
if ($id) {
    $stmt = db()->prepare("SELECT * FROM board_members WHERE id = ?");
    $stmt->execute([$id]);
    $member = $stmt->fetch();
    if (!$member) { header('Location: ' . base_url('admin/board.php')); exit; }
}

$pageTitle = $member ? 'Edit Board Member' : 'Add Board Member';
$activeNav = 'board';
$errors = [];
$roles = ['editor_in_chief' => 'Editor-in-Chief', 'associate_editor' => 'Associate Editor', 'board_member' => 'Board Member'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $role = $_POST['role'] ?? 'board_member';
        if (!isset($roles[$role])) $role = 'board_member';
        $position = trim($_POST['position'] ?? '');
        $affiliation = trim($_POST['affiliation'] ?? '');
        $country = trim($_POST['country'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        $orcid = trim($_POST['orcid'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $sortOrder = (int)($_POST['sort_order'] ?? 0);

        if ($name === '') $errors[] = 'Name is required.';
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email or leave it blank.';

        $img = handle_image_upload('photo', 'board');
        if (!$img['ok']) $errors[] = $img['error'];

        if (!$errors) {
            if ($member) {
                $photo = $img['path'] ?? $member['photo'];
                if ($img['path'] && $member['photo']) delete_uploaded_file($member['photo']);
                db()->prepare(
                    "UPDATE board_members SET name=?, role=?, position=?, affiliation=?, country=?, bio=?, orcid=?, email=?, photo=?, sort_order=? WHERE id=?"
                )->execute([$name, $role, $position ?: null, $affiliation ?: null, $country ?: null, $bio ?: null, $orcid ?: null, $email ?: null, $photo, $sortOrder, $member['id']]);
                flash_set('success', 'Board member updated.');
            } else {
                db()->prepare(
                    "INSERT INTO board_members (name, role, position, affiliation, country, bio, orcid, email, photo, sort_order) VALUES (?,?,?,?,?,?,?,?,?,?)"
                )->execute([$name, $role, $position ?: null, $affiliation ?: null, $country ?: null, $bio ?: null, $orcid ?: null, $email ?: null, $img['path'], $sortOrder]);
                flash_set('success', 'Board member added.');
            }
            header('Location: ' . base_url('admin/board.php'));
            exit;
        }
    }
}

$val = fn($k, $d = '') => e($_POST[$k] ?? ($member[$k] ?? $d));

require __DIR__ . '/includes/layout_header.php';
?>
<?php foreach ($errors as $err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endforeach; ?>

<div class="form-card" style="max-width:720px;">
  <form method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="form-row">
      <div class="form-group">
        <label for="name">Full Name</label>
        <input type="text" id="name" name="name" required value="<?= $val('name') ?>">
      </div>
      <div class="form-group">
        <label for="role">Role</label>
        <select id="role" name="role">
          <?php $r = $_POST['role'] ?? ($member['role'] ?? 'board_member'); foreach ($roles as $k => $lbl): ?>
            <option value="<?= $k ?>" <?= $r === $k ? 'selected' : '' ?>><?= e($lbl) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="form-group">
      <label for="position">Position / Title (optional)</label>
      <input type="text" id="position" name="position" value="<?= $val('position') ?>" placeholder="e.g. Professor of Technology Education">
    </div>
    <div class="form-row">
      <div class="form-group">
        <label for="affiliation">Affiliation</label>
        <input type="text" id="affiliation" name="affiliation" value="<?= $val('affiliation') ?>">
      </div>
      <div class="form-group">
        <label for="country">Country</label>
        <input type="text" id="country" name="country" value="<?= $val('country') ?>">
      </div>
    </div>
    <div class="form-group">
      <label for="bio">Biography</label>
      <textarea id="bio" name="bio"><?= $val('bio') ?></textarea>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label for="orcid">ORCID (optional)</label>
        <input type="text" id="orcid" name="orcid" value="<?= $val('orcid') ?>" placeholder="0000-0000-0000-0000">
      </div>
      <div class="form-group">
        <label for="email">Email (optional)</label>
        <input type="email" id="email" name="email" value="<?= $val('email') ?>">
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label for="photo">Photo (optional)</label>
        <input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/webp">
        <?php if ($member && $member['photo']): ?><div class="hint">Current: <?= e(basename($member['photo'])) ?></div><?php endif; ?>
      </div>
      <div class="form-group">
        <label for="sort_order">Sort order</label>
        <input type="number" id="sort_order" name="sort_order" value="<?= $val('sort_order', '0') ?>">
      </div>
    </div>
    <div style="display:flex;gap:10px;">
      <button type="submit" class="btn btn-navy"><?= $member ? 'Save Changes' : 'Add Member' ?></button>
      <a href="<?= base_url('admin/board.php') ?>" class="btn btn-outline" style="border-color:var(--border);color:var(--text);">Cancel</a>
    </div>
  </form>
</div>

<?php require __DIR__ . '/includes/layout_footer.php'; ?>
