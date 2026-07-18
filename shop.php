<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/db.php';

$base="/infinity/";

// ===============================
// CART
// ===============================
if(!isset($_SESSION['cart'])){
    $_SESSION['cart']=[];
}

$cartCount=0;

foreach($_SESSION['cart'] as $item){
    $cartCount += $item['quantity'];
}

// ===============================
// GET ALL CATEGORIES
// ===============================
$categories=$conn->query("
SELECT id,name
FROM categories
ORDER BY name
");
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width,initial-scale=1">

<title>
Mtell Kenya | Shop Online
</title>

<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
}

body{

font-family:'Segoe UI',Arial,sans-serif;
background:#f5f7fa;
color:#222;

}

a{
text-decoration:none;
}

img{
max-width:100%;
display:block;
}

/* ===========================
NAVBAR
=========================== */

.navbar{

background:#1565c0;
padding:15px 40px;

display:flex;

justify-content:space-between;

align-items:center;

position:sticky;

top:0;

z-index:999;

}

.logo{

font-size:28px;

font-weight:bold;

color:#fff;

}

.nav-links{

display:flex;

gap:25px;

align-items:center;

}

.nav-links a{

color:#fff;

font-size:16px;

font-weight:600;

}

.nav-links a:hover{

color:#ffd54f;

}

.cart{

background:#ff9800;

padding:10px 18px;

border-radius:30px;

color:white;

font-weight:bold;

}

/* ===========================
PAGE TITLE
=========================== */

.page-header{

background:#fff;

padding:35px;

text-align:center;

margin-bottom:30px;

box-shadow:0 2px 10px rgba(0,0,0,.08);

}

.page-header h1{

font-size:42px;

margin-bottom:10px;

}

.page-header p{

font-size:18px;

color:#666;

}

/* ===========================
CATEGORY
=========================== */

.category-title{

font-size:30px;

margin:50px 40px 20px;

padding-left:15px;

border-left:6px solid #1565c0;

color:#1565c0;

font-weight:bold;

}

/* ===========================
PRODUCT GRID
=========================== */

.products{

display:grid;

grid-template-columns:
repeat(auto-fill,minmax(240px,1fr));

gap:25px;

padding:0 40px 40px;

}

/* ===========================
CARD
=========================== */

.card{

background:#fff;

border-radius:15px;

overflow:hidden;

box-shadow:0 5px 20px rgba(0,0,0,.08);

transition:.3s;

position:relative;

}

.card:hover{

transform:translateY(-8px);

box-shadow:0 15px 30px rgba(0,0,0,.15);

}

.badge{

position:absolute;

top:15px;

left:15px;

background:#ff3b30;

color:#fff;

padding:6px 12px;

border-radius:20px;

font-size:12px;

font-weight:bold;

}

.card img{

height:220px;

width:100%;

object-fit:contain;

padding:20px;

background:#fff;

}

.card-body{

padding:20px;

}

.card-body h3{

font-size:20px;

height:55px;

margin-bottom:10px;

}

.price{

font-size:28px;

font-weight:bold;

color:#1565c0;

margin:15px 0;

}

.stock{

color:#16a34a;

font-weight:600;

margin-bottom:15px;

}

.btn{

display:block;

width:100%;

padding:12px;

border:none;

border-radius:8px;

font-size:16px;

font-weight:600;

cursor:pointer;

margin-top:10px;

}

.view-btn{

background:#1565c0;

color:#fff;

}

.cart-btn{

background:#ff9800;

color:#fff;

}

.view-btn:hover{

background:#0d47a1;

}

.cart-btn:hover{

background:#f57c00;

}

/* ===========================
FOOTER
=========================== */

footer{

background:#1f2937;

color:white;

text-align:center;

padding:35px;

margin-top:50px;

}

/* ===========================
RESPONSIVE
=========================== */

@media(max-width:768px){

.navbar{

flex-direction:column;

gap:15px;

}

.nav-links{

flex-wrap:wrap;

justify-content:center;

}

.page-header h1{

font-size:30px;

}

.products{

padding:15px;

}

}

</style>

</head>

<body>

<div class="navbar">

<div class="logo">

Mtell Shop

</div>

<div class="nav-links">

<a href="<?= $base ?>index.php">

Home

</a>

<a href="<?= $base ?>shop.php">

Shop

</a>

<a href="<?= $base ?>contact.php">

Contact

</a>

<a
class="cart"
href="<?= $base ?>cart/cart.php">

Cart (<?= $cartCount ?>)

</a>

</div>

</div>

<div class="page-header">

<h1>

Shop Online

</h1>

<p>

Browse our latest online products by category.

</p>

</div>
<?php while($cat = $categories->fetch_assoc()): ?>

<?php

$onlineBranch = 1; // Change if your online branch is different

$products = $conn->query("
SELECT
    id,
    name,
    online_price,
    online_image,
    image,
    stock
FROM products
WHERE
    category_id = {$cat['id']}
    AND online_price > 0
ORDER BY name ASC
");
if($products->num_rows == 0){
    continue;
}

?>

<h2 class="category-title">

    <?= htmlspecialchars($cat['name']) ?>

</h2>

<div class="products">

<?php while($p = $products->fetch_assoc()): ?>

<div class="card">

    <span class="badge">

        NEW

    </span>

    <a href="<?= $base ?>product.php?id=<?= $p['id'] ?>">
<?php

$image = !empty($p['online_image'])
    ? $p['online_image']
    : $p['image'];

?>

<img
src="<?= $base ?>uploads/<?= htmlspecialchars($image) ?>"
alt="<?= htmlspecialchars($p['name']) ?>">

    </a>

    <div class="card-body">

        <h3>

            <?= htmlspecialchars($p['name']) ?>

        </h3>

        <div style="color:#ffc107;font-size:18px;margin:8px 0;">

?????

</div>

        <div class="price">

            KES <?= number_format($p['online_price'],2) ?>

        </div>

        <div class="stock">

In Stock (<?= $p['stock'] ?>)

</div>

        <a
        href="<?= $base ?>product.php?id=<?= $p['id'] ?>"
        class="btn view-btn">

            View Product

        </a>

        <form
        action="<?= $base ?>cart/add-to-cart.php"
        method="POST">

            <input
            type="hidden"
            name="product_id"
            value="<?= $p['id'] ?>">

            <input
            type="hidden"
            name="quantity"
            value="1">

            <button
            type="submit"
            class="btn cart-btn">

                Add to Cart

            </button>

        </form>

    </div>

</div>

<?php endwhile; ?>

</div>

<?php endwhile; ?>
<!-- ===========================
FOOTER
=========================== -->

<footer>

    <h2 style="margin-bottom:15px;">

        Mtell Online Shopping

    </h2>

    <p style="margin-bottom:15px;">

        Kenya's trusted online marketplace for Phones,
        Electronics, Fashion, Beauty and Home Appliances.

    </p>

    <div style="
        display:flex;
        justify-content:center;
        gap:30px;
        flex-wrap:wrap;
        margin:25px 0;
    ">

        <a href="<?= $base ?>index.php"
           style="color:white;">
            Home
        </a>

        <a href="<?= $base ?>about.php"
           style="color:white;">
            About
        </a>

        <a href="<?= $base ?>contact.php"
           style="color:white;">
            Contact
        </a>

        <a href="<?= $base ?>privacy.php"
           style="color:white;">
            Privacy Policy
        </a>

        <a href="<?= $base ?>terms.php"
           style="color:white;">
            Terms & Conditions
        </a>

    </div>

    <div