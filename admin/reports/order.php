<?php

// ==========================
// LOAD SYSTEM
// ==========================
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/init.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/db.php';

// ==========================
// 🔐 SESSION SAFE
// ==========================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==========================
// ✅ DB CHECK
// ==========================
if (!isset($conn) || !$conn) {
    die("Database connection missing");
}

// ==========================
// 🔐 AUTH CHECK
// ==========================
if (!isset($_SESSION['user_id'])) {

    header("Location: /infinity/admin/pages/login.php");
    exit;
}

// ==========================
// 🛠 DEBUG (disable later)
// ==========================
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ==========================
// 🌍 BRANCH FILTER
// ==========================
$branch_id = $_SESSION['branch_id'] ?? 1;

// ==========================
// 📊 TOTALS
// ==========================
$totalOrders = 0;
$totalSales  = 0;

// ==========================
// ALL BRANCHES
// ==========================
if ($branch_id === 'all') {

    $sql = "
        SELECT
            COUNT(*) AS total_orders,
            SUM(total_amount) AS total_sales
        FROM orders
    ";

    $stmt = $conn->prepare($sql);

} else {

    $branch_id = (int)$branch_id;

    $sql = "
        SELECT
            COUNT(*) AS total_orders,
            SUM(total_amount) AS total_sales
        FROM orders
        WHERE branch_id = ?
    ";

    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("i", $branch_id);
    }
}

// ==========================
// EXECUTE TOTALS
// ==========================
if ($stmt && $stmt->execute()) {

    $totals = $stmt->get_result();

    if ($totals && $row = $totals->fetch_assoc()) {

        $totalOrders = (int)($row['total_orders'] ?? 0);

        $totalSales = (float)($row['total_sales'] ?? 0);
    }

} else {

    echo "
    <div style='color:red;padding:10px;'>
        ❌ Failed to load totals
    </div>
    ";
}

// ==========================
// 📦 FETCH ORDERS
// ==========================
$orders = [];

// ==========================
// ALL BRANCHES
// ==========================
if ($branch_id === 'all') {

    $sql = "
        SELECT
            id,
            customer_name,
            phone,
            total_amount,
            payment_method,
            status,
            source,
            created_at,
            branch_id
        FROM orders
        ORDER BY created_at DESC
        LIMIT 100
    ";

    $stmt2 = $conn->prepare($sql);

} else {

    $sql = "
        SELECT
            id,
            customer_name,
            phone,
            total_amount,
            payment_method,
            status,
            source,
            created_at
        FROM orders
        WHERE branch_id = ?
        ORDER BY created_at DESC
        LIMIT 100
    ";

    $stmt2 = $conn->prepare($sql);

    if ($stmt2) {
        $stmt2->bind_param("i", $branch_id);
    }
}

// ==========================
// EXECUTE ORDERS
// ==========================
if ($stmt2 && $stmt2->execute()) {

    $result = $stmt2->get_result();

    if ($result && $result->num_rows > 0) {

        while ($row = $result->fetch_assoc()) {

            $orders[] = $row;
        }
    }

} else {

    echo "
    <div style='color:red;padding:10px;'>
        ❌ Failed to load orders
    </div>
    ";
}
?>

<!DOCTYPE html>
<html>

<head>

<title>Order Reports</title>

<style>

body{
    font-family:Arial;
    background:#f4f4f4;
    padding:20px;
}

.container{
    width:95%;
    margin:auto;
}

.summary{
    display:flex;
    gap:20px;
    margin:20px 0;
    flex-wrap:wrap;
}

.card{
    background:white;
    padding:20px;
    flex:1;
    min-width:220px;
    border-radius:10px;
    text-align:center;
    box-shadow:0 2px 5px rgba(0,0,0,0.1);
}

table{
    width:100%;
    border-collapse:collapse;
    background:white;
}

th,
td{
    padding:10px;
    border:1px solid #ddd;
    text-align:center;
}

th{
    background:#333;
    color:white;
}

tr:hover{
    background:#f1f1f1;
}

.empty{
    padding:20px;
    text-align:center;
    color:#64748b;
}

</style>

</head>

<body>

<div class="container">

<h2>
📊 Orders Report
(Branch <?= htmlspecialchars((string)$branch_id) ?>)
</h2>

<!-- ========================== -->
<!-- SUMMARY -->
<!-- ========================== -->

<div class="summary">

<div class="card">

<h3>Total Orders</h3>

<p>
    <?= (int)$totalOrders ?>
</p>

</div>

<div class="card">

<h3>Total Sales (KES)</h3>

<p>
    <?= number_format((float)$totalSales, 2) ?>
</p>

</div>

</div>

<!-- ========================== -->
<!-- TABLE -->
<!-- ========================== -->

<table>

<tr>

<th>ID</th>
<th>Customer</th>
<th>Phone</th>
<th>Amount</th>
<th>Payment</th>
<th>Status</th>
<th>Source</th>
<th>Date</th>

<?php if ($branch_id === 'all'): ?>
<th>Branch</th>
<?php endif; ?>

</tr>

<?php if (!empty($orders)): ?>

    <?php foreach ($orders as $order): ?>

    <tr>

        <td>
            <?= (int)$order['id']; ?>
        </td>

        <td>
            <?= htmlspecialchars($order['customer_name'] ?? '-'); ?>
        </td>

        <td>
            <?= htmlspecialchars($order['phone'] ?? '-'); ?>
        </td>

        <td>
            KES <?= number_format((float)($order['total_amount'] ?? 0), 2); ?>
        </td>

        <td>
            <?= htmlspecialchars($order['payment_method'] ?? '-'); ?>
        </td>

        <td>
            <?= htmlspecialchars($order['status'] ?? '-'); ?>
        </td>

        <td>
            <?= htmlspecialchars($order['source'] ?? '-'); ?>
        </td>

        <td>
            <?= htmlspecialchars($order['created_at'] ?? '-'); ?>
        </td>

        <?php if ($branch_id === 'all'): ?>

        <td>
            <?= htmlspecialchars((string)($order['branch_id'] ?? '-')); ?>
        </td>

        <?php endif; ?>

    </tr>

    <?php endforeach; ?>

<?php else: ?>

<tr>

<td
    colspan="<?= ($branch_id === 'all') ? 9 : 8 ?>"
    class="empty"
>
    No orders found
</td>

</tr>

<?php endif; ?>

</table>

</div>

</body>

</html>

<?php

// ==========================
// CLOSE STATEMENTS
// ==========================
if (isset($stmt) && $stmt) {
    $stmt->close();
}

if (isset($stmt2) && $stmt2) {
    $stmt2->close();
}

?>