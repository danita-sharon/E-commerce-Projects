<?php
require_once '../core/core.php';
require_once '../controllers/ProductController.php';

require_admin();

$redirectUrl = base_url() . '/views/admin/product.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  redirect($redirectUrl);
}

$cat = filter_var($_POST['product_cat'] ?? null, FILTER_VALIDATE_INT);
$brand = filter_var($_POST['product_brand'] ?? null, FILTER_VALIDATE_INT);
$title = trim(strip_tags($_POST['product_title'] ?? ''));
$price = filter_var($_POST['product_price'] ?? null, FILTER_VALIDATE_FLOAT);
$desc = trim(strip_tags($_POST['product_desc'] ?? ''));
$keywords = trim(strip_tags($_POST['product_keywords'] ?? ''));

if (
  !$cat || !$brand ||
  $title === '' ||
  $price === false || $price < 0 ||
  $desc === '' || $keywords === ''
) {
  $_SESSION['error'] = 'Please enter valid product details.';
  redirect($redirectUrl);
}

$controller = new ProductController();

if (
  !$controller->getCategoryById($cat) ||
  !$controller->getBrandById($brand)
) {
  $_SESSION['error'] = 'Please select a valid category and brand.';
  redirect($redirectUrl);
}

// Validate uploaded image

if (
  !isset($_FILES['product_image']) ||
  $_FILES['product_image']['error'] !== UPLOAD_ERR_OK
) {
  $_SESSION['error'] = 'Please upload a product image.';
  redirect($redirectUrl);
}

$image = $_FILES['product_image'];

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

// Check actual file type, not just the browser-provided type

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($image['tmp_name']);

if (!isset($allowed[$mimeType]) || getimagesize($image['tmp_name']) === false) {
  $_SESSION['error'] = 'Only JPG, PNG, GIF and WebP images are allowed.';
  redirect($redirectUrl);
}

// Generate a unique filename

$filename = bin2hex(random_bytes(16)) . '.' . $allowed[$mimeType];

$uploadDirectory = __DIR__ . '/../images/products/';

if (!is_dir($uploadDirectory) || !is_writable($uploadDirectory)) {
  $_SESSION['error'] = 'Product image folder is missing or not writable.';
  redirect($redirectUrl);
}

$destination = $uploadDirectory . $filename;

if (!move_uploaded_file($image['tmp_name'], $destination)) {
  $_SESSION['error'] = 'Unable to save the product image.';
  redirect($redirectUrl);
}

// Save product to database

$added = $controller->addProduct(
  $cat,
  $brand,
  $title,
  $price,
  $desc,
  $filename,
  $keywords
);

if ($added) {
  $_SESSION['success'] = 'Product added successfully.';
} else {
  // Remove the uploaded image if the database insert fails
  unlink($destination);
  $_SESSION['error'] = 'Unable to add product. Please try again.';
}

redirect($redirectUrl);