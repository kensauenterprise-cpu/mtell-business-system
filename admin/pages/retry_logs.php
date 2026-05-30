<?php
require_once __DIR__ . '/../includes/db.php';
include_once __DIR__ . '/../includes/header.php';

// 🔁 Retry Summary
function getRetrySummary() {
    global $conn;
    $summary = [];
    $statuses = ['Success', 'Error', 'Pending'];
    foreach ($statuses as $status) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM stk_retry_logs WHERE status = ?");
        $stmt->bind_param("s", $status);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $summary[$status] = $count;
        $stmt->close();
    }
    $stmt = $conn->prepare("SELECT MAX(timestamp) FROM stk_retry_logs");
    $stmt->execute();
    $stmt->bind_result($lastRun);
    $stmt->fetch();
    $stmt->close();
    $summary['lastRun'] = $lastRun ?: 'N/A';
    return $summary;
}

$summary = getRetrySummary();

// 🔍 Filters
$statusFilter = $_GET['status'] ?? '';
$checkoutFilter = $_GET['checkout'] ?? '';
$where = [];

if ($statusFilter) $where[] = "status = '" . mysqli_real_escape_string($conn, $statusFilter) . "'";
if ($checkoutFilter) $where[] = "checkout_id LIKE '%" . mysqli_real_escape_string($conn, $checkoutFilter) . "%'";

$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$query = "SELECT * FROM stk_retry_logs $whereClause ORDER BY timestamp DESC LIMIT 100";
$result = mysqli_query($conn, $query);
?>

<div class="container mt-4">

  <!-- 🔁 Summary Card -->
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

  <!-- 🔍 Filter Form -->
  <form method="GET" class="mb-3">
    <div class="row">
      <div class="col-md-3">
        <select name="status" class="form-control">
          <option value="">-- Filter by Status --</option>
          <option value="Success" <?= $statusFilter === 'Success' ? 'selected' : '' ?>>Success</option>
          <option value="Error" <?= $statusFilter === 'Error' ? 'selected' : '' ?>>Error</option>
          <option value="Pending" <?= $statusFilter === 'Pending' ? 'selected' : '' ?>>Pending</option>
        </select>
      </div>
      <div class="col-md-3">
        <input type="text" name="checkout" class="form-control" placeholder="Search by Checkout ID" value="<?= htmlspecialchars($checkoutFilter) ?>">
      </div>
      <div class="col-md-2">
        <button type="submit" class="btn btn-info">🔍 Apply Filters</button>
      </div>
    </div>
  </form>

  <!-- 📈 Chart -->
  <canvas id="retryChart" height="100"></canvas>

  <!-- 📜 Logs Table -->
  <h2 class="mt-4">📜 STK Retry Logs</h2>
  <table class="table table-bordered table-striped">
    <thead class="table-dark">
      <tr>
        <th>Timestamp</th>
        <th>CheckoutRequestID</th>
        <th>Status</th>
        <th>Error Code</th>
        <th>Error Message</th>
        <th>Attempts</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $chartData = ['Success' => 0, 'Error' => 0, 'Pending' => 0];
      while ($row = mysqli_fetch_assoc($result)):
        $chartData[$row['status']]++;
      ?>
        <tr class="<?= ($row['status'] === 'Pending') ? 'table-warning' : (($row['status'] === 'Error') ? 'table-danger' : 'table-success') ?>">
          <td><?= $row['timestamp'] ?></td>
          <td><?= $row['checkout_id'] ?></td>
          <td><?= $row['status'] ?></td>
          <td><?= $row['error_code'] ?? '—' ?></td>
          <td><?= $row['error_message'] ?? '—' ?></td>
          <td><?= $row['attempts'] ?></td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<!-- 📤 CSV Export -->
<?php
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="stk_retry_logs.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Timestamp', 'CheckoutRequestID', 'Status', 'Error Code', 'Error Message', 'Attempts']);
    $exportQuery = "SELECT * FROM stk_retry_logs ORDER BY timestamp DESC LIMIT 100";
    $exportResult = mysqli_query($conn, $exportQuery);
    while ($row = mysqli_fetch_assoc($exportResult)) {
        fputcsv($output, [$row['timestamp'], $row['checkout_id'], $row['status'], $row['error_code'], $row['error_message'], $row['attempts']]);
    }
    fclose($output);
    exit;
}
?>

<!-- 📊 Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('retryChart').getContext('2d');
new Chart(ctx, {
  type: 'bar',
  data: {
    labels: ['Success', 'Error', 'Pending'],
    datasets: [{
      label: 'Retry Counts',
      data: [<?= $chartData['Success'] ?>, <?= $chartData['Error'] ?>, <?= $chartData['Pending'] ?>],
      backgroundColor: ['#28a745', '#dc3545', '#ffc107']
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { display: false },
      title: { display: true, text: 'STK Retry Trends' }
    }
  }
});
</script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
