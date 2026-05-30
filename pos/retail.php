<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/auth.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/role.php';
requireRole(['admin','cashier']);

require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/db.php';
?>

<!DOCTYPE html>
<html>
<head>
<title>Retail POS</title>
<style>
body { font-family: Arial; }
.card { border:1px solid #ccc; padding:10px; margin:10px; }
</style>
</head>

<body>

<h2>🛒 Retail POS</h2>

<form method="POST" action="/infinity/pos/checkout.php">

<input type="hidden" name="customer_id" value="1">

<?php
$res = $conn->query("SELECT * FROM products WHERE stock > 0 LIMIT 50");

while($p = $res->fetch_assoc()):
?>

<div class="card">
<strong><?= $p['name'] ?></strong><br>
KES <?= $p['price'] ?><br>

<button type="button" onclick="addQty(<?= $p['id'] ?>)">➕ Add</button>

<input type="hidden" name="qty[<?= $p['id'] ?>]" id="qty<?= $p['id'] ?>" value="0">
<span id="d<?= $p['id'] ?>">0</span>
</div>

<?php endwhile; ?>

<hr>

<select name="payment_method">
<option value="cash">Cash</option>
<option value="mpesa">M-Pesa</option>
</select>

<input type="number" name="paid" placeholder="Amount Paid">

<br><br>
<button type="submit">💳 Checkout</button>

</form>

<script>
function addQty(id){
    let q = document.getElementById('qty'+id);
    let d = document.getElementById('d'+id);

    q.value = parseInt(q.value) + 1;
    d.innerText = q.value;
}
</script>

</body>
</html>