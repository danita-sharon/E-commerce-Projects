
<?php
require_once '../core/core.php';
require_once '../controllers/ProductController.php';

require_admin();

$redirectUrl = base_url() . '/views/admin/product.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request.';
    redirect($redirectUrl);
}

$productId = filter_var(
    $_POST['product_id'] ?? null,
    FILTER_VALIDATE_INT
);

if (!$productId || $productId <= 0) {
    $_SESSION['error'] = 'Invalid product ID.';
    redirect($redirectUrl);
}

$controller = new ProductController();

$product = $controller->getProductById($productId);

if (!$product) {
    $_SESSION['error'] = 'Product not found.';
    redirect($redirectUrl);
}

if ($controller->deleteProduct($productId)) {
    $_SESSION['success'] = 'Product deleted successfully.';
} else {
    $_SESSION['error'] = 'Unable to delete product.';
}

redirect($redirectUrl);
?>
