<?php
$month = $_GET['month'] ?? date('Y_m');
$logFile = __DIR__ . "/logs/stkpush_{$month}.log";

$successCount = 0;
$errorCount = 0;
$entries = [];

if (file_exists($logFile)) {
    $lines = file($logFile);
    foreach ($lines as $line) {
        $isSuccess = strpos($line, '✅') !== false;
        $isError   = strpos($line, '❌') !== false;

        if ($isSuccess) $successCount++;
        if ($isError)   $errorCount++;

        $entries[] = [
            'timestamp' => substr($line, 0, 19),
            'status'    => $isSuccess ? '✅ Success' : ($isError ? '❌ Error' : '—'),
            'details'   => htmlspecialchars($line)
        ];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>STK Push Log Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        h2 { margin-bottom: 10px; }
        .summary { margin-bottom: 20px; }
        .success { color: green; }
        .error { color: red; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px; border: 1px solid #ccc; text-align: left; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .filter-form { margin-bottom: 20px; }
    </style>
</head>
<body>

<h2>📊 STK Push Log Dashboard</h2>

<form method="GET" class="filter-form">
    <label>Month (YYYY_MM): <input type="text" name="month" value="<?= $month ?>"></label>
    <button type="submit">View</button>
</form>

<div class="summary">
    <strong>Total Entries:</strong> <?= count($entries) ?> |
    <span class="success">✅ Success: <?= $successCount ?></span> |
    <span class="error">❌ Errors: <?= $errorCount ?></span>
</div>

<table>
    <thead>
        <tr>
            <th>Timestamp</th>
            <th>Status</th>
            <th>Details</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($entries as $entry): ?>
            <tr>
                <td><?= $entry['timestamp'] ?></td>
                <td><?= $entry['status'] ?></td>
                <td><code><?= $entry['details'] ?></code></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>
