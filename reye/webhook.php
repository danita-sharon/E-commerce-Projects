<?php
// =========================================================
// Paystack webhook: Paystack's servers call this URL directly
// when a payment succeeds (backup in case the customer closes
// the page before verify.php runs).
// Set it in Paystack: Settings -> API Keys & Webhooks -> Webhook URL
// =========================================================
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/payments.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$body = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] ?? '';

// Only accept messages really signed by Paystack with our secret key
if (!hash_equals(hash_hmac('sha512', $body, PAYSTACK_SECRET_KEY), $signature)) {
    http_response_code(401);
    exit;
}

$event = json_decode($body, true);
if (($event['event'] ?? '') === 'charge.success') {
    fulfil_order($pdo, (string) ($event['data']['reference'] ?? ''), $event['data']);
}

http_response_code(200);
echo 'OK';
