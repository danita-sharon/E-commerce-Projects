
<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CartController.php';

$cartController = new CartController();
$ip = get_ip();

$cartItems = $cartController->getCartItems($ip);
$cartTotal = $cartController->getCartTotal($ip);

include __DIR__ . '/layout/header.php';
?>

<main class="cart-page">

    <h1>My Shopping Cart 🛒</h1>

    <?php if (isset($_SESSION['cart_message'])): ?>
        <div class="cart-message">
            <?php
            echo htmlspecialchars(
                $_SESSION['cart_message'],
                ENT_QUOTES,
                'UTF-8'
            );
            unset($_SESSION['cart_message']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (empty($cartItems)): ?>

        <div class="empty-cart">
            <p>Your shopping cart is empty.</p>

            <a href="<?php echo $base_url; ?>/index.php"
               class="cart-btn">
                Continue Shopping
            </a>
        </div>

    <?php else: ?>

        <div class="cart-table-wrapper">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Remove</th>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($cartItems as $item): ?>
                        <tr>
                            <td>
                                <input
                                    type="checkbox"
                                    name="remove[]"
                                    value="<?php echo (int)$item['p_id']; ?>"
                                    form="remove-cart-form"
                                    aria-label="Remove <?php echo htmlspecialchars($item['product_title'], ENT_QUOTES, 'UTF-8'); ?>"
                                >
                            </td>

                            <td>
                                <div class="cart-product">
                                    <img
                                        src="<?php echo $base_url; ?>/images/products/<?php echo rawurlencode($item['product_image']); ?>"
                                        alt="<?php echo htmlspecialchars($item['product_title'], ENT_QUOTES, 'UTF-8'); ?>"
                                    >

                                    <span>
                                        <?php echo htmlspecialchars($item['product_title'], ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </div>
                            </td>

                            <td>
                                <input
                                    type="hidden"
                                    name="update[]"
                                    value="<?php echo (int)$item['p_id']; ?>"
                                    form="update-cart-form"
                                >

                                <input
                                    type="number"
                                    name="qty[]"
                                    value="<?php echo (int)$item['qty']; ?>"
                                    min="1"
                                    required
                                    class="cart-quantity"
                                    form="update-cart-form"
                                >
                            </td>

                            <td>
                                GH₵ <?php echo number_format((float)$item['product_price'], 2); ?>
                            </td>

                            <td>
                                GH₵ <?php echo number_format(
                                    (float)$item['product_price'] * (int)$item['qty'],
                                    2
                                ); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <form
            id="remove-cart-form"
            action="<?php echo $base_url; ?>/actions/remove_from_cart_action.php"
            method="POST">
        </form>

        <form
            id="update-cart-form"
            action="<?php echo $base_url; ?>/actions/update_qty_action.php"
            method="POST">
        </form>

        <div class="cart-total">
            <h2>
                Cart Total:
                GH₵ <?php echo number_format($cartTotal, 2); ?>
            </h2>
        </div>

        <div class="cart-actions">

            <button
                type="submit"
                form="remove-cart-form"
                class="cart-btn cart-btn-danger">
                Remove Selected
            </button>

            <button
                type="submit"
                form="update-cart-form"
                class="cart-btn">
                Update Quantity
            </button>

            <a href="<?php echo $base_url; ?>/index.php"
               class="cart-btn cart-btn-secondary">
                Continue Shopping
            </a>

            <a href="<?php echo $base_url; ?>/views/checkout.php"
               class="cart-btn">
                Checkout
            </a>

        </div>

    <?php endif; ?>

</main>

<?php include __DIR__ . '/layout/footer.php'; ?>
