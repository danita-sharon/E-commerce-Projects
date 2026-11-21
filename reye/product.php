<?php
// =========================================================
// Product page: photos, colours, sizes, details
// product.php?slug=onyx-slide
// =========================================================
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/product_card.php';

$slug = $_GET['slug'] ?? '';
$all = get_products($pdo);
$product = null;
foreach ($all as $p) {
    if ($p['slug'] === $slug) {
        $product = $p;
    }
}

if (!$product) {
    http_response_code(404);
    $page_title = 'Not found';
    require __DIR__ . '/includes/header.php';
    echo '<section class="page-head"><h1>We couldn\'t find that piece</h1></section>
          <div class="narrow" style="text-align:center"><a class="btn" href="' . url('shop.php') . '">Back to the shop</a></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$saved = wishlist_ids($pdo);
$isSaved = in_array((int) $product['id'], $saved, true);
$colours = $product['colours'];
$first = $colours[0] ?? ['id' => 0, 'colour_name' => '', 'hex_main' => '#3a332d', 'hex_accent' => '#3a332d', 'image' => null];
$sizes = array_map('trim', explode(',', $product['sizes']));
$soldOut = (int) $product['stock'] <= 0;

// "You may also like": other pieces from the same category first
$related = array_filter($all, fn($p) => $p['id'] !== $product['id']);
usort($related, fn($a, $b) => ($b['category_slug'] === $product['category_slug']) <=> ($a['category_slug'] === $product['category_slug']));
$related = array_slice($related, 0, 3);

$regions = $pdo->query('SELECT name, fee, free_over FROM delivery_regions ORDER BY fee')->fetchAll();

$page_title = $product['name'];
$active = $product['category_slug'] === 'pouches' ? 'pouches' : 'shop';
require __DIR__ . '/includes/header.php';
?>
<div class="wrap">
  <nav class="crumbs"><a href="<?= url('shop.php') ?>">Shop</a> / <a href="<?= url('shop.php?cat=' . e($product['category_slug'])) ?>"><?= e($product['category_name']) ?></a> / <?= e($product['name']) ?></nav>

  <div class="card product-detail"
       data-id="<?= (int) $product['id'] ?>"
       data-type="<?= e($product['category_slug']) ?>"
       data-name="<?= e($product['name']) ?>">
    <div class="card-media pd-media">
      <?php if ($product['badge'] || $soldOut): ?>
        <span class="badge <?= $soldOut || stripos((string) $product['badge'], 'left') !== false ? 'low' : '' ?>"><?= e($soldOut ? 'SOLD OUT' : $product['badge']) ?></span>
      <?php endif; ?>
      <div class="card-photo">
        <?php if ($first['image']): ?>
          <img src="<?= url($first['image']) ?>" alt="<?= e($product['name'] . ' in ' . $first['colour_name']) ?>">
        <?php else: ?>
          <?= product_art($product['category_slug'], $first['hex_main'], $first['hex_accent']) ?>
        <?php endif; ?>
      </div>
    </div>

    <div class="pd-info">
      <p class="label"><?= e($product['category_name']) ?> · Handmade in Ghana</p>
      <h1 class="pd-title"><?= e($product['name']) ?></h1>
      <p class="pd-price"><?= money($product['price']) ?></p>
      <?php if (!$soldOut && (int) $product['stock'] <= 5): ?>
        <p class="pd-stock">Only <?= (int) $product['stock'] ?> left. Each pair is made by hand.</p>
      <?php endif; ?>
      <p class="pd-desc"><?= nl2br(e($product['description'])) ?></p>

      <?php if ($colours): ?>
        <p class="pd-label">Colour: <span class="swatch-name"><?= e($first['colour_name']) ?></span></p>
        <div class="swatches">
          <?php foreach ($colours as $i => $c): ?>
            <button class="swatch <?= $i === 0 ? 'on' : '' ?>" style="background: <?= e($c['hex_accent']) ?>"
                    data-colour="<?= (int) $c['id'] ?>" data-name="<?= e($c['colour_name']) ?>"
                    data-main="<?= e($c['hex_main']) ?>" data-accent="<?= e($c['hex_accent']) ?>"
                    data-image="<?= $c['image'] ? e(url($c['image'])) : '' ?>"
                    aria-label="<?= e($c['colour_name']) ?>"></button>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <p class="pd-label">Size <?php if (count($sizes) > 1): ?><button class="link-btn" id="sizeGuideBtn" style="float:right;font-size:12px">Size guide</button><?php endif; ?></p>
      <div class="sizes">
        <?php foreach ($sizes as $s): ?>
          <button class="size <?= count($sizes) === 1 ? 'on' : '' ?>" data-size="<?= e($s) ?>"><?= e($s) ?></button>
        <?php endforeach; ?>
      </div>
      <?php if (count($sizes) > 1) { require __DIR__ . '/includes/size_guide.php'; } ?>
      <p class="error-text">Pick a size first</p>

      <div class="pd-actions">
        <div class="qty">
          <button class="qty-step" data-step="-1" aria-label="Fewer">−</button>
          <span class="qty-input">1</span>
          <button class="qty-step" data-step="1" aria-label="More">+</button>
        </div>
        <button class="btn add-btn" <?= $soldOut ? 'disabled' : '' ?>><?= $soldOut ? 'Sold out' : 'Add to bag' ?></button>
        <button class="btn btn-outline wish-btn-lg wish-btn <?= $isSaved ? 'on' : '' ?>" data-wish="<?= (int) $product['id'] ?>" aria-label="Save">
          <i class="ti ti-heart"></i>
        </button>
      </div>

      <details class="pd-more" open>
        <summary>Delivery</summary>
        <?php foreach ($regions as $r): ?>
          <p><?= e($r['name']) ?>: <?= money($r['fee']) ?><?= $r['free_over'] ? ' · free over ' . money($r['free_over']) : '' ?></p>
        <?php endforeach; ?>
      </details>
      <details class="pd-more">
        <summary>Made by hand</summary>
        <p>Cut, stitched and finished by hand in Ghana. Small differences in grain and colour make every pair one of a kind.</p>
      </details>
      <details class="pd-more">
        <summary>Want it your way?</summary>
        <p>Choose your own leather, colour and fit. <a href="<?= url('bespoke.php') ?>" style="text-decoration:underline">Start a bespoke order</a>.</p>
      </details>
    </div>
  </div>

  <?php if ($related): ?>
  <section class="section">
    <div class="section-head"><h2>You may also like</h2></div>
    <div class="grid" style="margin-top:20px">
      <?php foreach ($related as $p) { product_card($p, $saved); } ?>
    </div>
  </section>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
