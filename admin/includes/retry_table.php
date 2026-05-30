<?php if (isset($result) && mysqli_num_rows($result) > 0): ?>
  <!-- 📜 STK Retry Logs Table -->
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
<?php else: ?>
  <p>No retry logs found.</p>
<?php endif; ?>
