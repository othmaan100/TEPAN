<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Author Resources';

$resources = db()->query("SELECT * FROM journal_resources ORDER BY sort_order, label")->fetchAll();
$authorPages = nav_pages('authors');

require __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
  <div class="container">
    <h1>For Authors</h1>
    <p>Everything you need to prepare and submit a manuscript to the TEPAN journal.</p>
  </div>
</section>

<section class="section">
  <div class="container" style="max-width:900px;">

    <div class="section-head"><h2>Get started</h2></div>
    <div class="card-grid" style="grid-template-columns:repeat(auto-fill,minmax(220px,1fr));">
      <a class="tile" href="<?= base_url('submit.php') ?>">
        <div class="tile-num">1</div><div class="tile-label">Submit a manuscript</div>
      </a>
      <a class="tile" href="<?= base_url('submission-status.php') ?>">
        <div class="tile-num">2</div><div class="tile-label">Check submission status</div>
      </a>
      <a class="tile" href="<?= base_url('page.php?slug=author-guidelines') ?>">
        <div class="tile-num">§</div><div class="tile-label">Read author guidelines</div>
      </a>
    </div>

    <div class="section-head" style="margin-top:44px;"><h2>Templates &amp; forms</h2></div>
    <?php if ($resources): ?>
      <div class="resource-list">
        <?php foreach ($resources as $r): ?>
          <div class="resource-row">
            <div>
              <strong><?= e($r['label']) ?></strong>
              <?php if ($r['description']): ?><div class="resource-desc"><?= e($r['description']) ?></div><?php endif; ?>
              <div class="resource-meta"><?= strtoupper(pathinfo($r['file_path'], PATHINFO_EXTENSION)) ?> &middot; <?= format_filesize((int)$r['file_size']) ?></div>
            </div>
            <a class="btn btn-gold btn-sm" href="<?= base_url('download.php?type=resource&id=' . $r['id']) ?>">Download</a>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="empty-state"><div class="icon">📄</div><p>Templates will be posted here shortly.</p></div>
    <?php endif; ?>

    <?php if ($authorPages): ?>
      <div class="section-head" style="margin-top:44px;"><h2>Policies for authors</h2></div>
      <ul class="link-list">
        <?php foreach ($authorPages as $p): ?>
          <li><a href="<?= base_url('page.php?slug=' . e($p['slug'])) ?>"><?= e($p['title']) ?></a></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>

  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
