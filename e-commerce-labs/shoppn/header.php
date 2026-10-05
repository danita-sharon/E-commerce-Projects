<!DOCTYPE html>
<!-- SERVER HEADER TEST -->
<html>
<head>
  <title>Shoppn</title>
  <link rel="stylesheet" href="/shoppn/css/style.css">
</head>
<body>

<header>
  <nav>
    <a href="/shoppn/index.php">Shoppn</a>

    <form action="/shoppn/views/search_results.php" method="GET">
      <input type="text" name="q" placeholder="Search products...">
      <button type="submit">Search</button>
    </form>

    <?php if (is_logged_in()): ?>
      <span>Welcome, <?php echo $_SESSION['customer_name']; ?></span>
      <a href="/shoppn/views/account/my_account.php">My Account</a>
      <a href="/shoppn/logout.php">Logout</a>
    <?php else: ?>
      <a href="/shoppn/views/register.php">Register</a>
      <a href="/shoppn/views/login.php">Login</a>
    <?php endif; ?>
  </nav>
</header>