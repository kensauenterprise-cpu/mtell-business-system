<?php
// ==========================
// 🔐 LOAD DB
// ==========================
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/db.php';

// ==========================
// 🔐 SESSION
// ==========================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==========================
// 🏢 BRANCH (SECURE)
// ==========================
$branch_id = $_SESSION['branch_id'] ?? 1;

// ==========================
// 🔍 FILTERS
// ==========================
$method = $_GET['method'] ?? '';
$from   = $_GET['from'] ?? '';
$to     = $_GET['to'] ?? '';

// ==========================
// 🧠 BASE QUERY (JOIN ORDERS)
// ==========================
$sql = "
    SELECT 
        t.id,
        t.order_id,
        t.payment_method,
        t.amount,
        t.status,
        t.created_at
    FROM transactions t
    JOIN orders o ON o.id = t.order_id
    WHERE o.branch_id = ?
";

$params = [$branch_id];
$types  = "i";

// ==========================
// ➕ FILTERS
// ==========================
if (!empty($method)) {
    $sql .= " AND t.payment_method = ?";
    $params[] = $method;
    $types .= "s";
}

if (!empty($from)) {
    $sql .= " AND DATE(t.created_at) >= ?";
    $params[] = $from;
    $types .= "s";
}

if (!empty($to)) {
    $sql .= " AND DATE(t.created_at) <= ?";
    $params[] = $to;
    $types .= "s";
}

$sql .= " ORDER BY t.created_at DESC";

// ==========================
// 🚀 EXECUTE
// ==========================
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode([]);
    exit;
}

$stmt->bind_param($types, ...$params);
$stmt->execute();

$result = $stmt->get_result();

// ==========================
// 📦 OUTPUT
// ==========================
$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = [
        "id"       => $row['id'],
        "order_id" => $row['order_id'],
        "method"   => $row['payment_method'],
        "amount"   => $row['amount'],
        "status"   => $row['status'],
        "date"     => $row['created_at']
    ];
}

header('Content-Type: application/json');
echo json_encode($data);