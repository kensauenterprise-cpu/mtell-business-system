<?php
require_once __DIR__ . '/../includes/log_helper.php';

$month = $_GET['month'] ?? date('Y_m');
$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? 'all';
$export = isset($_GET['export']);

$logDir = __DIR__ . '/../../logs';
$callbackPath = "$logDir/callback_{$month}.log";

// Parse STK Push and Callback logs
$entries = array_merge(
    parseLogFile("$logDir/stkpush_{$month}.log", 'STK Push', $search, $filter),
    parseLogFile($callbackPath, 'Callback', $search, $filter)
);

// Match STK Push entries with callback status and enrich details
$entries = matchStkPushWithCallback($entries, $callbackPath);

// Export CSV if requested
if ($export) {
    header('Content-Type: text/csv');
    header("Content-Disposition: attachment; filename=mpesa_logs_{$month}.csv");
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Source', 'Timestamp', 'Status', 'Details']);
    foreach ($entries as $row) {
        fputcsv($out, $row);
    }
    fclose($out);
    exit;
}
?>

<h3>📊 Unified STK Push & Callback Logs</h3>
<form method="GET" style="margin-bottom:15px;">
    <label>Month (YYYY_MM): <input type="text" name="month" value="<?= htmlspecialchars($month) ?>"></label>
    <label>Search: <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" style="width:200px;"></label>
    <label>Status:
        <select name="filter">
            <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All</option>
            <option value="success" <?= $filter === 'success' ? 'selected' : '' ?>>✅ Success</option>
            <option value="error" <?= $filter === 'error' ? 'selected' : '' ?>>❌ Error</option>
        </select>
    </label>
    <button type="submit">Filter</button>
    <button type="submit" name="export" value="1">Export CSV</button>
</form>

<?php if (empty($entries)): ?>
    <div style="margin-top:20px; background:#fff3cd; padding:15px; border-radius:6px; color:#856404;">
        <strong>⚠️ No logs found for selected filters.</strong><br>
        Try adjusting your search or selecting a different month/status.
    </div>
<?php else: ?>
<div style="max-height:600px; overflow:auto; margin-top:20px;">
<table border="1" cellpadding="5" cellspacing="0" style="width:100%; border-collapse:collapse;">
    <tr><th>Source</th><th>Timestamp</th><th>Status</th><th>Details</th></tr>
    <?php foreach ($entries as [$source, $timestamp, $status, $details]): ?>
        <tr>
            <td><?= htmlspecialchars($source) ?></td>
            <td><?= htmlspecialchars($timestamp) ?></td>
            <td><?= htmlspecialchars($status) ?></td>
            <td><pre><?= htmlspecialchars($details) ?></pre></td>
        </tr>
    <?php endforeach; ?>
</table>
</div>
<?php endif; ?>
