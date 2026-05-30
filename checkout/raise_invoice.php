<?php
// =========================
// ✅ DB CONNECTION
// =========================
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/db.php';

// =========================
// ✅ VALIDATE ORDER ID
// =========================
if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    die("Invalid Order ID");
}

$order_id = intval($_GET['order_id']);

// =========================
// ✅ FETCH ORDER
// =========================
$stmt = $conn->prepare("SELECT * FROM orders WHERE id=?");

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $order_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Order not found");
}

$order = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Invoice</title>
    <style>
        body {
            font-family: Arial;
            padding: 20px;
        }
        .invoice-box {
            border: 1px solid #ddd;
            padding: 20px;
            max-width: 600px;
            margin: auto;
        }
        .btn {
            display: inline-block;
            padding: 10px 15px;
            background: #007bff;
            color: #fff;
            text-decoration: none;
            margin-top: 15px;
        }
        .success {
            color: green;
        }
    </style>
</head>
<body>

<div class="invoice-box">

    <h2>📄 Invoice</h2>

    <p><strong>Order ID:</strong> <?= htmlspecialchars($order['id']) ?></p>
    <p><strong>Name:</strong> <?= htmlspecialchars($order['customer_name']) ?></p>
    <p><strong>Phone:</strong> <?= htmlspecialchars($order['phone']) ?></p>
    <p><strong>Address:</strong> <?= htmlspecialchars($order['address']) ?></p>
    <p><strong>Total:</strong> KES <?= number_format($order['total_amount'], 2) ?></p>

    <hr>

    <h3>🚚 Pay on Delivery</h3>
    <p class="success">Your order has been placed successfully.</p>
    <p>A rider will call you before delivery.</p>

    <!-- ✅ PDF DOWNLOAD LINK -->
    <a class="btn" href="/infinity/checkout/generate_invoice.php?order_id=<?= $order['id'] ?>" target="_blank">
        📄 Download Invoice (PDF)
    </a>

</div>

</body>
</html>