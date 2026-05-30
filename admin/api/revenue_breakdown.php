<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/db.php';

header('Content-Type: application/json');

$sql = "
SELECT payment_method, SUM(total_amount) as total
FROM orders
GROUP BY payment_method
";

$res = $conn->query($sql);

$labels = [];
$data = [];

while($row = $res->fetch_assoc()){
    $labels[] = strtoupper($row['payment_method']);
    $data[] = (float)$row['total'];
}

echo json_encode([
    "labels" => $labels,
    "data" => $data
]);