<?php
require_once __DIR__ . '/../../controllers/ProductController.php';
$productController = new ProductController();

$categories = $productController->getAllCategories();
$brands = $productController->getAllBrands();
?>

<aside>
  <h3>Categories</h3>
  <ul>
    <?php foreach ($categories as $category): ?>
      <li>
        <a href="/~danita.quarshie/shoppn/views/all_products.php?cat=<?php echo $category['category_id']; ?>">
          <?php echo $category['category_name']; ?>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>

  <h3>Brands</h3>
  <ul>
    <?php foreach ($brands as $brand): ?>
      <li>
        <a href="/~danita.quarshie/shoppn/views/all_products.php?brand=<?php echo $brand['brand_id']; ?>">
          <?php echo $brand['brand_name']; ?>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
</aside>
