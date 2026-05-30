<?php
function getValidAccessToken() {
    // Your token logic here
    return 'ACCESS_TOKEN';
}

function initiateStkPush($phone, $amount, $reference, $token) {
    // Your STK Push logic here
    return [
        'success' => true,
        'message' => 'STK Push initiated',
        'reference' => $reference
    ];
}

function retryStkPushDirect($phone, $amount, $reference) {
    $token = getValidAccessToken();
    return initiateStkPush($phone, $amount, $reference, $token);
}
?>
