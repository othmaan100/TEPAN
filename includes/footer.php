</main>
<footer class="site-footer">
  <div class="container footer-grid">
    <div>
      <div class="footer-brand-row">
        <img src="<?= base_url('assets/images/TEPAN_logo.jpeg') ?>" alt="TEPAN Logo" class="footer-logo">
        <h4 style="margin:0;">TEPAN</h4>
      </div>
      <p>Technology Education Practitioners Association of Nigeria promotes excellence in technology education through research publication, conferences and professional development.</p>
    </div>
    <div>
      <h4>Explore</h4>
      <ul class="footer-links">
        <li><a href="<?= base_url('journals.php?view=current') ?>">Current Issue</a></li>
        <li><a href="<?= base_url('journals.php?view=volumes') ?>">Journal Volumes</a></li>
        <li><a href="<?= base_url('journals.php?view=years') ?>">Journals by Year</a></li>
        <li><a href="<?= base_url('proceedings.php') ?>">Conference Proceedings</a></li>
      </ul>
    </div>
    <div>
      <h4>Association</h4>
      <ul class="footer-links">
        <li><a href="<?= base_url('about.php') ?>">About TEPAN</a></li>
        <li><a href="<?= base_url('contact.php') ?>">Contact Us</a></li>
        <li><a href="<?= base_url('admin/login.php') ?>">Admin Login</a></li>
      </ul>
    </div>
    <div>
      <h4>Contact</h4>
      <ul class="footer-links">
        <li>Abuja, Nigeria</li>
        <li>info@tepan.org.ng</li>
      </ul>
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
