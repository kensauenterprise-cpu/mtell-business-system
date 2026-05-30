<?php

// =====================================
// FILE: analytics.php
// =====================================

// ==========================
// ?? SESSION SAFE
// ==========================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==========================
// ? USE EXISTING CONNECTION
// ==========================
global $conn;

// ==========================
// ? LOAD INIT ONLY IF NEEDED
// ==========================
if (
    !isset($conn) ||
    !($conn instanceof mysqli)
) {

    require_once $_SERVER['DOCUMENT_ROOT']
    .'/infinity/admin/includes/init.php';
}

// ==========================
// ? FINAL DB CHECK
// ==========================
if (
    !isset($conn) ||
    !($conn instanceof mysqli) ||
    $conn->connect_error
) {

    die("
    <div style='
        background:#fff0f0;
        color:red;
        padding:15px;
        border:1px solid red;
        border-radius:6px;
        margin:10px 0;
    '>
        ? Database connection missing
    </div>
    ");
}

// ==========================
// ?? BRANCH
// ==========================
$branch_id = $_SESSION['branch_id'] ?? 1;

// ==========================
// ?? TOTAL ORDERS
// ==========================
$totalOrders = 0;

if ($branch_id === 'all') {

    $stmt = $conn->prepare("
        SELECT COUNT(*) AS total
        FROM orders
    ");

} else {

    $stmt = $conn->prepare("
        SELECT COUNT(*) AS total
        FROM orders
        WHERE branch_id = ?
    ");

    $stmt->bind_param("i", $branch_id);
}

if ($stmt && $stmt->execute()) {

    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {

        $totalOrders = (int)$row['total'];
    }
}

if ($stmt) {
    $stmt->close();
}

// ==========================
// ?? TOTAL SALES
// ==========================
$totalSales = 0;

if ($branch_id === 'all') {

    $stmt = $conn->prepare("
        SELECT SUM(total) AS total
        FROM sales
    ");

} else {

    $stmt = $conn->prepare("
        SELECT SUM(total) AS total
        FROM sales
        WHERE branch_id = ?
    ");

    $stmt->bind_param("i", $branch_id);
}

if ($stmt && $stmt->execute()) {

    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {

        $totalSales = (float)($row['total'] ?? 0);
    }
}

if ($stmt) {
    $stmt->close();
}

// ==========================
// ?? TOTAL PRODUCTS
// ==========================
$totalProducts = 0;

if ($branch_id === 'all') {

    $stmt = $conn->prepare("
        SELECT COUNT(*) AS total
        FROM products
    ");

} else {

    $stmt = $conn->prepare("
        SELECT COUNT(*) AS total
        FROM products
        WHERE branch_id = ?
    ");

    $stmt->bind_param("i", $branch_id);
}

if ($stmt && $stmt->execute()) {

    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {

        $totalProducts = (int)$row['total'];
    }
}

if ($stmt) {
    $stmt->close();
}

// ==========================
// ?? TOTAL CUSTOMERS
// ==========================
$totalCustomers = 0;

if ($branch_id === 'all') {

    $stmt = $conn->prepare("
        SELECT COUNT(*) AS total
        FROM customers
    ");

} else {

    $stmt = $conn->prepare("
        SELECT COUNT(*) AS total
        FROM customers
        WHERE branch_id = ?
    ");

    $stmt->bind_param("i", $branch_id);
}

if ($stmt && $stmt->execute()) {

    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {

        $totalCustomers = (int)$row['total'];
    }
}

if ($stmt) {
    $stmt->close();
}

?>

<div class="analytics-container">

<h2>?? Analytics Dashboard</h2>

<div class="cards">

    <div class="card">
        <h3>?? Orders</h3>
        <p><?= number_format($totalOrders) ?></p>
    </div>

    <div class="card">
        <h3>?? Sales</h3>
        <p>KES <?= number_format($totalSales, 2) ?></p>
    </div>

    <div class="card">
        <h3>?? Products</h3>
        <p><?= number_format($totalProducts) ?></p>
    </div>

    <div class="card">
        <h3>?? Customers</h3>
        <p><?= number_format($totalCustomers) ?></p>
    </div>

</div>

</div>

<style>

.analytics-container{
    padding:20px;
}

.cards{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:20px;
}

.card{
    background:white;
    padding:20px;
    border-radius:10px;
    box-shadow:0 0 10px rgba(0,0,0,0.1);
}

.card h3{
    margin-top:0;
}

.card p{
    font-size:28px;
    font-weight:bold;
    color:#2563eb;
}

</style>