<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/init.php';

if (!isset($conn) || !$conn) {
    die("Database connection failed.");
}

// Products
$products = $conn->query("
    SELECT *
    FROM products
    ORDER BY name ASC
");

// Suppliers
$suppliers = $conn->query("
    SELECT *
    FROM suppliers
    ORDER BY name ASC
");

// Save Purchase
if (isset($_POST['save'])) {

    $supplier_id  = (int)$_POST['supplier_id'];
    $product_id   = (int)$_POST['product_id'];
    $quantity     = (int)$_POST['quantity'];
    $cost_price   = (float)$_POST['cost_price'];
    $purchase_date = $_POST['purchase_date'];

    $branch_id = $_SESSION['branch_id'] ?? 1;
    $created_by = $_SESSION['username'] ?? 'System';

    $total_cost = $quantity * $cost_price;

    $stmt = $conn->prepare("
        INSERT INTO purchases (
            supplier_id,
            product_id,
            quantity,
            cost_price,
            total_cost,
            purchase_date,
            branch_id,
            created_by,
            total,
            status
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $status = 'Completed';

    $stmt->bind_param(
        "iiiddsisss",
        $supplier_id,
        $product_id,
        $quantity,
        $cost_price,
        $total_cost,
        $purchase_date,
        $branch_id,
        $created_by,
        $total_cost,
        $status
    );

    if (!$stmt->execute()) {
        die("Purchase Error: " . $stmt->error);
    }

    // Update Product Stock
    $update = $conn->prepare("
        UPDATE products
        SET stock = stock + ?
        WHERE id = ?
    ");

    $update->bind_param(
        "ii",
        $quantity,
        $product_id
    );

    if (!$update->execute()) {
        die("Stock Update Error: " . $update->error);
    }

    echo "<script>
        alert('Purchase saved successfully.');
        window.location.href='dashboard.php?tab=purchases';
    </script>";
    exit;
}
?>

<h2>➕ Add Purchase</h2>

<form method="POST">

    <label>Supplier</label><br>
    <select name="supplier_id" required>
        <option value="">Select Supplier</option>

        <?php while ($s = $suppliers->fetch_assoc()): ?>
            <option value="<?= $s['id']; ?>">
                <?= htmlspecialchars($s['name']); ?>
            </option>
        <?php endwhile; ?>

    </select>

    <br><br>

    <label>Product</label><br>
    <select name="product_id" required>
        <option value="">Select Product</option>

        <?php while ($p = $products->fetch_assoc()): ?>
            <option value="<?= $p['id']; ?>">
                <?= htmlspecialchars($p['name']); ?>
            </option>
        <?php endwhile; ?>

    </select>

    <br><br>

    <label>Quantity</label><br>
    <input
        type="number"
        name="quantity"
        min="1"
        required
    >

    <br><br>

    <label>Cost Price</label><br>
    <input
        type="number"
        step="0.01"
        name="cost_price"
        min="0"
        required
    >

    <br><br>

    <label>Purchase Date</label><br>
    <input
        type="date"
        name="purchase_date"
        value="<?= date('Y-m-d'); ?>"
        required
    >

    <br><br>

    <button type="submit" name="save">
        Save Purchase
    </button>

</form>