<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ✅ Connect to DB
$pdo = new PDO('mysql:host=localhost;dbname=if0_39282158_business', 'your_db_user', 'your_db_pass');

// ✅ Handle filters
$phone = $_GET['phone'] ?? '';
$from  = $_GET['from'] ?? '';
$to    = $_GET['to'] ?? '';
$code  = $_GET['code'] ?? '';

// ✅ Build query
$sql = "SELECT * FROM stkpush_queries WHERE 1";
$params = [];

if ($phone) {
    $sql .= " AND phone LIKE ?";
    $params[] = "%$phone%";
}
if ($code !== '') {
    $sql .= " AND result_code = ?";
    $params[] = $code;
}
if ($from && $to) {
    $sql .= " AND query_time BETWEEN ? AND ?";
    $params[] = $from . " 00:00:00";
    $params[] = $to . " 23:59:59";
}

$sql .= " ORDER BY query_time DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>STK Push Query Dashboard</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #f4f4f4; }
        input, select { padding: 6px; margin-right: 10px; }
    </style>
</head>
<body>
    <h2>📊 STK Push Query Dashboard</h2>

    <form method="GET">
        <input type="text" name="phone" placeholder="Phone" value="<?= htmlspecialchars($phone) ?>">
        <input type="date" name="from" value="<?= htmlspecialchars($from) ?>">
        <input type="date" name="to" value="<?= htmlspecialchars($to) ?>">
        <select name="code">
            <option value="">All Result Codes</option>
            <option value="0" <?= $code === '0' ? 'selected' : '' ?>>0 - Success</option>
            <option value="1" <?= $code === '1' ? 'selected' : '' ?>>1 - Failed</option>
            <!-- Add more codes as needed -->
        </select>
        <button type="submit">Filter</button>
        <a href="export_csv.php?<?= http_build_query($_GET) ?>">📥 Export CSV</a>
    </form>

    <table>
        <tr>
            <th>Phone</th>
            <th>Checkout ID</th>
            <th>Result Code</th>
            <th>Description</th>
            <th>Timestamp</th>
        </tr>
        <?php foreach ($rows as $row): ?>
        <tr>
            <td><?= htmlspecialchars($row['phone']) ?></td>
            <td><?= htmlspecialchars($row['checkout_id']) ?></td>
            <td><?= htmlspecialchars($row['result_code']) ?></td>
            <td><?= htmlspecialchars($row['result_desc']) ?></td>
            <td><?= htmlspecialchars($row['query_time']) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
