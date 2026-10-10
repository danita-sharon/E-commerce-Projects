
<?php
require_once __DIR__ . '/../controllers/ProductController.php';

$controller = new ProductController();
$products = $controller->getAllProducts();

include 'layout/header.php';
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

      <a
        href="/~danita.quarshie/shoppn/views/admin/brand.php"
        class="admin-dashboard-card"
      >
        <div class="admin-card-icon">🏷️</div>
        <h2>Manage Brands</h2>
        <p>Add, edit, and manage product brands.</p>
        <span>Manage Brands →</span>
      </a>

      <a
        href="/~danita.quarshie/shoppn/views/admin/category.php"
        class="admin-dashboard-card"
      >
        <div class="admin-card-icon">📦</div>
        <h2>Manage Categories</h2>
        <p>Add, edit, and manage product categories.</p>
        <span>Manage Categories →</span>
      </a>

      <a
        href="/~danita.quarshie/shoppn/views/admin/product.php"
        class="admin-dashboard-card"
      >
        <div class="admin-card-icon">🛍️</div>
        <h2>Manage Products</h2>
        <p>Add, edit, and manage store products.</p>
        <span>Manage Products →</span>
      </a>

    </section>

  <?php endif; ?>

  <section class="products-section">

    <h2>Our Products</h2>

    <?php if (empty($products)): ?>

      <p>No products available yet.</p>

    <?php else: ?>

      <div class="products-grid">

        <?php foreach ($products as $product): ?>

          <a
            class="product-card"
            href="/~danita.quarshie/shoppn/views/single_product.php?id=<?php echo (int)$product['product_id']; ?>"
            style="display: block; text-decoration: none; color: inherit;"
          >

            <img
              src="/~danita.quarshie/shoppn/images/products/<?php
                echo htmlspecialchars($product['product_image'], ENT_QUOTES, 'UTF-8');
              ?>"
              alt="<?php echo htmlspecialchars($product['product_title'], ENT_QUOTES, 'UTF-8'); ?>"
              width="200"
            >

            <h3>
              <?php echo htmlspecialchars($product['product_title'], ENT_QUOTES, 'UTF-8'); ?>
            </h3>

            <p>
              GH₵ <?php echo number_format((float)$product['product_price'], 2); ?>
            </p>

            <p>
              <?php echo htmlspecialchars($product['cat_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
            </p>

          </a>

        <?php endforeach; ?>

      </div>

    <?php endif; ?>

  </section>

</main>

<?php include 'layout/footer.php'; ?>
