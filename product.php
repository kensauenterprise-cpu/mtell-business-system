```php
<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/db.php';

$base = "/infinity/";

$id = (int)($_GET['id'] ?? 0);

if($id <= 0){
    die("Invalid Product");
}

$stmt = $conn->prepare("
SELECT *
FROM products
WHERE id=?
LIMIT 1
");

$stmt->bind_param("i",$id);
$stmt->execute();

$result = $stmt->get_result();

if($result->num_rows == 0){
    die("Product not found");
}

$product = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">

<title>
<?= htmlspecialchars($product['name']) ?>
 | Mtell Kenya
</title>

<style>

body{
    margin:0;
    background:#f5f5f5;
    font-family:Arial,sans-serif;
}

.nav{
    background:#0d6efd;
    color:white;
    padding:15px 30px;
    display:flex;
    justify-content:space-between;
}

.nav a{
    color:white;
    text-decoration:none;
    margin-left:15px;
}

.container{
    max-width:1200px;
    margin:30px auto;
    background:white;
    padding:30px;
    border-radius:10px;
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:40px;
}

.image-box{
    text-align:center;
}

.image-box img{
    width:100%;
    max-width:450px;
    height:450px;
    object-fit:contain;
}

.price{
    font-size:30px;
    font-weight:bold;
    color:#198754;
}

.stock{
    color:#0d6efd;
    font-weight:bold;
}

.description{
    margin:20px 0;
    line-height:1.6;
}

.qty{
    width:80px;
    padding:10px;
}

.add-btn{
    background:#ff9900;
    color:white;
    border:none;
    padding:14px 25px;
    cursor:pointer;
    border-radius:5px;
    font-size:16px;
}

.add-btn:hover{
    background:#e68900;
}

.back{
    display:inline-block;
    margin-top:20px;
    color:#0d6efd;
    text-decoration:none;
}

@media(max-width:768px){

.container{
    grid-template-columns:1fr;
}

.image-box img{
    height:300px;
}

}

</style>

</head>

<body>

<div class="nav">

    <div>
        <strong>🛍️ Mtell Kenya</strong>
    </div>

    <div>
        <a href="<?= $base ?>index.php">Home</a>
        <a href="<?= $base ?>shop.php">Shop</a>
        <a href="<?= $base ?>cart/cart.php">Cart</a>
    </div>

</div>

<div class="container">

    <div class="image-box">

        <img
        src="<?= $base ?>uploads/<?= htmlspecialchars($product['online_image'] ?: 'default.png') ?>"
        alt="<?= htmlspecialchars($product['name']) ?>"
        >

    </div>

    <div>

        <h1>
            <?= htmlspecialchars($product['name']) ?>
        </h1>

        <div class="price">
            KES <?= number_format($product['online_price'],2) ?>
        </div>

        <p class="stock">
            Stock Available:
            <?= (int)$product['stock'] ?>
        </p>

        <div class="description">

            <?= nl2br(htmlspecialchars($product['description'])) ?>

        </div>

        <form
        method="POST"
        action="<?= $base ?>cart/add-to-cart.php"
        >

            <input
            type="hidden"
            name="product_id"
            value="<?= $product['id'] ?>"
            >

            <label>
                Quantity
            </label>

            <br><br>

            <input
            class="qty"
            type="number"
            name="quantity"
            value="1"
            min="1"
            max="<?= max(1,(int)$product['stock']) ?>"
            >

            <br><br>

            <button
            type="submit"
            class="add-btn"
            >
                🛒 Add To Cart
            </button>

        </form>

        <a
        href="<?= $base ?>shop.php"
        class="back"
        >
            ← Continue Shopping
        </a>

    </div>

</div>

</body>
</html>
```
