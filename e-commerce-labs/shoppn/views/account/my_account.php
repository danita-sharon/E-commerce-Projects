<?php
require_once '../../core/core.php';
require_login();
include '../layout/header.php';
?>

<main style="max-width: 500px; margin: 30px auto; padding: 20px;">

  <h2>My Account</h2>

  <p><strong>Name:</strong> <?php echo htmlspecialchars($_SESSION['customer_name']); ?></p>

  <?php if (isset($_SESSION['customer_email'])): ?>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['customer_email']); ?></p>
  <?php endif; ?>

  <p><a href="/~danita.quarshie/shoppn/logout.php">Logout</a></p>

</main>

<?php include '../layout/footer.php'; ?>