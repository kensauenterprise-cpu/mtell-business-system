<?php
// ✅ Load DB connection and validate
$conn = require __DIR__ . '/../includes/db.php';

if (!$conn || !$conn instanceof mysqli) {
    echo "<div style='background:#ffe0e0; color:#900; padding:10px; border-radius:8px; margin-bottom:10px;'>
        ❌ Database connection failed or not initialized. Please verify <code>db.php</code>.
    </div>";
    exit;
}

// ✅ Load supporting modules
include_once __DIR__ . '/../includes/token_utils.php';
include_once __DIR__ . '/../includes/stk_push_monitor.php';
include_once __DIR__ . '/../includes/CredentialValidator.php';

/* ------------------ HELPERS ------------------ */
function classifyLog($details) {
    if (stripos($details, '"ResponseCode":0') !== false || stripos($details, '"ResultCode":0') !== false) {
        return '✅ Success';
    } elseif (stripos($details, 'error') !== false || stripos($details, 'failed') !== false) {
        return '❌ Error';
    }
    return '🔄 Pending';
}

/* ------------------ INPUTS ------------------ */
$month  = $_GET['month']  ?? date('Y_m');
$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? 'all';
$export = isset($_GET['export']);

$logDir = __DIR__ . '/../../logs';
$files = [
    'STK Push' => "$logDir/stkpush_{$month}.log",
    'Callback' => "$logDir/callback_{$month}.log"
];

$entries = [];

/* ------------------ LOAD LOGS ------------------ */
foreach ($files as $label => $path) {
    if (file_exists($path)) {
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if ($search !== '' && stripos($line, $search) === false) continue;

            $pos = strpos($line, ' - ');
            $timestamp = $pos !== false ? substr($line, 0, $pos) : '';
            $details   = $pos !== false ? substr($line, $pos + 3) : $line;
            $status    = classifyLog($details);

            if ($filter === 'success' && $status !== '✅ Success') continue;
            if ($filter === 'error'   && $status !== '❌ Error') continue;

            $entries[] = [$label, $timestamp, $status, $details];
        }
    }
}

/* ------------------ EXPORT CSV ------------------ */
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

/* ------------------ DB RECONCILIATION ------------------ */
$totalPushes = $conn->query("SELECT COUNT(*) AS total FROM stk_pushes")->fetch_assoc()['total'];
$resolved    = $conn->query("SELECT COUNT(*) AS resolved FROM stk_pushes WHERE status != 'Pending'")->fetch_assoc()['resolved'];
$pending     = $conn->query("SELECT COUNT(*) AS pending FROM stk_pushes WHERE status = 'Pending'")->fetch_assoc()['pending'];
$stale       = $conn->query("SELECT COUNT(*) AS stale FROM stk_pushes WHERE status = 'Pending' AND TIMESTAMPDIFF(MINUTE, created_at, NOW()) >= 2")->fetch_assoc()['stale'];
?>

<!-- ================= UI SECTIONS ================= -->

<h3>📊 Unified STK Push & Callback Logs</h3>
<form method="GET" style="margin-bottom:15px;">
    <label>Month (YYYY_MM): <input type="text" name="month" value="<?= htmlspecialchars($month) ?>"></label>
    <label>Search: <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" style="width:200px;"></label>
    <label>Status:
        <select name="filter">
            <option value="all"     <?= $filter === 'all' ? 'selected' : '' ?>>All</option>
            <option value="success" <?= $filter === 'success' ? 'selected' : '' ?>>✅ Success</option>
            <option value="error"   <?= $filter === 'error' ? 'selected' : '' ?>>❌ Error</option>
        </select>
    </label>
    <button type="submit">Filter</button>
    <button type="submit" name="export" value="1">Export CSV</button>
</form>

<?php if (empty($entries)): ?>
    <p>No logs found for selected filters.</p>
<?php else: ?>
<div style="max-height:600px; overflow:auto;">
<table border="1" cellpadding="5" cellspacing="0" style="width:100%; border-collapse:collapse;">
    <tr><th>Source</th><th>Timestamp</th><th>Status</th><th>Details</th></tr>
    <?php foreach ($entries as [$source, $timestamp, $status, $details]): ?>
        <?php
            $ageLabel = '';
            $ageClass = '';
            if ($status === '🔄 Pending' && $timestamp !== '') {
                $requestTime = strtotime($timestamp);
                $ageSeconds  = time() - $requestTime;
                $ageMinutes  = floor($ageSeconds / 60);
                $ageLabel    = "{$ageMinutes}m ago";

                if ($ageMinutes >= 2) {
                    $status   .= ' ⚠️ Stale';
                    $ageClass  = 'style="color:red;"';
                }
            }
        ?>
        <tr>
            <td><?= htmlspecialchars($source) ?></td>
            <td><?= htmlspecialchars($timestamp) ?></td>
            <td <?= $ageClass ?>><?= htmlspecialchars($status) ?></td>
            <td>
                <pre><?= htmlspecialchars($details) ?></pre>
                <?php if ($ageLabel): ?>
                    <div><strong>⏱ Time Since Request:</strong> <?= $ageLabel ?></div>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
</div>
<?php endif; ?>

<!-- ✅ Modular Blocks -->
<?php
include_once __DIR__ . '/../includes/reconciliation_summary.php';
include_once __DIR__ . '/../includes/token_status_block.php';
include_once __DIR__ . '/../includes/endpoint_status_block.php';
?>
