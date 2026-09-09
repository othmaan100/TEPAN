</main>
<footer class="site-footer">
  <div class="container footer-grid">
    <div>
      <div class="footer-brand-row">
        <img src="<?= base_url('assets/images/TEPAN_logo.jpeg') ?>" alt="TEPAN Logo" class="footer-logo">
        <h4 style="margin:0;">TEPAN</h4>
      </div>
      <p>Technology Education Practitioners Association of Nigeria promotes excellence in technology education through research publication, conferences and professional development.</p>
      <?php if (defined('JOURNAL_ISSN') && (JOURNAL_ISSN || JOURNAL_EISSN)): ?>
        <p class="footer-issn">
          <?php if (JOURNAL_ISSN): ?>ISSN <?= e(JOURNAL_ISSN) ?><?php endif; ?><?php if (JOURNAL_ISSN && JOURNAL_EISSN): ?> &middot; <?php endif; ?><?php if (JOURNAL_EISSN): ?>eISSN <?= e(JOURNAL_EISSN) ?><?php endif; ?>
        </p>
      <?php endif; ?>
    </div>
    <div>
      <h4>For Authors</h4>
      <ul class="footer-links">
        <li><a href="<?= base_url('submit.php') ?>">Submit a Manuscript</a></li>
        <li><a href="<?= base_url('submission-status.php') ?>">Submission Status</a></li>
        <li><a href="<?= base_url('page.php?slug=author-guidelines') ?>">Author Guidelines</a></li>
        <li><a href="<?= base_url('author-resources.php') ?>">Templates &amp; Forms</a></li>
        <li><a href="<?= base_url('page.php?slug=publication-ethics') ?>">Publication Ethics</a></li>
      </ul>
    </div>
    <div>
      <h4>Association</h4>
      <ul class="footer-links">
        <li><a href="<?= base_url('about.php') ?>">About TEPAN</a></li>
        <li><a href="<?= base_url('editorial-board.php') ?>">Editorial Board</a></li>
        <li><a href="<?= base_url('conferences.php') ?>">Conferences</a></li>
        <li><a href="<?= base_url('announcements.php') ?>">Announcements</a></li>
        <li><a href="<?= base_url('contact.php') ?>">Contact Us</a></li>
        <li><a href="<?= base_url('admin/login.php') ?>">Admin Login</a></li>
      </ul>
    </div>
    <div>
      <h4>Contact</h4>
      <ul class="footer-links footer-contact-list">
        <li>
          <span class="footer-contact-item">
            <svg class="footer-contact-icon" viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
            <a href="mailto:editoratepan@gmail.com">editoratepan@gmail.com</a>
          </span>
        </li>
        <li>
          <span class="footer-contact-item">
            <svg class="footer-contact-icon" viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            <a href="tel:07036120387">07036120387</a>
          </span>
        </li>
        <li>
          <span class="footer-contact-item">
            <svg class="footer-contact-icon" viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            <a href="tel:08036899034">08036899034</a>
          </span>
        </li>
        <li>
          <span class="footer-contact-item">
            <svg class="footer-contact-icon" viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            <a href="tel:08036290334">08036290334</a>
          </span>
        </li>
        <li>
          <span class="footer-contact-item">
            <svg class="footer-contact-icon" viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
            <span>Abuja, Nigeria</span>
          </span>
        </li>
      </ul>
    </div>
  </div>
  <div class="footer-subscribe">
    <div class="container">
      <div>
        <strong>Get the &ldquo;New Issue&rdquo; email</strong>
        <span>Journal issue alerts, calls for papers and announcements.</span>
      </div>
      <?php require __DIR__ . '/subscribe_form.php'; ?>
    </div>
  </div>
  <div class="footer-bottom">
    <div class="container">
      &copy; <?= date('Y') ?> Technology Education Practitioners Association of Nigeria. All rights reserved.
    </div>
  </div>
</footer>
<script src="<?= base_url('assets/js/main.js') ?>"></script>
</body>
</html>
