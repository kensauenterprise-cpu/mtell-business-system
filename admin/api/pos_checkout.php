<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/init.php';

$data = json_decode(file_get_contents("php://input"), true);

$cart = $data['cart'] ?? [];
$payment_method = $data['payment_method'] ?? 'cash';
$sale_type = $data['sale_type'] ?? 'retail';

$branch_id = $_SESSION['branch_id'] ?? 1;

if(empty($cart)){
    echo json_encode(["success"=>false]);
    exit;
}

$conn->begin_transaction();

try {

    // ==========================
    // 🧾 CREATE ORDER
    // ==========================
    $stmt = $conn->prepare("
        INSERT INTO orders (total_amount, payment_method, branch_id)
        VALUES (0, ?, ?)
    ");
    $stmt->bind_param("si", $payment_method, $branch_id);
    $stmt->execute();

    $order_id = $conn->insert_id;

    $total = 0;

    // ==========================
    // 🔁 LOOP CART
    // ==========================
    foreach($cart as $item){

        $product_id = $item['id'];
        $qty = $item['qty'];
        $price = $item['price'];

        $total += $qty * $price;

        // ==========================
        // 📦 CHECK STOCK
        // ==========================
        $stmt = $conn->prepare("
            SELECT stock FROM products 
            WHERE id=? AND branch_id=?
        ");
        $stmt->bind_param("ii", $product_id, $branch_id);
        $stmt->execute();
        $stock = $stmt->get_result()->fetch_assoc()['stock'];

        if($stock < $qty){
            throw new Exception("Not enough stock for product ID $product_id");
        }

        // ==========================
        // ➖ REDUCE STOCK
        // ==========================
        $stmt = $conn->prepare("
            UPDATE products 
            SET stock = stock - ?
            WHERE id=? AND branch_id=?
        ");
        $stmt->bind_param("iii", $qty, $product_id, $branch_id);
        $stmt->execute();

        // ==========================
        // 🧾 ORDER ITEMS
        // ==========================
        $stmt = $conn->prepare("
            INSERT INTO order_items (order_id, product_id, qty, price)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("iiid", $order_id, $product_id, $qty, $price);
        $stmt->execute();
    }

    // ==========================
    // 💰 UPDATE ORDER TOTAL
    // ==========================
    $stmt = $conn->prepare("
        UPDATE orders SET total_amount=? WHERE id=?
    ");
    $stmt->bind_param("di", $total, $order_id);
    $stmt->execute();

    // ==========================
    // 💳 TRANSACTION RECORD
    // ==========================
    $status = ($payment_method === 'invoice') ? 'pending' : 'paid';

    $stmt = $conn->prepare("
        INSERT INTO transactions (order_id, amount, payment_method, status, branch_id)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("idssi", $order_id, $total, $payment_method, $status, $branch_id);
    $stmt->execute();

    $conn->commit();

    echo json_encode(["success"=>true]);

} catch(Exception $e){

    $conn->rollback();

    echo json_encode([
        "success"=>false,
        "error"=>$e->getMessage()
    ]);
}