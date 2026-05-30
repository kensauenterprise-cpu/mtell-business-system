<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 🔧 Load helpers
require_once __DIR__ . '/../../helpers/retry_helper.php';
require_once __DIR__ . '/../../helpers/logs.php';

// 📁 Load logs
$logFile = __DIR__ . '/../../logs/stkpush_' . date('Y_m') . '.log';
$logs = [];

if (file_exists($logFile)) {
    $lines = file($logFile);
    foreach ($lines as $line) {
        preg_match('/(✅|❌).*Phone: (\d+).*Amount: ([0-9.]+).*Response: (.+)/', $line, $match);
        if ($match) {
            $logs[] = [
                'date' => substr($line, 0, 19),
                'status' => $match[1],
                'phone' => $match[2],
                'amount' => $match[3],
                'message' => $match[4],
                'callback_payload' => trim($line)
            ];
        }
    }
}

// 🔍 Filters
$start = $_GET['start'] ?? '';
$end   = $_GET['end'] ?? '';
$phone = $_GET['phone'] ?? '';

$filteredLogs = array_filter($logs, function ($log) use ($start, $end, $phone) {
    $logDate = strtotime($log['date']);
    if ($start && $logDate < strtotime($start)) return false;
    if ($end && $logDate > strtotime($end . ' 23:59:59')) return false;
    if ($phone && strpos($log['phone'], $phone) === false) return false;
    return true;
});

$successCount = count(array_filter($filteredLogs, fn($l) => $l['status'] === '✅'));
$failCount    = count(array_filter($filteredLogs, fn($l) => $l['status'] === '❌'));

// 📤 CSV Export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="stk_push_log.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Date', 'Phone', 'Amount', 'Status', 'Message']);
    foreach ($filteredLogs as $row) {
        fputcsv($output, [$row['date'], $row['phone'], $row['amount'], $row['status'], $row['message']]);
    }
    fclose($output);
    exit;
}

// 🔁 Retry handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['retry'])) {
    $phone = $_POST['phone'];
    $amount = $_POST['amount'];
    $reference = 'RETRY-' . time();
    $response = retryStkPushDirect($phone, $amount, $reference);
    file_put_contents(
        __DIR__ . '/../../logs/retry.log',
        date('Y-m-d H:i:s') . " Retry for $phone: " . json_encode($response) . "\n",
        FILE_APPEND
    );
    header("Location: stk_dashboard.php?start=$start&end=$end&phone=$phone");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>STK Push Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; }
        .summary { margin-top: 10px; }
        .retry-form { margin-top: 20px; }
    </style>
</head>
<body>
    <h2>📊 STK Push Dashboard</h2>

    <form method="get">
        <label>Start Date: <input type="date" name="start" value="<?= htmlspecialchars($start) ?>"></label>
        <label>End Date: <input type="date" name="end" value="<?= htmlspecialchars($end) ?>"></label>
        <label>Phone: <input type="text" name="phone" value="<?= htmlspecialchars($phone) ?>"></label>
        <button type="submit">Filter</button>
        <a href="?start=<?= urlencode($start) ?>&end=<?= urlencode($end) ?>&phone=<?= urlencode($phone) ?>&export=csv">Export CSV</a>
    </form>

    <div class="summary">
        <strong>✅ Success:</strong> <?= $successCount ?> |
        <strong>❌ Failures:</strong> <?= $failCount ?>
    </div>

    <table>
        <tr>
            <th>Date</th>
            <th>Phone</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Message</th>
            <th>Retry</th>
        </tr>
        <?php foreach ($filteredLogs as $log): ?>
        <tr>
            <td><?= htmlspecialchars($log['date']) ?></td>
            <td><?= htmlspecialchars($log['phone']) ?></td>
            <td><?= htmlspecialchars($log['amount']) ?></td>
            <td><?= htmlspecialchars($log['status']) ?></td>
            <td><?= htmlspecialchars($log['message']) ?></td>
            <td>
                <form method="post" class="retry-form">
                    <input type="hidden" name="phone" value="<?= htmlspecialchars($log['phone']) ?>">
                    <input type="hidden" name="amount" value="<?= htmlspecialchars($log['amount']) ?>">
                    <button type="submit" name="retry">Retry</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
