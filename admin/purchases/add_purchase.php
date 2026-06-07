<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/init.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($conn) || !$conn instanceof mysqli) {
    die("❌ Database connection failed");
}

// Products
$products = $conn->query("
    SELECT id, name
    FROM products
    ORDER BY name ASC
");

// Suppliers
$suppliers = $conn->query("
    SELECT id, name
    FROM suppliers
    ORDER BY name ASC
");

// ==========================
// SAVE PURCHASE
// ==========================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $supplier_id   = (int)($_POST['supplier_id'] ?? 0);
    $product_id    = (int)($_POST['product_id'] ?? 0);
    $quantity      = (int)($_POST['quantity'] ?? 0);
    $cost_price    = (float)($_POST['cost_price'] ?? 0);
    $purchase_date = $_POST['purchase_date'] ?? date('Y-m-d');

    if (
        $supplier_id <= 0 ||
        $product_id <= 0 ||
        $quantity <= 0 ||
        $cost_price <= 0
    ) {
        die("❌ Please fill all fields correctly.");
    }

    $branch_id = (int)($_SESSION['branch_id'] ?? 1);
    $created_by = $_SESSION['username'] ?? 'System';

    $total_cost = $quantity * $cost_price;
    $status = 'Completed';

    $conn->begin_transaction();

    try {

        // ==========================
        // INSERT PURCHASE
        // ==========================
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
            VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            )
        ");

        if (!$stmt) {
            throw new Exception($conn->error);
        }

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
            throw new Exception($stmt->error);
        }

        // ==========================
        // UPDATE PRODUCT STOCK
        // ==========================
        $update = $conn->prepare("
            UPDATE products
            SET
                stock = stock + ?,
                cost_price = ?,
                cost = ?
            WHERE id = ?
        ");

        if (!$update) {
            throw new Exception($conn->error);
        }

        $update->bind_param(
            "iddi",
            $quantity,
            $cost_price,
            $cost_price,
            $product_id
        );

        if (!$update->execute()) {
            throw new Exception($update->error);
        }

        $conn->commit();

        echo "
        <script>
            alert('✅ Purchase saved successfully');
            window.location='?tab=purchases';
        </script>";
        exit;

    } catch (Exception $e) {

        $conn->rollback();

        die(
            '❌ Purchase Error: ' .
            htmlspecialchars($e->getMessage())
        );
    }
}
?>

<h2>➕ Add Purchase</h2>

<form method="POST">

    <label>Supplier</label><br>
    <select name="supplier_id" required>
        <option value="">Select Supplier</option>

        <?php while ($s = $suppliers->fetch_assoc()): ?>
            <option value="<?= $s['id'] ?>">
                <?= htmlspecialchars($s['name']) ?>
            </option>
        <?php endwhile; ?>

    </select>

    <br><br>

    <label>Product</label><br>
    <select name="product_id" required>
        <option value="">Select Product</option>

        <?php while ($p = $products->fetch_assoc()): ?>
            <option value="<?= $p['id'] ?>">
                <?= htmlspecialchars($p['name']) ?>
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
        min="0.01"
        name="cost_price"
        required
    >

    <br><br>

    <label>Purchase Date</label><br>
    <input
        type="date"
        name="purchase_date"
        value="<?= date('Y-m-d') ?>"
        required
    >

    <br><br>

    <button type="submit">
        💾 Save Purchase
    </button>

</form>