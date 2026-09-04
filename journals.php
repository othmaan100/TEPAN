<?php
require_once __DIR__ . '/includes/functions.php';

$view = $_GET['view'] ?? 'current';
if (!in_array($view, ['current', 'volumes', 'years'], true)) $view = 'current';
$volumeParam = isset($_GET['volume']) ? (int)$_GET['volume'] : null;
$yearParam = isset($_GET['year']) ? (int)$_GET['year'] : null;

$pageTitle = 'Journals';

function journal_card($j)
{
    ?>
    <div class="pub-card">
      <div class="pub-cover">
        <?php if ($j['is_current']): ?><span class="badge">Current</span><?php endif; ?>
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
        <div class="pub-meta">Vol. <?= (int)$j['volume'] ?>, Issue <?= (int)$j['issue'] ?> &middot; <?= format_date($j['publication_date'], 'M j, Y') ?></div>
        <h3><?= e($j['title']) ?></h3>
        <p><?= e(mb_strimwidth((string)$j['description'], 0, 110, '…')) ?></p>
        <div class="pub-actions">
          <a href="<?= base_url('journal-view.php?id=' . $j['id']) ?>" class="btn btn-navy btn-sm">View</a>
          <a href="<?= base_url('download.php?type=journal&id=' . $j['id']) ?>" class="btn btn-gold btn-sm">Download</a>
        </div>
      </div>
    </div>
    <?php
}

require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
  <div class="container">
    <h1>Journal Archive</h1>
    <p>Browse TEPAN's peer-reviewed conference journals by current issue, publication volume, or year.</p>
  </div>
</section>

<section class="section">
  <div class="container">

    <div class="tabs">
      <a href="<?= base_url('journals.php?view=current') ?>" class="<?= $view === 'current' ? 'active' : '' ?>">Current Issue</a>
      <a href="<?= base_url('journals.php?view=volumes') ?>" class="<?= $view === 'volumes' ? 'active' : '' ?>">Browse by Volume</a>
      <a href="<?= base_url('journals.php?view=years') ?>" class="<?= $view === 'years' ? 'active' : '' ?>">Browse by Year</a>
    </div>

    <?php if ($view === 'current'):
      $currentIssue = db()->query("SELECT * FROM journals WHERE is_current = 1 ORDER BY publication_date DESC LIMIT 1")->fetch();
      if (!$currentIssue) {
          $currentIssue = db()->query("SELECT * FROM journals ORDER BY publication_date DESC LIMIT 1")->fetch();
      }
      if ($currentIssue): ?>
        <div class="detail-grid">
          <div class="detail-cover">
            <div class="pub-cover" style="aspect-ratio:3/4;">
              <?php if ($currentIssue['cover_image']): ?>
                <img src="<?= base_url($currentIssue['cover_image']) ?>" alt="<?= e($currentIssue['title']) ?>">
              <?php else: ?>
                <div class="cover-fallback">
                  <div class="cover-vol">Volume <?= (int)$currentIssue['volume'] ?>, Issue <?= (int)$currentIssue['issue'] ?></div>
                  <div class="cover-title"><?= e($currentIssue['title']) ?></div>
                </div>
              <?php endif; ?>
            </div>
          </div>
          <div class="detail-info">
            <div class="pub-meta">Volume <?= (int)$currentIssue['volume'] ?>, Issue <?= (int)$currentIssue['issue'] ?> &middot; Published <?= format_date($currentIssue['publication_date']) ?></div>
            <h1><?= e($currentIssue['title']) ?></h1>
            <p><?= nl2br(e($currentIssue['description'])) ?></p>
            <div class="pub-actions">
              <a href="<?= base_url('journal-view.php?id=' . $currentIssue['id']) ?>" class="btn btn-navy">Full Details</a>
              <a href="<?= base_url('download.php?type=journal&id=' . $currentIssue['id']) ?>" class="btn btn-gold">Download PDF</a>
            </div>
          </div>
        </div>
      <?php else: ?>
        <div class="empty-state"><div class="icon">📰</div><p>No journal issues have been published yet. Please check back soon.</p></div>
      <?php endif; ?>

    <?php elseif ($view === 'volumes'):
      if ($volumeParam):
        $stmt = db()->prepare("SELECT * FROM journals WHERE volume = ? ORDER BY issue DESC, publication_date DESC");
        $stmt->execute([$volumeParam]);
        $issues = $stmt->fetchAll();
        ?>
        <div class="breadcrumb">
          <a href="<?= base_url('journals.php?view=volumes') ?>">All Volumes</a> <span class="sep">/</span> Volume <?= $volumeParam ?>
        </div>
        <div class="section-head"><h2>Volume <?= $volumeParam ?></h2></div>
        <?php if ($issues): ?>
          <div class="card-grid">
            <?php foreach ($issues as $j) journal_card($j); ?>
          </div>
        <?php else: ?>
          <div class="empty-state"><div class="icon">📁</div><p>No issues found for this volume.</p></div>
        <?php endif; ?>
      <?php else:
        $volumes = get_volume_summary(); ?>
        <div class="section-head"><h2>All Volumes</h2></div>
        <?php if ($volumes): ?>
          <div class="tile-grid">
            <?php foreach ($volumes as $v): ?>
              <a class="tile" href="<?= base_url('journals.php?view=volumes&volume=' . (int)$v['volume']) ?>">
                <div class="tile-num">Vol. <?= (int)$v['volume'] ?></div>
                <div class="tile-label"><?= (int)$v['issue_count'] ?> issue<?= $v['issue_count'] == 1 ? '' : 's' ?></div>
                <div class="tile-sub"><?= $v['from_year'] == $v['to_year'] ? $v['from_year'] : $v['from_year'] . '–' . $v['to_year'] ?></div>
              </a>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="empty-state"><div class="icon">📁</div><p>No journal volumes published yet.</p></div>
        <?php endif; ?>
      <?php endif; ?>

    <?php elseif ($view === 'years'):
      if ($yearParam):
        $stmt = db()->prepare("SELECT * FROM journals WHERE pub_year = ? ORDER BY publication_date DESC");
        $stmt->execute([$yearParam]);
        $issues = $stmt->fetchAll();
        ?>
        <div class="breadcrumb">
          <a href="<?= base_url('journals.php?view=years') ?>">All Years</a> <span class="sep">/</span> <?= $yearParam ?>
        </div>
        <div class="section-head"><h2><?= $yearParam ?> Publications</h2></div>
        <?php if ($issues): ?>
          <div class="card-grid">
            <?php foreach ($issues as $j) journal_card($j); ?>
          </div>
        <?php else: ?>
          <div class="empty-state"><div class="icon">📁</div><p>No issues found for this year.</p></div>
        <?php endif; ?>
      <?php else:
        $years = get_year_summary(); ?>
        <div class="section-head"><h2>All Years</h2></div>
        <?php if ($years): ?>
          <div class="tile-grid">
            <?php foreach ($years as $y): ?>
              <a class="tile" href="<?= base_url('journals.php?view=years&year=' . (int)$y['pub_year']) ?>">
                <div class="tile-num"><?= (int)$y['pub_year'] ?></div>
                <div class="tile-label"><?= (int)$y['issue_count'] ?> issue<?= $y['issue_count'] == 1 ? '' : 's' ?></div>
              </a>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="empty-state"><div class="icon">📁</div><p>No journal publications yet.</p></div>
        <?php endif; ?>
      <?php endif; ?>
    <?php endif; ?>

  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
