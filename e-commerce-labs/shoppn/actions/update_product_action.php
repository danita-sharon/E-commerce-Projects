<?php
require_once '../core/core.php';
require_once '../controllers/ProductController.php';

require_admin();

$redirectUrl = base_url() . '/views/admin/product.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  redirect($redirectUrl);
}

// Get and validate product information

$id = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT);
$cat = filter_var($_POST['product_cat'] ?? null, FILTER_VALIDATE_INT);
$brand = filter_var($_POST['product_brand'] ?? null, FILTER_VALIDATE_INT);
$title = trim(strip_tags($_POST['product_title'] ?? ''));
$price = filter_var($_POST['product_price'] ?? null, FILTER_VALIDATE_FLOAT);
$desc = trim(strip_tags($_POST['product_desc'] ?? ''));
$keywords = trim(strip_tags($_POST['product_keywords'] ?? ''));

if (
  !$id || !$cat || !$brand ||
  $title === '' ||
  $price === false || $price < 0 ||
  $desc === '' || $keywords === ''
) {
  $_SESSION['error'] = 'Please enter valid product details.';
  redirect($redirectUrl);
}

$controller = new ProductController();

$existingProduct = $controller->getProductById($id);

if (!$existingProduct) {
  $_SESSION['error'] = 'Product not found.';
  redirect($redirectUrl);
}

if (
  !$controller->getCategoryById($cat) ||
  !$controller->getBrandById($brand)
) {
  $_SESSION['error'] = 'Please select a valid category and brand.';
  redirect($redirectUrl);
}

// Keep the current image unless a new one is uploaded.
// Read the existing filename from the database rather than trusting a hidden field.

$filename = $existingProduct['product_image'];
$newImagePath = null;

if (
  isset($_FILES['product_image']) &&
  $_FILES['product_image']['error'] !== UPLOAD_ERR_NO_FILE
) {
  $image = $_FILES['product_image'];

  if ($image['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['error'] = 'Image upload failed.';
    redirect($redirectUrl);
  }

  $maxSize = 5 * 1024 * 1024; // 5 MB

  if ($image['size'] > $maxSize || $image['size'] <= 0) {
    $_SESSION['error'] = 'Image must be smaller than 5 MB.';
    redirect($redirectUrl);
  }

  $allowed = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/gif' => 'gif',
    'image/webp' => 'webp'
  ];

  $finfo = new finfo(FILEINFO_MIME_TYPE);
  $mimeType = $finfo->file($image['tmp_name']);

  if (!isset($allowed[$mimeType]) || getimagesize($image['tmp_name']) === false) {
    $_SESSION['error'] = 'Only JPG, PNG, GIF and WebP images are allowed.';
    redirect($redirectUrl);
  }

  $uploadDirectory = __DIR__ . '/../images/products/';

  if (!is_dir($uploadDirectory) || !is_writable($uploadDirectory)) {
    $_SESSION['error'] = 'Product image folder is missing or not writable.';
    redirect($redirectUrl);
  }

  $filename = bin2hex(random_bytes(16)) . '.' . $allowed[$mimeType];
  $newImagePath = $uploadDirectory . $filename;

  if (!move_uploaded_file($image['tmp_name'], $newImagePath)) {
    $_SESSION['error'] = 'Unable to save the new image.';
    redirect($redirectUrl);
  }
}

// Update product in database

$updated = $controller->updateProduct(
  $id,
  $cat,
  $brand,
  $title,
  $price,
  $desc,
  $filename,
  $keywords
);

if ($updated) {
  $_SESSION['success'] = 'Product updated successfully.';
} else {
  if ($newImagePath !== null && is_file($newImagePath)) {
    unlink($newImagePath);
  }

  $_SESSION['error'] = 'Unable to update product. Please try again.';
}

redirect($redirectUrl);