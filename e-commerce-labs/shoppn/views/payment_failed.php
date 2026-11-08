
<?php
require_once __DIR__ . '/../core/core.php';
include __DIR__ . '/layout/header.php';
?>

<main style="
    max-width: 650px;
    margin: 60px auto;
    padding: 40px;
    background: white;
    border-radius: 15px;
    text-align: center;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
">

    <h1 style="color: #b94f70;">
        Payment Unsuccessful 💔
    </h1>

    <p>
        We couldn't confirm your payment.
    </p>

    <p>
        Please check your payment status before trying again.
        If you were charged, do not pay again until the
        transaction has been confirmed.
    </p>

    <a href="<?php echo base_url(); ?>/views/payment.php"
       style="
           display: inline-block;
           background: #b94f70;
           color: white;
           padding: 12px 25px;
           border-radius: 8px;
           text-decoration: none;
           margin-top: 20px;
       ">
        Return to Checkout
    </a>

</main>

<?php include __DIR__ . '/layout/footer.php'; ?>
