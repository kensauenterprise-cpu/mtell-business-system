<?php
session_start();

// ==========================
// 🔗 BASE CONFIG (FINAL)
// ==========================
define('BASE_URL', '/infinity/');
define('BASE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/infinity/');

// ==========================
// 🛒 CART
// ==========================
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$cartCount = 0;
foreach ($_SESSION['cart'] as $item) {
    $cartCount += $item['quantity'] ?? 0;
}

// ==========================
// 🔗 DB CONNECTION
// ==========================
require_once BASE_PATH . 'admin/includes/db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mtell Smart Business</title>

<style>
body { margin:0; font-family:Segoe UI, Arial; background:#f5f7fa; }

/* NAVBAR */
.navbar {
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:15px 30px;
    background:#0d6efd;
    color:white;
}
.navbar a {
    color:white;
    text-decoration:none;
    margin:0 10px;
    font-weight:bold;
}

/* HERO */
.hero {
    background:linear-gradient(to right,#0d6efd,#4dabf7);
    color:white;
    padding:80px 20px;
    text-align:center;
}
.hero h1 { font-size:40px; }
.hero p { font-size:18px; }

.btn {
    padding:12px 20px;
    margin:10px;
    border:none;
    cursor:pointer;
    border-radius:5px;
}
.btn-primary { background:#ffc107; }

/* GRID */
.section { padding:40px 20px; }
.section h2 { text-align:center; }

.grid {
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:20px;
}

.card {
    background:white;
    padding:15px;
    border-radius:10px;
    box-shadow:0 2px 10px rgba(0,0,0,0.1);
    text-align:center;
}
.card img {
    width:100%;
    height:150px;
    object-fit:cover;
}

/* FOOTER */
.footer {
    background:#212529;
    color:white;
    text-align:center;
    padding:20px;
}
</style>
</head>

<body>

<!-- NAVBAR -->
<div class="navbar">
    <div><strong>🚀 Mtell</strong></div>
    <div>
        <a href="<?= BASE_URL ?>index.php">Home</a>
        <a href="<?= BASE_URL ?>shop.php">Shop</a>
        <a href="<?= BASE_URL ?>about.php">About</a>
        <a href="<?= BASE_URL ?>contact.php">Contact</a>
        <a href="<?= BASE_URL ?>cart/cart.php">🛒 Cart (<?= $cartCount ?>)</a>

        <!-- ✅ FIXED LOGIN LINK -->
        <a href="<?= BASE_URL ?>admin/pages/login.php">🔐 Login</a>
    </div>
</div>

<!-- HERO -->
<div class="hero">
    <h1>Smart Electronics Shopping</h1>
    <p>Fast delivery. Real-time stock. Easy checkout.</p>

    <a href="<?= BASE_URL ?>shop.php">
        <button class="btn btn-primary">🛍️ Shop Now</button>
    </a>
</div>

<!-- FEATURES -->
<div class="section">
    <h2>Why Choose Us</h2>
    <div class="grid">
        <div class="card">🚚 Fast Delivery</div>
        <div class="card">💳 M-Pesa Payments</div>
        <div class="card">📦 Live Stock Tracking</div>
        <div class="card">🛡️ Secure System</div>
    </div>
</div>

<!-- PRODUCTS -->
<div class="section">
    <h2>Featured Products</h2>

    <div class="grid">

    <?php
    if ($conn) {

        // ✅ SAFE QUERY (NO CRASH)
        $sql = "SELECT id, name, price, image FROM products WHERE stock > 0 LIMIT 6";
        $res = $conn->query($sql);

        if ($res && $res->num_rows > 0):
            while($p = $res->fetch_assoc()):
    ?>

    <div class="card">
        <img src="<?= BASE_URL ?>uploads/<?= htmlspecialchars($p['image']) ?>" alt="">

        <h3>
            <a href="<?= BASE_URL ?>product.php?id=<?= $p['id'] ?>">
                <?= htmlspecialchars($p['name']) ?>
            </a>
        </h3>

        <p>KES <?= number_format($p['price'], 2) ?></p>

        <form method="POST" action="<?= BASE_URL ?>cart/add-to-cart.php">
            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
            <button class="btn btn-primary">Add to Cart</button>
        </form>
    </div>

    <?php 
            endwhile;
        else:
            echo "<p style='text-align:center;'>No products available.</p>";
        endif;

    } else {
        echo "<p style='text-align:center;color:red;'>Database connection error.</p>";
    }
    ?>

    </div>
</div>

<!-- FOOTER -->
<div class="footer">
    <p>© <?= date('Y') ?> Mtell Smart Business System</p>
</div>

</body>
</html>