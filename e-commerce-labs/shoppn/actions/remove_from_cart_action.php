
<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CartController.php';

// Only allow form submissions.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(base_url() . '/views/cart.php');
}

$cartController = new CartController();
$ip = get_ip();

// Get the products selected for removal.
$remove = $_POST['remove'] ?? [];

if (is_array($remove)) {
    foreach ($remove as $product_id) {
        $product_id = filter_var($product_id, FILTER_VALIDATE_INT);

        if ($product_id !== false && $product_id > 0) {
            $cartController->removeFromCart($product_id, $ip);
        }
    }
}

$_SESSION['cart_message'] = 'Selected products removed from cart.';

redirect(base_url() . '/views/cart.php');
?>
