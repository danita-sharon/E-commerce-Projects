<?php
// =========================================================
// Create an account
// =========================================================
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    redirect('account.php');
}

$errors = [];
$old = ['full_name' => '', 'email' => '', 'phone' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $old['full_name'] = trim($_POST['full_name'] ?? '');
    $old['email']     = strtolower(trim($_POST['email'] ?? ''));
    $old['phone']     = preg_replace('/\s+/', '', $_POST['phone'] ?? '');
    $password         = $_POST['password'] ?? '';
    $confirm          = $_POST['confirm'] ?? '';

    // Validate everything on the server (never trust the browser alone)
    if (mb_strlen($old['full_name']) < 2) {
        $errors['full_name'] = 'Enter your full name.';
    }
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Enter a valid email address.';
    }
    if (!preg_match('/^(\+233|0)\d{9}$/', $old['phone'])) {
        $errors['phone'] = 'Enter a Ghana number like 024 123 4567.';
    }
    if (strlen($password) < 8 || !preg_match('/\d/', $password) || !preg_match('/[A-Za-z]/', $password)) {
        $errors['password'] = 'Use at least 8 characters with letters and numbers.';
    }
    if ($password !== $confirm) {
        $errors['confirm'] = "Passwords don't match.";
    }

    if (!$errors) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$old['email']]);
        if ($stmt->fetch()) {
            $errors['email'] = 'An account with this email already exists. Try logging in.';
        }
    }

    if (!$errors) {
        $stmt = $pdo->prepare('INSERT INTO users (full_name, email, phone, password_hash) VALUES (?, ?, ?, ?)');
        $stmt->execute([$old['full_name'], $old['email'], $old['phone'], password_hash($password, PASSWORD_DEFAULT)]);

        // Log them straight in
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id'        => (int) $pdo->lastInsertId(),
            'full_name' => $old['full_name'],
            'email'     => $old['email'],
            'phone'     => $old['phone'],
            'role'      => 'customer',
        ];
        flash('success', 'Welcome to the Reye fam, ' . explode(' ', $old['full_name'])[0] . '.');
        $next = $_SESSION['after_login'] ?? null;
        unset($_SESSION['after_login']);
        $next ? header('Location: ' . $next) : redirect('account.php');
        exit;
    }
}

$page_title = 'Create an account';
require __DIR__ . '/includes/header.php';
?>
<div class="wrap auth-grid">
  <?php $auth_heading = 'Join the <em>Reye fam</em>'; require __DIR__ . '/includes/auth_brand.php'; ?>

  <form method="post" class="panel auth-form" novalidate>
    <p class="label">It only takes a minute</p>
    <h2>Create an account</h2>
    <?= csrf_field() ?>
    <label class="field"><span>Full name</span>
      <input class="input" name="full_name" value="<?= e($old['full_name']) ?>" autocomplete="name" required>
      <?php if (isset($errors['full_name'])): ?><p class="error-text"><?= e($errors['full_name']) ?></p><?php endif; ?>
    </label>
    <label class="field"><span>Email</span>
      <input class="input" type="email" name="email" value="<?= e($old['email']) ?>" autocomplete="email" required>
      <?php if (isset($errors['email'])): ?><p class="error-text"><?= e($errors['email']) ?></p><?php endif; ?>
    </label>
    <label class="field"><span>Phone (for delivery)</span>
      <input class="input" type="tel" name="phone" value="<?= e($old['phone']) ?>" placeholder="024 123 4567" autocomplete="tel" required>
      <?php if (isset($errors['phone'])): ?><p class="error-text"><?= e($errors['phone']) ?></p><?php endif; ?>
    </label>
    <label class="field"><span>Password</span>
      <input class="input" type="password" name="password" autocomplete="new-password" required>
      <?php if (isset($errors['password'])): ?><p class="error-text"><?= e($errors['password']) ?></p><?php endif; ?>
    </label>
    <label class="field"><span>Confirm password</span>
      <input class="input" type="password" name="confirm" autocomplete="new-password" required>
      <?php if (isset($errors['confirm'])): ?><p class="error-text"><?= e($errors['confirm']) ?></p><?php endif; ?>
    </label>
    <button class="btn btn-block" type="submit">Create account</button>
    <p class="form-foot">Already have an account? <a href="<?= url('login.php') ?>">Log in</a></p>
  </form>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
