<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/auth.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/role.php';
requireRole(['admin','wholesale']);

require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/db.php';
?>

<!DOCTYPE html>
<html>
<head>
<title>Wholesale POS</title>
<style>
body { font-family: Arial; }
.card { border:1px solid #ccc; padding:10px; margin:10px; }
</style>
</head>

<body>

<h2>🏪 Wholesale POS</h2>

<form method="POST" action="/infinity/pos/checkout.php">

<select name="customer_id" required>
<option value="">Select Customer</option>

<?php
$cust = $conn->query("SELECT * FROM customers");

while($c = $cust->fetch_assoc()):
?>
<option value="<?= $c['id'] ?>">
<?= $c['name'] ?> (Bal: <?= number_format($c['balance'],2) ?>)
</option>
<?php endwhile; ?>
</select>

<hr>

<?php
$res = $conn->query("SELECT * FROM products WHERE stock > 0");

while($p = $res->fetch_assoc()):
?>

<div class="card">
<strong><?= $p['name'] ?></strong><br>
Retail: <?= $p['price'] ?> |
Wholesale: <?= $p['wholesale_price'] ?><br>

Qty:
<input type="number" name="qty[<?= $p['id'] ?>]" min="0">
</div>

<?php endwhile; ?>

<hr>

<select name="payment_method">
<option value="cash">Cash</option>
<option value="mpesa">M-Pesa</option>
<option value="credit">Credit</option>
</select>

<input type="number" name="paid" placeholder="Amount Paid">

<br><br>
<button type="submit">✅ Complete Sale</button>

</form>

</body>
</html>