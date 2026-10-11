<?php
require_once __DIR__ . '/../classes/ProductClass.php';

class ProductController {
  private $productClass;

  public function __construct() {
    $this->productClass = new ProductClass();
  }

  // BRAND METHODS

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


  // CATEGORY METHODS

  public function addCategory($name) {
    return $this->productClass->addCategory($name);
  }

  public function getAllCategories() {
    return $this->productClass->getAllCategories();
  }

  public function getCategoryById($id) {
    return $this->productClass->getCategoryById($id);
  }

  public function updateCategory($id, $name) {
    return $this->productClass->updateCategory($id, $name);
  }

  public function deleteCategory($id) {
    return $this->productClass->deleteCategory($id);
  }


  // PRODUCT METHODS

  public function addProduct($cat, $brand, $title, $price, $desc, $image_filename, $keywords) {
    return $this->productClass->addProduct(
      $cat,
      $brand,
      $title,
      $price,
      $desc,
      $image_filename,
      $keywords
    );
  }

  public function updateProduct($id, $cat, $brand, $title, $price, $desc, $image_filename, $keywords) {
    return $this->productClass->updateProduct(
      $id,
      $cat,
      $brand,
      $title,
      $price,
      $desc,
      $image_filename,
      $keywords
    );
  }

  public function getProductById($id) {
    return $this->productClass->getProductById($id);
  }

  public function getAllProducts() {
    return $this->productClass->getAllProducts();
  }

    // FEATURED PRODUCTS

  public function getFeaturedProducts($limit = 6) {
    return $this->productClass->getFeaturedProducts($limit);
  }


  // GET PRODUCTS BY CATEGORY

  public function getProductsByCategory($cat_id) {
    return $this->productClass->getProductsByCategory($cat_id);
  }


  // GET PRODUCTS BY BRAND

  public function getProductsByBrand($brand_id) {
    return $this->productClass->getProductsByBrand($brand_id);
  }
  
  // SEARCH PRODUCTS

  public function searchProducts($query) {
    return $this->productClass->searchProducts($query);
  }


  // DELETE PRODUCT

  public function deleteProduct($id) {
    return $this->productClass->deleteProduct($id);
  }


}
?>
