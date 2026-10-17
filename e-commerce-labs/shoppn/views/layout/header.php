<?php
require_once __DIR__ . '/../../core/core.php';
require_once __DIR__ . '/../../controllers/CartController.php';

$base_url = base_url();

$cartController = new CartController();
$ip = get_ip();

$cartCount = $cartController->getCartCount($ip);
$cartTotal = $cartController->getCartTotal($ip);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shoppn</title>

    <link rel="stylesheet" href="<?php echo $base_url; ?>/css/style.css?v=4">
</head>

<body>

<header>
    <nav>

        <!-- HOME -->
        <a href="<?php echo $base_url; ?>/index.php">
            Shoppn
        </a>

        <!-- SEARCH -->
        <form action="<?php echo $base_url; ?>/views/search_results.php" method="GET">
            <input
                type="text"
                name="user_query"
                placeholder="Search products..."
            >
            <button type="submit">Search</button>
        </form>

        <?php if (is_logged_in()): ?>

            <!-- WELCOME MESSAGE -->
            <span>
                Welcome,
                <?php echo htmlspecialchars($_SESSION['customer_name'] ?? 'Customer'); ?>
            </span>

            <!-- MY ACCOUNT -->
            <a href="<?php echo $base_url; ?>/views/account/my_account.php">
                My Account
            </a>

            <!-- LOGOUT -->
            <a href="<?php echo $base_url; ?>/logout.php">
                Logout
            </a>

        <?php else: ?>

            <!-- REGISTER -->
            <a href="<?php echo $base_url; ?>/views/register.php">
                Register
            </a>

            <!-- LOGIN -->
            <a href="<?php echo $base_url; ?>/views/login.php">
                Login
            </a>

        <?php endif; ?>

    </nav>


<div class="cart-summary">
    <span>🛒 Shopping Cart — Total Items:
        <?php echo $cartCount; ?>
        | Total Price: GH₵
        <?php echo number_format($cartTotal, 2); ?>
    </span>

    <a href="<?php echo $base_url; ?>/views/cart.php">
        Go to Cart →
    </a>
</div>
</header>
