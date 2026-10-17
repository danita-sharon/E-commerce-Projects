
<?php
require_once __DIR__ . '/../classes/CartClass.php';

class CartController {

    private $cart;

    public function __construct() {
        $this->cart = new CartClass();
    }

    // Check if a product is already in the cart
    public function isInCart($product_id, $ip) {
        return $this->cart->isInCart($product_id, $ip);
    }

    // Add a product to the cart
    public function addToCart($product_id, $qty, $ip, $customer_id = null) {
        return $this->cart->addToCart($product_id, $qty, $ip, $customer_id);
    }

    // Get all cart items
    public function getCartItems($ip) {
        return $this->cart->getCartItems($ip);
    }

    // Get the number of items in the cart
    public function getCartCount($ip) {
        return $this->cart->getCartCount($ip);
    }

    // Calculate the total cart price
    public function getCartTotal($ip) {
        return $this->cart->getCartTotal($ip);
    }

    // Remove a product from the cart
    public function removeFromCart($product_id, $ip) {
        return $this->cart->removeFromCart($product_id, $ip);
    }

    // Update product quantity
    public function updateQty($product_id, $new_qty, $ip) {
        return $this->cart->updateQty($product_id, $new_qty, $ip);
    }

}
?>
