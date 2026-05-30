<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/init.php';

// ==========================
// 🏢 BRANCH
// ==========================
$branch_id = $_SESSION['branch_id'] ?? 1;

// ==========================
// 📊 KPI QUERIES (FILTERED)
// ==========================

// TOTAL SALES
$stmt = $conn->prepare("
    SELECT SUM(total_amount) as total 
    FROM orders 
    WHERE branch_id = ?
");
$stmt->bind_param("i", $branch_id);
$stmt->execute();
$total_sales = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

// TOTAL ORDERS
$stmt = $conn->prepare("
    SELECT COUNT(*) as total 
    FROM orders 
    WHERE branch_id = ?
");
$stmt->bind_param("i", $branch_id);
$stmt->execute();
$total_orders = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

// PENDING
$stmt = $conn->prepare("
    SELECT COUNT(*) as total 
    FROM orders 
    WHERE status='Pending' AND branch_id = ?
");
$stmt->bind_param("i", $branch_id);
$stmt->execute();
$pending = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

// REVENUE
$stmt = $conn->prepare("
    SELECT SUM(total_amount) as total 
    FROM orders 
    WHERE payment_status='paid' AND branch_id = ?
");
$stmt->bind_param("i", $branch_id);
$stmt->execute();
$revenue = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

// CUSTOMERS COUNT
$stmt = $conn->prepare("
    SELECT COUNT(*) as total 
    FROM customers 
    WHERE branch_id = ?
");
$stmt->bind_param("i", $branch_id);
$stmt->execute();
$customers = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

// LOW STOCK
$stmt = $conn->prepare("
    SELECT COUNT(*) as total 
    FROM products 
    WHERE stock < 5 AND branch_id = ?
");
$stmt->bind_param("i", $branch_id);
$stmt->execute();
$low_stock = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
?>

<h2>📊 Dashboard Analytics</h2>

<div style="display:flex; gap:15px; flex-wrap:wrap;">

<div class="card">💰 Sales<br><b>KES <?= number_format($total_sales,2) ?></b></div>

<div class="card">📦 Orders<br><b><?= $total_orders ?></b></div>

<div class="card">⏳ Pending<br><b style="color:orange"><?= $pending ?></b></div>

<div class="card">💵 Revenue<br><b style="color:green">KES <?= number_format($revenue,2) ?></b></div>

<div class="card">👥 Customers<br><b><?= $customers ?></b></div>

<div class="card">⚠️ Low Stock<br><b style="color:red"><?= $low_stock ?></b></div>

</div>

<style>
.card {
    background:white;
    padding:20px;
    border-radius:8px;
    box-shadow:0 0 10px #ccc;
    min-width:150px;
}
</style>