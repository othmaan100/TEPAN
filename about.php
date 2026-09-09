<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'About';
require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
  <div class="container">
    <h1>About TEPAN</h1>
    <p>Technology Education Practitioners Association of Nigeria</p>
  </div>
</section>

<section class="section">
  <div class="container" style="max-width:820px;">
    <div style="display:flex;align-items:center;gap:24px;margin-bottom:28px;flex-wrap:wrap;">
      <img src="<?= base_url('assets/images/TEPAN_logo.jpeg') ?>" alt="TEPAN Official Logo" style="width:110px;height:110px;object-fit:contain;flex-shrink:0;">
      <div style="flex:1;min-width:260px;">
        <h2 style="margin-top:0;">Who We Are</h2>
        <p style="margin-bottom:0;">The Technology Education Practitioners Association of Nigeria (TEPAN) is a professional body bringing together lecturers, researchers, instructors, and industry practitioners committed to advancing the quality and reach of technology education across Nigeria.</p>
      </div>
    </div>

    <h2>What We Do</h2>
    <p>TEPAN publishes a peer-reviewed conference journal that showcases original research in technology
      education, and organizes an annual conference whose proceedings are published and archived for the wider
      academic community. Both the journal and the conference proceedings are freely accessible through this
      website.</p>

    <h2>Our Publications</h2>
    <p>Our <a href="<?= base_url('journals.php') ?>">journal archive</a> is organized by volume and publication
      year, with the latest research always available under the current issue. Separately, our
      <a href="<?= base_url('proceedings.php') ?>">conference proceedings archive</a> preserves the papers
      presented at each annual TEPAN conference.</p>
    <?php if (defined('JOURNAL_ISSN') && (JOURNAL_ISSN || JOURNAL_EISSN)): ?>
      <p>The TEPAN journal is registered as
        <?php if (JOURNAL_ISSN): ?><strong>ISSN <?= e(JOURNAL_ISSN) ?></strong> (print)<?php endif; ?><?php if (JOURNAL_ISSN && JOURNAL_EISSN): ?> and <?php endif; ?><?php if (JOURNAL_EISSN): ?><strong>eISSN <?= e(JOURNAL_EISSN) ?></strong> (online)<?php endif; ?>.</p>
    <?php endif; ?>

    <h2>Get Involved</h2>
    <p>To learn more about membership, submitting a paper, or partnering with TEPAN, please
      <a href="<?= base_url('contact.php') ?>">get in touch with us</a>.</p>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
