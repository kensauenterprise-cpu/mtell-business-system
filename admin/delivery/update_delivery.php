<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/db.php';

$id = $_GET['id'];
$status = $_GET['status'];

if ($status == 'Delivered') {
    $stmt = $conn->prepare("
        UPDATE orders 
        SET delivery_status=?, delivered_at=NOW(), status='Completed'
        WHERE id=?
    ");
} else {
    $stmt = $conn->prepare("
        UPDATE orders 
        SET delivery_status=?
        WHERE id=?
    ");
}

$stmt->bind_param("si", $status, $id);
$stmt->execute();

echo "Updated!";