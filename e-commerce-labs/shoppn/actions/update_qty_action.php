<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CartController.php';

// Only allow form submissions.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(base_url() . '/views/cart.php');
}

$cartController = new CartController();
$ip = get_ip();

$product_ids = $_POST['update'] ?? [];
$quantities = $_POST['qty'] ?? [];

if (is_array($product_ids) && is_array($quantities)) {
    foreach ($product_ids as $index => $product_id) {
        $product_id = filter_var($product_id, FILTER_VALIDATE_INT);
        $new_qty = filter_var($quantities[$index] ?? null, FILTER_VALIDATE_INT);

        if (
            $product_id !== false &&
            $product_id > 0 &&
            $new_qty !== false &&
            $new_qty >= 1
        ) {
            $cartController->updateQty($product_id, $new_qty, $ip);
        }
    }
}

$_SESSION['cart_message'] = 'Cart quantities updated successfully.';

redirect(base_url() . '/views/cart.php');
?>
