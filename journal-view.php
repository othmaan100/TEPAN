<?php
require_once __DIR__ . '/includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare("SELECT * FROM journals WHERE id = ?");
$stmt->execute([$id]);
$journal = $stmt->fetch();

if (!$journal) {
    http_response_code(404);
    $pageTitle = 'Not Found';
    require __DIR__ . '/includes/header.php';
    echo '<section class="section container"><div class="empty-state"><div class="icon">🔍</div><p>Journal issue not found.</p><a class="btn btn-navy btn-sm" href="' . base_url('journals.php') . '">Back to Journals</a></div></section>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = $journal['title'];

$stmt = db()->prepare("SELECT * FROM journals WHERE volume = ? AND id != ? ORDER BY issue DESC LIMIT 4");
$stmt->execute([$journal['volume'], $journal['id']]);
$related = $stmt->fetchAll();

$byAuthor = [];
if (!empty($journal['authors'])) {
    $stmt = db()->prepare("SELECT * FROM journals WHERE authors = ? AND id != ? ORDER BY publication_date DESC LIMIT 4");
    $stmt->execute([$journal['authors'], $journal['id']]);
    $byAuthor = $stmt->fetchAll();
}

require __DIR__ . '/includes/header.php';
?>

<section class="section">
  <div class="container">
    <div class="breadcrumb">
      <a href="<?= base_url('journals.php') ?>">Journals</a> <span class="sep">/</span>
      <a href="<?= base_url('journals.php?view=volumes&volume=' . (int)$journal['volume']) ?>">Volume <?= (int)$journal['volume'] ?></a>
      <span class="sep">/</span> <?= e($journal['title']) ?>
    </div>

    <div class="detail-grid">
      <div class="detail-cover">
        <div class="pub-cover" style="aspect-ratio:3/4;">
          <?php if ($journal['is_current']): ?><span class="badge">Current Issue</span><?php endif; ?>
          <?php if ($journal['cover_image']): ?>
            <img src="<?= base_url($journal['cover_image']) ?>" alt="<?= e($journal['title']) ?>">
          <?php else: ?>
            <div class="cover-fallback">
              <div class="cover-vol">Vol. <?= (int)$journal['volume'] ?>, No. <?= (int)$journal['issue'] ?></div>
              <div class="cover-title"><?= e($journal['title']) ?></div>
            </div>
          <?php endif; ?>
        </div>
      </div>
      <div class="detail-info">
        <h1><?= e($journal['title']) ?></h1>
        <?php if (!empty($journal['authors'])): ?>
          <p class="detail-byline">By
            <a href="<?= base_url('journals.php?view=authors&author=' . rawurlencode($journal['authors'])) ?>"><?= e($journal['authors']) ?></a>
          </p>
        <?php endif; ?>
        <p><?= nl2br(e($journal['description'])) ?: '<em>No abstract provided.</em>' ?></p>

        <div class="detail-facts">
          <div><span>Volume</span><strong><?= (int)$journal['volume'] ?></strong></div>
          <div><span>Issue</span><strong><?= (int)$journal['issue'] ?></strong></div>
          <div><span>Published</span><strong><?= format_date($journal['publication_date']) ?></strong></div>
          <?php if ($journal['editor']): ?><div><span>Editor</span><strong><?= e($journal['editor']) ?></strong></div><?php endif; ?>
          <?php if (!empty($journal['authors'])): ?><div><span>Author(s)</span><strong><?= e($journal['authors']) ?></strong></div><?php endif; ?>
          <?php if (defined('JOURNAL_ISSN') && JOURNAL_ISSN): ?><div><span>ISSN (Print)</span><strong><?= e(JOURNAL_ISSN) ?></strong></div><?php endif; ?>
          <?php if (defined('JOURNAL_EISSN') && JOURNAL_EISSN): ?><div><span>eISSN (Online)</span><strong><?= e(JOURNAL_EISSN) ?></strong></div><?php endif; ?>
          <div><span>File Size</span><strong><?= format_filesize((int)$journal['file_size']) ?></strong></div>
          <div><span>Downloads</span><strong><?= (int)$journal['downloads'] ?></strong></div>
        </div>

        <div class="pub-actions">
          <a href="<?= base_url('download.php?type=journal&id=' . $journal['id']) ?>" class="btn btn-gold">Download Full Issue (PDF)</a>
          <a href="<?= base_url('journals.php?view=volumes&volume=' . (int)$journal['volume']) ?>" class="btn btn-navy">More from Volume <?= (int)$journal['volume'] ?></a>
        </div>
      </div>
    </div>

    <?php
    $card_list = function (array $rows) {
        foreach ($rows as $j): ?>
          <div class="pub-card">
            <div class="pub-cover">
              <?php if ($j['cover_image']): ?>
                <img src="<?= base_url($j['cover_image']) ?>" alt="<?= e($j['title']) ?>">
              <?php else: ?>
                <div class="cover-fallback">
                  <div class="cover-vol">Vol. <?= (int)$j['volume'] ?>, No. <?= (int)$j['issue'] ?></div>
                  <div class="cover-title"><?= e($j['title']) ?></div>
                </div>
              <?php endif; ?>
            </div>
            <div class="pub-body">
              <div class="pub-meta"><?= format_date($j['publication_date'], 'M j, Y') ?></div>
              <h3><?= e($j['title']) ?></h3>
              <?php if (!empty($j['authors'])): ?><div class="pub-authors">By <?= e($j['authors']) ?></div><?php endif; ?>
              <div class="pub-actions">
                <a href="<?= base_url('journal-view.php?id=' . $j['id']) ?>" class="btn btn-navy btn-sm">View</a>
              </div>
            </div>
          </div>
        <?php endforeach;
    };
    ?>

    <?php if ($byAuthor): ?>
      <div class="section-head" style="margin-top:48px;"><h2>More by <?= e($journal['authors']) ?></h2></div>
      <div class="card-grid"><?php $card_list($byAuthor); ?></div>
    <?php endif; ?>

    <?php if ($related): ?>
      <div class="section-head" style="margin-top:48px;"><h2>More from Volume <?= (int)$journal['volume'] ?></h2></div>
      <div class="card-grid"><?php $card_list($related); ?></div>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
