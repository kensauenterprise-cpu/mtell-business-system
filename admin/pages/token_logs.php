<?php
$logFile = __DIR__ . '/../../logs/token_regeneration/csv/token_regeneration.csv';
$logs = [];
$dailyCounts = [];

if (file_exists($logFile)) {
    $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $parts = str_getcsv($line);
        $timestamp = $parts[0] ?? '';
        $token     = $parts[1] ?? '';
        $expiresAt = $parts[2] ?? 0;

        $logs[] = [
            'timestamp' => $timestamp,
            'token'     => substr($token, 0, 6) . '...',
            'expires'   => date('Y-m-d H:i:s', $expiresAt),
            'expiring_soon' => ($expiresAt - time()) < 3600
        ];

        // Count tokens per day
        $day = substr($timestamp, 0, 10); // YYYY-MM-DD
        if (!isset($dailyCounts[$day])) {
            $dailyCounts[$day] = 0;
        }
        $dailyCounts[$day]++;
    }
}

// Optional date filter
$startDate = $_GET['start'] ?? null;
$endDate   = $_GET['end'] ?? null;

if ($startDate && $endDate) {
    $logs = array_filter($logs, function($entry) use ($startDate, $endDate) {
        return $entry['timestamp'] >= $startDate && $entry['timestamp'] <= $endDate;
    });
}
?>

<div class="card">
    <h3>🔐 Token Regeneration Log</h3>

    <form method="get" class="filter-form">
        <label>Start Date: <input type="date" name="start" value="<?= htmlspecialchars($startDate) ?>"></label>
        <label>End Date: <input type="date" name="end" value="<?= htmlspecialchars($endDate) ?>"></label>
        <button type="submit">Filter</button>
    </form>

    <?php if ($dailyCounts): ?>
        <div style="margin-top:20px;">
            <strong>📊 Daily Summary:</strong>
            <ul>
                <?php foreach ($dailyCounts as $day => $count): ?>
                    <li><?= $day ?> — <?= $count ?> tokens</li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <table style="margin-top:20px;">
        <thead>
            <tr>
                <th>Timestamp</th>
                <th>Access Token</th>
                <th>Expires At</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($logs)): ?>
                <?php foreach ($logs as $entry): ?>
                    <tr>
                        <td><?= htmlspecialchars($entry['timestamp']) ?></td>
                        <td><?= htmlspecialchars($entry['token']) ?></td>
                        <td><?= htmlspecialchars($entry['expires']) ?></td>
                        <td>
                            <?= $entry['expiring_soon'] ? '<span style="color:red;">Expiring Soon</span>' : '✅ Healthy' ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="4">No logs found for selected range.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <a href="../../logs/token_regeneration/csv/token_regeneration.csv" download class="export-btn">📥 Export CSV</a>
</div>
