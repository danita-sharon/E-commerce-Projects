<?php
// =========================================================
// Shop: every product, with filters and sorting
// ?cat=pouches or ?cat=slides opens on that category
// =========================================================
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/product_card.php';

$cat = $_GET['cat'] ?? 'all';
$products = get_products($pdo);
$saved = wishlist_ids($pdo);

$page_title = $cat === 'pouches' ? 'Pouches' : 'Shop';
$active = $cat === 'pouches' ? 'pouches' : 'shop';
require __DIR__ . '/includes/header.php';
?>
<section class="page-head"><p class="label">Handmade in Ghana</p><h1><?= $cat === 'pouches' ? 'Pouches' : 'The collection' ?></h1></section>

<section class="section" style="padding-top:8px">
  <div class="wrap">
    <div class="section-head">
      <p style="margin:0;color:var(--muted);font-size:13px" id="pieceCount"><?= count($products) ?> pieces</p>
      <div class="toolbar">
        <div class="filters" id="filters">
          <button class="active" data-filter="all">All</button>
          <button data-filter="slides">Slides</button>
          <button data-filter="pouches">Pouches</button>
        </div>
        <label class="sr-only" for="sortSelect">Sort</label>
        <select class="sort" id="sortSelect">
          <option value="popular">Popular</option>
          <option value="low">Price: low to high</option>
          <option value="high">Price: high to low</option>
        </select>
      </div>
    </div>
    <p class="sub-note" id="size-guide"><span id="subNoteText">Pick a colour and size, then add to bag.</span> <button class="link-btn" id="sizeGuideBtn">Size guide</button></p>
    <?php require __DIR__ . '/includes/size_guide.php'; ?>

    <div class="grid" id="productGrid">
      <?php foreach ($products as $p) { product_card($p, $saved); } ?>
      <p class="empty-note" id="emptyNote" hidden>Nothing here yet. Check back soon.</p>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
