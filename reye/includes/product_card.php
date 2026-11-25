<?php
// =========================================================
// Product card: used on the homepage and the shop page.
// Shows the photo (or a drawing if no photo yet), colours,
// sizes and the Add to bag button.
// =========================================================

// Simple drawing used until real product photos are uploaded
function product_art(string $categorySlug, string $main, string $accent): string
{
    $main = e($main);
    $accent = e($accent);
    if ($categorySlug === 'pouches') {
        return '<svg class="art" viewBox="0 0 120 100" aria-hidden="true">'
            . '<rect x="15" y="25" width="90" height="62" rx="6" fill="' . $accent . '"/>'
            . '<path d="M15 31Q60 70 105 31V28Q60 58 15 28Z" fill="' . $main . '"/>'
            . '<circle cx="60" cy="52" r="4" fill="#c9a24b"/></svg>';
    }
    return '<svg class="art" viewBox="0 0 120 120" aria-hidden="true">'
        . '<path d="M60 8C38 8 32 40 33 62C34 95 42 112 60 112C78 112 86 95 87 62C88 40 82 8 60 8Z" fill="#3a332d"/>'
        . '<path d="M28 42L92 70V86L28 58Z" fill="' . $main . '"/>'
        . '<path d="M92 42L28 70V86L92 58Z" fill="' . $accent . '"/></svg>';
}

// Load every active product with its colours, in one go
function get_products(PDO $pdo, ?string $categorySlug = null): array
{
    $sql = 'SELECT p.*, c.slug AS category_slug, c.name AS category_name
            FROM products p JOIN categories c ON c.id = p.category_id
            WHERE p.is_active = 1';
    $params = [];
    if ($categorySlug) {
        $sql .= ' AND c.slug = ?';
        $params[] = $categorySlug;
    }
    $sql .= ' ORDER BY p.id';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();

    if (!$products) {
        return [];
    }
    // IDs are forced to whole numbers, so they are safe to put in the query
    $ids = implode(',', array_map('intval', array_column($products, 'id')));
    $rows = $pdo->query("SELECT * FROM product_colours WHERE product_id IN ($ids) ORDER BY id")->fetchAll();
    $colours = [];
    foreach ($rows as $c) {
        $colours[(int) $c['product_id']][] = $c;
    }
    foreach ($products as $i => $p) {
        $products[$i]['colours'] = $colours[(int) $p['id']] ?? [];
    }
    return $products;
}

// IDs of products the logged-in customer has saved
function wishlist_ids(PDO $pdo): array
{
    if (!is_logged_in()) {
        return [];
    }
    $stmt = $pdo->prepare('SELECT product_id FROM wishlist WHERE user_id = ?');
    $stmt->execute([current_user()['id']]);
    return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
}

// Print one product card
function product_card(array $p, array $savedIds = []): void
{
    $colours = $p['colours'];
    $first = $colours[0] ?? ['id' => 0, 'colour_name' => '', 'hex_main' => '#3a332d', 'hex_accent' => '#3a332d', 'image' => null];
    $sizes = array_map('trim', explode(',', $p['sizes']));
    $saved = in_array((int) $p['id'], $savedIds, true);
    $soldOut = (int) $p['stock'] <= 0;
    $badge = $soldOut ? 'SOLD OUT' : $p['badge'];
    ?>
    <article class="card reveal"
             data-id="<?= (int) $p['id'] ?>"
             data-type="<?= e($p['category_slug']) ?>"
             data-gender="<?= e($p['gender'] ?? 'men') ?>"
             data-name="<?= e($p['name']) ?>"
             data-price="<?= e($p['price']) ?>"
             data-order="<?= (int) $p['id'] ?>">
      <div class="card-media">
        <?php if ($badge): ?>
          <span class="badge <?= ($soldOut || stripos($badge, 'left') !== false) ? 'low' : '' ?>"><?= e($badge) ?></span>
        <?php endif; ?>
        <button class="wish-btn <?= $saved ? 'on' : '' ?>" data-wish="<?= (int) $p['id'] ?>" aria-label="Save <?= e($p['name']) ?>">
          <i class="ti ti-heart" aria-hidden="true"></i>
        </button>
        <a href="<?= url('product.php?slug=' . urlencode($p['slug'])) ?>" class="card-photo" aria-label="<?= e($p['name']) ?>">
          <?php if (!empty($first['image'])): ?>
            <img src="<?= url($first['image']) ?>" alt="<?= e($p['name'] . ' in ' . $first['colour_name']) ?>" loading="lazy">
          <?php else: ?>
            <?= product_art($p['category_slug'], $first['hex_main'], $first['hex_accent']) ?>
          <?php endif; ?>
        </a>
      </div>

      <div class="card-body">
        <div class="card-title">
          <a href="<?= url('product.php?slug=' . urlencode($p['slug'])) ?>"><?= e($p['name']) ?></a>
          <span><?= money($p['price']) ?></span>
        </div>

        <?php if ($colours): ?>
        <div class="swatches">
          <?php foreach ($colours as $i => $c): ?>
            <button class="swatch <?= $i === 0 ? 'on' : '' ?>"
                    style="background: <?= e($c['hex_accent']) ?>"
                    data-colour="<?= (int) $c['id'] ?>"
                    data-name="<?= e($c['colour_name']) ?>"
                    data-main="<?= e($c['hex_main']) ?>"
                    data-accent="<?= e($c['hex_accent']) ?>"
                    data-image="<?= $c['image'] ? e(url($c['image'])) : '' ?>"
                    aria-label="<?= e($c['colour_name']) ?>"></button>
          <?php endforeach; ?>
          <span class="swatch-name"><?= e($first['colour_name']) ?></span>
        </div>
        <?php endif; ?>

        <div class="sizes">
          <?php foreach ($sizes as $s): ?>
            <button class="size <?= count($sizes) === 1 ? 'on' : '' ?>" data-size="<?= e($s) ?>"><?= e($s) ?></button>
          <?php endforeach; ?>
        </div>
        <p class="error-text">Pick a size first</p>

        <button class="btn btn-block add-btn" <?= $soldOut ? 'disabled' : '' ?>>
          <?= $soldOut ? 'Sold out' : 'Add to bag' ?>
        </button>
      </div>
    </article>
    <?php
}
