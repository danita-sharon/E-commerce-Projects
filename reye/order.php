<?php
// =========================================================
// Order confirmation and tracking
// order.php?no=REYE-7F3A2C
// =========================================================
require_once __DIR__ . '/includes/functions.php';
require_login();

$stmt = $pdo->prepare('SELECT o.*, r.name AS region_name FROM orders o
                       JOIN delivery_regions r ON r.id = o.region_id WHERE o.order_no = ?');
$stmt->execute([$_GET['no'] ?? '']);
$order = $stmt->fetch();

// Customers can only see their own orders (admins can see all)
if (!$order || ((int) $order['user_id'] !== (int) current_user()['id'] && !is_admin())) {
    flash('error', 'We could not find that order.');
    redirect('account.php');
}

$items = $pdo->prepare('SELECT * FROM order_items WHERE order_id = ?');
$items->execute([$order['id']]);
$items = $items->fetchAll();

$steps = ['processing' => 'Order confirmed', 'shipped' => 'On its way', 'delivered' => 'Delivered'];
$reached = array_search($order['status'], array_keys($steps), true);
$thanks = isset($_GET['thanks']) && $order['payment_status'] === 'paid';

$page_title = 'Order ' . $order['order_no'];
require __DIR__ . '/includes/header.php';
?>
<section class="page-head">
  <?php if ($thanks): ?>
    <svg class="logo" style="width:48px;height:59px;margin:0 auto 16px"><use href="#reye-logo"/></svg>
    <p class="label">Thank you, <?= e(explode(' ', $order['full_name'])[0]) ?></p>
    <h1>Your order is confirmed</h1>
    <p style="color:var(--muted);margin:10px 0 0">We've started on your order. You can follow it any time from <a href="<?= url('account.php') ?>" style="text-decoration:underline">My account</a>.</p>
  <?php else: ?>
    <p class="label">Order</p><h1><?= e($order['order_no']) ?></h1>
  <?php endif; ?>
</section>

<div class="wrap order-wrap">
  <?php if ($order['payment_status'] === 'paid' && $order['status'] !== 'cancelled'): ?>
    <div class="timeline">
      <?php $i = 0; foreach ($steps as $key => $label): ?>
        <div class="tl-step <?= $reached !== false && $i <= $reached ? 'done' : '' ?>">
          <span class="tl-dot"><i class="ti ti-check"></i></span>
          <span><?= e($label) ?></span>
        </div>
      <?php $i++; endforeach; ?>
    </div>
  <?php elseif ($order['status'] === 'cancelled'): ?>
    <p class="flash flash-error">This order was cancelled.</p>
  <?php else: ?>
    <p class="flash flash-error">This order has not been paid yet.</p>
  <?php endif; ?>

  <div class="order-grid">
    <div class="panel">
      <p class="label">Items</p>
      <?php foreach ($items as $it): ?>
        <div class="sum-line" style="padding:10px 0;border-bottom:1px solid var(--line)">
          <div style="flex:1"><?= e($it['product_name']) ?> × <?= (int) $it['quantity'] ?><br>
            <small style="color:var(--muted)"><?= e(trim($it['colour'] . ' · ' . $it['size'], ' ·')) ?></small></div>
          <span><?= money($it['unit_price'] * $it['quantity']) ?></span>
        </div>
      <?php endforeach; ?>
      <div class="totals">
        <div><span>Subtotal</span><span><?= money($order['subtotal']) ?></span></div>
        <div><span>Delivery (<?= e($order['region_name']) ?>)</span><span><?= (float) $order['delivery_fee'] > 0 ? money($order['delivery_fee']) : 'Free' ?></span></div>
        <?php if ((float) $order['discount'] > 0): ?>
          <div style="color:var(--success)"><span><?= e($order['promo_code']) ?></span><span>− <?= money($order['discount']) ?></span></div>
        <?php endif; ?>
        <div class="grand"><span>Total paid</span><span><?= money($order['total']) ?></span></div>
      </div>
    </div>

    <div class="panel">
      <p class="label">Delivering to</p>
      <p style="margin:0"><?= e($order['full_name']) ?><br><?= nl2br(e($order['address'])) ?><br><?= e($order['region_name']) ?><br><?= e($order['phone']) ?></p>
      <p class="label" style="margin-top:22px">Placed</p>
      <p style="margin:0"><?= e(date('j M Y, g:ia', strtotime($order['created_at']))) ?></p>
      <a class="btn btn-outline btn-block" style="margin-top:22px" target="_blank" rel="noopener"
         href="<?= e(whatsapp_link('Hi Reye, I have a question about order ' . $order['order_no'] . '.')) ?>">
        <i class="ti ti-brand-whatsapp"></i> Questions? WhatsApp us
      </a>
      <a class="btn btn-block" style="margin-top:8px" href="<?= url('shop.php') ?>">Keep shopping</a>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
