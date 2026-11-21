<?php
// =========================================================
// Bag API: add, update and remove items (called by app.js)
// Returns JSON so the page can update without reloading.
// =========================================================
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/cart.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['ok' => false, 'message' => 'Method not allowed']));
}
csrf_check();

const MAX_PER_LINE = 10;
$action = $_POST['action'] ?? '';
$_SESSION['cart'] = $_SESSION['cart'] ?? [];

function respond(PDO $pdo, bool $ok, string $message = ''): void
{
    $lines = cart_lines($pdo);
    $totals = [];
    foreach ($lines as $l) {
        $totals[$l['key']] = money($l['line_total']);
    }
    echo json_encode([
        'ok'       => $ok,
        'message'  => $message,
        'count'    => cart_count(),
        'subtotal' => money(cart_subtotal($lines)),
        'subtotal_raw' => cart_subtotal($lines),
        'lines'    => $totals,
    ]);
    exit;
}

if ($action === 'add') {
    $productId = (int) ($_POST['product_id'] ?? 0);
    $colourId  = (int) ($_POST['colour_id'] ?? 0);
    $size      = trim($_POST['size'] ?? '');
    $qty       = max(1, min(MAX_PER_LINE, (int) ($_POST['qty'] ?? 1)));

    // Check the product exists and is on sale
    $stmt = $pdo->prepare('SELECT id, name, sizes, stock FROM products WHERE id = ? AND is_active = 1');
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    if (!$product) {
        respond($pdo, false, 'This product is no longer available.');
    }

    // Check the size and colour really belong to this product
    $sizes = array_map('trim', explode(',', $product['sizes']));
    if (!in_array($size, $sizes, true)) {
        respond($pdo, false, 'Please pick a valid size.');
    }
    if ($colourId) {
        $stmt = $pdo->prepare('SELECT id FROM product_colours WHERE id = ? AND product_id = ?');
        $stmt->execute([$colourId, $productId]);
        if (!$stmt->fetch()) {
            respond($pdo, false, 'Please pick a valid colour.');
        }
    }

    // Same product + colour + size = same line in the bag
    $key = $productId . '-' . $colourId . '-' . $size;
    $already = (int) ($_SESSION['cart'][$key]['qty'] ?? 0);

    // Don't allow more than we have in stock
    $inBagForProduct = 0;
    foreach ($_SESSION['cart'] as $l) {
        if ((int) $l['product_id'] === $productId) {
            $inBagForProduct += (int) $l['qty'];
        }
    }
    if ($inBagForProduct + $qty > (int) $product['stock']) {
        respond($pdo, false, 'Sorry, only ' . (int) $product['stock'] . ' left in stock.');
    }

    $_SESSION['cart'][$key] = [
        'product_id' => $productId,
        'colour_id'  => $colourId,
        'size'       => $size,
        'qty'        => min(MAX_PER_LINE, $already + $qty),
    ];
    respond($pdo, true, $product['name'] . ' added to your bag');
}

if ($action === 'update') {
    $key = $_POST['key'] ?? '';
    $qty = (int) ($_POST['qty'] ?? 1);
    if (!isset($_SESSION['cart'][$key])) {
        respond($pdo, false, 'That item is no longer in your bag.');
    }
    if ($qty < 1) {
        unset($_SESSION['cart'][$key]);
        respond($pdo, true, 'Removed from your bag');
    }
    $stmt = $pdo->prepare('SELECT stock FROM products WHERE id = ?');
    $stmt->execute([(int) $_SESSION['cart'][$key]['product_id']]);
    $stock = (int) $stmt->fetchColumn();
    if ($qty > $stock) {
        respond($pdo, false, 'Sorry, only ' . $stock . ' left in stock.');
    }
    $_SESSION['cart'][$key]['qty'] = min(MAX_PER_LINE, $qty);
    respond($pdo, true);
}

if ($action === 'remove') {
    unset($_SESSION['cart'][$_POST['key'] ?? '']);
    respond($pdo, true, 'Removed from your bag');
}

respond($pdo, false, 'Unknown action.');
