<?php
session_start();
header('Content-Type: application/json');

// 🔍 Error reporting (disable on production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ✅ InfinityFree database credentials
$servername = "sql303.infinityfree.com";
$username   = "if0_39282158";
$password   = "0suY3MfJZVqPJZ7";  // 🔐 Replace with real password
$database   = "if0_39282158_business";

// ✅ Optional DB connection (for future expansion)
$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    // Database isn’t required here, but log issue if expanding later
    error_log("DB connection failed: " . $conn->connect_error);
}

// 🎁 Prepare cart summary from session
$items = [];

if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $items[] = [
            'name'     => $item['name'],
            'price'    => $item['price'],
            'quantity' => $item['quantity']
        ];
    }
}

// 🛒 Return cart as JSON
echo json_encode($items);
?>
