<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $product_id = (int)$_POST['product_id'];
    $qty = (int)$_POST['qty'];

    // ➕ Add stock
    $conn->query("
        UPDATE products 
        SET stock = stock + $qty 
        WHERE id = $product_id
    ");

    // 📝 Log movement
    $conn->query("
        INSERT INTO stock_movements (product_id, type, quantity, reason)
        VALUES ($product_id, 'in', $qty, 'restock')
    ");

    echo "✅ Stock updated";
}
?>