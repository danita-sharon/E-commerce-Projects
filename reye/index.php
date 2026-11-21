<?php
require_once __DIR__ . '/includes/functions.php';

$count = $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
echo 'Connected to the database. Reye has ' . e($count) . ' products.';