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
// DB (USE EXISTING)
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
// 📊 QUERY
// ==========================
$result = false;

if ($branch_id === 'all') {

    $sql = "
    SELECT 
        a.account_code,
        a.account_name,
        SUM(COALESCE(je.debit,0)) AS total_debit,
        SUM(COALESCE(je.credit,0)) AS total_credit
    FROM journal_entries je

    INNER JOIN chart_of_accounts a 
        ON je.account_id = a.id

    GROUP BY a.account_code, a.account_name

    ORDER BY a.account_code
    ";

    $stmt = $conn->prepare($sql);

} else {

    $branch_id = (int)$branch_id;

    $sql = "
    SELECT 
        a.account_code,
        a.account_name,
        SUM(COALESCE(je.debit,0)) AS total_debit,
        SUM(COALESCE(je.credit,0)) AS total_credit
    FROM journal_entries je

    INNER JOIN chart_of_accounts a 
        ON je.account_id = a.id

    WHERE je.branch_id = ?

    GROUP BY a.account_code, a.account_name

    ORDER BY a.account_code
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
        ❌ Failed to load Trial Balance
    </div>
    ";
}

// ==========================
// TOTALS
// ==========================
$totalDebit = 0;
$totalCredit = 0;

?>

<!DOCTYPE html>
<html>

<head>

<title>Trial Balance</title>

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

.total-row{
    font-weight:bold;
    background:#f0f0f0;
}

.ok{
    color:green;
    font-weight:bold;
}

.error{
    color:red;
    font-weight:bold;
}

</style>

</head>

<body>

<h2>
📑 Trial Balance
(Branch <?= htmlspecialchars((string)$branch_id) ?>)
</h2>

<div class="card">

<table>

<tr>

<th>Account Code</th>
<th>Account Name</th>
<th>Debit (KES)</th>
<th>Credit (KES)</th>

</tr>

<?php if ($result && $result->num_rows > 0): ?>

<?php while ($row = $result->fetch_assoc()): 

    $debit  = (float)($row['total_debit'] ?? 0);

    $credit = (float)($row['total_credit'] ?? 0);

    $totalDebit  += $debit;

    $totalCredit += $credit;

?>

<tr>

<td>
    <?= htmlspecialchars($row['account_code'] ?? '-') ?>
</td>

<td>
    <?= htmlspecialchars($row['account_name'] ?? '-') ?>
</td>

<td>
    KES <?= number_format($debit, 2) ?>
</td>

<td>
    KES <?= number_format($credit, 2) ?>
</td>

</tr>

<?php endwhile; ?>

<?php else: ?>

<tr>

<td colspan="4">

    No data found

</td>

</tr>

<?php endif; ?>

<!-- TOTAL ROW -->

<tr class="total-row">

<td colspan="2">

    TOTAL

</td>

<td>

    KES <?= number_format($totalDebit, 2) ?>

</td>

<td>

    KES <?= number_format($totalCredit, 2) ?>

</td>

</tr>

</table>

</div>

<?php

// ==========================
// ⚖️ BALANCE CHECK (SAFE)
// ==========================
$difference = abs($totalDebit - $totalCredit);

if ($difference < 0.01) {

    echo "
    <p class='ok'>
        ✅ Trial Balance is balanced.
    </p>
    ";

} else {

    echo "
    <p class='error'>
        ❌ Not balanced (Difference: KES "
        . number_format($difference, 2) .
        ")
    </p>
    ";
}
?>

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