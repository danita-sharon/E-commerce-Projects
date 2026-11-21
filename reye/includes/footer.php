<?php
// =========================================================
// Site footer: included at the bottom of every customer page.
// =========================================================
?>
</main>

<footer class="site-footer">
  <div class="wrap">
    <div class="footer-grid">
      <div class="footer-brand">
        <svg class="logo"><use href="#reye-logo"/></svg>
        <p style="margin:0 0 6px">Exquisitely handmade leather slides and pouches.</p>
        <a href="https://www.instagram.com/the.reye.official/" target="_blank" rel="noopener">
          <i class="ti ti-brand-instagram"></i> @the.reye.official
        </a>
        <a href="<?= e(whatsapp_link('Hi Reye, I have a question.')) ?>" target="_blank" rel="noopener">
          <i class="ti ti-brand-whatsapp"></i> WhatsApp us
        </a>
      </div>

      <div>
        <h4>Shop</h4>
        <a href="<?= url('shop.php') ?>">All slides</a>
        <a href="<?= url('shop.php?cat=pouches') ?>">Pouches</a>
        <a href="<?= url('bespoke.php') ?>">Bespoke</a>
        <a href="<?= url('wishlist.php') ?>">Saved items</a>
      </div>

      <div>
        <h4>Help</h4>
        <a href="<?= url('about.php#delivery') ?>">Deliveries</a>
        <a href="<?= url('shop.php#size-guide') ?>">Size guide</a>
        <a href="<?= url('account.php') ?>">Track my order</a>
        <a href="<?= url('about.php') ?>">About us</a>
      </div>

      <div>
        <h4>Join the Reye fam</h4>
        <p style="margin:0 0 10px">New drops and members-only offers, straight to your inbox.</p>
        <form class="newsletter" id="newsletterForm" novalidate>
          <label class="sr-only" for="nlEmail">Email</label>
          <input class="input" id="nlEmail" type="email" placeholder="ama@gmail.com">
          <button class="btn" type="submit">Join</button>
        </form>
        <p class="error-text" id="nlMsg"></p>
      </div>
    </div>

    <div class="footer-bottom">
      <span>© <?= date('Y') ?> Reye. Handmade in Ghana.</span>
      <span class="pay-icons"><i class="ti ti-lock"></i> Secure payments by Paystack · Card · MoMo</span>
    </div>
  </div>
</footer>

<div class="toast" id="toast" role="status" aria-live="polite"></div>

<script src="<?= url('assets/js/app.js') ?>"></script>
</body>
</html>
