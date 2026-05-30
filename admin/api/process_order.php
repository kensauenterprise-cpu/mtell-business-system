foreach ($cart as $item) {

    $product_id = $item['id'];
    $qty = $item['qty'];

    // 🔻 Deduct stock
    $conn->query("
        UPDATE products 
        SET stock = stock - $qty 
        WHERE id = $product_id
    ");

    // 📝 Log movement
    $conn->query("
        INSERT INTO stock_movements (product_id, type, quantity, reason, reference_id)
        VALUES ($product_id, 'out', $qty, 'sale', $order_id)
    ");
}