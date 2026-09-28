<?php
require_once '../core/core.php';
include 'layout/header.php';
?>

<main style="max-width: 500px; margin: 30px auto; padding: 20px;">

  <h2>Create an Account</h2>

  <?php if (isset($_SESSION['error'])): ?>
    <p style="color: red;"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
  <?php endif; ?>

  <form action="../actions/register_action.php" method="POST" id="registerForm" novalidate>

    <label>Full Name</label><br>
    <input type="text" name="name" id="name">
    <span class="error-msg" id="name-error" style="color: red;"></span><br><br>

    <label>Email</label><br>
    <input type="email" name="email" id="email">
    <span class="error-msg" id="email-error" style="color: red;"></span><br><br>

    <label>Password</label><br>
    <input type="password" name="pass" id="pass">
    <span class="error-msg" id="pass-error" style="color: red;"></span><br><br>

    <label>Country</label><br>
    <select name="country" id="country">
      <option value="Ghana">Ghana</option>
      <option value="Nigeria">Nigeria</option>
      <option value="Other">Other</option>
    </select><br><br>

    <label>City</label><br>
    <input type="text" name="city" id="city">
    <span class="error-msg" id="city-error" style="color: red;"></span><br><br>

    <label>Contact Number</label><br>
    <input type="text" name="contact" id="contact">
    <span class="error-msg" id="contact-error" style="color: red;"></span><br><br>

    <button type="submit">Register</button>

  </form>

  <p>Already have an account? <a href="login.php">Login here</a></p>

</main>

<script src="../js/validate.js"></script>

<?php include 'layout/footer.php'; ?>