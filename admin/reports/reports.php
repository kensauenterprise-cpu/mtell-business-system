<?php

require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/init.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/db.php';

// ======================
// 🔐 SESSION SAFE
// ======================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ======================
// ✅ DB CHECK
// ======================
if (!isset($conn) || !$conn) {
    die("Database connection missing");
}

// ======================
// 🔐 AUTH CHECK
// ======================
if (!isset($_SESSION['user_id'])) {

    header("Location: /infinity/admin/pages/login.php");
    exit;
}

// ======================
// 🌍 BRANCH FILTER
// ======================
$branch_id = $_SESSION['branch_id'] ?? 1;

// ======================
// SUMMARY STATS
// ======================
$stats = [
    'total_sales' => 0,
    'mpesa' => 0,
    'cash' => 0,
    'invoice' => 0
];

// ======================
// ALL BRANCHES
// ======================
if ($branch_id === 'all') {

    $sql = "
        SELECT 
            SUM(total_amount) as total_sales,

            SUM(
                CASE 
                    WHEN payment_method='mpesa' 
                    THEN total_amount 
                    ELSE 0 
                END
            ) as mpesa,

            SUM(
                CASE 
                    WHEN payment_method='cash' 
                    THEN total_amount 
                    ELSE 0 
                END
            ) as cash,

            SUM(
                CASE 
                    WHEN payment_method='invoice' 
                    THEN total_amount 
                    ELSE 0 
                END
            ) as invoice

        FROM orders
    ";

    $stmt = $conn->prepare($sql);

} else {

    $branch_id = (int)$branch_id;

    $sql = "
        SELECT 
            SUM(total_amount) as total_sales,

            SUM(
                CASE 
                    WHEN payment_method='mpesa' 
                    THEN total_amount 
                    ELSE 0 
                END
            ) as mpesa,

            SUM(
                CASE 
                    WHEN payment_method='cash' 
                    THEN total_amount 
                    ELSE 0 
                END
            ) as cash,

            SUM(
                CASE 
                    WHEN payment_method='invoice' 
                    THEN total_amount 
                    ELSE 0 
                END
            ) as invoice

        FROM orders

        WHERE branch_id = ?
    ";

    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("i", $branch_id);
    }
}

// ======================
// EXECUTE SUMMARY
// ======================
if ($stmt && $stmt->execute()) {

    $result = $stmt->get_result();

    if ($result && $row = $result->fetch_assoc()) {

        $stats = $row;
    }

} else {

    echo "
    <div style='
        background:#fee2e2;
        color:#991b1b;
        padding:15px;
        margin-bottom:20px;
        border-radius:6px;
    '>
        ❌ Failed to load report summary
    </div>
    ";
}

// ======================
// RECENT ORDERS
// ======================
$orders = false;

if ($branch_id === 'all') {

    $orders = $conn->query("
        SELECT 
            id,
            total_amount,
            payment_method,
            payment_status,
            created_at
        FROM orders
        ORDER BY id DESC
        LIMIT 10
    ");

} else {

    $stmt2 = $conn->prepare("
        SELECT 
            id,
            total_amount,
            payment_method,
            payment_status,
            created_at
        FROM orders
        WHERE branch_id = ?
        ORDER BY id DESC
        LIMIT 10
    ");

    if ($stmt2) {

        $stmt2->bind_param("i", $branch_id);
        $stmt2->execute();

        $orders = $stmt2->get_result();
    }
}
?>

<h2>📊 Reports Dashboard</h2>

<div style="display:flex; gap:20px; flex-wrap:wrap;">

    <div style="background:#d1fae5; padding:15px; border-radius:8px; min-width:200px;">
        💰 Total Sales <br>
        <strong>
            KES <?= number_format((float)($stats['total_sales'] ?? 0), 2) ?>
        </strong>
    </div>

    <div style="background:#e0f2fe; padding:15px; border-radius:8px; min-width:200px;">
        📲 MPESA <br>
        <strong>
            KES <?= number_format((float)($stats['mpesa'] ?? 0), 2) ?>
        </strong>
    </div>

    <div style="background:#fef3c7; padding:15px; border-radius:8px; min-width:200px;">
        💵 Cash <br>
        <strong>
            KES <?= number_format((float)($stats['cash'] ?? 0), 2) ?>
        </strong>
    </div>

    <div style="background:#fee2e2; padding:15px; border-radius:8px; min-width:200px;">
        🧾 Invoice <br>
        <strong>
            KES <?= number_format((float)($stats['invoice'] ?? 0), 2) ?>
        </strong>
    </div>

</div>

<h3 style="margin-top:30px;">🕒 Recent Orders</h3>

<table border="1" width="100%" cellpadding="8" cellspacing="0" style="background:white; border-collapse:collapse;">

<tr>
    <th>ID</th>
    <th>Total</th>
    <th>Method</th>
    <th>Status</th>
    <th>Date</th>
</tr>

<?php if ($orders && $orders->num_rows > 0): ?>

    <?php while($o = $orders->fetch_assoc()): ?>

    <tr>

        <td>
            #<?= (int)$o['id'] ?>
        </td>

        <td>
            KES <?= number_format((float)($o['total_amount'] ?? 0), 2) ?>
        </td>

        <td>
            <?= strtoupper(htmlspecialchars($o['payment_method'] ?? '-')) ?>
        </td>

        <td>
            <?= htmlspecialchars($o['payment_status'] ?? '-') ?>
        </td>

        <td>
            <?= htmlspecialchars($o['created_at'] ?? '-') ?>
        </td>

    </tr>

    <?php endwhile; ?>

<?php else: ?>

<tr>
    <td colspan="5">No orders found</td>
</tr>

<?php endif; ?>

</table>

<?php
// ======================
// CLOSE STATEMENTS
// ======================
if (isset($stmt) && $stmt) {
    $stmt->close();
}

if (isset($stmt2) && $stmt2) {
    $stmt2->close();
}
?>