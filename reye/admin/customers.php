<?php
// =========================================================
// Customers: who signed up and what they've ordered
// =========================================================
require_once __DIR__ . '/_init.php';

$q = trim($_GET['q'] ?? '');
$sql = "SELECT u.id, u.full_name, u.email, u.phone, u.created_at,
          (SELECT COUNT(*) FROM orders o WHERE o.user_id = u.id AND o.payment_status = 'paid') AS orders,
          (SELECT COALESCE(SUM(total), 0) FROM orders o WHERE o.user_id = u.id AND o.payment_status = 'paid' AND o.status <> 'cancelled') AS spent
        FROM users u WHERE u.role = 'customer'";
$params = [];
if ($q !== '') {
    $sql .= ' AND (u.full_name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)';
    $params = ["%$q%", "%$q%", "%$q%"];
}
$stmt = $pdo->prepare($sql . ' ORDER BY u.id DESC');
$stmt->execute($params);
$customers = $stmt->fetchAll();
$subscribers = (int) $pdo->query('SELECT COUNT(*) FROM newsletter_subscribers')->fetchColumn();

$page_title = 'Customers';
$nav = 'customers';
require __DIR__ . '/_header.php';
?>
<div class="admin-head">
  <div><p class="label">People</p><h1>Customers</h1>
    <p class="muted" style="margin:4px 0 0"><?= count($customers) ?> customers · <?= $subscribers ?> newsletter subscribers</p></div>
  <form class="admin-search" method="get"><i class="ti ti-search"></i><input class="input" name="q" value="<?= e($q) ?>" placeholder="Name, email or phone"></form>
</div>

<div class="panel" style="padding:0">
  <?php if (!$customers): ?>
    <p class="muted" style="padding:24px;margin:0">No customers yet.</p>
  <?php else: ?>
    <table class="table">
      <tr><th>Name</th><th>Contact</th><th>Joined</th><th>Orders</th><th>Spent</th></tr>
      <?php foreach ($customers as $c): ?>
        <tr onclick="location='<?= url('admin/orders.php?status=paid&q=' . urlencode($c['email'])) ?>'">
          <td><strong><?= e($c['full_name']) ?></strong></td>
          <td><?= e($c['email']) ?><br><small class="muted"><?= e($c['phone']) ?></small></td>
          <td><?= e(date('j M Y', strtotime($c['created_at']))) ?></td>
          <td><?= (int) $c['orders'] ?></td>
          <td><?= money($c['spent']) ?></td>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/_footer.php'; ?>
