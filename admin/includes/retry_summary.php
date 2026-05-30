<?php
if (!isset($conn) || !$conn) {
    error_log("Retry Summary: DB connection not available.");
    return;
}

// 🔁 Retry Summary Logic
function getRetrySummaryBlock($conn) {
    $summary = ['Success' => 0, 'Error' => 0, 'Pending' => 0, 'lastRun' => 'N/A'];
    $statuses = ['Success', 'Error', 'Pending'];

    foreach ($statuses as $status) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM stk_retry_logs WHERE status = ?");
        if ($stmt) {
            $stmt->bind_param("s", $status);
            $stmt->execute();
            $stmt->bind_result($count);
            $stmt->fetch();
            $summary[$status] = $count;
            $stmt->close();
        }
    }

    $stmt = $conn->prepare("SELECT MAX(timestamp) FROM stk_retry_logs");
    if ($stmt) {
        $stmt->execute();
        $stmt->bind_result($lastRun);
        $stmt->fetch();
        $summary['lastRun'] = $lastRun ?: 'N/A';
        $stmt->close();
    }

    return $summary;
}

$summary = getRetrySummaryBlock($conn);
?>

<!-- 🔁 Retry Summary Card -->
<div class="card mb-4">
  <div class="card-header bg-primary text-white">🔁 STK Retry Summary</div>
  <div class="card-body">
    <p>✅ Successful Retries: <?= $summary['Success'] ?></p>
    <p>❌ Failed Retries: <?= $summary['Error'] ?></p>
    <p>⏳ Pending Retries: <?= $summary['Pending'] ?></p>
    <p>🕒 Last Retry Run: <?= $summary['lastRun'] !== 'N/A' ? date('d M Y, H:i:s', strtotime($summary['lastRun'])) : 'N/A' ?></p>
    <a href="/api/retry_stkpush.php" class="btn btn-warning">🔁 Run STK Retry Now</a>
    <a href="?export=csv" class="btn btn-success">📤 Export to CSV</a>
  </div>
</div>
