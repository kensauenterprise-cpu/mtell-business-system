<?php
if (!isset($conn)) {
    require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/db.php';
}

$categories = [];

$result = $conn->query("SELECT id,name FROM categories ORDER BY name ASC");

if($result){
    while($row = $result->fetch_assoc()){
        $categories[] = $row;
    }
}

$cartCount = 0;

if(isset($_SESSION['cart'])){
    foreach($_SESSION['cart'] as $item){
        $cartCount += $item['qty'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title><?= htmlspecialchars($page_title ?? 'Mtell Online Shopping'); ?></title>

<meta name="description" content="<?= htmlspecialchars($page_description ?? 'Shop online in Kenya'); ?>">

<meta name="keywords" content="Mtell Online Shopping, Kenya, Phones, Electronics, Smartphones">

<link rel="icon" href="/infinity/assets/images/favicon.png">

<link rel="stylesheet" href="/infinity/assets/css/style.css">
<link rel="stylesheet" href="/infinity/assets/css/responsive.css">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

</head>

<body>

<div class="top-bar">

<div class="container">

<div class="top-left">
<i class="fa-solid fa-truck-fast"></i>
Free Delivery Across Kenya
</div>

<div class="top-right">

<a href="/infinity/customer_login.php">
<i class="fa fa-user"></i> Login
</a>

<a href="/infinity/signup.php">
Register
</a>

</div>

</div>

</div>

<header class="main-header">

<div class="container header-flex">

<div class="logo">

    <a href="/" class="logo-link">

        <div class="logo-text">
            <h1>Mtell Online Shopping</h1>
            <p>Shop Smart. Live Better.</p>
        </div>

    </a>

</div>

<div class="search-area">

<form action="/infinity/shop.php" method="GET">

<input
type="text"
name="search"
placeholder="Search phones, electronics, accessories..."
required>

<button type="submit">

<i class="fa fa-search"></i>

</button>

</form>

</div>

<div class="header-icons">

<a href="/infinity/cart/cart.php">

<i class="fa-solid fa-cart-shopping"></i>

<span class="cart-count"><?= $cartCount ?></span>

</a>

<a href="/infinity/customer_login.php">

<i class="fa-solid fa-user"></i>

</a>

</div>

</div>

</header>

<nav class="navbar">

<div class="container">

<ul>

<li><a href="/">Home</a></li>

<li><a href="/infinity/shop.php">Shop</a></li>

<li class="dropdown">

<a href="#">Categories <i class="fa fa-angle-down"></i></a>

<ul class="dropdown-menu">

<?php foreach($categories as $cat): ?>

<li>

<a href="/infinity/shop.php?category=<?= $cat['id']; ?>">

<?= htmlspecialchars($cat['name']); ?>

</a>

</li>

<?php endforeach; ?>

</ul>

</li>

<li><a href="/infinity/deals.php">Today's Deals</a></li>

<li><a href="/infinity/about.php">About</a></li>

<li><a href="/infinity/contact.php">Contact</a></li>

</ul>

</div>

</nav>

<main>