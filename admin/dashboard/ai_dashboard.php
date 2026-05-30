<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/init.php';

$branch_id = $_SESSION['branch_id'] ?? 1;

if ($branch_id === 'all') {
    $data = $conn->query("
        SELECT p.name, a.*
        FROM ai_predictions a
        JOIN products p ON p.id = a.product_id
        ORDER BY a.days_to_stockout ASC
        LIMIT 50
    ");
} else {
    $stmt = $conn->prepare("
        SELECT p.name, a.*
        FROM ai_predictions a
        JOIN products p ON p.id = a.product_id
        WHERE a.branch_id=?
        ORDER BY a.days_to_stockout ASC
        LIMIT 50
    ");
    $stmt->bind_param("i", $branch_id);
    $stmt->execute();
    $data = $stmt->get_result();
}
?>

<h2>🧠 AI Sales Prediction</h2>

<table border="1" width="100%" cellpadding="8">
<tr>
    <th>Product</th>
    <th>Daily Sales</th>
    <th>Days Left</th>
    <th>Restock</th>
</tr>

<?php while($row = $data->fetch_assoc()): ?>

<tr>
<td><?= htmlspecialchars($row['name']) ?></td>

<td><?= round($row['predicted_daily_sales'],2) ?></td>

<td style="color:
<?= $row['days_to_stockout'] < 5 ? 'red' : 'green' ?>">
<?= round($row['days_to_stockout']) ?>
</td>

<td>
<?php if($row['recommended_restock'] > 0): ?>
<b style="color:red">
Order <?= $row['recommended_restock'] ?>
</b>
<?php else: ?>
OK
<?php endif; ?>
</td>

</tr>

<?php endwhile; ?>

</table>