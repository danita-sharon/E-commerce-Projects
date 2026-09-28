<?php
require_once __DIR__ . '/../core/db_class.php';

class CustomerClass extends Database {

  public function emailExists($email) {
    $sql = "SELECT customer_email FROM customer WHERE customer_email = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->num_rows > 0;
  }

  public function getCustomerByEmail($email) {
    $sql = "SELECT * FROM customer WHERE customer_email = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_assoc();
  }

  public function login($email, $pass) {
    $customer = $this->getCustomerByEmail($email);

    if ($customer && password_verify($pass, $customer['customer_pass'])) {
      return $customer;
    }

    return false;
  }

  public function addCustomer($name, $email, $pass, $country, $city, $contact) {
    $hashedPass = password_hash($pass, PASSWORD_BCRYPT);

    $sql = "INSERT INTO customer (customer_name, customer_email, customer_pass, customer_country, customer_city, customer_contact, user_role)
            VALUES (?, ?, ?, ?, ?, ?, 2)";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("ssssss", $name, $email, $hashedPass, $country, $city, $contact);

    return $stmt->execute();
  }

}
?>