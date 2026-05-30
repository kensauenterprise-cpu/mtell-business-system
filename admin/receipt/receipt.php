<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/init.php';

$order_id = $_GET['order_id'] ?? 0;

// FETCH ORDER
$order = $conn->query("
    SELECT * FROM orders WHERE id = $order_id LIMIT 1
")->fetch_assoc();

if(!$order){
    die("Order not found");
}

// FETCH ITEMS
$items = $conn->query("
    SELECT * FROM order_items WHERE order_id = $order_id
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Receipt #<?= $order_id ?></title>

<style>
body {
    font-family: monospace;
    width: 300px;
    margin: auto;
}

.center { text-align:center; }

table {
    width:100%;
    font-size:12px;
}

hr {
    border:1px dashed #000;
}

.total {
    font-weight:bold;
}

@media print {
    button { display:none; }
}
</style>
</head>

<body>

<div class="center">
    <h3>🛍️ Infinity Store</h3>
    <p>Nairobi, Kenya</p>
    <p>Tel: 07XXXXXXXX</p>
</div>

<hr>

<p>Receipt #: <?= $order_id ?></p>
<p>Date: <?= $order['created_at'] ?></p>
<p>Payment: <?= strtoupper($order['payment_method']) ?></p>

<hr>

<table>
<?php while($item = $items->fetch_assoc()): ?>
<tr>
    <td><?= $item['product_name'] ?> x<?= $item['quantity'] ?></td>
    <td align="right"><?= number_format($item['total'],2) ?></td>
</tr>
<?php endwhile; ?>
</table>

<hr>

<p class="total">TOTAL: KES <?= number_format($order['total_amount'],2) ?></p>

<p>Status: <?= $order['payment_status'] ?></p>

<hr>

<div class="center">
    <p>🙏 Thank you!</p>
</div>

<br>

<button onclick="window.print()">🖨️ Print</button>
<button onclick="downloadPDF()">📄 Download PDF</button>

<script>
function downloadPDF(){
    window.open("receipt_pdf.php?order_id=<?= $order_id ?>", "_blank");
}
</script>

</body>
</html>