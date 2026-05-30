<?php
// infinity/admin/pages/timezone_log.php

// Paths
$logsDir    = __DIR__ . '/../../logs';
$archiveDir = $logsDir . '/archive';
if (!is_dir($archiveDir)) {
    mkdir($archiveDir, 0777, true);
}
$driftLog = $logsDir . '/timezone_drift.log';
$abortLog = $logsDir . '/stkpush_abort.log';

// === Handle actions (download / clear) ===
if (isset($_GET['download'])) {
    $type = $_GET['download'];
    $file = ($type === 'drift') ? $driftLog : (($type === 'abort') ? $abortLog : null);

    if ($file && file_exists($file)) {
        if (isset($_GET['format']) && $_GET['format'] === 'csv') {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . basename($file, '.log') . '.csv"');
            $out = fopen("php://output", "w");

            if ($type === 'drift') {
                fputcsv($out, ['Timestamp', 'Server TZ', 'Server Time', 'Nairobi Time', 'Drift (sec)']);
                foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                    if (preg_match('/^(.*?) \| Server TZ: (.*?) \| Server: (.*?) \| Nairobi: (.*?) \| Drift: (-?\d+) sec$/', $line, $m)) {
                        fputcsv($out, [$m[1], $m[2], $m[3], $m[4], $m[5]]);
                    }
                }
            } elseif ($type === 'abort') {
                fputcsv($out, ['Timestamp', 'Phone', 'Amount', 'Message']);
                foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                    if (preg_match('/^(.*?) \| Phone: (.*?) \| Amount: (.*?) \| (.*)$/', $line, $m)) {
                        fputcsv($out, [$m[1], $m[2], $m[3], $m[4]]);
                    }
                }
            }
            fclose($out);
            exit;
        }

        // Default: TXT
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        readfile($file);
        exit;
    }
}

// === Handle clear logs (with backup) ===
if (isset($_GET['clear'])) {
    $type = $_GET['clear'];
    $file = ($type === 'drift') ? $driftLog : (($type === 'abort') ? $abortLog : null);

    if ($file && file_exists($file)) {
        $timestamp = date('Ymd_His');
        $backupFile = $archiveDir . '/' . basename($file, '.log') . "_$timestamp.log";

        // Move old log to archive (backup)
        if (filesize($file) > 0) {
            copy($file, $backupFile);
        }

        // Clear the log
        file_put_contents($file, "");
        header("Location: timezone_log.php?cleared=$type&backup=" . urlencode(basename($backupFile)));
        exit;
    }
}

// Helper: tail
function tailFile($file, $lines = 200) {
    if (!file_exists($file)) return [];
    $data = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    return array_slice(array_reverse($data), 0, $lines);
}

// Load entries
$driftEntries = tailFile($driftLog, 200);
$abortEntries = tailFile($abortLog, 200);

// Filter
$filter = $_GET['filter'] ?? 'all';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Timezone Drift Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f9f9f9; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th, td { padding: 8px 12px; border: 1px solid #ccc; font-size: 14px; }
        th { background: #eee; }
        tr.alert td { background: #fdd; color: #900; }
        tr.ok td { background: #dfd; color: #060; }
        .filters a, .downloads a, .clear-btn {
            margin-right: 10px; padding: 6px 10px; text-decoration: none;
            border: 1px solid #aaa; border-radius: 4px; font-size: 13px; background: #fff;
        }
        .filters a.active { background: #333; color: #fff; }
        .downloads, .clear { margin-bottom: 20px; }
    </style>
    <meta http-equiv="refresh" content="30">
</head>
<body>
    <h1>⏱ Timezone Drift Dashboard</h1>
    <p>Auto-refreshes every 30s. Logs stored in <code>/infinity/logs</code>. Old logs are archived in <code>/infinity/logs/archive</code>.</p>

    <div class="filters">
        <a href="?filter=all" class="<?= $filter === 'all' ? 'active' : '' ?>">All</a>
        <a href="?filter=alerts" class="<?= $filter === 'alerts' ? 'active' : '' ?>">Only Alerts</a>
        <a href="?filter=ok" class="<?= $filter === 'ok' ? 'active' : '' ?>">Only OK</a>
    </div>

    <div class="downloads">
        📥 <strong>Download:</strong>
        <a href="?download=drift">Drift TXT</a>
        <a href="?download=drift&format=csv">Drift CSV</a>
        <a href="?download=abort">Abort TXT</a>
        <a href="?download=abort&format=csv">Abort CSV</a>
    </div>

    <div class="clear">
        🧹 <strong>Clear Logs (with backup):</strong>
        <a class="clear-btn" href="?clear=drift" onclick="return confirm('Clear Drift Log?')">Clear Drift Log</a>
        <a class="clear-btn" href="?clear=abort" onclick="return confirm('Clear Abort Log?')">Clear Abort Log</a>
    </div>

    <?php if (isset($_GET['cleared'])): ?>
        <p style="color:green;">
            ✅ <?= htmlspecialchars($_GET['cleared']) ?> log cleared.
            <?php if (!empty($_GET['backup'])): ?>
                Backup saved as <code><?= htmlspecialchars($_GET['backup']) ?></code> in <code>logs/archive/</code>.
            <?php endif; ?>
        </p>
    <?php endif; ?>

    <h2>Recent Drift Checks</h2>
    <table>
        <tr><th>Timestamp</th><th>Server TZ</th><th>Server Time</th><th>Nairobi Time</th><th>Drift (sec)</th></tr>
        <?php foreach ($driftEntries as $entry):
            if (preg_match('/^(.*?) \| Server TZ: (.*?) \| Server: (.*?) \| Nairobi: (.*?) \| Drift: (-?\d+) sec$/', $entry, $m)):
                [$all, $ts, $tz, $server, $nairobi, $drift] = $m;
                $class = (abs((int)$drift) > 60) ? 'alert' : 'ok';
                if ($filter === 'alerts' && $class !== 'alert') continue;
                if ($filter === 'ok' && $class !== 'ok') continue;
        ?>
            <tr class="<?= $class ?>">
                <td><?= htmlspecialchars($ts) ?></td>
                <td><?= htmlspecialchars($tz) ?></td>
                <td><?= htmlspecialchars($server) ?></td>
                <td><?= htmlspecialchars($nairobi) ?></td>
                <td><?= htmlspecialchars($drift) ?></td>
            </tr>
        <?php endif; endforeach; ?>
    </table>

    <h2>Aborted STK Pushes (due to Drift)</h2>
    <table>
        <tr><th>Timestamp</th><th>Phone</th><th>Amount</th><th>Message</th></tr>
        <?php foreach ($abortEntries as $entry):
            if (preg_match('/^(.*?) \| Phone: (.*?) \| Amount: (.*?) \| (.*)$/', $entry, $m)):
                [$all, $ts, $phone, $amount, $msg] = $m;
                if ($filter === 'ok') continue;
        ?>
            <tr class="alert">
                <td><?= htmlspecialchars($ts) ?></td>
                <td><?= htmlspecialchars($phone) ?></td>
                <td><?= htmlspecialchars($amount) ?></td>
                <td><?= htmlspecialchars($msg) ?></td>
            </tr>
        <?php endif; endforeach; ?>
    </table>
</body>
</html>
