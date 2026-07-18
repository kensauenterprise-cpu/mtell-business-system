<?php

require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/init.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/db.php';

// UTF-8 FIX
header('Content-Type: text/html; charset=UTF-8');

if (isset($conn) && $conn instanceof mysqli) {
    mysqli_set_charset($conn, "utf8mb4");
}

// SESSION
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// ==========================
// ? DB CHECK
// ==========================
if (!isset($conn) || !$conn) {
    die("? Database connection failed");
}

// ==========================
// ?? BRANCH (NEW)
// ==========================
$branch_id = $_SESSION['branch_id'] ?? 1;

// ==========================
// ?? MODE
// ==========================
$mode = $_GET['mode'] ?? 'online';

// ==========================
// ?? FILTERS
// ==========================
$search = $_GET['search'] ?? '';
$category_id = (int)($_GET['category_id'] ?? 0);


// ==========================
// ? SAVE PRODUCT (ADD / UPDATE)
// ==========================
if (isset($_POST['save_product'])) {

    $id         = $_POST['id'] ?? null;

    $name       = trim($_POST['name'] ?? '');

    $desc       = trim($_POST['description'] ?? '');
    $retail     = (float) ($_POST['retail_price'] ?? 0);
    $wholesale  = (float) ($_POST['wholesale_price'] ?? 0);
    $online     = (float) ($_POST['online_price'] ?? 0);

    $cost       = (float) ($_POST['cost_price'] ?? 0);
$wholesale_cost =
(float)($_POST['wholesale_cost_price'] ?? 0);
    $stock      = (int) ($_POST['stock'] ?? 0);

    $category_id = (int)($_POST['category_id'] ?? 0);
$featured = isset($_POST['featured']) ? 1 : 0;
$is_new   = isset($_POST['is_new']) ? 1 : 0;
$online_image    = $_POST['existing_online_image'] ?? '';

$targetDir = $_SERVER['DOCUMENT_ROOT'].'/infinity/uploads/';

if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
}



// Online Image
if (!empty($_FILES['online_image']['name'])) {

    $ext = strtolower(pathinfo($_FILES['online_image']['name'], PATHINFO_EXTENSION));

    $online_image = time().'_online_'.uniqid().'.'.$ext;

    move_uploaded_file(
        $_FILES['online_image']['tmp_name'],
        $targetDir.$online_image
    );
}
    // ==========================
    // ?? UPDATE
    // ==========================
    if ($id) {

    $price = $retail;
$image = $online_image;

$stmt = $conn->prepare("
UPDATE products
SET
    name=?,
    description=?,
    price=?,
    image=?,
    retail_price=?,
    wholesale_price=?,
    online_price=?,
    cost_price=?,
    wholesale_cost_price=?,
    stock=?,
    category_id=?,
    online_image=?,
featured=?,
is_new=?
WHERE id=? AND branch_id=?
");
        if ($stmt) {

    $stmt->bind_param(
    "ssdsdddddiisiiii",
    $name,
    $desc,
    $price,
    $image,
    $retail,
    $wholesale,
    $online,
    $cost,
    $wholesale_cost,
    $stock,
    $category_id,
    $online_image,
    $featured,
    $is_new,
    $id,
    $branch_id
);

    $stmt->execute();
    $stmt->close();
}

    } else {

        // ==========================
// INSERT
// ==========================

$price = $retail;
$image = $online_image;

$stmt = $conn->prepare("
INSERT INTO products
(
    name,
    description,
    price,
    image,
    retail_price,
    wholesale_price,
    online_price,
    cost_price,
    wholesale_cost_price,
    stock,
    category_id,
    online_image,
    featured,
    is_new,
    branch_id,
    created_at
)
VALUES
(
    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()
)
");

if ($stmt) {

    $stmt->bind_param(
        "ssdsdddddiisiii",
        $name,
        $desc,
        $price,
        $image,
        $retail,
        $wholesale,
        $online,
        $cost,
        $wholesale_cost,
        $stock,
        $category_id,
        $online_image,
        $featured,
        $is_new,
        $branch_id
    );

    $stmt->execute();
    $stmt->close();
}
    }
header("Location: ?tab=products&mode=".$mode."&updated=1");

    exit;
}

// ==========================
// ?? DELETE (SECURED)
// ==========================
if (isset($_POST['delete_id'])) {

    $delete_id = (int)$_POST['delete_id'];

    $stmt = $conn->prepare("
        DELETE FROM products
        WHERE id=? AND branch_id=?
    ");

    if ($stmt) {

        $stmt->bind_param(
            "ii",
            $delete_id,
            $branch_id
        );

        $stmt->execute();

        $stmt->close();
    }

    header("Location: ?tab=products&mode=".$mode);

    exit;
}

// ==========================
// ?? FETCH PRODUCTS
// ==========================
$sql = "
SELECT p.*, c.name AS category_name
FROM products p
LEFT JOIN categories c
ON p.category_id = c.id
WHERE p.branch_id=?
";
$params = [$branch_id];

$types = "i";

if ($search) {

    $sql .= " AND p.name LIKE ?";

    $params[] = "%".$search."%";

    $types .= "s";
}

if ($category_id > 0) {

    $sql .= " AND p.category_id=?";

    $params[] = $category_id;

    $types .= "i";
}

$sql .= " ORDER BY id DESC";

$stmt = $conn->prepare($sql);

if (!$stmt) {

    die("? Failed to load products");
}

$stmt->bind_param($types, ...$params);

$stmt->execute();

$result = $stmt->get_result();

// ==========================
// ?? TOTAL PRODUCTS
// ==========================
$total_products = 0;

$stmt2 = $conn->prepare("
    SELECT COUNT(*) as total
    FROM products
    WHERE branch_id=?
");

if ($stmt2) {

    $stmt2->bind_param("i", $branch_id);

    $stmt2->execute();

    $res = $stmt2->get_result();

    if ($res && $row = $res->fetch_assoc()) {

        $total_products = $row['total'];
    }

    $stmt2->close();
}

// ==========================
// ?? LOW STOCK
// ==========================
$low_stock = 0;

$stmt3 = $conn->prepare("
    SELECT COUNT(*) as total
    FROM products
    WHERE stock < 5
    AND branch_id=?
");

if ($stmt3) {

    $stmt3->bind_param("i", $branch_id);

    $stmt3->execute();

    $res = $stmt3->get_result();

    if ($res && $row = $res->fetch_assoc()) {

        $low_stock = $row['total'];
    }

    $stmt3->close();
}

?>
<style>
.product-form{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:10px;
    margin-bottom:20px;
}

.product-form input,
.product-form textarea,
.product-form select{
    width:100%;
    padding:10px;
    border:1px solid #ccc;
    border-radius:6px;
}

.product-form button{
    padding:10px;
}
</style>

<h2>
Products (<?= strtoupper($mode) ?>)
</h2>


<div class="stats">

    <div class="card">
        Total Products: <?= $total_products ?>
    </div>

    <div class="card">
        Low Stock Items: <?= $low_stock ?>
    </div>

</div>

<!-- FILTER -->
<form method="GET">

    <input type="hidden" name="tab" value="products">
    <input type="hidden" name="mode" value="<?= htmlspecialchars($mode) ?>">

    <input
        type="text"
        name="search"
        placeholder="Search..."
        value="<?= htmlspecialchars($search) ?>"
    >

    <select name="category_id">
        <option value="">All Categories</option>

        <?php
        $cats = mysqli_query(
            $conn,
            "SELECT id,name FROM categories ORDER BY name"
        );

        while($cat = mysqli_fetch_assoc($cats)):
        ?>
            <option
                value="<?= $cat['id']; ?>"
                <?= $category_id == $cat['id'] ? 'selected' : '' ?>
            >
                <?= htmlspecialchars($cat['name']); ?>
            </option>
        <?php endwhile; ?>
    </select>

    <button type="submit">
        Filter
    </button>

</form>

<hr>

<?php

$editProduct = null;

if (isset($_GET['edit_id'])) {

    $edit_id = (int)$_GET['edit_id'];

    $stmt = $conn->prepare("
        SELECT *
        FROM products
        WHERE id=? AND branch_id=?
    ");

    $stmt->bind_param("ii", $edit_id, $branch_id);

    $stmt->execute();

    $editProduct = $stmt->get_result()->fetch_assoc();

    $stmt->close();
}
?>

<h3>
<?= $editProduct ? 'Edit Product' : 'Add Product' ?>
</h3>

<hr>

<form method="POST" enctype="multipart/form-data" class="product-form">

<input type="hidden"
       name="id"
       value="<?= $editProduct['id'] ?? '' ?>">

<input type="hidden"
       name="existing_online_image"
       value="<?= $editProduct['online_image'] ?? '' ?>">

<input type="text"
       name="name"
       placeholder="Product Name"
       value="<?= htmlspecialchars($editProduct['name'] ?? '') ?>"
       required>

<textarea name="description"
          placeholder="Description"><?= htmlspecialchars($editProduct['description'] ?? '') ?></textarea>

<input type="number"
       step="0.01"
       name="retail_price"
       value="<?= $editProduct['retail_price'] ?? '' ?>"
       placeholder="Retail Price"
       required>

<input type="number"
       step="0.01"
       name="wholesale_price"
       value="<?= $editProduct['wholesale_price'] ?? '' ?>"
       placeholder="Wholesale Price"
       required>

<input type="number"
       step="0.01"
       name="online_price"
       value="<?= $editProduct['online_price'] ?? '' ?>"
       placeholder="Online Price"
       required>
<input type="number"
       step="0.01"
       name="cost_price"
       value="<?= $editProduct['cost_price'] ?? '' ?>"
       placeholder="Retail/Online Cost Price"
       required>

<input type="number"
       step="0.01"
       name="wholesale_cost_price"
       value="<?= $editProduct['wholesale_cost_price'] ?? '' ?>"
       placeholder="Wholesale Cost Price"
       required>

<input type="number"
       name="stock"
       value="<?= $editProduct['stock'] ?? '' ?>"
       placeholder="Stock"
       required>

<select name="category_id" required>

    <option value="">Select Category</option>

    <?php
    $cats2 = mysqli_query(
        $conn,
        "SELECT id,name FROM categories ORDER BY name"
    );

    while($cat2 = mysqli_fetch_assoc($cats2)):
    ?>

    <option value="<?= $cat2['id']; ?>"
        <?= (($editProduct['category_id'] ?? 0) == $cat2['id']) ? 'selected' : '' ?>>
        <?= htmlspecialchars($cat2['name']); ?>
    </option>

    <?php endwhile; ?>

</select>
<label>
    <input type="checkbox"
           name="featured"
           value="1"
           <?= !empty($editProduct['featured']) ? 'checked' : '' ?>>
    Featured Product
</label>

<label>
    <input type="checkbox"
           name="is_new"
           value="1"
           <?= !empty($editProduct['is_new']) ? 'checked' : '' ?>>
    New Arrival
</label>
<label>Online Image</label>
<input type="file" name="online_image">

<?php if(!empty($editProduct['online_image'])): ?>
    <img
        src="/infinity/uploads/<?= $editProduct['online_image'] ?>"
        width="80">
<?php endif; ?>

<button type="submit" name="save_product">

    <?= $editProduct ? 'Update Product' : 'Save Product' ?>

</button>


</form>

<hr>

<div class="product-grid">


<?php while($row = $result->fetch_assoc()): ?>

<?php
switch ($mode) {

    case 'retail':
    case 'supermarket':
        $selling_price = $row['retail_price'];
        break;

    case 'wholesale':
        $selling_price = $row['wholesale_price'];
        break;

    default:
        $selling_price = $row['online_price'];
        break;
}

if($mode == 'wholesale'){
    $cost_used = $row['wholesale_cost_price'];
}else{
    $cost_used = $row['cost_price'];
}

$retail_profit =
$row['retail_price']
-
$row['cost_price'];

$online_profit =
$row['online_price']
-
$row['cost_price'];

$wholesale_profit =
$row['wholesale_price']
-
$row['wholesale_cost_price'];
$total_stock_value =
$row['stock'] *
$row['cost_price'];

$total_retail_profit =
$row['stock'] *
$retail_profit;

$total_wholesale_profit =
$row['stock'] *
$wholesale_profit;

$total_online_profit =
$row['stock'] *
$online_profit;
$profit =
$selling_price -
$cost_used;

$margin = $selling_price > 0
    ? ($profit / $selling_price) * 100
    : 0;
?>

<div class="product-card">

   <?php

$productImage = !empty($row['online_image'])
    ? $row['online_image']
    : 'default.png';
?>

<img
    src="/infinity/uploads/<?= $productImage ?>"
    width="120"
>

    <h3><?= htmlspecialchars($row['name']) ?></h3>

    <p>
        Category:
        <?= htmlspecialchars($row['category_name'] ?? 'Uncategorized') ?>
    </p>

    <p>
        <?= htmlspecialchars($row['description'] ?? '') ?>
    </p>

    <p>
      Retail Price: KES <?= number_format($row['retail_price'], 2) ?>
    </p>

    <p>
      Wholesale Price: KES <?= number_format($row['wholesale_price'], 2) ?>
    </p>

    <p>
    Online Price: KES <?= number_format($row['online_price'], 2) ?>
    </p>

    <p>
Retail/Online Cost:
KES <?= number_format($row['cost_price'],2) ?>
</p>

<p>
Wholesale Cost:
KES <?= number_format($row['wholesale_cost_price'],2) ?>
</p>
    <p>
Retail Profit:
KES <?= number_format($retail_profit,2) ?>
</p>

<p>
Wholesale Profit:
KES <?= number_format($wholesale_profit,2) ?>
</p>

<p>
Online Profit:
KES <?= number_format($online_profit,2) ?>
</p>
<p>
Stock Value:
KES <?= number_format($total_stock_value,2) ?>
</p>

<p>
Expected Retail Profit:
KES <?= number_format($total_retail_profit,2) ?>
</p>

<p>
Expected Wholesale Profit:
KES <?= number_format($total_wholesale_profit,2) ?>
</p>

<p>
Expected Online Profit:
KES <?= number_format($total_online_profit,2) ?>
</p>

    <p style="color:<?= $margin >= 30 ? 'green' : ($margin >= 10 ? 'orange' : 'red') ?>">
        Margin: <?= round($margin, 2) ?>%
    </p>

    <p>
        Stock:
        <span style="color:<?= $row['stock'] < 5 ? 'red' : 'black' ?>">
            <?= $row['stock'] ?>
        </span>
    </p>
<form method="GET" style="display:inline;">

    <input type="hidden" name="tab" value="products">
    <input type="hidden" name="mode" value="<?= htmlspecialchars($mode) ?>">
    <input type="hidden" name="edit_id" value="<?= $row['id'] ?>">

    <button type="submit">
        Edit Product
    </button>

</form>
    <!-- DELETE -->
    <form method="POST" style="display:inline;">
        <input
            type="hidden"
            name="delete_id"
            value="<?= $row['id'] ?>"
        >

        <button
            type="submit"
            onclick="return confirm('Delete product?')"
        >
            Delete
        </button>
    </form>

    <!-- CART -->
    <form
        method="POST"
        action="/infinity/add-to-cart.php"
        style="display:inline;"
    >
        <input
            type="hidden"
            name="product_id"
            value="<?= $row['id'] ?>"
        >

        <input
            type="hidden"
            name="mode"
            value="<?= htmlspecialchars($mode) ?>"
        >

        <button type="submit">
            Add To Cart
        </button>
    </form>

</div>

<?php endwhile; ?>

</div>