<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/db.php';

echo "<h2>🧮 Accounting Dashboard</h2>";

$sales = 0;
$expenses = 0;

$res = $conn->query("SELECT SUM(total) as total_sales FROM sales");

if($res && $row = $res->fetch_assoc()){
    $sales = $row['total_sales'] ?? 0;
}

$res = $conn->query("SELECT SUM(amount) as total_expenses FROM expenses");

if($res && $row = $res->fetch_assoc()){
    $expenses = $row['total_expenses'] ?? 0;
}

$profit = $sales - $expenses;
?>

<div class="stats">

    <div class="card">
        <h3>💵 Sales</h3>
        <p>Ksh <?= number_format($sales,2) ?></p>
    </div>

    <div class="card">
        <h3>💸 Expenses</h3>
        <p>Ksh <?= number_format($expenses,2) ?></p>
    </div>

    <div class="card">
        <h3>📈 Profit</h3>
        <p>Ksh <?= number_format($profit,2) ?></p>
    </div>

</div>

<style>
.stats{
    display:flex;
    gap:20px;
    flex-wrap:wrap;
}
.card{
    background:white;
    padding:20px;
    border-radius:8px;
    box-shadow:0 0 10px #ccc;
    min-width:250px;
}
.card p{
    font-size:24px;
    color:#007bff;
    font-weight:bold;
}
</style>