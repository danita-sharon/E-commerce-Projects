<?php
// =========================================================
// Loaded at the top of every admin page.
// Blocks anyone who is not logged in as an admin.
// =========================================================
require_once __DIR__ . '/../includes/functions.php';
require_admin();

// Number of new customer requests (shown as a badge in the menu)
$new_requests = (int) $pdo->query("SELECT COUNT(*) FROM customer_requests WHERE status = 'new'")->fetchColumn();

// Turn a Ghana phone number into WhatsApp format: 0241234567 -> 233241234567
function wa_number(string $phone): string
{
    $digits = preg_replace('/\D/', '', $phone);
    if (strlen($digits) === 10 && $digits[0] === '0') {
        return '233' . substr($digits, 1);
    }
    return $digits;
}

// Badge colours for order and request statuses
function status_badge(string $status): string
{
    return '<span class="status status-' . e($status) . '">' . e(ucfirst($status)) . '</span>';
}
