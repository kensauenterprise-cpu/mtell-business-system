<?php
$merchantRequestId = 'f788-45a5-af7d-d2dc9486ca597714706';
$checkoutRequestId = 'ws_CO_010920251414526711931632';
$requestTimestamp = strtotime('2025-09-01 07:14:53');
$callbackReceived = false;

$timeSinceRequest = time() - $requestTimestamp;
$minutes = floor($timeSinceRequest / 60);
$seconds = $timeSinceRequest % 60;
?>

<div class="card" style="margin-top:30px;">
  <h4>📡 STK Push Monitor</h4>
  <p><strong>✅ STK Push ID:</strong> <?= htmlspecialchars($merchantRequestId) ?></p>
  <p><strong>🕒 Time Since Request:</strong> <?= $minutes ?>m <?= $seconds ?>s</p>
  <p><strong>📥 Callback Received:</strong>
    <?= $callbackReceived ? '<span style="color:green;">Yes</span>' : '<span style="color:red;">No</span>' ?>
  </p>
  <form onsubmit="return queryStatus(event)">
    <input type="hidden" name="checkout_id" value="<?= htmlspecialchars($checkoutRequestId) ?>">
    <button type="submit">🔁 Retry Status Query</button>
  </form>
  <pre id="queryResult">Waiting...</pre>
</div>

<script>
function queryStatus(e) {
  e.preventDefault();
  const form = e.target;
  const formData = new FormData(form);

  fetch('query_api.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    document.getElementById('queryResult').textContent =
      `ResultCode: ${data.ResultCode}\nDescription: ${data.ResultDesc}`;
  })
  .catch(() => {
    document.getElementById('queryResult').textContent = '⚠️ Error querying status.';
  });
}
</script>
