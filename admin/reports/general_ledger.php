<?php

// === Safe session start ===
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// === Error logging setup ===
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ==========================
// LOAD DB
// ==========================
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/db.php';

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
// 🌍 BRANCH FILTER
// ==========================
$branch_id = $_SESSION['branch_id'] ?? 1;

// ==========================
// ✅ QUERY
// ==========================
$result = false;
$stmt = null;

if ($branch_id === 'all') {

    $sql = "
    SELECT
        je.entry_date,
        je.description,
        a.account_code,
        a.account_name,
        COALESCE(je.debit,0) AS debit,
        COALESCE(je.credit,0) AS credit
    FROM journal_entries je
    INNER JOIN chart_of_accounts a
        ON je.account_id = a.id
    ORDER BY je.entry_date ASC, je.id ASC
    ";

    $stmt = $conn->prepare($sql);

} else {

    $branch_id = (int)$branch_id;

    $sql = "
    SELECT
        je.entry_date,
        je.description,
        a.account_code,
        a.account_name,
        COALESCE(je.debit,0) AS debit,
        COALESCE(je.credit,0) AS credit
    FROM journal_entries je
    INNER JOIN chart_of_accounts a
        ON je.account_id = a.id
    WHERE je.branch_id = ?
    ORDER BY je.entry_date ASC, je.id ASC
    ";

    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("i", $branch_id);
    }
}

// ==========================
// EXECUTE
// ==========================
if ($stmt) {

    if ($stmt->execute()) {

        $result = $stmt->get_result();

    } else {

        echo "
        <div style='
            background:#fee2e2;
            color:#991b1b;
            padding:15px;
            margin:15px 0;
            border-radius:6px;
        '>
            ❌ Failed to execute query:
            <br>
            ".htmlspecialchars($stmt->error)."
        </div>
        ";
    }

} else {

    echo "
    <div style='
        background:#fee2e2;
        color:#991b1b;
        padding:15px;
        margin:15px 0;
        border-radius:6px;
    '>
        ❌ Failed to prepare SQL query
    </div>
    ";
}
?>

<!DOCTYPE html>
<html>

<head>

<title>General Ledger</title>

<style>

body{
    font-family:Arial;
    background:#f4f4f4;
    padding:20px;
}

table{
    width:100%;
    border-collapse:collapse;
    background:white;
}

th,
td{
    border:1px solid #ddd;
    padding:10px;
    text-align:left;
}

th{
    background:#0f172a;
    color:white;
}

.debit{
    color:red;
    font-weight:bold;
}

.credit{
    color:green;
    font-weight:bold;
}

.container{
    background:white;
    padding:20px;
    border-radius:10px;
    box-shadow:0 0 10px rgba(0,0,0,0.1);
}

h2{
    margin-bottom:20px;
}

.no-data{
    text-align:center;
    color:#64748b;
    padding:20px;
}

</style>

</head>

<body>

<div class="container">

<h2>
📒 General Ledger
(Branch <?= htmlspecialchars((string)$branch_id) ?>)
</h2>

<table>

<tr>
    <th>Date</th>
    <th>Description</th>
    <th>Account Code</th>
    <th>Account Name</th>
    <th>Debit (KES)</th>
    <th>Credit (KES)</th>
</tr>

<?php if ($result && $result->num_rows > 0): ?>

    <?php while ($row = $result->fetch_assoc()): ?>

    <tr>

        <td>
            <?= htmlspecialchars($row['entry_date'] ?? '-') ?>
        </td>

        <td>
            <?= htmlspecialchars($row['description'] ?? '-') ?>
        </td>

        <td>
            <?= htmlspecialchars($row['account_code'] ?? '-') ?>
        </td>

        <td>
            <?= htmlspecialchars($row['account_name'] ?? '-') ?>
        </td>

        <td class="debit">
            KES <?= number_format((float)($row['debit'] ?? 0), 2) ?>
        </td>

        <td class="credit">
            KES <?= number_format((float)($row['credit'] ?? 0), 2) ?>
        </td>

    </tr>

    <?php endwhile; ?>

<?php else: ?>

<tr>

<td colspan="6" class="no-data">
    No journal entries found
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