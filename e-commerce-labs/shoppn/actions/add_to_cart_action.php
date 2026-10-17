
<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CartController.php';

$base_url = base_url();

// Only accept a valid product ID.
$product_id = filter_input(INPUT_GET, 'add_cart', FILTER_VALIDATE_INT);

if (!$product_id || $product_id < 1) {
    redirect($base_url . '/index.php');
}

$cartController = new CartController();

$ip = get_ip();
$qty = 1;

$customer_id = is_logged_in()
    ? (int) $_SESSION['customer_id']
    : null;

// Prevent duplicate products.
if ($cartController->isInCart($product_id, $ip)) {
    $_SESSION['cart_message'] =
        'This item is already in your cart. Go to cart to update quantity.';

    redirect($base_url . '/views/cart.php');
}

// Add the product to the cart.
if ($cartController->addToCart($product_id, $qty, $ip, $customer_id)) {
    $_SESSION['cart_message'] = 'Product added to cart successfully!';
} else {
    $_SESSION['cart_message'] = 'Unable to add product to cart.';
}

// Return to the shopping page.
redirect($base_url . '/index.php');
?>
