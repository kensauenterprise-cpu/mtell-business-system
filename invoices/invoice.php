// invoice.php
include 'db.php';
include 'functions.php'; // helper functions

$orderId = $_GET['order_id'];
$total   = calculateOrderTotal($orderId);

$invoiceNo = "INV-" . time();

$stmt = $pdo->prepare("INSERT INTO invoices 
    (order_id, invoice_number, total_amount, status, created_at) 
    VALUES (?, ?, ?, 'raised', NOW())");
$stmt->execute([$orderId, $invoiceNo, $total]);

echo "Invoice #$invoiceNo raised for Order #$orderId";
