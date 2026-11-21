<?php
// =========================================================
// Site header: included at the top of every customer page.
// Before including, a page can set:
//   $page_title  - text for the browser tab
//   $active      - which nav link to underline (shop, pouches, bespoke, about)
// =========================================================
require_once __DIR__ . '/functions.php';

$page_title = $page_title ?? 'Exquisitely handmade';
$active = $active ?? '';

// Number of items in the bag (stored in the session)
$bag_count = 0;
foreach ($_SESSION['cart'] ?? [] as $line) {
    $bag_count += (int) $line['qty'];
}

// Number of saved products (logged-in customers only)
$wish_count = 0;
if (is_logged_in()) {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM wishlist WHERE user_id = ?');
    $stmt->execute([current_user()['id']]);
    $wish_count = (int) $stmt->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($page_title) ?> · Reye</title>
  <meta name="description" content="Reye - exquisitely handmade leather slides and pouches from Ghana. Nationwide delivery and worldwide shipping.">
  <meta name="csrf-token" content="<?= csrf_token() ?>">
  <link rel="icon" href="<?= url('assets/img/reye-logo-black.svg') ?>" type="image/svg+xml">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300;1,400&family=Jost:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3/dist/tabler-icons.min.css">
  <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>
<body data-base="<?= e(BASE_URL) ?>" data-logged-in="<?= is_logged_in() ? '1' : '0' ?>">
<?php require __DIR__ . '/logo.php'; ?>

<div class="announce" id="announce">Free delivery in Accra on orders over GH₵ 800 · Worldwide shipping</div>

<header class="site-header">
  <div class="wrap nav">
    <div class="nav-left">
    <button class="menu-btn" id="menuBtn" aria-label="Menu"><i class="ti ti-menu-2"></i></button>
    <nav class="nav-links" aria-label="Main">
      <a href="<?= url('shop.php') ?>" class="<?= $active === 'shop' ? 'active' : '' ?>">Shop</a>
      <a href="<?= url('shop.php?cat=pouches') ?>" class="<?= $active === 'pouches' ? 'active' : '' ?>">Pouches</a>
      <a href="<?= url('bespoke.php') ?>" class="<?= $active === 'bespoke' ? 'active' : '' ?>">Bespoke</a>
      <a href="<?= url('about.php') ?>" class="<?= $active === 'about' ? 'active' : '' ?>">About</a>
    </nav>
    </div>

    <a href="<?= url() ?>" class="nav-logo" aria-label="Reye home">
      <svg class="logo"><use href="#reye-logo"/></svg>
    </a>

    <div class="nav-icons">
      <button id="searchBtn" aria-label="Search"><i class="ti ti-search"></i></button>
      <a href="<?= url('wishlist.php') ?>" class="hide-sm" aria-label="Saved items" style="display:flex;align-items:center;gap:4px">
        <i class="ti ti-heart"></i><span id="wishCount"><?= $wish_count ?: '' ?></span>
      </a>
      <a href="<?= url(is_logged_in() ? 'account.php' : 'login.php') ?>" class="hide-sm" aria-label="Account">
        <i class="ti ti-user"></i>
      </a>
      <a href="<?= url('cart.php') ?>">BAG (<span id="bagCount"><?= $bag_count ?></span>)</a>
    </div>
  </div>

  <div class="search-panel" id="searchPanel">
    <div class="wrap">
      <label class="sr-only" for="searchInput">Search</label>
      <input class="input" id="searchInput" type="search" placeholder="Search slides, pouches, colours" autocomplete="off">
      <div class="search-results" id="searchResults"></div>
    </div>
  </div>

  <div class="mobile-menu" id="mobileMenu">
    <a href="<?= url('shop.php') ?>">Shop</a>
    <a href="<?= url('shop.php?cat=pouches') ?>">Pouches</a>
    <a href="<?= url('bespoke.php') ?>">Bespoke</a>
    <a href="<?= url('about.php') ?>">About</a>
    <a href="<?= url('wishlist.php') ?>">Saved</a>
    <a href="<?= url(is_logged_in() ? 'account.php' : 'login.php') ?>"><?= is_logged_in() ? 'My account' : 'Log in' ?></a>
  </div>
</header>

<?php foreach (get_flashes() as $f): ?>
  <div class="flash flash-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
<?php endforeach; ?>

<main>
