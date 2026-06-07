<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/db.php';

if(session_status() === PHP_SESSION_NONE){
    session_start();
}

if(isset($_POST['adjust_stock'])){

    $product_id = (int)$_POST['product_id'];
    $qty        = (int)$_POST['quantity'];
    $action     = $_POST['action'];

    if($action == 'add'){

        $sql = "
        UPDATE products
        SET stock = stock + ?
        WHERE id = ?
        ";

    }else{

        $sql = "
        UPDATE products
        SET stock = stock - ?
        WHERE id = ?
        ";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii",$qty,$product_id);
    $stmt->execute();
    $stmt->close();

    echo "<div style='color:green'>Stock Updated Successfully</div>";
}

$products = $conn->query("
    SELECT id,name,stock
    FROM products
    ORDER BY name ASC
");
?>

<h2>📦 Stock Adjustment</h2>

<form method="POST">

<select name="product_id" required>

<?php while($p = $products->fetch_assoc()): ?>

<option value="<?= $p['id'] ?>">
    <?= htmlspecialchars($p['name']) ?>
    (Stock: <?= $p['stock'] ?>)
</option>

<?php endwhile; ?>

</select>

<input
type="number"
name="quantity"
required
min="1"
placeholder="Quantity"
>

<select name="action">

<option value="add">
Add Stock
</option>

<option value="subtract">
Remove Stock
</option>

</select>

<button
type="submit"
name="adjust_stock"
>
Update Stock
</button>

</form>