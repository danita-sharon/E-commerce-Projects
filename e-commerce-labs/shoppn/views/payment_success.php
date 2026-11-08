
<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CartController.php';

require_login();

$orderId = filter_input(INPUT_GET, 'order_id', FILTER_VALIDATE_INT);

if (!$orderId) {
    redirect(base_url() . '/index.php');
}

$cartController = new CartController();
$orderItems = $cartController->getOrderById($orderId);

// Only show orders belonging to the logged-in customer.
if (empty($orderItems) ||
    (int) $orderItems[0]['customer_id'] !== (int) $_SESSION['customer_id'] ||
    $orderItems[0]['order_status'] !== 'paid') {
    http_response_code(403);
    exit('Order not found or access denied.');
}

$total = 0;
foreach ($orderItems as $item) {
    $total += $item['product_price'] * $item['qty'];
}

include __DIR__ . '/layout/header.php';
?>

<main style="max-width:750px;margin:40px auto;padding:30px;
background:#fff;border-radius:15px;text-align:center;">

    <h1 style="color:#b94f70;">Payment Successful! 💗</h1>

    <p>Thank you for shopping with Shoppn!</p>
    <p>Your payment has been verified successfully.</p>

    <h2>Invoice #<?php echo htmlspecialchars($orderItems[0]['invoice_no']); ?></h2>

    <table style="width:100%;margin-top:25px;border-collapse:collapse;">
        <tr>
            <th>Product</th>
            <th>Quantity</th>
            <th>Subtotal</th>
        </tr>

        <?php foreach ($orderItems as $item): ?>
        <tr>
            <td><?php echo htmlspecialchars($item['product_title']); ?></td>
            <td><?php echo (int) $item['qty']; ?></td>
            <td>
                GH₵ <?php echo number_format(
                    $item['product_price'] * $item['qty'], 2
                ); ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <h2 style="color:#b94f70;margin-top:25px;">
        Total Paid: GH₵ <?php echo number_format($total, 2); ?>
    </h2>

    <p>Payment Reference:
        <?php echo htmlspecialchars($orderItems[0]['payment_reference']); ?>
    </p>

    <a href="<?php echo base_url(); ?>/index.php"
       style="display:inline-block;background:#b94f70;color:white;
       padding:12px 25px;border-radius:8px;text-decoration:none;">
        Continue Shopping
    </a>
</main>

<?php include __DIR__ . '/layout/footer.php'; ?>
