<?php
require_once __DIR__ . '/includes/functions.php';

$edition = null;
if (isset($_GET['slug'])) {
    $slug = preg_replace('/[^a-z0-9\-]/', '', strtolower($_GET['slug']));
    $stmt = db()->prepare("SELECT * FROM conference_editions WHERE slug = ? AND is_published = 1 LIMIT 1");
    $stmt->execute([$slug]);
    $edition = $stmt->fetch();
} elseif (isset($_GET['id'])) {
    $stmt = db()->prepare("SELECT * FROM conference_editions WHERE id = ? AND is_published = 1 LIMIT 1");
    $stmt->execute([(int)$_GET['id']]);
    $edition = $stmt->fetch();
}

if (!$edition) {
    http_response_code(404);
    $pageTitle = 'Conference Not Found';
    require __DIR__ . '/includes/header.php';
    echo '<section class="section container"><div class="empty-state"><div class="icon">🔍</div>'
        . '<p>That conference edition could not be found.</p><a class="btn btn-navy btn-sm" href="' . base_url('conferences.php') . '">All conferences</a></div></section>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = $edition['name'];

$procStmt = db()->prepare("SELECT * FROM proceedings WHERE conference_edition_id = ? ORDER BY created_at DESC");
$procStmt->execute([$edition['id']]);
$proceedings = $procStmt->fetchAll();

$dateLine = '';
if ($edition['start_date']) {
    $dateLine = format_date($edition['start_date'], 'F j, Y');
    if ($edition['end_date'] && $edition['end_date'] !== $edition['start_date']) {
        $dateLine .= ' – ' . format_date($edition['end_date'], 'F j, Y');
    }
}

require __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
  <div class="container">
    <div class="breadcrumb" style="color:#b9c6d6;">
      <a href="<?= base_url('conferences.php') ?>" style="color:#e4c987;">Conferences</a>
    </div>
    <h1><?= e($edition['name']) ?></h1>
    <?php if ($edition['theme']): ?><p>Theme: <?= e($edition['theme']) ?></p><?php endif; ?>
  </div>
</section>

<section class="section">
  <div class="container" style="max-width:860px;">

    <div class="detail-facts">
      <div><span>Year</span><strong><?= (int)$edition['year'] ?></strong></div>
      <?php if ($dateLine): ?><div><span>Dates</span><strong><?= e($dateLine) ?></strong></div><?php endif; ?>
      <?php if ($edition['venue'] || $edition['city']): ?>
        <div><span>Venue</span><strong><?= e(trim($edition['venue'] . ($edition['venue'] && $edition['city'] ? ', ' : '') . $edition['city'])) ?></strong></div>
      <?php endif; ?>
      <?php if ($edition['cfp_open'] && $edition['cfp_deadline']): ?>
        <div><span>Abstract deadline</span><strong><?= format_date($edition['cfp_deadline']) ?></strong></div>
      <?php endif; ?>
    </div>

    <?php if ($edition['registration_open']): ?>
      <p><a class="btn btn-gold" href="<?= base_url('conference-register.php?slug=' . e($edition['slug'])) ?>">Register for this conference</a></p>
    <?php endif; ?>

    <?php if ($edition['description']): ?>
      <div class="prose" style="margin-top:20px;"><?= $edition['description'] ?></div>
    <?php endif; ?>

    <?php if ($edition['cfp_open']): ?>
      <div class="section-head" style="margin-top:40px;"><h2>Call for Papers</h2></div>
      <div class="prose"><?= $edition['cfp_body'] ?: '<p>Abstracts are invited for this conference.</p>' ?></div>
      <?php if ($edition['registration_open']): ?>
        <p style="margin-top:14px;"><a class="btn btn-navy btn-sm" href="<?= base_url('conference-register.php?slug=' . e($edition['slug']) . '&type=presenter') ?>">Submit an abstract / register as a presenter</a></p>
      <?php endif; ?>
    <?php endif; ?>

    <?php if ($edition['registration_open'] && $edition['registration_info']): ?>
      <div class="section-head" style="margin-top:40px;"><h2>Registration &amp; Fees</h2></div>
      <div class="prose"><?= $edition['registration_info'] ?></div>
    <?php endif; ?>

    <?php if ($proceedings): ?>
      <div class="section-head" style="margin-top:40px;"><h2>Proceedings</h2></div>
      <div class="resource-list">
        <?php foreach ($proceedings as $p): ?>
          <div class="resource-row">
            <div>
              <strong><?= e($p['title']) ?></strong>
              <div class="resource-meta"><?= format_filesize((int)$p['file_size']) ?> &middot; <?= (int)$p['downloads'] ?> downloads</div>
            </div>
            <a class="btn btn-gold btn-sm" href="<?= base_url('download.php?type=proceeding&id=' . $p['id']) ?>">Download</a>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
