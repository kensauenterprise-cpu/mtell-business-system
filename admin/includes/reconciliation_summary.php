<div class="card">
  <h4>📊 STK Push Reconciliation Summary</h4>
  <ul style="list-style:none; padding-left:0;">
    <li><strong>Total STK Pushes:</strong> <?= $totalPushes ?></li>
    <li><strong>✅ Resolved:</strong> <?= $resolved ?></li>
    <li><strong>🔄 Pending:</strong> <?= $pending ?></li>
    <li><strong>⚠️ Stale (>2 min):</strong> <span style="color:red;"><?= $stale ?></span></li>
  </ul>
</div>
