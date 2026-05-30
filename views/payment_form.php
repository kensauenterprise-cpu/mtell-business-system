<?php
// Optional: Display feedback messages
if (isset($_SESSION['payment_success'])) {
    echo "<div style='background:#d4edda;color:#155724;padding:10px;border-radius:5px;margin-bottom:15px;'>
            {$_SESSION['payment_success']}
          </div>";
    unset($_SESSION['payment_success']);
}

if (isset($_SESSION['payment_error'])) {
    echo "<div style='background:#f8d7da;color:#721c24;padding:10px;border-radius:5px;margin-bottom:15px;'>
            {$_SESSION['payment_error']}
          </div>";
    unset($_SESSION['payment_error']);
}
?>

<div class="container">
  <h2>Make a Payment</h2>
  <form method="POST">
    <label for="phone">Phone Numbers (comma-separated):</label>
    <input type="text" id="phone" name="phone" placeholder="e.g. 2547XXXXXXXX,2547YYYYYYYY" required>

    <input type="hidden" name="amount" value="<?php echo htmlspecialchars($_SESSION['cart_total'] ?? ''); ?>">

    <button type="submit">Pay Now</button>
  </form>
</div>
