<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ Load token helper
include_once $_SERVER['DOCUMENT_ROOT'] . '/infinity/config/daraja_auth.php';

$logFile = __DIR__ . '/../../logs/retry_log.txt';

// ✅ Handle batch retry request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['stk_ids'])) {
    $ids = explode(',', $_POST['stk_ids']);
    $results = [];

    $tokenStatus = getAccessToken();
    if ($tokenStatus['status'] === 'ok' || $tokenStatus['status'] === 'regenerated') {
        $accessToken = $tokenStatus['token'];

        foreach ($ids as $id) {
            $id = trim($id);
            $response = retryStkPush($id, $accessToken);
            $timestamp = date('Y-m-d H:i:s');

            if ($response['status'] !== 'success') {
                $errorMessage = $response['message'] ?? 'Unknown error';
                $logEntry = "$timestamp - Retry failed for STK Push ID: $id - Reason: $errorMessage\n";
                file_put_contents($logFile, $logEntry, FILE_APPEND);
                $results[] = ['id' => $id, 'status' => '⚠️ Failed'];
            } else {
                $logEntry = "$timestamp - Retry successful for STK Push ID: $id\n";
                file_put_contents($logFile, $logEntry, FILE_APPEND);
                $results[] = ['id' => $id, 'status' => '✅ Success'];
            }
        }
    } else {
        foreach ($ids as $id) {
            $logEntry = date('Y-m-d H:i:s') . " - Retry failed for STK Push ID: $id - Reason: Token unavailable\n";
            file_put_contents($logFile, $logEntry, FILE_APPEND);
            $results[] = ['id' => $id, 'status' => '❌ Token error'];
        }
    }

    echo json_encode($results);
    exit;
}

// ✅ Load retry stats for chart
$retryStats = [];
$entries = [];

if (file_exists($logFile)) {
    $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $parts = explode(' - ', $line, 2);
        $timestamp = $parts[0] ?? '';
        $details   = $parts[1] ?? $line;
        $entries[] = [$timestamp, $details];

        $date = substr($timestamp, 0, 10);
        $retryStats[$date] = ($retryStats[$date] ?? 0) + 1;
    }
}
?>

<!-- ✅ Retry Frequency Chart -->
<div class="card">
  <h4>📊 Retry Frequency</h4>
  <canvas id="retryChart" height="100"></canvas>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('retryChart').getContext('2d');
const chart = new Chart(ctx, {
  type: 'bar',
  data: {
    labels: <?= json_encode(array_keys($retryStats)) ?>,
    datasets: [{
      label: 'Retries per Day',
      data: <?= json_encode(array_values($retryStats)) ?>,
      backgroundColor: '#007bff'
    }]
  },
  options: {
    scales: {
      y: { beginAtZero: true }
    }
  }
});
</script>

<!-- ✅ Batch Retry Form -->
<div class="card">
  <h4>🔁 Retry Failed STK Pushes</h4>
  <form onsubmit="return sendBatchRetry(event)">
    <textarea name="stk_ids" placeholder="Enter comma-separated STK Push IDs" required style="width:100%; height:80px;"></textarea>
    <button type="submit">Retry Selected</button>
  </form>
  <pre id="batchResults">Waiting...</pre>
</div>

<script>
function sendBatchRetry(e) {
  e.preventDefault();
  const form = e.target;
  const formData = new FormData(form);

  fetch('retry.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    const output = data.map(r => `ID: ${r.id} → ${r.status}`).join('\n');
    document.getElementById('batchResults').textContent = output || 'No retries processed.';
  })
  .catch(() => {
    document.getElementById('batchResults').textContent = '⚠️ Error during batch retry.';
  });
}
</script>
