<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/db.php';

// ==========================
// CONFIG (USE YOUR LIVE OR SANDBOX KEYS)
// ==========================
$consumerKey = "yi0BntQvtOMZsqZ8yja32DKuIc1YzuAFIxbjEAuHmKhn1RBK";
$consumerSecret = "R7K99p710eF35geSw8mM6I5f4GavUe7HUK8YTE2lNaijVDvvLRubgZjXLnnAEje4";
$shortCode = "5669370"; // ✅ YOUR TILL
$passkey = "2429a34d7dda868350e07cf1afbaaa309f9b5638de29d142bfe2fb6065c61545";

// ==========================
// GET POST DATA
// ==========================
$phone = $_POST['phone'] ?? '';
$amount = (int)($_POST['amount'] ?? 0);
$order_id = (int)($_POST['order_id'] ?? 0);

// ==========================
// FORMAT PHONE (CRITICAL)
// ==========================
$phone = preg_replace('/\D/', '', $phone);

if (substr($phone, 0, 1) === '0') {
    $phone = '254' . substr($phone, 1);
}

// Validate
if (!preg_match('/^2547\d{8}$/', $phone)) {
    echo json_encode(["error" => "Invalid phone number"]);
    exit;
}

// ==========================
// GET ACCESS TOKEN
// ==========================
$credentials = base64_encode($consumerKey . ":" . $consumerSecret);

$ch = curl_init("https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials");
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Basic $credentials"]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);

if (!$response) {
    echo json_encode(["error" => "Token request failed"]);
    exit;
}

$result = json_decode($response);
$token = $result->access_token ?? '';

if (!$token) {
    echo json_encode(["error" => "Invalid access token"]);
    exit;
}

// ==========================
// STK PUSH REQUEST
// ==========================
$timestamp = date('YmdHis');
$password = base64_encode($shortCode . $passkey . $timestamp);

$stkData = [
    "BusinessShortCode" => $shortCode,
    "Password" => $password,
    "Timestamp" => $timestamp,

    // ✅ BUY GOODS (IMPORTANT)
    "TransactionType" => "CustomerBuyGoodsOnline",

    "Amount" => $amount,
    "PartyA" => $phone,
    "PartyB" => $shortCode,
    "PhoneNumber" => $phone,

    "CallBackURL" => "https://mtell.kesug.com/infinity/mpesa/callback.php",

    "AccountReference" => "SALE".$order_id,
    "TransactionDesc" => "POS Payment"
];

// ==========================
// SEND REQUEST
// ==========================
$ch = curl_init("https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest");

curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer $token"
]);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($stkData));

$stkResponse = curl_exec($ch);

if (!$stkResponse) {
    echo json_encode(["error" => "STK Push failed"]);
    exit;
}

$responseData = json_decode($stkResponse, true);

// ==========================
// SAVE CHECKOUT REQUEST ID
// ==========================
$checkout_id = $responseData['CheckoutRequestID'] ?? null;

if ($checkout_id) {

    $stmt = $conn->prepare("
        UPDATE mpesa_transactions 
        SET checkout_request_id=? 
        WHERE order_id=?
    ");

    $stmt->bind_param("si", $checkout_id, $order_id);
    $stmt->execute();
}

// ==========================
// RESPONSE BACK TO POS
// ==========================
echo json_encode([
    "success" => true,
    "message" => "STK Push sent",
    "data" => $responseData
]);