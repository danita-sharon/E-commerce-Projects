<?php
require_once '../core/core.php';
require_once '../controllers/ProductController.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  redirect(base_url() . '/views/admin/brand.php');
}

$brandId = filter_var($_POST['brand_id'] ?? null, FILTER_VALIDATE_INT);

if ($brandId === false || $brandId <= 0) {
  $_SESSION['error'] = 'Invalid brand.';
  redirect(base_url() . '/views/admin/brand.php');
}

$controller = new ProductController();
$deleted = $controller->deleteBrand($brandId);

if ($deleted) {
  $_SESSION['success'] = 'Brand deleted.';
} else {
  $_SESSION['error'] = 'Something went wrong. Please try again.';
}

redirect(base_url() . '/views/admin/brand.php');