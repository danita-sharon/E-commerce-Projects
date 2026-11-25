<?php
// Admin layout: sidebar menu + page content. Set $page_title and $nav before including.
$nav = $nav ?? '';
$links = [
    'dashboard' => ['index.php', 'ti-layout-dashboard', 'Dashboard'],
    'orders'    => ['orders.php', 'ti-receipt', 'Orders'],
    'products'  => ['products.php', 'ti-shoe', 'Products'],
    'requests'  => ['requests.php', 'ti-message-circle', 'Requests'],
    'reviews'   => ['reviews.php', 'ti-star', 'Reviews'],
    'customers' => ['customers.php', 'ti-users', 'Customers'],
    'settings'  => ['settings.php', 'ti-settings', 'Settings'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex">
  <title><?= e($page_title ?? 'Admin') ?> · Reye admin</title>
  <link rel="icon" href="<?= url('assets/img/reye-logo-black.svg') ?>" type="image/svg+xml">
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;1,400&family=Jost:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3/dist/tabler-icons.min.css">
  <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>">
</head>
<body class="admin-body">
<?php require __DIR__ . '/../includes/logo.php'; ?>
<div class="admin">
  <aside class="admin-side">
    <a href="<?= url('admin/index.php') ?>" class="admin-brand">
      <svg class="logo"><use href="#reye-logo"/></svg><span>Admin</span>
    </a>
    <nav>
      <?php foreach ($links as $key => [$file, $icon, $label]): ?>
        <a href="<?= url('admin/' . $file) ?>" class="<?= $nav === $key ? 'active' : '' ?>">
          <i class="ti <?= $icon ?>"></i> <?= $label ?>
          <?php if ($key === 'requests' && $new_requests): ?><span class="count"><?= $new_requests ?></span><?php endif; ?>
        </a>
      <?php endforeach; ?>
    </nav>
    <div class="admin-side-foot">
      <a href="<?= url() ?>" target="_blank"><i class="ti ti-external-link"></i> View shop</a>
      <form method="post" action="<?= url('logout.php') ?>"><?= csrf_field() ?>
        <button type="submit"><i class="ti ti-logout"></i> Log out</button>
      </form>
    </div>
  </aside>

  <main class="admin-main">
    <?php foreach (get_flashes() as $f): ?>
      <div class="flash flash-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
    <?php endforeach; ?>
