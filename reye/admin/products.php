<?php
// =========================================================
// Products: list, hide/show, delete
// =========================================================
require_once __DIR__ . '/_init.php';
require_once __DIR__ . '/../includes/product_card.php';
require_once __DIR__ . '/../includes/uploads.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int) ($_POST['id'] ?? 0);

    if (($_POST['action'] ?? '') === 'toggle') {
        $pdo->prepare('UPDATE products SET is_active = 1 - is_active WHERE id = ?')->execute([$id]);
        flash('success', 'Product updated.');
    }

    if (($_POST['action'] ?? '') === 'delete') {
        // Delete the photo files too
        $imgs = $pdo->prepare('SELECT image FROM product_colours WHERE product_id = ?');
        $imgs->execute([$id]);
        foreach ($imgs->fetchAll(PDO::FETCH_COLUMN) as $img) {
            delete_product_image($img);
        }
        $pdo->prepare('DELETE FROM product_colours WHERE product_id = ?')->execute([$id]);
        $pdo->prepare('DELETE FROM wishlist WHERE product_id = ?')->execute([$id]);
        $pdo->prepare('DELETE FROM products WHERE id = ?')->execute([$id]);
        flash('success', 'Product deleted. Past orders still keep its name and price.');
    }
    redirect('admin/products.php');
}

$products = $pdo->query('SELECT p.*, c.name AS category_name, c.slug AS category_slug
                         FROM products p JOIN categories c ON c.id = p.category_id ORDER BY p.id DESC')->fetchAll();
$colours = [];
foreach ($pdo->query('SELECT * FROM product_colours ORDER BY id') as $c) {
    $colours[(int) $c['product_id']][] = $c;
}

$page_title = 'Products';
$nav = 'products';
require __DIR__ . '/_header.php';
?>
<div class="admin-head">
  <div><p class="label">Catalogue</p><h1>Products</h1></div>
  <a class="btn" href="<?= url('admin/product_edit.php') ?>"><i class="ti ti-plus"></i> Add product</a>
</div>

<div class="panel" style="padding:0">
  <table class="table">
    <tr><th></th><th>Product</th><th>Price</th><th>Stock</th><th>Colours</th><th>Status</th><th></th></tr>
    <?php foreach ($products as $p): $first = $colours[(int) $p['id']][0] ?? null; ?>
      <tr>
        <td class="thumb-cell">
          <div class="admin-thumb">
            <?php if ($first && $first['image']): ?>
              <img src="<?= url($first['image']) ?>" alt="">
            <?php else: ?>
              <?= product_art($p['category_slug'], $first['hex_main'] ?? '#3a332d', $first['hex_accent'] ?? '#3a332d') ?>
            <?php endif; ?>
          </div>
        </td>
        <td><strong><?= e($p['name']) ?></strong><br><small class="muted"><?= e($p['category_name']) ?><?= $p['badge'] ? ' · ' . e($p['badge']) : '' ?></small></td>
        <td><?= money($p['price']) ?></td>
        <td><span class="<?= (int) $p['stock'] <= 3 ? 'low-stock' : '' ?>"><?= (int) $p['stock'] ?></span></td>
        <td>
          <?php foreach ($colours[(int) $p['id']] ?? [] as $c): ?>
            <span class="dot" style="background:<?= e($c['hex_accent']) ?>" title="<?= e($c['colour_name']) ?><?= $c['image'] ? '' : ' (no photo yet)' ?>"></span>
          <?php endforeach; ?>
        </td>
        <td><?= $p['is_active'] ? '<span class="status status-delivered">Live</span>' : '<span class="status">Hidden</span>' ?></td>
        <td class="actions">
          <a class="icon-btn" href="<?= url('admin/product_edit.php?id=' . (int) $p['id']) ?>" title="Edit"><i class="ti ti-pencil"></i></a>
          <form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $p['id'] ?>"><input type="hidden" name="action" value="toggle">
            <button class="icon-btn" title="<?= $p['is_active'] ? 'Hide from shop' : 'Show in shop' ?>"><i class="ti <?= $p['is_active'] ? 'ti-eye-off' : 'ti-eye' ?>"></i></button>
          </form>
          <form method="post" data-confirm="Delete <?= e($p['name']) ?>? This cannot be undone. (Tip: hiding keeps it for later.)"><?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int) $p['id'] ?>"><input type="hidden" name="action" value="delete">
            <button class="icon-btn danger" title="Delete"><i class="ti ti-trash"></i></button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
</div>

<?php require __DIR__ . '/_footer.php'; ?>
