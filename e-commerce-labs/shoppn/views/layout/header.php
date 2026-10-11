
<?php
// Automatically detect whether Shoppn is running locally or on the school server.
$base_url = '/shoppn';

if (strpos($_SERVER['SCRIPT_NAME'], '/~danita.quarshie/shoppn/') !== false) {
    $base_url = '/~danita.quarshie/shoppn';
}
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
</header>
