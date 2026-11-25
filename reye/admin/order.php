<?php
// =========================================================
// One order: details, payment record, update the status
// =========================================================
require_once __DIR__ . '/_init.php';

$no = $_GET['no'] ?? '';
$stmt = $pdo->prepare('SELECT o.*, r.name AS region_name FROM orders o JOIN delivery_regions r ON r.id = o.region_id WHERE o.order_no = ?');
$stmt->execute([$no]);
$order = $stmt->fetch();
if (!$order) {
    flash('error', 'Order not found.');
    redirect('admin/orders.php');
}

$statuses = ['processing' => 'Confirmed, being made or packed', 'shipped' => 'Shipped, on its way', 'delivered' => 'Delivered', 'cancelled' => 'Cancelled'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $new = $_POST['status'] ?? '';
    if ($order['payment_status'] !== 'paid') {
        flash('error', 'Only paid orders can be updated.');
    } elseif (!isset($statuses[$new])) {
        flash('error', 'Choose a valid status.');
    } else {
        $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?')->execute([$new, $order['id']]);
        // Cancelling puts the items back in stock
        if ($new === 'cancelled' && $order['status'] !== 'cancelled') {
            $items = $pdo->prepare('SELECT product_id, quantity FROM order_items WHERE order_id = ? AND product_id IS NOT NULL');
            $items->execute([$order['id']]);
            $back = $pdo->prepare('UPDATE products SET stock = stock + ? WHERE id = ?');
            foreach ($items->fetchAll() as $it) {
                $back->execute([$it['quantity'], $it['product_id']]);
            }
        }
        flash('success', 'Order ' . $order['order_no'] . ' marked as ' . $new . '.');
    }
    redirect('admin/order.php?no=' . urlencode($order['order_no']));
}

$items = $pdo->prepare('SELECT * FROM order_items WHERE order_id = ?');
$items->execute([$order['id']]);
$items = $items->fetchAll();
$payments = $pdo->prepare('SELECT * FROM payments WHERE order_id = ? ORDER BY id DESC');
$payments->execute([$order['id']]);
$payments = $payments->fetchAll();

$messages = [
    'processing' => "Hi {name}, thank you for your Reye order {no}. We're preparing it now.",
    'shipped'    => "Hi {name}, your Reye order {no} is on its way to you.",
    'delivered'  => "Hi {name}, your Reye order {no} has been delivered. We hope you love it! We'd be grateful for a short review.",
    'cancelled'  => "Hi {name}, about your Reye order {no}: ",
];
$first = explode(' ', $order['full_name'])[0];
$waText = str_replace(['{name}', '{no}'], [$first, $order['order_no']], $messages[$order['status']] ?? 'Hi {name}, about your Reye order {no}: ');

$page_title = 'Order ' . $order['order_no'];
$nav = 'orders';
require __DIR__ . '/_header.php';
?>
<div class="admin-head">
  <div><a href="<?= url('admin/orders.php') ?>" class="back-link"><i class="ti ti-arrow-left"></i> Orders</a>
    <h1><?= e($order['order_no']) ?> <?= status_badge($order['payment_status'] === 'paid' ? $order['status'] : $order['payment_status']) ?></h1>
    <p class="muted" style="margin:4px 0 0">Placed <?= e(date('j M Y, g:ia', strtotime($order['created_at']))) ?><?= $order['paid_at'] ? ' · Paid ' . e(date('j M Y, g:ia', strtotime($order['paid_at']))) : '' ?></p>
  </div>
  <a class="btn btn-outline" target="_blank" rel="noopener" href="https://wa.me/<?= e(wa_number($order['phone'])) ?>?text=<?= rawurlencode($waText) ?>">
    <i class="ti ti-brand-whatsapp"></i> Message customer
  </a>
</div>

<div class="admin-grid wide-left">
  <div>
    <section class="panel">
      <h2>Items</h2>
      <table class="table">
        <tr><th>Product</th><th>Colour</th><th>Size</th><th>Qty</th><th>Price</th></tr>
        <?php foreach ($items as $it): ?>
          <tr><td><?= e($it['product_name']) ?></td><td><?= e($it['colour']) ?></td><td><?= e($it['size']) ?></td><td><?= (int) $it['quantity'] ?></td><td><?= money($it['unit_price'] * $it['quantity']) ?></td></tr>
        <?php endforeach; ?>
      </table>
      <div class="totals" style="max-width:320px;margin-left:auto">
        <div><span>Subtotal</span><span><?= money($order['subtotal']) ?></span></div>
        <div><span>Delivery (<?= e($order['region_name']) ?>)</span><span><?= (float) $order['delivery_fee'] > 0 ? money($order['delivery_fee']) : 'Free' ?></span></div>
        <?php if ((float) $order['discount'] > 0): ?><div><span><?= e($order['promo_code']) ?></span><span>− <?= money($order['discount']) ?></span></div><?php endif; ?>
        <div class="grand"><span>Total</span><span><?= money($order['total']) ?></span></div>
      </div>
    </section>

    <section class="panel">
      <h2>Payments</h2>
      <?php if (!$payments): ?><p class="muted">No payment attempts.</p><?php else: ?>
      <table class="table">
        <tr><th>Paystack reference</th><th>Method</th><th>Amount</th><th>Result</th></tr>
        <?php foreach ($payments as $p): ?>
          <tr><td><code><?= e($p['reference']) ?></code></td><td><?= e(str_replace('_', ' ', $p['channel'] ?: '-')) ?></td><td><?= money($p['amount']) ?></td>
              <td><?= status_badge($p['status'] === 'success' ? 'paid' : ($p['status'] === 'failed' ? 'failed' : 'unpaid')) ?></td></tr>
        <?php endforeach; ?>
      </table>
      <?php endif; ?>
    </section>
  </div>

  <div>
    <section class="panel">
      <h2>Update status</h2>
      <?php if ($order['payment_status'] !== 'paid'): ?>
        <p class="muted">This order has not been paid, so there is nothing to send yet.</p>
      <?php else: ?>
        <form method="post">
          <?= csrf_field() ?>
          <?php foreach ($statuses as $key => $label): ?>
            <label class="radio-row"><input type="radio" name="status" value="<?= $key ?>" <?= $order['status'] === $key ? 'checked' : '' ?>> <?= e($label) ?></label>
          <?php endforeach; ?>
          <button class="btn btn-block" style="margin-top:12px">Save status</button>
        </form>
        <p class="muted" style="font-size:12px;margin:10px 0 0">The customer sees this on their order page. Cancelling puts the items back in stock. Refunds are done in your Paystack dashboard.</p>
      <?php endif; ?>
    </section>

    <section class="panel">
      <h2>Deliver to</h2>
      <p style="margin:0"><?= e($order['full_name']) ?><br><?= nl2br(e($order['address'])) ?><br><?= e($order['region_name']) ?></p>
      <p style="margin:12px 0 0"><i class="ti ti-phone"></i> <a href="tel:<?= e($order['phone']) ?>"><?= e($order['phone']) ?></a><br>
         <i class="ti ti-mail"></i> <a href="mailto:<?= e($order['email']) ?>"><?= e($order['email']) ?></a></p>
    </section>
  </div>
</div>

<?php require __DIR__ . '/_footer.php'; ?>
