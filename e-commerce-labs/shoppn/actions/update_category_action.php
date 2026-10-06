<?php
require_once '../core/core.php';
require_once '../controllers/ProductController.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $categoryId = filter_var($_POST['cat_id'], FILTER_VALIDATE_INT);
  $categoryName = trim(strip_tags($_POST['cat_name']));

  if ($categoryId === false || $categoryId <= 0) {
    $_SESSION['error'] = 'Invalid category.';
    redirect('/~danita.quarshie/shoppn/views/admin/category.php');
  }

  if ($categoryName === '') {
    $_SESSION['error'] = 'Category name cannot be empty.';
    redirect('/~danita.quarshie/shoppn/views/admin/category.php');
  }

  $controller = new ProductController();
  $updated = $controller->updateCategory($categoryId, $categoryName);

  if ($updated) {
    $_SESSION['success'] = 'Category updated.';
  } else {
    $_SESSION['error'] = 'Something went wrong. Please try again.';
  }

  redirect('/~danita.quarshie/shoppn/views/admin/category.php');

} else {
  redirect('/~danita.quarshie/shoppn/views/admin/category.php');
}