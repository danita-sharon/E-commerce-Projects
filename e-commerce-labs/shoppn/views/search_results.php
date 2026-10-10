
<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/ProductController.php';

$controller = new ProductController();

$query = trim($_GET['q'] ?? '');
$products = [];

if ($query !== '') {
    $products = $controller->searchProducts($query);
}

include 'layout/header.php';
?>

<main class="home-page">

  <section class="home-welcome">
    <h1>Search Results</h1>

    <p>
      Results for:
      <?php echo htmlspecialchars($query, ENT_QUOTES, 'UTF-8'); ?>
    </p>
  </section>

  <section class="products-section">

    <?php if ($query === ''): ?>

      <p>Please enter a product name to search.</p>

    <?php elseif (empty($products)): ?>

      <p>No matching products found.</p>

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

          </a>

        <?php endforeach; ?>

      </div>

    <?php endif; ?>

  </section>

</main>

<?php include 'layout/footer.php'; ?>
