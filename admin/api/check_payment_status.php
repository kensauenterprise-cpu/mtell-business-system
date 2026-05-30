<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/init.php';

// ======================
// SET JSON HEADER
// ======================
header('Content-Type: application/json');

// ======================
// GET ORDER ID
// ======================
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if($order_id <= 0){
    echo json_encode(['status' => 'invalid']);
    exit;
}

// ======================
// FETCH PAYMENT STATUS
// ======================
$stmt = $conn->prepare("
    SELECT payment_status 
    FROM orders 
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param("i", $order_id);
$stmt->execute();

$result = $stmt->get_result()->fetch_assoc();

// ======================
// RESPONSE
// ======================
if($result){
    echo json_encode([
        'status' => $result['payment_status']
    ]);
} else {
    echo json_encode([
        'status' => 'not_found'
    ]);
}