

<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/init.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $conn->prepare("
    SELECT
        p.*,
        pr.product_name,
        s.supplier_name
    FROM purchases p
    LEFT JOIN products pr ON p.product_id = pr.id
    LEFT JOIN suppliers s ON p.supplier_id = s.id
    WHERE p.id=?
");

$stmt->bind_param("i", $id);

$stmt->execute();

$purchase = $stmt->get_result()->fetch_assoc();

if(!$purchase){
    die("Purchase not found");
}
?>

<h2>🧾 Purchase Invoice</h2>

<hr>

<p>
<b>Invoice ID:</b>
<?= $purchase['id'] ?>
</p>

<p>
<b>Supplier:</b>
<?= htmlspecialchars($purchase['supplier_name']) ?>
</p>

<p>
<b>Product:</b>
<?= htmlspecialchars($purchase['product_name']) ?>
</p>

<p>
<b>Quantity:</b>
<?= $purchase['quantity'] ?>
</p>

<p>
<b>Cost Price:</b>
KES <?= number_format($purchase['cost_price'],2) ?>
</p>

<p>
<b>Total Cost:</b>
KES <?= number_format($purchase['total_cost'],2) ?>
</p>

<p>
<b>Purchase Date:</b>
<?= $purchase['purchase_date'] ?>
</p>

<p>
<b>Created By:</b>
<?= htmlspecialchars($purchase['created_by']) ?>
</p>

<br>

<button onclick="window.print()">
🖨 Print Invoice
</button>