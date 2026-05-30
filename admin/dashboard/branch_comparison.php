<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/init.php';

// ==========================
// 🔐 SECURITY (SUPER ADMIN ONLY)
// ==========================
$role = $_SESSION['role'] ?? 'guest';

if ($role !== 'super_admin' && $role !== 'admin') {
    echo "<p style='color:red'>⛔ Access denied</p>";
    exit;
}

// ==========================
// 📊 FETCH BRANCH DATA
// ==========================
$data = [];

$res = $conn->query("
    SELECT 
        b.id,
        b.name,

        COUNT(o.id) as total_orders,
        SUM(o.total_amount) as revenue,

        (SELECT COUNT(*) FROM products p 
         WHERE p.branch_id = b.id AND p.stock < 5) as low_stock

    FROM branches b

    LEFT JOIN orders o ON o.branch_id = b.id

    GROUP BY b.id
");

while($row = $res->fetch_assoc()){
    $data[] = $row;
}

// ==========================
// 🏆 BEST BRANCH
// ==========================
$best = null;

foreach ($data as $d) {
    if (!$best || $d['revenue'] > $best['revenue']) {
        $best = $d;
    }
}
?>

<h2>🏢 Branch Comparison Dashboard</h2>

<?php if ($best): ?>
<div style="background:#16a34a; color:white; padding:15px; border-radius:8px; margin-bottom:15px;">
🏆 Best Branch: <b><?= htmlspecialchars($best['name']) ?></b> 
(KES <?= number_format($best['revenue'],2) ?>)
</div>
<?php endif; ?>

<!-- TABLE -->
<table border="1" width="100%" cellpadding="10" style="background:white;">
<tr style="background:#0f172a; color:white;">
    <th>Branch</th>
    <th>Orders</th>
    <th>Revenue</th>
    <th>Low Stock</th>
    <th>Performance</th>
</tr>

<?php foreach($data as $d): 

$performance = $d['revenue'] > 100000 ? "🔥 High" :
               ($d['revenue'] > 50000 ? "⚡ Medium" : "⚠️ Low");

?>

<tr>
    <td><?= htmlspecialchars($d['name']) ?></td>
    <td><?= $d['total_orders'] ?></td>
    <td>KES <?= number_format($d['revenue'],2) ?></td>

    <td style="color:<?= $d['low_stock'] > 5 ? 'red' : 'green' ?>">
        <?= $d['low_stock'] ?>
    </td>

    <td><?= $performance ?></td>
</tr>

<?php endforeach; ?>

</table>

<hr>

<!-- CHART -->
<h3>📊 Revenue Comparison</h3>
<canvas id="branchChart"></canvas>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const labels = <?= json_encode(array_column($data, 'name')) ?>;
const revenues = <?= json_encode(array_column($data, 'revenue')) ?>;

new Chart(document.getElementById('branchChart'), {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [{
            label: 'Revenue (KES)',
            data: revenues
        }]
    }
});
</script>