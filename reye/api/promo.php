<?php
// =========================================================
// Check a promo code (checkout page). The real discount is
// recalculated on the server when the order is created.
// =========================================================
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/payments.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['ok' => false]));
}
csrf_check();

$promo = find_promo($pdo, $_POST['code'] ?? '');
echo json_encode($promo
    ? ['ok' => true, 'code' => $promo['code'], 'percent' => (int) $promo['percent_off'], 'message' => $promo['percent_off'] . '% off applied']
    : ['ok' => false, 'message' => "That code isn't valid or has expired."]);
