<?php
// ==========================
// 🔥 USE GLOBAL CONNECTION (DO NOT LOAD INIT AGAIN)
// ==========================
$conn = $GLOBALS['conn'] ?? null;

if (!$conn) {
    die("❌ Database connection missing");
}

// ==========================
// 📦 FETCH ORDERS
// ==========================
$result = $conn->query("
    SELECT id, total_amount, payment_method, payment_status, created_at 
    FROM orders 
    ORDER BY id DESC
");

if (!$result) {
    die("❌ Query failed: " . $conn->error);
}
?>

<h2>🧾 Invoices</h2>

<table border="1" width="100%" cellpadding="8" style="background:white;">
<tr style="background:#f1f5f9;">
    <th>ID</th>
    <th>Total</th>
    <th>Payment</th>
    <th>Status</th>
    <th>Date</th>
    <th>Action</th>
</tr>

<?php while($o = $result->fetch_assoc()): ?>
<tr>
    <td>#<?= $o['id'] ?></td>
    <td><b>KES <?= number_format($o['total_amount'],2) ?></b></td>
    <td><?= strtoupper($o['payment_method']) ?></td>
    <td>
        <span style="color:
            <?= $o['payment_status']=='paid' ? 'green' :
               ($o['payment_status']=='pending' ? 'orange' : 'red') ?>">
            <?= strtoupper($o['payment_status']) ?>
        </span>
    </td>
    <td><?= $o['created_at'] ?></td>
    <td>
        <button onclick="printInvoice(<?= $o['id'] ?>)">🖨️ Print</button>
    </td>
</tr>
<?php endwhile; ?>
</table>

<script>
function printInvoice(id){
    window.open("/infinity/admin/invoices/print_invoice.php?id=" + id, "_blank");
}
</script>