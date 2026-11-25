<?php
// =========================================================
// After the Paystack popup says "paid", the browser comes here.
// We ask Paystack directly whether the payment really happened.
// =========================================================
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/payments.php';
require_login();

$reference = $_GET['reference'] ?? '';
$stmt = $pdo->prepare('SELECT p.*, o.order_no, o.user_id FROM payments p JOIN orders o ON o.id = p.order_id WHERE p.reference = ?');
$stmt->execute([$reference]);
$payment = $stmt->fetch();

if (!$payment || (int) $payment['user_id'] !== (int) current_user()['id']) {
    flash('error', 'We could not find that payment.');
    redirect('cart.php');
}

$result = paystack_request('GET', '/transaction/verify/' . rawurlencode($reference));

if (!$result || empty($result['status'])) {
    flash('error', "We couldn't confirm your payment yet. If you were charged, don't worry: your order " . $payment['order_no'] . ' will update automatically. Contact us on WhatsApp if it does not.');
    redirect('checkout.php');
}

if (fulfil_order($pdo, $reference, $result['data'])) {
    unset($_SESSION['cart'], $_SESSION['pending_order_id']);
    redirect('order.php?no=' . urlencode($payment['order_no']) . '&thanks=1');
}

flash('error', 'Your payment was not completed. You have not been charged. Please try again.');
redirect('checkout.php');
