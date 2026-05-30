<?php

// ==========================
// 🔐 SESSION
// ==========================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==========================
// 🔐 AUTH CHECK
// ==========================
if (!isset($_SESSION['user_id'])) {
    header("Location: /infinity/admin/pages/login.php");
    exit;
}

// ==========================
// DB (USE EXISTING CONNECTION)
// ==========================
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/db.php';

// ==========================
// ✅ DB CHECK
// ==========================
if (!isset($conn) || !$conn) {
    die("Database connection missing");
}

// ==========================
// 🌍 BRANCH FILTER
// ==========================
$branch_id = $_SESSION['branch_id'] ?? 1;

// ==========================
// 📊 PROFIT & LOSS QUERY
// ==========================
$result = false;

if ($branch_id === 'all') {

    $sql = "
    SELECT 
        DATE_FORMAT(je.entry_date, '%Y-%m') AS month,

        SUM(CASE 
            WHEN a.account_code IN ('4000','4100') 
            THEN COALESCE(je.credit,0) - COALESCE(je.debit,0)
            ELSE 0 
        END) AS total_revenue,

        SUM(CASE 
            WHEN a.account_code = '5000' 
            THEN COALESCE(je.debit,0) - COALESCE(je.credit,0)
            ELSE 0 
        END) AS total_cogs,

        SUM(CASE 
            WHEN a.account_code LIKE '61%' 
            THEN COALESCE(je.debit,0) - COALESCE(je.credit,0)
            ELSE 0 
        END) AS total_expenses

    FROM journal_entries je

    INNER JOIN chart_of_accounts a 
        ON je.account_id = a.id

    GROUP BY month

    ORDER BY month DESC
    ";

    $stmt = $conn->prepare($sql);

} else {

    $branch_id = (int)$branch_id;

    $sql = "
    SELECT 
        DATE_FORMAT(je.entry_date, '%Y-%m') AS month,

        SUM(CASE 
            WHEN a.account_code IN ('4000','4100') 
            THEN COALESCE(je.credit,0) - COALESCE(je.debit,0)
            ELSE 0 
        END) AS total_revenue,

        SUM(CASE 
            WHEN a.account_code = '5000' 
            THEN COALESCE(je.debit,0) - COALESCE(je.credit,0)
            ELSE 0 
        END) AS total_cogs,

        SUM(CASE 
            WHEN a.account_code LIKE '61%' 
            THEN COALESCE(je.debit,0) - COALESCE(je.credit,0)
            ELSE 0 
        END) AS total_expenses

    FROM journal_entries je

    INNER JOIN chart_of_accounts a 
        ON je.account_id = a.id

    WHERE je.branch_id = ?

    GROUP BY month

    ORDER BY month DESC
    ";

    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("i", $branch_id);
    }
}

// ==========================
// EXECUTE QUERY
// ==========================
if ($stmt && $stmt->execute()) {

    $result = $stmt->get_result();

} else {

    echo "
    <div style='
        background:#fee2e2;
        color:#991b1b;
        padding:15px;
        margin-bottom:20px;
        border-radius:6px;
    '>
        ❌ Failed to load Profit & Loss report
    </div>
    ";
}
?>

<!DOCTYPE html>
<html>

<head>

<title>Profit & Loss Report</title>

<style>

body{
    font-family:Arial;
    padding:20px;
    background:#f4f4f4;
}

.card{
    background:white;
    padding:15px;
    margin-bottom:20px;
    border-radius:8px;
    box-shadow:0 0 10px rgba(0,0,0,0.1);
}

table{
    width:100%;
    border-collapse:collapse;
    background:white;
}

th,
td{
    padding:10px;
    border:1px solid #ccc;
    text-align:center;
}

th{
    background:#333;
    color:white;
}

.profit{
    color:green;
    font-weight:bold;
}

.loss{
    color:red;
    font-weight:bold;
}

.empty{
    color:#64748b;
    padding:20px;
}

</style>

</head>

<body>

<h2>
📊 Profit & Loss Report
(Branch <?= htmlspecialchars((string)$branch_id) ?>)
</h2>

<div class="card">

<table>

<tr>

<th>Month</th>
<th>Revenue</th>
<th>COGS</th>
<th>Gross Profit</th>
<th>Expenses</th>
<th>Net Profit</th>

</tr>

<?php if ($result && $result->num_rows > 0): ?>

<?php while ($row = $result->fetch_assoc()): 

    $revenue  = (float)($row['total_revenue'] ?? 0);

    $cogs     = (float)($row['total_cogs'] ?? 0);

    $expenses = (float)($row['total_expenses'] ?? 0);

    $gross = $revenue - $cogs;

    $net   = $gross - $expenses;

?>

<tr>

<td>
    <?= htmlspecialchars($row['month'] ?? '-') ?>
</td>

<td>
    KES <?= number_format($revenue, 2) ?>
</td>

<td>
    KES <?= number_format($cogs, 2) ?>
</td>

<td>
    KES <?= number_format($gross, 2) ?>
</td>

<td>
    KES <?= number_format($expenses, 2) ?>
</td>

<td class="<?= $net >= 0 ? 'profit' : 'loss' ?>">

    KES <?= number_format($net, 2) ?>

</td>

</tr>

<?php endwhile; ?>

<?php else: ?>

<tr>

<td colspan="6" class="empty">

    No Profit & Loss records found

</td>

</tr>

<?php endif; ?>

</table>

</div>

</body>

</html>

<?php

// ==========================
// CLOSE STATEMENT
// ==========================
if (isset($stmt) && $stmt) {
    $stmt->close();
}

?>