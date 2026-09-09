<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Editorial Board';

$members = db()->query("SELECT * FROM board_members ORDER BY sort_order, name")->fetchAll();
$groups = [
    'editor_in_chief'  => 'Editor-in-Chief',
    'associate_editor' => 'Associate Editors',
    'board_member'     => 'Editorial Board Members',
];
$byRole = ['editor_in_chief' => [], 'associate_editor' => [], 'board_member' => []];
foreach ($members as $m) {
    $byRole[$m['role']][] = $m;
}

require __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
  <div class="container">
    <h1>Editorial Board</h1>
    <p>The scholars who lead and oversee peer review for the TEPAN journal.</p>
  </div>
</section>

<section class="section">
  <div class="container" style="max-width:960px;">
    <?php if (!$members): ?>
      <div class="empty-state"><div class="icon">👥</div><p>The editorial board will be listed here shortly.</p></div>
    <?php endif; ?>

    <?php foreach ($groups as $key => $label): if (!$byRole[$key]) continue; ?>
      <div class="section-head" style="margin-top:8px;"><h2><?= e($label) ?></h2></div>
      <div class="board-grid">
        <?php foreach ($byRole[$key] as $m): ?>
          <article class="board-card">
            <div class="board-photo">
              <?php if ($m['photo']): ?>
                <img src="<?= base_url($m['photo']) ?>" alt="<?= e($m['name']) ?>">
              <?php else: ?>
                <span><?= e(strtoupper(mb_substr($m['name'], 0, 1))) ?></span>
              <?php endif; ?>
            </div>
            <div class="board-info">
              <h3><?= e($m['name']) ?></h3>
              <?php if ($m['position']): ?><div class="board-position"><?= e($m['position']) ?></div><?php endif; ?>
              <?php if ($m['affiliation']): ?><div class="board-affil"><?= e($m['affiliation']) ?><?= $m['country'] ? ', ' . e($m['country']) : '' ?></div><?php endif; ?>
              <?php if ($m['bio']): ?><p class="board-bio"><?= nl2br(e($m['bio'])) ?></p><?php endif; ?>
              <div class="board-links">
                <?php if ($m['orcid']): ?><a href="https://orcid.org/<?= e($m['orcid']) ?>" target="_blank" rel="noopener">ORCID</a><?php endif; ?>
                <?php if ($m['email']): ?><a href="mailto:<?= e($m['email']) ?>">Email</a><?php endif; ?>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
