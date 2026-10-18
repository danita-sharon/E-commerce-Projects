
<?php
require_once __DIR__ . '/../core/db_class.php';

class ProductClass extends Database {

  // BRAND METHODS

  public function addBrand($name) {
    $sql = "INSERT INTO brands (brand_name) VALUES (?)";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $name);
    return $stmt->execute();
  }

  public function getAllBrands() {
    $sql = "SELECT * FROM brands ORDER BY brand_name ASC";
    $result = $this->conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
  }

  public function getBrandById($id) {
    $sql = "SELECT * FROM brands WHERE brand_id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
  }

  public function updateBrand($id, $name) {
    $sql = "UPDATE brands SET brand_name = ? WHERE brand_id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("si", $name, $id);
    return $stmt->execute();
  }

  public function deleteBrand($id) {
    $sql = "DELETE FROM brands WHERE brand_id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $id);
    return $stmt->execute();
  }


  // CATEGORY METHODS

  public function addCategory($name) {
    $sql = "INSERT INTO categories (cat_name) VALUES (?)";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $name);
    return $stmt->execute();
  }

  public function getAllCategories() {
    $sql = "SELECT * FROM categories ORDER BY cat_name ASC";
    $result = $this->conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
  }

  public function getCategoryById($id) {
    $sql = "SELECT * FROM categories WHERE cat_id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
  }

  public function updateCategory($id, $name) {
    $sql = "UPDATE categories SET cat_name = ? WHERE cat_id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("si", $name, $id);
    return $stmt->execute();
  }

  public function deleteCategory($id) {
    $sql = "DELETE FROM categories WHERE cat_id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $id);
    return $stmt->execute();
  }


  // PRODUCT METHODS

  public function addProduct($cat, $brand, $title, $price, $desc, $image_filename, $keywords) {
    $sql = "INSERT INTO products
            (product_cat, product_brand, product_title, product_price,
             product_desc, product_image, product_keywords)
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param(
      "iisdsss",
      $cat,
      $brand,
      $title,
      $price,
      $desc,
      $image_filename,
      $keywords
    );

    return $stmt->execute();
  }

  public function updateProduct($id, $cat, $brand, $title, $price, $desc, $image_filename, $keywords) {
    $sql = "UPDATE products
            SET product_cat = ?,
                product_brand = ?,
                product_title = ?,
                product_price = ?,
                product_desc = ?,
                product_image = ?,
                product_keywords = ?
            WHERE product_id = ?";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param(
      "iisdsssi",
      $cat,
      $brand,
      $title,
      $price,
      $desc,
      $image_filename,
      $keywords,
      $id
    );

    return $stmt->execute();
  }

  public function getProductById($id) {
    $sql = "SELECT p.*,
                   c.cat_name,
                   b.brand_name
            FROM products p
            LEFT JOIN categories c ON p.product_cat = c.cat_id
            LEFT JOIN brands b ON p.product_brand = b.brand_id
            WHERE p.product_id = ?";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();

    return $stmt->get_result()->fetch_assoc();
  }

  public function getAllProducts() {
    $sql = "SELECT p.*, c.cat_name, b.brand_name
            FROM products p
            LEFT JOIN categories c ON p.product_cat = c.cat_id
            LEFT JOIN brands b ON p.product_brand = b.brand_id
            ORDER BY p.product_id DESC";

    $result = $this->conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
  }


  // FEATURED PRODUCTS

  public function getFeaturedProducts() {
    $sql = "SELECT p.*, c.cat_name, b.brand_name
            FROM products p
            LEFT JOIN categories c ON p.product_cat = c.cat_id
            LEFT JOIN brands b ON p.product_brand = b.brand_id
            ORDER BY p.product_id DESC";

    $result = $this->conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
  }


  // GET PRODUCTS BY CATEGORY

  public function getProductsByCategory($cat_id) {
    $sql = "SELECT p.*, c.cat_name, b.brand_name
            FROM products p
            LEFT JOIN categories c ON p.product_cat = c.cat_id
            LEFT JOIN brands b ON p.product_brand = b.brand_id
            WHERE p.product_cat = ?";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $cat_id);
    $stmt->execute();

    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  }


  // GET PRODUCTS BY BRAND

  public function getProductsByBrand($brand_id) {
    $sql = "SELECT p.*, c.cat_name, b.brand_name
            FROM products p
            LEFT JOIN categories c ON p.product_cat = c.cat_id
            LEFT JOIN brands b ON p.product_brand = b.brand_id
            WHERE p.product_brand = ?";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $brand_id);
    $stmt->execute();

    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  }


  // SEARCH PRODUCTS

  public function searchProducts($query) {
    $sql = "SELECT p.*, c.cat_name, b.brand_name
            FROM products p
            LEFT JOIN categories c ON p.product_cat = c.cat_id
            LEFT JOIN brands b ON p.product_brand = b.brand_id
            WHERE p.product_title LIKE ?
               OR p.product_keywords LIKE ?
            ORDER BY p.product_id DESC";

    $searchTerm = "%" . $query . "%";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
    $stmt->execute();

    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  }


  // DELETE PRODUCT

  public function deleteProduct($id) {
    $sql = "DELETE FROM products WHERE product_id = ?";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if (!$stmt->execute()) {
      return false;
    }

    return $stmt->affected_rows > 0;
  }

}
?>
