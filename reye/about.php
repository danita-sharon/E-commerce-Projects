<?php
// =========================================================
// About Reye, the process, and delivery information
// =========================================================
require_once __DIR__ . '/includes/functions.php';

$regions = $pdo->query('SELECT name, fee, free_over FROM delivery_regions ORDER BY fee')->fetchAll();

$page_title = 'About';
$active = 'about';
require __DIR__ . '/includes/header.php';
?>
<section class="page-head"><p class="label">About Reye</p><h1>The Reye't choice</h1></section>

<div class="wrap">
  <div class="about-intro">
    <svg class="logo" style="width:64px;height:79px;margin:0 auto 20px"><use href="#reye-logo"/></svg>
    <p>Reye (said "rai") makes leather slides and pouches by hand in Ghana, mostly for men, with smaller batches of women's pairs. Every pair is cut, stitched and finished by a craftsman, then sealed with the Reye R, so no two pairs are ever exactly alike.</p>
    <p>We make pieces to wear for years, not for a season. And if you want something that is completely yours, we will make it for you.</p>
  </div>

  <section class="split" style="margin-top:48px">
    <div class="split-text">
      <p class="label">The process</p>
      <h2>Made slowly.<em>Made to last.</em></h2>
      <p><strong>Cut.</strong> Each piece of leather is chosen and cut by hand.<br>
         <strong>Stitch.</strong> Straps and soles are stitched and shaped for comfort.<br>
         <strong>Finish.</strong> Edges are polished and every pair gets the Reye R.</p>
    </div>
    <div class="process">
      <div class="step"><div class="step-icon"><i class="ti ti-scissors"></i></div><p class="label">Cut</p></div>
      <div class="step"><div class="step-icon"><i class="ti ti-needle-thread"></i></div><p class="label">Stitch</p></div>
      <div class="step final"><div class="step-icon"><svg class="logo"><use href="#reye-logo"/></svg></div><p class="label">Finish</p></div>
    </div>
  </section>

  <section class="section" id="delivery">
    <div class="section-head"><h2>Delivery</h2></div>
    <div class="panel" style="margin-top:20px">
      <table class="info-table">
        <tr><th>Where</th><th>Fee</th><th>Free over</th></tr>
        <?php foreach ($regions as $r): ?>
          <tr><td><?= e($r['name']) ?></td><td><?= money($r['fee']) ?></td><td><?= $r['free_over'] ? money($r['free_over']) : '-' ?></td></tr>
        <?php endforeach; ?>
      </table>
      <p style="color:var(--muted);margin:14px 0 0;font-size:13px">Orders in Ghana arrive in 1 to 3 working days. International orders take about 7 to 10 working days. Bespoke pairs take 7 to 10 days to make before they ship.</p>
    </div>
  </section>

  <section class="section" style="padding-top:0">
    <div class="section-head"><h2>Questions?</h2></div>
    <p style="color:var(--body)">Message us on <a href="<?= e(whatsapp_link('Hi Reye, I have a question.')) ?>" target="_blank" rel="noopener" style="text-decoration:underline">WhatsApp</a> or <a href="https://www.instagram.com/the.reye.official/" target="_blank" rel="noopener" style="text-decoration:underline">Instagram</a>. We usually reply within a few hours.</p>
  </section>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
