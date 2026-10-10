<?php
require_once '../../core/core.php';
require_once '../../controllers/ProductController.php';

require_admin();

$controller = new ProductController();

$categories = $controller->getAllCategories();
$brands = $controller->getAllBrands();
$products = $controller->getAllProducts();

$baseUrl = '/~danita.quarshie/shoppn';

$editProduct = null;
$editId = filter_var($_GET['edit_id'] ?? null, FILTER_VALIDATE_INT);

if ($editId) {
  $editProduct = $controller->getProductById($editId);

  if (!$editProduct) {
    $_SESSION['error'] = 'Product not found.';
    redirect($baseUrl . '/views/admin/product.php');
  }
}

$isEditing = $editProduct !== null;

$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';

unset($_SESSION['success'], $_SESSION['error']);

function productEscape($value) {
  return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

include '../layout/header.php';
?>

<main class="product-management" style="max-width: 850px; margin: 35px auto; padding: 20px;">

  <h1>
    <?php echo $isEditing ? 'Edit Product' : 'Add New Product'; ?>
  </h1>

  <p>
    <?php if ($isEditing): ?>
      Update the product information below.
    <?php else: ?>
      Fill in the form below to add a product to Shoppn.
    <?php endif; ?>
  </p>

  <?php if ($success): ?>
    <div style="padding: 12px; background: #d4edda; color: #155724; margin: 15px 0;">
      <?php echo productEscape($success); ?>
    </div>
  <?php endif; ?>

  <?php if ($error): ?>
    <div style="padding: 12px; background: #f8d7da; color: #721c24; margin: 15px 0;">
      <?php echo productEscape($error); ?>
    </div>
  <?php endif; ?>

  <form
    id="productForm"
    action="<?php echo $baseUrl; ?>/actions/<?php echo $isEditing ? 'update_product_action.php' : 'add_product_action.php'; ?>"
    method="POST"
    enctype="multipart/form-data"
    style="display: flex; flex-direction: column; gap: 15px; margin-top: 25px;"
  >

    <?php if ($isEditing): ?>
      <input
        type="hidden"
        name="product_id"
        value="<?php echo (int)$editProduct['product_id']; ?>"
      >

      <input
        type="hidden"
        name="existing_image"
        value="<?php echo productEscape($editProduct['product_image']); ?>"
      >
    <?php endif; ?>

    <label for="product_title">Product Title</label>
    <input
      type="text"
      id="product_title"
      name="product_title"
      required
      value="<?php echo productEscape($editProduct['product_title'] ?? ''); ?>"
      style="padding: 10px;"
    >

    <label for="product_price">Product Price (GH₵)</label>
    <input
      type="number"
      id="product_price"
      name="product_price"
      min="0"
      step="0.01"
      required
      value="<?php echo productEscape($editProduct['product_price'] ?? ''); ?>"
      style="padding: 10px;"
    >

    <label for="product_cat">Category</label>
    <select
      id="product_cat"
      name="product_cat"
      required
      style="padding: 10px;"
    >
      <option value="">Select Category</option>

      <?php foreach ($categories as $category): ?>
        <option
          value="<?php echo (int)$category['cat_id']; ?>"
          <?php
          if (
            $isEditing &&
            (int)$editProduct['product_cat'] === (int)$category['cat_id']
          ) {
            echo 'selected';
          }
          ?>
        >
          <?php echo productEscape($category['cat_name']); ?>
        </option>
      <?php endforeach; ?>

    </select>

    <label for="product_brand">Brand</label>
    <select
      id="product_brand"
      name="product_brand"
      required
      style="padding: 10px;"
    >
      <option value="">Select Brand</option>

      <?php foreach ($brands as $brand): ?>
        <option
          value="<?php echo (int)$brand['brand_id']; ?>"
          <?php
          if (
            $isEditing &&
            (int)$editProduct['product_brand'] === (int)$brand['brand_id']
          ) {
            echo 'selected';
          }
          ?>
        >
          <?php echo productEscape($brand['brand_name']); ?>
        </option>
      <?php endforeach; ?>

    </select>

    <label for="product_desc">Product Description</label>
    <textarea
      id="product_desc"
      name="product_desc"
      rows="5"
      required
      style="padding: 10px;"
    ><?php echo productEscape($editProduct['product_desc'] ?? ''); ?></textarea>

    <label for="product_keywords">Product Keywords</label>
    <input
      type="text"
      id="product_keywords"
      name="product_keywords"
      required
      placeholder="Example: shoes, sneakers, fashion"
      value="<?php echo productEscape($editProduct['product_keywords'] ?? ''); ?>"
      style="padding: 10px;"
    >

    <label for="product_image">
      Product Image
      <?php echo $isEditing ? '(Optional: choose a new image)' : '(Required)'; ?>
    </label>

    <?php if ($isEditing && !empty($editProduct['product_image'])): ?>
      <div>
        <p>Current image:</p>
        <img
          src="<?php echo $baseUrl; ?>/images/products/<?php echo rawurlencode($editProduct['product_image']); ?>"
          alt="Current product image"
          style="max-width: 180px; max-height: 180px; object-fit: contain;"
        >
      </div>
    <?php endif; ?>

    <input
      type="file"
      id="product_image"
      name="product_image"
      accept="image/jpeg,image/png,image/gif,image/webp"
      <?php echo $isEditing ? '' : 'required'; ?>
    >

    <small>Allowed formats: JPG, PNG, GIF and WebP. Maximum size: 5 MB.</small>

    <div id="imagePreviewContainer" style="display: none;">
      <p>Selected image preview:</p>
      <img
        id="imagePreview"
        alt="Selected product image preview"
        style="max-width: 200px; max-height: 200px; object-fit: contain;"
      >
    </div>

    <p id="imageError" style="color: #b00020;"></p>

    <button
      type="submit"
      style="padding: 12px; background: #222; color: white; border: none; cursor: pointer;"
    >
      <?php echo $isEditing ? 'Update Product' : 'Add Product'; ?>
    </button>

  </form>

  <?php if ($isEditing): ?>
    <p style="margin-top: 20px;">
      <a href="<?php echo $baseUrl; ?>/views/admin/product.php">
        ← Cancel Editing / Add New Product
      </a>
    </p>
  <?php endif; ?>
<section style="margin-top: 45px;">

  <h2>Existing Products</h2>

  <?php if (empty($products)): ?>

    <p>No products have been added yet.</p>

  <?php else: ?>

    <div style="overflow-x: auto;">

      <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">

        <thead>
          <tr style="background: #f2f2f2;">
            <th style="padding: 12px; text-align: left;">Image</th>
            <th style="padding: 12px; text-align: left;">Product</th>
            <th style="padding: 12px; text-align: left;">Category</th>
            <th style="padding: 12px; text-align: left;">Brand</th>
            <th style="padding: 12px; text-align: left;">Price</th>
            <th style="padding: 12px; text-align: left;">Action</th>
          </tr>
        </thead>

        <tbody>

          <?php foreach ($products as $product): ?>

            <tr style="border-bottom: 1px solid #ddd;">

              <td style="padding: 12px;">
                <img
                  src="<?php echo $baseUrl; ?>/images/products/<?php echo rawurlencode($product['product_image']); ?>"
                  alt="<?php echo productEscape($product['product_title']); ?>"
                  style="width: 65px; height: 65px; object-fit: cover;"
                >
              </td>

              <td style="padding: 12px;">
                <?php echo productEscape($product['product_title']); ?>
              </td>

              <td style="padding: 12px;">
                <?php echo productEscape($product['cat_name'] ?? 'N/A'); ?>
              </td>

              <td style="padding: 12px;">
                <?php echo productEscape($product['brand_name'] ?? 'N/A'); ?>
              </td>

              <td style="padding: 12px;">
                GH₵ <?php echo number_format((float)$product['product_price'], 2); ?>
              </td>

              <td style="padding: 12px;">
                <a
                  href="<?php echo $baseUrl; ?>/views/admin/product.php?edit_id=<?php echo (int)$product['product_id']; ?>"
                  style="color: #0066cc; font-weight: bold;"
                >
                  Edit
                </a>
              </td>

            </tr>

          <?php endforeach; ?>

        </tbody>

      </table>

    </div>

  <?php endif; ?>

</section>
</main>

<script>
const productForm = document.getElementById('productForm');
const imageInput = document.getElementById('product_image');
const imagePreview = document.getElementById('imagePreview');
const previewContainer = document.getElementById('imagePreviewContainer');
const imageError = document.getElementById('imageError');

const allowedTypes = [
  'image/jpeg',
  'image/png',
  'image/gif',
  'image/webp'
];

const maxImageSize = 5 * 1024 * 1024;

let previewUrl = null;

function validateImage() {
  imageError.textContent = '';

  if (previewUrl !== null) {
    URL.revokeObjectURL(previewUrl);
    previewUrl = null;
  }

  previewContainer.style.display = 'none';

  const file = imageInput.files[0];

  if (!file) {
    return true;
  }

  if (!allowedTypes.includes(file.type)) {
    imageError.textContent = 'Please select a JPG, PNG, GIF or WebP image.';
    imageInput.value = '';
    return false;
  }

  if (file.size > maxImageSize || file.size === 0) {
    imageError.textContent = 'The image must be smaller than 5 MB.';
    imageInput.value = '';
    return false;
  }

  previewUrl = URL.createObjectURL(file);
  imagePreview.src = previewUrl;
  previewContainer.style.display = 'block';

  return true;
}

imageInput.addEventListener('change', validateImage);

productForm.addEventListener('submit', function(event) {
  if (!validateImage()) {
    event.preventDefault();
  }
});
</script>

<?php include '../layout/footer.php'; ?>