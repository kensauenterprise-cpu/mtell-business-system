<?php
require_once __DIR__ . '/../includes/db.php'; // ensures $conn is available
require_once __DIR__ . '/../../config/functions.php'; // utility functions

if (!isset($conn) || $conn === null) {
    die('Database connection not initialized.');
}

$search = $_GET['search'] ?? '';
$query = "SELECT phone, amount, receipt_number, checkout_request_id, merchant_request_id, created_at FROM transactions WHERE 1";
$params = [];

if ($search !== '') {
    $query .= " AND (phone LIKE ? OR receipt_number LIKE ? OR checkout_request_id LIKE ?)";
    $searchTerm = "%$search%";
    $params = [$searchTerm, $searchTerm, $searchTerm];
}

$stmt = $conn->prepare($query);
if ($params) {
    $stmt->bind_param(str_repeat('s', count($params)), ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<h3>🧾 Transactions</h3>
<form method="GET" style="margin-bottom:15px;">
    <input type="text" name="search" placeholder="Search by phone, receipt, or checkout ID" value="<?= htmlspecialchars($search) ?>" style="width:300px; padding:5px;">
    <button type="submit">Filter</button>
</form>

<?php if ($result->num_rows === 0): ?>
    <p>No transactions found.</p>
<?php else: ?>
<table style="width:100%; border-collapse:collapse; margin-top:15px;" border="1" cellpadding="5" cellspacing="0">
    <tr>
        <th>Phone</th>
        <th>Amount</th>
        <th>Receipt</th>
        <th>Checkout ID</th>
        <th>Merchant ID</th>
        <th>Date</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['phone']) ?></td>
            <td>KES <?= number_format($row['amount'], 2) ?></td>
            <td><?= htmlspecialchars($row['receipt_number']) ?></td>
            <td><?= htmlspecialchars($row['checkout_request_id']) ?></td>
            <td><?= htmlspecialchars($row['merchant_request_id']) ?></td>
            <td><?= htmlspecialchars($row['created_at']) ?></td>
        </tr>
    <?php endwhile; ?>
</table>
<?php endif; ?>

<?php $stmt->close(); ?>
