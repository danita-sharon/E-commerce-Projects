<?php
// =========================================================
// Wishlist API: tap the heart to save or unsave a product
// =========================================================
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['ok' => false, 'message' => 'Method not allowed']));
}
csrf_check();

if (!is_logged_in()) {
    http_response_code(401);
    exit(json_encode(['ok' => false, 'message' => 'Log in to save your favourites']));
}

$userId = (int) current_user()['id'];
$productId = (int) ($_POST['product_id'] ?? 0);

$stmt = $pdo->prepare('SELECT 1 FROM wishlist WHERE user_id = ? AND product_id = ?');
$stmt->execute([$userId, $productId]);

if ($stmt->fetch()) {
    $pdo->prepare('DELETE FROM wishlist WHERE user_id = ? AND product_id = ?')->execute([$userId, $productId]);
    $saved = false;
} else {
    $check = $pdo->prepare('SELECT id FROM products WHERE id = ? AND is_active = 1');
    $check->execute([$productId]);
    if (!$check->fetch()) {
        exit(json_encode(['ok' => false, 'message' => 'This product is no longer available.']));
    }
    $pdo->prepare('INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)')->execute([$userId, $productId]);
    $saved = true;
}

$count = $pdo->prepare('SELECT COUNT(*) FROM wishlist WHERE user_id = ?');
$count->execute([$userId]);

echo json_encode(['ok' => true, 'saved' => $saved, 'count' => (int) $count->fetchColumn()]);
