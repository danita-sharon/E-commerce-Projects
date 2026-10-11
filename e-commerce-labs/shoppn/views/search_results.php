<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/ProductController.php';

$controller = new ProductController();

// Get the search query from the URL
$query = trim($_GET['user_query'] ?? '');

$products = [];

if ($query !== '') {
    $products = $controller->searchProducts($query);
}

include __DIR__ . '/layout/header.php';
?>

<main class="home-page">

  <section class="home-welcome">

    <h1>Search Results</h1>

    <?php if ($query !== ''): ?>
      <p>
        Results for:
        <?php echo htmlspecialchars($query, ENT_QUOTES, 'UTF-8'); ?>
      </p>
    <?php endif; ?>

  </section>

  <section class="products-section">

    <?php if ($query === ''): ?>

      <p>Please enter a product name to search.</p>

    <?php elseif (empty($products)): ?>

      <p>No matching products found.</p>

    <?php else: ?>

      <div class="products-grid">

        <?php foreach ($products as $product): ?>

          <div class="product-card">

            <img
              src="<?php echo base_url(); ?>/images/products/<?php
                echo rawurlencode($product['product_image']);
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

            <a href="<?php echo base_url(); ?>/views/single_product.php?pro_id=<?php echo (int)$product['product_id']; ?>">
              Details
            </a>

            <a href="<?php echo base_url(); ?>/index.php?add_cart=<?php echo (int)$product['product_id']; ?>">
              Add to Cart
            </a>

          </div>

        <?php endforeach; ?>

      </div>

    <?php endif; ?>

  </section>

</main>

<?php include __DIR__ . '/layout/footer.php'; ?>