<?php
function getCallbackLogs($transactionId) {
    // Placeholder: adapt if you store logs in DB
    $logFile = __DIR__ . '/../logs/stkpush_' . date('Y_m') . '.log';
    $lines = file($logFile);
    foreach ($lines as $line) {
        if (strpos($line, $transactionId) !== false) {
            return [
                'received_at' => substr($line, 0, 19),
                'raw_json' => $line
            ];
        }
    }
    return ['received_at' => null, 'raw_json' => 'No log found'];
}
?>
