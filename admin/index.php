<?php
require_once __DIR__ . '/../includes/functions.php';
require __DIR__ . '/includes/auth.php';

$pageTitle = 'Dashboard';
$activeNav = 'dashboard';

$totalJournals = (int) db()->query("SELECT COUNT(*) FROM journals")->fetchColumn();
$totalVolumes = (int) db()->query("SELECT COUNT(DISTINCT volume) FROM journals")->fetchColumn();
$totalProceedings = (int) db()->query("SELECT COUNT(*) FROM proceedings")->fetchColumn();
$totalDownloads = (int) db()->query("SELECT COALESCE(SUM(downloads),0) FROM journals")->fetchColumn()
                 + (int) db()->query("SELECT COALESCE(SUM(downloads),0) FROM proceedings")->fetchColumn();
$unreadMessages = (int) db()->query("SELECT COUNT(*) FROM contact_messages WHERE is_read = 0")->fetchColumn();

$recentJournals = db()->query("SELECT * FROM journals ORDER BY created_at DESC LIMIT 5")->fetchAll();
$recentProceedings = db()->query("SELECT * FROM proceedings ORDER BY created_at DESC LIMIT 5")->fetchAll();

require __DIR__ . '/includes/layout_header.php';
?>

<div class="admin-stats">
  <div class="admin-stat"><strong><?= $totalJournals ?></strong><span>Journal Issues</span></div>
  <div class="admin-stat"><strong><?= $totalVolumes ?></strong><span>Volumes</span></div>
  <div class="admin-stat"><strong><?= $totalProceedings ?></strong><span>Conference Proceedings</span></div>
  <div class="admin-stat"><strong><?= $totalDownloads ?></strong><span>Total Downloads</span></div>
  <div class="admin-stat"><strong><?= $unreadMessages ?></strong><span>Unread Messages</span></div>
</div>

<div class="admin-toolbar">
  <h3 style="margin:0;">Quick Actions</h3>
</div>
<div style="display:flex;gap:12px;margin-bottom:32px;flex-wrap:wrap;">
  <a href="<?= base_url('admin/journal-form.php') ?>" class="btn btn-navy">+ Upload Journal Issue</a>
  <a href="<?= base_url('admin/proceedings-form.php') ?>" class="btn btn-gold">+ Upload Conference Proceedings</a>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
  <div class="admin-card">
    <h3 style="margin-top:0;">Recent Journal Issues</h3>
    <?php if ($recentJournals): ?>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Title</th><th>Vol/Issue</th><th>Date</th></tr></thead>
          <tbody>
          <?php foreach ($recentJournals as $j): ?>
            <tr>
              <td><a href="<?= base_url('admin/journal-form.php?id=' . $j['id']) ?>"><?= e(mb_strimwidth($j['title'],0,30,'…')) ?></a></td>
              <td>V<?= (int)$j['volume'] ?>·N<?= (int)$j['issue'] ?></td>
              <td><?= format_date($j['publication_date'], 'M j, Y') ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <p style="color:var(--text-muted);">No journals uploaded yet.</p>
    <?php endif; ?>
  </div>

  <div class="admin-card">
    <h3 style="margin-top:0;">Recent Proceedings</h3>
    <?php if ($recentProceedings): ?>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Title</th><th>Conference</th><th>Year</th></tr></thead>
          <tbody>
          <?php foreach ($recentProceedings as $p): ?>
            <tr>
              <td><a href="<?= base_url('admin/proceedings-form.php?id=' . $p['id']) ?>"><?= e(mb_strimwidth($p['title'],0,30,'…')) ?></a></td>
              <td><?= e(mb_strimwidth($p['conference_name'],0,20,'…')) ?></td>
              <td><?= (int)$p['conference_year'] ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <p style="color:var(--text-muted);">No proceedings uploaded yet.</p>
    <?php endif; ?>
  </div>
</div>

<?php require __DIR__ . '/includes/layout_footer.php'; ?>
