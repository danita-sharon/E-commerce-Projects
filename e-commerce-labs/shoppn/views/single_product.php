
<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/ProductController.php';

$controller = new ProductController();

$productId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$product = null;

if ($productId && $productId > 0) {
    $product = $controller->getProductById($productId);
}

include 'layout/header.php';
?>

<main class="home-page">

  <?php if (!$product): ?>

    <section class="home-welcome">
      <h1>Product Not Found</h1>
      <p>Sorry, this product does not exist.</p>
      <a href="/~danita.quarshie/shoppn/index.php">
        Back to Products
      </a>
    </section>

  <?php else: ?>

    <section class="product-details">

      <h1>
        <?php echo htmlspecialchars($product['product_title'], ENT_QUOTES, 'UTF-8'); ?>
      </h1>

      <img
        src="/~danita.quarshie/shoppn/images/products/<?php
          echo htmlspecialchars($product['product_image'], ENT_QUOTES, 'UTF-8');
        ?>"
        alt="<?php echo htmlspecialchars($product['product_title'], ENT_QUOTES, 'UTF-8'); ?>"
        width="300"
      >

      <h2>
        GH₵ <?php echo number_format((float)$product['product_price'], 2); ?>
      </h2>

      <p>
        <strong>Category:</strong>
        <?php echo htmlspecialchars($product['cat_name'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?>
      </p>

      <p>
        <strong>Brand:</strong>
        <?php echo htmlspecialchars($product['brand_name'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?>
      </p>

      <h3>Product Description</h3>

      <p>
        <?php echo nl2br(htmlspecialchars($product['product_desc'] ?? '', ENT_QUOTES, 'UTF-8')); ?>
      </p>

      <a href="/~danita.quarshie/shoppn/index.php">
        ← Back to Products
      </a>

    </section>

  <?php endif; ?>

</main>

<?php include 'layout/footer.php'; ?>
