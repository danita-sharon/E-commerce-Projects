<?php
// =========================================================
// REYE - Homepage
// =========================================================
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/product_card.php';

$page_title = 'Exquisitely handmade';
$products = get_products($pdo);
$saved = wishlist_ids($pdo);

// Only reviews the admin has approved are shown
$reviews = $pdo->query('SELECT customer_name, location, review_text, rating
                        FROM reviews WHERE is_approved = 1
                        ORDER BY created_at DESC LIMIT 6')->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<!-- ===== Hero ===== -->
<section class="hero">
  <div class="hero-text">
    <p class="label">Handmade in Ghana</p>
    <h1>DISCOVER THE <em>Reye't choice</em></h1>
    <a href="#bestsellers" class="btn">Shop the collection</a>
  </div>
  <div class="hero-art"><img src="<?= url('assets/img/hero-slides.svg') ?>" alt=""></div>
</section>

<!-- ===== Trust strip ===== -->
<div class="trust">
  <div><i class="ti ti-truck"></i> Nationwide delivery</div>
  <div><i class="ti ti-world"></i> Ships worldwide</div>
  <div><i class="ti ti-shield-check"></i> Secure by Paystack</div>
  <div><i class="ti ti-device-mobile"></i> MoMo and card</div>
</div>

<!-- ===== Bestsellers ===== -->
<section class="section" id="bestsellers">
  <div class="wrap">
    <div class="section-head">
      <h2>Bestsellers</h2>
      <div class="toolbar">
        <div class="filters" id="filters">
          <button class="active" data-filter="all">All</button>
          <button data-filter="slides">Slides</button>
          <button data-filter="women">Women</button>
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
    <p class="sub-note"><span id="subNoteText">Pick a colour and size, then add to bag.</span> <button class="link-btn" id="sizeGuideBtn">Size guide</button></p>

    <?php require __DIR__ . '/includes/size_guide.php'; ?>

    <div class="grid" id="productGrid">
      <?php foreach ($products as $p) { product_card($p, $saved); } ?>
      <p class="empty-note" id="emptyNote" hidden>Nothing here yet. Check back soon.</p>
    </div>
  </div>
</section>

<!-- ===== Philosophy and process ===== -->
<section class="wrap split">
  <div class="split-text reveal">
    <p class="label">Our philosophy</p>
    <h2>Made slowly.<em>Made to last.</em></h2>
    <p>Every pair is cut, stitched and finished by hand, then sealed with the Reye R. No two pairs are ever exactly alike.</p>
    <a href="<?= url('about.php') ?>" class="btn btn-outline">See the process</a>
  </div>
  <div class="process reveal">
    <div class="step"><div class="step-icon"><i class="ti ti-scissors"></i></div><p class="label">Cut</p></div>
    <div class="step"><div class="step-icon"><i class="ti ti-needle-thread"></i></div><p class="label">Stitch</p></div>
    <div class="step final"><div class="step-icon"><svg class="logo"><use href="#reye-logo"/></svg></div><p class="label">Finish</p></div>
  </div>
</section>

<!-- ===== Bespoke ===== -->
<section class="bespoke">
  <div class="wrap">
    <div class="reveal">
      <p class="label">Bespoke</p>
      <h2>Your leather. Your colour. Your fit.</h2>
      <p>Send your foot measurements and we'll make a pair just for you in 7 to 10 days.</p>
    </div>
    <div class="bespoke-cta reveal">
      <a class="btn btn-gold" href="<?= e(whatsapp_link('Hi Reye, I would like a bespoke pair.')) ?>" target="_blank" rel="noopener">
        <i class="ti ti-brand-whatsapp"></i> Start on WhatsApp
      </a>
    </div>
  </div>
</section>

<!-- ===== Reviews (real, approved reviews only) ===== -->
<section class="reviews">
  <div class="wrap">
    <p class="label">The Reye fam</p>
    <?php if ($reviews): ?>
      <p class="review-text" id="reviewText">"<?= e($reviews[0]['review_text']) ?>"</p>
      <p class="review-by"><span class="stars"><?= str_repeat('★', (int) $reviews[0]['rating']) ?></span>
        <span id="reviewBy"><?= e($reviews[0]['customer_name'] . ($reviews[0]['location'] ? ', ' . $reviews[0]['location'] : '')) ?></span></p>
      <?php if (count($reviews) > 1): ?>
        <div class="review-nav">
          <button id="revPrev" aria-label="Previous review"><i class="ti ti-arrow-left"></i></button>
          <span id="revCount">1 / <?= count($reviews) ?></span>
          <button id="revNext" aria-label="Next review"><i class="ti ti-arrow-right"></i></button>
        </div>
      <?php endif; ?>
      <script>window.REVIEWS = <?= json_encode(array_map(fn($r) => [
          'text' => $r['review_text'],
          'by' => $r['customer_name'] . ($r['location'] ? ', ' . $r['location'] : ''),
          'stars' => (int) $r['rating'],
      ], $reviews), JSON_HEX_TAG | JSON_HEX_AMP) ?>;</script>
    <?php else: ?>
      <p class="review-text">See the Reye fam wearing their pairs.</p>
      <a class="btn btn-outline" href="https://www.instagram.com/the.reye.official/" target="_blank" rel="noopener">
        <i class="ti ti-brand-instagram"></i> Follow on Instagram
      </a>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
