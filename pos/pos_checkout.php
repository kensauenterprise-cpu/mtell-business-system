<?php 
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/init.php';

header('Content-Type: application/json');

// ======================
// 📥 INPUT
// ======================
$data = json_decode(file_get_contents("php://input"), true);

$cart = $data['cart'] ?? [];
$payment = $data['payment_method'] ?? 'cash';
$sale_type = $data['sale_type'] ?? 'retail';

// DEFAULT BRANCH
$branch_id = $_SESSION['branch_id'] ?? 1;

// 🔥 ONLINE → HQ (NAIROBI)
if ($sale_type === 'online') {
    $branch_id = 3;
}

if (empty($cart)) {
    echo json_encode(["success"=>false,"error"=>"Empty cart"]);
    exit;
}

// ======================
// 🔐 START TRANSACTION
// ======================
$conn->begin_transaction();

try {

    // ======================
    // 💰 CALCULATE TOTAL
    // ======================
    $total = 0;

    foreach ($cart as $item) {
        $total += $item['price'] * $item['qty'];
    }

    // ======================
    // 💳 PAYMENT STATUS
    // ======================
    $payment_status = ($payment === 'mpesa') ? 'pending' : 'paid';

    // ======================
    // 📦 ORDER STATUS
    // ======================
    $status = 'Pending';

    // ======================
    // 📍 SOURCE
    // ======================
    $source = ($sale_type === 'online') ? 'online' : 'pos';

    // ======================
    // 🧾 DEFAULT CUSTOMER
    // ======================
    $customer_name = 'Walk-in Customer';
    $phone = '';
    $address = '';

    // ======================
    // 🧾 INSERT ORDER
    // ======================
    $stmt = $conn->prepare("
        INSERT INTO orders 
        (customer_name, phone, address, total_amount, payment_method, payment_status, status, branch_id, source, sale_type, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");

    if (!$stmt) {
        throw new Exception($conn->error);
    }

    // ✅ CORRECT BIND PARAM (ONLY ONCE)
    $stmt->bind_param(
        "sssdsssiss",
        $customer_name,
        $phone,
        $address,
        $total,
        $payment,
        $payment_status,
        $status,
        $branch_id,
        $source,
        $sale_type
    );

    $stmt->execute();
    $order_id = $stmt->insert_id;

    // ======================
    // 📦 ITEMS + STOCK
    // ======================
    foreach ($cart as $item) {

        $product_id = (int)$item['id'];
        $qty = (int)$item['qty'];
        $price = (float)$item['price'];

        if ($qty <= 0) {
            throw new Exception("Invalid quantity");
        }

        // 🔒 STOCK CHECK (PER BRANCH)
        $update = $conn->query("
            UPDATE products 
            SET stock = stock - $qty 
            WHERE id = $product_id 
            AND branch_id = $branch_id
            AND stock >= $qty
        ");

        if ($conn->affected_rows === 0) {
            throw new Exception("Not enough stock (Product ID $product_id)");
        }

        // 🧾 SAVE ORDER ITEM
        $stmtItem = $conn->prepare("
            INSERT INTO order_items (order_id, product_id, quantity, price)
            VALUES (?, ?, ?, ?)
        ");

        if (!$stmtItem) {
            throw new Exception($conn->error);
        }

        $stmtItem->bind_param("iiid", $order_id, $product_id, $qty, $price);
        $stmtItem->execute();

        // 📊 STOCK MOVEMENT LOG
        $conn->query("
            INSERT INTO stock_movements 
            (product_id, type, quantity, reason, reference_id, branch_id)
            VALUES ($product_id, 'out', $qty, 'sale', $order_id, $branch_id)
        ");
    }

    // ======================
    // 💰 ACCOUNTING (ONLY IF PAID)
    // ======================
    if ($payment_status === 'paid') {

        // Debit Cash / MPESA
        $conn->query("
        INSERT INTO journal_entries (account_id, debit, credit, description, entry_date)
        VALUES (
            (SELECT id FROM chart_of_accounts WHERE account_code='1000'),
            $total,
            0,
            'Sale #$order_id',
            NOW()
        )
        ");

        // Credit Revenue
        $conn->query("
        INSERT INTO journal_entries (account_id, debit, credit, description, entry_date)
        VALUES (
            (SELECT id FROM chart_of_accounts WHERE account_code='4000'),
            0,
            $total,
            'Sale #$order_id',
            NOW()
        )
        ");
    }

    // ======================
    // ✅ COMMIT
    // ======================
    $conn->commit();

    echo json_encode([
        "success"=>true,
        "order_id"=>$order_id,
        "amount"=>$total,
        "sale_type"=>$sale_type,
        "branch_id"=>$branch_id
    ]);

} catch(Exception $e){

    $conn->rollback();

    echo json_encode([
        "success"=>false,
        "error"=>$e->getMessage()
    ]);
}