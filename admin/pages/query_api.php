<?php
// ===============================
// 🌍 Environment Setup
// ===============================
date_default_timezone_set("Africa/Nairobi");
ini_set('display_errors', 1); // ✅ Set to 0 in production
error_reporting(E_ALL);

header('Content-Type: application/json');

// ⚙️ Core Dependencies
require_once __DIR__ . '/../config/functions.php';
require_once __DIR__ . '/../config/daraja_auth.php';

// ===============================
// 📦 Prepare Query Request
// ===============================
$checkoutID = $_GET['checkout_id'] ?? null;
if (!$checkoutID) {
    echo json_encode(["status" => "error", "message" => "Missing checkout_id"]);
    exit;
}

$token = getAccessToken(); // from daraja_auth.php
$url   = "https://api.safaricom.co.ke/mpesa/stkpushquery/v1/query";

$payload = [
    "BusinessShortCode" => SHORTCODE,
    "Password"          => generatePassword(), // your helper
    "Timestamp"         => date('YmdHis'),
    "CheckoutRequestID" => $checkoutID
];

$headers = [
    "Content-Type: application/json",
    "Authorization: Bearer $token"
];

// ===============================
// 📤 Send Query Request
// ===============================
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$error    = curl_error($ch);
curl_close($ch);

// ===============================
// 🧩 Logging
// ===============================
$monthFile = 'query_' . date('Y_m') . '.log';

if ($error) {
    write_log('error_log.txt', "❌ Query failed: $error");
    write_log($monthFile, "❌ Query failed for $checkoutID: $error");
    echo json_encode(["status" => "error", "message" => $error]);
    exit;
}

// ✅ Log raw request/response
write_log('debug_payload.log', "📤 Query Payload: " . json_encode($payload));
write_log('debug_payload.log', "📥 Query Response: $response");

write_log($monthFile, "📤 Query Payload: " . json_encode($payload));
write_log($monthFile, "📥 Query Response: $response");

// ===============================
// 📤 Final Output
// ===============================
echo $response;
