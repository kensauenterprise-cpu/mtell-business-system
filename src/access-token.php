<?php
// Your credentials from Safaricom Developer Portal
$consumerKey = 'dyq2XoMkJD9ZvwBQ3otQApZhlBIrTsfXKxUOA77N7KPEKGf3';
$consumerSecret = 'QnNDVvpG2AFFkzn3IAnbkqNxNFnfJ0F4prOqI0YR3hqvrrJLFRi9WE5lsGgADbnM';

// API URL
$url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

// Encode credentials
$credentials = base64_encode($consumerKey . ':' . $consumerSecret);

// Set headers
$headers = [
    'Authorization: Basic ' . $credentials
];

// Initialize cURL
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

// Execute request
$response = curl_exec($curl);
curl_close($curl);

// Decode and display token
$token = json_decode($response);
echo "Access Token: " . $token->access_token;
?>
