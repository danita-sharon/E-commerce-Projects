
<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CartController.php';

// Customer must be logged in.
if (!is_logged_in()) {
    redirect(base_url() . '/views/login.php');
}

// Only accept a valid payment reference.
$reference = $_GET['ref'] ?? '';

if (!is_string($reference) ||
    !preg_match('/^[A-Za-z0-9._=-]{1,100}$/', $reference)) {
    redirect(base_url() . '/views/payment_failed.php');
}

// Load the secret key from a private configuration file.
$configFile = __DIR__ . '/../core/paystack_config.php';

if (!file_exists($configFile)) {
    error_log('Paystack configuration is missing.');
    redirect(base_url() . '/views/payment_failed.php');
}

$secretKey = require $configFile;

if (!is_string($secretKey) ||
    !str_starts_with($secretKey, 'sk_test_')) {
    error_log('Invalid Paystack test configuration.');
    redirect(base_url() . '/views/payment_failed.php');
}

// Ask Paystack to verify the transaction.
$url = 'https://api.paystack.co/transaction/verify/'
     . rawurlencode($reference);

$ch = curl_init($url);

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $secretKey,
        'Accept: application/json'
    ],
    CURLOPT_TIMEOUT => 20
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$data = json_decode($response ?: '', true);
$payment = $data['data'] ?? [];

error_log('SHOPPN PAYSTACK VERIFICATION: ' . json_encode([
    'http_code' => $httpCode,
    'api_status' => $data['status'] ?? null,
    'payment_status' => $payment['status'] ?? null,
    'currency' => $payment['currency'] ?? null,
    'reference_matches' => ($payment['reference'] ?? null) === $reference
]));

if ($httpCode !== 200 ||
    ($data['status'] ?? false) !== true ||
    ($payment['status'] ?? '') !== 'success' ||
    ($payment['reference'] ?? '') !== $reference ||
    ($payment['currency'] ?? '') !== 'GHS') {
    redirect(base_url() . '/views/payment_failed.php');
}

// Verify the customer and the exact amount.
$customerEmail = $_SESSION['customer_email'] ?? '';
$paidEmail = $payment['customer']['email'] ?? '';

$cartController = new CartController();
$ip = get_ip();

$cartTotal = $cartController->getCartTotal($ip);
$expectedAmount = (int) round($cartTotal * 100);
$paidAmount = $payment['amount'] ?? null;

error_log('SHOPPN PAYMENT CHECK: ' . json_encode([
    'expected_amount' => $expectedAmount,
    'paid_amount' => $paidAmount,
    'email_matches' => is_string($paidEmail) &&
        strcasecmp($customerEmail, $paidEmail) === 0
]));

if ($expectedAmount <= 0 ||
    !is_numeric($paidAmount) ||
    (int) $paidAmount !== $expectedAmount ||
    !is_string($paidEmail) ||
    strcasecmp($customerEmail, $paidEmail) !== 0) {
    redirect(base_url() . '/views/payment_failed.php');
}

// Create the order only after successful verification.
$orderId = $cartController->processPayment(
    (int) $_SESSION['customer_id'],
    $ip,
    $reference,
    $cartTotal
);

if (!$orderId) {
    error_log('SHOPPN PAYMENT ERROR: Order could not be saved.');
}

redirect(
    base_url() . '/views/payment_success.php?order_id=' . $orderId
);
?>
