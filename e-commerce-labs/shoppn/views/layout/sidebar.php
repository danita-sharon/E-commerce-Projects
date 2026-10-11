
<?php
require_once __DIR__ . '/../../controllers/ProductController.php';

$productController = new ProductController();

$categories = $productController->getAllCategories();
$brands = $productController->getAllBrands();

// Use the correct path for localhost or the school server.
if (!isset($base_url)) {
    $base_url = '/shoppn';

    if (strpos($_SERVER['SCRIPT_NAME'], '/~danita.quarshie/shoppn/') !== false) {
        $base_url = '/~danita.quarshie/shoppn';
    }
}
?>

<aside class="product-sidebar">

  <h3>Shop by Category</h3>

  <ul>
    <?php foreach ($categories as $category): ?>
      <li>
        <a href="<?php echo htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8'); ?>/index.php?cat=<?php echo (int)$category['cat_id']; ?>">
          <?php echo htmlspecialchars($category['cat_name'], ENT_QUOTES, 'UTF-8'); ?>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>

  <h3>Shop by Brand</h3>

  <ul>
    <?php foreach ($brands as $brand): ?>
      <li>
        <a href="<?php echo htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8'); ?>/index.php?brand=<?php echo (int)$brand['brand_id']; ?>">
          <?php echo htmlspecialchars($brand['brand_name'], ENT_QUOTES, 'UTF-8'); ?>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>

</aside>
