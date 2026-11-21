<?php
// =========================================================
// Log out (only by a form POST, so other sites can't log you out)
// =========================================================
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $cart = $_SESSION['cart'] ?? [];      // keep their bag
    $_SESSION = [];
    session_regenerate_id(true);
    $_SESSION['cart'] = $cart;
    flash('success', "You're logged out. See you soon.");
}
redirect('');
