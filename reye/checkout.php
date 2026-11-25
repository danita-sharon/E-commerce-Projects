<?php
// =========================================================
// Checkout: delivery details, promo code, pay with Paystack
// =========================================================
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/product_card.php';
require_once __DIR__ . '/includes/cart.php';
require_once __DIR__ . '/includes/payments.php';
require_login();

$lines = cart_lines($pdo);
if (!$lines) {
    flash('error', 'Your bag is empty.');
    redirect('cart.php');
}
$subtotal = cart_subtotal($lines);
$regions = $pdo->query('SELECT * FROM delivery_regions ORDER BY fee')->fetchAll();
$user = current_user();
$testMode = strpos(PAYSTACK_PUBLIC_KEY, 'pk_test_') === 0;

$page_title = 'Checkout';
require __DIR__ . '/includes/header.php';
?>
<section class="page-head"><p class="label">Checkout</p><h1>Almost yours</h1></section>

<div class="wrap checkout-grid">
  <form id="checkoutForm" class="checkout-form" novalidate data-subtotal="<?= e($subtotal) ?>">
    <div class="panel">
      <p class="label">1 · Contact</p>
      <div class="two-col">
        <label class="field"><span>Full name</span>
          <input class="input" name="full_name" value="<?= e($user['full_name']) ?>" autocomplete="name" required>
        </label>
        <label class="field"><span>Phone (for delivery)</span>
          <input class="input" name="phone" value="<?= e($user['phone']) ?>" autocomplete="tel" required>
        </label>
      </div>
      <p style="font-size:13px;color:var(--muted);margin:0">Your receipt goes to <?= e($user['email']) ?></p>
    </div>

    <div class="panel">
      <p class="label">2 · Delivery</p>
      <div class="regions">
        <?php foreach ($regions as $i => $r): ?>
          <?php $free = $r['free_over'] !== null && $subtotal >= (float) $r['free_over']; ?>
          <label class="region">
            <input type="radio" name="region_id" value="<?= (int) $r['id'] ?>" <?= $i === 0 ? 'checked' : '' ?>
                   data-fee="<?= e($r['fee']) ?>" data-free="<?= e($r['free_over'] ?? '') ?>">
            <span class="region-name"><?= e($r['name']) ?></span>
            <span class="region-fee"><?= $free ? 'Free' : money($r['fee']) ?></span>
          </label>
        <?php endforeach; ?>
      </div>
      <label class="field" style="margin-top:14px"><span>Delivery address</span>
        <textarea class="input" name="address" rows="3" placeholder="House number, street, area and a landmark, e.g. 12 Oxford St, Osu, near Koala" required></textarea>
      </label>
    </div>

    <div class="panel">
      <p class="label">3 · Promo code</p>
      <div style="display:flex;gap:8px">
        <input class="input" id="promoInput" placeholder="REYEFAM10" style="text-transform:uppercase">
        <button class="btn btn-outline" type="button" id="promoBtn">Apply</button>
      </div>
      <p class="error-text" id="promoMsg" style="min-height:18px"></p>
      <input type="hidden" name="promo_code" id="promoCode">
    </div>
  </form>

  <aside class="panel checkout-summary">
    <p class="label">Your order</p>
    <?php foreach ($lines as $l): ?>
      <div class="sum-line">
        <div class="sum-thumb">
          <?= $l['image'] ? '<img src="' . url($l['image']) . '" alt="">' : product_art($l['category_slug'], $l['hex_main'], $l['hex_accent']) ?>
          <span class="sum-qty"><?= (int) $l['qty'] ?></span>
        </div>
        <div style="flex:1"><?= e($l['name']) ?><br><small style="color:var(--muted)"><?= e(trim($l['colour'] . ' · ' . $l['size'], ' ·')) ?></small></div>
        <span><?= money($l['line_total']) ?></span>
      </div>
    <?php endforeach; ?>

    <div class="totals">
      <div><span>Subtotal</span><span><?= money($subtotal) ?></span></div>
      <div><span>Delivery</span><span id="sumDelivery">-</span></div>
      <div id="sumDiscountRow" hidden style="color:var(--success)"><span id="sumDiscountLabel">Discount</span><span id="sumDiscount"></span></div>
      <div class="grand"><span>Total</span><span id="sumTotal"><?= money($subtotal) ?></span></div>
    </div>

    <p class="error-text" id="payError" style="margin:0 0 10px"></p>
    <button class="btn btn-block" id="payBtn" type="button"><i class="ti ti-lock"></i> Pay with Paystack</button>
    <p style="font-size:12px;color:var(--muted);text-align:center;margin:10px 0 0">Card, MTN MoMo, Telecel Cash and AT Money</p>
    <?php if ($testMode): ?>
      <p class="test-note"><strong>Test mode:</strong> no real money moves. Use Paystack's test card or test MoMo number.</p>
    <?php endif; ?>
  </aside>
</div>

<script src="https://js.paystack.co/v2/inline.js"></script>
<?php require __DIR__ . '/includes/footer.php'; ?>
