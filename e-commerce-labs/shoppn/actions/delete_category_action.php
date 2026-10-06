<?php
require_once '../core/core.php';
require_once '../controllers/ProductController.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  redirect('/~danita.quarshie/shoppn/views/admin/category.php');
}

$categoryId = filter_var($_POST['cat_id'] ?? null, FILTER_VALIDATE_INT);

if ($categoryId === false || $categoryId <= 0) {
  $_SESSION['error'] = 'Invalid category.';
  redirect('/~danita.quarshie/shoppn/views/admin/category.php');
}

$controller = new ProductController();
$deleted = $controller->deleteCategory($categoryId);

if ($deleted) {
  $_SESSION['success'] = 'Category deleted.';
} else {
  $_SESSION['error'] = 'Something went wrong. Please try again.';
}

redirect('/~danita.quarshie/shoppn/views/admin/category.php');