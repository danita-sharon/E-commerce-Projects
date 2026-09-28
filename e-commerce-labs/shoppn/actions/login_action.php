<?php
require_once '../core/core.php';
require_once '../controllers/CustomerController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $email = trim(strip_tags($_POST['email']));
  $pass = $_POST['pass'];

  $controller = new CustomerController();
  $result = $controller->login($email, $pass);

  if ($result['success']) {
    $customer = $result['customer'];

    $_SESSION['customer_id'] = $customer['customer_id'];
    $_SESSION['customer_name'] = $customer['customer_name'];
    $_SESSION['customer_email'] = $customer['customer_email'];
    $_SESSION['user_role'] = $customer['user_role'];

    redirect('/~danita.quarshie/shoppn/index.php');
  } else {
    $_SESSION['error'] = $result['error'];
    redirect('/~danita.quarshie/shoppn/views/login.php');
  }

} else {
  redirect('/~danita.quarshie/shoppn/views/login.php');
}
?>