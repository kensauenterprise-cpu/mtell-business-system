<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/db.php';

// ==========================
// GET RAW DATA
// ==========================
$data = file_get_contents('php://input');
file_put_contents("mpesa_log.txt", $data.PHP_EOL, FILE_APPEND);

$response = json_decode($data, true);

// ==========================
// EXTRACT DATA
// ==========================
$stk = $response['Body']['stkCallback'] ?? null;

if (!$stk) exit;

$resultCode = $stk['ResultCode'];
$checkout_id = $stk['CheckoutRequestID'];

// ==========================
// SUCCESS
// ==========================
if ($resultCode == 0) {

    $meta = $stk['CallbackMetadata']['Item'];

    $amount = 0;
    $receipt = '';
    $phone = '';

    foreach ($meta as $item) {
        if ($item['Name'] == 'Amount') $amount = $item['Value'];
        if ($item['Name'] == 'MpesaReceiptNumber') $receipt = $item['Value'];
        if ($item['Name'] == 'PhoneNumber') $phone = $item['Value'];
    }

    $conn->query("
        UPDATE mpesa_transactions
        SET 
            status='Completed',
            receipt_number='$receipt',
            phone='$phone'
        WHERE checkout_request_id='$checkout_id'
    ");

} else {

    // ==========================
    // FAILED
    // ==========================
    $conn->query("
        UPDATE mpesa_transactions
        SET status='Failed'
        WHERE checkout_request_id='$checkout_id'
    ");
}