<?php
// =========================================================
// Reviews: add real customer reviews, choose which ones show
// =========================================================
require_once __DIR__ . '/_init.php';

$errors = [];
$old = ['customer_name' => '', 'location' => '', 'review_text' => '', 'rating' => 5];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);

    if ($action === 'add') {
        $old = [
            'customer_name' => trim($_POST['customer_name'] ?? ''),
            'location'      => trim($_POST['location'] ?? ''),
            'review_text'   => trim($_POST['review_text'] ?? ''),
            'rating'        => max(1, min(5, (int) ($_POST['rating'] ?? 5))),
        ];
        if (mb_strlen($old['customer_name']) < 2) $errors[] = "Enter the customer's name (first name is fine).";
        if (mb_strlen($old['review_text']) < 5)   $errors[] = 'Enter the review.';
        if (mb_strlen($old['review_text']) > 500) $errors[] = 'Keep reviews under 500 characters.';
        if (empty($_POST['confirm_real']))        $errors[] = 'Tick the box to confirm this is a real customer review.';
        if (!$errors) {
            $pdo->prepare('INSERT INTO reviews (customer_name, location, review_text, rating, is_approved) VALUES (?, ?, ?, ?, 1)')
                ->execute([$old['customer_name'], $old['location'] ?: null, $old['review_text'], $old['rating']]);
            flash('success', 'Review added and showing on the homepage.');
            redirect('admin/reviews.php');
        }
    } elseif ($action === 'toggle') {
        $pdo->prepare('UPDATE reviews SET is_approved = 1 - is_approved WHERE id = ?')->execute([$id]);
        redirect('admin/reviews.php');
    } elseif ($action === 'delete') {
        $pdo->prepare('DELETE FROM reviews WHERE id = ?')->execute([$id]);
        flash('success', 'Review deleted.');
        redirect('admin/reviews.php');
    }
}

$reviews = $pdo->query('SELECT * FROM reviews ORDER BY id DESC')->fetchAll();

$page_title = 'Reviews';
$nav = 'reviews';
require __DIR__ . '/_header.php';
?>
<div class="admin-head">
  <div><p class="label">The Reye fam</p><h1>Reviews</h1>
    <p class="muted" style="margin:4px 0 0">Shown on the homepage. Only add reviews real customers sent you, e.g. from WhatsApp or Instagram messages.</p></div>
</div>

<div class="admin-grid wide-left">
  <section class="panel" style="padding:0">
    <?php if (!$reviews): ?>
      <p class="muted" style="padding:24px;margin:0">No reviews yet. Until you add some, the homepage shows a "Follow on Instagram" button instead.</p>
    <?php else: ?>
      <table class="table">
        <tr><th>Review</th><th>Rating</th><th>Showing</th><th></th></tr>
        <?php foreach ($reviews as $r): ?>
          <tr>
            <td><em>"<?= e($r['review_text']) ?>"</em><br><small class="muted"><?= e($r['customer_name'] . ($r['location'] ? ', ' . $r['location'] : '')) ?></small></td>
            <td class="stars"><?= str_repeat('★', (int) $r['rating']) ?></td>
            <td><?= $r['is_approved'] ? '<span class="status status-delivered">Live</span>' : '<span class="status">Hidden</span>' ?></td>
            <td class="actions">
              <form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $r['id'] ?>"><input type="hidden" name="action" value="toggle">
                <button class="icon-btn" title="<?= $r['is_approved'] ? 'Hide' : 'Show' ?>"><i class="ti <?= $r['is_approved'] ? 'ti-eye-off' : 'ti-eye' ?>"></i></button></form>
              <form method="post" data-confirm="Delete this review?"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $r['id'] ?>"><input type="hidden" name="action" value="delete">
                <button class="icon-btn danger" title="Delete"><i class="ti ti-trash"></i></button></form>
            </td>
          </tr>
        <?php endforeach; ?>
      </table>
    <?php endif; ?>
  </section>

  <section class="panel">
    <h2>Add a review</h2>
    <?php if ($errors): ?><div class="flash flash-error" style="text-align:left;margin-bottom:14px"><?= implode('<br>', array_map('e', $errors)) ?></div><?php endif; ?>
    <form method="post" novalidate>
      <?= csrf_field() ?><input type="hidden" name="action" value="add">
      <label class="field"><span>Customer name</span><input class="input" name="customer_name" value="<?= e($old['customer_name']) ?>" placeholder="Ama"></label>
      <label class="field"><span>Location (optional)</span><input class="input" name="location" value="<?= e($old['location']) ?>" placeholder="Accra"></label>
      <label class="field"><span>Their review</span><textarea class="input" name="review_text" rows="4"><?= e($old['review_text']) ?></textarea></label>
      <label class="field"><span>Rating</span>
        <select class="input" name="rating"><?php for ($i = 5; $i >= 1; $i--): ?><option value="<?= $i ?>" <?= (int) $old['rating'] === $i ? 'selected' : '' ?>><?= str_repeat('★', $i) ?></option><?php endfor; ?></select>
      </label>
      <label class="check"><input type="checkbox" name="confirm_real"> This is a real review from a customer</label>
      <button class="btn btn-block" style="margin-top:14px">Add review</button>
    </form>
  </section>
</div>

<?php require __DIR__ . '/_footer.php'; ?>
