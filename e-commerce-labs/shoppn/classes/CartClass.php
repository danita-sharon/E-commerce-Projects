
<?php
require_once __DIR__ . '/../core/db_class.php';

class CartClass extends Database {

  // Check whether a product is already in the cart
  public function isInCart($product_id, $ip) {
    $sql = "SELECT p_id FROM cart WHERE p_id = ? AND ip_add = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("is", $product_id, $ip);
    $stmt->execute();

    return $stmt->get_result()->num_rows > 0;
  }

  // Add a product to the cart
  public function addToCart($product_id, $qty, $ip, $customer_id = null) {
    $sql = "INSERT INTO cart (p_id, ip_add, qty) VALUES (?, ?, ?)";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("isi", $product_id, $ip, $qty);

    return $stmt->execute();
  }

  // Get all products in the cart
  public function getCartItems($ip) {
    $sql = "SELECT cart.*,
                   products.product_title,
                   products.product_price,
                   products.product_image
            FROM cart
            JOIN products ON cart.p_id = products.product_id
            WHERE cart.ip_add = ?";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $ip);
    $stmt->execute();

    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  }

  // Count the products in the cart
  public function getCartCount($ip) {
    $sql = "SELECT COUNT(*) AS total FROM cart WHERE ip_add = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $ip);
    $stmt->execute();

    $result = $stmt->get_result()->fetch_assoc();

    return (int) $result['total'];
  }

  // Calculate the total price of the cart
  public function getCartTotal($ip) {
    $items = $this->getCartItems($ip);
    $total = 0.0;

    foreach ($items as $item) {
      $total += $item['qty'] * $item['product_price'];
    }

    return (float) $total;
  }


    // Remove a product from the cart
    public function removeFromCart($product_id, $ip) {
        $sql = "DELETE FROM cart WHERE p_id = ? AND ip_add = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("is", $product_id, $ip);

        return $stmt->execute();
    }

    // Update the quantity of a product in the cart
    public function updateQty($product_id, $new_qty, $ip) {
        if ($new_qty < 1) {
            return false;
        }

        $sql = "UPDATE cart SET qty = ?
                WHERE p_id = ? AND ip_add = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iis", $new_qty, $product_id, $ip);

        return $stmt->execute();
    }



}
?>
