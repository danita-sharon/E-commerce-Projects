<?php
require_once '../../core/core.php';
require_admin();
require_once '../../controllers/ProductController.php';

include '../layout/header.php';

$controller = new ProductController();

$editingBrand = null;

if (isset($_GET['edit_id'])) {
  $editId = filter_var($_GET['edit_id'], FILTER_VALIDATE_INT);

  if ($editId) {
    $editingBrand = $controller->getBrandById($editId);
  }
}

$brands = $controller->getAllBrands();
?>

<main class="brand-page">

  <div class="brand-header">
    <h1>Manage Brands</h1>
    <p>Add, edit, and manage your product brands.</p>
  </div>

  <?php if (isset($_SESSION['success'])): ?>
    <div class="message success">
      <?php
      echo htmlspecialchars($_SESSION['success']);
      unset($_SESSION['success']);
      ?>
    </div>
  <?php endif; ?>

  <?php if (isset($_SESSION['error'])): ?>
    <div class="message error">
      <?php
      echo htmlspecialchars($_SESSION['error']);
      unset($_SESSION['error']);
      ?>
    </div>
  <?php endif; ?>


  <!-- Add / Edit Brand Card -->
  <section class="brand-form-card">

    <h2>
      <?php echo $editingBrand ? 'Edit Brand' : 'Add New Brand'; ?>
    </h2>

    <p class="card-description">
      <?php echo $editingBrand
        ? 'Update the name of this brand.'
        : 'Create a new brand for your products.'; ?>
    </p>

    <?php if ($editingBrand): ?>

      <form action="../../actions/update_brand_action.php" method="POST">

        <input
          type="hidden"
          name="brand_id"
          value="<?php echo (int)$editingBrand['brand_id']; ?>"
        >

        <label for="brand_name">Brand Name</label>

        <input
          type="text"
          id="brand_name"
          name="brand_name"
          value="<?php echo htmlspecialchars($editingBrand['brand_name']); ?>"
          required
        >

        <div class="form-buttons">
          <button type="submit">Update Brand</button>

          <a href="brand.php" class="cancel-button">
            Cancel
          </a>
        </div>

      </form>

    <?php else: ?>

      <form action="../../actions/add_brand_action.php" method="POST">

        <label for="brand_name">Brand Name</label>

        <input
          type="text"
          id="brand_name"
          name="brand_name"
          placeholder="Enter brand name"
          required
        >

        <button type="submit">
          Add Brand
        </button>

      </form>

    <?php endif; ?>

  </section>


  <!-- All Brands -->
  <section class="all-brands">

    <div class="section-heading">
      <h2>All Brands</h2>
      <span><?php echo count($brands); ?> brands</span>
    </div>

    <?php if (empty($brands)): ?>

      <div class="empty-brands">
        <h3>No brands yet</h3>
        <p>Add your first brand using the form above.</p>
      </div>

    <?php else: ?>

      <div class="brand-grid">

        <?php foreach ($brands as $brand): ?>

          <div class="brand-card">

            <div class="brand-card-icon">
              🏷️
            </div>

            <div class="brand-card-content">

              <h3>
                <?php echo htmlspecialchars($brand['brand_name']); ?>
              </h3>

            </div>

            <div class="brand-card-actions">

              <a
                href="brand.php?edit_id=<?php echo (int)$brand['brand_id']; ?>"
                class="edit-button"
              >
                Edit
              </a>

              <form
                action="../../actions/delete_brand_action.php"
                method="POST"
                onsubmit="return confirm('Are you sure you want to delete this brand?');"
              >

                <input
                  type="hidden"
                  name="brand_id"
                  value="<?php echo (int)$brand['brand_id']; ?>"
                >

                <button type="submit" class="delete-button">
                  Delete
                </button>

              </form>

            </div>

          </div>

        <?php endforeach; ?>

      </div>

    <?php endif; ?>

  </section>

</main>

<?php include '../layout/footer.php'; ?>