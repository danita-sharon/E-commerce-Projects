<?php
// =========================================================
// Reye AI chat endpoint (called by the chat bubble)
// =========================================================
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/reye_ai.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['ok' => false]));
}
csrf_check();

// Simple limit: 40 messages per 10 minutes per visitor (stops spam and runaway AI costs)
$now = time();
$_SESSION['ai_times'] = array_values(array_filter($_SESSION['ai_times'] ?? [], fn($t) => $t > $now - 600));
if (count($_SESSION['ai_times']) >= 40) {
    exit(json_encode(['ok' => true, 'reply' => ai_reply("You're sending messages very quickly. Give me a minute, or WhatsApp the team.", [],
        [['label' => 'WhatsApp', 'url' => whatsapp_link('Hi Reye, I have a question.')]])]));
}
$_SESSION['ai_times'][] = $now;

$message = trim($_POST['message'] ?? '');
if ($message === '' || mb_strlen($message) > 500) {
    exit(json_encode(['ok' => false, 'message' => 'Type a message under 500 characters.']));
}

try {
    echo json_encode(['ok' => true, 'reply' => reye_ai($pdo, $message)]);
} catch (Throwable $e) {
    error_log('Reye AI error: ' . $e->getMessage());
    echo json_encode(['ok' => true, 'reply' => ai_reply('Sorry, something went wrong on my side. You can reach the team on WhatsApp.', [],
        [['label' => 'WhatsApp', 'url' => whatsapp_link('Hi Reye, I have a question.')]])]);
}
