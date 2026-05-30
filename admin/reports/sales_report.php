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
// 🔐 PROTECT PAGE
// ==========================
if (!isset($_SESSION['user_id'])) {

    header("Location: /infinity/admin/pages/login.php");
    exit;
}

// ==========================
// 🌍 BRANCH FILTER
// ==========================
$branch_id = $_SESSION['branch_id'] ?? 1;

// ==========================
// ✅ SAFE BRANCH SQL
// ==========================
$where = "";
$params = [];
$types  = "";

if ($branch_id !== 'all') {

    $branch_id = (int)$branch_id;

    $where = " WHERE branch_id = ? ";

    $params[] = $branch_id;

    $types .= "i";
}

// ==========================
// 💰 TOTAL SALES
// ==========================
$totalSales = 0;

$sql = "
    SELECT SUM(total_amount) AS total
    FROM orders
    $where
";

$stmt = $conn->prepare($sql);

if ($stmt) {

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();

    $res = $stmt->get_result();

    if ($res && $row = $res->fetch_assoc()) {

        $totalSales = $row['total'] ?? 0;
    }

    $stmt->close();
}

// ==========================
// 📅 TODAY SALES
// ==========================
$todaySales = 0;

$sql = "
    SELECT SUM(total_amount) AS total
    FROM orders
    $where
    " . ($where ? "AND" : "WHERE") . "
    DATE(created_at)=CURDATE()
";

$stmt = $conn->prepare($sql);

if ($stmt) {

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();

    $res = $stmt->get_result();

    if ($res && $row = $res->fetch_assoc()) {

        $todaySales = $row['total'] ?? 0;
    }

    $stmt->close();
}

// ==========================
// 📆 MONTH SALES
// ==========================
$monthSales = 0;

$sql = "
    SELECT SUM(total_amount) AS total
    FROM orders
    $where
    " . ($where ? "AND" : "WHERE") . "
    MONTH(created_at)=MONTH(CURDATE())
    AND YEAR(created_at)=YEAR(CURDATE())
";

$stmt = $conn->prepare($sql);

if ($stmt) {

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();

    $res = $stmt->get_result();

    if ($res && $row = $res->fetch_assoc()) {

        $monthSales = $row['total'] ?? 0;
    }

    $stmt->close();
}

// ==========================
// 📊 ORDER STATUS
// ==========================
$paidOrders = 0;
$pendingOrders = 0;

// ==========================
// PAID
// ==========================
$sql = "
    SELECT COUNT(*) AS total
    FROM orders
    $where
    " . ($where ? "AND" : "WHERE") . "
    status='paid'
";

$stmt = $conn->prepare($sql);

if ($stmt) {

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();

    $res = $stmt->get_result();

    if ($res && $row = $res->fetch_assoc()) {

        $paidOrders = $row['total'] ?? 0;
    }

    $stmt->close();
}

// ==========================
// PENDING
// ==========================
$sql = "
    SELECT COUNT(*) AS total
    FROM orders
    $where
    " . ($where ? "AND" : "WHERE") . "
    status='pending'
";

$stmt = $conn->prepare($sql);

if ($stmt) {

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();

    $res = $stmt->get_result();

    if ($res && $row = $res->fetch_assoc()) {

        $pendingOrders = $row['total'] ?? 0;
    }

    $stmt->close();
}

// ==========================
// 📊 SALES BY SOURCE
// ==========================
$sourceLabels = [];
$sourceData = [];

$sql = "
    SELECT source, SUM(total_amount) AS total
    FROM orders
    $where
    GROUP BY source
";

$stmt = $conn->prepare($sql);

if ($stmt) {

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();

    $res = $stmt->get_result();

    if ($res) {

        while ($row = $res->fetch_assoc()) {

            $sourceLabels[] = $row['source'] ?? 'Unknown';

            $sourceData[] = (float)($row['total'] ?? 0);
        }
    }

    $stmt->close();
}

// ==========================
// 📈 DAILY SALES
// ==========================
$dates = [];
$sales = [];

$sql = "
    SELECT DATE(created_at) AS day,
           SUM(total_amount) AS total
    FROM orders
    $where
    GROUP BY day
    ORDER BY day ASC
";

$stmt = $conn->prepare($sql);

if ($stmt) {

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();

    $res = $stmt->get_result();

    if ($res) {

        while ($row = $res->fetch_assoc()) {

            $dates[] = $row['day'];

            $sales[] = (float)($row['total'] ?? 0);
        }
    }

    $stmt->close();
}

// ==========================
// 🧾 RECENT ORDERS
// ==========================
$sql = "
    SELECT *
    FROM orders
    $where
    ORDER BY id DESC
    LIMIT 10
";

$stmt = $conn->prepare($sql);

$recent = false;

if ($stmt) {

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();

    $recent = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html>

<head>

<title>Sales Report</title>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>

body{
    font-family:Arial;
    padding:20px;
}

.card{
    padding:15px;
    border:1px solid #ccc;
    border-radius:8px;
    background:white;
    min-width:180px;
}

table{
    width:100%;
    border-collapse:collapse;
    background:white;
}

th, td{
    padding:10px;
    border:1px solid #ccc;
    text-align:left;
}

canvas{
    background:white;
    padding:10px;
    border-radius:10px;
}

</style>

</head>

<body>

<h2>
📊 Sales Report
(Branch <?= htmlspecialchars((string)$branch_id) ?>)
</h2>

<!-- SUMMARY -->
<div style="display:flex;flex-wrap:wrap;gap:10px;">

<div class="card">
<b>💰 Total Sales</b><br>
Ksh <?= number_format((float)$totalSales,2) ?>
</div>

<div class="card">
<b>📅 Today</b><br>
Ksh <?= number_format((float)$todaySales,2) ?>
</div>

<div class="card">
<b>📆 This Month</b><br>
Ksh <?= number_format((float)$monthSales,2) ?>
</div>

<div class="card">
<b>✅ Paid Orders</b><br>
<?= (int)$paidOrders ?>
</div>

<div class="card">
<b>⏳ Pending Orders</b><br>
<?= (int)$pendingOrders ?>
</div>

</div>

<hr>

<!-- CHARTS -->
<h3>📊 Sales by Source</h3>

<canvas id="sourceChart"></canvas>

<br><br>

<h3>📈 Daily Sales</h3>

<canvas id="dailyChart"></canvas>

<script>

// ==========================
// SOURCE CHART
// ==========================
new Chart(document.getElementById('sourceChart'), {

    type: 'pie',

    data: {

        labels: <?= json_encode($sourceLabels) ?>,

        datasets: [{
            data: <?= json_encode($sourceData) ?>
        }]
    }
});

// ==========================
// DAILY SALES
// ==========================
new Chart(document.getElementById('dailyChart'), {

    type: 'line',

    data: {

        labels: <?= json_encode($dates) ?>,

        datasets: [{

            label: 'Sales',

            data: <?= json_encode($sales) ?>

        }]
    }
});

</script>

<hr>

<!-- RECENT ORDERS -->
<h3>🧾 Recent Orders</h3>

<table>

<tr>

<th>ID</th>
<th>Total</th>
<th>Status</th>
<th>Source</th>
<th>Date</th>

</tr>

<?php if ($recent && $recent->num_rows > 0): ?>

    <?php while ($row = $recent->fetch_assoc()): ?>

    <tr>

        <td>
            <?= (int)$row['id'] ?>
        </td>

        <td>
            Ksh <?= number_format((float)($row['total_amount'] ?? 0),2) ?>
        </td>

        <td>
            <?= htmlspecialchars($row['status'] ?? '-') ?>
        </td>

        <td>
            <?= htmlspecialchars($row['source'] ?? '-') ?>
        </td>

        <td>
            <?= htmlspecialchars($row['created_at'] ?? '-') ?>
        </td>

    </tr>

    <?php endwhile; ?>

<?php else: ?>

<tr>

<td colspan="5">

    No data found

</td>

</tr>

<?php endif; ?>

</table>

</body>

</html>

<?php

// ==========================
// CLOSE FINAL STATEMENT
// ==========================
if (isset($stmt) && $stmt) {
    $stmt->close();
}

?>