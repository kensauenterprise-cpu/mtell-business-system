<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/init.php';

$id = $_GET['id'];

$order = $conn->query("
    SELECT * FROM orders WHERE id = $id
")->fetch_assoc();

$items = $conn->query("
    SELECT * FROM order_items WHERE order_id = $id
");
?>

<h2>🧾 Invoice #<?= $id ?></h2>

<p>Date: <?= $order['created_at'] ?></p>
<p>Payment: <?= $order['payment_method'] ?></p>

<table border="1" width="100%" cellpadding="8">
<tr>
    <th>Item</th>
    <th>Qty</th>
    <th>Price</th>
    <th>Total</th>
</tr>

<?php while($i = $items->fetch_assoc()): ?>
<tr>
    <td><?= $i['product_name'] ?></td>
    <td><?= $i['quantity'] ?></td>
    <td><?= $i['price'] ?></td>
    <td><?= $i['total'] ?></td>
</tr>
<?php endwhile; ?>
</table>

<h3>Total: KES <?= $order['total_amount'] ?></h3>

<script>
window.print();
</script>