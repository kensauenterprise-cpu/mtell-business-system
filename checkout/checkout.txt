<?php
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/db.php';

// ==========================
// 🔐 AUTH (for POS only)
// ==========================
$isPOS = isset($_SESSION['user_id']);

// ==========================
// 📦 INPUTS
// ==========================
$customer_id = $_POST['customer_id'] ?? 1;
$payment_method = $_POST['payment_method'] ?? 'cash';
$paid = $_POST['paid'] ?? 0;
$qtys = $_POST['qty'] ?? [];

// ==========================
// 🏢 BRANCH
// ==========================
$branch_id = $_SESSION['branch_id'] ?? 1;

// ==========================
// 🧾 TOTAL CALCULATION
// ==========================
$total = 0;
$items = [];

foreach($qtys as $product_id => $qty){

    if($qty <= 0) continue;

    $res = $conn->query("SELECT * FROM products WHERE id = $product_id");
    $p = $res->fetch_assoc();

    $price = ($payment_method == 'credit' && isset($p['wholesale_price']))
        ? $p['wholesale_price']
        : $p['price'];

    $subtotal = $price * $qty;
    $total += $subtotal;

    $items[] = [
        'product_id' => $product_id,
        'qty' => $qty,
        'price' => $price,
        'subtotal' => $subtotal
    ];
}

if($total <= 0){
    die("❌ No items selected");
}

// ==========================
// 🧾 CREATE ORDER
// ==========================
$conn->query("
INSERT INTO orders (customer_id, total, payment_method, paid, status, created_at)
VALUES ($customer_id, $total, '$payment_method', $paid, 'pending', NOW())
");

$order_id = $conn->insert_id;

// ==========================
// 📦 ORDER ITEMS + STOCK
// ==========================
foreach($items as $item){

    $pid = $item['product_id'];
    $qty = $item['qty'];
    $price = $item['price'];

    // SAVE ORDER ITEM
    $conn->query("
    INSERT INTO order_items (order_id, product_id, quantity, price)
    VALUES ($order_id, $pid, $qty, $price)
    ");

    // 🔻 REDUCE STOCK (branch)
    $conn->query("
    UPDATE branch_stock 
    SET stock = stock - $qty 
    WHERE product_id = $pid AND branch_id = $branch_id
    ");
}

// ==========================
// 💰 ACCOUNTING (DOUBLE ENTRY)
// ==========================

// CASH / RECEIVABLE
$conn->query("
INSERT INTO journal_entries (account_id, debit, credit, description, entry_date)
VALUES (
    (SELECT id FROM chart_of_accounts WHERE account_code='1000'),
    $total,
    0,
    'Sale ORDER#$order_id',
    NOW()
)
");

// REVENUE
$conn->query("
INSERT INTO journal_entries (account_id, debit, credit, description, entry_date)
VALUES (
    (SELECT id FROM chart_of_accounts WHERE account_code='4000'),
    0,
    $total,
    'Revenue ORDER#$order_id',
    NOW()
)
");

// ==========================
// 💳 PAYMENT LOGIC
// ==========================
if($payment_method == 'mpesa'){

    // FORMAT PHONE
    $phone = $_POST['phone'] ?? '';
    $phone = preg_replace('/^0/', '254', $phone);

    // CALL M-PESA FUNCTION
    require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/mpesa/stk_push.php';

    $stk = stkPush($phone, $total, $order_id);

    // SAVE TRANSACTION
    $conn->query("
    INSERT INTO payments (order_id, amount, method, status)
    VALUES ($order_id, $total, 'mpesa', 'pending')
    ");

}

// ==========================
// 🧾 CREDIT CUSTOMER
// ==========================
if($payment_method == 'credit'){

    $conn->query("
    UPDATE customers 
    SET balance = balance + $total 
    WHERE id = $customer_id
    ");
}

// ==========================
// 🧾 INVOICE REDIRECT
// ==========================
header("Location: /infinity/checkout/raise_invoice.php?order_id=".$order_id);
exit;