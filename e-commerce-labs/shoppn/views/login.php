<?php
require_once '../core/core.php';
include 'layout/header.php';
?>

<main style="max-width: 500px; margin: 30px auto; padding: 20px;">

  <h2>Login</h2>

  <?php if (isset($_SESSION['error'])): ?>
    <p style="color: red;"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
  <?php endif; ?>

  <form action="../actions/login_action.php" method="POST">

    <label>Email</label><br>
    <input type="email" name="email" required><br><br>

    <label>Password</label><br>
    <input type="password" name="pass" required><br><br>

    <button type="submit">Login</button>

  </form>

  <p>Don't have an account? <a href="register.php">Register here</a></p>

</main>

<?php include 'layout/footer.php'; ?>