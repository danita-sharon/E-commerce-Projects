<?php
// =========================================================
// My account: details and order history
// =========================================================
require_once __DIR__ . '/includes/functions.php';
require_login();

$user = current_user();
$stmt = $pdo->prepare('SELECT order_no, total, status, payment_status, created_at
                       FROM orders WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$user['id']]);
$orders = $stmt->fetchAll();

$page_title = 'My account';
require __DIR__ . '/includes/header.php';
?>
<section class="page-head"><p class="label">My account</p><h1>Hi, <?= e(explode(' ', $user['full_name'])[0]) ?></h1></section>

<div class="wrap account-grid">
  <aside class="panel">
    <p class="label">Your details</p>
    <p style="margin:0"><?= e($user['full_name']) ?></p>
    <p style="margin:0;color:var(--muted)"><?= e($user['email']) ?></p>
    <p style="margin:0 0 18px;color:var(--muted)"><?= e($user['phone']) ?></p>
    <?php if (is_admin()): ?>
      <a class="btn btn-block" href="<?= url('admin/index.php') ?>" style="margin-bottom:8px">Admin dashboard</a>
    <?php endif; ?>
    <a class="btn btn-outline btn-block" href="<?= url('wishlist.php') ?>" style="margin-bottom:8px">Saved items</a>
    <form method="post" action="<?= url('logout.php') ?>">
      <?= csrf_field() ?>
      <button class="btn btn-outline btn-block" type="submit">Log out</button>
    </form>
  </aside>

  <section>
    <p class="label">Your orders</p>
    <?php if (!$orders): ?>
      <div class="panel empty-state">
        <p class="serif" style="font-size:24px;margin:0 0 6px">No orders yet</p>
        <p style="color:var(--muted);margin:0 0 18px">Your first pair is waiting for you.</p>
        <a class="btn" href="<?= url('shop.php') ?>">Shop the collection</a>
      </div>
    <?php else: ?>
      <div class="orders">
        <?php foreach ($orders as $o): ?>
          <a class="order-row" href="<?= url('order.php?no=' . urlencode($o['order_no'])) ?>">
            <span><strong><?= e($o['order_no']) ?></strong><br><small><?= e(date('j M Y', strtotime($o['created_at']))) ?></small></span>
            <span class="status status-<?= e($o['payment_status'] === 'paid' ? $o['status'] : $o['payment_status']) ?>">
              <?= e($o['payment_status'] === 'paid' ? ucfirst($o['status']) : ucfirst($o['payment_status'])) ?>
            </span>
            <span><?= money($o['total']) ?></span>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
