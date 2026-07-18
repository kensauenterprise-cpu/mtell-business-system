<?php 
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ==========================
// SESSION
// ==========================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$base = "?tab=pos";

if (isset($_POST['sale_mode'])) {
    $_SESSION['sale_mode'] = $_POST['sale_mode'];
}
// ==========================
// DATABASE
// ==========================
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/db.php';

// ==========================
// DB CHECK
// ==========================
if (!isset($conn) || !$conn) {
    die("❌ Database connection missing");
}

// ==========================
// SESSION + USER
// ==========================
$user_id = $_SESSION['user_id'] ?? 0;
$username = $_SESSION['username'] ?? 'Guest';
$branch_id = (int)($_SESSION['branch_id'] ?? 1);

// ✅ IMPORTANT: keep dashboard routing
$base = "?tab=pos";

// ==========================
// INIT CART
// ==========================
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// ==========================
// FETCH PRODUCTS
// ==========================
$products = $conn->query("
    SELECT
    id,
    name,
    retail_price,
    wholesale_price,
    online_price,
    stock,
    barcode
FROM products
    WHERE branch_id = $branch_id
    ORDER BY name ASC
");

// ==========================
// BARCODE ADD
// ==========================
if (isset($_GET['barcode'])) {

    $barcode = trim($_GET['barcode']);

    $stmt = $conn->prepare("
        SELECT id
        FROM products
        WHERE barcode = ?
        AND branch_id = ?
        LIMIT 1
    ");

    $stmt->bind_param("si", $barcode, $branch_id);
    $stmt->execute();

    $res = $stmt->get_result();

    if ($res && $res->num_rows > 0) {

        $p = $res->fetch_assoc();

        header("Location: {$base}&add=".$p['id']."&focus=cart");
        exit;

    } else {

        echo "<script>alert('❌ Product not found');</script>";
    }

    $stmt->close();
}

// ==========================
// ADD TO CART
// ==========================
if (isset($_GET['add'])) {

    $id = (int)$_GET['add'];

    $stmt = $conn->prepare("
        SELECT stock
        FROM products
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->bind_param("i", $id);
    $stmt->execute();

    $res = $stmt->get_result();
    $p = $res->fetch_assoc();

    if ($p && $p['stock'] > ($_SESSION['cart'][$id] ?? 0)) {

        $_SESSION['cart'][$id] =
            ($_SESSION['cart'][$id] ?? 0) + 1;
    }

    $stmt->close();

    header("Location: {$base}&focus=cart");
    exit;
}

// ==========================
// REMOVE
// ==========================
if (isset($_GET['remove'])) {

    unset($_SESSION['cart'][(int)$_GET['remove']]);

    header("Location: {$base}&focus=cart");
    exit;
}

// ==========================
// QTY CONTROL
// ==========================
if (isset($_GET['inc'])) {

    $_SESSION['cart'][(int)$_GET['inc']]++;

    header("Location: {$base}&focus=cart");
    exit;
}

if (isset($_GET['dec'])) {

    $id = (int)$_GET['dec'];

    $_SESSION['cart'][$id]--;

    if ($_SESSION['cart'][$id] <= 0) {
        unset($_SESSION['cart'][$id]);
    }

    header("Location: {$base}&focus=cart");
    exit;
}

// ==========================
// CHECKOUT
// ==========================
if (isset($_POST['checkout'])) {

    $payment = trim($_POST['payment'] ?? 'cash');

    $phone = trim($_POST['phone'] ?? '');

    $customer_id = !empty($_POST['customer_id'])
        ? (int)$_POST['customer_id']
        : NULL;

    $total = 0;

    if (empty($_SESSION['cart'])) {
        die("❌ Cart empty");
    }

    // ==========================
    // CALCULATE TOTAL
    // ==========================
    foreach ($_SESSION['cart'] as $pid => $qty) {

        $pid = (int)$pid;

   $stmt = $conn->prepare("
    SELECT
        retail_price,
        wholesale_price,
        online_price,
        stock
    FROM products
    WHERE id = ?
    LIMIT 1
");
        $stmt->bind_param("i", $pid);
        $stmt->execute();

        $res = $stmt->get_result();
        $p = $res->fetch_assoc();

        if (!$p) {
            die("❌ Product missing");
        }

        if ($qty > $p['stock']) {
            die("❌ Not enough stock");
        }
        $mode = $_SESSION['sale_mode'] ?? 'retail';

        $price = $p['retail_price'];

        if ($mode == 'wholesale') {
        $price = $p['wholesale_price'];
        }

        if ($mode == 'online') {
        $price = $p['online_price'];
        }

        $total += $price * $qty;

        $stmt->close();
    }

    // ==========================
    // STATUS
    // ==========================
    $status_map = [
        'cash'   => 'paid',
        'mpesa'  => 'pending',
        'invoice'=> 'credit'
    ];

    $status = $status_map[$payment] ?? 'paid';

    // ==========================
    // INSERT SALE
    // ==========================
    $stmt = $conn->prepare("
        INSERT INTO sales (
            total,
            payment_method,
            branch_id,
            user_id,
            status,
            customer_id
        )
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "dsiisi",
        $total,
        $payment,
        $branch_id,
        $user_id,
        $status,
        $customer_id
    );

    $stmt->execute();

    $sale_id = $stmt->insert_id;

    $stmt->close();

    // ==========================
    // INSERT ITEMS
    // ==========================
    foreach ($_SESSION['cart'] as $pid => $qty) {

        $pid = (int)$pid;

        $stmt = $conn->prepare("
    SELECT
        retail_price,
        wholesale_price,
        online_price
    FROM products
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param("i", $pid);
$stmt->execute();

$res = $stmt->get_result();
$p = $res->fetch_assoc();

$mode = $_SESSION['sale_mode'] ?? 'retail';

$price = $p['retail_price'];

if ($mode == 'wholesale') {
    $price = $p['wholesale_price'];
}

if ($mode == 'online') {
    $price = $p['online_price'];
}

$stmt->close();

        // SALE ITEM
        $stmt = $conn->prepare("
            INSERT INTO sale_items (
                sale_id,
                product_id,
                qty,
                price
            )
            VALUES (?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "iiid",
            $sale_id,
            $pid,
            $qty,
            $price
        );

        $stmt->execute();
        $stmt->close();

        // UPDATE STOCK
        $stmt = $conn->prepare("
            UPDATE products
            SET stock = stock - ?
            WHERE id = ?
        ");

        $stmt->bind_param("ii", $qty, $pid);

        $stmt->execute();
        $stmt->close();
    }

    // ==========================
    // CUSTOMER CREDIT
    // ==========================
    if ($payment === 'invoice' && $customer_id) {

        $stmt = $conn->prepare("
            UPDATE customers
            SET credit_balance = credit_balance + ?
            WHERE id = ?
        ");

        $stmt->bind_param(
            "di",
            $total,
            $customer_id
        );

        $stmt->execute();
        $stmt->close();
    }

    // ==========================
    // MPESA
    // ==========================
    if ($payment === 'mpesa') {

        $stmt = $conn->prepare("
            INSERT INTO mpesa_transactions (
                order_id,
                amount,
                phone,
                status
            )
            VALUES (?, ?, ?, 'Pending')
        ");

        $stmt->bind_param(
            "ids",
            $sale_id,
            $total,
            $phone
        );

        $stmt->execute();
        $stmt->close();

        $_SESSION['cart'] = [];

        echo "
        <script>

        fetch('/infinity/mpesa/stk_push.php', {
            method: 'POST',
            headers: {
                'Content-Type':
                'application/x-www-form-urlencoded'
            },
            body:
                'phone={$phone}' +
                '&amount={$total}' +
                '&order_id={$sale_id}'
        })

        .then(() => {
            alert(' STK Sent');
        })

        .finally(() => {
            window.location =
                '{$base}&receipt={$sale_id}';
        });

        </script>
        ";

        exit;
    }

    $_SESSION['cart'] = [];

    header("Location: {$base}&receipt=$sale_id");
    exit;
}

// ==========================
// RECEIPT
// ==========================
if (isset($_GET['receipt'])) {

    $sale_id = (int)$_GET['receipt'];

    $stmt = $conn->prepare("
        SELECT *
        FROM sales
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->bind_param("i", $sale_id);
    $stmt->execute();

    $sale = $stmt->get_result()->fetch_assoc();

    $stmt->close();

    $items = $conn->query("
        SELECT
            p.name,
            si.qty,
            si.price
        FROM sale_items si
        JOIN products p
            ON p.id = si.product_id
        WHERE si.sale_id = $sale_id
    ");
}
?>

<!DOCTYPE html>
<html>
<head>
<title>POS</title>

<style>
body {
    font-family:Arial;
    display:flex;
    margin:0;
}

.products {
    width:65%;
    padding:20px;
    display:grid;
    grid-template-columns: repeat(4,1fr);
    gap:10px;
}

.product {
    background:white;
    padding:10px;
    border-radius:10px;
    text-align:center;
}

.product a {
    display:block;
    background:#3b82f6;
    color:white;
    padding:6px;
    margin-top:5px;
    border-radius:6px;
    text-decoration:none;
}

.cart {
    width:35%;
    background:#0f172a;
    color:white;
    padding:20px;
}

button {
    padding:8px;
    margin:2px;
}

input,
select {
    width:100%;
    padding:8px;
    margin-top:8px;
}
</style>
</head>

<body>

<?php if (isset($sale)): ?>

<div id="receipt" style="margin:auto;width:300px;">

<div style="text-align:center;">

<img
src="/infinity/uploads/Mtell Online Shopping.jpg"
width="90"
alt="MTELL"
>

<h2>MTELL</h2>

<p>
HQ Nairobi<br>
Phone: +254 106 552 658
</p>

<hr>

<p>
Receipt No: <?= $sale['id'] ?><br>
Date: <?= date('d-m-Y H:i:s') ?><br>
Cashier: <?= htmlspecialchars($_SESSION['username'] ?? 'System') ?>
</p>

</div>

<table width="100%">
<?php while($i = $items->fetch_assoc()): ?>
<tr>
<td><?= htmlspecialchars($i['name']) ?></td>
<td>x<?= (int)$i['qty'] ?></td>
<td align="right">
KES <?= number_format($i['qty'] * $i['price'],2) ?>
</td>
</tr>
<?php endwhile; ?>
</table>

<hr>

<?php
$vat = $sale['total'] * 0.16;
$subtotal = $sale['total'] - $vat;
?>

<p>Subtotal: KES <?= number_format($subtotal,2) ?></p>
<p>VAT (16%): KES <?= number_format($vat,2) ?></p>

<h3>
TOTAL: KES <?= number_format($sale['total'],2) ?>
</h3>

<hr>

<p>
Thank You For Your Business!
</p>

<p>
Powered by MTELL ERP
</p>

<button onclick="window.print()">
Print Receipt
</button>

</div>

<?php else: ?>

<form method="POST">

<select
    name="sale_mode"
    onchange="this.form.submit()"
>

<option value="retail"
<?= ($_SESSION['sale_mode'] ?? 'retail') == 'retail' ? 'selected' : '' ?>>
Supermarket
</option>

<option value="wholesale"
<?= ($_SESSION['sale_mode'] ?? '') == 'wholesale' ? 'selected' : '' ?>>
Wholesale
</option>

<option value="online"
<?= ($_SESSION['sale_mode'] ?? '') == 'online' ? 'selected' : '' ?>>
Online
</option>

</select>

</form>

<input
    type="text"
    id="barcodeInput"
    placeholder="Scan barcode..."
    autofocus
>

<div class="products">
<?php while($p = $products->fetch_assoc()): ?>

<div class="product">

<b>
<?= htmlspecialchars($p['name']) ?>
</b>

<br>

<?php
$mode = $_SESSION['sale_mode'] ?? 'retail';

$price = $p['retail_price'];

if ($mode == 'wholesale') {
    $price = $p['wholesale_price'];
}

if ($mode == 'online') {
    $price = $p['online_price'];
}
?>

KES <?= number_format($price,2) ?>

<br>

Stock: <?= (int)$p['stock'] ?>

<a href="?tab=pos&add=<?= $p['id'] ?>">
➕ Add
</a>

</div>

<?php endwhile; ?>

</div>

<div class="cart" id="cart">

<h2>Cart</h2>

<?php
$total = 0;

foreach ($_SESSION['cart'] as $pid => $qty):

$stmt = $conn->prepare("
    SELECT
        name,
        retail_price,
        wholesale_price,
        online_price
    FROM products
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param("i", $pid);
$stmt->execute();

$res = $stmt->get_result();
$item = $res->fetch_assoc();

$stmt->close();

if (!$item) {
    continue;
}

$mode = $_SESSION['sale_mode'] ?? 'retail';

$price = $item['retail_price'];

if ($mode == 'wholesale') {
    $price = $item['wholesale_price'];
}

if ($mode == 'online') {
    $price = $item['online_price'];
}

$subtotal = $price * $qty;

$total += $subtotal;
?>

<div>

<?= htmlspecialchars($item['name']) ?>

<button
onclick="location.href='?tab=pos&dec=<?= $pid ?>'">
-
</button>

<?= $qty ?>

<button
onclick="location.href='?tab=pos&inc=<?= $pid ?>'">
+
</button>

= KES <?= number_format($subtotal,2) ?>

</div>

<?php endforeach; ?>

<h3>
Total: KES <?= number_format($total,2) ?>
</h3>

<form method="POST">

<select name="payment">

<option value="cash">
Cash
</option>

<option value="mpesa">
MPESA
</option>

<option value="invoice">
Invoice
</option>

</select>

<select name="customer_id">

<option value="">
Walk-in
</option>

<?php
$c = $conn->query("
    SELECT id, name
    FROM customers
    ORDER BY name ASC
");

while($x = $c->fetch_assoc()):
?>

<option value="<?= $x['id'] ?>">
<?= htmlspecialchars($x['name']) ?>
</option>

<?php endwhile; ?>

</select>

<input
    type="text"
    name="phone"
    placeholder="MPESA phone"
>

<button name="checkout">
Checkout
</button>

</form>

</div>

<script>
document
.getElementById("barcodeInput")
.addEventListener("keypress", function(e){

    if(e.key === "Enter") {

        window.location =
            "?tab=pos&barcode=" + this.value;
    }
});
</script>

<?php endif; ?>

</body>
</html>