<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($conn) || !$conn instanceof mysqli) {
    die("❌ Database connection failed");
}

$branch_id = $_SESSION['branch_id'] ?? 1;

$total_products = 0;
$total_stock = 0;
$low_stock = 0;
$out_of_stock = 0;
$total_stock_value = 0;
$total_purchases = 0;

if ($branch_id === 'all') {

    $row = $conn->query("SELECT COUNT(*) total FROM products")->fetch_assoc();
    $total_products = (int)$row['total'];

    $row = $conn->query("SELECT COALESCE(SUM(stock),0) total FROM products")->fetch_assoc();
    $total_stock = (int)$row['total'];

    $row = $conn->query("
        SELECT COUNT(*) total
        FROM products
        WHERE stock <= 5 AND stock > 0
    ")->fetch_assoc();
    $low_stock = (int)$row['total'];

    $row = $conn->query("
        SELECT COUNT(*) total
        FROM products
        WHERE stock <= 0
    ")->fetch_assoc();
    $out_of_stock = (int)$row['total'];

    $row = $conn->query("
        SELECT COALESCE(SUM(stock * cost_price),0) total
        FROM products
    ")->fetch_assoc();
    $total_stock_value = (float)$row['total'];

    $row = $conn->query("
        SELECT COALESCE(SUM(total_cost),0) total
        FROM purchases
    ")->fetch_assoc();
    $total_purchases = (float)$row['total'];

    $products = $conn->query("
        SELECT *
        FROM products
        ORDER BY stock ASC
    ");

} else {

    $branch_id = (int)$branch_id;

    $stmt = $conn->prepare("
        SELECT COUNT(*) total
        FROM products
        WHERE branch_id=?
    ");
    $stmt->bind_param("i",$branch_id);
    $stmt->execute();
    $total_products = (int)$stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();

    $stmt = $conn->prepare("
        SELECT COALESCE(SUM(stock),0) total
        FROM products
        WHERE branch_id=?
    ");
    $stmt->bind_param("i",$branch_id);
    $stmt->execute();
    $total_stock = (int)$stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();

    $stmt = $conn->prepare("
        SELECT COUNT(*) total
        FROM products
        WHERE stock <= 5
        AND stock > 0
        AND branch_id=?
    ");
    $stmt->bind_param("i",$branch_id);
    $stmt->execute();
    $low_stock = (int)$stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();

    $stmt = $conn->prepare("
        SELECT COUNT(*) total
        FROM products
        WHERE stock <= 0
        AND branch_id=?
    ");
    $stmt->bind_param("i",$branch_id);
    $stmt->execute();
    $out_of_stock = (int)$stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();

    $stmt = $conn->prepare("
        SELECT COALESCE(SUM(stock * cost_price),0) total
        FROM products
        WHERE branch_id=?
    ");
    $stmt->bind_param("i",$branch_id);
    $stmt->execute();
    $total_stock_value = (float)$stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();

    $stmt = $conn->prepare("
        SELECT COALESCE(SUM(total_cost),0) total
        FROM purchases
        WHERE branch_id=?
    ");
    $stmt->bind_param("i",$branch_id);
    $stmt->execute();
    $total_purchases = (float)$stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();

    $stmt = $conn->prepare("
        SELECT *
        FROM products
        WHERE branch_id=?
        ORDER BY stock ASC
    ");
    $stmt->bind_param("i",$branch_id);
    $stmt->execute();
    $products = $stmt->get_result();
}
?>

<h2>📦 Inventory Dashboard</h2>

<div class="stats">

    <div class="card">
        <h3>📦 Products</h3>
        <p><?= number_format($total_products) ?></p>
    </div>

    <div class="card">
        <h3>🛒 Total Stock</h3>
        <p><?= number_format($total_stock) ?></p>
    </div>

    <div class="card">
        <h3>⚠ Low Stock</h3>
        <p><?= number_format($low_stock) ?></p>
    </div>

    <div class="card">
        <h3>❌ Out of Stock</h3>
        <p><?= number_format($out_of_stock) ?></p>
    </div>

    <div class="card">
        <h3>💰 Stock Value</h3>
        <p>Ksh <?= number_format($total_stock_value,2) ?></p>
    </div>

    <div class="card">
        <h3>🧾 Purchases</h3>
        <p>Ksh <?= number_format($total_purchases,2) ?></p>
    </div>

</div>

<br>

<table border="1" width="100%" cellpadding="10" cellspacing="0">

<tr style="background:#f1f5f9;">
    <th>ID</th>
    <th>Image</th>
    <th>Product</th>
    <th>Stock</th>
    <th>Cost Price</th>
    <th>Retail Price</th>
    <th>Wholesale Price</th>
    <th>Online Price</th>
    <th>Stock Value</th>
    <th>Status</th>
    </tr>

<?php if ($products && $products->num_rows > 0): ?>

<?php while($row = $products->fetch_assoc()): ?>

<?php
$stock = (int)$row['stock'];

$status =
    $stock <= 0 ? '❌ Out of Stock' :
    ($stock <= 5 ? '⚠ Low Stock' : '✅ In Stock');

$cost_price      = (float)$row['cost_price'];
$retail_price    = (float)$row['retail_price'];
$wholesale_price = (float)$row['wholesale_price'];
$online_price    = (float)$row['online_price'];
$stock_value = $stock * $cost_price;

$image = !empty($row['image'])
    ? $row['image']
    : 'default.png';
?>

<tr>

    <td><?= $row['id'] ?></td>

    <td>
        <img
            src="/infinity/uploads/<?= htmlspecialchars($image) ?>"
            width="50"
            height="50"
            style="object-fit:cover;border-radius:5px;"
        >
    </td>

    <td><?= htmlspecialchars($row['name']) ?></td>

    <td><?= number_format($stock) ?></td>

    <td>Ksh <?= number_format($cost_price,2) ?></td>

    <td>Ksh <?= number_format($retail_price,2) ?></td>

    <td>Ksh <?= number_format($wholesale_price,2) ?></td>

    <td>Ksh <?= number_format($online_price,2) ?></td>

    <td>Ksh <?= number_format($stock_value,2) ?></td>

    <td><?= $status ?></td>

</tr>

<?php endwhile; ?>

<?php else: ?>

<tr>
    <td colspan="10">No inventory records found.</td>
</tr>

<?php endif; ?>

</table>