<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<h2>📦 Stock Transfer</h2>";

$result = $conn->query("
    SELECT p.name, p.stock_quantity, b.branch_name
    FROM products p
    LEFT JOIN branches b ON p.branch_id = b.id
    ORDER BY p.name ASC
");
?>

<div class="card">
    <h3>Transfer Stock Between Branches</h3>

    <form method="POST">

        <label>Product</label><br>
        <select name="product_id" required>
            <?php
            if ($result && $result->num_rows > 0):
                while($row = $result->fetch_assoc()):
            ?>
                <option value="<?= $row['id'] ?? '' ?>">
                    <?= htmlspecialchars($row['name']) ?>
                    (<?= $row['stock_quantity'] ?> in stock)
                </option>
            <?php endwhile; endif; ?>
        </select><br><br>

        <label>From Branch</label><br>
        <input type="text" name="from_branch" required><br><br>

        <label>To Branch</label><br>
        <input type="text" name="to_branch" required><br><br>

        <label>Quantity</label><br>
        <input type="number" name="quantity" required><br><br>

        <button type="submit">Transfer Stock</button>

    </form>
</div>

<style>
.card{
    background:#fff;
    padding:20px;
    border-radius:8px;
    box-shadow:0 0 10px #ccc;
}
button{
    background:#007bff;
    color:white;
    border:none;
    padding:10px 15px;
    border-radius:5px;
}
</style>