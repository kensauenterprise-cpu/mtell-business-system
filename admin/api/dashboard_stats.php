<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/db.php';

// ==========================
// 📅 RANGE HANDLING
// ==========================
$range = $_GET['range'] ?? '7';
$days = ($range == '30') ? 30 : (($range == '1') ? 1 : 7);

$labels = [];
$sales = [];
$expenses = [];

// ==========================
// 📊 SALES + EXPENSES (PER DAY)
// ==========================
for ($i = $days - 1; $i >= 0; $i--) {

    $date = date('Y-m-d', strtotime("-$i days"));

    // SALES
    $s = $conn->query("
        SELECT SUM(total_amount) as total 
        FROM orders 
        WHERE DATE(created_at) = '$date'
    ")->fetch_assoc();

    // EXPENSES
    $e = $conn->query("
        SELECT SUM(amount) as total 
        FROM expenses 
        WHERE DATE(created_at) = '$date'
    ")->fetch_assoc();

    $labels[]   = date('d M', strtotime($date));
    $sales[]    = (float) ($s['total'] ?? 0);
    $expenses[] = (float) ($e['total'] ?? 0);
}

// ==========================
// 💰 TOTAL PROFIT
// ==========================
$totalSales = $conn->query("
    SELECT SUM(total_amount) as total FROM orders
")->fetch_assoc()['total'] ?? 0;

$totalExpenses = $conn->query("
    SELECT SUM(amount) as total FROM expenses
")->fetch_assoc()['total'] ?? 0;

$profit = (float)$totalSales - (float)$totalExpenses;


// ==========================
// 📈 GROWTH CALCULATION
// ==========================
$currentTotal = array_sum($sales);

// previous period (same number of days before)
$prevSales = [];

for ($i = ($days * 2) - 1; $i >= $days; $i--) {

    $date = date('Y-m-d', strtotime("-$i days"));

    $s = $conn->query("
        SELECT SUM(total_amount) as total 
        FROM orders 
        WHERE DATE(created_at) = '$date'
    ")->fetch_assoc();

    $prevSales[] = (float) ($s['total'] ?? 0);
}

$previousTotal = array_sum($prevSales);

// avoid division by zero
$growth = 0;
if ($previousTotal > 0) {
    $growth = (($currentTotal - $previousTotal) / $previousTotal) * 100;
}


// ==========================
// 💳 PAYMENT METHODS (MPESA vs CASH)
// ==========================
$payments = $conn->query("
    SELECT payment_method, SUM(total_amount) as total 
    FROM orders 
    WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL $days DAY)
    GROUP BY payment_method
");

$paymentLabels = [];
$paymentData   = [];

while ($p = $payments->fetch_assoc()) {
    $paymentLabels[] = strtoupper($p['payment_method'] ?? 'UNKNOWN');
    $paymentData[]   = (float) $p['total'];
}


// ==========================
// ⚠️ LOW STOCK ALERTS
// ==========================
$lowStock = $conn->query("
    SELECT name, stock 
    FROM products 
    WHERE stock < 5
    LIMIT 5
");

$alerts = [];

while ($p = $lowStock->fetch_assoc()) {
    $alerts[] = "⚠️ ".$p['name']." (".$p['stock']." left)";
}


// ==========================
// 📦 PENDING ORDERS
// ==========================
$pending = $conn->query("
    SELECT COUNT(*) as total 
    FROM orders 
    WHERE status = 'pending'
")->fetch_assoc()['total'] ?? 0;


// ==========================
// 📤 FINAL JSON RESPONSE
// ==========================
header('Content-Type: application/json');

echo json_encode([
    'labels' => $labels,
    'sales' => $sales,
    'expenses' => $expenses,
    'profit' => (float)$profit,
    'growth' => round($growth, 2),

    'payments' => [
        'labels' => $paymentLabels,
        'data' => $paymentData
    ],

    'alerts' => $alerts,
    'pending_orders' => (int)$pending
]);