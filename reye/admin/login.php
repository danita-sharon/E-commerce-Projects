<?php
// =========================================================
// Admin login (only accounts with role = admin get in)
// =========================================================
require_once __DIR__ . '/../includes/functions.php';

if (is_admin()) {
    redirect('admin/index.php');
}

$error = '';
$email = '';
$_SESSION['admin_fails'] = $_SESSION['admin_fails'] ?? 0;
$lockedUntil = $_SESSION['admin_locked_until'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if ($lockedUntil > time()) {
        $error = 'Too many attempts. Try again in a few minutes.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            unset($_SESSION['admin_fails'], $_SESSION['admin_locked_until']);
            $_SESSION['user'] = [
                'id' => (int) $user['id'], 'full_name' => $user['full_name'],
                'email' => $user['email'], 'phone' => $user['phone'], 'role' => 'admin',
            ];
            redirect('admin/index.php');
        }
        if (++$_SESSION['admin_fails'] >= 5) {
            $_SESSION['admin_locked_until'] = time() + 300;
            $_SESSION['admin_fails'] = 0;
        }
        $error = 'That email and password combination is not right.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex">
  <title>Admin login · Reye</title>
  <link rel="icon" href="<?= url('assets/img/reye-logo-black.svg') ?>" type="image/svg+xml">
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;1,400&family=Jost:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>">
</head>
<body class="admin-login">
<?php require __DIR__ . '/../includes/logo.php'; ?>
  <form method="post" class="panel" novalidate>
    <?= csrf_field() ?>
    <svg class="logo" style="width:48px;height:59px;margin:0 auto 18px"><use href="#reye-logo"/></svg>
    <p class="label" style="text-align:center">Reye admin</p>
    <h1 class="serif" style="text-align:center;font-weight:400;font-size:32px;margin:0 0 24px">Welcome back</h1>
    <?php if ($error): ?><p class="error-text" style="margin:0 0 14px"><?= e($error) ?></p><?php endif; ?>
    <label class="field"><span>Email</span><input class="input" type="email" name="email" value="<?= e($email) ?>" required autocomplete="username"></label>
    <label class="field"><span>Password</span><input class="input" type="password" name="password" required autocomplete="current-password"></label>
    <button class="btn btn-block" type="submit">Log in</button>
    <p class="form-foot"><a href="<?= url() ?>">Back to the shop</a></p>
  </form>
</body>
</html>
