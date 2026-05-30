<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/init.php';

// ======================
// RECEIVE CALLBACK
// ======================
$data = json_decode(file_get_contents("php://input"), true);

// Log everything (VERY IMPORTANT)
file_put_contents("mpesa_log.txt", json_encode($data).PHP_EOL, FILE_APPEND);

// Safety check
if(!isset($data['Body']['stkCallback'])){
    exit;
}

$result = $data['Body']['stkCallback'];

$checkoutId = $result['CheckoutRequestID'] ?? '';
$status     = $result['ResultCode'] ?? 1;

// ======================
// SUCCESS PAYMENT
// ======================
if($status == 0){

    $meta = $result['CallbackMetadata']['Item'] ?? [];

    $amount = 0;
    $mpesa_code = '';
    $phone = '';

    // Extract values safely
    foreach($meta as $m){
        if($m['Name'] == 'Amount'){
            $amount = $m['Value'];
        }
        if($m['Name'] == 'MpesaReceiptNumber'){
            $mpesa_code = $m['Value'];
        }
        if($m['Name'] == 'PhoneNumber'){
            $phone = $m['Value'];
        }
    }

    // ======================
    // FIND ORDER
    // ======================
    $stmt = $conn->prepare("
        SELECT id FROM orders 
        WHERE checkout_request_id = ?
        LIMIT 1
    ");
    $stmt->bind_param("s", $checkoutId);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();

    if($order){

        $order_id = $order['id'];

        // ======================
        // SAVE TRANSACTION
        // ======================
        $stmt = $conn->prepare("
            INSERT INTO transactions (mpesa_code, amount, phone, status)
            VALUES (?, ?, ?, 'completed')
        ");
        $stmt->bind_param("sds", $mpesa_code, $amount, $phone);
        $stmt->execute();

        // ======================
        // UPDATE ORDER
        // ======================
        $stmt = $conn->prepare("
            UPDATE orders 
            SET payment_status = 'paid', status = 'completed'
            WHERE id = ?
        ");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();

        // ======================
        // ACCOUNTING ENTRIES
        // ======================

        // Debit Cash
        $conn->query("
            INSERT INTO journal_entries (account_id, debit, credit, description, entry_date)
            VALUES (
                (SELECT id FROM chart_of_accounts WHERE account_code='1000'),
                $amount,
                0,
                'MPESA Payment Order #$order_id',
                NOW()
            )
        ");

        // Credit Revenue
        $conn->query("
            INSERT INTO journal_entries (account_id, debit, credit, description, entry_date)
            VALUES (
                (SELECT id FROM chart_of_accounts WHERE account_code='4000'),
                0,
                $amount,
                'MPESA Payment Order #$order_id',
                NOW()
            )
        );
    }

} else {
    // ======================
    // FAILED PAYMENT
    // ======================
    // Optional: mark order as failed
}