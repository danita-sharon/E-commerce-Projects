<?php
// =========================================================
// Saved items (wishlist)
// =========================================================
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/product_card.php';
require_login();

$saved = wishlist_ids($pdo);
$products = array_values(array_filter(get_products($pdo), fn($p) => in_array((int) $p['id'], $saved, true)));

$page_title = 'Saved items';
require __DIR__ . '/includes/header.php';
?>
<section class="page-head"><p class="label">Your favourites</p><h1>Saved items</h1></section>

<div class="wrap section" style="padding-top:0">
  <?php if (!$products): ?>
    <div class="narrow" style="text-align:center">
      <p style="color:var(--muted)">Tap the heart on any piece to save it here.</p>
      <a class="btn" href="<?= url('shop.php') ?>">Browse the collection</a>
    </div>
  <?php else: ?>
    <div class="grid" id="productGrid">
      <?php foreach ($products as $p) { product_card($p, $saved); } ?>
    </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
