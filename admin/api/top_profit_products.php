<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/init.php';

$res = $conn->query("
SELECT 
    p.name,
    SUM(oi.total_price) as revenue,
    SUM(oi.quantity * p.cost_price) as cost
FROM order_items oi
JOIN products p ON oi.product_id = p.id
GROUP BY oi.product_id
ORDER BY (revenue - cost) DESC
LIMIT 5
");

$data = [];

while($row = $res->fetch_assoc()){

    $profit = $row['revenue'] - $row['cost'];

    $data[] = [
        "name" => $row['name'],
        "profit" => (float)$profit
    ];
}

echo json_encode($data);