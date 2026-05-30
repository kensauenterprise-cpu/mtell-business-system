<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/config/mpesa.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/init.php';

// ======================
// RECEIVE DATA
// ======================
$data = json_decode(file_get_contents("php://input"), true);

$phone     = $data['phone'] ?? '';
$amount    = $data['amount'] ?? 0;
$order_id  = $data['order_id'] ?? 0;

// ======================
// VALIDATION
// ======================
if(!$phone || !$amount || !$order_id){
    echo json_encode([
        "error" => "Missing required fields"
    ]);
    exit;
}

// format phone (07 → 2547)
$phone = preg_replace('/^0/', '254', $phone);

// ======================
// GET ACCESS TOKEN
// ======================
$credentials = base64_encode(MPESA_CONSUMER_KEY . ":" . MPESA_CONSUMER_SECRET);

$ch = curl_init("https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials");
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Basic $credentials"]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = json_decode(curl_exec($ch), true);

if(!isset($response['access_token'])){
    echo json_encode(["error" => "Failed to get access token"]);
    exit;
}

$access_token = $response['access_token'];

// ======================
// STK PUSH
// ======================
$timestamp = date("YmdHis");
$password = base64_encode(MPESA_SHORTCODE . MPESA_PASSKEY . $timestamp);

$payload = [
    "BusinessShortCode" => MPESA_SHORTCODE,
    "Password" => $password,
    "Timestamp" => $timestamp,
    "TransactionType" => "CustomerPayBillOnline",
    "Amount" => (int)$amount,
    "PartyA" => $phone,
    "PartyB" => MPESA_SHORTCODE,
    "PhoneNumber" => $phone,
    "CallBackURL" => MPESA_CALLBACK_URL,
    "AccountReference" => "ORDER#$order_id",
    "TransactionDesc" => "Order Payment"
];

$ch = curl_init("https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $access_token",
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$result = curl_exec($ch);
$response = json_decode($result, true);

// ======================
// SAVE CHECKOUT ID TO ORDER
// ======================
if(isset($response['CheckoutRequestID'])){

    $checkoutId = $response['CheckoutRequestID'];

    $stmt = $conn->prepare("
        UPDATE orders 
        SET checkout_request_id = ?
        WHERE id = ?
    ");
    $stmt->bind_param("si", $checkoutId, $order_id);
    $stmt->execute();
}

// ======================
// RETURN RESPONSE
// ======================
echo json_encode([
    "success" => true,
    "mpesa_response" => $response
]);