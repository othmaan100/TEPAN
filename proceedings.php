<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Conference Proceedings';

$yearParam = isset($_GET['year']) ? (int)$_GET['year'] : null;
$years = get_distinct_proceeding_years();

if ($yearParam) {
    $stmt = db()->prepare("SELECT * FROM proceedings WHERE conference_year = ? ORDER BY created_at DESC");
    $stmt->execute([$yearParam]);
} else {
    $stmt = db()->query("SELECT * FROM proceedings ORDER BY conference_year DESC, created_at DESC");
}
$proceedings = $stmt->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
  <div class="container">
    <h1>Conference Proceedings</h1>
    <p>The official record of papers presented at TEPAN annual conferences — distinct from the association's journals, and available here for viewing and download.</p>
  </div>
</section>

<section class="section">
  <div class="container">

    <?php if ($years): ?>
      <div class="tabs">
        <a href="<?= base_url('proceedings.php') ?>" class="<?= !$yearParam ? 'active' : '' ?>">All Years</a>
        <?php foreach ($years as $y): ?>
          <a href="<?= base_url('proceedings.php?year=' . (int)$y['conference_year']) ?>" class="<?= $yearParam === (int)$y['conference_year'] ? 'active' : '' ?>">
            <?= (int)$y['conference_year'] ?> <span style="opacity:.6;font-weight:400;">(<?= (int)$y['item_count'] ?>)</span>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if ($proceedings): ?>
      <div class="card-grid">
        <?php foreach ($proceedings as $p): ?>
          <div class="pub-card">
            <div class="pub-cover">
              <?php if ($p['cover_image']): ?>
                <img src="<?= base_url($p['cover_image']) ?>" alt="<?= e($p['title']) ?>">
              <?php else: ?>
                <div class="cover-fallback">
                  <div class="cover-vol"><?= (int)$p['conference_year'] ?></div>
                  <div class="cover-title"><?= e($p['title']) ?></div>
                </div>
              <?php endif; ?>
            </div>
            <div class="pub-body">
              <div class="pub-meta"><?= e($p['conference_name']) ?><?= $p['location'] ? ' &middot; ' . e($p['location']) : '' ?></div>
              <h3><?= e($p['title']) ?></h3>
              <p><?= e(mb_strimwidth((string)$p['description'], 0, 110, '…')) ?></p>
              <div class="pub-meta">File size: <?= format_filesize((int)$p['file_size']) ?> &middot; <?= (int)$p['downloads'] ?> downloads</div>
              <div class="pub-actions">
                <a href="<?= base_url('download.php?type=proceeding&id=' . $p['id']) ?>" class="btn btn-gold btn-sm">Download PDF</a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="empty-state">
        <div class="icon">🗂️</div>
        <p><?= $yearParam ? 'No proceedings found for ' . $yearParam . '.' : 'No conference proceedings have been uploaded yet. Please check back soon.' ?></p>
      </div>
    <?php endif; ?>

  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
