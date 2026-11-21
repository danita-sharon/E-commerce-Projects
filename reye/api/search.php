<?php
// =========================================================
// Live search: returns up to 6 matching products as JSON.
// Called by app.js as the customer types.
// =========================================================
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');

$q = trim($_GET['q'] ?? '');
if (mb_strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

$like = '%' . $q . '%';
$stmt = $pdo->prepare(
    'SELECT DISTINCT p.name, p.slug, p.price
     FROM products p
     JOIN categories c ON c.id = p.category_id
     LEFT JOIN product_colours pc ON pc.product_id = p.id
     WHERE p.is_active = 1
       AND (p.name LIKE ? OR p.description LIKE ? OR c.name LIKE ? OR pc.colour_name LIKE ?)
     LIMIT 6'
);
$stmt->execute([$like, $like, $like, $like]);

$results = [];
foreach ($stmt->fetchAll() as $row) {
    $results[] = [
        'name'  => $row['name'],
        'price' => money($row['price']),
        'url'   => url('product.php?slug=' . urlencode($row['slug'])),
    ];
}
echo json_encode($results);
