<?php
// === Safe session start ===
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// === Error logging setup ===
ini_set('log_errors', 1);
ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set('error_log', $_SERVER['DOCUMENT_ROOT'] . '/infinity/logs/error_log.txt');

// === Fallback redirect ===
if (empty($_SESSION['mpesa_response'])) {
    header("Location: /index.php");
    exit;
}

// === Load DB + functions ===
require_once $_SERVER['DOCUMENT_ROOT'] . '/infinity/config/functions.php'; 

$conn = $GLOBALS['conn'] ?? null;
if (!($conn instanceof mysqli)) {
    write_log('error_log.txt', "❌ DB connection missing in confirmation.php");
    die("Internal server error. Try again later.");
}

// === Handle M-Pesa response ===
$responseData = $_SESSION['mpesa_response'];
unset($_SESSION['mpesa_response']); // Clear once used

$status   = $responseData['status']   ?? 'failed';
$message  = $responseData['message']  ?? 'No response received';
$response = $responseData['response'] ?? [];

$responseCode      = $response['ResponseCode']      ?? 'N/A';
$merchantRequestId = $response['MerchantRequestID'] ?? 'N/A';
$checkoutRequestId = $response['CheckoutRequestID'] ?? 'N/A';
$customerMessage   = $response['CustomerMessage']   ?? 'N/A';

// === Check database callback audit ===
$checkoutId = $_SESSION['checkout_request_id'] ?? $checkoutRequestId;

if ($checkoutId) {
    $stmt = $conn->prepare("SELECT result_code, result_desc FROM callback_audit WHERE checkout_request_id=?");
    $stmt->bind_param("s", $checkoutId);
    $stmt->execute();
    $stmt->bind_result($code, $desc);

    if ($stmt->fetch()) {
        $status  = ($code == 0) ? 'success' : 'failed';
        $message = $desc;
    }

    $stmt->close();
}

// === If payment successful ===
if ($status === 'success' && $responseCode === '0') {

    // Log entry
    $month = date('Y_m');
    $logFile = "stkpush_{$month}.log";
    write_log($logFile, "MerchantRequestID: $merchantRequestId | CheckoutRequestID: $checkoutRequestId | Message: $customerMessage");

    // Update order status
    if (!empty($_SESSION['order_id'])) {
        $orderId = (int)$_SESSION['order_id'];

        $stmt = $conn->prepare("UPDATE orders SET status='completed', updated_at=NOW() WHERE id=?");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $stmt->close();

        // === ✔ Call order_complete.php (journal entries) ===
        require_once $_SERVER['DOCUMENT_ROOT'] . '/infinity/functions/order_complete.php';

        $orderTotal = $_SESSION['cart_total'] ?? 0;

        // run posting function
        if (function_exists("order_complete")) {
            order_complete($orderId, $orderTotal);
        } else {
            write_log('error_log.txt', "❌ order_complete() not found in order_complete.php");
        }
    }
}

?>
<!DOCTYPE html>
<html>
<head>
  <title>Payment Confirmation</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      padding: 40px;
      background: #f9f9f9;
      text-align: center;
    }
    .box {
      background: #fff;
      padding: 30px;
      border-radius: 8px;
      display: inline-block;
      max-width: 700px;
      box-shadow: 0 0 8px rgba(0,0,0,0.1);
      text-align: left;
    }
    h2 {
      text-align: center;
      color: <?= ($status === 'success') ? 'green' : 'red' ?>;
    }
    a {
      margin-top: 20px;
      display: inline-block;
      font-weight: bold;
      color: #006600;
      text-decoration: none;
    }
  </style>
</head>
<body>

<div class="box">
  <?php if ($status !== 'success'): ?>
      <h2>❌ Payment Failed</h2>
      <p><?= htmlspecialchars($message) ?></p>

  <?php else: ?>
      <h2>✅ Payment Successful</h2>
      <p>Your payment has been confirmed.</p>
      <p><strong>Message:</strong> <?= htmlspecialchars($message) ?></p>
  <?php endif; ?>

  <div style="text-align:center;">
    <a href="/index.php">⬅️ Back to Home</a>
  </div>
</div>

</body>
</html>
