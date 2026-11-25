<?php
// =========================================================
// Orders: filter by status, search by order number or name
// =========================================================
require_once __DIR__ . '/_init.php';

$filter = $_GET['status'] ?? 'paid';
$q = trim($_GET['q'] ?? '');

$tabs = [
    'paid'       => 'All paid',
    'processing' => 'To send out',
    'shipped'    => 'Shipped',
    'delivered'  => 'Delivered',
    'cancelled'  => 'Cancelled',
    'unpaid'     => 'Unpaid',
];
if (!isset($tabs[$filter])) {
    $filter = 'paid';
}

$where = [];
$params = [];
if ($filter === 'paid') {
    $where[] = "o.payment_status = 'paid'";
} elseif ($filter === 'unpaid') {
    $where[] = "o.payment_status <> 'paid'";
} else {
    $where[] = "o.payment_status = 'paid' AND o.status = ?";
    $params[] = $filter;
}
if ($q !== '') {
    $where[] = '(o.order_no LIKE ? OR o.full_name LIKE ? OR o.email LIKE ? OR o.phone LIKE ?)';
    array_push($params, "%$q%", "%$q%", "%$q%", "%$q%");
}

$stmt = $pdo->prepare('SELECT o.*, r.name AS region_name,
                         (SELECT SUM(quantity) FROM order_items i WHERE i.order_id = o.id) AS items
                       FROM orders o JOIN delivery_regions r ON r.id = o.region_id
                       WHERE ' . implode(' AND ', $where) . ' ORDER BY o.id DESC LIMIT 200');
$stmt->execute($params);
$orders = $stmt->fetchAll();

$page_title = 'Orders';
$nav = 'orders';
require __DIR__ . '/_header.php';
?>
<div class="admin-head">
  <div><p class="label">Sales</p><h1>Orders</h1></div>
  <form class="admin-search" method="get">
    <input type="hidden" name="status" value="<?= e($filter) ?>">
    <i class="ti ti-search"></i><input class="input" name="q" value="<?= e($q) ?>" placeholder="Order number, name, email or phone">
  </form>
</div>

<div class="tabs">
  <?php foreach ($tabs as $key => $label): ?>
    <a href="<?= url('admin/orders.php?status=' . $key . ($q ? '&q=' . urlencode($q) : '')) ?>" class="<?= $filter === $key ? 'active' : '' ?>"><?= $label ?></a>
  <?php endforeach; ?>
</div>

<div class="panel" style="padding:0">
  <?php if (!$orders): ?>
    <p class="muted" style="padding:24px">No orders here<?= $q ? ' matching "' . e($q) . '"' : '' ?>.</p>
  <?php else: ?>
    <table class="table">
      <tr><th>Order</th><th>Customer</th><th>Delivery</th><th>Items</th><th>Total</th><th>Status</th></tr>
      <?php foreach ($orders as $o): ?>
        <tr onclick="location='<?= url('admin/order.php?no=' . urlencode($o['order_no'])) ?>'">
          <td><strong><?= e($o['order_no']) ?></strong><br><small class="muted"><?= e(date('j M Y, g:ia', strtotime($o['created_at']))) ?></small></td>
          <td><?= e($o['full_name']) ?><br><small class="muted"><?= e($o['phone']) ?></small></td>
          <td><?= e($o['region_name']) ?></td>
          <td><?= (int) $o['items'] ?></td>
          <td><?= money($o['total']) ?></td>
          <td><?= status_badge($o['payment_status'] === 'paid' ? $o['status'] : $o['payment_status']) ?></td>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/_footer.php'; ?>
