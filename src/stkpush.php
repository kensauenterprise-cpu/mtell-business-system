<?php
date_default_timezone_set('Africa/Nairobi');

// Read JSON input from frontend
$data = json_decode(file_get_contents('php://input'), true);
$phone = isset($data['phone']) ? trim($data['phone']) : '';
$amount = isset($data['amount']) ? $data['amount'] : '1';

// Validate phone format
if (!preg_match('/^2547\d{8}$/', $phone)) {
    echo json_encode(['error' => 'Invalid phone number format']);
    exit();
}

// Safaricom Daraja credentials (from your portal)
$consumerKey    = 'dyq2XoMkJD9ZvwBQ3otQApZhlBIrTsfXKxUOA77N7KPEKGf3';
$consumerSecret = 'QnNDVvpG2AFFkzn3IAnbkqNxNFnfJ0F4prOqI0YR3hqvrrJLFRi9WE5lsGgADbnM';
$shortcode      = '5669370'; // Use your assigned Paybill number
$passkey        = '2429a34d7dda868350e07cf1afbaaa309f9b5638de29d142bfe2fb6065c61545'; // Your actual passkey

// Generate access token
$credentials = base64_encode($consumerKey . ':' . $consumerSecret);
$tokenUrl = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

$curl = curl_init($tokenUrl);
curl_setopt($curl, CURLOPT_HTTPHEADER, ['Authorization: Basic ' . $credentials]);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$tokenResponse = curl_exec($curl);
curl_close($curl);

$tokenData = json_decode($tokenResponse);
if (!isset($tokenData->access_token)) {
    echo json_encode(['error' => 'Failed to generate access token']);
    exit();
}
$accessToken = $tokenData->access_token;

// Prepare STK Push payload
$timestamp = date('YmdHis');
$password  = base64_encode($shortcode . $passkey . $timestamp);

$stkUrl = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
$payload = [
    'BusinessShortCode' => $shortcode,
    'Password' => $password,
    'Timestamp' => $timestamp,
    'TransactionType' => 'CustomerPayBillOnline',
    'Amount' => $amount,
    'PartyA' => $phone,
    'PartyB' => $shortcode,
    'PhoneNumber' => $phone,
    'CallBackURL' => 'https://mtell.kesug.com/infinity/daraja/public/callback.php',
    'AccountReference' => 'MtellOnline',
    'TransactionDesc' => 'Payment for goods'
];

$headers = [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $accessToken
];

$curl = curl_init($stkUrl);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payload));
$response = curl_exec($curl);
$error = curl_error($curl);
curl_close($curl);

// Output result
if ($error) {
    echo json_encode(['error' => $error]);
} else {
    echo $response;
}
?>
