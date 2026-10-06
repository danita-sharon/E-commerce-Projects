<?php
require_once '../core/core.php';
require_once '../controllers/ProductController.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $brandId = filter_var($_POST['brand_id'], FILTER_VALIDATE_INT);
  $brandName = trim(strip_tags($_POST['brand_name']));

  if ($brandId === false || $brandId <= 0) {
    $_SESSION['error'] = 'Invalid brand.';
    redirect('/~danita.quarshie/shoppn/views/admin/brand.php');
  }

  if ($brandName === '') {
    $_SESSION['error'] = 'Brand name cannot be empty.';
    redirect('/~danita.quarshie/shoppn/views/admin/brand.php');
  }

  $controller = new ProductController();
  $updated = $controller->updateBrand($brandId, $brandName);

  if ($updated) {
    $_SESSION['success'] = 'Brand updated.';
  } else {
    $_SESSION['error'] = 'Something went wrong. Please try again.';
  }

  redirect('/~danita.quarshie/shoppn/views/admin/brand.php');

} else {
  redirect('/~danita.quarshie/shoppn/views/admin/brand.php');
}