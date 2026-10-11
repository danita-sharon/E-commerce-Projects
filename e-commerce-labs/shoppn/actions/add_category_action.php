
<?php
require_once '../core/core.php';
require_once '../controllers/ProductController.php';

require_admin();

// Detect localhost or school server automatically.
$base_url = '/shoppn';

if (strpos($_SERVER['SCRIPT_NAME'], '/~danita.quarshie/shoppn/') !== false) {
    $base_url = '/~danita.quarshie/shoppn';
}

$categoryPage = $base_url . '/views/admin/category.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $categoryName = trim(strip_tags($_POST['cat_name'] ?? ''));

    if ($categoryName === '') {
        $_SESSION['error'] = 'Category name cannot be empty.';
        redirect($categoryPage);
    }

    $controller = new ProductController();
    $added = $controller->addCategory($categoryName);

    if ($added) {
        $_SESSION['success'] = 'Category added.';
    } else {
        $_SESSION['error'] = 'Something went wrong. Please try again.';
    }

    redirect($categoryPage);

} else {
    redirect($categoryPage);
}
?>
