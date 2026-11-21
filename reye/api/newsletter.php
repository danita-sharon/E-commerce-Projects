<?php
// =========================================================
// Newsletter sign-up (footer form)
// =========================================================
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['ok' => false, 'message' => 'Method not allowed']));
}
csrf_check();

$email = trim($_POST['email'] ?? '');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    exit(json_encode(['ok' => false, 'message' => 'Enter a valid email address.']));
}

// INSERT IGNORE: signing up twice is not an error
$stmt = $pdo->prepare('INSERT IGNORE INTO newsletter_subscribers (email) VALUES (?)');
$stmt->execute([$email]);

echo json_encode(['ok' => true, 'message' => 'Welcome to the Reye fam.']);
