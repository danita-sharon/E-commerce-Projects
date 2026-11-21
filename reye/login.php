<?php
// =========================================================
// Log in
// =========================================================
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    redirect('account.php');
}

$error = '';
$email = '';

// Slow down password guessing: 5 wrong tries = wait 5 minutes
$_SESSION['login_fails'] = $_SESSION['login_fails'] ?? 0;
$lockedUntil = $_SESSION['login_locked_until'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if ($lockedUntil > time()) {
        $error = 'Too many attempts. Try again in a few minutes.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);   // new session ID stops session fixation attacks
            unset($_SESSION['login_fails'], $_SESSION['login_locked_until']);
            $_SESSION['user'] = [
                'id'        => (int) $user['id'],
                'full_name' => $user['full_name'],
                'email'     => $user['email'],
                'phone'     => $user['phone'],
                'role'      => $user['role'],
            ];
            flash('success', 'Welcome back, ' . explode(' ', $user['full_name'])[0] . '.');
            $next = $_SESSION['after_login'] ?? null;
            unset($_SESSION['after_login']);
            if ($user['role'] === 'admin' && !$next) {
                redirect('admin/index.php');
            }
            $next ? header('Location: ' . $next) : redirect('account.php');
            exit;
        }

        // Same message whether the email or password was wrong (doesn't reveal who has an account)
        $_SESSION['login_fails']++;
        if ($_SESSION['login_fails'] >= 5) {
            $_SESSION['login_locked_until'] = time() + 300;
            $_SESSION['login_fails'] = 0;
        }
        $error = 'That email and password combination is not right.';
    }
}

$page_title = 'Log in';
require __DIR__ . '/includes/header.php';
?>
<div class="wrap auth-grid">
  <?php $auth_heading = 'Make the <em>Reye\'t choice</em>'; require __DIR__ . '/includes/auth_brand.php'; ?>

  <form method="post" class="panel auth-form" novalidate>
    <p class="label">Welcome back</p>
    <h2>Log in</h2>
    <?= csrf_field() ?>
    <?php if ($error): ?><p class="error-text" style="margin:0 0 14px"><?= e($error) ?></p><?php endif; ?>
    <label class="field"><span>Email</span>
      <input class="input" type="email" name="email" value="<?= e($email) ?>" autocomplete="email" required>
    </label>
    <label class="field"><span>Password</span>
      <input class="input" type="password" name="password" autocomplete="current-password" required>
    </label>
    <button class="btn btn-block" type="submit">Log in</button>
    <p class="form-foot">New to Reye? <a href="<?= url('register.php') ?>">Create an account</a></p>
  </form>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
