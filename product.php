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

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<?php

$productName = htmlspecialchars($product['name']);

$productDescription = !empty($product['description'])
    ? strip_tags($product['description'])
    : "Buy {$product['name']} online at Mtell Kenya.";

$productDescription = substr($productDescription,0,160);

$productImage = $base."uploads/".(!empty($product['online_image'])
    ? $product['online_image']
    : "default.png");

$productUrl =
"https://mtell.kesug.com/product.php?id=".$product['id'];

?>

<title>
<?= $productName ?> | Buy Online Kenya | Mtell Kenya
</title>

<meta name="description"
content="<?= htmlspecialchars($productDescription) ?>">

<meta name="keywords"
content="<?= $productName ?>,
Mtell Kenya,
Online Shopping Kenya,
Smartphones Kenya,
Electronics Kenya">

<meta name="robots"
content="index,follow">

<link rel="canonical"
href="<?= $productUrl ?>">

<!-- Open Graph -->

<meta property="og:type"
content="product">

<meta property="og:title"
content="<?= $productName ?>">

<meta property="og:description"
content="<?= htmlspecialchars($productDescription) ?>">

<meta property="og:image"
content="https://mtell.kesug.com/uploads/<?= htmlspecialchars($product['online_image']) ?>">

<meta property="og:url"
content="<?= $productUrl ?>">

<meta property="og:site_name"
content="Mtell Kenya">

<!-- Twitter -->

<meta name="twitter:card"
content="summary_large_image">

<meta name="twitter:title"
content="<?= $productName ?>">

<meta name="twitter:description"
content="<?= htmlspecialchars($productDescription) ?>">

<meta name="twitter:image"
content="https://mtell.kesug.com/uploads/<?= htmlspecialchars($product['online_image']) ?>">

<!-- Product Schema -->

<script type="application/ld+json">

{
"@context":"https://schema.org",
"@type":"Product",

"name":"<?= addslashes($product['name']) ?>",

"image":"https://mtell.kesug.com/uploads/<?= htmlspecialchars($product['online_image']) ?>",

"description":"<?= addslashes($productDescription) ?>",

"sku":"<?= $product['id'] ?>",

"brand":{

"@type":"Brand",

"name":"Mtell Kenya"

},

"offers":{

"@type":"Offer",

"url":"<?= $productUrl ?>",

"priceCurrency":"KES",

"price":"<?= $product['online_price'] ?>",

"availability":"<?= $product['stock']>0 ? 'https://schema.org/InStock':'https://schema.org/OutOfStock' ?>"

}

}

</script>

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
line-height:1.7;

}

.qty{

width:90px;
padding:10px;

}

.add-btn{

background:#ff9800;
color:white;
border:none;
padding:14px 28px;
border-radius:8px;
cursor:pointer;
font-size:16px;

}

.add-btn:hover{

background:#e68900;

}

.back{

display:inline-block;
margin-top:20px;
text-decoration:none;
color:#0d6efd;

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
        <strong>??? Mtell Kenya</strong>
    </div>

    <div>
        <a href="<?= $base ?>index.php">Home</a>
        <a href="<?= $base ?>shop.php">Shop</a>
        <a href="<?= $base ?>cart/cart.php">Cart</a>
    </div>

</div>



<!-- BREADCRUMB -->

<div style="max-width:1200px;margin:25px auto 0;">

<a href="<?= $base ?>index.php">Home</a>

>

<a href="<?= $base ?>shop.php">Shop</a>

>

<span><?= htmlspecialchars($product['name']) ?></span>

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

<div style="
background:#f8f9fa;
padding:15px;
margin:20px 0;
border-radius:10px;">

<p>

<strong>Brand:</strong> Mtell Kenya

</p>

<p>

<strong>Product ID:</strong>

<?= $product['id'] ?>

</p>

<p>

<strong>Payment:</strong>

M-Pesa • Card • Cash

</p>

<p>

<strong>Delivery:</strong>

Nationwide Delivery

</p>

</div>
        <p class="stock">

<?php if($product['stock']>0): ?>

<span
style="
background:#198754;
color:white;
padding:6px 12px;
border-radius:20px;">

✅ In Stock

</span>

<?php else: ?>

<span
style="
background:#dc3545;
color:white;
padding:6px 12px;
border-radius:20px;">

❌ Out of Stock

</span>

<?php endif; ?>

&nbsp;

Available Quantity:

<strong>

<?= (int)$product['stock'] ?>

</strong>

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
                ?? Add To Cart
            </button>

        </form>

<br>

<a

href="https://wa.me/?text=<?= urlencode($product['name'].' https://mtell.kesug.com/product.php?id='.$product['id']) ?>"

target="_blank"

style="
display:inline-block;
background:#25D366;
color:white;
padding:12px 20px;
border-radius:6px;
text-decoration:none;
margin-top:15px;">

📲 Share on WhatsApp

</a>

<br><br>

<a
href="<?= $base ?>shop.php"
class="back"
>
            ? Continue Shopping
        </a>

    </div>

</div>

</body>
</html>

