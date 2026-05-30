

<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/init.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $conn->prepare("
    SELECT * FROM purchases
    WHERE id=?
");

$stmt->bind_param("i", $id);
$stmt->execute();

$purchase = $stmt->get_result()->fetch_assoc();

if(!$purchase){
    die("Purchase not found");
}

$products = $conn->query("
    SELECT * FROM products
    ORDER BY product_name ASC
");

$suppliers = $conn->query("
    SELECT * FROM suppliers
    ORDER BY supplier_name ASC
");

if(isset($_POST['update'])){

    $supplier_id = (int)$_POST['supplier_id'];

    $product_id = (int)$_POST['product_id'];

    $quantity = (int)$_POST['quantity'];

    $cost_price = (float)$_POST['cost_price'];

    $purchase_date = $_POST['purchase_date'];

    $total_cost = $quantity * $cost_price;

    $stmt = $conn->prepare("
        UPDATE purchases
        SET
            supplier_id=?,
            product_id=?,
            quantity=?,
            cost_price=?,
            total_cost=?,
            purchase_date=?
        WHERE id=?
    ");

    $stmt->bind_param(
        "iiiddsi",
        $supplier_id,
        $product_id,
        $quantity,
        $cost_price,
        $total_cost,
        $purchase_date,
        $id
    );

    $stmt->execute();

    header("Location: purchases.php");
    exit;
}
?>

<h2>✏ Edit Purchase</h2>

<form method="POST">

<select name="supplier_id">

<?php while($s = $suppliers->fetch_assoc()): ?>

<option value="<?= $s['id'] ?>"
<?= $purchase['supplier_id']==$s['id'] ? 'selected' : '' ?>>

<?= htmlspecialchars($s['supplier_name']) ?>

</option>

<?php endwhile; ?>

</select>

<br><br>

<select name="product_id">

<?php while($p = $products->fetch_assoc()): ?>

<option value="<?= $p['id'] ?>"
<?= $purchase['product_id']==$p['id'] ? 'selected' : '' ?>>

<?= htmlspecialchars($p['product_name']) ?>

</option>

<?php endwhile; ?>

</select>

<br><br>

<input type="number"
       name="quantity"
       value="<?= $purchase['quantity'] ?>"
       required>

<br><br>

<input type="number"
       step="0.01"
       name="cost_price"
       value="<?= $purchase['cost_price'] ?>"
       required>

<br><br>

<input type="date"
       name="purchase_date"
       value="<?= $purchase['purchase_date'] ?>"
       required>

<br><br>

<button type="submit" name="update">
    Update Purchase
</button>

</form>