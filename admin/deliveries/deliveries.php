<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/db.php';

echo "<h2>🚚 Deliveries</h2>";

$result = $conn->query("
    SELECT *
    FROM sales
    ORDER BY created_at DESC
    LIMIT 20
");
?>

<div class="card">

<table border="1" width="100%" cellpadding="10">
    <tr>
        <th>ID</th>
        <th>Total</th>
        <th>Status</th>
        <th>Date</th>
    </tr>

    <?php if($result && $result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>

        <tr>
            <td><?= $row['id'] ?></td>
            <td>Ksh <?= number_format($row['total'],2) ?></td>
            <td>Delivered</td>
            <td><?= $row['created_at'] ?></td>
        </tr>

        <?php endwhile; ?>
    <?php endif; ?>

</table>

</div>

<style>
.card{
    background:white;
    padding:20px;
    border-radius:8px;
    box-shadow:0 0 10px #ccc;
}
table{
    border-collapse:collapse;
}
th{
    background:#007bff;
    color:white;
}
</style>