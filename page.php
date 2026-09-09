<?php
require_once __DIR__ . '/includes/functions.php';

$slug = preg_replace('/[^a-z0-9\-]/', '', strtolower($_GET['slug'] ?? ''));
$page = null;
if ($slug !== '') {
    $stmt = db()->prepare("SELECT * FROM pages WHERE slug = ? AND is_published = 1 LIMIT 1");
    $stmt->execute([$slug]);
    $page = $stmt->fetch();
}

if (!$page) {
    http_response_code(404);
    $pageTitle = 'Page Not Found';
    require __DIR__ . '/includes/header.php';
    echo '<section class="section container"><div class="empty-state"><div class="icon">🔍</div>'
        . '<p>That page could not be found.</p><a class="btn btn-navy btn-sm" href="' . base_url('index.php') . '">Home</a></div></section>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = $page['title'];
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
  <div class="container">
    <h1><?= e($page['title']) ?></h1>
  </div>
</section>

<section class="section">
  <div class="container prose" style="max-width:820px;">
    <?= $page['body'] /* trusted admin-authored HTML */ ?>
    <p class="page-updated">Last updated <?= format_date($page['updated_at'], 'F j, Y') ?></p>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
