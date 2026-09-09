<?php
require_once __DIR__ . '/includes/functions.php';

$slug = preg_replace('/[^a-z0-9\-]/', '', strtolower($_GET['slug'] ?? ''));
$item = null;
if ($slug !== '') {
    $stmt = db()->prepare(
        "SELECT * FROM announcements WHERE slug = ? AND is_published = 1
         AND (published_at IS NULL OR published_at <= NOW()) LIMIT 1"
    );
    $stmt->execute([$slug]);
    $item = $stmt->fetch();
}

if (!$item) {
    http_response_code(404);
    $pageTitle = 'Announcement Not Found';
    require __DIR__ . '/includes/header.php';
    echo '<section class="section container"><div class="empty-state"><div class="icon">🔍</div>'
        . '<p>That announcement could not be found.</p><a class="btn btn-navy btn-sm" href="' . base_url('announcements.php') . '">All announcements</a></div></section>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = $item['title'];
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
  <div class="container">
    <div class="breadcrumb" style="color:#b9c6d6;">
      <a href="<?= base_url('announcements.php') ?>" style="color:#e4c987;">Announcements</a>
    </div>
    <h1><?= e($item['title']) ?></h1>
    <p><?= format_date($item['published_at'] ?: $item['created_at'], 'F j, Y') ?></p>
  </div>
</section>

<section class="section">
  <div class="container prose" style="max-width:820px;">
    <?= $item['body'] /* trusted admin-authored HTML */ ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
