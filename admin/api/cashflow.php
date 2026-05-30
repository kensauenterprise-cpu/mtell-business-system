<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/init.php';

// ==========================
// 📅 DAYS (SAFE)
// ==========================
$days = isset($_GET['days']) ? (int)$_GET['days'] : 7;
if ($days <= 0) $days = 7;

// ==========================
// 📅 DATE RANGE
// ==========================
$dates = [];
for ($i = $days - 1; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $dates[$d] = [
        "income" => 0,
        "expense" => 0
    ];
}

// ==========================
// 💰 SALES (INCOME)
// ==========================
$sales = $conn->query("
SELECT DATE(created_at) as date, SUM(total) as income
FROM orders
WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL $days DAY)
GROUP BY DATE(created_at)
");

while ($row = $sales->fetch_assoc()) {
    $dates[$row['date']]['income'] = (float)$row['income'];
}

// ==========================
// 💸 EXPENSES
// ==========================
$expenses = $conn->query("
SELECT DATE(created_at) as date, SUM(amount) as expense
FROM expenses
WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL $days DAY)
GROUP BY DATE(created_at)
");

while ($row = $expenses->fetch_assoc()) {
    $dates[$row['date']]['expense'] = (float)$row['expense'];
}

// ==========================
// 🧮 RUNNING BALANCE
// ==========================
$balance = 0;
$result = [];

foreach ($dates as $date => $row) {

    $income = $row['income'];
    $expense = $row['expense'];

    $balance += ($income - $expense);

    $result[] = [
        "date" => $date,
        "income" => round($income, 2),
        "expense" => round($expense, 2),
        "balance" => round($balance, 2)
    ];
}

// ==========================
// 📤 OUTPUT
// ==========================
header('Content-Type: application/json');
echo json_encode($result);