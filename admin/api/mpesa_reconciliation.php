<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/db.php';

header('Content-Type: application/json');

$sql = "
SELECT id, total_amount
FROM orders
WHERE payment_method='mpesa'
AND payment_status!='paid'
";

$res = $conn->query($sql);

$data = [];

while($row = $res->fetch_assoc()){
    $data[] = $row;
}

echo json_encode($data);