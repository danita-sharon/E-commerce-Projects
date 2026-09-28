<?php
require_once __DIR__ . '/../classes/CustomerClass.php';

class CustomerController {
  private $customerClass;

  public function __construct() {
    $this->customerClass = new CustomerClass();
  }

  public function register($data) {
    if ($this->customerClass->emailExists($data['email'])) {
      return ['success' => false, 'error' => 'Email already registered'];
    }

    $added = $this->customerClass->addCustomer(
      $data['name'],
      $data['email'],
      $data['pass'],
      $data['country'],
      $data['city'],
      $data['contact']
    );

    if ($added) {
      return ['success' => true];
    } else {
      return ['success' => false, 'error' => 'Something went wrong. Please try again.'];
    }
  }

  public function login($email, $pass) {
    $customer = $this->customerClass->login($email, $pass);

    if ($customer) {
      return ['success' => true, 'customer' => $customer];
    } else {
      return ['success' => false, 'error' => 'Invalid email or password'];
    }
  }

}
?>