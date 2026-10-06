<?php
require_once '../../core/core.php';
require_admin();
require_once '../../controllers/ProductController.php';

include '../layout/header.php';

$controller = new ProductController();

$editingCategory = null;

if (isset($_GET['edit_id'])) {
  $editId = filter_var($_GET['edit_id'], FILTER_VALIDATE_INT);

  if ($editId) {
    $editingCategory = $controller->getCategoryById($editId);
  }
}

$categories = $controller->getAllCategories();
?>

<main class="brand-page">

  <div class="brand-header">
    <h1>Manage Categories</h1>
    <p>Add, edit, and manage your product categories.</p>
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

  <section class="brand-form-card">

    <h2>
      <?php echo $editingCategory ? 'Edit Category' : 'Add New Category'; ?>
    </h2>

    <p class="card-description">
      <?php echo $editingCategory
        ? 'Update the name of this category.'
        : 'Create a new category for your products.'; ?>
    </p>

    <?php if ($editingCategory): ?>

      <form action="../../actions/update_category_action.php" method="POST">

        <input
          type="hidden"
          name="cat_id"
          value="<?php echo (int)$editingCategory['cat_id']; ?>"
        >

        <label for="cat_name">Category Name</label>

        <input
          type="text"
          id="cat_name"
          name="cat_name"
          value="<?php echo htmlspecialchars($editingCategory['cat_name']); ?>"
          required
        >

        <div class="form-buttons">

          <button type="submit">
            Update Category
          </button>

          <a href="category.php" class="cancel-button">
            Cancel
          </a>

        </div>

      </form>

    <?php else: ?>

      <form action="../../actions/add_category_action.php" method="POST">

        <label for="cat_name">Category Name</label>

        <input
          type="text"
          id="cat_name"
          name="cat_name"
          placeholder="Enter category name"
          required
        >

        <button type="submit">
          Add Category
        </button>

      </form>

    <?php endif; ?>

  </section>


  <section class="all-brands">

    <div class="section-heading">

      <h2>All Categories</h2>

      <span>
        <?php echo count($categories); ?> categories
      </span>

    </div>


    <?php if (empty($categories)): ?>

      <div class="empty-brands">

        <h3>No categories yet</h3>

        <p>
          Add your first category using the form above.
        </p>

      </div>

    <?php else: ?>

      <div class="brand-grid">

        <?php foreach ($categories as $category): ?>

          <div class="brand-card">

            <div class="brand-card-icon">
              📦
            </div>

            <div class="brand-card-content">

              <h3>
                <?php echo htmlspecialchars($category['cat_name']); ?>
              </h3>

            </div>

            <div class="brand-card-actions">

              <a
                href="category.php?edit_id=<?php echo (int)$category['cat_id']; ?>"
                class="edit-button"
              >
                Edit
              </a>

              <form
                action="../../actions/delete_category_action.php"
                method="POST"
                onsubmit="return confirm('Are you sure you want to delete this category?');"
              >

                <input
                  type="hidden"
                  name="cat_id"
                  value="<?php echo (int)$category['cat_id']; ?>"
                >

                <button
                  type="submit"
                  class="delete-button"
                >
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