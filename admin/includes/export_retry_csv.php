<?php
if (!isset($conn) || !$conn) {
    error_log("CSV Export: DB connection not available.");
    exit;
}

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="stk_retry_logs.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Timestamp', 'CheckoutRequestID', 'Status', 'Error Code', 'Error Message', 'Attempts']);

$exportQuery = "SELECT * FROM stk_retry_logs ORDER BY timestamp DESC LIMIT 100";
$exportResult = mysqli_query($conn, $exportQuery);

if ($exportResult) {
    while ($row = mysqli_fetch_assoc($exportResult)) {
        fputcsv($output, [
            $row['timestamp'],
            $row['checkout_id'],
            $row['status'],
            $row['error_code'],
            $row['error_message'],
            $row['attempts']
        ]);
    }
}

fclose($output);
exit;
