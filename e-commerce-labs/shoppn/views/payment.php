
<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CartController.php';

// Customer must be logged in
if (empty($_SESSION['customer_id']) ||
    empty($_SESSION['customer_email'])) {
    redirect(base_url() . '/views/login.php');
}

$cartController = new CartController();
$ip = get_ip();

$cartItems = $cartController->getCartItems($ip);
$cartTotal = $cartController->getCartTotal($ip);

// Prevent payment for an empty cart
if (empty($cartItems) || $cartTotal <= 0) {
    redirect(base_url() . '/views/cart.php');
}

$customerName = $_SESSION['customer_name'] ?? 'Customer';
$customerEmail = $_SESSION['customer_email'];
$amountPesewas = (int) round($cartTotal * 100);

include __DIR__ . '/layout/header.php';
?>

<style>
.payment-page {
    max-width: 900px;
    margin: 40px auto;
    padding: 25px;
    font-family: inherit;
}

.payment-page h1 {
    text-align: center;
    color: var(--rose-dark, #b94f70);
    margin-bottom: 12px;
}

.payment-subtitle {
    text-align: center;
    color: var(--muted, #786e73);
    margin-bottom: 30px;
}

.payment-card {
    background: var(--glass, rgba(255,255,255,0.72));
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    padding: 30px;
    border-radius: var(--radius, 18px);
    border: 1px solid rgba(255,255,255,0.75);
    box-shadow: var(--shadow, 0 12px 35px rgba(88,50,63,0.10));
}

.payment-card h2 {
    color: var(--text, #352d31);
    margin-top: 0;
}

.payment-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    background: rgba(255,255,255,0.72);
    border-radius: 12px;
    overflow: hidden;
}

.payment-table th,
.payment-table td {
    padding: 14px;
    border-bottom: 1px solid #f0dbe3;
    text-align: left;
}

.payment-table th {
    background: var(--rose-light, #f7dce5);
    color: var(--rose-dark, #b94f70);
}

.payment-table td {
    color: var(--text, #352d31);
}

.payment-total {
    text-align: right;
    margin-top: 25px;
    font-size: 22px;
    font-weight: bold;
    color: var(--rose-dark, #b94f70);
}

.paystack-btn {
    display: block;
    width: 100%;
    padding: 15px;
    margin-top: 25px;
    background: linear-gradient(
        135deg,
        var(--rose, #d96b8a),
        var(--rose-dark, #b94f70)
    );
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 17px;
    font-weight: bold;
    cursor: pointer;
    box-shadow: 0 8px 18px rgba(185,79,112,0.20);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.paystack-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 24px rgba(185,79,112,0.28);
}

.payment-back {
    display: block;
    text-align: center;
    margin-top: 20px;
    color: var(--rose-dark, #b94f70);
    text-decoration: none;
    font-weight: 600;
}

.payment-back:hover {
    text-decoration: underline;
}

@media (max-width: 600px) {
    .payment-page {
        padding: 15px;
    }

    .payment-card {
        padding: 18px;
        overflow-x: auto;
    }

    .payment-table {
        min-width: 480px;
    }
}
</style>

<main class="payment-page">

    <h1>Secure Checkout 💗</h1>

    <p class="payment-subtitle">
        Hi <?php echo htmlspecialchars(
            $customerName, ENT_QUOTES, 'UTF-8'
        ); ?>!
        Review your order before making payment.
    </p>

    <div class="payment-card">

        <h2>Order Summary</h2>

        <table class="payment-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Subtotal</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($cartItems as $item): ?>
                    <tr>
                        <td>
                            <?php echo htmlspecialchars(
                                $item['product_title'],
                                ENT_QUOTES,
                                'UTF-8'
                            ); ?>
                        </td>

                        <td>
                            <?php echo (int) $item['qty']; ?>
                        </td>

                        <td>
                            GH₵ <?php echo number_format(
                                (float) $item['product_price'], 2
                            ); ?>
                        </td>

                        <td>
                            GH₵ <?php echo number_format(
                                $item['qty'] * $item['product_price'], 2
                            ); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="payment-total">
            Total: GH₵ <?php echo number_format($cartTotal, 2); ?>
        </div>

        <button
            type="button"
            class="paystack-btn"
            onclick="payWithPaystack()">
            Pay Securely with Paystack
        </button>

        <a href="<?php echo htmlspecialchars(
            base_url() . '/views/cart.php',
            ENT_QUOTES,
            'UTF-8'
        ); ?>" class="payment-back">
            ← Back to Cart
        </a>

    </div>
</main>

<script src="https://js.paystack.co/v1/inline.js"></script>

<script>
const paystackPublicKey = 'pk_test_d3222a445ffc2fdbb910b2f7ed814dcd0e9ae790';

function payWithPaystack() {

    if (!paystackPublicKey.startsWith('pk_test_')) {
        alert('Paystack test key has not been configured yet.');
        return;
    }

    if (typeof PaystackPop === 'undefined') {
        alert('Unable to load Paystack. Please try again.');
        return;
    }

    const handler = PaystackPop.setup({
        key: paystackPublicKey,

        email: <?php echo json_encode($customerEmail); ?>,

        amount: <?php echo $amountPesewas; ?>,

        currency: 'GHS',

        callback: function(response) {
            window.location.href =
                <?php echo json_encode(
                    base_url() . '/actions/process_payment_action.php?ref='
                ); ?> + encodeURIComponent(response.reference);
        },

        onClose: function() {
            alert('Payment cancelled.');
        }
    });

    handler.openIframe();
}
</script>

<?php include __DIR__ . '/layout/footer.php'; ?>
