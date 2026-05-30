<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/init.php';

$res = $conn->query("
    SELECT name, stock 
    FROM products 
    WHERE stock <= 5
");

$data = [];

while($row = $res->fetch_assoc()){
    $data[] = $row;
}

echo json_encode($data);