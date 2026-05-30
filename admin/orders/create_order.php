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
// 🔥 DB
// ==========================
$conn = $GLOBALS['conn'] ?? null;

if (!$conn) {
    die("❌ Database connection missing");
}

// ==========================
// 👤 CUSTOMER
// ==========================
$customer_id = (int)($_GET['customer_id'] ?? 0);

if ($customer_id <= 0) {
    die("❌ Customer not selected");
}

$getCustomer = $conn->prepare("
    SELECT
        id,
        name,
        phone,
        email,
        customer_type
    FROM customers
    WHERE id = ?
    AND branch_id = ?
");

$getCustomer->bind_param(
    "ii",
    $customer_id,
    $branch_id
);

$getCustomer->execute();

$customer = $getCustomer
    ->get_result()
    ->fetch_assoc();

if (!$customer) {
    die("❌ Customer not found");
}

// ==========================
// ➕ SAVE ORDER
// ==========================
if (isset($_POST['save_order'])) {

    $sale_type = trim($_POST['sale_type'] ?? 'walk_in');

    $order_date = trim($_POST['order_date'] ?? '');

    $order_time = trim($_POST['order_time'] ?? '');

    $items_taken = trim($_POST['items_taken'] ?? '');

    $payment_method = trim(
        $_POST['payment_method'] ?? 'cash'
    );

    $payment_status = trim(
        $_POST['payment_status'] ?? 'pending'
    );

    $status = trim(
        $_POST['status'] ?? 'Pending'
    );

    $total_amount = (float)(
        $_POST['total_amount'] ?? 0
    );

    if ($total_amount <= 0) {
        die("❌ Invalid order amount");
    }

    $stmt = $conn->prepare("
        INSERT INTO orders (
            customer_id,
            customer_name,
            phone,
            items_taken,
            order_date,
            order_time,
            total_amount,
            payment_method,
            payment_status,
            status,
            sale_type,
            branch_id
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        die("❌ Prepare failed: ".$conn->error);
    }

    $stmt->bind_param(
        "isssssdssssi",
        $customer['id'],
        $customer['name'],
        $customer['phone'],
        $items_taken,
        $order_date,
        $order_time,
        $total_amount,
        $payment_method,
        $payment_status,
        $status,
        $sale_type,
        $branch_id
    );

    $stmt->execute();

    $stmt->close();

    header(
        "Location: /infinity/admin/pages/dashboard.php?tab=orders"
    );

    exit;
}
?>

<h2>🛒 Create Order</h2>

<div
    style="
        background:white;
        padding:15px;
        border:1px solid #ddd;
        margin-bottom:20px;
    "
>

    <h3>👤 Customer Details</h3>

    <p>
        <b>Name:</b>
        <?= htmlspecialchars($customer['name'] ?? '') ?>
    </p>

    <p>
        <b>Phone:</b>
        <?= htmlspecialchars($customer['phone'] ?? '') ?>
    </p>

    <p>
        <b>Email:</b>
        <?= htmlspecialchars($customer['email'] ?? '') ?>
    </p>

    <p>
        <b>Customer Type:</b>
        <?= htmlspecialchars($customer['customer_type'] ?? '') ?>
    </p>

</div>

<form method="POST">

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

    <label>Order Date</label>

    <input
        type="date"
        name="order_date"
        value="<?= date('Y-m-d') ?>"
        required
    >

    <br><br>

    <label>Order Time</label>

    <input
        type="time"
        name="order_time"
        value="<?= date('H:i') ?>"
        required
    >

    <br><br>

    <label>Items Taken</label>

    <textarea
        name="items_taken"
        rows="5"
        placeholder="Example:
2 Cement
5 Iron Sheets
1 Paint"
        required
    ></textarea>

    <br><br>

    <label>Total Amount</label>

    <input
        type="number"
        step="0.01"
        name="total_amount"
        placeholder="Enter Total Amount"
        required
    >

    <br><br>

    <label>Payment Method</label>

    <select name="payment_method">

        <option value="cash">
            Cash
        </option>

        <option value="mpesa">
            M-Pesa
        </option>

        <option value="invoice">
            Invoice
        </option>

    </select>

    <br><br>

    <label>Payment Status</label>

    <select name="payment_status">

        <option value="pending">
            Pending
        </option>

        <option value="paid">
            Paid
        </option>

    </select>

    <br><br>

    <label>Order Status</label>

    <select name="status">

        <option value="Pending">
            Pending
        </option>

        <option value="Processing">
            Processing
        </option>

        <option value="Shipped">
            Shipped
        </option>

        <option value="Delivered">
            Delivered
        </option>

    </select>

    <br><br>

    <button
        type="submit"
        name="save_order"
    >
        💾 Save Order
    </button>

</form>