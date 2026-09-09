<?php /* Reusable subscribe form. Posts to subscribe.php. */ ?>
<form method="post" action="<?= base_url('subscribe.php') ?>" class="subscribe-form">
  <?= csrf_field() ?>
  <input type="text" name="name" placeholder="Your name (optional)" aria-label="Your name">
  <input type="email" name="email" placeholder="you@example.com" required aria-label="Email address">
  <button type="submit" class="btn btn-gold btn-sm">Subscribe</button>
</form>
