<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/db.php';

$base = "/infinity/";

// ==========================
// 🛒 CART
// ==========================
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
$cartCount = array_sum(array_column($_SESSION['cart'], 'quantity'));

// ==========================
// 🔍 FILTERS
// ==========================
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$category_id = (int)($_GET['category_id'] ?? 0);
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';
$sort = $_GET['sort'] ?? '';

// ==========================
// 📄 PAGINATION
// ==========================
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 12;
$offset = ($page - 1) * $limit;

// ==========================
// 🧠 BASE QUERY
// ==========================
$baseSql = "
FROM products p
JOIN branch_stock bs ON p.id = bs.product_id
WHERE bs.branch_id = 1
AND bs.stock > 0
AND p.online_price > 0
";

// FILTERS
if($search){
    $baseSql .= " AND p.name LIKE '%".$conn->real_escape_string($search)."%'";
}

if($category_id > 0){

    $baseSql .= " AND p.category_id = ".$category_id;

}
elseif($category){

    $baseSql .= " AND p.category = '".$conn->real_escape_string($category)."'";

}

if($min_price){
    $baseSql .= " AND p.online_price >= ".(float)$min_price;
}

if($max_price){
    $baseSql .= " AND p.online_price <= ".(float)$max_price;
}



// ==========================
// 🔽 SORTING
// ==========================
$orderBy = "p.id DESC";

if($sort == 'price_asc') $orderBy = "p.online_price ASC";
if($sort == 'price_desc') $orderBy = "p.online_price DESC";

// ==========================
// 📊 COUNT TOTAL
// ==========================
$countRes = $conn->query("SELECT COUNT(*) as total $baseSql");
$totalRows = $countRes->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

// ==========================
// 📦 FINAL QUERY
// ==========================
$sql = "SELECT p.*, bs.stock $baseSql ORDER BY $orderBy LIMIT $limit OFFSET $offset";
$res = $conn->query($sql);

// ==========================
// 📂 CATEGORIES
// ==========================
$cats = $conn->query("
    SELECT id, name
    FROM categories
    ORDER BY name
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Shop - Mtell Kenya|Online Shop</title>

<style>

:root {
    --primary: #1e293b;
    --secondary: #334155;
    --accent: #f59e0b;
    --dark: #0f172a;
    --light: #f8fafc;
}

body {
    font-family: Arial;
    background: var(--light);
    margin: 0;
}

.nav {
    background: var(--primary);
    padding: 15px 30px;
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.nav a {
    color: white;
    text-decoration: none;
    margin: 0 10px;
    font-weight: bold;
}

.container {
    display: flex;
}

.sidebar {
    width: 250px;
    background: white;
    padding: 20px;
    border-right: 1px solid #ddd;
}

.content {
    flex: 1;
    padding: 20px;
}

.grid {
    display: grid;
    grid-template-columns: repeat(auto-fit,minmax(220px,1fr));
    gap: 20px;
}

.card {
    background: white;
    padding: 15px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,.1);
    text-align: center;
}

.card img {
    width: 100%;
    height: 120px;
    object-fit: contain;
}

.view-btn{
    display:block;
    background:#2563eb;
    color:white;
    text-decoration:none;
    padding:10px;
    border-radius:5px;
    margin:10px 0;
}

input,
select {
    width: 100%;
    padding: 8px;
    margin-bottom: 10px;
}

button {
    padding: 10px;
    width: 100%;
    cursor: pointer;
    background: var(--accent);
    color: white;
    border: none;
    border-radius: 5px;
}

.pagination button {
    width: auto;
    margin: 5px;
}

</style>

</head>

<body>

<!-- NAV -->
<div class="nav">
    <div>🛍️ Mtell Shop</div>
    <div>
        <a href="<?= $base ?>index.php">Home</a>
        <a href="<?= $base ?>cart/cart.php">🛒 Cart (<?= $cartCount ?>)</a>
    </div>
</div>

<div class="container">

<!-- SIDEBAR -->
<div class="sidebar">

<form method="GET">

<h3>🔍 Search</h3>
<input type="text" name="search" value="<?= htmlspecialchars($search) ?>">

<h3>📂 Category</h3>
<select name="category_id">
<option value="">All Categories</option>

<?php while($c = $cats->fetch_assoc()): ?>

<option
    value="<?= $c['id'] ?>"
    <?= $category_id==$c['id'] ? 'selected' : '' ?>
>
    <?= htmlspecialchars($c['name']) ?>
</option>

<?php endwhile; ?>

</select>

<h3>💰 Price</h3>
<input type="number" name="min_price" placeholder="Min" value="<?= $min_price ?>">
<input type="number" name="max_price" placeholder="Max" value="<?= $max_price ?>">

<h3>🔽 Sort</h3>
<select name="sort">
<option value="">Default</option>
<option value="price_asc" <?= $sort=='price_asc'?'selected':'' ?>>Low → High</option>
<option value="price_desc" <?= $sort=='price_desc'?'selected':'' ?>>High → Low</option>
</select>

<button type="submit">Apply</button>

</form>

</div>

<!-- PRODUCTS -->
<div class="content">

<h2>Products</h2>

<div class="grid">

<?php while($p = $res->fetch_assoc()): ?>

<div class="card">

<img
    src="<?= $base ?>uploads/<?= htmlspecialchars($p['online_image'] ?: 'default.png') ?>"
    alt="<?= htmlspecialchars($p['name']) ?>"
>

<h3><?= htmlspecialchars($p['name']) ?></h3>

<a href="<?= $base ?>product.php?id=<?= $p['id'] ?>" class="view-btn">
    View Product
</a>

<p>KES <?= number_format($p['online_price'],2) ?></p>
<p>Stock: <?= $p['stock'] ?></p>

<form method="POST" action="<?= $base ?>cart/add-to-cart.php">
<input type="hidden" name="product_id" value="<?= $p['id'] ?>">
<input type="number" name="quantity" value="1" min="1">
<button>Add to Cart</button>
</form>

</div>

<?php endwhile; ?>

</div>

<!-- PAGINATION -->
<div class="pagination" style="text-align:center; margin-top:20px;">

<?php for($i=1; $i <= $totalPages; $i++): ?>

<a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&category_id=<?= $category_id ?>&sort=<?= $sort ?>">
<button <?= $i==$page?'style="background:black;color:white"':'' ?>>
<?= $i ?>
</button>
</a>

<?php endfor; ?>

</div>

</div>

</div>

</body>
</html>