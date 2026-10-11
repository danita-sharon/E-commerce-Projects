
<?php
session_start();
date_default_timezone_set('Africa/Accra');

require_once 'db_class.php';

// Automatically detect localhost or school server.
function base_url() {
    if (strpos($_SERVER['SCRIPT_NAME'], '/~danita.quarshie/shoppn/') !== false) {
        return '/~danita.quarshie/shoppn';
    }

    return '/shoppn';
}

// Redirect to another page.
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

// Check whether the customer is logged in.
function is_logged_in() {
    return isset($_SESSION['customer_id']);
}

// Check whether the customer is an admin.
function is_admin() {
    return isset($_SESSION['user_role']) && (int)$_SESSION['user_role'] === 1;
}

// Require login before accessing a page.
function require_login() {
    if (!is_logged_in()) {
        redirect(base_url() . '/views/login.php');
    }
}

// Require admin permissions.
function require_admin() {
    if (!is_admin()) {
        redirect(base_url() . '/index.php');
    }
}
?>
