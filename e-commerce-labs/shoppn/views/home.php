
<?php
require_once __DIR__ . '/../controllers/ProductController.php';

$controller = new ProductController();

// Choose products based on the selected category or brand.
$selectedCat = filter_input(INPUT_GET, 'cat', FILTER_VALIDATE_INT);
$selectedBrand = filter_input(INPUT_GET, 'brand', FILTER_VALIDATE_INT);

if ($selectedCat !== null && $selectedCat !== false && $selectedCat > 0) {
    $products = $controller->getProductsByCategory($selectedCat);
    $sectionTitle = "Products by Category";
} elseif ($selectedBrand !== null && $selectedBrand !== false && $selectedBrand > 0) {
    $products = $controller->getProductsByBrand($selectedBrand);
    $sectionTitle = "Products by Brand";
} else {
    $products = $controller->getFeaturedProducts();
    $sectionTitle = "Featured Products";
}

include __DIR__ . '/layout/header.php';
?>

<main class="home-page">

  <section class="home-welcome">
    <h1>Welcome to Shoppn</h1>

    <?php if (is_admin()): ?>
      <p>Manage your store using the options below.</p>
    <?php else: ?>
      <p>Browse our products and find something you love.</p>
    <?php endif; ?>
  </section>

  <?php if (is_admin()): ?>

    <section class="admin-dashboard">

      <!-- MANAGE BRANDS -->
      <a
        href="<?php echo $base_url; ?>/views/admin/brand.php"
        class="admin-dashboard-card"
      >
        <div class="admin-card-icon">🏷️</div>
        <h2>Manage Brands</h2>
        <p>Add, edit, and manage product brands.</p>
        <span>Manage Brands →</span>
      </a>

      <!-- MANAGE CATEGORIES -->
      <a
        href="<?php echo $base_url; ?>/views/admin/category.php"
        class="admin-dashboard-card"
      >
        <div class="admin-card-icon">📦</div>
        <h2>Manage Categories</h2>
        <p>Add, edit, and manage product categories.</p>
        <span>Manage Categories →</span>
      </a>

      <!-- MANAGE PRODUCTS -->
      <a
        href="<?php echo $base_url; ?>/views/admin/product.php"
        class="admin-dashboard-card"
      >
        <div class="admin-card-icon">🛍️</div>
        <h2>Manage Products</h2>
        <p>Add, edit, and manage store products.</p>
        <span>Manage Products →</span>
      </a>

    </section>

  <?php endif; ?>

  <!-- SHOPPING SECTION -->
  <div class="shop-layout">

    <?php include __DIR__ . '/layout/sidebar.php'; ?>

    <section class="products-section">

      <h2>
        <?php echo htmlspecialchars($sectionTitle, ENT_QUOTES, 'UTF-8'); ?>
      </h2>

      <?php if (empty($products)): ?>

        <p>No products found.</p>

      <?php else: ?>

        <div class="products-grid">

          <?php foreach ($products as $product): ?>

            <div class="product-card">

              <!-- PRODUCT IMAGE -->
              <img
                src="<?php echo $base_url; ?>/images/products/<?php
                  echo rawurlencode($product['product_image']);
                ?>"
                alt="<?php echo htmlspecialchars($product['product_title'], ENT_QUOTES, 'UTF-8'); ?>"
                width="200"
              >

              <!-- PRODUCT TITLE -->
              <h3>
                <?php echo htmlspecialchars($product['product_title'], ENT_QUOTES, 'UTF-8'); ?>
              </h3>

              <!-- PRODUCT PRICE -->
              <p>
                GH₵ <?php echo number_format((float)$product['product_price'], 2); ?>
              </p>

              <!-- PRODUCT CATEGORY -->
              <p>
                <?php echo htmlspecialchars($product['cat_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
              </p>

              <!-- PRODUCT DETAILS -->
              <a href="<?php echo $base_url; ?>/views/single_product.php?pro_id=<?php echo (int)$product['product_id']; ?>">
                Details
              </a>

              
              <!-- ADD TO CART -->
              <a href="<?php echo $base_url; ?>/actions/add_to_cart_action.php?add_cart=<?php echo (int)$product['product_id']; ?>">
              Add to Cart
              </a>


            </div>

          <?php endforeach; ?>

        </div>

      <?php endif; ?>

    </section>

  </div>

</main>

<?php include __DIR__ . '/layout/footer.php'; ?>
