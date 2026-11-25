<?php
// =========================================================
// Create (or update) the order, then hand back what the
// Paystack popup needs. Totals are worked out HERE, never
// taken from the browser.
// =========================================================
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/cart.php';
require_once __DIR__ . '/../includes/payments.php';
header('Content-Type: application/json');

function fail(string $message, int $code = 200): void
{
    http_response_code($code);
    echo json_encode(['ok' => false, 'message' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    fail('Method not allowed', 405);
}
csrf_check();
if (!is_logged_in()) {
    fail('Please log in to check out.', 401);
}
if (!paystack_ready()) {
    fail('Payments are not set up yet. Add your Paystack test keys in config/config.php.');
}

$user = current_user();
$fullName = trim($_POST['full_name'] ?? '');
$phone    = preg_replace('/\s+/', '', $_POST['phone'] ?? '');
$address  = trim($_POST['address'] ?? '');
$regionId = (int) ($_POST['region_id'] ?? 0);
$promo    = trim($_POST['promo_code'] ?? '');

// ---- Validate ----
if (mb_strlen($fullName) < 2)                         fail('Enter the name for delivery.');
if (!preg_match('/^(\+?\d{9,15})$/', $phone))         fail('Enter a valid phone number for delivery.');
if (mb_strlen($address) < 5 || mb_strlen($address) > 250) fail('Enter your delivery address (area, street, landmark).');

$lines = cart_lines($pdo);
if (!$lines) {
    fail('Your bag is empty.');
}

// Make sure everything is still in stock
$needed = [];
foreach ($lines as $l) {
    $needed[$l['product_id']] = ($needed[$l['product_id']] ?? 0) + $l['qty'];
}
foreach ($lines as $l) {
    if ($needed[$l['product_id']] > $l['stock']) {
        fail("Sorry, only {$l['stock']} of {$l['name']} left. Please update your bag.");
    }
}

$subtotal = cart_subtotal($lines);
$t = order_totals($pdo, $subtotal, $regionId, $promo);
if (!$t['region']) {
    fail('Choose where we should deliver.');
}
if ($promo !== '' && !$t['promo']) {
    fail("That promo code isn't valid. Remove it or try another.");
}

$pdo->beginTransaction();
try {
    // Re-use this customer's unpaid order from an earlier attempt, if there is one
    $orderId = null;
    if (!empty($_SESSION['pending_order_id'])) {
        $stmt = $pdo->prepare("SELECT id, order_no FROM orders WHERE id = ? AND user_id = ? AND payment_status <> 'paid'");
        $stmt->execute([$_SESSION['pending_order_id'], $user['id']]);
        if ($row = $stmt->fetch()) {
            $orderId = (int) $row['id'];
            $orderNo = $row['order_no'];
        }
    }

    $values = [
        $fullName, $user['email'], $phone, $address, (int) $t['region']['id'],
        $subtotal, $t['fee'], $t['discount'], $t['total'], $t['promo']['code'] ?? null,
    ];

    if ($orderId) {
        $pdo->prepare('UPDATE orders SET full_name = ?, email = ?, phone = ?, address = ?, region_id = ?,
                       subtotal = ?, delivery_fee = ?, discount = ?, total = ?, promo_code = ? WHERE id = ?')
            ->execute(array_merge($values, [$orderId]));
        $pdo->prepare('DELETE FROM order_items WHERE order_id = ?')->execute([$orderId]);
    } else {
        do {   // order numbers like REYE-7F3A2C
            $orderNo = 'REYE-' . strtoupper(bin2hex(random_bytes(3)));
            $check = $pdo->prepare('SELECT 1 FROM orders WHERE order_no = ?');
            $check->execute([$orderNo]);
        } while ($check->fetch());

        $pdo->prepare('INSERT INTO orders (order_no, user_id, full_name, email, phone, address, region_id,
                       subtotal, delivery_fee, discount, total, promo_code)
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')
            ->execute(array_merge([$orderNo, $user['id']], $values));
        $orderId = (int) $pdo->lastInsertId();
        $_SESSION['pending_order_id'] = $orderId;
    }

    // Save each item with the price at the time of purchase
    $item = $pdo->prepare('INSERT INTO order_items (order_id, product_id, product_name, colour, size, unit_price, quantity)
                           VALUES (?, ?, ?, ?, ?, ?, ?)');
    foreach ($lines as $l) {
        $item->execute([$orderId, $l['product_id'], $l['name'], $l['colour'], $l['size'], $l['price'], $l['qty']]);
    }

    // A fresh, unique reference for this payment attempt
    $reference = 'RY-' . $orderNo . '-' . strtoupper(bin2hex(random_bytes(3)));
    $pdo->prepare("INSERT INTO payments (order_id, reference, amount, status) VALUES (?, ?, ?, 'initialized')")
        ->execute([$orderId, $reference, $t['total']]);

    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    error_log('Create order failed: ' . $e->getMessage());
    fail('We could not create your order. Please try again.');
}

echo json_encode([
    'ok'        => true,
    'key'       => PAYSTACK_PUBLIC_KEY,
    'email'     => $user['email'],
    'amount'    => to_pesewas($t['total']),
    'reference' => $reference,
    'order_no'  => $orderNo,
    'verify'    => url('verify.php?reference=' . urlencode($reference)),
]);
