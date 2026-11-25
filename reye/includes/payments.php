<?php
// =========================================================
// Paystack helpers
// The secret key is only ever used here, on the server.
// =========================================================

// Call the Paystack API and return the decoded JSON (or null on failure)
function paystack_request(string $method, string $path, ?array $body = null): ?array
{
    $ch = curl_init('https://api.paystack.co' . $path);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . PAYSTACK_SECRET_KEY,
            'Content-Type: application/json',
        ],
    ]);
    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }
    $response = curl_exec($ch);
    if ($response === false) {
        error_log('Paystack request failed: ' . curl_error($ch));
        curl_close($ch);
        return null;
    }
    curl_close($ch);
    return json_decode($response, true);
}

// True when real-looking keys have been added to config.php
function paystack_ready(): bool
{
    return strpos(PAYSTACK_PUBLIC_KEY, 'xxxx') === false && strpos(PAYSTACK_SECRET_KEY, 'xxxx') === false;
}

// Amount in pesewas (Paystack works in the smallest unit: GH₵ 1 = 100 pesewas)
function to_pesewas($amount): int
{
    return (int) round(((float) $amount) * 100);
}

// Mark an order as paid. Safe to call twice (from verify.php AND the webhook):
// only the first call changes anything.
function fulfil_order(PDO $pdo, string $reference, array $tx): bool
{
    $stmt = $pdo->prepare('SELECT p.*, o.total, o.id AS oid FROM payments p
                           JOIN orders o ON o.id = p.order_id WHERE p.reference = ?');
    $stmt->execute([$reference]);
    $payment = $stmt->fetch();
    if (!$payment) {
        return false;
    }

    // Check Paystack's record matches what we expected to be paid
    $paidOk = ($tx['status'] ?? '') === 'success'
        && (int) ($tx['amount'] ?? 0) === to_pesewas($payment['total'])
        && strtoupper($tx['currency'] ?? '') === 'GHS';

    if (!$paidOk) {
        $pdo->prepare("UPDATE payments SET status = ?, gateway_response = ? WHERE reference = ?")
            ->execute(['failed', substr((string) ($tx['gateway_response'] ?? 'Amount or status mismatch'), 0, 250), $reference]);
        return false;
    }

    $now = date('Y-m-d H:i:s');
    $pdo->beginTransaction();
    try {
        // Only one request can flip the order from unpaid to paid
        $flip = $pdo->prepare("UPDATE orders SET payment_status = 'paid', status = 'processing', paid_at = ?
                               WHERE id = ? AND payment_status <> 'paid'");
        $flip->execute([$now, $payment['oid']]);

        if ($flip->rowCount() === 1) {
            // Take the items out of stock
            $items = $pdo->prepare('SELECT product_id, quantity FROM order_items WHERE order_id = ?');
            $items->execute([$payment['oid']]);
            $dec = $pdo->prepare('UPDATE products SET stock = CASE WHEN stock >= ? THEN stock - ? ELSE 0 END WHERE id = ?');
            foreach ($items->fetchAll() as $it) {
                if ($it['product_id']) {
                    $dec->execute([$it['quantity'], $it['quantity'], $it['product_id']]);
                }
            }
        }

        $pdo->prepare("UPDATE payments SET status = 'success', channel = ?, gateway_response = ?, paid_at = ? WHERE reference = ?")
            ->execute([
                substr((string) ($tx['channel'] ?? ''), 0, 30),
                substr((string) ($tx['gateway_response'] ?? 'Approved'), 0, 250),
                $now,
                $reference,
            ]);
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        error_log('Order fulfilment failed: ' . $e->getMessage());
        return false;
    }
    return true;
}

// Delivery fee and discount, worked out on the server only
function order_totals(PDO $pdo, float $subtotal, int $regionId, string $promoCode): array
{
    $stmt = $pdo->prepare('SELECT * FROM delivery_regions WHERE id = ?');
    $stmt->execute([$regionId]);
    $region = $stmt->fetch();

    $fee = 0.0;
    if ($region) {
        $fee = ($region['free_over'] !== null && $subtotal >= (float) $region['free_over']) ? 0.0 : (float) $region['fee'];
    }

    $promo = find_promo($pdo, $promoCode);
    $discount = $promo ? round($subtotal * (int) $promo['percent_off'] / 100, 2) : 0.0;

    return [
        'region'   => $region,
        'fee'      => $fee,
        'promo'    => $promo,
        'discount' => $discount,
        'total'    => round($subtotal - $discount + $fee, 2),
    ];
}

function find_promo(PDO $pdo, string $code): ?array
{
    $code = strtoupper(trim($code));
    if ($code === '') {
        return null;
    }
    $stmt = $pdo->prepare('SELECT * FROM promo_codes WHERE code = ? AND is_active = 1');
    $stmt->execute([$code]);
    $promo = $stmt->fetch();
    if (!$promo || ($promo['expires_at'] && $promo['expires_at'] < date('Y-m-d'))) {
        return null;
    }
    return $promo;
}
