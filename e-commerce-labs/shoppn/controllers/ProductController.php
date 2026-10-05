<?php
require_once __DIR__ . '/../classes/ProductClass.php';

class ProductController {
  private $productClass;

  public function __construct() {
    $this->productClass = new ProductClass();
  }

  public function addBrand($name) {
    return $this->productClass->addBrand($name);
  }

  public function getAllBrands() {
    return $this->productClass->getAllBrands();
  }

  public function getBrandById($id) {
    return $this->productClass->getBrandById($id);
  }

  public function updateBrand($id, $name) {
    return $this->productClass->updateBrand($id, $name);
  }

  public function deleteBrand($id) {
  return $this->productClass->deleteBrand($id);
}

}
