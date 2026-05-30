<?php
session_start();

// 🧹 Clear cart after confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['cart'] = [];

    // 💡 Optional: Log order details to database here
    // INSERT INTO orders (...) VALUES (...)
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Payment Confirmation</title>
  <link rel="stylesheet" href="/assets/styles.css">
  <style>
    body {
      font-family: sans-serif;
      background: #f2f2f2;
      text-align: center;
      padding: 40px;
    }
    .confirm-box {
      background: #e6ffed;
      border: 1px solid #c3e6cb;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      display: inline-block;
      max-width: 500px;
    }
    .confirm-box h2 {
      color: #28a745;
      margin-bottom: 10px;
    }
    .confirm-box p {
      font-size: 16px;
      margin-bottom: 8px;
    }
    .confirm-box a {
      display: inline-block;
      margin-top: 20px;
      background: #007bff;
      color: white;
      padding: 10px 16px;
      border-radius: 6px;
      text-decoration: none;
      font-weight: bold;
      transition: background 0.3s ease;
    }
    .confirm-box a:hover {
      background-color: #0056b3;
    }
  </style>
</head>
<body>

<div class="confirm-box">
  <h2>✅ Payment Confirmed</h2>
  <p>Thank you for your order! Your transaction has been successfully processed.</p>
  <p>Payment Method: <strong><?= htmlspecialchars($_SESSION['last_payment_method'] ?? 'Online Payment') ?></strong></p>
  <!-- ✅ Corrected PHP-aware homepage link -->
  <a href="/index.php">⬅️ Continue Shopping</a>
</div>

</body>
</html>
