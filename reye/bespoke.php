<?php
// =========================================================
// Bespoke (custom-made) orders
// Requests are saved to customer_requests for the admin to see.
// =========================================================
require_once __DIR__ . '/includes/functions.php';

$errors = [];
$old = [
    'name'    => current_user()['full_name'] ?? '',
    'contact' => current_user()['phone'] ?? '',
    'style'   => '',
    'colour'  => '',
    'size'    => '',
    'notes'   => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    foreach ($old as $k => $v) {
        $old[$k] = trim($_POST[$k] ?? '');
    }
    if (mb_strlen($old['name']) < 2) {
        $errors['name'] = 'Enter your name.';
    }
    $contactClean = preg_replace('/\s+/', '', $old['contact']);
    if (!filter_var($contactClean, FILTER_VALIDATE_EMAIL) && !preg_match('/^\+?\d{9,15}$/', $contactClean)) {
        $errors['contact'] = 'Enter a WhatsApp number or email so we can reach you.';
    }
    if ($old['style'] === '') {
        $errors['style'] = 'Tell us what you would like made.';
    }
    if (mb_strlen($old['notes']) > 1000) {
        $errors['notes'] = 'Keep your notes under 1000 characters.';
    }

    if (!$errors) {
        $message = "Style: {$old['style']}\nColour/leather: {$old['colour']}\nSize or foot length: {$old['size']}\nNotes: {$old['notes']}";
        $stmt = $pdo->prepare("INSERT INTO customer_requests (user_id, name, contact, message, type) VALUES (?, ?, ?, ?, 'bespoke')");
        $stmt->execute([current_user()['id'] ?? null, $old['name'], $old['contact'], $message]);
        flash('success', 'Thank you. Your bespoke request is with the Reye team and we\'ll reach out within 24 hours.');
        redirect('bespoke.php');
    }
}

$page_title = 'Bespoke';
$active = 'bespoke';
require __DIR__ . '/includes/header.php';
?>
<section class="page-head"><p class="label">Bespoke</p><h1>Made just for you</h1>
  <p style="color:var(--muted);max-width:520px;margin:12px auto 0">Choose your leather, your colour and your fit. Your pair is made by hand in 7 to 10 days.</p>
</section>

<div class="wrap">
  <div class="steps-row">
    <div class="panel"><p class="serif step-num">1</p><p class="label">Tell us your idea</p><p>Style, colour, leather and any special details.</p></div>
    <div class="panel"><p class="serif step-num">2</p><p class="label">Send your size</p><p>Your EU size, or measure your foot from heel to longest toe.</p></div>
    <div class="panel"><p class="serif step-num">3</p><p class="label">We make it</p><p>We confirm the price on WhatsApp, then handcraft your pair.</p></div>
  </div>

  <div class="bespoke-form-wrap">
    <form method="post" class="panel" novalidate>
      <?= csrf_field() ?>
      <div class="two-col">
        <label class="field"><span>Your name</span>
          <input class="input" name="name" value="<?= e($old['name']) ?>" required>
          <?php if (isset($errors['name'])): ?><p class="error-text"><?= e($errors['name']) ?></p><?php endif; ?>
        </label>
        <label class="field"><span>WhatsApp number or email</span>
          <input class="input" name="contact" value="<?= e($old['contact']) ?>" placeholder="024 123 4567" required>
          <?php if (isset($errors['contact'])): ?><p class="error-text"><?= e($errors['contact']) ?></p><?php endif; ?>
        </label>
      </div>
      <label class="field"><span>What would you like made?</span>
        <input class="input" name="style" value="<?= e($old['style']) ?>" placeholder="Cross-strap slides, a pouch, a matching set..." required>
        <?php if (isset($errors['style'])): ?><p class="error-text"><?= e($errors['style']) ?></p><?php endif; ?>
      </label>
      <div class="two-col">
        <label class="field"><span>Colour or leather</span>
          <input class="input" name="colour" value="<?= e($old['colour']) ?>" placeholder="Cognac suede">
        </label>
        <label class="field"><span>Size or foot length</span>
          <input class="input" name="size" value="<?= e($old['size']) ?>" placeholder="EU 43 or 27 cm">
        </label>
      </div>
      <label class="field"><span>Anything else?</span>
        <textarea class="input" name="notes" rows="4" placeholder="Occasion, deadline, initials..."><?= e($old['notes']) ?></textarea>
        <?php if (isset($errors['notes'])): ?><p class="error-text"><?= e($errors['notes']) ?></p><?php endif; ?>
      </label>
      <button class="btn btn-block" type="submit">Send my request</button>
      <p class="form-foot">Prefer to chat? <a href="<?= e(whatsapp_link('Hi Reye, I would like a bespoke pair.')) ?>" target="_blank" rel="noopener">Message us on WhatsApp</a></p>
    </form>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
