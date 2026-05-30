<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/db.php';

// ==========================
// 🔐 SESSION (SAFE)
// ==========================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==========================
// ✅ DB CHECK
// ==========================
if (!isset($conn) || !$conn instanceof mysqli) {
    die("❌ Database connection failed");
}

// ==========================
// 🏢 BRANCH
// ==========================
$branch_id = $_SESSION['branch_id'] ?? 1;

// ==========================
// 📦 INVENTORY STATS
// ==========================
$total_products     = 0;
$total_stock        = 0;
$low_stock          = 0;
$out_of_stock       = 0;
$total_stock_value  = 0;
$total_purchases    = 0;

// ==========================
// 🌍 ALL BRANCHES
// ==========================
if ($branch_id === 'all') {

    $result = $conn->query("SELECT COUNT(*) as total FROM products");
    if ($result && $row = $result->fetch_assoc()) {
        $total_products = (int)$row['total'];
    }

    $result = $conn->query("SELECT SUM(stock) as total FROM products");
    if ($result && $row = $result->fetch_assoc()) {
        $total_stock = (float)($row['total'] ?? 0);
    }

    $result = $conn->query("
        SELECT COUNT(*) as total
        FROM products
        WHERE stock <= 5
        AND stock > 0
    ");
    if ($result && $row = $result->fetch_assoc()) {
        $low_stock = (int)$row['total'];
    }

    $result = $conn->query("
        SELECT COUNT(*) as total
        FROM products
        WHERE stock <= 0
    ");
    if ($result && $row = $result->fetch_assoc()) {
        $out_of_stock = (int)$row['total'];
    }

    $result = $conn->query("
        SELECT SUM(stock * cost_price) as total
        FROM products
    ");
    if ($result && $row = $result->fetch_assoc()) {
        $total_stock_value = (float)($row['total'] ?? 0);
    }

    $result = $conn->query("
        SELECT SUM(total_cost) as total
        FROM purchases
    ");
    if ($result && $row = $result->fetch_assoc()) {
        $total_purchases = (float)($row['total'] ?? 0);
    }

} else {

    $branch_id = (int)$branch_id;

    $stmt = $conn->prepare("
        SELECT COUNT(*) as total
        FROM products
        WHERE branch_id = ?
    ");
    $stmt->bind_param("i", $branch_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $total_products = (int)$row['total'];
    }
    $stmt->close();

    $stmt = $conn->prepare("
        SELECT SUM(stock) as total
        FROM products
        WHERE branch_id = ?
    ");
    $stmt->bind_param("i", $branch_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $total_stock = (float)($row['total'] ?? 0);
    }
    $stmt->close();

    $stmt = $conn->prepare("
        SELECT COUNT(*) as total
        FROM products
        WHERE stock <= 5
        AND stock > 0
        AND branch_id = ?
    ");
    $stmt->bind_param("i", $branch_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $low_stock = (int)$row['total'];
    }
    $stmt->close();

    $stmt = $conn->prepare("
        SELECT COUNT(*) as total
        FROM products
        WHERE stock <= 0
        AND branch_id = ?
    ");
    $stmt->bind_param("i", $branch_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $out_of_stock = (int)$row['total'];
    }
    $stmt->close();

    $stmt = $conn->prepare("
        SELECT SUM(stock * cost_price) as total
        FROM products
        WHERE branch_id = ?
    ");
    $stmt->bind_param("i", $branch_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $total_stock_value = (float)($row['total'] ?? 0);
    }
    $stmt->close();

    $stmt = $conn->prepare("
        SELECT SUM(total_cost) as total
        FROM purchases
        WHERE branch_id = ?
    ");
    $stmt->bind_param("i", $branch_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $total_purchases = (float)($row['total'] ?? 0);
    }
    $stmt->close();
}

// ==========================
// 📦 PRODUCTS LIST
// ==========================
if ($branch_id === 'all') {

    $products = $conn->query("
        SELECT *
        FROM products
        ORDER BY stock ASC
    ");

} else {

    $stmt = $conn->prepare("
        SELECT *
        FROM products
        WHERE branch_id = ?
        ORDER BY stock ASC
    ");

    $stmt->bind_param("i", $branch_id);
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
        <p>Ksh <?= number_format($total_stock_value, 2) ?></p>
    </div>

    <div class="card">
        <h3>🧾 Purchases</h3>
        <p>Ksh <?= number_format($total_purchases, 2) ?></p>
    </div>

</div>

<br>

<table border="1" width="100%" cellpadding="10" cellspacing="0">

<tr style="background:#f1f5f9;">
    <th>ID</th>
    <th>Product</th>
    <th>Stock</th>
    <th>Cost Price</th>
    <th>Selling Price</th>
    <th>Stock Value</th>
    <th>Status</th>
</tr>

<?php if ($products && $products->num_rows > 0): ?>

<?php while($row = $products->fetch_assoc()): ?>

<?php
    $stock = (float)($row['stock'] ?? 0);

    if ($stock <= 0) {
        $status = "❌ Out of Stock";
    } elseif ($stock <= 5) {
        $status = "⚠ Low Stock";
    } else {
        $status = "✅ In Stock";
    }

    $buying_price = (float)($row['cost_price'] ?? 0);
    $selling_price = (float)($row['price'] ?? 0);

    $stock_value = $stock * $buying_price;
?>

<tr>
    <td><?= $row['id'] ?></td>
    <td><?= htmlspecialchars($row['name'] ?? 'N/A') ?></td>
    <td><?= number_format($stock) ?></td>
    <td>Ksh <?= number_format($buying_price, 2) ?></td>
    <td>Ksh <?= number_format($selling_price, 2) ?></td>
    <td>Ksh <?= number_format($stock_value, 2) ?></td>
    <td><?= $status ?></td>
</tr>

<?php endwhile; ?>

<?php else: ?>

<tr>
    <td colspan="7">No inventory records found.</td>
</tr>

<?php endif; ?>

</table>