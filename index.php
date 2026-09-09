<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Home';

$currentIssue = db()->query("SELECT * FROM journals WHERE is_current = 1 ORDER BY publication_date DESC LIMIT 1")->fetch();
if (!$currentIssue) {
    $currentIssue = db()->query("SELECT * FROM journals ORDER BY publication_date DESC LIMIT 1")->fetch();
}
$recentJournals = db()->query("SELECT * FROM journals ORDER BY publication_date DESC LIMIT 3")->fetchAll();
$recentProceedings = db()->query("SELECT * FROM proceedings ORDER BY created_at DESC LIMIT 3")->fetchAll();

$totalJournals = (int) db()->query("SELECT COUNT(*) FROM journals")->fetchColumn();
$totalVolumes = (int) db()->query("SELECT COUNT(DISTINCT volume) FROM journals")->fetchColumn();
$totalProceedings = (int) db()->query("SELECT COUNT(*) FROM proceedings")->fetchColumn();
$homeAnnouncements = latest_announcements(3);

require __DIR__ . '/includes/header.php';
?>

<section class="hero">
  <div class="container hero-inner">
    <div class="eyebrow">Journals &middot; Proceedings &middot; Research</div>
    <h1>Advancing Technology Education Across Nigeria</h1>
    <p>TEPAN publishes peer-reviewed conference journals and hosts the official record of proceedings from our
      annual conferences — a growing archive for practitioners, researchers, and policymakers in technology education.</p>
    <div class="hero-actions">
      <a href="<?= base_url('journals.php?view=current') ?>" class="btn btn-gold">Read Current Issue</a>
      <a href="<?= base_url('proceedings.php') ?>" class="btn btn-outline">Conference Proceedings</a>
    </div>
    <div class="stats-row">
      <div class="stat-card"><strong><?= $totalVolumes ?></strong><span>Journal Volumes</span></div>
      <div class="stat-card"><strong><?= $totalJournals ?></strong><span>Published Issues</span></div>
      <div class="stat-card"><strong><?= $totalProceedings ?></strong><span>Conference Proceedings</span></div>
    </div>
  </div>
</section>

<?php if ($homeAnnouncements): ?>
<section class="announce-strip">
  <div class="container">
    <span class="announce-strip-label">📢 Announcements</span>
    <ul>
      <?php foreach ($homeAnnouncements as $a): ?>
        <li><a href="<?= base_url('announcement.php?slug=' . e($a['slug'])) ?>"><?= e($a['title']) ?></a>
          <span class="announce-strip-date"><?= format_date($a['published_at'] ?: $a['created_at'], 'M j') ?></span></li>
      <?php endforeach; ?>
    </ul>
    <a href="<?= base_url('announcements.php') ?>" class="announce-strip-all">All &rarr;</a>
  </div>
</section>
<?php endif; ?>

<section class="section home-current">
  <div class="container">
    <div class="section-head">
      <div>
        <div class="eyebrow">Latest Publication</div>
        <h2>Current Issue</h2>
      </div>
      <a href="<?= base_url('journals.php?view=volumes') ?>" class="btn btn-navy btn-sm">Browse All Volumes</a>
    </div>

    <?php if ($currentIssue): ?>
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
          <div class="pub-meta">Volume <?= (int)$currentIssue['volume'] ?>, Issue <?= (int)$currentIssue['issue'] ?> &middot; <?= format_date($currentIssue['publication_date']) ?></div>
          <h1><?= e($currentIssue['title']) ?></h1>
          <p><?= nl2br(e($currentIssue['description'])) ?></p>
          <div class="pub-actions">
            <a href="<?= base_url('journal-view.php?id=' . $currentIssue['id']) ?>" class="btn btn-navy">View Details</a>
            <a href="<?= base_url('download.php?type=journal&id=' . $currentIssue['id']) ?>" class="btn btn-gold">Download PDF</a>
          </div>
        </div>
      </div>
    <?php else: ?>
      <div class="empty-state">
        <div class="icon">📰</div>
        <p>No journal issues have been published yet. Please check back soon.</p>
      </div>
    <?php endif; ?>
  </div>
</section>

<section class="section section-alt home-recent">
  <div class="container">
    <div class="section-head">
      <div>
        <div class="eyebrow">Archive</div>
        <h2>Recently Published Journals</h2>
      </div>
      <a href="<?= base_url('journals.php?view=years') ?>" class="btn btn-navy btn-sm">Browse by Year</a>
    </div>

    <?php if ($recentJournals): ?>
      <div class="card-grid">
        <?php foreach ($recentJournals as $j): ?>
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
              <div class="pub-meta">Vol. <?= (int)$j['volume'] ?> &middot; <?= format_date($j['publication_date'], 'M Y') ?></div>
              <h3><?= e($j['title']) ?></h3>
              <div class="pub-actions">
                <a href="<?= base_url('journal-view.php?id=' . $j['id']) ?>" class="btn btn-navy btn-sm">View</a>
                <a href="<?= base_url('download.php?type=journal&id=' . $j['id']) ?>" class="btn btn-gold btn-sm">Download</a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="empty-state"><div class="icon">📰</div><p>No journals published yet.</p></div>
    <?php endif; ?>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="section-head">
      <div>
        <div class="eyebrow">Separate Archive</div>
        <h2>Conference Proceedings</h2>
      </div>
      <a href="<?= base_url('proceedings.php') ?>" class="btn btn-navy btn-sm">View All Proceedings</a>
    </div>

    <?php if ($recentProceedings): ?>
      <div class="card-grid">
        <?php foreach ($recentProceedings as $p): ?>
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
              <div class="pub-meta"><?= e($p['conference_name']) ?> &middot; <?= (int)$p['conference_year'] ?></div>
              <h3><?= e($p['title']) ?></h3>
              <div class="pub-actions">
                <a href="<?= base_url('download.php?type=proceeding&id=' . $p['id']) ?>" class="btn btn-gold btn-sm">Download PDF</a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="empty-state"><div class="icon">🗂️</div><p>No conference proceedings uploaded yet.</p></div>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
