<?php

require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/db.php';

// ==========================
// 🔐 SESSION
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
// 🏢 BRANCH
// ==========================
$branch_id = $_SESSION['branch_id'] ?? 1;

// ==========================
// 💵 CASH IN (SALES)
// ==========================
$cashIn = 0;

$sql = "
SELECT 
    SUM(COALESCE(total,0)) as total
FROM sales
WHERE branch_id = ?
";

$stmt = $conn->prepare($sql);

if ($stmt) {

    $stmt->bind_param("i", $branch_id);

    if ($stmt->execute()) {

        $result = $stmt->get_result();

        if ($result && $row = $result->fetch_assoc()) {

            $cashIn = (float)($row['total'] ?? 0);
        }

    } else {

        echo "
        <div style='color:red;padding:10px;'>
            ❌ Failed to execute sales query
        </div>
        ";
    }

    $stmt->close();

} else {

    echo "
    <div style='color:red;padding:10px;'>
        ❌ Failed to load sales data
    </div>
    ";
}

// ==========================
// 💸 CASH OUT (EXPENSES)
// ==========================
$cashOut = 0;

$sql = "
SELECT 
    SUM(COALESCE(amount,0)) as total
FROM expenses
WHERE branch_id = ?
";

$stmt = $conn->prepare($sql);

if ($stmt) {

    $stmt->bind_param("i", $branch_id);

    if ($stmt->execute()) {

        $result = $stmt->get_result();

        if ($result && $row = $result->fetch_assoc()) {

            $cashOut = (float)($row['total'] ?? 0);
        }

    } else {

        echo "
        <div style='color:red;padding:10px;'>
            ❌ Failed to execute expenses query
        </div>
        ";
    }

    $stmt->close();

} else {

    echo "
    <div style='color:red;padding:10px;'>
        ❌ Failed to load expenses data
    </div>
    ";
}

// ==========================
// 🧮 NET CASH
// ==========================
$netCash = $cashIn - $cashOut;
?>

<h2 style="margin-bottom:20px;">
    💰 Cash Flow Report
</h2>

<div class="cashflow-container">

    <div class="card">
        <h3>💵 Cash In</h3>

        <p class="amount positive">
            KES <?= number_format($cashIn, 2) ?>
        </p>
    </div>

    <div class="card">
        <h3>💸 Cash Out</h3>

        <p class="amount negative">
            KES <?= number_format($cashOut, 2) ?>
        </p>
    </div>

    <div class="card">
        <h3>📊 Net Cash</h3>

        <p class="amount <?= $netCash >= 0 ? 'positive' : 'negative' ?>">
            KES <?= number_format($netCash, 2) ?>
        </p>
    </div>

</div>

<style>

.cashflow-container{
    display:flex;
    gap:20px;
    flex-wrap:wrap;
    margin-top:20px;
}

.card{
    background:white;
    padding:20px;
    border-radius:10px;
    box-shadow:0 0 10px rgba(0,0,0,0.1);
    min-width:220px;
    flex:1;
}

.card h3{
    margin-top:0;
    color:#334155;
}

.amount{
    font-size:24px;
    font-weight:bold;
}

.positive{
    color:green;
}

.negative{
    color:red;
}

</style>