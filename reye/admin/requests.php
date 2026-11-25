<?php
// =========================================================
// Requests inbox: bespoke orders and Reye AI requests
// =========================================================
require_once __DIR__ . '/_init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int) ($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';
    if (in_array($action, ['new', 'seen', 'done'], true)) {
        $pdo->prepare('UPDATE customer_requests SET status = ? WHERE id = ?')->execute([$action, $id]);
    } elseif ($action === 'delete') {
        $pdo->prepare('DELETE FROM customer_requests WHERE id = ?')->execute([$id]);
        flash('success', 'Request deleted.');
    }
    redirect('admin/requests.php?show=' . urlencode($_GET['show'] ?? 'open'));
}

$show = $_GET['show'] ?? 'open';
$tabs = ['open' => 'Open', 'new' => 'New', 'done' => 'Done', 'all' => 'All'];
$where = ['open' => "status <> 'done'", 'new' => "status = 'new'", 'done' => "status = 'done'", 'all' => '1 = 1'][$show] ?? "status <> 'done'";
$requests = $pdo->query("SELECT * FROM customer_requests WHERE $where ORDER BY (status = 'new') DESC, id DESC")->fetchAll();

$page_title = 'Requests';
$nav = 'requests';
require __DIR__ . '/_header.php';
?>
<div class="admin-head">
  <div><p class="label">Inbox</p><h1>Customer requests</h1>
    <p class="muted" style="margin:4px 0 0">Bespoke orders, and things customers asked Reye AI for that the shop doesn't have yet.</p></div>
</div>

<div class="tabs">
  <?php foreach ($tabs as $key => $label): ?>
    <a href="<?= url('admin/requests.php?show=' . $key) ?>" class="<?= $show === $key ? 'active' : '' ?>"><?= $label ?></a>
  <?php endforeach; ?>
</div>

<?php if (!$requests): ?>
  <div class="panel"><p class="muted" style="margin:0">Nothing here. You're all caught up.</p></div>
<?php endif; ?>

<div class="request-list">
  <?php foreach ($requests as $r):
      $isPhone = (bool) preg_match('/^\+?[\d\s]{9,16}$/', $r['contact']);
      $first = explode(' ', $r['name'])[0];
      $reply = "Hi $first, this is Reye. Thank you for your " . ($r['type'] === 'bespoke' ? 'bespoke request' : 'message') . '. ';
  ?>
    <article class="panel request <?= $r['status'] === 'new' ? 'is-new' : '' ?>">
      <div class="request-top">
        <div>
          <strong><?= e($r['name']) ?></strong>
          <span class="tag"><?= $r['type'] === 'request' ? 'Reye AI request' : e(ucfirst($r['type'])) ?></span>
          <?= status_badge($r['status']) ?>
        </div>
        <small class="muted"><?= e(date('j M Y, g:ia', strtotime($r['created_at']))) ?></small>
      </div>
      <p class="request-msg"><?= nl2br(e($r['message'])) ?></p>
      <div class="request-actions">
        <?php if ($isPhone): ?>
          <a class="btn btn-sm" target="_blank" rel="noopener" href="https://wa.me/<?= e(wa_number($r['contact'])) ?>?text=<?= rawurlencode($reply) ?>"><i class="ti ti-brand-whatsapp"></i> Reply on WhatsApp</a>
        <?php else: ?>
          <a class="btn btn-sm" href="mailto:<?= e($r['contact']) ?>?subject=<?= rawurlencode('Your Reye request') ?>&body=<?= rawurlencode($reply) ?>"><i class="ti ti-mail"></i> Reply by email</a>
        <?php endif; ?>
        <span class="muted" style="font-size:13px"><?= e($r['contact']) ?></span>
        <span style="flex:1"></span>
        <?php foreach (['seen' => 'Mark seen', 'done' => 'Done'] as $act => $label): if ($r['status'] === $act) continue; ?>
          <form method="post" action="<?= url('admin/requests.php?show=' . urlencode($show)) ?>"><?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int) $r['id'] ?>"><input type="hidden" name="action" value="<?= $act ?>">
            <button class="btn btn-outline btn-sm"><?= $label ?></button></form>
        <?php endforeach; ?>
        <form method="post" action="<?= url('admin/requests.php?show=' . urlencode($show)) ?>" data-confirm="Delete this request?"><?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= (int) $r['id'] ?>"><input type="hidden" name="action" value="delete">
          <button class="icon-btn danger" title="Delete"><i class="ti ti-trash"></i></button></form>
      </div>
    </article>
  <?php endforeach; ?>
</div>

<?php require __DIR__ . '/_footer.php'; ?>
