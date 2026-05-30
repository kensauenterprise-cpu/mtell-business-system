<?php
// ===============================
// Load Dependencies
// ===============================
require_once $_SERVER['DOCUMENT_ROOT'] . '/infinity/config/functions.php';
$conn = require $_SERVER['DOCUMENT_ROOT'] . '/infinity/admin/includes/db.php';

// ===============================
// Fetch Audit Entries
// ===============================
$filter = $_GET['filter'] ?? 'all';
$whereClause = "";

if ($filter === 'success') {
    $whereClause = "WHERE result_code = 0";
} elseif ($filter === 'failed') {
    $whereClause = "WHERE result_code != 0";
}

$query = "SELECT * FROM callback_audit $whereClause ORDER BY created_at DESC LIMIT 100";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Callback Audit Dashboard</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f4f4f4; }
        table { width: 100%; border-collapse: collapse; background: #fff; }
        th, td { padding: 10px; border: 1px solid #ccc; }
        th { background: #eee; }
        .success { background: #d4edda; }
        .failed { background: #f8d7da; }
        .filter-links a {
            margin-right: 10px;
            text-decoration: none;
            padding: 6px 12px;
            background: #007bff;
            color: white;
            border-radius: 4px;
        }
        .filter-links a:hover {
            background: #0056b3;
        }
        details summary {
            cursor: pointer;
            font-weight: bold;
        }
        pre {
            white-space: pre-wrap;
            word-wrap: break-word;
            background: #f1f1f1;
            padding: 10px;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <h2>📋 Callback Audit Dashboard</h2>

    <div class="filter-links">
        <strong>Filter:</strong>
        <a href="?filter=all">All</a>
        <a href="?filter=success">Success</a>
        <a href="?filter=failed">Failed</a>
    </div>

    <table>
        <tr>
            <th>ID</th>
            <th>Checkout ID</th>
            <th>Result Code</th>
            <th>Description</th>
            <th>Timestamp</th>
            <th>Payload</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr class="<?= $row['result_code'] === 0 ? 'success' : 'failed' ?>">
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['checkout_request_id']) ?></td>
            <td><?= $row['result_code'] ?></td>
            <td><?= htmlspecialchars($row['result_desc']) ?></td>
            <td><?= $row['created_at'] ?></td>
            <td>
                <details>
                    <summary>View</summary>
                    <pre><?= htmlspecialchars($row['raw_payload']) ?></pre>
                </details>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
