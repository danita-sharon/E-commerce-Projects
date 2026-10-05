<?php
require_once '../core/core.php';
require_once '../controllers/ProductController.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $brandName = trim(strip_tags($_POST['brand_name']));

  if ($brandName === '') {
    $_SESSION['error'] = 'Brand name cannot be empty.';
    redirect('/shoppn/views/admin/brand.php');
  }

  $controller = new ProductController();
  $added = $controller->addBrand($brandName);

  if ($added) {
    $_SESSION['success'] = 'Brand added.';
  } else {
    $_SESSION['error'] = 'Something went wrong. Please try again.';
  }

  redirect('/shoppn/views/admin/brand.php');

} else {
  redirect('/shoppn/views/admin/brand.php');
}
