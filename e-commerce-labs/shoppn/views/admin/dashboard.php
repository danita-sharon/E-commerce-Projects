<?php
require_once '../../core/core.php';
require_admin();

include '../layout/header.php';
?>

<main class="admin-dashboard">

  <div class="dashboard-header">
    <h1>Admin Dashboard</h1>
    <p>Manage your Shoppn store from one place.</p>
  </div>

  <div class="dashboard-grid">

    <!-- Manage Brands -->
    <a href="brand.php" class="dashboard-card">

      <div class="dashboard-icon">
        🏷️
      </div>

      <div class="dashboard-card-content">
        <h2>Manage Brands</h2>
        <p>Add, edit, and delete product brands.</p>
      </div>

      <span class="dashboard-arrow">→</span>

    </a>


    <!-- Manage Categories -->
    <a href="category.php" class="dashboard-card">

      <div class="dashboard-icon">
        📦
      </div>

      <div class="dashboard-card-content">
        <h2>Manage Categories</h2>
        <p>Add, edit, and delete product categories.</p>
      </div>

      <span class="dashboard-arrow">→</span>

    </a>


    <!-- Manage Products -->
    <a href="product.php" class="dashboard-card">

      <div class="dashboard-icon">
        🛍️
      </div>

      <div class="dashboard-card-content">
        <h2>Manage Products</h2>
        <p>Add and edit your store products.</p>
      </div>

      <span class="dashboard-arrow">→</span>

    </a>


    <!-- Customers -->
    <div class="dashboard-card dashboard-card-disabled">

      <div class="dashboard-icon">
        👥
      </div>

      <div class="dashboard-card-content">
        <h2>Customers</h2>
        <p>View and manage customers.</p>
      </div>

      <span class="coming-soon">
        Coming Soon
      </span>

    </div>

  </div>

</main>

<?php include '../layout/footer.php'; ?>