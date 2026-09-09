<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Conferences';

$today = date('Y-m-d');
$upcoming = db()->query(
    "SELECT * FROM conference_editions
     WHERE is_published = 1 AND (end_date IS NULL OR end_date >= '$today')
     ORDER BY COALESCE(start_date, MAKEDATE(year,1)) ASC"
)->fetchAll();
$past = db()->query(
    "SELECT * FROM conference_editions
     WHERE is_published = 1 AND end_date IS NOT NULL AND end_date < '$today'
     ORDER BY end_date DESC"
)->fetchAll();

require __DIR__ . '/includes/header.php';

function conf_card(array $c): void { ?>
  <article class="pub-card">
    <div class="pub-cover">
      <?php if ($c['banner_image']): ?>
        <img src="<?= base_url($c['banner_image']) ?>" alt="<?= e($c['name']) ?>">
      <?php else: ?>
        <div class="cover-fallback">
          <div class="cover-vol"><?= e($c['edition_label'] ?: $c['year']) ?></div>
          <div class="cover-title"><?= e($c['name']) ?></div>
        </div>
      <?php endif; ?>
      <?php if ($c['cfp_open']): ?><span class="badge">Call for Papers</span><?php endif; ?>
    </div>
    <div class="pub-body">
      <div class="pub-meta"><?= (int)$c['year'] ?><?= $c['city'] ? ' &middot; ' . e($c['city']) : '' ?></div>
      <h3><?= e($c['name']) ?></h3>
      <?php if ($c['theme']): ?><p><em><?= e($c['theme']) ?></em></p><?php endif; ?>
      <div class="pub-actions">
        <a class="btn btn-navy btn-sm" href="<?= base_url('conference.php?slug=' . e($c['slug'])) ?>">Details</a>
        <?php if ($c['registration_open']): ?>
          <a class="btn btn-gold btn-sm" href="<?= base_url('conference-register.php?slug=' . e($c['slug'])) ?>">Register</a>
        <?php endif; ?>
      </div>
    </div>
  </article>
<?php }
?>
<section class="page-hero">
  <div class="container">
    <h1>TEPAN Conferences</h1>
    <p>Annual gatherings of technology education practitioners — calls for papers, registration and published proceedings.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <?php if (!$upcoming && !$past): ?>
      <div class="empty-state"><div class="icon">🎓</div><p>Conference editions will be listed here soon.</p></div>
    <?php endif; ?>

    <?php if ($upcoming): ?>
      <div class="section-head"><h2>Upcoming &amp; current</h2></div>
      <div class="card-grid"><?php foreach ($upcoming as $c) conf_card($c); ?></div>
    <?php endif; ?>

    <?php if ($past): ?>
      <div class="section-head" style="margin-top:44px;"><h2>Past editions</h2></div>
      <div class="card-grid"><?php foreach ($past as $c) conf_card($c); ?></div>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
