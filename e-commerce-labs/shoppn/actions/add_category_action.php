<?php
require_once '../core/core.php';
require_once '../controllers/ProductController.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $categoryName = trim(strip_tags($_POST['cat_name']));

  if ($categoryName === '') {
    $_SESSION['error'] = 'Category name cannot be empty.';
    redirect('/~danita.quarshie/shoppn/views/admin/category.php');
  }

  $controller = new ProductController();
  $added = $controller->addCategory($categoryName);

  if ($added) {
    $_SESSION['success'] = 'Category added.';
  } else {
    $_SESSION['error'] = 'Something went wrong. Please try again.';
  }

  redirect('/~danita.quarshie/shoppn/views/admin/category.php');

} else {
  redirect('/~danita.quarshie/shoppn/views/admin/category.php');
}