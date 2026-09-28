<?php
require_once '../core/core.php';
require_once '../controllers/CustomerController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $name = trim(strip_tags($_POST['name']));
  $email = trim(strip_tags($_POST['email']));
  $pass = $_POST['pass'];
  $country = trim(strip_tags($_POST['country']));
  $city = trim(strip_tags($_POST['city']));
  $contact = trim(strip_tags($_POST['contact']));

  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = 'Please enter a valid email address.';
    redirect('/~danita.quarshie/shoppn/views/register.php');
  }

  if (strlen($email) > 50) {
    $_SESSION['error'] = 'Email is too long.';
    redirect('/~danita.quarshie/shoppn/views/register.php');
  }

  $controller = new CustomerController();
  $result = $controller->register([
    'name' => $name,
    'email' => $email,
    'pass' => $pass,
    'country' => $country,
    'city' => $city,
    'contact' => $contact
  ]);

  if ($result['success']) {
    $customerClass = new CustomerClass();
    $newCustomer = $customerClass->getCustomerByEmail($email);

    $_SESSION['customer_id'] = $newCustomer['customer_id'];
    $_SESSION['customer_name'] = $newCustomer['customer_name'];
    $_SESSION['customer_email'] = $newCustomer['customer_email'];
    $_SESSION['user_role'] = $newCustomer['user_role'];

    redirect('/~danita.quarshie/shoppn/views/account/my_account.php');
  } else {
    $_SESSION['error'] = $result['error'];
    redirect('/~danita.quarshie/shoppn/views/register.php');
  }

} else {
  redirect('/~danita.quarshie/shoppn/views/register.php');
}
?>