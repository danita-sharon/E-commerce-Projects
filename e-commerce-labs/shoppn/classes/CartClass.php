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

    // Save a verified payment as an order
    public function processPayment($customer_id, $ip, $reference, $amount) {
        $this->conn->begin_transaction();

        try {
            $items = $this->getCartItems($ip);

            if (empty($items)) {
                throw new Exception("Your cart is empty.");
            }

            $total = $this->getCartTotal($ip);

            if (round($total * 100) !== round($amount * 100)) {
                throw new Exception("Payment amount does not match cart total.");
            }

            $invoice_no = mt_rand(100000, 999999);

            $sql = "INSERT INTO orders
                    (customer_id, invoice_no, order_date,
                     order_status, created_at, payment_reference)
                    VALUES (?, ?, CURDATE(), 'paid', NOW(), ?)";

            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("iis", $customer_id, $invoice_no, $reference);

            if (!$stmt->execute()) {
                throw new Exception("Could not create order.");
            }

            $order_id = $this->conn->insert_id;
            $stmt->close();

            $sql = "INSERT INTO orderdetails
                    (order_id, product_id, qty)
                    VALUES (?, ?, ?)";

            $stmt = $this->conn->prepare($sql);

            foreach ($items as $item) {
                $product_id = (int) $item['p_id'];
                $qty = (int) $item['qty'];

                $stmt->bind_param("iii", $order_id, $product_id, $qty);

                if (!$stmt->execute()) {
                    throw new Exception("Could not save order products.");
                }
            }

            $stmt->close();

            $sql = "DELETE FROM cart WHERE ip_add = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("s", $ip);

            if (!$stmt->execute()) {
                throw new Exception("Could not clear cart.");
            }

            $stmt->close();

            $this->conn->commit();
            return $order_id;

        } catch (Throwable $e) {
            $this->conn->rollback();
            error_log("Payment processing error: " . $e->getMessage());
            return false;
        }
    }

    // Retrieve order details for the invoice
    public function getOrderById($order_id) {
        $sql = "SELECT
                    o.order_id,
                    o.customer_id,
                    o.invoice_no,
                    o.order_date,
                    o.order_status,
                    o.payment_reference,
                    od.product_id,
                    od.qty,
                    p.product_title,
                    p.product_price
                FROM orders o
                JOIN orderdetails od
                    ON o.order_id = od.order_id
                JOIN products p
                    ON od.product_id = p.product_id
                WHERE o.order_id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();

        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $result;
    }

}
?>
