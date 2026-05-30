<?php
// === Safe session start ===
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// === Load DB connection ===
require_once $_SERVER['DOCUMENT_ROOT'] . '/infinity/config/functions.php';
$conn = $GLOBALS['conn'] ?? null;

// === Validate DB connection ===
if (!($conn instanceof mysqli)) {
    echo "<p style='color:red;'>❌ Unable to connect to database.</p>";
    return;
}

// === Fetch recent STK Push logs ===
$query = "SELECT phone, amount, status, created_at FROM stk_push_log ORDER BY created_at DESC LIMIT 10";
$result = $conn->query($query);

if (!$result || $result->num_rows === 0) {
    echo "<p>No STK Push logs found.</p>";
    return;
}
?>

<style>
    .stk-widget table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }
    .stk-widget th, .stk-widget td {
        padding: 10px;
        border-bottom: 1px solid #ddd;
        text-align: left;
    }
    .stk-widget th {
        background-color: #007bff;
        color: white;
    }
    .status-success {
        color: #28a745;
        font-weight: bold;
    }
    .status-failed {
        color: #dc3545;
        font-weight: bold;
    }
</style>

<div class="stk-widget">
    <h3>📋 Recent STK Push Attempts</h3>
    <table>
        <thead>
            <tr>
                <th>Phone</th>
                <th>Amount (Ksh)</th>
                <th>Status</th>
                <th>Timestamp</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['phone']) ?></td>
                    <td><?= number_format($row['amount'], 2) ?></td>
                    <td class="<?= $row['status'] === 'success' ? 'status-success' : 'status-failed' ?>">
                        <?= ucfirst($row['status']) ?>
                    </td>
                    <td><?= date('d M Y, H:i:s', strtotime($row['created_at'])) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
