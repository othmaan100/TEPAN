<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Announcements';

$items = db()->query(
    "SELECT * FROM announcements
     WHERE is_published = 1 AND (published_at IS NULL OR published_at <= NOW())
     ORDER BY COALESCE(published_at, created_at) DESC"
)->fetchAll();

require __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
  <div class="container">
    <h1>Announcements</h1>
    <p>Calls for papers, new issue alerts, deadlines and association news.</p>
  </div>
</section>

<section class="section">
  <div class="container" style="max-width:820px;">

    <div class="subscribe-box">
      <strong>Get the &ldquo;New Issue&rdquo; email</strong>
      <p>Be notified whenever a new journal issue or announcement is published.</p>
      <?php require __DIR__ . '/includes/subscribe_form.php'; ?>
    </div>

    <?php if ($items): ?>
      <div class="announce-list">
        <?php foreach ($items as $a): ?>
          <article class="announce-item">
            <div class="announce-date"><?= format_date($a['published_at'] ?: $a['created_at'], 'F j, Y') ?></div>
            <h3><a href="<?= base_url('announcement.php?slug=' . e($a['slug'])) ?>"><?= e($a['title']) ?></a></h3>
            <p><?= e(mb_strimwidth(trim(strip_tags((string)$a['body'])), 0, 220, '…')) ?></p>
            <a class="btn btn-navy btn-sm" href="<?= base_url('announcement.php?slug=' . e($a['slug'])) ?>">Read more</a>
          </article>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="empty-state"><div class="icon">📢</div><p>No announcements yet.</p></div>
    <?php endif; ?>

  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
