<?php
// ==========================
// 🔐 SYSTEM LOAD
// ==========================
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/init.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/role.php';

// ==========================
// 🔐 SESSION
// ==========================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$role = strtolower(trim($_SESSION['role'] ?? 'guest'));

if ($role === 'guest') {

    header("Location: /infinity/admin/pages/login.php");
    exit;
}

// ==========================
// 🏢 BRANCH
// ==========================
$branch_id = (int)($_SESSION['branch_id'] ?? 1);

// ==========================
// 🔥 GLOBAL DB FIX
// ==========================
$conn = $GLOBALS['conn'] ?? null;

if (!$conn) {
    die("❌ Database connection missing");
}

// ==========================
// 👥 FETCH CUSTOMERS
// ==========================
$customerStmt = $conn->prepare("
    SELECT
        id,
        name
    FROM customers
    WHERE branch_id = ?
    ORDER BY name ASC
");

if (!$customerStmt) {
    die("❌ Customer query failed: ".$conn->error);
}

$customerStmt->bind_param("i", $branch_id);

$customerStmt->execute();

$customerResult = $customerStmt->get_result();

// ==========================
// 🔍 FILTERS
// ==========================
$status    = trim($_GET['status'] ?? '');
$payment   = trim($_GET['payment'] ?? '');
$sale_type = trim($_GET['sale_type'] ?? '');

// ==========================
// 🗑 DELETE ORDER
// ==========================
if (isset($_GET['delete'])) {

    $id = (int)($_GET['delete'] ?? 0);

    $stmt = $conn->prepare("
        DELETE FROM orders
        WHERE id = ?
        AND branch_id = ?
    ");

    if ($stmt) {

        $stmt->bind_param(
            "ii",
            $id,
            $branch_id
        );

        $stmt->execute();
        $stmt->close();
    }

    header("Location: /infinity/admin/pages/dashboard.php?tab=orders");
    exit;
}

// ==========================
// 📦 QUERY ORDERS
// ==========================
$sql = "
    SELECT *
    FROM orders
    WHERE branch_id = ?
";

$params = [$branch_id];
$types  = "i";

// STATUS FILTER
if ($status !== '') {

    $sql .= " AND status = ?";

    $params[] = $status;
    $types .= "s";
}

// PAYMENT FILTER
if ($payment !== '') {

    $sql .= " AND payment_method = ?";

    $params[] = $payment;
    $types .= "s";
}

// SALE TYPE FILTER
if ($sale_type !== '') {

    $sql .= " AND sale_type = ?";

    $params[] = $sale_type;
    $types .= "s";
}

$sql .= " ORDER BY id DESC";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("❌ Query failed: ".$conn->error);
}

$stmt->bind_param($types, ...$params);

$stmt->execute();

$orders = $stmt->get_result();

// ==========================
// 📊 KPI STATS
// ==========================
$totalOrdersStmt = $conn->prepare("
    SELECT COUNT(*) as t
    FROM orders
    WHERE branch_id=?
");

$totalOrdersStmt->bind_param("i", $branch_id);

$totalOrdersStmt->execute();

$totalOrders = $totalOrdersStmt
    ->get_result()
    ->fetch_assoc()['t'] ?? 0;

$pendingStmt = $conn->prepare("
    SELECT COUNT(*) as t
    FROM orders
    WHERE status='Pending'
    AND branch_id=?
");

$pendingStmt->bind_param("i", $branch_id);

$pendingStmt->execute();

$pending = $pendingStmt
    ->get_result()
    ->fetch_assoc()['t'] ?? 0;

$completedStmt = $conn->prepare("
    SELECT COUNT(*) as t
    FROM orders
    WHERE status='Delivered'
    AND branch_id=?
");

$completedStmt->bind_param("i", $branch_id);

$completedStmt->execute();

$completed = $completedStmt
    ->get_result()
    ->fetch_assoc()['t'] ?? 0;

$revenueStmt = $conn->prepare("
    SELECT SUM(total_amount) as t
    FROM orders
    WHERE payment_status='paid'
    AND branch_id=?
");

$revenueStmt->bind_param("i", $branch_id);

$revenueStmt->execute();

$revenue = $revenueStmt
    ->get_result()
    ->fetch_assoc()['t'] ?? 0;
?>

<h2>📦 ERP Orders System</h2>

<!-- CUSTOMER SELECTION -->
<div
    style="
        background:white;
        padding:15px;
        margin-bottom:15px;
        border:1px solid #ddd;
    "
>

<h3>👤 Select Customer For Order</h3>

<form
    method="GET"
    action="/infinity/admin/orders/create_order.php"
>

    <label>Customer</label>

    <select
        name="customer_id"
        required
    >

        <option value="">
            Select Customer
        </option>

        <?php while($cust = $customerResult->fetch_assoc()): ?>

            <option
                value="<?= (int)($cust['id'] ?? 0) ?>"

                <?= (
                    (int)($_GET['customer_id'] ?? 0)
                    === (int)($cust['id'] ?? 0)
                )
                ? 'selected'
                : ''
                ?>
            >

                <?= htmlspecialchars($cust['name'] ?? '') ?>

            </option>

        <?php endwhile; ?>

    </select>

    <br><br>

    <label>Sale Type</label>

    <select name="sale_type">

        <option value="walk_in">
            Walk-in Sale
        </option>

        <option value="customer_collection">
            Customer Collection
        </option>

    </select>

    <br><br>

    <button type="submit">
        Continue Order
    </button>

</form>

</div>

<!-- KPI -->
<div style="display:flex; gap:15px; flex-wrap:wrap; margin-bottom:15px;">

    <div class="card">
        📦 Total Orders
        <br>
        <b><?= (int)$totalOrders ?></b>
    </div>

    <div class="card">
        ⏳ Pending
        <br>
        <b style="color:orange">
            <?= (int)$pending ?>
        </b>
    </div>

    <div class="card">
        ✅ Delivered
        <br>
        <b style="color:green">
            <?= (int)$completed ?>
        </b>
    </div>

    <div class="card">
        💰 Revenue
        <br>
        <b>
            KES <?= number_format((float)$revenue, 2) ?>
        </b>
    </div>

</div>

<hr>

<!-- FILTER -->
<form method="GET">

    <input
        type="hidden"
        name="tab"
        value="orders"
    >

    <select name="status">

        <option value="">
            All Status
        </option>

        <option value="Pending" <?= $status=='Pending' ? 'selected' : '' ?>>
            Pending
        </option>

        <option value="Processing" <?= $status=='Processing' ? 'selected' : '' ?>>
            Processing
        </option>

        <option value="Shipped" <?= $status=='Shipped' ? 'selected' : '' ?>>
            Shipped
        </option>

        <option value="Delivered" <?= $status=='Delivered' ? 'selected' : '' ?>>
            Delivered
        </option>

    </select>

    <select name="payment">

        <option value="">
            Payment Method
        </option>

        <option value="mpesa" <?= $payment=='mpesa' ? 'selected' : '' ?>>
            M-Pesa
        </option>

        <option value="cash" <?= $payment=='cash' ? 'selected' : '' ?>>
            Cash
        </option>

        <option value="invoice" <?= $payment=='invoice' ? 'selected' : '' ?>>
            Invoice
        </option>

    </select>

    <!-- SALE TYPE FILTER -->
    <select name="sale_type">

        <option value="">
            Sale Type
        </option>

        <option value="walk_in" <?= $sale_type=='walk_in' ? 'selected' : '' ?>>
            Walk-in Sale
        </option>

        <option value="customer_collection" <?= $sale_type=='customer_collection' ? 'selected' : '' ?>>
            Customer Collection
        </option>

    </select>

    <button>
        Filter
    </button>

</form>

<hr>

<table
    border="1"
    cellpadding="8"
    width="100%"
    style="background:white;"
>

<tr style="background:#f1f5f9;">

    <th>ID</th>

    <th>Customer</th>

    <th>Items</th>

    <th>Sale Type</th>

    <th>Total</th>

    <th>Payment</th>

    <th>Payment Status</th>

    <th>Status</th>

    <th>Phone</th>

    <th>Date</th>

    <th>Actions</th>

</tr>

<?php if ($orders && $orders->num_rows > 0): ?>

    <?php while($o = $orders->fetch_assoc()): ?>

    <tr>

        <td>
            #<?= (int)($o['id'] ?? 0) ?>
        </td>

        <td>
            <?= htmlspecialchars($o['customer_name'] ?? '') ?>
        </td>

        <td>
            <?= nl2br(htmlspecialchars($o['items_taken'] ?? '')) ?>
        </td>

        <td>

            <?=
                ($o['sale_type'] ?? '') === 'customer_collection'
                ? 'Customer Collection'
                : 'Walk-in Sale'
            ?>

        </td>

        <td>
            <b>
                KES <?= number_format((float)($o['total_amount'] ?? 0), 2) ?>
            </b>
        </td>

        <td>
            <?= strtoupper(htmlspecialchars($o['payment_method'] ?? '')) ?>
        </td>

        <td>

            <span style="color:
                <?= ($o['payment_status'] ?? '')=='paid'
                    ? 'green'
                    : (
                        ($o['payment_status'] ?? '')=='pending'
                        ? 'orange'
                        : 'red'
                    )
                ?>
            ">

                <?= strtoupper(htmlspecialchars($o['payment_status'] ?? '')) ?>

            </span>

        </td>

        <td>

            <span style="color:
                <?= ($o['status'] ?? '')=='Delivered'
                    ? 'green'
                    : (
                        ($o['status'] ?? '')=='Pending'
                        ? 'orange'
                        : (
                            ($o['status'] ?? '')=='Processing'
                            ? 'blue'
                            : 'gray'
                        )
                    )
                ?>
            ">

                <?= htmlspecialchars($o['status'] ?? '') ?>

            </span>

        </td>

        <td>
            <?= htmlspecialchars($o['phone'] ?? '') ?>
        </td>

        <td>

            <?= htmlspecialchars($o['order_date'] ?? '') ?>

            <br>

            <small>
                <?= htmlspecialchars($o['order_time'] ?? '') ?>
            </small>

        </td>

        <td>

            <a href="/infinity/admin/orders/order_view.php?id=<?= (int)($o['id'] ?? 0) ?>">
                👁
            </a>

            |

            <a
                href="?tab=orders&delete=<?= (int)($o['id'] ?? 0) ?>"
                onclick="return confirm('Delete order?')"
            >
                🗑
            </a>

        </td>

    </tr>

    <?php endwhile; ?>

<?php else: ?>

<tr>

    <td colspan="11">
        No orders found
    </td>

</tr>

<?php endif; ?>

</table>

<?php
$stmt->close();
?>