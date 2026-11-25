<?php
// =========================================================
// Add or edit a product, its colours and photos
// product_edit.php        -> new product
// product_edit.php?id=3   -> edit product 3
// =========================================================
require_once __DIR__ . '/_init.php';
require_once __DIR__ . '/../includes/uploads.php';

$id = (int) ($_GET['id'] ?? 0);
$categories = $pdo->query('SELECT * FROM categories ORDER BY id')->fetchAll();
$errors = [];

// Load the product (or start a blank one)
$product = ['name' => '', 'category_id' => $categories[0]['id'] ?? 1, 'gender' => 'men', 'price' => '', 'stock' => 0,
            'sizes' => '40,41,42,43,44,45', 'badge' => '', 'description' => '', 'is_active' => 1];
$colours = [];
if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    if (!$product) {
        flash('error', 'Product not found.');
        redirect('admin/products.php');
    }
    $stmt = $pdo->prepare('SELECT * FROM product_colours WHERE product_id = ? ORDER BY id');
    $stmt->execute([$id]);
    $colours = $stmt->fetchAll();
}

// Turn "The Onyx slide" into "the-onyx-slide" for the web address
function make_slug(PDO $pdo, string $name, int $id): string
{
    $base = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($name)), '-') ?: 'product';
    $slug = $base;
    $n = 2;
    $check = $pdo->prepare('SELECT 1 FROM products WHERE slug = ? AND id <> ?');
    while (true) {
        $check->execute([$slug, $id]);
        if (!$check->fetch()) {
            return $slug;
        }
        $slug = $base . '-' . $n++;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $product = [
        'name'        => trim($_POST['name'] ?? ''),
        'category_id' => (int) ($_POST['category_id'] ?? 0),
        'gender'      => in_array($_POST['gender'] ?? '', ['men', 'women', 'unisex'], true) ? $_POST['gender'] : 'men',
        'price'       => trim($_POST['price'] ?? ''),
        'stock'       => (int) ($_POST['stock'] ?? 0),
        'sizes'       => implode(',', array_filter(array_map('trim', explode(',', $_POST['sizes'] ?? '')), 'strlen')),
        'badge'       => strtoupper(trim($_POST['badge'] ?? '')),
        'description' => trim($_POST['description'] ?? ''),
        'is_active'   => isset($_POST['is_active']) ? 1 : 0,
    ];

    if (mb_strlen($product['name']) < 2)                         $errors[] = 'Give the product a name.';
    if (!in_array($product['category_id'], array_map('intval', array_column($categories, 'id')), true)) $errors[] = 'Choose a category.';
    if (!is_numeric($product['price']) || $product['price'] <= 0) $errors[] = 'Enter a price above 0.';
    if ($product['stock'] < 0)                                   $errors[] = 'Stock cannot be negative.';
    if ($product['sizes'] === '')                                $errors[] = 'Enter the sizes, e.g. 40,41,42 or One size.';
    if (mb_strlen($product['badge']) > 30)                       $errors[] = 'Keep the badge under 30 characters.';

    // Colours sent from the form
    $posted = $_POST['colours'] ?? [];
    $keptColours = 0;
    foreach ($posted as $c) {
        if (trim($c['name'] ?? '') !== '' && empty($c['remove'])) {
            $keptColours++;
        }
    }
    if ($keptColours === 0) {
        $errors[] = 'Add at least one colour.';
    }

    if (!$errors) {
        $pdo->beginTransaction();
        $newFiles = [];
        try {
            $slug = make_slug($pdo, $product['name'], $id);
            $vals = [$product['category_id'], $product['gender'], $product['name'], $slug, $product['description'], $product['price'],
                     $product['sizes'], $product['stock'], $product['badge'] ?: null, $product['is_active']];
            if ($id) {
                $pdo->prepare('UPDATE products SET category_id = ?, gender = ?, name = ?, slug = ?, description = ?, price = ?,
                               sizes = ?, stock = ?, badge = ?, is_active = ? WHERE id = ?')
                    ->execute(array_merge($vals, [$id]));
            } else {
                $pdo->prepare('INSERT INTO products (category_id, gender, name, slug, description, price, sizes, stock, badge, is_active)
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')->execute($vals);
                $id = (int) $pdo->lastInsertId();
            }

            $existing = [];
            foreach ($colours as $c) {
                $existing[(int) $c['id']] = $c;
            }
            $oldFilesToDelete = [];

            foreach ($posted as $i => $c) {
                $cid = (int) ($c['id'] ?? 0);
                $name = trim($c['name'] ?? '');
                $main = preg_match('/^#[0-9a-fA-F]{6}$/', $c['main'] ?? '') ? $c['main'] : '#3a332d';
                $accent = preg_match('/^#[0-9a-fA-F]{6}$/', $c['accent'] ?? '') ? $c['accent'] : $main;

                // Photo for this colour (if one was chosen)
                $file = [
                    'name' => $_FILES['photo']['name'][$i] ?? '', 'type' => $_FILES['photo']['type'][$i] ?? '',
                    'tmp_name' => $_FILES['photo']['tmp_name'][$i] ?? '', 'error' => $_FILES['photo']['error'][$i] ?? UPLOAD_ERR_NO_FILE,
                    'size' => $_FILES['photo']['size'][$i] ?? 0,
                ];
                $photo = save_product_image($file);
                if ($photo) {
                    $newFiles[] = $photo;
                }

                if ($cid && isset($existing[$cid])) {
                    if (!empty($c['remove']) || $name === '') {
                        $oldFilesToDelete[] = $existing[$cid]['image'];
                        $pdo->prepare('DELETE FROM product_colours WHERE id = ? AND product_id = ?')->execute([$cid, $id]);
                        continue;
                    }
                    $image = $existing[$cid]['image'];
                    if ($photo) {
                        $oldFilesToDelete[] = $image;
                        $image = $photo;
                    } elseif (!empty($c['clear_photo'])) {
                        $oldFilesToDelete[] = $image;
                        $image = null;
                    }
                    $pdo->prepare('UPDATE product_colours SET colour_name = ?, hex_main = ?, hex_accent = ?, image = ? WHERE id = ? AND product_id = ?')
                        ->execute([$name, $main, $accent, $image, $cid, $id]);
                } elseif ($name !== '' && empty($c['remove'])) {
                    $pdo->prepare('INSERT INTO product_colours (product_id, colour_name, hex_main, hex_accent, image) VALUES (?, ?, ?, ?, ?)')
                        ->execute([$id, $name, $main, $accent, $photo]);
                }
            }
            $pdo->commit();
            foreach ($oldFilesToDelete as $f) {
                delete_product_image($f);
            }
            flash('success', $product['name'] . ' saved.');
            redirect('admin/product_edit.php?id=' . $id);
        } catch (Throwable $e) {
            $pdo->rollBack();
            foreach ($newFiles as $f) {
                delete_product_image($f);
            }
            if (!$e instanceof RuntimeException) {
                error_log('Product save failed: ' . $e->getMessage());
            }
            $errors[] = $e instanceof RuntimeException ? $e->getMessage() : 'Could not save the product. Please try again.';
        }
    }
    // Keep what was typed if there was an error
    $colours = [];
    foreach ($posted as $c) {
        $colours[] = ['id' => (int) ($c['id'] ?? 0), 'colour_name' => $c['name'] ?? '', 'hex_main' => $c['main'] ?? '#3a332d',
                      'hex_accent' => $c['accent'] ?? '#3a332d', 'image' => $c['image'] ?? null];
    }
}

// Always show at least one empty row for a new colour
$colours[] = ['id' => 0, 'colour_name' => '', 'hex_main' => '#3a332d', 'hex_accent' => '#5a4a3c', 'image' => null];

$page_title = $id ? 'Edit ' . $product['name'] : 'Add product';
$nav = 'products';
require __DIR__ . '/_header.php';
?>
<div class="admin-head">
  <div><a href="<?= url('admin/products.php') ?>" class="back-link"><i class="ti ti-arrow-left"></i> Products</a>
    <h1><?= $id ? e($product['name']) : 'Add a product' ?></h1></div>
  <?php if ($id && $product['is_active']): ?>
    <a class="btn btn-outline" target="_blank" href="<?= url('product.php?slug=' . urlencode($product['slug'] ?? '')) ?>"><i class="ti ti-external-link"></i> View in shop</a>
  <?php endif; ?>
</div>

<?php if ($errors): ?>
  <div class="flash flash-error" style="text-align:left"><?= implode('<br>', array_map('e', $errors)) ?></div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" novalidate class="product-form">
  <?= csrf_field() ?>
  <div class="panel">
    <h2>Details</h2>
    <label class="field"><span>Name</span><input class="input" name="name" value="<?= e($product['name']) ?>" placeholder="The Onyx slide" required></label>
    <div class="two-col">
      <label class="field"><span>Category</span>
        <select class="input" name="category_id">
          <?php foreach ($categories as $c): ?>
            <option value="<?= (int) $c['id'] ?>" <?= (int) $product['category_id'] === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label class="field"><span>Made for</span>
        <select class="input" name="gender">
          <?php foreach (['men' => 'Men', 'women' => 'Women', 'unisex' => 'Everyone (unisex)'] as $g => $label): ?>
            <option value="<?= $g ?>" <?= ($product['gender'] ?? 'men') === $g ? 'selected' : '' ?>><?= $label ?></option>
          <?php endforeach; ?>
        </select>
      </label>
    </div>
    <label class="field"><span>Price (GH₵)</span><input class="input" name="price" type="number" step="0.01" min="0" value="<?= e($product['price']) ?>" required></label>
    <div class="two-col">
      <label class="field"><span>Stock (pairs or pieces available)</span><input class="input" name="stock" type="number" min="0" value="<?= (int) $product['stock'] ?>"></label>
      <label class="field"><span>Badge (optional)</span><input class="input" name="badge" value="<?= e($product['badge']) ?>" placeholder="NEW, BESTSELLER, LIMITED..." list="badges">
        <datalist id="badges"><option value="NEW"><option value="BESTSELLER"><option value="LIMITED"><option value="ONLY 3 LEFT"></datalist>
      </label>
    </div>
    <label class="field"><span>Sizes, separated by commas</span><input class="input" name="sizes" value="<?= e($product['sizes']) ?>" placeholder="Men: 40,41,42,43,44,45 · Women: 36,37,38,39,40,41 · Pouches: One size"></label>
    <label class="field"><span>Description</span><textarea class="input" name="description" rows="4"><?= e($product['description']) ?></textarea></label>
    <label class="check"><input type="checkbox" name="is_active" <?= $product['is_active'] ? 'checked' : '' ?>> Show this product in the shop</label>
  </div>

  <div class="panel">
    <h2>Colours and photos</h2>
    <p class="muted" style="margin-top:-6px">Each colour can have its own photo. Square photos (1:1) look best. JPG, PNG or WebP, under 4 MB.</p>
    <div id="colourRows">
      <?php foreach ($colours as $i => $c): ?>
        <div class="colour-row">
          <input type="hidden" name="colours[<?= $i ?>][id]" value="<?= (int) $c['id'] ?>">
          <input type="hidden" name="colours[<?= $i ?>][image]" value="<?= e($c['image'] ?? '') ?>">
          <div class="colour-photo">
            <?php if (!empty($c['image'])): ?><img src="<?= url($c['image']) ?>" alt=""><?php else: ?><i class="ti ti-photo"></i><?php endif; ?>
          </div>
          <div class="colour-fields">
            <input class="input" name="colours[<?= $i ?>][name]" value="<?= e($c['colour_name']) ?>" placeholder="<?= $c['id'] ? '' : 'New colour, e.g. Cognac' ?>">
            <label title="Main colour"><input type="color" name="colours[<?= $i ?>][main]" value="<?= e($c['hex_main']) ?>"></label>
            <label title="Swatch colour"><input type="color" name="colours[<?= $i ?>][accent]" value="<?= e($c['hex_accent']) ?>"></label>
            <input type="file" name="photo[<?= $i ?>]" accept="image/jpeg,image/png,image/webp">
          </div>
          <?php if ($c['id']): ?>
            <div class="colour-opts">
              <?php if (!empty($c['image'])): ?><label class="check"><input type="checkbox" name="colours[<?= $i ?>][clear_photo]"> Remove photo</label><?php endif; ?>
              <label class="check danger"><input type="checkbox" name="colours[<?= $i ?>][remove]"> Delete colour</label>
            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
    <p class="muted" style="font-size:12px;margin:10px 0 0">To add another colour, fill in the empty row and save. A new empty row appears each time.</p>
  </div>

  <div class="form-actions">
    <a class="btn btn-outline" href="<?= url('admin/products.php') ?>">Cancel</a>
    <button class="btn" type="submit"><i class="ti ti-check"></i> Save product</button>
  </div>
</form>

<script>
// Preview a photo as soon as it is chosen
document.querySelectorAll('.colour-row input[type=file]').forEach(input => {
  input.addEventListener('change', () => {
    const box = input.closest('.colour-row').querySelector('.colour-photo');
    if (input.files[0]) box.innerHTML = '<img src="' + URL.createObjectURL(input.files[0]) + '" alt="">';
  });
});
</script>
<?php require __DIR__ . '/_footer.php'; ?>
