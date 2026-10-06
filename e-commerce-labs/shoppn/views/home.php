<?php include 'layout/header.php'; ?>

<main class="home-page">

  <section class="home-welcome">
    <h1>Welcome to Shoppn</h1>

    <?php if (is_admin()): ?>
      <p>Manage your store using the options below.</p>
    <?php else: ?>
      <p>Browse our products and find something you love.</p>
    <?php endif; ?>
  </section>


  <?php if (is_admin()): ?>

    <section class="admin-dashboard">

      <a
        href="/~danita.quarshie/shoppn/views/admin/brand.php"
        class="admin-dashboard-card"
      >
        <div class="admin-card-icon">🏷️</div>

        <h2>Manage Brands</h2>

        <p>
          Add, edit, and manage product brands.
        </p>

        <span>Manage Brands →</span>
      </a>


      <a
        href="/~danita.quarshie/shoppn/views/admin/category.php"
        class="admin-dashboard-card"
      >
        <div class="admin-card-icon">📦</div>

        <h2>Manage Categories</h2>

        <p>
          Add, edit, and manage product categories.
        </p>

        <span>Manage Categories →</span>
      </a>

    </section>

  <?php endif; ?>

</main>

<?php include 'layout/footer.php'; ?>

