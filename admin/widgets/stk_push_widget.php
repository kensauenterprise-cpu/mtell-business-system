<?php
// ✅ Load DB connection
$conn = require __DIR__ . '/../../includes/db.php';

if (!$conn || !$conn instanceof mysqli) {
    echo "<div style='background:#ffe0e0; color:#900; padding:10px; border-radius:8px;'>
        ❌ Database connection failed. Please check <code>db.php</code>.
    </div>";
    exit;
}

// ✅ Fetch last 50 STK Push attempts
$query = "SELECT checkout_request_id, phone, amount, status, receipt_number, created_at 
          FROM transactions 
          ORDER BY created_at DESC 
          LIMIT 50";
$result = mysqli_query($conn, $query);
?>

<div class="widget">
    <h3>📋 Recent STK Push Logs</h3>

    <?php if (!$result || mysqli_num_rows($result) === 0): ?>
        <p>No STK Push logs found.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Checkout ID</th>
                    <th>Phone</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Receipt</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['checkout_request_id']) ?></td>
                        <td><?= htmlspecialchars($row['phone']) ?></td>
                        <td><?= htmlspecialchars($row['amount']) ?></td>
                        <td>
                            <?php
                                $status = $row['status'];
                                if ($status === 'Success') {
                                    echo "✅ Success";
                                } elseif ($status === 'Failed') {
                                    echo "❌ Failed";
                                } else {
                                    echo "🔄 Pending";
                                }
                            ?>
                        </td>
                        <td><?= htmlspecialchars($row['receipt_number'] ?: '-') ?></td>
                        <td><?= htmlspecialchars($row['created_at']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
