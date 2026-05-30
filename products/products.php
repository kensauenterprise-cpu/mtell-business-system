<?php

require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/init.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/db.php';

// ==========================
// 🔐 SESSION
// ==========================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==========================
// ✅ DB CHECK
// ==========================
if (!isset($conn) || !$conn) {
    die("❌ Database connection failed");
}

// ==========================
// 🏢 BRANCH (NEW)
// ==========================
$branch_id = $_SESSION['branch_id'] ?? 1;

// ==========================
// 📦 MODE
// ==========================
$mode = $_GET['mode'] ?? 'online';

// ==========================
// 🔍 FILTERS
// ==========================
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';

// ==========================
// ➕ SAVE PRODUCT (ADD / UPDATE)
// ==========================
if (isset($_POST['save_product'])) {

    $id         = $_POST['id'] ?? null;

    $name       = trim($_POST['name'] ?? '');

    $desc       = trim($_POST['description'] ?? '');

    $retail     = (float) ($_POST['retail_price'] ?? 0);

    $wholesale  = (float) ($_POST['wholesale_price'] ?? 0);

    $online     = (float) ($_POST['online_price'] ?? 0);

    $cost       = (float) ($_POST['cost_price'] ?? 0);

    $stock      = (int) ($_POST['stock'] ?? 0);

    $category   = trim($_POST['category'] ?? '');

    $imageName = $_POST['existing_image'] ?? 'default.png';

    // ==========================
    // 📸 IMAGE UPLOAD
    // ==========================
    if (!empty($_FILES['image']['name'])) {

        $allowed = ['jpg','jpeg','png','webp'];

        $ext = strtolower(
            pathinfo(
                $_FILES['image']['name'],
                PATHINFO_EXTENSION
            )
        );

        if (in_array($ext, $allowed)) {

            $targetDir = $_SERVER['DOCUMENT_ROOT'].'/infinity/uploads/';

            if (!is_dir($targetDir)) {

                mkdir($targetDir, 0777, true);
            }

            $imageName =
                time().'_'.uniqid().'.'.$ext;

            move_uploaded_file(
                $_FILES['image']['tmp_name'],
                $targetDir.$imageName
            );
        }
    }

    // ==========================
    // ✏️ UPDATE
    // ==========================
    if ($id) {

        $stmt = $conn->prepare("
            UPDATE products
            SET
                name=?,
                description=?,
                retail_price=?,
                wholesale_price=?,
                online_price=?,
                cost_price=?,
                stock=?,
                category=?,
                image=?
            WHERE id=? AND branch_id=?
        ");

        if ($stmt) {

            $stmt->bind_param(
                "ssddddissii",
                $name,
                $desc,
                $retail,
                $wholesale,
                $online,
                $cost,
                $stock,
                $category,
                $imageName,
                $id,
                $branch_id
            );

            $stmt->execute();

            $stmt->close();
        }

    } else {

        // ==========================
        // ➕ INSERT
        // ==========================
        $stmt = $conn->prepare("
            INSERT INTO products
            (
                name,
                description,
                retail_price,
                wholesale_price,
                online_price,
                cost_price,
                stock,
                category,
                image,
                branch_id,
                created_at
            )
            VALUES
            (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()
            )
        ");

        if ($stmt) {

            $stmt->bind_param(
                "ssddddissi",
                $name,
                $desc,
                $retail,
                $wholesale,
                $online,
                $cost,
                $stock,
                $category,
                $imageName,
                $branch_id
            );

            $stmt->execute();

            $stmt->close();
        }
    }

    header("Location: ?tab=products&mode=".$mode);

    exit;
}

// ==========================
// 🗑 DELETE (SECURED)
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
// 📦 FETCH PRODUCTS
// ==========================
$sql = "
SELECT *
FROM products
WHERE branch_id=?
";

$params = [$branch_id];

$types = "i";

if ($search) {

    $sql .= " AND name LIKE ?";

    $params[] = "%".$search."%";

    $types .= "s";
}

if ($category) {

    $sql .= " AND category=?";

    $params[] = $category;

    $types .= "s";
}

$sql .= " ORDER BY id DESC";

$stmt = $conn->prepare($sql);

if (!$stmt) {

    die("❌ Failed to load products");
}

$stmt->bind_param($types, ...$params);

$stmt->execute();

$result = $stmt->get_result();

// ==========================
// 📊 TOTAL PRODUCTS
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
// ⚠️ LOW STOCK
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

<h2>
🛍️ Products (<?= strtoupper($mode) ?>)
</h2>

<div class="stats">

    <div class="card">
        📦 Total: <?= $total_products ?>
    </div>

    <div class="card">
        ⚠️ Low Stock: <?= $low_stock ?>
    </div>

</div>

<!-- FILTER -->
<form method="GET">

<input
type="hidden"
name="tab"
value="products"
>

<input
type="hidden"
name="mode"
value="<?= htmlspecialchars($mode) ?>"
>

<input
type="text"
name="search"
placeholder="Search..."
value="<?= htmlspecialchars($search) ?>"
>

<select name="category">

<option value="">
All
</option>

<option
<?= $category=='Electronics'?'selected':'' ?>
>
Electronics
</option>

<option
<?= $category=='Drinks'?'selected':'' ?>
>
Drinks
</option>

<option
<?= $category=='Food'?'selected':'' ?>
>
Food
</option>

</select>

<button type="submit">
Filter
</button>

</form>

<hr>

<!-- ADD PRODUCT -->
<h3>➕ Add Product</h3>

<form method="POST" enctype="multipart/form-data">

<input
name="name"
required
placeholder="Name"
>

<textarea
name="description"
placeholder="Description"
></textarea>

<input
type="number"
step="0.01"
name="retail_price"
placeholder="Retail Price"
>

<input
type="number"
step="0.01"
name="wholesale_price"
placeholder="Wholesale Price"
>

<input
type="number"
step="0.01"
name="online_price"
placeholder="Online Price"
>

<input
type="number"
step="0.01"
name="cost_price"
placeholder="Cost Price"
required
>

<input
type="number"
name="stock"
placeholder="Stock"
>

<select name="category">

<option>
Electronics
</option>

<option>
Drinks
</option>

<option>
Food
</option>

</select>

<input
type="file"
name="image"
>

<button
type="submit"
name="save_product"
>
Save Product
</button>

</form>

<hr>

<!-- PRODUCTS -->
<div class="product-grid">

<?php while($row = $result->fetch_assoc()): ?>

<?php

$price =
    $row[$mode.'_price']
    ?? $row['online_price'];

$profit =
    $price - $row['cost_price'];

$margin =
    $price > 0
    ? ($profit / $price) * 100
    : 0;

?>

<div class="product-card">

<img
src="/infinity/uploads/<?= !empty($row['image']) ? $row['image'] : 'default.png' ?>"
width="120"
>

<h3>
<?= htmlspecialchars($row['name']) ?>
</h3>

<p>
<?= htmlspecialchars($row['description']) ?>
</p>

<p>
💰 Price:
KES <?= number_format($price,2) ?>
</p>

<p>
💸 Cost:
KES <?= number_format($row['cost_price'],2) ?>
</p>

<p style="color:<?= $profit >= 0 ? 'green' : 'red' ?>">

📈 Profit:
KES <?= number_format($profit,2) ?>

</p>

<p style="color:<?= $margin >= 30 ? 'green' : ($margin >= 10 ? 'orange':'red') ?>">

📊 Margin:
<?= round($margin,2) ?>%

</p>

<p>

📦 Stock:

<span style="color:<?= $row['stock'] < 5 ? 'red':'black' ?>">

<?= $row['stock'] ?>

</span>

</p>

<!-- DELETE -->
<form method="POST" style="display:inline;">

<input
type="hidden"
name="delete_id"
value="<?= $row['id'] ?>"
>

<button onclick="return confirm('Delete product?')">

🗑

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

<button>

🛒

</button>

</form>

</div>

<?php endwhile; ?>

</div>