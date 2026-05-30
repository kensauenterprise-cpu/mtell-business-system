<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/db.php';

echo "<h2>📊 Audit Trail</h2>";

$result = $conn->query("
    SELECT *
    FROM sales
    ORDER BY created_at DESC
    LIMIT 30
");
?>

<div class="card">

<table border="1" width="100%" cellpadding="10">

<tr>
    <th>Action</th>
    <th>User</th>
    <th>Date</th>
</tr>

<?php if($result && $result->num_rows > 0): ?>
    <?php while($row = $result->fetch_assoc()): ?>

    <tr>
        <td>Created Sale #<?= $row['id'] ?></td>
        <td><?= $row['user_id'] ?? 'System' ?></td>
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