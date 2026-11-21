<?php
// =========================================================
// Your bag
// =========================================================
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/product_card.php';
require_once __DIR__ . '/includes/cart.php';

$lines = cart_lines($pdo);
$subtotal = cart_subtotal($lines);

// Free delivery threshold (Accra), read from the database
$free = $pdo->query('SELECT name, free_over FROM delivery_regions WHERE free_over IS NOT NULL ORDER BY free_over LIMIT 1')->fetch();

$page_title = 'Your bag';
require __DIR__ . '/includes/header.php';
?>
<section class="page-head"><p class="label">Your bag</p><h1><?= $lines ? 'Ready when you are' : 'Your bag is empty' ?></h1></section>

<div class="wrap">
<?php if (!$lines): ?>
  <div class="narrow" style="text-align:center">
    <p style="color:var(--muted)">Your bag is waiting for its first pair.</p>
    <a class="btn" href="<?= url('shop.php') ?>">Shop the collection</a>
  </div>
<?php else: ?>
  <div class="bag-grid">
    <div class="bag-lines" id="bagLines">
      <?php foreach ($lines as $l): ?>
        <div class="bag-line" data-key="<?= e($l['key']) ?>">
          <a class="bag-thumb" href="<?= url('product.php?slug=' . urlencode($l['slug'])) ?>">
            <?php if ($l['image']): ?>
              <img src="<?= url($l['image']) ?>" alt="<?= e($l['name']) ?>">
            <?php else: ?>
              <?= product_art($l['category_slug'], $l['hex_main'], $l['hex_accent']) ?>
            <?php endif; ?>
          </a>
          <div class="bag-info">
            <a href="<?= url('product.php?slug=' . urlencode($l['slug'])) ?>" class="bag-name"><?= e($l['name']) ?></a>
            <p class="bag-meta"><?= e(trim($l['colour'] . ' · ' . ($l['size'] === 'One size' ? 'One size' : 'EU ' . $l['size']), ' ·')) ?></p>
            <div class="qty">
              <button class="qty-btn" data-step="-1" aria-label="Fewer">−</button>
              <span class="qty-val"><?= (int) $l['qty'] ?></span>
              <button class="qty-btn" data-step="1" aria-label="More">+</button>
            </div>
          </div>
          <div class="bag-price">
            <span class="line-total"><?= money($l['line_total']) ?></span>
            <button class="link-btn remove-btn">Remove</button>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <aside class="panel bag-summary">
      <p class="label">Summary</p>
      <?php if ($free): ?>
        <?php $left = max(0, $free['free_over'] - $subtotal); ?>
        <div class="free-bar" data-free="<?= e($free['free_over']) ?>">
          <p id="freeText" style="margin:0 0 8px;font-size:13px">
            <?= $left > 0
                ? 'You\'re ' . money($left) . ' away from free delivery in ' . e($free['name']) . '.'
                : 'You\'ve unlocked free delivery in ' . e($free['name']) . '.' ?>
          </p>
          <div class="bar"><span id="freeFill" style="width:<?= min(100, round($subtotal / $free['free_over'] * 100)) ?>%"></span></div>
        </div>
      <?php endif; ?>
      <div class="sum-row"><span>Subtotal</span><span id="bagSubtotal"><?= money($subtotal) ?></span></div>
      <p style="font-size:12px;color:var(--muted);margin:4px 0 18px">Delivery and promo codes are added at checkout.</p>
      <a class="btn btn-block" href="<?= url('checkout.php') ?>">Checkout</a>
      <p style="font-size:12px;color:var(--muted);text-align:center;margin:10px 0 0"><i class="ti ti-lock"></i> Secure payment by Paystack · Card and MoMo</p>
    </aside>
  </div>
<?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
