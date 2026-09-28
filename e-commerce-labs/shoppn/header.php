
<!DOCTYPE html>
<!-- SERVER HEADER TEST -->
<html>
<head>
  <title>Shoppn</title>
  <link rel="stylesheet" href="/~danita.quarshie/shoppn/css/style.css">
</head>
<body>

<header>
  <nav>
    <a href="/~danita.quarshie/shoppn/index.php">Shoppn</a>

    <form action="/~danita.quarshie/shoppn/views/search_results.php" method="GET">
      <input type="text" name="q" placeholder="Search products...">
      <button type="submit">Search</button>
    </form>

    <?php if (is_logged_in()): ?>
      <span>Welcome, <?php echo $_SESSION['customer_name']; ?></span>
      <a href="/~danita.quarshie/shoppn/views/account/my_account.php">My Account</a>
      <a href="/~danita.quarshie/shoppn/logout.php">Logout</a>
    <?php else: ?>
      <a href="/~danita.quarshie/shoppn/views/register.php">Register</a>
      <a href="/~danita.quarshie/shoppn/views/login.php">Login</a>
    <?php endif; ?>
  </nav>
</header>