<?php
// =========================================================
// Admin dashboard: today's picture of the shop
// =========================================================
require_once __DIR__ . '/_init.php';

$revenue      = (float) $pdo->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE payment_status = 'paid' AND status <> 'cancelled'")->fetchColumn();
$paidOrders   = (int) $pdo->query("SELECT COUNT(*) FROM orders WHERE payment_status = 'paid'")->fetchColumn();
$toShip       = (int) $pdo->query("SELECT COUNT(*) FROM orders WHERE payment_status = 'paid' AND status = 'processing'")->fetchColumn();
$customers    = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
$lowStock     = $pdo->query('SELECT id, name, stock FROM products WHERE is_active = 1 AND stock <= 3 ORDER BY stock')->fetchAll();
$recent       = $pdo->query('SELECT order_no, full_name, total, status, payment_status, created_at FROM orders ORDER BY id DESC LIMIT 8')->fetchAll();
$newRequests  = $pdo->query("SELECT name, type, message, created_at FROM customer_requests WHERE status = 'new' ORDER BY id DESC LIMIT 4")->fetchAll();

// Sales for the last 7 days (worked out in PHP so it runs on any database)
$days = [];
for ($i = 6; $i >= 0; $i--) {
    $days[date('Y-m-d', strtotime("-$i days"))] = 0.0;
}
$stmt = $pdo->prepare("SELECT paid_at, total FROM orders WHERE payment_status = 'paid' AND paid_at >= ?");
$stmt->execute([array_key_first($days) . ' 00:00:00']);
foreach ($stmt->fetchAll() as $row) {
    $d = substr($row['paid_at'], 0, 10);
    if (isset($days[$d])) {
        $days[$d] += (float) $row['total'];
    }
}
$maxDay = max(array_merge([1], array_values($days)));
$week = array_sum($days);

$page_title = 'Dashboard';
$nav = 'dashboard';
require __DIR__ . '/_header.php';
?>
<div class="admin-head">
  <div><p class="label">Dashboard</p><h1>Good <?= date('G') < 12 ? 'morning' : (date('G') < 17 ? 'afternoon' : 'evening') ?>, <?= e(explode(' ', current_user()['full_name'])[0]) ?></h1></div>
  <a class="btn" href="<?= url('admin/product_edit.php') ?>"><i class="ti ti-plus"></i> Add product</a>
</div>

<div class="stats">
  <div class="stat"><span>Revenue</span><strong><?= money($revenue) ?></strong><small>All paid orders</small></div>
  <div class="stat"><span>Paid orders</span><strong><?= $paidOrders ?></strong><small><?= money($week) ?> this week</small></div>
  <a class="stat <?= $toShip ? 'attention' : '' ?>" href="<?= url('admin/orders.php?status=processing') ?>"><span>To send out</span><strong><?= $toShip ?></strong><small>Paid, not shipped yet</small></a>
  <a class="stat <?= $new_requests ? 'attention' : '' ?>" href="<?= url('admin/requests.php') ?>"><span>New requests</span><strong><?= $new_requests ?></strong><small><?= $customers ?> <?= $customers === 1 ? 'customer' : 'customers' ?> signed up</small></a>
</div>

<div class="admin-grid">
  <section class="panel">
    <div class="panel-head"><h2>Sales this week</h2><span class="muted"><?= money($week) ?></span></div>
    <div class="bars">
      <?php foreach ($days as $date => $amount): ?>
        <div class="bar-col" title="<?= e(date('D j M', strtotime($date)) . ': ' . money($amount)) ?>">
          <div class="bar-fill" style="height:<?= max(2, round($amount / $maxDay * 100)) ?>%"></div>
          <span><?= e(date('D', strtotime($date))) ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="panel">
    <div class="panel-head"><h2>Low stock</h2><a href="<?= url('admin/products.php') ?>">All products</a></div>
    <?php if (!$lowStock): ?>
      <p class="muted">Everything is well stocked.</p>
    <?php else: foreach ($lowStock as $p): ?>
      <a class="mini-row" href="<?= url('admin/product_edit.php?id=' . (int) $p['id']) ?>">
        <span><?= e($p['name']) ?></span>
        <span class="status <?= (int) $p['stock'] === 0 ? 'status-cancelled' : 'status-processing' ?>"><?= (int) $p['stock'] === 0 ? 'Sold out' : (int) $p['stock'] . ' left' ?></span>
      </a>
    <?php endforeach; endif; ?>
  </section>
</div>

<div class="admin-grid">
  <section class="panel">
    <div class="panel-head"><h2>Recent orders</h2><a href="<?= url('admin/orders.php') ?>">All orders</a></div>
    <?php if (!$recent): ?>
      <p class="muted">No orders yet.</p>
    <?php else: ?>
      <table class="table">
        <tr><th>Order</th><th>Customer</th><th>Total</th><th>Status</th></tr>
        <?php foreach ($recent as $o): ?>
          <tr onclick="location='<?= url('admin/order.php?no=' . urlencode($o['order_no'])) ?>'">
            <td><strong><?= e($o['order_no']) ?></strong><br><small class="muted"><?= e(date('j M, g:ia', strtotime($o['created_at']))) ?></small></td>
            <td><?= e($o['full_name']) ?></td>
            <td><?= money($o['total']) ?></td>
            <td><?= status_badge($o['payment_status'] === 'paid' ? $o['status'] : $o['payment_status']) ?></td>
          </tr>
        <?php endforeach; ?>
      </table>
    <?php endif; ?>
  </section>

  <section class="panel">
    <div class="panel-head"><h2>New requests</h2><a href="<?= url('admin/requests.php') ?>">Inbox</a></div>
    <?php if (!$newRequests): ?>
      <p class="muted">You're all caught up.</p>
    <?php else: foreach ($newRequests as $r): ?>
      <a class="mini-row" href="<?= url('admin/requests.php') ?>" style="display:block">
        <span><strong><?= e($r['name']) ?></strong> <span class="tag"><?= e($r['type']) ?></span></span>
        <small class="muted" style="display:block"><?= e(mb_strimwidth(str_replace("\n", ' · ', $r['message']), 0, 90, '…')) ?></small>
      </a>
    <?php endforeach; endif; ?>
  </section>
</div>

<?php require __DIR__ . '/_footer.php'; ?>
