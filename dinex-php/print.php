<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

$userId = current_user_id();

if (empty($_SESSION['cart'])) {
    header('Location: billing.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM products WHERE user_id = ?');
$stmt->execute([$userId]);
$productsById = [];
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $p) $productsById[$p['id']] = $p;

$lines = [];
$subtotal = 0;
$gstTotal = 0;
foreach ($_SESSION['cart'] as $pid => $qty) {
    if (!isset($productsById[$pid])) continue;
    $p = $productsById[$pid];
    $lineBase = (float)$p['price'] * $qty;
    $lineGst = $lineBase * ((float)$p['gst'] / 100);
    $subtotal += $lineBase;
    $gstTotal += $lineGst;
    $lines[] = ['name' => $p['name'], 'qty' => $qty, 'amount' => $lineBase + $lineGst];
}
$total = $subtotal + $gstTotal;

$active = 'billing';
$hidePreloader = true;
include __DIR__ . '/includes/header.php';
?>

<div class="receipt-page">
  <div class="receipt">
    <div class="rlogo"><img src="assets/logo.png" alt="DineX"></div>
    <div class="rtitle"><?= htmlspecialchars(current_user_name()) ?></div>
    <div class="rsub"><?= date('d M Y, h:i A') ?></div>
    <hr>
    <?php foreach ($lines as $line): ?>
      <div class="rline">
        <span><?= htmlspecialchars($line['name']) ?> × <?= $line['qty'] ?></span>
        <span>₹<?= number_format($line['amount'], 2) ?></span>
      </div>
    <?php endforeach; ?>
    <hr>
    <div class="rline"><span>Subtotal</span><span>₹<?= number_format($subtotal, 2) ?></span></div>
    <div class="rline"><span>GST</span><span>₹<?= number_format($gstTotal, 2) ?></span></div>
    <div class="rtotal"><span>TOTAL</span><span>₹<?= number_format($total, 2) ?></span></div>
  </div>

  <div class="print-hint no-print">
    Use your browser's print option (<kbd>Ctrl</kbd>+<kbd>P</kbd> or <kbd>Cmd</kbd>+<kbd>P</kbd>) to print this receipt.
    <br><a href="billing.php" style="text-decoration:underline;">← Back to billing</a>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
