<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/ProductController.php';

$controller = new ProductController();

$homeUrl = base_url() . '/index.php';

// Get product ID from the URL
$productId = filter_input(INPUT_GET, 'pro_id', FILTER_VALIDATE_INT);

// Redirect home if ID is missing or invalid
if (!$productId || $productId <= 0) {
    redirect($homeUrl);
    exit;
}

// Get product through the Controller
$product = $controller->getProductById($productId);

// Redirect home if product does not exist
if (!$product) {
    redirect($homeUrl);
    exit;
}

include __DIR__ . '/layout/header.php';
?>

<main class="home-page">

  <section class="product-details">

    <h1>
      <?php echo htmlspecialchars($product['product_title'], ENT_QUOTES, 'UTF-8'); ?>
    </h1>

    <img
      src="<?php echo base_url(); ?>/images/products/<?php
        echo rawurlencode($product['product_image']);
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
      <?php
        echo nl2br(
          htmlspecialchars($product['product_desc'] ?? '', ENT_QUOTES, 'UTF-8')
        );
      ?>
    </p>

    <h3>Product Keywords</h3>

    <p>
      <?php
        echo htmlspecialchars(
          $product['product_keywords'] ?? '',
          ENT_QUOTES,
          'UTF-8'
        );
      ?>
    </p>

    <p>
      <a href="<?php echo base_url(); ?>/index.php?add_cart=<?php echo (int)$product['product_id']; ?>">
        Add to Cart
      </a>
    </p>

    <p>
      <a href="<?php echo $homeUrl; ?>">
        ← Back to Products
      </a>
    </p>

  </section>

</main>

<?php include __DIR__ . '/layout/footer.php'; ?>