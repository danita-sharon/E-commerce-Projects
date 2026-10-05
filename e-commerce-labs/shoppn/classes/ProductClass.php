<?php
require_once __DIR__ . '/../core/db_class.php';

class ProductClass extends Database {

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

}
?>