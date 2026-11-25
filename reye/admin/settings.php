<?php
// =========================================================
// Settings: delivery fees, promo codes, admin password
// =========================================================
require_once __DIR__ . '/_init.php';

$pwErrors = [];
$promoErrors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    // ---- Delivery fees ----
    if ($action === 'delivery') {
        $upd = $pdo->prepare('UPDATE delivery_regions SET fee = ?, free_over = ? WHERE id = ?');
        foreach ($_POST['fee'] ?? [] as $id => $fee) {
            $free = trim($_POST['free_over'][$id] ?? '');
            if (is_numeric($fee) && $fee >= 0) {
                $upd->execute([$fee, ($free !== '' && is_numeric($free) && $free > 0) ? $free : null, (int) $id]);
            }
        }
        flash('success', 'Delivery fees saved.');
        redirect('admin/settings.php');
    }

    // ---- Promo codes ----
    if ($action === 'promo_add') {
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $pct = (int) ($_POST['percent_off'] ?? 0);
        $exp = trim($_POST['expires_at'] ?? '');
        if (!preg_match('/^[A-Z0-9]{3,20}$/', $code)) $promoErrors[] = 'Codes are 3 to 20 letters or numbers, no spaces.';
        if ($pct < 1 || $pct > 90)                     $promoErrors[] = 'Discount must be between 1% and 90%.';
        if ($exp !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $exp)) $promoErrors[] = 'Choose a valid end date.';
        if (!$promoErrors) {
            $exists = $pdo->prepare('SELECT 1 FROM promo_codes WHERE code = ?');
            $exists->execute([$code]);
            if ($exists->fetch()) {
                $promoErrors[] = 'That code already exists.';
            } else {
                $pdo->prepare('INSERT INTO promo_codes (code, percent_off, expires_at) VALUES (?, ?, ?)')->execute([$code, $pct, $exp ?: null]);
                flash('success', "Promo code $code added.");
                redirect('admin/settings.php');
            }
        }
    }
    if ($action === 'promo_toggle') {
        $pdo->prepare('UPDATE promo_codes SET is_active = 1 - is_active WHERE id = ?')->execute([(int) $_POST['id']]);
        redirect('admin/settings.php');
    }
    if ($action === 'promo_delete') {
        $pdo->prepare('DELETE FROM promo_codes WHERE id = ?')->execute([(int) $_POST['id']]);
        flash('success', 'Promo code deleted.');
        redirect('admin/settings.php');
    }

    // ---- Change password ----
    if ($action === 'password') {
        $stmt = $pdo->prepare('SELECT password_hash FROM users WHERE id = ?');
        $stmt->execute([current_user()['id']]);
        $hash = $stmt->fetchColumn();
        $new = $_POST['new_password'] ?? '';
        if (!password_verify($_POST['current_password'] ?? '', $hash)) $pwErrors[] = 'Your current password is not right.';
        if (strlen($new) < 10 || !preg_match('/\d/', $new) || !preg_match('/[A-Za-z]/', $new)) $pwErrors[] = 'Use at least 10 characters with letters and numbers.';
        if ($new !== ($_POST['confirm_password'] ?? ''))                 $pwErrors[] = "New passwords don't match.";
        if (!$pwErrors) {
            $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([password_hash($new, PASSWORD_DEFAULT), current_user()['id']]);
            session_regenerate_id(true);
            flash('success', 'Password changed.');
            redirect('admin/settings.php');
        }
    }
}

$regions = $pdo->query('SELECT * FROM delivery_regions ORDER BY id')->fetchAll();
$promos = $pdo->query('SELECT p.*, (SELECT COUNT(*) FROM orders o WHERE o.promo_code = p.code AND o.payment_status = \'paid\') AS used FROM promo_codes p ORDER BY p.id DESC')->fetchAll();

$page_title = 'Settings';
$nav = 'settings';
require __DIR__ . '/_header.php';
?>
<div class="admin-head"><div><p class="label">Shop</p><h1>Settings</h1></div></div>

<div class="admin-grid">
  <section class="panel">
    <h2>Delivery fees</h2>
    <form method="post">
      <?= csrf_field() ?><input type="hidden" name="action" value="delivery">
      <table class="table compact">
        <tr><th>Region</th><th>Fee (GH₵)</th><th>Free over (GH₵)</th></tr>
        <?php foreach ($regions as $r): ?>
          <tr><td><?= e($r['name']) ?></td>
              <td><input class="input" type="number" step="0.01" min="0" name="fee[<?= (int) $r['id'] ?>]" value="<?= e($r['fee']) ?>"></td>
              <td><input class="input" type="number" step="0.01" min="0" name="free_over[<?= (int) $r['id'] ?>]" value="<?= e($r['free_over'] ?? '') ?>" placeholder="Never free"></td></tr>
        <?php endforeach; ?>
      </table>
      <button class="btn" style="margin-top:14px">Save delivery fees</button>
    </form>
  </section>

  <section class="panel">
    <h2>Promo codes</h2>
    <?php if ($promoErrors): ?><div class="flash flash-error" style="text-align:left;margin-bottom:12px"><?= implode('<br>', array_map('e', $promoErrors)) ?></div><?php endif; ?>
    <table class="table compact">
      <tr><th>Code</th><th>Off</th><th>Ends</th><th>Used</th><th></th></tr>
      <?php foreach ($promos as $p): $expired = $p['expires_at'] && $p['expires_at'] < date('Y-m-d'); ?>
        <tr>
          <td><strong><?= e($p['code']) ?></strong> <?= !$p['is_active'] ? '<span class="status">Off</span>' : ($expired ? '<span class="status status-cancelled">Ended</span>' : '') ?></td>
          <td><?= (int) $p['percent_off'] ?>%</td>
          <td><?= $p['expires_at'] ? e(date('j M Y', strtotime($p['expires_at']))) : '-' ?></td>
          <td><?= (int) $p['used'] ?></td>
          <td class="actions">
            <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="promo_toggle"><input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
              <button class="icon-btn" title="<?= $p['is_active'] ? 'Turn off' : 'Turn on' ?>"><i class="ti <?= $p['is_active'] ? 'ti-eye-off' : 'ti-eye' ?>"></i></button></form>
            <form method="post" data-confirm="Delete <?= e($p['code']) ?>?"><?= csrf_field() ?><input type="hidden" name="action" value="promo_delete"><input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
              <button class="icon-btn danger" title="Delete"><i class="ti ti-trash"></i></button></form>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
    <form method="post" class="promo-add" novalidate>
      <?= csrf_field() ?><input type="hidden" name="action" value="promo_add">
      <input class="input" name="code" placeholder="NEWCODE" style="text-transform:uppercase">
      <input class="input" name="percent_off" type="number" min="1" max="90" placeholder="% off">
      <input class="input" name="expires_at" type="date" title="End date (optional)">
      <button class="btn"><i class="ti ti-plus"></i> Add</button>
    </form>
  </section>
</div>

<section class="panel" style="max-width:520px">
  <h2>Change your password</h2>
  <?php if ($pwErrors): ?><div class="flash flash-error" style="text-align:left;margin-bottom:12px"><?= implode('<br>', array_map('e', $pwErrors)) ?></div><?php endif; ?>
  <form method="post" novalidate>
    <?= csrf_field() ?><input type="hidden" name="action" value="password">
    <label class="field"><span>Current password</span><input class="input" type="password" name="current_password" autocomplete="current-password"></label>
    <label class="field"><span>New password (10+ characters, letters and numbers)</span><input class="input" type="password" name="new_password" autocomplete="new-password"></label>
    <label class="field"><span>Confirm new password</span><input class="input" type="password" name="confirm_password" autocomplete="new-password"></label>
    <button class="btn">Change password</button>
  </form>
</section>

<?php require __DIR__ . '/_footer.php'; ?>
