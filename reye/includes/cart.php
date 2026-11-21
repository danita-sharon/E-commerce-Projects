<?php
// =========================================================
// Bag (shopping cart) helpers
// The bag lives in the session: $_SESSION['cart'][key] = line
// Prices are ALWAYS read from the database, never from the browser.
// =========================================================

// Total number of items in the bag
function cart_count(): int
{
    $n = 0;
    foreach ($_SESSION['cart'] ?? [] as $line) {
        $n += (int) $line['qty'];
    }
    return $n;
}

// Full details of every bag line, with current prices and stock from the database
function cart_lines(PDO $pdo): array
{
    $cart = $_SESSION['cart'] ?? [];
    if (!$cart) {
        return [];
    }

    $ids = implode(',', array_unique(array_map(fn($l) => (int) $l['product_id'], $cart)));
    $products = [];
    foreach ($pdo->query("SELECT p.*, c.slug AS category_slug FROM products p
                          JOIN categories c ON c.id = p.category_id
                          WHERE p.id IN ($ids) AND p.is_active = 1") as $p) {
        $products[(int) $p['id']] = $p;
    }
    $colours = [];
    foreach ($pdo->query("SELECT * FROM product_colours WHERE product_id IN ($ids)") as $c) {
        $colours[(int) $c['id']] = $c;
    }

    $lines = [];
    foreach ($cart as $key => $line) {
        $p = $products[(int) $line['product_id']] ?? null;
        if (!$p) {                       // product removed or hidden by the admin
            unset($_SESSION['cart'][$key]);
            continue;
        }
        $c = $colours[(int) $line['colour_id']] ?? null;
        $lines[] = [
            'key'           => $key,
            'product_id'    => (int) $p['id'],
            'name'          => $p['name'],
            'slug'          => $p['slug'],
            'category_slug' => $p['category_slug'],
            'colour_id'     => $c ? (int) $c['id'] : 0,
            'colour'        => $c['colour_name'] ?? '',
            'hex_main'      => $c['hex_main'] ?? '#3a332d',
            'hex_accent'    => $c['hex_accent'] ?? '#3a332d',
            'image'         => $c['image'] ?? null,
            'size'          => $line['size'],
            'qty'           => (int) $line['qty'],
            'stock'         => (int) $p['stock'],
            'price'         => (float) $p['price'],
            'line_total'    => (float) $p['price'] * (int) $line['qty'],
        ];
    }
    return $lines;
}

function cart_subtotal(array $lines): float
{
    return array_sum(array_column($lines, 'line_total'));
}
